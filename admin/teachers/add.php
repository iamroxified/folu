<?php
// Start session
session_start();

// Include database configuration and functions
require '../../db/config.php';
require '../../db/functions.php';
require '../../school_functions.php';

// Check if user is logged in
if (!isset($_SESSION['adid'])) {
    header('location:../../login.php');
    exit;
}


// Add Teacher
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validate inputs
    $firstName = validate($_POST['first_name'] ?? '');
    $lastName = validate($_POST['last_name'] ?? '');
    $email = validate($_POST['email'] ?? '');
    $phone = validate($_POST['phone'] ?? '');
    $qualification = validate($_POST['qualification'] ?? '');
    $username = validate($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    
    // Validate email
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Invalid email format';
    }

    // Check for missing fields
    if (empty($firstName) || empty($lastName) || empty($email) || empty($username) || empty($password)) {
        $error = 'Please fill all required fields';
    }

    // Check if username or email already exists
    if (!isset($error)) {
        $stmt = $pdo->prepare('SELECT COUNT(*) FROM users WHERE username = ? OR email = ?');
        $stmt->execute([$username, $email]);
        if ($stmt->fetchColumn() > 0) {
            $error = 'Username or email already exists';
        }
    }

    if (!isset($error)) {
        try {
            // Start transaction
            $pdo->beginTransaction();
            
            // Generate employee ID
            $employeeID = generate_employee_id();
            
            // Insert into users table
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare('INSERT INTO users (username, password, email, role, status) VALUES (?, ?, ?, ?, ?)');
            $stmt->execute([$username, $hashedPassword, $email, 'teacher', 'active']);
            $userId = $pdo->lastInsertId();
            
            // Insert into teachers table
            $stmt = $pdo->prepare('INSERT INTO teachers (user_id, employee_id, first_name, last_name, email, phone, qualification) VALUES (?, ?, ?, ?, ?, ?, ?)');
            $stmt->execute([$userId, $employeeID, $firstName, $lastName, $email, $phone, $qualification]);
            
            // Commit transaction
            $pdo->commit();
            
            $success = 'Teacher added successfully with Employee ID: ' . $employeeID;
        } catch (Exception $e) {
            // Rollback transaction
            $pdo->rollback();
            $error = 'Failed to add teacher: ' . $e->getMessage();
        }
    }
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <title>Add New Teacher</title>
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
                        <h2 class="text-dark pb-2 fw-bold">Add New Teacher</h2>
                        <div class="ml-md-auto py-2 py-md-0">
                            <a href="list.php" class="btn btn-secondary">View All Teachers</a>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-8">
                            <div class="card">
                                <div class="card-header">
                                    <div class="card-title">Teacher Details</div>
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
                                                    <input type="text" class="form-control" name="first_name" id="first_name" required value="<?php echo isset($_POST['first_name']) ? htmlspecialchars($_POST['first_name']) : ''; ?>">
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label for="last_name">Last Name <span class="text-danger">*</span></label>
                                                    <input type="text" class="form-control" name="last_name" id="last_name" required value="<?php echo isset($_POST['last_name']) ? htmlspecialchars($_POST['last_name']) : ''; ?>">
                                                </div>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label for="username">Username <span class="text-danger">*</span></label>
                                                    <input type="text" class="form-control" name="username" id="username" required value="<?php echo isset($_POST['username']) ? htmlspecialchars($_POST['username']) : ''; ?>">
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label for="password">Password <span class="text-danger">*</span></label>
                                                    <input type="password" class="form-control" name="password" id="password" required>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label for="email">Email <span class="text-danger">*</span></label>
                                                    <input type="email" class="form-control" name="email" id="email" required value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>">
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label for="phone">Phone</label>
                                                    <input type="tel" class="form-control" name="phone" id="phone" value="<?php echo isset($_POST['phone']) ? htmlspecialchars($_POST['phone']) : ''; ?>">
                                                </div>
                                            </div>
                                        </div>
                                        <div class="form-group">
                                            <label for="qualification">Qualification</label>
                                            <textarea class="form-control" name="qualification" id="qualification" rows="3" placeholder="e.g., B.Ed in Mathematics, M.Sc Physics"><?php echo isset($_POST['qualification']) ? htmlspecialchars($_POST['qualification']) : ''; ?></textarea>
                                        </div>
                                        <div class="form-group">
                                            <button type="submit" class="btn btn-primary">Add Teacher</button>
                                            <a href="list.php" class="btn btn-secondary">Cancel</a>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="card">
                                <div class="card-header">
                                    <div class="card-title">Instructions</div>
                                </div>
                                <div class="card-body">
                                    <p><strong>Required Fields:</strong></p>
                                    <ul>
                                        <li>First Name</li>
                                        <li>Last Name</li>
                                        <li>Username (for login)</li>
                                        <li>Password</li>  
                                        <li>Email</li>
                                    </ul>
                                    <p><strong>Note:</strong> An employee ID will be automatically generated for the teacher.</p>
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
