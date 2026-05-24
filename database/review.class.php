<?php
declare(strict_types = 1);

class Review {
    public int $id;
    public int $userId;
    public int $classId;
    public int $rating;
    public ?string $comment;
    public string $createdAt;

    public function __construct(int $id, int $userId, int $classId, int $rating, ?string $comment, string $createdAt) {
        $this->id = $id;
        $this->userId = $userId;
        $this->classId = $classId;
        $this->rating = $rating;
        $this->comment = $comment;
        $this->createdAt = $createdAt;
    }

    private static function fromRow(array $r) : Review {
        return new Review($r['id'], $r['userId'], $r['classId'], $r['rating'], $r['comment'], $r['createdAt']);
    }

    static function getByClass(PDO $db, int $classId) : array {
        $stmt = $db->prepare('SELECT * FROM Review WHERE classId = ? ORDER BY createdAt DESC');
        $stmt->execute([$classId]);
        $reviews = [];
        while ($r = $stmt->fetch())
            $reviews[] = self::fromRow($r);
        return $reviews;
    }

    static function getByUser(PDO $db, int $userId) : array {
        $stmt = $db->prepare('SELECT * FROM Review WHERE userId = ? ORDER BY createdAt DESC');
        $stmt->execute([$userId]);
        $reviews = [];
        while ($r = $stmt->fetch())
            $reviews[] = self::fromRow($r);
        return $reviews;
    }

    static function getAverageRating(PDO $db, int $classId) : ?float {
        $stmt = $db->prepare('SELECT AVG(rating) as avg FROM Review WHERE classId = ?');
        $stmt->execute([$classId]);
        $result = $stmt->fetch();
        return $result['avg'] ? (float) $result['avg'] : null;
    }

    static function create(PDO $db, int $userId, int $classId, int $rating, ?string $comment) : void {
        $stmt = $db->prepare('INSERT INTO Review (userId, classId, rating, comment) VALUES (?, ?, ?, ?)');
        $stmt->execute([$userId, $classId, $rating, $comment]);
    }

    static function update(PDO $db, int $userId, int $classId, int $rating, ?string $comment) : void {
        $stmt = $db->prepare('UPDATE Review SET rating = ?, comment = ? WHERE userId = ? AND classId = ?');
        $stmt->execute([$rating, $comment, $userId, $classId]);
    }

    static function delete(PDO $db, int $userId, int $classId) : void {
        $stmt = $db->prepare('DELETE FROM Review WHERE userId = ? AND classId = ?');
        $stmt->execute([$userId, $classId]);
    }
}
?>
