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

// Get teacher ID
$teacher_id = $_GET['id'] ?? null;
if (!$teacher_id || !is_numeric($teacher_id)) {
    header('location:list.php');
    exit;
}

// Fetch teacher data
$sql = "SELECT t.*, u.username, u.status as user_status 
        FROM teachers t 
        JOIN users u ON t.user_id = u.id 
        WHERE t.id = ?";
$stmt = $pdo->prepare($sql);
$stmt->execute([$teacher_id]);
$teacher = $stmt->fetch();

if (!$teacher) {
    header('location:list.php');
    exit;
}

// Handle subject assignment/removal
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    if ($action === 'assign' && isset($_POST['subject_id'])) {
        $subject_id = (int)$_POST['subject_id'];
        
        try {
            $stmt = $pdo->prepare('UPDATE subjects SET teacher_id = ? WHERE id = ?');
            $stmt->execute([$teacher_id, $subject_id]);
            $success = 'Subject assigned successfully';
        } catch (Exception $e) {
            $error = 'Failed to assign subject';
        }
    } elseif ($action === 'remove' && isset($_POST['subject_id'])) {
        $subject_id = (int)$_POST['subject_id'];
        
        try {
            $stmt = $pdo->prepare('UPDATE subjects SET teacher_id = NULL WHERE id = ? AND teacher_id = ?');
            $stmt->execute([$subject_id, $teacher_id]);
            $success = 'Subject removed successfully';
        } catch (Exception $e) {
            $error = 'Failed to remove subject';
        }
    } elseif ($action === 'update_specializations' && isset($_POST['specializations'])) {
        $specializations = validate($_POST['specializations']);
        
        try {
            // Convert to JSON for storage
            $specializations_json = json_encode(array_filter(array_map('trim', explode(',', $specializations))));
            $stmt = $pdo->prepare('UPDATE teachers SET subjects = ? WHERE id = ?');
            $stmt->execute([$specializations_json, $teacher_id]);
            $success = 'Specializations updated successfully';
            
            // Refresh teacher data
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$teacher_id]);
            $teacher = $stmt->fetch();
        } catch (Exception $e) {
            $error = 'Failed to update specializations';
        }
    }
}

// Fetch all available subjects (not assigned to this teacher)
$available_subjects_sql = "SELECT s.*, c.class_name, c.section 
                          FROM subjects s 
                          LEFT JOIN classes c ON s.class_id = c.id 
                          WHERE s.teacher_id IS NULL OR s.teacher_id != ?
                          ORDER BY c.class_name, s.subject_name";
$available_subjects = QueryDB($available_subjects_sql, [$teacher_id])->fetchAll();

// Fetch assigned subjects
$assigned_subjects_sql = "SELECT s.*, c.class_name, c.section 
                         FROM subjects s 
                         LEFT JOIN classes c ON s.class_id = c.id 
                         WHERE s.teacher_id = ? 
                         ORDER BY c.class_name, s.subject_name";
$assigned_subjects = QueryDB($assigned_subjects_sql, [$teacher_id])->fetchAll();

