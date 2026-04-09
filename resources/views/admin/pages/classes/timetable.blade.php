<?php
require_once base_path('db/config.php');
require_once base_path('db/functions.php');

if (!isset($_SESSION['adid'])) {
    header('Location: /admin/login.php');
    exit;
}

$message = '';
$error = '';
$days = [1 => 'Monday', 2 => 'Tuesday', 3 => 'Wednesday', 4 => 'Thursday', 5 => 'Friday', 6 => 'Saturday', 7 => 'Sunday'];
$selectedSessionId = (int) ($_REQUEST['academic_session_link'] ?? (get_current_academic_session_id() ?? 0));
$selectedClassId = (int) ($_REQUEST['class_link'] ?? 0);
$editId = (int) ($_GET['edit'] ?? 0);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = (string) ($_POST['action'] ?? '');

    try {
        if ($action === 'save_timetable_entry') {
            $timetableId = (int) ($_POST['timetable_id'] ?? 0);
            $selectedSessionId = (int) ($_POST['academic_session_link'] ?? $selectedSessionId);
            $selectedClassId = (int) ($_POST['class_link'] ?? $selectedClassId);
            $subjectId = (int) ($_POST['subject_link'] ?? 0);
            $teacherId = (int) ($_POST['teacher_link'] ?? 0);
            $dayOfWeek = (int) ($_POST['day_of_week'] ?? 0);
            $startTime = (string) ($_POST['start_time'] ?? '');
            $endTime = (string) ($_POST['end_time'] ?? '');
            $room = validate($_POST['room'] ?? '');

            if ($selectedSessionId < 1 || $selectedClassId < 1 || $subjectId < 1 || $teacherId < 1) {
                throw new Exception('Please choose the session, class, subject, and teacher.');
            }

            $isAssigned = QueryDB(
                'SELECT COUNT(*) FROM class_subject_assignments WHERE class_link = ? AND subject_link = ? AND academic_session_link = ?',
                [$selectedClassId, $subjectId, $selectedSessionId]
            )->fetchColumn();

            if ((int) $isAssigned === 0) {
                throw new Exception('Assign the subject to the class before adding it to the timetable.');
            }

            save_timetable_entry(
                $timetableId > 0 ? $timetableId : null,
                $selectedClassId,
                $subjectId,
                $teacherId,
                $selectedSessionId,
                $dayOfWeek,
                $startTime,
                $endTime,
                $room !== '' ? $room : null
            );

            $message = $timetableId > 0 ? 'Timetable entry updated successfully.' : 'Timetable entry created successfully.';
            $editId = 0;
        } elseif ($action === 'delete_timetable_entry') {
            $timetableId = (int) ($_POST['timetable_id'] ?? 0);
            $selectedSessionId = (int) ($_POST['academic_session_link'] ?? $selectedSessionId);
            $selectedClassId = (int) ($_POST['class_link'] ?? $selectedClassId);

            if ($timetableId < 1) {
                throw new Exception('Please choose a valid timetable entry to delete.');
            }

            $removed = delete_timetable_entry($timetableId);
            $message = $removed ? 'Timetable entry deleted successfully.' : 'No matching timetable entry was found.';
        }
    } catch (Throwable $e) {
        $error = $e->getMessage();
    }
}

$sessions = QueryDB('SELECT * FROM academic_sessions ORDER BY id DESC')->fetchAll();
$classes = QueryDB(
    'SELECT * FROM classes ORDER BY class_level, class_name, class_arm'
)->fetchAll();
$teachers = QueryDB(
    "SELECT t.*, u.username
     FROM teachers t
     LEFT JOIN users u ON t.user_link = u.id
     WHERE t.status = 'active' OR t.status IS NULL
     ORDER BY t.first_name, t.last_name"
)->fetchAll();
$editingEntry = $editId > 0
    ? QueryDB('SELECT * FROM class_timetables WHERE id = ? LIMIT 1', [$editId])->fetch(PDO::FETCH_ASSOC)
    : null;

