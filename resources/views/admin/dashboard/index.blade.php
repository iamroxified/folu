<?php
require_once base_path('db/config.php');
require_once base_path('db/functions.php');

$selectedSessionId = (int) ($_GET['academic_session_link'] ?? (get_current_academic_session_id() ?? 0));
$selectedTermId = (int) ($_GET['term_link'] ?? (get_current_academic_term_id($selectedSessionId) ?? 0));

$sessions = QueryDB('SELECT * FROM academic_sessions ORDER BY start_date DESC, id DESC')->fetchAll();
$terms = $selectedSessionId > 0 ? get_terms_for_session($selectedSessionId) : [];

$selectedSession = $selectedSessionId > 0
    ? QueryDB('SELECT * FROM academic_sessions WHERE id = ? LIMIT 1', [$selectedSessionId])->fetch(PDO::FETCH_ASSOC)
    : get_current_academic_session();

$selectedTerm = $selectedTermId > 0
    ? QueryDB('SELECT * FROM academic_terms WHERE id = ? LIMIT 1', [$selectedTermId])->fetch(PDO::FETCH_ASSOC)
    : get_current_academic_term($selectedSessionId);

$overview = get_admin_dashboard_overview($selectedSessionId, $selectedTermId);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta http-equiv="X-UA-Compatible" content="IE=edge" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Admin Executive Dashboard - Folu International Schools</title>
  @include('admin.partials.links')
  <style>
    :root {
      --brand-primary: #720922;
      --brand-dark: #4a0616;
      --brand-gradient: linear-gradient(135deg, #720922 0%, #8e1532 60%, #4a0616 100%);
      --card-radius: 12px;
    }

    body {
      background-color: #f4f6f9;
      font-family: 'Segoe UI', system-ui, -apple-system, sans-serif;
    }

    .hero-banner {
      background: var(--brand-gradient);
      color: #ffffff;
      border-radius: var(--card-radius);
      padding: 30px;
      box-shadow: 0 8px 25px rgba(114, 9, 34, 0.2);
      margin-bottom: 25px;
      position: relative;
      overflow: hidden;
    }

    .hero-banner::after {
      content: '';
      position: absolute;
      top: -50%;
      right: -10%;
      width: 350px;
      height: 350px;
      background: rgba(255, 255, 255, 0.05);
      border-radius: 50%;
      pointer-events: none;
    }

    .hero-logo {
      height: 65px;
      width: auto;
      background: #ffffff;
      padding: 6px;
      border-radius: 10px;
      box-shadow: 0 4px 10px rgba(0,0,0,0.15);
    }

    .filter-card {
      background: #ffffff;
      border-radius: var(--card-radius);
      border: 1px solid #e2e8f0;
      box-shadow: 0 2px 8px rgba(0,0,0,0.04);
      margin-bottom: 25px;
    }

    .stat-card {
      background: #ffffff;
      border-radius: var(--card-radius);
      border: none;
      box-shadow: 0 4px 12px rgba(0,0,0,0.05);
      transition: all 0.25s ease;
      height: 100%;
    }

    .stat-card:hover {
      transform: translateY(-4px);
      box-shadow: 0 8px 20px rgba(0,0,0,0.1);
    }

    .stat-icon-wrapper {
      width: 55px;
      height: 55px;
      border-radius: 12px;
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 1.5rem;
    }

    .bg-icon-primary { background: rgba(114, 9, 34, 0.1); color: var(--brand-primary); }
    .bg-icon-blue { background: rgba(37, 99, 235, 0.1); color: #2563eb; }
    .bg-icon-green { background: rgba(22, 163, 74, 0.1); color: #16a34a; }
    .bg-icon-amber { background: rgba(217, 119, 6, 0.1); color: #d97706; }
    .bg-icon-purple { background: rgba(147, 51, 234, 0.1); color: #9333ea; }
    .bg-icon-teal { background: rgba(13, 148, 136, 0.1); color: #0d9488; }

    .financial-card-success {
      background: linear-gradient(135deg, #15803d 0%, #22c55e 100%);
      color: #ffffff;
      border-radius: var(--card-radius);
    }

    .financial-card-danger {
      background: linear-gradient(135deg, #b91c1c 0%, #ef4444 100%);
      color: #ffffff;
      border-radius: var(--card-radius);
    }

    .dashboard-panel {
      background: #ffffff;
      border-radius: var(--card-radius);
      border: 1px solid #e2e8f0;
      box-shadow: 0 4px 12px rgba(0,0,0,0.04);
      margin-bottom: 25px;
    }

    .panel-header {
      padding: 18px 24px;
      border-bottom: 1px solid #f1f5f9;
      display: flex;
      justify-content: space-between;
      align-items: center;
    }

    .panel-title {
      font-size: 1.1rem;
      font-weight: 700;
      color: #1e293b;
      margin: 0;
    }

    .table-custom th {
      background: #f8fafc;
      color: #475569;
      font-weight: 600;
      font-size: 0.85rem;
      text-transform: uppercase;
      letter-spacing: 0.5px;
      border-bottom: 2px solid #e2e8f0;
    }

    .quick-action-item {
      background: #f8fafc;
      border: 1px solid #e2e8f0;
      border-radius: 10px;
      padding: 14px;
      color: #334155;
      font-weight: 600;
      text-decoration: none;
      display: flex;
      align-items: center;
      transition: all 0.2s ease;
    }

    .quick-action-item:hover {
      background: var(--brand-primary);
      color: #ffffff;
      border-color: var(--brand-primary);
      transform: translateX(4px);
    }

    .quick-action-item i {
      font-size: 1.2rem;
      margin-right: 12px;
      width: 25px;
      text-align: center;
    }
  </style>
</head>
<body>
  <div class="wrapper">
    @include('admin.partials.sidebar')
    
    <div class="main-panel">
      @include('admin.partials.header')
      
      <div class="container">
        <div class="page-inner">
          
          <!-- Executive Hero Banner -->
          <div class="hero-banner">
            <div class="row align-items-center">
              <div class="col-lg-8 mb-3 mb-lg-0">
                <div class="d-flex align-items-center gap-3 mb-2">
                  <img src="/images/folu-logo.png" alt="Folu Logo" class="hero-logo" onerror="this.style.display='none'">
                  <div>
                    <h2 class="fw-bold mb-0 text-white">Folu International Schools</h2>
                    <span class="badge bg-white text-dark fw-bold px-3 py-1">Executive Admin Portal</span>
                  </div>
                </div>
                <p class="mb-0 text-white-50 fs-6">
                  Session: <strong><?php echo htmlspecialchars(session_label($selectedSession)); ?></strong>
                  <?php if ($selectedTerm): ?>
                    &nbsp;|&nbsp; Term: <strong><?php echo htmlspecialchars(term_label($selectedTerm)); ?></strong>
                  <?php endif; ?>
                  &nbsp;|&nbsp; Today: <?php echo date('F d, Y'); ?>
                </p>
              </div>
              <div class="col-lg-4 text-lg-end">
                <div class="d-flex flex-wrap gap-2 justify-content-lg-end">
                  <a href="/admin/payments.php" class="btn btn-light text-dark fw-bold">
                    <i class="fas fa-credit-card me-1 text-danger"></i> Record Payment
                  </a>
                  <a href="/admin/add_students.php" class="btn btn-outline-light fw-bold">
                    <i class="fas fa-user-plus me-1"></i> Add Student
                  </a>
                </div>
              </div>
            </div>
          </div>

          <!-- Academic Session & Term Filter Card -->
          <div class="filter-card p-3">
            <form method="GET" class="row align-items-end g-3">
              <div class="col-md-5">
                <label for="academic_session_link" class="form-label fw-bold text-secondary small">Academic Session</label>
                <div class="input-group">
                  <span class="input-group-text bg-light"><i class="fas fa-calendar-alt text-primary"></i></span>
                  <select class="form-select" id="academic_session_link" name="academic_session_link">
                    <?php $sn = 1; ?><?php $sn = 1; ?><?php foreach ($sessions as $session): ?>
                      <option value="<?php echo (int) $session['id']; ?>" <?php echo $selectedSessionId === (int) $session['id'] ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars((string) $session['session_name']); ?>
                      </option>
                    <?php endforeach; ?>
                  </select>
                </div>
              </div>
              <div class="col-md-5">
                <label for="term_link" class="form-label fw-bold text-secondary small">Academic Term</label>
                <div class="input-group">
                  <span class="input-group-text bg-light"><i class="fas fa-layer-group text-info"></i></span>
                  <select class="form-select" id="term_link" name="term_link">
                    <option value="0">All / Current Term Context</option>
                    <?php $sn = 1; ?><?php foreach ($terms as $term): ?>
                      <option value="<?php echo (int) $term['id']; ?>" <?php echo $selectedTermId === (int) $term['id'] ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars(term_label($term)); ?>
                      </option>
                    <?php endforeach; ?>
                  </select>
                </div>
              </div>
              <div class="col-md-2">
                <button type="submit" class="btn btn-dark w-100 fw-bold" style="background-color: #720922; border-color: #720922;">
                  <i class="fas fa-filter me-1"></i> Apply Filter
                </button>
              </div>
            </form>
          </div>

          <!-- Top Row Operational Stat Cards -->
          <div class="row g-3 mb-4">
            <!-- Students -->
            <div class="col-sm-6 col-xl-3">
              <a href="/admin/list_students.php" class="text-decoration-none">
                <div class="card stat-card p-3">
                  <div class="d-flex align-items-center justify-content-between">
                    <div>
                      <span class="text-muted small fw-bold text-uppercase">Total Enrolled</span>
                      <h3 class="fw-bold text-dark mb-0 mt-1"><?php echo number_format((int) $overview['students']); ?></h3>
                      <small class="text-success fw-bold"><i class="fas fa-user-graduate me-1"></i>Active Students</small>
                    </div>
                    <div class="stat-icon-wrapper bg-icon-primary">
                      <i class="fas fa-user-graduate"></i>
                    </div>
                  </div>
                </div>
              </a>
            </div>

            <!-- Teachers -->
            <div class="col-sm-6 col-xl-3">
              <a href="/admin/teachers.php" class="text-decoration-none">
                <div class="card stat-card p-3">
                  <div class="d-flex align-items-center justify-content-between">
                    <div>
                      <span class="text-muted small fw-bold text-uppercase">Teaching Staff</span>
                      <h3 class="fw-bold text-dark mb-0 mt-1"><?php echo number_format((int) $overview['teachers']); ?></h3>
                      <small class="text-primary fw-bold"><i class="fas fa-chalkboard-teacher me-1"></i>Academic Staff</small>
                    </div>
                    <div class="stat-icon-wrapper bg-icon-blue">
                      <i class="fas fa-chalkboard-teacher"></i>
                    </div>
                  </div>
                </div>
              </a>
            </div>

            <!-- Classes -->
            <div class="col-sm-6 col-xl-3">
              <a href="/admin/classes.php" class="text-decoration-none">
                <div class="card stat-card p-3">
                  <div class="d-flex align-items-center justify-content-between">
                    <div>
                      <span class="text-muted small fw-bold text-uppercase">School Classes</span>
                      <h3 class="fw-bold text-dark mb-0 mt-1"><?php echo number_format((int) $overview['classes']); ?></h3>
                      <small class="text-success fw-bold"><i class="fas fa-school me-1"></i>Active Arms</small>
                    </div>
                    <div class="stat-icon-wrapper bg-icon-green">
                      <i class="fas fa-school"></i>
                    </div>
                  </div>
                </div>
              </a>
            </div>

            <!-- Subjects -->
            <div class="col-sm-6 col-xl-3">
              <a href="/admin/subjects.php" class="text-decoration-none">
                <div class="card stat-card p-3">
                  <div class="d-flex align-items-center justify-content-between">
                    <div>
                      <span class="text-muted small fw-bold text-uppercase">Curriculum Subjects</span>
                      <h3 class="fw-bold text-dark mb-0 mt-1"><?php echo number_format((int) $overview['subjects']); ?></h3>
                      <small class="text-warning fw-bold"><i class="fas fa-book me-1"></i>Subjects Offered</small>
                    </div>
                    <div class="stat-icon-wrapper bg-icon-amber">
                      <i class="fas fa-book-open"></i>
                    </div>
                  </div>
                </div>
              </a>
            </div>
          </div>

          <!-- Financial & Activity Metric Cards -->
          <div class="row g-3 mb-4">
            <!-- Fee Collections -->
            <div class="col-sm-6 col-xl-3">
              <div class="card financial-card-success p-3">
                <div class="d-flex justify-content-between align-items-start">
                  <div>
                    <span class="text-white-75 small text-uppercase fw-bold">Fee Collections</span>
                    <h3 class="fw-bold text-white mb-1 mt-1">₦<?php echo number_format((float) ($overview['payments']['total_paid'] ?? 0), 2); ?></h3>
                    <small class="text-white-75">
                      <i class="fas fa-check-circle me-1"></i><?php echo number_format((int) ($overview['payments']['payment_count'] ?? 0)); ?> Completed Txns
                    </small>
                  </div>
                  <div class="fs-2 text-white-50"><i class="fas fa-wallet"></i></div>
                </div>
              </div>
            </div>

            <!-- Outstanding Balances -->
            <div class="col-sm-6 col-xl-3">
              <div class="card financial-card-danger p-3">
                <div class="d-flex justify-content-between align-items-start">
                  <div>
                    <span class="text-white-75 small text-uppercase fw-bold">Outstanding Balances</span>
                    <h3 class="fw-bold text-white mb-1 mt-1">₦<?php echo number_format((float) ($overview['payments']['outstanding'] ?? 0), 2); ?></h3>
                    <small class="text-white-75">
                      <i class="fas fa-exclamation-triangle me-1"></i>Unpaid Balances
                    </small>
                  </div>
                  <div class="fs-2 text-white-50"><i class="fas fa-hand-holding-usd"></i></div>
                </div>
              </div>
            </div>

            <!-- Today's Attendance -->
            <div class="col-sm-6 col-xl-3">
              <div class="card stat-card p-3">
                <div class="d-flex align-items-center justify-content-between">
                  <div>
                    <span class="text-muted small fw-bold text-uppercase">Attendance Today</span>
                    <h3 class="fw-bold text-dark mb-0 mt-1"><?php echo number_format((int) ($overview['attendance_today'] ?? 0)); ?></h3>
                    <small class="text-purple fw-bold" style="color: #9333ea;"><i class="fas fa-clipboard-check me-1"></i>Students Marked</small>
                  </div>
                  <div class="stat-icon-wrapper bg-icon-purple">
                    <i class="fas fa-clipboard-user"></i>
                  </div>
                </div>
              </div>
            </div>

            <!-- Active Announcements -->
            <div class="col-sm-6 col-xl-3">
              <div class="card stat-card p-3">
                <div class="d-flex align-items-center justify-content-between">
                  <div>
                    <span class="text-muted small fw-bold text-uppercase">Announcements</span>
                    <h3 class="fw-bold text-dark mb-0 mt-1"><?php echo number_format((int) ($overview['announcements'] ?? 0)); ?></h3>
                    <small class="text-teal fw-bold" style="color: #0d9488;"><i class="fas fa-bullhorn me-1"></i>Published Notices</small>
                  </div>
                  <div class="stat-icon-wrapper bg-icon-teal">
                    <i class="fas fa-bullhorn"></i>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- Main Content Grid -->
          <div class="row g-4">
            <!-- Left Column: Recent Payments & Recent Admissions -->
            <div class="col-lg-8">
              
              <!-- Recent Payments Table Panel -->
              <div class="dashboard-panel">
                <div class="panel-header">
                  <div>
                    <h5 class="panel-title"><i class="fas fa-history text-primary me-2"></i>Recent Payment Transactions</h5>
                    <small class="text-muted">Latest fee payments recorded for selected session & term</small>
                  </div>
                  <a href="/admin/payments.php" class="btn btn-sm btn-outline-primary fw-bold">View All Payments</a>
                </div>
                <div class="card-body p-0">
                  <?php if (empty($overview['recent_payments'])): ?>
                    <div class="text-center py-4 text-muted">
                      <i class="fas fa-receipt fa-2x mb-2 d-block"></i> No payments recorded for this selected context yet.
                    </div>
                  <?php else: ?>
                    <div class="table-responsive">
                      <table id="basic-datatables" class="display table-striped table-hover table table-hover table-custom align-middle mb-0">
                        <thead>
                                                 <tr>
                                                    <th style="width: 50px;">S/N</th>
                            <th>Date</th>
                            <th>Student</th>
                            <th>Fee Details</th>
                            <th>Amount (₦)</th>
                            <th>Method</th>
                            <th>Action</th>
                          </tr>
                        </thead>
                        <tbody>
                          <?php $sn = 1; ?><?php foreach ($overview['recent_payments'] as $payment): ?>
                            <tr>
                                                         <td><?php echo $sn++; ?></td>
                              <td>
                                <small class="fw-semibold text-dark">
                                  <?php echo !empty($payment['payment_date']) ? date('M d, Y', strtotime((string)$payment['payment_date'])) : date('M d, Y', strtotime((string)($payment['created_at'] ?? 'now'))); ?>
                                </small>
                              </td>
                              <td>
                                <strong><?php echo htmlspecialchars(trim(($payment['first_name'] ?? '') . ' ' . ($payment['last_name'] ?? ''))); ?></strong>
                                <?php if (!empty($payment['admission_no'])): ?>
                                  <br><small class="text-muted"><code><?php echo htmlspecialchars((string)$payment['admission_no']); ?></code></small>
                                <?php endif; ?>
                              </td>
                              <td>
                                <span class="badge bg-light text-dark border">
                                  <?php echo htmlspecialchars((string) (($payment['fee_type_name'] ?? 'Fee') . (!empty($payment['term_name']) ? ' (' . $payment['term_name'] . ')' : ''))); ?>
                                </span>
                              </td>
                              <td>
                                <strong class="text-success">₦<?php echo number_format((float) ($payment['amount_paid'] ?? 0), 2); ?></strong>
                              </td>
                              <td>
                                <span class="badge bg-info text-dark">
                                  <?php echo htmlspecialchars(ucfirst(str_replace('_', ' ', (string) ($payment['payment_method'] ?? 'cash')))); ?>
                                </span>
                              </td>
                              <td>
                                <?php if (!empty($payment['student_id'])): ?>
                                  <a href="/admin/view_students.php?id=<?php echo (int)$payment['student_id']; ?>" class="btn btn-sm btn-light text-primary border" title="View Student Payments">
                                    <i class="fas fa-eye"></i>
                                  </a>
                                <?php endif; ?>
                              </td>
                            </tr>
                          <?php endforeach; ?>
                        </tbody>
                      </table>
                    </div>
                  <?php endif; ?>
                </div>
              </div>

              <!-- Recent Student Admissions Feed -->
              <div class="dashboard-panel">
                <div class="panel-header">
                  <div>
                    <h5 class="panel-title"><i class="fas fa-user-plus text-success me-2"></i>Recent Student Admissions</h5>
                    <small class="text-muted">Newly enrolled students in the current session</small>
                  </div>
                  <a href="/admin/list_students.php" class="btn btn-sm btn-outline-success fw-bold">Student Directory</a>
                </div>
                <div class="card-body p-3">
                  <?php if (empty($overview['recent_admissions'])): ?>
                    <div class="text-center py-4 text-muted">
                      <i class="fas fa-users-slash fa-2x mb-2 d-block"></i> No recent student records found.
                    </div>
                  <?php else: ?>
                    <div class="row g-3">
                      <?php $sn = 1; ?><?php foreach ($overview['recent_admissions'] as $student): ?>
                        <div class="col-md-6">
                          <div class="d-flex align-items-center p-3 border rounded bg-light">
                            <div class="flex-shrink-0 me-3">
                              <div class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center fw-bold" style="width: 45px; height: 45px; font-size: 1.1rem; background-color: #720922 !important;">
                                <?php echo strtoupper(substr($student['first_name'] ?? 'S', 0, 1) . substr($student['last_name'] ?? 'T', 0, 1)); ?>
                              </div>
                            </div>
                            <div class="flex-grow-1 overflow-hidden">
                              <h6 class="mb-0 text-truncate font-weight-bold text-dark">
                                <?php echo htmlspecialchars(trim(($student['first_name'] ?? '') . ' ' . ($student['last_name'] ?? ''))); ?>
                              </h6>
                              <small class="text-muted d-block">
                                Class: <?php echo htmlspecialchars(trim(($student['class_name'] ?? 'Not Assigned') . ' ' . ($student['class_arm'] ?? ''))); ?>
                              </small>
                              <span class="badge bg-secondary mt-1"><?php echo htmlspecialchars((string) ($student['admission_no'] ?? 'N/A')); ?></span>
                            </div>
                            <div>
                              <a href="/admin/view_students.php?id=<?php echo (int) $student['id']; ?>" class="btn btn-sm btn-light border text-dark">
                                <i class="fas fa-chevron-right"></i>
                              </a>
                            </div>
                          </div>
                        </div>
                      <?php endforeach; ?>
                    </div>
                  <?php endif; ?>
                </div>
              </div>

            </div>

            <!-- Right Column: Class Distribution & Quick Actions -->
            <div class="col-lg-4">
              
              <!-- Class Distribution Panel -->
              <div class="dashboard-panel">
                <div class="panel-header">
                  <h5 class="panel-title"><i class="fas fa-chart-bar text-warning me-2"></i>Class Enrolment</h5>
                  <span class="badge bg-light text-dark border"><?php echo count($overview['class_distribution']); ?> Classes</span>
                </div>
                <div class="card-body p-3">
                  <?php if (empty($overview['class_distribution'])): ?>
                    <div class="text-center py-3 text-muted">No class distribution data available.</div>
                  <?php else: ?>
                    <?php 
                      $totalStudentsInClasses = array_sum(array_column($overview['class_distribution'], 'student_count')) ?: 1;
                      foreach ($overview['class_distribution'] as $class):
                          $count = (int) ($class['student_count'] ?? 0);
                          $percentage = min(100, round(($count / $totalStudentsInClasses) * 100));
                    ?>
                      <div class="mb-3">
                        <div class="d-flex justify-content-between align-items-center mb-1">
                          <strong class="text-dark small">
                            <?php echo htmlspecialchars(trim(($class['class_name'] ?? '') . ' ' . ($class['class_arm'] ?? ''))); ?>
                          </strong>
                          <span class="badge bg-primary text-white" style="background-color: #720922 !important;"><?php echo $count; ?> Students</span>
                        </div>
                        <div class="progress" style="height: 6px;">
                          <div class="progress-bar" role="progressbar" style="width: <?php echo $percentage; ?>%; background-color: #720922;" aria-valuenow="<?php echo $percentage; ?>" aria-valuemin="0" aria-valuemax="100"></div>
                        </div>
                      </div>
                    <?php endforeach; ?>
                  <?php endif; ?>
                </div>
              </div>

              <!-- Quick Command Center Panel -->
              <div class="dashboard-panel">
                <div class="panel-header">
                  <h5 class="panel-title"><i class="fas fa-bolt text-danger me-2"></i>Quick Actions</h5>
                </div>
                <div class="card-body p-3">
                  <div class="d-grid gap-2">
                    <a href="/admin/payments.php" class="quick-action-item">
                      <i class="fas fa-credit-card text-success"></i> Record Student Payment
                    </a>
                    <a href="/admin/fee_structure.php" class="quick-action-item">
                      <i class="fas fa-file-invoice-dollar text-primary"></i> Allocate Fee Structure
                    </a>
                    <a href="/admin/import_export_students.php" class="quick-action-item">
                      <i class="fas fa-file-import text-info"></i> Import / Export Students
                    </a>
                    <a href="/admin/students/promotions" class="quick-action-item">
                      <i class="fas fa-user-graduate text-warning"></i> Student Promotions Engine
                    </a>
                    <a href="/admin/classes.php" class="quick-action-item">
                      <i class="fas fa-school text-purple"></i> Manage School Classes
                    </a>
                    <a href="/admin/sessions.php" class="quick-action-item">
                      <i class="fas fa-calendar-alt text-secondary"></i> Sessions & Terms Setup
                    </a>
                    <a href="/admin/settings" class="quick-action-item">
                      <i class="fas fa-cog text-dark"></i> System Settings
                    </a>
                  </div>
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
