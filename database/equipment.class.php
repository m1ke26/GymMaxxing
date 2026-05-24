<?php
declare(strict_types = 1);

class Equipment {
    public int $id;
    public string $name;
    public string $type;
    public string $status;
    public ?string $description;

    public function __construct(int $id, string $name, string $type, string $status, ?string $description) {
        $this->id = $id;
        $this->name = $name;
        $this->type = $type;
        $this->status = $status;
        $this->description = $description;
    }

    private static function fromRow(array $e) : Equipment {
        return new Equipment($e['id'], $e['name'], $e['type'], $e['status'], $e['description']);
    }

    static function getAll(PDO $db) : array {
        $stmt = $db->query('SELECT * FROM Equipment');
        $items = [];
        while ($e = $stmt->fetch())
            $items[] = self::fromRow($e);
        return $items;
    }

    static function getById(PDO $db, int $id) : ?Equipment {
        $stmt = $db->prepare('SELECT * FROM Equipment WHERE id = ?');
        $stmt->execute([$id]);
        $e = $stmt->fetch();
        return $e ? self::fromRow($e) : null;
    }

    static function getByType(PDO $db, string $type) : array {
        $stmt = $db->prepare('SELECT * FROM Equipment WHERE type = ?');
        $stmt->execute([$type]);
        $items = [];
        while ($e = $stmt->fetch())
            $items[] = self::fromRow($e);
        return $items;
    }

    static function getAvailable(PDO $db) : array {
        $stmt = $db->prepare('SELECT * FROM Equipment WHERE status = ?');
        $stmt->execute(['available']);
        $items = [];
        while ($e = $stmt->fetch())
            $items[] = self::fromRow($e);
        return $items;
    }

    static function create(PDO $db, string $name, string $type, string $status, ?string $description) : int {
        $stmt = $db->prepare('INSERT INTO Equipment (name, type, status, description) VALUES (?, ?, ?, ?)');
        $stmt->execute([$name, $type, $status, $description]);
        return (int) $db->lastInsertId();
    }

    static function update(PDO $db, int $id, string $name, string $type, string $status, ?string $description) : void {
        $stmt = $db->prepare('UPDATE Equipment SET name = ?, type = ?, status = ?, description = ? WHERE id = ?');
        $stmt->execute([$name, $type, $status, $description, $id]);
    }

    static function updateStatus(PDO $db, int $id, string $status) : void {
        $stmt = $db->prepare('UPDATE Equipment SET status = ? WHERE id = ?');
        $stmt->execute([$status, $id]);
    }

    static function delete(PDO $db, int $id) : void {
        $stmt = $db->prepare('DELETE FROM Equipment WHERE id = ?');
        $stmt->execute([$id]);
    }
}
?>
