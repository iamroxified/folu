<?php
// Start session

require_once base_path('db/config.php');
require_once base_path('db/functions.php');

if (!isset($_SESSION['adid'])) {
    header('Location: /admin/login.php');
    exit;
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
                    <div class="d-flex align-items-left flex-column flex-md-row">
                        <h2 class="text-dark pb-2 fw-bold">Fee Structures</h2>
                    </div>

                    <div class="row">
                        <div class="col-md-12">
                            <div class="card">
                                <div class="card-header">
                                    <div class="card-title">All Fee Structures</div>
                                    <div class="card-category">Live schema view backed by `fee_structures`.</div>
                                </div>
                                <div class="card-body">
                                    <div class="table-responsive">
                                        <table id="basic-datatables" class="display table table-striped table-hover">
                                            <thead>
                                                <tr>
                                                    <th>Name</th>
                                                    <th>Type</th>
                                                    <th>Class</th>
                                                    <th>Session</th>
                                                    <th>Term</th>
                                                    <th>Amount</th>
                                                    <th>Category</th>
                                                    <th>Status</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php foreach ($fees as $fee): ?>
                                                <tr>
                                                    <td><?php echo htmlspecialchars((string) $fee['name']); ?></td>
                                                    <td><?php echo htmlspecialchars(ucwords(str_replace('_', ' ', (string) $fee['fee_type']))); ?></td>
                                                    <td><?php echo htmlspecialchars(trim(((string) ($fee['class_name'] ?? 'All Classes')) . ' ' . ((string) ($fee['class_arm'] ?? '')))); ?></td>
                                                    <td><?php echo htmlspecialchars((string) ($fee['session_name'] ?? 'N/A')); ?></td>
                                                    <td><?php echo htmlspecialchars((string) ($fee['term_name'] ?? 'N/A')); ?></td>
                                                    <td>N<?php echo number_format((float) ($fee['amount'] ?? 0), 2); ?></td>
                                                    <td><?php echo htmlspecialchars((string) ($fee['category'] ?? 'N/A')); ?></td>
                                                    <td>
                                                        <?php if (!empty($fee['is_active'])): ?>
                                                        <span class="badge badge-success">Active</span>
                                                        <?php else: ?>
                                                        <span class="badge badge-secondary">Inactive</span>
                                                        <?php endif; ?>
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
                    $('#basic-datatables').DataTable({});
                });
            </script>
</body>

</html>
