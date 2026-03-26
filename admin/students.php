<?php
// Start session
session_start();

// Include database and functions
require '../db/config.php';
require '../db/functions.php';

// Check if user is logged in
if (!isset($_SESSION['adid'])) {
    header('location:login.php');
    exit;
}

// Fetch students data
$students = QueryDB("SELECT * FROM students")->fetchAll();

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <title>Students Management</title>
    <?php include('nav/links.php'); ?>
</head>

<body>
    <div class="wrapper">
        <?php include('nav/sidebar.php'); ?>

        <div class="main-panel">
            <?php include('nav/header.php'); ?>
            <div class="container">
                <div class="page-inner">
                    <div class="d-flex align-items-left flex-column flex-md-row">
                        <h2 class="text-dark pb-2 fw-bold">Students</h2>
                        <div class="ml-md-auto py-2 py-md-0">
                            <a href="modules/students/add.php" class="btn btn-primary">Add New Student</a>
                            <a href="modules/students/list.php" class="btn btn-info">Manage Students</a>
                            <a href="modules/students/import_export.php" class="btn btn-success">Import/Export</a>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-12">
                            <div class="card">
                                <div class="card-header">
                                    <div class="card-title">All Students</div>
                                    <div class="card-tools">
                                        <a href="modules/students/list.php" class="btn btn-sm btn-primary">View All</a>
                                    </div>
                                </div>
                                <div class="card-body">
                                    <div class="table-responsive">
                                        <table class="table table-bordered">
                                            <thead>
                                                <tr>
                                                    <th>Student Number</th>
                                                    <th>First Name</th>
                                                    <th>Last Name</th>
                                                    <th>Email</th>
                                                    <th>Status</th>
                                                    <th>Actions</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php foreach ($students as $student): ?>
                                                    <tr>
                                                        <td><?php echo $student['admission_no']; ?></td>
                                                        <td><?php echo $student['first_name']; ?></td>
                                                        <td><?php echo $student['last_name']; ?></td>
                                                        <td><?php echo $student['email']; ?></td>
                                                        <td>
                                                            <span class="badge badge-<?php echo $student['status'] === 'active' ? 'success' : 'secondary'; ?>">
                                                                <?php echo ucfirst($student['status']); ?>
                                                            </span>
                                                        </td>
                                                        <td>
                                                            <a href="modules/students/view.php?id=<?php echo $student['id']; ?>" class="btn btn-info btn-sm">View</a>
                                                            <a href="modules/students/edit.php?id=<?php echo $student['id']; ?>" class="btn btn-warning btn-sm">Edit</a>
                                                        </td>
                                                    </tr>
                                                <?php endforeach; ?>
                                            </tbody>
                                        </table>
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
