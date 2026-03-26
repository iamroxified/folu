<?php
// Start session
session_start();

// Include database configuration and functions
require '../../db/config.php';
require '../../db/functions.php';

// Check if user is logged in
if (!isset($_SESSION['adid'])) {
    header('location:../../login.php');
    exit;
}

// Fetch teachers data with search
$search = $_GET['search'] ?? '';
$searchParam = '%' . $search . '%';

// Fetch teachers with user information
$sql = "SELECT t.*, u.username, u.status as user_status 
        FROM teachers t 
        JOIN users u ON t.user_id = u.id 
        WHERE t.first_name LIKE ? OR t.last_name LIKE ? OR t.email LIKE ? OR t.employee_id LIKE ?
        ORDER BY t.created_at DESC";
$teachers = QueryDB($sql, [$searchParam, $searchParam, $searchParam, $searchParam])->fetchAll();

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <title>Teachers List</title>
    <?php include('../../nav/links.php'); ?>
</head>

<body>
    <div class="wrapper">
        <?php include('../../nav/sidebar.php'); ?>

        <div class="main-panel">
            <?php include('../../nav/header.php'); ?>
            <div class="container">
                <div class="page-inner">
                    <div class="d-flex align-items-left flex-column flex-md-row">
                        <h2 class="text-dark pb-2 fw-bold">Teachers List</h2>
                        <div class="ml-md-auto py-2 py-md-0">
                            <a href="add.php" class="btn btn-primary">Add New Teacher</a>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-12">
                            <div class="card">
                                <div class="card-header">
                                    <div class="card-title">All Teachers</div>
                                    <div class="card-category">
                                        <form method="GET" action="list.php" class="d-flex">
                                            <input type="text" name="search" class="form-control" placeholder="Search teachers..." value="<?php echo htmlspecialchars($search); ?>">
                                            <button type="submit" class="btn btn-primary ml-2">Search</button>
                                            <?php if (!empty($search)): ?>
                                                <a href="list.php" class="btn btn-secondary ml-2">Clear</a>
                                            <?php endif; ?>
                                        </form>
                                    </div>
                                </div>
                                <div class="card-body">
                                    <?php if (empty($teachers)): ?>
                                        <div class="alert alert-info">
                                            <?php if (!empty($search)): ?>
                                                No teachers found matching your search criteria.
                                            <?php else: ?>
                                                No teachers registered yet. <a href="add.php" class="alert-link">Add the first teacher</a>.
                                            <?php endif; ?>
                                        </div>
                                    <?php else: ?>
                                        <div class="table-responsive">
                                            <table class="table table-bordered table-hover">
                                                <thead class="thead-light">
                                                    <tr>
                                                        <th>#</th>
                                                        <th>Employee ID</th>
                                                        <th>Name</th>
                                                        <th>Email</th>
                                                        <th>Phone</th>
                                                        <th>Status</th>
                                                        <th>Joined</th>
                                                        <th>Actions</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <?php $counter = 1; ?>
                                                    <?php foreach ($teachers as $teacher): ?>
                                                        <tr>
                                                            <td><?php echo $counter++; ?></td>
                                                            <td>
                                                                <strong><?php echo htmlspecialchars($teacher['employee_id']); ?></strong>
                                                            </td>
                                                            <td>
                                                                <div>
                                                                    <strong><?php echo htmlspecialchars($teacher['first_name'] . ' ' . $teacher['last_name']); ?></strong>
                                                                    <br>
                                                                    <small class="text-muted">@<?php echo htmlspecialchars($teacher['username']); ?></small>
                                                                </div>
                                                            </td>
                                                            <td><?php echo htmlspecialchars($teacher['email']); ?></td>
                                                            <td><?php echo htmlspecialchars($teacher['phone'] ?: 'N/A'); ?></td>
                                                            <td>
                                                                <?php if ($teacher['user_status'] === 'active'): ?>
                                                                    <span class="badge badge-success">Active</span>
                                                                <?php elseif ($teacher['user_status'] === 'inactive'): ?>
                                                                    <span class="badge badge-warning">Inactive</span>
                                                                <?php else: ?>
                                                                    <span class="badge badge-danger">Suspended</span>
                                                                <?php endif; ?>
                                                            </td>
                                                            <td>
                                                                <?php echo date('M d, Y', strtotime($teacher['created_at'])); ?>
                                                            </td>
                                                            <td>
                                                                <div class="btn-group" role="group">
                                                                    <a href="view.php?id=<?php echo $teacher['id']; ?>" class="btn btn-info btn-sm" title="View Details">
                                                                        <i class="fa fa-eye"></i>
                                                                    </a>
                                                                    <a href="edit.php?id=<?php echo $teacher['id']; ?>" class="btn btn-warning btn-sm" title="Edit">
                                                                        <i class="fa fa-edit"></i>
                                                                    </a>
                                                                    <a href="assign_subjects.php?id=<?php echo $teacher['id']; ?>" class="btn btn-success btn-sm" title="Assign Subjects">
                                                                        <i class="fa fa-book"></i>
                                                                    </a>
                                                                </div>
                                                            </td>
                                                        </tr>
                                                    <?php endforeach; ?>
                                                </tbody>
                                            </table>
                                        </div>
                                        
                                        <div class="mt-3">
                                            <p class="text-muted">
                                                Showing <?php echo count($teachers); ?> teacher(s)
                                                <?php if (!empty($search)): ?>
                                                    for search: "<strong><?php echo htmlspecialchars($search); ?></strong>"
                                                <?php endif; ?>
                                            </p>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>

</html>
