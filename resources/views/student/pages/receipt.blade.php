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
    "SELECT p.*,
            p.payment_reference AS receipt_number,
            p.amount AS amount_paid,
            p.description AS payment_description,
            fs.id AS fee_structure_link,
            fs.name AS fee_name,
            fs.description AS fee_description,
            fs.fee_type,
            ac.session_name,
            t.term_name AS session_term
     FROM payments p
     JOIN student_fees sf ON p.payable_type = 'student_fee' AND p.payable_id = sf.id
     LEFT JOIN fee_structures fs ON sf.fee_structure_id = fs.id
     LEFT JOIN academic_sessions ac ON fs.session_id = ac.id
     LEFT JOIN terms t ON fs.term_id = t.id
     WHERE p.id = ? AND sf.student_id = ? AND p.status = 'completed'
     LIMIT 1",
    [$paymentId, (int) $student['id']]
)->fetch(PDO::FETCH_ASSOC);

if (!$payment) {
    header('Location: /student/payments.php');
    exit;
}

try {
    $settings_stmt = QueryDB("SELECT * FROM school_settings LIMIT 1");
    $schoolSettings = $settings_stmt ? $settings_stmt->fetch(PDO::FETCH_ASSOC) : [];
} catch (\Throwable $t) {
    $schoolSettings = [];
}

