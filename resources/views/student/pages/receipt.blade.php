<?php
require_once base_path('db/config.php');
require_once base_path('db/functions.php');

if (!isset($_SESSION['student_user_id'], $_SESSION['student_id'])) {
    header('Location: /student/login.php');
    exit;
}

$student = get_student_profile_by_user_id((int) $_SESSION['student_user_id']);

if (!$student) {
    header('Location: /student/logout.php');
    exit;
}

$paymentId = (int) ($_GET['id'] ?? 0);
$payment = QueryDB(
    'SELECT sp.*, f.fee_name, f.fee_description, ac.session_name, at.term_name AS session_term
     FROM student_payments sp
     LEFT JOIN fees f ON sp.fee_structure_link = f.id
     LEFT JOIN academic_sessions ac ON sp.academic_session_link = ac.id
     LEFT JOIN academic_terms at ON sp.term_link = at.id
     WHERE sp.id = ? AND sp.student_link = ?
     LIMIT 1',
    [$paymentId, (int) $student['id']]
)->fetch(PDO::FETCH_ASSOC);

if (!$payment) {
    header('Location: /student/payments.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta http-equiv="X-UA-Compatible" content="IE=edge" />
  <title>Student Receipt</title>
  @include('admin.partials.links')
  <style>
    @media print {
      .no-print { display: none !important; }
      body { background: #fff !important; }
    }
  </style>
</head>
<body class="bg-light">
  <div class="container py-4">
    <div class="mb-3 no-print">
      <a href="{{ url('/student/payments.php') }}" class="btn btn-secondary">Back to Payments</a>
      <button type="button" class="btn btn-primary" onclick="window.print()">Print Receipt</button>
    </div>
    <div class="card shadow-sm">
      <div class="card-body p-4">
        <div class="d-flex justify-content-between align-items-start mb-4">
          <div>
            <h3 class="mb-1">Fee Payment Receipt</h3>
            <p class="mb-0 text-muted">FIMOCOL School Management System</p>
          </div>
          <img src="/images/folu_logo.jpg" alt="school logo" style="width: 80px; height: 80px; object-fit: contain;">
        </div>
        <hr>
        <div class="row">
          <div class="col-md-6">
            <p><strong>Student:</strong> <?php echo htmlspecialchars(trim(($student['first_name'] ?? '') . ' ' . ($student['last_name'] ?? ''))); ?></p>
            <p><strong>Admission No:</strong> <?php echo htmlspecialchars((string) $student['admission_no']); ?></p>
            <p><strong>Class:</strong> <?php echo htmlspecialchars(trim(($student['class_name'] ?? '') . ' ' . ($student['class_arm'] ?? ''))); ?></p>
          </div>
          <div class="col-md-6">
            <p><strong>Receipt Number:</strong> <?php echo htmlspecialchars((string) ($payment['receipt_number'] ?? 'Pending')); ?></p>
            <p><strong>Payment Date:</strong> <?php echo htmlspecialchars((string) ($payment['payment_date'] ?? $payment['created_at'] ?? '')); ?></p>
            <p><strong>Session:</strong> <?php echo htmlspecialchars(trim(($payment['session_name'] ?? '') . ' - ' . session_term_label($payment['session_term'] ?? ''))); ?></p>
          </div>
        </div>
        <div class="table-responsive mt-3">
          <table class="table table-bordered">
            <thead><tr><th>Fee Item</th><th>Amount Paid</th><th>Method</th><th>Status</th></tr></thead>
            <tbody>
              <tr>
                <td><?php echo htmlspecialchars((string) ($payment['fee_description'] ?: ('Fee #' . $payment['fee_structure_link']))); ?></td>
                <td>N<?php echo number_format((float) ($payment['amount_paid'] ?? 0), 2); ?></td>
                <td><?php echo htmlspecialchars(ucfirst(str_replace('_', ' ', (string) ($payment['payment_method'] ?? 'n/a')))); ?></td>
                <td><?php echo htmlspecialchars(ucfirst((string) ($payment['status'] ?? 'paid'))); ?></td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
</body>
</html>
