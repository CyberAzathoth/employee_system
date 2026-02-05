<?php
// Database Configuration
$host = 'localhost';
$dbname = 'employee_management_db';
$username = 'root';
$password = '';

try {
    $conn = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}

// Start session
session_start();

// Check if user is logged in
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

// Check user role
function hasRole($roles) {
    if (!isLoggedIn()) return false;
    if (!is_array($roles)) $roles = [$roles];
    return in_array($_SESSION['role'], $roles);
}

// Require login
function requireLogin() {
    if (!isLoggedIn()) {
        header("Location: login.php");
        exit();
    }
}

// Require specific role
function requireRole($roles) {
    requireLogin();
    if (!hasRole($roles)) {
        header("Location: dashboard.php?error=insufficient_permissions");
        exit();
    }
}

// Log activity
function logActivity($conn, $userID, $action, $module, $target_id = null, $details = null) {
    $sql = "INSERT INTO ActivityLog (userID, action, module, target_id, details) 
            VALUES (:userID, :action, :module, :target_id, :details)";
    $stmt = $conn->prepare($sql);
    $stmt->execute([
        ':userID' => $userID,
        ':action' => $action,
        ':module' => $module,
        ':target_id' => $target_id,
        ':details' => $details
    ]);
}

// Log password change
function logPasswordChange($conn, $userID, $action, $changed_by, $details = null) {
    $ip = $_SERVER['REMOTE_ADDR'] ?? 'Unknown';
    $sql = "INSERT INTO PasswordLog (userID, action, changed_by, new_value, ip_address) 
            VALUES (:userID, :action, :changed_by, :details, :ip)";
    $stmt = $conn->prepare($sql);
    $stmt->execute([
        ':userID' => $userID,
        ':action' => $action,
        ':changed_by' => $changed_by,
        ':details' => $details,
        ':ip' => $ip
    ]);
}

// Log login attempt
function logLoginAttempt($conn, $username, $userID, $status) {
    $ip = $_SERVER['REMOTE_ADDR'] ?? 'Unknown';
    $sql = "INSERT INTO LoginAttemptLog (username, userID, status, ip_address) 
            VALUES (:username, :userID, :status, :ip)";
    $stmt = $conn->prepare($sql);
    $stmt->execute([
        ':username' => $username,
        ':userID' => $userID,
        ':status' => $status,
        ':ip' => $ip
    ]);
}

// Sanitize output
function e($data) {
    return htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
}
?>
