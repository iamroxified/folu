<?php
// Start session

// Include database configuration and functions
require_once base_path('db/config.php');
require_once base_path('db/functions.php');

// Check if user is logged in
if (!isset($_SESSION['adid'])) {
    header('Location: /admin/login.php');
    exit;
}

// Get fee type ID
$fee_type_id = $_GET['id'] ?? null;
if (!$fee_type_id) {
    die('Fee Type ID is required');
}

// Fetch fee type details
$stmt = $pdo->prepare("SELECT * FROM fee_type WHERE id = ?");
$stmt->execute([$fee_type_id]);
$fee_type = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$fee_type) {
    die('Fee Type not found');
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $fee_name = trim($_POST['fee_name']);
    $description = trim($_POST['description']);
    $status = trim($_POST['status']);

    if (empty($fee_name) || empty($status)) {
        $error = "Fee name and status are required.";
    } else {
        try {
            $update_stmt = $pdo->prepare("UPDATE fee_type SET fee_name = ?, description = ?, status = ? WHERE id = ?");
            $update_stmt->execute([$fee_name, $description, $status, $fee_type_id]);
            $success = "Fee type updated successfully!";

            // Refresh data
            $stmt->execute([$fee_type_id]);
            $fee_type = $stmt->fetch(PDO::FETCH_ASSOC);

        } catch (PDOException $e) {
            $error = "Database error: " . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta http-equiv="X-UA-Compatible" content="IE=edge" />
  <title>Edit Fee Type</title>
  @include('admin.partials.links')
</head>

<body>
  <div class="wrapper">
    @include('admin.partials.sidebar')

    <div class="main-panel">
      @include('admin.partials.header')
      <div class="container">
        <div class="page-inner">
          <div class="d-flex align-items-left align-items-md-center flex-column flex-md-row">
            <h2 class="text-dark pb-2 fw-bold">Edit Fee Type</h2>
            <div class="ml-md-auto py-2 py-md-0">
                <a href="fee_type.php" class="btn btn-secondary btn-round">Back to Fee Types</a>
            </div>
          </div>
          <div class="row">
            <div class="col-md-12">
              <div class="card">
                <div class="card-header">
                  <div class="card-title">Edit Fee Type Details</div>
                </div>
                <div class="card-body">
                  <?php if (isset($error)): ?>
                  <div class="alert alert-danger"><?php echo $error; ?></div>
                  <?php endif; ?>
                  <?php if (isset($success)): ?>
                  <div class="alert alert-success"><?php echo $success; ?></div>
                  <?php endif; ?>
                  <form method="POST" action="">
                    <div class="form-group">
                      <label for="fee_name">Fee Name</label>
                      <input type="text" class="form-control" id="fee_name" name="fee_name" value="<?php echo htmlspecialchars($fee_type['fee_name']); ?>" required>
                    </div>
                    <div class="form-group">
                      <label for="description">Description</label>
                      <textarea class="form-control" id="description" name="description" rows="3"><?php echo htmlspecialchars($fee_type['description']); ?></textarea>
                    </div>
                    <div class="form-group">
                      <label for="status">Status</label>
                      <select class="form-control" id="status" name="status" required>
                        <option value="active" <?php echo ($fee_type['status'] == 'active') ? 'selected' : ''; ?>>Active</option>
                        <option value="inactive" <?php echo ($fee_type['status'] == 'inactive') ? 'selected' : ''; ?>>Inactive</option>
                      </select>
                    </div>
                    <div class="card-action">
                      <button type="submit" class="btn btn-success">Update Fee Type</button>
                    </div>
                  </form>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
      @include('admin.partials.footer')
    </div>
  </div>
</body>

</html>




