-- Student Portal Database
-- MySQL

CREATE DATABASE IF NOT EXISTS student_portal;
USE student_portal;

DROP TABLE IF EXISTS students;

CREATE TABLE students (
    id            INT AUTO_INCREMENT PRIMARY KEY,
    name          VARCHAR(100) NOT NULL,
    email         VARCHAR(150) NOT NULL UNIQUE,
    contact       VARCHAR(15)  NOT NULL,
    city          VARCHAR(50)  NOT NULL,
    created_at    TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Sample rows
INSERT INTO students (name, email, contact, city) VALUES
('Dhairy',  'Dhairy@example.com',  '9876543210', 'Morbi');