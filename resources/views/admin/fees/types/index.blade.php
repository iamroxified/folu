<?php
require_once base_path('db/config.php');
require_once base_path('db/functions.php');
require_once base_path('db/school_portal_helpers.php');

if (!isset($_SESSION['adid']) && !auth()->check()) {
    header('Location: /admin/login.php');
    exit;
}

if (session_status() === PHP_SESSION_NONE) {
    @session_start();
}

$flashMessage = $_SESSION['flash_message'] ?? session('success') ?? '';
$flashError = $_SESSION['flash_error'] ?? session('error') ?? '';
unset($_SESSION['flash_message'], $_SESSION['flash_error']);

$feeTypeOptions = schema_enum_values('fee_structures', 'fee_type', true);
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
            <div>
              <h2 class="text-dark pb-2 fw-bold">Fee Types Management</h2>
              <p class="text-muted mb-0">Manage predefined fee types used in fee structures and payment import.</p>
            </div>
            <div class="ms-md-auto py-2 py-md-0 d-flex gap-2">
                <a href="add_fee_type.php" class="btn btn-primary btn-round">
                    <i class="fas fa-plus me-1"></i> Add Fee Type
                </a>
                <a href="add_fees.php" class="btn btn-outline-primary btn-round">
                    <i class="fas fa-layer-group me-1"></i> Add Fee Structure
                </a>
            </div>
          </div>

          <?php if (!empty($flashMessage)): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
              <i class="fas fa-check-circle me-1"></i> <?php echo htmlspecialchars($flashMessage); ?>
              <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
          <?php endif; ?>

          <?php if (!empty($flashError)): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
              <i class="fas fa-exclamation-triangle me-1"></i> <?php echo htmlspecialchars($flashError); ?>
              <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
          <?php endif; ?>

          <div class="row">
            <div class="col-md-12">
              <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                  <div class="card-title">Available Fee Types (<?php echo count($feeTypes); ?>)</div>
                </div>
                <div class="card-body">
                  <div class="table-responsive">
                    <table id="add-row" class="display table table-striped table-hover align-middle">
                      <thead class="table-light">
                        <tr>
                          <th style="width: 60px;">S/N</th>
                          <th>Fee Type Code</th>
                          <th>Display Name</th>
                          <th>Structures Using It</th>
                          <th class="text-center" style="width: 180px;">Actions</th>
                        </tr>
                      </thead>
                      <tbody>
                        <?php $sn = 1; foreach ($feeTypes as $feeType): ?>
                        <tr>
                          <td><?php echo $sn++; ?></td>
                          <td><code><?php echo htmlspecialchars($feeType['fee_type']); ?></code></td>
                          <td class="fw-bold"><?php echo htmlspecialchars(ucwords(str_replace('_', ' ', $feeType['fee_type']))); ?></td>
                          <td>
                            <?php if ($feeType['usage_count'] > 0): ?>
                              <span class="badge bg-success"><?php echo (int) $feeType['usage_count']; ?> structure(s)</span>
                            <?php else: ?>
                              <span class="badge bg-secondary">Unused (0)</span>
                            <?php endif; ?>
                          </td>
                          <td class="text-center">
                            <div class="d-flex justify-content-center gap-1">
                              <a href="edit_fee_type.php?id=<?php echo urlencode($feeType['fee_type']); ?>" class="btn btn-warning btn-sm" title="Edit Fee Type">
                                <i class="fas fa-edit me-1"></i> Edit
                              </a>
                              <?php if ($feeType['usage_count'] === 0): ?>
                                <form method="POST" action="edit_fee_type.php?id=<?php echo urlencode($feeType['fee_type']); ?>" class="d-inline" onsubmit="return confirm('Are you sure you want to delete the fee type \'<?php echo htmlspecialchars($feeType['fee_type']); ?>\'?');">
                                  <input type="hidden" name="action" value="delete">
                                  <input type="hidden" name="fee_type" value="<?php echo htmlspecialchars($feeType['fee_type']); ?>">
                                  <button type="submit" class="btn btn-danger btn-sm" title="Delete Unused Fee Type">
                                    <i class="fas fa-trash-alt me-1"></i> Delete
                                  </button>
                                </form>
                              <?php else: ?>
                                <button type="button" class="btn btn-outline-secondary btn-sm" disabled title="Cannot delete: currently used by <?php echo $feeType['usage_count']; ?> fee structure(s)">
                                  <i class="fas fa-lock me-1"></i> In Use
                                </button>
                              <?php endif; ?>
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
        $(document).ready(function () {
          if ($.fn.DataTable && !$.fn.DataTable.isDataTable('#add-row')) {
            $('#add-row').DataTable({
              retrieve: true,
              pageLength: 10,
              responsive: true
            });
          }
        });
      </script>

</body>

</html>
