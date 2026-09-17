-- OJS-style Journal Management System - Database Schema
-- Import this file in phpMyAdmin or via: mysql -u root -p < schema.sql

CREATE DATABASE IF NOT EXISTS ojs_system;
USE ojs_system;

-- ============================
-- USERS (Author, Reviewer, Editor)
-- ============================
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(150) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role ENUM('author','reviewer','editor') NOT NULL DEFAULT 'author',
    affiliation VARCHAR(150) DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- ============================
-- SUBMISSIONS (papers submitted by authors)
-- ============================
CREATE TABLE IF NOT EXISTS submissions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    author_id INT NOT NULL,
    title VARCHAR(255) NOT NULL,
    abstract TEXT NOT NULL,
    keywords VARCHAR(255) DEFAULT NULL,
    file_path VARCHAR(255) NOT NULL,
    status ENUM('submitted','under_review','revision_required','accepted','rejected','published') NOT NULL DEFAULT 'submitted',
    submitted_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (author_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ============================
-- REVIEWS (reviewer assignments + feedback)
-- ============================
CREATE TABLE IF NOT EXISTS reviews (
    id INT AUTO_INCREMENT PRIMARY KEY,
    submission_id INT NOT NULL,
    reviewer_id INT NOT NULL,
    comments TEXT DEFAULT NULL,
    recommendation ENUM('accept','minor_revision','major_revision','reject') DEFAULT NULL,
    status ENUM('assigned','completed') NOT NULL DEFAULT 'assigned',
    assigned_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    reviewed_at TIMESTAMP NULL DEFAULT NULL,
    FOREIGN KEY (submission_id) REFERENCES submissions(id) ON DELETE CASCADE,
    FOREIGN KEY (reviewer_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ============================
-- ISSUES (published volumes/issues)
-- ============================
CREATE TABLE IF NOT EXISTS issues (
    id INT AUTO_INCREMENT PRIMARY KEY,
    volume INT NOT NULL,
    issue_number INT NOT NULL,
    title VARCHAR(255) DEFAULT NULL,
    published_date DATE DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- ============================
-- PUBLISHED PAPERS (accepted submissions linked to an issue)
-- ============================
CREATE TABLE IF NOT EXISTS published_papers (
    id INT AUTO_INCREMENT PRIMARY KEY,
    submission_id INT NOT NULL,
    issue_id INT NOT NULL,
    published_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (submission_id) REFERENCES submissions(id) ON DELETE CASCADE,
    FOREIGN KEY (issue_id) REFERENCES issues(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ============================
-- SEED DATA
-- Default editor login -> email: editor@ojs.com | password: editor123
-- (password below is the bcrypt hash of "editor123")
-- ============================
INSERT INTO users (name, email, password, role) VALUES
('Chief Editor', 'editor@ojs.com', '$2y$12$ICeE6U6cCk4DDpS7hoZ/6O4LKJ3zzAxlSHoPVv.y.5I3/gSWZuec2', 'editor');
