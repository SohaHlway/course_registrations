<?php
require_once __DIR__ . '/config.php';


function isLoggedIn() {
    return isset($_SESSION['user_id']) && isset($_SESSION['user_type']);
}


function hasRole($role) {
    return isLoggedIn() && $_SESSION['user_type'] === $role;
}

function requireStudent() {
    if (!isLoggedIn() || $_SESSION['user_type'] !== 'student') {
        header("Location: ../index.php");
        exit();
    }
}

function requireAdmin() {
    if (!isLoggedIn() || $_SESSION['user_type'] !== 'admin') {
        header("Location: ../index.php");
        exit();
    }
}

function validateCSRF($token) {
    if (!isset($_SESSION['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $token)) {
        die("CSRF validation failed");
    }
}

function getStudentData($student_id, $pdo) {
    $stmt = $pdo->prepare("SELECT * FROM students WHERE student_id = ?");
    $stmt->execute([$student_id]);
    return $stmt->fetch();
}

function getStudentCourses($student_id, $pdo, $status = 'Enrolled') {
    $stmt = $pdo->prepare("
        SELECT c.*, r.status, r.grade, r.semester
        FROM registrations r
        JOIN courses c ON r.course_code = c.course_code
        WHERE r.student_id = ? AND r.status = ?
    ");
    $stmt->execute([$student_id, $status]);
    return $stmt->fetchAll();
}

function getRemainingCourses($student_id, $pdo, $year_level) {
    $stmt = $pdo->prepare("
        SELECT * FROM courses 
        WHERE year_level <= ? 
        AND course_code NOT IN (
            SELECT course_code FROM registrations 
            WHERE student_id = ? AND status = 'Completed'
        )
        ORDER BY year_level, course_code
    ");
    $stmt->execute([$year_level, $student_id]);
    return $stmt->fetchAll();
}

function getCompletedCredits($student_id, $pdo) {
    $stmt = $pdo->prepare("
        SELECT SUM(c.credits) as total 
        FROM registrations r
        JOIN courses c ON r.course_code = c.course_code
        WHERE r.student_id = ? AND r.status = 'Completed'
    ");
    $stmt->execute([$student_id]);
    $result = $stmt->fetch();
    return $result['total'] ?? 0;
}
?>