<?php
require_once 'includes/config.php';
require_once 'includes/auth.php';

if (isLoggedIn()) {
    if ($_SESSION['user_type'] === 'student') {
        header("Location: student/dashboard.php");
    } else {
        header("Location: admin/dashboard.php");
    }
    exit();
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    validateCSRF($_POST['csrf_token'] ?? '');
    
    $email = filter_var($_POST['email'] ?? '', FILTER_SANITIZE_EMAIL);
    $password = $_POST['password'] ?? '';
    $userType = $_POST['userType'] ?? '';
    
    if ($userType === 'student') {
        $stmt = $pdo->prepare("SELECT * FROM students WHERE student_email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();
        
        if ($user && password_verify($password, $user['student_password'])) {
            $_SESSION['user_id'] = $user['student_id'];
            $_SESSION['user_name'] = $user['student_name'];
            $_SESSION['user_email'] = $user['student_email'];
            $_SESSION['user_type'] = 'student';
            $_SESSION['user_major'] = $user['student_major'];
            $_SESSION['user_level'] = $user['year_level'];
            header("Location: student/dashboard.php");
            exit();
        } else {
            $error = "Invalid email or password";
        }
    } elseif ($userType === 'admin') {
        $stmt = $pdo->prepare("SELECT * FROM admins WHERE admin_email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();
        
        if ($user && password_verify($password, $user['admin_password'])) {
            $_SESSION['user_id'] = $user['admin_id'];
            $_SESSION['user_name'] = $user['admin_name'];
            $_SESSION['user_email'] = $user['admin_email'];
            $_SESSION['user_type'] = 'admin';
            $_SESSION['user_major'] = $user['managed_major'];
            header("Location: admin/dashboard.php");
            exit();
        } else {
            $error = "Invalid email or password";
        }
    } else {
        $error = "Please select user type";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - YIC</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <header class="header-fixed">
        <div class="logo">🏫 YIC Course Registration System</div>
    </header>

    <main class="container-center">
        <section class="card login-card">
            <h1>Welcome Back</h1>
            <p class="subtitle">Login to access your courses</p>

            <?php if ($error): ?>
                <div class="error-message"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>

            <form method="POST" action="" id="loginForm">
                <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
                
                <div class="form-group">
                    <label>Select User Type:</label>
                    <div class="radio-group">
                        <label class="radio">
                            <input type="radio" name="userType" value="student" checked> 👨‍🎓 Student
                        </label>
                        <label class="radio">
                            <input type="radio" name="userType" value="admin"> 👨‍💼 Admin
                        </label>
                    </div>
                </div>

                <div class="form-group">
                    <label for="email">Email Address:</label>
                    <input type="email" name="email" id="email" value="soha@yic.edu" placeholder="Enter your email" required>
                </div>

                <div class="form-group">
                    <label for="password">Password:</label>
                    <input type="password" name="password" id="password" placeholder="Enter your password" required>
                </div>

                <div class="form-group checkbox-group">
                    <label class="checkbox-inline">
                        <input type="checkbox" id="showPassword"> 👁️ Show Password
                    </label>
                </div>

                <button type="submit" class="btn-primary">Login</button>
            </form>

            <div class="register-link">
                <p>Don't have an account? <a href="register.php">Register here</a></p>
            </div>
        </section>
    </main>

    <footer class="footer-fixed">
        <p>&copy; 2026 Yanbu Industrial College - Course Registration System</p>
    </footer>

    <script src="js/script.js"></script>
</body>
</html>