-- FIMOCOL Secondary School Database Setup (ABSOLUTELY ZERO FOREIGN KEYS)
-- Complete database structure for Nigerian secondary school
-- Database: folu

USE folu;

-- Drop existing tables completely
DROP TABLE IF EXISTS student_fees;
DROP TABLE IF EXISTS fee_structures;
DROP TABLE IF EXISTS teacher_subjects;
DROP TABLE IF EXISTS grades;
DROP TABLE IF EXISTS attendance;
DROP TABLE IF EXISTS student_parents;
DROP TABLE IF EXISTS students;
DROP TABLE IF EXISTS subjects;
DROP TABLE IF EXISTS parents;
DROP TABLE IF EXISTS teachers;
DROP TABLE IF EXISTS staff;
DROP TABLE IF EXISTS classes;
DROP TABLE IF EXISTS academic_sessions;
DROP TABLE IF EXISTS users;

-- 1. Users table
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    role ENUM('admin', 'teacher', 'student', 'parent', 'staff') NOT NULL DEFAULT 'student',
    status ENUM('active', 'inactive', 'suspended') NOT NULL DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- 2. Academic Sessions table
CREATE TABLE academic_sessions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    session_name VARCHAR(20) NOT NULL UNIQUE,
    start_date DATE NOT NULL,
    end_date DATE NOT NULL,
    is_current BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- 3. Teachers table
