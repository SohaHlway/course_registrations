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
    
    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ? AND user_type = ?");
    $stmt->execute([$email, $userType]);
    $user = $stmt->fetch();
    
    if ($user && password_verify($password, $user['password_hash'])) {
        $_SESSION['user_id'] = $user['user_id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['email'] = $user['email'];
        $_SESSION['full_name'] = $user['full_name'];
        $_SESSION['user_type'] = $user['user_type'];
        $_SESSION['major'] = $user['major'];
        $_SESSION['year_level'] = $user['year_level'];
        $_SESSION['admin_major'] = $user['admin_major'];
        
        if ($user['user_type'] == 'admin') {
            header('Location: admin/dashboard.php');
        } else {
            header('Location: student/dashboard.php');
        }
        exit;
    } else {
        $error = 'Invalid email or password';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - YIC Course Registration</title>
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
                <div class="alert-error"><?php echo $error; ?></div>
            <?php endif; ?>

            <form method="POST">
                <div class="form-group">
                    <label>Select User Type:</label>
                    <div class="radio-group">
                        <label class="radio">
                            <input type="radio" name="userType" value="student" checked> Student
                        </label>
                        <label class="radio">
                            <input type="radio" name="userType" value="admin"> Admin
                        </label>
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
                    <label class="checkbox-inline">
                        <input type="checkbox" id="showPassword"> Show Password
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

    <script>
        document.getElementById('showPassword')?.addEventListener('change', function() {
            const pwd = document.getElementById('password');
            pwd.type = this.checked ? 'text' : 'password';
        });
    </script>
    <style>
        .alert-error { background: #f8d7da; color: #721c24; padding: 12px; border-radius: 8px; margin-bottom: 20px; }
    </style>
</body>
</html>