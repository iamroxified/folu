<?php
// Start session

// Include database and functions
require_once base_path('db/config.php');
require_once base_path('db/functions.php');

// Check if user is logged in
if (!isset($_SESSION['adid'])) {
    header('Location: /admin/login.php');
    exit;
}

$studentColumns = QueryDB("SHOW COLUMNS FROM students")->fetchAll(PDO::FETCH_COLUMN);
$studentIdentifierField = in_array('admission_no', $studentColumns, true) ? 'admission_no' : 'student_number';
$studentIdentifierLabel = $studentIdentifierField === 'admission_no' ? 'Admission No' : 'Student Number';
$attendanceColumns = QueryDB("SHOW COLUMNS FROM attendance")->fetchAll(PDO::FETCH_COLUMN);
$timeInSelect = in_array('time_in', $attendanceColumns, true) ? 'a.time_in' : 'NULL AS time_in';
$timeOutSelect = in_array('time_out', $attendanceColumns, true) ? 'a.time_out' : 'NULL AS time_out';

// Fetch recent attendance data with student information
$attendance = QueryDB("
    SELECT a.date, a.status, {$timeInSelect}, {$timeOutSelect}, s.first_name, s.last_name, s.{$studentIdentifierField} AS student_identifier
    FROM attendance a
    LEFT JOIN students s ON a.student_link = s.id
    ORDER BY a.date DESC
    LIMIT 50
")->fetchAll();

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <title>Attendance Management</title>
    @include('admin.partials.links')
</head>

<body>
    <div class="wrapper">
        @include('admin.partials.sidebar')

        <div class="main-panel">
            @include('admin.partials.header')
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
                                                    <th><?php echo htmlspecialchars($studentIdentifierLabel); ?></th>
                                                    <th>Status</th>
                                                    <th>Time In</th>
                                                    <th>Time Out</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php foreach ($attendance as $record): ?>
                                                    <?php $studentName = trim(($record['first_name'] ?? '') . ' ' . ($record['last_name'] ?? '')); ?>
                                                    <tr>
                                                        <td><?php echo date('M d, Y', strtotime($record['date'])); ?></td>
                                                        <td><?php echo htmlspecialchars($studentName !== '' ? $studentName : 'N/A'); ?></td>
                                                        <td><?php echo htmlspecialchars((string) ($record['student_identifier'] ?? 'N/A')); ?></td>
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
            @include('admin.partials.footer')
</body>

</html>




