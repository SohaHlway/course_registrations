<?php
require_once '../config.php';
redirectIfNotAdmin();

$student_id = $_GET['id'] ?? 0;

$stmt = $pdo->prepare("SELECT * FROM users WHERE user_id = ? AND user_type = 'student'");
$stmt->execute([$student_id]);
$student = $stmt->fetch();

if (!$student) {
    header('Location: dashboard.php');
    exit;
}

$admin_major = $_SESSION['admin_major'];
if ($student['major'] !== $admin_major) {
    header('Location: dashboard.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['add_course'])) {
        $course_id = $_POST['course_id'];
        $stmt = $pdo->prepare("INSERT IGNORE INTO registrations (user_id, course_id, semester, year, status) VALUES (?, ?, 'Spring', 2026, 'registered')");
        $stmt->execute([$student_id, $course_id]);
    } elseif (isset($_POST['drop_course'])) {
        $course_id = $_POST['course_id'];
        $stmt = $pdo->prepare("UPDATE registrations SET status = 'dropped' WHERE user_id = ? AND course_id = ? AND status = 'registered'");
        $stmt->execute([$student_id, $course_id]);
    } elseif (isset($_POST['drop_all'])) {
        $stmt = $pdo->prepare("UPDATE registrations SET status = 'dropped' WHERE user_id = ? AND status = 'registered'");
        $stmt->execute([$student_id]);
    }
    header("Location: student_detail.php?id=$student_id");
    exit;
}

$stmt = $pdo->prepare("SELECT c.* FROM registrations r JOIN courses c ON r.course_id = c.course_id WHERE r.user_id = ? AND r.status = 'registered'");
$stmt->execute([$student_id]);
$schedule = $stmt->fetchAll();

$stmt = $pdo->prepare("SELECT c.* FROM courses c WHERE c.major = ? AND c.course_id NOT IN (SELECT course_id FROM registrations WHERE user_id = ? AND status = 'registered') AND c.course_id NOT IN (SELECT course_id FROM completed_courses WHERE user_id = ?)");
$stmt->execute([$admin_major, $student_id, $student_id]);
$available = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Student - Admin</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
    <header class="header-fixed">
        <div class="logo">Manage Student Registrations</div>
        <div class="header-buttons">
            <span class="admin-info">Admin: <?php echo $_SESSION['full_name']; ?> (<?php echo $admin_major; ?>)</span>
            <a href="dashboard.php" class="back-btn-header">Back to Dashboard</a>
            <a href="../logout.php" class="logout-btn-header">Logout</a>
        </div>
    </header>

    <main class="container">
        <section class="welcome-card">
            <h2><?php echo $student['full_name']; ?></h2>
            <p>Student ID: <?php echo $student['username']; ?> | Major: <?php echo $student['major']; ?> | Year Level: <?php echo $student['year_level']; ?></p>
        </section>

        <div class="dashboard-grid">
            <div class="card">
                <h2>Student's Schedule</h2>
                <form method="POST">
                    <div class="table-responsive">
                        <table class="data-table">
                            <thead><tr><th>Code</th><th>Course Name</th><th>Credits</th><th>Time Slot</th><th>Action</th></tr></thead>
                            <tbody>
                                <?php foreach($schedule as $course): ?>
                                <tr>
                                    <td><?php echo $course['course_code']; ?></td>
                                    <td><?php echo $course['course_name']; ?></td>
                                    <td><?php echo $course['credits']; ?></td>
                                    <td><?php echo $course['time_slot']; ?></td>
                                    <td><button type="submit" name="drop_course" value="1" class="btn-drop" onclick="this.form.course_id.value=<?php echo $course['course_id']; ?>">Drop</button></td>
                                </tr>
                                <?php endforeach; ?>
                                <?php if(empty($schedule)): ?>
                                <tr><td colspan="5" style="text-align:center">No courses registered</td></tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                    <input type="hidden" name="course_id" value="">
                    <button type="submit" name="drop_all" class="btn-danger">Drop All Courses</button>
                </form>
            </div>

            <div class="card">
                <h2>Available Courses to Add</h2>
                <form method="POST">
                    <div class="table-responsive">
                        <table class="data-table">
                            <thead><tr><th>Code</th><th>Name</th><th>Credits</th><th>Level</th><th>Prereq</th><th>Action</th></tr></thead>
                            <tbody>
                                <?php foreach($available as $course): ?>
                                <tr>
                                    <td><?php echo $course['course_code']; ?></td>
                                    <td><?php echo $course['course_name']; ?></td>
                                    <td><?php echo $course['credits']; ?></td>
                                    <td>Level <?php echo $course['level']; ?></td>
                                    <td><?php echo $course['prerequisite_code'] ?: 'None'; ?></td>
                                    <td><button type="submit" name="add_course" value="1" class="btn-register" onclick="this.form.course_id.value=<?php echo $course['course_id']; ?>">Add</button></td>
                                </tr>
                                <?php endforeach; ?>
                                <?php if(empty($available)): ?>
                                <tr><td colspan="6" style="text-align:center">No available courses</td></tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                    <input type="hidden" name="course_id" value="">
                </form>
            </div>
        </div>
    </main>

    <footer class="footer-fixed">
        <p>&copy; 2026 Yanbu Industrial College - Course Registration System</p>
    </footer>
</body>
</html>