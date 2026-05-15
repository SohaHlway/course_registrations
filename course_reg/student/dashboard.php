<?php
require_once '../config.php';
redirectIfNotLoggedIn();

if (isAdmin()) {
    header('Location: ../admin/dashboard.php');
    exit;
}

$user_id = $_SESSION['user_id'];
$major = $_SESSION['major'];
$year_level = $_SESSION['year_level'];

$stmt = $pdo->prepare("SELECT c.* FROM registrations r JOIN courses c ON r.course_id = c.course_id WHERE r.user_id = ? AND r.status = 'registered'");
$stmt->execute([$user_id]);
$schedule = $stmt->fetchAll();

$stmt = $pdo->prepare("SELECT c.* FROM courses c WHERE c.major = ? AND c.course_id NOT IN (SELECT course_id FROM registrations WHERE user_id = ? AND status = 'registered') AND c.course_id NOT IN (SELECT course_id FROM completed_courses WHERE user_id = ?)");
$stmt->execute([$major, $user_id, $user_id]);
$available = $stmt->fetchAll();

$stmt = $pdo->prepare("SELECT cc.*, c.course_code, c.course_name, c.credits, c.level FROM completed_courses cc JOIN courses c ON cc.course_id = c.course_id WHERE cc.user_id = ? ORDER BY cc.year DESC, cc.semester DESC");
$stmt->execute([$user_id]);
$completed = $stmt->fetchAll();

$totalCredits = 0;
foreach($schedule as $c) { $totalCredits += $c['credits']; }

