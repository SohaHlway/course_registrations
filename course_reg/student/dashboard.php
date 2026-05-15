<?php
require_once '../config.php';
redirectIfNotStudent();

$student_id = $_SESSION['student_id'];

$stmt = $pdo->prepare("SELECT c.* FROM registrations r JOIN courses c ON r.course_code = c.course_code WHERE r.student_id = ? AND r.registration_status = 'registered'");
$stmt->execute([$student_id]);
$schedule = $stmt->fetchAll();

$stmt = $pdo->prepare("SELECT c.* FROM courses c 
    WHERE c.course_major = (SELECT student_major FROM students WHERE student_id = ?)
    AND c.course_code NOT IN (SELECT course_code FROM registrations WHERE student_id = ? AND registration_status = 'registered')
    AND (c.course_prerequisite IS NULL OR c.course_prerequisite IN (SELECT course_code FROM registrations WHERE student_id = ? AND registration_status IN ('registered', 'completed')))");
$stmt->execute([$student_id, $student_id, $student_id]);
$available = $stmt->fetchAll();

$totalCredits = 0;
foreach($schedule as $c) { $totalCredits += $c['course_credits']; }

$stmt = $pdo->prepare("
    SELECT c.*, 
        CASE 
            WHEN c.course_code IN (SELECT course_code FROM registrations WHERE student_id = ? AND registration_status = 'registered') THEN 'in-progress'
            WHEN c.course_code IN (SELECT course_code FROM registrations WHERE student_id = ? AND registration_status = 'completed') THEN 'completed'
            ELSE 'remaining' 
        END as status
    FROM courses c 
    WHERE c.course_major = (SELECT student_major FROM students WHERE student_id = ?)
    ORDER BY c.course_year_level, c.course_code");
$stmt->execute([$student_id, $student_id, $student_id]);
$studyPlan = $stmt->fetchAll();

$remainingCourses = array_filter($studyPlan, function($course) {
    return $course['status'] == 'remaining';
});
$remainingCount = count($remainingCourses);
$remainingCredits = array_sum(array_column($remainingCourses, 'course_credits'));

$stmt = $pdo->prepare("SELECT c.course_code, c.course_name, c.course_credits, c.course_year_level 
    FROM registrations r 
    JOIN courses c ON r.course_code = c.course_code 
    WHERE r.student_id = ? AND r.registration_status = 'completed'");
$stmt->execute([$student_id]);
$completedCourses = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Dashboard - YIC</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
    <header class="header-fixed">
        <div class="logo">YIC Course Registration</div>
        <div class="header-buttons">
            <a href="profile.php" class="back-btn-header">My Profile</a>
            <a href="../logout.php" class="logout-btn-header">Logout</a>
        </div>
    </header>

    <main class="container">
        <section class="welcome-card">
            <h2>Welcome, <?php echo htmlspecialchars($_SESSION['full_name']); ?>!</h2>
            <p>Student ID: <?php echo $_SESSION['student_id']; ?> | Major: <?php echo $_SESSION['major']; ?> | Level: <?php echo $_SESSION['year_level']; ?></p>
        </section>

        <div class="action-buttons-grid">
            <button class="action-btn active" onclick="showSection('editSchedule')">Edit Schedule</button>
            <button class="action-btn" onclick="showSection('completedCourses')">Completed Courses</button>
            <button class="action-btn" onclick="showSection('remainingCourses')">Remaining Courses</button>
            <button class="action-btn" onclick="showSection('studyPlan')">Study Plan</button>
        </div>

        <div id="editSchedule" class="section-card">
            <div class="card">
                <h2>Edit Schedule - Spring 2026</h2>
                
                <h3>My Current Schedule</h3>
                <div class="table-responsive">
                    <table class="data-table">
                        <thead><tr><th>Code</th><th>Course Name</th><th>Credits</th><th>Time Slot</th><th>Action</th></tr></thead>
                        <tbody>
                            <?php foreach($schedule as $course): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($course['course_code']); ?></td>
                                <td><?php echo htmlspecialchars($course['course_name']); ?></td>
                                <td><?php echo $course['course_credits']; ?></td>
                                <td><?php echo htmlspecialchars($course['course_time_slot']); ?></td>
                                <td><button class="btn-drop" onclick="dropCourse('<?php echo $course['course_code']; ?>')">Drop</button></td>
                            </tr>
                            <?php endforeach; ?>
                            <?php if(empty($schedule)): ?>
                            <tr><td colspan="5" style="text-align:center">No courses registered</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
                <div class="total-credits">Total Credits: <?php echo $totalCredits; ?> / 18</div>
                <button class="btn-danger" onclick="dropAllCourses()">Drop All Courses</button>

                <h3>Available Courses to Add</h3>
                <div class="table-responsive">
                    <table class="data-table">
                        <thead><tr><th>Code</th><th>Course Name</th><th>Credits</th><th>Level</th><th>Time Slot</th><th>Prereq</th><th>Action</th></tr></thead>
                        <tbody>
                            <?php foreach($available as $course): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($course['course_code']); ?></td>
                                <td><?php echo htmlspecialchars($course['course_name']); ?></td>
                                <td><?php echo $course['course_credits']; ?></td>
                                <td>Level <?php echo $course['course_year_level']; ?></td>
                                <td><?php echo htmlspecialchars($course['course_time_slot']); ?></td>
                                <td><?php echo $course['course_prerequisite'] ?: 'None'; ?></td>
                                <td><button class="btn-register" onclick="registerCourse('<?php echo $course['course_code']; ?>')">Register</button></td>
                            </tr>
                            <?php endforeach; ?>
                            <?php if(empty($available)): ?>
                            <tr><td colspan="7" style="text-align:center">No available courses</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div id="completedCourses" class="section-card" style="display:none">
            <div class="card">
                <h2>Completed Courses</h2>
                <div class="stats-summary">
                    <div class="stat-badge">Total Completed: <span><?php echo count($completedCourses); ?></span> courses</div>
                    <div class="stat-badge">Total Credits: <span><?php echo array_sum(array_column($completedCourses, 'course_credits')); ?></span> hours</div>
                </div>
                <div class="table-responsive">
                    <table class="data-table">
                        <thead><tr><th>Level</th><th>Code</th><th>Course Name</th><th>Credits</th></tr></thead>
                        <tbody>
                            <?php foreach($completedCourses as $course): ?>
                            <tr>
                                <td>Level <?php echo $course['course_year_level']; ?></td>
                                <td><?php echo htmlspecialchars($course['course_code']); ?></td>
                                <td><?php echo htmlspecialchars($course['course_name']); ?></td>
                                <td><?php echo $course['course_credits']; ?></td>
                            </tr>
                            <?php endforeach; ?>
                            <?php if(empty($completedCourses)): ?>
                            <tr><td colspan="4" style="text-align:center">No completed courses</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div id="remainingCourses" class="section-card" style="display:none">
            <div class="card">
                <h2>Remaining Courses</h2>
                <div class="stats-summary">
                    <div class="stat-badge">Total Remaining: <span><?php echo $remainingCount; ?></span> courses</div>
                    <div class="stat-badge">Total Credits: <span><?php echo $remainingCredits; ?></span> hours</div>
                </div>
                <div class="table-responsive">
                    <table class="data-table">
                        <thead><tr><th>Level</th><th>Code</th><th>Course Name</th><th>Credits</th><th>Prerequisite</th></tr></thead>
                        <tbody>
                            <?php foreach($remainingCourses as $course): ?>
                            <tr>
                                <td>Level <?php echo $course['course_year_level']; ?></td>
                                <td><?php echo htmlspecialchars($course['course_code']); ?></td>
                                <td><?php echo htmlspecialchars($course['course_name']); ?></td>
                                <td><?php echo $course['course_credits']; ?></td>
                                <td><?php echo $course['course_prerequisite'] ?: 'None'; ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div id="studyPlan" class="section-card" style="display:none">
            <div class="card">
                <h2>Study Plan - <?php echo $_SESSION['major']; ?> Major</h2>
                <div class="study-legend">
                    <span><span class="legend-completed"></span>Completed</span>
                    <span><span class="legend-progress"></span>In Progress</span>
                    <span><span class="legend-remaining"></span>Not Started</span>
                </div>
                <div class="table-responsive">
                    <table class="data-table">
                        <thead><tr><th>Level</th><th>Code</th><th>Course Name</th><th>Credits</th><th>Prerequisite</th><th>Status</th></tr></thead>
                        <tbody>
                            <?php foreach($studyPlan as $course): ?>
                            <tr>
                                <td>Level <?php echo $course['course_year_level']; ?></td>
                                <td><?php echo htmlspecialchars($course['course_code']); ?></td>
                                <td><?php echo htmlspecialchars($course['course_name']); ?></td>
                                <td><?php echo $course['course_credits']; ?></td>
                                <td><?php echo $course['course_prerequisite'] ?: 'None'; ?></td>
                                <td>
                                    <?php if($course['status'] == 'completed'): ?>
                                        <span class="status-completed">Completed</span>
                                    <?php elseif($course['status'] == 'in-progress'): ?>
                                        <span class="status-progress">In Progress</span>
                                    <?php else: ?>
                                        <span class="status-remaining">Not Started</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </main>

    <footer class="footer-fixed">
        <p>&copy; 2026 Yanbu Industrial College - Course Registration System</p>
    </footer>

    <script>
        function showSection(sectionId) {
            document.getElementById('editSchedule').style.display = 'none';
            document.getElementById('completedCourses').style.display = 'none';
            document.getElementById('remainingCourses').style.display = 'none';
            document.getElementById('studyPlan').style.display = 'none';
            document.getElementById(sectionId).style.display = 'block';
            const btns = document.querySelectorAll('.action-btn');
            const sections = ['editSchedule', 'completedCourses', 'remainingCourses', 'studyPlan'];
            btns.forEach((btn, i) => {
                btn.classList.remove('active');
                if (sectionId === sections[i]) btn.classList.add('active');
            });
        }
        
        async function registerCourse(courseCode) {
            if (!confirm('Register for this course?')) return;
            let formData = new FormData();
            formData.append('course_code', courseCode);
            let response = await fetch('register_course.php', { method: 'POST', body: formData });
            let data = await response.json();
            showAlert(data.message, data.success ? 'success' : 'error');
            if (data.success) setTimeout(() => location.reload(), 1500);
        }
        
        async function dropCourse(courseCode) {
            if (!confirm('Drop this course?')) return;
            let formData = new FormData();
            formData.append('course_code', courseCode);
            let response = await fetch('drop_course.php', { method: 'POST', body: formData });
            let data = await response.json();
            showAlert(data.message, data.success ? 'success' : 'error');
            if (data.success) setTimeout(() => location.reload(), 1500);
        }
        
        async function dropAllCourses() {
            if (!confirm('WARNING: Drop ALL your courses?')) return;
            let response = await fetch('drop_all.php', { method: 'POST' });
            let data = await response.json();
            showAlert(data.message, data.success ? 'success' : 'error');
            if (data.success) setTimeout(() => location.reload(), 1500);
        }
        
        function showAlert(message, type) {
            let alertDiv = document.createElement('div');
            alertDiv.className = type === 'success' ? 'alert-success' : 'alert-error';
            alertDiv.innerHTML = message + '<span style="float:right;cursor:pointer" onclick="this.parentElement.remove()">&times;</span>';
            alertDiv.style.cssText = 'position:fixed;top:80px;right:20px;z-index:1000;padding:12px;border-radius:8px;background:' + (type === 'success' ? '#d4edda' : '#f8d7da') + ';color:' + (type === 'success' ? '#155724' : '#721c24');
            document.body.appendChild(alertDiv);
            setTimeout(() => alertDiv.remove(), 3000);
        }
    </script>
    <style>
        .alert-success, .alert-error { position: fixed; top: 80px; right: 20px; z-index: 1000; padding: 12px; border-radius: 8px; }
        .status-completed { background: #d4edda; color: #155724; padding: 0.2rem 0.6rem; border-radius: 20px; font-size: 0.75rem; display: inline-block; }
        .status-progress { background: #fff3cd; color: #856404; padding: 0.2rem 0.6rem; border-radius: 20px; font-size: 0.75rem; display: inline-block; }
        .status-remaining { background: #e9ecef; color: #6c757d; padding: 0.2rem 0.6rem; border-radius: 20px; font-size: 0.75rem; display: inline-block; }
        .legend-completed { width: 16px; height: 16px; background: #d4edda; border: 1px solid #155724; border-radius: 4px; display: inline-block; }
        .legend-progress { width: 16px; height: 16px; background: #fff3cd; border: 1px solid #856404; border-radius: 4px; display: inline-block; }
        .legend-remaining { width: 16px; height: 16px; background: #e9ecef; border: 1px solid #6c757d; border-radius: 4px; display: inline-block; }
        .study-legend { display: flex; gap: 1.5rem; margin-bottom: 1rem; align-items: center; }
        .study-legend span { display: flex; align-items: center; gap: 0.5rem; font-size: 0.8rem; }
        .stats-summary { display: flex; gap: 1rem; margin-bottom: 1rem; flex-wrap: wrap; }
        .stat-badge { background: #f0f2f5; padding: 0.5rem 1rem; border-radius: 20px; font-size: 0.9rem; font-weight: bold; color: #1a3c34; }
        .stat-badge span { color: #0f2b25; font-size: 1.2rem; }
    </style>
</body>
</html>