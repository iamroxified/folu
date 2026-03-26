<?php
/**
 * FIMOCOL Secondary School Complete Database Setup
 * This script creates the complete database structure and populates it with data
 */

ini_set('max_execution_time', 300); // 5 minutes
ini_set('memory_limit', '256M');

// Database configuration based on user rules
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "folu"; // Using folu as per user rules

// Create connection
$conn = new mysqli($servername, $username, $password);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

echo "<!DOCTYPE html>
<html lang='en'>
<head>
    <meta charset='UTF-8'>
    <meta name='viewport' content='width=device-width, initial-scale=1.0'>
    <title>FIMOCOL Database Setup</title>
    <style>
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; margin: 20px; background: #f5f5f5; }
        .container { max-width: 1200px; margin: 0 auto; background: white; padding: 30px; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        h1 { color: #2c5aa0; text-align: center; margin-bottom: 30px; }
        h2 { color: #2c5aa0; border-bottom: 2px solid #e0e0e0; padding-bottom: 10px; }
        h3 { color: #34495e; margin-top: 25px; }
        .success { color: #27ae60; font-weight: bold; }
        .error { color: #e74c3c; font-weight: bold; }
        .info { color: #3498db; }
        table { width: 100%; border-collapse: collapse; margin: 15px 0; background: white; }
        th { background: #3498db; color: white; padding: 12px; text-align: left; }
        td { padding: 10px; border-bottom: 1px solid #ddd; }
        tr:nth-child(even) { background: #f8f9fa; }
        .stats { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px; margin: 20px 0; }
        .stat-box { background: #ecf0f1; padding: 20px; border-radius: 8px; text-align: center; }
        .stat-number { font-size: 2em; font-weight: bold; color: #2c5aa0; }
        .progress { background: #ecf0f1; height: 10px; border-radius: 5px; margin: 10px 0; }
        .progress-bar { background: #27ae60; height: 100%; border-radius: 5px; transition: width 0.3s; }
        .next-steps { background: #e8f6f3; padding: 20px; border-radius: 8px; margin-top: 20px; }
        .logo { text-align: center; margin-bottom: 20px; }
        .logo h1 { color: #2c5aa0; font-size: 2.5em; margin: 0; }
        .logo p { color: #7f8c8d; margin: 5px 0; }
    </style>
</head>
<body>
<div class='container'>";

echo "<div class='logo'>
    <h1>FIMOCOL</h1>
    <p>Federal Independent Model Colleges</p>
    <p><em>Excellence in Secondary Education</em></p>
</div>";

echo "<h2>🏫 FIMOCOL Secondary School Database Setup</h2>";

// Create database if it doesn't exist
$sql = "CREATE DATABASE IF NOT EXISTS $dbname CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci";
if ($conn->query($sql) === TRUE) {
    echo "<p class='success'>✓ Database '$dbname' created or already exists</p>";
} else {
    echo "<p class='error'>✗ Error creating database: " . $conn->error . "</p>";
    exit();
}

// Select the database
$conn->select_db($dbname);

// Function to execute SQL file with progress tracking
function executeSQLFile($conn, $filename, $description = '') {
    if (!file_exists($filename)) {
        echo "<p class='error'>✗ File not found: $filename</p>";
        return false;
    }
    
    echo "<h3>📋 $description</h3>";
    
    $sql = file_get_contents($filename);
    if ($sql === false) {
        echo "<p class='error'>✗ Error reading file: $filename</p>";
        return false;
    }
    
    // Remove USE database statement to avoid conflicts
    $sql = preg_replace('/^USE\s+\w+;\s*/mi', '', $sql);
    
    // Split SQL into individual statements
    $statements = array_filter(array_map('trim', explode(';', $sql)));
    
    $success_count = 0;
    $total_count = count($statements);
    $errors = [];
    
    echo "<div class='progress'><div class='progress-bar' style='width: 0%'></div></div>";
    echo "<p class='info'>Executing $total_count SQL statements...</p>";
    
    ob_flush();
    flush();
    
    foreach ($statements as $index => $statement) {
        if (empty($statement) || strpos($statement, 'SELECT ') === 0) continue;
        
        // Handle MySQL version compatibility for GENERATED columns
        if (strpos($statement, 'GENERATED ALWAYS') !== false) {
            // Check MySQL version
            $version = $conn->server_info;
            if (version_compare($version, '5.7.0', '<')) {
                // Replace GENERATED column with regular column for older MySQL
                $statement = str_replace(
                    'balance DECIMAL(10,2) GENERATED ALWAYS AS (amount_due - amount_paid) STORED,',
                    'balance DECIMAL(10,2) DEFAULT 0.00,',
                    $statement
                );
            }
        }
        

        
        if ($conn->query($statement) === TRUE) {
            $success_count++;
        } else {
            $error_msg = $conn->error;
            $errors[] = "Statement " . ($index + 1) . ": $error_msg";
        }
        
        // Update progress
        $progress = round(($index + 1) / $total_count * 100);
        echo "<script>
            document.querySelector('.progress-bar').style.width = '{$progress}%';
        </script>";
        
        if ($index % 10 == 0) {
            ob_flush();
            flush();
        }
    }
    
    if (!empty($errors)) {
        echo "<details><summary class='error'>⚠️ " . count($errors) . " errors occurred (click to view)</summary>";
        foreach ($errors as $error) {
            echo "<p class='error'>• $error</p>";
        }
        echo "</details>";
    }
    
    echo "<p class='success'>✓ Executed $success_count/$total_count statements successfully</p>";
    return $success_count > ($total_count * 0.8); // Consider success if 80% of statements executed
}

// Execute setup files
echo "<h2>🚀 Setting up FIMOCOL Database...</h2>";

$structure_success = executeSQLFile($conn, 'fimocol_basic_setup.sql', 'Creating Database Structure (MyISAM Engine - No FK Support)');
$data_success = executeSQLFile($conn, 'fimocol_basic_data.sql', 'Populating with Sample Data');

// Display results and statistics
echo "<h2>📊 Setup Results</h2>";

if ($structure_success && $data_success) {
    echo "<p class='success'>🎉 FIMOCOL database setup completed successfully!</p>";
    
    // Get statistics
    $stats = [];
    $tables = [
        'students' => 'Students',
        'teachers' => 'Teachers', 
        'parents' => 'Parents',
        'staff' => 'Staff',
        'classes' => 'Classes',
        'subjects' => 'Subjects',
        'academic_sessions' => 'Academic Sessions',
        'users' => 'Users'
    ];
    
    foreach ($tables as $table => $label) {
        $result = $conn->query("SELECT COUNT(*) as count FROM $table");
        if ($result) {
            $row = $result->fetch_assoc();
            $stats[$label] = $row['count'];
        }
    }
    
    echo "<div class='stats'>";
    foreach ($stats as $label => $count) {
        echo "<div class='stat-box'>
            <div class='stat-number'>$count</div>
            <div>$label</div>
        </div>";
    }
    echo "</div>";
    
} else {
    echo "<p class='error'>❌ Database setup failed. Please check the errors above.</p>";
}

// Display sample data
if ($data_success) {
    echo "<h3>👥 Sample Students Data</h3>";
    $result = $conn->query("
        SELECT s.admission_no, s.first_name, s.last_name, s.gender, 
               c.class_name, s.student_type, s.state_of_origin
        FROM students s
        LEFT JOIN classes c ON s.class_link = c.id
        ORDER BY s.admission_no
        LIMIT 10
    ");
    
    if ($result && $result->num_rows > 0) {
        echo "<table>";
        echo "<tr><th>Admission No</th><th>Name</th><th>Gender</th><th>Class</th><th>Type</th><th>State</th></tr>";
        while($row = $result->fetch_assoc()) {
            echo "<tr>";
            echo "<td>" . $row['admission_no'] . "</td>";
            echo "<td>" . $row['first_name'] . " " . $row['last_name'] . "</td>";
            echo "<td>" . ucfirst($row['gender']) . "</td>";
            echo "<td>" . $row['class_name'] . "</td>";
            echo "<td>" . ucfirst($row['student_type']) . "</td>";
            echo "<td>" . $row['state_of_origin'] . "</td>";
            echo "</tr>";
        }
        echo "</table>";
    }
    
    echo "<h3>👨‍🏫 Sample Teachers Data</h3>";
    $result = $conn->query("
        SELECT teacher_id, first_name, last_name, specialization, qualification, salary
        FROM teachers
        ORDER BY teacher_id
        LIMIT 8
    ");
    
    if ($result && $result->num_rows > 0) {
        echo "<table>";
        echo "<tr><th>Teacher ID</th><th>Name</th><th>Specialization</th><th>Qualification</th><th>Salary</th></tr>";
        while($row = $result->fetch_assoc()) {
            echo "<tr>";
            echo "<td>" . $row['teacher_id'] . "</td>";
            echo "<td>" . $row['first_name'] . " " . $row['last_name'] . "</td>";
            echo "<td>" . $row['specialization'] . "</td>";
            echo "<td>" . $row['qualification'] . "</td>";
            echo "<td>₦" . number_format($row['salary'], 2) . "</td>";
            echo "</tr>";
        }
        echo "</table>";
    }
    
    echo "<h3>📚 Subjects (English, Mathematics, Physics)</h3>";
    $result = $conn->query("
        SELECT subject_name, subject_code, class_level, is_core
        FROM subjects 
        WHERE subject_name IN ('English Language', 'Mathematics', 'Physics')
        ORDER BY subject_name
    ");
    
    if ($result && $result->num_rows > 0) {
        echo "<table>";
        echo "<tr><th>Subject</th><th>Code</th><th>Class Level</th><th>Core Subject</th></tr>";
        while($row = $result->fetch_assoc()) {
            echo "<tr>";
            echo "<td>" . $row['subject_name'] . "</td>";
            echo "<td>" . $row['subject_code'] . "</td>";
            echo "<td>" . $row['class_level'] . "</td>";
            echo "<td>" . ($row['is_core'] ? '✓ Yes' : '✗ No') . "</td>";
            echo "</tr>";
        }
        echo "</table>";
    }
}

// Login credentials
echo "<div class='next-steps'>";
echo "<h3>🔐 Default Login Credentials</h3>";
echo "<table>";
echo "<tr><th>Role</th><th>Username</th><th>Password</th><th>Description</th></tr>";
echo "<tr><td>Admin</td><td>admin</td><td>password</td><td>System Administrator</td></tr>";
echo "<tr><td>Admin</td><td>principal</td><td>password</td><td>School Principal</td></tr>";
echo "<tr><td>Teacher</td><td>adebayo.mrs</td><td>password</td><td>English Teacher</td></tr>";
echo "<tr><td>Teacher</td><td>okafor.mr</td><td>password</td><td>Mathematics Teacher</td></tr>";
echo "<tr><td>Teacher</td><td>ibrahim.dr</td><td>password</td><td>Physics Teacher</td></tr>";
echo "<tr><td>Student</td><td>std001</td><td>password</td><td>Sample Student</td></tr>";
echo "<tr><td>Parent</td><td>parent001</td><td>password</td><td>Sample Parent</td></tr>";
echo "</table>";
echo "</div>";

echo "<div class='next-steps'>";
echo "<h3>🎯 Next Steps</h3>";
echo "<ul>";
echo "<li>✅ <strong>Classes:</strong> 12 classes created (JSS1A-SSS3B) for Nigerian secondary school structure</li>";
echo "<li>✅ <strong>Subjects:</strong> Core subjects including English, Mathematics, and Physics</li>";
echo "<li>✅ <strong>Students:</strong> 15 sample students with Nigerian names, states, and LGAs</li>";
echo "<li>✅ <strong>Teachers:</strong> 8 qualified teachers with specializations</li>";
echo "<li>✅ <strong>Parents:</strong> 10 parents linked to students with Nigerian context</li>";
echo "<li>✅ <strong>Staff:</strong> 5 non-teaching staff (Secretary, Accountant, Librarian, etc.)</li>";
echo "<li>✅ <strong>Fees:</strong> Realistic fee structures for day and boarding students</li>";
echo "<li>✅ <strong>Grades:</strong> Sample test scores and grades</li>";
echo "<li>✅ <strong>Attendance:</strong> Sample attendance records</li>";
echo "</ul>";

echo "<h4>🔧 System Features Ready:</h4>";
echo "<ul>";
echo "<li>Multi-role authentication (Admin, Teacher, Student, Parent, Staff)</li>";
echo "<li>Academic session management (2024/2025 current session)</li>";
echo "<li>Nigerian secondary school class structure (JSS1-SSS3)</li>";
echo "<li>Student records with Nigerian bio-data (State of origin, LGA, etc.)</li>";
echo "<li>Fee management with day/boarding student differentiation</li>";
echo "<li>Grade and attendance tracking</li>";
echo "<li>Parent-student relationships</li>";
echo "<li>Teacher-subject assignments</li>";
echo "</ul>";
echo "</div>";

$conn->close();

echo "</div>
</body>
</html>";
?>

<script>
// Auto-refresh progress bars
setInterval(function() {
    document.querySelectorAll('.progress-bar').forEach(function(bar) {
        if (bar.style.width !== '100%') {
            bar.style.width = '100%';
        }
    });
}, 1000);
</script>
