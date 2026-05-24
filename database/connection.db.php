<?php
declare(strict_types = 1);

function getDatabaseConnection() : PDO {
    $dbPath = __DIR__ . '/database.db';
    $needsInit = !file_exists($dbPath);

    $db = new PDO('sqlite:' . $dbPath);
    $db->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $db->exec('PRAGMA foreign_keys = ON');

    if ($needsInit) {
        $schema = file_get_contents(__DIR__ . '/database.sql');
        $db->exec($schema);
        seedDatabase($db);
    }

    return $db;
}

function seedDatabase(PDO $db) : void {
    $pw = password_hash('1234', PASSWORD_DEFAULT);

    $stmt = $db->prepare('INSERT INTO User (name, username, email, password, phone, role) VALUES (?, ?, ?, ?, ?, ?)');
    $stmt->execute(['Admin Zeus', 'adminzeus', 'admin@gymmaxxing.com', $pw, '912000000', 'admin']);
    $stmt->execute(['Apollo Trainer', 'apollo', 'apollo@gymmaxxing.com', $pw, '913000001', 'trainer']);
    $stmt->execute(['Hercules Trainer', 'hercules', 'hercules@gymmaxxing.com', $pw, '913000002', 'trainer']);
    $stmt->execute(['Athena Trainer', 'athena', 'athena@gymmaxxing.com', $pw, '913000003', 'trainer']);
    $stmt->execute(['João Citizen', 'joao', 'joao@gmail.com', $pw, '914000001', 'member']);
    $stmt->execute(['Maria Olympian', 'maria', 'maria@gmail.com', $pw, '914000002', 'member']);
    $stmt->execute(['Pedro Zeus', 'pedro', 'pedro@gmail.com', $pw, '914000003', 'member']);

    $stmt = $db->prepare('INSERT INTO TrainerProfile (userId, bio, specialization, certifications) VALUES (?, ?, ?, ?)');
    $stmt->execute([2, 'Specialist in functional training & strength.', 'Functional Training', 'ACE Certified Personal Trainer, CrossFit Level 2']);
    $stmt->execute([3, 'Focused in cardio & endurance.', 'Cardio & Endurance', 'NASM Certified, Spinning Instructor']);
    $stmt->execute([4, 'Expert in flexibility, yoga and mindfulness.', 'Yoga & Flexibility', 'Yoga Alliance RYT-500, Pilates Certified']);

    $stmt = $db->prepare('INSERT INTO MemberProfile (userId, tier) VALUES (?, ?)');
    $stmt->execute([5, 'citizen']);
    $stmt->execute([6, 'olympian']);
    $stmt->execute([7, 'zeus']);

    $stmt = $db->prepare('INSERT INTO Class (title, type, description, image, schedule, capacity, trainerId) VALUES (?, ?, ?, ?, ?, ?, ?)');
    $stmt->execute(['Outdoor Training', 'outdoor', 'High-intensity outdoor workout combining bodyweight exercises with natural terrain.', 'homepage_outdoor.png', 'Monday 08:00', 15, 2]);
    $stmt->execute(['Indoor Fitness', 'indoor', 'Full-body indoor training session with state-of-the-art equipment.', 'homepage_indoor.png', 'Tuesday 10:00', 20, 2]);
    $stmt->execute(['Wellness & Yoga', 'wellness', 'Relaxing yoga session focused on flexibility, balance and mindfulness.', 'homepage_wellness.png', 'Wednesday 09:00', 12, 4]);
    $stmt->execute(['Nutrition Workshop', 'nutrition', 'Learn how to fuel your body for peak performance.', 'homepage_nutrition.png', 'Thursday 18:00', 10, 3]);
    $stmt->execute(['Flexibility & Pilates', 'wellness', 'Improve your core strength and flexibility with guided pilates exercises.', 'homepage_wellness.png', 'Friday 17:00', 15, 4]);

    $stmt = $db->prepare('INSERT INTO Enrollment (userId, classId) VALUES (?, ?)');
    $stmt->execute([5, 1]);
    $stmt->execute([6, 1]);
    $stmt->execute([6, 3]);
    $stmt->execute([7, 2]);
    $stmt->execute([7, 3]);
    $stmt->execute([7, 4]);

    $stmt = $db->prepare('INSERT INTO Equipment (name, type, status, description) VALUES (?, ?, ?, ?)');
    $stmt->execute(['Treadmill 1', 'cardio', 'available', 'Professional treadmill with incline settings']);
    $stmt->execute(['Treadmill 2', 'cardio', 'available', 'Professional treadmill with incline settings']);
    $stmt->execute(['Stationary Bike 1', 'cardio', 'available', 'Adjustable resistance stationary bike']);
    $stmt->execute(['Stationary Bike 2', 'cardio', 'in_use', 'Adjustable resistance stationary bike']);
    $stmt->execute(['Bench Press', 'strength', 'available', 'Flat bench press station']);
    $stmt->execute(['Squat Rack', 'strength', 'available', 'Power rack with safety bars']);
    $stmt->execute(['Dumbbells Set', 'strength', 'available', 'Dumbbell set ranging from 2kg to 40kg']);
    $stmt->execute(['Rowing Machine', 'cardio', 'maintenance', 'Hydraulic rowing machine — under repair']);
    $stmt->execute(['Yoga Mats', 'flexibility', 'available', 'Set of 15 yoga mats']);
    $stmt->execute(['Cable Machine', 'strength', 'available', 'Dual pulley cable machine']);

    $stmt = $db->prepare('INSERT INTO Review (userId, classId, rating, comment) VALUES (?, ?, ?, ?)');
    $stmt->execute([6, 1, 5, 'Amazing outdoor session! Apollo really pushes you to your limits.']);
    $stmt->execute([7, 2, 4, 'Great indoor workout. Equipment is top-notch.']);
    $stmt->execute([7, 3, 5, 'Best yoga class I have ever attended. Very relaxing.']);
}
?>
