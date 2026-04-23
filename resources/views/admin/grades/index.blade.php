<?php
require_once base_path('db/config.php');
require_once base_path('db/functions.php');

if (!isset($_SESSION['adid'])) {
    header('Location: /admin/login.php');
    exit;
}

$message = '';
$error = '';

$currentSessionId = get_current_academic_session_id() ?? 0;
$selectedSessionId = (int) ($_REQUEST['academic_session_link'] ?? $currentSessionId);
$selectedClassId = (int) ($_REQUEST['class_link'] ?? 0);
$selectedSubjectId = (int) ($_REQUEST['subject_link'] ?? 0);
$currentTerm = get_current_academic_term($selectedSessionId > 0 ? $selectedSessionId : $currentSessionId);
$selectedTerm = validate($_REQUEST['term'] ?? ((string) ($currentTerm['term_code'] ?? '1')));
$selectedExamType = validate($_REQUEST['exam_type'] ?? 'test');

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'save_grades') {
    try {
        $selectedSessionId = (int) ($_POST['academic_session_link'] ?? 0);
        $selectedClassId = (int) ($_POST['class_link'] ?? 0);
        $selectedSubjectId = (int) ($_POST['subject_link'] ?? 0);
        $selectedTerm = validate($_POST['term'] ?? '1');
        $selectedExamType = validate($_POST['exam_type'] ?? 'test');

        if ($selectedSessionId < 1 || $selectedClassId < 1 || $selectedSubjectId < 1) {
            throw new Exception('Please select a session, class, and subject.');
        }

        $saved = save_bulk_grade_records(
            $selectedClassId,
            $selectedSubjectId,
            $selectedSessionId,
            $selectedTerm,
            $selectedExamType,
            $_POST['scores'] ?? [],
            $_POST['remarks'] ?? []
        );

        $message = 'Grades saved successfully for ' . $saved . ' student(s).';
    } catch (Throwable $e) {
        $error = $e->getMessage();
    }
}

$sessions = QueryDB('SELECT * FROM academic_sessions ORDER BY start_date DESC, id DESC')->fetchAll();
$terms = $selectedSessionId > 0 ? get_terms_for_session($selectedSessionId) : [];
$classes = QueryDB('SELECT * FROM classes ORDER BY class_level, class_name, class_arm')->fetchAll();
$subjects = [];
$students = [];
$existingGrades = [];
$selectedClass = null;

if ($selectedClassId > 0) {
    $selectedClass = QueryDB('SELECT * FROM classes WHERE id = ?', [$selectedClassId])->fetch(PDO::FETCH_ASSOC);

    if ($selectedClass) {
        $subjects = QueryDB(
            "SELECT * FROM subjects WHERE class_level = ? OR class_level = 'ALL' ORDER BY subject_name",
            [$selectedClass['class_level']]
        )->fetchAll();

        $students = get_students_by_class_and_session($selectedClassId, $selectedSessionId);
    }
}

