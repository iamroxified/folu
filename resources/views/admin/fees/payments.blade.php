<?php
require_once base_path('db/config.php');
require_once base_path('db/functions.php');

if (session_status() === PHP_SESSION_NONE) {
    @session_start();
}

if (!isset($_SESSION['adid']) && !auth()->check()) {
    if (!headers_sent()) {
        header('Location: /admin/login.php');
        exit;
    }
}

$importSuccess = null;
$importErrors = [];

// Handle CSV Payment Import
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['payment_csv_file'])) {
    $file = $_FILES['payment_csv_file'];

    if ($file['error'] === 0) {
        $filePath = $file['tmp_name'];
        $firstChunk = file_get_contents($filePath, false, null, 0, 2048);
        $firstChunk = preg_replace('/\x{EF}\x{BB}\x{BF}/', '', $firstChunk);

        // Detect delimiter: comma, tab, or semicolon
        $delimiter = ',';
        if (substr_count($firstChunk, "\t") > substr_count($firstChunk, ',')) {
            $delimiter = "\t";
        } elseif (substr_count($firstChunk, ';') > substr_count($firstChunk, ',')) {
            $delimiter = ';';
        }

        $handle = fopen($filePath, 'r');
        $updatedCount = 0;
        $unmatchedCount = 0;
        $rowNum = 0;

        // Map column indices by inspecting header row
        $colIndexes = [
            'name' => null,
            'expected' => null,
            'actual' => null,
            'balance' => null,
        ];

        $firstRow = fgetcsv($handle, 1000, $delimiter);
        if ($firstRow !== FALSE) {
            if (isset($firstRow[0])) {
                $firstRow[0] = preg_replace('/\x{EF}\x{BB}\x{BF}/', '', (string) $firstRow[0]);
            }

            $headerLower = array_map(fn($v) => strtolower(trim((string) $v)), $firstRow);

            foreach ($headerLower as $idx => $heading) {
                if (in_array($heading, ['name', 'student_name', 'full_name', 'fullname', 'student'], true)) {
                    $colIndexes['name'] = $idx;
                } elseif (in_array($heading, ['expected', 'expected_amount', 'amount_due', 'due_amount', 'expected (n)', 'expected (naira)'], true)) {
                    $colIndexes['expected'] = $idx;
                } elseif (in_array($heading, ['actual', 'actual_paid', 'amount_paid', 'paid', 'paid_amount', 'actual paid (n)'], true)) {
                    $colIndexes['actual'] = $idx;
                } elseif (in_array($heading, ['balance', 'amount_balance', 'balance_due', 'balance (n)'], true)) {
                    $colIndexes['balance'] = $idx;
                }
            }

            $hasHeader = ($colIndexes['name'] !== null || in_array('sn', $headerLower, true) || in_array('s/n', $headerLower, true) || in_array('name', $headerLower, true));

            if (!$hasHeader) {
                rewind($handle);
            }
        }

        while (($data = fgetcsv($handle, 1000, $delimiter)) !== FALSE) {
            $rowNum++;
            try {
                if (isset($data[0])) {
                    $data[0] = preg_replace('/\x{EF}\x{BB}\x{BF}/', '', (string) $data[0]);
                }

                $c0 = trim((string) ($data[0] ?? ''));
                $c1 = trim((string) ($data[1] ?? ''));
                $c2 = trim((string) ($data[2] ?? ''));
                $c3 = trim((string) ($data[3] ?? ''));
                $c4 = trim((string) ($data[4] ?? ''));

                // Skip header row if re-encountered
                if (in_array(strtolower($c0), ['sn', 's/n', 'id', 'name', 'full name', 'fullname'], true) ||
                    in_array(strtolower($c1), ['name', 'full name', 'fullname'], true)) {
                    continue;
                }

                // Layout mapping for: sn, name, expected, actual, balance
                if ($colIndexes['name'] !== null && isset($data[$colIndexes['name']])) {
                    $rawName = trim((string) $data[$colIndexes['name']]);
                    $expectedRaw = isset($colIndexes['expected']) && isset($data[$colIndexes['expected']]) ? trim((string) $data[$colIndexes['expected']]) : '';
                    $actualRaw = isset($colIndexes['actual']) && isset($data[$colIndexes['actual']]) ? trim((string) $data[$colIndexes['actual']]) : '';
                    $balanceRaw = isset($colIndexes['balance']) && isset($data[$colIndexes['balance']]) ? trim((string) $data[$colIndexes['balance']]) : '';
                } elseif (is_numeric($c0) || empty($c2)) {
                    // Exact layout: sn (col 0), name (col 1), expected (col 2), actual (col 3), balance (col 4)
                    $rawName = $c1;
                    $expectedRaw = $c2;
                    $actualRaw = $c3;
                    $balanceRaw = $c4;
                } else {
                    // Layout: name (col 0), expected (col 1), actual (col 2), balance (col 3)
                    $rawName = $c0;
                    $expectedRaw = $c1;
                    $actualRaw = $c2;
                    $balanceRaw = $c3;
                }

                if (empty($rawName)) {
                    continue;
                }

                $expected = (float) preg_replace('/[^\d.]/', '', $expectedRaw);
                $actual = (float) preg_replace('/[^\d.]/', '', $actualRaw);
                $balance = $balanceRaw !== '' ? (float) preg_replace('/[^\d.]/', '', $balanceRaw) : max(0.00, $expected - $actual);

                // Determine fee status
                if ($actual >= $expected && $expected > 0) {
                    $status = 'paid';
                } elseif ($actual > 0 && $actual < $expected) {
                    $status = 'partial';
                } else {
                    $status = 'pending';
                }

                // Check students table matching surname . " " . firstname . " " . other_name == or LIKE name
                $student = find_student_by_full_name($rawName);

                if ($student) {
                    $studentId = (int) $student['id'];
                    $academicSessionId = $student['current_session_id'] ?? $student['academic_session_link'] ?? 1;

                    // Check if fee record exists in student_fees for this student
                    $existingFee = QueryDB(
                        "SELECT id FROM student_fees WHERE student_id = ? ORDER BY id DESC LIMIT 1",
                        [$studentId]
                    )->fetch(PDO::FETCH_ASSOC);

                    if ($existingFee) {
                        $feeId = (int) $existingFee['id'];
                        $stmt = $pdo->prepare(
                            "UPDATE student_fees
                             SET amount_due = ?, amount_paid = ?, balance = ?, status = ?, updated_at = NOW()
                             WHERE id = ?"
                        );
                        $stmt->execute([$expected, $actual, $balance, $status, $feeId]);
                    } else {
                        $defaultFeeStructId = QueryDB("SELECT id FROM fee_structures ORDER BY id ASC LIMIT 1")->fetchColumn() ?: 1;
                        $stmt = $pdo->prepare(
                            "INSERT INTO student_fees (student_id, fee_structure_id, amount_due, amount_paid, balance, due_date, status, academic_year, created_at, updated_at)
                             VALUES (?, ?, ?, ?, ?, DATE_ADD(CURDATE(), INTERVAL 30 DAY), ?, ?, NOW(), NOW())"
                        );
                        $stmt->execute([$studentId, $defaultFeeStructId, $expected, $actual, $balance, $status, (string) $academicSessionId]);
                    }

                    $updatedCount++;
                } else {
                    $unmatchedCount++;
                    $importErrors[] = "Row {$rowNum}: Could not match student \"{$rawName}\" in database.";
                }
            } catch (Exception $e) {
                $importErrors[] = "Row {$rowNum}: Error processing - " . $e->getMessage();
            }
        }
        fclose($handle);

        $importSuccess = "Successfully updated/imported payment records for {$updatedCount} student(s).";
        if ($unmatchedCount > 0) {
            $importSuccess .= " ({$unmatchedCount} student name(s) could not be matched in database)";
        }
    } else {
        $importErrors[] = "Please upload a valid CSV file.";
    }
}

