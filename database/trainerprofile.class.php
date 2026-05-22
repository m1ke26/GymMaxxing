<?php
declare(strict_types = 1);
class TrainerProfile {
    public int $id;
    public int $userId;
    public ?string $bio;
    public ?string $specialization;

    public function __construct(int $id, int $userId, ?string $bio, ?string $specialization) {
        $this->id = $id;
        $this->userId = $userId;
        $this->bio = $bio;
        $this->specialization = $specialization;
    }

    static function getByUserId(PDO $db, int $userId) : ?TrainerProfile {
        $stmt = $db->prepare('SELECT * FROM TrainerProfile WHERE userId = ?');
        $stmt->execute([$userId]);
        $t = $stmt->fetch();
        if (!$t) return null;
        return new TrainerProfile($t['id'], $t['userId'], $t['bio'], $t['specialization']);
    }

    static function create(PDO $db, int $userId, ?string $bio, ?string $specialization) : void {
        $stmt = $db->prepare('INSERT INTO TrainerProfile (userId, bio, specialization) VALUES (?, ?, ?)');
        $stmt->execute([$userId, $bio, $specialization]);
    }
}
?>