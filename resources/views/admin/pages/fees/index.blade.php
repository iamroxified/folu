<?php
// Start session

// Include database configuration and functions
require_once base_path('db/config.php');
require_once base_path('db/functions.php');

// Check if user is logged in
if (!isset($_SESSION['adid'])) {
    header('Location: /admin/login.php');
    exit;
}

// Fetch fee types data
$fees = QueryDB("SELECT fees.*, ft.fee_name as fee_type_name, c.class_name, c.class_arm, acs.session_name, acs.session_term 
                FROM fees
                LEFT JOIN fee_type ft ON fees.fee_name = ft.id
                LEFT JOIN classes c ON fees.fee_class = c.id 
                LEFT JOIN academic_sessions acs ON fees.fee_session = acs.id 
                ORDER BY ft.fee_name")->fetchAll();



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
                        <h2 class="text-dark pb-2 fw-bold">Fees </h2>
                    </div>

                    <div class="row">
                        <div class="col-md-12">
                            <div class="card">
                                <div class="card-header">
                                    <div class="card-title">All Fees</div>
                                </div>
                                <div class="card-body">
                                    <div class="table-responsive">
                                        <table id="basic-datatables" class="display table table-striped table-hover">
                                            <thead>
                                                <tr>
                                                    <th>Fee Name</th>
                                                    <th>Class</th>
                                                    <th>Session</th>
                                                    <th>Amount</th>
                                                    <th>Description</th>
                                                    <th>Created At</th>
                                                    <th>Status</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php foreach ($fees as $fee):  ?> 
                                                  <tr>
                                                        <td><?php echo htmlspecialchars($fee['fee_type_name']); ?></td>
                                                        <td><?php echo htmlspecialchars($fee['class_name'] . ' ' . $fee['class_arm']); ?></td>
                                                        <td><?php echo htmlspecialchars($fee['session_name'] . ' (' . $fee['session_term'] . ' Term)'); ?></td>
                                                        <td><?php echo htmlspecialchars($fee['fee_amount']); ?></td>
                                                        <td><?php echo htmlspecialchars($fee['fee_description']); ?></td>
                                                        <td><?php echo htmlspecialchars($fee['created_at']); ?></td>

                                                        <td><?php echo htmlspecialchars($fee['status']); ?></td>
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

                    $('#multi-filter-select').DataTable({
                        "pageLength": 5,
                        initComplete: function() {
                            this.api().columns().every(function() {
                                var column = this;
                                var select = $('<select class="form-control"><option value=""></option></select>')
                                    .appendTo($(column.footer()).empty())
                                    .on('change', function() {
                                        var val = $.fn.dataTable.util.escapeRegex(
                                            $(this).val()
                                        );

                                        column
                                            .search(val ? '^' + val + '$' : '', true, false)
                                            .draw();
                                    });

                                column.data().unique().sort().each(function(d, j) {
                                    select.append('<option value="' + d + '">' + d + '</option>')
                                });
                            });
                        }
                    });
                });
            </script>
</body>

</html>



