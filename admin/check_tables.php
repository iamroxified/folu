<?php
require_once('../db/config.php');

echo "Checking existing tables in the database...\n\n";

try {
    // Get all tables
    $stmt = $pdo->query('SHOW TABLES');
    $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    foreach($tables as $table) {
        echo "✅ Table: $table\n";
        
        // Get table structure
        $struct = $pdo->query("DESCRIBE $table");
        $columns = $struct->fetchAll(PDO::FETCH_ASSOC);
        
        foreach($columns as $column) {
            echo "   - {$column['Field']} ({$column['Type']}) {$column['Null']} {$column['Key']} {$column['Default']}\n";
        }
        echo "\n";
    }
} catch (PDOException $e) {
    echo 'Error: ' . $e->getMessage();
}
?>
