<?php
require_once base_path('db/config.php');
require_once base_path('db/functions.php');

if (isset($_SESSION['student_user_id'], $_SESSION['student_id'])) {
    header('Location: /student/index.php');
    exit;
}

$error = '';
$username = trim((string) ($_POST['username'] ?? ''));

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'login') {
    $password = (string) ($_POST['password'] ?? '');

    if ($username === '' || $password === '') {
        $error = 'Please enter your admission number and password.';
    } else {
        $result = portal_authenticate_user($username, $password, ['student']);

        if (!($result['success'] ?? false)) {
            $error = $result['message'] ?? 'Unable to sign in right now.';
        } else {
            $student = get_student_profile_by_user_id((int) $result['user']['id']);

            if (!$student) {
                $error = 'Your student profile could not be found.';
            } else {
                session_regenerate_id(true);
                $_SESSION['student_user_id'] = (int) $result['user']['id'];
                $_SESSION['student_id'] = (int) $student['id'];
                $_SESSION['student_username'] = $student['admission_no'] ?? $result['user']['username'];
                $_SESSION['student_name'] = trim(($student['first_name'] ?? '') . ' ' . ($student['last_name'] ?? ''));
                header('Location: /student/index.php');
                exit;
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta http-equiv="X-UA-Compatible" content="IE=edge" />
  <title>Student Login</title>
  @include('admin.partials.links')
</head>
<body>
  <div class="wrapper">
    <div class="container-fluid d-flex align-items-center justify-content-center">
      <div class="page-inner" style="margin: auto;">
        <div class="col-md-6 offset-md-3">
          <div class="card">
            <div class="card-body">
              <div class="card card-profile">
                <div class="card-header" style="background-image: url('/admin/assets/img/blogpost.jpg')">
                  <div class="profile-picture">
                    <div class="avatar avatar-xl">
                      <img src="/images/folu_logo.jpg" alt="school logo" class="avatar-img rounded-circle" />
                    </div>
                  </div>
                </div>
                <div class="card-body">
                  <div>
                    <h5>Welcome to</h5>
                    <h3>Student Portal</h3>
                    <hr>
                    <p class="text-center">Log in with your admission number to view fees, attendance, and results.</p>
                  </div>
                  <?php if ($error !== ''): ?>
                    <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
                  <?php endif; ?>
                  <form method="POST">
                    <input type="hidden" name="action" value="login">
                    <div class="form-group">
                      <label for="username">Admission Number</label>
                      <input type="text" class="form-control" id="username" name="username" value="<?php echo htmlspecialchars($username); ?>" required>
                    </div>
                    <div class="form-group">
                      <label for="password">Password</label>
                      <input type="password" class="form-control" id="password" name="password" required>
                    </div>
                    <button type="submit" class="btn btn-primary w-100">Login to Student Portal</button>
                  </form>
                  <div class="text-center mt-3">
                    <small class="text-muted">Default password: <strong>password</strong></small>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
  <script src="/admin/assets/js/core/jquery-3.7.1.min.js"></script>
  <script src="/admin/assets/js/core/bootstrap.min.js"></script>
</body>
</html>
