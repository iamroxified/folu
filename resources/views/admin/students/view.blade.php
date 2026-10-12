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

// Handle payment form submission, fee charge addition, or deletion
$isPost = (isset($_SERVER['REQUEST_METHOD']) && strtoupper((string) $_SERVER['REQUEST_METHOD']) === 'POST') || (function_exists('request') && request()->isMethod('post'));
$hasProcessPayment = isset($_POST['process_payment']) || (function_exists('request') && request()->has('process_payment'));
$hasDeletePayment = isset($_POST['delete_payment']) || (function_exists('request') && request()->has('delete_payment'));
$hasAddCharge = isset($_POST['add_additional_charge']) || (function_exists('request') && request()->has('add_additional_charge'));
$hasDeleteCharge = isset($_POST['delete_additional_charge']) || (function_exists('request') && request()->has('delete_additional_charge'));

if ($isPost && $hasAddCharge) {
    $fee_name = trim((string) ($_POST['fee_name'] ?? request('fee_name') ?? ''));
    $charge_amount = (float) ($_POST['charge_amount'] ?? request('charge_amount') ?? 0);
    $reason = trim((string) ($_POST['reason'] ?? request('reason') ?? ''));

    if ($fee_name === '' || $charge_amount <= 0) {
        $error = 'Please enter a valid fee name and amount greater than 0.';
    } else {
        try {
            $studentModel = \App\Models\Student::find($studentId);
            if ($studentModel) {
                \App\Services\FeeAllocationService::addAdditionalCharge($studentModel, $fee_name, $charge_amount, $reason);
                $message = "Additional charge of ₦" . number_format($charge_amount, 2) . " ('{$fee_name}') added and calculated successfully in student profile.";
            }
        } catch (\Throwable $e) {
            $error = 'Failed to add additional charge: ' . $e->getMessage();
        }
    }
}

if ($isPost && $hasDeleteCharge) {
    $charge_id = (int) ($_POST['charge_id'] ?? request('charge_id') ?? 0);
    if ($charge_id > 0) {
        try {
            \App\Services\FeeAllocationService::removeAdditionalCharge($charge_id);
            $message = "Additional fee charge removed and balance recalculated successfully.";
        } catch (\Throwable $e) {
            $error = 'Failed to remove additional charge: ' . $e->getMessage();
        }
    }
}

if ($isPost && $hasDeletePayment) {
    $delete_payment_id = filter_input(INPUT_POST, 'payment_id', FILTER_VALIDATE_INT) ?: (int) ($_POST['payment_id'] ?? request('payment_id') ?? 0);

    if (!$delete_payment_id) {
        $error = 'Invalid payment transaction ID for deletion.';
    } else {
        try {
            $pdo->beginTransaction();

            // Fetch payment record
            $payment_stmt = $pdo->prepare("SELECT * FROM payments WHERE id = ? FOR UPDATE");
            $payment_stmt->execute([$delete_payment_id]);
            $payment_to_delete = $payment_stmt->fetch(PDO::FETCH_ASSOC);

            if (!$payment_to_delete) {
                throw new Exception('Payment record not found.');
            }

            $deleted_amount = (float) ($payment_to_delete['amount'] ?? 0);
            $pType = $payment_to_delete['payable_type'];
            $pId = (int) ($payment_to_delete['payable_id'] ?? 0);

            // Delete transaction from payments table
            $del_stmt = $pdo->prepare("DELETE FROM payments WHERE id = ?");
            $del_stmt->execute([$delete_payment_id]);

            if (in_array($pType, ['additional_charge', 'App\\Models\\AdditionalCharge'])) {
                $charge = \App\Models\AdditionalCharge::find($pId);
                if ($charge) {
                    $chargePayments = (float) \App\Models\Payment::where(function($q) use ($charge) {
                        $q->where('payable_type', 'additional_charge')
                          ->orWhere('payable_type', 'App\\Models\\AdditionalCharge');
                    })->where('payable_id', $charge->id)->where('status', 'completed')->sum('amount');

                    $charge->amount_paid = $chargePayments;
                    $charge->balance = max(0.00, (float) $charge->amount - $chargePayments);
                    $charge->status = ($charge->balance <= 0.001) ? 'paid' : ($charge->amount_paid > 0 ? 'partially_paid' : 'unpaid');
                    $charge->save();

                    if ($charge->studentFee) {
                        $charge->studentFee->recalculateTotals();
                    }
                }
            } else {
                $fee = \App\Models\StudentFee::find($pId);
                if ($fee) {
                    $fee->recalculateTotals();
                }
            }

            $pdo->commit();
            $message = "Payment transaction of ₦" . number_format($deleted_amount, 2) . " deleted successfully and fee balances updated.";

        } catch (Exception $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            $error = 'Failed to delete payment transaction: ' . $e->getMessage();
        }
    }
}

