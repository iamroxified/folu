<?php
// Start session
session_start();

// Include database configuration and functions
require '../../db/config.php';
require '../../db/functions.php';

// Check if user is logged in
if (!isset($_SESSION['adid'])) {
    header('location:../../login.php');
    exit;
}

// Get teacher ID
$teacher_id = $_GET['id'] ?? null;
if (!$teacher_id || !is_numeric($teacher_id)) {
    header('location:list.php');
    exit;
}

// Fetch teacher data
$sql = "SELECT t.*, u.username, u.status as user_status 
        FROM teachers t 
        JOIN users u ON t.user_id = u.id 
        WHERE t.id = ?";
$stmt = $pdo->prepare($sql);
$stmt->execute([$teacher_id]);
$teacher = $stmt->fetch();

if (!$teacher) {
    header('location:list.php');
    exit;
}

// Update Teacher
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validate inputs
    $firstName = validate($_POST['first_name'] ?? '');
    $lastName = validate($_POST['last_name'] ?? '');
    $email = validate($_POST['email'] ?? '');
    $phone = validate($_POST['phone'] ?? '');
    $qualification = validate($_POST['qualification'] ?? '');
    $username = validate($_POST['username'] ?? '');
    $status = validate($_POST['status'] ?? 'active');
    
    // Validate email
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Invalid email format';
    }

    // Check for missing required fields
    if (empty($firstName) || empty($lastName) || empty($email) || empty($username)) {
        $error = 'Please fill all required fields';
    }

    // Check if username or email already exists (excluding current teacher)
    if (!isset($error)) {
        $stmt = $pdo->prepare('SELECT COUNT(*) FROM users WHERE (username = ? OR email = ?) AND id != ?');
        $stmt->execute([$username, $email, $teacher['user_id']]);
        if ($stmt->fetchColumn() > 0) {
            $error = 'Username or email already exists';
        }
    }

    if (!isset($error)) {
        try {
            // Start transaction
            $pdo->beginTransaction();
            
            // Update users table
            $stmt = $pdo->prepare('UPDATE users SET username = ?, email = ?, status = ? WHERE id = ?');
            $stmt->execute([$username, $email, $status, $teacher['user_id']]);
            
            // Update teachers table
            $stmt = $pdo->prepare('UPDATE teachers SET first_name = ?, last_name = ?, email = ?, phone = ?, qualification = ? WHERE id = ?');
            $stmt->execute([$firstName, $lastName, $email, $phone, $qualification, $teacher_id]);
            
            // Update password if provided
            if (!empty($_POST['password'])) {
                $hashedPassword = password_hash($_POST['password'], PASSWORD_DEFAULT);
                $stmt = $pdo->prepare('UPDATE users SET password = ? WHERE id = ?');
                $stmt->execute([$hashedPassword, $teacher['user_id']]);
            }
            
            // Commit transaction
            $pdo->commit();
            
            $success = 'Teacher information updated successfully';
            
            // Refresh teacher data
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$teacher_id]);
            $teacher = $stmt->fetch();
            
        } catch (Exception $e) {
            // Rollback transaction
            $pdo->rollback();
            $error = 'Failed to update teacher: ' . $e->getMessage();
        }
    }
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <title>Edit Teacher</title>
    <?php include('../../nav/links.php'); ?>
