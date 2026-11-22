
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

// Jelszó módosítása
if (isset($_POST['change_password'])) {

    $currentPassword = $_POST['current_password'];
    $newPassword = $_POST['new_password'];
    $confirmPassword = $_POST['confirm_password'];

    // Jelenlegi jelszó lekérése
    $stmt = $pdo->prepare("
        SELECT password_hash
        FROM users
        WHERE id = :id
        AND is_deleted = 0
    ");

    $stmt->execute([
        'id' => $user['id']
    ]);

    $passwordData = $stmt->fetch(PDO::FETCH_ASSOC);



    if (!password_verify($currentPassword, $passwordData['password_hash'])) {
        $passwordError = "A jelenlegi jelszó hibás.";
    } elseif ($newPassword !== $confirmPassword) {
        $passwordError = "Az új jelszavak nem egyeznek.";
    } elseif (strlen($newPassword) < 8) {
        $passwordError = "Az új jelszónak legalább 8 karakteresnek kell lennie.";
    } else {

        $newHash = password_hash($newPassword, PASSWORD_DEFAULT);

        $stmt = $pdo->prepare("
            UPDATE users
            SET password_hash = :password_hash
            WHERE id = :id
        ");

        $stmt->execute([
            'password_hash' => $newHash,
            'id' => $user['id']
        ]);

        $passwordSuccess = "A jelszó sikeresen módosult.";
    }
}
// Profiladatok mentése
if (isset($_POST['save_profile'])) {

    $sql = "
        UPDATE users
        SET
            name = :name,
            phone_number = :phone_number,
            birth_date = :birth_date,
            nationality = :nationality,
            address = :address
        WHERE id = :id
        AND is_deleted = 0
    ";

    $stmt = $pdo->prepare($sql);

    $stmt->execute([
        'name' => trim($_POST['name']),
        'phone_number' => trim($_POST['phone_number']),
        'birth_date' => $_POST['birth_date'],
        'nationality' => trim($_POST['nationality']),
        'address' => trim($_POST['address']),
        'id' => $user['id']
    ]);

    header('Location: profile.php?saved=1');
    exit;
}

// Vendég adatainak lekérdezése
$sql = "
    SELECT name, email, phone_number, birth_date, nationality, address
    FROM users
    WHERE id = :id
    AND is_deleted = 0
    LIMIT 1
";

$stmt = $pdo->prepare($sql);
$stmt->execute([
    'id' => $user['id']
]);

$profile = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$profile) {
    die('A felhasználói profil nem található.');
}
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

       
        <form method="POST" action="">

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

<?php if (isset($passwordError)): ?>
    <div class="alert alert-danger">
        <?= htmlspecialchars($passwordError) ?>
    </div>
<?php endif; ?>

<?php if (isset($passwordSuccess)): ?>
    <div class="alert alert-success">
        <?= htmlspecialchars($passwordSuccess) ?>
    </div>
<?php endif; ?>

<form method="POST" action="">

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
