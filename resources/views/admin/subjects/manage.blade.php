<?php
require_once base_path('db/config.php');
require_once base_path('db/functions.php');

if (!isset($_SESSION['adid'])) {
    header('Location: /admin/login.php');
    exit;
}

// PRG Flash messages
$message = $_SESSION['flash_success'] ?? '';
$error = $_SESSION['flash_error'] ?? '';
unset($_SESSION['flash_success'], $_SESSION['flash_error']);

$editId = (int) ($_GET['edit'] ?? 0);
$teacherAssignmentCountSelect = schema_has_table('teacher_subjects')
    ? '(SELECT COUNT(*) FROM teacher_subjects ts WHERE ts.subject_link = s.id) AS teacher_assignment_count'
    : '0 AS teacher_assignment_count';
$csaSubjCol = get_csa_subject_column();
$classAssignmentCountSelect = schema_has_table('class_subject_assignments')
    ? "(SELECT COUNT(*) FROM class_subject_assignments csa WHERE csa.{$csaSubjCol} = s.id) AS class_assignment_count"
    : '0 AS class_assignment_count';
$resultCountSelect = schema_has_table('student_grades')
    ? '(SELECT COUNT(*) FROM student_grades g WHERE g.subject_id = s.id) AS result_count'
    : (schema_has_table('grades')
        ? '(SELECT COUNT(*) FROM grades g WHERE g.subject_link = s.id) AS result_count'
        : '0 AS result_count');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = (string) ($_POST['action'] ?? '');

    try {
        if ($action === 'save_subject') {
            $subjectId = (int) ($_POST['subject_id'] ?? 0);
            $subjectName = validate($_POST['subject_name'] ?? '');
            $subjectCode = strtoupper(validate($_POST['subject_code'] ?? ''));
            $classLevel = validate($_POST['class_level'] ?? 'ALL');
            $department = strtolower(validate($_POST['department'] ?? 'general'));
            if (!in_array($department, ['all', 'general', 'science', 'arts', 'commercial'], true)) {
                $department = 'general';
            }
            $subjectType = validate($_POST['subject_type'] ?? 'core');
            $subjectType = in_array($subjectType, ['core', 'elective', 'optional'], true) ? $subjectType : 'core';
            $gradeLevels = $classLevel !== '' ? json_encode([$classLevel], JSON_UNESCAPED_SLASHES) : null;

            if ($subjectName === '' || $subjectCode === '' || $classLevel === '') {
                throw new Exception('Please fill in the subject name, subject code, and class level.');
            }

            $duplicate = QueryDB(
                'SELECT COUNT(*) FROM subjects WHERE subject_code = ? AND id != ?',
                [$subjectCode, $subjectId]
            )->fetchColumn();

            if ((int) $duplicate > 0) {
                throw new Exception("Subject code '{$subjectCode}' is already registered for another subject.");
            }

            if ($subjectId > 0) {
                QueryDB(
                    'UPDATE subjects SET subject_name = ?, subject_code = ?, class_level = ?, department = ?, grade_levels = ?, subject_type = ?, updated_at = NOW() WHERE id = ?',
                    [$subjectName, $subjectCode, $classLevel, $department, $gradeLevels, $subjectType, $subjectId]
                );
                $_SESSION['flash_success'] = "Subject '{$subjectName}' ({$subjectCode}) updated successfully.";
            } else {
                QueryDB(
                    'INSERT INTO subjects (subject_name, subject_code, class_level, department, grade_levels, subject_type, status, created_at, updated_at) VALUES (?, ?, ?, ?, ?, ?, ?, NOW(), NOW())',
                    [$subjectName, $subjectCode, $classLevel, $department, $gradeLevels, $subjectType, 'active']
                );
                $_SESSION['flash_success'] = "Subject '{$subjectName}' ({$subjectCode}) created successfully.";
            }

            // Post/Redirect/Get pattern
            if (!headers_sent()) {
                header('Location: /admin/subjects/manage.php', true, 303);
                exit;
            } else {
                echo "<script>window.location.replace('/admin/subjects/manage.php');</script>";
                exit;
            }
        } elseif ($action === 'delete_subject') {
            $subjectId = (int) ($_POST['subject_id'] ?? 0);

            if ($subjectId < 1) {
                throw new Exception('Please choose a valid subject to delete.');
            }

            $hasAssignments = 0;
            if (schema_has_table('teacher_subjects')) {
                $hasAssignments += (int) QueryDB('SELECT COUNT(*) FROM teacher_subjects WHERE subject_link = ?', [$subjectId])->fetchColumn();
            }
            if (schema_has_table('class_subject_assignments')) {
                $csaSubjCol = get_csa_subject_column();
                $hasAssignments += (int) QueryDB("SELECT COUNT(*) FROM class_subject_assignments WHERE {$csaSubjCol} = ?", [$subjectId])->fetchColumn();
            }
            if (schema_has_table('student_grades')) {
                $hasAssignments += (int) QueryDB('SELECT COUNT(*) FROM student_grades WHERE subject_id = ?', [$subjectId])->fetchColumn();
            } elseif (schema_has_table('grades')) {
                $hasAssignments += (int) QueryDB('SELECT COUNT(*) FROM grades WHERE subject_link = ?', [$subjectId])->fetchColumn();
            }
            if (schema_has_table('class_timetables')) {
                $hasAssignments += (int) QueryDB('SELECT COUNT(*) FROM class_timetables WHERE subject_link = ?', [$subjectId])->fetchColumn();
            }

            if ($hasAssignments > 0) {
                throw new Exception('This subject is already in use. Remove its teacher, class, grade, and timetable references before deleting it.');
            }

            QueryDB('DELETE FROM subjects WHERE id = ?', [$subjectId]);
            $_SESSION['flash_success'] = 'Subject deleted successfully.';

            if (!headers_sent()) {
                header('Location: /admin/subjects/manage.php', true, 303);
                exit;
            } else {
                echo "<script>window.location.replace('/admin/subjects/manage.php');</script>";
                exit;
            }
        }
    } catch (Throwable $e) {
        $error = $e->getMessage();
    }
}

