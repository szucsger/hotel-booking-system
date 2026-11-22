<?php
session_start();
if (!isset($_SESSION['csrf_token']) || !is_string($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
require_once __DIR__ . '/../src/config/pdo.php';
require_once __DIR__ . '/../src/services/receptionservice.php';

$rooms = [];
$message = '';
$selectedGuestCount = is_string($_GET['guests'] ?? null) ? $_GET['guests'] : '2';
$selectedCheckIn = is_string($_GET['checkin'] ?? null) ? $_GET['checkin'] : '';
$selectedCheckOut = is_string($_GET['checkout'] ?? null) ? $_GET['checkout'] : '';

if (isset($_GET['checkin'], $_GET['checkout'], $_GET['guests'])) {
    try {
        $result = ReceptionService::getAvailableRooms(getDatabaseConnection(), [
            'check_in_date' => $_GET['checkin'],
            'check_out_date' => $_GET['checkout'],
            'guest_count' => $_GET['guests'],
        ]);
        $rooms = $result['rooms'];
        $message = $result['message'];
    } catch (DomainException $e) {
        $message = $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="hu">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hotel CityStar</title>
    <link rel="stylesheet" href="/hotel-booking-system/public/assets/bootstrap-5.3.8-dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="/hotel-booking-system/public/css/main.css">
    <link rel="stylesheet" href="/hotel-booking-system/public/css/components.css">
    <link rel="stylesheet" href="/hotel-booking-system/public/css/variables.css">
</head>
<?php require __DIR__ . '/../src/includes/header.php'; ?>

<body>
    <div class="booking-header-container">
        <header class="container booking-header">
            <h1 class="booking-title">A nyugalomnak címe van</h1>
            <p class="booking-subtitle">Foglalja le szállását nálunk, és élvezze a nyugalmat és kényelmet.</p>
        </header>
    </div>
    <main class="container">
        <div>
            <form action="/hotel-booking-system/public/index.php" method="GET" class="booking-form row">
                <div class="col-12 col-md-3 booking-check-in" onclick="this.querySelector('input').focus()">
                    <label for="check_in_date" class="booking-check-in-label">Érkezés</label>
                    <input type="date" id="check_in_date" name="checkin" class="form-control booking-check-in-input"
                        value="<?php echo htmlspecialchars(is_string($_GET['checkin'] ?? null) ? $_GET['checkin'] : '', ENT_QUOTES, 'UTF-8'); ?>">
                </div>
                <div class="col-12 col-md-3 booking-check-out" onclick="this.querySelector('input').focus()">
                    <label for="check_out_date" class="booking-check-out-label">Távozás</label>
                    <input type="date" id="check_out_date" name="checkout" class="form-control booking-check-out-input"
                        value="<?php echo htmlspecialchars(is_string($_GET['checkout'] ?? null) ? $_GET['checkout'] : '', ENT_QUOTES, 'UTF-8'); ?>">
                </div>
                <div class="col-12 col-md-3 booking-select" onclick="this.querySelector('select').focus()">
                    <label for="guests" class="booking-guests-label">Vendégek száma</label>
                    <select name="guests" id="guests" class="form-control booking-guests-select">
                        <option value="1" <?php echo $selectedGuestCount==='1' ? 'selected' : '' ; ?>>1</option>
                        <option value="2" <?php echo $selectedGuestCount==='2' ? 'selected' : '' ; ?>>2</option>
                        <option value="3" <?php echo $selectedGuestCount==='3' ? 'selected' : '' ; ?>>3</option>
                        <option value="4" <?php echo $selectedGuestCount==='4' ? 'selected' : '' ; ?>>4</option>
                        <option value="5" <?php echo $selectedGuestCount==='5' ? 'selected' : '' ; ?>>5</option>
                        <option value="6" <?php echo $selectedGuestCount==='6' ? 'selected' : '' ; ?>>6</option>
                    </select>
                </div>
                <div class="col-12 col-md-3 booking-submit">
                    <button type="submit" class="btn booking-btn">Keresés</button>
                </div>
            </form>
        </div>
        <?php if ($message !== ''): ?>
        <p>
            <?php echo htmlspecialchars($message, ENT_QUOTES, 'UTF-8'); ?>
        </p>
        <?php endif; ?>
        <div class="room-results<?php echo $rooms !== [] ? ' has-results' : ''; ?>">
            <div class="row">
                <?php foreach ($rooms as $room): ?>
                <div class="col-12 col-md-4">
                    <div class="card room-card">
                        <button type="button" class="btn p-0 text-start w-100 room-details-button"
                            data-room-number="<?php echo htmlspecialchars((string) $room['room_number'], ENT_QUOTES, 'UTF-8'); ?>"
                            data-room-type="<?php echo htmlspecialchars($room['room_type'], ENT_QUOTES, 'UTF-8'); ?>"
                            data-description="<?php echo htmlspecialchars($room['description'] ?? '', ENT_QUOTES, 'UTF-8'); ?>"
                            data-capacity="<?php echo (int) $room['capacity']; ?>"
                            data-bed-type="<?php echo htmlspecialchars($room['bed_type'], ENT_QUOTES, 'UTF-8'); ?>"
                            data-price="<?php echo (int) $room['price_per_night']; ?>"
                            data-room-id="<?php echo (int) $room['id']; ?>"
                            data-check-in="<?php echo htmlspecialchars($selectedCheckIn, ENT_QUOTES, 'UTF-8'); ?>"
                            data-check-out="<?php echo htmlspecialchars($selectedCheckOut, ENT_QUOTES, 'UTF-8'); ?>"
                            data-guest-count="<?php echo htmlspecialchars($selectedGuestCount, ENT_QUOTES, 'UTF-8'); ?>">
                            <div class="card-body">
                                <!-- <img src="/hotel-booking-system/public/assets/images/room1.jpg" class="card-img-top"
                                    alt="Szoba 1"> -->
                                <h5 class="card-title">Szoba
                                    <?php echo htmlspecialchars((string) $room['room_number'], ENT_QUOTES, 'UTF-8'); ?>
                                </h5>
                                <p class="card-text">
                                    <?php echo htmlspecialchars($room['room_type'], ENT_QUOTES, 'UTF-8'); ?>
                                </p>
                            </div>
                        </button>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>

        </div>

        <div class="information-section">
            <div class="row g-3 container">
                <div class="col-12 col-md-4 ">
                    <div class="information-card-highlight">
                        <h2>Több, mint egy <span class="highlight">éjszaka</span></h2>
                        <p>Fedezze fel szállodánk kényelmét és szolgáltatásait, amelyek garantálják a tökéletes pihenést
                            minden
                            vendég számára.</p>
                        <a href="/hotel-booking-system/public/pages/contact.php" class="btn">Beszéljünk az
                            igényeiről</a>
                    </div>
                </div>
                <div class="col-12 col-md-8">
                    <div class="row information-cards">
                        <div class="col-12 col-md-6">
                            <div class="information-card">
                                <h4>0-24 órás recepció</h4>
                                <p>Bármikor számithat ránk.</p>
                            </div>
                        </div>
                        <div class="col-12 col-md-6">
                            <div class="information-card">
                                <h4>Ingyenes Wi-Fi</h4>
                                <p>Maradjon kapcsolatban bárhol a szállodában.</p>
                            </div>
                        </div>
                        <div class="col-12 col-md-6">
                            <div class="information-card">
                                <h4>Wellness és Spa</h4>
                                <p>Relaxáljon és töltődjön fel a wellness részlegünkön.</p>
                            </div>
                        </div>
                        <div class="col-12 col-md-6">
                            <div class="information-card">
                                <h4>Gourmet étterem</h4>
                                <p>Élvezze a helyi és nemzetközi ízeket a szálloda éttermében.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>


        <dialog id="room-details-dialog" class="reception-modal" aria-labelledby="room-details-title">
            <h2 class="reception-modal-title" id="room-details-title"></h2>
            <p id="room-details-description"></p>
            <p>Férőhely: <span id="room-details-capacity"></span></p>
            <p>Ágytípus: <span id="room-details-bed-type"></span></p>
            <p>Ár: <span id="room-details-price"></span> Ft / éjszaka</p>
            <form action="/hotel-booking-system/public/api/router.php" method="post" id="guest-booking-form">
                <input type="hidden" name="action" value="guest.create_booking">
                <input type="hidden" name="room_id" id="booking-room-id">
                <input type="hidden" name="check_in_date" id="booking-check-in">
                <input type="hidden" name="check_out_date" id="booking-check-out">
                <input type="hidden" name="guest_count" id="booking-guest-count">
                <input type="hidden" name="csrf_token"
                    value="<?php echo htmlspecialchars($_SESSION['csrf_token'], ENT_QUOTES, 'UTF-8'); ?>">
                <p id="booking-message" role="status" aria-live="polite"></p>
                <button type="submit" class="btn room-booking-btn">Foglalás leadása</button>
            </form>
            <button type="button" class="btn reception-modal-cancel room-booking-btn"
                id="close-room-details">Bezárás</button>
        </dialog>

    </main>
    <?php require __DIR__ . '/../src/includes/footer.php'; ?>
</body>

<script src="/hotel-booking-system/public/assets/bootstrap-5.3.8-dist/js/bootstrap.bundle.min.js"></script>
<script src="/hotel-booking-system/public/js/index.js"></script>

</html>