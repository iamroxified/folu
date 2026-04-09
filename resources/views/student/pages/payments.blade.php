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

$selectedSessionId = (int) ($_GET['academic_session_link'] ?? (get_current_academic_session_id() ?? (int) ($student['academic_session_link'] ?? 0)));
$sessions = QueryDB('SELECT * FROM academic_sessions ORDER BY id DESC')->fetchAll();
$payments = get_student_payment_history((int) $student['id'], $selectedSessionId);
$feeRecords = get_student_fee_records((int) $student['id'], $selectedSessionId);
$outstandingFees = array_values(array_filter($feeRecords, static fn ($row) => (float) ($row['balance'] ?? 0) > 0));
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta http-equiv="X-UA-Compatible" content="IE=edge" />
  <title>Student Payments</title>
  @include('admin.partials.links')
</head>
<body>
  <div class="wrapper">
    @include('student.partials.sidebar')
    <div class="main-panel">
      @include('student.partials.header')
      <div class="container">
        <div class="page-inner">
          <div class="d-flex align-items-left flex-column flex-md-row">
            <h2 class="text-dark pb-2 fw-bold">Payments & Receipts</h2>
          </div>

          <div class="card">
            <div class="card-header"><div class="card-title">Session Filter</div></div>
            <div class="card-body">
              <form method="GET" class="row">
                <div class="col-md-10">
                  <label for="academic_session_link">Academic Session</label>
                  <select class="form-control" id="academic_session_link" name="academic_session_link">
                    <?php foreach ($sessions as $session): ?>
                      <option value="<?php echo (int) $session['id']; ?>" <?php echo $selectedSessionId === (int) $session['id'] ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars((string) ($session['session_name'] . ' - ' . session_term_label($session['session_term'] ?? ''))); ?>
                      </option>
                    <?php endforeach; ?>
                  </select>
                </div>
                <div class="col-md-2 d-flex align-items-end">
                  <button type="submit" class="btn btn-primary w-100">Load</button>
                </div>
              </form>
            </div>
          </div>

          <?php if (!empty($outstandingFees)): ?>
            <div class="card mt-3">
              <div class="card-header"><div class="card-title">Outstanding Fees</div></div>
              <div class="card-body">
                <div class="table-responsive">
                  <table class="table table-bordered table-striped">
                    <thead><tr><th>Fee Item</th><th>Amount Due</th><th>Paid</th><th>Balance</th><th>Status</th></tr></thead>
                    <tbody>
                      <?php foreach ($outstandingFees as $fee): ?>
                        <tr>
                          <td><?php echo htmlspecialchars((string) ($fee['fee_description'] ?: ('Fee #' . $fee['fee_structure_link']))); ?></td>
                          <td>N<?php echo number_format((float) ($fee['amount_due'] ?? 0), 2); ?></td>
                          <td>N<?php echo number_format((float) ($fee['amount_paid'] ?? 0), 2); ?></td>
                          <td>N<?php echo number_format((float) ($fee['balance'] ?? 0), 2); ?></td>
                          <td><span class="badge badge-warning"><?php echo htmlspecialchars(ucfirst((string) ($fee['status'] ?? 'pending'))); ?></span></td>
                        </tr>
                      <?php endforeach; ?>
                    </tbody>
                  </table>
                </div>
              </div>
            </div>
          <?php endif; ?>

          <div class="card mt-3">
            <div class="card-header"><div class="card-title">Payment History</div></div>
            <div class="card-body">
              <?php if (empty($payments)): ?>
                <div class="alert alert-info mb-0">No payment records were found for this session.</div>
              <?php else: ?>
                <div class="table-responsive">
                  <table class="table table-bordered table-striped">
                    <thead><tr><th>Date</th><th>Fee Item</th><th>Amount Paid</th><th>Method</th><th>Receipt</th><th>Action</th></tr></thead>
                    <tbody>
                      <?php foreach ($payments as $payment): ?>
                        <tr>
                          <td><?php echo htmlspecialchars((string) ($payment['payment_date'] ?? $payment['created_at'] ?? '')); ?></td>
                          <td><?php echo htmlspecialchars((string) ($payment['fee_description'] ?: ('Fee #' . $payment['fee_structure_link']))); ?></td>
                          <td>N<?php echo number_format((float) ($payment['amount_paid'] ?? 0), 2); ?></td>
                          <td><?php echo htmlspecialchars(ucfirst(str_replace('_', ' ', (string) ($payment['payment_method'] ?? 'n/a')))); ?></td>
                          <td><?php echo htmlspecialchars((string) ($payment['receipt_number'] ?? 'Pending')); ?></td>
                          <td><a href="{{ url('/student/receipt.php?id=' . $payment['id']) }}" class="btn btn-sm btn-primary">Print Receipt</a></td>
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
      @include('admin.partials.footer')
</body>
</html>
