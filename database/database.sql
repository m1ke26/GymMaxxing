PRAGMA foreign_keys = ON;

-- =====================
-- TABLES
-- =====================

CREATE TABLE IF NOT EXISTS User (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    name TEXT NOT NULL,
    username TEXT NOT NULL UNIQUE,
    email TEXT NOT NULL UNIQUE,
    password TEXT NOT NULL,
    phone TEXT,
    photo TEXT,
    role TEXT NOT NULL CHECK(role IN ('member', 'trainer', 'admin')),
    active INTEGER NOT NULL DEFAULT 1
);

CREATE TABLE IF NOT EXISTS TrainerProfile (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    userId INTEGER NOT NULL UNIQUE,
    bio TEXT,
    specialization TEXT,
    certifications TEXT,
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
    type TEXT NOT NULL,
    description TEXT,
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

CREATE TABLE IF NOT EXISTS Equipment (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    name TEXT NOT NULL,
    type TEXT NOT NULL,
    status TEXT NOT NULL CHECK(status IN ('available', 'in_use', 'maintenance')) DEFAULT 'available',
    description TEXT
);

CREATE TABLE IF NOT EXISTS Review (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    userId INTEGER NOT NULL,
    classId INTEGER NOT NULL,
    rating INTEGER NOT NULL CHECK(rating BETWEEN 1 AND 5),
    comment TEXT,
    createdAt TEXT NOT NULL DEFAULT (datetime('now')),
    UNIQUE(userId, classId),
    FOREIGN KEY (userId) REFERENCES User(id) ON DELETE CASCADE,
    FOREIGN KEY (classId) REFERENCES Class(id) ON DELETE CASCADE
);
