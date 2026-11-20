<?php
session_start();
require_once __DIR__ . '/../src/config/pdo.php';
require_once __DIR__ . '/../src/services/receptionservice.php';

$rooms = [];
$message = '';
$selectedGuestCount = is_string($_GET['guests'] ?? null) ? $_GET['guests'] : '2';

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
    <title>Foglalás</title>
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
                <input type="date" id="check_in_date" name="checkin" class="form-control booking-check-in-input" value="<?php echo htmlspecialchars(is_string($_GET['checkin'] ?? null) ? $_GET['checkin'] : '', ENT_QUOTES, 'UTF-8'); ?>">
                </div>
                <div class="col-12 col-md-3 booking-check-out" onclick="this.querySelector('input').focus()">
                    <label for="check_out_date" class="booking-check-out-label">Távozás</label>
                    <input type="date" id="check_out_date" name="checkout" class="form-control booking-check-out-input" value="<?php echo htmlspecialchars(is_string($_GET['checkout'] ?? null) ? $_GET['checkout'] : '', ENT_QUOTES, 'UTF-8'); ?>">
                </div>
                <div class="col-12 col-md-3 booking-select" onclick="this.querySelector('select').focus()">
                    <label for="guests" class="booking-guests-label">Vendégek száma</label>
                    <select name="guests" id="guests" class="form-control booking-guests-select">
                        <option value="1" <?php echo $selectedGuestCount === '1' ? 'selected' : ''; ?>>1</option>
                        <option value="2" <?php echo $selectedGuestCount === '2' ? 'selected' : ''; ?>>2</option>
                        <option value="3" <?php echo $selectedGuestCount === '3' ? 'selected' : ''; ?>>3</option>
                        <option value="4" <?php echo $selectedGuestCount === '4' ? 'selected' : ''; ?>>4</option>
                        <option value="5" <?php echo $selectedGuestCount === '5' ? 'selected' : ''; ?>>5</option>
                        <option value="6" <?php echo $selectedGuestCount === '6' ? 'selected' : ''; ?>>6</option>
                    </select>
                </div>
                <div class="col-12 col-md-3 booking-submit">
                    <button type="submit" class="btn booking-btn">Foglalás</button>
                </div>
            </form>
        </div>
        <?php if ($message !== ''): ?>
            <p><?php echo htmlspecialchars($message, ENT_QUOTES, 'UTF-8'); ?></p>
        <?php endif; ?>
<div>
<div class="row">
    <?php foreach ($rooms as $room): ?>
    <div class="col-12 col-md-4">
        <div class="card">
            <div class="card-body">
                <img src="/hotel-booking-system/public/assets/images/room1.jpg" class="card-img-top" alt="Szoba 1">
                <h5 class="card-title">Szoba <?php echo htmlspecialchars((string) $room['room_number'], ENT_QUOTES, 'UTF-8'); ?></h5>
                <p class="card-text"><?php echo htmlspecialchars($room['room_type'], ENT_QUOTES, 'UTF-8'); ?></p>
            </div>
        </div>
    </div>
    <?php endforeach; ?>
   </div>

</div>
    </main>
    <?php require __DIR__ . '/../src/includes/footer.php'; ?>
</body>

<script src="/hotel-booking-system/public/assets/bootstrap-5.3.8-dist/js/bootstrap.bundle.min.js"></script>
<script src="/hotel-booking-system/public/js/index.js"></script>

</html>