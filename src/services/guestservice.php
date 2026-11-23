
<?php

require_once __DIR__ . '/../config/pdo.php';

class GuestService
{
    private $pdo;

    // Adatbázis-kapcsolat létrehozása
    public function __construct()
    {
        $this->pdo = getDatabaseConnection();
    }

    // 1. Vendég profiladatainak lekérdezése
    public function getUserProfile($userId)
    {
        $sql = "
            SELECT name, email, phone_number, birth_date, nationality, address
            FROM users
            WHERE id = :id
            AND is_deleted = 0
            LIMIT 1
        ";

        $stmt = $this->pdo->prepare($sql);

        $stmt->execute([
            'id' => $userId
        ]);

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }


    // 2. Profiladatok módosítása
    public function updateProfile($userId, $data)
    {
        $sql = "
            UPDATE users
            SET
                name = :name,
                phone_number = :phone_number,
                birth_date = :birth_date,
                nationality = :nationality,
                address = :address
            WHERE id = :id
            AND is_deleted = 0
        ";

        $stmt = $this->pdo->prepare($sql);

        return $stmt->execute([
            'name' => $data['name'],
            'phone_number' => $data['phone_number'],
            'birth_date' => $data['birth_date'],
            'nationality' => $data['nationality'],
            'address' => $data['address'],
            'id' => $userId
        ]);
    }


    // 3. Jelszó ellenőrzése és módosítása
    public function changePassword(
        $userId,
        $currentPassword,
        $newPassword,
        $confirmPassword
    ) {

        // Jelenlegi jelszó lekérdezése
        $sql = "
            SELECT password_hash
            FROM users
            WHERE id = :id
            AND is_deleted = 0
            LIMIT 1
        ";

        $stmt = $this->pdo->prepare($sql);

        $stmt->execute([
            'id' => $userId
        ]);

        $passwordHash = $stmt->fetchColumn();

        // Felhasználó ellenőrzése
        if ($passwordHash === false) {
            return [
                'success' => false,
                'message' => 'A felhasználó nem található.'
            ];
        }

        // Régi jelszó ellenőrzése
        if (!password_verify($currentPassword, $passwordHash)) {
            return [
                'success' => false,
                'message' => 'A jelenlegi jelszó hibás.'
            ];
        }

        // Új jelszavak egyezésének ellenőrzése
        if ($newPassword !== $confirmPassword) {
            return [
                'success' => false,
                'message' => 'Az új jelszavak nem egyeznek.'
            ];
        }

        // Jelszó hosszának ellenőrzése
        if (strlen($newPassword) < 8) {
            return [
                'success' => false,
                'message' => 'Az új jelszónak legalább 8 karakteresnek kell lennie.'
            ];
        }

        // Új jelszó hash elkészítése
        $newHash = password_hash(
            $newPassword,
            PASSWORD_DEFAULT
        );

        // Új jelszó mentése
        $sql = "
            UPDATE users
            SET password_hash = :password_hash
            WHERE id = :id
            AND is_deleted = 0
        ";

        $stmt = $this->pdo->prepare($sql);

        $stmt->execute([
            'password_hash' => $newHash,
            'id' => $userId
        ]);

        return [
            'success' => true,
            'message' => 'A jelszó sikeresen módosult.'
        ];
    }
}
