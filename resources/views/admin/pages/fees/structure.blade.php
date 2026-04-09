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

$message = '';
$error = '';

// Fetch student information
$studentId = $_GET['student_id'] ?? null;
if (!$studentId) {
    die('Student ID is required');
}

try {
    $student_stmt = $pdo->prepare("SELECT s.*, c.class_name, c.class_arm, ac.session_name, ac.session_term FROM students s LEFT JOIN classes c ON s.class_link = c.id LEFT JOIN academic_sessions ac ON s.academic_session_link = ac.id WHERE s.id = ?");
    $student_stmt->execute([$studentId]);
    $student = $student_stmt->fetch(PDO::FETCH_ASSOC);

    if (!$student) {
        die('Student not found');
    }

    // Handle fee de-allocation
    if (isset($_GET['deallocate_fee'])) {
        $fee_structure_to_deallocate = $_GET['deallocate_fee'];

        // First, check if any payment has been made for this fee
        $payment_check_stmt = $pdo->prepare("SELECT amount_paid FROM student_fees WHERE student_link = ? AND fee_structure_link = ? AND academic_session_link = ?");
        $payment_check_stmt->execute([$studentId, $fee_structure_to_deallocate, $student['academic_session_link']]);
        $fee_record = $payment_check_stmt->fetch(PDO::FETCH_ASSOC);

        if ($fee_record && ($fee_record['amount_paid'] > 0)) {
            $error = "Cannot de-allocate fee. A payment has already been recorded for it.";
        } else {
            // No payment made, safe to de-allocate
            $delete_stmt = $pdo->prepare("DELETE FROM student_fees WHERE student_link = ? AND fee_structure_link = ? AND academic_session_link = ?");
            $deleted = $delete_stmt->execute([$studentId, $fee_structure_to_deallocate, $student['academic_session_link']]);

            if ($deleted) {
                // Redirect to clean the URL and show success
                $_SESSION['form_message'] = "Fee successfully de-allocated.";
                header('Location: fee_structure.php?student_id=' . $studentId);
                exit;
            } else {
                $error = "Failed to de-allocate the fee.";
            }
        }
    }

    // Handle fee allocation
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['allocate_fees'])) {
        $selected_fees = $_POST['fee_ids'] ?? [];
        if (empty($selected_fees)) {
            $error = "Please select at least one fee to allocate.";
        } else {
            $allocated_count = 0;
            $pdo->beginTransaction();
            foreach ($selected_fees as $fee_structure_id) {
                // Check if this fee is already allocated to the student for the session
                $check_stmt = $pdo->prepare("SELECT COUNT(*) FROM student_fees WHERE student_link = ? AND fee_structure_link = ? AND academic_session_link = ?");
                $check_stmt->execute([$studentId, $fee_structure_id, $student['academic_session_link']]);
                if ($check_stmt->fetchColumn() == 0) {
                    // Fetch fee details
                    $fee_struct_stmt = $pdo->prepare("SELECT * FROM fees WHERE id = ?");
                    $fee_struct_stmt->execute([$fee_structure_id]);
                    $fee_structure = $fee_struct_stmt->fetch(PDO::FETCH_ASSOC);

                    if ($fee_structure) {
                        // Allocate the fee
                        $insert_stmt = $pdo->prepare("INSERT INTO student_fees (student_link, fee_structure_link, amount_due, amount_paid, balance, status, due_date, academic_session_link) VALUES (?, ?, ?, 0, ?, 'pending', DATE_ADD(CURDATE(), INTERVAL 30 DAY), ?)");
                        $insert_stmt->execute([
                            $studentId,
                            $fee_structure_id,
                            $fee_structure['fee_amount'],
                            $fee_structure['fee_amount'],
                            $student['academic_session_link']
                        ]);
                        $allocated_count++;
                    }
                }
            }
            $pdo->commit();
            if ($allocated_count > 0) {
                $message = "Successfully allocated $allocated_count new fee(s) to the student.";
            } else {
                $error = "No new fees were allocated. They may have already been assigned.";
            }
        }
    }

    // Check for session-based messages after redirects
    if (isset($_SESSION['form_message'])) {
        $message = $_SESSION['form_message'];
        unset($_SESSION['form_message']);
    }

    // Fetch all applicable fees and their allocation status for the student
    $fee_structures_stmt = $pdo->prepare("
        SELECT 
            f.*,
            ft.fee_name as type_name,
            sf.id as student_fee_id,
            sf.amount_paid,
            sf.amount_due,
            sf.balance,
            sf.status as payment_status,
            sf.due_date,
            CASE WHEN sf.id IS NOT NULL THEN 1 ELSE 0 END as is_allocated
        FROM fees f
        LEFT JOIN fee_type ft ON f.fee_name = ft.id 
        LEFT JOIN student_fees sf ON f.id = sf.fee_structure_link 
            AND sf.student_link = ? 
            AND sf.academic_session_link = ?
        WHERE (f.fee_class = ? OR f.fee_class IS NULL)
        AND (f.fee_session = ? OR f.fee_session IS NULL)
        ORDER BY ft.fee_name
    ");
    $fee_structures_stmt->execute([$studentId, $student['academic_session_link'], $student['class_link'], $student['academic_session_link']]);
    $fee_structures = $fee_structures_stmt->fetchAll(PDO::FETCH_ASSOC);

    // Create array of allocated fee IDs for easy checking
    $allocated_fee_ids = array_filter(array_column($fee_structures, 'id', 'id'));

} catch (PDOException $e) {
    die('Database error: ' . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta http-equiv="X-UA-Compatible" content="IE=edge" />
  <title>Fee Structure Allocation</title>
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
            <div>
              <h2 class="text-dark pb-2 fw-bold">Fee Allocation</h2>
              <h5 class="text-muted">For:
                <?php echo htmlspecialchars($student['first_name'] . ' ' . $student['last_name']); ?>
                (<?php echo htmlspecialchars($student['class_name'] . ' ' . $student['class_arm']); ?>)</h5>
              <h6 class="text-muted">Session:
                <?php echo htmlspecialchars($student['session_name'] . ' - ' . $student['session_term'] . ' Term'); ?>
              </h6>
            </div>
            <div class="ms-md-auto py-2 py-md-0">
              <a href="view_students.php?id=<?php echo $studentId; ?>" class="btn btn-secondary btn-round">Back to
                Profile</a>
            </div>
          </div>

          <!-- Alert Messages -->
          <?php if ($message): ?>
          <div class="alert alert-success"><?php echo $message; ?></div>
          <?php endif; ?>
          <?php if ($error): ?>
          <div class="alert alert-danger"><?php echo $error; ?></div>
          <?php endif; ?>

          <div class="row">
            <div class="col-md-12">
              <div class="card">
                <div class="card-header">
                  <div class="card-title">Applicable Fees</div>
                </div>
                <div class="card-body">
                  <form method="POST" action="">
                    <div class="table-responsive">
                      <table class="table table-hover">
                        <thead>
                          <tr>
                            <th>
                              <input type="checkbox" id="select_all_fees">
                            </th>
                            <th>Fee Name</th>
                            <th>Amount (₦)</th>
                            <th>Description</th>
                            <th>Status</th>
                            <th>Action</th>
                          </tr>
                        </thead>
                        <tbody>
                          <?php if (empty($fee_structures)): ?>
                          <tr>
                            <td colspan="5" class="text-center text-muted">No applicable fee structures found for this
                              student's class and session.</td>
                          </tr>
                          <?php else: ?>
                          <?php foreach ($fee_structures as $fee): ?>
                          <tr>
                            <td>
                              <input type="checkbox" name="fee_ids[]" value="<?php echo $fee['id']; ?>"
                                class="fee-checkbox"
                                <?php echo $fee['is_allocated'] ? 'disabled' : ''; ?>>
                            </td>
                            <td><?php echo htmlspecialchars($fee['type_name']); ?></td>
                            <td><?php echo number_format($fee['fee_amount'], 2); ?></td>
                            <td><?php echo htmlspecialchars($fee['fee_description']); ?></td>
                            <td>
                              <?php if ($fee['is_allocated']): ?>
                              <span class="badge bg-success">Allocated</span>
                              <?php else: ?>
                              <span class="badge bg-warning">Not Allocated</span>
                              <?php endif; ?>
                            </td>
                            <td>
                              <?php if ($fee['is_allocated']): ?>
                              <a href="fee_structure.php?student_id=<?php echo $studentId; ?>&deallocate_fee=<?php echo $fee['id']; ?>"
                                class="btn btn-danger btn-sm"
                                onclick="return confirm('Are you sure you want to de-allocate this fee? This cannot be undone if a payment has not been made.')">
                                <i class="fas fa-times-circle me-1"></i> De-allocate
                              </a>
                              <?php endif; ?>
                            </td>
                          </tr>
                          <?php endforeach; ?>
                          <?php endif; ?>
                        </tbody>
                      </table>
                    </div>
                    <div class="card-action">
                      <button type="submit" name="allocate_fees" class="btn btn-primary">
                        <i class="fas fa-check-circle me-2"></i>Allocate Selected Fees
                      </button>
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
  <script>
    document.addEventListener('DOMContentLoaded', function () {
      const selectAll = document.getElementById('select_all_fees');
      const checkboxes = document.querySelectorAll('.fee-checkbox:not(:disabled)');

      if (selectAll) {
        selectAll.addEventListener('change', function () {
          checkboxes.forEach(checkbox => {
            checkbox.checked = this.checked;
          });
        });
      }
    });
  </script>
</body>

</html>





























































































































































































































































































































































































































-



