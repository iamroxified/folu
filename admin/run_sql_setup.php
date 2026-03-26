<?php
// Script to create the FOLU school management system database schema
require('../db/config.php');

echo "<h2>Setting up FOLU School Management System Database Schema</h2>";
echo "<div style='font-family: monospace; background: #f5f5f5; padding: 20px; border-radius: 5px;'>";

// Read the SQL file content
$sqlContent = file_get_contents('create_folu_schema.sql');

// Split the SQL into individual statements
$statements = explode(';', $sqlContent);

$success_count = 0;
$error_count = 0;

foreach ($statements as $statement) {
    $statement = trim($statement);
    
    // Skip empty statements
    if (empty($statement)) {
        continue;
    }
    
    // Skip comments
    if (strpos($statement, '--') === 0) {
        continue;
    }
    
    try {
        echo "<strong>Executing:</strong> " . substr($statement, 0, 50) . "...<br>";
        
        $result = $pdo->exec($statement);
        
        echo "<span style='color: green;'>✅ SUCCESS</span><br><br>";
        $success_count++;
        
    } catch (PDOException $e) {
        echo "<span style='color: red;'>❌ ERROR: " . $e->getMessage() . "</span><br><br>";
        $error_count++;
    }
}

echo "</div>";

echo "<h3>Summary:</h3>";
echo "<p><strong>Successful operations:</strong> $success_count</p>";
echo "<p><strong>Failed operations:</strong> $error_count</p>";

if ($error_count == 0) {
    echo "<div style='background: #d4edda; color: #155724; padding: 15px; border-radius: 5px; border: 1px solid #c3e6cb;'>";
    echo "<strong>🎉 All FOLU database tables created successfully!</strong><br>";
    echo "You can now use the school management system functionality.";
    echo "</div>";
    
    echo "<p><a href='students.php' style='background: #007bff; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>Manage Students</a></p>";
    echo "<p><a href='teachers.php' style='background: #28a745; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>Manage Teachers</a></p>";
    echo "<p><a href='attendance.php' style='background: #ffc107; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>Attendance</a></p>";
} else {
    echo "<div style='background: #f8d7da; color: #721c24; padding: 15px; border-radius: 5px; border: 1px solid #f5c6cb;'>";
    echo "<strong>⚠️ Some operations failed.</strong><br>";
    echo "Please check the errors above and fix any issues.";
    echo "</div>";
}

// Test if tables exist
echo "<h3>Verifying FOLU Tables:</h3>";
$tables = ['users', 'students', 'teachers', 'classes', 'subjects', 'attendance', 'grades', 'academic_sessions', 'fees'];

foreach ($tables as $table) {
    try {
        $result = $pdo->query("SHOW TABLES LIKE '$table'");
        if ($result->rowCount() > 0) {
            echo "<span style='color: green;'>✅ $table exists</span><br>";
        } else {
            echo "<span style='color: red;'>❌ $table does not exist</span><br>";
        }
    } catch (PDOException $e) {
        echo "<span style='color: red;'>❌ Error checking $table: " . $e->getMessage() . "</span><br>";
    }
}
?>
