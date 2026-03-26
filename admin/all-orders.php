<?php ob_start();
session_start();
require('../db/config.php');
require('../db/functions.php');

if (QueryDB("SELECT COUNT(*) FROM adminuser where username='" . $_SESSION['adid'] . "' and access = '" . $_SESSION['access'] . "'  ")->fetchColumn() < 1) {
  header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
  header("Expires: Sat, 01 Jan 2000 00:00:00 GMT");
  header('location:logout');
  exit;
} else {
  //echo $_SESSION['mid'];
  $uss = extract(get_admin_details($_SESSION['adid']));

  // Pagination settings
$records_per_page = 10;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $records_per_page;

// Get total orders count
$count_query = QueryDB("SELECT COUNT(*) as total FROM orders");
$total_orders = $count_query->fetch(PDO::FETCH_ASSOC)['total'];
$total_pages = ceil($total_orders / $records_per_page);

// Get orders for current page
$orders_query = QueryDB("SELECT * FROM orders ORDER BY created_at DESC LIMIT $records_per_page OFFSET $offset");
$orders = $orders_query->fetchAll(PDO::FETCH_ASSOC);

}


?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta http-equiv="X-UA-Compatible" content="IE=edge" />
  <title>Smart People Global - Dashboard</title>
  <?php include('nav/links.php'); ?>
</head>

