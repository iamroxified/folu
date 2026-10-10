<?php
require_once base_path('db/config.php');
require_once base_path('db/functions.php');

if (!isset($_SESSION['adid'])) {
    header('Location: /admin/login.php');
    exit;
}

$error = '';
$success = '';

if (isset($_SESSION['role_success'])) {
    $success = (string) $_SESSION['role_success'];
    unset($_SESSION['role_success']);
}
if (isset($_SESSION['role_error'])) {
    $error = (string) $_SESSION['role_error'];
    unset($_SESSION['role_error']);
}

// Handle Add Role
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'add_role') {
    $roleName = validate($_POST['role_name'] ?? '');
    $description = validate($_POST['description'] ?? '');

    if ($roleName === '') {
        $error = 'Role name is required.';
    } else {
        try {
            $existing = QueryDB('SELECT COUNT(*) FROM roles WHERE LOWER(role_name) = ?', [strtolower($roleName)])->fetchColumn();
            if ((int) $existing > 0) {
                throw new Exception('A role with that name already exists.');
            }

            QueryDB(
                'INSERT INTO roles (role_name, description, created_at, updated_at) VALUES (?, ?, NOW(), NOW())',
                [$roleName, $description ?: null]
            );

            $_SESSION['role_success'] = "New role '{$roleName}' added successfully!";
            header('Location: /admin/users/roles.php');
            exit;
        } catch (Throwable $e) {
            $error = 'Failed to add role: ' . $e->getMessage();
        }
    }
}

// Handle Edit Role Description
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'edit_role') {
    $roleId = (int) ($_POST['role_id'] ?? 0);
    $roleName = validate($_POST['role_name'] ?? '');
    $description = validate($_POST['description'] ?? '');

    if ($roleId > 0 && $roleName !== '') {
        try {
            QueryDB(
                'UPDATE roles SET role_name = ?, description = ?, updated_at = NOW() WHERE id = ?',
                [$roleName, $description ?: null, $roleId]
            );

            $_SESSION['role_success'] = "Role updated successfully!";
            header('Location: /admin/users/roles.php');
            exit;
        } catch (Throwable $e) {
            $error = 'Failed to update role: ' . $e->getMessage();
        }
    }
}

// Handle Delete Role
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'delete_role') {
    $roleId = (int) ($_POST['role_id'] ?? 0);
    $coreRoles = ['admin', 'teacher', 'accountant', 'student'];

    if ($roleId > 0) {
        $role = QueryDB('SELECT role_name FROM roles WHERE id = ? LIMIT 1', [$roleId])->fetch(PDO::FETCH_ASSOC);
        if ($role && in_array(strtolower($role['role_name']), $coreRoles, true)) {
            $_SESSION['role_error'] = "System core role '" . $role['role_name'] . "' cannot be deleted.";
        } else {
            try {
                $userCount = (int) QueryDB('SELECT COUNT(*) FROM users WHERE role_id = ?', [$roleId])->fetchColumn();
                if ($userCount > 0) {
                    throw new Exception("Cannot delete role while {$userCount} users are currently assigned to it.");
                }

                QueryDB('DELETE FROM roles WHERE id = ?', [$roleId]);
                $_SESSION['role_success'] = 'Role deleted successfully.';
            } catch (Throwable $e) {
                $_SESSION['role_error'] = 'Failed to delete role: ' . $e->getMessage();
            }
        }
        header('Location: /admin/users/roles.php');
        exit;
    }
}

