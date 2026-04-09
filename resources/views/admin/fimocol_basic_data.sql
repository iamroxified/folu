-- FIMOCOL Secondary School Data Population (Basic MyISAM Version)
-- Sample data for Nigerian secondary school
-- Database: folu

USE folu;

-- Disable foreign key checks completely
SET FOREIGN_KEY_CHECKS = 0;
SET SQL_MODE = '';

-- 1. Insert Academic Sessions
INSERT INTO academic_sessions (session_name, start_date, end_date, is_current) VALUES
('2023/2024', '2023-09-01', '2024-08-31', 0),
('2024/2025', '2024-09-01', '2025-08-31', 1),
('2025/2026', '2025-09-01', '2026-08-31', 0);

-- 2. Insert Users (Admin, Teachers, Students, Parents, Staff)
INSERT INTO users (username, password, email, role, status) VALUES
-- Admins
('admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin@fimocol.edu.ng', 'admin', 'active'),
('principal', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'principal@fimocol.edu.ng', 'admin', 'active'),

-- Teachers
('adebayo.mrs', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'adebayo@fimocol.edu.ng', 'teacher', 'active'),
('okafor.mr', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'okafor@fimocol.edu.ng', 'teacher', 'active'),
('ibrahim.dr', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'ibrahim@fimocol.edu.ng', 'teacher', 'active'),
('olumide.mrs', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'olumide@fimocol.edu.ng', 'teacher', 'active'),
('hassan.mr', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'hassan@fimocol.edu.ng', 'teacher', 'active'),
('amina.mrs', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'amina@fimocol.edu.ng', 'teacher', 'active'),
('peter.mr', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'peter@fimocol.edu.ng', 'teacher', 'active'),
('kemi.ms', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'kemi@fimocol.edu.ng', 'teacher', 'active'),

-- Students (15 sample students)
('std001', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'adamu.ibrahim@student.fimocol.edu.ng', 'student', 'active'),
('std002', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'fatima.yusuf@student.fimocol.edu.ng', 'student', 'active'),
('std003', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'chioma.okafor@student.fimocol.edu.ng', 'student', 'active'),
('std004', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'david.adebayo@student.fimocol.edu.ng', 'student', 'active'),
('std005', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'aisha.mohammed@student.fimocol.edu.ng', 'student', 'active'),
('std006', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'john.peter@student.fimocol.edu.ng', 'student', 'active'),
('std007', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'blessing.okoro@student.fimocol.edu.ng', 'student', 'active'),
('std008', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'musa.usman@student.fimocol.edu.ng', 'student', 'active'),
('std009', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'grace.emmanuel@student.fimocol.edu.ng', 'student', 'active'),
('std010', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'suleiman.audu@student.fimocol.edu.ng', 'student', 'active'),
('std011', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'mary.joseph@student.fimocol.edu.ng', 'student', 'active'),
('std012', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'samuel.daniel@student.fimocol.edu.ng', 'student', 'active'),
('std013', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'halima.yusuf@student.fimocol.edu.ng', 'student', 'active'),
('std014', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'victor.nkem@student.fimocol.edu.ng', 'student', 'active'),
('std015', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'rahmat.bello@student.fimocol.edu.ng', 'student', 'active'),

-- Parents
('parent001', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'ibrahim.adamu@parent.fimocol.edu.ng', 'parent', 'active'),
('parent002', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'yusuf.fatima@parent.fimocol.edu.ng', 'parent', 'active'),
('parent003', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'okafor.chioma@parent.fimocol.edu.ng', 'parent', 'active'),
('parent004', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'adebayo.david@parent.fimocol.edu.ng', 'parent', 'active'),
('parent005', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'mohammed.aisha@parent.fimocol.edu.ng', 'parent', 'active'),
('parent006', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'peter.john@parent.fimocol.edu.ng', 'parent', 'active'),
('parent007', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'okoro.blessing@parent.fimocol.edu.ng', 'parent', 'active'),
('parent008', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'usman.musa@parent.fimocol.edu.ng', 'parent', 'active'),
('parent009', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'emmanuel.grace@parent.fimocol.edu.ng', 'parent', 'active'),
('parent010', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'audu.suleiman@parent.fimocol.edu.ng', 'parent', 'active'),

