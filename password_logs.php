<?php
include 'db_connect.php';
requireLogin();

// Get password change logs
$sql = "SELECT pl.*, u.username, changer.username as changed_by_username, e.name as emp_name
        FROM PasswordLog pl
        JOIN User u ON pl.userID = u.userID
        JOIN Employee e ON u.empID = e.empID
        LEFT JOIN User changer ON pl.changed_by = changer.userID
        ORDER BY pl.timestamp DESC
        LIMIT 100";
$password_logs = $conn->query($sql)->fetchAll(PDO::FETCH_ASSOC);

// Get login attempt logs
$sql = "SELECT la.*, u.username as actual_username, e.name as emp_name
        FROM LoginAttemptLog la
        LEFT JOIN User u ON la.userID = u.userID
        LEFT JOIN Employee e ON u.empID = e.empID
        ORDER BY la.attempt_time DESC
        LIMIT 100";
$login_attempts = $conn->query($sql)->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Password Logs - Employee Management</title>
    <link rel="stylesheet" href="style.css">
    <style>
        .tabs {
            display: flex;
            gap: 10px;
            margin-bottom: 20px;
        }
        .tab-btn {
            padding: 12px 24px;
            background: white;
            border: 2px solid #e0e0e0;
            border-radius: 5px;
            cursor: pointer;
            transition: all 0.3s;
        }
        .tab-btn.active {
            background: #667eea;
            color: white;
            border-color: #667eea;
        }
        .tab-content {
            display: none;
        }
        .tab-content.active {
            display: block;
        }
    </style>
</head>
<body>
    <?php include 'includes/header.php'; ?>
    
    <div class="main-container">
        <?php include 'includes/sidebar.php'; ?>
        
        <div class="content">
            <div class="page-header">
                <h1>Password & Authentication Logs</h1>
                <p>View password changes and login attempts</p>
            </div>
            
            <div class="tabs">
                <button class="tab-btn active" onclick="showTab('password-changes')">Password Changes</button>
                <button class="tab-btn" onclick="showTab('login-attempts')">Login Attempts</button>
            </div>
            
            <!-- Password Changes Tab -->
            <div id="password-changes" class="tab-content active">
                <div class="table-container">
                    <h2 style="padding: 20px; margin: 0;">Password Change History</h2>
                    <table>
                        <thead>
                            <tr>
                                <th>Log ID</th>
                                <th>Employee Name</th>
                                <th>Username</th>
                                <th>Action</th>
                                <th>Changed By</th>
                                <th>IP Address</th>
                                <th>Timestamp</th>
                                <th>Details</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($password_logs)): ?>
                                <tr>
                                    <td colspan="8" style="text-align: center; padding: 30px; color: #999;">
                                        No password change logs found
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($password_logs as $log): ?>
                                <tr>
                                    <td><?php echo e($log['logID']); ?></td>
                                    <td><?php echo e($log['emp_name']); ?></td>
                                    <td><?php echo e($log['username']); ?></td>
                                    <td>
                                        <span class="badge badge-warning">
                                            <?php echo e($log['action']); ?>
                                        </span>
                                    </td>
                                    <td><?php echo $log['changed_by_username'] ? e($log['changed_by_username']) : 'System'; ?></td>
                                    <td><?php echo e($log['ip_address']); ?></td>
                                    <td><?php echo e(date('M d, Y H:i:s', strtotime($log['timestamp']))); ?></td>
                                    <td><?php echo e($log['new_value'] ?? 'N/A'); ?></td>
                                </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
            
            <!-- Login Attempts Tab -->
            <div id="login-attempts" class="tab-content">
                <div class="table-container">
                    <h2 style="padding: 20px; margin: 0;">Login Attempt History</h2>
                    <table>
                        <thead>
                            <tr>
                                <th>Attempt ID</th>
                                <th>Employee Name</th>
                                <th>Username</th>
                                <th>Status</th>
                                <th>IP Address</th>
                                <th>Attempt Time</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($login_attempts)): ?>
                                <tr>
                                    <td colspan="6" style="text-align: center; padding: 30px; color: #999;">
                                        No login attempts found
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($login_attempts as $attempt): ?>
                                <tr>
                                    <td><?php echo e($attempt['attemptID']); ?></td>
                                    <td><?php echo $attempt['emp_name'] ? e($attempt['emp_name']) : 'Unknown'; ?></td>
                                    <td><?php echo e($attempt['username']); ?></td>
                                    <td>
                                        <?php if ($attempt['status'] == 'success'): ?>
                                            <span class="badge badge-success">Success</span>
                                        <?php elseif ($attempt['status'] == 'locked'): ?>
                                            <span class="badge badge-danger">Locked</span>
                                        <?php else: ?>
                                            <span class="badge badge-danger">Failed</span>
                                        <?php endif; ?>
                                    </td>
                                    <td><?php echo e($attempt['ip_address']); ?></td>
                                    <td><?php echo e(date('M d, Y H:i:s', strtotime($attempt['attempt_time']))); ?></td>
                                </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
                
                <!-- Login Attempt Statistics -->
                <?php
                // Get stats for current user if manager
                if ($_SESSION['role'] == 'manager') {
                    $sql = "SELECT 
                                COUNT(*) as total_attempts,
                                SUM(CASE WHEN status = 'success' THEN 1 ELSE 0 END) as successful,
                                SUM(CASE WHEN status = 'failed' THEN 1 ELSE 0 END) as failed,
                                SUM(CASE WHEN status = 'locked' THEN 1 ELSE 0 END) as locked
                            FROM LoginAttemptLog 
                            WHERE userID = :userID";
                    $stmt = $conn->prepare($sql);
                    $stmt->execute([':userID' => $_SESSION['user_id']]);
                    $stats = $stmt->fetch(PDO::FETCH_ASSOC);
                ?>
                    <div class="info-box" style="margin-top: 20px;">
                        <h2>Your Login Statistics</h2>
                        <div class="stats-grid">
                            <div class="stat-card">
                                <div class="stat-icon" style="background: #2196F3;">📊</div>
                                <div class="stat-info">
                                    <h3><?php echo $stats['total_attempts']; ?></h3>
                                    <p>Total Attempts</p>
                                </div>
                            </div>
                            <div class="stat-card">
                                <div class="stat-icon" style="background: #4CAF50;">✓</div>
                                <div class="stat-info">
                                    <h3><?php echo $stats['successful']; ?></h3>
                                    <p>Successful</p>
                                </div>
                            </div>
                            <div class="stat-card">
                                <div class="stat-icon" style="background: #f44336;">✗</div>
                                <div class="stat-info">
                                    <h3><?php echo $stats['failed']; ?></h3>
                                    <p>Failed</p>
                                </div>
                            </div>
                            <div class="stat-card">
                                <div class="stat-icon" style="background: #FF9800;">🔒</div>
                                <div class="stat-info">
                                    <h3><?php echo $stats['locked']; ?></h3>
                                    <p>Locked</p>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php } ?>
            </div>
        </div>
    </div>
    
    <script>
        function showTab(tabName) {
            // Hide all tabs
            document.querySelectorAll('.tab-content').forEach(tab => {
                tab.classList.remove('active');
            });
            
            // Remove active from all buttons
            document.querySelectorAll('.tab-btn').forEach(btn => {
                btn.classList.remove('active');
            });
            
            // Show selected tab
            document.getElementById(tabName).classList.add('active');
            
            // Activate clicked button
            event.target.classList.add('active');
        }
    </script>
</body>
</html>
