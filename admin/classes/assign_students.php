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

// Handle assignment
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $student_id = $_POST['student_id'];
    $class_id = $_POST['class_id'];
    
    // Check if assignment already exists
    $check_stmt = $pdo->prepare("SELECT COUNT(*) FROM student_class_assignments WHERE student_id = ? AND class_id = ?");
    $check_stmt->execute([$student_id, $class_id]);
    
    if ($check_stmt->fetchColumn() == 0) {
        $stmt = $pdo->prepare("INSERT INTO student_class_assignments (student_id, class_id, assigned_date) VALUES (?, ?, NOW())");
        $stmt->execute([$student_id, $class_id]);
        $success_message = "Student assigned to class successfully!";
    } else {
        $error_message = "Student is already assigned to this class!";
    }
}

// Handle unassignment
if (isset($_GET['unassign'])) {
    $assignment_id = $_GET['unassign'];
    $stmt = $pdo->prepare("DELETE FROM student_class_assignments WHERE id = ?");
    $stmt->execute([$assignment_id]);
    $success_message = "Student unassigned from class successfully!";
}

// Fetch all students
$students = QueryDB("SELECT * FROM students WHERE status = 'active' ORDER BY first_name, last_name")->fetchAll();

// Fetch all classes
$classes = QueryDB("SELECT * FROM classes ORDER BY class_name")->fetchAll();

// Fetch current assignments with student and class details
$assignments = QueryDB("
    SELECT sca.id, s.first_name, s.last_name, s.student_number, c.class_name, sca.assigned_date
    FROM student_class_assignments sca
    JOIN students s ON sca.student_id = s.id
    JOIN classes c ON sca.class_id = c.id
    ORDER BY c.class_name, s.first_name, s.last_name
")->fetchAll();

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <title>Assign Students to Classes</title>
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
                        <h2 class="text-dark pb-2 fw-bold">Assign Students to Classes</h2>
                    </div>

                    <?php if (isset($success_message)): ?>
                        <div class="alert alert-success"><?php echo $success_message; ?></div>
                    <?php endif; ?>
                    
                    <?php if (isset($error_message)): ?>
                        <div class="alert alert-danger"><?php echo $error_message; ?></div>
                    <?php endif; ?>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-header">
                                    <div class="card-title">Assign Student to Class</div>
                                </div>
                                <div class="card-body">
                                    <form method="POST" action="assign_students.php">
                                        <div class="form-group">
                                            <label for="student_id">Select Student</label>
                                            <select class="form-control" name="student_id" required>
                                                <option value="">Choose a student...</option>
                                                <?php foreach ($students as $student): ?>
                                                    <option value="<?php echo $student['id']; ?>">
                                                        <?php echo $student['student_number'] . ' - ' . $student['first_name'] . ' ' . $student['last_name']; ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                        
                                        <div class="form-group">
                                            <label for="class_id">Select Class</label>
                                            <select class="form-control" name="class_id" required>
                                                <option value="">Choose a class...</option>
                                                <?php foreach ($classes as $class): ?>
                                                    <option value="<?php echo $class['id']; ?>">
                                                        <?php echo $class['class_name']; ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                        
                                        <button type="submit" class="btn btn-primary">Assign Student</button>
                                    </form>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-12">
                            <div class="card">
                                <div class="card-header">
                                    <div class="card-title">Current Class Assignments</div>
                                </div>
                                <div class="card-body">
                                    <div class="table-responsive">
                                        <table class="table table-bordered">
                                            <thead>
                                                <tr>
                                                    <th>Student Number</th>
                                                    <th>Student Name</th>
                                                    <th>Class</th>
                                                    <th>Assigned Date</th>
                                                    <th>Actions</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php foreach ($assignments as $assignment): ?>
                                                    <tr>
                                                        <td><?php echo $assignment['student_number']; ?></td>
                                                        <td><?php echo $assignment['first_name'] . ' ' . $assignment['last_name']; ?></td>
                                                        <td><?php echo $assignment['class_name']; ?></td>
                                                        <td><?php echo date('M d, Y', strtotime($assignment['assigned_date'])); ?></td>
                                                        <td>
                                                            <a href="assign_students.php?unassign=<?php echo $assignment['id']; ?>" 
                                                               class="btn btn-danger btn-sm"
                                                               onclick="return confirm('Are you sure you want to unassign this student?')">
                                                                Unassign
                                                            </a>
                                                        </td>
                                                    </tr>
                                                <?php endforeach; ?>
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
