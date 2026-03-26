-- FIMOCOL Secondary School Data Population
-- Populate all tables with realistic Nigerian secondary school data
-- Database: dataworld

USE folu;

-- 1. Insert Academic Sessions
INSERT INTO academic_sessions (session_name, start_date, end_date, is_current) VALUES
('2023/2024', '2023-09-11', '2024-07-26', FALSE),
('2024/2025', '2024-09-09', '2025-07-25', TRUE),
('2025/2026', '2025-09-08', '2026-07-24', FALSE);

-- 2. Insert Admin Users
INSERT INTO users (username, password, email, role, status) VALUES
('admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin@fimocol.edu.ng', 'admin', 'active'),
('principal', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'principal@fimocol.edu.ng', 'admin', 'active');

-- 3. Insert Teachers Users
INSERT INTO users (username, password, email, role, status) VALUES
('adebayo.mrs', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'adebayo@fimocol.edu.ng', 'teacher', 'active'),
('okafor.mr', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'okafor@fimocol.edu.ng', 'teacher', 'active'),
('ibrahim.dr', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'ibrahim@fimocol.edu.ng', 'teacher', 'active'),
('williams.miss', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'williams@fimocol.edu.ng', 'teacher', 'active'),
('mohammed.mr', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'mohammed@fimocol.edu.ng', 'teacher', 'active'),
('eze.mrs', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'eze@fimocol.edu.ng', 'teacher', 'active'),
('yusuf.mr', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'yusuf@fimocol.edu.ng', 'teacher', 'active'),
('hassan.mrs', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'hassan@fimocol.edu.ng', 'teacher', 'active');

-- 4. Insert Teachers
INSERT INTO teachers (user_id, teacher_id, first_name, last_name, email, phone, qualification, specialization, employment_date, salary, status) VALUES
(3, 'TCH001', 'Folake', 'Adebayo', 'adebayo@fimocol.edu.ng', '+234-803-123-4567', 'B.A English, M.Ed', 'English Language and Literature', '2018-01-15', 180000.00, 'active'),
(4, 'TCH002', 'Chukwuma', 'Okafor', 'okafor@fimocol.edu.ng', '+234-805-234-5678', 'B.Sc Mathematics, PGDE', 'Mathematics', '2019-03-10', 170000.00, 'active'),
(5, 'TCH003', 'Aliyu', 'Ibrahim', 'ibrahim@fimocol.edu.ng', '+234-807-345-6789', 'B.Sc Physics, Ph.D', 'Physics and Applied Sciences', '2017-09-05', 220000.00, 'active'),
(6, 'TCH004', 'Grace', 'Williams', 'williams@fimocol.edu.ng', '+234-809-456-7890', 'B.A English Literature', 'English Language', '2020-01-20', 160000.00, 'active'),
(7, 'TCH005', 'Abdullahi', 'Mohammed', 'mohammed@fimocol.edu.ng', '+234-811-567-8901', 'B.Sc Mathematics', 'Mathematics', '2019-08-15', 165000.00, 'active'),
(8, 'TCH006', 'Chioma', 'Eze', 'eze@fimocol.edu.ng', '+234-813-678-9012', 'B.Sc Physics, M.Sc', 'Physics', '2018-06-12', 190000.00, 'active'),
(9, 'TCH007', 'Musa', 'Yusuf', 'yusuf@fimocol.edu.ng', '+234-815-789-0123', 'B.A English', 'English Language', '2020-09-01', 155000.00, 'active'),
(10, 'TCH008', 'Khadija', 'Hassan', 'hassan@fimocol.edu.ng', '+234-817-890-1234', 'B.Sc Mathematics, PGDE', 'Mathematics', '2019-11-18', 175000.00, 'active');

