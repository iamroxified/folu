<?php
// Start session

// Include database configuration and functions
require_once base_path('db/config.php');
require_once base_path('db/functions.php');
require_once resource_path('views/admin/school_functions.php');

// Check if user is logged in
if (!isset($_SESSION['adid'])) {
    header('Location: /admin/login.php');
    exit;
}

$studentColumns = QueryDB("SHOW COLUMNS FROM students")->fetchAll(PDO::FETCH_COLUMN);
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

// Handle CSV Import
$rowErrors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['csv_file'])) {
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
                    throw new Exception("Student name '{$fullName}' could not be parsed into surname and firstname.");
                }

                // Auto-generate school email using @foluinternationalschools.com.ng
                $studentEmail = generate_student_school_email($firstName, $lastName);

                $admissionNo = generate_student_admission_no();
                $studentNumber = schema_has_column('students', 'student_number') ? generate_student_id() : $admissionNo;
                $displayName = trim("{$lastName} {$firstName} {$otherNames}");

                // Create user in users table for authentication
                $userId = create_portal_user([
                    'username' => $admissionNo,
                    'name' => $displayName,
                    'email' => $studentEmail,
                    'password' => password_hash('password', PASSWORD_DEFAULT),
                    'role_name' => 'student',
                    'role_id' => get_role_id_by_name('student') ?? 4,
                    'status' => $status,
                ]);

                $termContext = admin_student_term_context($selectedSessionId ?: null);
                $studentInsertData = admin_build_student_insert_data([
                    'user_link' => $userId,
                    'user_id' => $userId,
                    'admission_no' => $admissionNo,
                    'student_number' => $studentNumber,
                    'first_name' => $firstName,
                    'last_name' => $lastName,
                    'other_names' => $otherNames,
                    'email' => $studentEmail,
                    'enrollment_date' => date('Y-m-d'),
                    'status' => $status,
                    'admission_status' => 'admitted',
                    'current_class_id' => $selectedClassId ?: null,
                    'class_link' => $selectedClassId ?: null,
                    'class_id' => $selectedClassId ?: null,
                    'current_session_id' => $selectedSessionId ?: null,
                    'academic_session_link' => $selectedSessionId ?: null,
                    'academic_session_id' => $selectedSessionId ?: null,
                    'current_term_id' => $selectedTermId ?: $termContext['current_term_id'],
                    'term_link' => $selectedTermId ?: $termContext['academic_term_id'],
                    'timestamp' => date('Y-m-d H:i:s'),
                ]);

                if ($studentInsertData !== []) {
                    $studentColumns = implode(', ', array_keys($studentInsertData));
                    $studentPlaceholders = implode(', ', array_fill(0, count($studentInsertData), '?'));
                    $stmt = $pdo->prepare("INSERT INTO students ({$studentColumns}) VALUES ({$studentPlaceholders})");
                    $stmt->execute(array_values($studentInsertData));
                    $importedCount++;
                } else {
                    throw new Exception("No writable student table columns were matched for insert.");
                }
            } catch (Throwable $e) {
                $rowErrors[] = "Row {$rowNum} ('{$col0}'): " . $e->getMessage();
            }
        }
        fclose($handle);

        if ($importedCount > 0) {
            $success = "Successfully imported {$importedCount} student(s) with base entry details and auto-generated @foluinternationalschools.com.ng emails.";
        } else {
            $error = "No student records were imported. Please check row errors below.";
        }
    } else {
        $error = 'Please upload a valid CSV file.';
    }
}

// Handle CSV Export
if (isset($_GET['export']) && $_GET['export'] === 'csv') {
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
    <title>Import/Export Students</title>
    @include('admin.partials.links')
</head>
<body>
    <div class="wrapper">
        @include('admin.partials.sidebar')

        <div class="main-panel">
            @include('admin.partials.header')
            <div class="container">
                <div class="page-inner">
                    <div class="d-flex align-items-left flex-column flex-md-row">
                        <h2 class="text-dark pb-2 fw-bold">Import/Export Students</h2>
                        <div class="ml-md-auto py-2 py-md-0">
                            <a href="list.php" class="btn btn-secondary">Back to List</a>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-header">
                                    <div class="card-title">Import Students</div>
                                </div>
                                <div class="card-body">
                                    <?php if (isset($error)): ?>
                                        <div class="alert alert-danger">
                                            <?php echo $error; ?>
                                        </div>
                                    <?php endif; ?>
                                    <?php if (isset($success)): ?>
                                        <div class="alert alert-success">
                                            <?php echo $success; ?>
                                        </div>
                                    <?php endif; ?>
                                    <?php if (!empty($rowErrors)): ?>
                                        <div class="alert alert-warning">
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

                                    <p>Upload a CSV file with student data. The CSV should have the following columns:</p>
                                    <ul>
                                        <li><strong>SN</strong> (Serial Number)</li>
                                        <li><strong>Name</strong> (Full Name in a single column - automatically split into Surname, Firstname, Other Name)</li>
                                        <li><strong>Status</strong> (Optional: active / inactive, defaults to <code>active</code>)</li>
                                    </ul>
                                    <p class="text-muted"><small><i class="fas fa-info-circle"></i> Student school emails ending with <strong>@foluinternationalschools.com.ng</strong> will be automatically generated upon import.</small></p>

                                    <form method="POST" enctype="multipart/form-data">
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
                                                    <label for="session_id" class="form-label font-weight-bold">Entry Academic Session <span class="text-danger">*</span></label>
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
                                                    <label for="term_id" class="form-label font-weight-bold">Entry Academic Term <span class="text-danger">*</span></label>
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
                                            <input type="file" class="form-control-file" name="csv_file" id="csv_file" accept=".csv" required>
                                        </div>
                                        <button type="submit" class="btn btn-primary">Import Students</button>
                                    </form>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-header">
                                    <div class="card-title">Export Students</div>
                                </div>
                                <div class="card-body">
                                    <p>Export all student data to a CSV file for backup or analysis purposes.</p>
                                    <a href="?export=csv" class="btn btn-success">Export to CSV</a>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row mt-4">
                        <div class="col-md-12">
                            <div class="card">
                                <div class="card-header">
                                    <div class="card-title">Sample CSV Format</div>
                                </div>
                                <div class="card-body">
                                    <pre>sn,name,status
1,Adeyemi Babatunde John,active
2,Okonkwo Chinedu,active
3,Bello Mohammed Aliyu,active</pre>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            @include('admin.partials.footer')
</body>
</html>




