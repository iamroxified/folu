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

// Fetch assigned subjects
$subjects_sql = "SELECT s.*, c.class_name, c.section 
                 FROM subjects s 
                 LEFT JOIN classes c ON s.class_id = c.id 
                 WHERE s.teacher_id = ? 
                 ORDER BY c.class_name, s.subject_name";
$subjects_stmt = $pdo->prepare($subjects_sql);
$subjects_stmt->execute([$teacher_id]);
$subjects = $subjects_stmt->fetchAll();

// Parse subjects from teachers table (if stored as JSON or comma-separated)
$teacher_subjects = [];
if (!empty($teacher['subjects'])) {
    // Try to decode as JSON first
    $decoded = json_decode($teacher['subjects'], true);
    if ($decoded) {
        $teacher_subjects = $decoded;
    } else {
        // Otherwise treat as comma-separated
        $teacher_subjects = array_map('trim', explode(',', $teacher['subjects']));
    }
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <title>Teacher Profile</title>
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
                        <h2 class="text-dark pb-2 fw-bold">Teacher Profile</h2>
                        <div class="ml-md-auto py-2 py-md-0">
                            <a href="list.php" class="btn btn-secondary">Back to List</a>
                            <a href="edit.php?id=<?php echo $teacher_id; ?>" class="btn btn-warning">Edit Teacher</a>
                            <a href="assign_subjects.php?id=<?php echo $teacher_id; ?>" class="btn btn-success">Assign Subjects</a>
                        </div>
                    </div>

                    <div class="row">
                        <!-- Teacher Information -->
                        <div class="col-md-8">
                            <div class="card">
                                <div class="card-header">
                                    <div class="card-title">
                                        <?php echo htmlspecialchars($teacher['first_name'] . ' ' . $teacher['last_name']); ?>
                                    </div>
                                    <div class="card-category">
                                        Employee ID: <strong><?php echo htmlspecialchars($teacher['employee_id']); ?></strong>
                                    </div>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <table class="table table-borderless">
                                                <tr>
                                                    <td><strong>First Name:</strong></td>
                                                    <td><?php echo htmlspecialchars($teacher['first_name']); ?></td>
                                                </tr>
                                                <tr>
                                                    <td><strong>Last Name:</strong></td>
                                                    <td><?php echo htmlspecialchars($teacher['last_name']); ?></td>
                                                </tr>
                                                <tr>
                                                    <td><strong>Username:</strong></td>
                                                    <td>@<?php echo htmlspecialchars($teacher['username']); ?></td>
                                                </tr>
                                                <tr>
                                                    <td><strong>Email:</strong></td>
                                                    <td><?php echo htmlspecialchars($teacher['email']); ?></td>
                                                </tr>
                                            </table>
                                        </div>
                                        <div class="col-md-6">
                                            <table class="table table-borderless">
                                                <tr>
                                                    <td><strong>Phone:</strong></td>
                                                    <td><?php echo htmlspecialchars($teacher['phone'] ?: 'Not provided'); ?></td>
                                                </tr>
                                                <tr>
                                                    <td><strong>Status:</strong></td>
                                                    <td>
                                                        <?php if ($teacher['user_status'] === 'active'): ?>
                                                            <span class="badge badge-success">Active</span>
                                                        <?php elseif ($teacher['user_status'] === 'inactive'): ?>
                                                            <span class="badge badge-warning">Inactive</span>
                                                        <?php else: ?>
                                                            <span class="badge badge-danger">Suspended</span>
                                                        <?php endif; ?>
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td><strong>Joined:</strong></td>
                                                    <td><?php echo date('M d, Y \a\t g:i A', strtotime($teacher['created_at'])); ?></td>
                                                </tr>
                                                <tr>
                                                    <td><strong>Subjects Count:</strong></td>
                                                    <td><?php echo count($subjects); ?> assigned</td>
                                                </tr>
                                            </table>
                                        </div>
                                    </div>
                                    
                                    <?php if (!empty($teacher['qualification'])): ?>
                                    <div class="row mt-3">
                                        <div class="col-12">
                                            <h5>Qualification</h5>
                                            <p class="text-muted"><?php echo nl2br(htmlspecialchars($teacher['qualification'])); ?></p>
                                        </div>
                                    </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>

                        <!-- Quick Stats -->
                        <div class="col-md-4">
                            <div class="card">
                                <div class="card-header">
                                    <div class="card-title">Quick Actions</div>
                                </div>
                                <div class="card-body">
                                    <div class="d-grid gap-2">
                                        <a href="edit.php?id=<?php echo $teacher_id; ?>" class="btn btn-warning btn-block">
                                            <i class="fa fa-edit"></i> Edit Information
                                        </a>
                                        <a href="assign_subjects.php?id=<?php echo $teacher_id; ?>" class="btn btn-success btn-block">
                                            <i class="fa fa-book"></i> Assign Subjects
                                        </a>
                                        <a href="list.php" class="btn btn-secondary btn-block">
                                            <i class="fa fa-list"></i> All Teachers
                                        </a>
                                    </div>
                                </div>
                            </div>

                            <div class="card mt-3">
                                <div class="card-header">
                                    <div class="card-title">Statistics</div>
                                </div>
                                <div class="card-body">
                                    <div class="text-center">
                                        <h3 class="text-primary"><?php echo count($subjects); ?></h3>
                                        <p class="text-muted">Assigned Subjects</p>
                                    </div>
                                    <hr>
                                    <div class="text-center">
                                        <h4 class="text-info"><?php echo htmlspecialchars($teacher['employee_id']); ?></h4>
                                        <p class="text-muted">Employee ID</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Assigned Subjects -->
                    <div class="row mt-4">
                        <div class="col-md-12">
                            <div class="card">
                                <div class="card-header">
                                    <div class="card-title">Assigned Subjects</div>
                                    <div class="card-category">
                                        Classes and subjects taught by this teacher
                                    </div>
                                </div>
                                <div class="card-body">
                                    <?php if (empty($subjects)): ?>
                                        <div class="alert alert-info">
                                            <i class="fa fa-info-circle"></i>
                                            No subjects have been assigned to this teacher yet. 
                                            <a href="assign_subjects.php?id=<?php echo $teacher_id; ?>" class="alert-link">Assign subjects now</a>.
                                        </div>
                                    <?php else: ?>
                                        <div class="table-responsive">
                                            <table class="table table-bordered table-hover">
                                                <thead class="thead-light">
                                                    <tr>
                                                        <th>#</th>
                                                        <th>Subject Name</th>
                                                        <th>Subject Code</th>
                                                        <th>Class</th>
                                                        <th>Section</th>
                                                        <th>Actions</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <?php $counter = 1; ?>
                                                    <?php foreach ($subjects as $subject): ?>
                                                        <tr>
                                                            <td><?php echo $counter++; ?></td>
                                                            <td><strong><?php echo htmlspecialchars($subject['subject_name']); ?></strong></td>
                                                            <td><?php echo htmlspecialchars($subject['subject_code']); ?></td>
                                                            <td><?php echo htmlspecialchars($subject['class_name'] ?: 'N/A'); ?></td>
                                                            <td><?php echo htmlspecialchars($subject['section'] ?: 'N/A'); ?></td>
                                                            <td>
                                                                <a href="../subjects/view.php?id=<?php echo $subject['id']; ?>" class="btn btn-info btn-sm" title="View Subject">
                                                                    <i class="fa fa-eye"></i>
                                                                </a>
                                                            </td>
                                                        </tr>
                                                    <?php endforeach; ?>
                                                </tbody>
                                            </table>
                                        </div>
                                    <?php endif; ?>
                                    
                                    <?php if (!empty($teacher_subjects)): ?>
                                    <div class="mt-4">
                                        <h5>Other Subjects/Specializations</h5>
                                        <div class="row">
                                            <?php foreach ($teacher_subjects as $subject): ?>
                                                <div class="col-md-3 mb-2">
                                                    <span class="badge badge-primary"><?php echo htmlspecialchars($subject); ?></span>
                                                </div>
                                            <?php endforeach; ?>
                                        </div>
                                    </div>
                                    <?php endif; ?>
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
