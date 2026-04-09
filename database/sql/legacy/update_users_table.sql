-- SQL script to update the users table to match login.php requirements
-- Database: folu (based on user rules)

USE folu;

-- Add role_id column if it doesn't exist
ALTER TABLE users 
ADD COLUMN role_id INT DEFAULT 1 
COMMENT 'Role ID: 1=admin, 2=teacher, 3=student, 4=parent';

-- Update status column to include 'pending' status if needed
ALTER TABLE users 
MODIFY COLUMN status ENUM('active', 'inactive', 'suspended', 'pending') NOT NULL DEFAULT 'active';

-- Migrate existing role data to role_id (if role column exists)
UPDATE users SET role_id = CASE 
    WHEN role = 'admin' THEN 1
    WHEN role = 'teacher' THEN 2  
    WHEN role = 'student' THEN 3
    WHEN role = 'parent' THEN 4
    ELSE 1
END WHERE role_id IS NULL;

-- Create roles reference table for better data management
CREATE TABLE IF NOT EXISTS user_roles (
    id INT AUTO_INCREMENT PRIMARY KEY,
    role_name VARCHAR(50) NOT NULL UNIQUE,
    description TEXT,
    permissions JSON,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Insert default roles
INSERT IGNORE INTO user_roles (id, role_name, description, permissions) VALUES
(1, 'admin', 'System Administrator', '{"all": true}'),
(2, 'teacher', 'Teacher/Staff Member', '{"students": "read", "classes": "manage", "grades": "manage"}'),
(3, 'student', 'Student User', '{"profile": "read", "grades": "read"}'),
(4, 'parent', 'Parent/Guardian', '{"child_profile": "read", "child_grades": "read"}')