<body>
  <div class="wrapper">
    <!-- Sidebar -->
    <?php include('nav/sidebar.php'); ?>
    <!-- End Sidebar -->

    <div class="main-panel">
      <?php include('nav/header.php'); ?>
      <div class="container">
        <div class="page-inner">
          <div class="row">
            <div class="col-md-12">
              <div class="card">
                <div class="card-header">
                  <div class="d-flex align-items-center">
                    <h4 class="card-title">All Orders History</h4>

                  </div>
                </div>
                <div class="card-body">
                  <?php if (!empty($orders)): ?>

                  <!-- Order Statistics -->
                  <div class="row mb-4">
                    <div class="col-md-3">
                      <div class="card card-stats card-primary card-round">
                        <div class="card-body">
                          <div class="row align-items-center">
                            <div class="col-icon">
                              <div class="icon-big text-center icon-primary bubble-shadow-small">
                                <i class="fas fa-shopping-cart"></i>
                              </div>
                            </div>
                            <div class="col col-stats ms-3 ms-sm-0">
                              <div class="numbers">
                                <p class="card-category">Total Orders</p>
                                <h4 class="card-title">
                                  <?php echo $total_orders; ?></h4>
                              </div>
                            </div>
                          </div>
                        </div>
                      </div>
                    </div>
                    <div class="col-md-3">
                      <div class="card card-stats card-warning card-round">
                        <div class="card-body">
                          <div class="row align-items-center">
                            <div class="col-icon">
                              <div class="icon-big text-center icon-warning bubble-shadow-small">
                                <i class="fas fa-clock"></i>
                              </div>
                            </div>
                            <div class="col col-stats ms-3 ms-sm-0">
                              <div class="numbers">
                                <p class="card-category">Pending Orders</p>
                                <h4 class="card-title">
                                  <?php 
                                    $pending_count = 0;
                                    foreach($orders as $order) {
                                      if($order['order_status'] == 'pending') $pending_count++;
                                    }
                                    echo $pending_count;
                                    ?>
                                </h4>
                              </div>
                            </div>
                          </div>
                        </div>
                      </div>
                    </div>
                    <div class="col-md-3">
                      <div class="card card-stats card-success card-round">
                        <div class="card-body">
                          <div class="row align-items-center">
                            <div class="col-icon">
                              <div class="icon-big text-center icon-success bubble-shadow-small">
                                <i class="fas fa-dollar-sign"></i>
                              </div>
                            </div>
                            <div class="col col-stats ms-3 ms-sm-0">
                              <div class="numbers">
                                <p class="card-category">Total Spent</p>
                                <h4 class="card-title">
                                  $<?php 
                                    $total_spent = 0;
                                    foreach($orders as $order) {
                                      if($order['order_status'] != 'cancelled') $total_spent += $order['total_amount'];
                                    }
                                    echo number_format($total_spent, 2);
                                    ?>
                                </h4>
                              </div>
                            </div>
                          </div>
                        </div>
                      </div>
                    </div>
                    <div class="col-md-3">
                      <div class="card card-stats card-info card-round">
                        <div class="card-body">
                          <div class="row align-items-center">
                            <div class="col-icon">
                              <div class="icon-big text-center icon-info bubble-shadow-small">
                                <i class="fas fa-star"></i>
                              </div>
                            </div>
                            <div class="col col-stats ms-3 ms-sm-0">
                              <div class="numbers">
                                <p class="card-category">Total PV Earned</p>
                                <h4 class="card-title pv-highlight">
                                  <?php 
                                    $total_pv = 0;
                                    foreach($orders as $order) {
                                      if($order['order_status'] != 'cancelled') $total_pv += $order['total_pv'];
                                    }
                                    echo number_format($total_pv);
                                    ?>
                                </h4>
                              </div>
                            </div>
                          </div>
                        </div>
                      </div>
                    </div>
                  </div>

                  <!-- Orders List -->
                  <div class="table-responsive">
                    <table id="add-row" class="display table  table-hover">
                      <thead>
                        <tr>
                          <th>SN</th>
                          <th>Order Number</th>
                          <th>Date</th>
                          <th>Order Status</th>
                          <th>Payment Method</th>
                          <th>Payment Status</th>
                          <th>Paid By</th>
                          <th>Amount</th>
                          <th>PV</th>
                          <th>Action</th>
                        </tr>
                      </thead>
                      <tfoot>
                        <tr>
                          <th>SN</th>
                          <th>Order Number</th>
                          <th>Date</th>
                          <th>Order Status</th>
                          <th>Payment Method</th>
                          <th>Payment Status</th>
                          <th>Paid By</th>
                             <th>Amount</th>
                          <th>PV</th>
                          <th>Action</th>
                        </tr>
                      </tfoot>
                      <tbody>

                        <?php $sn=1; foreach($orders as $order): ?>
                        <tr>
                          <td><?php echo $sn; ?></td>
                          <td>#<?php echo htmlspecialchars($order['order_number']); ?></td>
                          <td><?php echo date('F j, Y g:i A', strtotime($order['created_at'])); ?></td>
                          <td><?php echo ucfirst($order['order_status']); ?></td>
                          <td><?php echo ucfirst(str_replace('_', ' ', $order['payment_method'])); ?></td>
                          <td><?php echo ucfirst($order['payment_status']); ?></td>
                          <td><?php echo $order['full_name']; ?></td>
                          <td> $<?php echo number_format($order['total_amount'], 2); ?></td>
                          <td> <?php echo number_format($order['total_pv']); ?> PV</td>
                          <td>
                            <div class="mt-2">
                              <a href="order-details.php?order_id=<?php echo $order['id']; ?>"
                                class="btn btn-outline-primary btn-sm">
                                <i class="fas fa-eye mr-1"></i>View Details
                              </a>

                              <?php if($order['order_status'] == 'delivered'): ?>
                              <a href="reorder.php?order=<?php echo $order['order_number']; ?>"
                                class="btn btn-outline-success btn-sm">
                                <i class="fas fa-redo mr-1"></i>Reorder
                              </a>
                              <?php endif; ?>
                            </div>
                          </td>
                        </tr>
                         <?php $sn++; endforeach; ?>
                      </tbody>
                    </table>
                    </div>

                   

                    <!-- Pagination -->

                    <?php else: ?>
                    <!-- Empty State -->
                    <div class="empty-orders">
                      <i class="fas fa-shopping-cart fa-5x mb-3 text-muted"></i>
                      <h3>No Orders Yet</h3>
                      <p>You haven't placed any orders yet. Start shopping to see your
                        orders here!</p>
                      <a href="../products.php" class="btn btn-primary btn-lg">
                        <i class="fas fa-shopping-bag mr-2"></i>Start Shopping
                      </a>
                    </div>
                    <?php endif; ?>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>

        <?php include('nav/footer.php'); ?>
</body>

</html>