<?php
$current_page = basename($_SERVER['PHP_SELF']);
?>

<aside class="sidebar">
    <nav>
        <a href="dashboard.php" class="nav-link <?php echo $current_page == 'dashboard.php' ? 'active' : ''; ?>">
            Dashboard
        </a>
        
        <a href="employees.php" class="nav-link <?php echo $current_page == 'employees.php' ? 'active' : ''; ?>">
            Employees
        </a>
        
        <?php if (hasRole(['admin', 'super_admin'])): ?>
        <a href="users.php" class="nav-link <?php echo $current_page == 'users.php' ? 'active' : ''; ?>">
            Users
        </a>
        <?php endif; ?>
        
        <?php if (hasRole(['admin', 'super_admin'])): ?>
        <a href="deleted_employees.php" class="nav-link <?php echo $current_page == 'deleted_employees.php' ? 'active' : ''; ?>">
            Deleted Employees
        </a>
        <?php endif; ?>
        
        <a href="password_logs.php" class="nav-link <?php echo $current_page == 'password_logs.php' ? 'active' : ''; ?>">
            Password Logs
        </a>
        
        <?php if (hasRole(['admin', 'super_admin'])): ?>
        <a href="activity_logs.php" class="nav-link <?php echo $current_page == 'activity_logs.php' ? 'active' : ''; ?>">
            Activity Logs
        </a>
        <?php endif; ?>
    </nav>
</aside>
