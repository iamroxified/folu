<?php
// Start session
session_start();

// Include database and functions
require '../../db/config.php';
require '../../db/functions.php';
require '../../school_functions.php';
require '../../functions.php';

// Check if user is logged in
if (!isset($_SESSION['adid'])) {
    header('location:../../login.php');
    exit;
}

// Get dashboard statistics
$stats = get_school_stats();

// Get recent activities (last 10 activities)
function get_recent_activities($limit = 10) {
    global $pdo;
    $stmt = $pdo->prepare("
        (SELECT 'student_enrollment' as type, CONCAT(first_name, ' ', last_name, ' enrolled') as activity, created_at as activity_date 
         FROM students ORDER BY created_at DESC LIMIT 5)
        UNION ALL
        (SELECT 'payment' as type, CONCAT('Payment of ₦', amount, ' received') as activity, payment_date as activity_date 
         FROM payments WHERE status = 'completed' ORDER BY payment_date DESC LIMIT 3)
        UNION ALL
        (SELECT 'attendance' as type, 'Attendance recorded' as activity, date as activity_date 
         FROM staff_attendances ORDER BY date DESC LIMIT 2)
        ORDER BY activity_date DESC LIMIT ?
    ");
    $stmt->execute([$limit]);
    return $stmt->fetchAll();
}

$recent_activities = get_recent_activities();

// Get upcoming events/calendar items
function get_upcoming_events($limit = 5) {
    global $pdo;
    $stmt = $pdo->prepare("
        SELECT event_title as title, event_date as date, event_type as type 
        FROM academic_calendar 
        WHERE event_date >= CURDATE() 
        ORDER BY event_date ASC 
        LIMIT ?
    ");
    $stmt->execute([$limit]);
    return $stmt->fetchAll();
}

$upcoming_events = get_upcoming_events();

// Get attendance data for charts (last 7 days)
function get_attendance_chart_data() {
    global $pdo;
    $stmt = $pdo->query("
        SELECT DATE(date) as attendance_date, 
               COUNT(CASE WHEN status = 'present' THEN 1 END) as present_count,
               COUNT(CASE WHEN status = 'absent' THEN 1 END) as absent_count,
               COUNT(*) as total_count
        FROM staff_attendances 
        WHERE date >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)
        GROUP BY DATE(date)
        ORDER BY attendance_date DESC
        LIMIT 7
    ");
    return $stmt->fetchAll();
}

$attendance_data = get_attendance_chart_data();

// Get fee collection data for charts (last 6 months)
function get_fee_collection_chart_data() {
    global $pdo;
    $stmt = $pdo->query("
        SELECT MONTH(payment_date) as month, 
               YEAR(payment_date) as year,
               SUM(amount) as total_amount,
               COUNT(*) as payment_count
        FROM payments 
        WHERE status = 'completed' 
        AND payment_date >= DATE_SUB(CURDATE(), INTERVAL 6 MONTH)
        GROUP BY YEAR(payment_date), MONTH(payment_date)
        ORDER BY year DESC, month DESC
        LIMIT 6
    ");
    return $stmt->fetchAll();
}

$fee_data = get_fee_collection_chart_data();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <title>FIMOCOL - Admin Dashboard</title>
    <?php include('../../nav/links.php'); ?>
    <style>
        .stat-card {
            transition: transform 0.3s ease;
        }
        .stat-card:hover {
            transform: translateY(-5px);
        }
        .activity-item {
            padding: 10px 0;
            border-bottom: 1px solid #eee;
        }
        .activity-item:last-child {
            border-bottom: none;
        }
        .calendar-event {
            margin-bottom: 10px;
            padding: 8px;
            border-radius: 5px;
            background-color: #f8f9fa;
        }
        .fee-status-indicator {
            width: 20px;
            height: 20px;
            border-radius: 50%;
            display: inline-block;
            margin-right: 8px;
        }
        .status-collected { background-color: #28a745; }
        .status-pending { background-color: #ffc107; }
        .status-overdue { background-color: #dc3545; }
    </style>
</head>

<body>
    <div class="wrapper">
        <!-- Sidebar -->
        <?php include('../../nav/sidebar.php'); ?>
        <!-- End Sidebar -->

        <div class="main-panel">
            <?php include('../../nav/header.php'); ?>
            <div class="container">
                <div class="page-inner">
                    <div class="d-flex align-items-left align-items-md-center flex-column flex-md-row pt-2 pb-4">
                        <div>
                            <h3 class="fw-bold mb-3">FIMOCOL Admin Dashboard</h3>
                            <h6 class="op-7 mb-2">
                                <?php echo _greetin().', '.ausername($_SESSION['adid']); ?>! 
                                Welcome to your comprehensive school management dashboard
                            </h6>
                        </div>
                    </div>

                    <!-- Summary Statistics -->
                    <div class="row">
                        <div class="col-sm-6 col-md-3">
                            <div class="card card-stats card-round stat-card">
                                <div class="card-body">
                                    <a href="../../students.php">
                                        <div class="row align-items-center">
                                            <div class="col-icon">
                                                <div class="icon-big text-center icon-primary bubble-shadow-small">
                                                    <i class="fas fa-user-graduate"></i>
                                                </div>
                                            </div>
                                            <div class="col col-stats ms-3 ms-sm-0">
                                                <div class="numbers">
                                                    <p class="card-category">Total Students</p>
                                                    <h4 class="card-title"><?php echo number_format($stats['total_students']); ?></h4>
                                                </div>
                                            </div>
                                        </div>
                                    </a>
                                </div>
                            </div>
                        </div>
                        <div class="col-sm-6 col-md-3">
                            <div class="card card-stats card-round stat-card">
                                <div class="card-body">
                                    <a href="../../teachers.php">
                                        <div class="row align-items-center">
                                            <div class="col-icon">
                                                <div class="icon-big text-center icon-info bubble-shadow-small">
                                                    <i class="fas fa-chalkboard-teacher"></i>
                                                </div>
                                            </div>
                                            <div class="col col-stats ms-3 ms-sm-0">
                                                <div class="numbers">
                                                    <p class="card-category">Total Teachers</p>
                                                    <h4 class="card-title"><?php echo number_format($stats['total_teachers']); ?></h4>
                                                </div>
                                            </div>
                                        </div>
                                    </a>
                                </div>
                            </div>
                        </div>
                        <div class="col-sm-6 col-md-3">
                            <div class="card card-stats card-round stat-card">
                                <div class="card-body">
                                    <a href="../../subjects.php">
                                        <div class="row align-items-center">
                                            <div class="col-icon">
                                                <div class="icon-big text-center icon-success bubble-shadow-small">
                                                    <i class="fas fa-book"></i>
                                                </div>
                                            </div>
                                            <div class="col col-stats ms-3 ms-sm-0">
                                                <div class="numbers">
                                                    <p class="card-category">Total Classes/Courses</p>
                                                    <h4 class="card-title"><?php echo number_format($stats['total_courses']); ?></h4>
                                                </div>
                                            </div>
                                        </div>
                                    </a>
                                </div>
                            </div>
                        </div>
                        <div class="col-sm-6 col-md-3">
                            <div class="card card-stats card-round stat-card">
                                <div class="card-body">
                                    <a href="../../attendance.php">
                                        <div class="row align-items-center">
                                            <div class="col-icon">
                                                <div class="icon-big text-center icon-secondary bubble-shadow-small">
                                                    <i class="fas fa-calendar-check"></i>
                                                </div>
                                            </div>
                                            <div class="col col-stats ms-3 ms-sm-0">
                                                <div class="numbers">
                                                    <p class="card-category">Today's Attendance</p>
                                                    <h4 class="card-title"><?php echo number_format($stats['today_attendance']); ?></h4>
                                                </div>
                                            </div>
                                        </div>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Quick Links and Fee Status -->
                    <div class="row">
                        <div class="col-md-8">
                            <div class="card card-round">
                                <div class="card-header">
                                    <div class="card-head-row">
                                        <div class="card-title">Quick Access Links</div>
                                    </div>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-4 mb-3">
                                            <a href="../../students.php" class="btn btn-primary btn-block">
                                                <i class="fas fa-user-graduate"></i> Manage Students
                                            </a>
                                        </div>
                                        <div class="col-md-4 mb-3">
                                            <a href="../../teachers.php" class="btn btn-info btn-block">
                                                <i class="fas fa-chalkboard-teacher"></i> Manage Teachers
                                            </a>
                                        </div>
                                        <div class="col-md-4 mb-3">
                                            <a href="../../subjects.php" class="btn btn-success btn-block">
                                                <i class="fas fa-book"></i> Manage Subjects
                                            </a>
                                        </div>
                                        <div class="col-md-4 mb-3">
                                            <a href="../../attendance.php" class="btn btn-warning btn-block">
                                                <i class="fas fa-calendar-check"></i> Attendance
                                            </a>
                                        </div>
                                        <div class="col-md-4 mb-3">
                                            <a href="../../fees.php" class="btn btn-secondary btn-block">
                                                <i class="fas fa-dollar-sign"></i> Fee Management
                                            </a>
                                        </div>
                                        <div class="col-md-4 mb-3">
                                            <a href="#" class="btn btn-dark btn-block">
                                                <i class="fas fa-chart-bar"></i> Reports
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="card card-round">
                                <div class="card-header">
                                    <div class="card-title">Fee Collection Status</div>
                                </div>
                                <div class="card-body">
                                    <div class="mb-4">
                                        <h4 class="text-success">₦<?php echo number_format($stats['total_collections'], 2); ?></h4>
                                        <small class="text-muted">Total Collections</small>
                                    </div>
                                    <div class="mb-4">
                                        <h4 class="text-warning">₦<?php echo number_format($stats['pending_fees'], 2); ?></h4>
                                        <small class="text-muted">Pending Fees</small>
                                    </div>
                                    <div class="mb-4">
                                        <h4 class="text-danger"><?php echo number_format($stats['overdue_fees']); ?></h4>
                                        <small class="text-muted">Overdue Payments</small>
                                    </div>
                                    <div class="mb-3">
                                        <h4 class="text-info">₦<?php echo number_format($stats['monthly_collection'], 2); ?></h4>
                                        <small class="text-muted">This Month's Collection</small>
                                    </div>
                                    <div class="fee-status-legend">
                                        <div><span class="fee-status-indicator status-collected"></span> Collected</div>
                                        <div><span class="fee-status-indicator status-pending"></span> Pending</div>
                                        <div><span class="fee-status-indicator status-overdue"></span> Overdue</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Recent Activities and Academic Calendar -->
                    <div class="row">
                        <div class="col-md-6">
                            <div class="card card-round">
                                <div class="card-header">
                                    <div class="card-title">Recent Activities</div>
                                </div>
                                <div class="card-body">
                                    <?php if (!empty($recent_activities)): ?>
                                        <?php foreach ($recent_activities as $activity): ?>
                                            <div class="activity-item">
                                                <div class="d-flex justify-content-between">
                                                    <span>
                                                        <i class="fas fa-circle text-primary" style="font-size: 8px;"></i>
                                                        <?php echo htmlspecialchars($activity['activity']); ?>
                                                    </span>
                                                    <small class="text-muted">
                                                        <?php echo date('M d, Y', strtotime($activity['activity_date'])); ?>
                                                    </small>
                                                </div>
                                            </div>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <p class="text-muted">No recent activities found.</p>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="card card-round">
                                <div class="card-header">
                                    <div class="card-title">Academic Calendar Overview</div>
                                </div>
                                <div class="card-body">
                                    <?php if (!empty($upcoming_events)): ?>
                                        <?php foreach ($upcoming_events as $event): ?>
                                            <div class="calendar-event">
                                                <div class="d-flex justify-content-between align-items-center">
                                                    <div>
                                                        <strong><?php echo htmlspecialchars($event['title']); ?></strong>
                                                        <br>
                                                        <small class="text-muted"><?php echo ucfirst($event['type']); ?></small>
                                                    </div>
                                                    <span class="badge badge-info">
                                                        <?php echo date('M d', strtotime($event['date'])); ?>
                                                    </span>
                                                </div>
                                            </div>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <div class="calendar-event">
                                            <p class="text-muted">No upcoming events scheduled.</p>
                                            <small>Add events to the academic calendar to see them here.</small>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Charts Row -->
                    <div class="row">
                        <div class="col-md-8">
                            <div class="card">
                                <div class="card-header">
                                    <div class="card-title">Attendance Overview (Last 7 Days)</div>
                                </div>
                                <div class="card-body">
                                    <div class="chart-container">
                                        <canvas id="attendanceChart"></canvas>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="card">
                                <div class="card-header">
                                    <div class="card-title">Fee Collection Trends</div>
                                </div>
                                <div class="card-body">
                                    <div class="chart-container">
                                        <canvas id="feeChart"></canvas>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
            <?php include('../../nav/footer.php'); ?>

            <!-- Chart.js Scripts -->
            <script>
                // Attendance Chart Data
                const attendanceData = <?php echo json_encode(array_reverse($attendance_data)); ?>;
                const attendanceLabels = attendanceData.map(item => {
                    const date = new Date(item.attendance_date);
                    return date.toLocaleDateString('en-US', { month: 'short', day: 'numeric' });
                });
                const presentData = attendanceData.map(item => parseInt(item.present_count));
                const absentData = attendanceData.map(item => parseInt(item.absent_count));

                // Fee Collection Chart Data
                const feeData = <?php echo json_encode(array_reverse($fee_data)); ?>;
                const feeLabels = feeData.map(item => {
                    const monthNames = ["Jan", "Feb", "Mar", "Apr", "May", "Jun", 
                                       "Jul", "Aug", "Sep", "Oct", "Nov", "Dec"];
                    return monthNames[item.month - 1] + ' ' + item.year;
                });
                const feeAmounts = feeData.map(item => parseFloat(item.total_amount));

                // Attendance Chart
                const attendanceCtx = document.getElementById('attendanceChart').getContext('2d');
                new Chart(attendanceCtx, {
                    type: 'line',
                    data: {
                        labels: attendanceLabels,
                        datasets: [{
                            label: 'Present',
                            data: presentData,
                            borderColor: '#28a745',
                            backgroundColor: 'rgba(40, 167, 69, 0.1)',
                            borderWidth: 2,
                            fill: true
                        }, {
                            label: 'Absent',
                            data: absentData,
                            borderColor: '#dc3545',
                            backgroundColor: 'rgba(220, 53, 69, 0.1)',
                            borderWidth: 2,
                            fill: true
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        scales: {
                            y: {
                                beginAtZero: true
                            }
                        }
                    }
                });

                // Fee Collection Chart
                const feeCtx = document.getElementById('feeChart').getContext('2d');
                new Chart(feeCtx, {
                    type: 'doughnut',
                    data: {
                        labels: feeLabels.slice(0, 3), // Show only last 3 months
                        datasets: [{
                            data: feeAmounts.slice(0, 3),
                            backgroundColor: [
                                '#1d7af3',
                                '#f3545d',
                                '#fdaf4b'
                            ],
                            borderWidth: 0
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        legend: {
                            position: 'bottom'
                        }
                    }
                });
            </script>

        </div>
    </div>
</body>

</html>
