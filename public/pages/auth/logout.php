<?php
session_start();

if ($_SERVER['REQUEST_METHOD'] !== 'POST'
    || !isset($_SESSION['csrf_token'], $_POST['csrf_token'])
    || !is_string($_SESSION['csrf_token'])
    || !is_string($_POST['csrf_token'])
    || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])
) {
    http_response_code(403);
    exit('Érvénytelen kérés.');
}

session_destroy();
header("Location: /hotel-booking-system/public/index.php");
exit;
?>