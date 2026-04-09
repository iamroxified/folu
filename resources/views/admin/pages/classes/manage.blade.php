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

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = (string) ($_POST['action'] ?? '');

    try {
        if ($action === 'save_class') {
            $classId = (int) ($_POST['class_id'] ?? 0);
            $className = validate($_POST['class_name'] ?? '');
            $classArm = strtoupper(validate($_POST['class_arm'] ?? ''));
            $classLevel = validate($_POST['class_level'] ?? '');
            $maxCapacity = (int) ($_POST['max_capacity'] ?? 0);
            $formTeacherLink = (int) ($_POST['form_teacher_link'] ?? 0);

            if ($className === '' || $classArm === '' || $classLevel === '') {
                throw new Exception('Please fill in the class name, arm, and class level.');
            }

            if ($maxCapacity < 1) {
                throw new Exception('Maximum capacity must be greater than zero.');
            }

            $duplicate = (int) QueryDB(
                'SELECT COUNT(*) FROM classes WHERE class_name = ? AND class_arm = ? AND id != ?',
                [$className, $classArm, $classId]
            )->fetchColumn();

            if ($duplicate > 0) {
                throw new Exception('That class entity already exists. Use edit instead of creating a duplicate.');
            }

            if ($classId > 0) {
                QueryDB(
                    'UPDATE classes
                     SET class_name = ?, class_arm = ?, class_level = ?, form_teacher_link = ?, max_capacity = ?
                     WHERE id = ?',
                    [$className, $classArm, $classLevel, $formTeacherLink > 0 ? $formTeacherLink : null, $maxCapacity, $classId]
                );
                $message = 'Class updated successfully.';
                $editId = 0;
            } else {
                QueryDB(
                    'INSERT INTO classes (class_name, class_arm, class_level, form_teacher_link, max_capacity, created_at)
                     VALUES (?, ?, ?, ?, ?, NOW())',
                    [$className, $classArm, $classLevel, $formTeacherLink > 0 ? $formTeacherLink : null, $maxCapacity]
                );
                $message = 'Class created successfully.';
            }
        } elseif ($action === 'delete_class') {
            $classId = (int) ($_POST['class_id'] ?? 0);

            if ($classId < 1) {
                throw new Exception('Please select a valid class to delete.');
            }

            $studentCount = (int) QueryDB('SELECT COUNT(*) FROM students WHERE class_link = ?', [$classId])->fetchColumn();

            if ($studentCount > 0) {
                throw new Exception('This class still has students assigned to it. Move the students first before deleting the class.');
            }

            QueryDB('DELETE FROM classes WHERE id = ?', [$classId]);
            $message = 'Class deleted successfully.';
            $editId = 0;
        }
    } catch (Throwable $e) {
        $error = $e->getMessage();
    }
}

$currentSession = get_current_academic_session();
$currentTerm = get_current_academic_term($currentSession['id'] ?? null);
$teachers = QueryDB(
    "SELECT id, teacher_id, first_name, last_name
     FROM teachers
     WHERE status = 'active' OR status IS NULL
     ORDER BY first_name, last_name"
)->fetchAll();
$editingClass = $editId > 0
    ? QueryDB('SELECT * FROM classes WHERE id = ? LIMIT 1', [$editId])->fetch(PDO::FETCH_ASSOC)
    : null;
