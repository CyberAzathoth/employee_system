<?php
include 'db_connect.php';

// Redirect if already logged in
if (isLoggedIn()) {
    header("Location: dashboard.php");
    exit();
}

$error = '';
$warning = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = trim($_POST['username']);
    $password = $_POST['password'];
    
    // Get user
    $sql = "SELECT * FROM User WHERE username = :username AND is_active = 1";
    $stmt = $conn->prepare($sql);
    $stmt->execute([':username' => $username]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($user) {
        // Check if account is locked
        if ($user['lockout_until'] && new DateTime() < new DateTime($user['lockout_until'])) {
            $lockout = new DateTime($user['lockout_until']);
            $now = new DateTime();
            $diff = $now->diff($lockout);
            $seconds = $diff->s + ($diff->i * 60);
            
            $error = "Account locked. Please wait {$seconds} seconds.";
            logLoginAttempt($conn, $username, $user['userID'], 'locked');
        } else {
            // Verify password
            if (password_verify($password, $user['passwordHash'])) {
                // Reset failed attempts
                $sql = "UPDATE User SET failed_attempts = 0, lockout_until = NULL WHERE userID = :userID";
                $stmt = $conn->prepare($sql);
                $stmt->execute([':userID' => $user['userID']]);
                
                // Create session
                $_SESSION['user_id'] = $user['userID'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['role'] = $user['role'];
                $_SESSION['emp_id'] = $user['empID'];
                
                // Get employee name
                $sql = "SELECT name FROM Employee WHERE empID = :empID";
                $stmt = $conn->prepare($sql);
                $stmt->execute([':empID' => $user['empID']]);
                $emp = $stmt->fetch(PDO::FETCH_ASSOC);
                $_SESSION['emp_name'] = $emp['name'];
                
                // Log successful login
                logLoginAttempt($conn, $username, $user['userID'], 'success');
                logActivity($conn, $user['userID'], 'Login', 'Authentication', null, 'User logged in successfully');
                
                header("Location: dashboard.php");
                exit();
            } else {
                // Increment failed attempts
                $failed_attempts = $user['failed_attempts'] + 1;
                
                // Check if should lock account
                if ($failed_attempts >= 5) {
                    $lockout_until = date('Y-m-d H:i:s', strtotime('+30 seconds'));
                    $sql = "UPDATE User SET failed_attempts = :attempts, last_failed_attempt = NOW(), lockout_until = :lockout 
                            WHERE userID = :userID";
                    $stmt = $conn->prepare($sql);
                    $stmt->execute([
                        ':attempts' => $failed_attempts,
                        ':lockout' => $lockout_until,
                        ':userID' => $user['userID']
                    ]);
                    $error = "Too many failed attempts. Account locked for 30 seconds.";
                } else {
                    $sql = "UPDATE User SET failed_attempts = :attempts, last_failed_attempt = NOW() WHERE userID = :userID";
                    $stmt = $conn->prepare($sql);
                    $stmt->execute([
                        ':attempts' => $failed_attempts,
                        ':userID' => $user['userID']
                    ]);
                    
                    $remaining = 5 - $failed_attempts;
                    $error = "Invalid password. {$remaining} attempts remaining.";
                    
                    // Show warning at 3 attempts
                    if ($failed_attempts >= 3) {
                        $warning = "Warning: 2 more failed attempts will lock your account for 30 seconds.";
                    }
                }
                
                logLoginAttempt($conn, $username, $user['userID'], 'failed');
            }
        }
    } else {
        $error = "Invalid username or password.";
        logLoginAttempt($conn, $username, null, 'failed');
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Login - Employee Management System</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .login-container {
            background: white;
            padding: 40px;
            border-radius: 10px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.2);
            width: 100%;
            max-width: 400px;
        }
        .login-header {
            text-align: center;
            margin-bottom: 30px;
        }
        .login-header h1 {
            color: #333;
            font-size: 28px;
            margin-bottom: 10px;
        }
        .login-header p {
            color: #666;
            font-size: 14px;
        }
        .form-group {
            margin-bottom: 20px;
        }
        label {
            display: block;
            margin-bottom: 8px;
            color: #333;
            font-weight: 500;
        }
        input[type="text"],
        input[type="password"] {
            width: 100%;
            padding: 12px;
            border: 2px solid #e0e0e0;
            border-radius: 5px;
            font-size: 14px;
            transition: border-color 0.3s;
        }
        input[type="text"]:focus,
        input[type="password"]:focus {
            outline: none;
            border-color: #667eea;
        }
        .btn-login {
            width: 100%;
            padding: 12px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            border-radius: 5px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: transform 0.2s;
        }
        .btn-login:hover {
            transform: translateY(-2px);
        }
        .error {
            background-color: #fee;
            color: #c33;
            padding: 12px;
            border-radius: 5px;
            margin-bottom: 20px;
            border-left: 4px solid #c33;
        }
        .warning {
            background-color: #fff3cd;
            color: #856404;
            padding: 12px;
            border-radius: 5px;
            margin-bottom: 20px;
            border-left: 4px solid #ffc107;
        }
        .test-creds {
            margin-top: 30px;
            padding: 15px;
            background: #f8f9fa;
            border-radius: 5px;
            border-left: 4px solid #667eea;
        }
        .test-creds h4 {
            color: #333;
            margin-bottom: 10px;
            font-size: 14px;
        }
        .test-creds p {
            color: #666;
            font-size: 13px;
            margin: 5px 0;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="login-header">
            <h1>Employee Management</h1>
            <p>Please login to continue</p>
        </div>
        
        <?php if ($error): ?>
            <div class="error"><?php echo e($error); ?></div>
        <?php endif; ?>
        
        <?php if ($warning): ?>
            <div class="warning">⚠️ <?php echo e($warning); ?></div>
        <?php endif; ?>
        
        <form method="POST" action="">
            <div class="form-group">
                <label for="username">Username</label>
                <input type="text" id="username" name="username" required autofocus>
            </div>
            
            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" required>
            </div>
            
            <button type="submit" class="btn-login">Login</button>
        </form>
        
        <div class="test-creds">
            <h4>Test Accounts:</h4>
            <p><strong>Super Admin:</strong> superadmin / admin123</p>
            <p><strong>Admin:</strong> admin1 / password123</p>
            <p><strong>Manager:</strong> manager1 / password123</p>
        </div>
    </div>
</body>
</html>