-- Staff
('staff001', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'secretary@fimocol.edu.ng', 'staff', 'active'),
('staff002', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'accountant@fimocol.edu.ng', 'staff', 'active'),
('staff003', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'librarian@fimocol.edu.ng', 'staff', 'active'),
('staff004', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'security@fimocol.edu.ng', 'staff', 'active'),
('staff005', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'nurse@fimocol.edu.ng', 'staff', 'active');

-- 3. Insert Teachers
INSERT INTO teachers (user_link, teacher_id, first_name, last_name, email, phone, qualification, specialization, employment_date, salary, status) VALUES
(3, 'TCH001', 'Folake', 'Adebayo', 'adebayo@fimocol.edu.ng', '08012345601', 'B.Ed English, M.A Literature', 'English Language and Literature', '2020-01-15', 450000.00, 'active'),
(4, 'TCH002', 'Chukwudi', 'Okafor', 'okafor@fimocol.edu.ng', '08012345602', 'B.Sc Mathematics, PGDE', 'Mathematics and Statistics', '2019-03-20', 480000.00, 'active'),
(5, 'TCH003', 'Abdullahi', 'Ibrahim', 'ibrahim@fimocol.edu.ng', '08012345603', 'B.Sc Physics, M.Sc Physics, Ph.D', 'Physics and Applied Physics', '2018-01-10', 650000.00, 'active'),
(6, 'TCH004', 'Olumide', 'Adeoye', 'olumide@fimocol.edu.ng', '08012345604', 'B.Sc Chemistry, PGDE', 'Chemistry and Applied Chemistry', '2021-09-01', 420000.00, 'active'),
(7, 'TCH005', 'Hassan', 'Musa', 'hassan@fimocol.edu.ng', '08012345605', 'B.Sc Biology, M.Sc Ecology', 'Biology and Agricultural Science', '2020-06-15', 470000.00, 'active'),
(8, 'TCH006', 'Amina', 'Bello', 'amina@fimocol.edu.ng', '08012345606', 'B.A Geography, M.A Urban Planning', 'Geography and Environmental Studies', '2019-11-10', 440000.00, 'active'),
(9, 'TCH007', 'Peter', 'Nwosu', 'peter@fimocol.edu.ng', '08012345607', 'B.A History, PGDE', 'History and Government', '2022-01-05', 400000.00, 'active'),
(10, 'TCH008', 'Kemi', 'Ogundimu', 'kemi@fimocol.edu.ng', '08012345608', 'B.A Economics, M.Sc Economics', 'Economics and Commerce', '2020-10-20', 490000.00, 'active');

-- 4. Insert Classes (Nigerian Secondary School Structure)
INSERT INTO classes (class_name, class_arm, class_level, form_teacher_link, academic_session_link, max_capacity) VALUES
('JSS1A', 'A', 'JSS1', 3, 2, 40),  -- Adebayo
('JSS1B', 'B', 'JSS1', 4, 2, 40),  -- Okafor
('JSS2A', 'A', 'JSS2', 5, 2, 40),  -- Ibrahim
('JSS2B', 'B', 'JSS2', 6, 2, 40),  -- Olumide
('JSS3A', 'A', 'JSS3', 7, 2, 40),  -- Hassan
('JSS3B', 'B', 'JSS3', 8, 2, 38),  -- Amina
('SSS1A', 'A', 'SSS1', 9, 2, 35),  -- Peter
('SSS1B', 'B', 'SSS1', 10, 2, 35), -- Kemi
('SSS2A', 'A', 'SSS2', 3, 2, 30),  -- Adebayo
('SSS2B', 'B', 'SSS2', 4, 2, 30),  -- Okafor
('SSS3A', 'A', 'SSS3', 5, 2, 25),  -- Ibrahim
('SSS3B', 'B', 'SSS3', 6, 2, 25);  -- Olumide

