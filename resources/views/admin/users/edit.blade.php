<?php
require_once base_path('db/config.php');
require_once base_path('db/functions.php');

if (!isset($_SESSION['adid'])) {
    header('Location: /admin/login.php');
    exit;
}

$userId = (int) ($_GET['id'] ?? $_POST['user_id'] ?? 0);
if ($userId < 1) {
    header('Location: /admin/users/list.php');
    exit;
}

$user = QueryDB('SELECT * FROM users WHERE id = ? LIMIT 1', [$userId])->fetch(PDO::FETCH_ASSOC);
if (!$user) {
    header('Location: /admin/users/list.php');
    exit;
}

$roles = QueryDB('SELECT * FROM roles ORDER BY role_name ASC')->fetchAll();

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = validate($_POST['name'] ?? '');
    $username = validate($_POST['username'] ?? '');
    $email = validate($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $roleId = (int) ($_POST['role_id'] ?? 0);
    $status = validate($_POST['status'] ?? 'active');

    if ($name === '' || $username === '' || $email === '') {
        $error = 'Please fill in all required fields.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Please enter a valid email address.';
    } elseif ($roleId < 1) {
        $error = 'Please select a user role.';
    } else {
        try {
            $existing = QueryDB('SELECT COUNT(*) FROM users WHERE (username = ? OR email = ?) AND id != ?', [$username, $email, $userId])->fetchColumn();
            if ((int) $existing > 0) {
                throw new Exception('Another user with that username or email address already exists.');
            }

            if (trim($password) !== '') {
                if (strlen($password) < 6) {
                    throw new Exception('New password must be at least 6 characters long.');
                }
                $hashed = password_hash($password, PASSWORD_DEFAULT);
                QueryDB(
                    'UPDATE users SET name = ?, username = ?, email = ?, password = ?, role_id = ?, status = ?, updated_at = NOW() WHERE id = ?',
                    [$name, $username, $email, $hashed, $roleId, $status, $userId]
                );
            } else {
                QueryDB(
                    'UPDATE users SET name = ?, username = ?, email = ?, role_id = ?, status = ?, updated_at = NOW() WHERE id = ?',
                    [$name, $username, $email, $roleId, $status, $userId]
                );
            }

            $user = QueryDB('SELECT * FROM users WHERE id = ? LIMIT 1', [$userId])->fetch(PDO::FETCH_ASSOC);
            $success = 'User account updated successfully!';
        } catch (Throwable $e) {
            $error = 'Failed to update user: ' . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <title>Edit User - Admin Panel</title>
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
                            <h2 class="text-dark pb-1 fw-bold"><i class="fas fa-user-edit me-2 text-warning"></i>Edit User Account</h2>
                            <p class="text-muted mb-0">Update account details, change role, or reset password for <?php echo htmlspecialchars($user['name']); ?>.</p>
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
                        <div class="card-header bg-warning text-dark">
                            <h5 class="card-title mb-0 fw-bold"><i class="fas fa-user-cog me-2"></i>Update User Profile</h5>
                        </div>
                        <div class="card-body p-4">
                            <form method="POST">
                                <input type="hidden" name="user_id" value="<?php echo (int) $user['id']; ?>">

                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label for="name" class="form-label fw-bold">Full Name <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control" id="name" name="name" value="<?php echo htmlspecialchars($_POST['name'] ?? $user['name']); ?>" required>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label for="username" class="form-label fw-bold">Username <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control" id="username" name="username" value="<?php echo htmlspecialchars($_POST['username'] ?? $user['username']); ?>" required>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label for="email" class="form-label fw-bold">Email Address <span class="text-danger">*</span></label>
                                        <input type="email" class="form-control" id="email" name="email" value="<?php echo htmlspecialchars($_POST['email'] ?? $user['email']); ?>" required>
                                    </div>
                                    <div class="col-md-3 mb-3">
                                        <label for="role_id" class="form-label fw-bold">Assign Role <span class="text-danger">*</span></label>
                                        <select class="form-select" id="role_id" name="role_id" required>
                                            <option value="">-- Select Role --</option>
                                            <?php 
                                            $currRole = (int) ($_POST['role_id'] ?? $user['role_id']);
                                            foreach ($roles as $r):
                                            ?>
                                                <option value="<?php echo (int) $r['id']; ?>" <?php echo $currRole === (int) $r['id'] ? 'selected' : ''; ?>>
                                                    <?php echo htmlspecialchars($r['role_name']); ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                    <div class="col-md-3 mb-3">
                                        <label for="status" class="form-label fw-bold">Account Status</label>
                                        <?php $currStatus = $_POST['status'] ?? $user['status']; ?>
                                        <select class="form-select" id="status" name="status">
                                            <option value="active" <?php echo $currStatus === 'active' ? 'selected' : ''; ?>>Active</option>
                                            <option value="dismissed" <?php echo $currStatus === 'dismissed' ? 'selected' : ''; ?>>Dismissed / Inactive</option>
                                        </select>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-12 mb-3">
                                        <label for="password" class="form-label fw-bold">Reset Password <small class="text-muted">(Leave blank to keep existing password)</small></label>
                                        <input type="password" class="form-control" id="password" name="password" placeholder="Enter new password if changing">
                                    </div>
                                </div>

                                <hr class="my-4">

                                <div class="d-flex justify-content-end gap-2">
                                    <a href="{{ url('/admin/users/list.php') }}" class="btn btn-light border btn-lg">Cancel</a>
                                    <button type="submit" class="btn btn-warning btn-lg px-4 fw-bold">
                                        <i class="fas fa-save me-1"></i> Update User Account
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
