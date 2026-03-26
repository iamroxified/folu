<?php ob_start();
session_start();
require('../db/config.php');
require('../db/functions.php');
//if(isset($_SESSION['adid']) && isset($_SESSION['master'])){
// echo $_SESSION['mid'];
if (QueryDB("SELECT COUNT(*) FROM adminuser where username='" . $_SESSION['adid'] . "' and access = '" . $_SESSION['access'] . "'  ")->fetchColumn() < 1) {
  header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
  header("Expires: Sat, 01 Jan 2000 00:00:00 GMT");
  header('location:logout');
  exit;
} else {
  //echo $_SESSION['mid'];
  $uss = extract(get_admin_details($_SESSION['adid']));
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
                    <h4 class="card-title">All SPG Users</h4>
                    <button class="btn btn-primary btn-round ms-auto" data-bs-toggle="modal"
                      data-bs-target="#addRowModal">
                      <i class="fa fa-plus"></i>
                      Add New User
                    </button>
                  </div>
                </div>
                <div class="card-body">
                  <!-- Modal -->
                  <div class="modal fade modal-lg" id="addRowModal" tabindex="-1" role="dialog" aria-hidden="true">
                    <div class="modal-dialog" role="document">
                      <div class="modal-content">
                        <div class="modal-header border-0">
                          <h5 class="modal-title">
                            <span class="fw-mediumbold"> Add</span>
                            <span class="fw-light"> New User </span>
                          </h5>
                          <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                          </button>
                        </div>
                        <div class="modal-body">
                          <form id="registerForm" action="" method="POST" class="billing-form">
                            <?php if(isset($_POST['signup'])){
                                // Process form submission
                                $ref_user = $_POST['ref_user'];
                                $ref_pos = $_POST['ref_pos'];
                                $fname = $_POST['fname'];
                                $mname = $_POST['mname'];
                                $lname = $_POST['lname'];
                                $username = $_POST['username'];
                                $phone = $_POST['phone'];
                                $email = $_POST['email'];
                                $country = $_POST['country'];
                                $stateid = $_POST['stateid'];
                                $lga = $_POST['lga'];
                                $dob = $_POST['dob'];
                                $addr = $_POST['addr'];
                                $yreg = date('Y');
                                $pass = password_hash('user@spg2025', PASSWORD_DEFAULT);

                              // Check if username already exists
                              $username_check = QueryDB("SELECT COUNT(*) FROM users WHERE username = ?", [$username])->fetchColumn();
                              
                              if($username_check < 1){
                                // Username doesn't exist, proceed with insertion
                                $insert_query = "INSERT INTO users (lname, mname, fname, username, pass, phone, email, countryid, stateid, dob, sponsor, position, lga, permanent_address, yreg, Active) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 1)";
                                
                                if(QueryDB($insert_query, [$lname, $mname, $fname, $username, $pass, $phone, $email, $country, $stateid, $dob, $ref_user, $ref_pos, $lga, $addr, $yreg])){
                                  print "<script>swal({ position: 'top-end',type: 'success', title: 'Account Created Successfully', text:'', showConfirmButton: false, timer: 1500}, function(){ window.location = 'all_users.php'}); </script>";
                                } else{
                                  print "<script> swal({ position: 'top-end',type: 'error', title: 'Registration Failed', showConfirmButton: false, timer: 1500}, function(){ window.location = 'all_users.php'}); </script>";
                                }
                              } else {
                                print "<script> swal({ position: 'top-end',type: 'error', title: 'Username Already Exist', showConfirmButton: false, timer: 1500}, function(){ window.location = 'all_users.php'}); </script>";
                              }
                        }

      ?>
                            <h5 class="mb-4  text-center">Add New Users to SPG</h5>
                            <div class="row ">
                              <div class="col-md-6">
                                <div class="form-group">
                                  <label for="ref_user">Sponsor Username <span class="comp">*</span></label>
                                  <input type="text" id="ref_user" class="form-control" name="ref_user" value=""
                                    required placeholder="Enter Sponsor Username" onkeyup="validateSponsor()">
                                  <div> <small id="sponsorFeedback"></small></div>
                                </div>
                              </div>
                              <div class="col-md-6">
                                <div class="form-group">
                                  <label for="country">Positions <span class="comp">*</span></label>
                                  <div class="select-wrap">
                                    <div class="icon"><span class="ion-ios-arrow-down"></span></div>
                                    <select name="ref_pos" id="" class="form-control" required>
                                      <option value="">Select Postion</option>
                                      <option value="1">Left</option>
                                      <option value="2">Right</option>
                                    </select>
                                  </div>
                                </div>
                              </div>
                              <div class="col-md-6">
                                <div class="form-group">
                                  <label for="lastname">First Name <span class="comp">*</span></label>
                                  <input type="text" class="form-control" name="fname" required placeholder="">
                                </div>
                              </div>
                              <div class="col-md-6">
                                <div class="form-group">
                                  <label for="lastname">Middle Name</label>
                                  <input type="text" class="form-control" name="mname" placeholder="">
                                </div>
                              </div>
                              <div class="col-md-6">
                                <div class="form-group">
                                  <label for="lastname">Last Name <span class="comp">*</span></label>
                                  <input type="text" class="form-control" name="lname" required placeholder="">
                                </div>
                              </div>
                              <div class="col-md-6">
                                <div class="form-group">
                                  <label for="lastname">Username <span class="comp">*</span></label>
                                  <input type="text" id="re_user" class="form-control" name="username" required
                                    placeholder="" onkeyup="validateuser()">
                                  <div> <small id="userFeedback"></small></div>
                                </div>
                              </div>
                              <div class="w-100"></div>
                              <div class="col-md-6">
                                <div class="form-group">
                                  <label for="phone">Phone <span class="comp">*</span></label>
                                  <input type="tel" class="form-control" name="phone" required placeholder="">
                                </div>
                              </div>
                              <div class="col-md-6">
                                <div class="form-group">
                                  <label for="emailaddress">Email Address <span class="comp">*</span></label>
                                  <input type="email" class="form-control" name="email" required
                                    placeholder="Enter Email Address">
                                </div>
                              </div>
                              <div class="w-100"></div>
                              <div class="w-100"></div>
                              <div class="col-md-6">
                                <div class="form-group">
                                  <label for="country">Country of Residence <span class="comp">*</span></label>
                                  <div class="select-wrap">
                                    <div class="icon"><span class="ion-ios-arrow-down"></span></div>
                                    <select name="country" id="countrySelect" class="form-control" required>
                                      <option value="">[Choose Country]</option>
                                      <?php foreach(QueryDB("SELECT * FROM countries ") as $ct){extract($ct); ?>
                                      <option value="<?php echo $ct['id']; ?>"><?php echo $ct['name']; ?></option>
                                      <?php } ?>
                                    </select>
                                  </div>
                                </div>
                              </div>
                              <div class="col-md-6">
                                <div class="form-group">
                                  <label for="country">State <span class="comp">*</span></label>
                                  <div class="select-wrap">
                                    <div class="icon"><span class="ion-ios-arrow-down"></span></div>
                                    <select name="stateid" id="stateSelect" class="form-control" required>
                                      <option value="">[Choose State]</option>
                                    </select>
                                  </div>
                                </div>
                              </div>
                              <div class="col-md-6" id="lgaContainer" style="display: none;">
                                <div class="form-group">
                                  <label for="country">Local Government <span class="comp">*</span></label>
                                  <div class="select-wrap">
                                    <div class="icon"><span class="ion-ios-arrow-down"></span></div>
                                    <select name="lga" id="lgaSelect" class="form-control">
                                      <option value="">[Choose LGA]</option>

                                    </select>
                                  </div>
                                </div>
                              </div>
                              <div class="col-md-6">
                                <div class="form-group">
                                  <label for="emailaddress">Date of Birth <span class="comp">*</span></label>
                                  <input type="date" class="form-control" placeholder="" name="dob" required>
                                </div>
                              </div>
                              <div class="w-100"></div>
                              <div class="col-md-12">
                                <div class="form-group">
                                  <label for="streetaddress">Full Address <span class="comp">*</span></label>
                                  <input type="text" class="form-control" name="addr" required
                                    placeholder="House number and street name">
                                </div>
                              </div>
                            </div>

                            <div class="row align-items-end">


                              <div class="col-md-12">
                                <div class="form-group mt-4">
                                  <input type="submit" name="signup" class=" btn btn-info py-3 px-4 btn-"
                                    value="Create Account">
                                  <button type="button" class="btn btn-danger" data-dismiss="modal">
                                    Close
                                  </button>
                                </div>
                              </div>
                            </div>
                          </form><!-- END -->
                        </div>
                      </div>
                    </div>
                  </div>
                  <div class="table-responsive">
                    <table id="add-row" class="display table  table-hover">
                      <thead>
                        <tr>
                          <th>SN</th>
                          <th>Username / Rank</th>
                          <th>Email-Mobile</th>
                          <th>Sponsor</th>
                          <th>Country</th>
                          <th>Joined At</th>
                          <th>Balance</th>
                          <th style="width: 30%">Action</th>
                        </tr>
                      </thead>
                      <tfoot>
                        <tr>
                          <th>SN</th>
                          <th>Username / Rank</th>
                          <th>Email-Mobile</th>
                          <th>Sponsor</th>
                          <th>Country</th>
                          <th>Joined At</th>
                          <th>Balance</th>
                          <th style="width: 10%">Action</th>
                        </tr>
                      </tfoot>
                      <tbody>
                        <?php $sn =1;
                        foreach(QueryDB("SELECT * FROM users") as $row){
                            $username = username($row['username']);
                            $email = $row['email'];
                            // Get wallet balance
                            $wallet_balance = get_user_wallet_balance($row['bmid']);
                            // Get PV score
                            $pv_query = QueryDB("SELECT total_pv FROM user_pv WHERE user_id = ?", [$row['bmid']]);
                            $pv_result = $pv_query->fetch(PDO::FETCH_ASSOC);
                            $pv_score = $pv_result ? $pv_result['total_pv'] : 0;
                            $date = date('M d, Y h:ia', strtotime($row['DateCreated'])); ?>
                        <tr>
                          <td><?php echo $sn; ?></td>
                          <td><?php echo $username; ?><br><a
                              href="user_details.php?user_id=<?php echo $row['bmid']; ?>">@<?php echo $row['username']; ?></a>
                          </td>
                          <td class="text-center"><?php echo $email; ?><br><?php echo $row['phone']; ?></td>
                          <td class="text-center"><a
                              href="user_details.php?user_id=<?php echo $row['bmid']; ?>">@<?php echo $row['username']; ?></a>
                          </td>
                          <td><?php echo get_country_code($row['countryid']); ?></td>
                          <td class="text-center"><?php echo $date;  ?><br><?php echo get_time_ago(strtotime($date)); ?>
                          </td>
                          <td>
                            $<?php echo number_format($wallet_balance, 2); ?> USD<br>
                            <small class="text-muted"><?php echo number_format($pv_score, 0); ?> PV</small>
                          </td>
                          <td>
                            <div class="form-button-action">
                              <button class="btn btn-link btn-primary btn-lg" data-original-title="Edit Task"
                                data-bs-toggle="modal" data-bs-target="#editRowModal<?php echo $row['bmid']; ?>">
                                <i class="fa fa-edit"></i>
                              </button>
                              <button type="button" data-bs-toggle="tooltip" title=""
                                class="btn btn-lg btn-link btn-danger" data-original-title="Remove">
                                <i class="fa fa-times"></i>
                              </button>
                              <a href="user_details.php?user_id=<?php echo $row['bmid']; ?>"
                                class="btn btn-lg btn-link btn-info" title="View Details">
                                <i class="fa fa-eye"></i>
                              </a>
                              <div class="modal fade modal-lg" id="editRowModal<?php echo $row['bmid'];?>" tabindex="-1"
                                role="dialog" aria-hidden="true">
                                <div class="modal-dialog" role="document">
                                  <div class="modal-content">
                                    <div class="modal-header border-0">
                                      <h5 class="modal-title text-center"> Edit <?php echo $row['username']; ?> Details
                                      </h5>
                                      <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                        <span aria-hidden="true">&times;</span>
                                      </button>
                                    </div>
                                    <div class="modal-body">
                                      <form id="registerForm" action="" method="POST" class="billing-form">
                                        <?php if(isset($_POST['signup'])){
                                                  $ref_user = $_POST['ref_user'];
                                                  $ref_pos = $_POST['ref_pos'];
                                                  $fname = $_POST['fname'];
                                                  $mname = $_POST['mname'];
                                                  $lname = $_POST['lname'];
                                                  $username = $_POST['username'];
                                                  $phone = $_POST['phone'];
                                                  $email = $_POST['email'];
                                                  $country = $_POST['country'];
                                                  $stateid = $_POST['stateid'];
                                                  $lga = $_POST['lga'];
                                                  $dob = $_POST['dob'];
                                                  $addr = $_POST['addr'];
                                                  $yreg = date('Y');
                                                  $pass = password_hash('user@spg2025', PASSWORD_DEFAULT);
                                                // Check if username already exists (for edit modal)
                                                $username_check = QueryDB("SELECT COUNT(*) FROM users WHERE username = ?", [$username])->fetchColumn();
                                                
                                                if($username_check < 1){
                                                  // Username doesn't exist, proceed with insertion
                                                  $insert_query = "INSERT INTO users (lname, mname, fname, username, pass, phone, email, countryid, stateid, dob, sponsor, position, lga, permanent_address, yreg) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
                                                  
                                                  if(QueryDB($insert_query, [$lname, $mname, $fname, $username, $pass, $phone, $email, $country, $stateid, $dob, $ref_user, $ref_pos, $lga, $addr, $yreg])){
                                                    print "<script>swal({ position: 'top-end',type: 'success', title: 'Account Created Successfully', text:'', showConfirmButton: false, timer: 1500}, function(){ window.location = 'all_users.php'}); </script>";
                                                  } else{
                                                    print "<script> swal({ position: 'top-end',type: 'error', title: 'Registration Failed', showConfirmButton: false, timer: 1500}, function(){ window.location = 'all_users.php'}); </script>";
                                                  }
                                                } else {
                                                  print "<script> swal({ position: 'top-end',type: 'error', title: 'Username Already Exist', showConfirmButton: false, timer: 1500}, function(){ window.location = 'all_users.php'}); </script>";
                                                }
                                        
                                          }
                                            
                                        ?>
                                        <div class="row ">
                                          <div class="col-md-6">
                                            <div class="form-group">
                                              <label for="ref_user">Sponsor Username <span class="comp">*</span></label>
                                              <input type="text" id="ref_user" class="form-control" name="ref_user"
                                                value="" required placeholder="Enter Sponsor Username"
                                                onkeyup="validateSponsor()">
                                              <div> <small id="sponsorFeedback"></small></div>
                                            </div>

                                          </div>
                                          <div class="col-md-6">
                                            <div class="form-group">
                                              <label for="country">Positions <span class="comp">*</span></label>
                                              <div class="select-wrap">
                                                <div class="icon"><span class="ion-ios-arrow-down"></span></div>
                                                <select name="ref_pos" id="" class="form-control" required>
                                                  <option value="">Select Postion</option>
                                                  <option value="1">Left</option>
                                                  <option value="2">Right</option>
                                                </select>
                                              </div>
                                            </div>
                                          </div>
                                          <div class="col-md-6">
                                            <div class="form-group">
                                              <label for="lastname">First Name <span class="comp">*</span></label>
                                              <input type="text" class="form-control" name="fname" required
                                                placeholder="">
                                            </div>
                                          </div>
                                          <div class="col-md-6">
                                            <div class="form-group">
                                              <label for="lastname">Middle Name</label>
                                              <input type="text" class="form-control" name="mname" placeholder="">
                                            </div>
                                          </div>
                                          <div class="col-md-6">
                                            <div class="form-group">
                                              <label for="lastname">Last Name <span class="comp">*</span></label>
                                              <input type="text" class="form-control" name="lname" required
                                                placeholder="">
                                            </div>
                                          </div>
                                          <div class="col-md-6">
                                            <div class="form-group">
                                              <label for="lastname">Username <span class="comp">*</span></label>
                                              <input type="text" id="re_user" class="form-control" name="username"
                                                required placeholder="" onkeyup="validateuser()">
                                              <div> <small id="userFeedback"></small></div>
                                            </div>
                                          </div>
                                          <div class="w-100"></div>
                                          <div class="col-md-6">
                                            <div class="form-group">
                                              <label for="phone">Phone <span class="comp">*</span></label>
                                              <input type="tel" class="form-control" name="phone" required
                                                placeholder="">
                                            </div>
                                          </div>
                                          <div class="col-md-6">
                                            <div class="form-group">
                                              <label for="emailaddress">Email Address <span
                                                  class="comp">*</span></label>
                                              <input type="email" class="form-control" name="email" required
                                                placeholder="Enter Email Address">
                                            </div>
                                          </div>
                                          <div class="w-100"></div>

                                          <div class="w-100"></div>
                                          <div class="col-md-6">
                                            <div class="form-group">
                                              <label for="country">Country of Residence <span
                                                  class="comp">*</span></label>
                                              <div class="select-wrap">
                                                <div class="icon"><span class="ion-ios-arrow-down"></span></div>
                                                <select name="country" id="countrySelect" class="form-control" required>
                                                  <option value="">[Choose Country]</option>
                                                  <?php foreach(QueryDB("SELECT * FROM countries ") as $ct){extract($ct); ?>
                                                  <option value="<?php echo $ct['id']; ?>"><?php echo $ct['name']; ?>
                                                  </option>
                                                  <?php } ?>

                                                </select>
                                              </div>
                                            </div>
                                          </div>
                                          <div class="col-md-6">
                                            <div class="form-group">
                                              <label for="country">State <span class="comp">*</span></label>
                                              <div class="select-wrap">
                                                <div class="icon"><span class="ion-ios-arrow-down"></span></div>
                                                <select name="stateid" id="stateSelect" class="form-control" required>
                                                  <option value="">[Choose State]</option>

                                                </select>
                                              </div>
                                            </div>
                                          </div>
                                          <div class="col-md-6" id="lgaContainer" style="display: none;">
                                            <div class="form-group">
                                              <label for="country">Local Government <span class="comp">*</span></label>
                                              <div class="select-wrap">
                                                <div class="icon"><span class="ion-ios-arrow-down"></span></div>
                                                <select name="lga" id="lgaSelect" class="form-control">
                                                  <option value="">[Choose LGA]</option>

                                                </select>
                                              </div>
                                            </div>
                                          </div>
                                          <div class="col-md-6">
                                            <div class="form-group">
                                              <label for="emailaddress">Date of Birth <span
                                                  class="comp">*</span></label>
                                              <input type="date" class="form-control" placeholder="" name="dob"
                                                required>
                                            </div>
                                          </div>
                                          <div class="w-100"></div>
                                          <div class="col-md-12">
                                            <div class="form-group">
                                              <label for="streetaddress">Full Address <span
                                                  class="comp">*</span></label>
                                              <input type="text" class="form-control" name="addr" required
                                                placeholder="House number and street name">
                                            </div>
                                          </div>
                                        </div>

                                        <div class="row align-items-end">


                                          <div class="col-md-12">
                                            <div class="form-group mt-4">
                                              <input type="submit" name="signup" class=" btn btn-info py-3 px-4 btn-"
                                                value="Create Account">
                                              <button type="button" class="btn btn-danger" data-dismiss="modal">
                                                Close
                                              </button>
                                            </div>
                                          </div>
                                        </div>
                                      </form><!-- END -->
                                    </div>
                                  </div>
                                </div>
                              </div>
                            </div>
                          </td>
                        </tr>
                        <?php $sn++;  } ?>
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
      <script>
        function checkPassMatch() {
          const pass = document.getElementById("pass").value;
          const cpass = document.getElementById("cpass").value;
          const message = document.getElementById("matchMessage");

          if (cpass === "") {
            message.textContent = "";
          } else if (pass === cpass) {
            message.textContent = "Passwords match ✅";
            message.style.color = "green";
          } else {
            message.textContent = "Passwords do not match ❌";
            message.style.color = "red";
          }
        }
      </script>
      <script>
        let userIsValid = true; // Initialize as true (valid) by default

        function validateuser() {
          const user = document.getElementById("re_user").value.trim();
          const feedback = document.getElementById("userFeedback");

          if (user === "") {
            feedback.textContent = "";
            userIsValid = false;
            return;
          }

          const xhr = new XMLHttpRequest();
          xhr.open("POST", "../check_user", true);
          xhr.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
          xhr.setRequestHeader("X-Requested-With", "XMLHttpRequest");

          xhr.onload = function () {
            if (xhr.status === 200) {
              const res = xhr.responseText.trim();
              if (res === "exists") {
                userIsValid = false; // Username exists, so it's NOT valid
                feedback.textContent = "❌ Username already exists! Select Another";
                feedback.style.color = "red";
              } else {
                userIsValid = true; // Username doesn't exist, so it's valid
                feedback.textContent = "✅ Username Available";
                feedback.style.color = "green";
              }
            }
          };

          xhr.send("user=" + encodeURIComponent(user));
        }

        // ✅ Run on page load
        window.addEventListener("DOMContentLoaded", function () {
          const userInput = document.getElementById("re_user");
          if (userInput && userInput.value.trim() !== "") {
            validateuser();
          }

          // ✅ Intercept form submit
          const form = document.getElementById("registerForm");
          if (form) {
            form.addEventListener("submit", function (e) {
              const currentUsername = document.getElementById("re_user").value.trim();
              if (!userIsValid && currentUsername !== "") {
                e.preventDefault(); // block form submission
                alert("❌ Username already exists or is invalid. Please check and try again.");
              }
            });
          }
        });
      </script>
      <script>
        let sponsorIsValid = false;

        function validateSponsor() {
          const sponsor = document.getElementById("ref_user").value.trim();
          const feedback = document.getElementById("sponsorFeedback");

          if (sponsor === "") {
            feedback.textContent = "";
            sponsorIsValid = false;
            return;
          }

          const xhr = new XMLHttpRequest();
          xhr.open("POST", "../check_sponsor", true);
          xhr.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
          xhr.setRequestHeader("X-Requested-With", "XMLHttpRequest");

          xhr.onload = function () {
            if (xhr.status === 200) {
              const res = xhr.responseText.trim();
              if (res === "exists") {
                sponsorIsValid = true;
                feedback.textContent = "✅ Sponsor exists";
                feedback.style.color = "green";
              } else {
                sponsorIsValid = false;
                feedback.textContent = "❌ Sponsor not found";
                feedback.style.color = "red";
              }
            }
          };

          xhr.send("sponsor=" + encodeURIComponent(sponsor));
        }

        // ✅ Run on page load
        window.addEventListener("DOMContentLoaded", function () {
          const sponsorInput = document.getElementById("ref_user");
          if (sponsorInput && sponsorInput.value.trim() !== "") {
            validateSponsor();
          }

          // ✅ Intercept form submit
          const form = document.getElementById("registerForm");
          if (form) {
            form.addEventListener("submit", function (e) {
              const currentSponsor = document.getElementById("ref_user").value.trim();
              if (!sponsorIsValid && currentSponsor !== "") {
                e.preventDefault(); // block form submission
                alert("❌ Invalid sponsor username. Please check and try again.");
              }
            });
          }
        });
      </script>
      <script>
        document.addEventListener("DOMContentLoaded", function () {
          const countrySelect = document.getElementById("countrySelect");
          const stateSelect = document.getElementById("stateSelect");
          const lgaSelect = document.getElementById("lgaSelect");
          const lgaContainer = document.getElementById("lgaContainer");

          countrySelect.addEventListener("change", function () {
            const countryId = this.value;
            const countryName = this.options[this.selectedIndex].text.trim().toLowerCase();

            fetch(`fetch_states.php?country_id=${countryId}&country_name=${encodeURIComponent(countryName)}`)
              .then(res => res.json())
              .then(states => {
                stateSelect.innerHTML = `<option value="">[Choose State]</option>`;
                states.forEach(state => {
                  stateSelect.innerHTML += `<option value="${state.id}">${state.name}</option>`;
                });

                // Toggle LGA section
                if (countryName === "nigeria") {
                  lgaContainer.style.display = "block";
                } else {
                  lgaContainer.style.display = "none";
                  lgaSelect.innerHTML = `<option value="">[Choose LGA]</option>`;
                }
              });
          });

          stateSelect.addEventListener("change", function () {
            const stateId = this.value;
            const countryName = countrySelect.options[countrySelect.selectedIndex].text.trim().toLowerCase();

            if (countryName === "nigeria") {
              fetch(`fetch_lgas.php?state_id=${stateId}`)
                .then(res => res.json())
                .then(lgas => {
                  lgaSelect.innerHTML = `<option value="">[Choose LGA]</option>`;
                  lgas.forEach(lga => {
                    lgaSelect.innerHTML += `<option value="${lga.id}">${lga.name}</option>`;
                  });
                });
            }
          });
        });
      </script>


</body>

</html>