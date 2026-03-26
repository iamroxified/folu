<?php
// Start the session
session_start();

// Include database and functions
require '../../db/config.php';
require '../../db/functions.php';

// Check if user is logged in
if (!isset($_SESSION['adid'])) {
    header('location:../../login.php');
    exit;
}

// Fetch attendance records
$attendanceRecords = QueryDB("SELECT a.*, s.first_name, s.last_name FROM attendance a JOIN students s ON a.student_id = s.id ORDER BY a.date DESC")
    ->fetchAll();

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <title>View Attendance</title>
    <?php include('../../nav/links.php'); ?>
</head>
<body>
    <div class="wrapper">
        <?php include('../../nav/sidebar.php'); ?>

        <div class="main-panel">
            <?php include('../../nav/header.php'); ?>
            <div class="container">
                <div class="page-inner">
                    <div class="table-responsive">
                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th>Date</th>
                                    <th>Student</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($attendanceRecords as $record): ?>
                                    <tr>
                                        <td><?= date('M d, Y', strtotime($record['date'])) ?></td>
                                        <td><?= $record['first_name'] . ' ' . $record['last_name'] ?></td>
                                        <td><?= ucfirst($record['status']) ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
