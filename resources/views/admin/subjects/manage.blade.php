<?php
require_once base_path('db/config.php');
require_once base_path('db/functions.php');

if (!isset($_SESSION['adid'])) {
    header('Location: /admin/login.php');
    exit;
}

$message = '';
$error = '';
$editId = (int) ($_GET['edit'] ?? 0);
$teacherAssignmentCountSelect = schema_has_table('teacher_subjects')
    ? '(SELECT COUNT(*) FROM teacher_subjects ts WHERE ts.subject_link = s.id) AS teacher_assignment_count'
    : '0 AS teacher_assignment_count';
$classAssignmentCountSelect = schema_has_table('class_subject_assignments')
    ? '(SELECT COUNT(*) FROM class_subject_assignments csa WHERE csa.subject_id = s.id) AS class_assignment_count'
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
            $classLevel = validate($_POST['class_level'] ?? '');
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
                throw new Exception('That subject already exists for the selected class level.');
            }

            if ($subjectId > 0) {
                QueryDB(
                    'UPDATE subjects SET subject_name = ?, subject_code = ?, grade_levels = ?, subject_type = ?, updated_at = NOW() WHERE id = ?',
                    [$subjectName, $subjectCode, $gradeLevels, $subjectType, $subjectId]
                );
                $message = 'Subject updated successfully.';
                $editId = 0;
            } else {
                QueryDB(
                    'INSERT INTO subjects (subject_name, subject_code, grade_levels, subject_type, status, created_at, updated_at) VALUES (?, ?, ?, ?, ?, NOW(), NOW())',
                    [$subjectName, $subjectCode, $gradeLevels, $subjectType, 'active']
                );
                $message = 'Subject created successfully.';
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
                $hasAssignments += (int) QueryDB('SELECT COUNT(*) FROM class_subject_assignments WHERE subject_id = ?', [$subjectId])->fetchColumn();
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
            $message = 'Subject deleted successfully.';
            $editId = 0;
        }
    } catch (Throwable $e) {
        $error = $e->getMessage();
    }
}

$editingSubject = $editId > 0
    ? QueryDB('SELECT * FROM subjects WHERE id = ? LIMIT 1', [$editId])->fetch(PDO::FETCH_ASSOC)
    : null;
