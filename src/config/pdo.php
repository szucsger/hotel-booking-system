<?php

// Egyetlen adatbázis-kapcsolatot használ egy kérésen belül, és csak az első híváskor hozza létre.
function getDatabaseConnection(): PDO
{
    static $pdo = null;

    if ($pdo instanceof PDO) {
        return $pdo;
    }

    // A hitelesítési adatokat a meglévő konfigurációs fájlból olvassuk be.
    require __DIR__ . '/db.php';

    $pdo = new PDO(
        "mysql:host=$host;dbname=$db;charset=utf8mb4",
        $user,
        $pass,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]
    );

    return $pdo;
}
