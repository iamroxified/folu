<?php
// Start session safely
if (session_status() === PHP_SESSION_NONE) {
    @session_start();
}

// Include database configuration and functions
require_once base_path('db/config.php');
require_once base_path('db/functions.php');
require_once resource_path('views/admin/school_functions.php');

// Check if user is logged in
if (!isset($_SESSION['adid']) && !auth()->check()) {
    header('Location: /admin/login.php');
    exit;
}

$rawFullCols = QueryDB("SHOW FULL COLUMNS FROM students")->fetchAll(PDO::FETCH_ASSOC);
$generatedColumns = ['class_link', 'academic_session_link', 'term_link', 'session_link'];
$studentColumns = [];
foreach ($rawFullCols as $c) {
    if (empty($c['Extra']) || stripos($c['Extra'], 'GENERATED') === false) {
        if (!in_array($c['Field'], $generatedColumns, true)) {
            $studentColumns[] = $c['Field'];
        }
    }
}
$studentIdentifierField = in_array('admission_no', $studentColumns, true) ? 'admission_no' : 'student_number';
$studentIdentifierLabel = $studentIdentifierField === 'admission_no' ? 'Admission No' : 'Student Number';

$classesList = schema_has_column('students', 'current_class_id') && schema_has_table('school_classes')
    ? QueryDB("SELECT id, class_name, COALESCE(section, '') AS class_arm FROM school_classes WHERE status = 'active' ORDER BY grade_level, class_name, section")->fetchAll()
    : QueryDB("SELECT id, class_name, class_arm FROM classes ORDER BY class_level, class_arm")->fetchAll();

$sessionsList = QueryDB("SELECT id, session_name, is_active FROM academic_sessions ORDER BY is_active DESC, start_date DESC")->fetchAll();

$termsList = schema_has_table('terms')
    ? QueryDB("SELECT id, term_name, term_number, is_active FROM terms ORDER BY term_number ASC")->fetchAll()
    : [
        ['id' => 1, 'term_name' => 'First Term', 'is_active' => 1],
        ['id' => 2, 'term_name' => 'Second Term', 'is_active' => 0],
        ['id' => 3, 'term_name' => 'Third Term', 'is_active' => 0],
    ];

$feeStructuresList = schema_has_table('fee_structures')
    ? QueryDB("SELECT id, name, fee_type, amount, class_id FROM fee_structures WHERE is_active = 1 ORDER BY fee_type ASC, name ASC")->fetchAll(PDO::FETCH_ASSOC)
    : [];

$feeTypeEnumOptions = [
    'tuition' => 'Tuition / School Fees',
    'uniform' => 'Uniform Fee',
    'transport' => 'Transport / Bus Fee',
    'exam' => 'Examination Fee',
    'registration' => 'Registration Fee',
    'library' => 'Library / Books Fee',
    'sports' => 'Sports Fee',
    'laboratory' => 'Laboratory / Science Fee',
    'other' => 'Other Charges / Miscellaneous',
];

// Handle Sample CSV Downloads
if (isset($_GET['download_sample']) && $_GET['download_sample'] === 'payment_csv') {
    if (ob_get_level()) { ob_end_clean(); }
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="sample_student_payments.csv"');
    $out = fopen('php://output', 'w');
    fputcsv($out, ['sn', 'name', 'paid']);
    fputcsv($out, [1, 'Adeyemi Babatunde John', 40000]);
    fputcsv($out, [2, 'Okonkwo Chinedu', 59500]);
    fputcsv($out, [3, 'Bello Mohammed Aliyu', 25000]);
    fclose($out);
    exit;
}

if (isset($_GET['download_sample']) && $_GET['download_sample'] === 'student_csv') {
    if (ob_get_level()) { ob_end_clean(); }
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="sample_students_import.csv"');
    $out = fopen('php://output', 'w');
    fputcsv($out, ['sn', 'name', 'status']);
    fputcsv($out, [1, 'Adeyemi Babatunde John', 'active']);
    fputcsv($out, [2, 'Okonkwo Chinedu', 'active']);
    fputcsv($out, [3, 'Bello Mohammed Aliyu', 'active']);
    fclose($out);
    exit;
}

// Balance Import State (checks session from Laravel controller redirect or inline processing)
$balanceImportSummary = session('balance_import_summary') ?? null;
$balanceImportSuccess = session('balance_import_success') ?? null;
$balanceRowErrors = [];

