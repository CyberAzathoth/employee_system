<?php
include 'db_connect.php';
requireRole(['admin', 'super_admin']);

// Get activity logs
$sql = "SELECT al.*, u.username, e.name as emp_name
        FROM ActivityLog al
        JOIN User u ON al.userID = u.userID
        JOIN Employee e ON u.empID = e.empID
        ORDER BY al.timestamp DESC
        LIMIT 200";
$activities = $conn->query($sql)->fetchAll(PDO::FETCH_ASSOC);

// Get statistics
$sql = "SELECT 
            COUNT(*) as total,
            COUNT(CASE WHEN DATE(timestamp) = CURDATE() THEN 1 END) as today,
            COUNT(CASE WHEN module = 'Employee' THEN 1 END) as employee_actions,
            COUNT(CASE WHEN module = 'User' THEN 1 END) as user_actions
        FROM ActivityLog";
$stats = $conn->query($sql)->fetch(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Activity Logs - Employee Management</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <?php include 'includes/header.php'; ?>
    
    <div class="main-container">
        <?php include 'includes/sidebar.php'; ?>
        
        <div class="content">
            <div class="page-header">
                <h1>Activity Logs</h1>
                <p>Track all system activities and changes</p>
            </div>
            
            <!-- Statistics -->
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-icon" style="background: #2196F3;">📊</div>
                    <div class="stat-info">
                        <h3><?php echo $stats['total']; ?></h3>
                        <p>Total Activities</p>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon" style="background: #4CAF50;">📅</div>
                    <div class="stat-info">
                        <h3><?php echo $stats['today']; ?></h3>
                        <p>Today's Activities</p>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon" style="background: #FF9800;">👥</div>
                    <div class="stat-info">
                        <h3><?php echo $stats['employee_actions']; ?></h3>
                        <p>Employee Actions</p>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon" style="background: #9C27B0;">👤</div>
                    <div class="stat-info">
                        <h3><?php echo $stats['user_actions']; ?></h3>
                        <p>User Actions</p>
                    </div>
                </div>
            </div>
            
            <!-- Activity Log Table -->
            <div class="table-container">
                <h2 style="padding: 20px; margin: 0;">Activity History</h2>
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>User</th>
                            <th>Employee</th>
                            <th>Action</th>
                            <th>Module</th>
                            <th>Target ID</th>
                            <th>Details</th>
                            <th>Timestamp</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($activities)): ?>
                            <tr>
                                <td colspan="8" style="text-align: center; padding: 30px; color: #999;">
                                    No activities found
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($activities as $activity): ?>
                            <tr>
                                <td><?php echo e($activity['activityID']); ?></td>
                                <td><?php echo e($activity['username']); ?></td>
                                <td><?php echo e($activity['emp_name']); ?></td>
                                <td>
                                    <span class="badge 
                                        <?php 
                                        if (strpos($activity['action'], 'Create') !== false) echo 'badge-success';
                                        elseif (strpos($activity['action'], 'Update') !== false) echo 'badge-info';
                                        elseif (strpos($activity['action'], 'Delete') !== false) echo 'badge-danger';
                                        else echo 'badge-warning';
                                        ?>">
                                        <?php echo e($activity['action']); ?>
                                    </span>
                                </td>
                                <td><?php echo e($activity['module']); ?></td>
                                <td><?php echo e($activity['target_id'] ?? 'N/A'); ?></td>
                                <td><?php echo e($activity['details'] ?? 'No details'); ?></td>
                                <td><?php echo e(date('M d, Y H:i:s', strtotime($activity['timestamp']))); ?></td>
                            </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
            
            <div class="info-box" style="margin-top: 20px;">
                <h2>About Activity Logs</h2>
                <p>This section tracks all user activities in the system including:</p>
                <ul>
                    <li>✓ Employee creation, updates, and deletions</li>
                    <li>✓ User account management</li>
                    <li>✓ Password changes</li>
                    <li>✓ Login and logout activities</li>
                    <li>✓ Timestamps and user information for audit trails</li>
                </ul>
            </div>
        </div>
    </div>
</body>
</html>
