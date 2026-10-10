<?php
require_once base_path('db/config.php');
require_once base_path('db/functions.php');

if (!isset($_SESSION['adid'])) {
    header('Location: /admin/login.php');
    exit;
}

$search = trim((string) ($_GET['search'] ?? ''));
$roleFilter = (int) ($_GET['role_id'] ?? 0);
$statusFilter = trim((string) ($_GET['status'] ?? ''));

$success = '';
$error = '';

if (isset($_SESSION['user_success'])) {
    $success = (string) $_SESSION['user_success'];
    unset($_SESSION['user_success']);
}
if (isset($_SESSION['user_error'])) {
    $error = (string) $_SESSION['user_error'];
    unset($_SESSION['user_error']);
}

// Handle Delete User Action
if (isset($_POST['action']) && $_POST['action'] === 'delete_user') {
    $deleteId = (int) ($_POST['user_id'] ?? 0);
    if ($deleteId > 0 && $deleteId !== (int) $_SESSION['adid']) {
        try {
            QueryDB('DELETE FROM users WHERE id = ?', [$deleteId]);
            $_SESSION['user_success'] = 'User deleted successfully.';
            header('Location: /admin/users/list.php');
            exit;
        } catch (Throwable $e) {
            $_SESSION['user_error'] = 'Failed to delete user: ' . $e->getMessage();
            header('Location: /admin/users/list.php');
            exit;
        }
    }
}

// Handle Toggle Status Action
if (isset($_POST['action']) && $_POST['action'] === 'toggle_status') {
    $userId = (int) ($_POST['user_id'] ?? 0);
    $newStatus = trim((string) ($_POST['new_status'] ?? 'active'));
    if ($userId > 0) {
        try {
            QueryDB('UPDATE users SET status = ?, updated_at = NOW() WHERE id = ?', [$newStatus, $userId]);
            $_SESSION['user_success'] = 'User status updated successfully.';
            header('Location: /admin/users/list.php');
            exit;
        } catch (Throwable $e) {
            $_SESSION['user_error'] = 'Failed to update user status: ' . $e->getMessage();
            header('Location: /admin/users/list.php');
            exit;
        }
    }
}

// Fetch all roles for dropdown filter
$roles = QueryDB('SELECT * FROM roles ORDER BY role_name ASC')->fetchAll();

// Build Query
$searchParam = '%' . $search . '%';
$whereClause = "WHERE (1=1)";
$params = [];

if ($search !== '') {
    $whereClause .= " AND (u.name LIKE ? OR u.username LIKE ? OR u.email LIKE ?)";
    $params[] = $searchParam;
    $params[] = $searchParam;
    $params[] = $searchParam;
}

if ($roleFilter > 0) {
    $whereClause .= " AND u.role_id = ?";
    $params[] = $roleFilter;
}

if ($statusFilter !== '') {
    $whereClause .= " AND u.status = ?";
    $params[] = $statusFilter;
}

