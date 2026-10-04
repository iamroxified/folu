<?php
// Start session
if (session_status() === PHP_SESSION_NONE) {
    @session_start();
}

require_once base_path('db/config.php');
require_once base_path('db/functions.php');

if (!isset($_SESSION['adid']) && !auth()->check()) {
    header('Location: /admin/login.php');
    exit;
}

// Handle POST deletion
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_fee_id'])) {
    $deleteId = (int) $_POST['delete_fee_id'];
    $targetFee = \App\Models\FeeStructure::find($deleteId);
    if ($targetFee) {
        $oldData = $targetFee->toArray();
        $targetFee->delete();
        \App\Models\FinancialAuditLog::logAction('deleted_fee_structure', null, $oldData, []);
        $success = 'Fee structure deleted successfully.';
    } else {
        $error = 'Fee structure not found.';
    }
}

$fees = QueryDB(
    "SELECT fs.*,
            sc.class_name,
            sc.section AS class_arm,
            ac.session_name,
            t.term_name
     FROM fee_structures fs
     LEFT JOIN school_classes sc ON fs.class_id = sc.id
     LEFT JOIN academic_sessions ac ON fs.session_id = ac.id
     LEFT JOIN terms t ON fs.term_id = t.id
     ORDER BY fs.created_at DESC, fs.id DESC"
)->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <title>Fees Management</title>
    @include('admin.partials.links')
</head>

<body>
    <div class="wrapper">
        @include('admin.partials.sidebar')

        <div class="main-panel">
            @include('admin.partials.header')
            <div class="container">
                <div class="page-inner">
                    <div class="d-flex align-items-left align-items-md-center flex-column flex-md-row pb-3">
                        <h2 class="text-dark pb-2 fw-bold">Fee Structures</h2>
                        <div class="ms-md-auto">
                            <a href="/admin/fees/add.php" class="btn btn-primary btn-round"><i class="fas fa-plus me-1"></i> Add Fee Structure</a>
                        </div>
                    </div>

                    <?php if (isset($success)): ?>
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            <?php echo htmlspecialchars($success); ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    <?php endif; ?>

                    <?php if (isset($error)): ?>
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <?php echo htmlspecialchars($error); ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    <?php endif; ?>

                    @if(session('success'))
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            {{ session('success') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif

                    <div class="row">
                        <div class="col-md-12">
                            <div class="card">
                                <div class="card-header">
                                    <div class="card-title">All Fee Structures</div>
                                    <div class="card-category">Manage, edit, or delete configured fee structures.</div>
                                </div>
                                <div class="card-body">
                                    <div class="table-responsive">
                                        <table id="basic-datatables" class="display table table-striped table-hover align-middle">
                                            <thead>
                                                 <tr>
                                                    <th style="width: 50px;">S/N</th>
                                                    <th>Name</th>
                                                    <th>Type</th>
                                                    <th>Class</th>
                                                    <th>Session</th>
                                                    <th>Term</th>
                                                    <th>Amount (₦)</th>
                                                    <th>Category</th>
                                                    <th>Status</th>
                                                    <th class="text-center" style="width: 120px;">Actions</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php $sn = 1; foreach ($fees as $fee): ?>
                                                <tr>
                                                    <td><?php echo $sn++; ?></td>
                                                    <td><strong><?php echo htmlspecialchars((string) $fee['name']); ?></strong></td>
                                                    <td><?php echo htmlspecialchars(ucwords(str_replace('_', ' ', (string) $fee['fee_type']))); ?></td>
                                                    <td><?php echo htmlspecialchars(trim(((string) ($fee['class_name'] ?? 'All Classes')) . ' ' . ((string) ($fee['class_arm'] ?? '')))); ?></td>
                                                    <td><?php echo htmlspecialchars((string) ($fee['session_name'] ?? 'N/A')); ?></td>
                                                    <td><?php echo htmlspecialchars((string) ($fee['term_name'] ?? ($fee['category'] === 'NI' ? 'Session-Based (N/A)' : 'N/A'))); ?></td>
                                                    <td class="fw-bold text-primary">₦<?php echo number_format((float) ($fee['amount'] ?? 0), 2); ?></td>
                                                    <td>
                                                        <span class="badge bg-<?php echo $fee['category'] === 'NI' ? 'info' : 'secondary'; ?>">
                                                            <?php echo $fee['category'] === 'NI' ? 'New Intake (NI)' : 'Returning (OS)'; ?>
                                                        </span>
                                                    </td>
                                                    <td>
                                                        <?php if (!empty($fee['is_active'])): ?>
                                                        <span class="badge bg-success">Active</span>
                                                        <?php else: ?>
                                                        <span class="badge bg-secondary">Inactive</span>
                                                        <?php endif; ?>
                                                    </td>
                                                    <td class="text-center">
                                                        <div class="d-flex justify-content-center gap-1">
                                                            <a href="/admin/fees/edit.php?id=<?php echo (int) $fee['id']; ?>" class="btn btn-sm btn-outline-primary" data-bs-toggle="tooltip" title="Edit Fee Structure">
                                                                <i class="fas fa-edit"></i>
                                                            </a>
                                                            <form method="POST" action="" class="d-inline" onsubmit="return confirm('Are you sure you want to delete the fee structure &quot;<?php echo htmlspecialchars((string)$fee['name']); ?>&quot;?');">
                                                                <?php if (function_exists('csrf_field')) echo csrf_field(); ?>
                                                                <input type="hidden" name="delete_fee_id" value="<?php echo (int) $fee['id']; ?>">
                                                                <button type="submit" class="btn btn-sm btn-outline-danger" data-bs-toggle="tooltip" title="Delete Fee Structure">
                                                                    <i class="fas fa-trash"></i>
                                                                </button>
                                                            </form>
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
