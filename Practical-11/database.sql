CREATE DATABASE IF NOT EXISTS studenthub_pr11
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

USE studenthub_pr11;

CREATE TABLE IF NOT EXISTS students (
    student_id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    student_code VARCHAR(20) NOT NULL UNIQUE,
    full_name VARCHAR(100) NOT NULL,
    email VARCHAR(190) NOT NULL UNIQUE,
    course VARCHAR(50) NOT NULL,
    year_of_study TINYINT UNSIGNED NOT NULL,
    gender ENUM('Female', 'Male', 'Other', 'Prefer not to say') NOT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_students_name (full_name),
    INDEX idx_students_course (course)
) ENGINE=InnoDB;

INSERT IGNORE INTO students
    (student_code, full_name, email, course, year_of_study, gender)
VALUES
    ('STU001', 'Aarav Patel', 'aarav.patel@example.test', 'Computer Engineering', 1, 'Male'),
    ('STU002', 'Mira Shah', 'mira.shah@example.test', 'Information Technology', 2, 'Female'),
    ('STU003', 'Kabir Mehta', 'kabir.mehta@example.test', 'Computer Engineering', 3, 'Male'),
    ('STU004', 'Diya Joshi', 'diya.joshi@example.test', 'Data Science', 1, 'Female'),
    ('STU005', 'Rohan Desai', 'rohan.desai@example.test', 'Information Technology', 4, 'Male'),
    ('STU006', 'Anaya Trivedi', 'anaya.trivedi@example.test', 'Computer Engineering', 2, 'Female'),
    ('STU007', 'Vivaan Shah', 'vivaan.shah@example.test', 'Data Science', 3, 'Male'),
    ('STU008', 'Isha Patel', 'isha.patel@example.test', 'Information Technology', 1, 'Female'),
    ('STU009', 'Arjun Mehta', 'arjun.mehta@example.test', 'Computer Engineering', 4, 'Male'),
    ('STU010', 'Kiara Bhatt', 'kiara.bhatt@example.test', 'Data Science', 2, 'Female'),
    ('STU011', 'Reyansh Shah', 'reyansh.shah@example.test', 'Information Technology', 3, 'Male'),
    ('STU012', 'Sara Desai', 'sara.desai@example.test', 'Computer Engineering', 1, 'Female'),
    ('STU013', 'Ayaan Joshi', 'ayaan.joshi@example.test', 'Data Science', 4, 'Male'),
    ('STU014', 'Myra Patel', 'myra.patel@example.test', 'Information Technology', 2, 'Female'),
    ('STU015', 'Advik Mehta', 'advik.mehta@example.test', 'Computer Engineering', 3, 'Male'),
    ('STU016', 'Pari Shah', 'pari.shah@example.test', 'Data Science', 1, 'Female'),
    ('STU017', 'Krish Trivedi', 'krish.trivedi@example.test', 'Information Technology', 4, 'Male'),
    ('STU018', 'Aadhya Desai', 'aadhya.desai@example.test', 'Computer Engineering', 2, 'Female'),
    ('STU019', 'Dhruv Bhatt', 'dhruv.bhatt@example.test', 'Data Science', 3, 'Male'),
    ('STU020', 'Navya Joshi', 'navya.joshi@example.test', 'Information Technology', 1, 'Female');
