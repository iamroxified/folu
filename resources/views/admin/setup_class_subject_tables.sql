-- Database setup for Class and Subject Management System
-- Run this in your MySQL database to create the required tables

USE dataworld;

-- Create classes table
CREATE TABLE IF NOT EXISTS classes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    class_name VARCHAR(100) NOT NULL UNIQUE,
    description TEXT,
    capacity INT DEFAULT 30,
    status ENUM('active', 'inactive') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Create subjects table
CREATE TABLE IF NOT EXISTS subjects (
    id INT AUTO_INCREMENT PRIMARY KEY,
    subject_name VARCHAR(100) NOT NULL,
    subject_code VARCHAR(20) NOT NULL UNIQUE,
    description TEXT,
    credits INT DEFAULT 1,
    status ENUM('active', 'inactive') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Create student_class_assignments table for assigning students to classes
CREATE TABLE IF NOT EXISTS student_class_assignments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    student_id INT NOT NULL,
    class_id INT NOT NULL,
    assigned_date DATE NOT NULL,
    status ENUM('active', 'inactive', 'transferred') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (student_id) REFERENCES students(id) ON DELETE CASCADE,
    FOREIGN KEY (class_id) REFERENCES classes(id) ON DELETE CASCADE,
    UNIQUE KEY unique_student_class (student_id, class_id)
);

-- Create class_subject_assignments table for assigning subjects to classes
CREATE TABLE IF NOT EXISTS class_subject_assignments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    class_id INT NOT NULL,
    subject_id INT NOT NULL,
    assigned_date DATE NOT NULL,
    status ENUM('active', 'inactive') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (class_id) REFERENCES classes(id) ON DELETE CASCADE,
    FOREIGN KEY (subject_id) REFERENCES subjects(id) ON DELETE CASCADE,
    UNIQUE KEY unique_class_subject (class_id, subject_id)
);

-- Create class_timetables table for managing class schedules
CREATE TABLE IF NOT EXISTS class_timetables (
    id INT AUTO_INCREMENT PRIMARY KEY,
    class_id INT NOT NULL,
    subject_id INT NOT NULL,
    teacher_id INT NOT NULL,
    day_of_week TINYINT NOT NULL COMMENT '1=Monday, 2=Tuesday, ..., 7=Sunday',
    start_time TIME NOT NULL,
    end_time TIME NOT NULL,
    room VARCHAR(50),
    status ENUM('active', 'inactive') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (class_id) REFERENCES classes(id) ON DELETE CASCADE,
    FOREIGN KEY (subject_id) REFERENCES subjects(id) ON DELETE CASCADE,
    FOREIGN KEY (teacher_id) REFERENCES staff(id) ON DELETE CASCADE,
    UNIQUE KEY unique_class_time (class_id, day_of_week, start_time, end_time)
);

-- Insert some sample data
INSERT INTO classes (class_name, description, capacity) VALUES 
('Class A', 'Primary class for beginners', 25),
('Class B', 'Intermediate level class', 30),
('Class C', 'Advanced level class', 20);

INSERT INTO subjects (subject_name, subject_code, description, credits) VALUES 
('Mathematics', 'MATH101', 'Basic mathematics concepts', 3),
('English Language', 'ENG101', 'English language and literature', 3),
('Science', 'SCI101', 'Basic science concepts', 2),
('Social Studies', 'SOC101', 'History and geography', 2),
('Physical Education', 'PE101', 'Physical fitness and sports', 1);

-- Create indexes for better performance
CREATE INDEX idx_student_class_assignments_student ON student_class_assignments(student_id);
CREATE INDEX idx_student_class_assignments_class ON student_class_assignments(class_id);
CREATE INDEX idx_class_subject_assignments_class ON class_subject_assignments(class_id);
CREATE INDEX idx_class_subject_assignments_subject ON class_subject_assignments(subject_id);
CREATE INDEX idx_class_timetables_class ON class_timetables(class_id);
CREATE INDEX idx_class_timetables_day_time ON class_timetables(day_of_week, start_time);

COMMIT;
