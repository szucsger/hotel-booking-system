<?php

// Ez az alkalmazás közös API-belépési pontja; a műveletet az action mező választja ki.
session_start();
header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');

// A végpont csak az API-réteget köti össze a közös adatbázissal és az üzleti logikával.
require_once __DIR__ . '/../../src/config/pdo.php';
require_once __DIR__ . '/../../src/services/receptionservice.php';
require_once __DIR__ . '/../../src/services/authservice.php';

// Minden válasz ugyanazt a JSON-struktúrát és HTTP-státuszkódot használja.
function sendApiJson(int $statusCode, array $payload): void
{
    http_response_code($statusCode);
    echo json_encode($payload, JSON_UNESCAPED_UNICODE);
    exit;
}

// Az API kizárólag módosító vagy lekérdező POST-kéréseket fogad.
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    sendApiJson(405, ['success' => false, 'message' => 'Nem támogatott kérés.']);
}

// Ellenőrzi, hogy a felhasználó rendelkezik-e a megfelelő szerepkörrel az API-híváshoz.
$user = AuthService::getAuthenticatedUser();
if ($user === null) {
    sendApiJson(401, ['success' => false, 'message' => 'Nincs jogosultságod az API-híváshoz.']);
}

if (!AuthService::hasRole(['admin', 'receptionist'])) {
    sendApiJson(403, ['success' => false, 'message' => 'Nincs jogosultságod az API-híváshoz.']);
}

// A munkamenethez kötött token megakadályozza, hogy más weboldal a felhasználó nevében küldjön kérést.
$sessionToken = $_SESSION['csrf_token'] ?? '';
$requestToken = $_POST['csrf_token'] ?? '';
if (
    !is_string($sessionToken)
    || $sessionToken === ''
    || !is_string($requestToken)
    || !hash_equals($sessionToken, $requestToken)
) {
    sendApiJson(403, ['success' => false, 'message' => 'Érvénytelen kérés. Frissítsd az oldalt.']);
}

try {
    $pdo = getDatabaseConnection();

    // Az action neve tartalmazza a funkcióterületet, így a későbbi API-műveletek is egyediek maradnak.
    switch ($_POST['action'] ?? '') {
        case 'reception.search_guest':
            $result = ReceptionService::searchGuests($pdo, $_POST);
            sendApiJson(200, [
                'success' => true,
                'message' => $result['guests'] === [] ? 'Nem találtam ilyen vendéget.' : '',
                'guests' => $result['guests'],
            ]);

        case 'reception.available_rooms':
            $result = ReceptionService::getAvailableRooms($pdo, $_POST);
            sendApiJson(200, [
                'success' => true,
                'rooms' => $result['rooms'],
                'message' => $result['message'],
            ]);

        case 'reception.new_booking':
            ReceptionService::createBooking($pdo, $_POST);
            sendApiJson(201, [
                'success' => true,
                'message' => 'A foglalás sikeresen rögzítve.',
            ]);

        case 'reception.modify_room_status':
            ReceptionService::modifyRoomStatus($pdo, $_POST);
            $_SESSION['room_status_message'] = 'A szoba státusza sikeresen módosult.';
            sendApiJson(200, [
                'success' => true,
                'message' => $_SESSION['room_status_message'],
            ]);

        case 'reception.check_in':
            ReceptionService::checkIn($pdo, $_POST);
            sendApiJson(200, [
                'success' => true,
                'message' => 'A vendég bejelentkeztetése sikerült.',
            ]);

        case 'reception.check_out':
            ReceptionService::checkOut($pdo, $_POST);
            sendApiJson(200, [
                'success' => true,
                'message' => 'A vendég kijelentkeztetése sikerült.',
            ]);

        default:
            sendApiJson(400, ['success' => false, 'message' => 'Ismeretlen művelet.']);
    }
} catch (DomainException $e) {
    // A szolgáltatás ismert üzleti vagy validációs hibáit kliensoldali hibaként adjuk vissza.
    $statusCode = in_array($e->getCode(), [404, 409, 422], true) ? $e->getCode() : 422;
    sendApiJson($statusCode, [
        'success' => false,
        'message' => $e->getMessage(),
    ]);
} catch (PDOException $e) {
    // A részletes adatbázishiba a szervernaplóba kerül, a kliens általános üzenetet kap.
    error_log($e->getMessage());
    sendApiJson(500, [
        'success' => false,
        'message' => 'Az adatbázis-művelet nem sikerült.',
    ]);
}