// Handle Actual Balance Payment Import (POST)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && (isset($_POST['import_actual_balance']) || (isset($_POST['action']) && $_POST['action'] === 'import_actual_balance'))) {
    $balFile = $_FILES['balance_csv_file'] ?? $_FILES['csv_file'] ?? null;
    $targetSessionId = filter_input(INPUT_POST, 'session_id', FILTER_VALIDATE_INT) ?: (int) ($sessionsList[0]['id'] ?? 2);
    $targetTermId = filter_input(INPUT_POST, 'term_id', FILTER_VALIDATE_INT) ?: 1;
    $targetFeeType = trim((string) ($_POST['fee_type'] ?? 'tuition')) ?: 'tuition';
    $paymentMethod = trim((string) ($_POST['payment_method'] ?? 'bank_transfer')) ?: 'bank_transfer';
    $paymentDate = trim((string) ($_POST['payment_date'] ?? date('Y-m-d'))) ?: date('Y-m-d');

    if ($balFile && $balFile['error'] === 0) {
        $balPath = $balFile['tmp_name'];
        $firstLine = file_get_contents($balPath, false, null, 0, 500);

        $delimiter = ',';
        if ($firstLine !== false) {
            if (substr_count($firstLine, ';') > substr_count($firstLine, ',')) {
                $delimiter = ';';
            } elseif (substr_count($firstLine, "\t") > substr_count($firstLine, ',')) {
                $delimiter = "\t";
            }
        }

        $bHandle = fopen($balPath, 'r');
        $bTotal = 0;
        $bSuccess = 0;
        $bIgnoredCount = 0;
        $bTotalPaid = 0.0;
        $bImportedRows = [];
        $bFailedRows = [];

        // Header detection
        $bHeader = fgetcsv($bHandle, 0, $delimiter);
        $bSnCol = 0;
        $bNameCol = 1;
        $bPaidCol = 2;
        $bHasHeader = false;

        if ($bHeader) {
            if (isset($bHeader[0])) {
                $bHeader[0] = preg_replace('/\x{EF}\x{BB}\x{BF}/', '', (string) $bHeader[0]);
            }
            foreach ($bHeader as $idx => $heading) {
                $clean = strtolower(trim((string) $heading));
                if (in_array($clean, ['sn', 's/n', 'id', 'no', 's_n'], true)) {
                    $bSnCol = $idx;
                    $bHasHeader = true;
                } elseif (in_array($clean, ['name', 'student_name', 'student name', 'fullname', 'full_name', 'names'], true)) {
                    $bNameCol = $idx;
                    $bHasHeader = true;
                } elseif (in_array($clean, ['paid', 'amount_paid', 'amount', 'actual', 'actual_paid', 'paid_amount', 'fee_paid'], true)) {
                    $bPaidCol = $idx;
                    $bHasHeader = true;
                }
            }
            if (!$bHasHeader) {
                rewind($bHandle);
            }
        }

        $bRowNum = 0;
        while (($bData = fgetcsv($bHandle, 0, $delimiter)) !== false) {
            $bRowNum++;
            if (empty($bData) || (count($bData) === 1 && trim((string) $bData[0]) === '')) {
                continue;
            }

            if (isset($bData[0])) {
                $bData[0] = preg_replace('/\x{EF}\x{BB}\x{BF}/', '', (string) $bData[0]);
            }

            $rSn = trim((string) ($bData[$bSnCol] ?? $bRowNum));
            $rName = trim((string) ($bData[$bNameCol] ?? ''));
            $rPaid = trim((string) ($bData[$bPaidCol] ?? ''));

            if ($rName === '' && !empty($bData[0]) && !is_numeric($bData[0])) {
                $rName = trim((string) $bData[0]);
                $rPaid = trim((string) ($bData[1] ?? ''));
            }

            if (in_array(strtolower($rName), ['name', 'student_name', 'fullname', 'full name', 'student'], true)) {
                continue;
            }

            // If the row or student name is blank, ignore it
            if ($rName === '') {
                continue;
            }

            // If the paid value is blank, empty, '0', or <= 0, ignore the student (they didn't pay any money)
            $trimmedPaid = trim($rPaid);
            $cleanPaid = (float) preg_replace('/[^0-9.]/', '', $trimmedPaid);
            if ($trimmedPaid === '' || in_array(strtolower($trimmedPaid), ['0', '0.0', '0.00', '-', 'nil', 'null', 'none', 'n/a'], true) || $cleanPaid <= 0.0001) {
                $bIgnoredCount++;
                continue;
            }

            $bTotal++;

            $studentData = find_student_by_full_name($rName);
            $student = null;
            if ($studentData && !empty($studentData['id'])) {
                $student = \App\Models\Student::find($studentData['id']);
            }

            if (!$student) {
                $student = \App\Models\Student::where('admission_no', $rName)
                    ->orWhere('student_number', $rName)
                    ->orWhere('email', $rName)
                    ->orWhere(\Illuminate\Support\Facades\DB::raw("LOWER(CONCAT(last_name, ' ', first_name))"), strtolower($rName))
                    ->orWhere(\Illuminate\Support\Facades\DB::raw("LOWER(CONCAT(first_name, ' ', last_name))"), strtolower($rName))
                    ->first();
            }

            if (!$student) {
                $bFailedRows[] = [
                    'row' => $bRowNum,
                    'sn' => $rSn,
                    'name' => $rName,
                    'paid' => $cleanPaid,
                    'reason' => "Student not found in database. Please verify name spelling.",
                ];
                continue;
            }

            try {
                \Illuminate\Support\Facades\DB::beginTransaction();

                $isSpecificStructure = str_starts_with($targetFeeType, 'structure:') || (is_numeric($targetFeeType) && (int)$targetFeeType > 0);
                $specificStructureId = $isSpecificStructure ? (int) str_replace('structure:', '', $targetFeeType) : null;
                $studentFee = null;

                if ($specificStructureId) {
                    $studentFee = \App\Models\StudentFee::where('student_id', $student->id)
                        ->where('fee_structure_id', $specificStructureId)
                        ->where(function($q) use ($targetSessionId) {
                            if ($targetSessionId) {
                                $q->where('session_id', $targetSessionId)->orWhereNull('session_id');
                            }
                        })
                        ->orderByDesc('id')
                        ->first();

                    if (!$studentFee) {
                        $studentFee = \App\Services\FeeAllocationService::allocateSpecificFeeForStudent($student, $specificStructureId, $targetSessionId, $targetTermId);
                    }
                } else {
                    // Match by fee_type in student's allocated fees for this session/term
                    $studentFee = \App\Models\StudentFee::where('student_id', $student->id)
                        ->where(function($q) use ($targetSessionId) {
                            if ($targetSessionId) {
                                $q->where('session_id', $targetSessionId)->orWhereNull('session_id');
                            }
                        })
                        ->where(function($q) use ($targetTermId) {
                            if ($targetTermId) {
                                $q->where('term_id', $targetTermId)->orWhereNull('term_id');
                            }
                        })
                        ->whereHas('feeStructure', function($q) use ($targetFeeType) {
                            $q->where('fee_type', $targetFeeType);
                        })
                        ->orderByDesc('id')
                        ->first();

                    if (!$studentFee) {
                        // Check if any student fee exists for this fee_type regardless of session/term
                        $studentFee = \App\Models\StudentFee::where('student_id', $student->id)
                            ->whereHas('feeStructure', function($q) use ($targetFeeType) {
                                $q->where('fee_type', $targetFeeType);
                            })
                            ->orderByDesc('id')
                            ->first();
                    }

                    if (!$studentFee) {
                        // Find matching fee structure by class, category, gender
                        $category = in_array(strtoupper((string) $student->category), ['NI', 'NEW_INTAKE', 'NEW']) ? 'NI' : 'OS';
                        $gender = strtoupper(substr((string) $student->gender, 0, 1));
                        if (!in_array($gender, ['M', 'F'])) $gender = 'M';

                        $candidates = \App\Models\FeeStructure::where('fee_type', $targetFeeType)
                            ->where('is_active', true)
                            ->where(function($q) use ($targetSessionId) {
                                if ($targetSessionId) {
                                    $q->where('session_id', $targetSessionId)->orWhereNull('session_id');
                                }
                            })
                            ->get();

                        $matchedFs = null;
                        if ($candidates->isNotEmpty()) {
                            $matchedFs = $candidates->sortByDesc(function($s) use ($student, $gender, $category, $targetTermId) {
                                $score = 0;
                                if ($student->current_class_id && $s->class_id == $student->current_class_id) $score += 20;
                                if ($s->category === $category) $score += 10;
                                if ($s->gender === $gender || $s->gender === 'All') $score += 5;
                                if ($targetTermId && $s->term_id == $targetTermId) $score += 5;
                                return $score;
                            })->first();
                        }

                        if (!$matchedFs) {
                            $matchedFs = \App\Models\FeeStructure::where('fee_type', $targetFeeType)->where('is_active', true)->first();
                        }

                        if ($matchedFs) {
                            $studentFee = \App\Services\FeeAllocationService::allocateSpecificFeeForStudent($student, $matchedFs->id, $targetSessionId, $targetTermId);
                        } elseif ($targetFeeType === 'tuition') {
                            $studentFee = \App\Services\FeeAllocationService::allocateFeeForStudent($student, $targetSessionId, $targetTermId);
                        }
                    }
                }

                if (!$studentFee) {
                    $typeLabel = ucwords(str_replace('_', ' ', $targetFeeType));
                    $fs = \App\Models\FeeStructure::where('fee_type', $targetFeeType)->first();
                    if (!$fs && in_array($targetFeeType, ['tuition', 'registration', 'transport', 'library', 'sports', 'laboratory', 'uniform', 'exam', 'other'], true)) {
                        $fs = \App\Models\FeeStructure::create([
                            'name' => $typeLabel . ' Fee',
                            'description' => "Standard {$typeLabel} Fee",
                            'fee_type' => $targetFeeType,
                            'amount' => 59500.00,
                            'frequency' => 'annual',
                            'effective_from' => now()->toDateString(),
                            'session_id' => $targetSessionId,
                            'term_id' => $targetTermId,
                            'category' => in_array(strtoupper((string) $student->category), ['NI', 'NEW_INTAKE', 'NEW']) ? 'NI' : 'OS',
                            'gender' => 'All',
                            'is_mandatory' => true,
                            'is_active' => true,
                        ]);
                    }
                    if ($fs) {
                        $studentFee = \App\Services\FeeAllocationService::allocateSpecificFeeForStudent($student, $fs->id, $targetSessionId, $targetTermId);
                    }
                }

                if (!$studentFee) {
                    $typeLabel = ucwords(str_replace('_', ' ', $targetFeeType));
                    $baseAmt = 59500.00;
                    $studentFee = \App\Models\StudentFee::create([
                        'student_id' => $student->id,
                        'session_id' => $targetSessionId,
                        'term_id' => $targetTermId,
                        'fee_structure_id' => null,
                        'base_amount' => $baseAmt,
                        'additional_amount' => 0.00,
                        'total_payable' => $baseAmt,
                        'amount_due' => $baseAmt,
                        'amount_paid' => 0.00,
                        'amount_owed' => $baseAmt,
                        'balance' => $baseAmt,
                        'due_date' => now()->addDays(30),
                        'status' => 'unpaid',
                        'notes' => "Auto-allocated {$typeLabel} fee for balance import",
                    ]);
                }

                if ((float)$studentFee->base_amount <= 0.001) {
                    if ($studentFee->feeStructure && (float)$studentFee->feeStructure->amount > 0) {
                        $studentFee->base_amount = (float)$studentFee->feeStructure->amount;
                        $studentFee->total_payable = (float)$studentFee->base_amount + (float)$studentFee->additional_amount;
                        $studentFee->save();
                    }
                }

                $allocatedBefore = (float) $studentFee->total_payable;
                $feeDisplayName = $studentFee->feeStructure->name ?? (ucwords(str_replace('_', ' ', $targetFeeType)) . ' Fee');

                $payRef = 'PAY-' . date('Ymd') . '-' . strtoupper(\Illuminate\Support\Str::random(6));
                while (\App\Models\Payment::where('payment_reference', $payRef)->exists()) {
                    $payRef = 'PAY-' . date('Ymd') . '-' . strtoupper(\Illuminate\Support\Str::random(6));
                }

                $studentName = trim($student->first_name . ' ' . ($student->other_names ?? '') . ' ' . $student->last_name);

                $payment = \App\Models\Payment::create([
                    'payment_reference' => $payRef,
                    'payable_type' => 'App\\Models\\StudentFee',
                    'payable_id' => $studentFee->id,
                    'amount' => $cleanPaid,
                    'payment_method' => $paymentMethod,
                    'payment_date' => $paymentDate,
                    'payer_name' => $studentName,
                    'payer_phone' => $student->parent_phone ?? $student->phone ?? null,
                    'description' => "Actual balance payment import - Fee deduction for " . $feeDisplayName,
                    'status' => 'completed',
                ]);

                // Recalculate totals and deduct from allocated fee
                $studentFee->recalculateTotals();

                $receiptNumber = 'REC-' . date('Ymd') . '-' . strtoupper(\Illuminate\Support\Str::random(6));
                while (\App\Models\Receipt::where('receipt_number', $receiptNumber)->exists()) {
                    $receiptNumber = 'REC-' . date('Ymd') . '-' . strtoupper(\Illuminate\Support\Str::random(6));
                }

                $issuedBy = auth()->check() ? auth()->user()->name : ($_SESSION['username'] ?? 'Administrator');

                $receipt = \App\Models\Receipt::create([
                    'receipt_number' => $receiptNumber,
                    'payment_id' => $payment->id,
                    'student_id' => $student->id,
                    'amount_paid' => $cleanPaid,
                    'issued_date' => $paymentDate,
                    'issued_by' => $issuedBy,
                    'notes' => "Fee: {$feeDisplayName} | Allocated Fee: ₦" . number_format($allocatedBefore, 2) . " | Deducted Paid: ₦" . number_format($cleanPaid, 2) . " | Remaining Balance: ₦" . number_format($studentFee->balance, 2),
                ]);

                \App\Models\FinancialAuditLog::logAction('imported_actual_balance', $payment, [], [
                    'student_id' => $student->id,
                    'student_fee_id' => $studentFee->id,
                    'amount_paid' => $cleanPaid,
                    'allocated_fee' => $allocatedBefore,
                    'remaining_balance' => $studentFee->balance,
                    'receipt_number' => $receiptNumber,
                ]);

                \Illuminate\Support\Facades\DB::commit();

                $bSuccess++;
                $bTotalPaid += $cleanPaid;

                $bImportedRows[] = [
                    'sn' => $rSn,
                    'student_name' => $studentName,
                    'admission_no' => $student->admission_no ?? $student->student_number ?? 'N/A',
                    'class_name' => $student->schoolClass ? ($student->schoolClass->class_name . ' ' . ($student->schoolClass->section ?? '')) : 'N/A',
                    'fee_name' => $feeDisplayName,
                    'allocated_fee' => $allocatedBefore,
                    'paid_amount' => $cleanPaid,
                    'balance_remaining' => (float) $studentFee->balance,
                    'status' => $studentFee->status,
                    'payment_reference' => $payRef,
                    'receipt_number' => $receiptNumber,
                    'receipt_url' => url('/admin/fees/receipt.php?receipt=' . $receiptNumber),
                ];

            } catch (\Throwable $e) {
                \Illuminate\Support\Facades\DB::rollBack();
                $bFailedRows[] = [
                    'row' => $bRowNum,
                    'sn' => $rSn,
                    'name' => $rName,
                    'paid' => $cleanPaid,
                    'reason' => 'Database error: ' . $e->getMessage(),
                ];
            }
        }
        fclose($bHandle);

        $balanceImportSummary = [
            'total' => $bTotal,
            'success' => $bSuccess,
            'ignored' => $bIgnoredCount,
            'total_amount' => $bTotalPaid,
            'failed' => count($bFailedRows),
            'records' => $bImportedRows,
            'failed_records' => $bFailedRows,
        ];
        $displayFeeLabel = $isSpecificStructure
            ? ($studentFee && $studentFee->feeStructure ? $studentFee->feeStructure->name : 'Selected Fee Structure')
            : (ucwords(str_replace('_', ' ', $targetFeeType)) . ' Fee');
        $bSuccessMsg = "Imported {$bSuccess} actual balance payment(s) for [{$displayFeeLabel}] successfully totaling ₦" . number_format($bTotalPaid, 2) . " with official receipts generated.";
        if ($bIgnoredCount > 0) {
            $bSuccessMsg .= " ({$bIgnoredCount} student(s) with ₦0 or blank were ignored as no payment was made).";
        }
        $balanceImportSuccess = $bSuccessMsg;
    } else {
        $balanceRowErrors[] = 'Please upload a valid CSV file for actual balance payments.';
    }
}

