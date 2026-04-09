-- Create Parents Table and Populate with Sample Data for FOLU School Management System
-- Database: dataworld (based on user rules)

USE dataworld;

-- Create parents table
CREATE TABLE IF NOT EXISTS parents (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    parent_id VARCHAR(20) NOT NULL UNIQUE,
    first_name VARCHAR(50) NOT NULL,
    last_name VARCHAR(50) NOT NULL,
    email VARCHAR(100) UNIQUE,
    phone VARCHAR(20) NOT NULL,
    alternative_phone VARCHAR(20),
    occupation VARCHAR(100),
    address TEXT,
    relationship_to_student ENUM('father', 'mother', 'guardian', 'stepfather', 'stepmother', 'grandfather', 'grandmother', 'uncle', 'aunt', 'other') NOT NULL DEFAULT 'father',
    emergency_contact BOOLEAN DEFAULT FALSE,
    status ENUM('active', 'inactive') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_user_id (user_id),
    INDEX idx_parent_id (parent_id),
    INDEX idx_email (email),
    INDEX idx_phone (phone)
);

-- Create student_parents relationship table (many-to-many relationship)
CREATE TABLE IF NOT EXISTS student_parents (
    id INT AUTO_INCREMENT PRIMARY KEY,
    student_id INT NOT NULL,
    parent_id INT NOT NULL,
    relationship ENUM('father', 'mother', 'guardian', 'stepfather', 'stepmother', 'grandfather', 'grandmother', 'uncle', 'aunt', 'other') NOT NULL,
    is_primary_contact BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (student_id) REFERENCES students(id) ON DELETE CASCADE,
    FOREIGN KEY (parent_id) REFERENCES parents(id) ON DELETE CASCADE,
    UNIQUE KEY unique_student_parent (student_id, parent_id),
    INDEX idx_student_id (student_id),
    INDEX idx_parent_id (parent_id)
);

-- Insert sample users for parents (assuming users table exists)
INSERT INTO users (username, password, email, role, status) VALUES
('adebayo.john', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'adebayo.john@email.com', 'parent', 'active'),
('smith.mary', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'smith.mary@email.com', 'parent', 'active'),
('okafor.james', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'okafor.james@email.com', 'parent', 'active'),
('williams.sarah', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'williams.sarah@email.com', 'parent', 'active'),
('ibrahim.fatima', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'ibrahim.fatima@email.com', 'parent', 'active'),
('johnson.michael', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'johnson.michael@email.com', 'parent', 'active'),
('adeola.grace', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'adeola.grace@email.com', 'parent', 'active'),
('emmanuel.peter', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'emmanuel.peter@email.com', 'parent', 'active'),
('hassan.aisha', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'hassan.aisha@email.com', 'parent', 'active'),
('davies.elizabeth', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'davies.elizabeth@email.com', 'parent', 'active');

