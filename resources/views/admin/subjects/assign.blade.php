<?php
require_once base_path('db/config.php');
require_once base_path('db/functions.php');

if (!isset($_SESSION['adid'])) {
    header('Location: /admin/login.php');
    exit;
}

$message = '';
$error = '';
$selectedSessionId = (int) ($_REQUEST['academic_session_link'] ?? (get_current_academic_session_id() ?? 0));
$selectedClassId = (int) ($_REQUEST['class_link'] ?? 0);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = (string) ($_POST['action'] ?? '');

    try {
        $selectedSessionId = (int) ($_POST['academic_session_link'] ?? $selectedSessionId);
        $selectedClassId = (int) ($_POST['class_link'] ?? $selectedClassId);

        if ($selectedSessionId < 1) {
            throw new Exception('Please select an academic session first.');
        }

        if ($action === 'assign_subject') {
            $subjectId = (int) ($_POST['subject_link'] ?? 0);

            if ($selectedClassId < 1 || $subjectId < 1) {
                throw new Exception('Please select both a class and a subject.');
            }

            $saved = assign_subject_to_class($selectedClassId, $subjectId, $selectedSessionId);
            $message = $saved
                ? 'Subject assigned to class successfully.'
                : 'That subject is already assigned to the class, or it does not match the class level.';
        } elseif ($action === 'remove_assignment') {
            $assignmentId = (int) ($_POST['assignment_id'] ?? 0);

            if ($assignmentId < 1) {
                throw new Exception('Please select a valid subject assignment to remove.');
            }

            $removed = remove_class_subject_assignment($assignmentId);
            $message = $removed ? 'Subject assignment removed successfully.' : 'No matching assignment was found.';
        }
    } catch (Throwable $e) {
        $error = $e->getMessage();
    }
}

$sessions = QueryDB('SELECT * FROM academic_sessions ORDER BY id DESC')->fetchAll();
$classes = QueryDB(
    'SELECT * FROM classes ORDER BY class_level, class_name, class_arm'
)->fetchAll();
$selectedClass = $selectedClassId > 0
    ? QueryDB('SELECT * FROM classes WHERE id = ? LIMIT 1', [$selectedClassId])->fetch(PDO::FETCH_ASSOC)
    : null;
$assignments = get_class_subject_assignments($selectedClassId > 0 ? $selectedClassId : null, $selectedSessionId);
$subjects = [];

