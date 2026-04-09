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
$selectedClassId = (int) ($_REQUEST['class_link'] ?? ($accessibleClasses[0]['id'] ?? 0));
$selectedTerm = validate($_REQUEST['term'] ?? ((string) ($currentSession['session_term'] ?? '1')));
$selectedExamType = validate($_REQUEST['exam_type'] ?? 'test');
$message = '';
$error = '';

if ($selectedClassId > 0 && !teacher_has_class_access((int) $teacher['id'], $selectedClassId, $currentSessionId)) {
    $selectedClassId = (int) ($accessibleClasses[0]['id'] ?? 0);
}

$accessibleSubjects = $selectedClassId > 0 ? get_teacher_accessible_subjects((int) $teacher['id'], $selectedClassId, $currentSessionId) : [];
$selectedSubjectId = (int) ($_REQUEST['subject_link'] ?? ($accessibleSubjects[0]['subject_link'] ?? 0));

if ($selectedSubjectId > 0 && !teacher_has_subject_access((int) $teacher['id'], $selectedClassId, $selectedSubjectId, $currentSessionId)) {
    $selectedSubjectId = (int) ($accessibleSubjects[0]['subject_link'] ?? 0);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'save_grades') {
    try {
        if ($selectedClassId < 1 || !teacher_has_class_access((int) $teacher['id'], $selectedClassId, $currentSessionId)) {
            throw new Exception('Please choose one of your assigned classes.');
        }

        if ($selectedSubjectId < 1 || !teacher_has_subject_access((int) $teacher['id'], $selectedClassId, $selectedSubjectId, $currentSessionId)) {
            throw new Exception('You can only save scores for subjects assigned to you.');
        }

        $saved = save_bulk_grade_records(
            $selectedClassId,
            $selectedSubjectId,
            $currentSessionId,
            $selectedTerm,
            $selectedExamType,
            $_POST['scores'] ?? [],
            $_POST['remarks'] ?? []
        );

        $message = 'Scores saved for ' . $saved . ' student(s).';
    } catch (Throwable $e) {
        $error = $e->getMessage();
    }
}

$students = $selectedClassId > 0 ? get_students_by_class_and_session($selectedClassId, $currentSessionId) : [];
$existingGrades = [];

