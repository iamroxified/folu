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

// Handle add, edit, delete
$action = $_GET['action'] ?? '';
$class_id = $_GET['id'] ?? '';

// Process based on action:
if ($action === 'add' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $class_name = $_POST['class_name'];
    $stmt = $pdo->prepare('INSERT INTO classes (class_name) VALUES (?)');
    $stmt->execute([$class_name]);
    header('location: manage.php');
} elseif ($action === 'edit' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $class_name = $_POST['class_name'];
    $stmt = $pdo->prepare('UPDATE classes SET class_name = ? WHERE id = ?');
    $stmt->execute([$class_name, $class_id]);
    header('location: manage.php');
} elseif ($action === 'delete') {
    $stmt = $pdo->prepare('DELETE FROM classes WHERE id = ?');
    $stmt->execute([$class_id]);
    header('location: manage.php');
}

// Fetch classes
$classes = QueryDB("SELECT * FROM classes ORDER BY class_name")->fetchAll();

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <title>Manage Classes</title>
    <?php include('../../nav/links.php'); ?>
</head>
<body>
    <div class="wrapper">
        <?php include('../../nav/sidebar.php'); ?>
        <div class="main-panel">
            <?php include('../../nav/header.php'); ?>
            <div class="container">
                <div class="page-inner">
                    <h2 class="text-dark pb-2 fw-bold">Manage Classes</h2>
                    <form method="POST" action="manage.php?action=add">
                        <input type="text" name="class_name" placeholder="Add new class...">
                        <button type="submit" class="btn btn-primary">Add</button>
                    </form>
                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th>Class Name</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($classes as $class): ?>
                                <tr>
                                    <td><?php echo $class['class_name']; ?></td>
                                    <td>
                                        <a href="manage.php?action=edit&id=<?php echo $class['id']; ?>">Edit</a>
                                        <a href="manage.php?action=delete&id=<?php echo $class['id']; ?>">Delete</a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
