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
$accessibleClasses = get_teacher_accessible_classes((int) $teacher['id'], $currentSessionId);
$selectedClassId = (int) ($_GET['class_link'] ?? ($accessibleClasses[0]['id'] ?? 0));

if ($selectedClassId > 0 && !teacher_has_class_access((int) $teacher['id'], $selectedClassId, $currentSessionId)) {
    $selectedClassId = (int) ($accessibleClasses[0]['id'] ?? 0);
}

$students = $selectedClassId > 0 ? get_students_by_class_and_session($selectedClassId, $currentSessionId) : [];
$subjectAssignments = $selectedClassId > 0 ? get_teacher_accessible_subjects((int) $teacher['id'], $selectedClassId, $currentSessionId) : [];
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta http-equiv="X-UA-Compatible" content="IE=edge" />
  <title>My Students</title>
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
            <h2 class="text-dark pb-2 fw-bold">My Students</h2>
          </div>

          <?php if ($currentSession): ?>
            <div class="alert alert-info">
              Working session: <?php echo htmlspecialchars((string) ($currentSession['session_name'] . ' - ' . session_term_label($currentSession['session_term'] ?? ''))); ?>
            </div>
          <?php endif; ?>

          <div class="card">
            <div class="card-header"><div class="card-title">Class Filter</div></div>
            <div class="card-body">
              <form method="GET" class="row">
                <div class="col-md-8">
                  <label for="class_link">Assigned Class</label>
                  <select class="form-control" id="class_link" name="class_link">
                    <option value="">Select Class</option>
                    <?php foreach ($accessibleClasses as $class): ?>
                      <option value="<?php echo (int) $class['id']; ?>" <?php echo $selectedClassId === (int) $class['id'] ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars(trim(($class['class_name'] ?? '') . ' ' . ($class['class_arm'] ?? ''))); ?>
                      </option>
                    <?php endforeach; ?>
                  </select>
                </div>
                <div class="col-md-4 d-flex align-items-end">
                  <button type="submit" class="btn btn-primary w-100">Load Students</button>
                </div>
              </form>
            </div>
          </div>

          <?php if ($selectedClassId > 0 && !empty($subjectAssignments)): ?>
            <div class="alert alert-info">
              Subject access for this class:
              <?php echo htmlspecialchars(implode(', ', array_map(static fn ($item) => (string) $item['subject_name'], $subjectAssignments))); ?>
            </div>
          <?php endif; ?>

          <div class="card">
            <div class="card-header"><div class="card-title">Students</div></div>
            <div class="card-body">
              <?php if ($selectedClassId < 1): ?>
                <div class="alert alert-info mb-0">No classes are available for your account yet.</div>
              <?php elseif (empty($students)): ?>
                <div class="alert alert-info mb-0">No students were found for this class in the active session.</div>
              <?php else: ?>
                <div class="table-responsive">
                  <table class="table table-bordered table-striped">
                    <thead>
                      <tr>
                        <th>Admission No</th>
                        <th>Name</th>
                        <th>Gender</th>
                        <th>Status</th>
                        <th>Attendance %</th>
                        <th>Average Score</th>
                      </tr>
                    </thead>
                    <tbody>
                      <?php foreach ($students as $student): ?>
                        <tr>
                          <td><?php echo htmlspecialchars((string) $student['admission_no']); ?></td>
                          <td><?php echo htmlspecialchars(trim(($student['first_name'] ?? '') . ' ' . ($student['last_name'] ?? '') . ' ' . ($student['other_names'] ?? ''))); ?></td>
                          <td><?php echo htmlspecialchars(ucfirst((string) ($student['gender'] ?? 'n/a'))); ?></td>
                          <td><span class="badge badge-<?php echo ($student['status'] ?? '') === 'active' ? 'success' : 'warning'; ?>"><?php echo htmlspecialchars(ucfirst((string) ($student['status'] ?? 'unknown'))); ?></span></td>
                          <td><?php echo number_format((float) student_attendance_percentage((int) $student['id']), 1); ?>%</td>
                          <td><?php echo number_format((float) student_grade_average((int) $student['id']), 1); ?></td>
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
