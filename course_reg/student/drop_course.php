<?php
require_once '../config.php';
redirectIfNotStudent();

header('Content-Type: application/json');

$course_code = $_POST['course_code'] ?? '';
$student_id = $_SESSION['student_id'];

$stmt = $pdo->prepare("UPDATE registrations SET registration_status = 'dropped' WHERE student_id = ? AND course_code = ? AND registration_status = 'registered'");
if ($stmt->execute([$student_id, $course_code]) && $stmt->rowCount() > 0) {
    echo json_encode(['success' => true, 'message' => 'Course dropped successfully']);
} else {
    echo json_encode(['success' => false, 'message' => 'Failed to drop course']);
}
?>