if ($isPost && $hasProcessPayment) {
    $payable_type = trim((string) ($_POST['payable_type'] ?? request('payable_type') ?? ''));
    $payable_id = filter_input(INPUT_POST, 'payable_id', FILTER_VALIDATE_INT) ?: (int) ($_POST['payable_id'] ?? request('payable_id') ?? 0);

    // Fallback if legacy form submission sent fee_id
    if ($payable_type === '' && isset($_POST['fee_id'])) {
        $payable_type = 'student_fee';
        $payable_id = (int) $_POST['fee_id'];
    }

    $payment_amount = (float) ($_POST['payment_amount'] ?? request('payment_amount') ?? 0);
    $payment_method = trim((string) ($_POST['payment_method'] ?? request('payment_method') ?? ''));
    $payment_description = trim((string) ($_POST['payment_description'] ?? request('payment_description') ?? ''));

    // Validation
    if ($payment_amount <= 0 || $payment_method === '') {
        $error = 'Please enter a valid payment amount greater than 0 and select a payment method.';
    } elseif (!$payable_id || !in_array($payable_type, ['student_fee', 'additional_charge'])) {
        $error = 'Please select a valid payable fee or extra charge item.';
    } else {
        try {
            $pdo->beginTransaction();

            $receipt_number = 'RCP' . date('Y') . str_pad(rand(1, 999999), 6, '0', STR_PAD_LEFT);
            $paymentMethodForStorage = ($payment_method === 'pos') ? 'card' : $payment_method;
            $studentFullName = trim(($student['first_name'] ?? '') . ' ' . ($student['last_name'] ?? ''));

            if ($payable_type === 'additional_charge') {
                // Lock and fetch additional_charges record
                $charge_stmt = $pdo->prepare("SELECT * FROM additional_charges WHERE id = ? AND student_id = ? FOR UPDATE");
                $charge_stmt->execute([$payable_id, $studentId]);
                $charge = $charge_stmt->fetch(PDO::FETCH_ASSOC);

                if (!$charge) {
                    throw new Exception('Additional charge not found or does not belong to this student.');
                }

                $chargeAmount = (float) ($charge['amount'] ?? 0);
                $chargePaid = (float) ($charge['amount_paid'] ?? 0);
                $chargeBalance = (float) ($charge['balance'] ?? max(0.00, $chargeAmount - $chargePaid));

                if ($payment_amount > ($chargeBalance + 0.001)) {
                    throw new Exception('Payment amount (₦' . number_format($payment_amount, 2) . ') cannot exceed the charge balance of ₦' . number_format($chargeBalance, 2));
                }

                $newChargePaid = $chargePaid + $payment_amount;
                $newChargeBalance = max(0.00, $chargeAmount - $newChargePaid);
                $chargeStatus = ($newChargeBalance <= 0.001) ? 'paid' : ($newChargePaid > 0 ? 'partially_paid' : 'unpaid');

                $update_charge_stmt = $pdo->prepare("UPDATE additional_charges SET amount_paid = ?, balance = ?, status = ?, updated_at = NOW() WHERE id = ?");
                $update_charge_stmt->execute([$newChargePaid, $newChargeBalance, $chargeStatus, $charge['id']]);

                // Insert into payments table with payable_type = 'additional_charge'
                $desc = $payment_description ?: ('Payment for ' . $charge['fee_name'] . ' - Receipt #' . $receipt_number);
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
                ) VALUES (?, 'additional_charge', ?, ?, ?, CURDATE(), ?, ?, ?, 'completed', NOW(), NOW())");

                $payment_insert_stmt->execute([
                    $receipt_number,
                    $charge['id'],
                    $payment_amount,
                    $paymentMethodForStorage,
                    $studentFullName,
                    $student['phone'] ?? null,
                    $desc
                ]);

                // Recalculate parent StudentFee totals
                if (!empty($charge['student_fee_id'])) {
                    $parentFee = \App\Models\StudentFee::find($charge['student_fee_id']);
                    if ($parentFee) {
                        $parentFee->recalculateTotals();
                    }
                }

                $pdo->commit();
                $payment_success = true;
                $message = "Payment of ₦" . number_format($payment_amount, 2) . " for '{$charge['fee_name']}' processed successfully! Receipt Number: " . $receipt_number;

            } else {
                // Base tuition student_fee payment
                $fee_stmt = $pdo->prepare("SELECT * FROM student_fees WHERE id = ? AND student_id = ? FOR UPDATE");
                $fee_stmt->execute([$payable_id, $studentId]);
                $existing_fee = $fee_stmt->fetch(PDO::FETCH_ASSOC);

                if (!$existing_fee) {
                    throw new Exception('Fee record not found or does not belong to this student.');
                }

                $baseFeeStmt = $pdo->prepare("SELECT COALESCE(SUM(amount), 0) FROM payments WHERE (payable_type = 'student_fee' OR payable_type = 'App\\\\Models\\\\StudentFee') AND payable_id = ? AND status = 'completed'");
                $baseFeeStmt->execute([$existing_fee['id']]);
                $currentBasePaid = (float) $baseFeeStmt->fetchColumn();

                $baseAmount = (float) ($existing_fee['base_amount'] ?? 0);
                $baseBalance = max(0.00, $baseAmount - $currentBasePaid);

                if ($payment_amount > ($baseBalance + 0.001)) {
                    throw new Exception('Payment amount (₦' . number_format($payment_amount, 2) . ') cannot exceed the base tuition balance of ₦' . number_format($baseBalance, 2));
                }

                // Insert into payments table
                $desc = $payment_description ?: ('Tuition Fee Payment - Receipt #' . $receipt_number);
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
                    $desc
                ]);

                // Recalculate totals on StudentFee
                $studentFeeModel = \App\Models\StudentFee::find($existing_fee['id']);
                if ($studentFeeModel) {
                    $studentFeeModel->recalculateTotals();
                }

                $pdo->commit();
                $payment_success = true;
                $message = "Payment of ₦" . number_format($payment_amount, 2) . " processed successfully! Receipt Number: " . $receipt_number;
            }

        } catch (Exception $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            $error = 'Payment processing failed: ' . $e->getMessage();
        }
    }
}

