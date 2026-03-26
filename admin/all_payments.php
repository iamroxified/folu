<?php
// Start session
session_start();

// Include database and functions
require '../db/config.php';
require '../db/functions.php';

// Check if user is logged in
if (!isset($_SESSION['adid'])) {
    header('location:login.php');
    exit;
}

// Fetch student fees data
$student_fees = QueryDB("
    SELECT sf.*, s.first_name, s.last_name, s.admission_no, fs.fee_name, fs.amount
    FROM student_fees sf 
    LEFT JOIN students s ON sf.student_link = s.id 
    LEFT JOIN fee_structures fs ON sf.fee_structure_link = fs.id
    ORDER BY sf.created_at DESC
    LIMIT 50
")->fetchAll();

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <title>Fees Management</title>
    <?php include('nav/links.php'); ?>
</head>

<body>
    <div class="wrapper">
        <?php include('nav/sidebar.php'); ?>

        <div class="main-panel">
            <?php include('nav/header.php'); ?>
            <div class="container">
                <div class="page-inner">
                    <div class="d-flex align-items-left flex-column flex-md-row">
                        <h2 class="text-dark pb-2 fw-bold">Fee Management</h2>
                    </div>

                    <div class="row">
                        <div class="col-md-12">
                            <div class="card">
                                <div class="card-header">
                                    <div class="card-title">Student Fees</div>
                                </div>
                                <div class="card-body">
                                        <div class="table-responsive">
                    <table id="add-row" class="display table  table-hover">
                                            <thead>
                                                <tr>
                                                    <th>Student</th>
                                                    <th>Admission No</th>
                                                    <th>Fee Type</th>
                                                    <th>Amount</th>
                                                    <th>Status</th>
                                                    <th>Due Date</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php foreach ($student_fees as $fee): ?>
                                                    <tr>
                                                        <td><?php echo $fee['first_name'] . ' ' . $fee['last_name']; ?></td>
                                                        <td><?php echo $fee['admission_no']; ?></td>
                                                        <td><?php echo $fee['fee_name']; ?></td>
                                                        <td>₦<?php echo number_format($fee['amount'], 2); ?></td>
                                                        <td>
                                                            <?php if ($fee['status'] == 'paid'): ?>
                                                                <span class="badge badge-success">Paid</span>
                                                            <?php elseif ($fee['status'] == 'pending'): ?>
                                                                <span class="badge badge-warning">Pending</span>
                                                            <?php else: ?>
                                                                <span class="badge badge-danger">Overdue</span>
                                                            <?php endif; ?>
                                                        </td>
                                                        <td><?php echo date('M d, Y', strtotime($fee['due_date'])); ?></td>
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
        <?php include('nav/footer.php'); ?>
      <script>
        function copyToClipboard2() {
          const copyInput = document.getElementById("copyText2");
          copyInput.select();
          copyInput.setSelectionRange(0, 99999); // for mobile
          document.execCommand("copy");
          alert("Copied: " + copyInput.value);
        }

        function copyToClipboard() {
          const copyInput = document.getElementById("copyText");
          copyInput.select();
          copyInput.setSelectionRange(0, 99999); // for mobile
          document.execCommand("copy");
          alert("Copied: " + copyInput.value);
        }
      </script>

      <script>
        $(document).ready(function () {
          $("#basic-datatables").DataTable({});

          $("#multi-filter-select").DataTable({
            pageLength: 5,
            initComplete: function () {
              this.api()
                .columns()
                .every(function () {
                  var column = this;
                  var select = $(
                      '<select class="form-select"><option value=""></option></select>'
                    )
                    .appendTo($(column.footer()).empty())
                    .on("change", function () {
                      var val = $.fn.dataTable.util.escapeRegex($(this).val());

                      column
                        .search(val ? "^" + val + "$" : "", true, false)
                        .draw();
                    });

                  column
                    .data()
                    .unique()
                    .sort()
                    .each(function (d, j) {
                      select.append(
                        '<option value="' + d + '">' + d + "</option>"
                      );
                    });
                });
            },
          });

          // Add Row
          $("#add-row").DataTable({
            pageLength: 10,
          });


        });
      </script>
</body>

</html>
