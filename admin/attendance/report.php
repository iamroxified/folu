<?php
// Start the session
session_start();

// Include database configuration and utility functions
require '../../db/config.php';
require '../../db/functions.php';

// Check if user is logged in
if (!isset($_SESSION['adid'])) {
    header('location:../../login.php');
    exit;
}

// Fetch attendance summary
$attendanceSummary = QueryDB("SELECT s.first_name, s.last_name, (SELECT COUNT(*) FROM attendance a WHERE a.student_id = s.id AND a.status = 'present') as present_count, (SELECT COUNT(*) FROM attendance a WHERE a.student_id = s.id AND a.status = 'absent') as absent_count FROM students s")
    ->fetchAll(PDO::FETCH_ASSOC);

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <title>Attendance Report</title>
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
                                    <th>Student</th>
                                    <th>Present Count</th>
                                    <th>Absent Count</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($attendanceSummary as $summary): ?>
                                    <tr>
                                        <td><?= $summary['first_name'] . ' ' . $summary['last_name'] ?></td>
                                        <td><?= $summary['present_count'] ?></td>
                                        <td><?= $summary['absent_count'] ?></td>
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