-- 5. Insert Classes for current session (2024/2025)
INSERT INTO classes (class_name, class_arm, class_level, form_teacher_id, academic_session_id, max_capacity) VALUES
('JSS 1A', 'A', 'JSS1', 1, 2, 35),
('JSS 1B', 'B', 'JSS1', 2, 2, 35),
('JSS 2A', 'A', 'JSS2', 3, 2, 35),
('JSS 2B', 'B', 'JSS2', 4, 2, 35),
('JSS 3A', 'A', 'JSS3', 5, 2, 35),
('JSS 3B', 'B', 'JSS3', 6, 2, 35),
('SSS 1A', 'A', 'SSS1', 7, 2, 30),
('SSS 1B', 'B', 'SSS1', 8, 2, 30),
('SSS 2A', 'A', 'SSS2', 1, 2, 30),
('SSS 2B', 'B', 'SSS2', 2, 2, 30),
('SSS 3A', 'A', 'SSS3', 3, 2, 25),
('SSS 3B', 'B', 'SSS3', 4, 2, 25);

-- 6. Insert Subjects (English, Mathematics, Physics + others)
INSERT INTO subjects (subject_name, subject_code, class_level, is_core) VALUES
('English Language', 'ENG', 'ALL', TRUE),
('Mathematics', 'MTH', 'ALL', TRUE),
('Physics', 'PHY', 'ALL', TRUE),
('Chemistry', 'CHE', 'ALL', TRUE),
('Biology', 'BIO', 'ALL', TRUE),
('Civic Education', 'CIV', 'ALL', TRUE),
('Basic Science', 'BSC1', 'JSS1', TRUE),
('Basic Science', 'BSC2', 'JSS2', TRUE),
('Basic Science', 'BSC3', 'JSS3', TRUE),
('Basic Technology', 'BTN1', 'JSS1', TRUE),
('Basic Technology', 'BTN2', 'JSS2', TRUE),
('Basic Technology', 'BTN3', 'JSS3', TRUE),
('Computer Studies', 'CMP', 'ALL', FALSE),
('Fine Arts', 'ART', 'ALL', FALSE),
('Physical Education', 'PHE', 'ALL', FALSE),
('Agricultural Science', 'AGR', 'ALL', FALSE),
('Home Economics', 'HEC', 'ALL', FALSE),
('French Language', 'FRE', 'ALL', FALSE),
('Yoruba Language', 'YOR', 'ALL', FALSE),
('Hausa Language', 'HAU', 'ALL', FALSE),
('Igbo Language', 'IGB', 'ALL', FALSE),
('Geography', 'GEO', 'ALL', TRUE),
('History', 'HIS', 'ALL', FALSE),
('Economics', 'ECO', 'SSS1', FALSE),
('Government', 'GOV', 'SSS1', FALSE),
('Literature in English', 'LIT', 'SSS1', FALSE),
('Further Mathematics', 'FMT', 'SSS1', FALSE);

-- 7. Insert Parent Users
INSERT INTO users (username, password, email, role, status) VALUES
('parent001', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'adebayo.john@email.com', 'parent', 'active'),
('parent002', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'okonkwo.mary@email.com', 'parent', 'active'),
('parent003', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'ibrahim.fatima@email.com', 'parent', 'active'),
('parent004', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'williams.james@email.com', 'parent', 'active'),
('parent005', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'mohammed.aisha@email.com', 'parent', 'active'),
('parent006', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'eze.peter@email.com', 'parent', 'active'),
('parent007', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'yusuf.halima@email.com', 'parent', 'active'),
('parent008', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'hassan.ibrahim@email.com', 'parent', 'active'),
('parent009', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'daniel.grace@email.com', 'parent', 'active'),
('parent010', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'bello.ahmad@email.com', 'parent', 'active');

