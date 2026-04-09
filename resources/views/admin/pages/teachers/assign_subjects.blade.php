<?php
require_once base_path('db/config.php');
require_once base_path('db/functions.php');

if (!isset($_SESSION['adid'])) {
    header('Location: /admin/login.php');
    exit;
}

$teacherId = (int) ($_GET['id'] ?? $_POST['teacher_id'] ?? 0);

if ($teacherId < 1) {
    header('Location: /admin/teachers/list.php');
    exit;
}

$teacher = QueryDB(
    'SELECT t.*, u.username, u.status AS user_status
     FROM teachers t
     JOIN users u ON t.user_link = u.id
     WHERE t.id = ?',
    [$teacherId]
)->fetch(PDO::FETCH_ASSOC);

if (!$teacher) {
    header('Location: /admin/teachers/list.php');
    exit;
}

$message = '';
$error = '';
$selectedSessionId = (int) ($_REQUEST['academic_session_link'] ?? (get_current_academic_session_id() ?? 0));

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $selectedSessionId = (int) ($_POST['academic_session_link'] ?? $selectedSessionId);

        if ($selectedSessionId < 1) {
            throw new Exception('Please select an academic session first.');
        }

        $action = (string) ($_POST['action'] ?? '');

        if ($action === 'assign_class') {
            $classId = (int) ($_POST['class_link'] ?? 0);
            $assignmentRole = validate($_POST['assignment_role'] ?? 'class_teacher');

            if ($classId < 1) {
                throw new Exception('Please choose a class to assign.');
            }

            $saved = assign_teacher_class($teacherId, $classId, $selectedSessionId, $assignmentRole);
            $message = $saved
                ? 'Class assignment saved successfully.'
                : 'That class assignment already exists for this teacher.';
        } elseif ($action === 'remove_class') {
            $classId = (int) ($_POST['class_link'] ?? 0);
            $assignmentRole = validate($_POST['assignment_role'] ?? 'class_teacher');

            if ($classId < 1) {
                throw new Exception('Please choose a valid class assignment to remove.');
            }

            $removed = remove_teacher_class_assignment($teacherId, $classId, $selectedSessionId, $assignmentRole);
            $message = $removed ? 'Class assignment removed successfully.' : 'No matching class assignment was found.';
        } elseif ($action === 'assign_subject') {
            $classId = (int) ($_POST['class_link'] ?? 0);
            $subjectId = (int) ($_POST['subject_link'] ?? 0);

            if ($classId < 1 || $subjectId < 1) {
                throw new Exception('Please select both a class and a subject.');
            }

            $class = QueryDB(
                'SELECT * FROM classes WHERE id = ? LIMIT 1',
                [$classId]
            )->fetch(PDO::FETCH_ASSOC);

            $subject = QueryDB(
                'SELECT * FROM subjects WHERE id = ? LIMIT 1',
                [$subjectId]
            )->fetch(PDO::FETCH_ASSOC);

            if (!$class || !$subject) {
                throw new Exception('The selected class or subject could not be found.');
            }

            $subjectLevel = (string) ($subject['class_level'] ?? 'ALL');
            if ($subjectLevel !== 'ALL' && $subjectLevel !== (string) ($class['class_level'] ?? '')) {
                throw new Exception('This subject cannot be assigned to the selected class level.');
            }

            $saved = assign_teacher_subject($teacherId, $subjectId, $classId, $selectedSessionId);
            $message = $saved
                ? 'Subject assignment saved successfully.'
                : 'That subject assignment already exists for this teacher.';
        } elseif ($action === 'remove_subject') {
            $assignmentId = (int) ($_POST['assignment_id'] ?? 0);

            if ($assignmentId < 1) {
                throw new Exception('Please choose a valid subject assignment to remove.');
            }

            $removed = remove_teacher_subject_assignment($teacherId, $assignmentId);
            $message = $removed ? 'Subject assignment removed successfully.' : 'No matching subject assignment was found.';
        } elseif ($action === 'update_specialization') {
            $specialization = validate($_POST['specialization'] ?? '');

            QueryDB(
                'UPDATE teachers SET specialization = ?, updated_at = NOW() WHERE id = ?',
                [$specialization !== '' ? $specialization : null, $teacherId]
            );

            $teacher['specialization'] = $specialization;
            $message = 'Specialization updated successfully.';
        }
    } catch (Throwable $e) {
        $error = $e->getMessage();
    }
}

