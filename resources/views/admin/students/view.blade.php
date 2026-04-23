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
$payment_success = false;

// Fetch student information
$studentId = $_GET['id'] ?? null;
if ($studentId) {
    try {
        $student_stmt = $pdo->prepare("SELECT s.*, c.class_name, c.class_level, ac.session_name FROM students s LEFT JOIN classes c ON s.class_link = c.id LEFT JOIN academic_sessions ac ON s.academic_session_link = ac.id WHERE s.id = ?");
        $student_stmt->execute([$studentId]);
        $student = $student_stmt->fetch(PDO::FETCH_ASSOC);

        if (!$student) {
            die('Student not found');
        }
    } catch (PDOException $e) {
        die('Database error: ' . $e->getMessage());
    }
} else {
    die('Student ID is required');
}

// Handle payment form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['process_payment'])) {
    // Debug: Log all POST data
    error_log('Payment POST data: ' . print_r($_POST, true));
    
    $fee_id = $_POST['fee_id'] ?? null; // Direct fee ID from modal
    $selected_session = $_POST['academic_session'] ?? null;
    $selected_class = $_POST['class'] ?? null;
    $selected_term = $_POST['term'] ?? null;
    $payment_amount = floatval($_POST['payment_amount'] ?? 0);
    $payment_method = $_POST['payment_method'] ?? null;
    $payment_description = $_POST['payment_description'] ?? '';

    // Validation
    if ($payment_amount <= 0 || !$payment_method) {
        $error = 'Please enter a valid payment amount and select a payment method.';
    } else {
        try {
            $pdo->beginTransaction();
            $receipt_number = 'RCP' . date('Y') . str_pad(rand(1, 9999), 4, '0', STR_PAD_LEFT);

            if ($fee_id) {
                // Direct fee payment from modal - update existing fee record
                $existing_fee_stmt = $pdo->prepare("SELECT * FROM student_fees WHERE id = ? AND student_link = ?");
                $existing_fee_stmt->execute([$fee_id, $studentId]);
                $existing_fee = $existing_fee_stmt->fetch(PDO::FETCH_ASSOC);

                if (!$existing_fee) {
                    throw new Exception('Fee record not found or does not belong to this student.');
                }

                // Validate payment amount doesn't exceed balance
                if ($payment_amount > $existing_fee['balance']) {
                    throw new Exception('Payment amount cannot exceed the outstanding balance of ₦' . number_format($existing_fee['balance'], 2));
                }

                // Update existing fee record
                $new_amount_paid = $existing_fee['amount_paid'] + $payment_amount;
                $new_balance = max(0, $existing_fee['amount_due'] - $new_amount_paid);

                $status = 'partial';
                if ($new_balance == 0) {
                    $status = 'paid';
                } elseif ($new_amount_paid == 0) {
                    $status = 'pending';
                }

                // Update student_fees table
                $update_fee_stmt = $pdo->prepare("UPDATE student_fees SET amount_paid = ?, balance = ?, status = ?, payment_date = CURDATE(), payment_method = ?, receipt_number = ?, updated_at = NOW() WHERE id = ?");

                $update_fee_stmt->execute([
                    $new_amount_paid,
                    $new_balance,
                    $status,
                    $payment_method,
                    $receipt_number,
                    $existing_fee['id']
                ]);

                // Record the payment in student_payments table
                $payment_insert_stmt = $pdo->prepare("INSERT INTO student_payments (
                    student_link,
                    fee_structure_link,
                    amount_due,
                    amount_paid,
                    balance,
                    status,
                    due_date,
                    payment_date,
                    payment_method,
                    receipt_number,
                    academic_session_link,
                    description
                ) VALUES (?, ?, ?, ?, ?, ?, DATE_ADD(CURDATE(), INTERVAL 30 DAY), CURDATE(), ?, ?, ?, ?)");

                $payment_insert_stmt->execute([
                    $studentId,
                    $existing_fee['fee_structure_link'],
                    $existing_fee['amount_due'],
                    $payment_amount,
                    $new_balance,
                    $status,
                    $payment_method,
                    $receipt_number,
                    $student['academic_session_link'],
                    $payment_description ?: 'Fee Payment - Receipt #' . $receipt_number
                ]);

            } else {
                // Legacy payment processing for general payments
                if (!$selected_session || !$selected_class || !$selected_term) {
                    throw new Exception('Please fill in all required fields.');
                }

                // Check if there's an existing fee structure for this context
                $fee_structure_stmt = $pdo->prepare("SELECT * FROM fee_structures WHERE (class_level = (SELECT class_level FROM classes WHERE id = ?) OR class_level = 'ALL') AND (student_type = ? OR student_type = 'ALL') AND term = ? AND academic_session_link = ? ORDER BY class_level DESC, student_type DESC LIMIT 1");

                $class_info_stmt = $pdo->prepare("SELECT class_level FROM classes WHERE id = ?");
                $class_info_stmt->execute([$selected_class]);
                $class_info = $class_info_stmt->fetch(PDO::FETCH_ASSOC);

                $fee_structure_stmt->execute([
                    $selected_class,
                    $student['student_type'] ?? 'day',
                    $selected_term,
                    $selected_session
                ]);

                $fee_structure = $fee_structure_stmt->fetch(PDO::FETCH_ASSOC);

                if (!$fee_structure) {
                    // Create a generic fee structure if none exists
                    $fee_insert_stmt = $pdo->prepare("INSERT INTO fee_structures (fee_name, class_level, student_type, amount, term, academic_session_link, description) VALUES (?, ?, ?, ?, ?, ?, ?)");

                    $fee_name = "General School Fee - " . ($class_info['class_level'] ?? 'Unknown');
                    $fee_insert_stmt->execute([
                        $fee_name,
                        $class_info['class_level'] ?? 'ALL',
                        $student['student_type'] ?? 'day',
                        $payment_amount,
                        $selected_term,
                        $selected_session,
                        $payment_description ?: 'General payment'
                    ]);

                    $fee_structure_id = $pdo->lastInsertId();
                } else {
                    $fee_structure_id = $fee_structure['id'];
                }

                // Check if student already has a fee record for this structure
                $existing_fee_stmt = $pdo->prepare("SELECT * FROM student_fees WHERE student_link = ? AND fee_structure_link = ?");
                $existing_fee_stmt->execute([$studentId, $fee_structure_id]);
                $existing_fee = $existing_fee_stmt->fetch(PDO::FETCH_ASSOC);

                if ($existing_fee) {
                    // Update existing fee record
                    $new_amount_paid = $existing_fee['amount_paid'] + $payment_amount;
                    $new_balance = max(0, $existing_fee['amount_due'] - $new_amount_paid);

                    $status = 'partial';
                    if ($new_balance == 0) {
                        $status = 'paid';
                    } elseif ($new_amount_paid == 0) {
                        $status = 'pending';
                    }

                    $update_fee_stmt = $pdo->prepare("UPDATE student_fees SET amount_paid = ?, balance = ?, status = ?, payment_date = CURDATE(), payment_method = ?, receipt_number = ?, updated_at = NOW() WHERE id = ?");

                    $update_fee_stmt->execute([
                        $new_amount_paid,
                        $new_balance,
                        $status,
                        $payment_method,
                        $receipt_number,
                        $existing_fee['id']
                    ]);
                } else {
                    // Create new fee record
                    $amount_due = $fee_structure['amount'] ?? $payment_amount;
                    $balance = max(0, $amount_due - $payment_amount);

                    $status = 'partial';
                    if ($balance == 0) {
                        $status = 'paid';
                    } elseif ($payment_amount == 0) {
                        $status = 'pending';
                    }

                    $insert_fee_stmt = $pdo->prepare("INSERT INTO student_fees (student_link, fee_structure_link, amount_due, amount_paid, balance, status, due_date, payment_date, payment_method, receipt_number, academic_session_link) VALUES (?, ?, ?, ?, ?, ?, DATE_ADD(CURDATE(), INTERVAL 30 DAY), CURDATE(), ?, ?, ?)");

                    $insert_fee_stmt->execute([
                        $studentId,
                        $fee_structure_id,
                        $amount_due,
                        $payment_amount,
                        $balance,
                        $status,
                        $payment_method,
                        $receipt_number,
                        $selected_session
                    ]);
                }
            }

            $pdo->commit();
            $payment_success = true;
            $message = "Payment of ₦" . number_format($payment_amount, 2) . " processed successfully! Receipt Number: " . $receipt_number;

        } catch (Exception $e) {
            $pdo->rollBack();
            $error = 'Payment processing failed: ' . $e->getMessage();
        } catch (PDOException $e) {
            $pdo->rollBack();
            $error = 'Payment processing failed: ' . $e->getMessage();
        }
    }
}

