-- FIMOCOL Secondary School Data Population (Zero FK Version)
-- Sample data for Nigerian secondary school
-- Database: folu

USE folu;

-- 1. Insert Academic Sessions
INSERT INTO academic_sessions (session_name, start_date, end_date, is_current) VALUES
('2023/2024', '2023-09-01', '2024-08-31', FALSE),
('2024/2025', '2024-09-01', '2025-08-31', TRUE),
('2025/2026', '2025-09-01', '2026-08-31', FALSE);

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
INSERT INTO teachers (user_ref, teacher_id, first_name, last_name, email, phone, qualification, specialization, employment_date, salary, status) VALUES
(3, 'TCH001', 'Folake', 'Adebayo', 'adebayo@fimocol.edu.ng', '08012345601', 'B.Ed English, M.A Literature', 'English Language and Literature', '2020-01-15', 450000.00, 'active'),
(4, 'TCH002', 'Chukwudi', 'Okafor', 'okafor@fimocol.edu.ng', '08012345602', 'B.Sc Mathematics, PGDE', 'Mathematics and Statistics', '2019-03-20', 480000.00, 'active'),
(5, 'TCH003', 'Abdullahi', 'Ibrahim', 'ibrahim@fimocol.edu.ng', '08012345603', 'B.Sc Physics, M.Sc Physics, Ph.D', 'Physics and Applied Physics', '2018-01-10', 650000.00, 'active'),
(6, 'TCH004', 'Olumide', 'Adeoye', 'olumide@fimocol.edu.ng', '08012345604', 'B.Sc Chemistry, PGDE', 'Chemistry and Applied Chemistry', '2021-09-01', 420000.00, 'active'),
(7, 'TCH005', 'Hassan', 'Musa', 'hassan@fimocol.edu.ng', '08012345605', 'B.Sc Biology, M.Sc Ecology', 'Biology and Agricultural Science', '2020-06-15', 470000.00, 'active'),
(8, 'TCH006', 'Amina', 'Bello', 'amina@fimocol.edu.ng', '08012345606', 'B.A Geography, M.A Urban Planning', 'Geography and Environmental Studies', '2019-11-10', 440000.00, 'active'),
(9, 'TCH007', 'Peter', 'Nwosu', 'peter@fimocol.edu.ng', '08012345607', 'B.A History, PGDE', 'History and Government', '2022-01-05', 400000.00, 'active'),
(10, 'TCH008', 'Kemi', 'Ogundimu', 'kemi@fimocol.edu.ng', '08012345608', 'B.A Economics, M.Sc Economics', 'Economics and Commerce', '2020-10-20', 490000.00, 'active');

