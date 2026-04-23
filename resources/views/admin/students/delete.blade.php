<?php
// Start session

// Include database configuration and functions
require_once base_path('db/config.php');
require_once base_path('db/functions.php');

// Check if user is logged in
if (!isset($_SESSION['adid'])) {
    header('Location: /admin/login.php');
    exit;
}

// Soft delete student
$studentId = $_GET['id'] ?? null;
if ($studentId) {
    try {
        $stmt = $pdo->prepare('UPDATE students SET status = "inactive" WHERE id = ?');
        $stmt->execute([$studentId]);
        $success = 'Student deleted successfully';
    } catch (Exception $e) {
        $error = 'Failed to delete student';
    }
} else {
    $error = 'Student ID is required';
}

header('location: list.php');
exit;

?>




