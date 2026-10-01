<?php
// Start session safely
if (session_status() === PHP_SESSION_NONE) {
    @session_start();
}

// Include database configuration and functions
require_once base_path('db/config.php');
require_once base_path('db/functions.php');

global $pdo;
if (!isset($pdo) || !($pdo instanceof PDO)) {
    if (class_exists('\Illuminate\Support\Facades\DB')) {
        try {
            $pdo = \Illuminate\Support\Facades\DB::connection()->getPdo();
        } catch (\Throwable $t) {}
    }
    if (!isset($pdo) || !($pdo instanceof PDO)) {
        $pdo = $GLOBALS['pdo'] ?? null;
    }
}

// Check if user is logged in
if (!isset($_SESSION['adid']) && !auth()->check()) {
    if (!headers_sent()) {
        header('Location: /admin/login.php');
        exit;
    }
}

$message = '';
$error = '';
$payment_success = false;

// Fetch student information
$studentId = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT) ?: (int) ($_GET['id'] ?? request('id') ?? 0);
if ($studentId) {
    try {
        $student_stmt = $pdo->prepare(
            "SELECT s.*,
                    COALESCE(sc.class_name, 'Not Assigned') AS class_name,
                    COALESCE(sc.grade_level, '') AS class_level,
                    COALESCE(ac.session_name, 'N/A') AS session_name
             FROM students s
             LEFT JOIN school_classes sc ON (s.current_class_id = sc.id OR s.class_link = sc.id)
             LEFT JOIN academic_sessions ac ON (s.current_session_id = ac.id OR s.academic_session_link = ac.id)
             WHERE s.id = ?"
        );
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
$isPost = (isset($_SERVER['REQUEST_METHOD']) && strtoupper((string) $_SERVER['REQUEST_METHOD']) === 'POST') || (function_exists('request') && request()->isMethod('post'));
$hasProcessPayment = isset($_POST['process_payment']) || (function_exists('request') && request()->has('process_payment'));

if ($isPost && $hasProcessPayment) {
    $fee_id = filter_input(INPUT_POST, 'fee_id', FILTER_VALIDATE_INT) ?: (int) ($_POST['fee_id'] ?? request('fee_id') ?? 0);
    $payment_amount = (float) ($_POST['payment_amount'] ?? request('payment_amount') ?? 0);
    $payment_method = trim((string) ($_POST['payment_method'] ?? request('payment_method') ?? ''));
    $payment_description = trim((string) ($_POST['payment_description'] ?? request('payment_description') ?? ''));

    // Validation
    if ($payment_amount <= 0 || $payment_method === '') {
        $error = 'Please enter a valid payment amount greater than 0 and select a payment method.';
    } elseif (!$fee_id) {
        $error = 'Please select an allocated fee to process a payment.';
    } else {
        try {
            $pdo->beginTransaction();

            // Fetch existing fee record for this student
            $existing_fee_stmt = $pdo->prepare("SELECT * FROM student_fees WHERE id = ? AND student_id = ? FOR UPDATE");
            $existing_fee_stmt->execute([$fee_id, $studentId]);
            $existing_fee = $existing_fee_stmt->fetch(PDO::FETCH_ASSOC);

            if (!$existing_fee) {
                throw new Exception('Fee record not found or does not belong to this student.');
            }

            $currentBalance = (float) ($existing_fee['balance'] ?? 0);
            $currentAmountDue = (float) ($existing_fee['amount_due'] ?? 0);
            $currentAmountPaid = (float) ($existing_fee['amount_paid'] ?? 0);

            // Validate payment amount doesn't exceed current outstanding balance
            if ($payment_amount > ($currentBalance + 0.001)) {
                throw new Exception('Payment amount (₦' . number_format($payment_amount, 2) . ') cannot exceed the outstanding balance of ₦' . number_format($currentBalance, 2));
            }

            // Calculate new totals (INCREMENT existing payment amount so past payments are never overwritten)
            $new_amount_paid = $currentAmountPaid + $payment_amount;
            $new_balance = max(0.00, $currentAmountDue - $new_amount_paid);

            $status = 'partial';
            if ($new_balance <= 0.001) {
                $status = 'paid';
                $new_balance = 0.00;
            } elseif ($new_amount_paid <= 0) {
                $status = 'pending';
            }

            // Update student_fees table
            $update_fee_stmt = $pdo->prepare("UPDATE student_fees SET amount_paid = ?, balance = ?, status = ?, updated_at = NOW() WHERE id = ?");
            $update_fee_stmt->execute([
                $new_amount_paid,
                $new_balance,
                $status,
                $existing_fee['id']
            ]);

            // Generate receipt reference and record transaction entry in payments table
            $receipt_number = 'RCP' . date('Y') . str_pad(rand(1, 999999), 6, '0', STR_PAD_LEFT);
            $paymentMethodForStorage = ($payment_method === 'pos') ? 'card' : $payment_method;

            $studentFullName = trim(($student['first_name'] ?? '') . ' ' . ($student['last_name'] ?? ''));

            $payment_insert_stmt = $pdo->prepare("INSERT INTO payments (
                payment_reference,
                payable_type,
                payable_id,
                amount,
                payment_method,
                payment_date,
                payer_name,
                payer_phone,
                description,
                status,
                created_at,
                updated_at
            ) VALUES (?, 'student_fee', ?, ?, ?, CURDATE(), ?, ?, ?, 'completed', NOW(), NOW())");

            $payment_insert_stmt->execute([
                $receipt_number,
                $existing_fee['id'],
                $payment_amount,
                $paymentMethodForStorage,
                $studentFullName,
                $student['phone'] ?? null,
                $payment_description ?: ('Fee Payment - Receipt #' . $receipt_number)
            ]);

            $pdo->commit();
            $payment_success = true;
            $message = "Payment of ₦" . number_format($payment_amount, 2) . " processed successfully! Receipt Number: " . $receipt_number;

        } catch (Exception $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            $error = 'Payment processing failed: ' . $e->getMessage();
        }
    }
}

// Get student fee summary, recent payment history, and outstanding fees
try {
    // Get student fee summary
    $fee_summary_stmt = $pdo->prepare("SELECT COALESCE(SUM(sf.amount_due), 0) as total_due, COALESCE(SUM(sf.amount_paid), 0) as total_paid, COALESCE(SUM(sf.balance), 0) as total_balance FROM student_fees sf WHERE sf.student_id = ?");
    $fee_summary_stmt->execute([$studentId]);
    $fee_summary = $fee_summary_stmt->fetch(PDO::FETCH_ASSOC);

    // Get recent payment history
    $payment_history_stmt = $pdo->prepare("
        SELECT 
            p.payment_reference AS receipt_number,
            p.payment_date,
            p.amount AS transaction_amount,
            p.payment_method,
            sf.status AS fee_status,
            sf.amount_due,
            sf.amount_paid AS total_fee_paid,
            sf.balance AS current_balance,
            COALESCE(fs.name, fs.description, 'School Fee') AS type_name,
            ac.session_name,
            t.term_name AS session_term,
            t.term_name AS term
        FROM payments p
        JOIN student_fees sf ON p.payable_type = 'student_fee' AND p.payable_id = sf.id
        LEFT JOIN fee_structures fs ON sf.fee_structure_id = fs.id
        LEFT JOIN academic_sessions ac ON fs.session_id = ac.id
        LEFT JOIN academic_terms t ON fs.term_id = t.id
        WHERE sf.student_id = ?
        AND p.status = 'completed'
        ORDER BY COALESCE(p.payment_date, p.created_at) DESC, p.id DESC
        LIMIT 20
    ");
    $payment_history_stmt->execute([$studentId]);
    $payment_history = $payment_history_stmt->fetchAll(PDO::FETCH_ASSOC);

    // Get outstanding fees
    $outstanding_stmt = $pdo->prepare("
        SELECT 
            sf.*,
            COALESCE(fs.name, fs.description, 'School Fee') AS type_name,
            COALESCE(fs.description, fs.name) AS fee_description,
            ac.session_name,
            t.term_name AS session_term,
            t.term_name AS term
        FROM student_fees sf
        LEFT JOIN fee_structures fs ON sf.fee_structure_id = fs.id
        LEFT JOIN academic_sessions ac ON fs.session_id = ac.id
        LEFT JOIN academic_terms t ON fs.term_id = t.id
        WHERE sf.student_id = ?
        AND sf.balance > 0
        ORDER BY sf.due_date ASC, sf.updated_at DESC
    ");
    $outstanding_stmt->execute([$studentId]);
    $outstanding_fees = $outstanding_stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    $fee_summary = ['total_due' => 0, 'total_paid' => 0, 'total_balance' => 0];
    $payment_history = [];
    $outstanding_fees = [];
}

// Get additional student metrics
$attendancePercentage = student_attendance_percentage($studentId);
$gradeAverage = student_grade_average($studentId);

?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta http-equiv="X-UA-Compatible" content="IE=edge" />
  <title>Student Profile - <?php echo htmlspecialchars(($student['first_name'] ?? '') . ' ' . ($student['last_name'] ?? '')); ?></title>
  @include('admin.partials.links')
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
  <style>
    .student-header {
      background: linear-gradient(135deg, #720922 0%, #8e1532 100%);
      color: white;
      border-radius: 15px;
      padding: 25px;
      margin-bottom: 25px;
    }

    .fee-summary-card {
      background: linear-gradient(135deg, #15803d 0%, #22c55e 100%);
      color: white;
      border: none;
      border-radius: 15px;
    }

    .outstanding-card {
      background: linear-gradient(135deg, #b91c1c 0%, #ef4444 100%);
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
      background: linear-gradient(45deg, #720922, #8e1532);
      border: none;
      color: white;
    }

    .btn-make-payment:hover {
      background: linear-gradient(45deg, #5c071b, #720922);
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
                <h2 class="mb-1 text-white"><?php echo htmlspecialchars(trim(($student['first_name'] ?? '') . ' ' . ($student['other_names'] ?? '') . ' ' . ($student['last_name'] ?? ''))); ?></h2>
                <p class="mb-2">Admission No: <strong><?php echo htmlspecialchars((string) ($student['admission_no'] ?? 'N/A')); ?></strong></p>
                <div class="d-flex gap-3">
                  <span class="badge bg-light text-dark">Class: <?php echo htmlspecialchars((string) ($student['class_name'] ?? 'Not Assigned')); ?></span>
                  <span class="badge bg-light text-dark">Session: <?php echo htmlspecialchars((string) ($student['session_name'] ?? 'N/A')); ?></span>
                  <span class="badge bg-<?php echo ($student['status'] ?? '') === 'active' ? 'success' : 'warning'; ?>"><?php echo ucfirst((string) ($student['status'] ?? 'active')); ?></span>
                </div>
              </div>
              <div class="col-md-4 text-end">
                <div class="btn-group-vertical" role="group">
                  <a href="edit_students.php?id=<?php echo (int) $student['id']; ?>" class="btn btn-light action-btn">
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
              <i class="fas fa-check-circle me-2 fa-lg"></i>
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
              <i class="fas fa-exclamation-triangle me-2 fa-lg"></i>
              <div>
                <strong>Payment Processing Error:</strong><br>
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
                          <td><code><?php echo htmlspecialchars((string) ($student['admission_no'] ?? 'N/A')); ?></code></td>
                        </tr>
                        <tr>
                          <td><strong>Full Name:</strong></td>
                          <td><?php echo htmlspecialchars(trim(($student['first_name'] ?? '') . ' ' . ($student['other_names'] ?? '') . ' ' . ($student['last_name'] ?? ''))); ?></td>
                        </tr>
                        <tr>
                          <td><strong>State of Origin:</strong></td>
                          <td><?php echo htmlspecialchars((string) ($student['state_of_origin'] ?? 'N/A')); ?></td>
                        </tr>
                        <tr>
                          <td><strong>LGA:</strong></td>
                          <td><?php echo htmlspecialchars((string) ($student['lga'] ?? 'N/A')); ?></td>
                        </tr>
                        <tr>
                          <td><strong>Gender:</strong></td>
                          <td><?php echo ucfirst((string) ($student['gender'] ?? 'N/A')); ?></td>
                        </tr>
                        <tr>
                          <td><strong>Date of Birth:</strong></td>
                          <td><?php echo !empty($student['date_of_birth']) ? date('M d, Y', strtotime((string) $student['date_of_birth'])) : 'N/A'; ?></td>
                        </tr>
                        <tr>
                          <td><strong>Blood Group:</strong></td>
                          <td><?php echo htmlspecialchars((string) ($student['blood_group'] ?? 'N/A')); ?></td>
                        </tr>
                        <tr>
                          <td><strong>Genotype:</strong></td>
                          <td><?php echo htmlspecialchars((string) ($student['genotype'] ?? 'N/A')); ?></td>
                        </tr>
                      </table>
                    </div>
                    <div class="col-md-6">
                      <h5>Academic Information</h5>
                      <table class="table table-borderless">
                        <tr>
                          <td><strong>Current Class:</strong></td>
                          <td><?php echo htmlspecialchars((string) ($student['class_name'] ?? 'Not Assigned')); ?></td>
                        </tr>
                        <tr>
                          <td><strong>Student Type:</strong></td>
                          <td>
                            <span class="badge bg-<?php echo strtolower((string) ($student['student_type'] ?? 'day')) === 'boarding' ? 'info' : 'warning'; ?> text-dark">
                              <?php echo ucfirst((string) ($student['student_type'] ?? 'Day')); ?>
                            </span>
                          </td>
                        </tr>
                        <tr>
                          <td><strong>Attendance:</strong></td>
                          <td><?php echo number_format((float) $attendancePercentage, 1); ?>%</td>
                        </tr>
                        <tr>
                          <td><strong>Grade Average:</strong></td>
                          <td><?php echo number_format((float) $gradeAverage, 1); ?></td>
                        </tr>
                        <tr>
                          <td><strong>Admission Date:</strong></td>
                          <td>
                            <?php
                              $admissionDate = $student['enrollment_date'] ?? $student['created_at'] ?? null;
                              echo !empty($admissionDate) ? date('M d, Y', strtotime((string) $admissionDate)) : 'N/A';
                            ?>
                          </td>
                        </tr>
                      </table>
                    </div>
                  </div>
                </div>
              </div>

              <!-- Recent Payment History -->
              <div class="card mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                  <h4 class="card-title mb-0">
                    <i class="fas fa-history me-2"></i>Recent Payment History
                  </h4>
                  <span class="badge bg-secondary"><?php echo count($payment_history); ?> transactions</span>
                </div>
                <div class="card-body p-0">
                  <?php if (empty($payment_history)): ?>
                    <div class="text-center py-4 text-muted">
                      <i class="fas fa-info-circle fa-2x mb-2 d-block"></i> No completed payment transactions found for this student.
                    </div>
                  <?php else: ?>
                    <div class="table-responsive">
                      <table class="table table-hover mb-0">
                        <thead class="bg-light">
                          <tr>
                            <th>Fee Type</th>
                            <th>Session / Term</th>
                            <th>Total Fee (₦)</th>
                            <th>Paid in Txn (₦)</th>
                            <th>Current Balance (₦)</th>
                            <th>Status</th>
                            <th>Payment Date</th>
                            <th>Receipt</th>
                          </tr>
                        </thead>
                        <tbody>
                          <?php foreach ($payment_history as $payment): ?>
                          <tr>
                            <td><strong><?php echo htmlspecialchars((string) ($payment['type_name'] ?? 'School Fee')); ?></strong></td>
                            <td>
                              <small class="text-muted">
                                <?php echo htmlspecialchars((string) ($payment['session_name'] ?? 'N/A')); ?>
                                <?php if (!empty($payment['session_term'])): ?>
                                  (<?php echo htmlspecialchars((string) $payment['session_term']); ?>)
                                <?php endif; ?>
                              </small>
                            </td>
                            <td class="fw-bold">₦<?php echo number_format((float) ($payment['amount_due'] ?? 0), 2); ?></td>
                            <td class="text-success fw-bold">₦<?php echo number_format((float) ($payment['transaction_amount'] ?? 0), 2); ?></td>
                            <td class="text-danger fw-bold">₦<?php echo number_format((float) ($payment['current_balance'] ?? 0), 2); ?></td>
                            <td>
                              <span class="badge bg-<?php 
                                  echo ($payment['fee_status'] ?? '') === 'paid' ? 'success' : 
                                      (($payment['fee_status'] ?? '') === 'partial' ? 'warning' : 'secondary'); 
                              ?> text-dark">
                                <?php echo ucfirst((string) ($payment['fee_status'] ?? 'completed')); ?>
                              </span>
                            </td>
                            <td>
                              <?php echo !empty($payment['payment_date']) ? date('M d, Y', strtotime((string) $payment['payment_date'])) : 'N/A'; ?>
                            </td>
                            <td>
                              <?php if (!empty($payment['receipt_number'])): ?>
                                <code><?php echo htmlspecialchars((string) $payment['receipt_number']); ?></code>
                                <br>
                                <a href="print_receipt.php?receipt=<?php echo urlencode((string) $payment['receipt_number']); ?>" 
                                   target="_blank" 
                                   class="btn btn-sm btn-outline-primary mt-1">
                                    <i class="fas fa-print me-1"></i> Print
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
                  <?php endif; ?>
                </div>
              </div>
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
                      <h4 class="text-white mb-1">₦<?php echo number_format((float) ($fee_summary['total_due'] ?? 0), 2); ?></h4>
                      <small class="text-white-75">Total Due</small>
                    </div>
                    <div class="col-4">
                      <h4 class="text-white mb-1">₦<?php echo number_format((float) ($fee_summary['total_paid'] ?? 0), 2); ?></h4>
                      <small class="text-white-75">Total Paid</small>
                    </div>
                    <div class="col-4">
                      <h4 class="text-white mb-1">₦<?php echo number_format((float) ($fee_summary['total_balance'] ?? 0), 2); ?></h4>
                      <small class="text-white-75">Balance</small>
                    </div>
                  </div>
                  <hr class="border-white-25">
                  <div class="d-grid gap-2">
                    <button type="button" class="btn btn-light text-dark fw-bold" data-bs-toggle="modal" data-bs-target="#paymentModal">
                      <i class="fas fa-credit-card me-2"></i>Make Payment
                    </button>
                    <a href="fee_structure.php?student_id=<?php echo (int) $student['id']; ?>" class="btn btn-outline-light text-white">
                      <i class="fas fa-plus-circle me-1"></i> Allocate Fee Structure
                    </a>
                  </div>
                </div>
              </div>

              <!-- Outstanding Fees Sidebar Card -->
              <?php if (!empty($outstanding_fees)): ?>
              <div class="card outstanding-card mb-4">
                <div class="card-header border-0">
                  <h5 class="card-title text-white mb-0">
                    <i class="fas fa-exclamation-triangle me-2"></i>Outstanding Allocated Fees
                  </h5>
                </div>
                <div class="card-body">
                  <?php 
                    $total_outstanding = 0;
                    foreach ($outstanding_fees as $fee):
                        $total_outstanding += (float) ($fee['balance'] ?? 0);
                  ?>
                  <div class="border-bottom border-white-25 pb-2 mb-2">
                    <div class="d-flex justify-content-between align-items-start">
                      <div>
                        <strong class="text-white"><?php echo htmlspecialchars((string) ($fee['type_name'] ?? 'Fee')); ?></strong><br>
                        <small class="text-white-75">
                          <?php echo htmlspecialchars((string) ($fee['session_name'] ?? 'N/A')); ?> 
                          <?php if (!empty($fee['session_term'])): ?>
                            (<?php echo htmlspecialchars((string) $fee['session_term']); ?>)
                          <?php endif; ?>
                        </small>
                      </div>
                      <div class="text-end">
                        <strong class="text-white">₦<?php echo number_format((float) ($fee['balance'] ?? 0), 2); ?></strong>
                      </div>
                    </div>
                  </div>
                  <?php endforeach; ?>

                  <div class="text-center mt-3 pt-3 border-top border-white-25">
                    <h4 class="text-white mb-0">Total Outstanding: ₦<?php echo number_format($total_outstanding, 2); ?></h4>
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
                    <button type="button" class="btn btn-make-payment action-btn" data-bs-toggle="modal" data-bs-target="#paymentModal">
                      <i class="fas fa-credit-card me-2"></i>Make Payment
                    </button>
                    <a href="fee_structure.php?student_id=<?php echo (int) $student['id']; ?>" class="btn btn-info action-btn text-white">
                      <i class="fas fa-file-invoice me-2"></i>Allocate Fee Structure
                    </a>
                    <a href="edit_students.php?id=<?php echo (int) $student['id']; ?>" class="btn btn-warning action-btn text-dark">
                      <i class="fas fa-edit me-2"></i>Edit Student Profile
                    </a>
                    <button type="button" class="btn btn-secondary action-btn" onclick="window.print()">
                      <i class="fas fa-print me-2"></i>Print Profile
                    </button>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- Payment Modal -->
          <div class="modal fade" id="paymentModal" tabindex="-1" aria-labelledby="paymentModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-xl">
              <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                  <h5 class="modal-title" id="paymentModalLabel">
                    <i class="fas fa-credit-card me-2"></i>Make Payment - <?php echo htmlspecialchars(trim(($student['first_name'] ?? '') . ' ' . ($student['last_name'] ?? ''))); ?>
                  </h5>
                  <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                  <?php if (!empty($outstanding_fees)): ?>
                  <!-- Outstanding Fees Selection -->
                  <div class="row">
                    <div class="col-lg-7">
                      <h5 class="text-danger mb-3">
                        <i class="fas fa-exclamation-triangle me-2"></i>Select Allocated Fee to Pay
                      </h5>
                      <p class="text-muted mb-4">Select an allocated fee below. Payments strictly update and increment existing payments on the selected fee without overwriting prior payment history.</p>

                      <div class="outstanding-fees-list">
                        <?php foreach ($outstanding_fees as $index => $fee): ?>
                        <div class="card mb-3 fee-item" 
                             data-fee-id="<?php echo (int) $fee['id']; ?>"
                             data-fee-name="<?php echo htmlspecialchars((string) ($fee['type_name'] ?? 'Fee')); ?>"
                             data-session="<?php echo htmlspecialchars((string) ($fee['session_name'] ?? 'N/A')); ?>"
                             data-term="<?php echo htmlspecialchars((string) ($fee['session_term'] ?? '')); ?>"
                             data-amount-due="<?php echo (float) ($fee['amount_due'] ?? 0); ?>"
                             data-amount-paid="<?php echo (float) ($fee['amount_paid'] ?? 0); ?>"
                             data-balance="<?php echo (float) ($fee['balance'] ?? 0); ?>" 
                             data-status="<?php echo htmlspecialchars((string) ($fee['status'] ?? 'pending')); ?>">
                          <div class="card-body py-3">
                            <div class="form-check">
                              <input class="form-check-input fee-selector" type="radio" name="selected_fee" id="fee_<?php echo $index; ?>" value="<?php echo (int) $fee['id']; ?>">
                              <label class="form-check-label w-100" for="fee_<?php echo $index; ?>">
                                <div class="d-flex justify-content-between align-items-start">
                                  <div>
                                    <h6 class="mb-1 text-primary"><?php echo htmlspecialchars((string) ($fee['type_name'] ?? 'Fee')); ?></h6>
                                    <small class="text-muted">
                                      <?php echo htmlspecialchars((string) ($fee['session_name'] ?? 'N/A')); ?>
                                      <?php if (!empty($fee['session_term'])): ?>
                                        - <?php echo htmlspecialchars((string) $fee['session_term']); ?>
                                      <?php endif; ?>
                                    </small>
                                    <br>
                                    <span class="badge bg-<?php echo ($fee['status'] ?? '') === 'overdue' ? 'danger' : 'warning'; ?> text-dark mt-1">
                                      <?php echo ucfirst((string) ($fee['status'] ?? 'pending')); ?>
                                    </span>
                                  </div>
                                  <div class="text-end">
                                    <div class="small text-muted">Amount Due</div>
                                    <div class="fw-bold">₦<?php echo number_format((float) ($fee['amount_due'] ?? 0), 2); ?></div>
                                    <div class="small text-success">Paid: ₦<?php echo number_format((float) ($fee['amount_paid'] ?? 0), 2); ?></div>
                                    <div class="small text-danger">Balance: ₦<?php echo number_format((float) ($fee['balance'] ?? 0), 2); ?></div>
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
                            <input type="hidden" name="process_payment" value="1">

                            <!-- Selected Fee Summary -->
                            <div id="selected-fee-summary" class="alert alert-info" style="display: none;">
                              <h6 class="alert-heading mb-2">Selected Fee</h6>
                              <div id="fee-summary-content"></div>
                            </div>

                            <div class="form-group mb-3">
                              <label for="modal_payment_amount" class="form-label fw-bold">Payment Amount (₦) <span class="text-danger">*</span></label>
                              <input type="number" step="0.01" min="0.01" class="form-control form-control-lg" id="modal_payment_amount" name="payment_amount" placeholder="0.00" required disabled>
                              <small class="form-text text-muted">Maximum Payable: <span id="max-amount" class="fw-bold text-danger">₦0.00</span></small>
                            </div>

                            <div class="form-group mb-3">
                              <label for="modal_payment_method" class="form-label fw-bold">Payment Method <span class="text-danger">*</span></label>
                              <select class="form-select" id="modal_payment_method" name="payment_method" required disabled>
                                <option value="">Select Method</option>
                                <option value="cash">Cash</option>
                                <option value="bank_transfer">Bank Transfer</option>
                                <option value="pos">POS / Card</option>
                                <option value="cheque">Cheque</option>
                                <option value="online">Online Payment</option>
                              </select>
                            </div>

                            <div class="form-group mb-4">
                              <label for="modal_payment_description" class="form-label fw-bold">Payment Description</label>
                              <textarea class="form-control" id="modal_payment_description" name="payment_description" rows="3" placeholder="Optional notes or receipt remark" disabled></textarea>
                            </div>

                            <div class="d-grid">
                              <button type="submit" class="btn btn-make-payment btn-lg" id="modal-submit-btn" disabled>
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
                    <h4 class="text-success mt-3">No Outstanding Allocated Fees</h4>
                    <p class="text-muted">This student has no outstanding fee balances at the moment.</p>
                    <div class="d-flex justify-content-center gap-2 mt-3">
                      <a href="fee_structure.php?student_id=<?php echo (int) $student['id']; ?>" class="btn btn-primary">
                        <i class="fas fa-plus-circle me-1"></i> Allocate Fee Structure
                      </a>
                      <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="fas fa-times me-1"></i> Close
                      </button>
                    </div>
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

  <script src="/admin/assets/js/core/jquery-3.7.1.min.js"></script>
  <script src="/admin/assets/js/core/bootstrap.min.js"></script>
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
          const amountDue = parseFloat(feeItem.data('amount-due')) || 0;
          const amountPaid = parseFloat(feeItem.data('amount-paid')) || 0;
          const balance = parseFloat(feeItem.data('balance')) || 0;

          // Populate hidden fields
          $('#modal_fee_id').val(feeId);

          // Show and populate fee summary
          $('#fee-summary-content').html(`
            <strong>${feeName}</strong><br>
            <small class="text-muted">${session} ${term ? '- ' + term : ''}</small><br>
            <div class="mt-2">
              <div class="row text-center">
                <div class="col-4"><small class="text-muted">Total Due</small><br><strong>₦${amountDue.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2})}</strong></div>
                <div class="col-4"><small class="text-muted">Paid So Far</small><br><strong class="text-success">₦${amountPaid.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2})}</strong></div>
                <div class="col-4"><small class="text-muted">Balance</small><br><strong class="text-danger">₦${balance.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2})}</strong></div>
              </div>
            </div>
          `);
          $('#selected-fee-summary').show();

          // Set maximum payment amount and enable form fields
          $('#modal_payment_amount').attr('max', balance).val('').prop('disabled', false);
          $('#modal_payment_method').prop('disabled', false);
          $('#modal_payment_description').prop('disabled', false);
          $('#modal-submit-btn').prop('disabled', false);
          $('#max-amount').text('₦' + balance.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2}));

          // Validate payment amount on input
          $('#modal_payment_amount').off('input').on('input', function () {
            const paymentAmount = parseFloat($(this).val()) || 0;
            if (paymentAmount > balance) {
              $(this).val(balance);
              Swal.fire({
                title: "Amount Exceeded",
                text: `Payment amount cannot exceed the outstanding balance of ₦${balance.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2})}`,
                icon: "warning",
                confirmButtonColor: "#720922"
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
        $('#modal_payment_amount, #modal_payment_method, #modal_payment_description, #modal-submit-btn').prop('disabled', true);
      });

      // Form validation and confirmation for modal form
      $('#modalPaymentForm').on('submit', function (e) {
        e.preventDefault();

        const selectedFee = $('.fee-selector:checked');
        if (selectedFee.length === 0) {
          Swal.fire({
            title: "No Fee Selected",
            text: "Please select a fee to make a payment",
            icon: "error",
            confirmButtonColor: "#720922"
          });
          return false;
        }

        let amount = parseFloat($('#modal_payment_amount').val()) || 0;
        if (amount <= 0) {
          Swal.fire({
            title: "Invalid Amount",
            text: "Please enter a valid payment amount greater than 0",
            icon: "error",
            confirmButtonColor: "#720922"
          });
          return false;
        }

        const balance = parseFloat(selectedFee.closest('.fee-item').data('balance')) || 0;
        if (amount > (balance + 0.001)) {
          Swal.fire({
            title: "Amount Exceeded",
            text: `Payment amount cannot exceed the outstanding balance of ₦${balance.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2})}`,
            icon: "error",
            confirmButtonColor: "#720922"
          });
          return false;
        }

        const paymentMethod = $('#modal_payment_method').val();
        if (!paymentMethod) {
          Swal.fire({
            title: "Payment Method Required",
            text: "Please select a payment method",
            icon: "error",
            confirmButtonColor: "#720922"
          });
          return false;
        }

        const form = this;
        const feeName = selectedFee.closest('.fee-item').data('fee-name');
        const studentName = <?php echo json_encode(trim(($student['first_name'] ?? '') . ' ' . ($student['last_name'] ?? ''))); ?>;

        Swal.fire({
          title: "Confirm Payment",
          text: `Process payment of ₦${amount.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2})} for ${feeName} (${studentName})?`,
          icon: "question",
          showCancelButton: true,
          confirmButtonColor: "#720922",
          confirmButtonText: "Yes, Process Payment",
          cancelButtonText: "Cancel",
          allowOutsideClick: false
        }).then((result) => {
          if (result.isConfirmed) {
            Swal.fire({
              title: "Processing Payment",
              text: "Please wait...",
              icon: "info",
              allowOutsideClick: false,
              showConfirmButton: false
            });

            setTimeout(() => {
              $(form).off('submit');
              form.submit();
            }, 300);
          }
        });
      });
    });
  </script>

</body>

</html>