if ($editingEntry) {
    $selectedSessionId = (int) ($editingEntry['academic_session_link'] ?? $selectedSessionId);
    $selectedClassId = (int) ($editingEntry['class_link'] ?? $selectedClassId);
}

$classAssignments = $selectedClassId > 0 ? get_class_subject_assignments($selectedClassId, $selectedSessionId) : [];
$timetableEntries = get_timetable_entries($selectedClassId > 0 ? $selectedClassId : null, $selectedSessionId);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <title>Class Timetable Management</title>
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
                        <h2 class="text-dark pb-2 fw-bold">Class Timetable Management</h2>
                        <div class="ml-md-auto py-2 py-md-0">
                            <a href="{{ url('/admin/classes/manage.php') }}" class="btn btn-secondary">Manage Classes</a>
                            <a href="{{ url('/admin/subjects/assign.php') }}" class="btn btn-info">Class Subjects</a>
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
                            <div class="card-title">Timetable Filter</div>
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
                        <div class="col-md-4">
                            <div class="card">
                                <div class="card-header">
                                    <div class="card-title"><?php echo $editingEntry ? 'Edit Timetable Entry' : 'Add Timetable Entry'; ?></div>
                                    <div class="card-category">Only subjects already assigned to the selected class can be scheduled.</div>
                                </div>
                                <div class="card-body">
                                    <form method="POST">
                                        <input type="hidden" name="action" value="save_timetable_entry">
                                        <input type="hidden" name="timetable_id" value="<?php echo (int) ($editingEntry['id'] ?? 0); ?>">
                                        <div class="form-group">
                                            <label for="form_session_id">Academic Session</label>
                                            <select class="form-control" id="form_session_id" name="academic_session_link" required>
                                                <?php foreach ($sessions as $session): ?>
                                                    <option value="<?php echo (int) $session['id']; ?>" <?php echo $selectedSessionId === (int) $session['id'] ? 'selected' : ''; ?>>
                                                        <?php echo htmlspecialchars((string) $session['session_name']); ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                        <div class="form-group">
                                            <label for="form_class_link">Class</label>
                                            <select class="form-control" id="form_class_link" name="class_link" required>
                                                <option value="">Select Class</option>
                                                <?php foreach ($classes as $class): ?>
                                                    <option value="<?php echo (int) $class['id']; ?>" <?php echo $selectedClassId === (int) $class['id'] ? 'selected' : ''; ?>>
                                                        <?php echo htmlspecialchars(trim(($class['class_name'] ?? '') . ' ' . ($class['class_arm'] ?? ''))); ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                        <div class="form-group">
                                            <label for="subject_link">Subject</label>
                                            <select class="form-control" id="subject_link" name="subject_link" required>
                                                <option value="">Select Subject</option>
                                                <?php foreach ($classAssignments as $assignment): ?>
                                                    <option value="<?php echo (int) ($assignment['subject_link'] ?? 0); ?>" <?php echo ((int) ($editingEntry['subject_link'] ?? 0) === (int) ($assignment['subject_link'] ?? 0)) ? 'selected' : ''; ?>>
                                                        <?php echo htmlspecialchars((string) (($assignment['subject_name'] ?? '') . ' (' . ($assignment['subject_code'] ?? '') . ')')); ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                        <div class="form-group">
                                            <label for="teacher_link">Teacher</label>
                                            <select class="form-control" id="teacher_link" name="teacher_link" required>
                                                <option value="">Select Teacher</option>
                                                <?php foreach ($teachers as $teacher): ?>
                                                    <option value="<?php echo (int) $teacher['id']; ?>" <?php echo ((int) ($editingEntry['teacher_link'] ?? 0) === (int) $teacher['id']) ? 'selected' : ''; ?>>
                                                        <?php echo htmlspecialchars((string) ($teacher['teacher_id'] . ' - ' . $teacher['first_name'] . ' ' . $teacher['last_name'])); ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                        <div class="form-group">
                                            <label for="day_of_week">Day of Week</label>
                                            <select class="form-control" id="day_of_week" name="day_of_week" required>
                                                <option value="">Select Day</option>
                                                <?php foreach ($days as $value => $label): ?>
                                                    <option value="<?php echo $value; ?>" <?php echo ((int) ($editingEntry['day_of_week'] ?? 0) === $value) ? 'selected' : ''; ?>>
                                                        <?php echo htmlspecialchars($label); ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label for="start_time">Start Time</label>
                                                    <input type="time" class="form-control" id="start_time" name="start_time" value="<?php echo htmlspecialchars((string) ($editingEntry['start_time'] ?? '')); ?>" required>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label for="end_time">End Time</label>
                                                    <input type="time" class="form-control" id="end_time" name="end_time" value="<?php echo htmlspecialchars((string) ($editingEntry['end_time'] ?? '')); ?>" required>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="form-group">
                                            <label for="room">Room</label>
                                            <input type="text" class="form-control" id="room" name="room" value="<?php echo htmlspecialchars((string) ($editingEntry['room'] ?? '')); ?>" placeholder="Hall 1, Room 4, Science Lab">
                                        </div>
                                        <button type="submit" class="btn btn-primary"><?php echo $editingEntry ? 'Update Entry' : 'Create Entry'; ?></button>
                                        <?php if ($editingEntry): ?>
                                            <a href="{{ url('/admin/classes/timetable.php?academic_session_link=' . $selectedSessionId . '&class_link=' . $selectedClassId) }}" class="btn btn-secondary">Cancel</a>
                                        <?php endif; ?>
                                    </form>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-8">
                            <div class="card">
                                <div class="card-header">
                                    <div class="card-title">Timetable Entries</div>
                                </div>
                                <div class="card-body">
                                    <?php if (empty($timetableEntries)): ?>
                                        <div class="alert alert-info mb-0">No timetable entries were found for this filter.</div>
                                    <?php else: ?>
                                        <div class="table-responsive">
                                            <table class="table table-bordered table-striped">
                                                <thead>
                                                    <tr>
                                                        <th>Class</th>
                                                        <th>Day</th>
                                                        <th>Time</th>
                                                        <th>Subject</th>
                                                        <th>Teacher</th>
                                                        <th>Room</th>
                                                        <th>Actions</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <?php foreach ($timetableEntries as $entry): ?>
                                                        <tr>
                                                            <td><?php echo htmlspecialchars(trim(($entry['class_name'] ?? '') . ' ' . ($entry['class_arm'] ?? ''))); ?></td>
                                                            <td><?php echo htmlspecialchars($days[(int) ($entry['day_of_week'] ?? 0)] ?? 'Unknown'); ?></td>
                                                            <td><?php echo htmlspecialchars(date('H:i', strtotime((string) $entry['start_time'])) . ' - ' . date('H:i', strtotime((string) $entry['end_time']))); ?></td>
                                                            <td><?php echo htmlspecialchars((string) ($entry['subject_name'] ?? '')); ?></td>
                                                            <td><?php echo htmlspecialchars(trim(($entry['first_name'] ?? '') . ' ' . ($entry['last_name'] ?? ''))); ?></td>
                                                            <td><?php echo htmlspecialchars((string) ($entry['room'] ?? '')); ?></td>
                                                            <td>
                                                                <a href="{{ url('/admin/classes/timetable.php?academic_session_link=' . $selectedSessionId . '&class_link=' . ($selectedClassId > 0 ? $selectedClassId : $entry['class_link']) . '&edit=' . $entry['id']) }}" class="btn btn-warning btn-sm">Edit</a>
                                                                <form method="POST" class="d-inline">
                                                                    <input type="hidden" name="action" value="delete_timetable_entry">
                                                                    <input type="hidden" name="timetable_id" value="<?php echo (int) ($entry['id'] ?? 0); ?>">
                                                                    <input type="hidden" name="academic_session_link" value="<?php echo $selectedSessionId; ?>">
                                                                    <input type="hidden" name="class_link" value="<?php echo (int) ($selectedClassId > 0 ? $selectedClassId : ($entry['class_link'] ?? 0)); ?>">
                                                                    <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Delete this timetable entry?')">Delete</button>
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
