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
$selectedTermId = (int) ($_REQUEST['term_link'] ?? (get_current_academic_term_id($selectedSessionId) ?? 0));

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $studentId = (int) ($_POST['student_id'] ?? 0);
        $classId = (int) ($_POST['class_id'] ?? 0);
        $selectedSessionId = (int) ($_POST['academic_session_link'] ?? $selectedSessionId);
        $selectedTermId = (int) ($_POST['term_link'] ?? $selectedTermId);

        if ($studentId < 1 || $classId < 1 || $selectedSessionId < 1 || $selectedTermId < 1) {
            throw new Exception('Please select the student, class, session, and term.');
        }

        QueryDB(
            'UPDATE students SET class_link = ?, academic_session_link = ?, term_link = ?, updated_at = NOW() WHERE id = ?',
            [$classId, $selectedSessionId, $selectedTermId, $studentId]
        );

        $message = 'Student placement updated successfully.';
    } catch (Throwable $e) {
        $error = $e->getMessage();
    }
}

if (isset($_GET['unassign'])) {
    try {
        $studentId = (int) $_GET['unassign'];
        QueryDB('UPDATE students SET class_link = NULL, updated_at = NOW() WHERE id = ?', [$studentId]);
        $message = 'Student unassigned from class successfully.';
    } catch (Throwable $e) {
        $error = $e->getMessage();
    }
}