-- 5. Insert Parents
INSERT INTO parents (user_link, parent_id, first_name, last_name, email, phone, alternative_phone, occupation, address, relationship_to_student, emergency_contact, status) VALUES
(18, 'PAR001', 'Musa', 'Adamu', 'ibrahim.adamu@parent.fimocol.edu.ng', '08023456701', '08134567801', 'Civil Servant', 'No. 12, Ahmadu Bello Way, Kaduna', 'father', 1, 'active'),
(19, 'PAR002', 'Hajiya', 'Yusuf', 'yusuf.fatima@parent.fimocol.edu.ng', '08023456702', '08134567802', 'Trader', 'No. 45, Ibrahim Taiwo Road, Ilorin', 'mother', 1, 'active'),
(20, 'PAR003', 'Emeka', 'Okafor', 'okafor.chioma@parent.fimocol.edu.ng', '08023456703', '08134567803', 'Engineer', 'No. 78, Owerri Road, Enugu', 'father', 1, 'active'),
(21, 'PAR004', 'Funmi', 'Adebayo', 'adebayo.david@parent.fimocol.edu.ng', '08023456704', '08134567804', 'Lawyer', 'No. 23, Bodija Estate, Ibadan', 'mother', 1, 'active'),
(22, 'PAR005', 'Alhaji', 'Mohammed', 'mohammed.aisha@parent.fimocol.edu.ng', '08023456705', '08134567805', 'Businessman', 'No. 56, Bello Road, Kano', 'father', 0, 'active'),
(23, 'PAR006', 'Grace', 'Peter', 'peter.john@parent.fimocol.edu.ng', '08023456706', '08134567806', 'Teacher', 'No. 34, New Haven, Enugu', 'mother', 1, 'active'),
(24, 'PAR007', 'Chidi', 'Okoro', 'okoro.blessing@parent.fimocol.edu.ng', '08023456707', '08134567807', 'Doctor', 'No. 67, GRA, Port Harcourt', 'father', 1, 'active'),
(25, 'PAR008', 'Hadiza', 'Usman', 'usman.musa@parent.fimocol.edu.ng', '08023456708', '08134567808', 'Nurse', 'No. 89, Sabon Gari, Zaria', 'mother', 0, 'active'),
(26, 'PAR009', 'Paul', 'Emmanuel', 'emmanuel.grace@parent.fimocol.edu.ng', '08023456709', '08134567809', 'Mechanic', 'No. 12, Wuse II, Abuja', 'father', 1, 'active'),
(27, 'PAR010', 'Zainab', 'Audu', 'audu.suleiman@parent.fimocol.edu.ng', '08023456710', '08134567810', 'Accountant', 'No. 45, Lokoja Road, Kogi', 'mother', 1, 'active');

