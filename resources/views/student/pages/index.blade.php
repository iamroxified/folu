<?php
require_once base_path('db/config.php');
require_once base_path('db/functions.php');

if (!isset($_SESSION['student_user_id'], $_SESSION['student_id'])) {
    header('Location: /student/login.php');
    exit;
}

$student = get_student_profile_by_user_id((int) $_SESSION['student_user_id']);

if (!$student) {
    header('Location: /student/logout.php');
    exit;
}

$currentSessionId = get_student_default_session_id((int) $student['id'], (int) ($student['academic_session_link'] ?? 0)) ?? 0;
$payments = get_student_payment_history((int) $student['id'], $currentSessionId);
$feeRecords = get_student_fee_records((int) $student['id'], $currentSessionId);
$announcements = get_announcements_for_audience('students', $currentSessionId);
$outstandingCount = 0;
$totalPaid = 0.0;

foreach ($feeRecords as $feeRecord) {
    if ((float) ($feeRecord['balance'] ?? 0) > 0) {
        $outstandingCount++;
    }
}

foreach ($payments as $payment) {
    $totalPaid += (float) ($payment['amount_paid'] ?? 0);
}

$canViewResults = student_result_access_allowed((int) $student['id'], $currentSessionId);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta http-equiv="X-UA-Compatible" content="IE=edge" />
  <title>Student Dashboard</title>
  @include('admin.partials.links')
</head>
<body>
  <div class="wrapper">
    @include('student.partials.sidebar')
    <div class="main-panel">
      @include('student.partials.header')
      <div class="container">
        <div class="page-inner">
          <div class="d-flex align-items-left flex-column flex-md-row">
            <h2 class="text-dark pb-2 fw-bold">Student Dashboard</h2>
          </div>

          <div class="row">
            <div class="col-md-3">
              <div class="card card-stats card-primary card-round">
                <div class="card-body"><div class="numbers"><p class="card-category">Attendance %</p><h4 class="card-title"><?php echo number_format((float) student_attendance_percentage((int) $student['id']), 1); ?>%</h4></div></div>
              </div>
            </div>
            <div class="col-md-3">
              <div class="card card-stats card-info card-round">
                <div class="card-body"><div class="numbers"><p class="card-category">Average Score</p><h4 class="card-title"><?php echo number_format((float) student_grade_average((int) $student['id']), 1); ?></h4></div></div>
              </div>
            </div>
            <div class="col-md-3">
              <div class="card card-stats card-success card-round">
                <div class="card-body"><div class="numbers"><p class="card-category">Payments This Session</p><h4 class="card-title">N<?php echo number_format($totalPaid, 2); ?></h4></div></div>
              </div>
            </div>
            <div class="col-md-3">
              <div class="card card-stats card-warning card-round">
                <div class="card-body"><div class="numbers"><p class="card-category">Outstanding Fees</p><h4 class="card-title"><?php echo $outstandingCount; ?></h4></div></div>
              </div>
            </div>
          </div>

          <div class="row">
            <div class="col-md-5">
              <div class="card">
                <div class="card-header"><div class="card-title">Profile Summary</div></div>
                <div class="card-body">
                  <p><strong>Name:</strong> <?php echo htmlspecialchars(trim(($student['first_name'] ?? '') . ' ' . ($student['last_name'] ?? '') . ' ' . ($student['other_names'] ?? ''))); ?></p>
                  <p><strong>Admission No:</strong> <?php echo htmlspecialchars((string) $student['admission_no']); ?></p>
                  <p><strong>Class:</strong> <?php echo htmlspecialchars(trim(($student['class_name'] ?? '') . ' ' . ($student['class_arm'] ?? ''))); ?></p>
                  <p><strong>Session:</strong> <?php echo htmlspecialchars(trim(($student['session_name'] ?? '') . ' - ' . session_term_label($student['session_term'] ?? ''))); ?></p>
                  <p><strong>Status:</strong> <span class="badge badge-<?php echo ($student['status'] ?? '') === 'active' ? 'success' : 'warning'; ?>"><?php echo htmlspecialchars(ucfirst((string) ($student['status'] ?? 'unknown'))); ?></span></p>
                </div>
              </div>
            </div>
            <div class="col-md-7">
              <div class="card">
                <div class="card-header"><div class="card-title">Quick Actions</div></div>
                <div class="card-body d-grid gap-2">
                  <a href="{{ url('/student/payments.php') }}" class="btn btn-primary">View Payment History</a>
                  <a href="{{ url('/student/attendance.php') }}" class="btn btn-info">View Attendance</a>
                  <a href="{{ url('/student/results.php') }}" class="btn btn-success">View Results</a>
                  <a href="{{ url('/student/announcements.php') }}" class="btn btn-secondary">Read Announcements</a>
                  <a href="{{ url('/student/account.php') }}" class="btn btn-dark">Change Password</a>
                </div>
              </div>
            </div>
          </div>

          <?php if (!$canViewResults): ?>
            <div class="alert alert-warning">Results are currently locked because there are unpaid fees for the active session.</div>
          <?php endif; ?>

          <div class="card">
            <div class="card-header"><div class="card-title">Latest Announcements</div></div>
            <div class="card-body">
              <?php if (empty($announcements)): ?>
                <div class="alert alert-info mb-0">No announcements are available right now.</div>
              <?php else: ?>
                <?php foreach (array_slice($announcements, 0, 4) as $announcement): ?>
                  <div class="border rounded p-3 mb-3">
                    <h5 class="mb-1"><?php echo htmlspecialchars((string) $announcement['title']); ?></h5>
                    <div class="text-muted small mb-2"><?php echo htmlspecialchars((string) ($announcement['published_at'] ?? $announcement['created_at'] ?? '')); ?></div>
                    <p class="mb-0"><?php echo nl2br(htmlspecialchars((string) $announcement['body'])); ?></p>
                  </div>
                <?php endforeach; ?>
              <?php endif; ?>
            </div>
          </div>
        </div>
      </div>
      @include('admin.partials.footer')
</body>
</html>
