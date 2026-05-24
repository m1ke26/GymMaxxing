<?php
declare(strict_types = 1);

class MemberProfile {
    public int $id;
    public int $userId;
    public string $tier;

    public function __construct(int $id, int $userId, string $tier) {
        $this->id = $id;
        $this->userId = $userId;
        $this->tier = $tier;
    }

    static function getByUserId(PDO $db, int $userId) : ?MemberProfile {
        $stmt = $db->prepare('SELECT * FROM MemberProfile WHERE userId = ?');
        $stmt->execute([$userId]);
        $m = $stmt->fetch();
        if (!$m) return null;
        return new MemberProfile($m['id'], $m['userId'], $m['tier']);
    }

    static function create(PDO $db, int $userId, string $tier) : void {
        $stmt = $db->prepare('INSERT INTO MemberProfile (userId, tier) VALUES (?, ?)');
        $stmt->execute([$userId, $tier]);
    }

    static function updateTier(PDO $db, int $userId, string $tier) : void {
        $stmt = $db->prepare('UPDATE MemberProfile SET tier = ? WHERE userId = ?');
        $stmt->execute([$tier, $userId]);
    }
}
?>
