PRAGMA foreign_keys = ON;

-- =====================
-- TABLES
-- =====================

CREATE TABLE IF NOT EXISTS User (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    name TEXT NOT NULL,
    email TEXT NOT NULL UNIQUE,
    password TEXT NOT NULL,
    phone TEXT,
    role TEXT NOT NULL CHECK(role IN ('member', 'trainer', 'admin'))
);

CREATE TABLE IF NOT EXISTS TrainerProfile (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    userId INTEGER NOT NULL UNIQUE,
    bio TEXT,
    specialization TEXT,
    FOREIGN KEY (userId) REFERENCES User(id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS MemberProfile (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    userId INTEGER NOT NULL UNIQUE,
    tier TEXT NOT NULL CHECK(tier IN ('citizen', 'olympian', 'zeus')),
    FOREIGN KEY (userId) REFERENCES User(id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS Class (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    title TEXT NOT NULL,
    image TEXT,
    schedule TEXT NOT NULL,
    capacity INTEGER NOT NULL,
    trainerId INTEGER NOT NULL,
    FOREIGN KEY (trainerId) REFERENCES User(id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS Enrollment (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    userId INTEGER NOT NULL,
    classId INTEGER NOT NULL,
    UNIQUE(userId, classId),
    FOREIGN KEY (userId) REFERENCES User(id) ON DELETE CASCADE,
    FOREIGN KEY (classId) REFERENCES Class(id) ON DELETE CASCADE
);

-- =====================
-- SAMPLE DATA
-- =====================

-- password de todos: 1234 (em produção estaria em hash)
INSERT INTO User (name, email, password, phone, role) VALUES
    ('Admin Zeus', 'admin@gymmaxxing.com', '1234', '912000000', 'admin'),
    ('Apollo Trainer', 'apollo@gymmaxxing.com', '1234', '913000001', 'trainer'),
    ('Hercules Trainer', 'hercules@gymmaxxing.com', '1234', '913000002', 'trainer'),
    ('João Citizen', 'joao@gmail.com', '1234', '914000001', 'member'),
    ('Maria Olympian', 'maria@gmail.com', '1234', '914000002', 'member'),
    ('Pedro Zeus', 'pedro@gmail.com', '1234', '914000003', 'member');

INSERT INTO TrainerProfile (userId, bio, specialization) VALUES
    (2, 'Specialist in functional training & strength.', 'Functional Training'),
    (3, 'Focused in cardio & endurance.', 'Cardio & Endurance');

INSERT INTO MemberProfile (userId, tier) VALUES
    (4, 'citizen'),
    (5, 'olympian'),
    (6, 'zeus');

INSERT INTO Class (title, image, schedule, capacity, trainerId) VALUES
    ('Outdoor Training', 'homepage_outdoor.png', 'Monday 08:00', 15, 2),
    ('Indoor Fitness', 'homepage_indoor.png', 'Tuesday 10:00', 20, 2),
    ('Wellness & Yoga', 'homepage_wellness.png', 'Wednesday 09:00', 12, 3),
    ('Nutrition Workshop', 'homepage_nutrition.png', 'Thursday 18:00', 10, 3);

INSERT INTO Enrollment (userId, classId) VALUES
    (4, 1),
    (5, 1),
    (5, 3),
    (6, 2),
    (6, 3),
    (6, 4);