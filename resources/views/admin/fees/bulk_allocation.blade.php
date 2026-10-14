<?php
// Start session
if (session_status() === PHP_SESSION_NONE && !headers_sent()) {
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
    header('Location: /admin/login.php');
    exit;
}

// Flash messages (PRG pattern)
$message = $_SESSION['flash_message'] ?? '';
$error = $_SESSION['flash_error'] ?? '';
unset($_SESSION['flash_message'], $_SESSION['flash_error']);

// Filter parameters
$sessionId = filter_input(INPUT_GET, 'session_id', FILTER_VALIDATE_INT) ?: 0;
$termId = filter_input(INPUT_GET, 'term_id', FILTER_VALIDATE_INT) ?: 0;
$classId = filter_input(INPUT_GET, 'class_id', FILTER_VALIDATE_INT) ?: 0;
$categoryFilter = strtoupper(trim((string) ($_GET['category'] ?? '')));
$genderFilter = strtolower(trim((string) ($_GET['gender'] ?? '')));

// Fetch active sessions and terms for defaults
$sessions = QueryDB("SELECT id, session_name, is_active FROM academic_sessions ORDER BY is_active DESC, start_date DESC")->fetchAll(PDO::FETCH_ASSOC);
if ($sessionId <= 0 && !empty($sessions)) {
    foreach ($sessions as $s) {
        if (!empty($s['is_active'])) {
            $sessionId = (int) $s['id'];
            break;
        }
    }
    if ($sessionId <= 0 && isset($sessions[0]['id'])) {
        $sessionId = (int) $sessions[0]['id'];
    }
}

$terms = QueryDB("SELECT id, term_name, is_active FROM terms ORDER BY is_active DESC, id ASC")->fetchAll(PDO::FETCH_ASSOC);
if ($termId <= 0 && !empty($terms)) {
    foreach ($terms as $t) {
        if (!empty($t['is_active'])) {
            $termId = (int) $t['id'];
            break;
        }
    }
    if ($termId <= 0 && isset($terms[0]['id'])) {
        $termId = (int) $terms[0]['id'];
    }
}

// Fetch classes for dropdown
$classes = schema_has_table('school_classes')
    ? QueryDB("SELECT id, class_name, section AS class_arm, grade_level FROM school_classes WHERE status = 'active' ORDER BY grade_level, class_name, section")->fetchAll(PDO::FETCH_ASSOC)
    : QueryDB("SELECT id, class_name, class_arm, class_level FROM classes ORDER BY class_level, class_arm")->fetchAll(PDO::FETCH_ASSOC);

// Fetch fee structures for allocation dropdown
$availableFeeStructures = QueryDB(
    "SELECT fs.*, sc.class_name AS fee_class_name, ac.session_name AS fee_session_name, t.term_name AS fee_term_name
     FROM fee_structures fs
     LEFT JOIN school_classes sc ON fs.class_id = sc.id
     LEFT JOIN academic_sessions ac ON fs.session_id = ac.id
     LEFT JOIN terms t ON fs.term_id = t.id
     WHERE fs.is_active = 1
     ORDER BY fs.name ASC"
)->fetchAll(PDO::FETCH_ASSOC);

$isPost = (isset($_SERVER['REQUEST_METHOD']) && strtoupper((string) $_SERVER['REQUEST_METHOD']) === 'POST') || (function_exists('request') && request()->isMethod('post'));

// Current URL for PRG
$queryString = http_build_query([
    'session_id' => $sessionId,
    'term_id' => $termId,
    'class_id' => $classId,
    'category' => $categoryFilter,
    'gender' => $genderFilter,
]);
$redirectUrl = '/admin/fees/bulk_allocation.php?' . $queryString;

$performRedirect = function() use ($redirectUrl) {
    if (!headers_sent()) {
        header("Location: " . $redirectUrl, true, 303);
        exit;
    } else {
        echo "<script>window.location.replace(" . json_encode($redirectUrl) . ");</script>";
        echo "<noscript><meta http-equiv='refresh' content='0;url=" . htmlspecialchars($redirectUrl, ENT_QUOTES) . "'></noscript>";
        exit;
    }
};

