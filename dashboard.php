<?php
include 'db_connect.php';
requireLogin();

// Handle logout
if (isset($_GET['logout'])) {
    logActivity($conn, $_SESSION['user_id'], 'Logout', 'Authentication', null, 'User logged out');
    session_destroy();
    header("Location: login.php");
    exit();
}

// Get statistics
$stats = [
    'total_employees' => 0,
    'active_users' => 0,
    'total_activities' => 0
];

$sql = "SELECT COUNT(*) as count FROM Employee WHERE is_deleted = 0";
$stats['total_employees'] = $conn->query($sql)->fetch()['count'];

$sql = "SELECT COUNT(*) as count FROM User WHERE is_active = 1";
$stats['active_users'] = $conn->query($sql)->fetch()['count'];

$sql = "SELECT COUNT(*) as count FROM ActivityLog WHERE DATE(timestamp) = CURDATE()";
$stats['total_activities'] = $conn->query($sql)->fetch()['count'];
?>

<!DOCTYPE html>
<html>
<head>
    <title>Dashboard - Employee Management</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <?php include 'includes/header.php'; ?>
    
    <div class="main-container">
        <?php include 'includes/sidebar.php'; ?>
        
        <div class="content">
            <div class="page-header">
                <h1>Dashboard</h1>
                <p>Welcome back, <?php echo e($_SESSION['emp_name']); ?>!</p>
            </div>
            
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-icon" style="background: #4CAF50;">👥</div>
                    <div class="stat-info">
                        <h3><?php echo $stats['total_employees']; ?></h3>
                        <p>Total Employees</p>
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon" style="background: #2196F3;">👤</div>
                    <div class="stat-info">
                        <h3><?php echo $stats['active_users']; ?></h3>
                        <p>Active Users</p>
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon" style="background: #FF9800;">📊</div>
                    <div class="stat-info">
                        <h3><?php echo $stats['total_activities']; ?></h3>
                        <p>Today's Activities</p>
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon" style="background: #9C27B0;">🔑</div>
                    <div class="stat-info">
                        <h3><?php echo strtoupper($_SESSION['role']); ?></h3>
                        <p>Your Role</p>
                    </div>
                </div>
            </div>
            
            <div class="info-box">
                <h2>System Information</h2>
                <p><strong>Logged in as:</strong> <?php echo e($_SESSION['username']); ?></p>
                <p><strong>Role:</strong> <?php echo e(ucwords(str_replace('_', ' ', $_SESSION['role']))); ?></p>
                <p><strong>Employee ID:</strong> <?php echo e($_SESSION['emp_id']); ?></p>
            </div>
            
            <div class="permissions-info">
                <h2>Your Permissions</h2>
                <?php if ($_SESSION['role'] == 'super_admin'): ?>
                    <ul>
                        <li>✅ View all employees</li>
                        <li>✅ Add new employees</li>
                        <li>✅ Edit employee information</li>
                        <li>✅ Delete employees</li>
                        <li>✅ Manage all users</li>
                        <li>✅ View all logs</li>
                        <li>✅ Access deleted records</li>
                    </ul>
                <?php elseif ($_SESSION['role'] == 'admin'): ?>
                    <ul>
                        <li>✅ View all employees</li>
                        <li>✅ Add new employees</li>
                        <li>✅ Edit employee information</li>
                        <li>✅ Delete employees</li>
                        <li>✅ Manage users</li>
                        <li>✅ View logs</li>
                        <li>✅ Access deleted records</li>
                    </ul>
                <?php else: ?>
                    <ul>
                        <li>✅ View all employees</li>
                        <li>✅ Add new employees</li>
                        <li>✅ Edit employee information</li>
                        <li>❌ Cannot delete employees</li>
                        <li>❌ Cannot manage users</li>
                        <li>❌ Limited log access</li>
                    </ul>
                <?php endif; ?>
            </div>
        </div>
    </div>
</body>
</html>
