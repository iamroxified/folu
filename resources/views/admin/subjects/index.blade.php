<?php
// Start session

// Include database and functions
require_once base_path('db/config.php');
require_once base_path('db/functions.php');

// Check if user is logged in
if (!isset($_SESSION['adid'])) {
    header('Location: /admin/login.php');
    exit;
}

// Match the active subjects schema while staying compatible with older variants.
$subjectColumns = QueryDB("SHOW COLUMNS FROM subjects")->fetchAll(PDO::FETCH_COLUMN);
$subjectCodeField = in_array('subject_code', $subjectColumns, true) ? 'subject_code' : 'course_code';
$subjectNameField = in_array('subject_name', $subjectColumns, true) ? 'subject_name' : 'course_name';
$subjectContextField = in_array('class_level', $subjectColumns, true) ? 'class_level' : 'department';
$subjectContextLabel = $subjectContextField === 'class_level' ? 'Class Level' : 'Department';
$subjectTypeField = in_array('is_core', $subjectColumns, true) ? 'is_core' : 'credits';
$subjectTypeLabel = $subjectTypeField === 'is_core' ? 'Type' : 'Credits';
$hasStatus = in_array('status', $subjectColumns, true);

$subjectsQuery = "SELECT * FROM subjects";
if ($hasStatus) {
    $subjectsQuery .= " WHERE status = 'active'";
}
$subjectsQuery .= " ORDER BY {$subjectNameField}";

$subjects = QueryDB($subjectsQuery)->fetchAll();

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <title>Subjects Management</title>
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
                        <h2 class="text-dark pb-2 fw-bold">Subjects</h2>
                        <div class="ml-md-auto py-2 py-md-0">
                            <a href="{{ url('/admin/subjects/manage.php') }}" class="btn btn-primary">Manage Subjects</a>
                            <a href="{{ url('/admin/subjects/assign.php') }}" class="btn btn-secondary">Assign to Classes</a>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-12">
                            <div class="card">
                                <div class="card-header">
                                    <div class="card-title">All Subjects</div>
                                </div>
                                <div class="card-body">
                                    <div class="table-responsive">
                                        <table class="table table-bordered">
                                            <thead>
                                                <tr>
                                                    <th>Subject Code</th>
                                                    <th>Subject Name</th>
                                                    <th><?php echo htmlspecialchars($subjectContextLabel); ?></th>
                                                    <th><?php echo htmlspecialchars($subjectTypeLabel); ?></th>
                                                    <th>Created Date</th>
                                                    <th>Status</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php if (empty($subjects)): ?>
                                                    <tr>
                                                        <td colspan="6" class="text-center">No subjects found.</td>
                                                    </tr>
                                                <?php else: ?>
                                                    <?php foreach ($subjects as $subject): ?>
                                                        <?php
                                                            $subjectCode = $subject[$subjectCodeField] ?? 'N/A';
                                                            $subjectName = $subject[$subjectNameField] ?? 'N/A';
                                                            $subjectContext = $subject[$subjectContextField] ?? 'N/A';
                                                            $subjectType = $subjectTypeField === 'is_core'
                                                                ? (($subject['is_core'] ?? 0) ? 'Core' : 'Elective')
                                                                : ($subject['credits'] ?? 'N/A');
                                                            $createdAt = $subject['created_at'] ?? null;
                                                            $statusLabel = $hasStatus
                                                                ? ucfirst((string) ($subject['status'] ?? 'active'))
                                                                : 'Active';
                                                        ?>
                                                        <tr>
                                                            <td><?php echo htmlspecialchars((string) $subjectCode); ?></td>
                                                            <td><?php echo htmlspecialchars((string) $subjectName); ?></td>
                                                            <td><?php echo htmlspecialchars((string) $subjectContext); ?></td>
                                                            <td><?php echo htmlspecialchars((string) $subjectType); ?></td>
                                                            <td><?php echo $createdAt ? htmlspecialchars(date('M d, Y', strtotime($createdAt))) : 'N/A'; ?></td>
                                                            <td>
                                                                <span class="badge badge-success"><?php echo htmlspecialchars($statusLabel); ?></span>
                                                            </td>
                                                        </tr>
                                                    <?php endforeach; ?>
                                                <?php endif; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
            @include('admin.partials.footer')
</body>

</html>