-- 4. Insert Classes (Nigerian Secondary School Structure)
INSERT INTO classes (class_name, class_arm, class_level, form_teacher_ref, academic_session_ref, max_capacity) VALUES
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
INSERT INTO parents (user_ref, parent_id, first_name, last_name, email, phone, alternative_phone, occupation, address, relationship_to_student, emergency_contact, status) VALUES
(18, 'PAR001', 'Musa', 'Adamu', 'ibrahim.adamu@parent.fimocol.edu.ng', '08023456701', '08134567801', 'Civil Servant', 'No. 12, Ahmadu Bello Way, Kaduna', 'father', TRUE, 'active'),
(19, 'PAR002', 'Hajiya', 'Yusuf', 'yusuf.fatima@parent.fimocol.edu.ng', '08023456702', '08134567802', 'Trader', 'No. 45, Ibrahim Taiwo Road, Ilorin', 'mother', TRUE, 'active'),
(20, 'PAR003', 'Emeka', 'Okafor', 'okafor.chioma@parent.fimocol.edu.ng', '08023456703', '08134567803', 'Engineer', 'No. 78, Owerri Road, Enugu', 'father', TRUE, 'active'),
(21, 'PAR004', 'Funmi', 'Adebayo', 'adebayo.david@parent.fimocol.edu.ng', '08023456704', '08134567804', 'Lawyer', 'No. 23, Bodija Estate, Ibadan', 'mother', TRUE, 'active'),
(22, 'PAR005', 'Alhaji', 'Mohammed', 'mohammed.aisha@parent.fimocol.edu.ng', '08023456705', '08134567805', 'Businessman', 'No. 56, Bello Road, Kano', 'father', FALSE, 'active'),
(23, 'PAR006', 'Grace', 'Peter', 'peter.john@parent.fimocol.edu.ng', '08023456706', '08134567806', 'Teacher', 'No. 34, New Haven, Enugu', 'mother', TRUE, 'active'),
(24, 'PAR007', 'Chidi', 'Okoro', 'okoro.blessing@parent.fimocol.edu.ng', '08023456707', '08134567807', 'Doctor', 'No. 67, GRA, Port Harcourt', 'father', TRUE, 'active'),
(25, 'PAR008', 'Hadiza', 'Usman', 'usman.musa@parent.fimocol.edu.ng', '08023456708', '08134567808', 'Nurse', 'No. 89, Sabon Gari, Zaria', 'mother', FALSE, 'active'),
(26, 'PAR009', 'Paul', 'Emmanuel', 'emmanuel.grace@parent.fimocol.edu.ng', '08023456709', '08134567809', 'Mechanic', 'No. 12, Wuse II, Abuja', 'father', TRUE, 'active'),
(27, 'PAR010', 'Zainab', 'Audu', 'audu.suleiman@parent.fimocol.edu.ng', '08023456710', '08134567810', 'Accountant', 'No. 45, Lokoja Road, Kogi', 'mother', TRUE, 'active');

-- 6. Insert Students (15 Nigerian students)
INSERT INTO students (user_ref, admission_no, first_name, last_name, other_names, date_of_birth, gender, state_of_origin, lga, home_address, class_ref, academic_session_ref, admission_date, student_type, blood_group, genotype, status) VALUES
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
INSERT INTO student_parents (student_ref, parent_ref, relationship, is_primary_contact) VALUES
(1, 1, 'father', TRUE),   -- Adamu - Musa Adamu
(2, 2, 'mother', TRUE),   -- Fatima - Hajiya Yusuf
(3, 3, 'father', TRUE),   -- Chioma - Emeka Okafor
(4, 4, 'mother', TRUE),   -- David - Funmi Adebayo
(5, 5, 'father', TRUE),   -- Aisha - Alhaji Mohammed
(6, 6, 'mother', TRUE),   -- John - Grace Peter
(7, 7, 'father', TRUE),   -- Blessing - Chidi Okoro
(8, 8, 'mother', TRUE),   -- Musa - Hadiza Usman
(9, 9, 'father', TRUE),   -- Grace - Paul Emmanuel
(10, 10, 'mother', TRUE), -- Suleiman - Zainab Audu
(11, 1, 'father', FALSE), -- Mary - Musa Adamu (second child)
(12, 2, 'mother', FALSE), -- Samuel - Hajiya Yusuf (second child)
(13, 3, 'father', FALSE), -- Halima - Emeka Okafor (second child)
(14, 4, 'mother', FALSE), -- Victor - Funmi Adebayo (second child)
(15, 5, 'father', FALSE); -- Rahmat - Alhaji Mohammed (second child)

-- 8. Insert Subjects (Nigerian Secondary School Curriculum)
INSERT INTO subjects (subject_name, subject_code, class_level, is_core) VALUES
-- Core subjects for all levels
('English Language', 'ENG', 'ALL', TRUE),
('Mathematics', 'MATH', 'ALL', TRUE),
('Civic Education', 'CIV', 'ALL', TRUE),

-- JSS subjects
('Basic Science', 'BSC', 'JSS1', TRUE),
('Basic Science', 'BSC', 'JSS2', TRUE),
('Basic Science', 'BSC', 'JSS3', TRUE),
('Social Studies', 'SST', 'JSS1', TRUE),
('Social Studies', 'SST', 'JSS2', TRUE),
('Social Studies', 'SST', 'JSS3', TRUE),
('Basic Technology', 'BTECH', 'JSS1', TRUE),
('Basic Technology', 'BTECH', 'JSS2', TRUE),
('Basic Technology', 'BTECH', 'JSS3', TRUE),