if ($selectedClassId > 0 && $selectedSubjectId > 0) {
    foreach (
        QueryDB(
            'SELECT * FROM grades WHERE class_link = ? AND subject_link = ? AND academic_session_link = ? AND term = ? AND exam_type = ?',
            [$selectedClassId, $selectedSubjectId, $currentSessionId, $selectedTerm, $selectedExamType]
        )->fetchAll() as $gradeRow
    ) {
        $existingGrades[(int) $gradeRow['student_link']] = $gradeRow;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta http-equiv="X-UA-Compatible" content="IE=edge" />
  <title>Teacher Grades</title>
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
            <h2 class="text-dark pb-2 fw-bold">Scores & Grades</h2>
          </div>

          <?php if ($currentSession): ?>
            <div class="alert alert-info">
              Working session: <?php echo htmlspecialchars((string) ($currentSession['session_name'] . ' - ' . session_term_label($currentSession['session_term'] ?? ''))); ?>
            </div>
          <?php endif; ?>

          <?php if ($message !== ''): ?>
            <div class="alert alert-success"><?php echo htmlspecialchars($message); ?></div>
          <?php endif; ?>
          <?php if ($error !== ''): ?>
            <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
          <?php endif; ?>

          <div class="card">
            <div class="card-header"><div class="card-title">Assessment Setup</div></div>
            <div class="card-body">
              <form method="GET" class="row">
                <div class="col-md-4">
                  <label for="class_link">Class</label>
                  <select class="form-control" id="class_link" name="class_link">
                    <option value="">Select Class</option>
                    <?php foreach ($accessibleClasses as $class): ?>
                      <option value="<?php echo (int) $class['id']; ?>" <?php echo $selectedClassId === (int) $class['id'] ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars(trim(($class['class_name'] ?? '') . ' ' . ($class['class_arm'] ?? ''))); ?>
                      </option>
                    <?php endforeach; ?>
                  </select>
                </div>
                <div class="col-md-3">
                  <label for="subject_link">Subject</label>
                  <select class="form-control" id="subject_link" name="subject_link">
                    <option value="">Select Subject</option>
                    <?php foreach ($accessibleSubjects as $subject): ?>
                      <option value="<?php echo (int) $subject['subject_link']; ?>" <?php echo $selectedSubjectId === (int) $subject['subject_link'] ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars((string) $subject['subject_name']); ?>
                      </option>
                    <?php endforeach; ?>
                  </select>
                </div>
                <div class="col-md-2">
                  <label for="term">Term</label>
                  <select class="form-control" id="term" name="term">
                    <option value="1" <?php echo $selectedTerm === '1' ? 'selected' : ''; ?>>First Term</option>
                    <option value="2" <?php echo $selectedTerm === '2' ? 'selected' : ''; ?>>Second Term</option>
                    <option value="3" <?php echo $selectedTerm === '3' ? 'selected' : ''; ?>>Third Term</option>
                  </select>
                </div>
                <div class="col-md-2">
                  <label for="exam_type">Assessment</label>
                  <select class="form-control" id="exam_type" name="exam_type">
                    <option value="test" <?php echo $selectedExamType === 'test' ? 'selected' : ''; ?>>Test</option>
                    <option value="assignment" <?php echo $selectedExamType === 'assignment' ? 'selected' : ''; ?>>Assignment</option>
                    <option value="midterm" <?php echo $selectedExamType === 'midterm' ? 'selected' : ''; ?>>Midterm</option>
                    <option value="final" <?php echo $selectedExamType === 'final' ? 'selected' : ''; ?>>Final</option>
                  </select>
                </div>
                <div class="col-md-1 d-flex align-items-end">
                  <button type="submit" class="btn btn-primary w-100">Load</button>
                </div>
              </form>
            </div>
          </div>

          <?php if ($selectedClassId > 0 && empty($accessibleSubjects)): ?>
            <div class="alert alert-info mt-3">No subject assignments are available for this class yet. Ask the admin to assign your subjects first.</div>
          <?php endif; ?>

          <?php if ($selectedClassId > 0 && $selectedSubjectId > 0 && !empty($students)): ?>
            <div class="card mt-3">
              <div class="card-header">
                <div class="card-title">Bulk Score Entry</div>
                <div class="card-category">Nigerian grading is calculated automatically from each score.</div>
              </div>
              <div class="card-body">
                <form method="POST">
                  <input type="hidden" name="action" value="save_grades">
                  <input type="hidden" name="class_link" value="<?php echo $selectedClassId; ?>">
                  <input type="hidden" name="subject_link" value="<?php echo $selectedSubjectId; ?>">
                  <input type="hidden" name="term" value="<?php echo htmlspecialchars($selectedTerm); ?>">
                  <input type="hidden" name="exam_type" value="<?php echo htmlspecialchars($selectedExamType); ?>">
                  <div class="table-responsive">
                    <table class="table table-bordered table-striped">
                      <thead>
                        <tr>
                          <th>Admission No</th>
                          <th>Student</th>
                          <th>Score</th>
                          <th>Grade</th>
                          <th>Remarks</th>
                        </tr>
                      </thead>
                      <tbody>
                        <?php foreach ($students as $student): ?>
                          <?php
                            $existing = $existingGrades[(int) $student['id']] ?? null;
                            $scoreValue = $existing['score'] ?? '';
                            $gradeValue = $existing['grade'] ?? ($scoreValue !== '' ? nigerian_grade_from_score($scoreValue) : '');
                          ?>
                          <tr>
                            <td><?php echo htmlspecialchars((string) $student['admission_no']); ?></td>
                            <td><?php echo htmlspecialchars(trim(($student['first_name'] ?? '') . ' ' . ($student['last_name'] ?? '') . ' ' . ($student['other_names'] ?? ''))); ?></td>
                            <td><input type="number" min="0" max="100" step="0.01" class="form-control score-input" name="scores[<?php echo (int) $student['id']; ?>]" value="<?php echo htmlspecialchars((string) $scoreValue); ?>"></td>
                            <td class="grade-preview"><?php echo htmlspecialchars((string) $gradeValue); ?></td>
                            <td><input type="text" class="form-control" name="remarks[<?php echo (int) $student['id']; ?>]" value="<?php echo htmlspecialchars((string) ($existing['remarks'] ?? '')); ?>"></td>
                          </tr>
                        <?php endforeach; ?>
                      </tbody>
                    </table>
                  </div>
                  <button type="submit" class="btn btn-success">Save Scores</button>
                </form>
              </div>
            </div>
          <?php elseif ($selectedClassId > 0 && $selectedSubjectId > 0): ?>
            <div class="alert alert-info mt-3">No students were found for this class in the active session.</div>
          <?php endif; ?>
        </div>
      </div>
      @include('admin.partials.footer')
      <script>
        const scoreToGrade = (score) => {
          const value = parseFloat(score);
          if (Number.isNaN(value)) return '';
          if (value >= 70) return 'A1';
          if (value >= 60) return 'B2';
          if (value >= 50) return 'B3';
          if (value >= 45) return 'C4';
          if (value >= 40) return 'C5';
          if (value >= 35) return 'C6';
          if (value >= 30) return 'D7';
          if (value >= 25) return 'E8';
          return 'F9';
        };

        document.querySelectorAll('.score-input').forEach((input) => {
          input.addEventListener('input', () => {
            input.closest('tr').querySelector('.grade-preview').textContent = scoreToGrade(input.value);
          });
        });
      </script>
</body>
</html>