$editingSubject = $editId > 0
    ? QueryDB('SELECT * FROM subjects WHERE id = ? LIMIT 1', [$editId])->fetch(PDO::FETCH_ASSOC)
    : null;
$editingSubjectLevel = (string) ($editingSubject['class_level'] ?? '');
if ($editingSubjectLevel === '' && $editingSubject && !empty($editingSubject['grade_levels'])) {
    $decodedLevels = json_decode((string) $editingSubject['grade_levels'], true);
    $editingSubjectLevel = (string) ($decodedLevels[0] ?? 'ALL');
}
$editingSubjectDept = strtolower((string) ($editingSubject['department'] ?? 'general'));

$subjects = QueryDB(
    "SELECT s.*,
            {$teacherAssignmentCountSelect},
            {$classAssignmentCountSelect},
            {$resultCountSelect}
     FROM subjects s
     ORDER BY s.subject_name, s.subject_code"
)->fetchAll();

$classLevelOptions = [
    'ALL'  => 'All Classes (School-wide)',
    'SSS'  => 'Senior Secondary (All SSS: SSS1, SSS2, SSS3)',
    'SSS1' => 'SSS 1 (Senior Secondary 1)',
    'SSS2' => 'SSS 2 (Senior Secondary 2)',
    'SSS3' => 'SSS 3 (Senior Secondary 3)',
    'JSS'  => 'Junior Secondary (All JSS: JSS1, JSS2, JSS3)',
    'JSS1' => 'JSS 1 (Junior Secondary 1)',
    'JSS2' => 'JSS 2 (Junior Secondary 2)',
    'JSS3' => 'JSS 3 (Junior Secondary 3)',
    'PRI'  => 'Primary (All Primary: Primary 1 - 6)',
    'PRI1' => 'Primary 1',
    'PRI2' => 'Primary 2',
    'PRI3' => 'Primary 3',
    'PRI4' => 'Primary 4',
    'PRI5' => 'Primary 5',
    'PRI6' => 'Primary 6',
    'NUR1' => 'Nursery 1',
    'NUR2' => 'Nursery 2',
    'PNUR' => 'Pre-Nursery',
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <title>Manage Subjects</title>
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
                            <h2 class="text-dark pb-2 fw-bold">Manage Subjects & Categories</h2>
                            <p class="text-muted">Register subjects for all classes or specific categories/streams (Sciences, Arts, Commercial).</p>
                        </div>
                        <div class="ml-md-auto py-2 py-md-0 d-flex gap-2">
                            <a href="{{ url('/admin/subjects.php') }}" class="btn btn-info btn-round">
                                <i class="fas fa-list me-1"></i> Overview
                            </a>
                            <a href="{{ url('/admin/subjects/assign.php') }}" class="btn btn-secondary btn-round">
                                <i class="fas fa-link me-1"></i> Assign to Classes
                            </a>
                            <a href="{{ url('/admin/subjects/student_subjects.php') }}" class="btn btn-primary btn-round">
                                <i class="fas fa-user-tag me-1"></i> Student Subjects & Drops
                            </a>
                        </div>
                    </div>

                    <?php if ($message !== ''): ?>
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            <i class="fas fa-check-circle me-2"></i><?php echo htmlspecialchars($message); ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    <?php endif; ?>
                    <?php if ($error !== ''): ?>
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <i class="fas fa-exclamation-triangle me-2"></i><?php echo htmlspecialchars($error); ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    <?php endif; ?>

                    <div class="row">
                        <!-- Add / Edit Subject Form -->
                        <div class="col-md-5">
                            <div class="card shadow-sm border-top border-primary border-3">
                                <div class="card-header bg-light">
                                    <div class="card-title fw-bold text-dark">
                                        <i class="fas fa-book-open me-2 text-primary"></i>
                                        <?php echo $editingSubject ? 'Edit Subject' : 'Register New Subject'; ?>
                                    </div>
                                    <div class="card-category text-muted">
                                        Configure subject level and department stream (Science, Arts, Commercial).
                                    </div>
                                </div>
                                <div class="card-body">
                                    <form method="POST">
                                        <input type="hidden" name="action" value="save_subject">
                                        <input type="hidden" name="subject_id" value="<?php echo (int) ($editingSubject['id'] ?? 0); ?>">
                                        
                                        <div class="form-group mb-3">
                                            <label for="subject_name" class="fw-bold">Subject Name <span class="text-danger">*</span></label>
                                            <input type="text" class="form-control" id="subject_name" name="subject_name" value="<?php echo htmlspecialchars((string) ($editingSubject['subject_name'] ?? '')); ?>" placeholder="e.g. Physics, Chemistry, Literature" required>
                                        </div>

                                        <div class="form-group mb-3">
                                            <label for="subject_code" class="fw-bold">Subject Code <span class="text-danger">*</span></label>
                                            <input type="text" class="form-control text-uppercase" id="subject_code" name="subject_code" value="<?php echo htmlspecialchars((string) ($editingSubject['subject_code'] ?? '')); ?>" placeholder="e.g. PHY, CHM, LIT, COMM" required>
                                        </div>

                                        <div class="form-group mb-3">
                                            <label for="class_level" class="fw-bold">Target Class Level <span class="text-danger">*</span></label>
                                            <select class="form-control" id="class_level" name="class_level" required>
                                                <?php foreach ($classLevelOptions as $code => $lbl): ?>
                                                    <option value="<?php echo htmlspecialchars($code); ?>" <?php echo ($editingSubjectLevel === $code) ? 'selected' : ''; ?>>
                                                        <?php echo htmlspecialchars($lbl); ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                            <small class="form-text text-muted">Select 'ALL' for school-wide, 'SSS' for all senior classes, or a specific class.</small>
                                        </div>

                                        <div class="form-group mb-3">
                                            <label for="department" class="fw-bold">Category / Department Stream <span class="text-danger">*</span></label>
                                            <select class="form-control" id="department" name="department" required>
                                                <option value="general" <?php echo in_array($editingSubjectDept, ['general', 'all', ''], true) ? 'selected' : ''; ?>>
                                                    🌐 All Categories (General / Core to all students)
                                                </option>
                                                <option value="science" <?php echo ($editingSubjectDept === 'science') ? 'selected' : ''; ?>>
                                                    🔬 Sciences (e.g. Physics, Chemistry, Biology)
                                                </option>
                                                <option value="arts" <?php echo ($editingSubjectDept === 'arts') ? 'selected' : ''; ?>>
                                                    🎨 Arts & Humanities (e.g. Literature, CRS/IRS, History)
                                                </option>
                                                <option value="commercial" <?php echo ($editingSubjectDept === 'commercial') ? 'selected' : ''; ?>>
                                                    📊 Commercial & Business (e.g. Accounting, Commerce)
                                                </option>
                                            </select>
                                            <small class="form-text text-muted">Senior students in that category will automatically receive their stream subjects.</small>
                                        </div>

                                        <div class="form-group mb-3">
                                            <label for="subject_type" class="fw-bold">Subject Type</label>
                                            <select class="form-control" id="subject_type" name="subject_type">
                                                <option value="core" <?php echo (($editingSubject['subject_type'] ?? 'core') === 'core') ? 'selected' : ''; ?>>Core Subject (Compulsory)</option>
                                                <option value="elective" <?php echo (($editingSubject['subject_type'] ?? 'core') === 'elective') ? 'selected' : ''; ?>>Elective Subject</option>
                                                <option value="optional" <?php echo (($editingSubject['subject_type'] ?? 'core') === 'optional') ? 'selected' : ''; ?>>Optional Subject</option>
                                            </select>
                                        </div>

                                        <div class="d-flex gap-2 mt-4">
                                            <button type="submit" class="btn btn-primary flex-grow-1 fw-bold">
                                                <i class="fas fa-save me-1"></i> <?php echo $editingSubject ? 'Update Subject' : 'Register Subject'; ?>
                                            </button>
                                            <?php if ($editingSubject): ?>
                                                <a href="{{ url('/admin/subjects/manage.php') }}" class="btn btn-secondary">Cancel</a>
                                            <?php endif; ?>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>

                        <!-- Subject Catalogue Table -->
                        <div class="col-md-7">
                            <div class="card shadow-sm">
                                <div class="card-header d-flex justify-content-between align-items-center">
                                    <div>
                                        <div class="card-title fw-bold">Subject Catalogue</div>
                                        <div class="card-category text-muted">All active subjects, stream mappings and usage count.</div>
                                    </div>
                                    <span class="badge bg-primary fs-6"><?php echo count($subjects); ?> Subjects</span>
                                </div>
                                <div class="card-body">
                                    <div class="table-responsive">
                                        <table id="basic-datatables" class="display table-striped table-hover table table-bordered">
                                            <thead class="table-light">
                                                 <tr>
                                                    <th style="width: 40px;">S/N</th>
                                                    <th>Code</th>
                                                    <th>Subject Name</th>
                                                    <th>Class Level</th>
                                                    <th>Stream / Category</th>
                                                    <th>Type</th>
                                                    <th>Usage</th>
                                                    <th>Actions</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php if (empty($subjects)): ?>
                                                    <tr>
                                                        <td colspan="8" class="text-center py-4 text-muted">No subjects found.</td>
                                                    </tr>
                                                <?php else: ?>
                                                    <?php $sn = 1; foreach ($subjects as $subject): ?>
                                                        <?php
                                                            $subjType = (string) ($subject['subject_type'] ?? 'core');
                                                            $subjDept = strtolower((string) ($subject['department'] ?? 'general'));
                                                            $levelDisplay = (string) ($subject['class_level'] ?? 'ALL');
                                                        ?>
                                                        <tr>
                                                            <td><?php echo $sn++; ?></td>
                                                            <td><strong class="text-primary"><?php echo htmlspecialchars((string) $subject['subject_code']); ?></strong></td>
                                                            <td>
                                                                <strong class="text-dark"><?php echo htmlspecialchars((string) $subject['subject_name']); ?></strong>
                                                            </td>
                                                            <td>
                                                                <span class="badge bg-secondary">
                                                                    <?php echo htmlspecialchars($levelDisplay); ?>
                                                                </span>
                                                            </td>
                                                            <td>
                                                                <?php if ($subjDept === 'science'): ?>
                                                                    <span class="badge bg-info text-dark">
                                                                        <i class="fas fa-flask me-1"></i>Sciences
                                                                    </span>
                                                                <?php elseif ($subjDept === 'arts'): ?>
                                                                    <span class="badge text-white" style="background-color: #6f42c1;">
                                                                        <i class="fas fa-palette me-1"></i>Arts
                                                                    </span>
                                                                <?php elseif ($subjDept === 'commercial'): ?>
                                                                    <span class="badge bg-success text-white">
                                                                        <i class="fas fa-calculator me-1"></i>Commercial
                                                                    </span>
                                                                <?php else: ?>
                                                                    <span class="badge bg-light text-dark border">
                                                                        <i class="fas fa-globe me-1"></i>All / General
                                                                    </span>
                                                                <?php endif; ?>
                                                            </td>
                                                            <td>
                                                                <span class="badge bg-<?php echo $subjType === 'core' ? 'primary' : ($subjType === 'optional' ? 'warning text-dark' : 'secondary'); ?>">
                                                                    <?php echo ucfirst($subjType); ?>
                                                                </span>
                                                            </td>
                                                            <td>
                                                                <small class="text-muted">
                                                                    <?php echo (int) ($subject['class_assignment_count'] ?? 0); ?> classes,
                                                                    <?php echo (int) ($subject['teacher_assignment_count'] ?? 0); ?> teachers
                                                                </small>
                                                            </td>
                                                            <td class="text-nowrap">
                                                                <a href="{{ url('/admin/subjects/manage.php?edit=' . $subject['id']) }}" class="btn btn-warning btn-sm" title="Edit">
                                                                    <i class="fas fa-edit"></i>
                                                                </a>
                                                                <form method="POST" class="d-inline">
                                                                    <input type="hidden" name="action" value="delete_subject">
                                                                    <input type="hidden" name="subject_id" value="<?php echo (int) $subject['id']; ?>">
                                                                    <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Delete this subject: <?php echo htmlspecialchars(addslashes($subject['subject_name'])); ?>?')" title="Delete">
                                                                        <i class="fas fa-trash"></i>
                                                                    </button>
                                                                </form>
                                                            </td>
                                                        </tr>
                                                    <?php endforeach; ?>
                                                <?php endif; ?>
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
        </div>
    </div>
</body>
</html>