-- 8. Insert Parents
INSERT INTO parents (user_id, parent_id, first_name, last_name, email, phone, alternative_phone, occupation, address, relationship_to_student, emergency_contact, status) VALUES
(11, 'PAR001', 'John', 'Adebayo', 'adebayo.john@email.com', '+234-803-111-2222', '+234-805-333-4444', 'Engineer', '45 Lagos Street, Victoria Island, Lagos', 'father', TRUE, 'active'),
(12, 'PAR002', 'Mary', 'Okonkwo', 'okonkwo.mary@email.com', '+234-807-222-3333', '+234-809-444-5555', 'Teacher', '23 Abuja Close, Garki, Abuja', 'mother', TRUE, 'active'),
(13, 'PAR003', 'Fatima', 'Ibrahim', 'ibrahim.fatima@email.com', '+234-811-333-4444', '+234-813-555-6666', 'Trader', '67 Kano Road, Sabon Gari, Kano', 'mother', TRUE, 'active'),
(14, 'PAR004', 'James', 'Williams', 'williams.james@email.com', '+234-815-444-5555', NULL, 'Civil Servant', '89 Port Harcourt Street, GRA, Port Harcourt', 'father', FALSE, 'active'),
(15, 'PAR005', 'Aisha', 'Mohammed', 'mohammed.aisha@email.com', '+234-817-555-6666', '+234-819-666-7777', 'Nurse', '12 Kaduna Avenue, Barnawa, Kaduna', 'mother', TRUE, 'active'),
(16, 'PAR006', 'Peter', 'Eze', 'eze.peter@email.com', '+234-821-666-7777', '+234-823-777-8888', 'Business Owner', '34 Enugu Crescent, Independence Layout, Enugu', 'father', TRUE, 'active'),
(17, 'PAR007', 'Halima', 'Yusuf', 'yusuf.halima@email.com', '+234-825-777-8888', NULL, 'Hairdresser', '56 Sokoto Road, Tudun Wada, Sokoto', 'mother', FALSE, 'active'),
(18, 'PAR008', 'Ibrahim', 'Hassan', 'hassan.ibrahim@email.com', '+234-827-888-9999', '+234-829-999-0000', 'Farmer', '78 Maiduguri Boulevard, GRA, Maiduguri', 'father', TRUE, 'active'),
(19, 'PAR009', 'Grace', 'Daniel', 'daniel.grace@email.com', '+234-831-000-1111', '+234-833-111-2222', 'Accountant', '90 Jos Plateau, Rayfield, Jos', 'mother', TRUE, 'active'),
(20, 'PAR010', 'Ahmad', 'Bello', 'bello.ahmad@email.com', '+234-835-222-3333', NULL, 'Mechanic', '123 Gombe Avenue, Federal Low Cost, Gombe', 'father', FALSE, 'active');

-- 9. Insert Student Users
INSERT INTO users (username, password, email, role, status) VALUES
('std001', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'adebayo.temi@fimocol.edu.ng', 'student', 'active'),
('std002', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'okonkwo.chidi@fimocol.edu.ng', 'student', 'active'),
('std003', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'ibrahim.amina@fimocol.edu.ng', 'student', 'active'),
('std004', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'williams.david@fimocol.edu.ng', 'student', 'active'),
('std005', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'mohammed.hassan@fimocol.edu.ng', 'student', 'active'),
('std006', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'eze.blessing@fimocol.edu.ng', 'student', 'active'),
('std007', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'yusuf.fatima@fimocol.edu.ng', 'student', 'active'),
('std008', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'hassan.yusuf@fimocol.edu.ng', 'student', 'active'),
('std009', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'daniel.praise@fimocol.edu.ng', 'student', 'active'),
('std010', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'bello.abubakar@fimocol.edu.ng', 'student', 'active'),
('std011', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'adamu.khadija@fimocol.edu.ng', 'student', 'active'),
('std012', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'okoro.chioma@fimocol.edu.ng', 'student', 'active'),
('std013', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'musa.ibrahim@fimocol.edu.ng', 'student', 'active'),
('std014', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'james.mercy@fimocol.edu.ng', 'student', 'active'),
('std015', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'ali.zainab@fimocol.edu.ng', 'student', 'active');

