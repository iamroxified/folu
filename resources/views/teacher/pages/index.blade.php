<?php
require_once base_path('db/config.php');
require_once base_path('db/functions.php');

if (!isset($_SESSION['teacher_user_id'], $_SESSION['teacher_id'])) {
    header('Location: /teacher/login.php');
    exit;
}

$teacher = get_teacher_profile_by_user_id((int) $_SESSION['teacher_user_id']);

if (!$teacher) {
    header('Location: /teacher/logout.php');
    exit;
}

$currentSessionId = get_teacher_default_session_id((int) $teacher['id']) ?? 0;
$currentSession = $currentSessionId > 0
    ? QueryDB('SELECT * FROM academic_sessions WHERE id = ? LIMIT 1', [$currentSessionId])->fetch(PDO::FETCH_ASSOC)
    : null;
$classAssignments = get_teacher_class_assignments((int) $teacher['id'], $currentSessionId);
$subjectAssignments = get_teacher_subject_assignments((int) $teacher['id'], $currentSessionId);
$accessibleClasses = get_teacher_accessible_classes((int) $teacher['id'], $currentSessionId);
$announcements = get_announcements_for_audience('teachers', $currentSessionId);
$studentIds = [];

foreach ($accessibleClasses as $class) {
    foreach (get_students_by_class_and_session((int) $class['id'], $currentSessionId) as $student) {
        $studentIds[(int) $student['id']] = true;
    }
}

$attendanceCount = (int) QueryDB(
    'SELECT COUNT(*) FROM attendance WHERE marked_by_user_link = ? AND (? = 0 OR academic_session_link = ?)',
    [(int) $teacher['user_link'], $currentSessionId, $currentSessionId]
)->fetchColumn();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta http-equiv="X-UA-Compatible" content="IE=edge" />
  <title>Teacher Dashboard</title>
  @include('admin.partials.links')
</head>
<body>
  <div class="wrapper">
    @include('teacher.partials.sidebar')
    <div class="main-panel">
      @include('teacher.partials.header')
      <div class="container">
        <div class="page-inner">
                    <div class="d-flex align-items-left flex-column flex-md-row">
                        <h2 class="text-dark pb-2 fw-bold">Teacher Dashboard</h2>
                    </div>

                    <?php if ($currentSession): ?>
                        <div class="alert alert-info">
                            Working session: <?php echo htmlspecialchars((string) ($currentSession['session_name'] . ' - ' . session_term_label($currentSession['session_term'] ?? ''))); ?>
                        </div>
                    <?php endif; ?>

          <div class="row">
            <div class="col-md-3">
              <div class="card card-stats card-primary card-round">
                <div class="card-body"><div class="numbers"><p class="card-category">Assigned Classes</p><h4 class="card-title"><?php echo count($accessibleClasses); ?></h4></div></div>
              </div>
            </div>
            <div class="col-md-3">
              <div class="card card-stats card-info card-round">
                <div class="card-body"><div class="numbers"><p class="card-category">Subject Assignments</p><h4 class="card-title"><?php echo count($subjectAssignments); ?></h4></div></div>
              </div>
            </div>
            <div class="col-md-3">
              <div class="card card-stats card-success card-round">
                <div class="card-body"><div class="numbers"><p class="card-category">Students in View</p><h4 class="card-title"><?php echo count($studentIds); ?></h4></div></div>
              </div>
            </div>
            <div class="col-md-3">
              <div class="card card-stats card-warning card-round">
                <div class="card-body"><div class="numbers"><p class="card-category">Attendance Entries</p><h4 class="card-title"><?php echo $attendanceCount; ?></h4></div></div>
              </div>
            </div>
          </div>

          <div class="row">
            <div class="col-md-6">
              <div class="card">
                <div class="card-header"><div class="card-title">Assigned Classes</div></div>
                <div class="card-body">
                  <?php if (empty($classAssignments) && empty($accessibleClasses)): ?>
                    <div class="alert alert-info mb-0">No classes have been assigned to you yet.</div>
                  <?php else: ?>
                    <ul class="list-group">
                      <?php foreach ($accessibleClasses as $class): ?>
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                          <span><?php echo htmlspecialchars(trim(($class['class_name'] ?? '') . ' ' . ($class['class_arm'] ?? ''))); ?></span>
                          <span class="badge badge-primary"><?php echo htmlspecialchars(ucwords(str_replace('_', ' ', (string) ($class['assignment_role'] ?? 'class_teacher')))); ?></span>
                        </li>
                      <?php endforeach; ?>
                    </ul>
                  <?php endif; ?>
                </div>
              </div>
            </div>
            <div class="col-md-6">
              <div class="card">
                <div class="card-header"><div class="card-title">Assigned Subjects</div></div>
                <div class="card-body">
                  <?php if (empty($subjectAssignments)): ?>
                    <div class="alert alert-info mb-0">No subject assignments have been added yet.</div>
                  <?php else: ?>
                    <ul class="list-group">
                      <?php foreach ($subjectAssignments as $assignment): ?>
                        <li class="list-group-item">
                          <strong><?php echo htmlspecialchars((string) $assignment['subject_name']); ?></strong>
                          <div class="text-muted"><?php echo htmlspecialchars(trim(($assignment['class_name'] ?? '') . ' ' . ($assignment['class_arm'] ?? ''))); ?></div>
                        </li>
                      <?php endforeach; ?>
                    </ul>
                  <?php endif; ?>
                </div>
              </div>
            </div>
          </div>

          <div class="row">
            <div class="col-md-8">
              <div class="card">
                <div class="card-header"><div class="card-title">Announcements</div></div>
                <div class="card-body">
                  <?php if (empty($announcements)): ?>
                    <div class="alert alert-info mb-0">No announcements available right now.</div>
                  <?php else: ?>
                    <?php foreach (array_slice($announcements, 0, 5) as $announcement): ?>
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
            <div class="col-md-4">
              <div class="card">
                <div class="card-header"><div class="card-title">Quick Actions</div></div>
                <div class="card-body d-grid gap-2">
                  <a href="{{ url('/teacher/students.php') }}" class="btn btn-primary">View My Students</a>
                  <a href="{{ url('/teacher/attendance.php') }}" class="btn btn-info">Mark Attendance</a>
                  <a href="{{ url('/teacher/grades.php') }}" class="btn btn-success">Enter Scores</a>
                  <a href="{{ url('/teacher/account.php') }}" class="btn btn-secondary">Change Password</a>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
      @include('admin.partials.footer')
</body>
</html>
