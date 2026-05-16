
DROP DATABASE IF EXISTS yic_course_registration;
CREATE DATABASE yic_course_registration;
USE yic_course_registration;

CREATE TABLE major (
    major_code VARCHAR(10) PRIMARY KEY,
    major_name VARCHAR(50) NOT NULL
);

CREATE TABLE admins (
    admin_id INT PRIMARY KEY AUTO_INCREMENT,
    admin_name VARCHAR(100) NOT NULL,
    admin_email VARCHAR(100) UNIQUE NOT NULL,
    admin_password VARCHAR(255) NOT NULL,
    admin_managed_major ENUM('SC', 'SE', 'MIS', 'HR') NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);


CREATE TABLE students (
    student_id INT PRIMARY KEY AUTO_INCREMENT,
    student_name VARCHAR(100) NOT NULL,
    student_email VARCHAR(100) UNIQUE NOT NULL,
    student_password VARCHAR(255) NOT NULL,
    student_major ENUM('SC', 'SE', 'MIS', 'HR') NOT NULL,
    student_year_level INT CHECK (student_year_level BETWEEN 1 AND 5),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (student_major) REFERENCES major(major_code)
);


CREATE TABLE courses (
    course_id INT PRIMARY KEY AUTO_INCREMENT,
    course_code VARCHAR(10) UNIQUE NOT NULL,
    course_name VARCHAR(100) NOT NULL,
    course_major ENUM('SC', 'SE', 'MIS', 'HR') NOT NULL,
    course_year_level INT NOT NULL,
    course_credits INT NOT NULL,
    course_time_slot VARCHAR(50) DEFAULT 'TBA',
    course_prerequisite VARCHAR(10) DEFAULT NULL,
    FOREIGN KEY (course_prerequisite) REFERENCES courses(course_code)
);

CREATE TABLE registrations (
    registration_id INT PRIMARY KEY AUTO_INCREMENT,
    student_id INT NOT NULL,
    course_code VARCHAR(10) NOT NULL,
    semester ENUM('Fall', 'Spring', 'Summer') NOT NULL,
    registration_status ENUM('registered', 'dropped', 'completed') DEFAULT 'registered',
    registered_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (student_id) REFERENCES students(student_id) ON DELETE CASCADE,
    FOREIGN KEY (course_code) REFERENCES courses(course_code) ON DELETE CASCADE,
    UNIQUE KEY unique_registration (student_id, course_code, semester)
);


INSERT INTO major (major_code, major_name) VALUES
('SC', 'Computer Science'),
('SE', 'Software Engineering'),
('MIS', 'Management Information Systems'),
('HR', 'Human Resources');


INSERT INTO admins (admin_name, admin_email, admin_password, admin_managed_major) VALUES
('Dr. Ahmed Al-sharef', 'admin.sc@yic.edu', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'SC'),
('Dr. Sarah Mohammed', 'admin.se@yic.edu', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'SE'),
('Dr. Khalid Omar', 'admin.mis@yic.edu', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'MIS'),
('Dr. Nora Hassan', 'admin.hr@yic.edu', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'HR');

INSERT INTO students (student_name, student_email, student_password, student_major, student_year_level) VALUES
('Soha Samy Hlway', 'soha@yic.edu', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'SC', 3),
('Renad Jaber', 'renad@yic.edu', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'SC', 3),
('Ahmed Mansor', 'ahmed@yic.edu', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'SC', 2),
('Fatima Ali', 'fatima@yic.edu', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'SE', 2),
('Omar Saeed', 'omar@yic.edu', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'MIS', 1),
('Nora Khalid', 'nora@yic.edu', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'SC', 4),
('Khaled Ahmed', 'khaled@yic.edu', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'SE', 3),
('Layla Hassan', 'layla@yic.edu', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'MIS', 2),
('Abdullah Saad', 'abdullah@yic.edu', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'SC', 1),
('Mona Ibrahim', 'mona@yic.edu', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'SE', 1),
('Huda Khalid', 'huda@yic.edu', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'HR', 2);


INSERT INTO courses (course_code, course_name, course_major, course_year_level, course_credits, course_time_slot, course_prerequisite) VALUES

('CS101', 'Computer Programming', 'SC', 1, 3, 'Sun/Tue 10:00-11:30', NULL),
('MATH101', 'Calculus I', 'SC', 1, 4, 'Sun/Tue 08:00-09:30', NULL),
('PHYS101', 'General Physics I', 'SC', 1, 4, 'Mon/Wed 09:00-10:30', NULL),
('ENGL101', 'English Composition I', 'SC', 1, 3, 'Wed/Thu 11:00-12:30', NULL),