CREATE TABLE teachers (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_ref INT NOT NULL,
    teacher_id VARCHAR(20) NOT NULL UNIQUE,
    first_name VARCHAR(50) NOT NULL,
    last_name VARCHAR(50) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    phone VARCHAR(20),
    qualification VARCHAR(200),
    specialization TEXT,
    employment_date DATE,
    salary DECIMAL(10,2),
    status ENUM('active', 'inactive', 'on_leave', 'terminated') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- 4. Classes table 
CREATE TABLE classes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    class_name VARCHAR(50) NOT NULL,
    class_arm VARCHAR(10) DEFAULT 'A',
    class_level ENUM('JSS1', 'JSS2', 'JSS3', 'SSS1', 'SSS2', 'SSS3') NOT NULL,
    form_teacher_ref INT NULL,
    academic_session_ref INT NOT NULL,
    max_capacity INT DEFAULT 40,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- 5. Parents table
CREATE TABLE parents (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_ref INT NOT NULL,
    parent_id VARCHAR(20) NOT NULL UNIQUE,
    first_name VARCHAR(50) NOT NULL,
    last_name VARCHAR(50) NOT NULL,
    email VARCHAR(100) UNIQUE,
    phone VARCHAR(20) NOT NULL,
    alternative_phone VARCHAR(20),
    occupation VARCHAR(100),
    address TEXT,
    relationship_to_student ENUM('father', 'mother', 'guardian', 'stepfather', 'stepmother', 'uncle', 'aunt', 'other') NOT NULL DEFAULT 'father',
    emergency_contact BOOLEAN DEFAULT FALSE,
    status ENUM('active', 'inactive') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- 6. Students table
CREATE TABLE students (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_ref INT NOT NULL,
    admission_no VARCHAR(20) NOT NULL UNIQUE,
    first_name VARCHAR(50) NOT NULL,
    last_name VARCHAR(50) NOT NULL,
    other_names VARCHAR(50),
    date_of_birth DATE NOT NULL,
    gender ENUM('male', 'female') NOT NULL,
    state_of_origin VARCHAR(50),
    lga VARCHAR(50),
    home_address TEXT,
    class_ref INT NULL,
    academic_session_ref INT NOT NULL,
    admission_date DATE NOT NULL,
    student_type ENUM('day', 'boarding') DEFAULT 'day',
    blood_group ENUM('A+', 'A-', 'B+', 'B-', 'AB+', 'AB-', 'O+', 'O-') NULL,
    genotype ENUM('AA', 'AS', 'SS', 'AC', 'SC') NULL,
    status ENUM('active', 'inactive', 'graduated', 'transferred', 'expelled') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- 7. Student_parents relationship table
CREATE TABLE student_parents (
    id INT AUTO_INCREMENT PRIMARY KEY,
    student_ref INT NOT NULL,
    parent_ref INT NOT NULL,
    relationship ENUM('father', 'mother', 'guardian', 'stepfather', 'stepmother', 'uncle', 'aunt', 'other') NOT NULL,
    is_primary_contact BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY unique_student_parent (student_ref, parent_ref)
);

-- 8. Subjects table
CREATE TABLE subjects (
    id INT AUTO_INCREMENT PRIMARY KEY,
    subject_name VARCHAR(100) NOT NULL,
    subject_code VARCHAR(20) NOT NULL UNIQUE,
    class_level ENUM('JSS1', 'JSS2', 'JSS3', 'SSS1', 'SSS2', 'SSS3', 'ALL') NOT NULL DEFAULT 'ALL',
    is_core BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- 9. Teacher_subjects relationship table
CREATE TABLE teacher_subjects (
    id INT AUTO_INCREMENT PRIMARY KEY,
    teacher_ref INT NOT NULL,
    subject_ref INT NOT NULL,
    class_ref INT NOT NULL,
    academic_session_ref INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY unique_teacher_subject_class (teacher_ref, subject_ref, class_ref)
);

-- 10. Attendance table
CREATE TABLE attendance (
    id INT AUTO_INCREMENT PRIMARY KEY,
    student_ref INT NOT NULL,
    class_ref INT NOT NULL,
    date DATE NOT NULL,
    status ENUM('present', 'absent', 'late', 'excused') NOT NULL DEFAULT 'present',
    remarks TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY unique_student_date (student_ref, date)
);

-- 11. Grades table
CREATE TABLE grades (
    id INT AUTO_INCREMENT PRIMARY KEY,
    student_ref INT NOT NULL,
    subject_ref INT NOT NULL,
    class_ref INT NOT NULL,
    term ENUM('first_term', 'second_term', 'third_term') NOT NULL,
    exam_type ENUM('test', 'assignment', 'midterm', 'final', 'continuous_assessment') NOT NULL DEFAULT 'test',
    score DECIMAL(5,2) NOT NULL,
    max_score DECIMAL(5,2) DEFAULT 100.00,
    grade VARCHAR(5),
    remarks TEXT,
    academic_session_ref INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- 12. Staff table
CREATE TABLE staff (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_ref INT NOT NULL,
    staff_id VARCHAR(20) NOT NULL UNIQUE,
    first_name VARCHAR(50) NOT NULL,
    last_name VARCHAR(50) NOT NULL,
    email VARCHAR(100) UNIQUE,
    phone VARCHAR(20),
    position VARCHAR(50),
    department ENUM('administration', 'finance', 'maintenance', 'security', 'library', 'health', 'transport', 'catering', 'sports') DEFAULT 'administration',
    salary DECIMAL(10,2),
    hire_date DATE,
    status ENUM('active', 'inactive', 'on_leave', 'terminated') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- 13. Fee structures table
CREATE TABLE fee_structures (
    id INT AUTO_INCREMENT PRIMARY KEY,
    fee_name VARCHAR(255) NOT NULL,
    class_level ENUM('JSS1', 'JSS2', 'JSS3', 'SSS1', 'SSS2', 'SSS3', 'ALL') NOT NULL DEFAULT 'ALL',
    student_type ENUM('day', 'boarding', 'ALL') DEFAULT 'ALL',
    amount DECIMAL(10,2) NOT NULL,
    term ENUM('first_term', 'second_term', 'third_term', 'annual') NOT NULL,
    academic_session_ref INT NOT NULL,
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- 14. Student fees table
CREATE TABLE student_fees (
    id INT AUTO_INCREMENT PRIMARY KEY,
    student_ref INT NOT NULL,
    fee_structure_ref INT NOT NULL,
    amount_due DECIMAL(10,2) NOT NULL,
    amount_paid DECIMAL(10,2) DEFAULT 0.00,
    balance DECIMAL(10,2) DEFAULT 0.00,
    status ENUM('pending', 'partial', 'paid', 'overdue') DEFAULT 'pending',
    due_date DATE,
    payment_date DATE NULL,
    payment_method ENUM('cash', 'bank_transfer', 'pos', 'cheque', 'online') NULL,
    receipt_number VARCHAR(50),
    academic_session_ref INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

SELECT 'FIMOCOL database structure created successfully (ABSOLUTELY ZERO foreign key constraints)!' as message;
