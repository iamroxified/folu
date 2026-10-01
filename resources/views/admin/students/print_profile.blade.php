<?php
if (session_status() === PHP_SESSION_NONE) {
    @session_start();
}

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

if (!isset($_SESSION['adid']) && !auth()->check()) {
    if (!headers_sent()) {
        header('Location: /admin/login.php');
        exit;
    }
}

$studentId = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT) ?: (int) ($_GET['id'] ?? request('id') ?? 0);
if (!$studentId) {
    die('Student ID is required');
}

// Fetch student details
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
    die('Student record not found');
}

// Calculate age if DOB exists
$age = 'N/A';
if (!empty($student['date_of_birth'])) {
    try {
        $dob = new DateTime($student['date_of_birth']);
        $now = new DateTime();
        $age = $dob->diff($now)->y . ' Years';
    } catch (\Throwable $t) {}
}

// Fetch parent details
$parent = null;
try {
    $parent_stmt = $pdo->prepare(
        "SELECT p.* 
         FROM parents p
         JOIN student_parents sp ON sp.parent_id = p.id
         WHERE sp.student_id = ?
         LIMIT 1"
    );
    $parent_stmt->execute([$studentId]);
    $parent = $parent_stmt->fetch(PDO::FETCH_ASSOC);
} catch (\Throwable $t) {}

