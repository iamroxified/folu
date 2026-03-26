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
    $subject_id = $_POST['subject_id'];
    $class_id = $_POST['class_id'];
    
    // Check if assignment already exists
    $check_stmt = $pdo->prepare("SELECT COUNT(*) FROM class_subject_assignments WHERE subject_id = ? AND class_id = ?");
    $check_stmt->execute([$subject_id, $class_id]);
    
    if ($check_stmt->fetchColumn() == 0) {
        $stmt = $pdo->prepare("INSERT INTO class_subject_assignments (subject_id, class_id, assigned_date) VALUES (?, ?, NOW())");
        $stmt->execute([$subject_id, $class_id]);
        $success_message = "Subject assigned to class successfully!";
    } else {
        $error_message = "Subject is already assigned to this class!";
    }
}

// Handle unassignment
if (isset($_GET['unassign'])) {
    $assignment_id = $_GET['unassign'];
    $stmt = $pdo->prepare("DELETE FROM class_subject_assignments WHERE id = ?");
    $stmt->execute([$assignment_id]);
    $success_message = "Subject unassigned from class successfully!";
}

// Fetch all subjects
$subjects = QueryDB("SELECT * FROM subjects ORDER BY subject_name")->fetchAll();

// Fetch all classes
$classes = QueryDB("SELECT * FROM classes ORDER BY class_name")->fetchAll();

// Fetch current assignments with subject and class details
$assignments = QueryDB("
    SELECT csa.id, c.class_name, s.subject_name, csa.assigned_date
    FROM class_subject_assignments csa
    JOIN subjects s ON csa.subject_id = s.id
    JOIN classes c ON csa.class_id = c.id
    ORDER BY c.class_name, s.subject_name
")->fetchAll();

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <title>Assign Subjects to Classes</title>
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
                        <h2 class="text-dark pb-2 fw-bold">Assign Subjects to Classes</h2>
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
                                    <div class="card-title">Assign Subject to Class</div>
                                </div>
                                <div class="card-body">
                                    <form method="POST" action="assign.php">
                                        <div class="form-group">
                                            <label for="subject_id">Select Subject</label>
                                            <select class="form-control" name="subject_id" required>
                                                <option value="">Choose a subject...</option>
                                                <?php foreach ($subjects as $subject): ?>
                                                    <option value="<?php echo $subject['id']; ?>">
                                                        <?php echo $subject['subject_name']; ?>
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
                                        
                                        <button type="submit" class="btn btn-primary">Assign Subject</button>
                                    </form>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-12">
                            <div class="card">
                                <div class="card-header">
                                    <div class="card-title">Current Subject Assignments</div>
                                </div>
                                <div class="card-body">
                                    <div class="table-responsive">
                                        <table class="table table-bordered">
                                            <thead>
                                                <tr>
                                                    <th>Class</th>
                                                    <th>Subject</th>
                                                    <th>Assigned Date</th>
                                                    <th>Actions</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php foreach ($assignments as $assignment): ?>
                                                    <tr>
                                                        <td><?php echo $assignment['class_name']; ?></td>
                                                        <td><?php echo $assignment['subject_name']; ?></td>
                                                        <td><?php echo date('M d, Y', strtotime($assignment['assigned_date'])); ?></td>
                                                        <td>
                                                            <a href="assign.php?unassign=<?php echo $assignment['id']; ?>" 
                                                               class="btn btn-danger btn-sm"
                                                               onclick="return confirm('Are you sure you want to unassign this subject?')">
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

