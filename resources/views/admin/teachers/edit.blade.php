<?php
require_once base_path('db/config.php');
require_once base_path('db/functions.php');

if (!isset($_SESSION['adid'])) {
    header('Location: /admin/login.php');
    exit;
}

$teacherId = (int) ($_GET['id'] ?? 0);
$teacher = QueryDB(
    'SELECT t.*, u.username, u.status AS user_status FROM teachers t JOIN users u ON t.user_link = u.id WHERE t.id = ?',
    [$teacherId]
)->fetch(PDO::FETCH_ASSOC);

if (!$teacher) {
    header('Location: /admin/teachers/list.php');
    exit;
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $firstName = validate($_POST['first_name'] ?? '');
        $lastName = validate($_POST['last_name'] ?? '');
        $email = validate($_POST['email'] ?? '');
        $phone = validate($_POST['phone'] ?? '');
        $qualification = validate($_POST['qualification'] ?? '');
        $specialization = validate($_POST['specialization'] ?? '');
        $employmentDate = validate($_POST['employment_date'] ?? '');
        $username = validate($_POST['username'] ?? '');
        $status = validate($_POST['status'] ?? 'active');
        $password = $_POST['password'] ?? '';

        if ($firstName === '' || $lastName === '' || $email === '' || $username === '') {
            throw new Exception('Please fill in all required teacher details.');
        }

        $existing = QueryDB(
            'SELECT COUNT(*) FROM users WHERE (username = ? OR email = ?) AND id != ?',
            [$username, $email, $teacher['user_link']]
        )->fetchColumn();

        if ((int) $existing > 0) {
            throw new Exception('That username or email address is already in use.');
        }

        $pdo->beginTransaction();

        QueryDB(
            'UPDATE users SET username = ?, email = ?, status = ?, updated_at = NOW() WHERE id = ?',
            [$username, $email, $status, $teacher['user_link']]
        );

        QueryDB(
            'UPDATE teachers SET first_name = ?, last_name = ?, email = ?, phone = ?, qualification = ?, specialization = ?, employment_date = ?, status = ?, updated_at = NOW() WHERE id = ?',
            [$firstName, $lastName, $email, $phone ?: null, $qualification ?: null, $specialization ?: null, $employmentDate ?: null, $status, $teacherId]
        );

        if ($password !== '') {
            QueryDB(
                'UPDATE users SET password = ?, updated_at = NOW() WHERE id = ?',
                [password_hash($password, PASSWORD_DEFAULT), $teacher['user_link']]
            );
        }

        $pdo->commit();

        $teacher = QueryDB(
            'SELECT t.*, u.username, u.status AS user_status FROM teachers t JOIN users u ON t.user_link = u.id WHERE t.id = ?',
            [$teacherId]
        )->fetch(PDO::FETCH_ASSOC);

        $success = 'Teacher updated successfully.';
    } catch (Throwable $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }

        $error = 'Failed to update teacher: ' . $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <title>Edit Teacher</title>
    @include('admin.partials.links')
</head>
<body>
    <div class="wrapper">
        @include('admin.partials.sidebar')

        <div class="main-panel">
            @include('admin.partials.header')
            <div class="container">
                <div class="page-inner">
                    <div class="d-flex align-items-left flex-column flex-md-row">
                        <h2 class="text-dark pb-2 fw-bold">Edit Teacher</h2>
                        <div class="ml-md-auto py-2 py-md-0">
                            <a href="{{ url('/admin/teachers/view.php?id=' . $teacherId) }}" class="btn btn-info">View Profile</a>
                        </div>
                    </div>

                    <?php if ($error !== ''): ?>
                        <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
                    <?php endif; ?>
                    <?php if ($success !== ''): ?>
                        <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
                    <?php endif; ?>

                    <div class="card">
                        <div class="card-header">
                            <div class="card-title">Teacher Information</div>
                            <div class="card-category">Teacher ID: <?php echo htmlspecialchars($teacher['teacher_id']); ?></div>
                        </div>
                        <div class="card-body">
                            <form method="POST">
                                <div class="row">
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label for="first_name">First Name</label>
                                            <input type="text" class="form-control" id="first_name" name="first_name" value="<?php echo htmlspecialchars($teacher['first_name']); ?>" required>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label for="last_name">Last Name</label>
                                            <input type="text" class="form-control" id="last_name" name="last_name" value="<?php echo htmlspecialchars($teacher['last_name']); ?>" required>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label for="employment_date">Employment Date</label>
                                            <input type="date" class="form-control" id="employment_date" name="employment_date" value="<?php echo htmlspecialchars((string) ($teacher['employment_date'] ?? '')); ?>">
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label for="username">Login Username</label>
                                            <input type="text" class="form-control" id="username" name="username" value="<?php echo htmlspecialchars($teacher['username']); ?>" required>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label for="password">New Password</label>
                                            <input type="password" class="form-control" id="password" name="password" placeholder="Leave blank to keep current password">
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label for="status">Status</label>
                                            <select class="form-control" id="status" name="status">
                                                <option value="active" <?php echo ($teacher['user_status'] ?? '') === 'active' ? 'selected' : ''; ?>>Active</option>
                                                <option value="inactive" <?php echo ($teacher['user_status'] ?? '') === 'inactive' ? 'selected' : ''; ?>>Inactive</option>
                                                <option value="suspended" <?php echo ($teacher['user_status'] ?? '') === 'suspended' ? 'selected' : ''; ?>>Suspended</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="email">Email</label>
                                            <input type="email" class="form-control" id="email" name="email" value="<?php echo htmlspecialchars($teacher['email']); ?>" required>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="phone">Phone</label>
                                            <input type="text" class="form-control" id="phone" name="phone" value="<?php echo htmlspecialchars((string) ($teacher['phone'] ?? '')); ?>">
                                        </div>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label for="qualification">Qualification</label>
                                    <textarea class="form-control" id="qualification" name="qualification" rows="3"><?php echo htmlspecialchars((string) ($teacher['qualification'] ?? '')); ?></textarea>
                                </div>
                                <div class="form-group">
                                    <label for="specialization">Specialization</label>
                                    <textarea class="form-control" id="specialization" name="specialization" rows="3"><?php echo htmlspecialchars((string) ($teacher['specialization'] ?? '')); ?></textarea>
                                </div>
                                <button type="submit" class="btn btn-primary">Save Changes</button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
            @include('admin.partials.footer')
</body>
</html>
