CREATE DATABASE IF NOT EXISTS studenthub_pr10
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

USE studenthub_pr10;

CREATE TABLE IF NOT EXISTS users (
    user_id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    full_name VARCHAR(100) NOT NULL,
    username VARCHAR(30) NOT NULL UNIQUE,
    email VARCHAR(190) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    role ENUM('admin', 'student') NOT NULL DEFAULT 'student',
    last_login DATETIME NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS remember_tokens (
    selector CHAR(32) PRIMARY KEY,
    user_id INT UNSIGNED NOT NULL,
    token_hash CHAR(64) NOT NULL,
    expires_at DATETIME NOT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_remember_user FOREIGN KEY (user_id)
        REFERENCES users(user_id) ON DELETE CASCADE ON UPDATE CASCADE,
    INDEX idx_remember_expiry (expires_at)
) ENGINE=InnoDB;

INSERT IGNORE INTO users
    (full_name, username, email, password_hash, role)
VALUES
    ('StudentHub Administrator', 'admin', 'admin@studenthub.test',
     '$2y$10$VNo2DWf53F7XqbhoUbhHhOftcPL7uwqL2PL8CRavBH.uCeqp38hh.', 'admin'),
    ('Sample Student', 'student', 'student@studenthub.test',
     '$2y$10$ACUJzj2Bi1W5HGCze4s2tujkSqpa7dEYdvphrdZ72bCj.h9sZMOy6', 'student');