</head>
<body>
    <div class="wrapper">
        <?php include('../../nav/sidebar.php'); ?>

        <div class="main-panel">
            <?php include('../../nav/header.php'); ?>
            <div class="container">
                <div class="page-inner">
                    <div class="d-flex align-items-left flex-column flex-md-row">
                        <h2 class="text-dark pb-2 fw-bold">Edit Teacher</h2>
                        <div class="ml-md-auto py-2 py-md-0">
                            <a href="list.php" class="btn btn-secondary">Back to List</a>
                            <a href="view.php?id=<?php echo $teacher_id; ?>" class="btn btn-info">View Profile</a>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-8">
                            <div class="card">
                                <div class="card-header">
                                    <div class="card-title">
                                        Edit Teacher: <?php echo htmlspecialchars($teacher['first_name'] . ' ' . $teacher['last_name']); ?>
                                    </div>
                                    <div class="card-category">
                                        Employee ID: <strong><?php echo htmlspecialchars($teacher['employee_id']); ?></strong>
                                    </div>
                                </div>
                                <div class="card-body">
                                    <?php if (isset($error)): ?>
                                        <div class="alert alert-danger">
                                            <?php echo $error; ?>
                                        </div>
                                    <?php endif; ?>
                                    <?php if (isset($success)): ?>
                                        <div class="alert alert-success">
                                            <?php echo $success; ?>
                                        </div>
                                    <?php endif; ?>
                                    <form method="POST" action="">
                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label for="first_name">First Name <span class="text-danger">*</span></label>
                                                    <input type="text" class="form-control" name="first_name" id="first_name" required 
                                                           value="<?php echo htmlspecialchars($teacher['first_name']); ?>">
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label for="last_name">Last Name <span class="text-danger">*</span></label>
                                                    <input type="text" class="form-control" name="last_name" id="last_name" required 
                                                           value="<?php echo htmlspecialchars($teacher['last_name']); ?>">
                                                </div>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label for="username">Username <span class="text-danger">*</span></label>
                                                    <input type="text" class="form-control" name="username" id="username" required 
                                                           value="<?php echo htmlspecialchars($teacher['username']); ?>">
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label for="password">New Password</label>
                                                    <input type="password" class="form-control" name="password" id="password" 
                                                           placeholder="Leave blank to keep current password">
                                                    <small class="form-text text-muted">Only fill this if you want to change the password</small>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label for="email">Email <span class="text-danger">*</span></label>
                                                    <input type="email" class="form-control" name="email" id="email" required 
                                                           value="<?php echo htmlspecialchars($teacher['email']); ?>">
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label for="phone">Phone</label>
                                                    <input type="tel" class="form-control" name="phone" id="phone" 
                                                           value="<?php echo htmlspecialchars($teacher['phone']); ?>">
                                                </div>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label for="status">Status <span class="text-danger">*</span></label>
                                                    <select class="form-control" name="status" id="status" required>
                                                        <option value="active" <?php echo $teacher['user_status'] === 'active' ? 'selected' : ''; ?>>Active</option>
                                                        <option value="inactive" <?php echo $teacher['user_status'] === 'inactive' ? 'selected' : ''; ?>>Inactive</option>
                                                        <option value="suspended" <?php echo $teacher['user_status'] === 'suspended' ? 'selected' : ''; ?>>Suspended</option>
                                                    </select>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="form-group">
                                            <label for="qualification">Qualification</label>
                                            <textarea class="form-control" name="qualification" id="qualification" rows="3" 
                                                      placeholder="e.g., B.Ed in Mathematics, M.Sc Physics"><?php echo htmlspecialchars($teacher['qualification']); ?></textarea>
                                        </div>
                                        <div class="form-group">
                                            <button type="submit" class="btn btn-primary">Update Teacher</button>
                                            <a href="list.php" class="btn btn-secondary">Cancel</a>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="card">
                                <div class="card-header">
                                    <div class="card-title">Teacher Info</div>
                                </div>
                                <div class="card-body">
                                    <p><strong>Employee ID:</strong><br><?php echo htmlspecialchars($teacher['employee_id']); ?></p>
                                    <p><strong>Current Status:</strong><br>
                                        <?php if ($teacher['user_status'] === 'active'): ?>
                                            <span class="badge badge-success">Active</span>
                                        <?php elseif ($teacher['user_status'] === 'inactive'): ?>
                                            <span class="badge badge-warning">Inactive</span>
                                        <?php else: ?>
                                            <span class="badge badge-danger">Suspended</span>
                                        <?php endif; ?>
                                    </p>
                                    <p><strong>Joined:</strong><br><?php echo date('M d, Y', strtotime($teacher['created_at'])); ?></p>
                                    <hr>
                                    <div class="d-grid gap-2">
                                        <a href="view.php?id=<?php echo $teacher_id; ?>" class="btn btn-info btn-block">View Full Profile</a>
                                        <a href="assign_subjects.php?id=<?php echo $teacher_id; ?>" class="btn btn-success btn-block">Assign Subjects</a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