-- SSS Science subjects
('Physics', 'PHY', 'SSS1', FALSE),
('Physics', 'PHY', 'SSS2', FALSE),
('Physics', 'PHY', 'SSS3', FALSE),
('Chemistry', 'CHEM', 'SSS1', FALSE),
('Chemistry', 'CHEM', 'SSS2', FALSE),
('Chemistry', 'CHEM', 'SSS3', FALSE),
('Biology', 'BIO', 'SSS1', FALSE),
('Biology', 'BIO', 'SSS2', FALSE),
('Biology', 'BIO', 'SSS3', FALSE),

-- SSS Arts subjects
('Government', 'GOV', 'SSS1', FALSE),
('Government', 'GOV', 'SSS2', FALSE),
('Government', 'GOV', 'SSS3', FALSE),
('Economics', 'ECON', 'SSS1', FALSE),
('Economics', 'ECON', 'SSS2', FALSE),
('Economics', 'ECON', 'SSS3', FALSE),
('Geography', 'GEO', 'SSS1', FALSE),
('Geography', 'GEO', 'SSS2', FALSE),
('Geography', 'GEO', 'SSS3', FALSE);

-- 9. Insert Staff
INSERT INTO staff (user_ref, staff_id, first_name, last_name, email, phone, position, department, salary, hire_date, status) VALUES
(28, 'STF001', 'Khadija', 'Aliyu', 'secretary@fimocol.edu.ng', '08034567801', 'School Secretary', 'administration', 280000.00, '2022-01-15', 'active'),
(29, 'STF002', 'Emeka', 'Okonkwo', 'accountant@fimocol.edu.ng', '08034567802', 'School Accountant', 'finance', 380000.00, '2021-03-10', 'active'),
(30, 'STF003', 'Fatima', 'Garba', 'librarian@fimocol.edu.ng', '08034567803', 'School Librarian', 'library', 320000.00, '2022-09-01', 'active'),
(31, 'STF004', 'John', 'Okeke', 'security@fimocol.edu.ng', '08034567804', 'Security Officer', 'security', 200000.00, '2023-01-05', 'active'),
(32, 'STF005', 'Aisha', 'Baba', 'nurse@fimocol.edu.ng', '08034567805', 'School Nurse', 'health', 350000.00, '2022-06-20', 'active');

-- 10. Insert Fee Structures
INSERT INTO fee_structures (fee_name, class_level, student_type, amount, term, academic_session_ref, description) VALUES
-- JSS1 Fees
('School Fees', 'JSS1', 'day', 150000.00, 'annual', 2, 'Annual school fees for JSS1 day students'),
('School Fees', 'JSS1', 'boarding', 350000.00, 'annual', 2, 'Annual school fees for JSS1 boarding students'),
('Examination Fee', 'JSS1', 'ALL', 15000.00, 'annual', 2, 'Annual examination fee'),
('Development Levy', 'JSS1', 'ALL', 25000.00, 'annual', 2, 'School development levy'),

-- JSS2 Fees
('School Fees', 'JSS2', 'day', 160000.00, 'annual', 2, 'Annual school fees for JSS2 day students'),
('School Fees', 'JSS2', 'boarding', 360000.00, 'annual', 2, 'Annual school fees for JSS2 boarding students'),
('Examination Fee', 'JSS2', 'ALL', 15000.00, 'annual', 2, 'Annual examination fee'),
('Development Levy', 'JSS2', 'ALL', 25000.00, 'annual', 2, 'School development levy'),

