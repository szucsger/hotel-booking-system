<?php
session_start();
// Ellenőrzi a felhasználó hitelesítését a munkamenet alapján.
require_once __DIR__ . '/../../../src/services/authservice.php';

// Ellenőrzi, hogy a felhasználó rendelkezik-e a megfelelő szerepkörrel az oldal megtekintéséhez.
$user = AuthService::getAuthenticatedUser();

if (
    $user === null
    || !AuthService::hasRole(['admin', 'receptionist'])
) {
    http_response_code(403);
    exit('Nincs jogosultságod az oldal megtekintéséhez.');
}

// A szerveroldali API-kéréseket csak az oldal munkamenetéhez tartozó tokennel fogadjuk el.
if (!isset($_SESSION['csrf_token']) || !is_string($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

require_once __DIR__ . '/../../../src/config/pdo.php';
require_once __DIR__ . '/../../../src/services/receptionservice.php';

// Az adatbázis-lekérdezéseket a szolgáltatás végzi; ez a fájl az eredményt a nézetnek készíti elő.
try {
    $dashboard = ReceptionService::getDashboard(getDatabaseConnection());
} catch (PDOException $e) {
    error_log($e->getMessage());
    http_response_code(500);
    exit('Az oldal adatai jelenleg nem tölthetők be.');
}

$today_arrivals = $dashboard['today_arrivals'];
$today_departures = $dashboard['today_departures'];
$available_rooms = $dashboard['available_rooms'];
$arrivals = $dashboard['arrivals'];
$departures = $dashboard['departures'];
$roomsForStatusChange = $dashboard['rooms_for_status_change'];
$roomStatusLabels = [
    'free' => 'Szabad',
    'cleaning' => 'Takarítás',
    'maintenance' => 'Karbantartás',
    'occupied' => 'Foglalt',
];
$roomStatusMessage = $_SESSION['room_status_message'] ?? '';
unset($_SESSION['room_status_message']);
$apiUrl = rtrim(dirname(dirname(dirname($_SERVER['SCRIPT_NAME']))), '/') . '/api/router.php';
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
        <div class="reception-header-inner">
            <div class="container">
            <div class="reception-topline">
                <div class="reception-topline-row">
                    <div> Recepció · <span id="current-day"></span>
                    </div>
                    <form action="../../pages/auth/logout.php" method="post">
                        <button class="btn button-logout" id="logout-button" type="submit">Kijelentkezés</button>
                    </form>
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
        </div>
    </header>

    <main class="container my-4 reception-body">
        <!-- A napi érkezések, távozások és szabad szobák összesítése. -->
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
        <!-- Az érkezők listája és a hozzá tartozó check-in művelet. -->
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
                                <form method="post"
                                    action="<?= htmlspecialchars($apiUrl, ENT_QUOTES, 'UTF-8') ?>"
                                    data-reception-action>
                                    <input type="hidden" name="csrf_token"
                                        value="<?= htmlspecialchars($_SESSION['csrf_token'], ENT_QUOTES, 'UTF-8') ?>">
                                    <input type="hidden" value="<?php echo (int) $arrival['booking_id']; ?>"
                                        name="booking_id">
                                    <input type="hidden" name="action" value="reception.check_in">
                                    <button type="submit" name="action" value="reception.check_in" class="btn arrival-button">
                                        Check-in
                                    </button>
                                </form>
                            </div>

                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
            <!-- A mai távozók listája és a kijelentkezés művelete. -->
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
                        <form method="post"
                            action="<?= htmlspecialchars($apiUrl, ENT_QUOTES, 'UTF-8') ?>"
                            data-reception-action>
                            <input type="hidden" name="csrf_token"
                                value="<?= htmlspecialchars($_SESSION['csrf_token'], ENT_QUOTES, 'UTF-8') ?>">
                            <input type="hidden" name="booking_id"
                                value="<?php echo (int) $departure['booking_id']; ?>">
                            <input type="hidden" name="action" value="reception.check_out">
                            <button type="submit" name="action" value="reception.check_out" class="btn departure-button">
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
        <!-- Az itt található gombok nyitják meg a recepciós műveletek párbeszédablakait. -->
        <div class="operation-panel">
            <div class="operation-header">
                <p class="operation-subtitle">Gyorsműveletek</p>
                <h2 class="operation-title">Recepciós eszközök</h2>
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
        <!-- Az új foglalás mezőit és az időpont alapján lekért szobákat tartalmazó ablak. -->
        <dialog id="booking-modal" class="reception-modal" aria-labelledby="booking-modal-title">
            <h2 class="reception-modal-title" id="booking-modal-title">Új foglalás</h2>
            <div id="new-booking-message" class="alert" role="status" aria-live="polite" hidden></div>
            <form method="post" action="<?= htmlspecialchars($apiUrl, ENT_QUOTES, 'UTF-8') ?>"
                id="new-booking-form" class="reception-modal-form">
                <input type="hidden" name="csrf_token"
                    value="<?= htmlspecialchars($_SESSION['csrf_token'], ENT_QUOTES, 'UTF-8') ?>">
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
                    <label for="check-in-date">Bejelentkezés dátuma:</label>
                    <input type="date" id="check-in-date" name="check_in_date" min="<?= date('Y-m-d') ?>" required>
                </div>
                <div class="reception-modal-field">
                    <label for="check-out-date">Kijelentkezés dátuma:</label>
                    <input type="date" id="check-out-date" name="check_out_date"
                        min="<?= date('Y-m-d', strtotime('+1 day')) ?>" required>
                </div>
                <div class="reception-modal-field">
                    <label for="room-id">Szabad szoba:</label>
                    <select id="room-id" name="room_id" required>
                        <option value="" selected>Előbb válaszd ki a dátumokat</option>
                    </select>
                    <small id="available-room-message" class="text-muted" role="status" aria-live="polite"></small>
                </div>
                <div class="reception-modal-actions">
                    <button type="submit" name="action" value="reception.new_booking" class="btn reception-modal-submit" id="new-booking-submit">Foglalás
                        létrehozása</button>
                    <button type="button" class="btn reception-modal-cancel" id="close-booking-modal">Mégse</button>
                </div>
            </form>
        </dialog>
        <!-- Vendégkeresés és a szervertől érkező találatok megjelenítése. -->
        <dialog id="search-guest-modal" class="reception-modal" aria-labelledby="search-guest-modal-title">
            <h2 class="reception-modal-title" id="search-guest-modal-title">Vendég keresése</h2>
            <form method="post" action="<?= htmlspecialchars($apiUrl, ENT_QUOTES, 'UTF-8') ?>"
                id="search-guest-form"
                class="reception-modal-form">
                <input type="hidden" name="csrf_token"
                    value="<?= htmlspecialchars($_SESSION['csrf_token'], ENT_QUOTES, 'UTF-8') ?>">
                <div class="reception-modal-field">
                    <label for="search-guest-name">Vendég neve vagy e-mail-címe:</label>
                    <input type="text" id="search-guest-name" name="search_guest_name" required>
                </div>
                <section id="guest-search-results" class="guest-search-results" hidden>
                    <h3>Találatok</h3>
                    <div id="guest-search-result-list"></div>
                </section>
                <p id="guest-search-message" role="status" aria-live="polite"></p>
                <div class="reception-modal-actions">
                    <button type="submit" name="action" value="reception.search_guest"
                        class="btn reception-modal-submit">Keresés</button>
                    <button type="button" class="btn reception-modal-cancel"
                        id="close-search-guest-modal">Mégse</button>
                </div>
            </form>
        </dialog>
        <!-- A szoba státusza csak a szerveroldali ellenőrzés után módosul. -->
        <dialog id="modify-room-status-modal" class="reception-modal" aria-labelledby="modify-room-status-modal-title">
            <h2 class="reception-modal-title" id="modify-room-status-modal-title">Szobastátusz módosítása</h2>
            <div id="room-status-message" class="alert" role="status" aria-live="polite" hidden></div>
            <?php if ($roomStatusMessage !== ''): ?>
            <div class="alert alert-success" role="status">
                <?= htmlspecialchars($roomStatusMessage, ENT_QUOTES, 'UTF-8') ?>
            </div>
            <?php endif; ?>
            <form method="post" action="<?= htmlspecialchars($apiUrl, ENT_QUOTES, 'UTF-8') ?>"
                id="modify-room-status-form"
                class="reception-modal-form" data-reception-action>
                <input type="hidden" name="csrf_token"
                    value="<?= htmlspecialchars($_SESSION['csrf_token'], ENT_QUOTES, 'UTF-8') ?>">
                <input type="hidden" name="action" value="reception.modify_room_status">
                <div class="reception-modal-field">
                    <label for="room-id-status">Szoba:</label>
                    <select id="room-id-status" name="room_id" required>
                        <option value="" selected disabled>Válassz szobát</option>
                        <?php foreach ($roomsForStatusChange as $room): ?>
                        <option value="<?= (int) $room['id'] ?>">
                            <?= htmlspecialchars($room['room_number'], ENT_QUOTES, 'UTF-8') ?>
                            (
                            <?= htmlspecialchars($roomStatusLabels[$room['status']] ?? $room['status'], ENT_QUOTES, 'UTF-8') ?>)
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
                    <button type="submit" name="action" value="reception.modify_room_status"
                        class="btn reception-modal-submit" id="modify-room-status-submit">Módosítás</button>
                    <button type="button" class="btn reception-modal-cancel"
                        id="close-modify-room-status-modal">Bezárás</button>
                </div>
            </form>
        </dialog>
    </main>
    <?php require __DIR__ . '/../../../src/includes/footer.php'; ?>
    <script src="../../assets/bootstrap-5.3.8-dist/js/bootstrap.bundle.min.js"></script>
    <script type="module" src="../../js/staff/reception.js"></script>
    <?php if ($roomStatusMessage !== ''): ?>
    <script>
        document.querySelector("#modify-room-status-modal").showModal();
    </script>
    <?php endif; ?>
</body>

</html>