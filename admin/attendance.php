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

// Fetch recent attendance data with student information
$attendance = QueryDB("
    SELECT sa.*, s.first_name, s.last_name, s.student_number 
    FROM staff_attendances sa 
    LEFT JOIN students s ON sa.student_id = s.id 
    ORDER BY sa.date DESC 
    LIMIT 50
")->fetchAll();

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <title>Attendance Management</title>
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
                        <h2 class="text-dark pb-2 fw-bold">Attendance</h2>
                    </div>

                    <div class="row">
                        <div class="col-md-12">
                            <div class="card">
                                <div class="card-header">
                                    <div class="card-title">Recent Attendance Records</div>
                                </div>
                                <div class="card-body">
                                    <div class="table-responsive">
                                        <table class="table table-bordered">
                                            <thead>
                                                <tr>
                                                    <th>Date</th>
                                                    <th>Student</th>
                                                    <th>Student Number</th>
                                                    <th>Status</th>
                                                    <th>Time In</th>
                                                    <th>Time Out</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php foreach ($attendance as $record): ?>
                                                    <tr>
                                                        <td><?php echo date('M d, Y', strtotime($record['date'])); ?></td>
                                                        <td><?php echo $record['first_name'] . ' ' . $record['last_name']; ?></td>
                                                        <td><?php echo $record['student_number']; ?></td>
                                                        <td>
                                                            <?php if ($record['status'] == 'present'): ?>
                                                                <span class="badge badge-success">Present</span>
                                                            <?php elseif ($record['status'] == 'absent'): ?>
                                                                <span class="badge badge-danger">Absent</span>
                                                            <?php else: ?>
                                                                <span class="badge badge-warning">Late</span>
                                                            <?php endif; ?>
                                                        </td>
                                                        <td><?php echo $record['time_in'] ?? 'N/A'; ?></td>
                                                        <td><?php echo $record['time_out'] ?? 'N/A'; ?></td>
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
