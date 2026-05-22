<?php
declare(strict_types = 1);
class User {
    public int $id;
    public string $name;
    public string $email;
    public string $password;
    public ?string $phone;
    public string $role;

    public function __construct(int $id, string $name, string $email, string $password, ?string $phone, string $role) {
        $this->id = $id;
        $this->name = $name;
        $this->email = $email;
        $this->password = $password;
        $this->phone = $phone;
        $this->role = $role;
    }

    static function getUserByEmail(PDO $db, string $email) : ?User {
        $stmt = $db->prepare('SELECT * FROM User WHERE email = ?');
        $stmt->execute([$email]);
        $u = $stmt->fetch();
        if (!$u) return null;
        return new User($u['id'], $u['name'], $u['email'], $u['password'], $u['phone'], $u['role']);
    }

    static function getUserById(PDO $db, int $id) : ?User {
        $stmt = $db->prepare('SELECT * FROM User WHERE id = ?');
        $stmt->execute([$id]);
        $u = $stmt->fetch();
        if (!$u) return null;
        return new User($u['id'], $u['name'], $u['email'], $u['password'], $u['phone'], $u['role']);
    }

    static function createUser(PDO $db, string $name, string $email, string $password, ?string $phone, string $role) : void {
        $stmt = $db->prepare('INSERT INTO User (name, email, password, phone, role) VALUES (?, ?, ?, ?, ?)');
        $stmt->execute([$name, $email, $password, $phone, $role]);
    }
}
?>