$sessions = QueryDB('SELECT * FROM academic_sessions ORDER BY start_date DESC, id DESC')->fetchAll();
$terms = $selectedSessionId > 0 ? get_terms_for_session($selectedSessionId) : [];
$students = QueryDB("SELECT * FROM students WHERE status = 'active' ORDER BY first_name, last_name")->fetchAll();
$classes = QueryDB("SELECT * FROM classes ORDER BY class_level, class_name, class_arm")->fetchAll();
$assignments = QueryDB(
    "SELECT s.id AS student_id,
            s.first_name,
            s.last_name,
            s.admission_no,
            s.academic_session_link,
            s.term_link,
            c.class_name,
            c.class_arm,
            ac.session_name,
            at.term_name,
            s.updated_at,
            s.created_at
     FROM students s
     LEFT JOIN classes c ON s.class_link = c.id
     LEFT JOIN academic_sessions ac ON s.academic_session_link = ac.id
     LEFT JOIN academic_terms at ON s.term_link = at.id
     WHERE s.class_link IS NOT NULL
       AND (? = 0 OR s.academic_session_link = ?)
       AND (? = 0 OR s.term_link = ?)
     ORDER BY ac.start_date DESC, at.term_code ASC, c.class_level, c.class_name, s.first_name, s.last_name",
    [$selectedSessionId, $selectedSessionId, $selectedTermId, $selectedTermId]
)->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <title>Assign Students to Classes</title>
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
                        <h2 class="text-dark pb-2 fw-bold">Assign Students to Classes</h2>
                    </div>

                    <?php if ($message !== ''): ?>
                        <div class="alert alert-success"><?php echo htmlspecialchars($message); ?></div>
                    <?php endif; ?>
                    <?php if ($error !== ''): ?>
                        <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
                    <?php endif; ?>

                    <div class="alert alert-info">
                        Student placement now carries the academic session and term. The class itself remains a reusable school entity.
                    </div>

                    <div class="row">
                        <div class="col-md-5">
                            <div class="card">
                                <div class="card-header">
                                    <div class="card-title">Place Student in Class</div>
                                </div>
                                <div class="card-body">
                                    <form method="POST">
                                        <div class="form-group">
                                            <label for="student_id">Student</label>
                                            <select class="form-control" id="student_id" name="student_id" required>
                                                <option value="">Choose a student...</option>
                                                <?php foreach ($students as $student): ?>
                                                    <option value="<?php echo (int) $student['id']; ?>">
                                                        <?php echo htmlspecialchars((string) ($student['admission_no'] ?? 'N/A')); ?> - <?php echo htmlspecialchars(trim(($student['first_name'] ?? '') . ' ' . ($student['last_name'] ?? ''))); ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                        <div class="form-group">
                                            <label for="class_id">Class</label>
                                            <select class="form-control" id="class_id" name="class_id" required>
                                                <option value="">Choose a class...</option>
                                                <?php foreach ($classes as $class): ?>
                                                    <option value="<?php echo (int) $class['id']; ?>">
                                                        <?php echo htmlspecialchars(trim(($class['class_name'] ?? '') . ' ' . ($class['class_arm'] ?? ''))); ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                        <div class="form-group">
                                            <label for="academic_session_link">Session</label>
                                            <select class="form-control" id="academic_session_link" name="academic_session_link" required>
                                                <option value="">Choose session...</option>
                                                <?php foreach ($sessions as $session): ?>
                                                    <option value="<?php echo (int) $session['id']; ?>" <?php echo $selectedSessionId === (int) $session['id'] ? 'selected' : ''; ?>>
                                                        <?php echo htmlspecialchars((string) $session['session_name']); ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                        <div class="form-group">
                                            <label for="term_link">Term</label>
                                            <select class="form-control" id="term_link" name="term_link" required>
                                                <option value="">Choose term...</option>
                                                <?php foreach ($terms as $term): ?>
                                                    <option value="<?php echo (int) $term['id']; ?>" <?php echo $selectedTermId === (int) $term['id'] ? 'selected' : ''; ?>>
                                                        <?php echo htmlspecialchars(term_label($term)); ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                        <button type="submit" class="btn btn-primary">Save Placement</button>
                                    </form>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-7">
                            <div class="card">
                                <div class="card-header">
                                    <div class="card-title">Filter Placements</div>
                                </div>
                                <div class="card-body">
                                    <form method="GET" class="row">
                                        <div class="col-md-6">
                                            <label for="filter_session">Session</label>
                                            <select class="form-control" id="filter_session" name="academic_session_link">
                                                <option value="0">All Sessions</option>
                                                <?php foreach ($sessions as $session): ?>
                                                    <option value="<?php echo (int) $session['id']; ?>" <?php echo $selectedSessionId === (int) $session['id'] ? 'selected' : ''; ?>>
                                                        <?php echo htmlspecialchars((string) $session['session_name']); ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                        <div class="col-md-4">
                                            <label for="filter_term">Term</label>
                                            <select class="form-control" id="filter_term" name="term_link">
                                                <option value="0">All Terms</option>
                                                <?php foreach ($terms as $term): ?>
                                                    <option value="<?php echo (int) $term['id']; ?>" <?php echo $selectedTermId === (int) $term['id'] ? 'selected' : ''; ?>>
                                                        <?php echo htmlspecialchars(term_label($term)); ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                        <div class="col-md-2 d-flex align-items-end">
                                            <button type="submit" class="btn btn-secondary w-100">Load</button>
                                        </div>
                                    </form>
                                </div>
                            </div>

                            <div class="card mt-3">
                                <div class="card-header">
                                    <div class="card-title">Current Student Placements</div>
                                </div>
                                <div class="card-body">
                                    <?php if (empty($assignments)): ?>
                                        <div class="alert alert-info mb-0">No student placements were found for this filter.</div>
                                    <?php else: ?>
                                        <div class="table-responsive">
                                            <table class="table table-bordered table-striped">
                                                <thead>
                                                    <tr>
                                                        <th>Admission No</th>
                                                        <th>Student</th>
                                                        <th>Class</th>
                                                        <th>Session</th>
                                                        <th>Term</th>
                                                        <th>Updated</th>
                                                        <th>Action</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <?php foreach ($assignments as $assignment): ?>
                                                        <tr>
                                                            <td><?php echo htmlspecialchars((string) ($assignment['admission_no'] ?? 'N/A')); ?></td>
                                                            <td><?php echo htmlspecialchars(trim(($assignment['first_name'] ?? '') . ' ' . ($assignment['last_name'] ?? ''))); ?></td>
                                                            <td><?php echo htmlspecialchars(trim(($assignment['class_name'] ?? '') . ' ' . ($assignment['class_arm'] ?? ''))); ?></td>
                                                            <td><?php echo htmlspecialchars((string) ($assignment['session_name'] ?? 'N/A')); ?></td>
                                                            <td><?php echo htmlspecialchars((string) ($assignment['term_name'] ?? 'N/A')); ?></td>
                                                            <td><?php echo htmlspecialchars((string) ($assignment['updated_at'] ?? $assignment['created_at'] ?? '')); ?></td>
                                                            <td>
                                                                <a href="{{ url('/admin/classes/assign_students.php?academic_session_link=' . $selectedSessionId . '&term_link=' . $selectedTermId . '&unassign=' . $assignment['student_id']) }}"
                                                                   class="btn btn-danger btn-sm"
                                                                   onclick="return confirm('Unassign this student from the class?')">
                                                                    Unassign
                                                                </a>
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
