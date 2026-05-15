<?php
require_once '../config.php';
redirectIfNotLoggedIn();

header('Content-Type: application/json');

$user_id = $_SESSION['user_id'];
$major = $_SESSION['major'];

$stmt = $pdo->prepare("SELECT c.* FROM courses c WHERE c.major = ? AND c.course_id NOT IN (SELECT course_id FROM completed_courses WHERE user_id = ?) AND c.course_id NOT IN (SELECT course_id FROM registrations WHERE user_id = ? AND status = 'registered')");
$stmt->execute([$major, $user_id, $user_id]);
$courses = $stmt->fetchAll();

$count = count($courses);
$credits = array_sum(array_column($courses, 'credits'));

echo json_encode(['success' => true, 'courses' => $courses, 'count' => $count, 'credits' => $credits]);
?>