<?php
declare(strict_types = 1);

class GymClass {
    public int $id;
    public string $title;
    public string $type;
    public ?string $description;
    public ?string $image;
    public string $schedule;
    public int $capacity;
    public int $trainerId;

    public function __construct(int $id, string $title, string $type, ?string $description, ?string $image, string $schedule, int $capacity, int $trainerId) {
        $this->id = $id;
        $this->title = $title;
        $this->type = $type;
        $this->description = $description;
        $this->image = $image;
        $this->schedule = $schedule;
        $this->capacity = $capacity;
        $this->trainerId = $trainerId;
    }

    private static function fromRow(array $c) : GymClass {
        return new GymClass($c['id'], $c['title'], $c['type'], $c['description'], $c['image'], $c['schedule'], $c['capacity'], $c['trainerId']);
    }

    static function getAllClasses(PDO $db) : array {
        $stmt = $db->query('SELECT * FROM Class');
        $classes = [];
        while ($c = $stmt->fetch())
            $classes[] = self::fromRow($c);
        return $classes;
    }

    static function getClassById(PDO $db, int $id) : ?GymClass {
        $stmt = $db->prepare('SELECT * FROM Class WHERE id = ?');
        $stmt->execute([$id]);
        $c = $stmt->fetch();
        return $c ? self::fromRow($c) : null;
    }

    static function getClassesByTrainer(PDO $db, int $trainerId) : array {
        $stmt = $db->prepare('SELECT * FROM Class WHERE trainerId = ?');
        $stmt->execute([$trainerId]);
        $classes = [];
        while ($c = $stmt->fetch())
            $classes[] = self::fromRow($c);
        return $classes;
    }

    static function getClassesByType(PDO $db, string $type) : array {
        $stmt = $db->prepare('SELECT * FROM Class WHERE type = ?');
        $stmt->execute([$type]);
        $classes = [];
        while ($c = $stmt->fetch())
            $classes[] = self::fromRow($c);
        return $classes;
    }

    static function searchClasses(PDO $db, ?string $type, ?int $trainerId, ?string $day) : array {
        $sql = 'SELECT * FROM Class WHERE 1=1';
        $params = [];
        if ($type) { $sql .= ' AND type = ?'; $params[] = $type; }
        if ($trainerId) { $sql .= ' AND trainerId = ?'; $params[] = $trainerId; }
        if ($day) { $sql .= ' AND schedule LIKE ?'; $params[] = $day . '%'; }
        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        $classes = [];
        while ($c = $stmt->fetch())
            $classes[] = self::fromRow($c);
        return $classes;
    }

    static function createClass(PDO $db, string $title, string $type, ?string $description, ?string $image, string $schedule, int $capacity, int $trainerId) : int {
        $stmt = $db->prepare('INSERT INTO Class (title, type, description, image, schedule, capacity, trainerId) VALUES (?, ?, ?, ?, ?, ?, ?)');
        $stmt->execute([$title, $type, $description, $image, $schedule, $capacity, $trainerId]);
        return (int) $db->lastInsertId();
    }

    static function updateClass(PDO $db, int $id, string $title, string $type, ?string $description, ?string $image, string $schedule, int $capacity, int $trainerId) : void {
        $stmt = $db->prepare('UPDATE Class SET title = ?, type = ?, description = ?, image = ?, schedule = ?, capacity = ?, trainerId = ? WHERE id = ?');
        $stmt->execute([$title, $type, $description, $image, $schedule, $capacity, $trainerId, $id]);
    }

    static function deleteClass(PDO $db, int $id) : void {
        $stmt = $db->prepare('DELETE FROM Class WHERE id = ?');
        $stmt->execute([$id]);
    }
}
?>