$roles = QueryDB(
    'SELECT r.*, (SELECT COUNT(*) FROM users u WHERE u.role_id = r.id) AS user_count
     FROM roles r
     ORDER BY r.id ASC'
)->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <title>Roles & Permissions - Admin Panel</title>
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
                            <h2 class="text-dark pb-1 fw-bold"><i class="fas fa-user-shield me-2 text-primary"></i>User Roles & Permissions</h2>
                            <p class="text-muted mb-0">Define administrative, staff, and system access levels across the school portal.</p>
                        </div>
                        <div class="ms-md-auto py-2 py-md-0">
                            <a href="{{ url('/admin/users/list.php') }}" class="btn btn-secondary fw-bold">
                                <i class="fas fa-users me-1"></i> User List
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

                    <div class="row">
                        <!-- Left: Add New Role Form -->
                        <div class="col-md-4 mb-4">
                            <div class="card shadow-sm">
                                <div class="card-header bg-primary text-white">
                                    <h5 class="card-title mb-0"><i class="fas fa-plus-circle me-2"></i>Add New Role</h5>
                                </div>
                                <div class="card-body">
                                    <form method="POST">
                                        <input type="hidden" name="action" value="add_role">

                                        <div class="mb-3">
                                            <label for="role_name" class="form-label fw-bold">Role Title <span class="text-danger">*</span></label>
                                            <input type="text" class="form-control" id="role_name" name="role_name" placeholder="e.g. Exam Officer, Librarian, Vice Principal" required>
                                        </div>

                                        <div class="mb-3">
                                            <label for="description" class="form-label fw-bold">Description / Scope</label>
                                            <textarea class="form-control" id="description" name="description" rows="4" placeholder="Briefly describe what users assigned to this role can access..."></textarea>
                                        </div>

                                        <button type="submit" class="btn btn-primary w-100 fw-bold">
                                            <i class="fas fa-save me-1"></i> Create User Role
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>

                        <!-- Right: Roles List -->
                        <div class="col-md-8 mb-4">
                            <div class="card shadow-sm">
                                <div class="card-header bg-dark text-white d-flex justify-content-between align-items-center">
                                    <h5 class="card-title mb-0"><i class="fas fa-shield-alt me-2"></i>Existing System Roles</h5>
                                    <span class="badge bg-primary fw-bold"><?php echo count($roles); ?> Total Roles</span>
                                </div>
                                <div class="card-body p-0">
                                    <div class="table-responsive">
                                        <table class="table table-hover align-middle mb-0">
                                            <thead class="table-light">
                                                <tr>
                                                    <th style="width: 50px;">#</th>
                                                    <th>Role Name</th>
                                                    <th>Description</th>
                                                    <th>Assigned Users</th>
                                                    <th class="text-end" style="width: 140px;">Actions</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php $sn = 1; foreach ($roles as $r): ?>
                                                    <?php $isCore = in_array(strtolower($r['role_name']), ['admin', 'teacher', 'accountant', 'student'], true); ?>
                                                    <tr>
                                                        <td><?php echo $sn++; ?></td>
                                                        <td>
                                                            <span class="fw-bold text-dark"><?php echo htmlspecialchars($r['role_name']); ?></span>
                                                            <?php if ($isCore): ?>
                                                                <span class="badge bg-info ms-1 small">System Core</span>
                                                            <?php endif; ?>
                                                        </td>
                                                        <td><small class="text-muted"><?php echo htmlspecialchars($r['description'] ?? 'No description set'); ?></small></td>
                                                        <td><span class="badge bg-secondary rounded-pill"><?php echo (int) $r['user_count']; ?> Users</span></td>
                                                        <td class="text-end">
                                                            <!-- Edit Modal Button -->
                                                            <button type="button" class="btn btn-warning btn-sm" data-bs-toggle="modal" data-bs-target="#editRoleModal<?php echo $r['id']; ?>" title="Edit Role">
                                                                <i class="fas fa-edit"></i>
                                                            </button>

                                                            <?php if (!$isCore): ?>
                                                                <form method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this role?');">
                                                                    <input type="hidden" name="action" value="delete_role">
                                                                    <input type="hidden" name="role_id" value="<?php echo (int) $r['id']; ?>">
                                                                    <button type="submit" class="btn btn-danger btn-sm" title="Delete Role">
                                                                        <i class="fas fa-trash"></i>
                                                                    </button>
                                                                </form>
                                                            <?php endif; ?>
                                                        </td>
                                                    </tr>

                                                    <!-- Edit Role Modal -->
                                                    <div class="modal fade" id="editRoleModal<?php echo $r['id']; ?>" tabindex="-1" aria-hidden="true">
                                                        <div class="modal-dialog">
                                                            <div class="modal-content">
                                                                <form method="POST">
                                                                    <input type="hidden" name="action" value="edit_role">
                                                                    <input type="hidden" name="role_id" value="<?php echo (int) $r['id']; ?>">
                                                                    
                                                                    <div class="modal-header bg-warning">
                                                                        <h5 class="modal-title fw-bold text-dark"><i class="fas fa-edit me-1"></i> Edit Role: <?php echo htmlspecialchars($r['role_name']); ?></h5>
                                                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                                    </div>
                                                                    <div class="modal-body">
                                                                        <div class="mb-3">
                                                                            <label class="form-label fw-bold">Role Title</label>
                                                                            <input type="text" name="role_name" class="form-control" value="<?php echo htmlspecialchars($r['role_name']); ?>" <?php echo $isCore ? 'readonly' : 'required'; ?>>
                                                                        </div>
                                                                        <div class="mb-3">
                                                                            <label class="form-label fw-bold">Description / Scope</label>
                                                                            <textarea name="description" class="form-control" rows="3"><?php echo htmlspecialchars($r['description'] ?? ''); ?></textarea>
                                                                        </div>
                                                                    </div>
                                                                    <div class="modal-footer">
                                                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                                                        <button type="submit" class="btn btn-warning fw-bold">Save Changes</button>
                                                                    </div>
                                                                </form>
                                                            </div>
                                                        </div>
                                                    </div>
                                                <?php endforeach; ?>
                                            </tbody>
                                        </table>
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