// Fetch fee summary & history
try {
    $fee_summary_stmt = $pdo->prepare(
        "SELECT COALESCE(SUM(amount_due), 0) AS total_due,
                COALESCE(SUM(amount_paid), 0) AS total_paid,
                COALESCE(SUM(balance), 0) AS total_balance
         FROM student_fees
         WHERE student_id = ?"
    );
    $fee_summary_stmt->execute([$studentId]);
    $fee_summary = $fee_summary_stmt->fetch(PDO::FETCH_ASSOC);

    $allocated_fees_stmt = $pdo->prepare(
        "SELECT sf.*, COALESCE(fs.name, fs.description, 'School Fee') AS fee_name
         FROM student_fees sf
         LEFT JOIN fee_structures fs ON sf.fee_structure_id = fs.id
         WHERE sf.student_id = ?
         ORDER BY sf.id DESC"
    );
    $allocated_fees_stmt->execute([$studentId]);
    $allocated_fees = $allocated_fees_stmt->fetchAll(PDO::FETCH_ASSOC);

    $payments_stmt = $pdo->prepare(
        "SELECT p.*, COALESCE(fs.name, fs.description, 'School Fee') AS fee_name
         FROM payments p
         JOIN student_fees sf ON p.payable_type = 'student_fee' AND p.payable_id = sf.id
         LEFT JOIN fee_structures fs ON sf.fee_structure_id = fs.id
         WHERE sf.student_id = ? AND p.status = 'completed'
         ORDER BY COALESCE(p.payment_date, p.created_at) DESC
         LIMIT 10"
    );
    $payments_stmt->execute([$studentId]);
    $payment_history = $payments_stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (\Throwable $t) {
    $fee_summary = ['total_due' => 0, 'total_paid' => 0, 'total_balance' => 0];
    $allocated_fees = [];
    $payment_history = [];
}

$attendancePercentage = student_attendance_percentage($studentId);
$gradeAverage = student_grade_average($studentId);

$studentFullName = trim(($student['first_name'] ?? '') . ' ' . ($student['other_names'] ?? '') . ' ' . ($student['last_name'] ?? ''));

?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Student Profile Record - <?php echo htmlspecialchars($studentFullName); ?></title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <style>
    :root {
      --brand-color: #720922;
      --brand-dark: #500618;
      --accent-gold: #d4af37;
    }

    body {
      font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
      background-color: #f4f6f9;
      color: #333;
      padding-bottom: 40px;
    }

    .action-bar {
      background: #ffffff;
      border-bottom: 2px solid #e2e8f0;
      padding: 15px 0;
      box-shadow: 0 2px 4px rgba(0,0,0,0.05);
      margin-bottom: 30px;
    }

    .profile-card {
      background: #ffffff;
      border-radius: 12px;
      box-shadow: 0 4px 15px rgba(0, 0, 0, 0.08);
      padding: 30px;
      margin: 0 auto;
      max-width: 950px;
      border-top: 6px solid var(--brand-color);
    }

    .school-header {
      border-bottom: 2px dashed #cbd5e1;
      padding-bottom: 20px;
      margin-bottom: 25px;
    }

    .school-logo {
      max-height: 90px;
      width: auto;
    }

    .school-title {
      color: var(--brand-color);
      font-weight: 800;
      letter-spacing: 0.5px;
      font-size: 1.8rem;
    }

    .document-title {
      background: var(--brand-color);
      color: #ffffff;
      font-weight: 700;
      font-size: 1.1rem;
      padding: 8px 18px;
      border-radius: 20px;
      display: inline-block;
      letter-spacing: 1px;
    }

    .section-header {
      background: #f8fafc;
      color: var(--brand-color);
      font-weight: 700;
      font-size: 1rem;
      padding: 10px 15px;
      border-left: 4px solid var(--brand-color);
      border-radius: 4px;
      margin-top: 25px;
      margin-bottom: 15px;
      text-transform: uppercase;
      letter-spacing: 0.5px;
    }

    .info-table td {
      padding: 8px 12px;
      font-size: 0.95rem;
    }

    .info-label {
      font-weight: 600;
      color: #475569;
      width: 38%;
    }

    .info-value {
      color: #0f172a;
      font-weight: 500;
    }

    .student-photo {
      width: 140px;
      height: 140px;
      object-fit: cover;
      border-radius: 10px;
      border: 3px solid var(--brand-color);
      box-shadow: 0 3px 8px rgba(0,0,0,0.15);
    }

    .photo-placeholder {
      width: 140px;
      height: 140px;
      border-radius: 10px;
      border: 3px solid var(--brand-color);
      background: #f1f5f9;
      display: flex;
      align-items: center;
      justify-content: center;
      color: #94a3b8;
      font-size: 3.5rem;
    }

    .badge-status {
      font-size: 0.85rem;
      padding: 5px 12px;
      border-radius: 12px;
    }

    .summary-box {
      background: #f8fafc;
      border: 1px solid #e2e8f0;
      border-radius: 8px;
      padding: 15px;
      text-align: center;
    }

    .signature-box {
      margin-top: 40px;
      padding-top: 20px;
      border-top: 1px solid #cbd5e1;
    }

    @media print {
      .action-bar {
        display: none !important;
      }
      body {
        background-color: #ffffff;
        padding: 0;
      }
      .profile-card {
        box-shadow: none;
        border-radius: 0;
        max-width: 100%;
        padding: 15px;
        border-top: none;
      }
      .section-header {
        background-color: #f1f5f9 !important;
        -webkit-print-color-adjust: exact;
        print-color-adjust: exact;
      }
      .document-title {
        background-color: var(--brand-color) !important;
        color: #ffffff !important;
        -webkit-print-color-adjust: exact;
        print-color-adjust: exact;
      }
    }
  </style>
</head>
<body>

  <!-- Top Action Bar -->
  <div class="action-bar">
    <div class="container d-flex justify-content-between align-items-center" style="max-width: 950px;">
      <div>
        <a href="view_students.php?id=<?php echo (int)$student['id']; ?>" class="btn btn-outline-secondary">
          <i class="fas fa-arrow-left me-2"></i>Back to Student Profile
        </a>
      </div>
      <div class="d-flex gap-2">
        <button onclick="window.print()" class="btn btn-primary" style="background-color: #720922; border-color: #720922;">
          <i class="fas fa-print me-2"></i>Print Full Profile
        </button>
      </div>
    </div>
  </div>

  <div class="container">
    <div class="profile-card">
      
      <!-- School Header & Branding -->
      <div class="school-header text-center">
        <div class="row align-items-center">
          <div class="col-3 text-start">
            <img src="/images/folu-logo.png" alt="Folu International Schools Logo" class="school-logo" onerror="this.src='/admin/assets/img/logo.png'">
          </div>
          <div class="col-6">
            <h2 class="school-title mb-1">FOLU INTERNATIONAL SCHOOLS</h2>
            <p class="mb-1 text-muted small">Km 4, Idiroko Road, Ota, Ogun State, Nigeria</p>
            <p class="mb-1 text-muted small"><i class="fas fa-phone me-1"></i> +234 803 000 0000 | <i class="fas fa-envelope me-1"></i> info@foluinternationalschools.com.ng</p>
            <small class="fst-italic text-secondary">Motto: Excellence, Discipline & Integrity</small>
          </div>
          <div class="col-3 text-end">
            <span class="document-title">STUDENT RECORD</span>
            <div class="mt-2 text-muted small">Generated: <?php echo date('M d, Y'); ?></div>
          </div>
        </div>
      </div>

      <!-- Student Banner & Passport -->
      <div class="row align-items-center mb-4">
        <div class="col-md-9">
          <h3 class="fw-bold mb-1" style="color: #720922;"><?php echo htmlspecialchars($studentFullName); ?></h3>
          <p class="text-muted mb-2">Admission No: <span class="badge bg-dark fs-6"><?php echo htmlspecialchars((string)($student['admission_no'] ?? $student['student_number'] ?? 'N/A')); ?></span></p>
          <div class="d-flex flex-wrap gap-2 align-items-center">
            <span class="badge bg-primary text-white fs-6">Class: <?php echo htmlspecialchars((string)($student['class_name'] ?? 'Not Assigned')); ?></span>
            <span class="badge bg-secondary text-white fs-6">Session: <?php echo htmlspecialchars((string)($student['session_name'] ?? 'N/A')); ?></span>
            <span class="badge bg-<?php echo ($student['status'] ?? '') === 'active' ? 'success' : 'warning'; ?> text-white fs-6">
              Status: <?php echo ucfirst((string)($student['status'] ?? 'Active')); ?>
            </span>
          </div>
        </div>
        <div class="col-md-3 text-end">
          <?php if (!empty($student['passport'])): ?>
            <img src="<?php echo htmlspecialchars((string)$student['passport']); ?>" alt="Student Passport" class="student-photo">
          <?php else: ?>
            <div class="photo-placeholder mx-auto me-md-0">
              <i class="fas fa-user"></i>
            </div>
          <?php endif; ?>
        </div>
      </div>

      <!-- Personal Information -->
      <div class="section-header">
        <i class="fas fa-id-card me-2"></i>1. Personal Information
      </div>
      <div class="row">
        <div class="col-md-6">
          <table class="table table-borderless info-table">
            <tr>
              <td class="info-label">Full Name:</td>
              <td class="info-value"><?php echo htmlspecialchars($studentFullName); ?></td>
            </tr>
            <tr>
              <td class="info-label">Gender:</td>
              <td class="info-value"><?php echo ucfirst((string)($student['gender'] ?? 'N/A')); ?></td>
            </tr>
            <tr>
              <td class="info-label">Date of Birth:</td>
              <td class="info-value">
                <?php echo !empty($student['date_of_birth']) ? date('F d, Y', strtotime((string)$student['date_of_birth'])) : 'N/A'; ?>
                (Age: <?php echo $age; ?>)
              </td>
            </tr>
            <tr>
              <td class="info-label">Blood Group:</td>
              <td class="info-value"><?php echo htmlspecialchars((string)($student['blood_group'] ?? 'N/A')); ?></td>
            </tr>
            <tr>
              <td class="info-label">Genotype:</td>
              <td class="info-value"><?php echo htmlspecialchars((string)($student['genotype'] ?? 'N/A')); ?></td>
            </tr>
          </table>
        </div>
        <div class="col-md-6">
          <table class="table table-borderless info-table">
            <tr>
              <td class="info-label">State of Origin:</td>
              <td class="info-value"><?php echo htmlspecialchars((string)($student['state_of_origin'] ?? 'N/A')); ?></td>
            </tr>
            <tr>
              <td class="info-label">L.G.A:</td>
              <td class="info-value"><?php echo htmlspecialchars((string)($student['lga'] ?? 'N/A')); ?></td>
            </tr>
            <tr>
              <td class="info-label">Email Address:</td>
              <td class="info-value"><?php echo htmlspecialchars((string)($student['email'] ?? 'N/A')); ?></td>
            </tr>
            <tr>
              <td class="info-label">Phone Number:</td>
              <td class="info-value"><?php echo htmlspecialchars((string)($student['phone'] ?? 'N/A')); ?></td>
            </tr>
            <tr>
              <td class="info-label">Contact Address:</td>
              <td class="info-value"><?php echo htmlspecialchars((string)($student['address'] ?? $student['home_address'] ?? 'N/A')); ?></td>
            </tr>
          </table>
        </div>
      </div>

      <!-- Academic & Enrollment Profile -->
      <div class="section-header">
        <i class="fas fa-graduation-cap me-2"></i>2. Academic & Enrollment Record
      </div>
      <div class="row">
        <div class="col-md-6">
          <table class="table table-borderless info-table">
            <tr>
              <td class="info-label">Current Class:</td>
              <td class="info-value"><?php echo htmlspecialchars((string)($student['class_name'] ?? 'Not Assigned')); ?></td>
            </tr>
            <tr>
              <td class="info-label">Academic Session:</td>
              <td class="info-value"><?php echo htmlspecialchars((string)($student['session_name'] ?? 'N/A')); ?></td>
            </tr>
            <tr>
              <td class="info-label">Student Category:</td>
              <td class="info-value"><?php echo ucfirst((string)($student['student_type'] ?? 'Day')); ?> Student</td>
            </tr>
          </table>
        </div>
        <div class="col-md-6">
          <table class="table table-borderless info-table">
            <tr>
              <td class="info-label">Enrollment Date:</td>
              <td class="info-value">
                <?php 
                  $enrDate = $student['enrollment_date'] ?? $student['admission_date'] ?? $student['created_at'] ?? null;
                  echo !empty($enrDate) ? date('F d, Y', strtotime((string)$enrDate)) : 'N/A';
                ?>
              </td>
            </tr>
            <tr>
              <td class="info-label">Attendance Rate:</td>
              <td class="info-value"><?php echo number_format((float)$attendancePercentage, 1); ?>%</td>
            </tr>
            <tr>
              <td class="info-label">Academic Average:</td>
              <td class="info-value"><?php echo number_format((float)$gradeAverage, 1); ?></td>
            </tr>
          </table>
        </div>
      </div>

      <!-- Parent / Guardian Information -->
      <?php if ($parent): ?>
      <div class="section-header">
        <i class="fas fa-users me-2"></i>3. Parent / Guardian Details
      </div>
      <div class="row">
        <div class="col-md-6">
          <table class="table table-borderless info-table">
            <tr>
              <td class="info-label">Guardian Name:</td>
              <td class="info-value"><?php echo htmlspecialchars(trim(($parent['first_name'] ?? '') . ' ' . ($parent['last_name'] ?? ''))); ?></td>
            </tr>
            <tr>
              <td class="info-label">Relationship:</td>
              <td class="info-value"><?php echo htmlspecialchars((string)($parent['relationship_to_student'] ?? 'Parent')); ?></td>
            </tr>
            <tr>
              <td class="info-label">Occupation:</td>
              <td class="info-value"><?php echo htmlspecialchars((string)($parent['occupation'] ?? 'N/A')); ?></td>
            </tr>
          </table>
        </div>
        <div class="col-md-6">
          <table class="table table-borderless info-table">
            <tr>
              <td class="info-label">Phone Number:</td>
              <td class="info-value"><?php echo htmlspecialchars((string)($parent['phone'] ?? 'N/A')); ?></td>
            </tr>
            <tr>
              <td class="info-label">Email Address:</td>
              <td class="info-value"><?php echo htmlspecialchars((string)($parent['email'] ?? 'N/A')); ?></td>
            </tr>
            <tr>
              <td class="info-label">Address:</td>
              <td class="info-value"><?php echo htmlspecialchars((string)($parent['address'] ?? 'N/A')); ?></td>
            </tr>
          </table>
        </div>
      </div>
      <?php endif; ?>

      <!-- Financial Summary & Fee Statement -->
      <div class="section-header">
        <i class="fas fa-file-invoice-dollar me-2"></i>4. Fee Account & Payment Summary
      </div>
      <div class="row mb-3">
        <div class="col-md-4">
          <div class="summary-box">
            <div class="text-muted small">Total Allocated Fees</div>
            <h5 class="fw-bold mb-0">₦<?php echo number_format((float)($fee_summary['total_due'] ?? 0), 2); ?></h5>
          </div>
        </div>
        <div class="col-md-4">
          <div class="summary-box text-success" style="background: #f0fdf4; border-color: #bbf7d0;">
            <div class="text-muted small">Total Payments Made</div>
            <h5 class="fw-bold mb-0">₦<?php echo number_format((float)($fee_summary['total_paid'] ?? 0), 2); ?></h5>
          </div>
        </div>
        <div class="col-md-4">
          <div class="summary-box text-danger" style="background: #fef2f2; border-color: #fecaca;">
            <div class="text-muted small">Outstanding Balance</div>
            <h5 class="fw-bold mb-0">₦<?php echo number_format((float)($fee_summary['total_balance'] ?? 0), 2); ?></h5>
          </div>
        </div>
      </div>

      <!-- Allocated Fee Breakdowns -->
      <?php if (!empty($allocated_fees)): ?>
      <div class="table-responsive mb-3">
        <table class="table table-bordered align-middle small">
          <thead class="table-light">
            <tr>
              <th>Fee Name</th>
              <th>Academic Session</th>
              <th>Amount Due (₦)</th>
              <th>Amount Paid (₦)</th>
              <th>Balance (₦)</th>
              <th>Status</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($allocated_fees as $fee): ?>
            <tr>
              <td><strong><?php echo htmlspecialchars((string)($fee['fee_name'] ?? 'Fee')); ?></strong></td>
              <td><?php echo htmlspecialchars((string)($fee['academic_year'] ?? 'N/A')); ?></td>
              <td>₦<?php echo number_format((float)($fee['amount_due'] ?? 0), 2); ?></td>
              <td class="text-success">₦<?php echo number_format((float)($fee['amount_paid'] ?? 0), 2); ?></td>
              <td class="text-danger fw-bold">₦<?php echo number_format((float)($fee['balance'] ?? 0), 2); ?></td>
              <td>
                <span class="badge bg-<?php echo ($fee['status'] ?? '') === 'paid' ? 'success' : (($fee['status'] ?? '') === 'partial' ? 'warning' : 'danger'); ?> text-dark">
                  <?php echo ucfirst((string)($fee['status'] ?? 'pending')); ?>
                </span>
              </td>
            </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
      <?php endif; ?>

      <!-- Signature & Signoff Block -->
      <div class="signature-box">
        <div class="row align-items-end">
          <div class="col-6">
            <p class="small text-muted mb-4">Official Verification & Certification:</p>
            <br>
            <div style="border-bottom: 2px solid #333; width: 220px;"></div>
            <small class="fw-bold text-dark d-block mt-1">School Administrator / Registrar</small>
            <small class="text-muted">Folu International Schools</small>
          </div>
          <div class="col-6 text-end">
            <div class="border d-inline-block p-3 text-center rounded text-muted small" style="width: 160px; height: 90px; border-style: dashed !important;">
              School Stamp & Seal
            </div>
          </div>
        </div>
      </div>

      <div class="text-center text-muted small mt-4 pt-3 border-top">
        This document is an official computer-generated student record from Folu International Schools Portal.
      </div>

    </div>
  </div>

</body>
</html>
