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
  
$order_id = isset($_GET['order_id']) ? intval($_GET['order_id']) : 0;

// Handle approve/reject actions
$message = '';
$messageType = '';

if (isset($_GET['action']) && isset($_GET['order_id'])) {
    $action = $_GET['action'];
    $order_id = intval($_GET['order_id']);
    
    if ($action === 'approve') {
        try {
            $stmt = QueryDB("UPDATE orders SET order_status = 'approved', updated_at = CURRENT_TIMESTAMP WHERE id = ?", [$order_id]);
            if ($stmt) {
                $message = 'Order has been successfully approved!';
                $messageType = 'success';
            }
        } catch (Exception $e) {
            error_log("Order approval error: " . $e->getMessage());
            $message = 'Failed to approve order. Please try again.';
            $messageType = 'danger';
        }
    } elseif ($action === 'reject') {
        try {
            $stmt = QueryDB("UPDATE orders SET order_status = 'rejected', updated_at = CURRENT_TIMESTAMP WHERE id = ?", [$order_id]);
            if ($stmt) {
                $message = 'Order has been rejected.';
                $messageType = 'warning';
            }
        } catch (Exception $e) {
            error_log("Order rejection error: " . $e->getMessage());
            $message = 'Failed to reject order. Please try again.';
            $messageType = 'danger';
        }
    }
}

try {
    $stmt = QueryDB("SELECT * FROM orders WHERE id = ?", [$order_id]);
    $order = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$order) {
        throw new Exception('Order not found.');
    }
    $items_stmt = QueryDB("SELECT * FROM order_items WHERE order_id = ?", [$order['id']]);
    $order_items = $items_stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    error_log("Order retrieval error: " . $e->getMessage());
    header('Location: all-orders.php');
    exit;
}
}

?>
<!DOCTYPE html>
<html lang="en">

<head>
  <title>Order Details - Smart People Global</title>
  <?php include('nav/links.php'); ?>
  <style>
    .order-details-container {
      padding: 40px;
      background: #f8f9fa;
      border-radius: 10px;
      box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
    }

    .order-item {
      border-bottom: 1px solid #eee;
      padding: 15px 0;
      display: flex;
      justify-content: space-between;
      align-items: center;
    }

    .order-item:last-child {
      border-bottom: none;
    }

    .order-summary {
      background: #ffffff;
      padding: 30px;
      border-radius: 10px;
      box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
      margin-top: 20px;
    }

    .order-details h2 {
      font-size: 22px;
      margin-bottom: 20px;
      color: #333;
    }

    .total-section {
      background: #f8f9fa;
      padding: 20px;
      border-radius: 8px;
      margin-top: 20px;
    }

    .total-row {
      display: flex;
      justify-content: space-between;
      margin-bottom: 10px;
      padding: 5px 0;
    }

    .total-row.final {
      border-top: 2px solid #82ae46;
      padding-top: 15px;
      margin-top: 15px;
      font-weight: bold;
      font-size: 18px;
    }

    .pv-highlight {
      color: #82ae46;
      font-weight: bold;
    }

    .order-status {
      padding: 5px 12px;
      border-radius: 20px;
      font-size: 12px;
      font-weight: bold;
      text-transform: uppercase;
    }

    .status-pending {
      background: #fff3cd;
      color: #856404;
    }

    .status-processing {
      background: #d4edda;
      color: #155724;
    }

    .status-shipped {
      background: #d1ecf1;
      color: #0c5460;
    }

    .status-delivered {
      background: #d4edda;
      color: #155724;
    }

    .status-cancelled {
      background: #f8d7da;
      color: #721c24;
    }

    .status-approved {
      background: #d4edda;
      color: #155724;
    }

    .status-rejected {
      background: #f8d7da;
      color: #721c24;
    }

    .payment-evidence {
      background: #e9ecef;
      padding: 20px;
      border-radius: 8px;
      margin-top: 20px;
    }

    .item-details {
      flex: 1;
    }

    .item-pricing {
      text-align: right;
      min-width: 150px;
    }

    .order-info-grid {
      display: grid;
      grid-template-columns: 1fr 1fr;
      gap: 20px;
      margin-bottom: 30px;
    }
  </style>
</head>

