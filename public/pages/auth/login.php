<?php 
session_start();

require_once __DIR__ . '/../../../src/config/pdo.php';
require_once __DIR__ . '/../../../src/services/authservice.php';

$error = null;
$success = $_SESSION['success'] ?? null;
unset($_SESSION['success']);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    if (!filter_var($email, FILTER_VALIDATE_EMAIL) || $password === '') {
        $error = 'Hibás email vagy jelszó.';
    } else {
        try {
            $pdo = getDatabaseConnection();
            $user = AuthService::authenticate(
                $pdo,
                $email,
                $password
            );

            if ($user === null) {
                $error = 'Hibás email vagy jelszó.';
            } else {
                session_regenerate_id(true);
                $_SESSION['user'] = $user;

                $redirects = [
                    'admin' => '/hotel-booking-system/public/pages/staff/admin.php',
                    'receptionist' => '/hotel-booking-system/public/pages/staff/reception.php',
                    'user' => '/hotel-booking-system/public/pages/guest/dashboard.php',
                ];

                $redirectUrl = $redirects[$user['role']]
                    ?? '/hotel-booking-system/public/index.php';

                header('Location: ' . $redirectUrl);
                exit;
            }
        } catch (PDOException $exception) {
            error_log($exception->getMessage());
            $error = 'A bejelentkezés jelenleg nem elérhető.';
        }
    }
}

?>
<!DOCTYPE html>
<html lang="hu">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <link rel="stylesheet" href="../../assets/bootstrap-5.3.8-dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="../../css/main.css">
    <link rel="stylesheet" href="../../css/components.css">
    <link rel="stylesheet" href="../../css/variables.css">
</head>

<body>
    <?php require __DIR__ . '/../../../src/includes/header.php'; ?>
    <main>
        <div class="container">
            <div class="login-form-container">
                <h1 class="login-title">Bejelentkezés</h1>
                <form action="/hotel-booking-system/public/pages/auth/login.php" method="POST">
                    <?php if ($success !== null): ?>
                        <div class="alert alert-success mt-3">
                            <?= htmlspecialchars($success, ENT_QUOTES, 'UTF-8') ?>
                        </div>
                    <?php endif; ?>
                    <div class="mb-3">
                        <label for="email" class="form-label">Email:</label>
                        <input type="email" id="email" name="email" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label for="password" class="form-label">Jelszó:</label>
                        <input type="password" id="password" name="password" class="form-control" required>
                    </div>
                    <button type="submit" class="btn submit-login" id="submit-login">Belépés</button>
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