<?php
require_once base_path('db/config.php');
require_once base_path('db/functions.php');

if (!isset($_SESSION['adid'])) {
    header('Location: /admin/login.php');
    exit;
}

$error = '';
$success = '';

$generatedId = generate_teacher_id();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $firstName = validate($_POST['first_name'] ?? '');
    $lastName = validate($_POST['last_name'] ?? '');
    $email = validate($_POST['email'] ?? '');
    $phone = validate($_POST['phone'] ?? '');
    $qualification = validate($_POST['qualification'] ?? '');
    $specialization = validate($_POST['specialization'] ?? '');
    $employmentDate = validate($_POST['employment_date'] ?? date('Y-m-d'));
    
    $customTeacherId = validate($_POST['teacher_id'] ?? '');
    $teacherId = $customTeacherId !== '' ? $customTeacherId : $generatedId;
    
    $username = validate($_POST['username'] ?? '');
    if ($username === '') {
        $username = $teacherId;
    }

    $password = $_POST['password'] ?? 'password';
    if (trim($password) === '') {
        $password = 'password';
    }

    $status = validate($_POST['status'] ?? 'active');

    if ($firstName === '' || $lastName === '' || $email === '') {
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

            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

            $userId = create_portal_user([
                'username' => $username,
                'name' => trim($firstName . ' ' . $lastName),
                'email' => $email,
                'password' => $hashedPassword,
                'role_name' => 'teacher',
                'status' => $status,
            ]);

            // Insert into staff base table (which automatically populates the teachers view)
            QueryDB(
                'INSERT INTO staff (staff_number, first_name, last_name, email, phone, position, qualification, specialization, hire_date, salary, date_of_birth, gender, status, created_at, updated_at)
                 VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW())',
                [
                    $teacherId,
                    $firstName,
                    $lastName,
                    $email,
                    $phone ?: null,
                    'Teacher',
                    $qualification ?: null,
                    $specialization ?: null,
                    $employmentDate ?: date('Y-m-d'),
                    0.00,
                    '1990-01-01',
                    'other',
                    $status
                ]
            );

            $pdo->commit();
            $success = 'Teacher created successfully! Teacher ID & Username: ' . $teacherId . ' | Default Password: ' . $password;
            $generatedId = generate_teacher_id(); // Refresh for next creation
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
    <title>Add Teacher - Admin Panel</title>
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
                            <h2 class="text-dark pb-1 fw-bold"><i class="fas fa-user-plus me-2 text-primary"></i>Add New Teacher</h2>
                            <p class="text-muted mb-0">Create a teacher account with auto-assigned Teacher ID & default login details.</p>
                        </div>
                        <div class="ms-md-auto py-2 py-md-0">
                            <a href="{{ url('/admin/teachers/list.php') }}" class="btn btn-secondary fw-bold">
                                <i class="fas fa-list me-1"></i> All Teachers List
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
                            <h5 class="card-title mb-0"><i class="fas fa-id-card me-2"></i>Teacher Registration & Credentials</h5>
                        </div>
                        <div class="card-body p-4">
                            <form method="POST">
                                <!-- Teacher ID & Credentials Banner -->
                                <div class="p-3 bg-light rounded border mb-4">
                                    <h6 class="text-uppercase text-muted fw-bold mb-3"><i class="fas fa-key me-1 text-warning"></i> Auto-Generated Login Credentials</h6>
                                    <div class="row">
                                        <div class="col-md-4 mb-3 mb-md-0">
                                            <label for="teacher_id" class="form-label fw-bold">Teacher ID</label>
                                            <input type="text" class="form-control bg-white font-monospace fw-bold text-primary border-primary" id="teacher_id" name="teacher_id" value="<?php echo htmlspecialchars($generatedId); ?>" readonly>
                                        </div>
                                        <div class="col-md-4 mb-3 mb-md-0">
                                            <label for="username" class="form-label fw-bold">Login Username</label>
                                            <input type="text" class="form-control bg-white font-monospace fw-bold text-dark border-dark" id="username" name="username" value="<?php echo htmlspecialchars($generatedId); ?>" required>
                                            <small class="form-text text-muted">Teacher ID serves as login username by default.</small>
                                        </div>
                                        <div class="col-md-4">
                                            <label for="password" class="form-label fw-bold">Default Password</label>
                                            <input type="text" class="form-control bg-white font-monospace fw-bold text-success border-success" id="password" name="password" value="password" required>
                                            <small class="form-text text-muted">Default password set to 'password'.</small>
                                        </div>
                                    </div>
                                </div>

                                <!-- Personal Information -->
                                <div class="row">
                                    <div class="col-md-4 mb-3">
                                        <label for="first_name" class="form-label fw-bold">First Name <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control" id="first_name" name="first_name" placeholder="e.g. John" value="<?php echo htmlspecialchars($_POST['first_name'] ?? ''); ?>" required>
                                    </div>
                                    <div class="col-md-4 mb-3">
                                        <label for="last_name" class="form-label fw-bold">Last Name <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control" id="last_name" name="last_name" placeholder="e.g. Doe" value="<?php echo htmlspecialchars($_POST['last_name'] ?? ''); ?>" required>
                                    </div>
                                    <div class="col-md-4 mb-3">
                                        <label for="employment_date" class="form-label fw-bold">Employment Date</label>
                                        <input type="date" class="form-control" id="employment_date" name="employment_date" value="<?php echo htmlspecialchars($_POST['employment_date'] ?? date('Y-m-d')); ?>">
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-4 mb-3">
                                        <label for="email" class="form-label fw-bold">Email Address <span class="text-danger">*</span></label>
                                        <input type="email" class="form-control" id="email" name="email" placeholder="teacher@school.com" value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>" required>
                                    </div>
                                    <div class="col-md-4 mb-3">
                                        <label for="phone" class="form-label fw-bold">Phone Number</label>
                                        <input type="text" class="form-control" id="phone" name="phone" placeholder="08012345678" value="<?php echo htmlspecialchars($_POST['phone'] ?? ''); ?>">
                                    </div>
                                    <div class="col-md-4 mb-3">
                                        <label for="status" class="form-label fw-bold">Account Status</label>
                                        <select class="form-select" id="status" name="status">
                                            <option value="active" <?php echo ($_POST['status'] ?? 'active') === 'active' ? 'selected' : ''; ?>>Active</option>
                                            <option value="inactive" <?php echo ($_POST['status'] ?? '') === 'inactive' ? 'selected' : ''; ?>>Inactive</option>
                                            <option value="suspended" <?php echo ($_POST['status'] ?? '') === 'suspended' ? 'selected' : ''; ?>>Suspended</option>
                                        </select>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label for="qualification" class="form-label fw-bold">Academic Qualification <span class="text-danger">*</span></label>
                                        <select class="form-select" id="qualification" name="qualification" required>
                                            <option value="">-- Select Academic Qualification --</option>
                                            <?php 
                                            $quals = [
                                                'B.Ed. (Bachelor of Education)',
                                                "B.Sc. / B.A. (Bachelor's Degree)",
                                                'M.Ed. (Master of Education)',
                                                "M.Sc. / M.A. (Master's Degree)",
                                                'Ph.D. (Doctor of Philosophy)',
                                                'NCE (National Certificate in Education)',
                                                'PGDE (Postgraduate Diploma in Education)',
                                                'HND (Higher National Diploma)',
                                                'OND (Ordinary National Diploma)',
                                                'SSCE / WASSCE',
                                                'Other Professional Certification',
                                            ];
                                            $selQual = $_POST['qualification'] ?? '';
                                            foreach ($quals as $q):
                                            ?>
                                                <option value="<?php echo htmlspecialchars($q); ?>" <?php echo $selQual === $q ? 'selected' : ''; ?>><?php echo htmlspecialchars($q); ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label for="specialization" class="form-label fw-bold">Specialization / Subjects Taught</label>
                                        <input type="text" class="form-control" id="specialization" name="specialization" placeholder="e.g. Mathematics, Basic Science, Senior English" value="<?php echo htmlspecialchars($_POST['specialization'] ?? ''); ?>">
                                    </div>
                                </div>

                                <hr class="my-4">

                                <div class="d-flex justify-content-end gap-2">
                                    <a href="{{ url('/admin/teachers/list.php') }}" class="btn btn-light border btn-lg">Cancel</a>
                                    <button type="submit" class="btn btn-primary btn-lg px-4 fw-bold">
                                        <i class="fas fa-user-plus me-1"></i> Create Teacher Account
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
