<?php
require_once '../includes/config.php';
require_once '../includes/auth.php';

requireStudent();

$student_id = $_SESSION['user_id'];
$message = '';
$messageType = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    validateCSRF($_POST['csrf_token'] ?? '');
    $course_code = $_POST['course_code'] ?? '';
    
    if ($_POST['action'] === 'register') {
        $stmt = $pdo->prepare("SELECT prerequisite FROM courses WHERE course_code = ?");
        $stmt->execute([$course_code]);
        $prereq = $stmt->fetch();
        
        $canRegister = true;
        if ($prereq && $prereq['prerequisite']) {
            $stmt = $pdo->prepare("SELECT * FROM registrations WHERE student_id = ? AND course_code = ? AND status = 'Completed'");
            $stmt->execute([$student_id, $prereq['prerequisite']]);
            if (!$stmt->fetch()) $canRegister = false;
        }
        
        if ($canRegister) {
            $stmt = $pdo->prepare("INSERT INTO registrations (student_id, course_code) VALUES (?, ?)");
            $stmt->execute([$student_id, $course_code]);
            $message = "✅ Course registered successfully!";
            $messageType = "success";
        } else {
            $message = "❌ Cannot register. Prerequisite not met.";
            $messageType = "error";
        }
    } elseif ($_POST['action'] === 'drop') {
        $stmt = $pdo->prepare("UPDATE registrations SET status = 'Dropped' WHERE student_id = ? AND course_code = ? AND status = 'Enrolled'");
        $stmt->execute([$student_id, $course_code]);
        $message = "✅ Course dropped successfully!";
        $messageType = "success";
    }
}

$currentCourses = getStudentCourses($student_id, $pdo, 'Enrolled');
$totalCredits = 0;
foreach ($currentCourses as $course) $totalCredits += $course['credits'];

$completedCourses = getStudentCourses($student_id, $pdo, 'Completed');
$completedCredits = getCompletedCredits($student_id, $pdo);

$stmt = $pdo->prepare("
    SELECT * FROM courses 
    WHERE year_level <= ? 
    AND course_code NOT IN (
        SELECT course_code FROM registrations 
        WHERE student_id = ? AND status IN ('Enrolled', 'Completed')
    )
");
$stmt->execute([$_SESSION['user_level'], $student_id]);
$availableCourses = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Dashboard - YIC</title>
    <link rel="stylesheet" href="../css/style.css">
    <style>
        .success-message { background: #d4edda; color: #155724; padding: 10px; border-radius: 5px; margin-bottom: 15px; }
        .error-message { background: #f8d7da; color: #721c24; padding: 10px; border-radius: 5px; margin-bottom: 15px; }
        .btn-drop { background: #dc3545; color: white; padding: 5px 10px; border: none; border-radius: 3px; cursor: pointer; }
        .btn-register { background: #28a745; color: white; padding: 5px 10px; border: none; border-radius: 3px; cursor: pointer; }
        .stats-summary { display: flex; gap: 15px; margin-bottom: 20px; }
        .stat-badge { background: #007bff; color: white; padding: 10px 15px; border-radius: 5px; }
    </style>
</head>
<body>
    <header class="header-fixed">
        <div class="logo">🎓 YIC Course Registration</div>
        <div class="header-buttons">
            <a href="profile.php" class="back-btn-header">👤 My Profile</a>
            <a href="../logout.php" class="logout-btn-header">🚪 Logout</a>
        </div>
    </header>

    <main class="container">
        <section class="welcome-card">
            <h2>Welcome, <?= htmlspecialchars($_SESSION['user_name']) ?>!</h2>
            <p>Student ID: <?= $_SESSION['user_id'] ?> | Major: <?= $_SESSION['user_major'] ?> | Level: <?= $_SESSION['user_level'] ?></p>
        </section>

        <?php if ($message): ?>
            <div class="<?= $messageType ?>-message"><?= htmlspecialchars($message) ?></div>
        <?php endif; ?>

        <div class="stats-summary">
            <div class="stat-badge">✅ Completed: <?= count($completedCourses) ?> courses (<?= $completedCredits ?> credits)</div>
            <div class="stat-badge">📚 Current: <?= count($currentCourses) ?> courses (<?= $totalCredits ?> credits)</div>
        </div>

        <div class="dashboard-grid">
            <div class="card">
                <h2>📖 My Current Schedule</h2>
                <div class="table-responsive">
                    <table class="data-table">
                        <thead>
                            <tr><th>Code</th><th>Course Name</th><th>Credits</th><th>Time</th><th>Action</th></tr>
                        </thead>
                        <tbody>
                            <?php foreach ($currentCourses as $course): ?>
                            <tr>
                                <td><?= htmlspecialchars($course['course_code']) ?></td>
                                <td><?= htmlspecialchars($course['course_name']) ?></td>
                                <td><?= $course['credits'] ?></td>
                                <td><?= htmlspecialchars($course['time_slot']) ?></td>
                                <td>
                                    <form method="POST" style="display:inline">
                                        <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
                                        <input type="hidden" name="course_code" value="<?= $course['course_code'] ?>">
                                        <input type="hidden" name="action" value="drop">
                                        <button type="submit" class="btn-drop" onclick="return confirm('Drop this course?')">Drop</button>
                                    </form>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                            <?php if (empty($currentCourses)): ?>
                                <tr><td colspan="5" style="text-align:center">No courses enrolled</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="card">
                <h2>📚 Available Courses</h2>
                <div class="table-responsive">
                    <table class="data-table">
                        <thead>
                            <tr><th>Code</th><th>Name</th><th>Credits</th><th>Level</th><th>Time</th><th>Action</th></tr>
                        </thead>
                        <tbody>
                            <?php foreach ($availableCourses as $course): ?>
                            <tr>
                                <td><?= htmlspecialchars($course['course_code']) ?></td>
                                <td><?= htmlspecialchars($course['course_name']) ?></td>
                                <td><?= $course['credits'] ?></td>
                                <td>Level <?= $course['year_level'] ?></td>
                                <td><?= htmlspecialchars($course['time_slot']) ?></td>
                                <td>
                                    <form method="POST" style="display:inline">
                                        <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
                                        <input type="hidden" name="course_code" value="<?= $course['course_code'] ?>">
                                        <input type="hidden" name="action" value="register">
                                        <button type="submit" class="btn-register">Register</button>
                                    </form>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                            <?php if (empty($availableCourses)): ?>
                                <tr><td colspan="6" style="text-align:center">No available courses</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </main>

    <footer class="footer-fixed">
        <p>&copy; 2026 Yanbu Industrial College - Course Registration System</p>
    </footer>

    <script src="../js/script.js"></script>
</body>
</html>