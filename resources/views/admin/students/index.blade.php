<?php
// Start session
if (session_status() === PHP_SESSION_NONE) {
    @session_start();
}

// Include database and functions
require_once base_path('db/config.php');
require_once base_path('db/functions.php');

// Check if user is logged in
if (!isset($_SESSION['adid']) && !auth()->check()) {
    header('Location: /admin/login.php');
    exit;
}

// Fetch students data
$students = QueryDB("SELECT * FROM students ORDER BY id DESC")->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <title>Students Management</title>
    @include('admin.partials.links')
</head>

<body>
    <div class="wrapper">
        @include('admin.partials.sidebar')

        <div class="main-panel">
            @include('admin.partials.header')
            <div class="container">
                <div class="page-inner">
                    <div class="d-flex align-items-left flex-column flex-md-row pb-3">
                        <h2 class="text-dark pb-2 fw-bold">Students</h2>
                        <div class="ms-md-auto py-2 py-md-0">
                            <a href="/admin/students/add.php" class="btn btn-primary"><i class="fas fa-user-plus me-1"></i> Add New Student</a>
                            <a href="/admin/students/list.php" class="btn btn-info"><i class="fas fa-users me-1"></i> Manage Students</a>
                            <a href="/admin/students/import_export.php" class="btn btn-success"><i class="fas fa-file-excel me-1"></i> Import/Export</a>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-12">
                            <div class="card">
                                <div class="card-header">
                                    <div class="card-title">All Registered Students</div>
                                </div>
                                <div class="card-body">
                                    <div class="table-responsive">
                                        <table id="basic-datatables" class="display table table-striped table-hover align-middle">
                                            <thead>
                                                <tr>
                                                    <th style="width: 60px;">S/N</th>
                                                    <th>Admission No</th>
                                                    <th>First Name</th>
                                                    <th>Last Name</th>
                                                    <th>Email</th>
                                                    <th>Status</th>
                                                    <th class="text-center" style="width: 120px;">Actions</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php $sn = 1; foreach ($students as $student): ?>
                                                    <tr>
                                                        <td><?php echo $sn++; ?></td>
                                                        <td><code><?php echo htmlspecialchars((string)$student['admission_no']); ?></code></td>
                                                        <td class="fw-bold"><?php echo htmlspecialchars((string)$student['first_name']); ?></td>
                                                        <td><?php echo htmlspecialchars((string)$student['last_name']); ?></td>
                                                        <td><?php echo htmlspecialchars((string)$student['email']); ?></td>
                                                        <td>
                                                            <span class="badge bg-<?php echo ($student['status'] ?? 'active') === 'active' ? 'success' : 'secondary'; ?>">
                                                                <?php echo ucfirst((string)($student['status'] ?? 'active')); ?>
                                                            </span>
                                                        </td>
                                                        <td class="text-center">
                                                            <div class="d-flex justify-content-center gap-1">
                                                                <a href="/admin/students/view.php?id=<?php echo $student['id']; ?>" class="btn btn-info btn-sm" data-bs-toggle="tooltip" title="View Profile">
                                                                    <i class="fas fa-eye"></i>
                                                                </a>
                                                                <a href="/admin/students/edit.php?id=<?php echo $student['id']; ?>" class="btn btn-warning btn-sm" data-bs-toggle="tooltip" title="Edit Student">
                                                                    <i class="fas fa-edit"></i>
                                                                </a>
                                                            </div>
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

                </div>
            </div>
            @include('admin.partials.footer')

            <script>
                $(document).ready(function() {
                    $('#basic-datatables').DataTable({ retrieve: true });
                });
            </script>
</body>

</html>
