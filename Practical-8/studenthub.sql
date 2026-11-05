CREATE DATABASE IF NOT EXISTS studenthub_pr8
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;
USE studenthub_pr8;

CREATE TABLE IF NOT EXISTS students (
    student_id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    student_code VARCHAR(20) NOT NULL,
    full_name VARCHAR(100) NOT NULL,
    email VARCHAR(190) NOT NULL,
    mobile VARCHAR(15) NOT NULL,
    course_code VARCHAR(10) NOT NULL,
    year_of_study TINYINT UNSIGNED NOT NULL,
    gender VARCHAR(30) NOT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (student_id),
    UNIQUE KEY uq_students_code (student_code),
    UNIQUE KEY uq_students_email (email),
    CONSTRAINT chk_students_year CHECK (year_of_study BETWEEN 1 AND 6)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS events (
    event_id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    event_code VARCHAR(20) NOT NULL,
    title VARCHAR(150) NOT NULL,
    description TEXT NULL,
    category VARCHAR(50) NOT NULL,
    event_date DATE NOT NULL,
    venue VARCHAR(120) NOT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (event_id),
    UNIQUE KEY uq_events_code (event_code),
    KEY idx_events_date (event_date)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS registrations (
    registration_id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    student_id INT UNSIGNED NOT NULL,
    event_id INT UNSIGNED NOT NULL,
    registered_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    status ENUM('registered', 'cancelled', 'attended') NOT NULL DEFAULT 'registered',
    PRIMARY KEY (registration_id),
    UNIQUE KEY uq_student_event (student_id, event_id),
    KEY idx_registrations_event (event_id),
    CONSTRAINT fk_registrations_student
        FOREIGN KEY (student_id) REFERENCES students (student_id)
        ON UPDATE CASCADE ON DELETE CASCADE,
    CONSTRAINT fk_registrations_event
        FOREIGN KEY (event_id) REFERENCES events (event_id)
        ON UPDATE CASCADE ON DELETE CASCADE
) ENGINE=InnoDB;

INSERT IGNORE INTO students
    (student_code, full_name, email, mobile, course_code, year_of_study, gender)
VALUES
    ('SH101', 'Aarav Patel', 'aarav@example.test', '9876543210', 'CE', 1, 'Prefer not to say'),
    ('SH102', 'Mira Shah', 'mira@example.test', '9876543211', 'IT', 2, 'Female'),
    ('SH103', 'Rohan Joshi', 'rohan@example.test', '9876543212', 'CE', 3, 'Male'),
    ('SH104', 'Nisha Desai', 'nisha@example.test', '9876543213', 'EC', 1, 'Female'),
    ('SH105', 'Kabir Mehta', 'kabir@example.test', '9876543214', 'ME', 2, 'Male');

INSERT IGNORE INTO events
    (event_code, title, description, category, event_date, venue)
VALUES
    ('EVT101', 'Student Project Expo', 'Students present their semester projects.', 'Academic', '2027-01-15', 'Main Auditorium'),
    ('EVT102', 'Campus Cultural Evening', 'Music, dance, and student performances.', 'Cultural', '2027-01-22', 'College Ground'),
    ('EVT103', 'Web Development Workshop', 'A practical introduction to modern web development.', 'Technical', '2027-02-05', 'Computer Lab 2'),
    ('EVT104', 'Interdepartment Sports Meet', 'Team events for students across departments.', 'Sports', '2027-02-12', 'Sports Ground');

INSERT IGNORE INTO registrations (student_id, event_id, status)
SELECT s.student_id, e.event_id, 'registered'
FROM students AS s
CROSS JOIN events AS e
WHERE (s.student_code = 'SH101' AND e.event_code = 'EVT101')
   OR (s.student_code = 'SH101' AND e.event_code = 'EVT103')
   OR (s.student_code = 'SH102' AND e.event_code = 'EVT102')
   OR (s.student_code = 'SH103' AND e.event_code = 'EVT101')
   OR (s.student_code = 'SH104' AND e.event_code = 'EVT104');