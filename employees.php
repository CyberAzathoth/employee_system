<?php
include 'db_connect.php';
requireLogin();

$success = '';
$error = '';

// Handle Add Employee
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action']) && $_POST['action'] == 'add') {
    $name = trim($_POST['name']);
    $position = trim($_POST['position']);
    $birthdate = $_POST['birthdate'];
    
    $sql = "INSERT INTO Employee (name, position, birthdate) VALUES (:name, :position, :birthdate)";
    $stmt = $conn->prepare($sql);
    
    if ($stmt->execute([':name' => $name, ':position' => $position, ':birthdate' => $birthdate])) {
        $empID = $conn->lastInsertId();
        logActivity($conn, $_SESSION['user_id'], 'Create Employee', 'Employee', $empID, "Added: $name");
        $success = "Employee added successfully!";
    } else {
        $error = "Error adding employee.";
    }
}

// Handle Edit Employee
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action']) && $_POST['action'] == 'edit') {
    $empID = $_POST['empID'];
    $name = trim($_POST['name']);
    $position = trim($_POST['position']);
    $birthdate = $_POST['birthdate'];
    
    $sql = "UPDATE Employee SET name = :name, position = :position, birthdate = :birthdate 
            WHERE empID = :empID AND is_deleted = 0";
    $stmt = $conn->prepare($sql);
    
    if ($stmt->execute([':name' => $name, ':position' => $position, ':birthdate' => $birthdate, ':empID' => $empID])) {
        logActivity($conn, $_SESSION['user_id'], 'Update Employee', 'Employee', $empID, "Updated: $name");
        $success = "Employee updated successfully!";
    } else {
        $error = "Error updating employee.";
    }
}

// Handle Delete Employee (Soft Delete - Admin and Super Admin only)
if (isset($_GET['delete']) && hasRole(['admin', 'super_admin'])) {
    $empID = $_GET['delete'];
    
    // Get employee name before delete
    $sql = "SELECT name FROM Employee WHERE empID = :empID";
    $stmt = $conn->prepare($sql);
    $stmt->execute([':empID' => $empID]);
    $emp = $stmt->fetch(PDO::FETCH_ASSOC);
    
    $sql = "UPDATE Employee SET is_deleted = 1, deleted_at = NOW(), deleted_by = :deleted_by 
            WHERE empID = :empID";
    $stmt = $conn->prepare($sql);
    
    if ($stmt->execute([':empID' => $empID, ':deleted_by' => $_SESSION['user_id']])) {
        logActivity($conn, $_SESSION['user_id'], 'Delete Employee', 'Employee', $empID, "Deleted: " . $emp['name']);
        $success = "Employee moved to deleted records.";
    } else {
        $error = "Error deleting employee.";
    }
}

// Get all active employees
$sql = "SELECT * FROM Employee WHERE is_deleted = 0 ORDER BY empID DESC";
$employees = $conn->query($sql)->fetchAll(PDO::FETCH_ASSOC);

// Get employee for editing
$edit_employee = null;
if (isset($_GET['edit'])) {
    $sql = "SELECT * FROM Employee WHERE empID = :empID AND is_deleted = 0";
    $stmt = $conn->prepare($sql);
    $stmt->execute([':empID' => $_GET['edit']]);
    $edit_employee = $stmt->fetch(PDO::FETCH_ASSOC);
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Employees - Employee Management</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <?php include 'includes/header.php'; ?>
    
    <div class="main-container">
        <?php include 'includes/sidebar.php'; ?>
        
        <div class="content">
            <div class="page-header">
                <h1>Employee Management</h1>
                <p>Manage employee records</p>
            </div>
            
            <?php if ($success): ?>
                <div class="alert alert-success"><?php echo e($success); ?></div>
            <?php endif; ?>
            
            <?php if ($error): ?>
                <div class="alert alert-error"><?php echo e($error); ?></div>
            <?php endif; ?>
            
            <!-- Add/Edit Form -->
            <div class="form-container">
                <h2><?php echo $edit_employee ? 'Edit Employee' : 'Add New Employee'; ?></h2>
                <form method="POST" action="">
                    <input type="hidden" name="action" value="<?php echo $edit_employee ? 'edit' : 'add'; ?>">
                    <?php if ($edit_employee): ?>
                        <input type="hidden" name="empID" value="<?php echo $edit_employee['empID']; ?>">
                    <?php endif; ?>
                    
                    <div class="form-group">
                        <label for="name">Employee Name:</label>
                        <input type="text" id="name" name="name" required 
                               value="<?php echo $edit_employee ? e($edit_employee['name']) : ''; ?>">
                    </div>
                    
                    <div class="form-group">
                        <label for="position">Position:</label>
                        <input type="text" id="position" name="position" required 
                               value="<?php echo $edit_employee ? e($edit_employee['position']) : ''; ?>">
                    </div>
                    
                    <div class="form-group">
                        <label for="birthdate">Birth Date:</label>
                        <input type="date" id="birthdate" name="birthdate" required 
                               value="<?php echo $edit_employee ? e($edit_employee['birthdate']) : ''; ?>">
                    </div>
                    
                    <button type="submit" class="btn btn-primary">
                        <?php echo $edit_employee ? 'Update Employee' : 'Add Employee'; ?>
                    </button>
                    
                    <?php if ($edit_employee): ?>
                        <a href="employees.php" class="btn btn-warning">Cancel</a>
                    <?php endif; ?>
                </form>
            </div>
            
            <!-- Employee List -->
            <div class="table-container">
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Name</th>
                            <th>Position</th>
                            <th>Birth Date</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($employees as $emp): ?>
                        <tr>
                            <td><?php echo e($emp['empID']); ?></td>
                            <td><?php echo e($emp['name']); ?></td>
                            <td><?php echo e($emp['position']); ?></td>
                            <td><?php echo e($emp['birthdate']); ?></td>
                            <td>
                                <div class="action-buttons">
                                    <a href="?edit=<?php echo $emp['empID']; ?>" class="btn btn-sm btn-primary">Edit</a>
                                    
                                    <?php if (hasRole(['admin', 'super_admin'])): ?>
                                        <a href="?delete=<?php echo $emp['empID']; ?>" 
                                           class="btn btn-sm btn-danger"
                                           onclick="return confirm('Move this employee to deleted records?')">
                                           Delete
                                        </a>
                                    <?php else: ?>
                                        <span class="badge badge-warning">No Delete Permission</span>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</body>
</html>
