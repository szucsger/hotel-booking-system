
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
// Profiladatok mentése
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

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

                <button type="submit" id="saveButton" class="btn btn-success d-none">
                    Mentés
                </button>

                <a href="guest.php" class="btn btn-secondary">
                    Vissza a főoldalra
                </a>
            </div>

        </form>


    </div>

</main>
<script>
document.getElementById('editButton').addEventListener('click', function () {

    const fields = document.querySelectorAll('input');

    fields.forEach(function(field) {
        field.removeAttribute('readonly');
    });

    document.getElementById('editButton').classList.add('d-none');
    document.getElementById('saveButton').classList.remove('d-none');

});
</script>
</body>
</html>
