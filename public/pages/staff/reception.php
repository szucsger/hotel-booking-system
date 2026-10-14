<?php
include_once('../../../src/config/db.php');

try {
 $pdo = new PDO("mysql:host=$host;dbname=$db", $user, $pass);
 $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

 $stmt = $pdo->query("SELECT COUNT(*) AS today_arrivals FROM bookings WHERE check_in_date = CURDATE() AND status = 'confirmed'");
 $today_arrivals = (int) $stmt->fetchColumn();
 $stmt = $pdo->query("SELECT COUNT(*) AS today_departures FROM bookings WHERE check_out_date = CURDATE() AND status = 'confirmed'");
 $today_departures = (int) $stmt->fetchColumn();

 $stmt = $pdo->query("SELECT COUNT(*) AS available_rooms FROM rooms WHERE status = 'available'");
 $available_rooms = (int) $stmt->fetchColumn();

 $messages = 0;

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
        <!-- Érkező vendégek szekció kezdete -->
        <div class="row g-3 ">
            <div class="col-12 col-lg-8">
                <div class="arrival-panel">
                    <div class="row">
                        <div class="col-6">
                            <h2 class="arrival-title">Érkező vendégek</h2>
                        </div>
                        <div class="col-6">
                            <input type="text" class="form-control search-input" placeholder="Vendég keresése">
                        </div>
                    </div>
                    <div>
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
                                <h3>Vendég neve</h3>
                                <div class="row g-2 align-items-center">
                                    <div class="col-12 col-md-4">
                                        <p class="arrival-text">Szoba száma: 101</p>
                                    </div>
                                    <div class="col-12 col-md-4">
                                        <p class="arrival-text">Szoba típusa: Standard</p>
                                    </div>
                                    <div class="col-12 col-md-4">
                                        <p class="arrival-text">Érkezés ideje: 14:00</p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-3 col-md-3 arrival-action">
                                <button class="btn arrival-button">Check-in</button>
                            </div>
                        </div>

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
                                <h3>Vendég neve</h3>
                                <div class="row g-2 align-items-center">
                                    <div class="col-12 col-md-4">
                                        <p class="arrival-text">Szoba száma: 102</p>
                                    </div>
                                    <div class="col-12 col-md-4">
                                        <p class="arrival-text">Szoba típusa: Standard</p>
                                    </div>
                                    <div class="col-12 col-md-4">
                                        <p class="arrival-text">Érkezés ideje: 14:30</p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-3 col-md-3 arrival-action">
                                <button class="btn arrival-button">Check-in</button>
                            </div>
                        </div>

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
                                <h3>Vendég neve</h3>
                                <div class="row g-2 align-items-center">
                                    <div class="col-12 col-md-4">
                                        <p class="arrival-text">Szoba száma: 103</p>
                                    </div>
                                    <div class="col-12 col-md-4">
                                        <p class="arrival-text">Szoba típusa: Standard</p>
                                    </div>
                                    <div class="col-12 col-md-4">
                                        <p class="arrival-text">Érkezés ideje: 15:00</p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-3 col-md-3 arrival-action">
                                <button class="btn arrival-button">Check-in</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-12 col-lg-4 d-flex flex-column">
                <div class="departure-panel flex-fill">
                    <h2 class="departure-title">Távozó vendégek</h2>
                    <div class="departure-card">
                        <div class="row">
                            <div class="col-6 col-md-6">
                                <h3 class="departure-room-number">103</h3>
                            </div>
                            <div class="col-6 col-md-6">
                                <p class="departure-time">11:00</p>
                            </div>
                        </div>
                        <p class="departure-guest-name">Kovács János</p>
                        <button class="btn departure-button">Kijelentkezés </button>
                    </div>
                    <div class="departure-card">
                        <div class="row">
                            <div class="col-6 col-md-6">
                                <h3 class="departure-room-number">103</h3>
                            </div>
                            <div class="col-6 col-md-6">
                                <p class="departure-time">11:00</p>
                            </div>
                        </div>
                        <p class="departure-guest-name">Kovács János</p>
                        <button class="btn departure-button">Kijelentkezés </button>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <script src="../../assets/bootstrap-5.3.8-dist/js/bootstrap.bundle.min.js"></script>
    <script src="./staff.js"></script>
</body>

</html>