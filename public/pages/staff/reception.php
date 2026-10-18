<?php
session_start();
include_once('../../../src/config/db.php');

$guestSearchSubmitted = false;
$guestSearchTerm = '';
$guestResults = [];
$roomStatusMessage = '';

if (isset($_SESSION['room_status_message'])) {
    $roomStatusMessage = $_SESSION['room_status_message'];
    unset($_SESSION['room_status_message']);
}

if (isset($_SESSION['guest_search_result'])) {
    $searchResult = $_SESSION['guest_search_result'];
    unset($_SESSION['guest_search_result']);

    if (is_array($searchResult)) {
        $guestSearchSubmitted = true;
        $guestSearchTerm = $searchResult['term'] ?? '';
        $guestResults = $searchResult['results'] ?? [];
    }
}

try {

 $pdo = new PDO("mysql:host=$host;dbname=$db", $user, $pass);
 $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

// Handle guest search requests
if ($_SERVER['REQUEST_METHOD'] === 'POST'
    && ($_POST['action'] ?? '') === 'search_guest') {

    $guestSearchTerm = trim((string) ($_POST['search_guest_name'] ?? ''));
    $guestResults = [];

    if ($guestSearchTerm !== '') {
        $stmt = $pdo->prepare("
            SELECT name, email, phone_number
            FROM users
            WHERE role = 'user'
              AND is_deleted = 0
              AND (name LIKE :search_name OR email LIKE :search_email)
            ORDER BY name
            LIMIT 20
        ");
        $searchPattern = '%' . $guestSearchTerm . '%';
        $stmt->execute([
            'search_name' => $searchPattern,
            'search_email' => $searchPattern,
        ]);
        $guestResults = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    $_SESSION['guest_search_result'] = [
        'term' => $guestSearchTerm,
        'results' => $guestResults,
    ];
    header('Location: reception.php');
    exit;
}

// Handle new booking requests
if ($_SERVER['REQUEST_METHOD'] === 'POST'
    && ($_POST['action'] ?? '') === 'new_booking') {

    $guestName = trim((string) ($_POST['guest_name'] ?? ''));
    $guestEmail = trim((string) ($_POST['guest_email'] ?? ''));
    $guestCount = filter_var($_POST['guest_count'] ?? null, FILTER_VALIDATE_INT);
    $roomNumber = trim((string) ($_POST['room_number'] ?? ''));
    $checkInValue = $_POST['check_in_date'] ?? '';
    $checkOutValue = $_POST['check_out_date'] ?? '';

    if (
        $guestName === ''
        || !filter_var($guestEmail, FILTER_VALIDATE_EMAIL)
        || $guestCount === false
        || $guestCount < 1
        || $roomNumber === ''
    ) {
        die('Ellenőrizd a megadott adatokat.');
    }

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
        die('A dátumok érvénytelenek.');
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
            throw new RuntimeException('Nem található ilyen regisztrált vendég.');
        }

        $stmt = $pdo->prepare("
            SELECT rooms.id, room_types.capacity, room_types.price_per_night
            FROM rooms
            JOIN room_types ON room_types.id = rooms.room_type_id
            WHERE rooms.room_number = :room_number
              AND (:arrival_date > CURDATE() OR rooms.status = 'free')
            FOR UPDATE
        ");
        $stmt->execute([
            'room_number' => $roomNumber,
            'arrival_date' => $checkInValue,
        ]);
        $room = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$room) {
            throw new RuntimeException('A szoba nem található vagy ma nem szabad.');
        }

        if ($guestCount > (int) $room['capacity']) {
            throw new RuntimeException('Túl sok vendég ehhez a szobához.');
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
            'new_check_out' => $checkOutValue,
            'new_check_in' => $checkInValue,
        ]);

        if ((int) $stmt->fetchColumn() > 0) {
            throw new RuntimeException('A szoba már foglalt erre az időszakra.');
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
            'check_in_date' => $checkInValue,
            'check_out_date' => $checkOutValue,
            'guest_count' => $guestCount,
            'total_price' => $totalPrice,
        ]);

        $pdo->commit();
    } catch (Throwable $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }

        if ($e instanceof RuntimeException) {
            die(htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8'));
        }

        throw $e;
    }

    header('Location: reception.php');
    exit;
}

