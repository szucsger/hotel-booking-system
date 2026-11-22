<?php
session_start();

require_once __DIR__ . '/../../../src/config/pdo.php';
require_once __DIR__ . '/../../../src/services/authservice.php';

// Bejelentkezett felhasználó lekérése
$user = AuthService::getAuthenticatedUser();

// Ha nincs bejelentkezve, visszairányítjuk
if ($user === null || $user['role'] !== 'user') {
    header('Location: /hotel-booking-system/public/pages/auth/login.php');
    exit;
}

// Adatbázis-kapcsolat
$pdo = getDatabaseConnection();

// Vendég azonosítója
$userId = (int) $user['id'];

// Következő foglalás lekérdezése
$sql = "
    SELECT
        b.check_in_date,
        b.check_out_date,
        b.total_price,
        r.room_number
    FROM bookings b
    INNER JOIN rooms r ON b.room_id = r.id
    WHERE b.user_id = :user_id
      AND b.check_in_date >= CURDATE()
      AND b.status IN ('pending', 'confirmed')
    ORDER BY b.check_in_date ASC
    LIMIT 1
";

$stmt = $pdo->prepare($sql);

$stmt->execute([
    'user_id' => $userId
]);

$next_booking = $stmt->fetch(PDO::FETCH_ASSOC);


?>

<!DOCTYPE html>
<html lang="hu">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Vendég</title>
    <link rel="stylesheet" href="../../assets/bootstrap-5.3.8-dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="../../css/main.css">
    <link rel="stylesheet" href="../../css/components.css">
    <link rel="stylesheet" href="../../css/variables.css">
</head>

<body>
    <header class="reception-header py-4">
        <div class="container reception-header-inner">
            <div class="reception-topline">
                <div> Vendég <span id="current-day"></span>
                </div>
                <div class="reception-header-row">
                    <div class="reception-title-group">
                        <h3 class="mb-0">
                            <span id="time-of-day"></span>
                            <?= htmlspecialchars($user['name'], ENT_QUOTES, 'UTF-8'); ?>!
                        </h3>
                    </div>
                </div>
            </div>
        </div>
    </header>

<main class="container my-4 reception-body">

    <!-- Vendég gyorsmenü -->
<div class="row g-4">

    <!-- 1. Profilom -->
<div class="col-12 col-md-6">
    <a href="profile.php" class="info-card h-100 d-block text-decoration-none">
        <i class="bi bi-person-circle fs-1"></i>

        <p class="info-card-label">Profilom</p>

        <p class="info-card-description">
            Személyes adataim megtekintése és módosítása.
        </p>
    </a>
</div>

<!-- 2. Foglalásaim -->
<div class="col-12 col-md-6">
    <a href="bookings.php" class="info-card h-100 d-block text-decoration-none">
        <i class="bi bi-calendar-check fs-1"></i>

        <p class="info-card-label">Foglalásaim</p>

        <p class="info-card-description">
            Korábbi és aktuális foglalásaim megtekintése.
        </p>
    </a>
</div>

<!-- 3. Új foglalás -->
<div class="col-12 col-md-6">
        <a href="../../index.php"
        class="info-card h-100 d-block text-decoration-none">

        <i class="bi bi-plus-circle fs-1"></i>

        <p class="info-card-label">Új foglalás</p>

        <p class="info-card-description">
            Új szoba lefoglalása a szállodában.
        </p>

    </a>
</div>

<!-- 4. Kijelentkezés -->
<div class="col-12 col-md-6">
    <a href="logout.php" class="info-card h-100 d-block text-decoration-none">
        <i class="bi bi-box-arrow-right fs-1"></i>

        <p class="info-card-label">Kijelentkezés</p>

        <p class="info-card-description">
            Kilépés a vendégfiókból.
        </p>
    </a>
</div>

</div>
<!-- Következő foglalásom -->
<div class="row mt-4">
    <div class="col-12">

        <div class="info-card h-100">

            <p class="info-card-label">
                <i class="bi bi-calendar-event me-2"></i>
                Következő foglalásom
            </p>

            <?php if ($next_booking): ?>

                <div class="row mt-3">

                    <div class="col-md-3">
                        <h4>
                            <?php echo htmlspecialchars($next_booking['room_number']); ?>
                        </h4>
                        <p class="info-card-description">Szobaszám</p>
                    </div>

                    <div class="col-md-3">
                        <h4>
                            <?php echo htmlspecialchars($next_booking['check_in_date']); ?>
                        </h4>
                        <p class="info-card-description">Érkezés</p>
                    </div>

                    <div class="col-md-3">
                        <h4>
                            <?php echo htmlspecialchars($next_booking['check_out_date']); ?>
                        </h4>
                        <p class="info-card-description">Távozás</p>
                    </div>

                    <div class="col-md-3">
                        <h4>
                            <?php echo number_format($next_booking['total_price'], 0, ',', ' '); ?> Ft
                        </h4>
                        <p class="info-card-description">Végösszeg</p>
                    </div>

                </div>

            <?php else: ?>

                <p class="info-card-description mt-3">
                    Jelenleg nincs következő foglalásod.
                </p>

            <?php endif; ?>

        </div>

    </div>
</div>
<script>
    function updateGreeting() {
        const hour = new Date().getHours();
        let greeting;

        if (hour >= 5 && hour < 10) {
            greeting = "Jó reggelt";
        } else if (hour >= 10 && hour < 18) {
            greeting = "Jó napot";
        } else if (hour >= 18 && hour < 22) {
            greeting = "Jó estét";
        } else {
            greeting = "Jó éjszakát";
        }

        document.getElementById("time-of-day").textContent = greeting + " ";
    }

    updateGreeting();
</script>

</body>
</html>