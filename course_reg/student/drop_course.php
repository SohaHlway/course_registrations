<?php
require_once '../config.php';
redirectIfNotLoggedIn();

header('Content-Type: application/json');

$course_id = $_POST['course_id'] ?? 0;
$user_id = $_SESSION['user_id'];

$stmt = $pdo->prepare("UPDATE registrations SET status = 'dropped' WHERE user_id = ? AND course_id = ? AND status = 'registered'");
if ($stmt->execute([$user_id, $course_id]) && $stmt->rowCount() > 0) {
    echo json_encode(['success' => true, 'message' => 'Course dropped successfully']);
} else {
    echo json_encode(['success' => false, 'message' => 'Failed to drop course']);
}
?>