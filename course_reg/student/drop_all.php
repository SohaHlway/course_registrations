<?php
require_once '../config.php';
redirectIfNotStudent();

header('Content-Type: application/json');

$student_id = $_SESSION['student_id'];

$stmt = $pdo->prepare("UPDATE registrations SET registration_status = 'dropped' WHERE student_id = ? AND registration_status = 'registered'");
if ($stmt->execute([$student_id])) {
    echo json_encode(['success' => true, 'message' => 'All courses dropped successfully']);
} else {
    echo json_encode(['success' => false, 'message' => 'Failed to drop courses']);
}
?>