<body>
  <div class="wrapper">
    <!-- Sidebar -->
    <?php include('nav/sidebar.php'); ?>
    <!-- End Sidebar -->
    <div class="main-panel">
      <?php include('nav/header.php'); ?>
      <div class='container'>
        <div class='page-inner'>
          <div class="d-flex align-items-left align-items-md-center flex-column flex-md-row pt-2 pb-4">
            <div>
              <h3 class="fw-bold mb-3">Order Details</h3>
              <!-- <h6 class="op-7 mb-2">Free Bootstrap 5 Admin Dashboard</h6> -->
            </div>
            <div class="ms-md-auto py-2 py-md-0">
              <?php  if ($order['order_status'] === 'processing'): ?>
              <a href="?order_id=<?php echo $order['id']; ?>&action=approve"
                class="btn btn-label-success btn-round me-2"
                onclick="return confirm('Are you sure you want to approve this order?')">Approve</a>
              <a href="?order_id=<?php echo $order['id']; ?>&action=reject" class="btn btn-label-danger btn-round me-2"
                onclick="return confirm('Are you sure you want to reject this order?')">Reject</a>
              <?php elseif ($order['order_status'] === 'approved'): ?>
              <span class=" badge badge-success">Order Approved</span>
              <a href="?order_id=<?php echo $order['id']; ?>&action=reject" class="btn btn-label-danger btn-round me-2"
                onclick="return confirm('Are you sure you want to reject this order?')">Reject</a>
              <?php elseif ($order['order_status'] === 'rejected'): ?>
              <span class="badge badge-danger">Order Rejected</span>
              <a href="?order_id=<?php echo $order['id']; ?>&action=approve"
                class="btn btn-label-success btn-round me-2"
                onclick="return confirm('Are you sure you want to approve this order?')">Approve</a>
              <?php endif; ?>
              <a href="all-orders" class="btn btn-primary btn-round">Back to Orders</a>
            </div>
          </div>
          <div class='row'>
            <div class='col-md-12'>
              <div class='card'>
                <div class="card-body">
                  <?php if ($message): ?>
                  <div class="alert alert-<?php echo $messageType; ?> alert-dismissible fade show" role="alert">
                    <?php echo htmlspecialchars($message); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                  </div>
                  <?php endif; ?>
                  <div class="order-details-container">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                      <h2>Order #<?php echo htmlspecialchars($order['order_number']); ?></h2>
                      <span class="order-status status-<?php echo $order['order_status']; ?>">
                        <?php echo ($order['order_status']); ?>
                      </span>
                    </div>
                    <div class="order-info-grid">
                      <div>
                        <h5>Order Information</h5>
                        <p><strong>Order Date:</strong>
                          <?php echo date('F j, Y g:i A', strtotime($order['created_at'])); ?>
                        </p>
                        <p><strong>Payee Name:</strong> <?php echo htmlspecialchars($order['full_name']); ?></p>
                        <p><strong>Payment Method:</strong>
                          <?php echo ucfirst(str_replace('_', ' ', $order['payment_method'])); ?></p>
                        <p><strong>Payment Status:</strong> <span
                            class="badge badge-secondary"><?php echo ucfirst($order['payment_status']); ?></span></p>
                      </div>
                      <div>
                        <h5>Order Summary</h5>
                        <p><strong>Total Items:</strong> <?php echo count($order_items); ?></p>
                        <p><strong>Total Amount:</strong> N<?php echo number_format($order['total_amount'] * 500, 2); ?>
                        </p>
                        <p><strong>Total PV:</strong> <span
                            class="pv-highlight"><?php echo number_format($order['total_pv']); ?> PV</span></p>
                        <p><strong>Last Updated:</strong>
                          <?php echo date('F j, Y g:i A', strtotime($order['updated_at'])); ?>
                        </p>
                      </div>
                    </div>
                    <div class="order-items mt-4">
                      <h5>Ordered Items</h5>
                      <?php foreach($order_items as $item): ?>
                      <div class="order-item">
                        <div class="item-details">
                          <h6><?php echo htmlspecialchars($item['product_name']); ?></h6>
                          <p class="text-muted mb-1">Quantity: <?php echo $item['quantity']; ?> ×
                            $<?php echo number_format($item['price'], 2); ?></p>
                          <p class="text-muted mb-0">PV: <?php echo number_format($item['pv']); ?> ×
                            <?php echo $item['quantity']; ?> = <span
                              class="pv-highlight"><?php echo number_format($item['total_pv']); ?> PV</span></p>
                        </div>
                        <div class="item-pricing">
                          <h6>$<?php echo number_format($item['total_price'], 2); ?> $TC</h6>
                          <p class="pv-highlight mb-0"><?php echo number_format($item['total_pv']); ?> PV</p>
                        </div>
                      </div>
                      <?php endforeach; ?>
                    </div>
                    <div class="order-summary">
                      <h5>Order Totals</h5>
                      <div class="total-section">
                        <div class="total-row">
                          <span>Subtotal:</span>
                          <span>$<?php echo number_format($order['subtotal'], 2); ?> $TC</span>
                        </div>
                        <div class="total-row">
                          <span>Total PV:</span>
                          <span class="pv-highlight"><?php echo number_format($order['total_pv']); ?> PV</span>
                        </div>
                        <div class="total-row">
                          <span>Shipping:</span>
                          <span><?php echo $order['shipping_cost'] > 0 ? '$' . number_format($order['shipping_cost'], 2) : 'Free'; ?></span>
                        </div>
                        <?php if ($order['discount'] > 0): ?>
                        <div class="total-row">
                          <span>Discount:</span>
                          <span class="text-success">-$<?php echo number_format($order['discount'], 2); ?></span>
                        </div>
                        <?php endif; ?>
                        <div class="total-row">
                          <strong><span>Conversion:</span></strong>
                          <strong><span>1 $TC = 500.00 NGN</span></strong>
                        </div>
                        <div class="total-row final">
                          <span>Total Amount:</span>
                          <span>N<?php echo number_format($order['total_amount'] * 500, 2); ?></span>
                        </div>
                      </div>
                      <div class="mt-4 text-center">
                        <a href="all-orders.php" class="btn btn-secondary mr-2">
                          <i class="fas fa-arrow-left mr-2"></i>Back to Orders
                        </a>
                        <?php if ($order['order_status'] == 'delivered'): ?>
                        <a href="reorder.php?order_id=<?php echo $order['id']; ?>" class="btn btn-success">
                          <i class="fas fa-redo mr-2"></i>Reorder
                        </a>
                        <?php endif; ?>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
      <?php include('nav/footer.php'); ?>
</body>

</html>