// Handle Bulk Allocation or De-allocation POST
if ($isPost && (isset($_POST['bulk_action']) || request()->has('bulk_action'))) {
    $action = trim((string) ($_POST['bulk_action'] ?? request('bulk_action') ?? ''));
    $selectedStudentIds = array_values(array_unique(array_map('intval', $_POST['student_ids'] ?? request('student_ids') ?? [])));
    $targetFeeStructureId = (int) ($_POST['target_fee_structure_id'] ?? request('target_fee_structure_id') ?? 0);

    if (empty($selectedStudentIds)) {
        $_SESSION['flash_error'] = 'Please select at least one student using the checkboxes.';
        $performRedirect();
    }

    if ($action === 'allocate') {
        try {
            $result = \App\Services\FeeAllocationService::bulkAllocateFees(
                $selectedStudentIds,
                $targetFeeStructureId > 0 ? $targetFeeStructureId : null,
                $sessionId ?: null,
                $termId ?: null,
                true
            );

            $msg = "Bulk Allocation Complete: Successfully allocated fees to {$result['allocated']} student(s).";
            if ($result['skipped'] > 0) {
                $msg .= " ({$result['skipped']} student(s) already had active fees allocated).";
            }
            if ($result['failed'] > 0) {
                $msg .= " ({$result['failed']} failed).";
            }
            $_SESSION['flash_message'] = $msg;
        } catch (\Throwable $e) {
            $_SESSION['flash_error'] = 'Failed to process bulk fee allocation: ' . $e->getMessage();
        }
        $performRedirect();
    }

    if ($action === 'deallocate') {
        try {
            $result = \App\Services\FeeAllocationService::bulkDeallocateFees(
                $selectedStudentIds,
                $targetFeeStructureId > 0 ? $targetFeeStructureId : null,
                $sessionId ?: null,
                $termId ?: null
            );

            $msg = "Bulk De-allocation Complete: Successfully de-allocated fees for {$result['deallocated']} student(s).";
            if ($result['protected'] > 0) {
                $msg .= " Note: {$result['protected']} student fee(s) were protected from de-allocation because payments have already been recorded.";
            }
            if ($result['skipped'] > 0) {
                $msg .= " ({$result['skipped']} had no matching fee to deallocate).";
            }
            $_SESSION['flash_message'] = $msg;
        } catch (\Throwable $e) {
            $_SESSION['flash_error'] = 'Failed to process bulk fee de-allocation: ' . $e->getMessage();
        }
        $performRedirect();
    }
}

// Build query for students list based on filters
$studentQuery = "
    SELECT 
        s.id,
        s.admission_no,
        s.first_name,
        s.last_name,
        s.other_names,
        s.gender,
        s.category,
        s.status,
        s.current_class_id,
        s.class_link,
        COALESCE(sc.class_name, c.class_name, 'Not Assigned') AS class_name,
        COALESCE(sc.section, c.class_arm, '') AS class_arm,
        (
            SELECT COUNT(*) 
            FROM student_fees sf 
            WHERE sf.student_id = s.id 
              AND (sf.session_id = :sf_sess OR sf.session_id IS NULL)
        ) AS allocated_fee_count,
        (
            SELECT COALESCE(SUM(
                CASE 
                    WHEN sf.base_amount > 0.001 THEN sf.base_amount
                    WHEN sf.amount_due > 0.001 THEN sf.amount_due
                    ELSE 0.00
                END
            ), 0.00)
            FROM student_fees sf 
            WHERE sf.student_id = s.id 
              AND (sf.session_id = :sf_sess2 OR sf.session_id IS NULL)
        ) AS total_due,
        (
            SELECT COALESCE(SUM(p.amount), 0.00)
            FROM payments p
            JOIN student_fees sf ON p.payable_id = sf.id
            WHERE (p.payable_type = 'student_fee' OR p.payable_type = 'App\\\\Models\\\\StudentFee')
              AND p.status = 'completed'
              AND sf.student_id = s.id
              AND (sf.session_id = :sf_sess3 OR sf.session_id IS NULL)
        ) AS total_paid
    FROM students s
    LEFT JOIN school_classes sc ON (s.current_class_id = sc.id OR s.class_link = sc.id)
    LEFT JOIN classes c ON (s.class_link = c.id)
    WHERE s.status = 'active'
";

$params = [
    'sf_sess' => $sessionId,
    'sf_sess2' => $sessionId,
    'sf_sess3' => $sessionId,
];

if ($classId > 0) {
    $studentQuery .= " AND (s.current_class_id = :cid OR s.class_link = :cid2)";
    $params['cid'] = $classId;
    $params['cid2'] = $classId;
}

if ($categoryFilter !== '' && in_array($categoryFilter, ['NI', 'OS'], true)) {
    $studentQuery .= " AND s.category = :category";
    $params['category'] = $categoryFilter;
}

