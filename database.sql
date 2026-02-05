-- Enhanced Employee Management Database
CREATE DATABASE IF NOT EXISTS employee_management_db;
USE employee_management_db;

-- Employee Table (with soft delete)
CREATE TABLE IF NOT EXISTS Employee (
    empID INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(100) NOT NULL,
    position VARCHAR(100) NOT NULL,
    birthdate DATE NOT NULL,
    is_deleted TINYINT(1) DEFAULT 0,
    deleted_at DATETIME NULL,
    deleted_by INT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- User Table with Roles
CREATE TABLE IF NOT EXISTS User (
    userID INT PRIMARY KEY AUTO_INCREMENT,
    empID INT NOT NULL,
    username VARCHAR(50) NOT NULL UNIQUE,
    passwordHash VARCHAR(255) NOT NULL,
    role ENUM('manager', 'admin', 'super_admin') NOT NULL DEFAULT 'manager',
    is_active TINYINT(1) DEFAULT 1,
    failed_attempts INT DEFAULT 0,
    last_failed_attempt DATETIME NULL,
    lockout_until DATETIME NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    created_by INT NULL,
    FOREIGN KEY (empID) REFERENCES Employee(empID),
    FOREIGN KEY (created_by) REFERENCES User(userID) ON DELETE SET NULL
);

-- Password Log Table
CREATE TABLE IF NOT EXISTS PasswordLog (
    logID INT PRIMARY KEY AUTO_INCREMENT,
    userID INT NOT NULL,
    action VARCHAR(50) NOT NULL,
    changed_by INT NULL,
    old_value VARCHAR(255) NULL,
    new_value VARCHAR(255) NULL,
    ip_address VARCHAR(45) NULL,
    timestamp DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (userID) REFERENCES User(userID),
    FOREIGN KEY (changed_by) REFERENCES User(userID) ON DELETE SET NULL
);

-- Login Attempt Log
CREATE TABLE IF NOT EXISTS LoginAttemptLog (
    attemptID INT PRIMARY KEY AUTO_INCREMENT,
    username VARCHAR(50) NOT NULL,
    userID INT NULL,
    status ENUM('success', 'failed', 'locked') NOT NULL,
    ip_address VARCHAR(45) NULL,
    attempt_time DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (userID) REFERENCES User(userID) ON DELETE SET NULL
);

-- Activity Log
CREATE TABLE IF NOT EXISTS ActivityLog (
    activityID INT PRIMARY KEY AUTO_INCREMENT,
    userID INT NOT NULL,
    action VARCHAR(100) NOT NULL,
    module VARCHAR(50) NOT NULL,
    target_id INT NULL,
    details TEXT NULL,
    timestamp DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (userID) REFERENCES User(userID)
);

-- Insert Default Super Admin Employee
INSERT INTO Employee (name, position, birthdate) VALUES
('System Administrator', 'Super Admin', '1990-01-01');

-- Insert Super Admin User (password: admin123)
INSERT INTO User (empID, username, passwordHash, role, created_by) VALUES
(1, 'superadmin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'super_admin', NULL);

-- Insert Sample Employees
INSERT INTO Employee (name, position, birthdate) VALUES
('John Smith', 'Manager', '1985-03-15'),
('Sarah Johnson', 'Developer', '1990-07-22'),
('Mike Davis', 'Designer', '1988-11-30');

-- Insert Sample Users with different roles
-- Password for all: password123
INSERT INTO User (empID, username, passwordHash, role, created_by) VALUES
(2, 'manager1', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'manager', 1),
(3, 'admin1', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin', 1),
(4, 'testuser', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'super_admin', 1);
