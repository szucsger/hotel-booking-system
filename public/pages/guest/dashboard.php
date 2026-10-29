<?php
session_start();
require_once __DIR__ . '/../../../src/services/authservice.php';

$user = AuthService::getAuthenticatedUser();

if ($user === null || !AuthService::hasRole(['user'])) {
    http_response_code(403);
    exit('Nincs jogosultságod az oldal megtekintéséhez.');
}
?>