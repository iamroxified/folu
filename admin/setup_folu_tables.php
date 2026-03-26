<?php
// Command line script to create the FOLU school management system database schema
require('../db/config.php');

echo "Setting up FOLU School Management System Database Schema...\n";
echo "============================================================\n\n";

$success_count = 0;
$error_count = 0;

// Define all table creation statements
$tables = [
    'users' => "CREATE TABLE IF NOT EXISTS users (
        id INT AUTO_INCREMENT PRIMARY KEY,
        username VARCHAR(50) NOT NULL UNIQUE,
        password VARCHAR(255) NOT NULL,
        email VARCHAR(100) NOT NULL UNIQUE,
        role ENUM('admin', 'teacher', 'student', 'parent') NOT NULL DEFAULT 'student',
        status ENUM('active', 'inactive', 'suspended') NOT NULL DEFAULT 'active',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )",
    
    'academic_sessions' => "CREATE TABLE IF NOT EXISTS academic_sessions (
        id INT AUTO_INCREMENT PRIMARY KEY,
        session_name VARCHAR(20) NOT NULL UNIQUE,
        start_date DATE NOT NULL,
        end_date DATE NOT NULL,
        is_current BOOLEAN DEFAULT FALSE,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )",
    
    'classes' => "CREATE TABLE IF NOT EXISTS classes (
        id INT AUTO_INCREMENT PRIMARY KEY,
        class_name VARCHAR(50) NOT NULL,
        section VARCHAR(10) DEFAULT 'A',
        teacher_id INT NULL,
        academic_year VARCHAR(20) NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        INDEX idx_teacher_id (teacher_id),
        INDEX idx_academic_year (academic_year)
    )",
    
    'teachers' => "CREATE TABLE IF NOT EXISTS teachers (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL,
        employee_id VARCHAR(20) NOT NULL UNIQUE,
        first_name VARCHAR(50) NOT NULL,
        last_name VARCHAR(50) NOT NULL,
        email VARCHAR(100) NOT NULL UNIQUE,
        phone VARCHAR(20),
        qualification TEXT,
        subjects TEXT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        INDEX idx_user_id (user_id),
        INDEX idx_employee_id (employee_id)
    )",
    
    'students' => "CREATE TABLE IF NOT EXISTS students (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL,
        admission_no VARCHAR(20) NOT NULL UNIQUE,
        first_name VARCHAR(50) NOT NULL,
        last_name VARCHAR(50) NOT NULL,
        date_of_birth DATE NOT NULL,
        gender ENUM('male', 'female') NOT NULL,
        address TEXT,
        parent_name VARCHAR(100),
        parent_phone VARCHAR(20),
        class_id INT NULL,
        session VARCHAR(20) NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
        FOREIGN KEY (class_id) REFERENCES classes(id) ON DELETE SET NULL,
        INDEX idx_user_id (user_id),
        INDEX idx_admission_no (admission_no),
        INDEX idx_class_id (class_id),
        INDEX idx_session (session)
    )",
    
    'subjects' => "CREATE TABLE IF NOT EXISTS subjects (
        id INT AUTO_INCREMENT PRIMARY KEY,
        subject_name VARCHAR(100) NOT NULL,
        subject_code VARCHAR(20) NOT NULL UNIQUE,
        class_id INT NOT NULL,
        teacher_id INT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (class_id) REFERENCES classes(id) ON DELETE CASCADE,
        FOREIGN KEY (teacher_id) REFERENCES teachers(id) ON DELETE SET NULL,
        INDEX idx_class_id (class_id),
        INDEX idx_teacher_id (teacher_id),
        INDEX idx_subject_code (subject_code)
    )",
    
    'attendance' => "CREATE TABLE IF NOT EXISTS attendance (
        id INT AUTO_INCREMENT PRIMARY KEY,
        student_id INT NOT NULL,
        class_id INT NOT NULL,
        date DATE NOT NULL,
        status ENUM('present', 'absent', 'late', 'excused') NOT NULL DEFAULT 'present',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        UNIQUE KEY unique_student_date (student_id, date),
        INDEX idx_student_id (student_id),
        INDEX idx_class_id (class_id),
        INDEX idx_date (date)
    )",
    
    'grades' => "CREATE TABLE IF NOT EXISTS grades (
        id INT AUTO_INCREMENT PRIMARY KEY,
        student_id INT NOT NULL,
        subject_id INT NOT NULL,
        term ENUM('first_term', 'second_term', 'third_term') NOT NULL,
        exam_type ENUM('test', 'assignment', 'midterm', 'final', 'continuous_assessment') NOT NULL DEFAULT 'test',
        score DECIMAL(5,2) NOT NULL,
        grade VARCHAR(5),
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        INDEX idx_student_id (student_id),
        INDEX idx_subject_id (subject_id),
        INDEX idx_term (term),
        INDEX idx_exam_type (exam_type)
    )",
    
    'fees' => "CREATE TABLE IF NOT EXISTS fees (
        id INT AUTO_INCREMENT PRIMARY KEY,
        student_id INT NOT NULL,
        amount DECIMAL(10,2) NOT NULL,
        payment_date DATE,
        payment_method ENUM('cash', 'bank_transfer', 'card', 'cheque', 'online') DEFAULT 'cash',
        status ENUM('pending', 'paid', 'partial', 'overdue') NOT NULL DEFAULT 'pending',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        INDEX idx_student_id (student_id),
        INDEX idx_status (status),
        INDEX idx_payment_date (payment_date)
    )"
];

// Create each table
foreach ($tables as $tableName => $sql) {
    try {
        echo "Creating table: $tableName...\n";
        $result = $pdo->exec($sql);
        echo "✅ SUCCESS: $tableName created\n\n";
        $success_count++;
    } catch (PDOException $e) {
        echo "❌ ERROR creating $tableName: " . $e->getMessage() . "\n\n";
        $error_count++;
    }
}

// Add foreign key constraint for classes.teacher_id after teachers table is created
try {
    echo "Adding foreign key constraint for classes.teacher_id...\n";
    $result = $pdo->exec("ALTER TABLE classes ADD CONSTRAINT fk_classes_teacher_id FOREIGN KEY (teacher_id) REFERENCES teachers(id) ON DELETE SET NULL");
    echo "✅ SUCCESS: Foreign key constraint added\n\n";
    $success_count++;
} catch (PDOException $e) {
    // This might fail if the constraint already exists, which is fine
    if (strpos($e->getMessage(), 'Duplicate key name') === false) {
        echo "❌ ERROR adding foreign key: " . $e->getMessage() . "\n\n";
        $error_count++;
    } else {
        echo "✅ Foreign key constraint already exists\n\n";
        $success_count++;
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
$expectedTables = ['users', 'students', 'teachers', 'classes', 'subjects', 'attendance', 'grades', 'academic_sessions', 'fees'];

foreach ($expectedTables as $table) {
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