if ($selectedClassId > 0 && $selectedSubjectId > 0 && $selectedSessionId > 0) {
    foreach (
        QueryDB(
            'SELECT * FROM grades WHERE class_link = ? AND subject_link = ? AND academic_session_link = ? AND term = ? AND exam_type = ?',
            [$selectedClassId, $selectedSubjectId, $selectedSessionId, $selectedTerm, $selectedExamType]
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
    <title>Grades</title>
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
                        <h2 class="text-dark pb-2 fw-bold">Grades</h2>
                    </div>

                    <?php if ($message !== ''): ?>
                        <div class="alert alert-success"><?php echo htmlspecialchars($message); ?></div>
                    <?php endif; ?>
                    <?php if ($error !== ''): ?>
                        <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
                    <?php endif; ?>

                    <div class="card">
                        <div class="card-header">
                            <div class="card-title">Grade Entry Setup</div>
                        </div>
                        <div class="card-body">
                            <form method="GET" class="row">
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label for="academic_session_link">Session</label>
                                        <select class="form-control" id="academic_session_link" name="academic_session_link">
                                            <?php foreach ($sessions as $session): ?>
                                                <option value="<?php echo (int) $session['id']; ?>" <?php echo $selectedSessionId === (int) $session['id'] ? 'selected' : ''; ?>>
                                                    <?php echo htmlspecialchars((string) $session['session_name']); ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label for="class_link">Class</label>
                                        <select class="form-control" id="class_link" name="class_link">
                                            <option value="">Select Class</option>
                                            <?php foreach ($classes as $class): ?>
                                                <option value="<?php echo (int) $class['id']; ?>" <?php echo $selectedClassId === (int) $class['id'] ? 'selected' : ''; ?>>
                                                    <?php echo htmlspecialchars($class['class_name'] . ' ' . $class['class_arm']); ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-2">
                                    <div class="form-group">
                                        <label for="term">Term</label>
                                        <select class="form-control" id="term" name="term">
                                            <?php if (empty($terms)): ?>
                                                <option value="1" <?php echo $selectedTerm === '1' ? 'selected' : ''; ?>>First Term</option>
                                                <option value="2" <?php echo $selectedTerm === '2' ? 'selected' : ''; ?>>Second Term</option>
                                                <option value="3" <?php echo $selectedTerm === '3' ? 'selected' : ''; ?>>Third Term</option>
                                            <?php else: ?>
                                                <?php foreach ($terms as $term): ?>
                                                    <option value="<?php echo htmlspecialchars((string) $term['term_code']); ?>" <?php echo $selectedTerm === (string) $term['term_code'] ? 'selected' : ''; ?>>
                                                        <?php echo htmlspecialchars(term_label($term)); ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            <?php endif; ?>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-2">
                                    <div class="form-group">
                                        <label for="exam_type">Assessment</label>
                                        <select class="form-control" id="exam_type" name="exam_type">
                                            <option value="test" <?php echo $selectedExamType === 'test' ? 'selected' : ''; ?>>Test</option>
                                            <option value="assignment" <?php echo $selectedExamType === 'assignment' ? 'selected' : ''; ?>>Assignment</option>
                                            <option value="midterm" <?php echo $selectedExamType === 'midterm' ? 'selected' : ''; ?>>Midterm</option>
                                            <option value="final" <?php echo $selectedExamType === 'final' ? 'selected' : ''; ?>>Final</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-2 d-flex align-items-end">
                                    <button type="submit" class="btn btn-primary w-100">Load Students</button>
                                </div>
                            </form>
                        </div>
                    </div>

                    <?php if ($selectedClass && !empty($students)): ?>
                        <div class="card mt-3">
                            <div class="card-header">
                                <div class="card-title">Bulk Grade Entry</div>
                                <div class="card-category">Nigerian grading is applied automatically from the score.</div>
                            </div>
                            <div class="card-body">
                                <form method="POST">
                                    <input type="hidden" name="action" value="save_grades">
                                    <input type="hidden" name="academic_session_link" value="<?php echo $selectedSessionId; ?>">
                                    <input type="hidden" name="class_link" value="<?php echo $selectedClassId; ?>">
                                    <input type="hidden" name="term" value="<?php echo htmlspecialchars($selectedTerm); ?>">
                                    <input type="hidden" name="exam_type" value="<?php echo htmlspecialchars($selectedExamType); ?>">

                                    <div class="row mb-3">
                                        <div class="col-md-4">
                                            <label for="subject_link">Subject</label>
                                            <select class="form-control" id="subject_link" name="subject_link" required>
                                                <option value="">Select Subject</option>
                                                <?php foreach ($subjects as $subject): ?>
                                                    <option value="<?php echo (int) $subject['id']; ?>" <?php echo $selectedSubjectId === (int) $subject['id'] ? 'selected' : ''; ?>>
                                                        <?php echo htmlspecialchars($subject['subject_name']); ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                    </div>

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
                                                        <td><?php echo htmlspecialchars($student['admission_no']); ?></td>
                                                        <td><?php echo htmlspecialchars(trim($student['first_name'] . ' ' . $student['last_name'])); ?></td>
                                                        <td>
                                                            <input type="number" min="0" max="100" step="0.01" class="form-control score-input" name="scores[<?php echo (int) $student['id']; ?>]" value="<?php echo htmlspecialchars((string) $scoreValue); ?>">
                                                        </td>
                                                        <td class="grade-preview"><?php echo htmlspecialchars((string) $gradeValue); ?></td>
                                                        <td>
                                                            <input type="text" class="form-control" name="remarks[<?php echo (int) $student['id']; ?>]" value="<?php echo htmlspecialchars((string) ($existing['remarks'] ?? '')); ?>">
                                                        </td>
                                                    </tr>
                                                <?php endforeach; ?>
                                            </tbody>
                                        </table>
                                    </div>

                                    <button type="submit" class="btn btn-success">Save Grades</button>
                                </form>
                            </div>
                        </div>
                    <?php elseif ($selectedClassId > 0): ?>
                        <div class="alert alert-info mt-3">No students were found for the selected class and session.</div>
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
                        const row = input.closest('tr');
                        const target = row.querySelector('.grade-preview');
                        target.textContent = scoreToGrade(input.value);
                    });
                });
            </script>
</body>
</html>
