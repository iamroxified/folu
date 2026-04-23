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

$subjectAssignments = get_teacher_subject_assignments($teacherId);
$classAssignments = get_teacher_class_assignments($teacherId);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <title>Teacher Profile</title>
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
                        <h2 class="text-dark pb-2 fw-bold">Teacher Profile</h2>
                        <div class="ml-md-auto py-2 py-md-0">
                            <a href="{{ url('/admin/teachers/edit.php?id=' . $teacherId) }}" class="btn btn-warning">Edit</a>
                            <a href="{{ url('/admin/teachers/assign_subjects.php?id=' . $teacherId) }}" class="btn btn-success">Assignments</a>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-header">
                                    <div class="card-title"><?php echo htmlspecialchars(trim($teacher['first_name'] . ' ' . $teacher['last_name'])); ?></div>
                                    <div class="card-category">Teacher ID: <?php echo htmlspecialchars($teacher['teacher_id']); ?></div>
                                </div>
                                <div class="card-body">
                                    <p><strong>Username:</strong> <?php echo htmlspecialchars($teacher['username']); ?></p>
                                    <p><strong>Email:</strong> <?php echo htmlspecialchars($teacher['email']); ?></p>
                                    <p><strong>Phone:</strong> <?php echo htmlspecialchars((string) ($teacher['phone'] ?? 'N/A')); ?></p>
                                    <p><strong>Employment Date:</strong> <?php echo htmlspecialchars((string) ($teacher['employment_date'] ?? 'N/A')); ?></p>
                                    <p><strong>Status:</strong> <span class="badge badge-<?php echo ($teacher['user_status'] ?? '') === 'active' ? 'success' : 'warning'; ?>"><?php echo htmlspecialchars(ucfirst($teacher['user_status'] ?? 'active')); ?></span></p>
                                    <p><strong>Qualification:</strong><br><?php echo nl2br(htmlspecialchars((string) ($teacher['qualification'] ?? 'N/A'))); ?></p>
                                    <p><strong>Specialization:</strong><br><?php echo nl2br(htmlspecialchars((string) ($teacher['specialization'] ?? 'N/A'))); ?></p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-header">
                                    <div class="card-title">Assignment Summary</div>
                                </div>
                                <div class="card-body">
                                    <p><strong>Class Assignments:</strong> <?php echo count($classAssignments); ?></p>
                                    <p><strong>Subject Assignments:</strong> <?php echo count($subjectAssignments); ?></p>
                                    <a href="{{ url('/admin/teachers/assign_subjects.php?id=' . $teacherId) }}" class="btn btn-success btn-block">Manage Assignments</a>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-header">
                                    <div class="card-title">Assigned Classes</div>
                                </div>
                                <div class="card-body">
                                    <?php if (empty($classAssignments)): ?>
                                        <div class="alert alert-info mb-0">No class assignments yet.</div>
                                    <?php else: ?>
                                        <ul class="list-group">
                                            <?php foreach ($classAssignments as $assignment): ?>
                                                <li class="list-group-item d-flex justify-content-between align-items-center">
                                                    <span><?php echo htmlspecialchars($assignment['class_name'] . ' ' . ($assignment['class_arm'] ?? '')); ?></span>
                                                    <span class="badge badge-primary"><?php echo htmlspecialchars(ucwords(str_replace('_', ' ', $assignment['assignment_role'] ?? 'class_teacher'))); ?></span>
                                                </li>
                                            <?php endforeach; ?>
                                        </ul>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-header">
                                    <div class="card-title">Assigned Subjects</div>
                                </div>
                                <div class="card-body">
                                    <?php if (empty($subjectAssignments)): ?>
                                        <div class="alert alert-info mb-0">No subject assignments yet.</div>
                                    <?php else: ?>
                                        <ul class="list-group">
                                            <?php foreach ($subjectAssignments as $assignment): ?>
                                                <li class="list-group-item">
                                                    <strong><?php echo htmlspecialchars($assignment['subject_name']); ?></strong>
                                                    <div class="text-muted"><?php echo htmlspecialchars(($assignment['class_name'] ?? '') . ' ' . ($assignment['class_arm'] ?? '')); ?></div>
                                                </li>
                                            <?php endforeach; ?>
                                        </ul>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            @include('admin.partials.footer')
</body>
</html>
