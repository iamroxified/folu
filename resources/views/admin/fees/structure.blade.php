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

$studentId = (int) ($_GET['student_id'] ?? $_GET['id'] ?? request('student_id') ?? request('id') ?? 0);
if ($studentId <= 0) {
    die('Student ID is required');
}

$redirectUrl = '/admin/fee_structure.php?student_id=' . $studentId;
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

$isPost = (isset($_SERVER['REQUEST_METHOD']) && strtoupper((string) $_SERVER['REQUEST_METHOD']) === 'POST') || (function_exists('request') && request()->isMethod('post'));

// Fetch student information
$studentSessionColumn = schema_has_column('students', 'current_session_id')
    ? 'current_session_id'
    : (schema_has_column('students', 'academic_session_link') ? 'academic_session_link' : null);
$studentTermColumn = schema_has_column('students', 'current_term_id')
    ? 'current_term_id'
    : (schema_has_column('students', 'term_link') ? 'term_link' : null);
$studentClassColumn = schema_has_column('students', 'current_class_id')
    ? 'current_class_id'
    : (schema_has_column('students', 'class_link') ? 'class_link' : null);

$studentSelects = [
    's.*',
    "COALESCE(sc.class_name, c.class_name, 'Not Assigned') AS class_name",
    "COALESCE(sc.section, c.class_arm, '') AS class_arm",
    "COALESCE(sc.grade_level, c.class_level, '') AS class_level",
    schema_has_table('academic_sessions') && $studentSessionColumn ? 'ac.session_name' : 'NULL AS session_name',
    schema_has_table('terms') && $studentTermColumn ? 't.term_name' : 'NULL AS term_name',
];

$studentJoins = [];
if (schema_has_table('school_classes') && schema_has_column('students', 'current_class_id')) {
    $studentJoins[] = 'LEFT JOIN school_classes sc ON s.current_class_id = sc.id';
} else {
    $studentJoins[] = 'LEFT JOIN school_classes sc ON 1 = 0';
}

if (schema_has_table('classes') && schema_has_column('students', 'class_link')) {
    $studentJoins[] = 'LEFT JOIN classes c ON s.class_link = c.id';
} else {
    $studentJoins[] = 'LEFT JOIN classes c ON 1 = 0';
}

if (schema_has_table('academic_sessions') && $studentSessionColumn) {
    $studentJoins[] = 'LEFT JOIN academic_sessions ac ON s.' . $studentSessionColumn . ' = ac.id';
}

if (schema_has_table('terms') && $studentTermColumn) {
    $studentJoins[] = 'LEFT JOIN terms t ON s.' . $studentTermColumn . ' = t.id';
}