$allCourses = $pdo->prepare("SELECT c.*, 
    CASE WHEN c.course_id IN (SELECT course_id FROM completed_courses WHERE user_id = ?) THEN 'completed'
         WHEN c.course_id IN (SELECT course_id FROM registrations WHERE user_id = ? AND status = 'registered') THEN 'in-progress'
         ELSE 'remaining' END as status
    FROM courses c WHERE c.major = ? ORDER BY c.level, c.course_code");
$allCourses->execute([$user_id, $user_id, $major]);
$studyPlan = $allCourses->fetchAll();
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
            <h2>Welcome, <?php echo $_SESSION['full_name']; ?>!</h2>
            <p>Student ID: <?php echo $_SESSION['username']; ?> | Major: <?php echo $major; ?> | Level: <?php echo $year_level; ?></p>
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
                        <tbody id="myScheduleList">
                            <?php foreach($schedule as $course): ?>
                            <tr>
                                <td><?php echo $course['course_code']; ?></td>
                                <td><?php echo $course['course_name']; ?></td>
                                <td><?php echo $course['credits']; ?></td>
                                <td><?php echo $course['time_slot']; ?></td>
                                <td><button class="btn-drop" onclick="dropCourse(<?php echo $course['course_id']; ?>)">Drop</button></td>
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
                        <tbody id="availableCoursesList">
                            <?php foreach($available as $course): ?>
                            <tr>
                                <td><?php echo $course['course_code']; ?></td>
                                <td><?php echo $course['course_name']; ?></td>
                                <td><?php echo $course['credits']; ?></td>
                                <td>Level <?php echo $course['level']; ?></td>
                                <td><?php echo $course['time_slot']; ?></td>
                                <td><?php echo $course['prerequisite_code'] ?: 'None'; ?></td>
                                <td><button class="btn-register" onclick="registerCourse(<?php echo $course['course_id']; ?>)">Register</button></td>
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
                    <div class="stat-badge">Total Completed: <span><?php echo count($completed); ?></span> courses</div>
                    <div class="stat-badge">Total Credits: <span><?php echo array_sum(array_column($completed, 'credits')); ?></span> hours</div>
                </div>
                <div class="table-responsive">
                    <table class="data-table">
                        <thead><tr><th>Level</th><th>Code</th><th>Course Name</th><th>Credits</th><th>Grade</th><th>Semester</th></tr></thead>
                        <tbody>
                            <?php foreach($completed as $course): ?>
                            <tr>
                                <td>Level <?php echo $course['level']; ?></td>
                                <td><?php echo $course['course_code']; ?></td>
                                <td><?php echo $course['course_name']; ?></td>
                                <td><?php echo $course['credits']; ?></td>
                                <td><span class="badge-success"><?php echo $course['grade']; ?></span></td>
                                <td><?php echo $course['semester'] . ' ' . $course['year']; ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div id="remainingCourses" class="section-card" style="display:none">
            <div class="card">
                <h2>Remaining Courses</h2>
                <div class="stats-summary">
                    <div class="stat-badge">Total Remaining: <span id="remainingCount">0</span> courses</div>
                    <div class="stat-badge">Total Credits: <span id="remainingCredits">0</span> hours</div>
                </div>
                <div class="table-responsive">
                    <table class="data-table">
                        <thead><tr><th>Level</th><th>Code</th><th>Course Name</th><th>Credits</th><th>Prerequisite</th></tr></thead>
                        <tbody id="remainingCoursesList"></tbody>
                    </table>
                </div>
            </div>
        </div>

        <div id="studyPlan" class="section-card" style="display:none">
            <div class="card">
                <h2>Study Plan - Computer Science (SC)</h2>
                <div class="study-legend">
                    <span><span class="legend-passed"></span>Completed</span>
                    <span><span class="legend-progress"></span>In Progress</span>
                    <span><span class="legend-remaining"></span>Not Started</span>
                </div>
                <div class="table-responsive">
                    <table class="data-table">
                        <thead><tr><th>Level</th><th>Course Code</th><th>Course Name</th><th>Credits</th><th>Status</th></tr></thead>
                        <tbody>
                            <?php foreach($studyPlan as $course): ?>
                            <tr>
                                <td>Level <?php echo $course['level']; ?></td>
                                <td><?php echo $course['course_code']; ?></td>
                                <td><?php echo $course['course_name']; ?></td>
                                <td><?php echo $course['credits']; ?></td>
                                <td>
                                    <?php if($course['status'] == 'completed'): ?>
                                        <span class="status-passed">Completed</span>
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
            
            document.querySelectorAll('.action-btn').forEach((btn, i) => {
                btn.classList.remove('active');
                if ((sectionId === 'editSchedule' && i === 0) ||
                    (sectionId === 'completedCourses' && i === 1) ||
                    (sectionId === 'remainingCourses' && i === 2) ||
                    (sectionId === 'studyPlan' && i === 3)) {
                    btn.classList.add('active');
                }
            });
            
            if (sectionId === 'remainingCourses') {
                fetchRemainingCourses();
            }
        }
        
        async function registerCourse(courseId) {
            const formData = new FormData();
            formData.append('course_id', courseId);
            
            const response = await fetch('register_course.php', { method: 'POST', body: formData });
            const data = await response.json();
            showAlert(data.message, data.success ? 'success' : 'error');
            if (data.success) setTimeout(() => location.reload(), 1500);
        }
        
        async function dropCourse(courseId) {
            if (!confirm('Are you sure you want to drop this course?')) return;
            const formData = new FormData();
            formData.append('course_id', courseId);
            const response = await fetch('drop_course.php', { method: 'POST', body: formData });
            const data = await response.json();
            showAlert(data.message, data.success ? 'success' : 'error');
            if (data.success) setTimeout(() => location.reload(), 1500);
        }
        
        async function dropAllCourses() {
            if (!confirm('WARNING: Drop ALL your courses?')) return;
            const response = await fetch('drop_all.php', { method: 'POST' });
            const data = await response.json();
            showAlert(data.message, data.success ? 'success' : 'error');
            if (data.success) setTimeout(() => location.reload(), 1500);
        }
        
        async function fetchRemainingCourses() {
            const response = await fetch('get_remaining.php');
            const data = await response.json();
            if (data.success) {
                document.getElementById('remainingCount').innerHTML = data.count;
                document.getElementById('remainingCredits').innerHTML = data.credits;
                const tbody = document.getElementById('remainingCoursesList');
                tbody.innerHTML = '';
                data.courses.forEach(course => {
                    const row = tbody.insertRow();
                    row.insertCell(0).innerHTML = `Level ${course.level}`;
                    row.insertCell(1).innerHTML = course.course_code;
                    row.insertCell(2).innerHTML = course.course_name;
                    row.insertCell(3).innerHTML = course.credits;
                    row.insertCell(4).innerHTML = course.prerequisite_code || 'None';
                });
            }
        }
        
        function showAlert(message, type) {
            const alertDiv = document.createElement('div');
            alertDiv.className = type === 'success' ? 'alert-success' : 'alert-error';
            alertDiv.innerHTML = message + '<span style="float:right;cursor:pointer" onclick="this.parentElement.remove()">&times;</span>';
            alertDiv.style.cssText = 'position:fixed;top:80px;right:20px;z-index:1000;padding:12px;border-radius:8px;';
            alertDiv.style.background = type === 'success' ? '#d4edda' : '#f8d7da';
            alertDiv.style.color = type === 'success' ? '#155724' : '#721c24';
            document.body.appendChild(alertDiv);
            setTimeout(() => alertDiv.remove(), 3000);
        }
    </script>
    <style>
        .alert-success, .alert-error { position: fixed; top: 80px; right: 20px; z-index: 1000; padding: 12px; border-radius: 8px; }
        .alert-success { background: #d4edda; color: #155724; }
        .alert-error { background: #f8d7da; color: #721c24; }
    </style>
</body>
</html>