// Get student fee summary, additional charges, recent payment history, and outstanding fees
try {
    // 1. Link any unlinked additional charges to the student's active fee record
    $unlinked_charges_count = (int) $pdo->query("SELECT COUNT(*) FROM additional_charges WHERE student_id = {$studentId} AND (student_fee_id IS NULL OR student_fee_id = 0)")->fetchColumn();
    if ($unlinked_charges_count > 0) {
        $studentModel = \App\Models\Student::find($studentId);
        if ($studentModel) {
            $sf = \App\Models\StudentFee::where('student_id', $studentId)->latest('id')->first();
            if (!$sf) {
                $sf = \App\Services\FeeAllocationService::allocateFeeForStudent($studentModel, 2);
            }
            if ($sf) {
                $pdo->exec("UPDATE additional_charges SET student_fee_id = {$sf->id} WHERE student_id = {$studentId} AND (student_fee_id IS NULL OR student_fee_id = 0)");
                $sf->recalculateTotals();
            }
        }
    }

    // 2. Get student fee summary
    $fee_summary_stmt = $pdo->prepare("SELECT COALESCE(SUM(sf.amount_due), 0) as total_due, COALESCE(SUM(sf.amount_paid), 0) as total_paid, COALESCE(SUM(sf.balance), 0) as total_balance FROM student_fees sf WHERE sf.student_id = ?");
    $fee_summary_stmt->execute([$studentId]);
    $fee_summary = $fee_summary_stmt->fetch(PDO::FETCH_ASSOC);

    // 3. Get all additional charges for this student
    $additional_charges_stmt = $pdo->prepare("
        SELECT ac.*, u.name AS added_by_name, s.session_name, t.term_name
        FROM additional_charges ac
        LEFT JOIN users u ON ac.added_by = u.id
        LEFT JOIN academic_sessions s ON ac.session_id = s.id
        LEFT JOIN academic_terms t ON ac.term_id = t.id
        WHERE ac.student_id = ?
        ORDER BY ac.id DESC
    ");
    $additional_charges_stmt->execute([$studentId]);
    $additional_charges = $additional_charges_stmt->fetchAll(PDO::FETCH_ASSOC);

    // 4. Get recent payment history (both base tuition and individual extra charges)
    $payment_history_stmt = $pdo->prepare("
        SELECT 
            p.id AS payment_id,
            p.payment_reference AS receipt_number,
            p.payment_date,
            p.amount AS transaction_amount,
            p.payment_method,
            p.payable_type,
            p.payable_id,
            p.description AS payment_description,
            CASE 
                WHEN p.payable_type IN ('additional_charge', 'App\\Models\\AdditionalCharge') THEN CONCAT(ac.fee_name, ' (Extra Fee)')
                ELSE COALESCE(fs.name, fs.description, 'School Tuition Fee')
            END AS type_name,
            CASE 
                WHEN p.payable_type IN ('additional_charge', 'App\\Models\\AdditionalCharge') THEN 'Extra Charge'
                ELSE 'Tuition Fee'
            END AS item_category,
            CASE 
                WHEN p.payable_type IN ('additional_charge', 'App\\Models\\AdditionalCharge') THEN ac.amount
                ELSE sf.amount_due
            END AS amount_due,
            CASE 
                WHEN p.payable_type IN ('additional_charge', 'App\\Models\\AdditionalCharge') THEN ac.amount_paid
                ELSE sf.amount_paid
            END AS total_fee_paid,
            CASE 
                WHEN p.payable_type IN ('additional_charge', 'App\\Models\\AdditionalCharge') THEN ac.balance
                ELSE sf.balance
            END AS current_balance,
            CASE 
                WHEN p.payable_type IN ('additional_charge', 'App\\Models\\AdditionalCharge') THEN ac.status
                ELSE sf.status
            END AS fee_status,
            COALESCE(sess_sf.session_name, sess_ac.session_name, 'N/A') AS session_name,
            COALESCE(t_sf.term_name, t_ac.term_name, '') AS session_term
        FROM payments p
        LEFT JOIN student_fees sf ON (p.payable_type IN ('student_fee', 'App\\Models\\StudentFee') AND p.payable_id = sf.id)
        LEFT JOIN fee_structures fs ON sf.fee_structure_id = fs.id
        LEFT JOIN academic_sessions sess_sf ON fs.session_id = sess_sf.id
        LEFT JOIN academic_terms t_sf ON fs.term_id = t_sf.id
        LEFT JOIN additional_charges ac ON (p.payable_type IN ('additional_charge', 'App\\Models\\AdditionalCharge') AND p.payable_id = ac.id)
        LEFT JOIN academic_sessions sess_ac ON ac.session_id = sess_ac.id
        LEFT JOIN academic_terms t_ac ON ac.term_id = t_ac.id
        WHERE ((p.payable_type IN ('student_fee', 'App\\Models\\StudentFee') AND sf.student_id = :sid1)
            OR (p.payable_type IN ('additional_charge', 'App\\Models\\AdditionalCharge') AND ac.student_id = :sid2))
          AND p.status = 'completed'
        ORDER BY COALESCE(p.payment_date, p.created_at) DESC, p.id DESC
        LIMIT 50
    ");
    $payment_history_stmt->execute(['sid1' => $studentId, 'sid2' => $studentId]);
    $payment_history = $payment_history_stmt->fetchAll(PDO::FETCH_ASSOC);

    // 5. Get outstanding fees
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

    // 6. Build Individual Payable Items list (DO NOT lump sum tuition with additional charges)
    $payable_items = [];

    // A. Base Tuition Fees (Itemized per student fee record)
    foreach ($outstanding_fees as $fee) {
        $direct_fee_paid_stmt = $pdo->prepare("SELECT COALESCE(SUM(amount), 0) FROM payments WHERE (payable_type = 'student_fee' OR payable_type = 'App\\\\Models\\\\StudentFee') AND payable_id = ? AND status = 'completed'");
        $direct_fee_paid_stmt->execute([$fee['id']]);
        $base_fee_paid = (float) $direct_fee_paid_stmt->fetchColumn();

        $base_amount = (float) ($fee['base_amount'] ?? 0);
        $base_balance = max(0.00, $base_amount - $base_fee_paid);

        if ($base_balance > 0.001) {
            $payable_items[] = [
                'item_key' => 'fee_' . $fee['id'],
                'payable_type' => 'student_fee',
                'payable_id' => (int) $fee['id'],
                'title' => $fee['type_name'] ?? 'School Tuition Fee',
                'badge' => 'Tuition / Base Fee',
                'badge_class' => 'bg-primary text-white',
                'description' => $fee['fee_description'] ?? 'Base Term / Session Fee',
                'session_name' => $fee['session_name'] ?? 'Current Session',
                'term_name' => $fee['session_term'] ?? '',
                'amount_due' => $base_amount,
                'amount_paid' => $base_fee_paid,
                'balance' => $base_balance,
                'status' => ($base_balance <= 0.001) ? 'paid' : ($base_fee_paid > 0 ? 'partially_paid' : 'unpaid'),
            ];
        }
    }

    // B. Individual Additional Charges (Each extra fee charge is an independent payable item)
    foreach ($additional_charges as $charge) {
        $charge_amount = (float) ($charge['amount'] ?? 0);
        $charge_paid = (float) ($charge['amount_paid'] ?? 0);
        $charge_balance = (float) ($charge['balance'] ?? max(0.00, $charge_amount - $charge_paid));

        if ($charge_balance > 0.001) {
            $payable_items[] = [
                'item_key' => 'charge_' . $charge['id'],
                'payable_type' => 'additional_charge',
                'payable_id' => (int) $charge['id'],
                'title' => $charge['fee_name'],
                'badge' => 'Extra Charge',
                'badge_class' => 'bg-warning text-dark',
                'description' => !empty($charge['reason']) ? $charge['reason'] : 'Additional Fee Charge',
                'session_name' => $charge['session_name'] ?? 'Current Session',
                'term_name' => $charge['term_name'] ?? '',
                'amount_due' => $charge_amount,
                'amount_paid' => $charge_paid,
                'balance' => $charge_balance,
                'status' => $charge['status'] ?? 'unpaid',
            ];
        }
    }

} catch (PDOException $e) {
    $fee_summary = ['total_due' => 0, 'total_paid' => 0, 'total_balance' => 0];
    $additional_charges = [];
    $payment_history = [];
    $outstanding_fees = [];
    $payable_items = [];
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
                  <a href="/admin/edit_students.php?id=<?php echo (int) $student['id']; ?>" class="btn btn-light action-btn">
                    <i class="fas fa-edit me-2"></i>Edit Student
                  </a>
                  <a href="/admin/list_students.php" class="btn btn-light action-btn">
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
                <strong>Success!</strong><br>
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
                      <table class="datatable table table-borderless datatable">
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
                      <table class="datatable table table-borderless datatable">
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

              <!-- Additional Charges & Extra Fees -->
              <div class="card mb-4 shadow-sm border-warning">
                <div class="card-header bg-light d-flex justify-content-between align-items-center">
                  <h4 class="card-title mb-0 text-dark fw-bold">
                    <i class="fas fa-file-invoice-dollar me-2 text-warning"></i>Additional Charges & Extra Fees
                  </h4>
                  <div class="d-flex align-items-center gap-2">
                    <span class="badge bg-warning text-dark fw-bold"><?php echo count($additional_charges); ?> Extra Charges</span>
                    <button type="button" class="btn btn-warning btn-sm text-dark fw-bold" data-bs-toggle="modal" data-bs-target="#extraFeeModal">
                      <i class="fas fa-plus me-1"></i> Add Fee
                    </button>
                  </div>
                </div>
                <div class="card-body p-0">
                  <?php if (empty($additional_charges)): ?>
                    <div class="text-center py-4 text-muted">
                      <i class="fas fa-receipt fa-2x mb-2 d-block text-muted"></i>
                      No additional charges or extra fees recorded for this student.
                      <div class="mt-2">
                        <button type="button" class="btn btn-outline-warning btn-sm text-dark fw-bold" data-bs-toggle="modal" data-bs-target="#extraFeeModal">
                          <i class="fas fa-plus-circle me-1"></i> Add Extra Fee / Charge
                        </button>
                      </div>
                    </div>
                  <?php else: ?>
                    <div class="table-responsive">
                      <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                          <tr>
                            <th style="width: 40px;">#</th>
                            <th>Fee Name</th>
                            <th>Reason / Description</th>
                            <th>Amount Due (₦)</th>
                            <th>Paid (₦)</th>
                            <th>Balance (₦)</th>
                            <th>Session / Term</th>
                            <th>Date Added</th>
                            <th>Status</th>
                            <th class="text-end" style="min-width: 130px;">Action</th>
                          </tr>
                        </thead>
                        <tbody>
                          <?php 
                            $sn = 1; 
                            $total_extra_due = 0;
                            $total_extra_paid = 0;
                            $total_extra_bal = 0;
                            foreach ($additional_charges as $charge): 
                              $c_amount = (float) ($charge['amount'] ?? 0);
                              $c_paid = (float) ($charge['amount_paid'] ?? 0);
                              $c_bal = (float) ($charge['balance'] ?? max(0.00, $c_amount - $c_paid));
                              $total_extra_due += $c_amount;
                              $total_extra_paid += $c_paid;
                              $total_extra_bal += $c_bal;
                          ?>
                          <tr>
                            <td><?php echo $sn++; ?></td>
                            <td>
                              <strong class="text-dark"><?php echo htmlspecialchars((string) $charge['fee_name']); ?></strong>
                            </td>
                            <td>
                              <small class="text-muted"><?php echo htmlspecialchars((string) ($charge['reason'] ?? 'N/A')); ?></small>
                            </td>
                            <td class="fw-bold text-dark">
                              ₦<?php echo number_format($c_amount, 2); ?>
                            </td>
                            <td class="fw-bold text-success">
                              ₦<?php echo number_format($c_paid, 2); ?>
                            </td>
                            <td class="fw-bold text-danger">
                              ₦<?php echo number_format($c_bal, 2); ?>
                            </td>
                            <td>
                              <small>
                                <?php echo htmlspecialchars((string) ($charge['session_name'] ?? 'Current Session')); ?>
                                <?php if (!empty($charge['term_name'])): ?>
                                  (<?php echo htmlspecialchars((string) $charge['term_name']); ?>)
                                <?php endif; ?>
                              </small>
                            </td>
                            <td>
                              <small class="text-muted">
                                <?php echo !empty($charge['created_at']) ? date('M d, Y', strtotime((string) $charge['created_at'])) : 'N/A'; ?>
                              </small>
                            </td>
                            <td>
                              <span class="badge bg-<?php echo ($charge['status'] ?? 'unpaid') === 'paid' ? 'success' : (($charge['status'] ?? '') === 'partially_paid' ? 'info' : 'warning'); ?> text-dark">
                                <?php echo ucfirst((string) ($charge['status'] ?? 'unpaid')); ?>
                              </span>
                            </td>
                            <td class="text-end">
                              <div class="d-flex justify-content-end gap-1">
                                <?php if ($c_bal > 0.001): ?>
                                  <button type="button" 
                                          class="btn btn-sm btn-success text-white pay-item-direct-btn"
                                          data-item-key="charge_<?php echo (int) $charge['id']; ?>"
                                          title="Pay this specific extra charge">
                                    <i class="fas fa-credit-card me-1"></i> Pay
                                  </button>
                                <?php endif; ?>
                                <form method="POST" action="" class="d-inline" onsubmit="return confirm('Are you sure you want to remove this additional fee charge? Total due and outstanding balance will be recalculated automatically.');">
                                  <input type="hidden" name="delete_additional_charge" value="1">
                                  <input type="hidden" name="charge_id" value="<?php echo (int) $charge['id']; ?>">
                                  <button type="submit" class="btn btn-sm btn-outline-danger" title="Remove Charge">
                                    <i class="fas fa-trash"></i>
                                  </button>
                                </form>
                              </div>
                            </td>
                          </tr>
                          <?php endforeach; ?>
                        </tbody>
                        <tfoot class="table-light">
                          <tr>
                            <td colspan="3" class="fw-bold text-end">Total Extra Charges:</td>
                            <td class="fw-bold text-dark">₦<?php echo number_format($total_extra_due, 2); ?></td>
                            <td class="fw-bold text-success">₦<?php echo number_format($total_extra_paid, 2); ?></td>
                            <td class="fw-bold text-danger">₦<?php echo number_format($total_extra_bal, 2); ?></td>
                            <td colspan="4"></td>
                          </tr>
                        </tfoot>
                      </table>
                    </div>
                  <?php endif; ?>
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
                      <table class="datatable table table-hover mb-0 datatable">
                        <thead class="bg-light">
                          <tr>
                            <th style="width: 40px;">#</th>
                            <th>Fee Item</th>
                            <th>Category</th>
                            <th>Session / Term</th>
                            <th>Item Due (₦)</th>
                            <th>Paid in Txn (₦)</th>
                            <th>Current Balance (₦)</th>
                            <th>Status</th>
                            <th>Payment Date</th>
                            <th>Receipt</th>
                          </tr>
                        </thead>
                        <tbody>
                          <?php $sn = 1; ?><?php foreach ($payment_history as $payment): ?>
                          <tr>
                            <td><?php echo $sn++; ?></td>
                            <td>
                              <strong class="text-dark"><?php echo htmlspecialchars((string) ($payment['type_name'] ?? 'School Fee')); ?></strong>
                              <?php if (!empty($payment['payment_description'])): ?>
                                <br><small class="text-muted"><?php echo htmlspecialchars((string) $payment['payment_description']); ?></small>
                              <?php endif; ?>
                            </td>
                            <td>
                              <span class="badge bg-<?php echo ($payment['item_category'] ?? '') === 'Extra Charge' ? 'warning text-dark' : 'primary'; ?>">
                                <?php echo htmlspecialchars((string) ($payment['item_category'] ?? 'Tuition Fee')); ?>
                              </span>
                            </td>
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
                                      (($payment['fee_status'] ?? '') === 'partial' || ($payment['fee_status'] ?? '') === 'partially_paid' ? 'warning' : 'secondary'); 
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
                                <div class="d-flex gap-1 mt-1">
                                   <a href="/admin/print_receipt.php?receipt=<?php echo urlencode((string) $payment['receipt_number']); ?>" 
                                      target="_blank" 
                                      class="btn btn-sm btn-outline-primary">
                                       <i class="fas fa-print me-1"></i> Print
                                   </a>
                                   <form method="POST" action="" class="d-inline delete-payment-form" onsubmit="return confirmDeletePayment(event, this, '<?php echo htmlspecialchars((string)$payment['receipt_number']); ?>', '<?php echo number_format((float)($payment['transaction_amount'] ?? 0), 2); ?>');">
                                     <input type="hidden" name="delete_payment" value="1">
                                     <input type="hidden" name="payment_id" value="<?php echo (int)($payment['payment_id'] ?? 0); ?>">
                                     <button type="submit" class="btn btn-sm btn-outline-danger">
                                       <i class="fas fa-trash me-1"></i> Delete
                                     </button>
                                   </form>
                                 </div>
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
                    <a href="/admin/fee_structure.php?student_id=<?php echo (int) $student['id']; ?>" class="btn btn-outline-light text-white">
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
                        <?php if ((float) ($fee['additional_amount'] ?? 0) > 0): ?>
                          <br><span class="badge bg-warning text-dark mt-1" style="font-size: 11px;">
                            Includes +₦<?php echo number_format((float) $fee['additional_amount'], 2); ?> extra charges
                          </span>
                        <?php endif; ?>
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
                    <button type="button" class="btn btn-warning text-dark action-btn fw-bold" data-bs-toggle="modal" data-bs-target="#extraFeeModal">
                      <i class="fas fa-plus-circle me-2"></i>Add Extra Fee / Charge
                    </button>
                    <a href="/admin/fee_structure.php?student_id=<?php echo (int) $student['id']; ?>" class="btn btn-info action-btn text-white">
                      <i class="fas fa-file-invoice me-2"></i>Allocate Fee Structure
                    </a>
                    <a href="/admin/edit_students.php?id=<?php echo (int) $student['id']; ?>" class="btn btn-warning action-btn text-dark">
                      <i class="fas fa-edit me-2"></i>Edit Student Profile
                    </a>
                    <a href="/admin/print_profile.php?id=<?php echo (int) $student['id']; ?>" target="_blank" class="btn btn-secondary action-btn">
                      <i class="fas fa-print me-2"></i>Print Full Profile
                    </a>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- Modal: Add Extra Fee / Charge -->
          <div class="modal fade" id="extraFeeModal" tabindex="-1" aria-labelledby="extraFeeModalLabel" aria-hidden="true">
            <div class="modal-dialog">
              <div class="modal-content">
                <div class="modal-header bg-warning text-dark">
                  <h5 class="modal-title fw-bold" id="extraFeeModalLabel">
                    <i class="fas fa-plus-circle me-2"></i>Add Extra Fee / Charge
                  </h5>
                  <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form method="POST" action="">
                  <input type="hidden" name="add_additional_charge" value="1">
                  <div class="modal-body">
                    <p class="text-muted small">Add a custom extra charge (e.g. Extra Examination Fee, Bus Levy, Uniform Replacement) to this student's profile.</p>

                    <div class="mb-3">
                      <label for="modal_fee_name" class="form-label fw-bold">Fee Name <span class="text-danger">*</span></label>
                      <input type="text" class="form-control" name="fee_name" id="modal_fee_name" placeholder="e.g. Extra Exam Fee, Bus Levy" required>
                    </div>

                    <div class="mb-3">
                      <label for="modal_charge_amount" class="form-label fw-bold">Amount (₦) <span class="text-danger">*</span></label>
                      <input type="number" step="0.01" min="0.01" class="form-control" name="charge_amount" id="modal_charge_amount" placeholder="0.00" required>
                    </div>

                    <div class="mb-3">
                      <label for="modal_reason" class="form-label fw-bold">Reason / Description</label>
                      <textarea class="form-control" name="reason" id="modal_reason" rows="3" placeholder="Reason or explanation for this extra fee charge"></textarea>
                    </div>
                  </div>
                  <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-warning fw-bold text-dark">Add Extra Fee</button>
                  </div>
                </form>
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
                  <?php if (!empty($payable_items)): ?>
                  <!-- Individual Payable Items Selection -->
                  <div class="row">
                    <div class="col-lg-7">
                      <div class="d-flex justify-content-between align-items-center mb-2">
                        <h5 class="text-danger mb-0 fw-bold">
                          <i class="fas fa-list-check me-2"></i>Select Individual Fee / Charge
                        </h5>
                        <span class="badge bg-danger"><?php echo count($payable_items); ?> unpaid items</span>
                      </div>
                      <p class="text-muted small mb-3">Tuition fees and individual extra charges are handled separately (not lump-summed). Select an individual payable item below to make a payment towards it.</p>

                      <div class="payable-items-list" style="max-height: 480px; overflow-y: auto; padding-right: 5px;">
                        <?php foreach ($payable_items as $index => $item): ?>
                        <div class="card mb-3 payable-item-card border" 
                             id="card_<?php echo $item['item_key']; ?>"
                             data-item-key="<?php echo $item['item_key']; ?>"
                             data-payable-type="<?php echo $item['payable_type']; ?>"
                             data-payable-id="<?php echo (int) $item['payable_id']; ?>"
                             data-title="<?php echo htmlspecialchars($item['title']); ?>"
                             data-badge="<?php echo htmlspecialchars($item['badge']); ?>"
                             data-badge-class="<?php echo htmlspecialchars($item['badge_class']); ?>"
                             data-session="<?php echo htmlspecialchars($item['session_name']); ?>"
                             data-term="<?php echo htmlspecialchars($item['term_name']); ?>"
                             data-amount-due="<?php echo (float) $item['amount_due']; ?>"
                             data-amount-paid="<?php echo (float) $item['amount_paid']; ?>"
                             data-balance="<?php echo (float) $item['balance']; ?>" 
                             data-status="<?php echo htmlspecialchars($item['status']); ?>"
                             style="cursor: pointer; transition: all 0.2s ease;">
                          <div class="card-body py-3">
                            <div class="form-check">
                              <input class="form-check-input payable-selector" 
                                     type="radio" 
                                     name="selected_item" 
                                     id="item_radio_<?php echo $item['item_key']; ?>" 
                                     value="<?php echo $item['item_key']; ?>">
                              <label class="form-check-label w-100" for="item_radio_<?php echo $item['item_key']; ?>" style="cursor: pointer;">
                                <div class="d-flex justify-content-between align-items-start">
                                  <div>
                                    <div class="d-flex align-items-center gap-2 mb-1">
                                      <span class="badge <?php echo $item['badge_class']; ?>"><?php echo $item['badge']; ?></span>
                                      <h6 class="mb-0 text-primary fw-bold"><?php echo htmlspecialchars($item['title']); ?></h6>
                                    </div>
                                    <small class="text-muted d-block">
                                      <?php echo htmlspecialchars($item['description']); ?>
                                    </small>
                                    <small class="text-muted">
                                      <i class="far fa-calendar-alt me-1"></i><?php echo htmlspecialchars($item['session_name']); ?>
                                      <?php if (!empty($item['term_name'])): ?>
                                        - <?php echo htmlspecialchars($item['term_name']); ?>
                                      <?php endif; ?>
                                    </small>
                                  </div>
                                  <div class="text-end ps-2">
                                    <div class="small text-muted">Item Due</div>
                                    <div class="fw-bold text-dark">₦<?php echo number_format((float) $item['amount_due'], 2); ?></div>
                                    <div class="small text-success">Paid: ₦<?php echo number_format((float) $item['amount_paid'], 2); ?></div>
                                    <div class="small fw-bold text-danger">Balance: ₦<?php echo number_format((float) $item['balance'], 2); ?></div>
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
                      <div class="card border-primary shadow-sm">
                        <div class="card-header bg-light py-2">
                          <h6 class="mb-0 fw-bold text-dark">
                            <i class="fas fa-money-bill-wave me-2 text-primary"></i>Payment Details
                          </h6>
                        </div>
                        <div class="card-body">
                          <form method="POST" action="" id="modalPaymentForm">
                            <!-- Hidden fields for selected item context -->
                            <input type="hidden" id="modal_payable_type" name="payable_type" value="">
                            <input type="hidden" id="modal_payable_id" name="payable_id" value="">
                            <input type="hidden" id="modal_fee_id" name="fee_id" value="">
                            <input type="hidden" name="process_payment" value="1">

                            <!-- Selected Item Summary -->
                            <div id="selected-fee-summary" class="alert alert-light border border-primary mb-3" style="display: none;">
                              <div class="d-flex justify-content-between align-items-start mb-1">
                                <strong id="summary-title" class="text-primary fs-6"></strong>
                                <span id="summary-badge" class="badge"></span>
                              </div>
                              <div id="summary-session" class="small text-muted mb-2"></div>
                              <div class="row text-center border-top pt-2">
                                <div class="col-4">
                                  <small class="text-muted d-block" style="font-size: 11px;">Due</small>
                                  <strong id="summary-due" class="text-dark" style="font-size: 13px;">₦0.00</strong>
                                </div>
                                <div class="col-4">
                                  <small class="text-muted d-block" style="font-size: 11px;">Paid</small>
                                  <strong id="summary-paid" class="text-success" style="font-size: 13px;">₦0.00</strong>
                                </div>
                                <div class="col-4">
                                  <small class="text-muted d-block" style="font-size: 11px;">Balance</small>
                                  <strong id="summary-balance" class="text-danger" style="font-size: 13px;">₦0.00</strong>
                                </div>
                              </div>
                            </div>

                            <div class="form-group mb-3">
                              <label for="modal_payment_amount" class="form-label fw-bold">Payment Amount (₦) <span class="text-danger">*</span></label>
                              <input type="number" step="0.01" min="0.01" class="form-control form-control-lg fw-bold" id="modal_payment_amount" name="payment_amount" placeholder="0.00" required disabled>
                              <div class="d-flex justify-content-between align-items-center mt-1">
                                <small class="form-text text-muted">Max Payable: <span id="max-amount" class="fw-bold text-danger">₦0.00</span></small>
                                <button type="button" class="btn btn-link btn-sm p-0 text-decoration-none fw-bold" id="btn-pay-full" style="display: none; font-size: 12px;">Pay Full Balance</button>
                              </div>
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
                              <label for="modal_payment_description" class="form-label fw-bold">Payment Description / Notes</label>
                              <textarea class="form-control" id="modal_payment_description" name="payment_description" rows="2" placeholder="Optional notes or receipt remark" disabled></textarea>
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
                    <h4 class="text-success mt-3">All Fees & Charges Settled!</h4>
                    <p class="text-muted">This student has no outstanding fee or charge balances at the moment.</p>
                    <div class="d-flex justify-content-center gap-2 mt-3">
                      <a href="/admin/fee_structure.php?student_id=<?php echo (int) $student['id']; ?>" class="btn btn-primary">
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

  <script>
    $(document).ready(function () {
      let activeItemBalance = 0;

      // Handle item card click or radio selection
      function selectPayableItem(card) {
        $('.payable-item-card').removeClass('border-primary shadow-sm bg-light');
        card.addClass('border-primary shadow-sm bg-light');

        const radio = card.find('.payable-selector');
        radio.prop('checked', true);

        const payableType = card.data('payable-type');
        const payableId = card.data('payable-id');
        const title = card.data('title');
        const badge = card.data('badge');
        const badgeClass = card.data('badge-class');
        const session = card.data('session');
        const term = card.data('term');
        const amountDue = parseFloat(card.data('amount-due')) || 0;
        const amountPaid = parseFloat(card.data('amount-paid')) || 0;
        const balance = parseFloat(card.data('balance')) || 0;
        activeItemBalance = balance;

        // Populate hidden fields
        $('#modal_payable_type').val(payableType);
        $('#modal_payable_id').val(payableId);
        $('#modal_fee_id').val(payableId);

        // Populate summary
        $('#summary-title').text(title);
        $('#summary-badge').attr('class', 'badge ' + badgeClass).text(badge);
        $('#summary-session').text(`${session} ${term ? '- ' + term : ''}`);
        $('#summary-due').text('₦' + amountDue.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2}));
        $('#summary-paid').text('₦' + amountPaid.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2}));
        $('#summary-balance').text('₦' + balance.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2}));
        $('#selected-fee-summary').slideDown(200);

        // Set maximum payment amount and enable form fields
        $('#modal_payment_amount').attr('max', balance).val(balance.toFixed(2)).prop('disabled', false);
        $('#modal_payment_method').prop('disabled', false);
        $('#modal_payment_description').prop('disabled', false);
        $('#modal-submit-btn').prop('disabled', false);
        $('#max-amount').text('₦' + balance.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2}));
        $('#btn-pay-full').show();

        // Validate on input
        $('#modal_payment_amount').off('input').on('input', function () {
          const val = parseFloat($(this).val()) || 0;
          if (val > (activeItemBalance + 0.001)) {
            $(this).val(activeItemBalance.toFixed(2));
            Swal.fire({
              title: "Amount Exceeded",
              text: `Payment amount cannot exceed the balance of ₦${activeItemBalance.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2})}`,
              icon: "warning",
              confirmButtonColor: "#720922"
            });
          }
        });
      }

      // Delegate click on card
      $('.payable-item-card').on('click', function (e) {
        selectPayableItem($(this));
      });

      // Delegate change on radio
      $('.payable-selector').on('change', function () {
        selectPayableItem($(this).closest('.payable-item-card'));
      });

      // Pay Full button
      $('#btn-pay-full').on('click', function () {
        if (activeItemBalance > 0) {
          $('#modal_payment_amount').val(activeItemBalance.toFixed(2)).trigger('input');
        }
      });

      // Direct pay button from Additional Charges table
      $('.pay-item-direct-btn').on('click', function (e) {
        e.preventDefault();
        const key = $(this).data('item-key');
        const modalEl = document.getElementById('paymentModal');
        const bsModal = bootstrap.Modal.getInstance(modalEl) || new bootstrap.Modal(modalEl);
        bsModal.show();

        setTimeout(function () {
          const targetCard = $(`#card_${key}`);
          if (targetCard.length) {
            selectPayableItem(targetCard);
            targetCard[0].scrollIntoView({ behavior: 'smooth', block: 'nearest' });
          }
        }, 300);
      });

      // Reset modal when closed
      $('#paymentModal').on('hidden.bs.modal', function () {
        $('.payable-selector').prop('checked', false);
        $('.payable-item-card').removeClass('border-primary shadow-sm bg-light');
        $('#selected-fee-summary').hide();
        $('#btn-pay-full').hide();
        $('#modalPaymentForm')[0].reset();
        $('#modal_payable_type, #modal_payable_id, #modal_fee_id').val('');
        $('#modal_payment_amount, #modal_payment_method, #modal_payment_description, #modal-submit-btn').prop('disabled', true);
        activeItemBalance = 0;
      });

      // Form validation and confirmation for modal form
      $('#modalPaymentForm').on('submit', function (e) {
        e.preventDefault();

        const selectedRadio = $('.payable-selector:checked');
        if (selectedRadio.length === 0) {
          Swal.fire({
            title: "No Item Selected",
            text: "Please select an individual fee or extra charge item to make a payment",
            icon: "error",
            confirmButtonColor: "#720922"
          });
          return false;
        }

        const amount = parseFloat($('#modal_payment_amount').val()) || 0;
        if (amount <= 0) {
          Swal.fire({
            title: "Invalid Amount",
            text: "Please enter a valid payment amount greater than 0",
            icon: "error",
            confirmButtonColor: "#720922"
          });
          return false;
        }

        if (amount > (activeItemBalance + 0.001)) {
          Swal.fire({
            title: "Amount Exceeded",
            text: `Payment amount cannot exceed the balance of ₦${activeItemBalance.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2})}`,
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
        const card = selectedRadio.closest('.payable-item-card');
        const itemTitle = card.data('title');
        const itemBadge = card.data('badge');
        const studentName = <?php echo json_encode(trim(($student['first_name'] ?? '') . ' ' . ($student['last_name'] ?? ''))); ?>;

        Swal.fire({
          title: "Confirm Payment",
          text: `Process payment of ₦${amount.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2})} for ${itemTitle} (${itemBadge}) for ${studentName}?`,
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
  
    function confirmDeletePayment(e, form, receiptNo, amountStr) {
      e.preventDefault();
      Swal.fire({
        title: "Delete Payment Transaction?",
        text: `Are you sure you want to delete payment receipt #${receiptNo} (₦${amountStr})? This action will reverse the payment on the student's fee balance.`,
        icon: "warning",
        showCancelButton: true,
        confirmButtonColor: "#d33",
        cancelButtonColor: "#3085d6",
        confirmButtonText: "Yes, Delete Payment",
        cancelButtonText: "Cancel"
      }).then((result) => {
        if (result.isConfirmed) {
          form.submit();
        }
      });
      return false;
    }
</script>

</body>

</html>
