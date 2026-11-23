
<?php

session_start();

require_once __DIR__ . '/../../../src/config/pdo.php';
require_once __DIR__ . '/../../../src/services/authservice.php';
require_once __DIR__ . '/../../../src/services/guestservice.php';

// 1. Bejelentkezett felhasználó ellenőrzése
$user = AuthService::getAuthenticatedUser();

if ($user === null || $user['role'] !== 'user') {
    header('Location: /hotel-booking-system/public/pages/auth/login.php');
    exit;
}

// 2. GuestService példányosítása
$guestService = new GuestService();

// 3. CSRF-token létrehozása
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$csrfToken = $_SESSION['csrf_token'];

// Üzenetek
$profileError = null;
$passwordError = null;
$passwordSuccess = null;

// 4. Űrlapok feldolgozása
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // CSRF-token ellenőrzése
    $submittedToken = $_POST['csrf_token'] ?? '';

    if (
        !is_string($submittedToken) ||
        !hash_equals($_SESSION['csrf_token'], $submittedToken)
    ) {
        http_response_code(403);
        die('Érvénytelen biztonsági token!');
    }

    // 5. Profiladatok mentése
    if (isset($_POST['save_profile'])) {

        $name = trim($_POST['name'] ?? '');
        $phoneNumber = trim($_POST['phone_number'] ?? '');
        $birthDate = trim($_POST['birth_date'] ?? '');
        $nationality = trim($_POST['nationality'] ?? '');
        $address = trim($_POST['address'] ?? '');

        // Szerveroldali adatellenőrzés
        if ($name === '') {
            $profileError = 'A név megadása kötelező.';
        } elseif (mb_strlen($name) > 100) {
            $profileError = 'A név maximum 100 karakter lehet.';
        } elseif (mb_strlen($phoneNumber) > 30) {
            $profileError = 'A telefonszám túl hosszú.';
        } elseif (mb_strlen($nationality) > 80) {
            $profileError = 'Az állampolgárság maximum 80 karakter lehet.';
        } elseif (mb_strlen($address) > 255) {
            $profileError = 'A lakcím maximum 255 karakter lehet.';
        }

        // Születési dátum ellenőrzése
        if ($profileError === null && $birthDate !== '') {

            $date = DateTime::createFromFormat('!Y-m-d', $birthDate);

            if (
                !$date ||
                $date->format('Y-m-d') !== $birthDate ||
                $date > new DateTime('today')
            ) {
                $profileError = 'Érvénytelen születési dátum.';
            }
        }

        // Ha minden adat megfelelő, mentés
        if ($profileError === null) {

            $profileData = [
                'name' => $name,
                'phone_number' => $phoneNumber,
                'birth_date' => $birthDate,
                'nationality' => $nationality,
                'address' => $address
            ];

            $saved = $guestService->updateProfile(
                $user['id'],
                $profileData
            );

            if ($saved) {

                $_SESSION['csrf_token'] = bin2hex(random_bytes(32));

                header('Location: profile.php?saved=1');
                exit;

            } else {
                $profileError = 'A profil mentése nem sikerült.';
            }
        }
    }

    // 6. Jelszó módosítása
    if (isset($_POST['change_password'])) {

        $currentPassword = $_POST['current_password'] ?? '';
        $newPassword = $_POST['new_password'] ?? '';
        $confirmPassword = $_POST['confirm_password'] ?? '';

        $result = $guestService->changePassword(
            $user['id'],
            $currentPassword,
            $newPassword,
            $confirmPassword
        );

        if ($result['success']) {

            $passwordSuccess = $result['message'];

            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));

            $csrfToken = $_SESSION['csrf_token'];

        } else {
            $passwordError = $result['message'];
        }
    }
}

// 7. Profiladatok lekérdezése
$profile = $guestService->getUserProfile($user['id']);

if (!$profile) {
    die('A felhasználói profil nem található.');
}

// Sikeres mentés üzenete
$profileSuccess = isset($_GET['saved']) && $_GET['saved'] === '1';

?>

<!DOCTYPE html>
<html lang="hu">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profilom</title>

    <link rel="stylesheet" href="../../assets/bootstrap-5.3.8-dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="../../css/main.css">
    <link rel="stylesheet" href="../../css/components.css">
    <link rel="stylesheet" href="../../css/variables.css">
</head>

<body>

<header class="reception-header py-4">
    <div class="container reception-header-inner">
        <h3 class="mb-0">Profilom</h3>
    </div>
</header>

