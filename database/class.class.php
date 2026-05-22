<?php
declare(strict_types = 1);
class GymClass {
    public int $id;
    public string $title;
    public ?string $image;
    public string $schedule;
    public int $capacity;
    public int $trainerId;

    public function __construct(int $id, string $title, ?string $image, string $schedule, int $capacity, int $trainerId) {
        $this->id = $id;
        $this->title = $title;
        $this->image = $image;
        $this->schedule = $schedule;
        $this->capacity = $capacity;
        $this->trainerId = $trainerId;
    }

    static function getAllClasses(PDO $db) : array {
        $stmt = $db->prepare('SELECT * FROM Class');
        $stmt->execute();
        $classes = [];
        while ($c = $stmt->fetch())
            $classes[] = new GymClass($c['id'], $c['title'], $c['image'], $c['schedule'], $c['capacity'], $c['trainerId']);
        return $classes;
    }

    static function getClassById(PDO $db, int $id) : ?GymClass {
        $stmt = $db->prepare('SELECT * FROM Class WHERE id = ?');
        $stmt->execute([$id]);
        $c = $stmt->fetch();
        if (!$c) return null;
        return new GymClass($c['id'], $c['title'], $c['image'], $c['schedule'], $c['capacity'], $c['trainerId']);
    }
}
?>