
<?php
session_start();

require_once __DIR__ . '/../../../src/config/pdo.php';
require_once __DIR__ . '/../../../src/services/authservice.php';

// Bejelentkezett felhasználó ellenőrzése
$user = AuthService::getAuthenticatedUser();

if ($user === null || $user['role'] !== 'user') {
    header('Location: /hotel-booking-system-main/public/pages/auth/login.php');
    exit;
}

// Adatbázis-kapcsolat
$pdo = getDatabaseConnection();

// A bejelentkezett vendég azonosítója
$userId = (int) $user['id'];

// A vendég összes foglalásának lekérdezése
$sql = "
    SELECT
        b.id,
        b.check_in_date,
        b.check_out_date,
        b.total_price,
        b.status,
        r.room_number
    FROM bookings b
    INNER JOIN rooms r ON b.room_id = r.id
    WHERE b.user_id = :user_id
    ORDER BY b.check_in_date DESC
";

$stmt = $pdo->prepare($sql);

$stmt->execute([
    'user_id' => $userId
]);

$bookings = $stmt->fetchAll(PDO::FETCH_ASSOC);

$statusLabels = [
    'pending' => 'Függőben',
    'confirmed' => 'Visszaigazolva',
    'checked_in' => 'Bejelentkezett',
    'checked_out' => 'Kijelentkezett',
    'cancelled' => 'Lemondva'
];
?>



<!DOCTYPE html>
<html lang="hu">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Foglalásaim</title>

    <link rel="stylesheet" href="../../assets/bootstrap-5.3.8-dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="../../css/main.css">
    <link rel="stylesheet" href="../../css/components.css">
    <link rel="stylesheet" href="../../css/variables.css">
</head>

<body>

<header class="reception-header py-4">
    <div class="container reception-header-inner">
        <h3 class="mb-0">Foglalásaim</h3>
    </div>
</header>

<main class="container my-4 reception-body">

    <div class="info-card mb-4">
        <h4 class="mb-3">
            <i class="bi bi-calendar-check me-2"></i>
            Aktuális és közelgő foglalások
        </h4>

        <?php
        $currentBookings = array_filter($bookings, function ($booking) {
            return $booking['check_out_date'] >= date('Y-m-d')
                && in_array($booking['status'], ['pending', 'confirmed'], true);
        });
        ?>

        <?php if (!empty($currentBookings)): ?>

            <?php foreach ($currentBookings as $booking): ?>

                <div class="border rounded p-3 mb-3">

                    <div class="row g-3">

                        <div class="col-md-3">
                            <strong>Szobaszám</strong>
                            <p><?= htmlspecialchars($booking['room_number']) ?></p>
                        </div>

                        <div class="col-md-3">
                            <strong>Érkezés</strong>
                            <p><?= htmlspecialchars($booking['check_in_date']) ?></p>
                        </div>

                        <div class="col-md-3">
                            <strong>Távozás</strong>
                            <p><?= htmlspecialchars($booking['check_out_date']) ?></p>
                        </div>

                        <div class="col-md-3">
                            <strong>Végösszeg</strong>
                            <p><?= number_format($booking['total_price'], 0, ',', ' ') ?> Ft</p>
                        </div>

                    </div>

                    <strong>Állapot:</strong>
                        <?= htmlspecialchars(
                            $statusLabels[$booking['status']] ?? $booking['status'],
                            ENT_QUOTES,
                            'UTF-8'
                        ) ?>

                </div>

            <?php endforeach; ?>

        <?php else: ?>

            <p class="info-card-description">
                Jelenleg nincs aktuális vagy közelgő foglalásod.
            </p>

        <?php endif; ?>
    </div>


    <div class="info-card">

        <h4 class="mb-3">
            <i class="bi bi-clock-history me-2"></i>
            Korábbi foglalások
        </h4>

        <?php
        $pastBookings = array_filter($bookings, function ($booking) {
            return $booking['check_out_date'] < date('Y-m-d');
        });
        ?>

        <?php if (!empty($pastBookings)): ?>

            <?php foreach ($pastBookings as $booking): ?>

                <div class="border rounded p-3 mb-3">

                    <div class="row g-3">

                        <div class="col-md-3">
                            <strong>Szobaszám</strong>
                            <p><?= htmlspecialchars($booking['room_number']) ?></p>
                        </div>

                        <div class="col-md-3">
                            <strong>Érkezés</strong>
                            <p><?= htmlspecialchars($booking['check_in_date']) ?></p>
                        </div>

                        <div class="col-md-3">
                            <strong>Távozás</strong>
                            <p><?= htmlspecialchars($booking['check_out_date']) ?></p>
                        </div>

                        <div class="col-md-3">
                            <strong>Végösszeg</strong>
                            <p><?= number_format($booking['total_price'], 0, ',', ' ') ?> Ft</p>
                        </div>

                    </div>

                    <strong>Állapot:</strong>
                    <?= htmlspecialchars($booking['status']) ?>

                </div>

            <?php endforeach; ?>

        <?php else: ?>

            <p class="info-card-description">
                Még nincsenek korábbi foglalásaid.
            </p>

        <?php endif; ?>

    </div>

    <div class="mt-4">
        <a href="guest.php" class="btn btn-secondary">
            Vissza a főoldalra
        </a>
    </div>

</main>

</body>
</html>