$schoolName = !empty($schoolSettings['school_name']) ? $schoolSettings['school_name'] : 'Folu International Schools';
$schoolAddress = !empty($schoolSettings['school_address']) ? $schoolSettings['school_address'] : 'P.O Box 37, Itedo-Ijowa, Isanlu Kogi State Nigeria';
$schoolPhone = !empty($schoolSettings['school_phone']) ? $schoolSettings['school_phone'] : '+234 816 535 4191';
$schoolEmail = !empty($schoolSettings['school_email']) ? $schoolSettings['school_email'] : 'info@foluinternationalschools.com.ng';
$schoolMotto = !empty($schoolSettings['school_motto']) ? $schoolSettings['school_motto'] : 'Knowledge & Discipline';
$schoolLogo = !empty($schoolSettings['school_logo']) ? (str_starts_with($schoolSettings['school_logo'], '/') ? $schoolSettings['school_logo'] : '/storage/' . $schoolSettings['school_logo']) : '/images/folu-logo.png';
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta http-equiv="X-UA-Compatible" content="IE=edge" />
  <title>Student Receipt</title>
  @include('admin.partials.links')
  <style>
    .card {
      position: relative;
      overflow: hidden;
    }
    .watermark-container {
      position: absolute;
      top: 52%;
      left: 50%;
      transform: translate(-50%, -50%) rotate(-24deg);
      pointer-events: none;
      z-index: 1;
      display: flex;
      flex-direction: column;
      align-items: center;
      justify-content: center;
      user-select: none;
      opacity: 0.12;
      width: 100%;
    }
    .watermark-box {
      border: 6px dashed #15803d;
      border-radius: 20px;
      padding: 16px 48px;
      text-align: center;
      background: rgba(240, 253, 244, 0.4);
    }
    .watermark-main-text {
      font-size: 58px;
      font-weight: 900;
      font-family: 'Arial Black', Impact, 'Segoe UI Black', sans-serif;
      color: #15803d;
      letter-spacing: 6px;
      line-height: 1;
      text-transform: uppercase;
      white-space: nowrap;
    }
    .watermark-sub-text {
      font-size: 13px;
      font-weight: 800;
      color: #166534;
      letter-spacing: 5px;
      text-transform: uppercase;
      margin-top: 8px;
      border-top: 2px solid #166534;
      padding-top: 4px;
      white-space: nowrap;
    }
    .official-stamp-badge {
      display: inline-block;
      padding: 6px 16px;
      border: 3.5px double #15803d;
      border-radius: 10px;
      background: rgba(240, 253, 244, 0.95);
      box-shadow: 0 0 0 2px #bbf7d0, 0 4px 12px rgba(21, 128, 61, 0.15);
      transform: rotate(-5deg);
      text-align: center;
      user-select: none;
    }
    .stamp-badge-inner {
      display: flex;
      flex-direction: column;
      align-items: center;
    }
    .stamp-school-name {
      font-size: 8.5px;
      font-weight: 800;
      letter-spacing: 1.5px;
      color: #166534;
      text-transform: uppercase;
    }
    .stamp-stars {
      font-size: 8px;
      letter-spacing: 3px;
      color: #15803d;
      margin: 1px 0;
    }
    .stamp-status-word {
      font-size: 17px;
      font-weight: 900;
      letter-spacing: 2px;
      color: #15803d;
      border-top: 2px solid #15803d;
      border-bottom: 2px solid #15803d;
      padding: 2px 10px;
      margin: 2px 0;
      text-transform: uppercase;
      font-family: 'Arial Black', Impact, sans-serif;
      line-height: 1.1;
    }
    .stamp-date-line {
      font-size: 9px;
      font-weight: 800;
      letter-spacing: 1px;
      color: #166534;
      margin-top: 2px;
    }
    .stamp-sub-text {
      font-size: 7.5px;
      font-weight: 700;
      letter-spacing: 1px;
      color: #15803d;
      opacity: 0.9;
      text-transform: uppercase;
    }
    .stamp-box-completed {
      border: 2px solid #15803d !important;
      background: #f0fdf4 !important;
      color: #15803d !important;
      padding: 8px 12px !important;
      border-radius: 8px;
      box-shadow: 0 0 0 2px #dcfce7 !important;
      transform: rotate(-3deg);
      width: 170px;
      text-align: center;
    }
    .seal-ring {
      border: 1.5px dashed #16a34a;
      border-radius: 6px;
      padding: 6px 4px;
    }
    .seal-icon {
      font-size: 16px;
      font-weight: 900;
      color: #15803d;
      line-height: 1;
    }
    .seal-title {
      font-size: 9.5px;
      font-weight: 900;
      letter-spacing: 1px;
      color: #15803d;
      margin-top: 2px;
    }
    .seal-org {
      font-size: 8px;
      font-weight: 700;
      color: #166534;
      letter-spacing: 0.5px;
    }
    .seal-date {
      font-size: 8px;
      font-weight: 700;
      color: #15803d;
      margin-top: 1px;
    }
    .seal-caption {
      font-size: 8px;
      font-weight: 800;
      letter-spacing: 1px;
      color: #15803d;
      margin-top: 4px;
      text-transform: uppercase;
    }
    @media print {
      * {
        -webkit-print-color-adjust: exact !important;
        print-color-adjust: exact !important;
      }
      .no-print { display: none !important; }
      body { background: #fff !important; }
      .watermark-container { opacity: 0.14 !important; }
      .official-stamp-badge {
        border-color: #15803d !important;
        background-color: #f0fdf4 !important;
      }
      .stamp-box-completed {
        border-color: #15803d !important;
        background-color: #f0fdf4 !important;
      }
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
      <div class="card-body p-4" style="position: relative;">
        <!-- Background Watermark -->
        <div class="watermark-container" aria-hidden="true">
          <div class="watermark-box">
            <div class="watermark-main-text">PAYMENT COMPLETED</div>
            <div class="watermark-sub-text">OFFICIAL RECEIPT • VALIDATED</div>
          </div>
        </div>

        <div style="position: relative; z-index: 2;">
          <div class="d-flex justify-content-between align-items-start mb-4">
            <div>
              <h3 class="mb-1 text-primary font-weight-bold"><?php echo htmlspecialchars($schoolName); ?></h3>
              <p class="mb-1 text-dark fw-bold">Official Fee Payment Receipt</p>
              <p class="mb-0 text-muted small"><?php echo htmlspecialchars($schoolAddress); ?></p>
              <p class="mb-0 text-muted small"><i class="fas fa-phone me-1"></i> <?php echo htmlspecialchars($schoolPhone); ?> | <i class="fas fa-envelope me-1"></i> <?php echo htmlspecialchars($schoolEmail); ?></p>
              <small class="fst-italic text-secondary">Motto: <?php echo htmlspecialchars($schoolMotto); ?></small>
            </div>
            <div class="d-flex flex-column align-items-end gap-2">
              <img src="<?php echo htmlspecialchars($schoolLogo); ?>" alt="school logo" style="max-width: 90px; max-height: 90px; object-fit: contain;" onerror="this.src='/images/folu-logo.png';">
              <div class="official-stamp-badge">
                <div class="stamp-badge-inner">
                  <div class="stamp-school-name"><?php echo htmlspecialchars($schoolName); ?></div>
                  <div class="stamp-stars">★ ★ ★ ★ ★</div>
                  <div class="stamp-status-word">PAID &amp; COMPLETED</div>
                  <div class="stamp-date-line">OFFICIALLY VERIFIED</div>
                  <div class="stamp-sub-text">ACCOUNTS &amp; BURSARY</div>
                </div>
              </div>
            </div>
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
                  <td>₦<?php echo number_format((float) ($payment['amount_paid'] ?? 0), 2); ?></td>
                  <td><?php echo htmlspecialchars(ucfirst(str_replace('_', ' ', (string) ($payment['payment_method'] ?? 'n/a')))); ?></td>
                  <td><span class="badge bg-success text-white fw-bold">✓ Completed</span></td>
                </tr>
              </tbody>
            </table>
          </div>

          <!-- Footer Signature & Stamp -->
          <div class="d-flex justify-content-between align-items-end mt-4 pt-3 border-top">
            <div>
              <p class="mb-1 text-muted small"><strong>Thank you for your payment!</strong></p>
              <p class="mb-0 text-muted" style="font-size: 11px;">Officially generated electronic receipt. Generated on <?php echo date('Y-m-d H:i:s'); ?>.</p>
            </div>
            <div class="stamp-box-completed">
              <div class="seal-ring">
                <div class="seal-icon">✓</div>
                <div class="seal-title">PAYMENT COMPLETED</div>
                <div class="seal-org"><?php echo htmlspecialchars($schoolName); ?></div>
                <div class="seal-date"><?php echo date('d-M-Y', strtotime($payment['payment_date'] ?? $payment['created_at'] ?? 'now')); ?></div>
              </div>
              <div class="seal-caption">BURSARY VALIDATED</div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</body>
</html>
