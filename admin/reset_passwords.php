<?php
require_once('../db/config.php');

echo "Resetting all user passwords in the database...\n";

try {
    // New hashed password
    $newPassword = password_hash('password123', PASSWORD_DEFAULT);
    
    // Update password for all users
    $stmt = $pdo->exec("UPDATE users SET password = '$newPassword'");
    echo "✅ SUCCESS: All user passwords reset to 'password123'.\n";
} catch (PDOException $e) {
    echo '❌ ERROR: ' . $e->getMessage() . "\n";
}
?>
