<?php
require_once('../db/config.php');

echo "Checking email verification status for all users...\n\n";

try {
    $stmt = $pdo->query('SELECT username, email, email_verified_at FROM users ORDER BY id');
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    foreach($users as $user) {
        echo "Username: {$user['username']}\n";
        echo "Email: {$user['email']}\n";
        echo "Email Verified: " . ($user['email_verified_at'] ? $user['email_verified_at'] : 'NOT VERIFIED') . "\n";
        echo "-------------------\n";
    }
    
    // Update all users to have verified emails
    echo "\nUpdating all users to have verified emails...\n";
    $stmt = $pdo->exec("UPDATE users SET email_verified_at = NOW() WHERE email_verified_at IS NULL");
    echo "✅ SUCCESS: All users now have verified emails.\n";
    
} catch (PDOException $e) {
    echo 'Error: ' . $e->getMessage();
}
?>
