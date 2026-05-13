<?php
require_once base_path('db/config.php');
require_once base_path('db/functions.php');

if (!isset($_SESSION['adid'])) {
    header('Location: /admin/login.php');
    exit;
}

$feeTypeOptions = schema_enum_values('fee_structures', 'fee_type');
$feeTypes = [];

foreach ($feeTypeOptions as $index => $feeType) {
    $usageCount = (int) QueryDB(
        'SELECT COUNT(*) FROM fee_structures WHERE fee_type = ?',
        [$feeType]
    )->fetchColumn();

    $feeTypes[] = [
        'id' => $index + 1,
        'fee_type' => $feeType,
        'usage_count' => $usageCount,
    ];
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta http-equiv="X-UA-Compatible" content="IE=edge" />
  <title>Fee Types</title>
  <?php echo $__env->make('admin.partials.links', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
</head>

<body>
  <div class="wrapper">
    <?php echo $__env->make('admin.partials.sidebar', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

    <div class="main-panel">
      <?php echo $__env->make('admin.partials.header', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
      <div class="container">
        <div class="page-inner">
          <div class="d-flex align-items-left align-items-md-center flex-column flex-md-row">
            <h2 class="text-dark pb-2 fw-bold">Fee Types</h2>
            <div class="ml-md-auto py-2 py-md-0">
                <a href="add_fees.php" class="btn btn-primary btn-round">Add Fee Structure</a>
            </div>
          </div>

          <div class="alert alert-info">
            Fee types are predefined by the current `fee_structures.fee_type` enum. They are reference values, not rows in a separate `fee_type` table.
          </div>

          <div class="row">
            <div class="col-md-12">
              <div class="card">
                <div class="card-header">
                  <div class="card-title">Available Fee Types</div>
                </div>
                <div class="card-body">
                  <div class="table-responsive">
                    <table id="add-row" class="display table table-hover">
                      <thead>
                        <tr>
                          <th>SN</th>
                          <th>Fee Type</th>
                          <th>Display Name</th>
                          <th>Structures Using It</th>
                        </tr>
                      </thead>
                      <tbody>
                        <?php foreach ($feeTypes as $feeType): ?>
                        <tr>
                          <td><?php echo (int) $feeType['id']; ?></td>
                          <td><code><?php echo htmlspecialchars($feeType['fee_type']); ?></code></td>
                          <td><?php echo htmlspecialchars(ucwords(str_replace('_', ' ', $feeType['fee_type']))); ?></td>
                          <td><?php echo (int) $feeType['usage_count']; ?></td>
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
      <script>
        $(document).ready(function () {
          $('#add-row').DataTable({
            pageLength: 10,
          });
        });
      </script>

</body>

</html>
<?php /**PATH C:\laragon\www\folu\resources\views/admin/fees/types/index.blade.php ENDPATH**/ ?>