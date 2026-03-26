<?php
require_once('../db/config.php');

echo "Checking roles table:\n";
try {
    $stmt = $pdo->query('SELECT * FROM roles');
    $roles = $stmt->fetchAll(PDO::FETCH_ASSOC);
    foreach($roles as $role) {
        echo "- ID: {$role['id']}, Name: {$role['role_name']}, Description: {$role['description']}\n";
    }
    
    echo "\nChecking users table sample:\n";
    $stmt = $pdo->query('SELECT u.*, r.role_name FROM users u LEFT JOIN roles r ON u.role_id = r.id LIMIT 5');
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
    foreach($users as $user) {
        echo "- ID: {$user['id']}, Username: {$user['username']}, Email: {$user['email']}, Role: {$user['role_name']}\n";
    }
} catch (PDOException $e) {
    echo 'Error: ' . $e->getMessage();
}
?>
