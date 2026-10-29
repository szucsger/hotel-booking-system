<?php

final class ReceptionService
{
    /**
     * Lekéri a recepciós oldalon megjelenő számlálókat, érkezéseket,
     * távozásokat és módosítható szobákat.
     */
    public static function getDashboard(PDO $pdo): array
    {
        $todayArrivals = (int) $pdo->query("
            SELECT COUNT(*)
            FROM bookings
            WHERE check_in_date = CURDATE()
              AND status = 'confirmed'
        ")->fetchColumn();

        $todayDepartures = (int) $pdo->query("
            SELECT COUNT(*)
            FROM bookings
            WHERE check_out_date = CURDATE()
              AND status = 'confirmed'
        ")->fetchColumn();

        $availableRooms = (int) $pdo->query("
            SELECT COUNT(*)
            FROM rooms
            WHERE rooms.status = 'free'
              AND NOT EXISTS (
                  SELECT 1
                  FROM bookings
                  WHERE bookings.room_id = rooms.id
                    AND bookings.status IN ('pending', 'confirmed', 'checked_in')
                    AND bookings.check_in_date <= CURDATE()
                    AND bookings.check_out_date > CURDATE()
              )
        ")->fetchColumn();

        $stmt = $pdo->prepare("
            SELECT bookings.id AS booking_id, users.name AS guest_name,
                   rooms.room_number, room_types.name AS room_type,
                   bookings.check_in_date
            FROM bookings
            JOIN users ON users.id = bookings.user_id
            JOIN rooms ON rooms.id = bookings.room_id
            JOIN room_types ON room_types.id = rooms.room_type_id
            WHERE bookings.check_in_date >= :check_in_date
              AND bookings.status = 'confirmed'
            ORDER BY bookings.check_in_date ASC, rooms.room_number ASC
        ");
        $stmt->execute(['check_in_date' => date('Y-m-d')]);
        $arrivals = $stmt->fetchAll();

        $stmt = $pdo->prepare("
            SELECT bookings.id AS booking_id, users.name AS guest_name,
                   rooms.room_number, room_types.name AS room_type,
                   bookings.check_out_date, bookings.status
            FROM bookings
            JOIN users ON users.id = bookings.user_id
            JOIN rooms ON rooms.id = bookings.room_id
            JOIN room_types ON room_types.id = rooms.room_type_id
            WHERE bookings.check_out_date = :check_out_date
              AND bookings.status IN ('confirmed', 'checked_in')
            ORDER BY rooms.room_number
        ");
        $stmt->execute(['check_out_date' => date('Y-m-d')]);
        $departures = $stmt->fetchAll();

        $roomsForStatusChange = $pdo->query("
            SELECT rooms.id, rooms.room_number, rooms.status
            FROM rooms
            WHERE NOT EXISTS (
                SELECT 1
                FROM bookings
                WHERE bookings.room_id = rooms.id
                  AND bookings.status = 'checked_in'
            )
            ORDER BY rooms.room_number
        ")->fetchAll();

        return [
            'today_arrivals' => $todayArrivals,
            'today_departures' => $todayDepartures,
            'available_rooms' => $availableRooms,
            'arrivals' => $arrivals,
            'departures' => $departures,
            'rooms_for_status_change' => $roomsForStatusChange,
        ];
    }

    /* Név vagy e-mail-cím alapján legfeljebb húsz regisztrált vendéget keres. */
    public static function searchGuests(PDO $pdo, array $input): array
    {
        $term = self::stringValue($input, 'search_guest_name');
        if ($term === '') {
            throw new DomainException('Add meg a vendég nevét vagy e-mail-címét.', 422);
        }

        $stmt = $pdo->prepare("
            SELECT name, email, phone_number
            FROM users
            WHERE role = 'user'
              AND is_deleted = 0
              AND (name LIKE :search_name OR email LIKE :search_email)
            ORDER BY name
            LIMIT 20
        ");
        $pattern = '%' . $term . '%';
        $stmt->execute([
            'search_name' => $pattern,
            'search_email' => $pattern,
        ]);

        return [
            'term' => $term,
            'guests' => $stmt->fetchAll(),
        ];
    }

    /* A megadott dátumokhoz és vendégszámhoz megfelelő, nem foglalt szobákat adja vissza. */
    public static function getAvailableRooms(PDO $pdo, array $input): array
    {
        [$checkIn, $checkOut] = self::validatedDateRange($input);
        $guestCount = self::positiveInteger($input, 'guest_count');

        $stmt = $pdo->prepare("
            SELECT rooms.id, rooms.room_number, room_types.name AS room_type
            FROM rooms
            JOIN room_types ON room_types.id = rooms.room_type_id
            WHERE room_types.capacity >= :guest_count
              AND rooms.status NOT IN ('cleaning', 'maintenance')
              AND (:arrival_date > CURDATE() OR rooms.status = 'free')
              AND NOT EXISTS (
                  SELECT 1
                  FROM bookings
                  WHERE bookings.room_id = rooms.id
                    AND bookings.status IN ('pending', 'confirmed', 'checked_in')
                    AND bookings.check_in_date < :new_check_out
                    AND bookings.check_out_date > :new_check_in
              )
            ORDER BY rooms.room_number
        ");
        $stmt->execute([
            'guest_count' => $guestCount,
            'arrival_date' => $checkIn->format('Y-m-d'),
            'new_check_out' => $checkOut->format('Y-m-d'),
            'new_check_in' => $checkIn->format('Y-m-d'),
        ]);
        $rooms = $stmt->fetchAll();

        return [
            'rooms' => $rooms,
            'message' => $rooms === []
                ? 'Erre az időszakra nincs szabad, megfelelő kapacitású szoba.'
                : '',
        ];
    }

    /* Ellenőrzi a vendéget és a szobát, majd tranzakcióban rögzíti a foglalást. */
    public static function createBooking(PDO $pdo, array $input): void
    {
        $guestName = self::stringValue($input, 'guest_name');
        $guestEmail = self::stringValue($input, 'guest_email');
        $guestCount = self::positiveInteger($input, 'guest_count');
        $roomId = self::positiveInteger($input, 'room_id');
        [$checkIn, $checkOut] = self::validatedDateRange($input);

        if ($guestName === '' || !filter_var($guestEmail, FILTER_VALIDATE_EMAIL)) {
            throw new DomainException('Ellenőrizd a megadott adatokat.', 422);
        }

        $pdo->beginTransaction();

        try {
            $stmt = $pdo->prepare("
                SELECT id
                FROM users
                WHERE name = :name
                  AND email = :email
                  AND role = 'user'
                  AND is_deleted = 0
                LIMIT 1
            ");
            $stmt->execute([
                'name' => $guestName,
                'email' => $guestEmail,
            ]);
            $guestId = $stmt->fetchColumn();

            if ($guestId === false) {
                throw new DomainException('Nem található ilyen regisztrált vendég.', 422);
            }

            $stmt = $pdo->prepare("
                SELECT rooms.id, room_types.capacity, room_types.price_per_night
                FROM rooms
                JOIN room_types ON room_types.id = rooms.room_type_id
                WHERE rooms.id = :room_id
                  AND rooms.status NOT IN ('cleaning', 'maintenance')
                  AND room_types.capacity >= :guest_count
                  AND (:arrival_date > CURDATE() OR rooms.status = 'free')
                FOR UPDATE
            ");
            $stmt->execute([
                'room_id' => $roomId,
                'guest_count' => $guestCount,
                'arrival_date' => $checkIn->format('Y-m-d'),
            ]);
            $room = $stmt->fetch();

            if (!$room) {
                throw new DomainException(
                    'A kiválasztott szoba nem elérhető erre az időszakra vagy nem megfelelő a kapacitása.',
                    409
                );
            }

            $stmt = $pdo->prepare("
                SELECT COUNT(*)
                FROM bookings
                WHERE room_id = :room_id
                  AND status IN ('pending', 'confirmed', 'checked_in')
                  AND check_in_date < :new_check_out
                  AND check_out_date > :new_check_in
            ");
            $stmt->execute([
                'room_id' => $room['id'],
                'new_check_out' => $checkOut->format('Y-m-d'),
                'new_check_in' => $checkIn->format('Y-m-d'),
            ]);

            if ((int) $stmt->fetchColumn() > 0) {
                throw new DomainException('A szoba már foglalt erre az időszakra.', 409);
            }

            $nights = $checkIn->diff($checkOut)->days;
            $totalPrice = $nights * (int) $room['price_per_night'];

            $stmt = $pdo->prepare("
                INSERT INTO bookings
                    (user_id, room_id, check_in_date, check_out_date,
                     guest_count, total_price, status)
                VALUES
                    (:user_id, :room_id, :check_in_date, :check_out_date,
                     :guest_count, :total_price, 'confirmed')
            ");
            $stmt->execute([
                'user_id' => $guestId,
                'room_id' => $room['id'],
                'check_in_date' => $checkIn->format('Y-m-d'),
                'check_out_date' => $checkOut->format('Y-m-d'),
                'guest_count' => $guestCount,
                'total_price' => $totalPrice,
            ]);

            $pdo->commit();
        } catch (Throwable $e) {
            // Hiba esetén a részleges foglalás helyett minden tranzakciós módosítást visszavonunk.
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }

            throw $e;
        }
    }

    /** Megváltoztatja egy szoba státuszát, ha nincs hozzá bejelentkezett vendég. */
    public static function modifyRoomStatus(PDO $pdo, array $input): void
    {
        $roomId = self::positiveInteger($input, 'room_id');
        $newStatus = self::stringValue($input, 'room_status');
        $allowedStatuses = ['free', 'occupied', 'cleaning', 'maintenance'];

        if (!in_array($newStatus, $allowedStatuses, true)) {
            throw new DomainException('Érvénytelen szobastátusz.', 422);
        }

        $pdo->beginTransaction();

        try {
            $stmt = $pdo->prepare("
                SELECT id
                FROM rooms
                WHERE id = :room_id
                FOR UPDATE
            ");
            $stmt->execute(['room_id' => $roomId]);
            $roomId = $stmt->fetchColumn();

            if ($roomId === false) {
                throw new DomainException('A szoba nem található.', 404);
            }

            $stmt = $pdo->prepare("
                SELECT COUNT(*)
                FROM bookings
                WHERE room_id = :room_id
                  AND status = 'checked_in'
            ");
            $stmt->execute(['room_id' => $roomId]);

            if ((int) $stmt->fetchColumn() > 0) {
                throw new DomainException('Bejelentkezett vendég szobájának státusza nem módosítható.', 409);
            }

            $stmt = $pdo->prepare("
                UPDATE rooms
                SET status = :status
                WHERE id = :room_id
            ");
            $stmt->execute([
                'status' => $newStatus,
                'room_id' => $roomId,
            ]);

            $pdo->commit();
        } catch (Throwable $e) {
            // Az ellenőrzés és a frissítés együtt maradjon atomi művelet.
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }

            throw $e;
        }
    }

    /** A mai napra vagy korábbra eső, megerősített foglalást bejelentkezteti. */
    public static function checkIn(PDO $pdo, array $input): void
    {
        self::changeBookingAndRoomStatus($pdo, $input, 'check_in');
    }

    /** A mai napra vagy korábbra eső, bejelentkezett foglalást kijelentkezteti. */
    public static function checkOut(PDO $pdo, array $input): void
    {
        self::changeBookingAndRoomStatus($pdo, $input, 'check_out');
    }

    /**
     * Közös tranzakciós folyamat a be- és kijelentkezéshez; mindkét adatbázisrekordot frissíti.
     */
    private static function changeBookingAndRoomStatus(PDO $pdo, array $input, string $action): void
    {
        $bookingId = self::positiveInteger($input, 'booking_id');
        $isCheckIn = $action === 'check_in';
        $expectedStatus = $isCheckIn ? 'confirmed' : 'checked_in';
        // A feltétel kizárólag a fenti két rögzített érték egyike lehet, nem kérésből érkezik.
        $dateCondition = $isCheckIn ? 'check_in_date <= CURDATE()' : 'check_out_date <= CURDATE()';
        $newBookingStatus = $isCheckIn ? 'checked_in' : 'checked_out';
        $newRoomStatus = $isCheckIn ? 'occupied' : 'cleaning';
        $failureMessage = $isCheckIn
            ? 'A foglalás nem található, vagy még nem érkezhetett meg.'
            : 'A foglalás nem található, vagy a vendég nincs bejelentkezve.';

        $pdo->beginTransaction();

        try {
            $stmt = $pdo->prepare("
                SELECT room_id
                FROM bookings
                WHERE id = :id
                  AND status = :status
                  AND $dateCondition
                FOR UPDATE
            ");
            $stmt->execute([
                'id' => $bookingId,
                'status' => $expectedStatus,
            ]);
            $booking = $stmt->fetch();

            if (!$booking) {
                throw new DomainException($failureMessage, 409);
            }

            $stmt = $pdo->prepare("
                UPDATE bookings
                SET status = :status
                WHERE id = :id
            ");
            $stmt->execute([
                'status' => $newBookingStatus,
                'id' => $bookingId,
            ]);

            $stmt = $pdo->prepare("
                UPDATE rooms
                SET status = :status
                WHERE id = :room_id
            ");
            $stmt->execute([
                'status' => $newRoomStatus,
                'room_id' => $booking['room_id'],
            ]);

            $pdo->commit();
        } catch (Throwable $e) {
            // Ha bármelyik státuszfrissítés hibázik, mindkettőt visszaállítjuk.
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }

            throw $e;
        }
    }

    /** Szigorúan ellenőrzi a bejelentkezés és kijelentkezés dátumát, sorrendjét és múltbeliségét. */
    private static function validatedDateRange(array $input): array
    {
        $checkInValue = self::stringValue($input, 'check_in_date');
        $checkOutValue = self::stringValue($input, 'check_out_date');
        $checkIn = DateTimeImmutable::createFromFormat('!Y-m-d', $checkInValue);
        $checkOut = DateTimeImmutable::createFromFormat('!Y-m-d', $checkOutValue);

        if (
            !$checkIn
            || !$checkOut
            || $checkIn->format('Y-m-d') !== $checkInValue
            || $checkOut->format('Y-m-d') !== $checkOutValue
            || $checkIn < new DateTimeImmutable('today')
            || $checkOut <= $checkIn
        ) {
            throw new DomainException(
                'Adj meg érvényes érkezési és távozási dátumot.',
                422
            );
        }

        return [$checkIn, $checkOut];
    }

    /** Pozitív egész számként olvas be egy űrlapmezőt; hibás értéknél validációs hibát jelez. */
    private static function positiveInteger(array $input, string $key): int
    {
        $value = $input[$key] ?? null;
        $integer = is_string($value) || is_int($value)
            ? filter_var($value, FILTER_VALIDATE_INT)
            : false;

        if ($integer === false || $integer < 1) {
            throw new DomainException('Ellenőrizd a megadott adatokat.', 422);
        }

        return $integer;
    }

    /** Csak szöveges űrlapértéket fogad el, és eltávolítja a szélső szóközöket. */
    private static function stringValue(array $input, string $key): string
    {
        $value = $input[$key] ?? '';

        return is_string($value) ? trim($value) : '';
    }
}