('CS102', 'Object Oriented Programming', 'SC', 2, 4, 'Mon/Wed 13:00-14:30', 'CS101'),
('MATH102', 'Calculus II', 'SC', 2, 4, 'Tue/Thu 08:00-09:30', 'MATH101'),
('PHYS102', 'General Physics II', 'SC', 2, 4, 'Mon/Wed 11:00-12:30', 'PHYS101'),
('ENGL131', 'Academic Writing', 'SC', 2, 3, 'Sun/Tue 13:00-14:30', 'ENGL101'),
('ARAB101', 'Functional Grammar', 'SC', 2, 2, 'Thu 10:00-11:30', NULL),

('CS204', 'Data Structures', 'SC', 3, 4, 'Sun/Tue 11:30-13:00', 'CS102'),
('CS202', 'Discrete Mathematics', 'SC', 3, 4, 'Mon/Wed 14:30-16:00', 'MATH102'),
('CS201', 'Digital Logic', 'SC', 3, 4, 'Tue/Thu 13:00-14:30', NULL),
('CS311', 'Database Systems', 'SC', 3, 4, 'Sun/Tue 15:00-16:30', 'CS102'),
('ARAB201', 'Objective Writing', 'SC', 3, 2, 'Sun 10:00-11:30', 'ARAB101'),

('CS301', 'Computer Architecture', 'SC', 4, 3, 'Mon/Wed 10:00-11:30', 'CS204'),
('CS302', 'Algorithms', 'SC', 4, 3, 'Tue/Thu 15:00-16:30', 'CS204'),
('CS401', 'Software Engineering', 'SC', 4, 4, 'Sun/Tue 09:00-10:30', 'CS311'),
('CS402', 'Graduation Project', 'SC', 4, 3, 'Wed 10:00-13:00', 'CS302'),

('SE101', 'Introduction to SE', 'SE', 1, 3, 'Sun/Tue 10:00-11:30', NULL),
('SE201', 'Requirements Engineering', 'SE', 2, 3, 'Mon/Wed 13:00-14:30', 'SE101'),
('SE301', 'Software Testing', 'SE', 3, 3, 'Tue/Thu 11:00-12:30', 'SE201'),
('SE401', 'Project Management', 'SE', 4, 3, 'Sun/Tue 14:00-15:30', 'SE301'),

('MIS101', 'Intro to MIS', 'MIS', 1, 3, 'Sun/Tue 14:00-15:30', NULL),
('MIS201', 'Business Analytics', 'MIS', 2, 3, 'Mon/Wed 15:00-16:30', 'MIS101'),
('MIS301', 'Database Management', 'MIS', 3, 3, 'Tue/Thu 09:00-10:30', 'MIS201'),

('HR101', 'Intro to HR', 'HR', 1, 3, 'Sun/Tue 11:00-12:30', NULL),
('HR201', 'Recruitment', 'HR', 2, 3, 'Mon/Wed 10:00-11:30', 'HR101'),
('HR301', 'Performance Management', 'HR', 3, 3, 'Tue/Thu 13:00-14:30', 'HR201');


INSERT INTO registrations (student_id, course_code, semester, registration_status) VALUES

(1, 'CS204', 'Spring', 'registered'),
(1, 'CS202', 'Spring', 'registered'),
(1, 'CS311', 'Spring', 'registered'),

(2, 'CS204', 'Spring', 'registered'),
(2, 'CS202', 'Spring', 'registered'),
(2, 'CS201', 'Spring', 'registered'),

(3, 'CS102', 'Spring', 'registered'),
(3, 'MATH102', 'Spring', 'registered'),
(3, 'PHYS102', 'Spring', 'registered'),
(3, 'ENGL131', 'Spring', 'registered'),

(4, 'SE201', 'Spring', 'registered'),
(4, 'SE301', 'Spring', 'registered'),

(6, 'CS401', 'Spring', 'registered'),
(6, 'CS402', 'Spring', 'registered');


CREATE INDEX idx_admin_major ON admins(admin_managed_major);
CREATE INDEX idx_student_major ON students(student_major);
CREATE INDEX idx_student_level ON students(student_year_level);
CREATE INDEX idx_course_major ON courses(course_major);
CREATE INDEX idx_course_level ON courses(course_year_level);
CREATE INDEX idx_registration_student ON registrations(student_id);
CREATE INDEX idx_registration_course ON registrations(course_code);
CREATE INDEX idx_registration_status ON registrations(registration_status);


SELECT 'major' as Table_Name, COUNT(*) as Records FROM major
UNION SELECT 'admins', COUNT(*) FROM admins
UNION SELECT 'students', COUNT(*) FROM students
UNION SELECT 'courses', COUNT(*) FROM courses
UNION SELECT 'registrations', COUNT(*) FROM registrations;
