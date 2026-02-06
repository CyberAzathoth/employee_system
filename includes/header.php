<header class="header">
    <div class="header-content">
        <div class="logo">
            <h2>Employee Management System</h2>
        </div>
        <div class="user-info">
            <span class="user-name"><?php echo e($_SESSION['emp_name']); ?></span>
            <span class="user-role"><?php echo e(ucwords(str_replace('_', ' ', $_SESSION['role']))); ?></span>
            <a href="?logout=1" class="btn-logout">Logout</a>
        </div>
    </div>
</header>
