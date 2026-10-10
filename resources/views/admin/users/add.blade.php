<?php
require_once base_path('db/config.php');
require_once base_path('db/functions.php');

if (!isset($_SESSION['adid'])) {
    header('Location: /admin/login.php');
    exit;
}

$error = '';
$success = '';

if (isset($_SESSION['user_add_success'])) {
    $success = (string) $_SESSION['user_add_success'];
    unset($_SESSION['user_add_success']);
}

$roles = QueryDB('SELECT * FROM roles ORDER BY role_name ASC')->fetchAll();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = validate($_POST['name'] ?? '');
    $username = validate($_POST['username'] ?? '');
    $email = validate($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $passwordConfirm = $_POST['password_confirmation'] ?? '';
    $roleId = (int) ($_POST['role_id'] ?? 0);
    $status = validate($_POST['status'] ?? 'active');

    if ($name === '' || $username === '' || $email === '' || $password === '') {
        $error = 'Please fill in all required fields.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Please enter a valid email address.';
    } elseif (strlen($password) < 6) {
        $error = 'Password must be at least 6 characters long.';
    } elseif ($password !== $passwordConfirm) {
        $error = 'Password confirmation does not match.';
    } elseif ($roleId < 1) {
        $error = 'Please select a user role.';
    } else {
        try {
            $existing = QueryDB('SELECT COUNT(*) FROM users WHERE username = ? OR email = ?', [$username, $email])->fetchColumn();
            if ((int) $existing > 0) {
                throw new Exception('A user with that username or email address already exists.');
            }

            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

            create_portal_user([
                'username' => $username,
                'name' => $name,
                'email' => $email,
                'password' => $hashedPassword,
                'role_id' => $roleId,
                'status' => $status,
            ]);

            $roleObj = QueryDB('SELECT role_name FROM roles WHERE id = ? LIMIT 1', [$roleId])->fetch(PDO::FETCH_ASSOC);
            $roleName = $roleObj['role_name'] ?? 'User';

            $_SESSION['user_add_success'] = "User account '{$name}' ({$username}) created successfully with role '{$roleName}'!";
            header('Location: /admin/users/add.php');
            exit;
        } catch (Throwable $e) {
            $error = 'Failed to create user: ' . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <title>Add New User - Admin Panel</title>
    @include('admin.partials.links')
</head>
<body>
    <div class="wrapper">
        @include('admin.partials.sidebar')

        <div class="main-panel">
            @include('admin.partials.header')
            <div class="container">
                <div class="page-inner">
                    <div class="d-flex align-items-left flex-column flex-md-row pt-2 pb-4">
                        <div>
                            <h2 class="text-dark pb-1 fw-bold"><i class="fas fa-user-plus me-2 text-primary"></i>Add New System User</h2>
                            <p class="text-muted mb-0">Create administrative or staff login accounts with specific roles.</p>
                        </div>
                        <div class="ms-md-auto py-2 py-md-0">
                            <a href="{{ url('/admin/users/list.php') }}" class="btn btn-secondary fw-bold">
                                <i class="fas fa-arrow-left me-1"></i> All Users List
                            </a>
                        </div>
                    </div>

                    <?php if ($error !== ''): ?>
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <i class="fas fa-exclamation-triangle me-1"></i> <?php echo htmlspecialchars($error); ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    <?php endif; ?>
                    <?php if ($success !== ''): ?>
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            <i class="fas fa-check-circle me-1"></i> <?php echo htmlspecialchars($success); ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    <?php endif; ?>

                    <div class="card shadow-sm">
                        <div class="card-header bg-primary text-white">
                            <h5 class="card-title mb-0"><i class="fas fa-id-badge me-2"></i>User Details & Credentials</h5>
                        </div>
                        <div class="card-body p-4">
                            <form method="POST">
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label for="name" class="form-label fw-bold">Full Name <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control" id="name" name="name" placeholder="e.g. Samuel Johnson" value="<?php echo htmlspecialchars($_POST['name'] ?? ''); ?>" required>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label for="username" class="form-label fw-bold">Username <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control" id="username" name="username" placeholder="e.g. samuel.johnson" value="<?php echo htmlspecialchars($_POST['username'] ?? ''); ?>" required>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label for="email" class="form-label fw-bold">Email Address <span class="text-danger">*</span></label>
                                        <input type="email" class="form-control" id="email" name="email" placeholder="user@school.com" value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>" required>
                                    </div>
                                    <div class="col-md-3 mb-3">
                                        <label for="role_id" class="form-label fw-bold">Assign Role <span class="text-danger">*</span></label>
                                        <select class="form-select" id="role_id" name="role_id" required>
                                            <option value="">-- Select Role --</option>
                                            <?php foreach ($roles as $r): ?>
                                                <option value="<?php echo (int) $r['id']; ?>" <?php echo (int) ($_POST['role_id'] ?? 0) === (int) $r['id'] ? 'selected' : ''; ?>>
                                                    <?php echo htmlspecialchars($r['role_name']); ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                    <div class="col-md-3 mb-3">
                                        <label for="status" class="form-label fw-bold">Account Status</label>
                                        <select class="form-select" id="status" name="status">
                                            <option value="active" <?php echo ($_POST['status'] ?? 'active') === 'active' ? 'selected' : ''; ?>>Active</option>
                                            <option value="dismissed" <?php echo ($_POST['status'] ?? '') === 'dismissed' ? 'selected' : ''; ?>>Dismissed / Inactive</option>
                                        </select>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label for="password" class="form-label fw-bold">Password <span class="text-danger">*</span></label>
                                        <input type="password" class="form-control" id="password" name="password" placeholder="Minimum 6 characters" required>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label for="password_confirmation" class="form-label fw-bold">Confirm Password <span class="text-danger">*</span></label>
                                        <input type="password" class="form-control" id="password_confirmation" name="password_confirmation" placeholder="Re-enter password" required>
                                    </div>
                                </div>

                                <hr class="my-4">

                                <div class="d-flex justify-content-end gap-2">
                                    <a href="{{ url('/admin/users/list.php') }}" class="btn btn-light border btn-lg">Cancel</a>
                                    <button type="submit" class="btn btn-primary btn-lg px-4 fw-bold">
                                        <i class="fas fa-user-plus me-1"></i> Create User Account
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
            @include('admin.partials.footer')
        </div>
    </div>
</body>
</html>
