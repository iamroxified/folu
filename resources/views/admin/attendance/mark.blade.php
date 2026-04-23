<?php
require_once base_path('db/config.php');
require_once base_path('db/functions.php');

if (!isset($_SESSION['adid'])) {
    header('Location: /admin/login.php');
    exit;
}

$currentSession = get_current_academic_session();
$selectedSessionId = (int) ($_REQUEST['academic_session_link'] ?? ($currentSession['id'] ?? 0));
$selectedClassId = (int) ($_REQUEST['class_link'] ?? 0);
$selectedSubjectId = (int) ($_REQUEST['subject_link'] ?? 0);
$selectedDate = validate($_REQUEST['attendance_date'] ?? date('Y-m-d'));
$message = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'save_attendance') {
    try {
        $selectedSessionId = (int) ($_POST['academic_session_link'] ?? $selectedSessionId);
        $selectedClassId = (int) ($_POST['class_link'] ?? $selectedClassId);
        $selectedSubjectId = (int) ($_POST['subject_link'] ?? $selectedSubjectId);
        $selectedDate = validate($_POST['attendance_date'] ?? $selectedDate);

        if ($selectedSessionId < 1 || $selectedClassId < 1) {
            throw new Exception('Please choose a session and class before saving attendance.');
        }

        $records = [];
        foreach (($_POST['attendance'] ?? []) as $studentId => $status) {
            $records[(int) $studentId] = [
                'status' => validate((string) $status),
                'remarks' => validate((string) (($_POST['remarks'][$studentId] ?? ''))),
            ];
        }

        $saved = save_bulk_attendance_records(
            $selectedClassId,
            $selectedSubjectId > 0 ? $selectedSubjectId : null,
            $selectedSessionId,
            $selectedDate,
            $records,
            (int) $_SESSION['adid']
        );

        $message = 'Attendance saved for ' . $saved . ' student(s).';
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
$subjects = [];

if ($selectedClass) {
    $subjects = QueryDB(
        "SELECT * FROM subjects WHERE class_level = ? OR class_level = 'ALL' ORDER BY subject_name",
        [$selectedClass['class_level']]
    )->fetchAll();
}

$students = $selectedClassId > 0 ? get_students_by_class_and_session($selectedClassId, $selectedSessionId) : [];
$subjectFilter = $selectedSubjectId > 0 ? $selectedSubjectId : null;
$existingAttendance = [];

if ($selectedClassId > 0) {
    foreach (
        QueryDB(
            'SELECT * FROM attendance WHERE class_link = ? AND date = ? AND ((subject_link IS NULL AND ? IS NULL) OR subject_link = ?) AND (? = 0 OR academic_session_link IS NULL OR academic_session_link = ?)',
            [$selectedClassId, $selectedDate, $subjectFilter, $subjectFilter, $selectedSessionId, $selectedSessionId]
        )->fetchAll() as $row
    ) {
        $existingAttendance[(int) $row['student_link']] = $row;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <title>Mark Attendance</title>
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
                        <h2 class="text-dark pb-2 fw-bold">Mark Attendance</h2>
                    </div>

                    <?php if ($message !== ''): ?>
                        <div class="alert alert-success"><?php echo htmlspecialchars($message); ?></div>
                    <?php endif; ?>
                    <?php if ($error !== ''): ?>
                        <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
                    <?php endif; ?>

                    <div class="card">
                        <div class="card-header">
                            <div class="card-title">Attendance Setup</div>
                            <div class="card-category">Select the class, optional subject, and date. Use bulk-friendly controls to mark the register quickly.</div>
                        </div>
                        <div class="card-body">
                            <form method="GET" class="row">
                                <div class="col-md-3">
                                    <label for="academic_session_link">Session</label>
                                    <select class="form-control" id="academic_session_link" name="academic_session_link">
                                        <?php foreach ($sessions as $session): ?>
                                            <option value="<?php echo (int) $session['id']; ?>" <?php echo $selectedSessionId === (int) $session['id'] ? 'selected' : ''; ?>>
                                                <?php echo htmlspecialchars((string) $session['session_name']); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <label for="class_link">Class</label>
                                    <select class="form-control" id="class_link" name="class_link">
                                        <option value="">Select Class</option>
                                        <?php foreach ($classes as $class): ?>
                                            <option value="<?php echo (int) $class['id']; ?>" <?php echo $selectedClassId === (int) $class['id'] ? 'selected' : ''; ?>>
                                                <?php echo htmlspecialchars(trim(($class['class_name'] ?? '') . ' ' . ($class['class_arm'] ?? ''))); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <label for="subject_link">Subject</label>
                                    <select class="form-control" id="subject_link" name="subject_link">
                                        <option value="0">General / Class Attendance</option>
                                        <?php foreach ($subjects as $subject): ?>
                                            <option value="<?php echo (int) $subject['id']; ?>" <?php echo $selectedSubjectId === (int) $subject['id'] ? 'selected' : ''; ?>>
                                                <?php echo htmlspecialchars((string) $subject['subject_name']); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="col-md-2">
                                    <label for="attendance_date">Date</label>
                                    <input type="date" class="form-control" id="attendance_date" name="attendance_date" value="<?php echo htmlspecialchars($selectedDate); ?>">
                                </div>
                                <div class="col-md-1 d-flex align-items-end">
                                    <button type="submit" class="btn btn-primary w-100">Load</button>
                                </div>
                            </form>
                        </div>
                    </div>

                    <?php if ($selectedClassId > 0 && !empty($students)): ?>
                        <div class="card mt-3">
                            <div class="card-header">
                                <div class="card-title">Attendance Register</div>
                                <div class="card-category">Mark the full class at once, then adjust individual students where needed.</div>
                            </div>
                            <div class="card-body">
                                <div class="mb-3 d-flex gap-2 flex-wrap">
                                    <button type="button" class="btn btn-success btn-sm bulk-status" data-status="present">Mark All Present</button>
                                    <button type="button" class="btn btn-danger btn-sm bulk-status" data-status="absent">Mark All Absent</button>
                                    <button type="button" class="btn btn-warning btn-sm bulk-status" data-status="late">Mark All Late</button>
                                    <button type="button" class="btn btn-secondary btn-sm bulk-status" data-status="excused">Mark All Excused</button>
                                </div>

                                <form method="POST">
                                    <input type="hidden" name="action" value="save_attendance">
                                    <input type="hidden" name="academic_session_link" value="<?php echo $selectedSessionId; ?>">
                                    <input type="hidden" name="class_link" value="<?php echo $selectedClassId; ?>">
                                    <input type="hidden" name="subject_link" value="<?php echo $selectedSubjectId; ?>">
                                    <input type="hidden" name="attendance_date" value="<?php echo htmlspecialchars($selectedDate); ?>">
                                    <div class="table-responsive">
                                        <table class="table table-bordered table-striped">
                                            <thead>
                                                <tr>
                                                    <th>Admission No</th>
                                                    <th>Student</th>
                                                    <th>Status</th>
                                                    <th>Remarks</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php foreach ($students as $student): ?>
                                                    <?php $existing = $existingAttendance[(int) $student['id']] ?? []; ?>
                                                    <tr>
                                                        <td><?php echo htmlspecialchars((string) $student['admission_no']); ?></td>
                                                        <td><?php echo htmlspecialchars(trim(($student['first_name'] ?? '') . ' ' . ($student['last_name'] ?? '') . ' ' . ($student['other_names'] ?? ''))); ?></td>
                                                        <td style="min-width: 180px;">
                                                            <select class="form-control status-select" name="attendance[<?php echo (int) $student['id']; ?>]">
                                                                <option value="">Not Marked</option>
                                                                <?php foreach (['present', 'absent', 'late', 'excused'] as $status): ?>
                                                                    <option value="<?php echo $status; ?>" <?php echo (($existing['status'] ?? '') === $status) ? 'selected' : ''; ?>>
                                                                        <?php echo htmlspecialchars(ucfirst($status)); ?>
                                                                    </option>
                                                                <?php endforeach; ?>
                                                            </select>
                                                        </td>
                                                        <td>
                                                            <input type="text" class="form-control" name="remarks[<?php echo (int) $student['id']; ?>]" value="<?php echo htmlspecialchars((string) ($existing['remarks'] ?? '')); ?>" placeholder="Optional note">
                                                        </td>
                                                    </tr>
                                                <?php endforeach; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                    <button type="submit" class="btn btn-success">Save Attendance</button>
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
                document.querySelectorAll('.bulk-status').forEach((button) => {
                    button.addEventListener('click', () => {
                        document.querySelectorAll('.status-select').forEach((select) => {
                            select.value = button.dataset.status;
                        });
                    });
                });
            </script>
</body>
</html>
