<?php
// Ellenőrzi a felhasználó hitelesítését az adatbázisban a megadott email és jelszó alapján.
final class AuthService
{
    // Hitelesíti a felhasználót az adatbázisban a megadott email és jelszó alapján.
    public static function authenticate(PDO $pdo, string $email, string $password): ?array
    {
        $stmt = $pdo->prepare('
            SELECT id, name, email, password_hash, role
            FROM users
            WHERE email = :email
            AND is_deleted = 0
            LIMIT 1
        ');
        $stmt->execute(['email' => $email]);

        $user = $stmt->fetch();

        if ($user === false || !password_verify($password, $user['password_hash'])) {
            return null;
        }

        unset($user['password_hash']);

        return $user;
    }

    // Visszaadja a jelenleg hitelesített felhasználót a munkamenetből, ha van.
    public static function getAuthenticatedUser(): ?array
    {
        $user = $_SESSION['user'] ?? null;
        return is_array($user) ? $user : null;
    }

    public static function hasRole(array $allowedRoles): bool
    {
        $user = self::getAuthenticatedUser();
        if ($user === null) {
            return false;
        }
        return in_array($user['role'], $allowedRoles, true);
    }

    // Regisztrálja az új felhasználót az adatbázisban.
    public static function register(PDO $pdo, array $data): void
    {
        $passwordHash = password_hash($data['password'], PASSWORD_DEFAULT);
        
        $stmt = $pdo->prepare('
            INSERT INTO users (role, name, email, password_hash, phone_number, birth_date, nationality, address, is_deleted)
            VALUES (:role, :name, :email, :password_hash, :phone_number, :birth_date, :nationality, :address, 0)
        ');

        $stmt->execute([
            'role' => 'user',
            'name' => $data['name'],
            'email' => $data['email'],
            'password_hash' => $passwordHash,
            'phone_number' => $data['phone'],
            'birth_date' => $data['birth_date'],
            'nationality' => $data['nationality'],
            'address' => $data['address'],
        ]);
    }
}





