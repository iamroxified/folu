<?php
require_once base_path('db/config.php');
require_once base_path('db/functions.php');

if (!isset($_SESSION['student_user_id'], $_SESSION['student_id'])) {
    header('Location: /student/login.php');
    exit;
}

$student = get_student_profile_by_user_id((int) $_SESSION['student_user_id']);

if (!$student) {
    header('Location: /student/logout.php');
    exit;
}

$selectedSessionId = (int) ($_GET['academic_session_link'] ?? (get_current_academic_session_id() ?? (int) ($student['academic_session_link'] ?? 0)));
$selectedTerm = validate($_GET['term'] ?? '');
$sessions = QueryDB('SELECT * FROM academic_sessions ORDER BY id DESC')->fetchAll();
$canViewResults = student_result_access_allowed((int) $student['id'], $selectedSessionId);
$outstandingFees = array_values(array_filter(get_student_fee_records((int) $student['id'], $selectedSessionId), static fn ($row) => (float) ($row['balance'] ?? 0) > 0));
$results = $canViewResults ? get_student_results((int) $student['id'], $selectedSessionId, $selectedTerm !== '' ? $selectedTerm : null) : [];
$resultAverage = 0.0;
$resultCount = 0;

foreach ($results as $result) {
    $resultAverage += (float) ($result['score'] ?? 0);
    $resultCount++;
}

if ($resultCount > 0) {
    $resultAverage /= $resultCount;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta http-equiv="X-UA-Compatible" content="IE=edge" />
  <title>Student Results</title>
  @include('admin.partials.links')
</head>
<body>
  <div class="wrapper">
    @include('student.partials.sidebar')
    <div class="main-panel">
      @include('student.partials.header')
      <div class="container">
        <div class="page-inner">
          <div class="d-flex align-items-left flex-column flex-md-row">
            <h2 class="text-dark pb-2 fw-bold">Results</h2>
          </div>

          <div class="card">
            <div class="card-header"><div class="card-title">Result Filter</div></div>
            <div class="card-body">
              <form method="GET" class="row">
                <div class="col-md-8">
                  <label for="academic_session_link">Academic Session</label>
                  <select class="form-control" id="academic_session_link" name="academic_session_link">
                    <?php foreach ($sessions as $session): ?>
                      <option value="<?php echo (int) $session['id']; ?>" <?php echo $selectedSessionId === (int) $session['id'] ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars((string) ($session['session_name'] . ' - ' . session_term_label($session['session_term'] ?? ''))); ?>
                      </option>
                    <?php endforeach; ?>
                  </select>
                </div>
                <div class="col-md-2">
                  <label for="term">Term</label>
                  <select class="form-control" id="term" name="term">
                    <option value="" <?php echo $selectedTerm === '' ? 'selected' : ''; ?>>All Terms</option>
                    <option value="1" <?php echo $selectedTerm === '1' ? 'selected' : ''; ?>>First</option>
                    <option value="2" <?php echo $selectedTerm === '2' ? 'selected' : ''; ?>>Second</option>
                    <option value="3" <?php echo $selectedTerm === '3' ? 'selected' : ''; ?>>Third</option>
                  </select>
                </div>
                <div class="col-md-2 d-flex align-items-end">
                  <button type="submit" class="btn btn-primary w-100">Load</button>
                </div>
              </form>
            </div>
          </div>

          <?php if (!$canViewResults): ?>
            <div class="alert alert-warning mt-3">Your results are locked because there are unpaid fees for the selected session.</div>
            <div class="card">
              <div class="card-header"><div class="card-title">Outstanding Fees</div></div>
              <div class="card-body">
                <div class="table-responsive">
                  <table class="table table-bordered table-striped">
                    <thead><tr><th>Fee Item</th><th>Amount Due</th><th>Paid</th><th>Balance</th></tr></thead>
                    <tbody>
                      <?php foreach ($outstandingFees as $fee): ?>
                        <tr>
                          <td><?php echo htmlspecialchars((string) ($fee['fee_description'] ?: ('Fee #' . $fee['fee_structure_link']))); ?></td>
                          <td>N<?php echo number_format((float) ($fee['amount_due'] ?? 0), 2); ?></td>
                          <td>N<?php echo number_format((float) ($fee['amount_paid'] ?? 0), 2); ?></td>
                          <td>N<?php echo number_format((float) ($fee['balance'] ?? 0), 2); ?></td>
                        </tr>
                      <?php endforeach; ?>
                    </tbody>
                  </table>
                </div>
              </div>
            </div>
          <?php else: ?>
            <div class="row mt-3">
              <div class="col-md-6">
                <div class="card card-stats card-info card-round">
                  <div class="card-body"><div class="numbers"><p class="card-category">Average Score</p><h4 class="card-title"><?php echo number_format($resultAverage, 1); ?></h4></div></div>
                </div>
              </div>
              <div class="col-md-6">
                <div class="card card-stats card-success card-round">
                  <div class="card-body"><div class="numbers"><p class="card-category">Result Rows</p><h4 class="card-title"><?php echo $resultCount; ?></h4></div></div>
                </div>
              </div>
            </div>

            <div class="card">
              <div class="card-header"><div class="card-title">Published Results</div></div>
              <div class="card-body">
                <?php if (empty($results)): ?>
                  <div class="alert alert-info mb-0">No result records were found for the selected filters.</div>
                <?php else: ?>
                  <div class="table-responsive">
                    <table class="table table-bordered table-striped">
                      <thead><tr><th>Subject</th><th>Term</th><th>Assessment</th><th>Score</th><th>Grade</th><th>Remarks</th></tr></thead>
                      <tbody>
                        <?php foreach ($results as $result): ?>
                          <tr>
                            <td><?php echo htmlspecialchars((string) ($result['subject_name'] ?? 'Subject')); ?></td>
                            <td><?php echo htmlspecialchars(session_term_label($result['term'] ?? '')); ?></td>
                            <td><?php echo htmlspecialchars(ucfirst((string) ($result['exam_type'] ?? 'assessment'))); ?></td>
                            <td><?php echo number_format((float) ($result['score'] ?? 0), 2); ?></td>
                            <td><?php echo htmlspecialchars((string) ($result['grade'] ?? '')); ?></td>
                            <td><?php echo htmlspecialchars((string) ($result['remarks'] ?? '')); ?></td>
                          </tr>
                        <?php endforeach; ?>
                      </tbody>
                    </table>
                  </div>
                <?php endif; ?>
              </div>
            </div>
          <?php endif; ?>
        </div>
      </div>
      @include('admin.partials.footer')
</body>
</html>
