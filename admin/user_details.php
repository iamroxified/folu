<?php ob_start();
session_start();
require('../db/config.php');
require('../db/functions.php');

//Authentication check
if (QueryDB("SELECT COUNT(*) FROM adminuser where username='" . $_SESSION['adid'] . "' and access = '" . $_SESSION['access'] . "'  ")->fetchColumn() < 1) {
  header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
  header("Expires: Sat, 01 Jan 2000 00:00:00 GMT");
  header('location:logout');
  exit;
} else {
  $uss = extract(get_admin_details($_SESSION['adid']));
}

// Check if user_id is set
if (!isset($_GET['user_id'])) {
    header('location: all_users.php');
    exit;
}

$user_id = $_GET['user_id'];

// Fetch user details
$userDetails = QueryDB("SELECT * FROM users WHERE bmid = ?", [$user_id])->fetch(PDO::FETCH_ASSOC);
if (!$userDetails) {
    header('location: all_users.php');
    exit;
}

// Fetch wallet balance
$wallet_balance = get_user_wallet_balance($user_id);

// Fetch PV score
$pv_query = QueryDB("SELECT total_pv FROM user_pv WHERE user_id = ?", [$user_id]);
$pv_result = $pv_query->fetch(PDO::FETCH_ASSOC);
$pv_score = $pv_result ? $pv_result['total_pv'] : 0;

// Fetch transactions
$transactions = QueryDB("SELECT * FROM wallet_transactions WHERE user_id = ? ORDER BY created_at DESC LIMIT 50", [$user_id])->fetchAll(PDO::FETCH_ASSOC);

// Fetch PV transactions
$pv_transactions = QueryDB("SELECT * FROM pv_transactions WHERE user_id = ? ORDER BY created_at DESC LIMIT 50", [$user_id])->fetchAll(PDO::FETCH_ASSOC);

