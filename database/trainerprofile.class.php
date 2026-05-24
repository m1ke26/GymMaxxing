<?php
declare(strict_types = 1);

class TrainerProfile {
    public int $id;
    public int $userId;
    public ?string $bio;
    public ?string $specialization;
    public ?string $certifications;

    public function __construct(int $id, int $userId, ?string $bio, ?string $specialization, ?string $certifications) {
        $this->id = $id;
        $this->userId = $userId;
        $this->bio = $bio;
        $this->specialization = $specialization;
        $this->certifications = $certifications;
    }

    private static function fromRow(array $t) : TrainerProfile {
        return new TrainerProfile($t['id'], $t['userId'], $t['bio'], $t['specialization'], $t['certifications']);
    }

    static function getByUserId(PDO $db, int $userId) : ?TrainerProfile {
        $stmt = $db->prepare('SELECT * FROM TrainerProfile WHERE userId = ?');
        $stmt->execute([$userId]);
        $t = $stmt->fetch();
        return $t ? self::fromRow($t) : null;
    }

    static function getAll(PDO $db) : array {
        $stmt = $db->query('SELECT * FROM TrainerProfile');
        $profiles = [];
        while ($t = $stmt->fetch())
            $profiles[] = self::fromRow($t);
        return $profiles;
    }

    static function create(PDO $db, int $userId, ?string $bio, ?string $specialization, ?string $certifications) : void {
        $stmt = $db->prepare('INSERT INTO TrainerProfile (userId, bio, specialization, certifications) VALUES (?, ?, ?, ?)');
        $stmt->execute([$userId, $bio, $specialization, $certifications]);
    }

    static function update(PDO $db, int $userId, ?string $bio, ?string $specialization, ?string $certifications) : void {
        $stmt = $db->prepare('UPDATE TrainerProfile SET bio = ?, specialization = ?, certifications = ? WHERE userId = ?');
        $stmt->execute([$bio, $specialization, $certifications, $userId]);
    }
}
?>
