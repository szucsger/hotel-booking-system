<?php
session_start();

require_once __DIR__ . '/../../../src/config/pdo.php';

$pdo = getDatabaseConnection();

$roomId = filter_var($_GET['id'] ?? null, FILTER_VALIDATE_INT);

if ($roomId === false || $roomId === null || $roomId < 1) {
    http_response_code(404);
    exit("A szoba nem található.");
}

$stmt = $pdo->prepare('
    SELECT
        rooms.id,
        rooms.room_number,
        room_types.name AS room_type_name,
        room_types.description,
        room_types.capacity,
        room_types.bed_type,
        room_types.price_per_night
    FROM rooms
    INNER JOIN room_types
        ON rooms.room_type_id = room_types.id
    WHERE rooms.id = :room_id
    LIMIT 1
');

$stmt->execute(['room_id' => $roomId]);
$room = $stmt->fetch(PDO::FETCH_ASSOC);

if ($room === false) {
    http_response_code(404);
    exit("A szoba nem található.");
}
?>