-- Insert sample parents data
INSERT INTO parents (user_id, parent_id, first_name, last_name, email, phone, alternative_phone, occupation, address, relationship_to_student, emergency_contact, status) VALUES
(LAST_INSERT_ID()-9, 'PAR001', 'John', 'Adebayo', 'adebayo.john@email.com', '+234-801-234-5678', '+234-803-456-7890', 'Engineer', '123 Lagos Street, Victoria Island, Lagos', 'father', TRUE, 'active'),
(LAST_INSERT_ID()-8, 'PAR002', 'Mary', 'Smith', 'smith.mary@email.com', '+234-802-345-6789', '+234-804-567-8901', 'Teacher', '45 Abuja Close, Garki, Abuja', 'mother', TRUE, 'active'),
(LAST_INSERT_ID()-7, 'PAR003', 'James', 'Okafor', 'okafor.james@email.com', '+234-803-456-7890', NULL, 'Business Owner', '67 Port Harcourt Road, GRA, Port Harcourt', 'father', FALSE, 'active'),
(LAST_INSERT_ID()-6, 'PAR004', 'Sarah', 'Williams', 'williams.sarah@email.com', '+234-804-567-8901', '+234-806-789-0123', 'Nurse', '89 Kano Street, Sabon Gari, Kano', 'mother', TRUE, 'active'),
(LAST_INSERT_ID()-5, 'PAR005', 'Fatima', 'Ibrahim', 'ibrahim.fatima@email.com', '+234-805-678-9012', '+234-807-890-1234', 'Accountant', '101 Kaduna Avenue, Barnawa, Kaduna', 'mother', TRUE, 'active'),
(LAST_INSERT_ID()-4, 'PAR006', 'Michael', 'Johnson', 'johnson.michael@email.com', '+234-806-789-0123', NULL, 'Doctor', '234 Ibadan Road, Bodija, Ibadan', 'father', FALSE, 'active'),
(LAST_INSERT_ID()-3, 'PAR007', 'Grace', 'Adeola', 'adeola.grace@email.com', '+234-807-890-1234', '+234-809-012-3456', 'Lawyer', '345 Benin Street, Ring Road, Benin City', 'mother', TRUE, 'active'),
(LAST_INSERT_ID()-2, 'PAR008', 'Peter', 'Emmanuel', 'emmanuel.peter@email.com', '+234-808-901-2345', '+234-810-123-4567', 'Civil Servant', '456 Enugu Crescent, Independence Layout, Enugu', 'father', TRUE, 'active'),
(LAST_INSERT_ID()-1, 'PAR009', 'Aisha', 'Hassan', 'hassan.aisha@email.com', '+234-809-012-3456', NULL, 'Pharmacist', '567 Maiduguri Boulevard, GRA, Maiduguri', 'mother', FALSE, 'active'),
(LAST_INSERT_ID(), 'PAR010', 'Elizabeth', 'Davies', 'davies.elizabeth@email.com', '+234-810-123-4567', '+234-812-345-6789', 'Architect', '678 Jos Plateau, Rayfield, Jos', 'guardian', TRUE, 'active');

-- Insert additional parent records for variety
INSERT INTO users (username, password, email, role, status) VALUES
('bello.ahmad', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'bello.ahmad@email.com', 'parent', 'active'),
('okoro.blessing', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'okoro.blessing@email.com', 'parent', 'active'),
('adamu.ibrahim', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'adamu.ibrahim@email.com', 'parent', 'active'),
('nwosu.chioma', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'nwosu.chioma@email.com', 'parent', 'active'),
('yusuf.khadija', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'yusuf.khadija@email.com', 'parent', 'active');

INSERT INTO parents (user_id, parent_id, first_name, last_name, email, phone, alternative_phone, occupation, address, relationship_to_student, emergency_contact, status) VALUES
(LAST_INSERT_ID()-4, 'PAR011', 'Ahmad', 'Bello', 'bello.ahmad@email.com', '+234-811-234-5678', '+234-813-456-7890', 'Trader', '789 Sokoto Road, Tudun Wada, Sokoto', 'father', TRUE, 'active'),
(LAST_INSERT_ID()-3, 'PAR012', 'Blessing', 'Okoro', 'okoro.blessing@email.com', '+234-812-345-6789', NULL, 'Hairdresser', '890 Owerri Street, World Bank, Owerri', 'mother', FALSE, 'active'),
(LAST_INSERT_ID()-2, 'PAR013', 'Ibrahim', 'Adamu', 'adamu.ibrahim@email.com', '+234-813-456-7890', '+234-815-678-9012', 'Farmer', '901 Gombe Avenue, Federal Low Cost, Gombe', 'father', TRUE, 'active'),
(LAST_INSERT_ID()-1, 'PAR014', 'Chioma', 'Nwosu', 'nwosu.chioma@email.com', '+234-814-567-8901', '+234-816-789-0123', 'Banker', '123 Umuahia Road, Government Station, Umuahia', 'mother', TRUE, 'active'),
(LAST_INSERT_ID(), 'PAR015', 'Khadija', 'Yusuf', 'yusuf.khadija@email.com', '+234-815-678-9012', NULL, 'Tailor', '234 Bauchi Close, Yelwa, Bauchi', 'mother', FALSE, 'active');

-- Display success message
SELECT 'Parents table created and populated successfully!' as message;
