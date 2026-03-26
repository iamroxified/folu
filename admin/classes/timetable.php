<?php
// Start session
session_start();

// Include database configuration and functions
require '../../db/config.php';
require '../../db/functions.php';

// Check if user is logged in
if (!isset($_SESSION['adid'])) {
    header('location:../../login.php');
    exit;
}

// Handle form submission for adding/editing timetable entries
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $class_id = $_POST['class_id'];
    $subject_id = $_POST['subject_id'];
    $teacher_id = $_POST['teacher_id'];
    $day_of_week = $_POST['day_of_week'];
    $start_time = $_POST['start_time'];
    $end_time = $_POST['end_time'];
    $room = validate($_POST['room']);
    
    $action = $_POST['action'];
    
    if ($action === 'add') {
        // Check for conflicts
        $conflict_check = $pdo->prepare("
            SELECT COUNT(*) FROM class_timetables 
            WHERE class_id = ? AND day_of_week = ? 
            AND ((start_time <= ? AND end_time > ?) OR (start_time < ? AND end_time >= ?))
        ");
        $conflict_check->execute([$class_id, $day_of_week, $start_time, $start_time, $end_time, $end_time]);
        
        if ($conflict_check->fetchColumn() == 0) {
            $stmt = $pdo->prepare("
                INSERT INTO class_timetables (class_id, subject_id, teacher_id, day_of_week, start_time, end_time, room, created_at) 
                VALUES (?, ?, ?, ?, ?, ?, ?, NOW())
            ");
            $stmt->execute([$class_id, $subject_id, $teacher_id, $day_of_week, $start_time, $end_time, $room]);
            $success_message = "Timetable entry added successfully!";
        } else {
            $error_message = "Time conflict detected for this class!";
        }
    } elseif ($action === 'edit') {
        $timetable_id = $_POST['timetable_id'];
        $stmt = $pdo->prepare("
            UPDATE class_timetables 
            SET class_id = ?, subject_id = ?, teacher_id = ?, day_of_week = ?, start_time = ?, end_time = ?, room = ?, updated_at = NOW()
            WHERE id = ?
        ");
        $stmt->execute([$class_id, $subject_id, $teacher_id, $day_of_week, $start_time, $end_time, $room, $timetable_id]);
        $success_message = "Timetable entry updated successfully!";
    }
}

// Handle delete
if (isset($_GET['delete'])) {
    $timetable_id = $_GET['delete'];
    $stmt = $pdo->prepare("DELETE FROM class_timetables WHERE id = ?");
    $stmt->execute([$timetable_id]);
    $success_message = "Timetable entry deleted successfully!";
}

// Get entry for editing
$editing_entry = null;
if (isset($_GET['edit'])) {
    $timetable_id = $_GET['edit'];
    $stmt = $pdo->prepare("SELECT * FROM class_timetables WHERE id = ?");
    $stmt->execute([$timetable_id]);
    $editing_entry = $stmt->fetch();
}

// Fetch required data
$classes = QueryDB("SELECT * FROM classes ORDER BY class_name")->fetchAll();
$subjects = QueryDB("SELECT * FROM subjects ORDER BY subject_name")->fetchAll();
$teachers = QueryDB("SELECT * FROM staff WHERE role = 'teacher' ORDER BY first_name, last_name")->fetchAll();

// Fetch timetable entries
$selected_class = $_GET['class_id'] ?? '';
$timetable_query = "
    SELECT ct.*, c.class_name, s.subject_name, st.first_name, st.last_name
    FROM class_timetables ct
    JOIN classes c ON ct.class_id = c.id
    JOIN subjects s ON ct.subject_id = s.id
    JOIN staff st ON ct.teacher_id = st.id
";

if ($selected_class) {
    $timetable_query .= " WHERE ct.class_id = $selected_class";
}

$timetable_query .= " ORDER BY ct.day_of_week, ct.start_time";
$timetable_entries = QueryDB($timetable_query)->fetchAll();

$days = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <title>Class Timetable Management</title>
    <?php include('../../nav/links.php'); ?>
</head>

<body>
    <div class="wrapper">
        <?php include('../../nav/sidebar.php'); ?>

        <div class="main-panel">
            <?php include('../../nav/header.php'); ?>
            <div class="container">
                <div class="page-inner">
                    <div class="d-flex align-items-left flex-column flex-md-row">
                        <h2 class="text-dark pb-2 fw-bold">Class Timetable Management</h2>
                    </div>

                    <?php if (isset($success_message)): ?>
                        <div class="alert alert-success"><?php echo $success_message; ?></div>
                    <?php endif; ?>
                    
                    <?php if (isset($error_message)): ?>
                        <div class="alert alert-danger"><?php echo $error_message; ?></div>
                    <?php endif; ?>

                    <div class="row">
                        <!-- Filter and Add Form -->
                        <div class="col-md-4">
                            <div class="card">
                                <div class="card-header">
                                    <div class="card-title">Filter by Class</div>
                                </div>
                                <div class="card-body">
                                    <form method="GET" action="timetable.php">
                                        <select class="form-control" name="class_id" onchange="this.form.submit()">
                                            <option value="">All Classes</option>
                                            <?php foreach ($classes as $class): ?>
                                                <option value="<?php echo $class['id']; ?>" 
                                                        <?php echo $selected_class == $class['id'] ? 'selected' : ''; ?>>
                                                    <?php echo $class['class_name']; ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </form>
                                </div>
                            </div>

                            <div class="card">
                                <div class="card-header">
                                    <div class="card-title">
                                        <?php echo $editing_entry ? 'Edit Timetable Entry' : 'Add Timetable Entry'; ?>
                                    </div>
                                </div>
                                <div class="card-body">
                                    <form method="POST" action="timetable.php">
                                        <input type="hidden" name="action" value="<?php echo $editing_entry ? 'edit' : 'add'; ?>">
                                        <?php if ($editing_entry): ?>
                                            <input type="hidden" name="timetable_id" value="<?php echo $editing_entry['id']; ?>">
                                        <?php endif; ?>
                                        
                                        <div class="form-group">
                                            <label>Class</label>
                                            <select class="form-control" name="class_id" required>
                                                <option value="">Select Class</option>
                                                <?php foreach ($classes as $class): ?>
                                                    <option value="<?php echo $class['id']; ?>"
                                                            <?php echo ($editing_entry && $editing_entry['class_id'] == $class['id']) ? 'selected' : ''; ?>>
                                                        <?php echo $class['class_name']; ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>

                                        <div class="form-group">
                                            <label>Subject</label>
                                            <select class="form-control" name="subject_id" required>
                                                <option value="">Select Subject</option>
                                                <?php foreach ($subjects as $subject): ?>
                                                    <option value="<?php echo $subject['id']; ?>"
                                                            <?php echo ($editing_entry && $editing_entry['subject_id'] == $subject['id']) ? 'selected' : ''; ?>>
                                                        <?php echo $subject['subject_name']; ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>

                                        <div class="form-group">
                                            <label>Teacher</label>
                                            <select class="form-control" name="teacher_id" required>
                                                <option value="">Select Teacher</option>
                                                <?php foreach ($teachers as $teacher): ?>
                                                    <option value="<?php echo $teacher['id']; ?>"
                                                            <?php echo ($editing_entry && $editing_entry['teacher_id'] == $teacher['id']) ? 'selected' : ''; ?>>
                                                        <?php echo $teacher['first_name'] . ' ' . $teacher['last_name']; ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>

                                        <div class="form-group">
                                            <label>Day of Week</label>
                                            <select class="form-control" name="day_of_week" required>
                                                <option value="">Select Day</option>
                                                <?php foreach ($days as $index => $day): ?>
                                                    <option value="<?php echo $index + 1; ?>"
                                                            <?php echo ($editing_entry && $editing_entry['day_of_week'] == ($index + 1)) ? 'selected' : ''; ?>>
                                                        <?php echo $day; ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>

                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label>Start Time</label>
                                                    <input type="time" class="form-control" name="start_time" 
                                                           value="<?php echo $editing_entry ? $editing_entry['start_time'] : ''; ?>" required>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label>End Time</label>
                                                    <input type="time" class="form-control" name="end_time" 
                                                           value="<?php echo $editing_entry ? $editing_entry['end_time'] : ''; ?>" required>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="form-group">
                                            <label>Room</label>
                                            <input type="text" class="form-control" name="room" 
                                                   value="<?php echo $editing_entry ? htmlspecialchars($editing_entry['room']) : ''; ?>">
                                        </div>

                                        <button type="submit" class="btn btn-primary">
                                            <?php echo $editing_entry ? 'Update Entry' : 'Add Entry'; ?>
                                        </button>
                                        
                                        <?php if ($editing_entry): ?>
                                            <a href="timetable.php" class="btn btn-secondary">Cancel</a>
                                        <?php endif; ?>
                                    </form>
                                </div>
                            </div>
                        </div>

                        <!-- Timetable Display -->
                        <div class="col-md-8">
                            <div class="card">
                                <div class="card-header">
                                    <div class="card-title">
                                        Timetable Entries
                                        <?php if ($selected_class): ?>
                                            - <?php 
                                                $class_name = '';
                                                foreach ($classes as $class) {
                                                    if ($class['id'] == $selected_class) {
                                                        $class_name = $class['class_name'];
                                                        break;
                                                    }
                                                }
                                                echo $class_name;
                                            ?>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                <div class="card-body">
                                    <div class="table-responsive">
                                        <table class="table table-bordered">
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
                                                <?php if (empty($timetable_entries)): ?>
                                                    <tr>
                                                        <td colspan="7" class="text-center">No timetable entries found</td>
                                                    </tr>
                                                <?php else: ?>
                                                    <?php foreach ($timetable_entries as $entry): ?>
                                                        <tr>
                                                            <td><?php echo $entry['class_name']; ?></td>
                                                            <td><?php echo $days[$entry['day_of_week'] - 1]; ?></td>
                                                            <td><?php echo date('H:i', strtotime($entry['start_time'])) . ' - ' . date('H:i', strtotime($entry['end_time'])); ?></td>
                                                            <td><?php echo $entry['subject_name']; ?></td>
                                                            <td><?php echo $entry['first_name'] . ' ' . $entry['last_name']; ?></td>
                                                            <td><?php echo $entry['room']; ?></td>
                                                            <td>
                                                                <a href="timetable.php?edit=<?php echo $entry['id']; ?>" 
                                                                   class="btn btn-warning btn-sm">Edit</a>
                                                                <a href="timetable.php?delete=<?php echo $entry['id']; ?>" 
                                                                   class="btn btn-danger btn-sm"
                                                                   onclick="return confirm('Are you sure you want to delete this entry?')">Delete</a>
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
        </div>
    </div>
</body>

</html>
