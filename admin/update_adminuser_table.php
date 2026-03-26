<?php
require_once('../db/config.php');

echo "Updating adminuser table for remember me functionality...\n";

try {
    $pdo->exec('ALTER TABLE adminuser ADD COLUMN remember_token VARCHAR(255) NULL, ADD COLUMN remember_expires DATETIME NULL');
    echo "✅ SUCCESS: Remember me columns added successfully!\n";
} catch (PDOException $e) {
    if (strpos($e->getMessage(), 'Duplicate column name') !== false) {
        echo "✅ INFO: Remember me columns already exist.\n";
    } else {
        echo "❌ ERROR: " . $e->getMessage() . "\n";
    }
}
?>