-- 6. Insert Students (15 Nigerian students)
INSERT INTO students (user_link, admission_no, first_name, last_name, other_names, date_of_birth, gender, state_of_origin, lga, home_address, class_link, academic_session_link, admission_date, student_type, blood_group, genotype, status) VALUES
(11, 'FIMO/2024/001', 'Adamu', 'Ibrahim', 'Musa', '2010-03-15', 'male', 'Kaduna', 'Kaduna North', 'No. 12, Ahmadu Bello Way, Kaduna', 1, 2, '2024-09-01', 'boarding', 'O+', 'AA', 'active'),
(12, 'FIMO/2024/002', 'Fatima', 'Yusuf', 'Aisha', '2009-07-22', 'female', 'Kwara', 'Ilorin South', 'No. 45, Ibrahim Taiwo Road, Ilorin', 1, 2, '2024-09-01', 'day', 'A+', 'AS', 'active'),
(13, 'FIMO/2024/003', 'Chioma', 'Okafor', 'Ngozi', '2010-11-08', 'female', 'Enugu', 'Enugu East', 'No. 78, Owerri Road, Enugu', 2, 2, '2024-09-01', 'boarding', 'B+', 'AA', 'active'),
(14, 'FIMO/2024/004', 'David', 'Adebayo', 'Olumide', '2009-12-30', 'male', 'Oyo', 'Ibadan North', 'No. 23, Bodija Estate, Ibadan', 3, 2, '2024-09-01', 'day', 'AB+', 'AA', 'active'),
(15, 'FIMO/2024/005', 'Aisha', 'Mohammed', 'Khadija', '2010-05-14', 'female', 'Kano', 'Kano Municipal', 'No. 56, Bello Road, Kano', 3, 2, '2024-09-01', 'boarding', 'O-', 'AS', 'active'),
(16, 'FIMO/2024/006', 'John', 'Peter', 'Chukwudi', '2009-09-18', 'male', 'Enugu', 'Nkanu East', 'No. 34, New Haven, Enugu', 4, 2, '2024-09-01', 'boarding', 'A-', 'AA', 'active'),
(17, 'FIMO/2024/007', 'Blessing', 'Okoro', 'Chinyere', '2010-01-25', 'female', 'Rivers', 'Port Harcourt', 'No. 67, GRA, Port Harcourt', 5, 2, '2024-09-01', 'day', 'B-', 'AA', 'active'),
(18, 'FIMO/2024/008', 'Musa', 'Usman', 'Salisu', '2009-04-12', 'male', 'Kaduna', 'Zaria', 'No. 89, Sabon Gari, Zaria', 6, 2, '2024-09-01', 'boarding', 'AB-', 'AS', 'active'),
(19, 'FIMO/2024/009', 'Grace', 'Emmanuel', 'Mary', '2008-08-07', 'female', 'FCT', 'Abuja Municipal', 'No. 12, Wuse II, Abuja', 7, 2, '2024-09-01', 'day', 'O+', 'AA', 'active'),
(20, 'FIMO/2024/010', 'Suleiman', 'Audu', 'Yakubu', '2008-06-29', 'male', 'Kogi', 'Lokoja', 'No. 45, Lokoja Road, Kogi', 8, 2, '2024-09-01', 'boarding', 'A+', 'AA', 'active'),
(21, 'FIMO/2024/011', 'Mary', 'Joseph', 'Elizabeth', '2007-10-16', 'female', 'Cross River', 'Calabar Municipal', 'No. 78, Calabar Road, Calabar', 9, 2, '2024-09-01', 'boarding', 'B+', 'AS', 'active'),
(22, 'FIMO/2024/012', 'Samuel', 'Daniel', 'Joshua', '2007-02-03', 'male', 'Plateau', 'Jos North', 'No. 34, Rayfield, Jos', 10, 2, '2024-09-01', 'day', 'O-', 'AA', 'active'),
(23, 'FIMO/2024/013', 'Halima', 'Yusuf', 'Hauwa', '2006-12-11', 'female', 'Bauchi', 'Bauchi', 'No. 56, Yelwa Road, Bauchi', 11, 2, '2024-09-01', 'boarding', 'A-', 'AA', 'active'),
(24, 'FIMO/2024/014', 'Victor', 'Nkem', 'Ikechukwu', '2006-07-28', 'male', 'Imo', 'Owerri Municipal', 'No. 89, Owerri Road, Owerri', 12, 2, '2024-09-01', 'day', 'AB+', 'AS', 'active'),
(25, 'FIMO/2024/015', 'Rahmat', 'Bello', 'Salamat', '2006-11-19', 'female', 'Niger', 'Minna', 'No. 23, Minna Road, Niger', 11, 2, '2024-09-01', 'boarding', 'B-', 'AA', 'active');

