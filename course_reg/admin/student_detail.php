<?php
require_once '../config.php';
redirectIfNotAdmin();

$student_id = $_GET['id'] ?? 0;

$stmt = $pdo->prepare("SELECT * FROM students WHERE student_id = ?");
$stmt->execute([$student_id]);
$student = $stmt->fetch();

if (!$student) {
    header('Location: dashboard.php');
    exit;
}

$admin_major = $_SESSION['admin_major'];

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['add_course'])) {
        $course_code = $_POST['add_course'];
        
        $check = $pdo->prepare("SELECT COUNT(*) FROM registrations WHERE student_id = ? AND course_code = ? AND registration_status = 'registered'");
        $check->execute([$student_id, $course_code]);
        
        if ($check->fetchColumn() == 0) {
            $insert = $pdo->prepare("INSERT INTO registrations (student_id, course_code, semester, registration_status) VALUES (?, ?, 'Spring', 'registered')");
            $insert->execute([$student_id, $course_code]);
        }
        header("Location: student_detail.php?id=$student_id");
        exit;
    }
    
    if (isset($_POST['drop_course'])) {
        $course_code = $_POST['drop_course'];
        $update = $pdo->prepare("UPDATE registrations SET registration_status = 'dropped' WHERE student_id = ? AND course_code = ? AND registration_status = 'registered'");
        $update->execute([$student_id, $course_code]);
        header("Location: student_detail.php?id=$student_id");
        exit;
    }
    
    if (isset($_POST['drop_all'])) {
        $update = $pdo->prepare("UPDATE registrations SET registration_status = 'dropped' WHERE student_id = ? AND registration_status = 'registered'");
        $update->execute([$student_id]);
        header("Location: student_detail.php?id=$student_id");
        exit;
    }
}

$schedule = $pdo->prepare("SELECT c.* FROM registrations r JOIN courses c ON r.course_code = c.course_code WHERE r.student_id = ? AND r.registration_status = 'registered'");
$schedule->execute([$student_id]);
$schedule = $schedule->fetchAll();

$registeredCodes = array_column($schedule, 'course_code');

$available = $pdo->prepare("SELECT * FROM courses WHERE course_major = ? AND course_code NOT IN (SELECT course_code FROM registrations WHERE student_id = ? AND registration_status = 'registered')");
$available->execute([$admin_major, $student_id]);
$availableCourses = $available->fetchAll();

$totalCredits = 0;
foreach($schedule as $c) { $totalCredits += $c['course_credits']; }
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
            <span class="admin-info">Admin: <?php echo htmlspecialchars($_SESSION['full_name']); ?> (<?php echo $admin_major; ?>)</span>
            <a href="dashboard.php" class="back-btn-header">Back to Dashboard</a>
            <a href="../logout.php" class="logout-btn-header">Logout</a>
        </div>
    </header>

    <main class="container">
        <section class="welcome-card">
            <h2><?php echo htmlspecialchars($student['student_name']); ?></h2>
            <p>Student ID: <?php echo $student['student_id']; ?> | Major: <?php echo $student['student_major']; ?> | Year Level: <?php echo $student['student_year_level']; ?></p>
        </section>

        <div class="dashboard-grid">
            <div class="card">
                <h2>Student's Schedule</h2>
                <div class="total-credits">Total Credits: <?php echo $totalCredits; ?></div>
                <div class="table-responsive">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Code</th>
                                <th>Course Name</th>
                                <th>Credits</th>
                                <th>Time Slot</th>
                                <th>Level</th>
                                <th>Prereq</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach($schedule as $course): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($course['course_code']); ?></td>
                                <td><?php echo htmlspecialchars($course['course_name']); ?></td>
                                <td><?php echo $course['course_credits']; ?></td>
                                <td><?php echo htmlspecialchars($course['course_time_slot']); ?></td>
                                <td>Level <?php echo $course['course_year_level']; ?></td>
                                <td><?php echo $course['course_prerequisite'] ?: 'None'; ?></td>
                                <td>
                                    <form method="POST" style="display:inline;">
                                        <button type="submit" name="drop_course" value="<?php echo $course['course_code']; ?>" class="btn-drop" onclick="return confirm('Drop this course?')">Drop</button>
                                    </form>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                            <?php if(empty($schedule)): ?>
                            <tr><td colspan="7" style="text-align:center">No courses registered</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
                <form method="POST">
                    <button type="submit" name="drop_all" class="btn-danger" onclick="return confirm('Drop ALL courses?')">Drop All Courses</button>
                </form>
            </div>

            <div class="card">
                <h2>Available Courses to Add</h2>
                <div class="table-responsive">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Code</th>
                                <th>Name</th>
                                <th>Credits</th>
                                <th>Level</th>
                                <th>Prereq</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach($availableCourses as $course): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($course['course_code']); ?></td>
                                <td><?php echo htmlspecialchars($course['course_name']); ?></td>
                                <td><?php echo $course['course_credits']; ?></td>
                                <td>Level <?php echo $course['course_year_level']; ?></td>
                                <td><?php echo $course['course_prerequisite'] ?: 'None'; ?></td>
                                <td>
                                    <form method="POST" style="display:inline;">
                                        <button type="submit" name="add_course" value="<?php echo $course['course_code']; ?>" class="btn-register">Add</button>
                                    </form>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                            <?php if(empty($availableCourses)): ?>
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
</body>
</html>