$sessions = QueryDB('SELECT * FROM academic_sessions ORDER BY id DESC')->fetchAll();
$classes = QueryDB(
    "SELECT c.*, CONCAT(t.first_name, ' ', t.last_name) AS form_teacher_name
     FROM classes c
     LEFT JOIN teachers t ON c.form_teacher_link = t.id
     ORDER BY c.class_level, c.class_name, c.class_arm",
)->fetchAll();
$subjects = QueryDB('SELECT * FROM subjects ORDER BY class_level, subject_name')->fetchAll();
$classAssignments = get_teacher_class_assignments($teacherId, $selectedSessionId);
$subjectAssignments = get_teacher_subject_assignments($teacherId, $selectedSessionId);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <title>Teacher Assignments</title>
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
                        <h2 class="text-dark pb-2 fw-bold">Teacher Assignments</h2>
                        <div class="ml-md-auto py-2 py-md-0">
                            <a href="{{ url('/admin/teachers/view.php?id=' . $teacherId) }}" class="btn btn-info">View Profile</a>
                            <a href="{{ url('/admin/teachers/list.php') }}" class="btn btn-secondary">Back to List</a>
                        </div>
                    </div>

                    <?php if ($message !== ''): ?>
                        <div class="alert alert-success"><?php echo htmlspecialchars($message); ?></div>
                    <?php endif; ?>
                    <?php if ($error !== ''): ?>
                        <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
                    <?php endif; ?>

                    <div class="row">
                        <div class="col-md-12">
                            <div class="card">
                                <div class="card-body">
                                    <div class="d-flex align-items-center">
                                        <div class="avatar avatar-lg">
                                            <span class="avatar-title bg-primary text-white rounded-circle">
                                                <?php echo strtoupper(substr((string) $teacher['first_name'], 0, 1) . substr((string) $teacher['last_name'], 0, 1)); ?>
                                            </span>
                                        </div>
                                        <div class="ml-3">
                                            <h4 class="mb-1"><?php echo htmlspecialchars(trim(($teacher['first_name'] ?? '') . ' ' . ($teacher['last_name'] ?? ''))); ?></h4>
                                            <p class="text-muted mb-0">Teacher ID: <?php echo htmlspecialchars((string) $teacher['teacher_id']); ?> | Username: <?php echo htmlspecialchars((string) $teacher['username']); ?></p>
                                        </div>
                                        <div class="ml-auto">
                                            <span class="badge badge-success"><?php echo count($classAssignments); ?> Class Assignments</span>
                                            <span class="badge badge-info"><?php echo count($subjectAssignments); ?> Subject Assignments</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card">
                        <div class="card-header">
                            <div class="card-title">Session Context</div>
                        </div>
                        <div class="card-body">
                            <form method="GET" class="row">
                                <input type="hidden" name="id" value="<?php echo $teacherId; ?>">
                                <div class="col-md-10">
                                    <label for="academic_session_link">Academic Session</label>
                                    <select class="form-control" id="academic_session_link" name="academic_session_link">
                                        <?php foreach ($sessions as $session): ?>
                                            <option value="<?php echo (int) $session['id']; ?>" <?php echo $selectedSessionId === (int) $session['id'] ? 'selected' : ''; ?>>
                                                <?php echo htmlspecialchars((string) $session['session_name']); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="col-md-2 d-flex align-items-end">
                                    <button type="submit" class="btn btn-primary w-100">Switch</button>
                                </div>
                            </form>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-header">
                                    <div class="card-title">Assign Class Roles</div>
                                    <div class="card-category">Teachers can now cover multiple classes and more than one role.</div>
                                </div>
                                <div class="card-body">
                                    <form method="POST">
                                        <input type="hidden" name="teacher_id" value="<?php echo $teacherId; ?>">
                                        <input type="hidden" name="academic_session_link" value="<?php echo $selectedSessionId; ?>">
                                        <input type="hidden" name="action" value="assign_class">
                                        <div class="form-group">
                                            <label for="class_link">Class</label>
                                            <select class="form-control" id="class_link" name="class_link" required>
                                                <option value="">Select Class</option>
                                                <?php foreach ($classes as $class): ?>
                                                    <option value="<?php echo (int) $class['id']; ?>">
                                                        <?php echo htmlspecialchars(trim(($class['class_name'] ?? '') . ' ' . ($class['class_arm'] ?? ''))); ?>
                                                        <?php if (!empty($class['form_teacher_name'])): ?>
                                                            <?php echo htmlspecialchars(' | Current Form Teacher: ' . $class['form_teacher_name']); ?>
                                                        <?php endif; ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                        <div class="form-group">
                                            <label for="assignment_role">Role</label>
                                            <select class="form-control" id="assignment_role" name="assignment_role">
                                                <option value="class_teacher">Class Teacher</option>
                                                <option value="support_teacher">Support Teacher</option>
                                                <option value="assistant_teacher">Assistant Teacher</option>
                                            </select>
                                        </div>
                                        <button type="submit" class="btn btn-primary">Save Class Assignment</button>
                                    </form>

                                    <hr>
                                    <h5>Current Class Assignments</h5>
                                    <?php if (empty($classAssignments)): ?>
                                        <div class="alert alert-info mb-0">No class assignments recorded for this session.</div>
                                    <?php else: ?>
                                        <div class="table-responsive">
                                            <table class="table table-bordered table-sm">
                                                <thead>
                                                    <tr>
                                                        <th>Class</th>
                                                        <th>Role</th>
                                                        <th>Action</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <?php foreach ($classAssignments as $assignment): ?>
                                                        <tr>
                                                            <td><?php echo htmlspecialchars(trim(($assignment['class_name'] ?? '') . ' ' . ($assignment['class_arm'] ?? ''))); ?></td>
                                                            <td><?php echo htmlspecialchars(ucwords(str_replace('_', ' ', (string) ($assignment['assignment_role'] ?? 'class_teacher')))); ?></td>
                                                            <td>
                                                                <form method="POST" class="d-inline">
                                                                    <input type="hidden" name="teacher_id" value="<?php echo $teacherId; ?>">
                                                                    <input type="hidden" name="academic_session_link" value="<?php echo $selectedSessionId; ?>">
                                                                    <input type="hidden" name="class_link" value="<?php echo (int) ($assignment['class_link'] ?? $assignment['id'] ?? 0); ?>">
                                                                    <input type="hidden" name="assignment_role" value="<?php echo htmlspecialchars((string) ($assignment['assignment_role'] ?? 'class_teacher')); ?>">
                                                                    <input type="hidden" name="action" value="remove_class">
                                                                    <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Remove this class assignment?')">Remove</button>
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

                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-header">
                                    <div class="card-title">Assign Subjects</div>
                                    <div class="card-category">Subject assignments stay tied to the correct class and session.</div>
                                </div>
                                <div class="card-body">
                                    <form method="POST">
                                        <input type="hidden" name="teacher_id" value="<?php echo $teacherId; ?>">
                                        <input type="hidden" name="academic_session_link" value="<?php echo $selectedSessionId; ?>">
                                        <input type="hidden" name="action" value="assign_subject">
                                        <div class="form-group">
                                            <label for="subject_class_link">Class</label>
                                            <select class="form-control" id="subject_class_link" name="class_link" required>
                                                <option value="">Select Class</option>
                                                <?php foreach ($classes as $class): ?>
                                                    <option value="<?php echo (int) $class['id']; ?>" data-class-level="<?php echo htmlspecialchars((string) ($class['class_level'] ?? '')); ?>">
                                                        <?php echo htmlspecialchars(trim(($class['class_name'] ?? '') . ' ' . ($class['class_arm'] ?? ''))); ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                        <div class="form-group">
                                            <label for="subject_link">Subject</label>
                                            <select class="form-control" id="subject_link" name="subject_link" required>
                                                <option value="">Select Subject</option>
                                                <?php foreach ($subjects as $subject): ?>
                                                    <option value="<?php echo (int) $subject['id']; ?>" data-class-level="<?php echo htmlspecialchars((string) ($subject['class_level'] ?? 'ALL')); ?>">
                                                        <?php echo htmlspecialchars((string) $subject['subject_name']); ?>
                                                        (<?php echo htmlspecialchars((string) ($subject['class_level'] ?? 'ALL')); ?>)
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                        <button type="submit" class="btn btn-success">Save Subject Assignment</button>
                                    </form>

                                    <hr>
                                    <h5>Current Subject Assignments</h5>
                                    <?php if (empty($subjectAssignments)): ?>
                                        <div class="alert alert-info mb-0">No subject assignments recorded for this session.</div>
                                    <?php else: ?>
                                        <div class="table-responsive">
                                            <table class="table table-bordered table-sm">
                                                <thead>
                                                    <tr>
                                                        <th>Subject</th>
                                                        <th>Class</th>
                                                        <th>Action</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <?php foreach ($subjectAssignments as $assignment): ?>
                                                        <tr>
                                                            <td><?php echo htmlspecialchars((string) ($assignment['subject_name'] ?? '')); ?></td>
                                                            <td><?php echo htmlspecialchars(trim(($assignment['class_name'] ?? '') . ' ' . ($assignment['class_arm'] ?? ''))); ?></td>
                                                            <td>
                                                                <form method="POST" class="d-inline">
                                                                    <input type="hidden" name="teacher_id" value="<?php echo $teacherId; ?>">
                                                                    <input type="hidden" name="academic_session_link" value="<?php echo $selectedSessionId; ?>">
                                                                    <input type="hidden" name="assignment_id" value="<?php echo (int) ($assignment['id'] ?? 0); ?>">
                                                                    <input type="hidden" name="action" value="remove_subject">
                                                                    <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Remove this subject assignment?')">Remove</button>
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

                    <div class="card">
                        <div class="card-header">
                            <div class="card-title">Specialization Notes</div>
                        </div>
                        <div class="card-body">
                            <form method="POST">
                                <input type="hidden" name="teacher_id" value="<?php echo $teacherId; ?>">
                                <input type="hidden" name="academic_session_link" value="<?php echo $selectedSessionId; ?>">
                                <input type="hidden" name="action" value="update_specialization">
                                <div class="form-group">
                                    <label for="specialization">Specialization</label>
                                    <textarea class="form-control" id="specialization" name="specialization" rows="4" placeholder="Primary class teacher, English Language, Basic Science, Mathematics"><?php echo htmlspecialchars((string) ($teacher['specialization'] ?? '')); ?></textarea>
                                </div>
                                <button type="submit" class="btn btn-secondary">Update Specialization</button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
            @include('admin.partials.footer')
            <script>
                const classSelect = document.getElementById('subject_class_link');
                const subjectSelect = document.getElementById('subject_link');

                const filterSubjectOptions = () => {
                    if (!classSelect || !subjectSelect) {
                        return;
                    }

                    const selectedClass = classSelect.options[classSelect.selectedIndex];
                    const classLevel = selectedClass ? selectedClass.dataset.classLevel || '' : '';

                    Array.from(subjectSelect.options).forEach((option, index) => {
                        if (index === 0) {
                            option.hidden = false;
                            return;
                        }

                        const subjectLevel = option.dataset.classLevel || 'ALL';
                        option.hidden = classLevel !== '' && subjectLevel !== 'ALL' && subjectLevel !== classLevel;

                        if (option.hidden && option.selected) {
                            subjectSelect.value = '';
                        }
                    });
                };

                if (classSelect) {
                    classSelect.addEventListener('change', filterSubjectOptions);
                    filterSubjectOptions();
                }
            </script>
</body>
</html>
