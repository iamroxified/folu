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

// Handle CSV Import
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['csv_file'])) {
    $file = $_FILES['csv_file'];
    
    if ($file['error'] === 0 && $file['type'] === 'text/csv') {
        $handle = fopen($file['tmp_name'], 'r');
        $header = fgetcsv($handle); // Skip header row
        $importedCount = 0;
        
        while (($data = fgetcsv($handle, 1000, ',')) !== FALSE) {
            try {
                $firstName = validate($data[0] ?? '');
                $lastName = validate($data[1] ?? '');
                $email = validate($data[2] ?? '');
                $status = validate($data[3] ?? 'active');
                
                if (!empty($firstName) && !empty($lastName) && filter_var($email, FILTER_VALIDATE_EMAIL)) {
                    $studentID = generate_student_id();
                    $stmt = $pdo->prepare("INSERT INTO students ({$studentIdentifierField}, first_name, last_name, email, status) VALUES (?, ?, ?, ?, ?)");
                    $stmt->execute([$studentID, $firstName, $lastName, $email, $status]);
                    $importedCount++;
                }
            } catch (Exception $e) {
                // Continue with next row if one fails
            }
        }
        fclose($handle);
        $success = "Successfully imported $importedCount students";
    } else {
        $error = 'Please upload a valid CSV file';
    }
}

// Handle CSV Export
if (isset($_GET['export']) && $_GET['export'] === 'csv') {
    $students = QueryDB("SELECT {$studentIdentifierField} AS student_identifier, first_name, last_name, email, status, created_at FROM students")->fetchAll();
    
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="students_export_' . date('Y-m-d') . '.csv"');
    
    $output = fopen('php://output', 'w');
    fputcsv($output, [$studentIdentifierLabel, 'First Name', 'Last Name', 'Email', 'Status', 'Created At']);
    
    foreach ($students as $student) {
        fputcsv($output, [
            $student['student_identifier'],
            $student['first_name'],
            $student['last_name'],
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
                                    
                                    <p>Upload a CSV file with student data. The CSV should have the following columns:</p>
                                    <ul>
                                        <li>First Name</li>
                                        <li>Last Name</li>
                                        <li>Email</li>
                                        <li>Status (active/inactive)</li>
                                    </ul>
                                    
                                    <form method="POST" enctype="multipart/form-data">
                                        <div class="form-group">
                                            <label for="csv_file">CSV File</label>
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
                                    <pre>First Name,Last Name,Email,Status
John,Doe,john.doe@example.com,active
Jane,Smith,jane.smith@example.com,active
Bob,Johnson,bob.johnson@example.com,inactive</pre>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            @include('admin.partials.footer')
</body>
</html>




