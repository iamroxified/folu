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

// Match the current teachers schema while staying compatible with older variants.
$teacherColumns = QueryDB("SHOW COLUMNS FROM teachers")->fetchAll(PDO::FETCH_COLUMN);
$teacherIdField = in_array('employee_id', $teacherColumns, true) ? 'employee_id' : 'teacher_id';
$teacherRoleField = in_array('position', $teacherColumns, true) ? 'position' : 'qualification';
$teacherDeptField = in_array('department', $teacherColumns, true) ? 'department' : 'specialization';
$teacherDateField = in_array('hire_date', $teacherColumns, true) ? 'hire_date' : 'employment_date';

$teacherIdLabel = $teacherIdField === 'employee_id' ? 'Employee ID' : 'Teacher ID';
$teacherRoleLabel = $teacherRoleField === 'position' ? 'Position' : 'Qualification';
$teacherDeptLabel = $teacherDeptField === 'department' ? 'Department' : 'Specialization';
$teacherDateLabel = $teacherDateField === 'hire_date' ? 'Hire Date' : 'Employment Date';

$teachers = QueryDB("SELECT * FROM teachers WHERE status = 'active' OR status IS NULL")->fetchAll();

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <title>Teachers Management</title>
    <?php echo $__env->make('admin.partials.links', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
</head>

<body>
    <div class="wrapper">
        <?php echo $__env->make('admin.partials.sidebar', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

        <div class="main-panel">
            <?php echo $__env->make('admin.partials.header', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
            <div class="container">
                <div class="page-inner">
                    <div class="d-flex align-items-left flex-column flex-md-row">
                        <h2 class="text-dark pb-2 fw-bold">Teachers & Staff</h2>
                    </div>

                    <div class="row">
                        <div class="col-md-12">
                            <div class="card">
                                <div class="card-header">
                                    <div class="card-title">All Teachers</div>
                                </div>
                                <div class="card-body">
                                    <div class="table-responsive">
                                        <table class="table table-bordered">
                                            <thead>
                                                <tr>
                                                    <th><?php echo htmlspecialchars($teacherIdLabel); ?></th>
                                                    <th>Name</th>
                                                    <th>Email</th>
                                                    <th><?php echo htmlspecialchars($teacherRoleLabel); ?></th>
                                                    <th><?php echo htmlspecialchars($teacherDeptLabel); ?></th>
                                                    <th><?php echo htmlspecialchars($teacherDateLabel); ?></th>
                                                    <th>Status</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php foreach ($teachers as $teacher): ?>
                                                    <?php
                                                        $teacherId = $teacher[$teacherIdField] ?? 'N/A';
                                                        $teacherName = trim(($teacher['first_name'] ?? '') . ' ' . ($teacher['last_name'] ?? ''));
                                                        $teacherEmail = $teacher['email'] ?? 'N/A';
                                                        $teacherRole = $teacher[$teacherRoleField] ?? 'N/A';
                                                        $teacherDept = $teacher[$teacherDeptField] ?? 'N/A';
                                                        $teacherDate = $teacher[$teacherDateField] ?? null;
                                                    ?>
                                                    <tr>
                                                        <td><?php echo htmlspecialchars($teacherId); ?></td>
                                                        <td><?php echo htmlspecialchars($teacherName !== '' ? $teacherName : 'N/A'); ?></td>
                                                        <td><?php echo htmlspecialchars($teacherEmail); ?></td>
                                                        <td><?php echo htmlspecialchars($teacherRole); ?></td>
                                                        <td><?php echo htmlspecialchars($teacherDept); ?></td>
                                                        <td><?php echo $teacherDate ? htmlspecialchars(date('M d, Y', strtotime($teacherDate))) : 'N/A'; ?></td>
                                                        <td>
                                                            <span class="badge badge-success"><?php echo htmlspecialchars(ucfirst($teacher['status'] ?? 'active')); ?></span>
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
            <?php echo $__env->make('admin.partials.footer', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
</body>

</html>




<?php /**PATH C:\laragon\www\folu\resources\views/admin/teachers/index.blade.php ENDPATH**/ ?>