-- JSS3 Fees
('School Fees', 'JSS3', 'day', 170000.00, 'annual', 2, 'Annual school fees for JSS3 day students'),
('School Fees', 'JSS3', 'boarding', 370000.00, 'annual', 2, 'Annual school fees for JSS3 boarding students'),
('Examination Fee', 'JSS3', 'ALL', 20000.00, 'annual', 2, 'Annual examination fee including BECE'),
('Development Levy', 'JSS3', 'ALL', 25000.00, 'annual', 2, 'School development levy'),

-- SSS1 Fees
('School Fees', 'SSS1', 'day', 180000.00, 'annual', 2, 'Annual school fees for SSS1 day students'),
('School Fees', 'SSS1', 'boarding', 380000.00, 'annual', 2, 'Annual school fees for SSS1 boarding students'),
('Examination Fee', 'SSS1', 'ALL', 18000.00, 'annual', 2, 'Annual examination fee'),
('Development Levy', 'SSS1', 'ALL', 25000.00, 'annual', 2, 'School development levy'),

-- SSS2 Fees
('School Fees', 'SSS2', 'day', 190000.00, 'annual', 2, 'Annual school fees for SSS2 day students'),
('School Fees', 'SSS2', 'boarding', 390000.00, 'annual', 2, 'Annual school fees for SSS2 boarding students'),
('Examination Fee', 'SSS2', 'ALL', 18000.00, 'annual', 2, 'Annual examination fee'),
('Development Levy', 'SSS2', 'ALL', 25000.00, 'annual', 2, 'School development levy'),

-- SSS3 Fees
('School Fees', 'SSS3', 'day', 200000.00, 'annual', 2, 'Annual school fees for SSS3 day students'),
('School Fees', 'SSS3', 'boarding', 400000.00, 'annual', 2, 'Annual school fees for SSS3 boarding students'),
('Examination Fee', 'SSS3', 'ALL', 25000.00, 'annual', 2, 'Annual examination fee including WAEC/NECO'),
('Development Levy', 'SSS3', 'ALL', 25000.00, 'annual', 2, 'School development levy');

-- 11. Insert Student Fees (Sample fee assignments)
INSERT INTO student_fees (student_ref, fee_structure_ref, amount_due, amount_paid, balance, status, due_date, academic_session_ref) VALUES
-- JSS1A students
(1, 2, 350000.00, 350000.00, 0.00, 'paid', '2024-10-31', 2),    -- Adamu (boarding)
(2, 1, 150000.00, 75000.00, 75000.00, 'partial', '2024-10-31', 2), -- Fatima (day)

-- JSS1B students  
(3, 2, 350000.00, 200000.00, 150000.00, 'partial', '2024-10-31', 2), -- Chioma (boarding)

-- JSS2A students
(4, 5, 160000.00, 160000.00, 0.00, 'paid', '2024-10-31', 2),     -- David (day)
(5, 6, 360000.00, 180000.00, 180000.00, 'partial', '2024-10-31', 2), -- Aisha (boarding)

-- JSS2B students
(6, 6, 360000.00, 360000.00, 0.00, 'paid', '2024-10-31', 2),     -- John (boarding)

-- JSS3A students
(7, 9, 170000.00, 85000.00, 85000.00, 'partial', '2024-10-31', 2),  -- Blessing (day)

-- JSS3B students
(8, 10, 370000.00, 370000.00, 0.00, 'paid', '2024-10-31', 2),    -- Musa (boarding)

-- SSS1A students
(9, 13, 180000.00, 90000.00, 90000.00, 'partial', '2024-10-31', 2), -- Grace (day)

-- SSS1B students
(10, 14, 380000.00, 380000.00, 0.00, 'paid', '2024-10-31', 2),   -- Suleiman (boarding)

-- SSS2A students
(11, 18, 390000.00, 195000.00, 195000.00, 'partial', '2024-10-31', 2), -- Mary (boarding)

-- SSS2B students
(12, 17, 190000.00, 190000.00, 0.00, 'paid', '2024-10-31', 2),   -- Samuel (day)

-- SSS3A students
(13, 22, 400000.00, 200000.00, 200000.00, 'partial', '2024-10-31', 2), -- Halima (boarding)

