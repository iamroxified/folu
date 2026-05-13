<?php
require_once base_path('db/config.php');
require_once base_path('db/functions.php');

if (!isset($_SESSION['adid'])) {
    header('Location: /admin/login.php');
    exit;
}

$feeTypeOptions = array_values(schema_enum_values('fee_structures', 'fee_type'));
$requestedId = $_GET['id'] ?? null;
$selectedIndex = is_numeric($requestedId) ? ((int) $requestedId - 1) : null;
$selectedType = $requestedId !== null && !is_numeric($requestedId)
    ? (string) $requestedId
    : ($selectedIndex !== null && isset($feeTypeOptions[$selectedIndex]) ? $feeTypeOptions[$selectedIndex] : null);

if ($selectedType === null || !in_array($selectedType, $feeTypeOptions, true)) {
    die('Fee type not found');
}

$usageCount = (int) QueryDB(
    'SELECT COUNT(*) FROM fee_structures WHERE fee_type = ?',
    [$selectedType]
)->fetchColumn();
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta http-equiv="X-UA-Compatible" content="IE=edge" />
  <title>Fee Type Details</title>
  @include('admin.partials.links')
</head>

<body>
  <div class="wrapper">
    @include('admin.partials.sidebar')

    <div class="main-panel">
      @include('admin.partials.header')
      <div class="container">
        <div class="page-inner">
          <div class="d-flex align-items-left align-items-md-center flex-column flex-md-row">
            <h2 class="text-dark pb-2 fw-bold">Fee Type Details</h2>
            <div class="ml-md-auto py-2 py-md-0">
                <a href="fee_type.php" class="btn btn-secondary btn-round">Back to Fee Types</a>
            </div>
          </div>
          <div class="row">
            <div class="col-md-12">
              <div class="card">
                <div class="card-header">
                  <div class="card-title"><?php echo htmlspecialchars(ucwords(str_replace('_', ' ', $selectedType))); ?></div>
                </div>
                <div class="card-body">
                  <div class="alert alert-info">
                    This fee type is defined by the `fee_structures.fee_type` enum in the live schema, so it is not editable from this legacy screen.
                  </div>
                  <div class="table-responsive">
                    <table class="table table-bordered">
                      <tr>
                        <th style="width: 220px;">Stored Value</th>
                        <td><code><?php echo htmlspecialchars($selectedType); ?></code></td>
                      </tr>
                      <tr>
                        <th>Display Name</th>
                        <td><?php echo htmlspecialchars(ucwords(str_replace('_', ' ', $selectedType))); ?></td>
                      </tr>
                      <tr>
                        <th>Structures Using It</th>
                        <td><?php echo $usageCount; ?></td>
                      </tr>
                    </table>
                  </div>
                  <a href="add_fees.php" class="btn btn-primary">Create a Fee Structure</a>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
      @include('admin.partials.footer')
    </div>
  </div>
</body>

</html>
