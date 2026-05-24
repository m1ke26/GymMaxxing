<?php
declare(strict_types = 1);

class Enrollment {
    public int $id;
    public int $userId;
    public int $classId;

    public function __construct(int $id, int $userId, int $classId) {
        $this->id = $id;
        $this->userId = $userId;
        $this->classId = $classId;
    }

    static function enroll(PDO $db, int $userId, int $classId) : void {
        $stmt = $db->prepare('INSERT INTO Enrollment (userId, classId) VALUES (?, ?)');
        $stmt->execute([$userId, $classId]);
    }

    static function unenroll(PDO $db, int $userId, int $classId) : void {
        $stmt = $db->prepare('DELETE FROM Enrollment WHERE userId = ? AND classId = ?');
        $stmt->execute([$userId, $classId]);
    }

    static function isEnrolled(PDO $db, int $userId, int $classId) : bool {
        $stmt = $db->prepare('SELECT COUNT(*) FROM Enrollment WHERE userId = ? AND classId = ?');
        $stmt->execute([$userId, $classId]);
        return (int) $stmt->fetchColumn() > 0;
    }

    static function getUserEnrollments(PDO $db, int $userId) : array {
        $stmt = $db->prepare('SELECT * FROM Enrollment WHERE userId = ?');
        $stmt->execute([$userId]);
        $enrollments = [];
        while ($e = $stmt->fetch())
            $enrollments[] = new Enrollment($e['id'], $e['userId'], $e['classId']);
        return $enrollments;
    }

    static function getClassEnrollments(PDO $db, int $classId) : array {
        $stmt = $db->prepare('SELECT * FROM Enrollment WHERE classId = ?');
        $stmt->execute([$classId]);
        $enrollments = [];
        while ($e = $stmt->fetch())
            $enrollments[] = new Enrollment($e['id'], $e['userId'], $e['classId']);
        return $enrollments;
    }

    static function getEnrollmentCount(PDO $db, int $classId) : int {
        $stmt = $db->prepare('SELECT COUNT(*) FROM Enrollment WHERE classId = ?');
        $stmt->execute([$classId]);
        return (int) $stmt->fetchColumn();
    }
}
?>