<main class="container my-4 reception-body">

    <div class="info-card">

        <h4 class="mb-4">Személyes adatok</h4>

        <?php if ($profileSuccess): ?>
            <div class="alert alert-success">
                A profiladatok sikeresen módosultak.
            </div>
        <?php endif; ?>

        <?php if ($profileError !== null): ?>
            <div class="alert alert-danger">
                <?= htmlspecialchars($profileError) ?>
            </div>
        <?php endif; ?>

        <!-- Profiladatok módosítása -->
        <form method="POST" action="">

            <input type="hidden" name="csrf_token"
                   value="<?= htmlspecialchars($csrfToken) ?>">

            <div class="row g-3">

                <div class="col-md-6">
                    <label class="form-label">Teljes név</label>
                    <input type="text" name="name" class="form-control"
                        value="<?= htmlspecialchars($profile['name'] ?? '') ?>" readonly>
                </div>

                <div class="col-md-6">
                    <label class="form-label">E-mail cím</label>
                    <input type="email" name="email" class="form-control"
                        value="<?= htmlspecialchars($profile['email'] ?? '') ?>" readonly>
                </div>

                <div class="col-md-6">
                    <label class="form-label">Telefonszám</label>
                    <input type="text" name="phone_number" class="form-control"
                        value="<?= htmlspecialchars($profile['phone_number'] ?? '') ?>" readonly>
                </div>

                <div class="col-md-6">
                    <label class="form-label">Születési dátum</label>
                    <input type="date" name="birth_date" class="form-control"
                        value="<?= htmlspecialchars($profile['birth_date'] ?? '') ?>" readonly>
                </div>

                <div class="col-md-6">
                    <label class="form-label">Állampolgárság</label>
                    <input type="text" name="nationality" class="form-control"
                        value="<?= htmlspecialchars($profile['nationality'] ?? '') ?>" readonly>
                </div>

                <div class="col-md-6">
                    <label class="form-label">Lakcím</label>
                    <input type="text" name="address" class="form-control"
                        value="<?= htmlspecialchars($profile['address'] ?? '') ?>" readonly>
                </div>

            </div>

            <div class="mt-4">

                <button type="button" id="editButton" class="btn btn-primary">
                    Szerkesztés
                </button>

                <button type="submit" name="save_profile" value="1"
                    id="saveButton" class="btn btn-success d-none">
                    Mentés
                </button>

                <a href="guest.php" class="btn btn-secondary">
                    Vissza a főoldalra
                </a>

            </div>

        </form>

        <!-- Jelszó módosítása -->
        <hr class="my-4">

        <h4 class="mb-4">Jelszó módosítása</h4>

        <?php if ($passwordError !== null): ?>
            <div class="alert alert-danger">
                <?= htmlspecialchars($passwordError) ?>
            </div>
        <?php endif; ?>

        <?php if ($passwordSuccess !== null): ?>
            <div class="alert alert-success">
                <?= htmlspecialchars($passwordSuccess) ?>
            </div>
        <?php endif; ?>

        <form method="POST" action="">

            <input type="hidden" name="csrf_token"
                   value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">

            <div class="row g-3">

                <div class="col-md-6">
                    <label class="form-label">Jelenlegi jelszó</label>
                    <input type="password"
                        name="current_password"
                        class="form-control"
                        autocomplete="current-password"
                        required>
                </div>

                <div class="col-md-6">
                    <label class="form-label">Új jelszó</label>
                    <input type="password"
                        name="new_password"
                        class="form-control"
                        minlength="8"
                        autocomplete="new-password"
                        required>
                </div>

                <div class="col-md-6">
                    <label class="form-label">Új jelszó megerősítése</label>
                    <input type="password"
                        name="confirm_password"
                        class="form-control"
                        minlength="8"
                        autocomplete="new-password"
                        required>
                </div>

            </div>

            <div class="mt-4">
                <button type="submit"
                    name="change_password"
                    value="1"
                    class="btn btn-primary">
                    Jelszó módosítása
                </button>
            </div>

        </form>

    </div>

</main>

<script>
document.getElementById('editButton').addEventListener('click', function () {

    const fields = document.querySelectorAll(
        'input[name="name"], input[name="phone_number"], input[name="birth_date"], input[name="nationality"], input[name="address"]'
    );

    fields.forEach(function(field) {
        field.removeAttribute('readonly');
    });

    document.getElementById('editButton').classList.add('d-none');
    document.getElementById('saveButton').classList.remove('d-none');

});
</script>

</body>
</html>