// Handle Student CSV Import
$rowErrors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['csv_file']) && (!isset($_POST['action']) || $_POST['action'] === 'import_students') && !isset($_POST['import_actual_balance'])) {
    $file = $_FILES['csv_file'];
    $selectedClassId = filter_input(INPUT_POST, 'class_id', FILTER_VALIDATE_INT);
    $selectedSessionId = filter_input(INPUT_POST, 'session_id', FILTER_VALIDATE_INT);
    $selectedTermId = filter_input(INPUT_POST, 'term_id', FILTER_VALIDATE_INT);

    if ($file['error'] === 0) {
        $filePath = $file['tmp_name'];
        $firstLine = file_get_contents($filePath, false, null, 0, 500);

        // Auto-detect CSV delimiter (comma, semicolon, tab)
        $delimiter = ',';
        if ($firstLine !== false) {
            if (substr_count($firstLine, ';') > substr_count($firstLine, ',')) {
                $delimiter = ';';
            } elseif (substr_count($firstLine, "\t") > substr_count($firstLine, ',')) {
                $delimiter = "\t";
            }
        }

        $handle = fopen($filePath, 'r');
        $importedCount = 0;
        $rowNum = 0;

        while (($data = fgetcsv($handle, 0, $delimiter)) !== FALSE) {
            $rowNum++;

            // Skip empty rows
            if (empty($data) || (count($data) === 1 && trim((string) $data[0]) === '')) {
                continue;
            }

            try {
                // Strip UTF-8 BOM from first column if present
                if (isset($data[0])) {
                    $data[0] = preg_replace('/\x{EF}\x{BB}\x{BF}/', '', (string) $data[0]);
                }

                $col0 = validate((string) ($data[0] ?? ''));
                $col1 = validate((string) ($data[1] ?? ''));
                $col2 = validate((string) ($data[2] ?? ''));

                // Skip header row if encountered
                if (in_array(strtolower($col0), ['sn', 's/n', 'id', 'name', 'full name', 'fullname', 'first_name', 'first name'], true) ||
                    in_array(strtolower($col1), ['name', 'full name', 'fullname', 'first_name'], true)) {
                    continue;
                }

                if (is_numeric($col0)) {
                    $fullName = $col1;
                    $status = !empty($col2) ? $col2 : 'active';
                } else {
                    $fullName = $col0;
                    $status = !empty($col1) ? $col1 : 'active';
                }

                if (empty($status)) {
                    $status = 'active';
                }

                if (empty($fullName)) {
                    continue;
                }

                // Split single name column into Surname, Firstname, Other names
                $splitNames = split_student_full_name($fullName);
                $firstName = $splitNames['first_name'];
                $lastName = $splitNames['last_name'];
                $otherNames = $splitNames['other_names'];

                if (empty($firstName) || empty($lastName)) {
                    $rowErrors[] = "Row {$rowNum} ('{$fullName}'): Could not parse both First Name and Surname.";
                    continue;
                }

                // Generate School Email automatically ending with @foluinternationalschools.com.ng
                $schoolEmail = generate_student_school_email($firstName, $lastName);

                // Check for duplicate school email
                $existingStudent = QueryDB("SELECT id FROM students WHERE email = ? LIMIT 1", [$schoolEmail])->fetch();
                if ($existingStudent) {
                    $rowErrors[] = "Row {$rowNum} ('{$fullName}'): A student with generated email '{$schoolEmail}' already exists.";
                    continue;
                }

                // Generate Admission Number
                $admissionNo = generate_student_admission_no($selectedClassId);

                // Default student credentials for portal login
                $hashedPassword = password_hash('password', PASSWORD_DEFAULT);
                $roleId = 4; // Student role ID

                // Create User account for student
                $userColumns = QueryDB("SHOW COLUMNS FROM users")->fetchAll(PDO::FETCH_COLUMN);
                $userFields = [];
                $userValues = [];

                if (in_array('username', $userColumns, true)) {
                    $userFields[] = 'username';
                    $userValues[] = $admissionNo;
                }
                if (in_array('name', $userColumns, true)) {
                    $userFields[] = 'name';
                    $userValues[] = $fullName;
                }
                if (in_array('email', $userColumns, true)) {
                    $userFields[] = 'email';
                    $userValues[] = $schoolEmail;
                }
                if (in_array('password', $userColumns, true)) {
                    $userFields[] = 'password';
                    $userValues[] = $hashedPassword;
                }
                if (in_array('role_id', $userColumns, true)) {
                    $userFields[] = 'role_id';
                    $userValues[] = $roleId;
                }
                if (in_array('status', $userColumns, true)) {
                    $userFields[] = 'status';
                    $userValues[] = 'active';
                }

                $userInsertSql = "INSERT INTO users (" . implode(', ', $userFields) . ") VALUES (" . implode(', ', array_fill(0, count($userFields), '?')) . ")";
                QueryDB($userInsertSql, $userValues);
                $newUserId = (int) $GLOBALS['pdo']->lastInsertId();

                // Prepare Student profile record (physical storage columns only, never virtual generated columns)
                $studentData = [
                    'admission_no' => $admissionNo,
                    'student_number' => $admissionNo,
                    'user_link' => $newUserId,
                    'first_name' => $firstName,
                    'last_name' => $lastName,
                    'other_names' => $otherNames,
                    'email' => $schoolEmail,
                    'status' => $status,
                    'admission_status' => 'admitted',
                    'current_class_id' => $selectedClassId,
                    'current_session_id' => $selectedSessionId,
                    'current_term_id' => $selectedTermId,
                    'category' => 'OS', // All students on import are categorised as returning students (OS)
                    'gender' => 'Male',
                    'date_of_birth' => date('Y-m-d', strtotime('-10 years')),
                    'enrollment_date' => date('Y-m-d'),
                ];

                $validStudentFields = [];
                $validStudentValues = [];
                foreach ($studentData as $field => $val) {
                    if (in_array($field, $studentColumns, true) && !in_array($field, $generatedColumns, true)) {
                        $validStudentFields[] = $field;
                        $validStudentValues[] = $val;
                    }
                }

                if (!empty($validStudentFields)) {
                    $studentInsertSql = "INSERT INTO students (" . implode(', ', $validStudentFields) . ") VALUES (" . implode(', ', array_fill(0, count($validStudentFields), '?')) . ")";
                    QueryDB($studentInsertSql, $validStudentValues);
                    $newStudentId = (int) $GLOBALS['pdo']->lastInsertId();
                    $importedCount++;

                    // Auto allocate fees for student
                    try {
                        $createdStudent = \App\Models\Student::find($newStudentId);
                        if ($createdStudent) {
                            \App\Services\FeeAllocationService::allocateFeeForStudent($createdStudent, $selectedSessionId, $selectedTermId);
                        }
                    } catch (\Throwable $feeEx) {
                        // Keep student created even if fee auto-allocation encounters an issue
                    }
                } else {
                    throw new Exception("No writable student table columns were matched for insert.");
                }
            } catch (Throwable $e) {
                // Clean up orphaned user record if student profile insert failed
                if (!empty($newUserId)) {
                    try {
                        QueryDB("DELETE FROM users WHERE id = ?", [$newUserId]);
                    } catch (\Throwable $cleanupEx) {}
                }
                $rowErrors[] = "Row {$rowNum} ('{$col0}'): " . $e->getMessage();
            }
        }
        fclose($handle);

        if ($importedCount > 0) {
            $success = "Successfully imported {$importedCount} student(s) with base entry details and auto-generated @foluinternationalschools.com.ng emails. All students categorised as Returning Students (OS).";
        } else {
            $error = "No student records were imported. Please check row errors below.";
        }
    } else {
        $error = 'Please upload a valid CSV file.';
    }
}

