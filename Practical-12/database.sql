CREATE DATABASE IF NOT EXISTS studenthub_pr12
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

USE studenthub_pr12;

CREATE TABLE IF NOT EXISTS events (
    event_id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(150) NOT NULL,
    category VARCHAR(60) NOT NULL,
    description TEXT NOT NULL,
    event_date DATE NOT NULL,
    venue VARCHAR(150) NOT NULL,
    status ENUM('open', 'closed', 'draft') NOT NULL DEFAULT 'open',
    poster_file VARCHAR(80) NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_events_date (event_date),
    INDEX idx_events_category_status (category, status)
) ENGINE=InnoDB;

INSERT INTO events (title, category, description, event_date, venue, status)
SELECT * FROM (
    SELECT 'Student Project Expo' AS title, 'Academic' AS category,
           'Students present their semester projects.' AS description,
           '2027-01-15' AS event_date, 'Main Auditorium' AS venue, 'open' AS status
    UNION ALL SELECT 'Campus Cultural Evening', 'Cultural',
           'Music, dance, and student performances.', '2027-01-22', 'College Ground', 'open'
    UNION ALL SELECT 'Web Development Workshop', 'Technical',
           'A practical introduction to modern web development.', '2027-02-05', 'Computer Lab 2', 'open'
    UNION ALL SELECT 'Interdepartment Sports Meet', 'Sports',
           'Team events for students across departments.', '2027-02-12', 'Sports Ground', 'closed'
    UNION ALL SELECT 'Career Guidance Seminar', 'Academic',
           'Career planning and interview preparation.', '2027-02-20', 'Seminar Hall', 'draft'
) AS seed
WHERE NOT EXISTS (SELECT 1 FROM events LIMIT 1);
