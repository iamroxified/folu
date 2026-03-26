<?php
// Create the adminuser table for admin authentication
require('../db/config.php');

echo "Creating adminuser table...\n";

$sql = "CREATE TABLE IF NOT EXISTS adminuser (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    afname VARCHAR(50) NOT NULL,
    lname VARCHAR(50) NOT NULL,
    phone VARCHAR(20),
    access ENUM('admin', 'super_admin') NOT NULL DEFAULT 'admin',
    status ENUM('active', 'inactive', 'suspended') NOT NULL DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)";

try {
    $result = $pdo->exec($sql);
    echo "✅ SUCCESS: adminuser table created successfully!\n";
    
    // Insert a default admin user
    $defaultAdminSql = "INSERT INTO adminuser (username, password, email, afname, lname, phone, access, status) 
                       VALUES ('admin', ?, 'admin@folu.com', 'Administrator', 'User', '08012345678', 'super_admin', 'active')
                       ON DUPLICATE KEY UPDATE username = username";
    
    $hashedPassword = password_hash('admin123', PASSWORD_DEFAULT);
    $stmt = $pdo->prepare($defaultAdminSql);
    $stmt->execute([$hashedPassword]);
    
    echo "✅ SUCCESS: Default admin user created!\n";
    echo "Login details:\n";
    echo "Username: admin\n";
    echo "Password: admin123\n";
    
} catch (PDOException $e) {
    echo "❌ ERROR: " . $e->getMessage() . "\n";
}
?>
