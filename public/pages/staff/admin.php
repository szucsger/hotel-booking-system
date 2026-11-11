<?php

session_start();

require_once __DIR__ . '/../../../src/services/authservice.php';

if (!AuthService::hasRole(['admin'])) {
    http_response_code(403);
    exit('Nincs jogosultságod az oldal megtekintéséhez.');
}