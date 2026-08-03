CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    username VARCHAR(50) UNIQUE NOT NULL,
    email VARCHAR(100),
    password_hash VARCHAR(255) NOT NULL,
    role ENUM('admin','teacher','student') NOT NULL,
    status ENUM('active','inactive') DEFAULT 'active'
);

CREATE TABLE students (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT UNIQUE,
    sex ENUM('M','F'),
    birthday DATE,
    age INT,
    grade_level INT,
    status ENUM('active','inactive') DEFAULT 'active',
    needs_reenrollment ENUM('yes','no') DEFAULT 'no'
);

CREATE TABLE teachers (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT UNIQUE,
    advisory_class VARCHAR(50),
    max_subjects INT DEFAULT 0,
    max_students INT DEFAULT 0,
    status ENUM('active','inactive') DEFAULT 'active'
);

CREATE TABLE teacher_approval (
    id INT AUTO_INCREMENT PRIMARY KEY,
    teacher_id INT,
    max_students INT DEFAULT 30,
    max_subjects INT DEFAULT 8,
    status ENUM('approved','inactive') DEFAULT 'approved'
);

CREATE TABLE enrollment_requests (
    id INT AUTO_INCREMENT PRIMARY KEY,
    student_id INT,
    teacher_id INT,
    status ENUM('pending','approved','rejected') DEFAULT 'pending',
    date_requested DATETIME DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE subjects (
    id INT AUTO_INCREMENT PRIMARY KEY,
    teacher_id INT,
    student_id INT,
    subject_name VARCHAR(100),
    course_code VARCHAR(50),
    teacher_code VARCHAR(50),
    room_no VARCHAR(50),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE grades (
    id INT AUTO_INCREMENT PRIMARY KEY,
    student_id INT,
    subject_id INT,
    grade VARCHAR(10) DEFAULT 'N/A',
    remarks VARCHAR(255) DEFAULT NULL,
    quarter VARCHAR(20),
    date_submitted DATETIME
);

CREATE TABLE assessment_scores (
    id INT AUTO_INCREMENT PRIMARY KEY,
    teacher_id INT NOT NULL,
    student_id INT NOT NULL,
    score_type ENUM('activity','quiz','exam') NOT NULL,
    item_no INT NOT NULL,
    score DECIMAL(10,2) NOT NULL DEFAULT 0,
    max_score DECIMAL(10,2) NOT NULL DEFAULT 100,
    remarks VARCHAR(255) DEFAULT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY uq_teacher_student_type_item (teacher_id, student_id, score_type, item_no)
);

CREATE TABLE settings (
    id INT PRIMARY KEY,
    current_semester INT DEFAULT 1,
    current_school_year VARCHAR(20) DEFAULT '2024-2025',
    max_students_per_class INT DEFAULT 30,
    max_subjects_per_teacher INT DEFAULT 8
);

INSERT INTO settings (id, current_semester, current_school_year) 
VALUES (1, 1, '2025-2026') 
ON DUPLICATE KEY UPDATE current_semester=1, current_school_year='2025-2026';

CREATE TABLE previous_semester_subjects (
    id INT AUTO_INCREMENT PRIMARY KEY,
    original_subject_id INT,
    student_id INT,
    teacher_id INT,
    subject_name VARCHAR(100),
    course_code VARCHAR(50),
    teacher_code VARCHAR(50),
    room_no VARCHAR(50),
    archived_semester INT,
    archived_school_year VARCHAR(20),
    archived_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE graduated_students (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    graduation_grade INT,
    graduation_semester INT,
    graduation_school_year VARCHAR(20),
    graduation_date DATE
);

CREATE TABLE teacher_subjects (
    id INT AUTO_INCREMENT PRIMARY KEY,
    teacher_id INT NOT NULL,
    subject_name VARCHAR(100) NOT NULL,
    course_code VARCHAR(50),
    teacher_code VARCHAR(50),
    room_no VARCHAR(50),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY unique_subject_per_teacher (teacher_id, subject_name)
);

CREATE TABLE previous_semester_grades (
    id INT AUTO_INCREMENT PRIMARY KEY,
    original_grade_id INT,
    student_id INT,
    subject_id INT,
    grade VARCHAR(10),
    quarter VARCHAR(20),
    archived_semester INT,
    archived_school_year VARCHAR(20),
    archived_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

ALTER TABLE grades 
ADD UNIQUE KEY unique_grade (student_id, subject_id, quarter);