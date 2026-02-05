<?php
include 'db_connect.php';
requireRole(['admin', 'super_admin']);

$success = '';
$error = '';

// Handle Add User
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action']) && $_POST['action'] == 'add') {
    $empID = $_POST['empID'];
    $username = trim($_POST['username']);
    $password = $_POST['password'];
    $role = $_POST['role'];
    
    $passwordHash = password_hash($password, PASSWORD_DEFAULT);
    
    $sql = "INSERT INTO User (empID, username, passwordHash, role, created_by) 
            VALUES (:empID, :username, :passwordHash, :role, :created_by)";
    $stmt = $conn->prepare($sql);
    
    try {
        if ($stmt->execute([
            ':empID' => $empID,
            ':username' => $username,
            ':passwordHash' => $passwordHash,
            ':role' => $role,
            ':created_by' => $_SESSION['user_id']
        ])) {
            $userID = $conn->lastInsertId();
            logPasswordChange($conn, $userID, 'Account Created', $_SESSION['user_id'], "Role: $role");
            logActivity($conn, $_SESSION['user_id'], 'Create User', 'User', $userID, "Created user: $username");
            $success = "User account created successfully!";
        }
    } catch(PDOException $e) {
        if ($e->getCode() == 23000) {
            $error = "Username already exists!";
        } else {
            $error = "Error creating user account.";
        }
    }
}

// Handle Change Password
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action']) && $_POST['action'] == 'change_password') {
    $userID = $_POST['userID'];
    $new_password = $_POST['new_password'];
    
    $passwordHash = password_hash($new_password, PASSWORD_DEFAULT);
    
    $sql = "UPDATE User SET passwordHash = :passwordHash WHERE userID = :userID";
    $stmt = $conn->prepare($sql);
    
    if ($stmt->execute([':passwordHash' => $passwordHash, ':userID' => $userID])) {
        logPasswordChange($conn, $userID, 'Password Changed', $_SESSION['user_id'], 'Admin changed password');
        logActivity($conn, $_SESSION['user_id'], 'Change Password', 'User', $userID, "Changed password for user ID: $userID");
        $success = "Password changed successfully!";
    } else {
        $error = "Error changing password.";
    }
}

// Get all active employees for dropdown
$sql = "SELECT empID, name FROM Employee WHERE is_deleted = 0 ORDER BY name";
$employees = $conn->query($sql)->fetchAll(PDO::FETCH_ASSOC);

// Get all users with employee info
$sql = "SELECT u.*, e.name as emp_name, creator.username as created_by_username
        FROM User u
        JOIN Employee e ON u.empID = e.empID
        LEFT JOIN User creator ON u.created_by = creator.userID
        WHERE u.is_active = 1
        ORDER BY u.userID DESC";
$users = $conn->query($sql)->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Users - Employee Management</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <?php include 'includes/header.php'; ?>
    
    <div class="main-container">
        <?php include 'includes/sidebar.php'; ?>
        
        <div class="content">
            <div class="page-header">
                <h1>User Management</h1>
                <p>Manage user accounts and permissions</p>
            </div>
            
            <?php if ($success): ?>
                <div class="alert alert-success"><?php echo e($success); ?></div>
            <?php endif; ?>
            
            <?php if ($error): ?>
                <div class="alert alert-error"><?php echo e($error); ?></div>
            <?php endif; ?>
            
            <!-- Add User Form -->
            <div class="form-container">
                <h2>Create New User Account</h2>
                <form method="POST" action="">
                    <input type="hidden" name="action" value="add">
                    
                    <div class="form-group">
                        <label for="empID">Select Employee:</label>
                        <select id="empID" name="empID" required>
                            <option value="">-- Choose Employee --</option>
                            <?php foreach ($employees as $emp): ?>
                                <option value="<?php echo $emp['empID']; ?>">
                                    <?php echo e($emp['name']); ?> (ID: <?php echo $emp['empID']; ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="username">Username:</label>
                        <input type="text" id="username" name="username" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="password">Password:</label>
                        <input type="password" id="password" name="password" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="role">Role:</label>
                        <select id="role" name="role" required>
                            <option value="manager">Manager (View, Add, Edit only)</option>
                            <option value="admin">Admin (All permissions except super admin)</option>
                            <?php if ($_SESSION['role'] == 'super_admin'): ?>
                                <option value="super_admin">Super Admin (Full access)</option>
                            <?php endif; ?>
                        </select>
                    </div>
                    
                    <button type="submit" class="btn btn-primary">Create User</button>
                </form>
            </div>
            
            <!-- Users List -->
            <div class="table-container">
                <table>
                    <thead>
                        <tr>
                            <th>User ID</th>
                            <th>Employee</th>
                            <th>Username</th>
                            <th>Role</th>
                            <th>Created By</th>
                            <th>Created At</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($users as $user): ?>
                        <tr>
                            <td><?php echo e($user['userID']); ?></td>
                            <td><?php echo e($user['emp_name']); ?></td>
                            <td><?php echo e($user['username']); ?></td>
                            <td>
                                <span class="badge badge-info">
                                    <?php echo e(ucwords(str_replace('_', ' ', $user['role']))); ?>
                                </span>
                            </td>
                            <td><?php echo $user['created_by_username'] ? e($user['created_by_username']) : 'System'; ?></td>
                            <td><?php echo e(date('M d, Y', strtotime($user['created_at']))); ?></td>
                            <td>
                                <button onclick="showChangePassword(<?php echo $user['userID']; ?>, '<?php echo e($user['username']); ?>')" 
                                        class="btn btn-sm btn-warning">
                                    Change Password
                                </button>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    
    <!-- Change Password Modal -->
    <div id="passwordModal" style="display:none; position:fixed; top:0; left:0; right:0; bottom:0; background:rgba(0,0,0,0.5); z-index:9999;">
        <div style="background:white; max-width:400px; margin:100px auto; padding:30px; border-radius:10px;">
            <h2>Change Password</h2>
            <form method="POST" action="">
                <input type="hidden" name="action" value="change_password">
                <input type="hidden" name="userID" id="change_userID">
                
                <div class="form-group">
                    <label>Username:</label>
                    <input type="text" id="change_username" readonly style="background:#f0f0f0;">
                </div>
                
                <div class="form-group">
                    <label for="new_password">New Password:</label>
                    <input type="password" id="new_password" name="new_password" required>
                </div>
                
                <button type="submit" class="btn btn-primary">Change Password</button>
                <button type="button" onclick="closePasswordModal()" class="btn btn-warning">Cancel</button>
            </form>
        </div>
    </div>
    
    <script>
        function showChangePassword(userID, username) {
            document.getElementById('change_userID').value = userID;
            document.getElementById('change_username').value = username;
            document.getElementById('passwordModal').style.display = 'block';
        }
        
        function closePasswordModal() {
            document.getElementById('passwordModal').style.display = 'none';
        }
    </script>
</body>
</html>
