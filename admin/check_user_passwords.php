<?php
require_once('../db/config.php');

echo "Checking user passwords in the database...\n\n";

try {
    $stmt = $pdo->query('SELECT u.username, u.email, r.role_name, u.created_at FROM users u LEFT JOIN roles r ON u.role_id = r.id ORDER BY u.id');
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "Users in the system:\n";
    echo "==================\n";
    foreach($users as $user) {
        echo "Username: {$user['username']}\n";
        echo "Email: {$user['email']}\n";
        echo "Role: {$user['role_name']}\n";
        echo "Created: {$user['created_at']}\n";
        echo "-------------------\n";
    }
    
    // Check if passwords are hashed or plain text
    echo "\nChecking password format for superadmin:\n";
    $stmt = $pdo->prepare('SELECT username, password FROM users WHERE username = ?');
    $stmt->execute(['superadmin']);
    $superadmin = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($superadmin) {
        echo "Superadmin found!\n";
        echo "Password hash starts with: " . substr($superadmin['password'], 0, 10) . "...\n";
        
        // Check if it's a bcrypt hash
        if (password_get_info($superadmin['password'])['algo']) {
            echo "Password is properly hashed with bcrypt.\n";
            echo "You'll need to check the original setup script or documentation for the plain text password.\n";
        } else {
            echo "Password might be in plain text: {$superadmin['password']}\n";
        }
    } else {
        echo "Superadmin user not found!\n";
    }
    
} catch (PDOException $e) {
    echo 'Error: ' . $e->getMessage();
}
?>
