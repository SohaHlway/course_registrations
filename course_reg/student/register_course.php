<?php
require_once '../config.php';
redirectIfNotLoggedIn();

header('Content-Type: application/json');

$course_id = $_POST['course_id'] ?? 0;
$user_id = $_SESSION['user_id'];

$stmt = $pdo->prepare("SELECT COUNT(*) FROM registrations WHERE user_id = ? AND course_id = ? AND status = 'registered'");
$stmt->execute([$user_id, $course_id]);

if ($stmt->fetchColumn() == 0) {
    $stmt = $pdo->prepare("INSERT INTO registrations (user_id, course_id, semester, year, status) VALUES (?, ?, 'Spring', 2026, 'registered')");
    if ($stmt->execute([$user_id, $course_id])) {
        echo json_encode(['success' => true, 'message' => 'Course registered successfully']);
        exit;
    }
}
echo json_encode(['success' => false, 'message' => 'Failed to register course']);
?>