$classes = QueryDB(
    "SELECT c.*,
            CONCAT(t.first_name, ' ', t.last_name) AS form_teacher_name,
            (SELECT COUNT(*) FROM students s WHERE s.class_link = c.id) AS total_student_count,
            (SELECT COUNT(*) FROM students s WHERE s.class_link = c.id AND (? = 0 OR s.academic_session_link = ?)) AS session_student_count
     FROM classes c
     LEFT JOIN teachers t ON c.form_teacher_link = t.id
     ORDER BY c.class_level, c.class_name, c.class_arm",
    [(int) ($currentSession['id'] ?? 0), (int) ($currentSession['id'] ?? 0)]
)->fetchAll();
$classLevels = [
    'PNUR1' => 'Pre-Nursery 1',
    'PNUR2' => 'Pre-Nursery 2',
    'NUR1' => 'Nursery 1',
    'NUR2' => 'Nursery 2',
    'PRI1' => 'Primary 1',
    'PRI2' => 'Primary 2',
    'PRI3' => 'Primary 3',
    'PRI4' => 'Primary 4',
    'PRI5' => 'Primary 5',
    'PRI6' => 'Primary 6',
    'JSS1' => 'JSS1',
    'JSS2' => 'JSS2',
    'JSS3' => 'JSS3',
    'SSS1' => 'SSS1',
    'SSS2' => 'SSS2',
    'SSS3' => 'SSS3',
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <title>Manage Classes</title>
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
                        <h2 class="text-dark pb-2 fw-bold">Manage Classes</h2>
                        <div class="ml-md-auto py-2 py-md-0">
                            <a href="{{ url('/admin/classes/assign_students.php') }}" class="btn btn-info">Assign Students</a>
                            <a href="{{ url('/admin/classes/timetable.php') }}" class="btn btn-secondary">Manage Timetable</a>
                        </div>
                    </div>

                    <?php if ($message !== ''): ?>
                        <div class="alert alert-success"><?php echo htmlspecialchars($message); ?></div>
                    <?php endif; ?>
                    <?php if ($error !== ''): ?>
                        <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
                    <?php endif; ?>

                    <div class="alert alert-info">
                        Classes now exist as reusable school entities. Session and term belong to the student placement, not to the class itself.
                        <?php if ($currentSession): ?>
                            Current placement context: <strong><?php echo htmlspecialchars(session_term_context_label($currentSession, $currentTerm)); ?></strong>.
                        <?php endif; ?>
                    </div>

                    <div class="row">
                        <div class="col-md-5">
                            <div class="card">
                                <div class="card-header">
                                    <div class="card-title"><?php echo $editingClass ? 'Edit Class Entity' : 'Add New Class Entity'; ?></div>
                                </div>
                                <div class="card-body">
                                    <form method="POST">
                                        <input type="hidden" name="action" value="save_class">
                                        <input type="hidden" name="class_id" value="<?php echo (int) ($editingClass['id'] ?? 0); ?>">
                                        <div class="form-group">
                                            <label for="class_name">Class Name</label>
                                            <input type="text" class="form-control" id="class_name" name="class_name" value="<?php echo htmlspecialchars((string) ($editingClass['class_name'] ?? '')); ?>" placeholder="Primary 1, JSS1, SSS2" required>
                                        </div>
                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label for="class_arm">Class Arm</label>
                                                    <input type="text" class="form-control" id="class_arm" name="class_arm" value="<?php echo htmlspecialchars((string) ($editingClass['class_arm'] ?? 'A')); ?>" placeholder="A, B, C" required>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label for="max_capacity">Max Capacity</label>
                                                    <input type="number" class="form-control" id="max_capacity" name="max_capacity" min="1" value="<?php echo htmlspecialchars((string) ($editingClass['max_capacity'] ?? 40)); ?>" required>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="form-group">
                                            <label for="class_level">Class Level Code</label>
                                            <select class="form-control" id="class_level" name="class_level" required>
                                                <option value="">Select Level</option>
                                                <?php foreach ($classLevels as $value => $label): ?>
                                                    <option value="<?php echo htmlspecialchars($value); ?>" <?php echo (($editingClass['class_level'] ?? '') === $value) ? 'selected' : ''; ?>>
                                                        <?php echo htmlspecialchars($label . ' (' . $value . ')'); ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                        <div class="form-group">
                                            <label for="form_teacher_link">Default Form Teacher</label>
                                            <select class="form-control" id="form_teacher_link" name="form_teacher_link">
                                                <option value="0">No Default Form Teacher Yet</option>
                                                <?php foreach ($teachers as $teacher): ?>
                                                    <option value="<?php echo (int) $teacher['id']; ?>" <?php echo ((int) ($editingClass['form_teacher_link'] ?? 0) === (int) $teacher['id']) ? 'selected' : ''; ?>>
                                                        <?php echo htmlspecialchars((string) ($teacher['teacher_id'] . ' - ' . $teacher['first_name'] . ' ' . $teacher['last_name'])); ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                        <button type="submit" class="btn btn-primary"><?php echo $editingClass ? 'Update Class' : 'Create Class'; ?></button>
                                        <?php if ($editingClass): ?>
                                            <a href="{{ url('/admin/classes/manage.php') }}" class="btn btn-secondary">Cancel</a>
                                        <?php endif; ?>
                                    </form>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-7">
                            <div class="card">
                                <div class="card-header">
                                    <div class="card-title">School Classes</div>
                                </div>
                                <div class="card-body">
                                    <?php if (empty($classes)): ?>
                                        <div class="alert alert-info mb-0">No classes have been created yet.</div>
                                    <?php else: ?>
                                        <div class="table-responsive">
                                            <table class="table table-bordered table-striped">
                                                <thead>
                                                    <tr>
                                                        <th>Class</th>
                                                        <th>Level</th>
                                                        <th>Form Teacher</th>
                                                        <th>Students</th>
                                                        <th>Capacity</th>
                                                        <th>Actions</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <?php foreach ($classes as $class): ?>
                                                        <tr>
                                                            <td><?php echo htmlspecialchars(trim(($class['class_name'] ?? '') . ' ' . ($class['class_arm'] ?? ''))); ?></td>
                                                            <td><?php echo htmlspecialchars((string) ($classLevels[$class['class_level'] ?? ''] ?? ($class['class_level'] ?? ''))); ?></td>
                                                            <td><?php echo htmlspecialchars((string) ($class['form_teacher_name'] ?? 'Not Assigned')); ?></td>
                                                            <td>
                                                                <div><?php echo (int) ($class['total_student_count'] ?? 0); ?> total</div>
                                                                <small class="text-muted"><?php echo (int) ($class['session_student_count'] ?? 0); ?> in active session</small>
                                                            </td>
                                                            <td><?php echo (int) ($class['max_capacity'] ?? 0); ?></td>
                                                            <td>
                                                                <a href="{{ url('/admin/classes/manage.php?edit=' . $class['id']) }}" class="btn btn-warning btn-sm">Edit</a>
                                                                <form method="POST" class="d-inline">
                                                                    <input type="hidden" name="action" value="delete_class">
                                                                    <input type="hidden" name="class_id" value="<?php echo (int) $class['id']; ?>">
                                                                    <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Delete this class entity?')">Delete</button>
                                                                </form>
                                                            </td>
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
                </div>
            </div>
            @include('admin.partials.footer')
</body>
</html>
