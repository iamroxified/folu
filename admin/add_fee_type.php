<?php
// Start session
session_start();

// Include database configuration and functions
require '../db/config.php';
require '../db/functions.php';

// Check if user is logged in
if (!isset($_SESSION['adid'])) {
    header('location:../login.php');
    exit;
}

// Add Fee Type
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Sanitize and validate inputs
    $fee_name = validate($_POST['fee_name'] ?? '');
    $description = validate($_POST['description'] ?? '');
    $status = validate($_POST['status'] ?? 'active');

    // Basic validation
    if (empty($fee_name)) {
        $error = 'Please fill all required fields.';
    } else {
        try {
            // Insert into database
            $stmt = $pdo->prepare(
                'INSERT INTO fee_type (fee_name, description, status) 
                 VALUES (?, ?, ?)'
            );
            $stmt->execute([
                $fee_name, $description, $status
            ]);

            $success = 'Fee type added successfully';

        } catch (Exception $e) {
            $error = 'Failed to add fee type: ' . $e->getMessage();
        }
    }
}

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <title>Add New Fee Type</title>
    <?php include('nav/links.php'); ?>
</head>

<body>
    <div class="wrapper">
        <?php include('nav/sidebar.php'); ?>

        <div class="main-panel">
            <?php include('nav/header.php'); ?>
            <div class="container">
                <div class="page-inner">
                    <div class="d-flex align-items-left flex-column flex-md-row">
                        <h2 class="text-dark pb-2 fw-bold">Add New Fee Type</h2>
                    </div>

                    <div class="row">
                        <div class="col-md-12">
                            <div class="card">
                                <div class="card-header">
                                    <div class="card-title">Fee Type Details</div>
                                </div>
                                <div class="card-body">
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
                                    <form method="POST" action="">
                                        <div class="form-group">
                                            <label for="fee_name">Fee Name <span class="text-danger">*</span></label>
                                            <input type="text" class="form-control" name="fee_name" id="fee_name" required>
                                        </div>
                                        <div class="form-group">
                                            <label for="description">Description</label>
                                            <textarea class="form-control" name="description" id="description" rows="3"></textarea>
                                        </div>
                                        <div class="form-group">
                                            <label for="status">Status</label>
                                            <select class="form-control" name="status" id="status">
                                                <option value="active">Active</option>
                                                <option value="inactive">Inactive</option>
                                            </select>
                                        </div>
                                        <button type="submit" class="btn btn-primary">Add Fee Type</button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php include('nav/footer.php'); ?>
</body>

</html>