<?php
include 'db_connect.php';
requireRole(['admin', 'super_admin']);

// Get deleted employees
$sql = "SELECT e.*, deleter.username as deleted_by_username
        FROM Employee e
        LEFT JOIN User deleter ON e.deleted_by = deleter.userID
        WHERE e.is_deleted = 1
        ORDER BY e.deleted_at DESC";
$deleted_employees = $conn->query($sql)->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Deleted Employees - Employee Management</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <?php include 'includes/header.php'; ?>
    
    <div class="main-container">
        <?php include 'includes/sidebar.php'; ?>
        
        <div class="content">
            <div class="page-header">
                <h1>Deleted Employees</h1>
                <p>View deleted employee records (No restore available - Logs only)</p>
            </div>
            
            <div class="alert alert-info">
                <strong>ℹ️ Information:</strong> This section serves as a log of deleted employees. 
                Records cannot be restored. This is for audit and tracking purposes only.
            </div>
            
            <div class="table-container">
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Name</th>
                            <th>Position</th>
                            <th>Birth Date</th>
                            <th>Deleted By</th>
                            <th>Deleted At</th>
                            <th>Days Since Deletion</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($deleted_employees)): ?>
                            <tr>
                                <td colspan="7" style="text-align: center; padding: 30px; color: #999;">
                                    No deleted employees found
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($deleted_employees as $emp): ?>
                            <tr>
                                <td><?php echo e($emp['empID']); ?></td>
                                <td><?php echo e($emp['name']); ?></td>
                                <td><?php echo e($emp['position']); ?></td>
                                <td><?php echo e($emp['birthdate']); ?></td>
                                <td>
                                    <?php echo $emp['deleted_by_username'] ? e($emp['deleted_by_username']) : 'Unknown'; ?>
                                </td>
                                <td><?php echo e(date('M d, Y H:i:s', strtotime($emp['deleted_at']))); ?></td>
                                <td>
                                    <?php
                                    $deleted = new DateTime($emp['deleted_at']);
                                    $now = new DateTime();
                                    $diff = $now->diff($deleted);
                                    echo $diff->days . ' day(s)';
                                    ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
            
            <div class="info-box" style="margin-top: 20px;">
                <h2>About Deleted Employees</h2>
                <ul>
                    <li>✓ Deleted records are permanently stored for audit purposes</li>
                    <li>✓ Records show who deleted the employee and when</li>
                    <li>✓ No restore functionality - deletion is permanent</li>
                    <li>✓ This serves as a historical log for compliance</li>
                    <li>✓ Only Admins and Super Admins can view this section</li>
                </ul>
            </div>
        </div>
    </div>
</body>
</html>
