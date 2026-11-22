
<?php
session_start();

// Munkamenet törlése
session_unset();
session_destroy();

// Visszairányítás a bejelentkező oldalra
header('Location: /hotel-booking-system/public/pages/auth/login.php');
exit;
