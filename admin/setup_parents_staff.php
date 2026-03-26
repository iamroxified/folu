<?php
/**
 * Setup Parents and Staff Tables for FOLU School Management System
 * This script creates and populates the parents and staff tables
 */

// Database configuration based on user rules
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "dataworld"; // Using dataworld as per user rules

// Create connection
$conn = new mysqli($servername, $username, $password);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

echo "<h2>FOLU School Management System - Parents and Staff Setup</h2>";

// Create database if it doesn't exist
$sql = "CREATE DATABASE IF NOT EXISTS $dbname";
if ($conn->query($sql) === TRUE) {
    echo "<p>✓ Database '$dbname' created or already exists</p>";
} else {
    echo "<p>✗ Error creating database: " . $conn->error . "</p>";
}

// Select the database
$conn->select_db($dbname);

// Function to execute SQL file
function executeSQLFile($conn, $filename) {
    if (!file_exists($filename)) {
        echo "<p>✗ File not found: $filename</p>";
        return false;
    }
    
    $sql = file_get_contents($filename);
    if ($sql === false) {
        echo "<p>✗ Error reading file: $filename</p>";
        return false;
    }
    
    // Remove USE database statement to avoid conflicts
    $sql = preg_replace('/^USE\s+\w+;\s*/mi', '', $sql);
    
    // Split SQL into individual statements
    $statements = array_filter(array_map('trim', explode(';', $sql)));
    
    $success_count = 0;
    $total_count = count($statements);
    
    foreach ($statements as $statement) {
        if (empty($statement)) continue;
        
        if ($conn->query($statement) === TRUE) {
            $success_count++;
        } else {
            echo "<p>✗ Error executing statement: " . $conn->error . "</p>";
            echo "<p>Statement: " . substr($statement, 0, 100) . "...</p>";
        }
    }
    
    echo "<p>✓ Executed $success_count/$total_count statements from " . basename($filename) . "</p>";
    return $success_count == $total_count;
}

echo "<h3>Setting up Parents and Staff Tables...</h3>";

// Execute the SQL files
$parents_success = executeSQLFile($conn, 'create_parents_table.sql');
$staff_success = executeSQLFile($conn, 'populate_staff_table.sql');

// Display results
echo "<h3>Setup Results:</h3>";

if ($parents_success) {
    echo "<p>✓ Parents table created and populated successfully!</p>";
    
    // Count parents records
    $result = $conn->query("SELECT COUNT(*) as count FROM parents");
    if ($result) {
        $row = $result->fetch_assoc();
        echo "<p>📊 Total parents records: " . $row['count'] . "</p>";
    }
} else {
    echo "<p>✗ Parents table setup failed!</p>";
}

if ($staff_success) {
    echo "<p>✓ Staff table populated successfully!</p>";
    
    // Count staff records
    $result = $conn->query("SELECT COUNT(*) as count FROM staff");
    if ($result) {
        $row = $result->fetch_assoc();
        echo "<p>📊 Total staff records: " . $row['count'] . "</p>";
    }
} else {
    echo "<p>✗ Staff table setup failed!</p>";
}

// Display sample data
if ($parents_success) {
    echo "<h3>Sample Parents Data:</h3>";
    $result = $conn->query("SELECT parent_id, first_name, last_name, occupation, relationship_to_student FROM parents LIMIT 5");
    if ($result && $result->num_rows > 0) {
        echo "<table border='1' cellpadding='5' cellspacing='0'>";
        echo "<tr><th>Parent ID</th><th>Name</th><th>Occupation</th><th>Relationship</th></tr>";
        while($row = $result->fetch_assoc()) {
            echo "<tr>";
            echo "<td>" . $row['parent_id'] . "</td>";
            echo "<td>" . $row['first_name'] . " " . $row['last_name'] . "</td>";
            echo "<td>" . $row['occupation'] . "</td>";
            echo "<td>" . ucfirst($row['relationship_to_student']) . "</td>";
            echo "</tr>";
        }
        echo "</table>";
    }
}

if ($staff_success) {
    echo "<h3>Sample Staff Data:</h3>";
    $result = $conn->query("SELECT staff_id, first_name, last_name, position, department, salary FROM staff LIMIT 5");
    if ($result && $result->num_rows > 0) {
        echo "<table border='1' cellpadding='5' cellspacing='0'>";
        echo "<tr><th>Staff ID</th><th>Name</th><th>Position</th><th>Department</th><th>Salary</th></tr>";
        while($row = $result->fetch_assoc()) {
            echo "<tr>";
            echo "<td>" . $row['staff_id'] . "</td>";
            echo "<td>" . $row['first_name'] . " " . $row['last_name'] . "</td>";
            echo "<td>" . $row['position'] . "</td>";
            echo "<td>" . ucfirst($row['department']) . "</td>";
            echo "<td>₦" . number_format($row['salary'], 2) . "</td>";
            echo "</tr>";
        }
        echo "</table>";
    }
}

echo "<h3>Next Steps:</h3>";
echo "<ul>";
echo "<li>You can now link parents to students using the student_parents relationship table</li>";
echo "<li>Staff records are ready for role assignments and payroll management</li>";
echo "<li>Both tables integrate with the existing users table for authentication</li>";
echo "<li>Consider adding more relationships and functionality as needed</li>";
echo "</ul>";

$conn->close();
?>

<style>
    body { font-family: Arial, sans-serif; margin: 20px; }
    h2 { color: #2c3e50; }
    h3 { color: #34495e; }
    table { border-collapse: collapse; margin: 10px 0; }
    th { background-color: #3498db; color: white; }
    tr:nth-child(even) { background-color: #f2f2f2; }
    p { line-height: 1.5; }
</style>