-- 7. Insert Student-Parent relationships
INSERT INTO student_parents (student_link, parent_link, relationship, is_primary_contact) VALUES
(1, 1, 'father', 1),   -- Adamu - Musa Adamu
(2, 2, 'mother', 1),   -- Fatima - Hajiya Yusuf
(3, 3, 'father', 1),   -- Chioma - Emeka Okafor
(4, 4, 'mother', 1),   -- David - Funmi Adebayo
(5, 5, 'father', 1),   -- Aisha - Alhaji Mohammed
(6, 6, 'mother', 1),   -- John - Grace Peter
(7, 7, 'father', 1),   -- Blessing - Chidi Okoro
(8, 8, 'mother', 1),   -- Musa - Hadiza Usman
(9, 9, 'father', 1),   -- Grace - Paul Emmanuel
(10, 10, 'mother', 1), -- Suleiman - Zainab Audu
(11, 1, 'father', 0),  -- Mary - Musa Adamu (second child)
(12, 2, 'mother', 0),  -- Samuel - Hajiya Yusuf (second child)
(13, 3, 'father', 0),  -- Halima - Emeka Okafor (second child)
(14, 4, 'mother', 0),  -- Victor - Funmi Adebayo (second child)
(15, 5, 'father', 0);  -- Rahmat - Alhaji Mohammed (second child)

-- 8. Insert Subjects (Nigerian Secondary School Curriculum)
INSERT INTO subjects (subject_name, subject_code, class_level, is_core) VALUES
-- Core subjects for all levels
('English Language', 'ENG', 'ALL', 1),
('Mathematics', 'MATH', 'ALL', 1),
('Civic Education', 'CIV', 'ALL', 1),

-- JSS subjects
('Basic Science', 'BSC', 'JSS1', 1),
('Basic Science', 'BSC', 'JSS2', 1),
('Basic Science', 'BSC', 'JSS3', 1),
('Social Studies', 'SST', 'JSS1', 1),
('Social Studies', 'SST', 'JSS2', 1),
('Social Studies', 'SST', 'JSS3', 1),
('Basic Technology', 'BTECH', 'JSS1', 1),
('Basic Technology', 'BTECH', 'JSS2', 1),
('Basic Technology', 'BTECH', 'JSS3', 1),

-- SSS Science subjects
('Physics', 'PHY', 'SSS1', 0),
('Physics', 'PHY', 'SSS2', 0),
('Physics', 'PHY', 'SSS3', 0),
('Chemistry', 'CHEM', 'SSS1', 0),
('Chemistry', 'CHEM', 'SSS2', 0),
('Chemistry', 'CHEM', 'SSS3', 0),
('Biology', 'BIO', 'SSS1', 0),
('Biology', 'BIO', 'SSS2', 0),
('Biology', 'BIO', 'SSS3', 0),

-- SSS Arts subjects
('Government', 'GOV', 'SSS1', 0),
('Government', 'GOV', 'SSS2', 0),
('Government', 'GOV', 'SSS3', 0),
('Economics', 'ECON', 'SSS1', 0),
('Economics', 'ECON', 'SSS2', 0),
('Economics', 'ECON', 'SSS3', 0),
('Geography', 'GEO', 'SSS1', 0),
('Geography', 'GEO', 'SSS2', 0),
('Geography', 'GEO', 'SSS3', 0);

-- 9. Insert Staff
INSERT INTO staff (user_link, staff_id, first_name, last_name, email, phone, position, department, salary, hire_date, status) VALUES
(28, 'STF001', 'Khadija', 'Aliyu', 'secretary@fimocol.edu.ng', '08034567801', 'School Secretary', 'administration', 280000.00, '2022-01-15', 'active'),
(29, 'STF002', 'Emeka', 'Okonkwo', 'accountant@fimocol.edu.ng', '08034567802', 'School Accountant', 'finance', 380000.00, '2021-03-10', 'active'),
(30, 'STF003', 'Fatima', 'Garba', 'librarian@fimocol.edu.ng', '08034567803', 'School Librarian', 'library', 320000.00, '2022-09-01', 'active'),
(31, 'STF004', 'John', 'Okeke', 'security@fimocol.edu.ng', '08034567804', 'Security Officer', 'security', 200000.00, '2023-01-05', 'active'),
(32, 'STF005', 'Aisha', 'Baba', 'nurse@fimocol.edu.ng', '08034567805', 'School Nurse', 'health', 350000.00, '2022-06-20', 'active');

-- Re-enable foreign key checks
SET FOREIGN_KEY_CHECKS = 1;

SELECT 'FIMOCOL sample data populated successfully with Nigerian context (MyISAM Engine)!' as message;
