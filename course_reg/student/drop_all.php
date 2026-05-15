<?php
require_once '../config.php';
redirectIfNotLoggedIn();

header('Content-Type: application/json');

$user_id = $_SESSION['user_id'];

$stmt = $pdo->prepare("UPDATE registrations SET status = 'dropped' WHERE user_id = ? AND status = 'registered'");
if ($stmt->execute([$user_id])) {
    echo json_encode(['success' => true, 'message' => 'All courses dropped successfully']);
} else {
    echo json_encode(['success' => false, 'message' => 'Failed to drop courses']);
}
?>