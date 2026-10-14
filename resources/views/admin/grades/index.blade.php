<?php
require_once base_path('db/config.php');
require_once base_path('db/functions.php');
require_once base_path('db/school_portal_helpers.php');

if (!isset($_SESSION['adid'])) {
    header('Location: /admin/login.php');
    exit;
}

$message = $_SESSION['flash_success'] ?? '';
$error = $_SESSION['flash_error'] ?? '';
unset($_SESSION['flash_success'], $_SESSION['flash_error']);

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

        $_SESSION['flash_success'] = 'Grades saved successfully for ' . $saved . ' student(s).';

        // Post/Redirect/Get pattern
        $redirectUrl = '/admin/grades.php?academic_session_link=' . $selectedSessionId . '&class_link=' . $selectedClassId . '&subject_link=' . $selectedSubjectId . '&term=' . urlencode($selectedTerm) . '&exam_type=' . urlencode($selectedExamType);
        if (!headers_sent()) {
            header("Location: {$redirectUrl}", true, 303);
            exit;
        } else {
            echo "<script>window.location.replace(" . json_encode($redirectUrl) . ");</script>";
            exit;
        }
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
    $selectedClass = QueryDB('SELECT * FROM classes WHERE id = ? LIMIT 1', [$selectedClassId])->fetch(PDO::FETCH_ASSOC);

    if ($selectedClass) {
        $classCol = get_csa_class_column();
        $subjCol = get_csa_subject_column();
        $hasSubjectClassLevel = schema_has_column('subjects', 'class_level');
        if ($hasSubjectClassLevel) {
            [$levelClause, $levelParams] = build_subject_class_level_sql_clause($classLevel, (string) ($selectedClass['class_name'] ?? ''));
            $levelSql = "(s.status = 'active' AND {$levelClause})";
            $queryParams = array_merge([$selectedClassId], $levelParams);
        } else {
            $levelSql = "(s.status = 'active')";
            $queryParams = [$selectedClassId];
        }

        $subjects = QueryDB(
            "SELECT DISTINCT s.*
             FROM subjects s
             LEFT JOIN class_subject_assignments csa ON csa.{$subjCol} = s.id
             WHERE csa.{$classCol} = ?
                OR {$levelSql}
             ORDER BY s.subject_name",
            $queryParams
        )->fetchAll(PDO::FETCH_ASSOC);

        $students = get_students_by_class_and_session($selectedClassId, $selectedSessionId);
    }
}

$selectedSubject = null;
if ($selectedSubjectId > 0) {
    $selectedSubject = QueryDB('SELECT * FROM subjects WHERE id = ? LIMIT 1', [$selectedSubjectId])->fetch(PDO::FETCH_ASSOC);
}

