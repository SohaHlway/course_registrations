<?php
require_once '../includes/config.php';
require_once '../includes/auth.php';

requireStudent();

$student_id = $_SESSION['user_id'];
$success = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['change_password'])) {
    validateCSRF($_POST['csrf_token'] ?? '');
    
    $current = $_POST['current_password'];
    $new = $_POST['new_password'];
    $confirm = $_POST['confirm_password'];
    
    $stmt = $pdo->prepare("SELECT student_password FROM students WHERE student_id = ?");
    $stmt->execute([$student_id]);
    $user = $stmt->fetch();
    
    if (!password_verify($current, $user['student_password'])) {
        $error = "Current password is incorrect";
    } elseif (strlen($new) < 6) {
        $error = "New password must be at least 6 characters";
    } elseif ($new !== $confirm) {
        $error = "Passwords do not match";
    } else {
        $hashed = password_hash($new, PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("UPDATE students SET student_password = ? WHERE student_id = ?");
        $stmt->execute([$hashed, $student_id]);
        $success = "Password changed successfully!";
    }
}

$student = getStudentData($student_id, $pdo);
$completedCredits = getCompletedCredits($student_id, $pdo);
$currentCourses = getStudentCourses($student_id, $pdo, 'Enrolled');
$currentCredits = 0;
foreach ($currentCourses as $c) $currentCredits += $c['credits'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Profile - Student</title>
    <link rel="stylesheet" href="../css/style.css">
    <style>
        .profile-info { padding: 20px; }
        .info-row { display: flex; padding: 10px 0; border-bottom: 1px solid #eee; }
        .info-label { font-weight: bold; width: 150px; }
        .info-value { color: #333; }
        .success-message { background: #d4edda; color: #155724; padding: 10px; border-radius: 5px; margin-bottom: 15px; }
        .error-message { background: #f8d7da; color: #721c24; padding: 10px; border-radius: 5px; margin-bottom: 15px; }
    </style>
</head>
<body>
    <header class="header-fixed">
        <div class="logo">👤 My Profile</div>
        <div class="header-buttons">
            <a href="dashboard.php" class="back-btn-header">← Back to Dashboard</a>
            <a href="../logout.php" class="logout-btn-header">🚪 Logout</a>
        </div>
    </header>

    <main class="container">
        <?php if ($success): ?>
            <div class="success-message"><?= $success ?></div>
        <?php endif; ?>
        <?php if ($error): ?>
            <div class="error-message"><?= $error ?></div>
        <?php endif; ?>

        <div class="dashboard-grid">
            <div class="card">
                <h2>Personal Information</h2>
                <div class="profile-info">
                    <div class="info-row"><span class="info-label">Student ID:</span><span class="info-value"><?= $student['student_id'] ?></span></div>
                    <div class="info-row"><span class="info-label">Full Name:</span><span class="info-value"><?= htmlspecialchars($student['student_name']) ?></span></div>
                    <div class="info-row"><span class="info-label">Email:</span><span class="info-value"><?= htmlspecialchars($student['student_email']) ?></span></div>
                    <div class="info-row"><span class="info-label">Major:</span><span class="info-value"><?= $student['student_major'] ?></span></div>
                    <div class="info-row"><span class="info-label">Year Level:</span><span class="info-value">Level <?= $student['year_level'] ?></span></div>
                    <div class="info-row"><span class="info-label">Completed Credits:</span><span class="info-value"><?= $completedCredits ?> hours</span></div>
                    <div class="info-row"><span class="info-label">Current Credits:</span><span class="info-value"><?= $currentCredits ?> hours</span></div>
                    <div class="info-row"><span class="info-label">Member Since:</span><span class="info-value"><?= date('F Y', strtotime($student['created_at'])) ?></span></div>
                </div>
            </div>

            <div class="card">
                <h2>Change Password</h2>
                <form method="POST">
                    <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
                    <input type="hidden" name="change_password" value="1">
                    
                    <div class="form-group">
                        <label>Current Password:</label>
                        <input type="password" name="current_password" required>
                    </div>
                    <div class="form-group">
                        <label>New Password:</label>
                        <input type="password" name="new_password" required>
                    </div>
                    <div class="form-group">
                        <label>Confirm Password:</label>
                        <input type="password" name="confirm_password" required>
                    </div>
                    <button type="submit" class="btn-primary">Update Password</button>
                </form>
            </div>
        </div>
    </main>

    <footer class="footer-fixed">
        <p>&copy; 2026 Yanbu Industrial College - Course Registration System</p>
    </footer>
</body>
</html>