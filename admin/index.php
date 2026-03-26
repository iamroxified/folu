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

// Get dashboard statistics
$stats = get_school_stats();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta http-equiv="X-UA-Compatible" content="IE=edge" />
  <title>FIMOCOL School Management - Dashboard</title>
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
          <div class="d-flex align-items-left align-items-md-center flex-column flex-md-row pt-2 pb-4">
            <div>
              <h3 class="fw-bold mb-3">FIMOCOL School Dashboard</h3>
              <h6 class="op-7 mb-2"><?php echo _greetin(); ?>! Welcome back to your
                FIMOCOL School Management Dashboard</h6>
            </div>
          </div>
          <div class="row">
            <div class="col-sm-6 col-md-3">
              <div class="card card-stats card-round">
                <div class="card-body">
                       <a href="all_users">
                  <div class="row align-items-center">
                    <div class="col-icon">
                      <div class="icon-big text-center icon-primary bubble-shadow-small">
                        <i class="fas fa-users"></i>
                      </div>
                    </div>
                    <div class="col col-stats ms-3 ms-sm-0">
                      <div class="numbers">
                        <p class="card-category">Total Students</p>
                        <h4 class="card-title"><?php echo all_students(); ?></h4>
                      </div>
                    </div>
                  </div>
                  </a>
                </div>
              </div>
            </div>
            <div class="col-sm-6 col-md-3">
              <div class="card card-stats card-round">
                <div class="card-body">
                  <a href="all_users">
                  <div class="row align-items-center">
                    <div class="col-icon">
                      <div class="icon-big text-center icon-info bubble-shadow-small">
                        <i class="fas fa-user-check"></i>
                      </div>
                    </div>
                    <div class="col col-stats ms-3 ms-sm-0">
                      <div class="numbers">
                        <p class="card-category">Total Staff</p>
                        <h4 class="card-title"><?php echo all_staff(); ?></h4>
                      </div>
                    </div>
                  </div>
                  </a>
                </div>
              </div>
            </div>
            <div class="col-sm-6 col-md-3">
              <div class="card card-stats card-round">
                <div class="card-body">
                  <div class="row align-items-center">
                    <div class="col-icon">
                      <div class="icon-big text-center icon-success bubble-shadow-small">
                        <i class="fas fa-envelope"></i>
                      </div>
                    </div>
                    <div class="col col-stats ms-3 ms-sm-0">
                      <div class="numbers">
                        <p class="card-category">Verified Amins</p>
                        <h4 class="card-title"><?php echo all_users(); ?></h4>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
            <div class="col-sm-6 col-md-3">
              <div class="card card-stats card-round">
                <div class="card-body">
                  <div class="row align-items-center">
                    <div class="col-icon">
                      <div class="icon-big text-center icon-secondary bubble-shadow-small">
                        <i class="fas fa-money-bill"></i>
                      </div>
                    </div>
                    <div class="col col-stats ms-3 ms-sm-0">
                      <div class="numbers">
                        <p class="card-category">Mobile Verified Users</p>
                        <h4 class="card-title">0</h4>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
          <div class="row">
            <div class="col-md-6">
              <div class="card card-round">
                <div class="card-header">
                  <div class="card-head-row">
                    <div class="card-title">Deposits</div>

                  </div>
                </div>
                <div class="card-body">
                  <div class="row">
                    <div class="col-md-6">
                      <a href="#" class="hover">
                        <div class=" card-stats card-round">
                          <div class="card-body">
                            <div class="row">
                              <div class="col-3">
                                <div class="icon-big text-center">
                                  <i class="icon-pie-chart text-warning"></i>
                                </div>
                              </div>
                              <div class="col-9 col-stats">
                                <div class="numbers">
                                  <h4 class="card-title">$31,233.00 $TC</h4>
                                  <p class="card-category">Total Deposits</p>
                                </div>
                                <div class=" text-center">
                                  <i class="fa fa-arrow-right"></i>
                                </div>
                              </div>
                            </div>
                          </div>
                        </div>
                      </a>
                    </div>
                    <div class="col-md-6">
                      <a href="#" class="hover">
                        <div class=" card-stats card-round">
                          <div class="card-body">
                            <div class="row">
                              <div class="col-3">
                                <div class="icon-big text-center">
                                  <i class="icon-pie-chart text-warning"></i>
                                </div>
                              </div>
                              <div class="col-9 col-stats">
                                <div class="numbers">
                                  <h4 class="card-title">0</h4>
                                  <p class="card-category">Pending Deposits</p>
                                </div>
                                <div class=" text-center" style="float: right;">
                                  <i class="fa fa-arrow-right"></i>
                                </div>
                              </div>
                            </div>
                          </div>
                        </div>
                      </a>
                    </div>
                    <div class="col-md-6">
                      <a href="#" class="hover">
                        <div class=" card-stats card-round">
                          <div class="card-body">
                            <div class="row">
                              <div class="col-3">
                                <div class="icon-big text-center">
                                  <i class="fas fa-cancel text-warning"></i>
                                </div>
                              </div>
                              <div class="col-9 col-stats">
                                <div class="numbers">
                                  <h4 class="card-title">0</h4>
                                  <p class="card-category">Rejected Deposits</p>
                                </div>
                                <div class=" text-center" style="float: right;">
                                  <i class="fa fa-arrow-right"></i>
                                </div>
                              </div>
                            </div>
                          </div>
                        </div>
                      </a>
                    </div>
                    <div class="col-md-6">
                      <a href="#" class="hover">
                        <div class=" card-stats card-round">
                          <div class="card-body">
                            <div class="row">
                              <div class="col-3">
                                <div class="icon-big text-center">
                                  <i class="fas fa-cancel text-warning"></i>
                                </div>
                              </div>
                              <div class="col-9 col-stats">
                                <div class="numbers">
                                  <h4 class="card-title">$0.00 $TC</h4>
                                  <p class="card-category">Deposits Charges</p>
                                </div>
                                <div class=" text-center" style="float: right;">
                                  <i class="fa fa-arrow-right"></i>
                                </div>
                              </div>
                            </div>
                          </div>
                        </div>
                      </a>
                    </div>
                  </div>
                </div>
              </div>
            </div>

            <div class="col-md-6">
              <div class="card card-round">
                <div class="card-header">
                  <div class="card-head-row">
                    <div class="card-title">Withdrawals</div>

                  </div>
                </div>
                <div class="card-body">
                  <div class="row">
                    <div class="col-md-6">
                      <a href="#" class="hover">
                        <div class=" card-stats card-round">
                          <div class="card-body">
                            <div class="row">
                              <div class="col-3">
                                <div class="icon-big text-center">
                                  <i class="icon-pie-chart text-warning"></i>
                                </div>
                              </div>
                              <div class="col-9 col-stats">
                                <div class="numbers">
                                  <h4 class="card-title">$0.00 $TC</h4>
                                  <p class="card-category">Total Withdrawals</p>
                                </div>
                                <div class=" text-center">
                                  <i class="fa fa-arrow-right"></i>
                                </div>
                              </div>
                            </div>
                          </div>
                        </div>
                      </a>
                    </div>
                    <div class="col-md-6">
                      <a href="#" class="hover">
                        <div class=" card-stats card-round">
                          <div class="card-body">
                            <div class="row">
                              <div class="col-3">
                                <div class="icon-big text-center">
                                  <i class="icon-pie-chart text-warning"></i>
                                </div>
                              </div>
                              <div class="col-9 col-stats">
                                <div class="numbers">
                                  <h4 class="card-title">0</h4>
                                  <p class="card-category">Pending Withdrawals</p>
                                </div>
                                <div class=" text-center" style="float: right;">
                                  <i class="fa fa-arrow-right"></i>
                                </div>
                              </div>
                            </div>
                          </div>
                        </div>
                      </a>
                    </div>
                    <div class="col-md-6">
                      <a href="#" class="hover">
                        <div class=" card-stats card-round">
                          <div class="card-body">
                            <div class="row">
                              <div class="col-3">
                                <div class="icon-big text-center">
                                  <i class="fas fa-cancel text-warning"></i>
                                </div>
                              </div>
                              <div class="col-9 col-stats">
                                <div class="numbers">
                                  <h4 class="card-title">0</h4>
                                  <p class="card-category">Rejected Withdrawal</p>
                                </div>
                                <div class=" text-center" style="float: right;">
                                  <i class="fa fa-arrow-right"></i>
                                </div>
                              </div>
                            </div>
                          </div>
                        </div>
                      </a>
                    </div>
                    <div class="col-md-6">
                      <a href="#" class="hover">
                        <div class=" card-stats card-round">
                          <div class="card-body">
                            <div class="row">
                              <div class="col-3">
                                <div class="icon-big text-center">
                                  <i class="fas fa-cancel text-warning"></i>
                                </div>
                              </div>
                              <div class="col-9 col-stats">
                                <div class="numbers">
                                  <h4 class="card-title">$0.00 $TC</h4>
                                  <p class="card-category">Withdrawal Charges</p>
                                </div>
                                <div class=" text-center" style="float: right;">
                                  <i class="fa fa-arrow-right"></i>
                                </div>
                              </div>
                            </div>
                          </div>
                        </div>
                      </a>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
          <div class="row">
            <div class="col-sm-6 col-md-3">
              <div class="card card-stats card-round">
                <div class="card-body">
                  <div class="row align-items-center">
                    <div class="col-icon">
                      <div class="icon-big text-center icon-primary bubble-shadow-small">
                        <i class="fas fa-money-bill"></i>
                      </div>
                    </div>
                    <div class="col col-stats ms-3 ms-sm-0">
                      <div class="numbers">
                        <p class="card-category">Total Investments</p>
                        <h4 class="card-title">$0.00 $TC</h4>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
            <div class="col-sm-6 col-md-3">
              <div class="card card-stats card-round">
                <div class="card-body">
                  <div class="row align-items-center">
                    <div class="col-icon">
                      <div class="icon-big text-center icon-info bubble-shadow-small">
                        <i class="fas fa-money-bill"></i>
                      </div>
                    </div>
                    <div class="col col-stats ms-3 ms-sm-0">
                      <div class="numbers">
                        <p class="card-category">Last 7 Days Invest</p>
                        <h4 class="card-title">$0.00 $TC</h4>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
            <div class="col-sm-6 col-md-3">
              <div class="card card-stats card-round">
                <div class="card-body">
                  <div class="row align-items-center">
                    <div class="col-icon">
                      <div class="icon-big text-center icon-success bubble-shadow-small">
                        <i class="fas fa-money-bill"></i>
                      </div>
                    </div>
                    <div class="col col-stats ms-3 ms-sm-0">
                      <div class="numbers">
                        <p class="card-category">Total Referral Commission</p>
                        <h4 class="card-title">$0.00 $TC</h4>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
            <div class="col-sm-6 col-md-3">
              <div class="card card-stats card-round">
                <div class="card-body">
                  <div class="row align-items-center">
                    <div class="col-icon">
                      <div class="icon-big text-center icon-secondary bubble-shadow-small">
                        <i class="fas fa-money-bill"></i>
                      </div>
                    </div>
                    <div class="col col-stats ms-3 ms-sm-0">
                      <div class="numbers">
                        <p class="card-category">Total Binary Commission</p>
                        <h4 class="card-title">$0.00 $TC</h4>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <div class="row">
            <div class="col-md-3">
              <div class="card card-stats card-info card-round">
                <div class="card-body">
                  <div class="row">
                    <div class="col-10 ">
                      <div class="">
                        <div class="numbers">
                          <p class="card-category">Users Total BV Cut </p>
                          <h4 class="card-title">0</h4>
                        </div>
                      </div>
                    </div>
                    <div class="col-2 ">
                      <div class="icon-big text-center ">
                        <i class="fas fa-cut"></i>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
            <div class="col-md-3">
              <div class="card card-stats card-warning card-round">
                <div class="card-body">
                  <div class="row">
                    <div class="col-10 ">
                      <div class="">
                        <div class="numbers">
                          <p class="card-category">Users Total BV </p>
                          <h4 class="card-title">0</h4>
                        </div>
                      </div>
                    </div>
                    <div class="col-2 ">
                      <div class="icon-big text-center ">
                        <i class="fas fa-cart-plus"></i>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
            <div class="col-md-3">
              <div class="card card-stats card-secondary card-round">
                <div class="card-body">
                  <div class="row">
                    <div class="col-10 ">
                      <div class="">
                        <div class="numbers">
                          <p class="card-category">Users Left BV </p>
                          <h4 class="card-title">0</h4>
                        </div>
                      </div>
                    </div>
                    <div class="col-2 ">
                      <div class="icon-big text-center ">
                        <i class="fas fa-arrow-left"></i>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
            <div class="col-md-3">
              <div class="card card-stats card-primary card-round">
                <div class="card-body">
                  <div class="row">
                    <div class="col-10 ">
                      <div class="">
                        <div class="numbers">
                          <p class="card-category">Users Right BV </p>
                          <h4 class="card-title">0</h4>
                        </div>
                      </div>
                    </div>
                    <div class="col-2 ">
                      <div class="icon-big text-center ">
                        <i class="fas fa-arrow-right"></i>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>


          <div class="row">
              <div class="col-md-6">
                <div class="card">
                  <div class="card-header">
                    <div class="card-title">Deposit , Withdraw & Invest Report</div>
                  </div>
                  <div class="card-body">
                    <div class="chart-container">
                      <canvas id="lineChart"></canvas>
                    </div>
                  </div>
                </div>
              </div>
          
            <div class="col-md-6">
              <div class="card  card-round">
                <div class="card-header">
                  <div class="card-head-row">
                    <div class="card-title">Transactions Report</div>
                    <div class="card-tools">
                      <div class="dropdown">
                        <button class="btn btn-sm btn-label btn-primary dropdown-toggle" type="button"
                          id="dropdownMenuButton" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                          Daily
                        </button>
                        <div class="dropdown-menu" aria-labelledby="dropdownMenuButton">
                          <a class="dropdown-item" href="#">Weekly</a>
                          <a class="dropdown-item" href="#">Monthly</a>
                          <a class="dropdown-item" href="#">Yearly</a>
                        </div>
                      </div>
                    </div>
                  </div>
                  <div class="card-category">March 25 - April 02</div>
                </div>
                <div class="card-body">
                  <div class="chart-container">
                    <canvas id="barChart"></canvas>
                  </div>
                </div>
              </div>
              <!-- <div class="card card-round">
                <div class="card-body pb-0">
                  <div class="h1 fw-bold float-end text-primary">+5%</div>
                  <h2 class="mb-2">17</h2>
                  <p class="text-muted">Users online</p>
                  <div class="pull-in sparkline-fix">
                    <canvas id="lineChart"></canvas>
                  </div>
                </div>
              </div> -->
            </div>
          </div>
          <div class="row">
            <div class="col-md-4">
              <div class="card">
                <div class="card-header">
                  <div class="card-title">Login By Browser (Last 30 days)</div>
                </div>
                <div class="card-body">
                  <div class="chart-container">
                    <canvas id="pieChart" style="width: 50%; height: 50%"></canvas>
                  </div>
                </div>
              </div>
            </div>
            <div class="col-md-4">
              <div class="card">
                <div class="card-header">
                  <div class="card-title">Login By OS (Last 30 days)</div>
                </div>
                <div class="card-body">
                  <div class="chart-container">
                    <canvas id="doughnutChart" style="width: 50%; height: 50%"></canvas>
                  </div>
                </div>
              </div>
            </div>
            <div class="col-md-4">
              <div class="card">
                <div class="card-header">
                  <div class="card-title">Login By Country (Last 30 days)</div>
                </div>
                <div class="card-body">
                  <div class="chart-container">
                    <canvas id="doughnutChart2" style="width: 50%; height: 50%"></canvas>
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
        var lineChart = document.getElementById("lineChart").getContext("2d"),
        barChart = document.getElementById("barChart").getContext("2d"),
          pieChart = document.getElementById("pieChart").getContext("2d"),
          doughnutChart = document
          .getElementById("doughnutChart")
          .getContext("2d"),
          myDoughnutChart2 = document
          .getElementById("doughnutChart2")
          .getContext("2d");

          
      var myLineChart = new Chart(lineChart, {
        type: "line",
        data: {
          labels: [
            "Jan",
            "Feb",
            "Mar",
            "Apr",
            "May",
            "Jun",
            "Jul",
            "Aug",
            "Sep",
            "Oct",
            "Nov",
            "Dec",
          ],
          datasets: [
            {
              label: "Active Users",
              borderColor: "#1d7af3",
              pointBorderColor: "#FFF",
              pointBackgroundColor: "#1d7af3",
              pointBorderWidth: 2,
              pointHoverRadius: 4,
              pointHoverBorderWidth: 1,
              pointRadius: 4,
              backgroundColor: "transparent",
              fill: true,
              borderWidth: 2,
              data: [
                542, 480, 430, 550, 530, 453, 380, 434, 568, 610, 700, 900,
              ],
            },
          ],
        },
        options: {
          responsive: true,
          maintainAspectRatio: false,
          legend: {
            position: "bottom",
            labels: {
              padding: 10,
              fontColor: "#1d7af3",
            },
          },
          tooltips: {
            bodySpacing: 4,
            mode: "nearest",
            intersect: 0,
            position: "nearest",
            xPadding: 10,
            yPadding: 10,
            caretPadding: 10,
          },
          layout: {
            padding: { left: 15, right: 15, top: 15, bottom: 15 },
          },
        },
      });


        var myBarChart = new Chart(barChart, {
          type: "bar",
          data: {
            labels: [
              "Jan",
              "Feb",
              "Mar",
              "Apr",
              "May",
              "Jun",
              "Jul",
              "Aug",
              "Sep",
              "Oct",
              "Nov",
              "Dec",
            ],
            datasets: [{
              label: "Sales",
              backgroundColor: "rgb(23, 125, 255)",
              borderColor: "rgb(23, 125, 255)",
              data: [3, 2, 9, 5, 4, 6, 4, 6, 7, 8, 7, 4],
            }, ],
          },
          options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
              yAxes: [{
                ticks: {
                  beginAtZero: true,
                },
              }, ],
            },
          },
        });

        var myPieChart = new Chart(pieChart, {
          type: "pie",
          data: {
            datasets: [{
              data: [50, 35, 15],
              backgroundColor: ["#1d7af3", "#f3545d", "#fdaf4b"],
              borderWidth: 0,
            }, ],
            labels: ["New Visitors", "Subscribers", "Active Users"],
          },
          options: {
            responsive: true,
            maintainAspectRatio: false,
            legend: {
              position: "bottom",
              labels: {
                fontColor: "rgb(154, 154, 154)",
                fontSize: 11,
                usePointStyle: true,
                padding: 20,
              },
            },
            pieceLabel: {
              render: "percentage",
              fontColor: "white",
              fontSize: 14,
            },
            tooltips: false,
            layout: {
              padding: {
                left: 20,
                right: 20,
                top: 20,
                bottom: 20,
              },
            },
          },
        });

        var myDoughnutChart = new Chart(doughnutChart, {
          type: "doughnut",
          data: {
            datasets: [{
              data: [10, 20, 30],
              backgroundColor: ["#f3545d", "#fdaf4b", "#1d7af3"],
            }, ],

            labels: ["Red", "Yellow", "Blue"],
          },
          options: {
            responsive: true,
            maintainAspectRatio: false,
            legend: {
              position: "bottom",
            },
            layout: {
              padding: {
                left: 20,
                right: 20,
                top: 20,
                bottom: 20,
              },
            },
          },
        });


                var myDoughnutChart2 = new Chart(doughnutChart2, {
          type: "doughnut",
          data: {
            datasets: [{
              data: [10, 20, 30],
              backgroundColor: ["#f3545d", "#fdaf4b", "#1d7af3"],
            }, ],

            labels: ["Red", "Yellow", "Blue"],
          },
          options: {
            responsive: true,
            maintainAspectRatio: false,
            legend: {
              position: "bottom",
            },
            layout: {
              padding: {
                left: 20,
                right: 20,
                top: 20,
                bottom: 20,
              },
            },
          },
        });




 
      </script>

</body>

</html>