?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta http-equiv="X-UA-Compatible" content="IE=edge" />
  <title>User Details - <?php echo htmlspecialchars($userDetails['username']); ?></title>
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
                    <h4 class="card-title">User Details: <?php echo htmlspecialchars($userDetails['fname'] . ' ' . $userDetails['lname']); ?></h4>
                    <a href="all_users.php" class="btn btn-secondary btn-round ms-auto">
                      <i class="fa fa-arrow-left"></i>
                      Back to Users
                    </a>
                  </div>
                </div>
                <div class="card-body">
                  
                  <!-- User Information Row -->
                  <div class="row mb-4">
                    <div class="col-md-6">
                      <div class="card">
                        <div class="card-header">
                          <h5 class="card-title">Profile Information</h5>
                        </div>
                        <div class="card-body">
                          <p><strong>Full Name:</strong> <?php echo htmlspecialchars($userDetails['fname'] . ' ' . $userDetails['mname'] . ' ' . $userDetails['lname']); ?></p>
                          <p><strong>Username:</strong> @<?php echo htmlspecialchars($userDetails['username']); ?></p>
                          <p><strong>Email:</strong> <?php echo htmlspecialchars($userDetails['email']); ?></p>
                          <p><strong>Phone:</strong> <?php echo htmlspecialchars($userDetails['phone']); ?></p>
                          <p><strong>Country:</strong> <?php echo get_country_code($userDetails['countryid']); ?></p>
                          <p><strong>Date Joined:</strong> <?php echo date('M d, Y', strtotime($userDetails['DateCreated'])); ?></p>
                          <p><strong>Status:</strong> 
                            <?php if($userDetails['Active'] == 1): ?>
                              <span class="badge badge-success">Active</span>
                            <?php else: ?>
                              <span class="badge badge-danger">Inactive</span>
                            <?php endif; ?>
                          </p>
                        </div>
                      </div>
                    </div>
                    
                    <div class="col-md-6">
                      <div class="row">
                        <!-- Wallet Card -->
                        <div class="col-md-12 mb-3">
                          <div class="card">
                            <div class="card-header">
                              <h5 class="card-title">Wallet Balance</h5>
                            </div>
                            <div class="card-body">
                              <h3 class="text-primary">$<?php echo number_format($wallet_balance, 2); ?> USD</h3>
                              <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#walletModal">
                                <i class="fa fa-plus"></i> Add Balance
                              </button>
                               <button type="button" class="btn btn-warning btn-sm" data-bs-toggle="modal" data-bs-target="#mwalletModal">
                                <i class="fa fa-minus"></i> Deduct Balance
                              </button>
                            </div>
                          </div>
                        </div>
                        
                        <!-- PV Score Card -->
                        <div class="col-md-12">
                          <div class="card">
                            <div class="card-header">
                              <h5 class="card-title">PV Score</h5>
                            </div>
                            <div class="card-body">
                              <h3 class="text-success"><?php echo number_format($pv_score, 0); ?> PV</h3>
                              <button type="button" class="btn btn-success btn-sm" data-bs-toggle="modal" data-bs-target="#pvModal">
                                <i class="fa fa-plus"></i> Add PV
                              </button>
                               <button type="button" class="btn btn-danger btn-sm" data-bs-toggle="modal" data-bs-target="#mpvModal">
                                <i class="fa fa-minus"></i> Deduct PV
                              </button>
                            </div>
                          </div>
                        </div>
                      </div>
                    </div>
                  </div>
                  
                  <!-- Transactions Section -->
                  <div class="row">
                    <div class="col-md-6">
                      <div class="card">
                        <div class="card-header">
                          <h5 class="card-title">Recent Wallet Transactions</h5>
                        </div>
                        <div class="card-body">
                          <div class="table-responsive">
                            <table class="table table-striped">
                              <thead>
                                <tr>
                                  <th>Date</th>
                                  <th>Amount</th>
                                  <th>Type</th>
                                  <th>Description</th>
                                </tr>
                              </thead>
                              <tbody>
                                <?php if(count($transactions) > 0): ?>
                                  <?php foreach ($transactions as $transaction): ?>
                                    <tr>
                                      <td><?php echo date('M d, Y', strtotime($transaction['created_at'])); ?></td>
                                      <td>
                                        <?php if($transaction['transaction_type'] == 'credit'): ?>
                                          <span class="text-success">+$<?php echo number_format($transaction['amount'], 2); ?></span>
                                        <?php else: ?>
                                          <span class="text-danger">-$<?php echo number_format($transaction['amount'], 2); ?></span>
                                        <?php endif; ?>
                                      </td>
                                      <td>
                                        <?php if($transaction['transaction_type'] == 'credit'): ?>
                                          <span class="badge badge-success">Credit</span>
                                        <?php else: ?>
                                          <span class="badge badge-danger">Debit</span>
                                        <?php endif; ?>
                                      </td>
                                      <td><?php echo htmlspecialchars($transaction['description']); ?></td>
                                    </tr>
                                  <?php endforeach; ?>
                                <?php else: ?>
                                  <tr>
                                    <td colspan="4" class="text-center">No wallet transactions found</td>
                                  </tr>
                                <?php endif; ?>
                              </tbody>
                            </table>
                          </div>
                        </div>
                      </div>
                    </div>
                    
                    <div class="col-md-6">
                      <div class="card">
                        <div class="card-header">
                          <h5 class="card-title">Recent PV Transactions</h5>
                        </div>
                        <div class="card-body">
                          <div class="table-responsive">
                            <table class="table table-striped">
                              <thead>
                                <tr>
                                  <th>Date</th>
                                  <th>Points</th>
                                  <th>Type</th>
                                  <th>Description</th>
                                </tr>
                              </thead>
                              <tbody>
                                <?php if(count($pv_transactions) > 0): ?>
                                  <?php foreach ($pv_transactions as $pv_transaction): ?>
                                    <tr>
                                      <td><?php echo date('M d, Y', strtotime($pv_transaction['created_at'])); ?></td>
                                      <td>
                                        <?php if($pv_transaction['transaction_type'] == 'credit'): ?>
                                          <span class="text-success">+<?php echo number_format($pv_transaction['pv_amount'], 0); ?></span>
                                        <?php else: ?>
                                          <span class="text-danger">-<?php echo number_format($pv_transaction['pv_amount'], 0); ?></span>
                                        <?php endif; ?>
                                      </td>
                                      <td>
                                        <?php if($pv_transaction['transaction_type'] == 'credit'): ?>
                                          <span class="badge badge-success">Credit</span>
                                        <?php else: ?>
                                          <span class="badge badge-danger">Debit</span>
                                        <?php endif; ?>
                                      </td>
                                      <td><?php echo htmlspecialchars($pv_transaction['description']); ?></td>
                                    </tr>
                                  <?php endforeach; ?>
                                <?php else: ?>
                                  <tr>
                                    <td colspan="4" class="text-center">No PV transactions found</td>
                                  </tr>
                                <?php endif; ?>
                              </tbody>
                            </table>
                          </div>
                        </div>
                      </div>
                    </div>
                  </div>
                  
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
      
      <!-- Wallet Modal -->
      <div class="modal fade" id="walletModal" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog" role="document">
          <div class="modal-content">
            <div class="modal-header">
              <h5 class="modal-title">Add Wallet Balance - <?php echo htmlspecialchars($userDetails['username']); ?></h5>
              <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                <span aria-hidden="true">&times;</span>
              </button>
            </div>
            <div class="modal-body">
              <form id="walletForm" method="POST">
                <input type="hidden" name="user_id" value="<?php echo $user_id; ?>">
                <div class="form-group">
                  <label for="amount">Amount ($)</label>
                  <input type="number" name="amount" class="form-control" placeholder="Enter amount" required min="0.01" step="0.01">
                </div>
                <div class="form-group">
                  <label for="description">Description</label>
                  <input type="text" name="description" class="form-control" placeholder="Admin wallet credit" value="Admin wallet credit">
                </div>
                <div class="form-group">
                  <button type="submit" class="btn btn-primary">Add Balance</button>
                  <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                </div>
              </form>
            </div>
          </div>
        </div>
      </div>
      
            <!-- Wallet Modal -->
      <div class="modal fade" id="mwalletModal" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog" role="document">
          <div class="modal-content">
            <div class="modal-header">
              <h5 class="modal-title">Deduct Wallet Balance - <?php echo htmlspecialchars($userDetails['username']); ?></h5>
              <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                <span aria-hidden="true">&times;</span>
              </button>
            </div>
            <div class="modal-body">
              <form id="mwalletForm" method="POST">
                <input type="hidden" name="user_id" value="<?php echo $user_id; ?>">
                <div class="form-group">
                  <label for="amount">Amount ($)</label>
                  <input type="number" name="amount" class="form-control" placeholder="Enter amount" required min="0.01" step="0.01">
                </div>
                <div class="form-group">
                  <label for="description">Description</label>
                  <input type="text" name="description" class="form-control" placeholder="Admin wallet debit" value="Admin wallet debit">
                </div>
                <div class="form-group">
                  <button type="submit" class="btn btn-primary">Deduct Balance</button>
                  <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                </div>
              </form>
            </div>
          </div>
        </div>
      </div>
      
      <!-- PV Modal -->
      <div class="modal fade" id="pvModal" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog" role="document">
          <div class="modal-content">
            <div class="modal-header">
              <h5 class="modal-title">Add PV Score - <?php echo htmlspecialchars($userDetails['username']); ?></h5>
              <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                <span aria-hidden="true">&times;</span>
              </button>
            </div>
            <div class="modal-body">
              <form id="pvForm" method="POST">
                <input type="hidden" name="user_id" value="<?php echo $user_id; ?>">
                <div class="form-group">
                  <label for="pv_points">PV Points</label>
                  <input type="number" name="pv_points" class="form-control" placeholder="Enter PV points" required min="1" step="1">
                </div>
                <div class="form-group">
                  <label for="description">Description</label>
                  <input type="text" name="description" class="form-control" placeholder="Admin PV credit" value="Admin PV credit">
                </div>
                <div class="form-group">
                  <button type="submit" class="btn btn-success">Add PV Score</button>
                  <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                </div>
              </form>
            </div>
          </div>
        </div>
      </div>

            
      <!-- PV Modal -->
      <div class="modal fade" id="mpvModal" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog" role="document">
          <div class="modal-content">
            <div class="modal-header">
              <h5 class="modal-title">Deduct PV Score - <?php echo htmlspecialchars($userDetails['username']); ?></h5>
              <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                <span aria-hidden="true">&times;</span>
              </button>
            </div>
            <div class="modal-body">
              <form id="mpvForm" method="POST">
                <input type="hidden" name="user_id" value="<?php echo $user_id; ?>">
                <div class="form-group">
                  <label for="pv_points">PV Points</label>
                  <input type="number" name="pv_points" class="form-control" placeholder="Enter PV points" required min="1" step="1">
                </div>
                <div class="form-group">
                  <label for="description">Description</label>
                  <input type="text" name="description" class="form-control" placeholder="Admin PV debit" value="Admin PV debit">
                </div>
                <div class="form-group">
                  <button type="submit" class="btn btn-success">Deduct PV Score</button>
                  <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                </div>
              </form>
            </div>
          </div>
        </div>
      </div>
      
      <?php include('nav/footer.php'); ?>
      
      <script>
        // Handle Wallet and PV Form Submissions
        document.addEventListener('DOMContentLoaded', function() {
          // Handle wallet form
          const walletForm = document.getElementById('walletForm');
          if (walletForm) {
            walletForm.addEventListener('submit', function(e) {
              e.preventDefault();
              
              const formData = new FormData(walletForm);
              const button = walletForm.querySelector('button[type="submit"]');
              const originalText = button.innerHTML;
              
              button.innerHTML = 'Adding...';
              button.disabled = true;
              
              fetch('add_wallet_balance', {
                method: 'POST',
                body: formData
              })
              .then(response => {
                console.log('Wallet Response status:', response.status);
                
                if (!response.ok) {
                  throw new Error(`HTTP error! status: ${response.status}`);
                }
                
                return response.text();
              })
              .then(text => {
                console.log('Wallet Raw response:', text);
                
                try {
                  const data = JSON.parse(text);
                  console.log('Wallet Parsed data:', data);
                  
                  // Close the modal first
                  $('#walletModal').modal('hide');
                  
                  if (data.success) {
                    swal({
                      position: 'top-end',
                      type: 'success',
                      title: data.message,
                      showConfirmButton: false,
                      timer: 2000
                    }).then(function() {
                      location.reload();
                    });
                  } else {
                    swal({
                      position: 'top-end',
                      type: 'error',
                      title: data.message || 'Unknown error occurred',
                      showConfirmButton: false,
                      timer: 2000
                    });
                  }
                } catch (parseError) {
                  console.error('Wallet JSON parse error:', parseError);
                  console.error('Wallet Response text:', text);
                  swal({
                    position: 'top-end',
                    type: 'error',
                    title: 'Invalid response format',
                    showConfirmButton: false,
                    timer: 2000
                  });
                }
              })
              .catch(error => {
                console.error('Wallet Fetch error:', error);
                swal({
                  position: 'top-end',
                  type: 'error',
                  title: 'Network error occurred',
                  showConfirmButton: false,
                  timer: 2000
                });
              })
              .finally(() => {
                button.innerHTML = originalText;
                button.disabled = false;
              });
            });
          }
          
               const mwalletForm = document.getElementById('mwalletForm');
          if (mwalletForm) {
            mwalletForm.addEventListener('submit', function(e) {
              e.preventDefault();
              
              const formData = new FormData(mwalletForm);
              const button = mwalletForm.querySelector('button[type="submit"]');
              const originalText = button.innerHTML;
              
              button.innerHTML = 'Deducting...';
              button.disabled = true;
              
              fetch('deduct_wallet_balance', {
                method: 'POST',
                body: formData
              })
              .then(response => {
                console.log('Wallet Response status:', response.status);
                
                if (!response.ok) {
                  throw new Error(`HTTP error! status: ${response.status}`);
                }
                
                return response.text();
              })
              .then(text => {
                console.log('Wallet Raw response:', text);
                
                try {
                  const data = JSON.parse(text);
                  console.log('Wallet Parsed data:', data);
                  
                  // Close the modal first
                  $('#mwalletModal').modal('hide');
                  
                  if (data.success) {
                    swal({
                      position: 'top-end',
                      type: 'success',
                      title: data.message,
                      showConfirmButton: false,
                      timer: 2000
                    }).then(function() {
                      location.reload();
                    });
                  } else {
                    swal({
                      position: 'top-end',
                      type: 'error',
                      title: data.message || 'Unknown error occurred',
                      showConfirmButton: false,
                      timer: 2000
                    });
                  }
                } catch (parseError) {
                  console.error('Wallet JSON parse error:', parseError);
                  console.error('Wallet Response text:', text);
                  swal({
                    position: 'top-end',
                    type: 'error',
                    title: 'Invalid response format',
                    showConfirmButton: false,
                    timer: 2000
                  });
                }
              })
              .catch(error => {
                console.error('Wallet Fetch error:', error);
                swal({
                  position: 'top-end',
                  type: 'error',
                  title: 'Network error occurred',
                  showConfirmButton: false,
                  timer: 2000
                });
              })
              .finally(() => {
                button.innerHTML = originalText;
                button.disabled = false;
              });
            });
          }
          
          // Handle PV form
          const pvForm = document.getElementById('pvForm');
          if (pvForm) {
            pvForm.addEventListener('submit', function(e) {
              e.preventDefault();
              
              const formData = new FormData(pvForm);
              const button = pvForm.querySelector('button[type="submit"]');
              const originalText = button.innerHTML;
              
              button.innerHTML = 'Adding...';
              button.disabled = true;
              
              fetch('add_pv_score', {
                method: 'POST',
                body: formData
              })
              .then(response => {
                console.log('PV Response status:', response.status);
                
                if (!response.ok) {
                  throw new Error(`HTTP error! status: ${response.status}`);
                }
                
                return response.text();
              })
              .then(text => {
                console.log('PV Raw response:', text);
                
                try {
                  const data = JSON.parse(text);
                  console.log('PV Parsed data:', data);
                  
                  // Close the modal first
                  $('#pvModal').modal('hide');
                  
                  if (data.success) {
                    swal({
                      position: 'top-end',
                      type: 'success',
                      title: data.message,
                      showConfirmButton: false,
                      timer: 2000
                    }).then(function() {
                      location.reload();
                    });
                  } else {
                    swal({
                      position: 'top-end',
                      type: 'error',
                      title: data.message || 'Unknown error occurred',
                      showConfirmButton: false,
                      timer: 2000
                    });
                  }
                } catch (parseError) {
                  console.error('PV JSON parse error:', parseError);
                  console.error('PV Response text:', text);
                  swal({
                    position: 'top-end',
                    type: 'error',
                    title: 'Invalid response format',
                    showConfirmButton: false,
                    timer: 2000
                  });
                }
              })
              .catch(error => {
                console.error('PV Fetch error:', error);
                swal({
                  position: 'top-end',
                  type: 'error',
                  title: 'Network error occurred',
                  showConfirmButton: false,
                  timer: 2000
                });
              })
              .finally(() => {
                button.innerHTML = originalText;
                button.disabled = false;
              });
            });
          }
       
                 // Handle PV form
          const mpvForm = document.getElementById('mpvForm');
          if (mpvForm) {
            mpvForm.addEventListener('submit', function(e) {
              e.preventDefault();
              
              const formData = new FormData(mpvForm);
              const button = mpvForm.querySelector('button[type="submit"]');
              const originalText = button.innerHTML;
              
              button.innerHTML = 'Adding...';
              button.disabled = true;
              
              fetch('deduct_pv_score', {
                method: 'POST',
                body: formData
              })
              .then(response => {
                console.log('PV Response status:', response.status);
                
                if (!response.ok) {
                  throw new Error(`HTTP error! status: ${response.status}`);
                }
                
                return response.text();
              })
              .then(text => {
                console.log('PV Raw response:', text);
                
                try {
                  const data = JSON.parse(text);
                  console.log('PV Parsed data:', data);
                  
                  // Close the modal first
                  $('#mpvModal').modal('hide');
                  
                  if (data.success) {
                    swal({
                      position: 'top-end',
                      type: 'success',
                      title: data.message,
                      showConfirmButton: false,
                      timer: 2000
                    }).then(function() {
                      location.reload();
                    });
                  } else {
                    swal({
                      position: 'top-end',
                      type: 'error',
                      title: data.message || 'Unknown error occurred',
                      showConfirmButton: false,
                      timer: 2000
                    });
                  }
                } catch (parseError) {
                  console.error('PV JSON parse error:', parseError);
                  console.error('PV Response text:', text);
                  swal({
                    position: 'top-end',
                    type: 'error',
                    title: 'Invalid response format',
                    showConfirmButton: false,
                    timer: 2000
                  });
                }
              })
              .catch(error => {
                console.error('PV Fetch error:', error);
                swal({
                  position: 'top-end',
                  type: 'error',
                  title: 'Network error occurred',
                  showConfirmButton: false,
                  timer: 2000
                });
              })
              .finally(() => {
                button.innerHTML = originalText;
                button.disabled = false;
              });
            });
          }
       
        });
      </script>
      
    </div>
  </div>
</body>
</html>