if ($genderFilter !== '' && in_array($genderFilter, ['male', 'female'], true)) {
    $studentQuery .= " AND LOWER(s.gender) = :gender";
    $params['gender'] = $genderFilter;
}

$studentQuery .= " ORDER BY sc.grade_level ASC, sc.class_name ASC, s.last_name ASC, s.first_name ASC";

$stmt = $pdo->prepare($studentQuery);
$stmt->execute($params);
$students = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Statistics
$totalCount = count($students);
$withFeesCount = 0;
$withoutFeesCount = 0;
$grandTotalDue = 0;
$grandTotalPaid = 0;
$grandTotalBalance = 0;

foreach ($students as $st) {
    $c = (int) $st['allocated_fee_count'];
    $due = (float) $st['total_due'];
    $paid = (float) $st['total_paid'];
    $bal = max(0.00, $due - $paid);

    if ($c > 0) {
        $withFeesCount++;
    } else {
        $withoutFeesCount++;
    }

    $grandTotalDue += $due;
    $grandTotalPaid += $paid;
    $grandTotalBalance += $bal;
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta http-equiv="X-UA-Compatible" content="IE=edge" />
  <title>Bulk Fee Allocation & Management - Admin</title>
  @include('admin.partials.links')
  <style>
    .bulk-stat-card {
      border-radius: 10px;
      padding: 16px;
      transition: all 0.2s ease-in-out;
      border: 1px solid rgba(0,0,0,0.06);
    }
    .bulk-stat-card:hover {
      transform: translateY(-2px);
      box-shadow: 0 6px 15px rgba(0,0,0,0.08);
    }
    .sticky-actions-bar {
      position: sticky;
      top: 15px;
      z-index: 100;
      background: #ffffff;
      border-radius: 10px;
      padding: 15px;
      margin-bottom: 20px;
      border: 1px solid #dee2e6;
      box-shadow: 0 4px 12px rgba(0,0,0,0.05);
    }
    .badge-ni {
      background-color: #ff9800;
      color: #000;
      font-weight: 600;
    }
    .badge-os {
      background-color: #0d6efd;
      color: #fff;
      font-weight: 600;
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

          <!-- Page Header -->
          <div class="d-flex align-items-left align-items-md-center flex-column flex-md-row pb-3">
            <div>
              <h2 class="text-dark pb-1 fw-bold">
                <i class="fas fa-users-cog me-2 text-primary"></i>Bulk Fee Allocation & De-allocation
              </h2>
              <p class="text-muted mb-0">
                Easily allocate or de-allocate tuition and term fees across entire classes, academic sessions, or intake categories.
              </p>
            </div>
            <div class="ms-md-auto py-2 py-md-0 d-flex gap-2">
              <a href="/admin/fees.php" class="btn btn-outline-secondary btn-round">
                <i class="fas fa-list me-1"></i> Fee Structures
              </a>
              <a href="/admin/fees/payments.php" class="btn btn-outline-primary btn-round">
                <i class="fas fa-money-check-alt me-1"></i> Payments
              </a>
            </div>
          </div>

          <!-- Alert Messages -->
          <?php if ($message !== ''): ?>
          <div class="alert alert-success alert-dismissible fade show" role="alert">
            <div class="d-flex align-items-center">
              <i class="fas fa-check-circle fa-lg me-2"></i>
              <div><strong>Success:</strong> <?php echo htmlspecialchars($message); ?></div>
            </div>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
          </div>
          <?php endif; ?>

          <?php if ($error !== ''): ?>
          <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <div class="d-flex align-items-center">
              <i class="fas fa-exclamation-triangle fa-lg me-2"></i>
              <div><strong>Notice:</strong> <?php echo htmlspecialchars($error); ?></div>
            </div>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
          </div>
          <?php endif; ?>

          <!-- Summary Metric Cards -->
          <div class="row g-3 mb-4">
            <div class="col-6 col-md-3">
              <div class="card bulk-stat-card bg-light">
                <div class="text-muted small fw-bold text-uppercase">Students Filtered</div>
                <div class="fs-3 fw-bold text-dark mt-1"><?php echo number_format($totalCount); ?></div>
                <div class="text-secondary small mt-1">Based on current filter</div>
              </div>
            </div>
            <div class="col-6 col-md-3">
              <div class="card bulk-stat-card bg-light">
                <div class="text-muted small fw-bold text-uppercase">Fees Allocated</div>
                <div class="fs-3 fw-bold text-success mt-1"><?php echo number_format($withFeesCount); ?></div>
                <div class="text-muted small mt-1"><?php echo number_format($withoutFeesCount); ?> pending allocation</div>
              </div>
            </div>
            <div class="col-6 col-md-3">
              <div class="card bulk-stat-card bg-light">
                <div class="text-muted small fw-bold text-uppercase">Total Amount Due</div>
                <div class="fs-4 fw-bold text-primary mt-1">₦<?php echo number_format($grandTotalDue, 0); ?></div>
                <div class="text-success small mt-1">₦<?php echo number_format($grandTotalPaid, 0); ?> collected</div>
              </div>
            </div>
            <div class="col-6 col-md-3">
              <div class="card bulk-stat-card bg-light">
                <div class="text-muted small fw-bold text-uppercase">Outstanding Balance</div>
                <div class="fs-4 fw-bold text-danger mt-1">₦<?php echo number_format($grandTotalBalance, 0); ?></div>
                <div class="text-muted small mt-1">Total unpaid balance</div>
              </div>
            </div>
          </div>

          <!-- Filter Card -->
          <div class="card shadow-sm mb-4">
            <div class="card-header bg-light">
              <h5 class="card-title mb-0 fw-bold">
                <i class="fas fa-filter me-2 text-primary"></i>Filter Students Broadsheet
              </h5>
            </div>
            <div class="card-body">
              <form method="GET" action="" class="row g-3 align-items-end">
                <div class="col-md-3">
                  <label for="session_id" class="form-label fw-bold small">Academic Session</label>
                  <select name="session_id" id="session_id" class="form-select form-select-sm">
                    <?php foreach ($sessions as $s): ?>
                      <option value="<?php echo (int) $s['id']; ?>" <?php echo ((int)$s['id'] === $sessionId) ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($s['session_name']); ?> <?php echo !empty($s['is_active']) ? '(Active)' : ''; ?>
                      </option>
                    <?php endforeach; ?>
                  </select>
                </div>

                <div class="col-md-2">
                  <label for="term_id" class="form-label fw-bold small">Academic Term</label>
                  <select name="term_id" id="term_id" class="form-select form-select-sm">
                    <?php foreach ($terms as $t): ?>
                      <option value="<?php echo (int) $t['id']; ?>" <?php echo ((int)$t['id'] === $termId) ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($t['term_name']); ?>
                      </option>
                    <?php endforeach; ?>
                  </select>
                </div>

                <div class="col-md-3">
                  <label for="class_id" class="form-label fw-bold small">Class / Arm</label>
                  <select name="class_id" id="class_id" class="form-select form-select-sm">
                    <option value="0">All Classes</option>
                    <?php foreach ($classes as $c): ?>
                      <option value="<?php echo (int) $c['id']; ?>" <?php echo ((int)$c['id'] === $classId) ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($c['class_name'] . ' ' . ($c['class_arm'] ?? '')); ?>
                      </option>
                    <?php endforeach; ?>
                  </select>
                </div>

                <div class="col-md-2">
                  <label for="category" class="form-label fw-bold small">Intake Category</label>
                  <select name="category" id="category" class="form-select form-select-sm">
                    <option value="" <?php echo $categoryFilter === '' ? 'selected' : ''; ?>>All Categories</option>
                    <option value="NI" <?php echo $categoryFilter === 'NI' ? 'selected' : ''; ?>>New Intake (NI)</option>
                    <option value="OS" <?php echo $categoryFilter === 'OS' ? 'selected' : ''; ?>>Returning Student (OS)</option>
                  </select>
                </div>

                <div class="col-md-2 d-flex gap-2">
                  <button type="submit" class="btn btn-primary btn-sm w-100 fw-bold">
                    <i class="fas fa-search me-1"></i> Apply Filter
                  </button>
                  <a href="/admin/fees/bulk_allocation.php" class="btn btn-outline-secondary btn-sm" title="Reset Filters">
                    <i class="fas fa-undo"></i>
                  </a>
                </div>
              </form>
            </div>
          </div>

          <!-- Main Form for Bulk Actions & Student List -->
          <form method="POST" action="" id="bulkAllocationForm">
            <input type="hidden" name="bulk_action" id="bulkActionInput" value="">

            <!-- Action Toolbar (Sticky) -->
            <div class="sticky-actions-bar">
              <div class="row g-2 align-items-center">
                <div class="col-lg-6">
                  <label for="target_fee_structure_id" class="form-label fw-bold small mb-1">
                    Select Target Fee Structure for Selected Students:
                  </label>
                  <select name="target_fee_structure_id" id="target_fee_structure_id" class="form-select">
                    <option value="0">-- Auto-Match Fee Structure (Based on Class & Intake Category) --</option>
                    <?php foreach ($availableFeeStructures as $afs): ?>
                      <option value="<?php echo (int) $afs['id']; ?>">
                        <?php echo htmlspecialchars($afs['name']); ?> - ₦<?php echo number_format((float)$afs['amount'], 2); ?> 
                        (<?php echo htmlspecialchars($afs['category'] ?: 'All'); ?> | <?php echo htmlspecialchars($afs['fee_class_name'] ?: 'All Classes'); ?>)
                      </option>
                    <?php endforeach; ?>
                  </select>
                </div>
                <div class="col-lg-6 text-lg-end d-flex flex-wrap justify-content-lg-end gap-2 mt-2 mt-lg-0">
                  <button type="button" class="btn btn-primary fw-bold" onclick="submitBulkAction('allocate')">
                    <i class="fas fa-check-double me-1"></i> Bulk Allocate Fees (<span id="selectedCountAlloc">0</span>)
                  </button>
                  <button type="button" class="btn btn-outline-danger fw-bold" onclick="submitBulkAction('deallocate')">
                    <i class="fas fa-trash-alt me-1"></i> Bulk De-allocate (<span id="selectedCountDealloc">0</span>)
                  </button>
                </div>
              </div>
            </div>

            <!-- Student Broadsheet Card -->
            <div class="card shadow-sm">
              <div class="card-header bg-white d-flex justify-content-between align-items-center">
                <h5 class="card-title mb-0 fw-bold">
                  <i class="fas fa-list-check me-2 text-primary"></i>Students Broadsheet (<?php echo count($students); ?> found)
                </h5>
                <div class="small text-muted">
                  Checkboxes select students for bulk operations.
                </div>
              </div>
              <div class="card-body p-0">
                <?php if (empty($students)): ?>
                  <div class="text-center py-5 text-muted">
                    <i class="fas fa-user-slash fa-3x mb-3 d-block text-muted"></i>
                    <h5>No active students found matching your selected filters.</h5>
                    <p class="small">Try selecting "All Classes" or clearing the category filter.</p>
                  </div>
                <?php else: ?>
                  <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                      <thead class="table-light">
                        <tr>
                          <th style="width: 40px;" class="text-center">
                            <input type="checkbox" id="selectAllCheckbox" class="form-check-input">
                          </th>
                          <th>#</th>
                          <th>Admission No</th>
                          <th>Student Full Name</th>
                          <th>Class & Arm</th>
                          <th>Intake Status</th>
                          <th>Fees Assigned</th>
                          <th>Total Due (₦)</th>
                          <th>Total Paid (₦)</th>
                          <th>Balance (₦)</th>
                          <th>Status</th>
                          <th class="text-end">Manage</th>
                        </tr>
                      </thead>
                      <tbody>
                        <?php 
                          $i = 1;
                          foreach ($students as $st): 
                            $feeCount = (int) $st['allocated_fee_count'];
                            $due = (float) $st['total_due'];
                            $paid = (float) $st['total_paid'];
                            $bal = max(0.00, $due - $paid);
                            $cat = strtoupper(trim((string) ($st['category'] ?? 'NI')));
                            $isReturningStd = ($cat === 'OS' || $cat === 'RETURNING');
                            
                            $statusLabel = 'No Fees';
                            $statusBadge = 'secondary';
                            if ($feeCount > 0) {
                                if ($bal <= 0.001) {
                                    $statusLabel = 'Fully Paid';
                                    $statusBadge = 'success';
                                } elseif ($paid > 0) {
                                    $statusLabel = 'Partially Paid';
                                    $statusBadge = 'warning text-dark';
                                } else {
                                    $statusLabel = 'Unpaid';
                                    $statusBadge = 'danger';
                                }
                            }
                        ?>
                        <tr>
                          <td class="text-center">
                            <input type="checkbox" 
                                   name="student_ids[]" 
                                   value="<?php echo (int) $st['id']; ?>" 
                                   class="form-check-input student-checkbox"
                                   data-has-paid="<?php echo ($paid > 0) ? '1' : '0'; ?>">
                          </td>
                          <td><?php echo $i++; ?></td>
                          <td><code><?php echo htmlspecialchars((string) ($st['admission_no'] ?? 'N/A')); ?></code></td>
                          <td>
                            <a href="/admin/view_students.php?id=<?php echo (int) $st['id']; ?>" class="fw-bold text-dark text-decoration-none">
                              <?php echo htmlspecialchars(trim($st['first_name'] . ' ' . ($st['other_names'] ?? '') . ' ' . $st['last_name'])); ?>
                            </a>
                            <div class="small text-muted"><?php echo ucfirst((string) ($st['gender'] ?? '')); ?></div>
                          </td>
                          <td><?php echo htmlspecialchars(trim($st['class_name'] . ' ' . $st['class_arm'])); ?></td>
                          <td>
                            <span class="badge <?php echo $isReturningStd ? 'badge-os' : 'badge-ni'; ?>">
                              <?php echo $isReturningStd ? 'Returning (OS)' : 'New Intake (NI)'; ?>
                            </span>
                          </td>
                          <td>
                            <span class="badge <?php echo $feeCount > 0 ? 'bg-light text-dark border' : 'bg-light text-muted border'; ?>">
                              <?php echo $feeCount; ?> fee(s)
                            </span>
                          </td>
                          <td class="fw-bold text-dark">
                            ₦<?php echo number_format($due, 2); ?>
                          </td>
                          <td class="fw-bold text-success">
                            ₦<?php echo number_format($paid, 2); ?>
                          </td>
                          <td class="fw-bold text-danger">
                            ₦<?php echo number_format($bal, 2); ?>
                          </td>
                          <td>
                            <span class="badge bg-<?php echo $statusBadge; ?>">
                              <?php echo $statusLabel; ?>
                            </span>
                          </td>
                          <td class="text-end">
                            <a href="/admin/fee_structure.php?student_id=<?php echo (int) $st['id']; ?>" 
                               class="btn btn-sm btn-outline-primary py-1 px-2"
                               title="Manage Fees for this student">
                              <i class="fas fa-sliders-h me-1"></i> Manage
                            </a>
                          </td>
                        </tr>
                        <?php endforeach; ?>
                      </tbody>
                    </table>
                  </div>
                <?php endif; ?>
              </div>
            </div>
          </form>

        </div>
      </div>
      @include('admin.partials.footer')
    </div>
  </div>

  <script>
    document.addEventListener('DOMContentLoaded', function () {
      const selectAll = document.getElementById('selectAllCheckbox');
      const checkboxes = document.querySelectorAll('.student-checkbox');
      const countAlloc = document.getElementById('selectedCountAlloc');
      const countDealloc = document.getElementById('selectedCountDealloc');

      function updateCounts() {
        const checkedCount = document.querySelectorAll('.student-checkbox:checked').length;
        if (countAlloc) countAlloc.textContent = checkedCount;
        if (countDealloc) countDealloc.textContent = checkedCount;
      }

      if (selectAll) {
        selectAll.addEventListener('change', function () {
          checkboxes.forEach(function (cb) {
            cb.checked = selectAll.checked;
          });
          updateCounts();
        });

        checkboxes.forEach(function (cb) {
          cb.addEventListener('change', function () {
            if (!this.checked) {
              selectAll.checked = false;
            } else {
              selectAll.checked = Array.from(checkboxes).every(function (c) { return c.checked; });
            }
            updateCounts();
          });
        });
      }

      window.submitBulkAction = function(action) {
        const checkedBoxes = document.querySelectorAll('.student-checkbox:checked');
        if (checkedBoxes.length === 0) {
          alert('Please select at least one student from the broadsheet using the checkboxes.');
          return;
        }

        const selectTarget = document.getElementById('target_fee_structure_id');
        const targetText = selectTarget.options[selectTarget.selectedIndex].text;

        let confirmMsg = '';
        if (action === 'allocate') {
          confirmMsg = 'Are you sure you want to ALLOCATE fees to ' + checkedBoxes.length + ' selected student(s)?\n\nTarget Structure: ' + targetText;
        } else if (action === 'deallocate') {
          confirmMsg = 'WARNING: Are you sure you want to DE-ALLOCATE fees from ' + checkedBoxes.length + ' selected student(s)?\n\nNote: Any fees with recorded payments are protected and will NOT be deleted.\n\nTarget Structure: ' + targetText;
        }

        if (confirm(confirmMsg)) {
          document.getElementById('bulkActionInput').value = action;
          document.getElementById('bulkAllocationForm').submit();
        }
      };
    });
  </script>
</body>
</html>
