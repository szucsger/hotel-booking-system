<?php
include_once('../../../src/config/db.php');

try {

 $pdo = new PDO("mysql:host=$host;dbname=$db", $user, $pass);
 $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

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

 $stmt = $pdo->query("SELECT COUNT(*) AS available_rooms FROM rooms WHERE status = 'available'");
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
    ORDER BY rooms.room_number
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
                            <input type="text" class="form-control search-input" id="search-input" placeholder="Vendég keresése">
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
                                <h3><?php echo htmlspecialchars($arrival['guest_name']); ?></h3>
                                <div class="row g-2 align-items-center">
                                    <div class="col-12 col-md-4">
                                        <p class="arrival-text">Szoba száma: <?php echo htmlspecialchars($arrival['room_number']); ?></p>
                                    </div>
                                    <div class="col-12 col-md-4">
                                        <p class="arrival-text">Szoba típusa: <?php echo htmlspecialchars($arrival['room_type']); ?></p>
                                    </div>
                                    <div class="col-12 col-md-4">
                                        <p class="arrival-text">Érkezés ideje: <?php echo htmlspecialchars($arrival['check_in_date']); ?></p>
                                    </div>
                                </div>
                            </div>
                        
                            <div class="col-3 col-md-3 arrival-action">
                            <form method="post">
                                <input
                                    type="hidden"
                                    value="<?php echo (int) $arrival['booking_id']; ?>"
                                    name="booking_id"
                                    >
                                <button
                                    type="submit"
                                    name="action"
                                    value="check_in"
                                    class="btn arrival-button"
                                    >
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
                            <h3 class="departure-room-number"><?php echo htmlspecialchars($departure['room_number']); ?></h3>
                        </div>
                        <div class="col-6 col-md-6">
                            <p class="departure-time"><?php echo htmlspecialchars($departure['check_out_date']); ?></p>
                        </div>
                    </div>
                    <p class="departure-guest-name"><?php echo htmlspecialchars($departure['guest_name']); ?></p>
                    <?php if ($departure['status'] === 'checked_in'): ?>
                    <form method="post">
                        <input
                            type="hidden"
                            name="booking_id"
                            value="<?php echo (int) $departure['booking_id']; ?>"
                        >
                        <button
                        type="submit"
                        name="action"
                        value="check_out"
                        class="btn departure-button"
                        >
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
    </main>

    <script src="../../assets/bootstrap-5.3.8-dist/js/bootstrap.bundle.min.js"></script>
    <script src="./staff.js"></script>
</body>

</html>