// Handle Student CSV Export
if (isset($_GET['export']) && $_GET['export'] === 'csv') {
    if (ob_get_level()) { ob_end_clean(); }
    $students = QueryDB("SELECT {$studentIdentifierField} AS student_identifier, first_name, last_name, other_names, email, status, created_at FROM students")->fetchAll();

    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="students_export_' . date('Y-m-d') . '.csv"');

    $output = fopen('php://output', 'w');
    fputcsv($output, ['SN', 'Full Name', 'First Name', 'Last Name', 'Other Names', 'School Email', 'Status', 'Created At']);

    $sn = 1;
    foreach ($students as $student) {
        $fullName = trim(($student['last_name'] ?? '') . ' ' . ($student['first_name'] ?? '') . ' ' . ($student['other_names'] ?? ''));
        fputcsv($output, [
            $sn++,
            $fullName,
            $student['first_name'],
            $student['last_name'],
            $student['other_names'],
            $student['email'],
            $student['status'],
            $student['created_at']
        ]);
    }

    fclose($output);
    exit;
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <title>Import/Export Students &amp; Balance - Admin Portal</title>
    @include('admin.partials.links')
</head>
<body>
    <div class="wrapper">
        @include('admin.partials.sidebar')

        <div class="main-panel">
            @include('admin.partials.header')
            <div class="container">
                <div class="page-inner">
                    <!-- Page Header & Action Bar -->
                    <div class="d-flex align-items-left flex-column flex-md-row pt-2 pb-3 mb-3 border-bottom">
                        <div>
                            <h2 class="text-dark pb-1 fw-bold mb-0">Import/Export Students &amp; Balance</h2>
                            <p class="text-muted small mb-0">Import student bio-data, deduct actual balances from fee allocations, and generate receipts.</p>
                        </div>
                        <div class="ml-md-auto py-2 py-md-0 d-flex flex-wrap gap-2 align-items-center">
                            <!-- Separate button for importing actual balance -->
                            <button type="button" class="btn btn-success me-2 shadow-sm" data-bs-toggle="modal" data-bs-target="#importBalanceModal" data-toggle="modal" data-target="#importBalanceModal" onclick="openBalanceModal()">
                                <i class="fas fa-file-invoice-dollar me-1"></i> Import Actual Balance / Payments
                            </button>
                            <a href="?download_sample=payment_csv" class="btn btn-outline-primary me-2 shadow-sm" title="Download sample payment CSV format: sn,name,paid">
                                <i class="fas fa-file-csv me-1"></i> Sample Payment CSV
                            </a>
                            <a href="list.php" class="btn btn-secondary shadow-sm">
                                <i class="fas fa-arrow-left me-1"></i> Back to List
                            </a>
                        </div>
                    </div>

                    <!-- Balance Import Results Summary Banner & Receipt Table -->
                    @if(!empty($balanceImportSummary))
                        <div class="row mb-4" id="import-results-container">
                            <div class="col-md-12">
                                <div class="card border border-success shadow">
                                    <div class="card-header bg-success text-white d-flex flex-wrap justify-content-between align-items-center py-3">
                                        <div class="card-title text-white mb-0 h5 font-weight-bold">
                                            <i class="fas fa-receipt me-2"></i> Actual Balance Import Summary &amp; Generated Receipts
                                        </div>
                                        <div class="mt-2 mt-md-0">
                                            <span class="badge bg-light text-dark me-1 py-2 px-3">Processed: {{ $balanceImportSummary['total'] ?? 0 }}</span>
                                            <span class="badge bg-light text-success me-1 py-2 px-3">Successful Deductions: {{ $balanceImportSummary['success'] ?? 0 }}</span>
                                            @if(!empty($balanceImportSummary['ignored']) && $balanceImportSummary['ignored'] > 0)
                                                <span class="badge bg-light text-secondary me-1 py-2 px-3">Ignored (₦0 / Blank): {{ $balanceImportSummary['ignored'] }}</span>
                                            @endif
                                            <span class="badge bg-light text-primary me-1 py-2 px-3">Total Paid: ₦{{ number_format((float) ($balanceImportSummary['total_amount'] ?? 0), 2) }}</span>
                                            @if(!empty($balanceImportSummary['failed']) && $balanceImportSummary['failed'] > 0)
                                                <span class="badge bg-danger text-white py-2 px-3">Failed: {{ $balanceImportSummary['failed'] }}</span>
                                            @endif
                                        </div>
                                    </div>
                                    <div class="card-body">
                                        @if(!empty($balanceImportSuccess))
                                            <div class="alert alert-success d-flex align-items-center">
                                                <i class="fas fa-check-circle fa-2x me-3 text-success"></i>
                                                <div>
                                                    <h6 class="mb-0 font-weight-bold">{{ $balanceImportSuccess }}</h6>
                                                    <small class="text-muted">Payment records have been created, allocated fees deducted, and official receipts generated for each student.</small>
                                                </div>
                                            </div>
                                        @endif

                                        @if(!empty($balanceImportSummary['failed_records']))
                                            <div class="alert alert-warning">
                                                <h6 class="font-weight-bold mb-1"><i class="fas fa-exclamation-triangle me-1"></i> Unmatched / Failed Records:</h6>
                                                <ul class="mb-0 small">
                                                    @foreach(array_slice($balanceImportSummary['failed_records'], 0, 10) as $fRow)
                                                        <li>Row {{ $fRow['row'] }}: <strong>{{ $fRow['name'] }}</strong> (SN: {{ $fRow['sn'] }}) &mdash; <span class="text-danger">{{ $fRow['reason'] }}</span></li>
                                                    @endforeach
                                                    @if(count($balanceImportSummary['failed_records']) > 10)
                                                        <li>...and {{ count($balanceImportSummary['failed_records']) - 10 }} more record(s).</li>
                                                    @endif
                                                </ul>
                                            </div>
                                        @endif

                                        @if(!empty($balanceImportSummary['records']))
                                            <div class="table-responsive mt-3">
                                                <table class="table table-hover table-striped table-bordered align-middle" id="balanceResultsTable">
                                                    <thead class="table-light">
                                                        <tr>
                                                            <th style="width: 50px;">SN</th>
                                                            <th>Student Name</th>
                                                            <th>Admission No</th>
                                                            <th>Class</th>
                                                            <th>Fee Type / Structure</th>
                                                            <th>Allocated Fee (₦)</th>
                                                            <th>Paid (Deducted) (₦)</th>
                                                            <th>Remaining Balance (₦)</th>
                                                            <th>Fee Status</th>
                                                            <th>Receipt Number</th>
                                                            <th class="text-center" style="width: 150px;">Action</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        @foreach($balanceImportSummary['records'] as $rec)
                                                            <tr>
                                                                <td>{{ $rec['sn'] }}</td>
                                                                <td><strong>{{ $rec['student_name'] }}</strong></td>
                                                                <td><code>{{ $rec['admission_no'] }}</code></td>
                                                                <td>{{ $rec['class_name'] }}</td>
                                                                <td><span class="badge bg-light text-dark border"><i class="fas fa-tag me-1 text-primary"></i> {{ $rec['fee_name'] ?? 'School Fees' }}</span></td>
                                                                <td class="text-primary font-weight-bold">₦{{ number_format((float)$rec['allocated_fee'], 2) }}</td>
                                                                <td class="text-success font-weight-bold">- ₦{{ number_format((float)$rec['paid_amount'], 2) }}</td>
                                                                <td class="text-danger font-weight-bold">₦{{ number_format((float)$rec['balance_remaining'], 2) }}</td>
                                                                <td>
                                                                    @if((float)$rec['balance_remaining'] <= 0.001)
                                                                        <span class="badge bg-success text-white">Paid in Full</span>
                                                                    @else
                                                                        <span class="badge bg-warning text-dark">Partially Paid</span>
                                                                    @endif
                                                                </td>
                                                                <td><span class="badge bg-secondary text-white font-monospace">{{ $rec['receipt_number'] }}</span></td>
                                                                <td class="text-center">
                                                                    <a href="{{ $rec['receipt_url'] }}" target="_blank" class="btn btn-sm btn-primary shadow-sm" title="Print Official Receipt">
                                                                        <i class="fas fa-print me-1"></i> Print Receipt
                                                                    </a>
                                                                </td>
                                                            </tr>
                                                        @endforeach
                                                    </tbody>
                                                </table>
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endif

                    <!-- Alerts for Student Import -->
                    <?php if (isset($error)): ?>
                        <div class="alert alert-danger shadow-sm">
                            <i class="fas fa-times-circle me-1"></i> <?php echo $error; ?>
                        </div>
                    <?php endif; ?>
                    <?php if (isset($success)): ?>
                        <div class="alert alert-success shadow-sm">
                            <i class="fas fa-check-circle me-1"></i> <?php echo $success; ?>
                        </div>
                    <?php endif; ?>
                    <?php if (!empty($rowErrors)): ?>
                        <div class="alert alert-warning shadow-sm">
                            <strong>Import Warnings / Errors:</strong>
                            <ul class="mb-0 mt-1">
                                <?php foreach (array_slice($rowErrors, 0, 10) as $rowErr): ?>
                                    <li><?php echo htmlspecialchars($rowErr); ?></li>
                                <?php endforeach; ?>
                                <?php if (count($rowErrors) > 10): ?>
                                    <li>...and <?php echo count($rowErrors) - 10; ?> more row error(s).</li>
                                <?php endif; ?>
                            </ul>
                        </div>
                    <?php endif; ?>

                    <!-- Main Grid: Student Import & Actual Balance Import Cards -->
                    <div class="row">
                        <!-- Card 1: Import Students -->
                        <div class="col-md-6 mb-4">
                            <div class="card h-100 shadow-sm border-top border-primary border-3">
                                <div class="card-header bg-white">
                                    <div class="card-title text-primary font-weight-bold">
                                        <i class="fas fa-user-plus me-1"></i> 1. Import Students (Bio-Data)
                                    </div>
                                </div>
                                <div class="card-body">
                                    <p class="text-muted small">Upload CSV file with student data. Columns: <strong>SN</strong>, <strong>Name</strong>, and optional <strong>Status</strong>.</p>
                                    <div class="alert alert-light border small text-muted py-2 mb-3">
                                        <i class="fas fa-info-circle text-info me-1"></i> School emails (<code>@foluinternationalschools.com.ng</code>) are auto-generated. All imported students are categorised as <strong>Returning Students (OS)</strong>.
                                    </div>

                                    <form method="POST" enctype="multipart/form-data">
                                        <input type="hidden" name="action" value="import_students">
                                        <div class="form-group mb-3">
                                            <label for="class_id" class="form-label font-weight-bold">Target Class <span class="text-danger">*</span></label>
                                            <select name="class_id" id="class_id" class="form-control" required>
                                                <option value="">-- Select Target Class --</option>
                                                <?php foreach ($classesList as $c): ?>
                                                    <option value="<?php echo $c['id']; ?>"><?php echo htmlspecialchars($c['class_name'] . ' ' . ($c['class_arm'] ?? '')); ?></option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>

                                        <div class="row mb-3">
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label for="session_id" class="form-label font-weight-bold">Academic Session <span class="text-danger">*</span></label>
                                                    <select name="session_id" id="session_id" class="form-control" required>
                                                        <?php foreach ($sessionsList as $s): ?>
                                                            <option value="<?php echo $s['id']; ?>" <?php echo ((int)($s['is_active'] ?? 0) === 1) ? 'selected' : ''; ?>>
                                                                <?php echo htmlspecialchars($s['session_name']); ?>
                                                            </option>
                                                        <?php endforeach; ?>
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label for="term_id" class="form-label font-weight-bold">Academic Term <span class="text-danger">*</span></label>
                                                    <select name="term_id" id="term_id" class="form-control" required>
                                                        <?php foreach ($termsList as $t): ?>
                                                            <option value="<?php echo $t['id']; ?>" <?php echo ((int)($t['is_active'] ?? 0) === 1) ? 'selected' : ''; ?>>
                                                                <?php echo htmlspecialchars($t['term_name'] ?? ('Term ' . ($t['term_number'] ?? $t['id']))); ?>
                                                            </option>
                                                        <?php endforeach; ?>
                                                    </select>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="form-group mb-3">
                                            <label for="csv_file" class="form-label font-weight-bold">CSV File <span class="text-danger">*</span></label>
                                            <input type="file" class="form-control-file form-control" name="csv_file" id="csv_file" accept=".csv" required>
                                        </div>
                                        <div class="d-flex justify-content-between align-items-center">
                                            <button type="submit" class="btn btn-primary">
                                                <i class="fas fa-upload me-1"></i> Import Students
                                            </button>
                                            <a href="?download_sample=student_csv" class="btn btn-link btn-sm text-decoration-none">
                                                <i class="fas fa-download me-1"></i> Sample Student CSV
                                            </a>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>

                        <!-- Card 2: Import Actual Balance (Fee Deduction & Payments) -->
                        <div class="col-md-6 mb-4" id="actual-balance-card">
                            <div class="card h-100 shadow-sm border-top border-success border-3">
                                <div class="card-header bg-white d-flex justify-content-between align-items-center">
                                    <div class="card-title text-success font-weight-bold mb-0">
                                        <i class="fas fa-file-invoice-dollar me-1"></i> 2. Import Actual Balance (Fee Deduction)
                                    </div>
                                    <span class="badge bg-success text-white">Payments &amp; Receipts</span>
                                </div>
                                <div class="card-body">
                                    <p class="text-muted small">Upload CSV with columns: <strong>sn,name,paid</strong>. The paid column will be deducted from each student's allocated fees.</p>
                                    <div class="alert alert-light border small text-dark py-2 mb-3">
                                        <i class="fas fa-calculator text-success me-1"></i> <strong>How it works:</strong> If the allocation is ₦59,500, the uploaded paid value (e.g. ₦40,000) will be recorded as a payment and deducted from it, leaving a balance of ₦19,500. Each student has a receipt generated.
                                        <div class="mt-1 text-muted"><i class="fas fa-info-circle text-info me-1"></i> <em>Rows with blank or 0 paid values are automatically ignored (indicating the student didn't pay any money).</em></div>
                                    </div>

                                    <form method="POST" action="{{ route('admin.students.import_actual_balance') }}" enctype="multipart/form-data">
                                        @csrf
                                        <input type="hidden" name="action" value="import_actual_balance">

                                        <div class="row mb-3">
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label for="bal_session_id" class="form-label font-weight-bold">Target Session <span class="text-danger">*</span></label>
                                                    <select name="session_id" id="bal_session_id" class="form-control" required>
                                                        <?php foreach ($sessionsList as $s): ?>
                                                            <option value="<?php echo $s['id']; ?>" <?php echo ((int)($s['is_active'] ?? 0) === 1) ? 'selected' : ''; ?>>
                                                                <?php echo htmlspecialchars($s['session_name']); ?>
                                                            </option>
                                                        <?php endforeach; ?>
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label for="bal_term_id" class="form-label font-weight-bold">Target Term <span class="text-danger">*</span></label>
                                                    <select name="term_id" id="bal_term_id" class="form-control" required>
                                                        <?php foreach ($termsList as $t): ?>
                                                            <option value="<?php echo $t['id']; ?>" <?php echo ((int)($t['is_active'] ?? 0) === 1) ? 'selected' : ''; ?>>
                                                                <?php echo htmlspecialchars($t['term_name'] ?? ('Term ' . ($t['term_number'] ?? $t['id']))); ?>
                                                            </option>
                                                        <?php endforeach; ?>
                                                    </select>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="row mb-3">
                                            <div class="col-md-12">
                                                <div class="form-group">
                                                    <label for="bal_fee_type" class="form-label font-weight-bold">Fee Type to Import / Deduct <span class="text-danger">*</span></label>
                                                    <select name="fee_type" id="bal_fee_type" class="form-control" required>
                                                        <optgroup label="Standard Fee Types">
                                                            <?php foreach ($feeTypeEnumOptions as $ftKey => $ftLabel): ?>
                                                                <option value="<?php echo htmlspecialchars($ftKey); ?>" <?php echo $ftKey === 'tuition' ? 'selected' : ''; ?>>
                                                                    <?php echo htmlspecialchars($ftLabel); ?>
                                                                </option>
                                                            <?php endforeach; ?>
                                                        </optgroup>
                                                        <?php if (!empty($feeStructuresList)): ?>
                                                        <optgroup label="Specific Fee Structures">
                                                            <?php foreach ($feeStructuresList as $fs): ?>
                                                                <option value="structure:<?php echo $fs['id']; ?>">
                                                                    <?php echo htmlspecialchars($fs['name']); ?> (₦<?php echo number_format($fs['amount'], 2); ?> &mdash; <?php echo ucfirst($fs['fee_type']); ?>)
                                                                </option>
                                                            <?php endforeach; ?>
                                                        </optgroup>
                                                        <?php endif; ?>
                                                    </select>
                                                    <small class="text-muted"><i class="fas fa-info-circle me-1"></i> Payments in this CSV will be deducted specifically from each student's selected fee allocation.</small>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="row mb-3">
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label for="bal_payment_method" class="form-label font-weight-bold">Payment Method</label>
                                                    <select name="payment_method" id="bal_payment_method" class="form-control">
                                                        <option value="bank_transfer" selected>Bank Transfer</option>
                                                        <option value="cash">Cash</option>
                                                        <option value="pos">POS</option>
                                                        <option value="cheque">Cheque</option>
                                                        <option value="online">Online Payment</option>
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label for="bal_payment_date" class="form-label font-weight-bold">Payment Date</label>
                                                    <input type="date" name="payment_date" id="bal_payment_date" class="form-control" value="<?php echo date('Y-m-d'); ?>">
                                                </div>
                                            </div>
                                        </div>

                                        <div class="form-group mb-3">
                                            <label for="balance_csv_file" class="form-label font-weight-bold">Payment CSV File (sn,name,paid) <span class="text-danger">*</span></label>
                                            <input type="file" class="form-control-file form-control" name="csv_file" id="balance_csv_file" accept=".csv" required>
                                        </div>

                                        <div class="d-flex justify-content-between align-items-center">
                                            <button type="submit" class="btn btn-success">
                                                <i class="fas fa-file-invoice-dollar me-1"></i> Import Balance &amp; Generate Receipts
                                            </button>
                                            <a href="?download_sample=payment_csv" class="btn btn-link btn-sm text-success text-decoration-none">
                                                <i class="fas fa-download me-1"></i> Download Sample CSV
                                            </a>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Row 2: Export & Sample CSV Formats -->
                    <div class="row">
                        <div class="col-md-6 mb-4">
                            <div class="card h-100 shadow-sm">
                                <div class="card-header bg-white">
                                    <div class="card-title text-secondary font-weight-bold">
                                        <i class="fas fa-file-export me-1"></i> Export Students
                                    </div>
                                </div>
                                <div class="card-body d-flex flex-column justify-content-between">
                                    <p class="text-muted">Export all registered student records including admission numbers, names, emails, and active statuses to a CSV file for backup or external reporting.</p>
                                    <div>
                                        <a href="?export=csv" class="btn btn-info text-white shadow-sm">
                                            <i class="fas fa-file-excel me-1"></i> Export All Students to CSV
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6 mb-4">
                            <div class="card h-100 shadow-sm">
                                <div class="card-header bg-white">
                                    <div class="card-title text-dark font-weight-bold">
                                        <i class="fas fa-table me-1"></i> Expected CSV Formats
                                    </div>
                                </div>
                                <div class="card-body">
                                    <h6 class="font-weight-bold text-success mb-1">Actual Balance CSV (sn,name,paid):</h6>
                                    <pre class="bg-light p-2 border rounded small mb-2">sn,name,paid
1,Adeyemi Babatunde John,40000
2,Okonkwo Chinedu,59500
3,Bello Mohammed Aliyu,25000</pre>
                                    <a href="?download_sample=payment_csv" class="btn btn-sm btn-outline-success mb-3">
                                        <i class="fas fa-download me-1"></i> Download Balance Sample CSV
                                    </a>

                                    <h6 class="font-weight-bold text-primary mb-1">Student Bio-Data CSV (sn,name,status):</h6>
                                    <pre class="bg-light p-2 border rounded small mb-2">sn,name,status
1,Adeyemi Babatunde John,active
2,Okonkwo Chinedu,active
3,Bello Mohammed Aliyu,active</pre>
                                    <a href="?download_sample=student_csv" class="btn btn-sm btn-outline-primary">
                                        <i class="fas fa-download me-1"></i> Download Student Sample CSV
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>
            </div>

            <!-- Modal for Fast One-Click Balance Import -->
            <div class="modal fade" id="importBalanceModal" tabindex="-1" role="dialog" aria-labelledby="importBalanceModalLabel" aria-hidden="true">
                <div class="modal-dialog modal-lg" role="document">
                    <div class="modal-content">
                        <div class="modal-header bg-success text-white">
                            <h5 class="modal-title font-weight-bold" id="importBalanceModalLabel">
                                <i class="fas fa-file-invoice-dollar me-2"></i> Import Actual Balance / Payments
                            </h5>
                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" data-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <form method="POST" action="{{ route('admin.students.import_actual_balance') }}" enctype="multipart/form-data">
                            @csrf
                            <input type="hidden" name="action" value="import_actual_balance">
                            <div class="modal-body">
                                <div class="alert alert-info border small py-2">
                                    <i class="fas fa-info-circle me-1"></i> <strong>Balance Deduction:</strong> The uploaded <code>paid</code> column will be recorded as actual payment and deducted from the student's allocated fees (e.g. Allocation ₦59,500 minus Paid ₦40,000 leaves Balance ₦19,500). An official receipt will be generated for each student.
                                    <div class="mt-1"><i class="fas fa-check-circle me-1"></i> <em>Rows with blank or 0 paid values are automatically ignored (indicating the student didn't pay any money).</em></div>
                                </div>

                                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <label for="modal_session_id" class="form-label font-weight-bold">Academic Session <span class="text-danger">*</span></label>
                                        <select name="session_id" id="modal_session_id" class="form-control" required>
                                            <?php foreach ($sessionsList as $s): ?>
                                                <option value="<?php echo $s['id']; ?>" <?php echo ((int)($s['is_active'] ?? 0) === 1) ? 'selected' : ''; ?>>
                                                    <?php echo htmlspecialchars($s['session_name']); ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                    <div class="col-md-6">
                                        <label for="modal_term_id" class="form-label font-weight-bold">Academic Term <span class="text-danger">*</span></label>
                                        <select name="term_id" id="modal_term_id" class="form-control" required>
                                            <?php foreach ($termsList as $t): ?>
                                                <option value="<?php echo $t['id']; ?>" <?php echo ((int)($t['is_active'] ?? 0) === 1) ? 'selected' : ''; ?>>
                                                    <?php echo htmlspecialchars($t['term_name'] ?? ('Term ' . ($t['term_number'] ?? $t['id']))); ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                </div>

                                <div class="row mb-3">
                                    <div class="col-md-12">
                                        <label for="modal_fee_type" class="form-label font-weight-bold">Fee Type to Import / Deduct <span class="text-danger">*</span></label>
                                        <select name="fee_type" id="modal_fee_type" class="form-control" required>
                                            <optgroup label="Standard Fee Types">
                                                <?php foreach ($feeTypeEnumOptions as $ftKey => $ftLabel): ?>
                                                    <option value="<?php echo htmlspecialchars($ftKey); ?>" <?php echo $ftKey === 'tuition' ? 'selected' : ''; ?>>
                                                        <?php echo htmlspecialchars($ftLabel); ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            </optgroup>
                                            <?php if (!empty($feeStructuresList)): ?>
                                            <optgroup label="Specific Fee Structures">
                                                <?php foreach ($feeStructuresList as $fs): ?>
                                                    <option value="structure:<?php echo $fs['id']; ?>">
                                                        <?php echo htmlspecialchars($fs['name']); ?> (₦<?php echo number_format($fs['amount'], 2); ?> &mdash; <?php echo ucfirst($fs['fee_type']); ?>)
                                                    </option>
                                                <?php endforeach; ?>
                                            </optgroup>
                                            <?php endif; ?>
                                        </select>
                                        <small class="text-muted"><i class="fas fa-info-circle me-1"></i> Payments in this CSV will be deducted specifically from each student's selected fee allocation.</small>
                                    </div>
                                </div>

                                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <label for="modal_payment_method" class="form-label font-weight-bold">Payment Method</label>
                                        <select name="payment_method" id="modal_payment_method" class="form-control">
                                            <option value="bank_transfer" selected>Bank Transfer</option>
                                            <option value="cash">Cash</option>
                                            <option value="pos">POS</option>
                                            <option value="cheque">Cheque</option>
                                            <option value="online">Online Payment</option>
                                        </select>
                                    </div>
                                    <div class="col-md-6">
                                        <label for="modal_payment_date" class="form-label font-weight-bold">Payment Date</label>
                                        <input type="date" name="payment_date" id="modal_payment_date" class="form-control" value="<?php echo date('Y-m-d'); ?>">
                                    </div>
                                </div>

                                <div class="form-group mb-3">
                                    <label for="modal_csv_file" class="form-label font-weight-bold">Payment CSV File (sn,name,paid) <span class="text-danger">*</span></label>
                                    <input type="file" class="form-control-file form-control" name="csv_file" id="modal_csv_file" accept=".csv" required>
                                    <small class="text-muted">Columns: <code>sn,name,paid</code>. (e.g. <code>1, Adeyemi Babatunde John, 40000</code>)</small>
                                </div>
                            </div>
                            <div class="modal-footer">
                                <a href="?download_sample=payment_csv" class="btn btn-outline-secondary me-auto">
                                    <i class="fas fa-download me-1"></i> Sample CSV
                                </a>
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal" data-dismiss="modal">Close</button>
                                <button type="submit" class="btn btn-success">
                                    <i class="fas fa-file-invoice-dollar me-1"></i> Upload &amp; Deduct Payments
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            @include('admin.partials.footer')

            <script>
            function openBalanceModal() {
                var modalEl = document.getElementById('importBalanceModal');
                if (typeof bootstrap !== 'undefined' && bootstrap.Modal) {
                    var myModal = bootstrap.Modal.getInstance(modalEl) || new bootstrap.Modal(modalEl);
                    myModal.show();
                } else if (typeof $ !== 'undefined' && typeof $('#importBalanceModal').modal === 'function') {
                    $('#importBalanceModal').modal('show');
                } else {
                    var card = document.getElementById('actual-balance-card');
                    if (card) {
                        card.scrollIntoView({ behavior: 'smooth' });
                    }
                }
            }

            $(document).ready(function() {
                if ($('#balanceResultsTable').length && typeof $.fn.DataTable !== 'undefined') {
                    if (!$.fn.DataTable.isDataTable('#balanceResultsTable')) {
                        $('#balanceResultsTable').DataTable({
                            pageLength: 25,
                            responsive: true,
                            order: [[0, 'asc']]
                        });
                    }
                }
            });
            </script>
</body>
</html>
