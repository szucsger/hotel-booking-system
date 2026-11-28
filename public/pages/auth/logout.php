<?php
session_start();
session_destroy();
header("Location: /hotel-booking-system/public/index.php");
exit;
?>