<?php
require_once '../config.php';
redirectIfNotStudent();

header('Content-Type: application/json');

$course_code = $_POST['course_code'] ?? '';
$student_id = $_SESSION['student_id'];

$check = $pdo->prepare("SELECT COUNT(*) FROM registrations WHERE student_id = ? AND course_code = ? AND registration_status = 'registered'");
$check->execute([$student_id, $course_code]);

if ($check->fetchColumn() == 0) {
    $insert = $pdo->prepare("INSERT INTO registrations (student_id, course_code, semester, registration_status) VALUES (?, ?, 'Spring', 'registered')");
    if ($insert->execute([$student_id, $course_code])) {
        echo json_encode(['success' => true, 'message' => 'Course registered successfully']);
        exit;
    }
}
echo json_encode(['success' => false, 'message' => 'Failed to register course']);
?>