-- FIMOCOL Secondary School Database Setup (ULTRA BASIC - MyISAM Engine)
-- Complete database structure for Nigerian secondary school
-- Database: folu

USE folu;

-- Disable foreign key checks completely
SET FOREIGN_KEY_CHECKS = 0;
SET SQL_MODE = '';

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
    username VARCHAR(50) NOT NULL,
    password VARCHAR(255) NOT NULL,
    email VARCHAR(100) NOT NULL,
    role VARCHAR(20) NOT NULL DEFAULT 'student',
    status VARCHAR(20) NOT NULL DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=MyISAM;

-- 2. Academic Sessions table
CREATE TABLE academic_sessions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    session_name VARCHAR(20) NOT NULL,
    start_date DATE NOT NULL,
    end_date DATE NOT NULL,
    is_current TINYINT(1) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=MyISAM;

-- 3. Teachers table
CREATE TABLE teachers (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_link INT NOT NULL,
    teacher_id VARCHAR(20) NOT NULL,
    first_name VARCHAR(50) NOT NULL,
    last_name VARCHAR(50) NOT NULL,
    email VARCHAR(100) NOT NULL,
    phone VARCHAR(20),
    qualification VARCHAR(200),
    specialization TEXT,
    employment_date DATE,
    salary DECIMAL(10,2),
    status VARCHAR(20) DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=MyISAM;

-- 4. Classes table 
CREATE TABLE classes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    class_name VARCHAR(50) NOT NULL,
    class_arm VARCHAR(10) DEFAULT 'A',
    class_level VARCHAR(10) NOT NULL,
    form_teacher_link INT NULL,
    academic_session_link INT NOT NULL,
    max_capacity INT DEFAULT 40,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=MyISAM;

-- 5. Parents table
CREATE TABLE parents (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_link INT NOT NULL,
    parent_id VARCHAR(20) NOT NULL,
    first_name VARCHAR(50) NOT NULL,
    last_name VARCHAR(50) NOT NULL,
    email VARCHAR(100),
    phone VARCHAR(20) NOT NULL,
    alternative_phone VARCHAR(20),
    occupation VARCHAR(100),
    address TEXT,
    relationship_to_student VARCHAR(20) NOT NULL DEFAULT 'father',
    emergency_contact TINYINT(1) DEFAULT 0,
    status VARCHAR(20) DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=MyISAM;

-- 6. Students table
CREATE TABLE students (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_link INT NOT NULL,
    admission_no VARCHAR(20) NOT NULL,
    first_name VARCHAR(50) NOT NULL,
    last_name VARCHAR(50) NOT NULL,
    other_names VARCHAR(50),
    date_of_birth DATE NOT NULL,
    gender VARCHAR(10) NOT NULL,
    state_of_origin VARCHAR(50),
    lga VARCHAR(50),
    home_address TEXT,
    class_link INT NULL,
    academic_session_link INT NOT NULL,
    admission_date DATE NOT NULL,
    student_type VARCHAR(10) DEFAULT 'day',
    blood_group VARCHAR(5) NULL,
    genotype VARCHAR(5) NULL,
    status VARCHAR(20) DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=MyISAM;

-- 7. Student_parents relationship table
CREATE TABLE student_parents (
    id INT AUTO_INCREMENT PRIMARY KEY,
    student_link INT NOT NULL,
    parent_link INT NOT NULL,
    relationship VARCHAR(20) NOT NULL,
    is_primary_contact TINYINT(1) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=MyISAM;

-- 8. Subjects table
CREATE TABLE subjects (
    id INT AUTO_INCREMENT PRIMARY KEY,
    subject_name VARCHAR(100) NOT NULL,
    subject_code VARCHAR(20) NOT NULL,
    class_level VARCHAR(10) NOT NULL DEFAULT 'ALL',
    is_core TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=MyISAM;

-- 9. Teacher_subjects relationship table
CREATE TABLE teacher_subjects (
    id INT AUTO_INCREMENT PRIMARY KEY,
    teacher_link INT NOT NULL,
    subject_link INT NOT NULL,
    class_link INT NOT NULL,
    academic_session_link INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=MyISAM;

-- 10. Attendance table
CREATE TABLE attendance (
    id INT AUTO_INCREMENT PRIMARY KEY,
    student_link INT NOT NULL,
    class_link INT NOT NULL,
    date DATE NOT NULL,
    status VARCHAR(20) NOT NULL DEFAULT 'present',
    remarks TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=MyISAM;

-- 11. Grades table
CREATE TABLE grades (
    id INT AUTO_INCREMENT PRIMARY KEY,
    student_link INT NOT NULL,
    subject_link INT NOT NULL,
    class_link INT NOT NULL,
    term VARCHAR(20) NOT NULL,
    exam_type VARCHAR(30) NOT NULL DEFAULT 'test',
    score DECIMAL(5,2) NOT NULL,
    max_score DECIMAL(5,2) DEFAULT 100.00,
    grade VARCHAR(5),
    remarks TEXT,
    academic_session_link INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=MyISAM;

-- 12. Staff table
CREATE TABLE staff (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_link INT NOT NULL,
    staff_id VARCHAR(20) NOT NULL,
    first_name VARCHAR(50) NOT NULL,
    last_name VARCHAR(50) NOT NULL,
    email VARCHAR(100),
    phone VARCHAR(20),
    position VARCHAR(50),
    department VARCHAR(30) DEFAULT 'administration',
    salary DECIMAL(10,2),
    hire_date DATE,
    status VARCHAR(20) DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=MyISAM;

-- 13. Fee structures table
CREATE TABLE fee_structures (
    id INT AUTO_INCREMENT PRIMARY KEY,
    fee_name VARCHAR(255) NOT NULL,
    class_level VARCHAR(10) NOT NULL DEFAULT 'ALL',
    student_type VARCHAR(10) DEFAULT 'ALL',
    amount DECIMAL(10,2) NOT NULL,
    term VARCHAR(20) NOT NULL,
    academic_session_link INT NOT NULL,
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=MyISAM;

-- 14. Student fees table
CREATE TABLE student_fees (
    id INT AUTO_INCREMENT PRIMARY KEY,
    student_link INT NOT NULL,
    fee_structure_link INT NOT NULL,
    amount_due DECIMAL(10,2) NOT NULL,
    amount_paid DECIMAL(10,2) DEFAULT 0.00,
    balance DECIMAL(10,2) DEFAULT 0.00,
    status VARCHAR(20) DEFAULT 'pending',
    due_date DATE,
    payment_date DATE NULL,
    payment_method VARCHAR(20) NULL,
    receipt_number VARCHAR(50),
    academic_session_link INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=MyISAM;

-- Re-enable foreign key checks
SET FOREIGN_KEY_CHECKS = 1;

SELECT 'FIMOCOL database structure created successfully (MyISAM Engine - NO foreign keys possible)!' as message;
