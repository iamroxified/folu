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

$selectedSessionId = (int) ($_GET['academic_session_link'] ?? (get_current_academic_session_id() ?? (int) ($student['academic_session_link'] ?? 0)));
$sessions = QueryDB('SELECT * FROM academic_sessions ORDER BY id DESC')->fetchAll();
$attendanceRecords = get_student_attendance_history((int) $student['id'], $selectedSessionId);
$presentCount = 0;
$absentCount = 0;

foreach ($attendanceRecords as $record) {
    if (($record['status'] ?? '') === 'present') {
        $presentCount++;
    } elseif (($record['status'] ?? '') === 'absent') {
        $absentCount++;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta http-equiv="X-UA-Compatible" content="IE=edge" />
  <title>Student Attendance</title>
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
            <h2 class="text-dark pb-2 fw-bold">Attendance</h2>
          </div>

          <div class="row">
            <div class="col-md-6">
              <div class="card card-stats card-success card-round">
                <div class="card-body"><div class="numbers"><p class="card-category">Present Records</p><h4 class="card-title"><?php echo $presentCount; ?></h4></div></div>
              </div>
            </div>
            <div class="col-md-6">
              <div class="card card-stats card-danger card-round">
                <div class="card-body"><div class="numbers"><p class="card-category">Absent Records</p><h4 class="card-title"><?php echo $absentCount; ?></h4></div></div>
              </div>
            </div>
          </div>

          <div class="card">
            <div class="card-header"><div class="card-title">Session Filter</div></div>
            <div class="card-body">
              <form method="GET" class="row">
                <div class="col-md-10">
                  <label for="academic_session_link">Academic Session</label>
                  <select class="form-control" id="academic_session_link" name="academic_session_link">
                    <?php foreach ($sessions as $session): ?>
                      <option value="<?php echo (int) $session['id']; ?>" <?php echo $selectedSessionId === (int) $session['id'] ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars((string) ($session['session_name'] . ' - ' . session_term_label($session['session_term'] ?? ''))); ?>
                      </option>
                    <?php endforeach; ?>
                  </select>
                </div>
                <div class="col-md-2 d-flex align-items-end">
                  <button type="submit" class="btn btn-primary w-100">Load</button>
                </div>
              </form>
            </div>
          </div>

          <div class="card mt-3">
            <div class="card-header"><div class="card-title">Attendance History</div></div>
            <div class="card-body">
              <?php if (empty($attendanceRecords)): ?>
                <div class="alert alert-info mb-0">No attendance records were found for the selected session.</div>
              <?php else: ?>
                <div class="table-responsive">
                  <table class="table table-bordered table-striped">
                    <thead><tr><th>Date</th><th>Class</th><th>Subject</th><th>Status</th><th>Remarks</th></tr></thead>
                    <tbody>
                      <?php foreach ($attendanceRecords as $record): ?>
                        <?php
                          $status = (string) ($record['status'] ?? '');
                          $badge = match ($status) {
                              'present' => 'success',
                              'absent' => 'danger',
                              'late' => 'warning',
                              default => 'secondary',
                          };
                        ?>
                        <tr>
                          <td><?php echo htmlspecialchars((string) ($record['date'] ?? '')); ?></td>
                          <td><?php echo htmlspecialchars(trim(($record['class_name'] ?? '') . ' ' . ($record['class_arm'] ?? ''))); ?></td>
                          <td><?php echo htmlspecialchars((string) ($record['subject_name'] ?? 'General Attendance')); ?></td>
                          <td><span class="badge badge-<?php echo $badge; ?>"><?php echo htmlspecialchars(ucfirst($status)); ?></span></td>
                          <td><?php echo htmlspecialchars((string) ($record['remarks'] ?? '')); ?></td>
                        </tr>
                      <?php endforeach; ?>
                    </tbody>
                  </table>
                </div>
              <?php endif; ?>
            </div>
          </div>
        </div>
      </div>
      @include('admin.partials.footer')
</body>
</html>
