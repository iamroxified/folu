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

$message = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'change_password') {
    $currentPassword = (string) ($_POST['current_password'] ?? '');
    $newPassword = (string) ($_POST['new_password'] ?? '');
    $confirmPassword = (string) ($_POST['confirm_password'] ?? '');

    try {
        if ($currentPassword === '' || $newPassword === '' || $confirmPassword === '') {
            throw new Exception('Please complete all password fields.');
        }

        if ($newPassword !== $confirmPassword) {
            throw new Exception('The new password confirmation does not match.');
        }

        if (strlen($newPassword) < 6) {
            throw new Exception('Use at least 6 characters for the new password.');
        }

        $user = QueryDB('SELECT * FROM users WHERE id = ?', [(int) $student['user_link']])->fetch(PDO::FETCH_ASSOC);

        if (!$user || !password_verify($currentPassword, (string) $user['password'])) {
            throw new Exception('Your current password is not correct.');
        }

        QueryDB(
            'UPDATE users SET password = ?, updated_at = NOW() WHERE id = ?',
            [password_hash($newPassword, PASSWORD_DEFAULT), (int) $student['user_link']]
        );

        $message = 'Password updated successfully.';
    } catch (Throwable $e) {
        $error = $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta http-equiv="X-UA-Compatible" content="IE=edge" />
  <title>Student Account</title>
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
            <h2 class="text-dark pb-2 fw-bold">Account Settings</h2>
          </div>

          <?php if ($message !== ''): ?>
            <div class="alert alert-success"><?php echo htmlspecialchars($message); ?></div>
          <?php endif; ?>
          <?php if ($error !== ''): ?>
            <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
          <?php endif; ?>

          <div class="row">
            <div class="col-md-5">
              <div class="card">
                <div class="card-header"><div class="card-title">Profile</div></div>
                <div class="card-body">
                  <p><strong>Name:</strong> <?php echo htmlspecialchars(trim(($student['first_name'] ?? '') . ' ' . ($student['last_name'] ?? '') . ' ' . ($student['other_names'] ?? ''))); ?></p>
                  <p><strong>Admission No:</strong> <?php echo htmlspecialchars((string) $student['admission_no']); ?></p>
                  <p><strong>Username:</strong> <?php echo htmlspecialchars((string) ($_SESSION['student_username'] ?? '')); ?></p>
                  <p><strong>Class:</strong> <?php echo htmlspecialchars(trim(($student['class_name'] ?? '') . ' ' . ($student['class_arm'] ?? ''))); ?></p>
                  <p><strong>Email:</strong> <?php echo htmlspecialchars((string) ($student['email'] ?? $student['user_email'] ?? 'N/A')); ?></p>
                </div>
              </div>
            </div>
            <div class="col-md-7">
              <div class="card">
                <div class="card-header"><div class="card-title">Change Password</div></div>
                <div class="card-body">
                  <form method="POST">
                    <input type="hidden" name="action" value="change_password">
                    <div class="form-group">
                      <label for="current_password">Current Password</label>
                      <input type="password" class="form-control" id="current_password" name="current_password" required>
                    </div>
                    <div class="form-group">
                      <label for="new_password">New Password</label>
                      <input type="password" class="form-control" id="new_password" name="new_password" required>
                    </div>
                    <div class="form-group">
                      <label for="confirm_password">Confirm New Password</label>
                      <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
                    </div>
                    <button type="submit" class="btn btn-primary">Update Password</button>
                  </form>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
      @include('admin.partials.footer')
</body>
</html>
