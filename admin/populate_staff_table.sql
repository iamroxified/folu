-- Populate Staff Table with Sample Data for FOLU School Management System
-- Database: dataworld (based on user rules)

USE dataworld;

-- Ensure the staff table exists (it should be created by create_missing_tables.sql)
CREATE TABLE IF NOT EXISTS staff (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    staff_id VARCHAR(20) NOT NULL UNIQUE,
    first_name VARCHAR(50) NOT NULL,
    last_name VARCHAR(50) NOT NULL,
    email VARCHAR(100) UNIQUE,
    phone VARCHAR(20),
    position VARCHAR(50),
    department ENUM('administration', 'academics', 'finance', 'maintenance', 'security', 'library', 'health', 'transport', 'catering', 'sports') DEFAULT 'administration',
    salary DECIMAL(10,2),
    hire_date DATE,
    status ENUM('active', 'inactive', 'on_leave', 'terminated') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_user_id (user_id),
    INDEX idx_staff_id (staff_id),
    INDEX idx_position (position),
    INDEX idx_department (department)
);

-- Insert sample users for staff members
INSERT INTO users (username, password, email, role, status) VALUES
('admin.principal', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'principal@school.edu', 'admin', 'active'),
('admin.vice', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'vice.principal@school.edu', 'admin', 'active'),
('librarian.jane', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'librarian@school.edu', 'admin', 'active'),
('accountant.paul', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'accountant@school.edu', 'admin', 'active'),
('secretary.grace', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'secretary@school.edu', 'admin', 'active'),
('nurse.mary', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'nurse@school.edu', 'admin', 'active'),
('security.john', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'security@school.edu', 'admin', 'active'),
('janitor.david', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'janitor@school.edu', 'admin', 'active'),
('driver.michael', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'driver@school.edu', 'admin', 'active'),
('cook.amina', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'cook@school.edu', 'admin', 'active'),
('admin.registrar', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'registrar@school.edu', 'admin', 'active'),
('counselor.sarah', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'counselor@school.edu', 'admin', 'active'),
('coordinator.sports', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'sports@school.edu', 'admin', 'active'),
('assistant.admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin.assistant@school.edu', 'admin', 'active'),
('technician.it', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'it.support@school.edu', 'admin', 'active');