$users = QueryDB(
    "SELECT u.*, r.role_name, r.description AS role_description
     FROM users u
     LEFT JOIN roles r ON u.role_id = r.id
     {$whereClause}
     ORDER BY u.created_at DESC",
    $params
)->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <title>User Management - Admin Panel</title>
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
                            <h2 class="text-dark pb-1 fw-bold"><i class="fas fa-users-cog me-2 text-primary"></i>User Management</h2>
                            <p class="text-muted mb-0">Manage system users, assigned user roles, and access credentials.</p>
                        </div>
                        <div class="ms-md-auto py-2 py-md-0 d-flex gap-2">
                            <a href="{{ url('/admin/users/add.php') }}" class="btn btn-primary fw-bold">
                                <i class="fas fa-user-plus me-1"></i> Add New User
                            </a>
                            <a href="{{ url('/admin/users/roles.php') }}" class="btn btn-secondary fw-bold">
                                <i class="fas fa-user-shield me-1"></i> Roles & Permissions
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

                    <div class="card shadow-sm mb-4">
                        <div class="card-body">
                            <form method="GET" class="row g-3 align-items-center">
                                <div class="col-md-4">
                                    <label class="form-label small fw-bold">Search Keyword</label>
                                    <input type="text" name="search" class="form-control" placeholder="Search name, username, or email..." value="<?php echo htmlspecialchars($search); ?>">
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label small fw-bold">User Role</label>
                                    <select name="role_id" class="form-select">
                                        <option value="0">All Roles</option>
                                        <?php foreach ($roles as $r): ?>
                                            <option value="<?php echo (int) $r['id']; ?>" <?php echo $roleFilter === (int) $r['id'] ? 'selected' : ''; ?>>
                                                <?php echo htmlspecialchars($r['role_name']); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label small fw-bold">Status</label>
                                    <select name="status" class="form-select">
                                        <option value="">All Statuses</option>
                                        <option value="active" <?php echo $statusFilter === 'active' ? 'selected' : ''; ?>>Active</option>
                                        <option value="dismissed" <?php echo $statusFilter === 'dismissed' ? 'selected' : ''; ?>>Dismissed / Inactive</option>
                                    </select>
                                </div>
                                <div class="col-md-2 d-flex align-items-end" style="margin-top: 32px;">
                                    <button type="submit" class="btn btn-primary w-100 fw-bold me-2"><i class="fas fa-search me-1"></i> Filter</button>
                                    <a href="{{ url('/admin/users/list.php') }}" class="btn btn-light border" title="Reset Filters"><i class="fas fa-undo"></i></a>
                                </div>
                            </form>
                        </div>
                    </div>

                    <div class="card shadow-sm">
                        <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                            <h5 class="card-title mb-0"><i class="fas fa-list me-2"></i>System Users List</h5>
                            <span class="badge bg-white text-primary fw-bold"><?php echo count($users); ?> Total Users</span>
                        </div>
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-hover table-striped mb-0 align-middle">
                                    <thead class="table-light">
                                        <tr>
                                            <th style="width: 60px;">S/N</th>
                                            <th>User Info</th>
                                            <th>Username</th>
                                            <th>Role</th>
                                            <th>Status</th>
                                            <th>Registered</th>
                                            <th class="text-end" style="width: 220px;">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if (empty($users)): ?>
                                            <tr>
                                                <td colspan="7" class="text-center py-4 text-muted">No system users found matching the specified filters.</td>
                                            </tr>
                                        <?php else: ?>
                                            <?php $sn = 1; foreach ($users as $user): ?>
                                                <tr>
                                                    <td><?php echo $sn++; ?></td>
                                                    <td>
                                                        <div class="fw-bold text-dark"><?php echo htmlspecialchars($user['name']); ?></div>
                                                        <div class="small text-muted"><?php echo htmlspecialchars($user['email']); ?></div>
                                                    </td>
                                                    <td><code><?php echo htmlspecialchars($user['username'] ?? 'N/A'); ?></code></td>
                                                    <td>
                                                        <?php 
                                                        $rName = $user['role_name'] ?? 'User';
                                                        $badgeClass = match (strtolower($rName)) {
                                                            'admin' => 'bg-danger',
                                                            'teacher' => 'bg-info',
                                                            'accountant' => 'bg-success',
                                                            'student' => 'bg-secondary',
                                                            default => 'bg-primary',
                                                        };
                                                        ?>
                                                        <span class="badge <?php echo $badgeClass; ?>"><?php echo htmlspecialchars($rName); ?></span>
                                                    </td>
                                                    <td>
                                                        <?php if ($user['status'] === 'active'): ?>
                                                            <span class="badge bg-success"><i class="fas fa-check-circle me-1"></i>Active</span>
                                                        <?php else: ?>
                                                            <span class="badge bg-danger"><i class="fas fa-ban me-1"></i>Dismissed</span>
                                                        <?php endif; ?>
                                                    </td>
                                                    <td><small><?php echo date('M d, Y', strtotime($user['created_at'])); ?></small></td>
                                                    <td class="text-end">
                                                        <a href="{{ url('/admin/users/edit.php?id=' . $user['id']) }}" class="btn btn-warning btn-sm fw-bold">
                                                            <i class="fas fa-edit me-1"></i> Edit
                                                        </a>
                                                        
                                                        <form method="POST" class="d-inline" onsubmit="return confirm('Toggle status for this user?');">
                                                            <input type="hidden" name="action" value="toggle_status">
                                                            <input type="hidden" name="user_id" value="<?php echo (int) $user['id']; ?>">
                                                            <input type="hidden" name="new_status" value="<?php echo $user['status'] === 'active' ? 'dismissed' : 'active'; ?>">
                                                            <button type="submit" class="btn btn-outline-secondary btn-sm" title="Toggle Active / Dismissed">
                                                                <i class="fas fa-power-off"></i>
                                                            </button>
                                                        </form>

                                                        <?php if ((int) $user['id'] !== (int) $_SESSION['adid']): ?>
                                                            <form method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to permanently delete this user account?');">
                                                                <input type="hidden" name="action" value="delete_user">
                                                                <input type="hidden" name="user_id" value="<?php echo (int) $user['id']; ?>">
                                                                <button type="submit" class="btn btn-danger btn-sm" title="Delete User">
                                                                    <i class="fas fa-trash"></i>
                                                                </button>
                                                            </form>
                                                        <?php endif; ?>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
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
