<?php
require_once base_path('db/config.php');
require_once base_path('db/functions.php');

if (!isset($_SESSION['adid'])) {
    header('Location: /admin/login.php');
    exit;
}

$studentFees = QueryDB(
    "SELECT sf.*,
            s.first_name,
            s.last_name,
            s.admission_no,
            fs.name AS fee_name,
            fs.fee_type,
            fs.amount AS fee_amount
     FROM student_fees sf
     LEFT JOIN students s ON sf.student_id = s.id
     LEFT JOIN fee_structures fs ON sf.fee_structure_id = fs.id
     ORDER BY sf.created_at DESC, sf.id DESC
     LIMIT 100"
)->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <title>Student Fees</title>
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
                        <h2 class="text-dark pb-2 fw-bold">Student Fees</h2>
                    </div>

                    <div class="row">
                        <div class="col-md-12">
                            <div class="card">
                                <div class="card-header">
                                    <div class="card-title">Assigned Fee Records</div>
                                </div>
                                <div class="card-body">
                                    <div class="table-responsive">
                                        <table id="add-row" class="display table table-hover">
                                            <thead>
                                                <tr>
                                                    <th>Student</th>
                                                    <th>Admission No</th>
                                                    <th>Fee Name</th>
                                                    <th>Fee Type</th>
                                                    <th>Amount Due</th>
                                                    <th>Amount Paid</th>
                                                    <th>Balance</th>
                                                    <th>Status</th>
                                                    <th>Due Date</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php foreach ($studentFees as $fee): ?>
                                                    <tr>
                                                        <td><?php echo htmlspecialchars(trim(($fee['first_name'] ?? '') . ' ' . ($fee['last_name'] ?? ''))); ?></td>
                                                        <td><?php echo htmlspecialchars((string) ($fee['admission_no'] ?? '')); ?></td>
                                                        <td><?php echo htmlspecialchars((string) ($fee['fee_name'] ?? 'Fee Structure')); ?></td>
                                                        <td><?php echo htmlspecialchars(ucwords(str_replace('_', ' ', (string) ($fee['fee_type'] ?? '')))); ?></td>
                                                        <td>N<?php echo number_format((float) ($fee['amount_due'] ?? 0), 2); ?></td>
                                                        <td>N<?php echo number_format((float) ($fee['amount_paid'] ?? 0), 2); ?></td>
                                                        <td>N<?php echo number_format((float) ($fee['balance'] ?? 0), 2); ?></td>
                                                        <td>
                                                            <?php if (($fee['status'] ?? '') === 'paid'): ?>
                                                                <span class="badge badge-success">Paid</span>
                                                            <?php elseif (($fee['status'] ?? '') === 'pending'): ?>
                                                                <span class="badge badge-warning">Pending</span>
                                                            <?php elseif (($fee['status'] ?? '') === 'partial'): ?>
                                                                <span class="badge badge-info">Partial</span>
                                                            <?php else: ?>
                                                                <span class="badge badge-danger"><?php echo htmlspecialchars(ucfirst((string) ($fee['status'] ?? 'unknown'))); ?></span>
                                                            <?php endif; ?>
                                                        </td>
                                                        <td><?php echo !empty($fee['due_date']) ? htmlspecialchars(date('M d, Y', strtotime((string) $fee['due_date']))) : 'N/A'; ?></td>
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
        $(document).ready(function () {
          $("#add-row").DataTable({
            pageLength: 10,
          });
        });
      </script>
</body>

</html>
