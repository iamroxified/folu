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

// Fetch teachers/staff data
$teachers = QueryDB("SELECT * FROM staff WHERE status = 'active'")->fetchAll();

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <title>Teachers Management</title>
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
                        <h2 class="text-dark pb-2 fw-bold">Teachers & Staff</h2>
                    </div>

                    <div class="row">
                        <div class="col-md-12">
                            <div class="card">
                                <div class="card-header">
                                    <div class="card-title">All Teachers</div>
                                </div>
                                <div class="card-body">
                                    <div class="table-responsive">
                                        <table class="table table-bordered">
                                            <thead>
                                                <tr>
                                                    <th>Staff Number</th>
                                                    <th>Name</th>
                                                    <th>Email</th>
                                                    <th>Position</th>
                                                    <th>Department</th>
                                                    <th>Hire Date</th>
                                                    <th>Status</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php foreach ($teachers as $teacher): ?>
                                                    <tr>
                                                        <td><?php echo $teacher['staff_number']; ?></td>
                                                        <td><?php echo $teacher['first_name'] . ' ' . $teacher['last_name']; ?></td>
                                                        <td><?php echo $teacher['email']; ?></td>
                                                        <td><?php echo $teacher['position']; ?></td>
                                                        <td><?php echo $teacher['department']; ?></td>
                                                        <td><?php echo date('M d, Y', strtotime($teacher['hire_date'])); ?></td>
                                                        <td>
                                                            <span class="badge badge-success"><?php echo ucfirst($teacher['status']); ?></span>
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