// Parse existing specializations
$specializations = [];
if (!empty($teacher['subjects'])) {
    $decoded = json_decode($teacher['subjects'], true);
    if ($decoded) {
        $specializations = $decoded;
    } else {
        $specializations = array_map('trim', explode(',', $teacher['subjects']));
    }
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <title>Assign Subjects to Teacher</title>
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
                        <h2 class="text-dark pb-2 fw-bold">Assign Subjects</h2>
                        <div class="ml-md-auto py-2 py-md-0">
                            <a href="view.php?id=<?php echo $teacher_id; ?>" class="btn btn-info">View Profile</a>
                            <a href="list.php" class="btn btn-secondary">Back to List</a>
                        </div>
                    </div>

                    <?php if (isset($error)): ?>
                        <div class="alert alert-danger">
                            <?php echo $error; ?>
                        </div>
                    <?php endif; ?>
                    <?php if (isset($success)): ?>
                        <div class="alert alert-success">
                            <?php echo $success; ?>
                        </div>
                    <?php endif; ?>

                    <!-- Teacher Info Header -->
                    <div class="row">
                        <div class="col-md-12">
                            <div class="card">
                                <div class="card-body">
                                    <div class="d-flex align-items-center">
                                        <div class="avatar avatar-lg">
                                            <span class="avatar-title bg-primary text-white rounded-circle">
                                                <?php echo strtoupper(substr($teacher['first_name'], 0, 1) . substr($teacher['last_name'], 0, 1)); ?>
                                            </span>
                                        </div>
                                        <div class="ml-3">
                                            <h4 class="mb-1"><?php echo htmlspecialchars($teacher['first_name'] . ' ' . $teacher['last_name']); ?></h4>
                                            <p class="text-muted mb-0">Employee ID: <?php echo htmlspecialchars($teacher['employee_id']); ?></p>
                                        </div>
                                        <div class="ml-auto">
                                            <span class="badge badge-success"><?php echo count($assigned_subjects); ?> Subjects Assigned</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <!-- Available Subjects -->
                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-header">
                                    <div class="card-title">Available Subjects</div>
                                    <div class="card-category">Click "Assign" to assign a subject to this teacher</div>
                                </div>
                                <div class="card-body">
                                    <?php if (empty($available_subjects)): ?>
                                        <div class="alert alert-info">
                                            <i class="fa fa-info-circle"></i>
                                            All subjects have been assigned. No available subjects to assign.
                                        </div>
                                    <?php else: ?>
                                        <div class="table-responsive">
                                            <table class="table table-bordered table-sm">
                                                <thead class="thead-light">
                                                    <tr>
                                                        <th>Subject</th>
                                                        <th>Code</th>
                                                        <th>Class</th>
                                                        <th>Current Teacher</th>
                                                        <th>Action</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <?php foreach ($available_subjects as $subject): ?>
                                                        <tr>
                                                            <td>
                                                                <strong><?php echo htmlspecialchars($subject['subject_name']); ?></strong>
                                                            </td>
                                                            <td><?php echo htmlspecialchars($subject['subject_code']); ?></td>
                                                            <td><?php echo htmlspecialchars($subject['class_name'] . ' ' . $subject['section']); ?></td>
                                                            <td>
                                                                <?php if ($subject['teacher_id']): ?>
                                                                    <span class="badge badge-warning">Assigned</span>
                                                                <?php else: ?>
                                                                    <span class="badge badge-light">Unassigned</span>
                                                                <?php endif; ?>
                                                            </td>
                                                            <td>
                                                                <form method="POST" style="display: inline;">
                                                                    <input type="hidden" name="action" value="assign">
                                                                    <input type="hidden" name="subject_id" value="<?php echo $subject['id']; ?>">
                                                                    <button type="submit" class="btn btn-success btn-sm" 
                                                                            onclick="return confirm('Assign this subject to <?php echo htmlspecialchars($teacher['first_name'] . ' ' . $teacher['last_name']); ?>?')">
                                                                        <i class="fa fa-plus"></i> Assign
                                                                    </button>
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

                        <!-- Assigned Subjects -->
                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-header">
                                    <div class="card-title">Assigned Subjects</div>
                                    <div class="card-category">Subjects currently taught by this teacher</div>
                                </div>
                                <div class="card-body">
                                    <?php if (empty($assigned_subjects)): ?>
                                        <div class="alert alert-warning">
                                            <i class="fa fa-exclamation-triangle"></i>
                                            No subjects assigned to this teacher yet.
                                        </div>
                                    <?php else: ?>
                                        <div class="table-responsive">
                                            <table class="table table-bordered table-sm">
                                                <thead class="thead-light">
                                                    <tr>
                                                        <th>Subject</th>
                                                        <th>Code</th>
                                                        <th>Class</th>
                                                        <th>Action</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <?php foreach ($assigned_subjects as $subject): ?>
                                                        <tr>
                                                            <td>
                                                                <strong><?php echo htmlspecialchars($subject['subject_name']); ?></strong>
                                                            </td>
                                                            <td><?php echo htmlspecialchars($subject['subject_code']); ?></td>
                                                            <td><?php echo htmlspecialchars($subject['class_name'] . ' ' . $subject['section']); ?></td>
                                                            <td>
                                                                <form method="POST" style="display: inline;">
                                                                    <input type="hidden" name="action" value="remove">
                                                                    <input type="hidden" name="subject_id" value="<?php echo $subject['id']; ?>">
                                                                    <button type="submit" class="btn btn-danger btn-sm" 
                                                                            onclick="return confirm('Remove this subject assignment?')">
                                                                        <i class="fa fa-minus"></i> Remove
                                                                    </button>
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

                    <!-- Teacher Specializations -->
                    <div class="row">
                        <div class="col-md-12">
                            <div class="card">
                                <div class="card-header">
                                    <div class="card-title">Subject Specializations</div>
                                    <div class="card-category">Additional subjects or areas of expertise (comma-separated)</div>
                                </div>
                                <div class="card-body">
                                    <form method="POST">
                                        <input type="hidden" name="action" value="update_specializations">
                                        <div class="form-group">
                                            <label for="specializations">Specializations</label>
                                            <input type="text" class="form-control" name="specializations" id="specializations" 
                                                   value="<?php echo htmlspecialchars(implode(', ', $specializations)); ?>"
                                                   placeholder="e.g., Mathematics, Physics, Chemistry, Computer Science">
                                            <small class="form-text text-muted">
                                                Enter subjects or areas of expertise separated by commas. These are for reference and don't affect class assignments.
                                            </small>
                                        </div>
                                        <button type="submit" class="btn btn-primary">Update Specializations</button>
                                    </form>

                                    <?php if (!empty($specializations)): ?>
                                    <div class="mt-3">
                                        <h6>Current Specializations:</h6>
                                        <div class="row">
                                            <?php foreach ($specializations as $spec): ?>
                                                <div class="col-md-3 mb-2">
                                                    <span class="badge badge-primary"><?php echo htmlspecialchars($spec); ?></span>
                                                </div>
                                            <?php endforeach; ?>
                                        </div>
                                    </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Auto-refresh success/error messages
        setTimeout(function() {
            $('.alert').fadeOut('slow');
        }, 3000);
    </script>
</body>
</html>
