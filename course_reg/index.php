<?php
require_once 'config.php';

if (isLoggedIn()) {
    if (isAdmin()) {
        header('Location: admin/dashboard.php');
    } else {
        header('Location: student/dashboard.php');
    }
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';
    $userType = $_POST['userType'] ?? 'student';
    
    if ($userType == 'student') {
        $stmt = $pdo->prepare("SELECT * FROM students WHERE student_email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();
        
        if ($user && password_verify($password, $user['student_password'])) {
            session_regenerate_id(true);
            $_SESSION['student_id'] = $user['student_id'];
            $_SESSION['full_name'] = $user['student_name'];
            $_SESSION['email'] = $user['student_email'];
            $_SESSION['major'] = $user['student_major'];
            $_SESSION['year_level'] = $user['student_year_level'];
            header('Location: student/dashboard.php');
            exit;
        } else {
            $error = 'Invalid email or password';
        }
    } else {
        $stmt = $pdo->prepare("SELECT * FROM admins WHERE admin_email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();
        
        if ($user && password_verify($password, $user['admin_password'])) {
            session_regenerate_id(true);
            $_SESSION['admin_id'] = $user['admin_id'];
            $_SESSION['full_name'] = $user['admin_name'];
            $_SESSION['email'] = $user['admin_email'];
            $_SESSION['admin_major'] = $user['admin_managed_major'];
            header('Location: admin/dashboard.php');
            exit;
        } else {
            $error = 'Invalid email or password';
        }
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
        <div class="logo">YIC Course Registration System</div>
    </header>

    <main class="container-center">
        <section class="card login-card">
            <h1>Welcome Back</h1>
            <p class="subtitle">Login to access your courses</p>

            <?php if($error): ?>
                <div class="alert-error"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>

            <form method="POST">
                <div class="form-group">
                    <label>Select User Type:</label>
                    <div class="radio-group">
                        <label class="radio"><input type="radio" name="userType" value="student" checked> Student</label>
                        <label class="radio"><input type="radio" name="userType" value="admin"> Admin</label>
                    </div>
                </div>

                <div class="form-group">
                    <label for="email">Email Address:</label>
                    <input type="email" id="email" name="email" placeholder="Enter your email" required>
                </div>

                <div class="form-group">
                    <label for="password">Password:</label>
                    <input type="password" id="password" name="password" placeholder="Enter your password" required>
                </div>

                <div class="form-group checkbox-group">
                    <label class="checkbox-inline"><input type="checkbox" id="showPassword"> Show Password</label>
                </div>

                <button type="submit" class="btn-primary">Login</button>
            </form>

        </section>
    </main>

    <footer class="footer-fixed">
        <p>&copy; 2026 Yanbu Industrial College - Course Registration System</p>
    </footer>

    <script>
        document.getElementById('showPassword')?.addEventListener('change', function() {
            let pwd = document.getElementById('password');
            pwd.type = this.checked ? 'text' : 'password';
        });
    </script>
    <style>
        .alert-error { background: #f8d7da; color: #721c24; padding: 12px; border-radius: 8px; margin-bottom: 20px; }
        
    </style>
</body>
</html>