-- 10. Insert Students
INSERT INTO students (user_id, admission_no, first_name, last_name, other_names, date_of_birth, gender, state_of_origin, lga, home_address, class_id, academic_session_id, admission_date, student_type, blood_group, genotype, status) VALUES
(21, 'FIM/2024/001', 'Temiloluwa', 'Adebayo', 'Grace', '2010-03-15', 'female', 'Lagos', 'Lagos Island', '45 Lagos Street, Victoria Island, Lagos', 1, 2, '2024-09-01', 'day', 'O+', 'AA', 'active'),
(22, 'FIM/2024/002', 'Chidiebere', 'Okonkwo', 'Emmanuel', '2010-07-22', 'male', 'Anambra', 'Awka North', '23 Abuja Close, Garki, Abuja', 1, 2, '2024-09-01', 'boarding', 'A+', 'AS', 'active'),
(23, 'FIM/2023/045', 'Amina', 'Ibrahim', 'Hauwa', '2009-12-08', 'female', 'Kano', 'Kano Municipal', '67 Kano Road, Sabon Gari, Kano', 3, 2, '2023-09-01', 'day', 'B+', 'AA', 'active'),
(24, 'FIM/2023/067', 'David', 'Williams', 'Praise', '2009-05-30', 'male', 'Rivers', 'Port Harcourt', '89 Port Harcourt Street, GRA, Port Harcourt', 3, 2, '2023-09-01', 'day', 'O-', 'AA', 'active'),
(25, 'FIM/2022/089', 'Hassan', 'Mohammed', 'Ali', '2008-11-12', 'male', 'Kaduna', 'Kaduna North', '12 Kaduna Avenue, Barnawa, Kaduna', 5, 2, '2022-09-01', 'boarding', 'AB+', 'AS', 'active'),
(26, 'FIM/2022/134', 'Blessing', 'Eze', 'Chioma', '2008-08-17', 'female', 'Enugu', 'Enugu North', '34 Enugu Crescent, Independence Layout, Enugu', 5, 2, '2022-09-01', 'day', 'A-', 'AA', 'active'),
(27, 'FIM/2021/156', 'Fatima', 'Yusuf', 'Khadija', '2007-04-03', 'female', 'Sokoto', 'Sokoto North', '56 Sokoto Road, Tudun Wada, Sokoto', 7, 2, '2021-09-01', 'day', 'B-', 'AA', 'active'),
(28, 'FIM/2021/178', 'Yusuf', 'Hassan', 'Musa', '2007-09-25', 'male', 'Borno', 'Maiduguri', '78 Maiduguri Boulevard, GRA, Maiduguri', 7, 2, '2021-09-01', 'boarding', 'O+', 'AS', 'active'),
(29, 'FIM/2020/201', 'Praise', 'Daniel', 'Joy', '2006-02-14', 'female', 'Plateau', 'Jos North', '90 Jos Plateau, Rayfield, Jos', 9, 2, '2020-09-01', 'day', 'A+', 'AA', 'active'),
(30, 'FIM/2020/223', 'Abubakar', 'Bello', 'Sani', '2006-06-18', 'male', 'Gombe', 'Gombe', '123 Gombe Avenue, Federal Low Cost, Gombe', 9, 2, '2020-09-01', 'day', 'B+', 'AA', 'active'),
(31, 'FIM/2019/245', 'Khadija', 'Adamu', 'Fatima', '2005-10-07', 'female', 'Bauchi', 'Bauchi', '234 Bauchi Close, Yelwa, Bauchi', 11, 2, '2019-09-01', 'boarding', 'O-', 'AS', 'active'),
(32, 'FIM/2019/267', 'Chioma', 'Okoro', 'Grace', '2005-12-23', 'female', 'Imo', 'Owerri West', '345 Owerri Street, World Bank, Owerri', 11, 2, '2019-09-01', 'day', 'AB-', 'AA', 'active'),
(33, 'FIM/2024/003', 'Ibrahim', 'Musa', 'Garba', '2010-01-11', 'male', 'Niger', 'Minna', '456 Minna Road, GRA, Minna', 2, 2, '2024-09-01', 'day', 'A+', 'AA', 'active'),
(34, 'FIM/2023/089', 'Mercy', 'James', 'Faith', '2009-08-05', 'female', 'Cross River', 'Calabar Municipal', '567 Calabar Street, State Housing, Calabar', 4, 2, '2023-09-01', 'day', 'O+', 'AS', 'active'),
(35, 'FIM/2021/190', 'Zainab', 'Ali', 'Hauwa', '2007-03-28', 'female', 'Kebbi', 'Birnin Kebbi', '678 Kebbi Avenue, GRA, Birnin Kebbi', 8, 2, '2021-09-01', 'boarding', 'B+', 'AA', 'active');