-- SSS3B students
(14, 21, 200000.00, 100000.00, 100000.00, 'partial', '2024-10-31', 2), -- Victor (day)
(15, 22, 400000.00, 400000.00, 0.00, 'paid', '2024-10-31', 2);   -- Rahmat (boarding)

-- 12. Insert Teacher-Subject Assignments
INSERT INTO teacher_subjects (teacher_ref, subject_ref, class_ref, academic_session_ref) VALUES
-- English Teacher (Adebayo) - Teaching English across classes
(1, 1, 1, 2),   -- JSS1A English
(1, 1, 2, 2),   -- JSS1B English
(1, 1, 9, 2),   -- SSS2A English
(1, 1, 10, 2),  -- SSS2B English

-- Mathematics Teacher (Okafor) - Teaching Math across classes
(2, 2, 1, 2),   -- JSS1A Mathematics
(2, 2, 2, 2),   -- JSS1B Mathematics
(2, 2, 3, 2),   -- JSS2A Mathematics
(2, 2, 4, 2),   -- JSS2B Mathematics

-- Physics Teacher (Ibrahim) - Teaching Physics in SSS
(3, 13, 7, 2),  -- SSS1A Physics
(3, 13, 8, 2),  -- SSS1B Physics
(3, 14, 9, 2),  -- SSS2A Physics
(3, 15, 11, 2), -- SSS3A Physics

-- Chemistry Teacher (Olumide) - Teaching Chemistry in SSS
(4, 16, 7, 2),  -- SSS1A Chemistry
(4, 17, 9, 2),  -- SSS2A Chemistry
(4, 18, 11, 2), -- SSS3A Chemistry
(4, 18, 12, 2), -- SSS3B Chemistry

-- Biology Teacher (Hassan) - Teaching Biology and Basic Science
(5, 4, 1, 2),   -- JSS1A Basic Science
(5, 5, 3, 2),   -- JSS2A Basic Science
(5, 19, 7, 2),  -- SSS1A Biology
(5, 20, 9, 2),  -- SSS2A Biology

-- Geography Teacher (Amina) - Teaching Geography and Social Studies
(6, 7, 2, 2),   -- JSS1B Social Studies
(6, 8, 4, 2),   -- JSS2B Social Studies
(6, 25, 8, 2),  -- SSS1B Geography
(6, 26, 10, 2), -- SSS2B Geography

-- Government/History Teacher (Peter) - Teaching Government
(7, 22, 7, 2),  -- SSS1A Government
(7, 23, 9, 2),  -- SSS2A Government
(7, 24, 11, 2), -- SSS3A Government

-- Economics Teacher (Kemi) - Teaching Economics
(8, 25, 8, 2),  -- SSS1B Economics
(8, 26, 10, 2), -- SSS2B Economics
(8, 27, 12, 2); -- SSS3B Economics

-- 13. Insert Sample Grades
INSERT INTO grades (student_ref, subject_ref, class_ref, term, exam_type, score, max_score, grade, remarks, academic_session_ref) VALUES
-- JSS1A students - First Term
-- Adamu Ibrahim (student_ref=1)
(1, 1, 1, 'first_term', 'test', 78.50, 100.00, 'B', 'Good performance', 2),       -- English
(1, 2, 1, 'first_term', 'test', 85.00, 100.00, 'A', 'Excellent in Mathematics', 2), -- Mathematics
(1, 4, 1, 'first_term', 'test', 72.00, 100.00, 'B', 'Average in Basic Science', 2), -- Basic Science

-- Fatima Yusuf (student_ref=2)
(2, 1, 2, 'first_term', 'test', 88.00, 100.00, 'A', 'Excellent in English', 2),     -- English
(2, 2, 2, 'first_term', 'test', 74.50, 100.00, 'B', 'Good in Mathematics', 2),      -- Mathematics