// Handle manual payment status/amount update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update_payment') {
    $feeId = filter_input(INPUT_POST, 'fee_id', FILTER_VALIDATE_INT);
    $amountDue = (float) filter_input(INPUT_POST, 'amount_due', FILTER_VALIDATE_FLOAT);
    $amountPaid = (float) filter_input(INPUT_POST, 'amount_paid', FILTER_VALIDATE_FLOAT);
    $balance = max(0.00, $amountDue - $amountPaid);

    if ($amountPaid >= $amountDue && $amountDue > 0) {
        $status = 'paid';
    } elseif ($amountPaid > 0) {
        $status = 'partial';
    } else {
        $status = 'pending';
    }

    if ($feeId) {
        $stmt = $pdo->prepare("UPDATE student_fees SET amount_due = ?, amount_paid = ?, balance = ?, status = ?, updated_at = NOW() WHERE id = ?");
        $stmt->execute([$amountDue, $amountPaid, $balance, $status, $feeId]);
        $importSuccess = "Payment record updated successfully.";
    }
}

// Filters Setup
$search = trim((string) ($_GET['search'] ?? ''));
$classFilter = filter_input(INPUT_GET, 'class_id', FILTER_VALIDATE_INT);
$statusFilter = trim((string) ($_GET['status'] ?? ''));
$sessionFilter = filter_input(INPUT_GET, 'session_id', FILTER_VALIDATE_INT);

