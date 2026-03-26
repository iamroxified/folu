<?php
// Command line script to create the FOLU school management system database schema
require('../db/config.php');

echo "Setting up FOLU School Management System Database Schema...\n";
echo "============================================================\n\n";

// Check if the SQL file exists
if (!file_exists('create_folu_schema.sql')) {
    die("ERROR: create_folu_schema.sql file not found!\n");
}

// Read the SQL file content
$sqlContent = file_get_contents('create_folu_schema.sql');

if ($sqlContent === false) {
    die("ERROR: Could not read create_folu_schema.sql file!\n");
}

// Remove comments and split by semicolons more carefully
$sqlContent = preg_replace('/--.*$/m', '', $sqlContent);  // Remove comments
$statements = preg_split('/;\s*(?=CREATE|ALTER|INSERT|UPDATE|DELETE|DROP)/i', $sqlContent);

$success_count = 0;
$error_count = 0;

foreach ($statements as $statement) {
    $statement = trim($statement);
    
    // Skip empty statements
    if (empty($statement)) {
        continue;
    }
    
    // Add semicolon back if it doesn't end with one
    if (!preg_match('/;\s*$/', $statement)) {
        $statement .= ';';
    }
    
    try {
        // Show first line of the statement for identification
        $firstLine = strtok($statement, "\n");
        echo "Executing: " . substr($firstLine, 0, 60) . "...\n";
        
        $result = $pdo->exec($statement);
        
        echo "✅ SUCCESS\n\n";
        $success_count++;
        
    } catch (PDOException $e) {
        echo "❌ ERROR: " . $e->getMessage() . "\n\n";
        $error_count++;
    }
}

echo "============================================================\n";
echo "Summary:\n";
echo "Successful operations: $success_count\n";
echo "Failed operations: $error_count\n\n";

if ($error_count == 0) {
    echo "🎉 All FOLU database tables created successfully!\n";
    echo "You can now use the school management system functionality.\n\n";
} else {
    echo "⚠️ Some operations failed.\n";
    echo "Please check the errors above and fix any issues.\n\n";
}

// Test if all tables exist
echo "Verifying FOLU Tables:\n";
echo "----------------------\n";
$tables = ['users', 'students', 'teachers', 'classes', 'subjects', 'attendance', 'grades', 'academic_sessions', 'fees'];

foreach ($tables as $table) {
    try {
        $result = $pdo->query("SHOW TABLES LIKE '$table'");
        if ($result->rowCount() > 0) {
            echo "✅ $table exists\n";
        } else {
            echo "❌ $table does not exist\n";
        }
    } catch (PDOException $e) {
        echo "❌ Error checking $table: " . $e->getMessage() . "\n";
    }
}

echo "\nSetup complete!\n";
?>
