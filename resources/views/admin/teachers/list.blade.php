<?php
require_once base_path('db/config.php');
require_once base_path('db/functions.php');

if (!isset($_SESSION['adid'])) {
    header('Location: /admin/login.php');
    exit;
}

$search = trim((string) ($_GET['search'] ?? ''));
$searchParam = '%' . $search . '%';

$teachers = QueryDB(
    "SELECT t.*, u.username, u.status AS user_status,
            (SELECT COUNT(*) FROM teacher_subjects ts WHERE ts.teacher_link = t.id) AS subject_count,
            (SELECT COUNT(*) FROM teacher_class_assignments tca WHERE tca.teacher_link = t.id) AS class_count
     FROM teachers t
     JOIN users u ON t.user_link = u.id
     WHERE (? = '%%')
        OR t.first_name LIKE ?
        OR t.last_name LIKE ?
        OR t.email LIKE ?
        OR t.teacher_id LIKE ?
     ORDER BY t.created_at DESC",
    [$searchParam, $searchParam, $searchParam, $searchParam, $searchParam]
)->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <title>Teachers</title>
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
                        <h2 class="text-dark pb-2 fw-bold">Teachers List</h2>
                        <div class="ml-md-auto py-2 py-md-0">
                            <a href="{{ url('/admin/teachers/add.php') }}" class="btn btn-primary">Add Teacher</a>
                        </div>
                    </div>

                    <div class="card">
                        <div class="card-header">
                            <div class="card-title">All Teachers</div>
                            <div class="card-category">
                                <form method="GET" class="d-flex">
                                    <input type="text" name="search" class="form-control" placeholder="Search teachers..." value="<?php echo htmlspecialchars($search); ?>">
                                    <button type="submit" class="btn btn-primary ml-2">Search</button>
                                </form>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-bordered table-striped">
                                    <thead>
                                        <tr>
                                            <th>Teacher ID</th>
                                            <th>Name</th>
                                            <th>Username</th>
                                            <th>Email</th>
                                            <th>Classes</th>
                                            <th>Subjects</th>
                                            <th>Status</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($teachers as $teacher): ?>
                                            <tr>
                                                <td><?php echo htmlspecialchars($teacher['teacher_id']); ?></td>
                                                <td><?php echo htmlspecialchars(trim($teacher['first_name'] . ' ' . $teacher['last_name'])); ?></td>
                                                <td><?php echo htmlspecialchars($teacher['username']); ?></td>
                                                <td><?php echo htmlspecialchars($teacher['email']); ?></td>
                                                <td><?php echo (int) $teacher['class_count']; ?></td>
                                                <td><?php echo (int) $teacher['subject_count']; ?></td>
                                                <td><span class="badge badge-<?php echo $teacher['user_status'] === 'active' ? 'success' : 'warning'; ?>"><?php echo htmlspecialchars(ucfirst($teacher['user_status'])); ?></span></td>
                                                <td>
                                                    <a href="{{ url('/admin/teachers/view.php?id=' . $teacher['id']) }}" class="btn btn-info btn-sm">View</a>
                                                    <a href="{{ url('/admin/teachers/edit.php?id=' . $teacher['id']) }}" class="btn btn-warning btn-sm">Edit</a>
                                                    <a href="{{ url('/admin/teachers/assign_subjects.php?id=' . $teacher['id']) }}" class="btn btn-success btn-sm">Assign</a>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            @include('admin.partials.footer')
</body>
</html>