-- 11. Insert Student-Parent Relationships
INSERT INTO student_parents (student_id, parent_id, relationship, is_primary_contact) VALUES
(1, 1, 'father', TRUE),
(2, 2, 'mother', TRUE),
(3, 3, 'mother', TRUE),
(4, 4, 'father', TRUE),
(5, 5, 'mother', TRUE),
(6, 6, 'father', TRUE),
(7, 7, 'mother', TRUE),
(8, 8, 'father', TRUE),
(9, 9, 'mother', TRUE),
(10, 10, 'father', TRUE),
(11, 1, 'father', FALSE),
(12, 2, 'mother', FALSE),
(13, 3, 'mother', FALSE),
(14, 4, 'father', FALSE),
(15, 5, 'mother', FALSE);

-- 12. Insert Teacher-Subject Assignments
INSERT INTO teacher_subjects (teacher_id, subject_id, class_id, academic_session_id) VALUES
-- English Teachers
(1, 1, 1, 2), (1, 1, 2, 2), (4, 1, 3, 2), (4, 1, 4, 2), (7, 1, 5, 2), (7, 1, 6, 2),
-- Mathematics Teachers  
(2, 2, 1, 2), (2, 2, 2, 2), (5, 2, 3, 2), (5, 2, 4, 2), (8, 2, 5, 2), (8, 2, 6, 2),
-- Physics Teachers
(3, 3, 7, 2), (3, 3, 8, 2), (6, 3, 9, 2), (6, 3, 10, 2), (3, 3, 11, 2), (3, 3, 12, 2),
-- Basic Science for JSS (updated subject IDs)
(3, 7, 1, 2), (3, 8, 3, 2), (6, 9, 5, 2),
-- Chemistry Teachers
(3, 4, 7, 2), (6, 4, 9, 2),
-- Biology Teachers
(6, 5, 8, 2), (3, 5, 10, 2),
-- Geography Teachers
(4, 22, 7, 2), (7, 22, 8, 2);

-- 13. Insert Fee Structures for 2024/2025 session
INSERT INTO fee_structures (fee_name, class_level, student_type, amount, term, academic_session_id, description) VALUES
-- Day Students Fees
('School Fees', 'JSS1', 'day', 35000.00, 'first_term', 2, 'Tuition and development levy for JSS1 day students'),
('School Fees', 'JSS2', 'day', 37000.00, 'first_term', 2, 'Tuition and development levy for JSS2 day students'),
('School Fees', 'JSS3', 'day', 40000.00, 'first_term', 2, 'Tuition and development levy for JSS3 day students'),
('School Fees', 'SSS1', 'day', 45000.00, 'first_term', 2, 'Tuition and development levy for SSS1 day students'),
('School Fees', 'SSS2', 'day', 48000.00, 'first_term', 2, 'Tuition and development levy for SSS2 day students'),
('School Fees', 'SSS3', 'day', 50000.00, 'first_term', 2, 'Tuition and development levy for SSS3 day students'),
-- Boarding Students Fees
('School Fees', 'JSS1', 'boarding', 65000.00, 'first_term', 2, 'Tuition, accommodation and feeding for JSS1 boarding students'),
('School Fees', 'JSS2', 'boarding', 67000.00, 'first_term', 2, 'Tuition, accommodation and feeding for JSS2 boarding students'),
('School Fees', 'JSS3', 'boarding', 70000.00, 'first_term', 2, 'Tuition, accommodation and feeding for JSS3 boarding students'),
('School Fees', 'SSS1', 'boarding', 75000.00, 'first_term', 2, 'Tuition, accommodation and feeding for SSS1 boarding students'),
('School Fees', 'SSS2', 'boarding', 78000.00, 'first_term', 2, 'Tuition, accommodation and feeding for SSS2 boarding students'),
('School Fees', 'SSS3', 'boarding', 80000.00, 'first_term', 2, 'Tuition, accommodation and feeding for SSS3 boarding students'),
-- Other Fees
('PTA Levy', 'ALL', 'ALL', 5000.00, 'first_term', 2, 'Parent Teacher Association annual levy'),
('Sports Levy', 'ALL', 'ALL', 3000.00, 'first_term', 2, 'Sports and recreational activities levy'),
('Examination Fee', 'SSS3', 'ALL', 15000.00, 'first_term', 2, 'WAEC and NECO examination fees');