// Room status modifications
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'modify_room_status') {

    $roomId = filter_var($_POST['room_id'] ?? null, FILTER_VALIDATE_INT);
    $newStatus = $_POST['room_status'] ?? '';
    $allowedStatuses = ['free', 'occupied', 'cleaning', 'maintenance'];

    if (!$roomId || !in_array($newStatus, $allowedStatuses, true)) {
        die('Érvénytelen szoba vagy státusz.');
    }
    
    $pdo->beginTransaction();

    try {
        $stmt = $pdo->prepare("SELECT id, status FROM rooms WHERE id = :room_id FOR UPDATE");
        $stmt->execute(['room_id' => $roomId]);
        $room = $stmt->fetch(PDO::FETCH_ASSOC);

        if(!$room) {
            throw new RuntimeException('A szoba nem található.');
        }
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM bookings WHERE room_id = :room_id AND status = 'checked_in'");
        $stmt->execute(['room_id' => $room['id']]);

        if((int) $stmt->fetchColumn() > 0) {    
            throw new RuntimeException('Bejelentkezett vendég szobájának státusza nem módosítható.');
        }
        $stmt = $pdo->prepare("UPDATE rooms SET status = :status WHERE id = :room_id");
        $stmt->execute([
            'status' => $newStatus,
            'room_id' => $room['id'],
        ]);
        $pdo->commit();
        $_SESSION['room_status_message'] = 'A szoba státusza sikeresen módosult.';
    } catch (Throwable $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        if ($e instanceof RuntimeException) {
            die(htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8'));
        }
        throw $e;
    }
    header('Location: reception.php');
    exit;
}
 // Handle check-in requests
if ($_SERVER['REQUEST_METHOD'] === 'POST'
    && ($_POST['action'] ?? '') === 'check_in') {

    $bookingId = filter_input(INPUT_POST, 'booking_id', FILTER_VALIDATE_INT);

    if (!$bookingId) {
        die('Érvénytelen foglalásazonosító.');
    }

    $pdo->beginTransaction();

    try {
        $stmt = $pdo->prepare("
            SELECT room_id
            FROM bookings
            WHERE id = :id
            AND status = 'confirmed'
            AND check_in_date <= CURDATE()
            FOR UPDATE
        ");
        $stmt->execute(['id' => $bookingId]);
        $booking = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$booking) {
            $pdo->rollBack();
            die('A foglalás nem található, vagy még nem érkezhetett meg.');
        }

        $stmt = $pdo->prepare("
            UPDATE bookings
            SET status = 'checked_in'
            WHERE id = :id
        ");
        $stmt->execute(['id' => $bookingId]);

        $stmt = $pdo->prepare("
            UPDATE rooms
            SET status = 'occupied'
            WHERE id = :room_id
        ");
        $stmt->execute(['room_id' => $booking['room_id']]);

        $pdo->commit();

        header('Location: reception.php');
        exit;
    } catch (Throwable $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        throw $e;
    }
}
// Handle check-out requests
if ($_SERVER['REQUEST_METHOD'] === 'POST'
    && ($_POST['action'] ?? '') === 'check_out') {

    $bookingId = filter_input(INPUT_POST, 'booking_id', FILTER_VALIDATE_INT);

    if (!$bookingId) {
        die('Érvénytelen foglalásazonosító.');
    }

    $pdo->beginTransaction();

    try {
        $stmt = $pdo->prepare("
            SELECT room_id
            FROM bookings
            WHERE id = :id
            AND status = 'checked_in'
            AND check_out_date <= CURDATE()
            FOR UPDATE
        ");
        $stmt->execute(['id' => $bookingId]);
        $booking = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$booking) {
            $pdo->rollBack();
            die('A foglalás nem található, vagy a vendég nincs bejelentkezve.');
        }

        $stmt = $pdo->prepare("
            UPDATE bookings
            SET status = 'checked_out'
            WHERE id = :id
        ");
        $stmt->execute(['id' => $bookingId]);

        $stmt = $pdo->prepare("
            UPDATE rooms
            SET status = 'cleaning'
            WHERE id = :room_id
        ");
        $stmt->execute(['room_id' => $booking['room_id']]);

        $pdo->commit();

        header('Location: reception.php');
        exit;
    } catch (Throwable $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        throw $e;
    }
}
// Fetch today's arrivals, departures, and available rooms counts
 $stmt = $pdo->query("SELECT COUNT(*) AS today_arrivals FROM bookings WHERE check_in_date = CURDATE() AND status = 'confirmed'");
 $today_arrivals = (int) $stmt->fetchColumn();
 $stmt = $pdo->query("SELECT COUNT(*) AS today_departures FROM bookings WHERE check_out_date = CURDATE() AND status = 'confirmed'");
 $today_departures = (int) $stmt->fetchColumn();

 $stmt = $pdo->query("SELECT COUNT(*) AS available_rooms FROM rooms WHERE status = 'free'");
 $available_rooms = (int) $stmt->fetchColumn();

 $messages = 0;
// Fetch today's arrivals details
$stmt = $pdo->prepare("
    SELECT bookings.id AS booking_id, users.name AS  guest_name, rooms.room_number, room_types.name AS room_type, bookings.check_in_date
    FROM bookings
    JOIN users ON users.id = bookings.user_id
    JOIN rooms ON rooms.id = bookings.room_id
    JOIN room_types ON room_types.id = rooms.room_type_id
    WHERE bookings.check_in_date >= :check_in_date
    AND bookings.status = 'confirmed'
    ORDER BY bookings.check_in_date ASC, rooms.room_number ASC
");
$stmt->execute(['check_in_date' => date('Y-m-d')]);
$arrivals = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch today's departures details
$stmt = $pdo->prepare("
    SELECT bookings.id AS booking_id, users.name AS guest_name, rooms.room_number, room_types.name AS room_type, bookings.check_out_date, bookings.status
    FROM bookings
    JOIN users ON users.id = bookings.user_id
    JOIN rooms ON rooms.id = bookings.room_id
    JOIN room_types ON room_types.id = rooms.room_type_id
    WHERE bookings.check_out_date = :check_out_date
    AND bookings.status IN ('confirmed', 'checked_in')
    ORDER BY rooms.room_number
");
$stmt->execute(['check_out_date' => date('Y-m-d')]);
$departures = $stmt->fetchAll(PDO::FETCH_ASSOC);

$stmt = $pdo->query("
    SELECT rooms.id, rooms.room_number, rooms.status
    FROM rooms
    WHERE NOT EXISTS (
        SELECT 1
        FROM bookings
        WHERE bookings.room_id = rooms.id
        AND bookings.status = 'checked_in'
    )
    ORDER BY rooms.room_number
");
$roomsForStatusChange = $stmt->fetchAll(PDO::FETCH_ASSOC);
$roomStatusLabels = [
    'free' => 'Szabad',
    'cleaning' => 'Takarítás',
    'maintenance' => 'Karbantartás',
    'occupied' => 'Foglalt',
];

} catch (PDOException $e) {
    die("Adatbázis hiba: " . $e->getMessage());
    }
?>


<!DOCTYPE html>
<html lang="hu">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Recepció</title>
    <link rel="stylesheet" href="../../assets/bootstrap-5.3.8-dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="../../css/main.css">
    <link rel="stylesheet" href="../../css/components.css">
    <link rel="stylesheet" href="../../css/variables.css">
</head>

<body>
    <header class="reception-header py-4">
        <div class="container reception-header-inner">
            <div class="reception-topline">
                <div> Recepció · <span id="current-day"></span>
                </div>
                <div class="reception-header-row">
                    <div class="reception-title-group">
                        <h3 class="mb-0"><span id="time-of-day">Jó reggelt </span></h3>
                    </div>
                    <div class="reception-shift">
                        <span><span id="shift-type">Nappali műszak</span> · <span
                                id="day-shift-time">8:00-16:00</span></span>
                    </div>
                </div>
            </div>
        </div>
        </div>
    </header>

    <main class="container my-4 reception-body">
        <!-- Info cards section -->
        <div class="row g-3">
            <div class="col-12 col-md-6 col-xl-4">
                <div class="info-card">
                    <p class="info-card-label">Mai érkezések</p>
                    <h2 id="today-arrivals">
                        <?php echo $today_arrivals; ?>
                    </h2>
                    <p class="info-card-description text-muted">A mai napra tervezett érkezések száma.</p>
                </div>
            </div>
            <div class="col-12 col-md-6 col-xl-4">
                <div class="info-card">
                    <p class="info-card-label">Mai távozások</p>
                    <h2 id="today-departures">
                        <?php echo $today_departures; ?>
                    </h2>
                    <p class="info-card-description text-muted">A mai napra tervezett távozások száma.</p>
                </div>
            </div>
            <div class="col-12 col-md-6 col-xl-4">
                <div class="info-card">
                    <p class="info-card-label">Szabad szobák</p>
                    <h2 id="available-rooms">
                        <?php echo $available_rooms; ?>
                    </h2>
                    <p class="info-card-description text-muted">A mai napra szabad szobák száma.</p>
                </div>
            </div>
        </div>
        <!-- Arrival panel section -->
        <div class="row g-3 ">
            <div class="col-12 col-lg-8">
                <div class="arrival-panel">
                    <div class="row">
                        <div class="col-6">
                            <h2 class="arrival-title">Érkező vendégek</h2>
                        </div>
                        <div class="col-6">
                            <input type="text" class="form-control search-input" id="search-input"
                                placeholder="Vendég keresése">
                        </div>
                    </div>
                    <div>
                        <?php foreach ($arrivals as $arrival): ?>
                        <div class="row g-3 arrival-card align-items-center">
                            <div class="col-2 col-md-1 arrival-image">
                                <svg class="user-icon" viewBox="0 0 200 200" aria-hidden="true">
                                    <circle cx="100" cy="100" r="88" fill="none" stroke="currentColor"
                                        stroke-width="8" />
                                    <circle cx="100" cy="72" r="24" fill="none" stroke="currentColor"
                                        stroke-width="10" />
                                    <path d="M56 152C61 128 76 112 100 112C124 112 139 128 144 152" fill="none"
                                        stroke="currentColor" stroke-width="10" stroke-linecap="round"
                                        stroke-linejoin="round" />
                                </svg>
                            </div>
                            <div class="col-7 col-md-8">
                                <h3>
                                    <?php echo htmlspecialchars($arrival['guest_name']); ?>
                                </h3>
                                <div class="row g-2 align-items-center">
                                    <div class="col-12 col-md-4">
                                        <p class="arrival-text">Szoba száma:
                                            <?php echo htmlspecialchars($arrival['room_number']); ?>
                                        </p>
                                    </div>
                                    <div class="col-12 col-md-4">
                                        <p class="arrival-text">Szoba típusa:
                                            <?php echo htmlspecialchars($arrival['room_type']); ?>
                                        </p>
                                    </div>
                                    <div class="col-12 col-md-4">
                                        <p class="arrival-text">Érkezés ideje:
                                            <?php echo htmlspecialchars($arrival['check_in_date']); ?>
                                        </p>
                                    </div>
                                </div>
                            </div>

                            <div class="col-3 col-md-3 arrival-action">
                                <form method="post">
                                    <input type="hidden" value="<?php echo (int) $arrival['booking_id']; ?>"
                                        name="booking_id">
                                    <button type="submit" name="action" value="check_in" class="btn arrival-button">
                                        Check-in
                                    </button>
                                </form>
                            </div>

                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
            <!-- Departure Panel -->
            <div class="col-12 col-lg-4">
                <div class="departure-panel">
                    <h2 class="departure-title">Távozó vendégek</h2>
                    <?php foreach ($departures as $departure): ?>
                    <div class="departure-card">
                        <div class="row">
                            <div class="col-6 col-md-6">
                                <h3 class="departure-room-number">
                                    <?php echo htmlspecialchars($departure['room_number']); ?>
                                </h3>
                            </div>
                            <div class="col-6 col-md-6">
                                <p class="departure-time">
                                    <?php echo htmlspecialchars($departure['check_out_date']); ?>
                                </p>
                            </div>
                        </div>
                        <p class="departure-guest-name">
                            <?php echo htmlspecialchars($departure['guest_name']); ?>
                        </p>
                        <?php if ($departure['status'] === 'checked_in'): ?>
                        <form method="post">
                            <input type="hidden" name="booking_id"
                                value="<?php echo (int) $departure['booking_id']; ?>">
                            <button type="submit" name="action" value="check_out" class="btn departure-button">
                                Kijelentkezés
                            </button>
                        </form>
                        <?php else: ?>
                        <p>Még nincs bejelentkezve</p>
                        <?php endif; ?>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
        <div class="operation-panel">
            <div class="operation-header">
                <p class="operation-subtitle">Gyorsműveletek</p>
                <h2 class="operation-title">Reception eszközök</h2>
            </div>
            <div class="row g-3">
                <div class="col-12 col-md-4">
                    <div class="operation-card">
                        <button type="button" class="btn operation-button" id="open-booking-modal-button">
                            <span class="operation-button-title">Új foglalás</span><br>
                            <span class="operation-button-description">Regisztrált felhasználó részére</span>
                        </button>
                    </div>
                </div>
                <div class="col-12 col-md-4">
                    <div class="operation-card">
                        <button type="button" class="btn operation-button" id="open-search-guest-modal-button">
                            <span class="operation-button-title">Vendég keresése</span><br>
                            <span class="operation-button-description">Foglalás vagy profil alapján</span>
                        </button>
                    </div>
                </div>
                <div class="col-12 col-md-4">
                    <div class="operation-card">
                        <button type="button" class="btn operation-button" id="open-modify-room-status-modal-button">
                            <span class="operation-button-title">Szobastátusz módosítása</span><br>
                            <span class="operation-button-description">Takarítás vagy karbantartás</span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
        <!-- Booking Modal -->
        <dialog id="booking-modal" class="reception-modal" aria-labelledby="booking-modal-title">
            <h2 class="reception-modal-title" id="booking-modal-title">Új foglalás</h2>
            <form method="post" id="new-booking-form" class="reception-modal-form">
                <div class="reception-modal-field">
                    <label for="guest-name">Vendég neve:</label>
                    <input type="text" id="guest-name" name="guest_name" required>
                </div>
                <div class="reception-modal-field">
                    <label for="guest-email">Vendég e-mail-címe:</label>
                    <input type="email" id="guest-email" name="guest_email" required>
                </div>
                <div class="reception-modal-field">
                    <label for="guest-count">Vendégek száma:</label>
                    <input type="number" id="guest-count" name="guest_count" min="1" value="1" required>
                </div>
                <div class="reception-modal-field">
                    <label for="room-number">Szobaszám:</label>
                    <input type="text" id="room-number" name="room_number" required>
                </div>
                <div class="reception-modal-field">
                    <label for="check-in-date">Bejelentkezés dátuma:</label>
                    <input type="date" id="check-in-date" name="check_in_date" required>
                </div>
                <div class="reception-modal-field">
                    <label for="check-out-date">Kijelentkezés dátuma:</label>
                    <input type="date" id="check-out-date" name="check_out_date" required>
                </div>
                <div class="reception-modal-actions">
                    <button type="submit" name="action" value="new_booking" class="btn reception-modal-submit">Foglalás
                        létrehozása</button>
                    <button type="button" class="btn reception-modal-cancel" id="close-booking-modal">Mégse</button>
                </div>
            </form>
        </dialog>
        <dialog id="search-guest-modal" class="reception-modal" aria-labelledby="search-guest-modal-title">
            <h2 class="reception-modal-title" id="search-guest-modal-title">Vendég keresése</h2>
            <form method="post" id="search-guest-form" class="reception-modal-form">
                <div class="reception-modal-field">
                    <label for="search-guest-name">Vendég neve vagy e-mail-címe:</label>
                    <input type="text" id="search-guest-name" name="search_guest_name"
                        value="<?= htmlspecialchars($guestSearchTerm, ENT_QUOTES, 'UTF-8') ?>" required>
                </div>
                <?php if ($guestSearchSubmitted): ?>
                    <section class="guest-search-results">
                        <h3>Találatok</h3>

                        <?php if (empty($guestResults)): ?>
                        <p>Nem találtam ilyen vendéget.</p>
                <?php else: ?>
                    <?php foreach ($guestResults as $guest): ?>
                        <article class="guest-result">
                            <h4><?= htmlspecialchars($guest['name']) ?></h4>
                            <p><?= htmlspecialchars($guest['email']) ?></p>
                            <p><?= htmlspecialchars($guest['phone_number'] ?? '') ?></p>
                        </article>
                    <?php endforeach; ?>
                <?php endif; ?>
                </section>
                <?php endif; ?>
                <div class="reception-modal-actions">
                    <button type="submit" name="action" value="search_guest"
                        class="btn reception-modal-submit">Keresés</button>
                    <button type="button" class="btn reception-modal-cancel"
                        id="close-search-guest-modal">Mégse</button>
                </div>
            </form>
        </dialog>
        <dialog id="modify-room-status-modal" class="reception-modal" aria-labelledby="modify-room-status-modal-title">
            <h2 class="reception-modal-title" id="modify-room-status-modal-title">Szobastátusz módosítása</h2>
            <?php if ($roomStatusMessage !== ''): ?>
            <div class="alert alert-success" role="status">
                <?= htmlspecialchars($roomStatusMessage, ENT_QUOTES, 'UTF-8') ?>
            </div>
            <?php endif; ?>
            <form method="post" id="modify-room-status-form" class="reception-modal-form">
                <div class="reception-modal-field">
                    <label for="room-id-status">Szoba:</label>
                    <select id="room-id-status" name="room_id" required>
                        <option value="" selected disabled>Válassz szobát</option>
                        <?php foreach ($roomsForStatusChange as $room): ?>
                        <option value="<?= (int) $room['id'] ?>">
                            <?= htmlspecialchars($room['room_number'], ENT_QUOTES, 'UTF-8') ?>
                            (<?= htmlspecialchars($roomStatusLabels[$room['status']] ?? $room['status'], ENT_QUOTES, 'UTF-8') ?>)
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="reception-modal-field">
                    <label for="room-status">Státusz:</label>
                    <select id="room-status" name="room_status" required>
                        <option value="free">Szabad</option>
                        <option value="occupied">Foglalt</option>
                        <option value="cleaning">Takarítás</option>
                        <option value="maintenance">Karbantartás</option>
                    </select>
                </div>
                <div class="reception-modal-actions">
                    <button type="submit" name="action" value="modify_room_status"
                        class="btn reception-modal-submit">Módosítás</button>
                    <button type="button" class="btn reception-modal-cancel"
                        id="close-modify-room-status-modal">Bezárás</button>
                </div>
            </form>
        </dialog>
    </main>

    <script src="../../assets/bootstrap-5.3.8-dist/js/bootstrap.bundle.min.js"></script>
    <script src="./staff.js"></script>
    <?php if ($guestSearchSubmitted): ?>
    <script>
        document.querySelector("#search-guest-modal").showModal();
    </script>
    <?php endif; ?>
    <?php if ($roomStatusMessage !== ''): ?>
    <script>
        document.querySelector("#modify-room-status-modal").showModal();
    </script>
    <?php endif; ?>
</body>

</html>