// Build SQL Query with Filters
$whereConditions = ["1=1"];
$queryParams = [];

if ($search !== '') {
    $whereConditions[] = "(s.first_name LIKE ? OR s.last_name LIKE ? OR s.other_names LIKE ? OR s.admission_no LIKE ?)";
    $searchLike = '%' . $search . '%';
    $queryParams[] = $searchLike;
    $queryParams[] = $searchLike;
    $queryParams[] = $searchLike;
    $queryParams[] = $searchLike;
}

if ($classFilter) {
    $classFilterCols = [];
    if (schema_has_column('students', 'current_class_id')) {
        $classFilterCols[] = "s.current_class_id = ?";
        $queryParams[] = $classFilter;
    }
    if (schema_has_column('students', 'class_link')) {
        $classFilterCols[] = "s.class_link = ?";
        $queryParams[] = $classFilter;
    }
    if (schema_has_column('students', 'class_id')) {
        $classFilterCols[] = "s.class_id = ?";
        $queryParams[] = $classFilter;
    }
    if (!empty($classFilterCols)) {
        $whereConditions[] = "(" . implode(" OR ", $classFilterCols) . ")";
    }
}

if ($statusFilter !== '') {
    $whereConditions[] = "sf.status = ?";
    $queryParams[] = $statusFilter;
}

if ($sessionFilter) {
    $whereConditions[] = "sf.academic_year = ?";
    $queryParams[] = (string) $sessionFilter;
}

$whereSql = implode(' AND ', $whereConditions);

$hasClassTable = schema_has_table('classes');
$hasSchoolClassesTable = schema_has_table('school_classes');

$classSelectName = "COALESCE(";
if ($hasSchoolClassesTable) {
    $classSelectName .= "sc.class_name, ";
}
if ($hasClassTable) {
    $classSelectName .= "c.class_name, ";
}
$classSelectName .= "'Unassigned') AS class_name";

$classSelectArm = "COALESCE(";
if ($hasSchoolClassesTable) {
    $classSelectArm .= "sc.section, ";
}
if ($hasClassTable) {
    $classSelectArm .= "c.class_arm, ";
}
$classSelectArm .= "'') AS class_arm";

$paymentJoins = "LEFT JOIN students s ON sf.student_id = s.id";

if ($hasSchoolClassesTable) {
    $scJoinCols = [];
    if (schema_has_column('students', 'current_class_id')) $scJoinCols[] = "s.current_class_id = sc.id";
    if (schema_has_column('students', 'class_link')) $scJoinCols[] = "s.class_link = sc.id";
    if (schema_has_column('students', 'class_id')) $scJoinCols[] = "s.class_id = sc.id";
    if (!empty($scJoinCols)) {
        $paymentJoins .= "\n     LEFT JOIN school_classes sc ON (" . implode(" OR ", $scJoinCols) . ")";
    }
}

if ($hasClassTable) {
    $cJoinCols = [];
    if (schema_has_column('students', 'current_class_id')) $cJoinCols[] = "s.current_class_id = c.id";
    if (schema_has_column('students', 'class_link')) $cJoinCols[] = "s.class_link = c.id";
    if (schema_has_column('students', 'class_id')) $cJoinCols[] = "s.class_id = c.id";
    if (!empty($cJoinCols)) {
        $paymentJoins .= "\n     LEFT JOIN classes c ON (" . implode(" OR ", $cJoinCols) . ")";
    }
}

$paymentJoins .= "\n     LEFT JOIN fee_structures fs ON sf.fee_structure_id = fs.id";

$studentFees = QueryDB(
    "SELECT sf.*,
            s.first_name,
            s.last_name,
            s.other_names,
            s.admission_no,
            {$classSelectName},
            {$classSelectArm},
            fs.name AS fee_name,
            fs.fee_type,
            fs.amount AS fee_amount
     FROM student_fees sf
     {$paymentJoins}
     WHERE {$whereSql}
     ORDER BY sf.created_at DESC, sf.id DESC
     LIMIT 200",
    $queryParams
)->fetchAll(PDO::FETCH_ASSOC);