-- 14. Insert Staff Users
INSERT INTO users (username, password, email, role, status) VALUES
('staff001', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'secretary@fimocol.edu.ng', 'staff', 'active'),
('staff002', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'accountant@fimocol.edu.ng', 'staff', 'active'),
('staff003', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'librarian@fimocol.edu.ng', 'staff', 'active'),
('staff004', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'nurse@fimocol.edu.ng', 'staff', 'active'),
('staff005', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'security@fimocol.edu.ng', 'staff', 'active');

-- 15. Insert Staff
INSERT INTO staff (user_id, staff_id, first_name, last_name, email, phone, position, department, salary, hire_date, status) VALUES
(36, 'STF001', 'Adunni', 'Ogundimu', 'secretary@fimocol.edu.ng', '+234-801-111-2222', 'School Secretary', 'administration', 120000.00, '2020-02-01', 'active'),
(37, 'STF002', 'Emeka', 'Nwankwo', 'accountant@fimocol.edu.ng', '+234-803-222-3333', 'School Accountant', 'finance', 180000.00, '2019-06-15', 'active'),
(38, 'STF003', 'Hauwa', 'Aliyu', 'librarian@fimocol.edu.ng', '+234-805-333-4444', 'School Librarian', 'library', 130000.00, '2021-01-10', 'active'),
(39, 'STF004', 'Comfort', 'Udo', 'nurse@fimocol.edu.ng', '+234-807-444-5555', 'School Nurse', 'health', 150000.00, '2020-08-20', 'active'),
(40, 'STF005', 'Ibrahim', 'Lawal', 'security@fimocol.edu.ng', '+234-809-555-6666', 'Head of Security', 'security', 100000.00, '2018-03-12', 'active');

-- 16. Insert Sample Grades for First Term
INSERT INTO grades (student_id, subject_id, class_id, term, exam_type, score, max_score, grade, academic_session_id) VALUES
-- JSS1A Students - English
(1, 1, 1, 'first_term', 'test', 78.50, 100.00, 'B', 2),
(2, 1, 1, 'first_term', 'test', 85.00, 100.00, 'A', 2),
-- JSS1A Students - Mathematics  
(1, 2, 1, 'first_term', 'test', 72.00, 100.00, 'B', 2),
(2, 2, 1, 'first_term', 'test', 88.50, 100.00, 'A', 2),
-- JSS2A Students - English
(3, 1, 3, 'first_term', 'test', 80.00, 100.00, 'A', 2),
(4, 1, 3, 'first_term', 'test', 75.50, 100.00, 'B', 2),
-- JSS2A Students - Mathematics
(3, 2, 3, 'first_term', 'test', 77.00, 100.00, 'B', 2),
(4, 2, 3, 'first_term', 'test', 82.50, 100.00, 'A', 2),
-- SSS1A Students - Physics
(7, 3, 7, 'first_term', 'test', 73.00, 100.00, 'B', 2),
(8, 3, 7, 'first_term', 'test', 79.50, 100.00, 'B', 2);

-- 17. Insert Sample Attendance
INSERT INTO attendance (student_id, class_id, date, status, remarks) VALUES
(1, 1, '2024-09-09', 'present', NULL),
(2, 1, '2024-09-09', 'present', NULL),
(1, 1, '2024-09-10', 'late', 'Arrived 15 minutes late'),
(2, 1, '2024-09-10', 'present', NULL),
(3, 3, '2024-09-09', 'present', NULL),
(4, 3, '2024-09-09', 'absent', 'Sick'),
(3, 3, '2024-09-10', 'present', NULL),
(4, 3, '2024-09-10', 'present', NULL);

SELECT 'FIMOCOL data populated successfully!' as message,
       (SELECT COUNT(*) FROM students) as total_students,
       (SELECT COUNT(*) FROM teachers) as total_teachers,
       (SELECT COUNT(*) FROM parents) as total_parents,
       (SELECT COUNT(*) FROM staff) as total_staff;
