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

$announcements = get_announcements_for_audience('students', get_current_academic_session_id() ?? 0);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta http-equiv="X-UA-Compatible" content="IE=edge" />
  <title>Student Announcements</title>
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
            <h2 class="text-dark pb-2 fw-bold">Announcements</h2>
          </div>
          <div class="card">
            <div class="card-header"><div class="card-title">School Updates</div></div>
            <div class="card-body">
              <?php if (empty($announcements)): ?>
                <div class="alert alert-info mb-0">No announcements are available yet.</div>
              <?php else: ?>
                <?php foreach ($announcements as $announcement): ?>
                  <div class="border rounded p-3 mb-3">
                    <h5 class="mb-1"><?php echo htmlspecialchars((string) $announcement['title']); ?></h5>
                    <div class="text-muted small mb-2">
                      Audience: <?php echo htmlspecialchars(ucfirst((string) $announcement['audience'])); ?>
                      <?php if (!empty($announcement['published_at'])): ?>
                        | Published: <?php echo htmlspecialchars((string) $announcement['published_at']); ?>
                      <?php endif; ?>
                    </div>
                    <p class="mb-0"><?php echo nl2br(htmlspecialchars((string) $announcement['body'])); ?></p>
                  </div>
                <?php endforeach; ?>
              <?php endif; ?>
            </div>
          </div>
        </div>
      </div>
      @include('admin.partials.footer')
</body>
</html>
