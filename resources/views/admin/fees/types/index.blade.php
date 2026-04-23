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
$fee_types = QueryDB("SELECT * FROM fee_type ORDER BY fee_name")->fetchAll(PDO::FETCH_ASSOC);

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
          <div class="d-flex align-items-left align-items-md-center flex-column flex-md-row">
            <h2 class="text-dark pb-2 fw-bold">Fee Types</h2>
            <div class="ml-md-auto py-2 py-md-0">
                <a href="add_fee_type.php" class="btn btn-primary btn-round">Add New Fee Type</a>
            </div>
          </div>
          <div class="row">
            <div class="col-md-12">
              <div class="card">
                <div class="card-header">
                  <div class="card-title">All Fee Types</div>
                </div>
                <div class="card-body">
                  <div class="table-responsive">
                    <table id="add-row" class="display table table-hover">
                      <thead>
                        <tr>
                          <th>SN</th>
                          <th>Fee Name</th>
                          <th>Description</th>
                          <th>Status</th>
                          <th>Actions</th>
                        </tr>
                      </thead>
                      <tbody>
                        <?php $sn=1; foreach ($fee_types as $fee_type): ?>
                        <tr>
                          <td><?php echo $sn; ?></td>
                          <td><?php echo htmlspecialchars($fee_type['fee_name']); ?></td>
                          <td><?php echo htmlspecialchars($fee_type['description']); ?></td>
                          <td><?php echo $fee_type['status']; ?></td>
                          <td>
                            <a href="edit_fee_type.php?id=<?php echo $fee_type['id']; ?>" class="btn btn-warning btn-sm">Edit</a>
                            <a href="delete_fee_type.php?id=<?php echo $fee_type['id']; ?>" class="btn btn-danger btn-sm">Delete</a>
                          </td>
                        </tr>
                        <?php $sn++; endforeach; ?>
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
          $('#add-row').DataTable({
            pageLength: 10,
          });
        });
      </script>

</body>

</html>