if ($selectedClass) {
    $subjects = QueryDB(
        "SELECT s.*,
                (SELECT COUNT(*) FROM class_subject_assignments csa WHERE csa.class_link = ? AND csa.subject_link = s.id AND csa.academic_session_link = ?) AS already_assigned
         FROM subjects s
         WHERE s.class_level = 'ALL' OR s.class_level = ?
         ORDER BY s.subject_name, s.subject_code",
        [$selectedClassId, $selectedSessionId, $selectedClass['class_level']]
    )->fetchAll();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <title>Assign Subjects to Classes</title>
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
                        <h2 class="text-dark pb-2 fw-bold">Assign Subjects to Classes</h2>
                        <div class="ml-md-auto py-2 py-md-0">
                            <a href="{{ url('/admin/subjects/manage.php') }}" class="btn btn-secondary">Manage Subjects</a>
                        </div>
                    </div>

                    <?php if ($message !== ''): ?>
                        <div class="alert alert-success"><?php echo htmlspecialchars($message); ?></div>
                    <?php endif; ?>
                    <?php if ($error !== ''): ?>
                        <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
                    <?php endif; ?>

                    <div class="card">
                        <div class="card-header">
                            <div class="card-title">Session and Class Filter</div>
                        </div>
                        <div class="card-body">
                            <form method="GET" class="row">
                                <div class="col-md-4">
                                    <label for="academic_session_link">Academic Session</label>
                                    <select class="form-control" id="academic_session_link" name="academic_session_link">
                                        <?php foreach ($sessions as $session): ?>
                                            <option value="<?php echo (int) $session['id']; ?>" <?php echo $selectedSessionId === (int) $session['id'] ? 'selected' : ''; ?>>
                                                <?php echo htmlspecialchars((string) $session['session_name']); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label for="class_link">Class</label>
                                    <select class="form-control" id="class_link" name="class_link">
                                        <option value="">All Classes</option>
                                        <?php foreach ($classes as $class): ?>
                                            <option value="<?php echo (int) $class['id']; ?>" <?php echo $selectedClassId === (int) $class['id'] ? 'selected' : ''; ?>>
                                                <?php echo htmlspecialchars(trim(($class['class_name'] ?? '') . ' ' . ($class['class_arm'] ?? ''))); ?>
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

                    <div class="row">
                        <div class="col-md-5">
                            <div class="card">
                                <div class="card-header">
                                    <div class="card-title">Assign Subject</div>
                                    <div class="card-category">Subjects are filtered to match the selected class level.</div>
                                </div>
                                <div class="card-body">
                                    <?php if (!$selectedClass): ?>
                                        <div class="alert alert-info mb-0">Select a class first to assign subjects.</div>
                                    <?php else: ?>
                                        <form method="POST">
                                            <input type="hidden" name="action" value="assign_subject">
                                            <input type="hidden" name="academic_session_link" value="<?php echo $selectedSessionId; ?>">
                                            <input type="hidden" name="class_link" value="<?php echo $selectedClassId; ?>">
                                            <div class="form-group">
                                                <label>Selected Class</label>
                                                <input type="text" class="form-control" value="<?php echo htmlspecialchars(trim(($selectedClass['class_name'] ?? '') . ' ' . ($selectedClass['class_arm'] ?? ''))); ?>" readonly>
                                            </div>
                                            <div class="form-group">
                                                <label for="subject_link">Subject</label>
                                                <select class="form-control" id="subject_link" name="subject_link" required>
                                                    <option value="">Select Subject</option>
                                                    <?php foreach ($subjects as $subject): ?>
                                                        <option value="<?php echo (int) $subject['id']; ?>" <?php echo ((int) ($subject['already_assigned'] ?? 0) > 0) ? 'disabled' : ''; ?>>
                                                            <?php echo htmlspecialchars((string) ($subject['subject_name'] . ' (' . $subject['subject_code'] . ') - ' . $subject['class_level'])); ?>
                                                            <?php if ((int) ($subject['already_assigned'] ?? 0) > 0): ?>
                                                                <?php echo htmlspecialchars(' [Already Assigned]'); ?>
                                                            <?php endif; ?>
                                                        </option>
                                                    <?php endforeach; ?>
                                                </select>
                                            </div>
                                            <button type="submit" class="btn btn-primary">Assign Subject</button>
                                        </form>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-7">
                            <div class="card">
                                <div class="card-header">
                                    <div class="card-title">Current Class Subject Assignments</div>
                                </div>
                                <div class="card-body">
                                    <?php if (empty($assignments)): ?>
                                        <div class="alert alert-info mb-0">No subject assignments were found for this filter.</div>
                                    <?php else: ?>
                                        <div class="table-responsive">
                                            <table class="table table-bordered table-striped">
                                                <thead>
                                                    <tr>
                                                        <th>Class</th>
                                                        <th>Subject</th>
                                                        <th>Code</th>
                                                        <th>Type</th>
                                                        <th>Action</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <?php foreach ($assignments as $assignment): ?>
                                                        <tr>
                                                            <td><?php echo htmlspecialchars(trim(($assignment['class_name'] ?? '') . ' ' . ($assignment['class_arm'] ?? ''))); ?></td>
                                                            <td><?php echo htmlspecialchars((string) ($assignment['subject_name'] ?? '')); ?></td>
                                                            <td><?php echo htmlspecialchars((string) ($assignment['subject_code'] ?? '')); ?></td>
                                                            <td><?php echo ((int) ($assignment['is_core'] ?? 0) === 1) ? 'Core' : 'Elective'; ?></td>
                                                            <td>
                                                                <form method="POST" class="d-inline">
                                                                    <input type="hidden" name="action" value="remove_assignment">
                                                                    <input type="hidden" name="assignment_id" value="<?php echo (int) ($assignment['id'] ?? 0); ?>">
                                                                    <input type="hidden" name="academic_session_link" value="<?php echo $selectedSessionId; ?>">
                                                                    <input type="hidden" name="class_link" value="<?php echo $selectedClassId; ?>">
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
                </div>
            </div>
            @include('admin.partials.footer')
</body>
</html>
