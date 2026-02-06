<?php
$host = 'localhost';
$username = 'root';
$password = '';

try {
    // Connect without database
    $conn = new PDO("mysql:host=$host", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "<h1>Employee Management System - Database Setup</h1>";
    echo "<div style='font-family: Arial; max-width: 800px; margin: 50px auto; padding: 20px; background: #f5f6fa;'>";
    
    // Create database
    $conn->exec("CREATE DATABASE IF NOT EXISTS employee_management_db");
    echo "<p style='color: green;'>✓ Database created successfully!</p>";
    
    // Select database
    $conn->exec("USE employee_management_db");
    
    // Drop existing tables to ensure clean setup (in reverse order of dependencies)
    $conn->exec("DROP TABLE IF EXISTS ActivityLog");
    $conn->exec("DROP TABLE IF EXISTS LoginAttemptLog");
    $conn->exec("DROP TABLE IF EXISTS PasswordLog");
    $conn->exec("DROP TABLE IF EXISTS User");
    $conn->exec("DROP TABLE IF EXISTS Employee");
    
    // Create Employee table
    $sql = "CREATE TABLE IF NOT EXISTS Employee (
        empID INT PRIMARY KEY AUTO_INCREMENT,
        name VARCHAR(100) NOT NULL,
        position VARCHAR(100) NOT NULL,
        birthdate DATE NOT NULL,
        is_deleted TINYINT(1) DEFAULT 0,
        deleted_at DATETIME NULL,
        deleted_by INT NULL,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    )";
    $conn->exec($sql);
    echo "<p style='color: green;'>✓ Employee table created!</p>";
    
    // Create User table
    $sql = "CREATE TABLE IF NOT EXISTS User (
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
    )";
    $conn->exec($sql);
    echo "<p style='color: green;'>✓ User table created!</p>";
    
    // Create PasswordLog table
    $sql = "CREATE TABLE IF NOT EXISTS PasswordLog (
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
    )";
    $conn->exec($sql);
    echo "<p style='color: green;'>✓ PasswordLog table created!</p>";
    
    // Create LoginAttemptLog table
    $sql = "CREATE TABLE IF NOT EXISTS LoginAttemptLog (
        attemptID INT PRIMARY KEY AUTO_INCREMENT,
        username VARCHAR(50) NOT NULL,
        userID INT NULL,
        status ENUM('success', 'failed', 'locked') NOT NULL,
        ip_address VARCHAR(45) NULL,
        attempt_time DATETIME DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (userID) REFERENCES User(userID) ON DELETE SET NULL
    )";
    $conn->exec($sql);
    echo "<p style='color: green;'>✓ LoginAttemptLog table created!</p>";
    
    // Create ActivityLog table
    $sql = "CREATE TABLE IF NOT EXISTS ActivityLog (
        activityID INT PRIMARY KEY AUTO_INCREMENT,
        userID INT NOT NULL,
        action VARCHAR(100) NOT NULL,
        module VARCHAR(50) NOT NULL,
        target_id INT NULL,
        details TEXT NULL,
        timestamp DATETIME DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (userID) REFERENCES User(userID)
    )";
    $conn->exec($sql);
    echo "<p style='color: green;'>✓ ActivityLog table created!</p>";
    
    // Insert default employees
    $sql = "INSERT INTO Employee (name, position, birthdate) VALUES
            ('System Administrator', 'Super Admin', '1990-01-01'),
            ('John Smith', 'Manager', '1985-03-15'),
            ('Sarah Johnson', 'Developer', '1990-07-22'),
            ('Mike Davis', 'Designer', '1988-11-30')";
    $conn->exec($sql);
    echo "<p style='color: green;'>✓ Sample employees inserted!</p>";
    
    // Hash passwords
    $password_admin = password_hash('admin123', PASSWORD_DEFAULT);
    $password_default = password_hash('password123', PASSWORD_DEFAULT);
    
    // Insert users
    $sql = "INSERT INTO User (empID, username, passwordHash, role, created_by) VALUES
            (1, 'superadmin', :pass1, 'super_admin', NULL),
            (2, 'manager1', :pass2, 'manager', 1),
            (3, 'admin1', :pass3, 'admin', 1);
    $stmt = $conn->prepare($sql);
    $stmt->execute([
        ':pass1' => $password_admin,
        ':pass2' => $password_default,
        ':pass3' => $password_default
    ]);
    echo "<p style='color: green;'>✓ Sample users created!</p>";
    
    echo "<div style='background: white; padding: 20px; border-radius: 10px; margin-top: 20px;'>";
    echo "<h2>Setup Complete!</h2>";
    echo "<h3>Test Accounts:</h3>";
    echo "<table style='width: 100%; border-collapse: collapse;'>";
    echo "<tr style='background: #667eea; color: white;'><th style='padding: 10px;'>Role</th><th style='padding: 10px;'>Username</th><th style='padding: 10px;'>Password</th><th style='padding: 10px;'>Permissions</th></tr>";
    echo "<tr><td style='padding: 10px; border: 1px solid #ddd;'>Super Admin</td><td style='padding: 10px; border: 1px solid #ddd;'><strong>superadmin</strong></td><td style='padding: 10px; border: 1px solid #ddd;'><strong>admin123</strong></td><td style='padding: 10px; border: 1px solid #ddd;'>Full Access</td></tr>";
    echo "<tr><td style='padding: 10px; border: 1px solid #ddd;'>Admin</td><td style='padding: 10px; border: 1px solid #ddd;'><strong>admin1</strong></td><td style='padding: 10px; border: 1px solid #ddd;'><strong>password123</strong></td><td style='padding: 10px; border: 1px solid #ddd;'>All except Super Admin</td></tr>";
    echo "<tr><td style='padding: 10px; border: 1px solid #ddd;'>Manager</td><td style='padding: 10px; border: 1px solid #ddd;'><strong>manager1</strong></td><td style='padding: 10px; border: 1px solid #ddd;'><strong>password123</strong></td><td style='padding: 10px; border: 1px solid #ddd;'>View, Add, Edit only</td></tr>";
    echo "<tr><td style='padding: 10px; border: 1px solid #ddd;'>Test Super Admin</td><td style='padding: 10px; border: 1px solid #ddd;'><strong>testuser</strong></td><td style='padding: 10px; border: 1px solid #ddd;'><strong>password123</strong></td><td style='padding: 10px; border: 1px solid #ddd;'>Full Access</td></tr>";
    echo "</table>";
    echo "<br><a href='login.php' style='background: #667eea; color: white; padding: 15px 30px; text-decoration: none; border-radius: 5px; display: inline-block; margin-top: 20px;'>Go to Login Page</a>";
    echo "</div>";
    
    echo "</div>";
    
} catch(PDOException $e) {
    echo "<p style='color: red;'>Error: " . $e->getMessage() . "</p>";
}
?>