if ($selectedClassId > 0 && $selectedSubjectId > 0 && $selectedSessionId > 0) {
    if (schema_has_table('student_grades')) {
        $selectedSession = QueryDB(
            'SELECT * FROM academic_sessions WHERE id = ? LIMIT 1',
            [$selectedSessionId]
        )->fetch(PDO::FETCH_ASSOC);
        $sessionName = (string) ($selectedSession['session_name'] ?? '');
        $termName = match (normalize_term_code($selectedTerm)) {
            '1' => 'first',
            '2' => 'second',
            '3' => 'third',
            default => 'first',
        };
        $assessmentType = in_array($selectedExamType, ['assignment', 'test'], true) ? $selectedExamType : 'exam';
        $assessmentName = ucwords(str_replace('_', ' ', strtolower($selectedExamType)));

        foreach (
            QueryDB(
                'SELECT student_id AS student_link, score, letter_grade AS grade, comments AS remarks
                 FROM student_grades
                 WHERE school_class_id = ? AND subject_id = ? AND (? = "" OR academic_year = ?) AND term = ? AND assessment_type = ? AND assessment_name = ?',
                [$selectedClassId, $selectedSubjectId, $sessionName, $sessionName, $termName, $assessmentType, $assessmentName]
            )->fetchAll() as $gradeRow
        ) {
            $existingGrades[(int) $gradeRow['student_link']] = $gradeRow;
        }
    } elseif (schema_has_table('grades')) {
        foreach (
            QueryDB(
                'SELECT * FROM grades WHERE class_link = ? AND subject_link = ? AND academic_session_link = ? AND term = ? AND exam_type = ?',
                [$selectedClassId, $selectedSubjectId, $selectedSessionId, $selectedTerm, $selectedExamType]
            )->fetchAll() as $gradeRow
        ) {
            $existingGrades[(int) $gradeRow['student_link']] = $gradeRow;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <title>Grades & Results Entry</title>
    @include('admin.partials.links')
</head>
<body>
    <div class="wrapper">
        @include('admin.partials.sidebar')

        <div class="main-panel">
            @include('admin.partials.header')
            <div class="container">
                <div class="page-inner">
                    <div class="d-flex align-items-left flex-column flex-md-row mb-3">
                        <div>
                            <h2 class="text-dark pb-2 fw-bold">Grades & Result Entry</h2>
                            <p class="text-muted">Enter exam and test scores. Dropped subjects and stream mismatches are automatically protected from grading.</p>
                        </div>
                        <div class="ml-md-auto py-2 py-md-0 d-flex gap-2">
                            <a href="{{ url('/admin/subjects/student_subjects.php') }}" class="btn btn-outline-primary btn-round">
                                <i class="fas fa-user-tag me-1"></i> Student Subjects & Drops
                            </a>
                        </div>
                    </div>

                    <?php if ($message !== ''): ?>
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            <i class="fas fa-check-circle me-2"></i><?php echo htmlspecialchars($message); ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    <?php endif; ?>
                    <?php if ($error !== ''): ?>
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <i class="fas fa-exclamation-triangle me-2"></i><?php echo htmlspecialchars($error); ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    <?php endif; ?>

                    <!-- Filter Form Card -->
                    <div class="card shadow-sm mb-4">
                        <div class="card-header bg-light">
                            <div class="card-title fw-bold"><i class="fas fa-sliders-h me-2 text-primary"></i>Grade Entry Setup</div>
                        </div>
                        <div class="card-body">
                            <form method="GET" class="row g-3">
                                <div class="col-md-3">
                                    <label for="academic_session_link" class="form-label fw-bold">Session</label>
                                    <select class="form-control" id="academic_session_link" name="academic_session_link">
                                        <?php foreach ($sessions as $session): ?>
                                            <option value="<?php echo (int) $session['id']; ?>" <?php echo $selectedSessionId === (int) $session['id'] ? 'selected' : ''; ?>>
                                                <?php echo htmlspecialchars((string) $session['session_name']); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <label for="class_link" class="form-label fw-bold">Class <span class="text-danger">*</span></label>
                                    <select class="form-control" id="class_link" name="class_link" required onchange="this.form.submit()">
                                        <option value="">Select Class</option>
                                        <?php foreach ($classes as $class): ?>
                                            <option value="<?php echo (int) $class['id']; ?>" <?php echo $selectedClassId === (int) $class['id'] ? 'selected' : ''; ?>>
                                                <?php echo htmlspecialchars($class['class_name'] . ' ' . $class['class_arm']); ?> (<?php echo htmlspecialchars($class['class_level'] ?? ''); ?>)
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <label for="subject_link" class="form-label fw-bold">Subject <span class="text-danger">*</span></label>
                                    <select class="form-control" id="subject_link" name="subject_link" required>
                                        <option value="">-- Select Subject --</option>
                                        <?php foreach ($subjects as $sub): ?>
                                            <?php 
                                                $sDept = strtolower((string) ($sub['department'] ?? 'general'));
                                                $deptTag = match($sDept) {
                                                    'science' => 'Sciences',
                                                    'arts' => 'Arts',
                                                    'commercial' => 'Commercial',
                                                    default => 'General',
                                                };
                                            ?>
                                            <option value="<?php echo (int) $sub['id']; ?>" <?php echo $selectedSubjectId === (int) $sub['id'] ? 'selected' : ''; ?>>
                                                <?php echo htmlspecialchars($sub['subject_name'] . ' (' . $sub['subject_code'] . ') [' . $deptTag . ']'); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <label for="term" class="form-label fw-bold">Term</label>
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
                                <div class="col-md-3">
                                    <label for="exam_type" class="form-label fw-bold">Assessment Type</label>
                                    <select class="form-control" id="exam_type" name="exam_type">
                                        <option value="test" <?php echo $selectedExamType === 'test' ? 'selected' : ''; ?>>Test (Continuous Assessment)</option>
                                        <option value="assignment" <?php echo $selectedExamType === 'assignment' ? 'selected' : ''; ?>>Assignment</option>
                                        <option value="midterm" <?php echo $selectedExamType === 'midterm' ? 'selected' : ''; ?>>Midterm Exam</option>
                                        <option value="final" <?php echo $selectedExamType === 'final' ? 'selected' : ''; ?>>Final Examination</option>
                                    </select>
                                </div>
                                <div class="col-md-9 d-flex align-items-end justify-content-end">
                                    <button type="submit" class="btn btn-primary px-4 fw-bold">
                                        <i class="fas fa-search me-1"></i> Load Students for Grading
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>

                    <?php if ($selectedClass && $selectedSubjectId > 0 && !empty($students)): ?>
                        <?php 
                            $subjDept = strtolower((string) ($selectedSubject['department'] ?? 'general'));
                            if ($subjDept === '' || $subjDept === 'all') {
                                $subjDept = 'general';
                            }
                        ?>
                        <div class="card shadow-sm border-top border-primary border-3">
                            <div class="card-header bg-light d-flex justify-content-between align-items-center flex-wrap gap-2">
                                <div>
                                    <div class="card-title fw-bold text-dark mb-0">
                                        <i class="fas fa-edit me-2 text-primary"></i>
                                        Grade Entry: <?php echo htmlspecialchars((string) ($selectedSubject['subject_name'] ?? '')); ?> (<?php echo htmlspecialchars((string) ($selectedSubject['subject_code'] ?? '')); ?>)
                                    </div>
                                    <div class="card-category text-muted">
                                        Class: <?php echo htmlspecialchars(trim(($selectedClass['class_name'] ?? '') . ' ' . ($selectedClass['class_arm'] ?? ''))); ?> |
                                        Assessment: <?php echo ucfirst($selectedExamType); ?> |
                                        Term: <?php echo htmlspecialchars($selectedTerm); ?>
                                    </div>
                                </div>
                                <div class="d-flex align-items-center gap-2">
                                    <span class="badge bg-primary text-white">Stream: <?php echo ucfirst($subjDept); ?></span>
                                    <span class="badge bg-secondary text-white"><?php echo count($students); ?> Student(s) Total</span>
                                </div>
                            </div>
                            <div class="card-body">
                                <form method="POST">
                                    <input type="hidden" name="action" value="save_grades">
                                    <input type="hidden" name="academic_session_link" value="<?php echo $selectedSessionId; ?>">
                                    <input type="hidden" name="class_link" value="<?php echo $selectedClassId; ?>">
                                    <input type="hidden" name="subject_link" value="<?php echo $selectedSubjectId; ?>">
                                    <input type="hidden" name="term" value="<?php echo htmlspecialchars($selectedTerm); ?>">
                                    <input type="hidden" name="exam_type" value="<?php echo htmlspecialchars($selectedExamType); ?>">

                                    <div class="table-responsive">
                                        <table class="table table-hover table-bordered align-middle">
                                            <thead class="table-light">
                                                 <tr>
                                                    <th style="width: 40px;">S/N</th>
                                                    <th>Admission No</th>
                                                    <th>Student Name</th>
                                                    <th>Student Stream</th>
                                                    <th>Enrollment Status</th>
                                                    <th style="width: 140px;">Score (0 - 100)</th>
                                                    <th style="width: 90px;">Grade</th>
                                                    <th>Remarks</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php 
                                                    $sn = 1; 
                                                    $activeEnrolledCount = 0;
                                                    foreach ($students as $student): 
                                                        $stDept = strtolower((string) ($student['department'] ?? 'general'));
                                                        if ($stDept === '') $stDept = 'general';

                                                        // Check student enrollment status for this subject
                                                        $rec = schema_has_table('student_subjects')
                                                            ? QueryDB(
                                                                "SELECT status, reason FROM student_subjects WHERE student_id = ? AND subject_id = ? AND (? = 0 OR session_id = ? OR session_id IS NULL) LIMIT 1",
                                                                [$student['id'], $selectedSubjectId, $selectedSessionId, $selectedSessionId]
                                                            )->fetch(PDO::FETCH_ASSOC)
                                                            : null;

                                                        $isExplicitEnrolled = $rec && ($rec['status'] === 'enrolled');
                                                        $isDropped = $rec && ($rec['status'] === 'dropped');
                                                        $matchesStream = subject_matches_student_department($subjDept, $stDept);

                                                        $canGrade = (!$isDropped && ($matchesStream || $isExplicitEnrolled));
                                                        if ($canGrade) {
                                                            $activeEnrolledCount++;
                                                        }

                                                        $existing = $existingGrades[(int) $student['id']] ?? null;
                                                        $scoreValue = $existing['score'] ?? '';
                                                        $gradeValue = $existing['grade'] ?? ($scoreValue !== '' ? nigerian_grade_from_score($scoreValue) : '');
                                                ?>
                                                    <tr class="<?php echo !$canGrade ? 'table-light text-muted' : ''; ?>">
                                                        <td><?php echo $sn++; ?></td>
                                                        <td><code><?php echo htmlspecialchars((string) $student['admission_no']); ?></code></td>
                                                        <td>
                                                            <strong class="<?php echo $canGrade ? 'text-dark' : 'text-muted'; ?>">
                                                                <?php echo htmlspecialchars(trim(($student['first_name'] ?? '') . ' ' . ($student['last_name'] ?? ''))); ?>
                                                            </strong>
                                                        </td>
                                                        <td>
                                                            <?php if ($stDept === 'science'): ?>
                                                                <span class="badge bg-info text-dark">Sciences</span>
                                                            <?php elseif ($stDept === 'arts'): ?>
                                                                <span class="badge text-white" style="background:#6f42c1;">Arts</span>
                                                            <?php elseif ($stDept === 'commercial'): ?>
                                                                <span class="badge bg-success">Commercial</span>
                                                            <?php else: ?>
                                                                <span class="badge bg-secondary">General</span>
                                                            <?php endif; ?>
                                                        </td>
                                                        <td>
                                                            <?php if ($isDropped): ?>
                                                                <span class="badge bg-danger text-white">
                                                                    <i class="fas fa-ban me-1"></i>Dropped
                                                                </span>
                                                                <?php if (!empty($rec['reason'])): ?>
                                                                    <br><small class="text-danger" style="font-size: 10px;"><?php echo htmlspecialchars($rec['reason']); ?></small>
                                                                <?php endif; ?>
                                                            <?php elseif ($canGrade): ?>
                                                                <span class="badge bg-success text-white">
                                                                    <i class="fas fa-check-circle me-1"></i>Enrolled (Active)
                                                                </span>
                                                            <?php else: ?>
                                                                <span class="badge bg-light text-muted border">
                                                                    Not Offering (Stream Mismatch)
                                                                </span>
                                                            <?php endif; ?>
                                                        </td>
                                                        <td>
                                                            <?php if ($canGrade): ?>
                                                                <input type="number" min="0" max="100" step="0.01" class="form-control score-input fw-bold" name="scores[<?php echo (int) $student['id']; ?>]" value="<?php echo htmlspecialchars((string) $scoreValue); ?>" placeholder="0.00">
                                                            <?php else: ?>
                                                                <input type="text" class="form-control bg-light" disabled value="<?php echo $isDropped ? 'Dropped / Exempt' : 'Not Offering'; ?>">
                                                            <?php endif; ?>
                                                        </td>
                                                        <td class="grade-preview fw-bold text-center">
                                                            <?php if ($canGrade): ?>
                                                                <span class="badge bg-info fs-6"><?php echo htmlspecialchars((string) $gradeValue); ?></span>
                                                            <?php else: ?>
                                                                <span class="text-muted">-</span>
                                                            <?php endif; ?>
                                                        </td>
                                                        <td>
                                                            <?php if ($canGrade): ?>
                                                                <input type="text" class="form-control" name="remarks[<?php echo (int) $student['id']; ?>]" value="<?php echo htmlspecialchars((string) ($existing['remarks'] ?? '')); ?>" placeholder="e.g. Excellent, Good">
                                                            <?php else: ?>
                                                                <small class="text-muted fst-italic">Exempt from assessment</small>
                                                            <?php endif; ?>
                                                        </td>
                                                    </tr>
                                                <?php endforeach; ?>
                                            </tbody>
                                        </table>
                                    </div>

                                    <div class="d-flex justify-content-between align-items-center mt-3">
                                        <span class="text-muted">
                                            <i class="fas fa-info-circle me-1"></i><strong><?php echo $activeEnrolledCount; ?></strong> student(s) currently active for this subject.
                                        </span>
                                        <button type="submit" class="btn btn-success px-5 fw-bold btn-lg">
                                            <i class="fas fa-save me-1"></i> Save Grades
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    <?php elseif ($selectedClass && $selectedSubjectId === 0): ?>
                        <div class="alert alert-info py-4 text-center">
                            <i class="fas fa-arrow-up fa-2x mb-2 d-block"></i>
                            Please select a <strong>Subject</strong> in the filter above to begin entering grades.
                        </div>
                    <?php endif; ?>

                </div>
            </div>
            @include('admin.partials.footer')
        </div>
    </div>

    <script>
        document.querySelectorAll('.score-input').forEach(function(input) {
            input.addEventListener('input', function() {
                var val = parseFloat(this.value);
                var row = this.closest('tr');
                var preview = row ? row.querySelector('.grade-preview span') : null;
                if (!preview) return;

                if (isNaN(val)) {
                    preview.textContent = '';
                    preview.className = 'badge bg-secondary fs-6';
                    return;
                }

                var grade = 'F9';
                var color = 'bg-danger';
                if (val >= 75) { grade = 'A1'; color = 'bg-success'; }
                else if (val >= 70) { grade = 'B2'; color = 'bg-success'; }
                else if (val >= 65) { grade = 'B3'; color = 'bg-primary'; }
                else if (val >= 60) { grade = 'C4'; color = 'bg-primary'; }
                else if (val >= 55) { grade = 'C5'; color = 'bg-info text-dark'; }
                else if (val >= 50) { grade = 'C6'; color = 'bg-info text-dark'; }
                else if (val >= 45) { grade = 'D7'; color = 'bg-warning text-dark'; }
                else if (val >= 40) { grade = 'E8'; color = 'bg-warning text-dark'; }

                preview.textContent = grade;
                preview.className = 'badge ' + color + ' fs-6';
            });
        });
    </script>
</body>
</html>