// Get available academic sessions and classes
try {
    $sessions_stmt = $pdo->query("SELECT * FROM academic_sessions ORDER BY start_date DESC");
    $sessions = $sessions_stmt->fetchAll(PDO::FETCH_ASSOC);

    $classes_stmt = $pdo->query("SELECT * FROM classes ORDER BY class_level, class_arm");
    $classes = $classes_stmt->fetchAll(PDO::FETCH_ASSOC);

    // Get student fee summary
    $fee_summary_stmt = $pdo->prepare("SELECT COALESCE(SUM(sf.amount_due), 0) as total_due, COALESCE(SUM(sf.amount_paid), 0) as total_paid, COALESCE(SUM(sf.balance), 0) as total_balance FROM student_fees sf WHERE sf.student_link = ?");
    $fee_summary_stmt->execute([$studentId]);
    $fee_summary = $fee_summary_stmt->fetch(PDO::FETCH_ASSOC);

    // Get recent payment history
    $payment_history_stmt = $pdo->prepare("
        SELECT 
            sf.*,
            f.fee_description,
            ft.fee_name as type_name,
            ac.session_name,
            ac.session_term,
            CASE ac.session_term
                WHEN 1 THEN 'First Term'
                WHEN 2 THEN 'Second Term'
                WHEN 3 THEN 'Third Term'
                ELSE 'Full Session'
            END as term
        FROM student_fees sf 
        JOIN fees f ON sf.fee_structure_link = f.id 
        LEFT JOIN fee_type ft ON f.fee_name = ft.id
        LEFT JOIN academic_sessions ac ON sf.academic_session_link = ac.id 
        WHERE sf.student_link = ? 
        AND sf.amount_paid > 0 
        ORDER BY sf.payment_date DESC, sf.updated_at DESC 
        LIMIT 10
    ");
    $payment_history_stmt->execute([$studentId]);
    $payment_history = $payment_history_stmt->fetchAll(PDO::FETCH_ASSOC);

    // Get outstanding fees
    $outstanding_stmt = $pdo->prepare("
        SELECT 
            sf.*,
            f.fee_amount,
            f.fee_description,
            f.fee_session,
            ft.fee_name as type_name,
            ac.session_name,
            ac.session_term,
            CASE ac.session_term
                WHEN 1 THEN 'First Term'
                WHEN 2 THEN 'Second Term'
                WHEN 3 THEN 'Third Term'
                ELSE 'Full Session'
            END as term
        FROM student_fees sf 
        JOIN fees f ON sf.fee_structure_link = f.id 
        LEFT JOIN fee_type ft ON f.fee_name = ft.id
        LEFT JOIN academic_sessions ac ON sf.academic_session_link = ac.id 
        WHERE sf.student_link = ? 
        AND sf.balance > 0 
        AND (
            f.fee_class = (SELECT class_link FROM students WHERE id = ?)
            OR f.fee_class IS NULL
        )
        ORDER BY sf.due_date ASC, f.fee_session ASC
    ");
    $outstanding_stmt->execute([$studentId, $studentId]);
    $outstanding_fees = $outstanding_stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    $sessions = [];
    $classes = [];
    $fee_summary = ['total_due' => 0, 'total_paid' => 0, 'total_balance' => 0];
    $payment_history = [];
    $outstanding_fees = [];
}

// Get additional student information
$attendancePercentage = student_attendance_percentage($studentId);
$gradeAverage = student_grade_average($studentId);

?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta http-equiv="X-UA-Compatible" content="IE=edge" />
  <title>Student Profile - <?php echo htmlspecialchars($student['first_name'] . ' ' . $student['last_name']); ?></title>
  @include('admin.partials.links')
  <!-- Add SweetAlert2 -->
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
  <style>
    .student-header {
      background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
      color: white;
      border-radius: 15px;
      padding: 25px;
      margin-bottom: 25px;
    }

    .fee-summary-card {
      background: linear-gradient(135deg, #2ecc71 0%, #27ae60 100%);
      color: white;
      border: none;
      border-radius: 15px;
    }

    .outstanding-card {
      background: linear-gradient(135deg, #e74c3c 0%, #c0392b 100%);
      color: white;
      border: none;
      border-radius: 15px;
    }

    .payment-form {
      background: #f8f9fa;
      border-radius: 15px;
      padding: 20px;
      margin-top: 20px;
    }

    .action-btn {
      margin: 5px 0;
      border-radius: 8px;
      font-weight: 600;
    }

    .btn-make-payment {
      background: linear-gradient(45deg, #3498db, #2980b9);
      border: none;
      color: white;
    }

    .btn-make-payment:hover {
      background: linear-gradient(45deg, #2980b9, #3498db);
      color: white;
    }
  </style>
</head>

<body>
  <div class="wrapper">
    @include('admin.partials.sidebar')

    <div class="main-panel">
      @include('admin.partials.header')
      <div class="container">
        <div class="page-inner">
          <!-- Student Header -->
          <div class="student-header">
            <div class="row align-items-center">
              <div class="col-md-8">
                <h2 class="mb-1"><?php echo htmlspecialchars($student['first_name'] . ' ' . $student['last_name']); ?>
                </h2>
                <p class="mb-2">Admission No: <strong><?php echo htmlspecialchars($student['admission_no']); ?></strong>
                </p>
                <div class="d-flex gap-3">
                  <span class="badge badge-light">Class:
                    <?php echo htmlspecialchars($student['class_name'] ?? 'Not Assigned'); ?></span>
                  <span class="badge badge-light">Session:
                    <?php echo htmlspecialchars($student['session_name'] ?? 'N/A'); ?></span>
                  <span
                    class="badge badge-<?php echo $student['status'] === 'active' ? 'success' : 'warning'; ?>"><?php echo ucfirst($student['status']); ?></span>
                </div>
              </div>
              <div class="col-md-4 text-end">
                <div class="btn-group-vertical" role="group">
                  <a href="edit_students.php?id=<?php echo $student['id']; ?>" class="btn btn-light action-btn">
                    <i class="fas fa-edit me-2"></i>Edit Student
                  </a>
                  <a href="list_students.php" class="btn btn-light action-btn">
                    <i class="fas fa-arrow-left me-2"></i>Back to List
                  </a>
                </div>
              </div>
            </div>
          </div>

          <!-- Alert Messages -->
          <?php if ($message): ?>
          <div class="alert alert-success alert-dismissible fade show" role="alert">
            <div class="d-flex align-items-center">
              <i class="fas fa-check-circle me-2"></i>
              <div>
                <strong>Payment Successful!</strong><br>
                <?php echo htmlspecialchars($message); ?>
              </div>
            </div>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
          </div>
          <?php endif; ?>

          <?php if ($error): ?>
          <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <div class="d-flex align-items-center">
              <i class="fas fa-exclamation-triangle me-2"></i>
              <div>
                <strong>Payment Failed!</strong><br>
                <?php echo htmlspecialchars($error); ?>
              </div>
            </div>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
          </div>
          <?php endif; ?>

          <div class="row">
            <!-- Student Details -->
            <div class="col-lg-8">
              <div class="card mb-4">
                <div class="card-header">
                  <h4 class="card-title mb-0">
                    <i class="fas fa-user me-2"></i>Student Information
                  </h4>
                </div>
                <div class="card-body">
                  <div class="row">
                    <div class="col-md-6">
                      <h5>Basic Information</h5>
                      <table class="table table-borderless">
                        <tr>
                          <td><strong>Admission No:</strong></td>
                          <td><?php echo htmlspecialchars($student['admission_no'] ?? 'N/A'); ?></td>
                        </tr>
                        <tr>
                          <td><strong>Full Name:</strong></td>
                          <td>
                            <?php echo htmlspecialchars($student['first_name'] . ' ' . ($student['other_names'] ?? '') . ' ' . $student['last_name']); ?>
                          </td>
                        </tr>
                        <tr>
                          <td><strong>State of Origin:</strong></td>
                          <td><?php echo htmlspecialchars($student['state_of_origin'] ?? 'N/A'); ?></td>
                        </tr>
                        <tr>
                          <td><strong>LGA:</strong></td>
                          <td><?php echo htmlspecialchars($student['lga'] ?? 'N/A'); ?></td>
                        </tr>
                        <tr>
                          <td><strong>Gender:</strong></td>
                          <td><?php echo ucfirst($student['gender'] ?? 'N/A'); ?></td>
                        </tr>
                        <tr>
                          <td><strong>Date of Birth:</strong></td>
                          <td>
                            <?php echo $student['date_of_birth'] ? date('M d, Y', strtotime($student['date_of_birth'])) : 'N/A'; ?>
                          </td>
                        </tr>
                      </table>
                    </div>
                    <div class="col-md-6">
                      <h5>Academic Information</h5>
                      <table class="table table-borderless">
                        <tr>
                          <td><strong>Current Class:</strong></td>
                          <td><?php echo htmlspecialchars($student['class_name'] ?? 'Not Assigned'); ?></td>
                        </tr>
                        <tr>
                          <td><strong>Student Type:</strong></td>
                          <td>
                            <span
                              class="badge badge-<?php echo ($student['student_type'] ?? 'day') == 'boarding' ? 'info' : 'warning'; ?>">
                              <?php echo ucfirst($student['student_type'] ?? 'Day'); ?>
                            </span>
                          </td>
                        </tr>
                        <tr>
                          <td><strong>Attendance:</strong></td>
                          <td><?php echo number_format($attendancePercentage, 1); ?>%</td>
                        </tr>
                        <tr>
                          <td><strong>Grade Average:</strong></td>
                          <td><?php echo number_format($gradeAverage, 1); ?></td>
                        </tr>
                        <tr>
                          <td><strong>Admission Date:</strong></td>
                          <td><?php echo date('M d, Y', strtotime($student['created_at'])); ?></td>
                        </tr>
                      </table>
                    </div>
                  </div>
                </div>
              </div>

              <!-- Payment History -->
              <?php if (!empty($payment_history)): ?>
              <div class="card mb-4">
                <div class="card-header">
                  <h4 class="card-title mb-0">
                    <i class="fas fa-history me-2"></i>Recent Payment History
                  </h4>
                </div>
                <div class="card-body p-0">
                  <div class="table-responsive">
                    <table class="table table-hover mb-0">
                      <thead class="bg-light">
                        <tr>
                          <th>Fee Type</th>
                          <th>Session/Term</th>
                          <th>Due Amount</th>
                          <th>Paid Amount</th>
                          <th>Balance</th>
                          <th>Status</th>
                          <th>Payment Date</th>
                          <th>Receipt</th>
                        </tr>
                      </thead>
                      <tbody>
                        <?php foreach ($payment_history as $payment): ?>
                        <tr>
                          <td><?php echo htmlspecialchars($payment['type_name']); ?></td>
                          <td>
                            <small class="text-muted">
                              <?php echo htmlspecialchars($payment['session_name'] ?? 'N/A'); ?> (<?php echo htmlspecialchars($payment['session_term']); ?>)<br>
                              <?php echo ucfirst(str_replace('_', ' ', $payment['term'])); ?>
                            </small>
                          </td>
                          <td>₦<?php echo number_format($payment['amount_due'], 2); ?></td>
                          <td>₦<?php echo number_format($payment['amount_paid'], 2); ?></td>
                          <td>₦<?php echo number_format($payment['balance'], 2); ?></td>
                          <td>
                            <span class="badge badge-<?php 
                                                            echo $payment['status'] == 'paid' ? 'success' : 
                                                                ($payment['status'] == 'partial' ? 'warning' : 'secondary'); 
                                                        ?>">
                              <?php echo ucfirst($payment['status']); ?>
                            </span>
                          </td>
                          <td>
                            <?php echo $payment['payment_date'] ? date('M d, Y', strtotime($payment['payment_date'])) : 'N/A'; ?>
                          </td>
                          <td>
                            <?php if ($payment['receipt_number']): ?>
                            <small class="text-muted"><?php echo htmlspecialchars($payment['receipt_number']); ?></small>
                            <br>
                            <a href="print_receipt.php?receipt=<?php echo urlencode($payment['receipt_number']); ?>" 
                               target="_blank" 
                               class="btn btn-sm btn-outline-primary mt-1">
                                <i class="fas fa-print"></i> Print
                            </a>
                            <?php else: ?>
                            <span class="text-muted">-</span>
                            <?php endif; ?>
                          </td>
                        </tr>
                        <?php endforeach; ?>
                      </tbody>
                    </table>
                  </div>
                </div>
              </div>
              <?php endif; ?>
            </div>

            <!-- Payment Management Sidebar -->
            <div class="col-lg-4">
              <!-- Fee Summary -->
              <div class="card fee-summary-card mb-4">
                <div class="card-header border-0">
                  <h5 class="card-title text-white mb-0">
                    <i class="fas fa-chart-pie me-2"></i>Fee Summary
                  </h5>
                </div>
                <div class="card-body text-center">
                  <div class="row">
                    <div class="col-4">
                      <h4 class="text-white mb-1">₦<?php echo number_format($fee_summary['total_due'], 0); ?></h4>
                      <small class="text-white-75">Total Due</small>
                    </div>
                    <div class="col-4">
                      <h4 class="text-white mb-1">₦<?php echo number_format($fee_summary['total_paid'], 0); ?></h4>
                      <small class="text-white-75">Total Paid</small>
                    </div>
                    <div class="col-4">
                      <h4 class="text-white mb-1">₦<?php echo number_format($fee_summary['total_balance'], 0); ?></h4>
                      <small class="text-white-75">Balance</small>
                    </div>
                  </div>
                  <hr class="border-white-25">
                  <button type="button" class="btn btn-light btn-make-payment" data-bs-toggle="modal"
                    data-bs-target="#paymentModal">
                    <i class="fas fa-credit-card me-2"></i>Make Payment
                  </button>
                </div>
              </div>

              <!-- Outstanding Fees -->
              <?php if (!empty($outstanding_fees)): ?>
              <div class="card outstanding-card mb-4">
                <div class="card-header border-0">
                  <h5 class="card-title text-white mb-0">
                    <i class="fas fa-exclamation-triangle me-2"></i>Outstanding Fees
                  </h5>
                </div>
                <div class="card-body">
                  <?php 
                                    $total_outstanding = 0;
                                    foreach ($outstanding_fees as $fee):
                                        $total_outstanding += $fee['balance'];
                                    ?>
                  <div class="border-bottom border-white-25 pb-2 mb-2">
                    <div class="d-flex justify-content-between">
                      <div>
                        <strong class="text-white"><?php echo htmlspecialchars($fee['type_name']); ?></strong><br>
                        <small class="text-white-75">
                          <?php echo htmlspecialchars($fee['session_name'] ?? 'N/A'); ?> (<?php echo htmlspecialchars($fee['session_term']); ?>) -
                          <?php echo ucfirst(str_replace('_', ' ', $fee['term'])); ?>
                        </small>
                      </div>
                      <div class="text-end">
                        <strong class="text-white">₦<?php echo number_format($fee['balance'], 2); ?></strong>
                      </div>
                    </div>
                  </div>
                  <?php endforeach; ?>

                  <div class="text-center mt-3 pt-3 border-top border-white-25">
                    <h4 class="text-white mb-0">Total: ₦<?php echo number_format($total_outstanding, 2); ?></h4>
                  </div>
                </div>
              </div>
              <?php endif; ?>

              <!-- Quick Actions -->
              <div class="card">
                <div class="card-header">
                  <h5 class="card-title mb-0">
                    <i class="fas fa-bolt me-2"></i>Quick Actions
                  </h5>
                </div>
                <div class="card-body">
                  <div class="d-grid gap-2">
                    <button type="button" class="btn btn-make-payment action-btn" data-bs-toggle="modal"
                      data-bs-target="#paymentModal">
                      <i class="fas fa-credit-card me-2"></i>Make Payment
                    </button>
                    <a href="edit_students.php?id=<?php echo $student['id']; ?>" class="btn btn-warning action-btn">
                      <i class="fas fa-edit me-2"></i>Edit Student
                    </a>
                    <a href="fee_structure.php?student_id=<?php echo $student['id']; ?>" class="btn btn-info action-btn">
                      <i class="fas fa-file-invoice me-2"></i>Fee Structure
                    </a>
                    <a href="#" class="btn btn-secondary action-btn" onclick="window.print()">
                      <i class="fas fa-print me-2"></i>Print Profile
                    </a>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- Payment Modal -->
          <div class="modal fade" id="paymentModal" tabindex="-1" aria-labelledby="paymentModalLabel"
            aria-hidden="true">
            <div class="modal-dialog modal-xl">
              <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                  <h5 class="modal-title" id="paymentModalLabel">
                    <i class="fas fa-credit-card me-2"></i>Make Payment -
                    <?php echo htmlspecialchars($student['first_name'] . ' ' . $student['last_name']); ?>
                  </h5>
                  <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                    aria-label="Close"></button>
                </div>
                <div class="modal-body">
                  <?php if (!empty($outstanding_fees)): ?>
                  <!-- Outstanding Fees Selection -->
                  <div class="row">
                    <div class="col-lg-7">
                      <h5 class="text-danger mb-3">
                        <i class="fas fa-exclamation-triangle me-2"></i>Outstanding & Partially Paid Fees
                      </h5>
                      <p class="text-muted mb-4">Select a fee to make a payment. Only fees with outstanding balances are
                        shown to prevent duplicate payments.</p>

                      <div class="outstanding-fees-list">
                        <?php foreach ($outstanding_fees as $index => $fee): ?>
                        <div class="card mb-3 fee-item" data-fee-id="<?php echo $fee['id']; ?>"
                          data-fee-name="<?php echo htmlspecialchars($fee['type_name']); ?>"
                          data-session="<?php echo htmlspecialchars($fee['session_name'] ?? 'N/A'); ?>"
                          data-term="<?php echo htmlspecialchars($fee['term']); ?>"
                          data-amount-due="<?php echo $fee['amount_due']; ?>"
                          data-amount-paid="<?php echo $fee['amount_paid']; ?>"
                          data-balance="<?php echo $fee['balance']; ?>" data-status="<?php echo $fee['status']; ?>">
                          <div class="card-body py-3">
                            <div class="form-check">
                              <input class="form-check-input fee-selector" type="radio" name="selected_fee"
                                id="fee_<?php echo $index; ?>" value="<?php echo $fee['id']; ?>">
                              <label class="form-check-label w-100" for="fee_<?php echo $index; ?>">
                                <div class="d-flex justify-content-between align-items-start">
                                  <div>
                                    <h6 class="mb-1 text-primary"><?php echo htmlspecialchars($fee['type_name']); ?></h6>
                                    <small class="text-muted">
                                      <?php echo htmlspecialchars($fee['session_name'] ?? 'N/A'); ?> -
                                      <?php echo ucfirst(str_replace('_', ' ', $fee['term'])); ?>
                                    </small>
                                    <br>
                                    <span
                                      class="badge badge-<?php echo $fee['status'] == 'overdue' ? 'danger' : 'warning'; ?> mt-1">
                                      <?php echo ucfirst($fee['status']); ?>
                                    </span>
                                  </div>
                                  <div class="text-end">
                                    <div class="small text-muted">Amount Due</div>
                                    <div class="fw-bold">₦<?php echo number_format($fee['amount_due'], 2); ?></div>
                                    <div class="small text-success">Paid:
                                      ₦<?php echo number_format($fee['amount_paid'], 2); ?></div>
                                    <div class="small text-danger">Balance:
                                      ₦<?php echo number_format($fee['balance'], 2); ?></div>
                                  </div>
                                </div>
                              </label>
                            </div>
                          </div>
                        </div>
                        <?php endforeach; ?>
                      </div>
                    </div>

                    <div class="col-lg-5">
                      <!-- Payment Form -->
                      <div class="card border-primary">
                        <div class="card-header bg-light">
                          <h6 class="mb-0">
                            <i class="fas fa-money-bill-wave me-2"></i>Payment Details
                          </h6>
                        </div>
                        <div class="card-body">
                          <form method="POST" action="" id="modalPaymentForm">
                            <!-- Hidden fields for selected fee context -->
                            <input type="hidden" id="modal_fee_id" name="fee_id" value="">
                            <input type="hidden" id="modal_academic_session" name="academic_session" value="">
                            <input type="hidden" id="modal_class" name="class"
                              value="<?php echo $student['class_link']; ?>">
                            <input type="hidden" id="modal_term" name="term" value="">

                            <!-- Selected Fee Summary -->
                            <div id="selected-fee-summary" class="alert alert-info" style="display: none;">
                              <h6 class="alert-heading mb-2">Selected Fee</h6>
                              <div id="fee-summary-content"></div>
                            </div>

                            <div class="form-group mb-3">
                              <label for="modal_payment_amount" class="form-label">Payment Amount (₦) <span
                                  class="text-danger">*</span></label>
                              <input type="number" step="0.01" min="0.01" class="form-control form-control-lg"
                                id="modal_payment_amount" name="payment_amount" placeholder="0.00" required disabled>
                              <small class="form-text text-muted">Maximum: <span id="max-amount">₦0.00</span></small>
                            </div>

                            <div class="form-group mb-3">
                              <label for="modal_payment_method" class="form-label">Payment Method <span
                                  class="text-danger">*</span></label>
                              <select class="form-select" id="modal_payment_method" name="payment_method" required
                                disabled>
                                <option value="">Select Method</option>
                                <option value="cash">Cash</option>
                                <option value="bank_transfer">Bank Transfer</option>
                                <option value="pos">POS</option>
                                <option value="cheque">Cheque</option>
                                <option value="online">Online Payment</option>
                              </select>
                            </div>

                            <div class="form-group mb-4">
                              <label for="modal_payment_description" class="form-label">Payment Description</label>
                              <textarea class="form-control" id="modal_payment_description" name="payment_description"
                                rows="3" placeholder="Optional: Add any notes about this payment" disabled></textarea>
                            </div>

                            <div class="d-grid">
                              <button type="submit" name="process_payment" class="btn btn-make-payment btn-lg"
                                id="modal-submit-btn" disabled>
                                <i class="fas fa-credit-card me-2"></i>Process Payment
                              </button>
                            </div>
                          </form>
                        </div>
                      </div>
                    </div>
                  </div>
                  <?php else: ?>
                  <div class="text-center py-5">
                    <i class="fas fa-check-circle text-success" style="font-size: 4rem;"></i>
                    <h4 class="text-success mt-3">All Fees Paid!</h4>
                    <p class="text-muted">This student has no outstanding fees at the moment.</p>
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                      <i class="fas fa-times me-2"></i>Close
                    </button>
                  </div>
                  <?php endif; ?>
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
    $(document).ready(function () {
      // Handle fee selection in modal
      $('.fee-selector').on('change', function () {
        if (this.checked) {
          const feeItem = $(this).closest('.fee-item');
          const feeId = feeItem.data('fee-id');
          const feeName = feeItem.data('fee-name');
          const session = feeItem.data('session');
          const term = feeItem.data('term');
          const amountDue = parseFloat(feeItem.data('amount-due'));
          const amountPaid = parseFloat(feeItem.data('amount-paid'));
          const balance = parseFloat(feeItem.data('balance'));
          const status = feeItem.data('status');

          // Populate hidden fields
          $('#modal_fee_id').val(feeId);
          $('#modal_academic_session').val('<?php echo $sessions[0]['id'] ?? ''; ?>'); // Use current session
          $('#modal_term').val(term);

          // Show and populate fee summary
          $('#fee-summary-content').html(`
                        <strong>${feeName}</strong><br>
                        <small class="text-muted">${session} - ${term.replace('_', ' ').replace(/\b\w/g, l => l.toUpperCase())}</small><br>
                        <div class="mt-2">
                            <div class="row">
                                <div class="col-4"><small>Amount Due:</small><br><strong>₦${amountDue.toLocaleString()}</strong></div>
                                <div class="col-4"><small>Paid:</small><br><strong class="text-success">₦${amountPaid.toLocaleString()}</strong></div>
                                <div class="col-4"><small>Balance:</small><br><strong class="text-danger">₦${balance.toLocaleString()}</strong></div>
                            </div>
                        </div>
                    `);
          $('#selected-fee-summary').show();

          // Set maximum payment amount and enable form fields
          $('#modal_payment_amount').attr('max', balance).val('').prop('disabled', false);
          $('#modal_payment_method').prop('disabled', false);
          $('#modal_payment_description').prop('disabled', false);
          $('#modal-submit-btn').prop('disabled', false);
          $('#max-amount').text('₦' + balance.toLocaleString());

          // Validate payment amount on input
          $('#modal_payment_amount').off('input').on('input', function () {
            const paymentAmount = parseFloat($(this).val()) || 0;
            if (paymentAmount > balance) {
              $(this).val(balance);
              Swal.fire({
                title: "Amount Exceeded",
                text: `Payment amount cannot exceed the outstanding balance of ₦${balance.toLocaleString()}`,
                icon: "warning",
                confirmButtonColor: "#3498db"
              });
            }
          });
        }
      });

      // Reset modal when closed
      $('#paymentModal').on('hidden.bs.modal', function () {
        $('.fee-selector').prop('checked', false);
        $('#selected-fee-summary').hide();
        $('#modalPaymentForm')[0].reset();
        $('#modal_payment_amount, #modal_payment_method, #modal_payment_description, #modal-submit-btn').prop(
          'disabled', true);
      });

      // Form validation and confirmation for modal form
      $('#modalPaymentForm').on('submit', function (e) {
        e.preventDefault(); // Always prevent default first

        const selectedFee = $('.fee-selector:checked');
        if (selectedFee.length === 0) {
          Swal.fire({
            title: "No Fee Selected",
            text: "Please select a fee to make a payment",
            icon: "error",
            confirmButtonColor: "#3498db"
          });
          return false;
        }

        let amount = parseFloat($('#modal_payment_amount').val());
        if (amount <= 0) {
          Swal.fire({
            title: "Invalid Amount",
            text: "Please enter a valid payment amount greater than 0",
            icon: "error",
            confirmButtonColor: "#3498db"
          });
          return false;
        }

        const balance = parseFloat(selectedFee.closest('.fee-item').data('balance'));
        if (amount > balance) {
          Swal.fire({
            title: "Amount Exceeded",
            text: `Payment amount cannot exceed the outstanding balance of ₦${balance.toLocaleString()}`,
            icon: "error",
            confirmButtonColor: "#3498db"
          });
          return false;
        }

        // Validate payment method
        const paymentMethod = $('#modal_payment_method').val();
        if (!paymentMethod) {
          Swal.fire({
            title: "Payment Method Required",
            text: "Please select a payment method",
            icon: "error",
            confirmButtonColor: "#3498db"
          });
          return false;
        }

        // Confirm payment processing
        const form = this;
        const feeName = selectedFee.closest('.fee-item').data('fee-name');

        // Use newer swal syntax
        Swal.fire({
          title: "Confirm Payment",
          text: `Process payment of ₦${amount.toLocaleString()} for ${feeName} - <?php echo htmlspecialchars($student['first_name'] . ' ' . $student['last_name']); ?>?`,
          icon: "question",
          showCancelButton: true,
          confirmButtonColor: "#3498db",
          confirmButtonText: "Yes, Process Payment",
          cancelButtonText: "Cancel",
          allowOutsideClick: false
        }).then((result) => {
          if (result.isConfirmed) {
            // Show processing message
            Swal.fire({
              title: "Processing Payment",
              text: "Please wait...",
              icon: "info",
              allowOutsideClick: false,
              showConfirmButton: false
            });

            // Debug: Log form data before submission
            console.log('Form data before submission:');
            console.log('Fee ID:', $('#modal_fee_id').val());
            console.log('Academic Session:', $('#modal_academic_session').val());
            console.log('Class:', $('#modal_class').val());
            console.log('Term:', $('#modal_term').val());
            console.log('Payment Amount:', $('#modal_payment_amount').val());
            console.log('Payment Method:', $('#modal_payment_method').val());
            console.log('Description:', $('#modal_payment_description').val());

            // Submit form directly without complex fallbacks
            setTimeout(() => {
              // Remove the submit event handler temporarily to avoid infinite loop
              $(form).off('submit');

              // Create a hidden input to ensure process_payment is sent
              if (!$(form).find('input[name="process_payment"]').length) {
                $(form).append('<input type="hidden" name="process_payment" value="1">');
              }

              // Submit the form
              form.submit();
            }, 500);
          }
        }).catch((error) => {
          console.log('SweetAlert error:', error);
          // Fallback: just submit the form if sweetalert fails
          if (confirm(`Process payment of ₦${amount.toLocaleString()} for ${feeName}?`)) {
            // Remove submit handler and submit
            $(form).off('submit');
            if (!$(form).find('input[name="process_payment"]').length) {
              $(form).append('<input type="hidden" name="process_payment" value="1">');
            }
            form.submit();
          }
        });
      });

      // Legacy form validation (if any old forms exist)
      $('form:not(#modalPaymentForm)').on('submit', function (e) {
        let amount = parseFloat($('#payment_amount').val());
        if (amount <= 0) {
          e.preventDefault();
          Swal.fire({
            title: "Invalid Amount",
            text: "Please enter a valid payment amount greater than 0",
            icon: "error",
            confirmButtonColor: "#3498db"
          });
          return false;
        }

        // Confirm payment processing
        e.preventDefault();
        let form = this;

        Swal.fire({
          title: "Confirm Payment",
          text: "Process payment of ₦" + amount.toLocaleString() +
            " for <?php echo htmlspecialchars($student['first_name'] . ' ' . $student['last_name']); ?>?",
          icon: "question",
          showCancelButton: true,
          confirmButtonColor: "#3498db",
          confirmButtonText: "Yes, Process Payment",
          cancelButtonText: "Cancel"
        }).then(function(result) {
          if (result.isConfirmed) {
            form.submit();
          }
        });
      });
    });
  </script>

</body>

</html>



