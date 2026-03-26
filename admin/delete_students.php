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
