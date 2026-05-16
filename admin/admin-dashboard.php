<?php
require_once '../includes/config.php';
require_once '../includes/auth.php';

requireAdmin();

$admin_major = $_SESSION['user_major'];

$stmt = $pdo->prepare("SELECT * FROM students WHERE student_major = ? ORDER BY year_level, student_name");
$stmt->execute([$admin_major]);
$students = $stmt->fetchAll();


$totalStudents = count($students);
$stmt = $pdo->prepare("SELECT COUNT(*) as total FROM courses WHERE year_level <= 4");
$stmt->execute();
$totalCourses = $stmt->fetch()['total'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - YIC</title>
    <link rel="stylesheet" href="../css/style.css">
    <style>
        .stats-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px; margin-bottom: 30px; }
        .stat-card { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 20px; border-radius: 10px; text-align: center; }
        .stat-number { font-size: 36px; font-weight: bold; }
        .stat-label { font-size: 14px; opacity: 0.9; margin-top: 5px; }
        .view-btn { background: #007bff; color: white; padding: 5px 10px; border-radius: 3px; text-decoration: none; font-size: 12px; }
    </style>
</head>
<body>
    <header class="header-fixed">
        <div class="logo">👨‍💼 Admin Panel - <?= $admin_major ?> Department</div>
        <div class="header-buttons">
            <a href="../logout.php" class="logout-btn-header">🚪 Logout</a>
        </div>
    </header>

    <main class="container">
        <section class="welcome-card">
            <h2>Welcome, <?= htmlspecialchars($_SESSION['user_name']) ?>!</h2>
            <p>Managing <?= $admin_major ?> Department</p>
        </section>

        <div class="stats-grid">
            <div class="stat-card"><div class="stat-number"><?= $totalStudents ?></div><div class="stat-label">Total Students</div></div>
            <div class="stat-card"><div class="stat-number"><?= $totalCourses ?></div><div class="stat-label">Total Courses</div></div>
            <div class="stat-card"><div class="stat-number"><?= $totalStudents * 3 ?></div><div class="stat-label">Registrations</div></div>
        </div>

        <div class="card">
            <h2>📋 Student List (<?= $admin_major ?> Department)</h2>
            <div class="table-responsive">
                <table class="data-table">
                    <thead>
                        <tr><th>ID</th><th>Name</th><th>Email</th><th>Level</th><th>Actions</th></tr>
                    </thead>
                    <tbody>
                        <?php foreach ($students as $student): ?>
                        <tr>
                            <td><?= $student['student_id'] ?></td>
                            <td><?= htmlspecialchars($student['student_name']) ?></td>
                            <td><?= htmlspecialchars($student['student_email']) ?></td>
                            <td>Level <?= $student['year_level'] ?></td>
                            <td><a href="student_detail.php?id=<?= $student['student_id'] ?>" class="view-btn">View Details</a></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </main>

    <footer class="footer-fixed">
        <p>&copy; 2026 Yanbu Industrial College - Course Registration System</p>
    </footer>
</body>
</html>