-- JSS2A students - First Term
-- David Adebayo (student_ref=4)
(4, 1, 3, 'first_term', 'test', 82.00, 100.00, 'A', 'Very good in English', 2),     -- English
(4, 2, 3, 'first_term', 'test', 79.50, 100.00, 'B', 'Good in Mathematics', 2),      -- Mathematics
(4, 5, 3, 'first_term', 'test', 76.00, 100.00, 'B', 'Good understanding', 2),       -- Basic Science

-- SSS1A students - First Term
-- Grace Emmanuel (student_ref=9)
(9, 1, 7, 'first_term', 'test', 91.50, 100.00, 'A', 'Outstanding in English', 2),   -- English
(9, 13, 7, 'first_term', 'test', 68.00, 100.00, 'C', 'Needs improvement in Physics', 2), -- Physics
(9, 16, 7, 'first_term', 'test', 75.50, 100.00, 'B', 'Good in Chemistry', 2),       -- Chemistry
(9, 19, 7, 'first_term', 'test', 84.00, 100.00, 'A', 'Excellent in Biology', 2),    -- Biology

-- SSS2A students - First Term
-- Mary Joseph (student_ref=11)
(11, 1, 9, 'first_term', 'test', 86.50, 100.00, 'A', 'Very good in English', 2),    -- English
(11, 14, 9, 'first_term', 'test', 73.00, 100.00, 'B', 'Good progress in Physics', 2), -- Physics
(11, 17, 9, 'first_term', 'test', 80.50, 100.00, 'A', 'Good in Chemistry', 2),      -- Chemistry
(11, 20, 9, 'first_term', 'test', 88.00, 100.00, 'A', 'Excellent in Biology', 2),   -- Biology

-- SSS3A students - First Term
-- Halima Yusuf (student_ref=13)
(13, 1, 11, 'first_term', 'final', 89.00, 100.00, 'A', 'Excellent preparation for WAEC', 2), -- English
(13, 15, 11, 'first_term', 'final', 77.50, 100.00, 'B', 'Good in Physics', 2),      -- Physics
(13, 18, 11, 'first_term', 'final', 85.50, 100.00, 'A', 'Very good in Chemistry', 2), -- Chemistry
(13, 24, 11, 'first_term', 'final', 92.00, 100.00, 'A', 'Outstanding in Government', 2); -- Government

-- 14. Insert Sample Attendance
INSERT INTO attendance (student_ref, class_ref, date, status, remarks) VALUES
-- Week 1 attendance (Sept 2024)
(1, 1, '2024-09-02', 'present', ''),
(1, 1, '2024-09-03', 'present', ''),
(1, 1, '2024-09-04', 'late', 'Arrived 10 minutes late'),
(1, 1, '2024-09-05', 'present', ''),
(1, 1, '2024-09-06', 'present', ''),

(2, 2, '2024-09-02', 'present', ''),
(2, 2, '2024-09-03', 'absent', 'Sick'),
(2, 2, '2024-09-04', 'present', ''),
(2, 2, '2024-09-05', 'present', ''),
(2, 2, '2024-09-06', 'present', ''),

(4, 3, '2024-09-02', 'present', ''),
(4, 3, '2024-09-03', 'present', ''),
(4, 3, '2024-09-04', 'present', ''),
(4, 3, '2024-09-05', 'excused', 'Medical appointment'),
(4, 3, '2024-09-06', 'present', ''),

(9, 7, '2024-09-02', 'present', ''),
(9, 7, '2024-09-03', 'present', ''),
(9, 7, '2024-09-04', 'present', ''),
(9, 7, '2024-09-05', 'present', ''),
(9, 7, '2024-09-06', 'late', 'Transportation issue'),

(13, 11, '2024-09-02', 'present', ''),
(13, 11, '2024-09-03', 'present', ''),
(13, 11, '2024-09-04', 'present', ''),
(13, 11, '2024-09-05', 'present', ''),
(13, 11, '2024-09-06', 'present', '');

SELECT 'FIMOCOL sample data populated successfully with Nigerian context!' as message;