// Summary Stats
$summaryStats = QueryDB(
    "SELECT COALESCE(SUM(sf.amount_due), 0) AS total_expected,
            COALESCE(SUM(sf.amount_paid), 0) AS total_actual,
            COALESCE(SUM(sf.balance), 0) AS total_balance,
            COUNT(sf.id) AS total_records
     FROM student_fees sf
     LEFT JOIN students s ON sf.student_id = s.id
     WHERE {$whereSql}",
    $queryParams
)->fetch(PDO::FETCH_ASSOC);

// Dropdown lists
$classesList = schema_has_table('school_classes')
    ? QueryDB("SELECT id, class_name, COALESCE(section, '') AS class_arm FROM school_classes ORDER BY class_name ASC")->fetchAll()
    : (schema_has_table('classes') ? QueryDB("SELECT id, class_name, class_arm FROM classes ORDER BY class_name ASC")->fetchAll() : []);

$sessionsList = schema_has_table('academic_sessions')
    ? QueryDB("SELECT id, session_name FROM academic_sessions ORDER BY is_active DESC, start_date DESC")->fetchAll()
    : [];

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <title>All Payments &amp; Fee Management</title>
    @include('admin.partials.links')
    <style>
        .summary-box {
            border-radius: 12px;
            padding: 20px;
            color: #fff;
            box-shadow: 0 4px 12px rgba(0,0,0,0.08);
            margin-bottom: 20px;
        }
        .bg-expected { background: linear-gradient(135deg, #1e3a8a, #3b82f6); }
        .bg-actual { background: linear-gradient(135deg, #15803d, #22c55e); }
        .bg-balance { background: linear-gradient(135deg, #b91c1c, #ef4444); }
        .bg-records { background: linear-gradient(135deg, #720922, #8e1532); }
        .summary-box h3 { font-size: 24px; font-weight: 800; margin-bottom: 4px; }
        .summary-box p { font-size: 13px; opacity: 0.9; margin: 0; }
    </style>
</head>

<body>
    <div class="wrapper">
        @include('admin.partials.sidebar')

        <div class="main-panel">
            @include('admin.partials.header')
            <div class="container">
                <div class="page-inner">
                    <div class="d-flex align-items-left flex-column flex-md-row pb-3">
                        <div>
                            <h2 class="text-dark fw-bold">Student Payments &amp; Fee Management</h2>
                            <p class="text-muted mb-0">View all student fee records, filter by class or status, and import payment CSV updates.</p>
                        </div>
                        <div class="ml-md-auto py-2 py-md-0 d-flex gap-2">
                            <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#importPaymentModal">
                                <i class="fas fa-file-import me-1"></i> Import Payments CSV
                            </button>
                        </div>
                    </div>

                    <!-- Summary Stats Cards -->
                    <div class="row">
                        <div class="col-md-3 col-6">
                            <div class="summary-box bg-expected">
                                <p>Total Expected Fees</p>
                                <h3>₦<?php echo number_format((float) $summaryStats['total_expected'], 2); ?></h3>
                            </div>
                        </div>
                        <div class="col-md-3 col-6">
                            <div class="summary-box bg-actual">
                                <p>Total Actual Paid</p>
                                <h3>₦<?php echo number_format((float) $summaryStats['total_actual'], 2); ?></h3>
                            </div>
                        </div>
                        <div class="col-md-3 col-6">
                            <div class="summary-box bg-balance">
                                <p>Outstanding Balance</p>
                                <h3>₦<?php echo number_format((float) $summaryStats['total_balance'], 2); ?></h3>
                            </div>
                        </div>
                        <div class="col-md-3 col-6">
                            <div class="summary-box bg-records">
                                <p>Total Records</p>
                                <h3><?php echo number_format((int) $summaryStats['total_records']); ?></h3>
                            </div>
                        </div>
                    </div>

                    <!-- Alerts -->
                    <?php if ($importSuccess): ?>
                        <div class="alert alert-success alert-dismissible fade show">
                            <i class="fas fa-check-circle me-2"></i> <?php echo htmlspecialchars($importSuccess); ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    <?php endif; ?>

                    <?php if (!empty($importErrors)): ?>
                        <div class="alert alert-warning alert-dismissible fade show">
                            <strong><i class="fas fa-exclamation-triangle me-2"></i> Import Notes / Warnings:</strong>
                            <ul class="mb-0 mt-1">
                                <?php foreach (array_slice($importErrors, 0, 5) as $err): ?>
                                    <li><?php echo htmlspecialchars($err); ?></li>
                                <?php endforeach; ?>
                                <?php if (count($importErrors) > 5): ?>
                                    <li>...and <?php echo count($importErrors) - 5; ?> more warnings.</li>
                                <?php endif; ?>
                            </ul>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    <?php endif; ?>

                    <!-- Filter Form -->
                    <div class="card mb-4">
                        <div class="card-body">
                            <form method="GET" class="row g-3 align-items-center">
                                <div class="col-md-4">
                                    <label class="form-label fw-bold">Search Student / Admission / Receipt</label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="fas fa-search"></i></span>
                                        <input type="text" name="search" class="form-control" placeholder="Enter student name or admission no..." value="<?php echo htmlspecialchars($search); ?>">
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label fw-bold">Class</label>
                                    <select name="class_id" class="form-select">
                                        <option value="">All Classes</option>
                                        <?php foreach ($classesList as $c): ?>
                                            <option value="<?php echo $c['id']; ?>" <?php echo $classFilter == $c['id'] ? 'selected' : ''; ?>>
                                                <?php echo htmlspecialchars($c['class_name'] . ' ' . ($c['class_arm'] ?? '')); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="col-md-2">
                                    <label class="form-label fw-bold">Payment Status</label>
                                    <select name="status" class="form-select">
                                        <option value="">All Statuses</option>
                                        <option value="paid" <?php echo $statusFilter === 'paid' ? 'selected' : ''; ?>>Paid</option>
                                        <option value="partial" <?php echo $statusFilter === 'partial' ? 'selected' : ''; ?>>Partial</option>
                                        <option value="pending" <?php echo $statusFilter === 'pending' ? 'selected' : ''; ?>>Pending / Unpaid</option>
                                    </select>
                                </div>
                                <div class="col-md-3 d-flex align-items-end gap-2" style="margin-top: 32px;">
                                    <button type="submit" class="btn btn-primary flex-grow-1"><i class="fas fa-filter me-1"></i> Filter</button>
                                    <a href="?" class="btn btn-outline-secondary"><i class="fas fa-undo"></i> Reset</a>
                                </div>
                            </form>
                        </div>
                    </div>

                    <!-- Payments Table -->
                    <div class="card">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h4 class="card-title mb-0">Student Fee Records</h4>
                            <span class="badge bg-secondary"><?php echo count($studentFees); ?> records shown</span>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table id="paymentsTable" class="display table table-hover">
                                    <thead>
                                        <tr>
                                            <th>SN</th>
                                            <th>Student Name</th>
                                            <th>Admission No</th>
                                            <th>Class</th>
                                            <th>Expected (₦)</th>
                                            <th>Actual Paid (₦)</th>
                                            <th>Balance (₦)</th>
                                            <th>Status</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if (empty($studentFees)): ?>
                                            <tr>
                                                <td colspan="9" class="text-center py-4 text-muted">
                                                    <i class="fas fa-info-circle fa-2x mb-2 d-block"></i> No payment records found matching your filters.
                                                </td>
                                            </tr>
                                        <?php endif; ?>
                                        <?php $sn = 1; foreach ($studentFees as $fee): ?>
                                            <tr>
                                                <td><?php echo $sn++; ?></td>
                                                <td>
                                                    <strong><?php echo htmlspecialchars(trim(($fee['last_name'] ?? '') . ' ' . ($fee['first_name'] ?? '') . ' ' . ($fee['other_names'] ?? ''))); ?></strong>
                                                </td>
                                                <td><code><?php echo htmlspecialchars((string) ($fee['admission_no'] ?? 'N/A')); ?></code></td>
                                                <td><?php echo htmlspecialchars(trim(($fee['class_name'] ?? '') . ' ' . ($fee['class_arm'] ?? ''))); ?></td>
                                                <td class="fw-bold">₦<?php echo number_format((float) ($fee['amount_due'] ?? 0), 2); ?></td>
                                                <td class="text-success fw-bold">₦<?php echo number_format((float) ($fee['amount_paid'] ?? 0), 2); ?></td>
                                                <td class="text-danger fw-bold">₦<?php echo number_format((float) ($fee['balance'] ?? 0), 2); ?></td>
                                                <td>
                                                    <?php if (($fee['status'] ?? '') === 'paid'): ?>
                                                        <span class="badge bg-success">Paid</span>
                                                    <?php elseif (($fee['status'] ?? '') === 'partial'): ?>
                                                        <span class="badge bg-warning text-dark">Partial</span>
                                                    <?php else: ?>
                                                        <span class="badge bg-danger">Pending</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td class="text-nowrap">
                                                    <a href="/admin/view_students.php?id=<?php echo (int) $fee['student_id']; ?>" class="btn btn-sm btn-info text-white me-1" title="View student payments and details">
                                                        <i class="fas fa-eye me-1"></i> View Payments
                                                    </a>
                                                    <button type="button" class="btn btn-sm btn-outline-primary"
                                                            onclick="openEditPaymentModal(<?php echo htmlspecialchars(json_encode($fee)); ?>)">
                                                        <i class="fas fa-edit me-1"></i> Edit
                                                    </button>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
            @include('admin.partials.footer')
        </div>
    </div>

    <!-- Import Payments Modal -->
    <div class="modal fade" id="importPaymentModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title fw-bold"><i class="fas fa-file-csv me-2"></i> Import Student Payments via CSV</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST" enctype="multipart/form-data">
                    <div class="modal-body">
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle me-1"></i>
                            <strong>Student Matching Rule:</strong> Before updating a payment, the system matches each student by checking if <code>surname . " " . firstname . " " . other_name</code> is equal or LIKE the <strong>name</strong> column in your CSV.
                        </div>

                        <p class="mb-2">Your CSV file should have the following headers:</p>
                        <table class="table table-bordered table-sm mb-3 no-datatable">
                            <thead class="table-light">
                                <tr>
                                    <th>sn (Optional)</th>
                                    <th>name</th>
                                    <th>expected</th>
                                    <th>actual</th>
                                    <th>balance (Optional)</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>1</td>
                                    <td>Adeyemi Babatunde John</td>
                                    <td>50000</td>
                                    <td>50000</td>
                                    <td>0</td>
                                </tr>
                                <tr>
                                    <td>2</td>
                                    <td>Okonkwo Chinedu</td>
                                    <td>45000</td>
                                    <td>25000</td>
                                    <td>20000</td>
                                </tr>
                            </tbody>
                        </table>

                        <div class="form-group mb-3">
                            <label for="payment_csv_file" class="form-label fw-bold">Select Payments CSV File <span class="text-danger">*</span></label>
                            <input type="file" name="payment_csv_file" id="payment_csv_file" class="form-control" accept=".csv" required>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary"><i class="fas fa-upload me-1"></i> Upload &amp; Update Payments</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Edit Payment Modal -->
    <div class="modal fade" id="editPaymentModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-secondary text-white">
                    <h5 class="modal-title fw-bold"><i class="fas fa-edit me-2"></i> Update Student Payment Record</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST">
                    <input type="hidden" name="action" value="update_payment">
                    <input type="hidden" name="fee_id" id="edit_fee_id">

                    <div class="modal-body">
                        <div class="form-group mb-3">
                            <label class="form-label fw-bold">Student Name</label>
                            <input type="text" id="edit_student_name" class="form-control" readonly>
                        </div>
                        <div class="form-group mb-3">
                            <label class="form-label fw-bold">Expected Amount (₦) <span class="text-danger">*</span></label>
                            <input type="number" step="0.01" name="amount_due" id="edit_amount_due" class="form-control" required>
                        </div>
                        <div class="form-group mb-3">
                            <label class="form-label fw-bold">Actual Amount Paid (₦) <span class="text-danger">*</span></label>
                            <input type="number" step="0.01" name="amount_paid" id="edit_amount_paid" class="form-control" required>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Save Payment Updates</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

            <script>
        function openEditPaymentModal(fee) {
            document.getElementById('edit_fee_id').value = fee.id;
            document.getElementById('edit_student_name').value = (fee.last_name || '') + ' ' + (fee.first_name || '') + ' ' + (fee.other_names || '');
            document.getElementById('edit_amount_due').value = fee.amount_due || 0;
            document.getElementById('edit_amount_paid').value = fee.amount_paid || 0;
            var modal = new bootstrap.Modal(document.getElementById('editPaymentModal'));
            modal.show();
        }
    </script>
</body>

</html>
