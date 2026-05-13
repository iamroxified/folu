<?php
// Start the session

// Include database configuration and utility functions
require_once base_path('db/config.php');
require_once base_path('db/functions.php');

// Check if user is logged in
if (!isset($_SESSION['adid'])) {
    header('Location: /admin/login.php');
    exit;
}

$attendanceSummary = [];

if (schema_has_table('student_attendance')) {
    $attendanceSummary = QueryDB(
        "SELECT s.first_name,
                s.last_name,
                SUM(CASE WHEN a.status = 'present' THEN 1 ELSE 0 END) AS present_count,
                SUM(CASE WHEN a.status = 'absent' THEN 1 ELSE 0 END) AS absent_count
         FROM students s
         LEFT JOIN student_attendance a ON a.student_id = s.id
         GROUP BY s.id, s.first_name, s.last_name
         ORDER BY s.last_name, s.first_name"
    )->fetchAll(PDO::FETCH_ASSOC);
} elseif (schema_has_table('attendance')) {
    $attendanceSummary = QueryDB(
        "SELECT s.first_name,
                s.last_name,
                (SELECT COUNT(*) FROM attendance a WHERE a.student_link = s.id AND a.status = 'present') AS present_count,
                (SELECT COUNT(*) FROM attendance a WHERE a.student_link = s.id AND a.status = 'absent') AS absent_count
         FROM students s"
    )->fetchAll(PDO::FETCH_ASSOC);
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <title>Attendance Report</title>
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
            @include('admin.partials.footer')
</body>
</html>



