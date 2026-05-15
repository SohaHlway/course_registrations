DROP DATABASE IF EXISTS yic_course_registration;
CREATE DATABASE yic_course_registration;
USE yic_course_registration;

CREATE TABLE users (
    user_id INT PRIMARY KEY AUTO_INCREMENT,
    username VARCHAR(50) UNIQUE NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    full_name VARCHAR(100) NOT NULL,
    user_type ENUM('student', 'admin') DEFAULT 'student',
    major VARCHAR(10) DEFAULT NULL,
    year_level INT DEFAULT 1,
    admin_major VARCHAR(10) DEFAULT NULL
);

CREATE TABLE courses (
    course_id INT PRIMARY KEY AUTO_INCREMENT,
    course_code VARCHAR(10) UNIQUE NOT NULL,
    course_name VARCHAR(100) NOT NULL,
    credits INT NOT NULL,
    level INT NOT NULL,
    major VARCHAR(10) NOT NULL,
    prerequisite_code VARCHAR(10) DEFAULT NULL,
    time_slot VARCHAR(50) DEFAULT 'TBA'
);

CREATE TABLE registrations (
    registration_id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    course_id INT NOT NULL,
    semester VARCHAR(20) NOT NULL,
    year INT NOT NULL,
    status ENUM('registered', 'dropped') DEFAULT 'registered',
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE,
    FOREIGN KEY (course_id) REFERENCES courses(course_id) ON DELETE CASCADE,
    UNIQUE KEY unique_reg (user_id, course_id, semester, year)
);

CREATE TABLE completed_courses (
    completion_id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    course_id INT NOT NULL,
    grade VARCHAR(2) NOT NULL,
    semester VARCHAR(20) NOT NULL,
    year INT NOT NULL,
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE,
    FOREIGN KEY (course_id) REFERENCES courses(course_id) ON DELETE CASCADE
);

INSERT INTO users (username, email, password_hash, full_name, user_type, major, year_level, admin_major) VALUES
('441500338', 'soha@yic.edu', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Soha Samy Hlway', 'student', 'SC', 3, NULL),
('441500583', 'renad@yic.edu', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Renad Jaber', 'student', 'SC', 3, NULL),
('admin_sc', 'admin.sc@yic.edu', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Dr. Ahmed', 'admin', NULL, NULL, 'SC'),
('admin_se', 'admin.se@yic.edu', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Dr. Sarah', 'admin', NULL, NULL, 'SE');

INSERT INTO courses (course_code, course_name, credits, level, major, prerequisite_code, time_slot) VALUES
('CS101', 'Computer Programming', 3, 1, 'SC', NULL, 'Sun/Tue 10:00'),
('CS102', 'Object Oriented Programming', 4, 2, 'SC', 'CS101', 'Mon/Wed 13:00'),
('CS204', 'Data Structures', 4, 3, 'SC', 'CS102', 'Sun/Tue 11:30'),
('CS202', 'Discrete Mathematics', 4, 3, 'SC', 'CS101', 'Mon/Wed 14:30'),
('CS311', 'Database Systems', 4, 3, 'SC', 'CS102', 'Sun/Tue 15:00'),
('MATH101', 'Calculus I', 4, 1, 'SC', NULL, 'Sun/Tue 08:00'),
('MATH102', 'Calculus II', 4, 2, 'SC', 'MATH101', 'Tue/Thu 08:00'),
('CS201', 'Digital Logic', 4, 3, 'SC', NULL, 'Tue/Thu 13:00'),
('ENGL101', 'English Composition', 3, 1, 'SC', NULL, 'Wed/Thu 11:00');

INSERT INTO completed_courses (user_id, course_id, grade, semester, year) VALUES
(1, 1, 'A', 'Fall', 2024),
(1, 2, 'B+', 'Fall', 2024),
(1, 6, 'A-', 'Fall', 2024),
(1, 9, 'B', 'Fall', 2024),
(1, 7, 'A-', 'Spring', 2025),
(1, 8, 'B+', 'Spring', 2025),
(2, 1, 'A-', 'Fall', 2024),
(2, 6, 'A', 'Fall', 2024),
(2, 7, 'B+', 'Spring', 2025),
(2, 8, 'A-', 'Spring', 2025);

INSERT INTO registrations (user_id, course_id, semester, year, status) VALUES
(1, 3, 'Spring', 2026, 'registered'),
(1, 4, 'Spring', 2026, 'registered'),
(1, 5, 'Spring', 2026, 'registered'),
(2, 3, 'Spring', 2026, 'registered'),
(2, 4, 'Spring', 2026, 'registered');