$editingSubjectLevels = [];
if ($editingSubject && !empty($editingSubject['grade_levels'])) {
    $decodedLevels = json_decode((string) $editingSubject['grade_levels'], true);
    $editingSubjectLevels = is_array($decodedLevels) ? $decodedLevels : [];
}
$editingSubjectLevel = (string) ($editingSubjectLevels[0] ?? '');
$subjects = QueryDB(
    "SELECT s.*,
            {$teacherAssignmentCountSelect},
            {$classAssignmentCountSelect},
            {$resultCountSelect}
     FROM subjects s
     ORDER BY s.subject_name, s.subject_code"
)->fetchAll();
$classLevels = ['ALL', 'Primary 1', 'Primary 2', 'Primary 3', 'Primary 4', 'Primary 5', 'Primary 6', 'JSS1', 'JSS2', 'JSS3', 'SSS1', 'SSS2', 'SSS3'];
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
                    <div class="d-flex align-items-left flex-column flex-md-row">
                        <h2 class="text-dark pb-2 fw-bold">Manage Subjects</h2>
                        <div class="ml-md-auto py-2 py-md-0">
                            <a href="{{ url('/admin/subjects.php') }}" class="btn btn-info">Overview</a>
                            <a href="{{ url('/admin/subjects/assign.php') }}" class="btn btn-secondary">Assign to Classes</a>
                        </div>
                    </div>

                    <?php if ($message !== ''): ?>
                        <div class="alert alert-success"><?php echo htmlspecialchars($message); ?></div>
                    <?php endif; ?>
                    <?php if ($error !== ''): ?>
                        <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
                    <?php endif; ?>

                    <div class="row">
                        <div class="col-md-5">
                            <div class="card">
                                <div class="card-header">
                                    <div class="card-title"><?php echo $editingSubject ? 'Edit Subject' : 'Add Subject'; ?></div>
                                </div>
                                <div class="card-body">
                                    <form method="POST">
                                        <input type="hidden" name="action" value="save_subject">
                                        <input type="hidden" name="subject_id" value="<?php echo (int) ($editingSubject['id'] ?? 0); ?>">
                                        <div class="form-group">
                                            <label for="subject_name">Subject Name</label>
                                            <input type="text" class="form-control" id="subject_name" name="subject_name" value="<?php echo htmlspecialchars((string) ($editingSubject['subject_name'] ?? '')); ?>" required>
                                        </div>
                                        <div class="form-group">
                                            <label for="subject_code">Subject Code</label>
                                            <input type="text" class="form-control" id="subject_code" name="subject_code" value="<?php echo htmlspecialchars((string) ($editingSubject['subject_code'] ?? '')); ?>" required>
                                        </div>
                                        <div class="form-group">
                                            <label for="class_level">Class Level</label>
                                            <select class="form-control" id="class_level" name="class_level" required>
                                                <option value="">Select Class Level</option>
                                                <?php foreach ($classLevels as $level): ?>
                                                    <option value="<?php echo htmlspecialchars($level); ?>" <?php echo ($editingSubjectLevel === $level) ? 'selected' : ''; ?>>
                                                        <?php echo htmlspecialchars($level); ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                        <div class="form-group">
                                            <label for="subject_type">Subject Type</label>
                                            <select class="form-control" id="subject_type" name="subject_type">
                                                <option value="core" <?php echo (($editingSubject['subject_type'] ?? 'core') === 'core') ? 'selected' : ''; ?>>Core Subject</option>
                                                <option value="elective" <?php echo (($editingSubject['subject_type'] ?? 'core') === 'elective') ? 'selected' : ''; ?>>Elective Subject</option>
                                                <option value="optional" <?php echo (($editingSubject['subject_type'] ?? 'core') === 'optional') ? 'selected' : ''; ?>>Optional Subject</option>
                                            </select>
                                        </div>
                                        <button type="submit" class="btn btn-primary"><?php echo $editingSubject ? 'Update Subject' : 'Create Subject'; ?></button>
                                        <?php if ($editingSubject): ?>
                                            <a href="{{ url('/admin/subjects/manage.php') }}" class="btn btn-secondary">Cancel</a>
                                        <?php endif; ?>
                                    </form>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-7">
                            <div class="card">
                                <div class="card-header">
                                    <div class="card-title">Subject Catalogue</div>
                                </div>
                                <div class="card-body">
                                    <div class="table-responsive">
                                        <table class="table table-bordered table-striped">
                                            <thead>
                                                <tr>
                                                    <th>Code</th>
                                                    <th>Subject</th>
                                                    <th>Class Level</th>
                                                    <th>Type</th>
                                                    <th>Usage</th>
                                                    <th>Actions</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php if (empty($subjects)): ?>
                                                    <tr>
                                                        <td colspan="6" class="text-center">No subjects found.</td>
                                                    </tr>
                                                <?php else: ?>
                                                    <?php foreach ($subjects as $subject): ?>
                                                        <?php
                                                            $subjectLevels = json_decode((string) ($subject['grade_levels'] ?? '[]'), true);
                                                            $subjectLevels = is_array($subjectLevels) ? $subjectLevels : [];
                                                            $subjectType = (string) ($subject['subject_type'] ?? 'core');
                                                        ?>
                                                        <tr>
                                                            <td><?php echo htmlspecialchars((string) $subject['subject_code']); ?></td>
                                                            <td><?php echo htmlspecialchars((string) $subject['subject_name']); ?></td>
                                                            <td><?php echo htmlspecialchars($subjectLevels !== [] ? implode(', ', $subjectLevels) : 'N/A'); ?></td>
                                                            <td>
                                                                <span class="badge badge-<?php echo $subjectType === 'core' ? 'primary' : ($subjectType === 'optional' ? 'info' : 'secondary'); ?>">
                                                                    <?php echo htmlspecialchars(ucfirst($subjectType)); ?>
                                                                </span>
                                                            </td>
                                                            <td>
                                                                <?php echo (int) ($subject['class_assignment_count'] ?? 0); ?> classes,
                                                                <?php echo (int) ($subject['teacher_assignment_count'] ?? 0); ?> teachers,
                                                                <?php echo (int) ($subject['result_count'] ?? 0); ?> grade rows
                                                            </td>
                                                            <td>
                                                                <a href="{{ url('/admin/subjects/manage.php?edit=' . $subject['id']) }}" class="btn btn-warning btn-sm">Edit</a>
                                                                <form method="POST" class="d-inline">
                                                                    <input type="hidden" name="action" value="delete_subject">
                                                                    <input type="hidden" name="subject_id" value="<?php echo (int) $subject['id']; ?>">
                                                                    <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Delete this subject?')">Delete</button>
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
</body>
</html>
