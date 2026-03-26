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

// Handle form submissions
$action = $_GET['action'] ?? '';
$subject_id = $_GET['id'] ?? '';
$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if ($action === 'add') {
        $subject_name = validate($_POST['subject_name']);
        $subject_code = validate($_POST['subject_code']);
        $description = validate($_POST['description']);
        $credits = (int)$_POST['credits'];
        
        try {
            $stmt = $pdo->prepare("INSERT INTO subjects (subject_name, subject_code, description, credits, created_at) VALUES (?, ?, ?, ?, NOW())");
            $stmt->execute([$subject_name, $subject_code, $description, $credits]);
            $message = "Subject added successfully!";
        } catch (PDOException $e) {
            $message = "Error: " . $e->getMessage();
        }
    } elseif ($action === 'edit' && $subject_id) {
        $subject_name = validate($_POST['subject_name']);
        $subject_code = validate($_POST['subject_code']);
        $description = validate($_POST['description']);
        $credits = (int)$_POST['credits'];
        
        try {
            $stmt = $pdo->prepare("UPDATE subjects SET subject_name = ?, subject_code = ?, description = ?, credits = ?, updated_at = NOW() WHERE id = ?");
            $stmt->execute([$subject_name, $subject_code, $description, $credits, $subject_id]);
            $message = "Subject updated successfully!";
        } catch (PDOException $e) {
            $message = "Error: " . $e->getMessage();
        }
    }
}

// Handle delete
if ($action === 'delete' && $subject_id) {
    try {
        $stmt = $pdo->prepare("DELETE FROM subjects WHERE id = ?");
        $stmt->execute([$subject_id]);
        $message = "Subject deleted successfully!";
    } catch (PDOException $e) {
        $message = "Error: " . $e->getMessage();
    }
}

// Get subject for editing
$editing_subject = null;
if ($action === 'edit' && $subject_id) {
    $stmt = $pdo->prepare("SELECT * FROM subjects WHERE id = ?");
    $stmt->execute([$subject_id]);
    $editing_subject = $stmt->fetch();
}

// Fetch all subjects
$subjects = QueryDB("SELECT * FROM subjects ORDER BY subject_name")->fetchAll();

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <title>Manage Subjects</title>
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
                        <h2 class="text-dark pb-2 fw-bold">Manage Subjects</h2>
                    </div>

                    <?php if ($message): ?>
                        <div class="alert alert-info"><?php echo $message; ?></div>
                    <?php endif; ?>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-header">
                                    <div class="card-title">
                                        <?php echo $editing_subject ? 'Edit Subject' : 'Add New Subject'; ?>
                                    </div>
                                </div>
                                <div class="card-body">
                                    <form method="POST" action="manage.php?action=<?php echo $editing_subject ? 'edit&id=' . $editing_subject['id'] : 'add'; ?>">
                                        <div class="form-group">
                                            <label for="subject_name">Subject Name</label>
                                            <input type="text" class="form-control" name="subject_name" 
                                                   value="<?php echo $editing_subject ? htmlspecialchars($editing_subject['subject_name']) : ''; ?>" 
                                                   required>
                                        </div>
                                        
                                        <div class="form-group">
                                            <label for="subject_code">Subject Code</label>
                                            <input type="text" class="form-control" name="subject_code" 
                                                   value="<?php echo $editing_subject ? htmlspecialchars($editing_subject['subject_code']) : ''; ?>" 
                                                   required>
                                        </div>
                                        
                                        <div class="form-group">
                                            <label for="description">Description</label>
                                            <textarea class="form-control" name="description" rows="3"><?php echo $editing_subject ? htmlspecialchars($editing_subject['description']) : ''; ?></textarea>
                                        </div>
                                        
                                        <div class="form-group">
                                            <label for="credits">Credits</label>
                                            <input type="number" class="form-control" name="credits" min="1" max="10"
                                                   value="<?php echo $editing_subject ? $editing_subject['credits'] : '1'; ?>" 
                                                   required>
                                        </div>
                                        
                                        <button type="submit" class="btn btn-primary">
                                            <?php echo $editing_subject ? 'Update Subject' : 'Add Subject'; ?>
                                        </button>
                                        
                                        <?php if ($editing_subject): ?>
                                            <a href="manage.php" class="btn btn-secondary">Cancel</a>
                                        <?php endif; ?>
                                    </form>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-12">
                            <div class="card">
                                <div class="card-header">
                                    <div class="card-title">All Subjects</div>
                                </div>
                                <div class="card-body">
                                    <div class="table-responsive">
                                        <table class="table table-bordered">
                                            <thead>
                                                <tr>
                                                    <th>Subject Code</th>
                                                    <th>Subject Name</th>
                                                    <th>Description</th>
                                                    <th>Credits</th>
                                                    <th>Created Date</th>
                                                    <th>Actions</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php if (empty($subjects)): ?>
                                                    <tr>
                                                        <td colspan="6" class="text-center">No subjects found</td>
                                                    </tr>
                                                <?php else: ?>
                                                    <?php foreach ($subjects as $subject): ?>
                                                        <tr>
                                                            <td><?php echo htmlspecialchars($subject['subject_code']); ?></td>
                                                            <td><?php echo htmlspecialchars($subject['subject_name']); ?></td>
                                                            <td><?php echo htmlspecialchars($subject['description']); ?></td>
                                                            <td><?php echo $subject['credits']; ?></td>
                                                            <td><?php echo date('M d, Y', strtotime($subject['created_at'])); ?></td>
                                                            <td>
                                                                <a href="manage.php?action=edit&id=<?php echo $subject['id']; ?>" 
                                                                   class="btn btn-warning btn-sm">Edit</a>
                                                                <a href="manage.php?action=delete&id=<?php echo $subject['id']; ?>" 
                                                                   class="btn btn-danger btn-sm"
                                                                   onclick="return confirm('Are you sure you want to delete this subject?')">Delete</a>
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
