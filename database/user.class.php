<?php
declare(strict_types = 1);

class User {
    public int $id;
    public string $name;
    public string $username;
    public string $email;
    public string $password;
    public ?string $phone;
    public ?string $photo;
    public string $role;
    public int $active;

    public function __construct(int $id, string $name, string $username, string $email, string $password, ?string $phone, ?string $photo, string $role, int $active = 1) {
        $this->id = $id;
        $this->name = $name;
        $this->username = $username;
        $this->email = $email;
        $this->password = $password;
        $this->phone = $phone;
        $this->photo = $photo;
        $this->role = $role;
        $this->active = $active;
    }

    private static function fromRow(array $u) : User {
        return new User($u['id'], $u['name'], $u['username'], $u['email'], $u['password'], $u['phone'], $u['photo'], $u['role'], $u['active']);
    }

    static function getUserByEmail(PDO $db, string $email) : ?User {
        $stmt = $db->prepare('SELECT * FROM User WHERE email = ?');
        $stmt->execute([$email]);
        $u = $stmt->fetch();
        return $u ? self::fromRow($u) : null;
    }

    static function getUserById(PDO $db, int $id) : ?User {
        $stmt = $db->prepare('SELECT * FROM User WHERE id = ?');
        $stmt->execute([$id]);
        $u = $stmt->fetch();
        return $u ? self::fromRow($u) : null;
    }

    static function getUserByUsername(PDO $db, string $username) : ?User {
        $stmt = $db->prepare('SELECT * FROM User WHERE username = ?');
        $stmt->execute([$username]);
        $u = $stmt->fetch();
        return $u ? self::fromRow($u) : null;
    }

    static function getAllUsers(PDO $db) : array {
        $stmt = $db->query('SELECT * FROM User');
        $users = [];
        while ($u = $stmt->fetch())
            $users[] = self::fromRow($u);
        return $users;
    }

    static function getUsersByRole(PDO $db, string $role) : array {
        $stmt = $db->prepare('SELECT * FROM User WHERE role = ?');
        $stmt->execute([$role]);
        $users = [];
        while ($u = $stmt->fetch())
            $users[] = self::fromRow($u);
        return $users;
    }

    static function createUser(PDO $db, string $name, string $username, string $email, string $password, ?string $phone, string $role) : int {
        $stmt = $db->prepare('INSERT INTO User (name, username, email, password, phone, role) VALUES (?, ?, ?, ?, ?, ?)');
        $stmt->execute([$name, $username, $email, password_hash($password, PASSWORD_DEFAULT), $phone, $role]);
        return (int) $db->lastInsertId();
    }

    static function updateUser(PDO $db, int $id, string $name, string $username, string $email, ?string $phone) : void {
        $stmt = $db->prepare('UPDATE User SET name = ?, username = ?, email = ?, phone = ? WHERE id = ?');
        $stmt->execute([$name, $username, $email, $phone, $id]);
    }

    static function updatePassword(PDO $db, int $id, string $password) : void {
        $stmt = $db->prepare('UPDATE User SET password = ? WHERE id = ?');
        $stmt->execute([password_hash($password, PASSWORD_DEFAULT), $id]);
    }

    static function updatePhoto(PDO $db, int $id, string $photo) : void {
        $stmt = $db->prepare('UPDATE User SET photo = ? WHERE id = ?');
        $stmt->execute([$photo, $id]);
    }

    static function updateRole(PDO $db, int $id, string $role) : void {
        $stmt = $db->prepare('UPDATE User SET role = ? WHERE id = ?');
        $stmt->execute([$role, $id]);
    }

    static function deactivateUser(PDO $db, int $id) : void {
        $stmt = $db->prepare('UPDATE User SET active = 0 WHERE id = ?');
        $stmt->execute([$id]);
    }

    static function activateUser(PDO $db, int $id) : void {
        $stmt = $db->prepare('UPDATE User SET active = 1 WHERE id = ?');
        $stmt->execute([$id]);
    }
}
?>