-- Insert sample staff data
INSERT INTO staff (user_id, staff_id, first_name, last_name, email, phone, position, department, salary, hire_date, status) VALUES
(LAST_INSERT_ID()-14, 'STF001', 'Dr. Robert', 'Adebayo', 'principal@school.edu', '+234-901-234-5678', 'Principal', 'administration', 500000.00, '2020-01-15', 'active'),
(LAST_INSERT_ID()-13, 'STF002', 'Mrs. Angela', 'Okoro', 'vice.principal@school.edu', '+234-902-345-6789', 'Vice Principal', 'administration', 400000.00, '2020-03-01', 'active'),
(LAST_INSERT_ID()-12, 'STF003', 'Miss Jane', 'Emeka', 'librarian@school.edu', '+234-903-456-7890', 'Librarian', 'library', 180000.00, '2021-01-10', 'active'),
(LAST_INSERT_ID()-11, 'STF004', 'Mr. Paul', 'Ibrahim', 'accountant@school.edu', '+234-904-567-8901', 'Accountant', 'finance', 250000.00, '2020-06-15', 'active'),
(LAST_INSERT_ID()-10, 'STF005', 'Mrs. Grace', 'Yusuf', 'secretary@school.edu', '+234-905-678-9012', 'Secretary', 'administration', 150000.00, '2021-02-20', 'active'),
(LAST_INSERT_ID()-9, 'STF006', 'Nurse Mary', 'Bello', 'nurse@school.edu', '+234-906-789-0123', 'School Nurse', 'health', 200000.00, '2020-09-01', 'active'),
(LAST_INSERT_ID()-8, 'STF007', 'Mr. John', 'Danladi', 'security@school.edu', '+234-907-890-1234', 'Security Guard', 'security', 120000.00, '2019-11-15', 'active'),
(LAST_INSERT_ID()-7, 'STF008', 'Mr. David', 'Musa', 'janitor@school.edu', '+234-908-901-2345', 'Janitor', 'maintenance', 100000.00, '2021-05-01', 'active'),
(LAST_INSERT_ID()-6, 'STF009', 'Mr. Michael', 'Hassan', 'driver@school.edu', '+234-909-012-3456', 'School Driver', 'transport', 130000.00, '2020-12-01', 'active'),
(LAST_INSERT_ID()-5, 'STF010', 'Mrs. Amina', 'Abdullahi', 'cook@school.edu', '+234-910-123-4567', 'Head Cook', 'catering', 140000.00, '2021-03-15', 'active'),
(LAST_INSERT_ID()-4, 'STF011', 'Mr. Ahmed', 'Garba', 'registrar@school.edu', '+234-911-234-5678', 'Registrar', 'administration', 220000.00, '2020-08-01', 'active'),
(LAST_INSERT_ID()-3, 'STF012', 'Mrs. Sarah', 'Aliyu', 'counselor@school.edu', '+234-912-345-6789', 'Guidance Counselor', 'academics', 190000.00, '2021-01-05', 'active'),
(LAST_INSERT_ID()-2, 'STF013', 'Mr. James', 'Okonkwo', 'sports@school.edu', '+234-913-456-7890', 'Sports Coordinator', 'sports', 170000.00, '2020-10-01', 'active'),
(LAST_INSERT_ID()-1, 'STF014', 'Miss Ruth', 'Eze', 'admin.assistant@school.edu', '+234-914-567-8901', 'Administrative Assistant', 'administration', 140000.00, '2021-04-20', 'active'),
(LAST_INSERT_ID(), 'STF015', 'Mr. Daniel', 'Sule', 'it.support@school.edu', '+234-915-678-9012', 'IT Support Technician', 'administration', 200000.00, '2021-06-01', 'active');

-- Insert additional staff for various departments
INSERT INTO users (username, password, email, role, status) VALUES
('assistant.librarian', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'lib.assistant@school.edu', 'admin', 'active'),
('cleaner.fatima', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'cleaner@school.edu', 'admin', 'active'),
('supervisor.maintenance', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'maintenance@school.edu', 'admin', 'active'),
('cashier.accounts', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'cashier@school.edu', 'admin', 'active'),
('guard.night', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'night.guard@school.edu', 'admin', 'active');

INSERT INTO staff (user_id, staff_id, first_name, last_name, email, phone, position, department, salary, hire_date, status) VALUES
(LAST_INSERT_ID()-4, 'STF016', 'Mrs. Kemi', 'Adamu', 'lib.assistant@school.edu', '+234-916-789-0123', 'Assistant Librarian', 'library', 120000.00, '2021-07-15', 'active'),
(LAST_INSERT_ID()-3, 'STF017', 'Mrs. Fatima', 'Lawal', 'cleaner@school.edu', '+234-917-890-1234', 'Cleaning Staff', 'maintenance', 80000.00, '2021-08-01', 'active'),
(LAST_INSERT_ID()-2, 'STF018', 'Mr. Tunde', 'Olumide', 'maintenance@school.edu', '+234-918-901-2345', 'Maintenance Supervisor', 'maintenance', 160000.00, '2020-05-20', 'active'),
(LAST_INSERT_ID()-1, 'STF019', 'Miss Blessing', 'Chukwu', 'cashier@school.edu', '+234-919-012-3456', 'Cashier', 'finance', 130000.00, '2021-02-10', 'active'),
(LAST_INSERT_ID(), 'STF020', 'Mr. Usman', 'Mohammed', 'night.guard@school.edu', '+234-920-123-4567', 'Night Security Guard', 'security', 110000.00, '2020-07-01', 'active');

-- Display success message
SELECT 'Staff table populated successfully with 20 staff members!' as message;
