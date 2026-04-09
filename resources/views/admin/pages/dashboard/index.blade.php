<?php
require_once base_path('db/config.php');
require_once base_path('db/functions.php');

if (!isset($_SESSION['adid'])) {
    header('Location: /admin/login.php');
    exit;
}

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
  <title>School Dashboard</title>
  @include('admin.partials.links')
</head>
<body>
  <div class="wrapper">
    @include('admin.partials.sidebar')
    <div class="main-panel">
      @include('admin.partials.header')
      <div class="container">
        <div class="page-inner">
          <div class="d-flex align-items-left align-items-md-center flex-column flex-md-row pt-2 pb-4">
            <div>
              <h3 class="fw-bold mb-2">School Management Dashboard</h3>
              <h6 class="op-7 mb-0">
                Session: <?php echo htmlspecialchars(session_label($selectedSession)); ?>
                <?php if ($selectedTerm): ?>
                  | Term: <?php echo htmlspecialchars(term_label($selectedTerm)); ?>
                <?php endif; ?>
              </h6>
            </div>
            <div class="ms-md-auto py-2 py-md-0">
              <a href="{{ url('/admin/sessions.php') }}" class="btn btn-primary btn-round">Manage Sessions & Terms</a>
              <a href="{{ url('/admin/payments.php') }}" class="btn btn-success btn-round">Payments Module</a>
            </div>
          </div>

          <div class="card">
            <div class="card-header">
              <div class="card-title">Dashboard Filters</div>
            </div>
            <div class="card-body">
              <form method="GET" class="row">
                <div class="col-md-5">
                  <label for="academic_session_link">Session</label>
                  <select class="form-control" id="academic_session_link" name="academic_session_link">
                    <?php foreach ($sessions as $session): ?>
                      <option value="<?php echo (int) $session['id']; ?>" <?php echo $selectedSessionId === (int) $session['id'] ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars((string) $session['session_name']); ?>
                      </option>
                    <?php endforeach; ?>
                  </select>
                </div>
                <div class="col-md-5">
                  <label for="term_link">Term</label>
                  <select class="form-control" id="term_link" name="term_link">
                    <option value="0">All / Current Term</option>
                    <?php foreach ($terms as $term): ?>
                      <option value="<?php echo (int) $term['id']; ?>" <?php echo $selectedTermId === (int) $term['id'] ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars(term_label($term)); ?>
                      </option>
                    <?php endforeach; ?>
                  </select>
                </div>
                <div class="col-md-2 d-flex align-items-end">
                  <button type="submit" class="btn btn-secondary w-100">Refresh</button>
                </div>
              </form>
            </div>
          </div>

          <div class="row mt-3">
            <div class="col-sm-6 col-md-3">
              <div class="card card-stats card-round">
                <div class="card-body">
                  <div class="row align-items-center">
                    <div class="col-icon">
                      <div class="icon-big text-center icon-primary bubble-shadow-small"><i class="fas fa-user-graduate"></i></div>
                    </div>
                    <div class="col col-stats ms-3 ms-sm-0">
                      <div class="numbers">
                        <p class="card-category">Students in Session</p>
                        <h4 class="card-title"><?php echo (int) $overview['students']; ?></h4>
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
                      <div class="icon-big text-center icon-info bubble-shadow-small"><i class="fas fa-chalkboard-teacher"></i></div>
                    </div>
                    <div class="col col-stats ms-3 ms-sm-0">
                      <div class="numbers">
                        <p class="card-category">Teachers</p>
                        <h4 class="card-title"><?php echo (int) $overview['teachers']; ?></h4>
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
                      <div class="icon-big text-center icon-success bubble-shadow-small"><i class="fas fa-school"></i></div>
                    </div>
                    <div class="col col-stats ms-3 ms-sm-0">
                      <div class="numbers">
                        <p class="card-category">Classes</p>
                        <h4 class="card-title"><?php echo (int) $overview['classes']; ?></h4>
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
                      <div class="icon-big text-center icon-warning bubble-shadow-small"><i class="fas fa-book"></i></div>
                    </div>
                    <div class="col col-stats ms-3 ms-sm-0">
                      <div class="numbers">
                        <p class="card-category">Subjects</p>
                        <h4 class="card-title"><?php echo (int) $overview['subjects']; ?></h4>
                      </div>
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
                  <div class="numbers">
                    <p class="card-category">Collections</p>
                    <h4 class="card-title">N<?php echo number_format((float) ($overview['payments']['total_paid'] ?? 0), 2); ?></h4>
                  </div>
                </div>
              </div>
            </div>
            <div class="col-sm-6 col-md-3">
              <div class="card card-stats card-round">
                <div class="card-body">
                  <div class="numbers">
                    <p class="card-category">Outstanding</p>
                    <h4 class="card-title">N<?php echo number_format((float) ($overview['payments']['outstanding'] ?? 0), 2); ?></h4>
                  </div>
                </div>
              </div>
            </div>
            <div class="col-sm-6 col-md-3">
              <div class="card card-stats card-round">
                <div class="card-body">
                  <div class="numbers">
                    <p class="card-category">Attendance Today</p>
                    <h4 class="card-title"><?php echo (int) ($overview['attendance_today'] ?? 0); ?></h4>
                  </div>
                </div>
              </div>
            </div>
            <div class="col-sm-6 col-md-3">
              <div class="card card-stats card-round">
                <div class="card-body">
                  <div class="numbers">
                    <p class="card-category">Announcements</p>
                    <h4 class="card-title"><?php echo (int) ($overview['announcements'] ?? 0); ?></h4>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <div class="row">
            <div class="col-md-7">
              <div class="card">
                <div class="card-header">
                  <div class="card-title">Recent Payments</div>
                  <div class="card-category">Filtered by session and term.</div>
                </div>
                <div class="card-body">
                  <?php if (empty($overview['recent_payments'])): ?>
                    <div class="alert alert-info mb-0">No payments have been recorded for this context yet.</div>
                  <?php else: ?>
                    <div class="table-responsive">
                      <table class="table table-striped table-bordered">
                        <thead>
                          <tr>
                            <th>Date</th>
                            <th>Student</th>
                            <th>Fee</th>
                            <th>Amount</th>
                            <th>Method</th>
                          </tr>
                        </thead>
                        <tbody>
                          <?php foreach ($overview['recent_payments'] as $payment): ?>
                            <tr>
                              <td><?php echo htmlspecialchars((string) ($payment['payment_date'] ?? $payment['created_at'] ?? '')); ?></td>
                              <td><?php echo htmlspecialchars(trim(($payment['first_name'] ?? '') . ' ' . ($payment['last_name'] ?? ''))); ?></td>
                              <td><?php echo htmlspecialchars((string) (($payment['fee_type_name'] ?? 'Fee') . ($payment['term_name'] ? ' - ' . $payment['term_name'] : ''))); ?></td>
                              <td>N<?php echo number_format((float) ($payment['amount_paid'] ?? 0), 2); ?></td>
                              <td><?php echo htmlspecialchars(ucfirst(str_replace('_', ' ', (string) ($payment['payment_method'] ?? 'n/a')))); ?></td>
                            </tr>
                          <?php endforeach; ?>
                        </tbody>
                      </table>
                    </div>
                  <?php endif; ?>
                </div>
              </div>
            </div>
            <div class="col-md-5">
              <div class="card">
                <div class="card-header">
                  <div class="card-title">Class Distribution</div>
                </div>
                <div class="card-body">
                  <?php if (empty($overview['class_distribution'])): ?>
                    <div class="alert alert-info mb-0">No class data available.</div>
                  <?php else: ?>
                    <?php foreach ($overview['class_distribution'] as $class): ?>
                      <div class="d-flex justify-content-between border rounded p-2 mb-2">
                        <div>
                          <strong><?php echo htmlspecialchars(trim(($class['class_name'] ?? '') . ' ' . ($class['class_arm'] ?? ''))); ?></strong>
                          <div class="text-muted small"><?php echo htmlspecialchars((string) ($class['class_level'] ?? '')); ?></div>
                        </div>
                        <span class="badge badge-primary align-self-center"><?php echo (int) ($class['student_count'] ?? 0); ?></span>
                      </div>
                    <?php endforeach; ?>
                  <?php endif; ?>
                </div>
              </div>
            </div>
          </div>

          <div class="row">
            <div class="col-md-6">
              <div class="card">
                <div class="card-header">
                  <div class="card-title">Recent Admissions</div>
                </div>
                <div class="card-body">
                  <?php if (empty($overview['recent_admissions'])): ?>
                    <div class="alert alert-info mb-0">No recent student records were found.</div>
                  <?php else: ?>
                    <ul class="list-group">
                      <?php foreach ($overview['recent_admissions'] as $student): ?>
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                          <span>
                            <?php echo htmlspecialchars(trim(($student['first_name'] ?? '') . ' ' . ($student['last_name'] ?? ''))); ?>
                            <small class="d-block text-muted"><?php echo htmlspecialchars(trim(($student['class_name'] ?? '') . ' ' . ($student['class_arm'] ?? ''))); ?></small>
                          </span>
                          <span class="badge badge-info"><?php echo htmlspecialchars((string) ($student['admission_no'] ?? '')); ?></span>
                        </li>
                      <?php endforeach; ?>
                    </ul>
                  <?php endif; ?>
                </div>
              </div>
            </div>
            <div class="col-md-6">
              <div class="card">
                <div class="card-header">
                  <div class="card-title">Quick Actions</div>
                </div>
                <div class="card-body d-grid gap-2">
                  <a href="{{ url('/admin/classes/manage.php') }}" class="btn btn-outline-primary">Manage Class Entities</a>
                  <a href="{{ url('/admin/classes/assign_students.php') }}" class="btn btn-outline-info">Manage Student Placement</a>
                  <a href="{{ url('/admin/payments.php') }}" class="btn btn-outline-success">Open Payments Module</a>
                  <a href="{{ url('/admin/sessions.php') }}" class="btn btn-outline-secondary">Open Sessions & Terms</a>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
      @include('admin.partials.footer')
</body>
</html>
