<?php
require_once base_path('db/config.php');
require_once base_path('db/functions.php');

if (!isset($_SESSION['adid'])) {
    header('Location: /admin/login.php');
    exit;
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $firstName = validate($_POST['first_name'] ?? '');
    $lastName = validate($_POST['last_name'] ?? '');
    $email = validate($_POST['email'] ?? '');
    $phone = validate($_POST['phone'] ?? '');
    $qualification = validate($_POST['qualification'] ?? '');
    $specialization = validate($_POST['specialization'] ?? '');
    $employmentDate = validate($_POST['employment_date'] ?? '');
    $username = validate($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    $status = validate($_POST['status'] ?? 'active');

    if ($firstName === '' || $lastName === '' || $email === '' || $username === '' || $password === '') {
        $error = 'Please fill in all required teacher details.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Please enter a valid teacher email address.';
    } else {
        try {
            $existing = QueryDB('SELECT COUNT(*) FROM users WHERE username = ? OR email = ?', [$username, $email])->fetchColumn();

            if ((int) $existing > 0) {
                throw new Exception('That username or email address is already in use.');
            }

            $pdo->beginTransaction();

            $teacherId = generate_teacher_id();
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

            QueryDB(
                'INSERT INTO users (username, password, email, role, status, created_at, updated_at) VALUES (?, ?, ?, ?, ?, NOW(), NOW())',
                [$username, $hashedPassword, $email, 'teacher', $status]
            );

            $userId = (int) $pdo->lastInsertId();

            QueryDB(
                'INSERT INTO teachers (user_link, teacher_id, first_name, last_name, email, phone, qualification, specialization, employment_date, status, created_at, updated_at)
                 VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW())',
                [$userId, $teacherId, $firstName, $lastName, $email, $phone ?: null, $qualification ?: null, $specialization ?: null, $employmentDate ?: null, $status]
            );

            $pdo->commit();
            $success = 'Teacher created successfully. Teacher ID: ' . $teacherId;
        } catch (Throwable $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }

            $error = 'Failed to create teacher: ' . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <title>Add Teacher</title>
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
                        <h2 class="text-dark pb-2 fw-bold">Add Teacher</h2>
                        <div class="ml-md-auto py-2 py-md-0">
                            <a href="{{ url('/admin/teachers/list.php') }}" class="btn btn-secondary">All Teachers</a>
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
                        </div>
                        <div class="card-body">
                            <form method="POST">
                                <div class="row">
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label for="first_name">First Name</label>
                                            <input type="text" class="form-control" id="first_name" name="first_name" required>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label for="last_name">Last Name</label>
                                            <input type="text" class="form-control" id="last_name" name="last_name" required>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label for="employment_date">Employment Date</label>
                                            <input type="date" class="form-control" id="employment_date" name="employment_date">
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label for="username">Login Username</label>
                                            <input type="text" class="form-control" id="username" name="username" required>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label for="password">Password</label>
                                            <input type="password" class="form-control" id="password" name="password" required>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label for="status">Status</label>
                                            <select class="form-control" id="status" name="status">
                                                <option value="active">Active</option>
                                                <option value="inactive">Inactive</option>
                                                <option value="suspended">Suspended</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="email">Email</label>
                                            <input type="email" class="form-control" id="email" name="email" required>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="phone">Phone</label>
                                            <input type="text" class="form-control" id="phone" name="phone">
                                        </div>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label for="qualification">Qualification</label>
                                    <textarea class="form-control" id="qualification" name="qualification" rows="3"></textarea>
                                </div>
                                <div class="form-group">
                                    <label for="specialization">Specialization</label>
                                    <textarea class="form-control" id="specialization" name="specialization" rows="3" placeholder="Primary class teacher, Basic Science, English Language, Mathematics"></textarea>
                                </div>
                                <button type="submit" class="btn btn-primary">Create Teacher</button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
            @include('admin.partials.footer')
</body>
</html>
