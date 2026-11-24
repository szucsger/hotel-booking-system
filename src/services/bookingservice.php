<?php

final class BookingService
{
    /**
     * Létrehoz egy vendég által leadott és azonnal megerősített foglalást.
     * A user ID-t a hívó a bejelentkezett munkamenetből adja át.
     */
    public static function createGuestBooking(PDO $pdo, int $userId, array $input): int
    {
        // Bejelentkezett felhasználó nélkül nem készülhet foglalás.
        if ($userId < 1) {
            throw new DomainException('Jelentkezz be a foglaláshoz.', 401);
        }

        // A szoba és a vendégszám pozitív egész szám kell legyen.
        $roomId = self::positiveInteger($input, 'room_id');
        $guestCount = self::positiveInteger($input, 'guest_count');
        $checkInValue = $input['check_in_date'] ?? null;
        $checkOutValue = $input['check_out_date'] ?? null;

        // A dátummezőknek szövegként kell érkezniük.
        if (!is_string($checkInValue) || !is_string($checkOutValue)) {
            throw new DomainException('Adj meg érvényes dátumokat.', 422);
        }

        // Szigorúan YYYY-MM-DD formátumra alakítjuk a dátumokat.
        $checkIn = DateTimeImmutable::createFromFormat('!Y-m-d', $checkInValue);
        $checkOut = DateTimeImmutable::createFromFormat('!Y-m-d', $checkOutValue);

        // Elutasítjuk a hibás, múltbeli vagy rossz sorrendű dátumokat.
        if (
            !$checkIn
            || !$checkOut
            || $checkIn->format('Y-m-d') !== $checkInValue
            || $checkOut->format('Y-m-d') !== $checkOutValue
            || $checkIn < new DateTimeImmutable('today')
            || $checkOut <= $checkIn
        ) {
            throw new DomainException('Adj meg érvényes érkezési és távozási dátumot.', 422);
        }

        // Az ellenőrzéseket és a mentést egy tranzakcióban tartjuk.
        $pdo->beginTransaction();

        try {
            // Ellenőrizzük, hogy a munkamenetből kapott felhasználó aktív vendég-e.
            $stmt = $pdo->prepare("
                SELECT id
                FROM users
                WHERE id = :user_id
                  AND role = 'user'
                  AND is_deleted = 0
            ");
            $stmt->execute(['user_id' => $userId]);

            if ($stmt->fetchColumn() === false) {
                throw new DomainException('A vendégfiók nem található.', 403);
            }

            // Lekérjük és zároljuk a szobát, miközben ellenőrizzük a kapacitását
            // és azt, hogy az aktuális státusza alapján foglalható-e.
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
                'arrival_date' => $checkInValue,
            ]);
            $room = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($room === false) {
                throw new DomainException(
                    'A szoba nem elérhető, vagy nem fér el benne ennyi vendég.',
                    409
                );
            }

            // A két dátumtartomány átfed, ha a meglévő foglalás vége későbbi
            // az új érkezésnél, és a meglévő érkezés korábbi az új távozásnál.
            $stmt = $pdo->prepare("
                SELECT COUNT(*)
                FROM bookings
                WHERE room_id = :room_id
                  AND status IN ('pending', 'confirmed', 'checked_in')
                  AND check_in_date < :new_check_out
                  AND check_out_date > :new_check_in
            ");
            $stmt->execute([
                'room_id' => $roomId,
                'new_check_out' => $checkOutValue,
                'new_check_in' => $checkInValue,
            ]);

            if ((int) $stmt->fetchColumn() > 0) {
                throw new DomainException('A szoba időközben foglalttá vált.', 409);
            }

            // A végösszeg szerveroldalon, az adatbázisból lekért ár alapján készül.
            $nights = $checkIn->diff($checkOut)->days;
            $totalPrice = $nights * (int) $room['price_per_night'];

            // Az új vendégfoglalást azonnal megerősítjük.
            $stmt = $pdo->prepare("
                INSERT INTO bookings
                    (user_id, room_id, check_in_date, check_out_date,
                     guest_count, total_price, status)
                VALUES
                    (:user_id, :room_id, :check_in_date, :check_out_date,
                     :guest_count, :total_price, 'confirmed')
            ");
            $stmt->execute([
                'user_id' => $userId,
                'room_id' => $roomId,
                'check_in_date' => $checkInValue,
                'check_out_date' => $checkOutValue,
                'guest_count' => $guestCount,
                'total_price' => $totalPrice,
            ]);

            // Siker esetén véglegesítjük a mentést, és visszaadjuk az új foglalás ID-ját.
            $bookingId = (int) $pdo->lastInsertId();
            $pdo->commit();

            return $bookingId;
        } catch (Throwable $e) {
            // Hiba esetén visszavonjuk az összes tranzakción belüli módosítást.
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }

            throw $e;
        }
    }

    /**
     * Pozitív egész számként olvas be egy űrlapértéket.
     * Hibás vagy hiányzó értéknél validációs hibát jelez.
     */
    private static function positiveInteger(array $input, string $key): int
    {
        $value = $input[$key] ?? null;
        $integer = is_string($value) || is_int($value)
            ? filter_var($value, FILTER_VALIDATE_INT)
            : false;

        if ($integer === false || $integer < 1) {
            throw new DomainException('Ellenőrizd a foglalási adatokat.', 422);
        }

        return $integer;
    }
}