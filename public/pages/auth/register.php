<?php
session_start();
require_once __DIR__ . '/../../../src/config/pdo.php';
require_once __DIR__ . '/../../../src/services/authservice.php';


$error = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = [
        'name' => trim($_POST['name'] ?? ''),
        'email' => trim($_POST['email'] ?? ''),
        'password' => $_POST['password'] ?? '',
        'phone' => trim($_POST['phone'] ?? ''),
        'birth_date' => trim($_POST['birth_date'] ?? ''),
        'nationality' => trim($_POST['nationality'] ?? ''),
        'address' => trim($_POST['address'] ?? '')
    ];
    if($data['password'] !== ($_POST['password_confirmation'] ?? '')) {
        $error = 'A jelszavak nem egyeznek.';
    }
    if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
        $error = 'Érvénytelen email cím.';
    }
    if (strlen($data['password']) < 8) {
        $error = 'A jelszónak legalább 8 karakter hosszúnak kell lennie.';
    }

    if ($data['name'] === '') {
        $error = 'A név megadása kötelező.';
    }

    if ($error === null) {
        try {
            $pdo = getDatabaseConnection();
            AuthService::register($pdo, $data);
            $_SESSION['success'] = 'Sikeres regisztráció. Most már bejelentkezhetsz.';
            header('Location: /hotel-booking-system/public/pages/auth/login.php');
            exit;
        } catch (Exception $exception) {
            error_log($exception->getMessage());

            $error = 'A regisztráció nem sikerült. Ellenőrizd az adatokat, vagy próbáld újra később.';

        }
    }


}
?>
<!DOCTYPE html>
<html lang="hu">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Regisztráció</title>
    <link rel="stylesheet" href="../../assets/bootstrap-5.3.8-dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="../../css/main.css">
    <link rel="stylesheet" href="../../css/components.css">
    <link rel="stylesheet" href="../../css/variables.css">
</head>

<body>
    <?php require __DIR__ . '/../../../src/includes/header.php'; ?>
    <main>
        <div class="container">
            
            <div class="register-form-container">
                <h1 class="register-title">Regisztráció</h1>
                <form action="/hotel-booking-system/public/pages/auth/register.php" method="POST">
                    <div class="mb-3">
                        <label for="name" class="form-label">Név:</label>
                        <input type="text" id="name" name="name" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label for="email" class="form-label">Email:</label>
                        <input type="email" id="email" name="email" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label for="password" class="form-label">Jelszó:</label>
                        <input type="password" id="password" name="password" class="form-control"  required>
                    </div>
                    <div class="mb-3">
                        <label for="password_confirmation" class="form-label">Jelszó megerősítése:</label>
                        <input type="password" id="password_confirmation" name="password_confirmation" class="form-control"  required>
                    </div>
                    <div class="mb-3">
                        <label for="phone" class="form-label">Telefonszám:</label>
                        <input type="text" id="phone" name="phone" class="form-control"  required>
                    </div>
                    <div class="mb-3">
                        <label for="birth_date" class="form-label">Születési dátum:</label>
                        <input type="date" id="birth_date" name="birth_date" class="form-control"  required>
                    </div>
                    <div class="mb-3">
                        <label for="nationality" class="form-label">Nemzetiség:</label>
                        <input type="text" id="nationality" name="nationality" class="form-control"  required>
                    </div>
                    <div class="mb-3">
                        <label for="address" class="form-label">Lakcím:</label>
                        <input type="text" id="address" name="address" class="form-control"  required>
                    </div>
                    <button type="submit" class="btn submit-login" id="submit-registration">Regisztráció</button>
                    <?php if ($error): ?>
                        <div class="alert alert-danger mt-3"><?php echo $error; ?></div>
                    <?php endif; ?>
                </form>
            </div>
        </div>
        <?php require __DIR__ . '/../../../src/includes/footer.php'; ?>
    </main>
        <script src="../../assets/bootstrap-5.3.8-dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>