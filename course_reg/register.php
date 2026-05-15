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
$success = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $studentId = $_POST['studentId'] ?? '';
    $fullName = $_POST['fullName'] ?? '';
    $email = $_POST['email'] ?? '';
    $major = $_POST['major'] ?? '';
    $yearLevel = $_POST['yearLevel'] ?? '';
    $password = $_POST['password'] ?? '';
    $confirmPassword = $_POST['confirmPassword'] ?? '';
    
    if ($password !== $confirmPassword) {
        $error = 'Passwords do not match';
    } elseif (strlen($password) < 6) {
        $error = 'Password must be at least 6 characters';
    } else {
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE email = ?");
        $stmt->execute([$email]);
        if ($stmt->fetchColumn() > 0) {
            $error = 'Email already registered';
        } else {
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("INSERT INTO users (username, email, password_hash, full_name, user_type, major, year_level) VALUES (?, ?, ?, ?, 'student', ?, ?)");
            
            if ($stmt->execute([$studentId, $email, $hashedPassword, $fullName, $major, $yearLevel])) {
                $success = 'Registration successful! Redirecting to login...';
                header('refresh:2;url=index.php');
            } else {
                $error = 'Registration failed';
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - YIC</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <header class="header-fixed">
        <div class="logo">YIC Course Registration System</div>
    </header>

    <main class="container-center">
        <section class="card register-card">
            <h1>Create New Account</h1>
            <p class="subtitle">Register as a new student</p>

            <?php if($error): ?>
                <div class="alert-error"><?php echo $error; ?></div>
            <?php endif; ?>
            <?php if($success): ?>
                <div class="alert-success"><?php echo $success; ?></div>
            <?php endif; ?>

            <form method="POST">
                <div class="form-group">
                    <label for="studentId">Student ID:</label>
                    <input type="text" id="studentId" name="studentId" placeholder="e.g., 441500338" required>
                </div>

                <div class="form-group">
                    <label for="fullName">Full Name:</label>
                    <input type="text" id="fullName" name="fullName" placeholder="Enter your full name" required>
                </div>

                <div class="form-group">
                    <label for="email">Email Address:</label>
                    <input type="email" id="email" name="email" placeholder="student@yic.edu" required>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="major">Major:</label>
                        <select id="major" name="major" required>
                            <option value="">Select Major</option>
                            <option value="SC">SC - Computer Science</option>
                            <option value="SE">SE - Software Engineering</option>
                            <option value="MIS">MIS - Management Information Systems</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="yearLevel">Year Level:</label>
                        <select id="yearLevel" name="yearLevel" required>
                            <option value="">Select Level</option>
                            <option value="1">Level 1 - Freshman</option>
                            <option value="2">Level 2 - Sophomore</option>
                            <option value="3">Level 3 - Junior</option>
                            <option value="4">Level 4 - Senior</option>
                        </select>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="password">Password:</label>
                        <input type="password" id="password" name="password" placeholder="Min 6 characters" required>
                    </div>

                    <div class="form-group">
                        <label for="confirmPassword">Confirm Password:</label>
                        <input type="password" id="confirmPassword" name="confirmPassword" placeholder="Re-enter password" required>
                    </div>
                </div>

                <div class="form-group checkbox-group">
                    <label class="checkbox-inline">
                        <input type="checkbox" id="showPasswordReg"> Show Password
                    </label>
                </div>

                <div class="form-group checkbox-group">
                    <label class="checkbox-inline">
                        <input type="checkbox" id="terms" required> I agree to the Terms and Conditions
                    </label>
                </div>

                <button type="submit" class="btn-primary">Register</button>
            </form>

            <div class="login-link">
                <p>Already have an account? <a href="index.php">Login here</a></p>
            </div>
        </section>
    </main>

    <footer class="footer-fixed">
        <p>&copy; 2026 Yanbu Industrial College - Course Registration System</p>
    </footer>

    <script>
        document.getElementById('showPasswordReg')?.addEventListener('change', function() {
            const pwd = document.getElementById('password');
            const confirm = document.getElementById('confirmPassword');
            const type = this.checked ? 'text' : 'password';
            pwd.type = type;
            confirm.type = type;
        });
    </script>
    <style>
        .alert-error { background: #f8d7da; color: #721c24; padding: 12px; border-radius: 8px; margin-bottom: 20px; }
        .alert-success { background: #d4edda; color: #155724; padding: 12px; border-radius: 8px; margin-bottom: 20px; }
    </style>
</body>
</html>