try {
    $studentQuery = 'SELECT ' . implode(', ', $studentSelects) .
        ' FROM students s ' . implode(' ', $studentJoins) .
        ' WHERE s.id = ? LIMIT 1';

    $student = QueryDB($studentQuery, [$studentId])->fetch(PDO::FETCH_ASSOC);

    if (!$student) {
        die('Student not found');
    }

    $studentClassId = (int) ($student['current_class_id'] ?? $student['class_link'] ?? 0);
    $studentSessionId = (int) ($student['current_session_id'] ?? $student['academic_session_link'] ?? 0);
    $studentTermId = (int) ($student['current_term_id'] ?? $student['term_link'] ?? 0);
    $studentCategory = strtoupper(trim((string) ($student['category'] ?? 'NI')));
    $isReturning = ($studentCategory === 'OS' || $studentCategory === 'RETURNING');
    $studentGenderCode = match (strtolower(trim((string) ($student['gender'] ?? '')))) {
        'male' => 'M',
        'female' => 'F',
        default => 'All',
    };

    if ($studentTermId <= 0 && schema_has_table('terms')) {
        $studentTermId = (int) (QueryDB(
            'SELECT id FROM terms WHERE is_active = 1 ORDER BY term_number ASC, id DESC LIMIT 1'
        )->fetchColumn() ?: 0);
    }

    if (($student['term_name'] ?? '') === '' && $studentTermId > 0 && schema_has_table('terms')) {
        $student['term_name'] = QueryDB(
            'SELECT term_name FROM terms WHERE id = ? LIMIT 1',
            [$studentTermId]
        )->fetchColumn() ?: null;
    }

    $sessionLabel = trim((string) ($student['session_name'] ?? 'Current Session'));
    $termLabel = trim((string) ($student['term_name'] ?? 'Current Term'));

    $studentModel = \App\Models\Student::find($studentId);

    // 1. Handle Student Category quick update
    if ($isPost && (isset($_POST['update_student_category']) || request()->has('update_student_category'))) {
        $newCategory = strtoupper(trim((string) ($_POST['category'] ?? request('category') ?? 'NI')));
        if (!in_array($newCategory, ['NI', 'OS'], true)) {
            $_SESSION['flash_error'] = 'Invalid student intake status selected.';
        } else {
            try {
                $catStmt = $pdo->prepare("UPDATE students SET category = ?, updated_at = NOW() WHERE id = ?");
                $catStmt->execute([$newCategory, $studentId]);
                $label = $newCategory === 'NI' ? 'New Intake (NI)' : 'Returning Student (OS)';
                $_SESSION['flash_message'] = "Student status updated to '{$label}' successfully.";
            } catch (\Throwable $e) {
                $_SESSION['flash_error'] = 'Failed to update student category: ' . $e->getMessage();
            }
        }
        $performRedirect();
    }

    // 2. Handle Deallocation (POST or GET)
    $hasDeallocatePost = $isPost && (isset($_POST['deallocate_fee']) || request()->has('deallocate_fee'));
    $hasDeallocateGet = isset($_GET['deallocate_fee']) || isset($_GET['student_fee_id']);

    if ($hasDeallocatePost || $hasDeallocateGet) {
        $studentFeeId = (int) ($_POST['student_fee_id'] ?? request('student_fee_id') ?? $_GET['student_fee_id'] ?? 0);
        $feeStructureId = (int) ($_POST['fee_structure_id'] ?? request('fee_structure_id') ?? $_GET['deallocate_fee'] ?? 0);

        if ($studentFeeId <= 0 && $feeStructureId > 0) {
            $foundSf = \App\Models\StudentFee::where('student_id', $studentId)
                ->where('fee_structure_id', $feeStructureId)
                ->latest('id')
                ->first();
            if ($foundSf) {
                $studentFeeId = (int) $foundSf->id;
            }
        }

        if ($studentFeeId > 0) {
            $result = \App\Services\FeeAllocationService::deallocateStudentFee($studentFeeId);
            if (!empty($result['success']) || !empty($result['status'])) {
                $_SESSION['flash_message'] = $result['message'];
            } else {
                $_SESSION['flash_error'] = $result['message'];
            }
        } else {
            $_SESSION['flash_error'] = 'Could not find the allocated fee record to de-allocate.';
        }
        $performRedirect();
    }

    // 3. Handle Fee Allocation (Single or Multi-select)
    $hasAllocatePost = $isPost && (isset($_POST['allocate_fees']) || isset($_POST['allocate_single_fee']) || request()->has('allocate_fees') || request()->has('allocate_single_fee'));
    if ($hasAllocatePost) {
        $selectedFees = [];
        if (!empty($_POST['allocate_single_fee'])) {
            $selectedFees = [(int) $_POST['allocate_single_fee']];
        } else {
            $selectedFees = array_values(array_unique(array_map('intval', $_POST['fee_ids'] ?? request('fee_ids') ?? [])));
        }

        if ($selectedFees === []) {
            $_SESSION['flash_error'] = 'Please select at least one fee structure to allocate.';
        } else {
            $allocatedCount = 0;
            $skippedCount = 0;
            $errors = [];

            foreach ($selectedFees as $fsId) {
                if ($fsId <= 0) {
                    continue;
                }

                try {
                    $newFee = \App\Services\FeeAllocationService::allocateSpecificFeeForStudent(
                        $studentModel,
                        $fsId,
                        $studentSessionId ?: null,
                        $studentTermId ?: null,
                        true
                    );

                    if ($newFee && $newFee->wasRecentlyCreated) {
                        $allocatedCount++;
                    } else {
                        $skippedCount++;
                    }
                } catch (\Throwable $e) {
                    $errors[] = $e->getMessage();
                }
            }

            if ($allocatedCount > 0) {
                $_SESSION['flash_message'] = "Successfully allocated {$allocatedCount} fee structure(s) to {$student['first_name']}.";
                if ($skippedCount > 0) {
                    $_SESSION['flash_message'] .= " ({$skippedCount} structure(s) were already allocated).";
                }
            } elseif ($skippedCount > 0) {
                $_SESSION['flash_error'] = "The selected fee structure(s) are already allocated to this student.";
            } else {
                $_SESSION['flash_error'] = "Failed to allocate fees: " . implode(', ', $errors);
            }
        }
        $performRedirect();
    }

    // 4. Query Currently Allocated Fees for this student
    $allocated_fees_stmt = $pdo->prepare("
        SELECT 
            sf.id AS student_fee_id,
            sf.fee_structure_id,
            COALESCE(fs.name, sf.notes, 'School Tuition Fee') AS fee_name,
            COALESCE(fs.category, 'All') AS fee_category,
            COALESCE(fs.description, '') AS fee_description,
            sf.academic_year,
            sf.semester,
            sess.session_name,
            t.term_name,
            CASE 
                WHEN sf.base_amount > 0.001 THEN sf.base_amount
                WHEN sf.amount_due > 0.001 THEN sf.amount_due
                ELSE COALESCE(fs.amount, 0.00)
            END AS calculated_amount,
            sf.amount_paid,
            (SELECT COALESCE(SUM(p.amount), 0) FROM payments p WHERE (p.payable_type = 'student_fee' OR p.payable_type = 'App\\\\Models\\\\StudentFee') AND p.payable_id = sf.id AND p.status = 'completed') AS direct_fee_paid,
            sf.balance,
            sf.status,
            sf.due_date,
            sf.created_at,
            sf.itemized_snapshot
        FROM student_fees sf
        LEFT JOIN fee_structures fs ON sf.fee_structure_id = fs.id
        LEFT JOIN academic_sessions sess ON sf.session_id = sess.id
        LEFT JOIN terms t ON sf.term_id = t.id
        WHERE sf.student_id = ?
        ORDER BY sf.id DESC
    ");
    $allocated_fees_stmt->execute([$studentId]);
    $allocatedFees = $allocated_fees_stmt->fetchAll(PDO::FETCH_ASSOC);

    $alreadyAllocatedFsIds = [];
    foreach ($allocatedFees as $afRow) {
        if (!empty($afRow['fee_structure_id'])) {
            $alreadyAllocatedFsIds[] = (int) $afRow['fee_structure_id'];
        }
    }

    // 5. Query Available Fee Structures to Allocate
    $showAll = !empty($_GET['show_all']);

    $fsQuery = "
        SELECT 
            fs.*,
            sc.class_name AS fee_class_name,
            sc.section AS fee_class_section,
            ac.session_name AS fee_session_name,
            t.term_name AS fee_term_name
        FROM fee_structures fs
        LEFT JOIN school_classes sc ON fs.class_id = sc.id
        LEFT JOIN academic_sessions ac ON fs.session_id = ac.id
        LEFT JOIN terms t ON fs.term_id = t.id
        WHERE fs.is_active = 1
    ";

    $fsParams = [];
    if (!$showAll) {
        $conditions = [];

        // Match session or session is null/general
        if ($studentSessionId > 0) {
            $conditions[] = "(fs.session_id = ? OR fs.session_id IS NULL)";
            $fsParams[] = $studentSessionId;
        }

        // Match class or class is null/all
        if ($studentClassId > 0) {
            $conditions[] = "(fs.class_id = ? OR fs.class_id IS NULL)";
            $fsParams[] = $studentClassId;
        }

        // Match category ('NI', 'OS' or 'ALL'/'All')
        if ($studentCategory !== '') {
            $conditions[] = "(fs.category = ? OR fs.category = 'ALL' OR fs.category = 'All' OR fs.category IS NULL)";
            $fsParams[] = $studentCategory;
        }

        // Match gender
        $conditions[] = "(fs.gender = ? OR fs.gender = 'All' OR fs.gender IS NULL)";
        $fsParams[] = $studentGenderCode;

        if (!empty($conditions)) {
            $fsQuery .= " AND " . implode(" AND ", $conditions);
        }
    }

    $fsQuery .= " ORDER BY fs.is_mandatory DESC, fs.name ASC, fs.amount ASC";

    $feeStructures = QueryDB($fsQuery, $fsParams)->fetchAll(PDO::FETCH_ASSOC);

} catch (Throwable $e) {
    if (isset($pdo) && $pdo->inTransaction()) {
        $pdo->rollBack();
    }
    die('Database error: ' . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta http-equiv="X-UA-Compatible" content="IE=edge" />
  <title>Fee Allocation - <?php echo htmlspecialchars(trim($student['first_name'] . ' ' . $student['last_name'])); ?></title>
  @include('admin.partials.links')
  <style>
    .fee-card-highlight {
      background: linear-gradient(135deg, #1d3557, #457b9d);
      color: white;
      border-radius: 12px;
      padding: 20px;
      margin-bottom: 25px;
      box-shadow: 0 4px 15px rgba(0,0,0,0.1);
    }
    .fee-card-highlight h2 {
      color: #ffffff;
      font-weight: 700;
    }
    .badge-category {
      font-size: 13px;
      padding: 6px 12px;
      border-radius: 20px;
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

          <!-- Student Header Banner -->
          <div class="fee-card-highlight">
            <div class="row align-items-center">
              <div class="col-lg-8">
                <div class="d-flex align-items-center gap-3 mb-2">
                  <h2 class="mb-0"><?php echo htmlspecialchars(trim($student['first_name'] . ' ' . ($student['other_names'] ?? '') . ' ' . $student['last_name'])); ?></h2>
                  <span class="badge <?php echo $isReturning ? 'bg-primary' : 'bg-warning text-dark'; ?> badge-category fw-bold">
                    <i class="fas <?php echo $isReturning ? 'fa-user-check' : 'fa-user-plus'; ?> me-1"></i>
                    <?php echo $isReturning ? 'Returning Student (OS)' : 'New Intake (NI)'; ?>
                  </span>
                </div>
                <p class="mb-2 text-light" style="opacity: 0.95;">
                  <strong>Admission No:</strong> <code><?php echo htmlspecialchars((string) ($student['admission_no'] ?? 'N/A')); ?></code> | 
                  <strong>Class:</strong> <?php echo htmlspecialchars(trim(($student['class_name'] ?? 'Unassigned') . ' ' . ($student['class_arm'] ?? ''))); ?> |
                  <strong>Session:</strong> <?php echo htmlspecialchars($sessionLabel); ?> (<?php echo htmlspecialchars($termLabel); ?>)
                </p>
                <div class="d-flex flex-wrap align-items-center gap-2 mt-2">
                  <span class="text-white-50 small">Quick Status Changer:</span>
                  <form method="POST" action="" class="d-inline" onsubmit="return confirm('Change intake status to <?php echo $isReturning ? 'New Intake (NI)' : 'Returning Student (OS)'; ?>?');">
                    <input type="hidden" name="update_student_category" value="1">
                    <input type="hidden" name="category" value="<?php echo $isReturning ? 'NI' : 'OS'; ?>">
                    <button type="submit" class="btn btn-sm btn-light py-1 px-3 fw-bold text-dark" title="Switch between New Intake and Returning Student">
                      <i class="fas fa-sync-alt me-1 text-primary"></i> Switch to <?php echo $isReturning ? 'New Intake (NI)' : 'Returning Student (OS)'; ?>
                    </button>
                  </form>
                </div>
              </div>
              <div class="col-lg-4 text-lg-end mt-3 mt-lg-0">
                <div class="btn-group-vertical">
                  <a href="/admin/view_students.php?id=<?php echo $studentId; ?>" class="btn btn-light fw-bold text-primary mb-2">
                    <i class="fas fa-user me-2"></i>View Student Profile
                  </a>
                  <a href="/admin/fees/bulk_allocation.php" class="btn btn-outline-light">
                    <i class="fas fa-users-cog me-2"></i>Bulk Allocation
                  </a>
                </div>
              </div>
            </div>
          </div>

          <!-- Alert Notifications -->
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

          <!-- Section 1: Currently Allocated Fees -->
          <div class="card mb-4 shadow-sm border-primary">
            <div class="card-header bg-light d-flex justify-content-between align-items-center">
              <div>
                <h4 class="card-title mb-0 text-dark fw-bold">
                  <i class="fas fa-receipt me-2 text-primary"></i>Currently Allocated Fees
                </h4>
                <div class="text-muted small mt-1">
                  Active fees assigned to this student in the financial system. You can safely de-allocate any fee that has not received payments.
                </div>
              </div>
              <span class="badge bg-primary text-white fw-bold px-3 py-2 fs-6">
                <?php echo count($allocatedFees); ?> Allocated Fee(s)
              </span>
            </div>
            <div class="card-body p-0">
              <?php if (empty($allocatedFees)): ?>
                <div class="text-center py-5">
                  <i class="fas fa-folder-open fa-3x text-muted mb-3 d-block"></i>
                  <h5 class="text-muted">No fees are currently allocated to this student.</h5>
                  <p class="text-secondary small">Select applicable fee structures from the section below and click "Allocate Selected Fees".</p>
                </div>
              <?php else: ?>
                <div class="table-responsive">
                  <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                      <tr>
                        <th style="width: 40px;">#</th>
                        <th>Fee Name / Structure</th>
                        <th>Category</th>
                        <th>Session / Term</th>
                        <th>Base Due (₦)</th>
                        <th>Amount Paid (₦)</th>
                        <th>Remaining (₦)</th>
                        <th>Status</th>
                        <th>Due Date</th>
                        <th class="text-end" style="min-width: 130px;">Action</th>
                      </tr>
                    </thead>
                    <tbody>
                      <?php 
                        $sn = 1;
                        $totalDueSum = 0;
                        $totalPaidSum = 0;
                        $totalBalSum = 0;

                        foreach ($allocatedFees as $af):
                          $afDue = (float) $af['calculated_amount'];
                          $afPaid = (float) $af['direct_fee_paid'];
                          $afBal = max(0.00, $afDue - $afPaid);
                          $totalDueSum += $afDue;
                          $totalPaidSum += $afPaid;
                          $totalBalSum += $afBal;
                          $afStatus = ($afBal <= 0.001) ? 'paid' : ($afPaid > 0 ? 'partially_paid' : 'unpaid');
                      ?>
                      <tr>
                        <td><?php echo $sn++; ?></td>
                        <td>
                          <strong class="text-dark"><?php echo htmlspecialchars((string) $af['fee_name']); ?></strong>
                          <?php if (!empty($af['fee_description']) && $af['fee_description'] !== $af['fee_name']): ?>
                            <br><small class="text-muted"><?php echo htmlspecialchars((string) $af['fee_description']); ?></small>
                          <?php endif; ?>
                        </td>
                        <td>
                          <span class="badge bg-light text-dark border">
                            <?php echo htmlspecialchars((string) $af['fee_category']); ?>
                          </span>
                        </td>
                        <td>
                          <small>
                            <?php echo htmlspecialchars((string) ($af['session_name'] ?? $af['academic_year'] ?? 'Current')); ?>
                            <?php if (!empty($af['term_name']) || !empty($af['semester'])): ?>
                              <span class="text-muted">(<?php echo htmlspecialchars((string) ($af['term_name'] ?? $af['semester'])); ?>)</span>
                            <?php endif; ?>
                          </small>
                        </td>
                        <td class="fw-bold text-dark">
                          ₦<?php echo number_format($afDue, 2); ?>
                        </td>
                        <td class="fw-bold text-success">
                          ₦<?php echo number_format($afPaid, 2); ?>
                        </td>
                        <td class="fw-bold text-danger">
                          ₦<?php echo number_format($afBal, 2); ?>
                        </td>
                        <td>
                          <span class="badge bg-<?php echo $afStatus === 'paid' ? 'success' : ($afStatus === 'partially_paid' ? 'warning' : 'danger'); ?> text-<?php echo $afStatus === 'partially_paid' ? 'dark' : 'white'; ?>">
                            <?php echo ucfirst(str_replace('_', ' ', $afStatus)); ?>
                          </span>
                        </td>
                        <td>
                          <small class="text-muted">
                            <?php echo !empty($af['due_date']) ? date('M d, Y', strtotime($af['due_date'])) : 'N/A'; ?>
                          </small>
                        </td>
                        <td class="text-end">
                          <?php if ($afPaid <= 0.001): ?>
                            <form method="POST" action="" class="d-inline" onsubmit="return confirm('Are you sure you want to de-allocate <?php echo htmlspecialchars(addslashes((string) $af['fee_name'])); ?> from this student? This fee allocation will be completely removed.');">
                              <input type="hidden" name="deallocate_fee" value="1">
                              <input type="hidden" name="student_fee_id" value="<?php echo (int) $af['student_fee_id']; ?>">
                              <button type="submit" class="btn btn-sm btn-outline-danger" title="De-allocate this fee">
                                <i class="fas fa-trash-alt me-1"></i> De-allocate
                              </button>
                            </form>
                          <?php else: ?>
                            <button type="button" class="btn btn-sm btn-light border text-muted" disabled title="Cannot deallocate: payments of ₦<?php echo number_format($afPaid, 2); ?> have been recorded">
                              <i class="fas fa-lock me-1"></i> Paid (Locked)
                            </button>
                          <?php endif; ?>
                        </td>
                      </tr>
                      <?php endforeach; ?>
                    </tbody>
                    <tfoot class="table-light">
                      <tr class="fw-bold">
                        <td colspan="4" class="text-end">Total Allocated Fees:</td>
                        <td class="text-dark">₦<?php echo number_format($totalDueSum, 2); ?></td>
                        <td class="text-success">₦<?php echo number_format($totalPaidSum, 2); ?></td>
                        <td class="text-danger">₦<?php echo number_format($totalBalSum, 2); ?></td>
                        <td colspan="3"></td>
                      </tr>
                    </tfoot>
                  </table>
                </div>
              <?php endif; ?>
            </div>
          </div>

          <!-- Section 2: Available Fee Structures to Allocate -->
          <div class="card shadow-sm border-secondary">
            <div class="card-header bg-white d-flex flex-wrap justify-content-between align-items-center gap-2">
              <div>
                <h4 class="card-title mb-0 text-dark fw-bold">
                  <i class="fas fa-layer-group me-2 text-info"></i>Available Fee Structures to Allocate
                </h4>
                <div class="text-muted small mt-1">
                  <?php if ($showAll): ?>
                    Showing <strong>all active fee structures</strong> in the system.
                  <?php else: ?>
                    Filtered for student's session (<strong><?php echo htmlspecialchars($sessionLabel); ?></strong>), class, intake category (<strong><?php echo htmlspecialchars($studentCategory); ?></strong>), and gender.
                  <?php endif; ?>
                </div>
              </div>
              <div class="d-flex align-items-center gap-2">
                <a href="/admin/fee_structure.php?student_id=<?php echo $studentId; ?>&show_all=<?php echo $showAll ? '0' : '1'; ?>" class="btn btn-sm <?php echo $showAll ? 'btn-outline-primary' : 'btn-outline-secondary'; ?>">
                  <i class="fas <?php echo $showAll ? 'fa-filter' : 'fa-list-ul'; ?> me-1"></i>
                  <?php echo $showAll ? 'Show Filtered Applicable Fees' : 'Show All Available Structures'; ?>
                </a>
              </div>
            </div>
            <div class="card-body">
              <form method="POST" action="" id="feeAllocationForm">
                <div class="table-responsive">
                  <table class="table table-hover align-middle">
                    <thead class="table-light">
                      <tr>
                        <th style="width: 40px;">
                          <input type="checkbox" id="selectAllFees" class="form-check-input">
                        </th>
                        <th>Fee Structure Name</th>
                        <th>Intake Category</th>
                        <th>Target Class / Session</th>
                        <th>Amount (₦)</th>
                        <th>Frequency</th>
                        <th>Allocation Status</th>
                        <th class="text-end">Action</th>
                      </tr>
                    </thead>
                    <tbody>
                      <?php if (empty($feeStructures)): ?>
                        <tr>
                          <td colspan="8" class="text-center text-muted py-5">
                            <i class="fas fa-search fa-2x mb-2 d-block text-muted"></i>
                            No matching fee structures found for this student.
                            <br>
                            <a href="/admin/fee_structure.php?student_id=<?php echo $studentId; ?>&show_all=1" class="btn btn-sm btn-primary mt-3">
                              <i class="fas fa-list me-1"></i> View All System Fee Structures
                            </a>
                          </td>
                        </tr>
                      <?php else: ?>
                        <?php 
                          foreach ($feeStructures as $fs): 
                            $fsId = (int) $fs['id'];
                            $isAlreadyAllocated = in_array($fsId, $alreadyAllocatedFsIds, true);
                        ?>
                        <tr class="<?php echo $isAlreadyAllocated ? 'table-light text-muted' : ''; ?>">
                          <td>
                            <input type="checkbox" 
                                   name="fee_ids[]" 
                                   value="<?php echo $fsId; ?>" 
                                   class="form-check-input fee-checkbox"
                                   <?php echo $isAlreadyAllocated ? 'disabled' : ''; ?>>
                          </td>
                          <td>
                            <strong class="<?php echo $isAlreadyAllocated ? 'text-muted' : 'text-dark'; ?>">
                              <?php echo htmlspecialchars((string) $fs['name']); ?>
                            </strong>
                            <?php if (!empty($fs['is_mandatory'])): ?>
                              <span class="badge bg-danger ms-1" style="font-size: 10px;">Mandatory</span>
                            <?php endif; ?>
                            <?php if (!empty($fs['description'])): ?>
                              <br><small class="text-secondary"><?php echo htmlspecialchars((string) $fs['description']); ?></small>
                            <?php endif; ?>
                          </td>
                          <td>
                            <span class="badge bg-light text-dark border">
                              <?php 
                                $cat = strtoupper(trim((string) ($fs['category'] ?? 'All')));
                                echo match($cat) {
                                  'NI' => 'New Intake (NI)',
                                  'OS' => 'Returning (OS)',
                                  default => 'All Students'
                                };
                              ?>
                            </span>
                          </td>
                          <td>
                            <small>
                              Class: <?php echo htmlspecialchars((string) ($fs['fee_class_name'] ?? 'All Classes')); ?>
                              <br>
                              Session: <?php echo htmlspecialchars((string) ($fs['fee_session_name'] ?? 'All Sessions')); ?>
                              <?php if (!empty($fs['fee_term_name'])): ?>
                                (<?php echo htmlspecialchars((string) $fs['fee_term_name']); ?>)
                              <?php endif; ?>
                            </small>
                          </td>
                          <td class="fw-bold <?php echo $isAlreadyAllocated ? 'text-muted' : 'text-primary'; ?>">
                            ₦<?php echo number_format((float) ($fs['amount'] ?? 0), 2); ?>
                          </td>
                          <td>
                            <span class="badge bg-secondary text-white">
                              <?php echo htmlspecialchars(ucwords(str_replace('_', ' ', (string) ($fs['frequency'] ?? 'per_term')))); ?>
                            </span>
                          </td>
                          <td>
                            <?php if ($isAlreadyAllocated): ?>
                              <span class="badge bg-success">
                                <i class="fas fa-check-circle me-1"></i> Allocated
                              </span>
                            <?php else: ?>
                              <span class="badge bg-warning text-dark">
                                <i class="fas fa-clock me-1"></i> Ready to Allocate
                              </span>
                            <?php endif; ?>
                          </td>
                          <td class="text-end">
                            <?php if ($isAlreadyAllocated): ?>
                              <span class="text-muted small"><i class="fas fa-check me-1"></i>Assigned</span>
                            <?php else: ?>
                              <button type="submit" 
                                      name="allocate_single_fee" 
                                      value="<?php echo $fsId; ?>" 
                                      class="btn btn-sm btn-primary">
                                <i class="fas fa-plus me-1"></i> Allocate
                              </button>
                            <?php endif; ?>
                          </td>
                        </tr>
                        <?php endforeach; ?>
                      <?php endif; ?>
                    </tbody>
                  </table>
                </div>

                <?php if (!empty($feeStructures)): ?>
                <div class="card-action mt-3 pt-3 border-top d-flex justify-content-between align-items-center">
                  <div class="text-muted small">
                    Check the boxes above to allocate multiple fee structures at once.
                  </div>
                  <button type="submit" name="allocate_fees" class="btn btn-primary fw-bold px-4">
                    <i class="fas fa-check-circle me-2"></i>Allocate Selected Fees
                  </button>
                </div>
                <?php endif; ?>
              </form>
            </div>
          </div>

        </div>
      </div>
      @include('admin.partials.footer')
    </div>
  </div>

  <script>
    document.addEventListener('DOMContentLoaded', function () {
      const selectAll = document.getElementById('selectAllFees');
      const checkboxes = document.querySelectorAll('.fee-checkbox:not(:disabled)');

      if (selectAll) {
        selectAll.addEventListener('change', function () {
          checkboxes.forEach(function (checkbox) {
            checkbox.checked = selectAll.checked;
          });
        });

        checkboxes.forEach(function (checkbox) {
          checkbox.addEventListener('change', function () {
            if (!this.checked) {
              selectAll.checked = false;
            } else {
              const allChecked = Array.from(checkboxes).every(function (cb) { return cb.checked; });
              selectAll.checked = allChecked;
            }
          });
        });
      }
    });
  </script>
</body>
</html>
