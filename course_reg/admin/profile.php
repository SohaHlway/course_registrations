<?php
require_once '../config.php';
redirectIfNotAdmin();

$message = '';
$error = '';
$admin_id = $_SESSION['admin_id'];

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $current = $_POST['currentPassword'] ?? '';
    $new = $_POST['newPassword'] ?? '';
    $confirm = $_POST['confirmNewPassword'] ?? '';
    
    if ($new !== $confirm) {
        $error = 'New passwords do not match';
    } elseif (strlen($new) < 6) {
        $error = 'Password must be at least 6 characters';
    } else {
        $stmt = $pdo->prepare("SELECT admin_password FROM admins WHERE admin_id = ?");
        $stmt->execute([$admin_id]);
        $user = $stmt->fetch();
        
        if (password_verify($current, $user['admin_password'])) {
            $newHash = password_hash($new, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("UPDATE admins SET admin_password = ? WHERE admin_id = ?");
            if ($stmt->execute([$newHash, $admin_id])) {
                $message = 'Password changed successfully';
            } else {
                $error = 'Failed to change password';
            }
        } else {
            $error = 'Current password is incorrect';
        }
    }
}

$stmt = $pdo->prepare("SELECT COUNT(*) FROM students WHERE student_major = ?");
$stmt->execute([$_SESSION['admin_major']]);
$studentCount = $stmt->fetchColumn();

$stmt = $pdo->prepare("SELECT COUNT(*) FROM courses WHERE course_major = ?");
$stmt->execute([$_SESSION['admin_major']]);
$courseCount = $stmt->fetchColumn();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Profile - Admin</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
    <header class="header-fixed">
        <div class="logo">Admin Profile</div>
        <div class="header-buttons">
            <a href="dashboard.php" class="back-btn-header">Back to Dashboard</a>
            <a href="../logout.php" class="logout-btn-header">Logout</a>
        </div>
    </header>

    <main class="container">
        <?php if($message): ?>
            <div class="alert-success"><?php echo htmlspecialchars($message); ?></div>
        <?php endif; ?>
        <?php if($error): ?>
            <div class="alert-error"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>

        <div class="dashboard-grid">
            <div class="card">
                <h2>Admin Information</h2>
                <div class="profile-info">
                    <div class="info-row"><span class="info-label">Admin ID:</span><span class="info-value"><?php echo $_SESSION['admin_id']; ?></span></div>
                    <div class="info-row"><span class="info-label">Full Name:</span><span class="info-value"><?php echo htmlspecialchars($_SESSION['full_name']); ?></span></div>
                    <div class="info-row"><span class="info-label">Email:</span><span class="info-value"><?php echo htmlspecialchars($_SESSION['email']); ?></span></div>
                    <div class="info-row"><span class="info-label">Managed Major:</span><span class="info-value"><?php echo $_SESSION['admin_major']; ?></span></div>
                    <div class="info-row"><span class="info-label">Total Students:</span><span class="info-value"><?php echo $studentCount; ?> students</span></div>
                    <div class="info-row"><span class="info-label">Total Courses:</span><span class="info-value"><?php echo $courseCount; ?> courses</span></div>
                </div>
            </div>

            <div class="card">
                <h2>Change Password</h2>
                <form method="POST">
                    <div class="form-group"><label for="currentPassword">Current Password:</label><input type="password" id="currentPassword" name="currentPassword" required></div>
                    <div class="form-group"><label for="newPassword">New Password:</label><input type="password" id="newPassword" name="newPassword" required></div>
                    <div class="form-group"><label for="confirmNewPassword">Confirm New Password:</label><input type="password" id="confirmNewPassword" name="confirmNewPassword" required></div>
                    <div class="form-group checkbox-group"><label class="checkbox-inline"><input type="checkbox" id="showPassword"> Show Password</label></div>
                    <button type="submit" class="btn-primary">Update Password</button>
                </form>
            </div>
        </div>
    </main>

    <footer class="footer-fixed">
        <p>&copy; 2026 Yanbu Industrial College - Course Registration System</p>
    </footer>

    <script>
        document.getElementById('showPassword')?.addEventListener('change', function() {
            let type = this.checked ? 'text' : 'password';
            document.getElementById('currentPassword').type = type;
            document.getElementById('newPassword').type = type;
            document.getElementById('confirmNewPassword').type = type;
        });
    </script>
    <style>
        .alert-success { background: #d4edda; color: #155724; padding: 12px; border-radius: 8px; margin-bottom: 20px; }
        .alert-error { background: #f8d7da; color: #721c24; padding: 12px; border-radius: 8px; margin-bottom: 20px; }
    </style>
</body>
</html>