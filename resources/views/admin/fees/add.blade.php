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

// Add Fee Type
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Sanitize and validate inputs
    $fee_name = validate($_POST['fee_name'] ?? '');
    $fee_class = validate($_POST['fee_class'] ?? '');
    $fee_session = validate($_POST['fee_session'] ?? '');
    $fee_amount = filter_input(INPUT_POST, 'fee_amount', FILTER_VALIDATE_FLOAT);
    $fee_description = validate($_POST['fee_description'] ?? '');
    $status = validate($_POST['status'] ?? 'active');

    // Basic validation
    if (empty($fee_class) || empty($fee_session)|| empty($fee_amount)) {
        $error = 'Please fill all required fields.';
    } else {
        try {

            // Insert into database
             $stmt = $pdo->prepare(
                'INSERT INTO fees (fee_name, fee_class, fee_session, fee_amount, fee_description)
                 VALUES (?, ?, ?, ?, ?)'
            );
            $stmt->execute([
                $fee_name, $fee_class, $fee_session, $fee_amount, $fee_description
            ]);

            $success = 'Fee type added successfully';

        } catch (Exception $e) {
            $error = 'Failed to add fee type: ' . $e->getMessage();
        }
    }
}

// Fetch classes, sessions and fee type data for dropdowns
$classes = QueryDB("SELECT id, class_name, class_arm FROM classes ORDER BY class_name")->fetchAll();
$sessions = QueryDB("SELECT id, session_name FROM academic_sessions ORDER BY session_name DESC")->fetchAll();

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <title>Add New Fee Type</title>
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
                                                 <select class="form-control" name="fee_name" id="fee_name" required>
                                                  <option value="">Select Fee Type</option>
                                                  <?php
                                                  $fee_types = QueryDB("SELECT id, fee_name FROM fee_type ORDER BY fee_name")->fetchAll();
                                                  foreach ($fee_types as $fee_type): ?>
                                                      <option value="<?php echo $fee_type['id']; ?>">
                                                          <?php echo htmlspecialchars($fee_type['fee_name']); ?>
                                                      </option>
                                                  <?php endforeach; ?>
                                              </select>

                                        </div>
                                        <div class="form-group">
                                            <label for="fee_class">Class</label>
                                            <select class="form-control" name="fee_class" id="fee_class">
                                                <option value="">Select Class</option>
                                                <?php foreach ($classes as $class): ?>
                                                    <option value="<?php echo $class['id']; ?>">
                                                        <?php echo htmlspecialchars($class['class_name'] . ' ' . $class['class_arm']); ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                        <div class="form-group">
                                              <label for="fee_session">Session</label>
                                              <select class="form-control" name="fee_session" id="fee_session">
                                                  <option value="">Select Session</option>
                                                  <?php foreach ($sessions as $session): ?>
                                                      <option value="<?php echo $session['id']; ?>">
                                                          <?php echo htmlspecialchars($session['session_name']); ?>
                                                      </option>
                                                  <?php endforeach; ?>
                                              </select>
                                          </div>


                                        <div class="form-group">
                                            <label for="fee_amount">Fee Amount <span class="text-danger">*</span></label>
                                            <input type="number" class="form-control" name="fee_amount" id="fee_amount" required>
                                        </div>
                                        <div class="form-group">
                                            <label for="fee_description">Fee Description</label>
                                            <textarea class="form-control" name="fee_description" id="fee_description" rows="3"></textarea>
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
    @include('admin.partials.footer')
</body>

</html>



