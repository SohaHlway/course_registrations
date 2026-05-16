<?php
require_once '../includes/config.php';
require_once '../includes/auth.php';

requireAdmin();

$admin_email = $_SESSION['user_email'];
$success = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['change_password'])) {
    validateCSRF($_POST['csrf_token'] ?? '');
    
    $current = $_POST['current_password'];
    $new = $_POST['new_password'];
    $confirm = $_POST['confirm_password'];
    
    $stmt = $pdo->prepare("SELECT admin_password FROM admins WHERE admin_email = ?");
    $stmt->execute([$admin_email]);
    $admin = $stmt->fetch();
    
    if (!password_verify($current, $admin['admin_password'])) {
        $error = "Current password is incorrect";
    } elseif (strlen($new) < 6) {
        $error = "New password must be at least 6 characters";
    } elseif ($new !== $confirm) {
        $error = "Passwords do not match";
    } else {
        $hashed = password_hash($new, PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("UPDATE admins SET admin_password = ? WHERE admin_email = ?");
        $stmt->execute([$hashed, $admin_email]);
        $success = "Password changed successfully!";
    }
}

$stmt = $pdo->prepare("SELECT * FROM admins WHERE admin_email = ?");
$stmt->execute([$admin_email]);
$admin = $stmt->fetch();


$stmt = $pdo->prepare("SELECT COUNT(*) as total FROM students WHERE student_major = ?");
$stmt->execute([$admin['managed_major']]);
$totalStudents = $stmt->fetch()['total'];


$stmt = $pdo->prepare("SELECT COUNT(*) as total FROM courses WHERE year_level <= 4");
$totalCourses = $stmt->fetch()['total'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Profile - YIC</title>
    <link rel="stylesheet" href="../css/style.css">
    <style>
        .profile-info { padding: 20px; }
        .info-row { display: flex; padding: 10px 0; border-bottom: 1px solid #eee; }
        .info-label { font-weight: bold; width: 180px; }
        .info-value { color: #333; }
        .success-message { background: #d4edda; color: #155724; padding: 10px; border-radius: 5px; margin-bottom: 15px; }
        .error-message { background: #f8d7da; color: #721c24; padding: 10px; border-radius: 5px; margin-bottom: 15px; }
    </style>
</head>
<body>
    <header class="header-fixed">
        <div class="logo">👨‍💼 Admin Profile</div>
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
                <h2>Admin Information</h2>
                <div class="profile-info">
                    <div class="info-row"><span class="info-label">Admin ID:</span><span class="info-value"><?= $admin['admin_id'] ?></span></div>
                    <div class="info-row"><span class="info-label">Full Name:</span><span class="info-value"><?= htmlspecialchars($admin['admin_name']) ?></span></div>
                    <div class="info-row"><span class="info-label">Email:</span><span class="info-value"><?= htmlspecialchars($admin['admin_email']) ?></span></div>
                    <div class="info-row"><span class="info-label">Managed Major:</span><span class="info-value"><?= $admin['managed_major'] ?></span></div>
                    <div class="info-row"><span class="info-label">Total Students:</span><span class="info-value"><?= $totalStudents ?> students</span></div>
                    <div class="info-row"><span class="info-label">Total Courses:</span><span class="info-value"><?= $totalCourses ?> courses</span></div>
                    <div class="info-row"><span class="info-label">Member Since:</span><span class="info-value"><?= date('F Y', strtotime($admin['created_at'])) ?></span></div>
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