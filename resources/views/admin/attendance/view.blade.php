<?php
// Start the session

// Include database and functions
require_once base_path('db/config.php');
require_once base_path('db/functions.php');

// Check if user is logged in
if (!isset($_SESSION['adid'])) {
    header('Location: /admin/login.php');
    exit;
}

// Fetch attendance records
$attendanceRecords = QueryDB("SELECT a.*, s.first_name, s.last_name FROM attendance a JOIN students s ON a.student_link = s.id ORDER BY a.date DESC")
    ->fetchAll();

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <title>View Attendance</title>
    @include('admin.partials.links')
</head>
<body>
    <div class="wrapper">
        @include('admin.partials.sidebar')

        <div class="main-panel">
            @include('admin.partials.header')
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
            @include('admin.partials.footer')
</body>
</html>




