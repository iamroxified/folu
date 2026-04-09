<?php
require_once base_path('db/config.php');
require_once base_path('db/functions.php');

if (!isset($_SESSION['teacher_user_id'], $_SESSION['teacher_id'])) {
    header('Location: /teacher/login.php');
    exit;
}

$teacher = get_teacher_profile_by_user_id((int) $_SESSION['teacher_user_id']);

if (!$teacher) {
    header('Location: /teacher/logout.php');
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

        $user = QueryDB('SELECT * FROM users WHERE id = ?', [(int) $teacher['user_link']])->fetch(PDO::FETCH_ASSOC);

        if (!$user || !password_verify($currentPassword, (string) $user['password'])) {
            throw new Exception('Your current password is not correct.');
        }

        QueryDB(
            'UPDATE users SET password = ?, updated_at = NOW() WHERE id = ?',
            [password_hash($newPassword, PASSWORD_DEFAULT), (int) $teacher['user_link']]
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
  <title>Teacher Account</title>
  @include('admin.partials.links')
</head>
<body>
  <div class="wrapper">
    @include('teacher.partials.sidebar')
    <div class="main-panel">
      @include('teacher.partials.header')
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
                  <p><strong>Name:</strong> <?php echo htmlspecialchars(trim(($teacher['first_name'] ?? '') . ' ' . ($teacher['last_name'] ?? ''))); ?></p>
                  <p><strong>Teacher ID:</strong> <?php echo htmlspecialchars((string) $teacher['teacher_id']); ?></p>
                  <p><strong>Username:</strong> <?php echo htmlspecialchars((string) ($_SESSION['teacher_username'] ?? '')); ?></p>
                  <p><strong>Email:</strong> <?php echo htmlspecialchars((string) $teacher['email']); ?></p>
                  <p><strong>Phone:</strong> <?php echo htmlspecialchars((string) ($teacher['phone'] ?? 'N/A')); ?></p>
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
