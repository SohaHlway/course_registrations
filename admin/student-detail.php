<?php
require_once '../includes/config.php';
require_once '../includes/auth.php';

requireAdmin();

$student_id = $_GET['id'] ?? 0;
if (!$student_id) {
    header("Location: dashboard.php");
    exit();
}

$stmt = $pdo->prepare("SELECT * FROM students WHERE student_id = ?");
$stmt->execute([$student_id]);
$student = $stmt->fetch();

if (!$student) {
    header("Location: dashboard.php");
    exit();
}

$stmt = $pdo->prepare("
    SELECT c.*, r.status, r.grade, r.semester
    FROM registrations r
    JOIN courses c ON r.course_code = c.course_code
    WHERE r.student_id = ?
    ORDER BY r.semester DESC
");
$stmt->execute([$student_id]);
$registrations = $stmt->fetchAll();

$completedCredits = 0;
$currentCredits = 0;
foreach ($registrations as $reg) {
    if ($reg['status'] == 'Completed') $completedCredits += $reg['credits'];
    if ($reg['status'] == 'Enrolled') $currentCredits += $reg['credits'];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Details - Admin</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
    <header class="header-fixed">
        <div class="logo">📋 Student Details</div>
        <div class="header-buttons">
            <a href="dashboard.php" class="back-btn-header">← Back</a>
            <a href="../logout.php" class="logout-btn-header">🚪 Logout</a>
        </div>
    </header>

    <main class="container">
        <div class="card">
            <h2>Student Information</h2>
            <div class="profile-info">
                <p><strong>ID:</strong> <?= $student['student_id'] ?></p>
                <p><strong>Name:</strong> <?= htmlspecialchars($student['student_name']) ?></p>
                <p><strong>Email:</strong> <?= htmlspecialchars($student['student_email']) ?></p>
                <p><strong>Major:</strong> <?= $student['student_major'] ?></p>
                <p><strong>Level:</strong> <?= $student['year_level'] ?></p>
                <p><strong>Completed Credits:</strong> <?= $completedCredits ?></p>
                <p><strong>Current Credits:</strong> <?= $currentCredits ?></p>
            </div>
        </div>

        <div class="card">
            <h2>Course History</h2>
            <div class="table-responsive">
                <table class="data-table">
                    <thead>
                        <tr><th>Semester</th><th>Code</th><th>Course</th><th>Credits</th><th>Status</th><th>Grade</th></tr>
                    </thead>
                    <tbody>
                        <?php foreach ($registrations as $reg): ?>
                        <tr>
                            <td><?= $reg['semester'] ?></td>
                            <td><?= $reg['course_code'] ?></td>
                            <td><?= htmlspecialchars($reg['course_name']) ?></td>
                            <td><?= $reg['credits'] ?></td>
                            <td><?= $reg['status'] ?></td>
                            <td><?= $reg['grade'] ?? '-' ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </main>
</body>
</html>