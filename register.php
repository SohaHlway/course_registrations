<?php
require_once 'includes/config.php';

if (isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    validateCSRF($_POST['csrf_token'] ?? '');
    
    $student_id = (int)$_POST['student_id'];
    $student_name = sanitize($_POST['full_name']);
    $student_email = filter_var($_POST['email'], FILTER_VALIDATE_EMAIL);
    $student_major = $_POST['major'];
    $year_level = (int)$_POST['year_level'];
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    
    $errors = [];
    if ($student_id < 100000000 || $student_id > 999999999) {
        $errors[] = "Student ID must be 9 digits";
    }
    if (empty($student_name)) $errors[] = "Full name is required";
    if (!$student_email) $errors[] = "Valid email is required";
    if (!in_array($student_major, ['SC', 'SE', 'MIS', 'HR'])) $errors[] = "Invalid major";
    if ($year_level < 1 || $year_level > 5) $errors[] = "Year level must be 1-5";
    if (strlen($password) < 6) $errors[] = "Password must be at least 6 characters";
    if ($password !== $confirm_password) $errors[] = "Passwords do not match";
    
    $stmt = $pdo->prepare("SELECT student_id FROM students WHERE student_id = ? OR student_email = ?");
    $stmt->execute([$student_id, $student_email]);
    if ($stmt->fetch()) $errors[] = "Student ID or Email already exists";
    
    if (empty($errors)) {
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("INSERT INTO students (student_id, student_name, student_email, student_password, student_major, year_level) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->execute([$student_id, $student_name, $student_email, $hashed_password, $student_major, $year_level]);
        $success = "Registration successful! You can now login.";
    } else {
        $error = implode("<br>", $errors);
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
        <div class="logo">🏫 YIC Course Registration System</div>
    </header>

    <main class="container-center">
        <section class="card register-card">
            <h1>Create New Account</h1>
            <p class="subtitle">Register as a new student</p>

            <?php if ($error): ?>
                <div class="error-message"><?= $error ?></div>
            <?php endif; ?>
            <?php if ($success): ?>
                <div class="success-message"><?= $success ?> <a href="index.php">Login here</a></div>
            <?php endif; ?>

            <?php if (!$success): ?>
            <form method="POST" action="" id="registerForm">
                <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
                
                <div class="form-group">
                    <label for="student_id">Student ID:</label>
                    <input type="number" name="student_id" id="student_id" placeholder="441500338" required>
                </div>

                <div class="form-group">
                    <label for="full_name">Full Name:</label>
                    <input type="text" name="full_name" id="full_name" placeholder="Enter your full name" required>
                </div>

                <div class="form-group">
                    <label for="email">Email Address:</label>
                    <input type="email" name="email" id="email" placeholder="student@yic.edu" required>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="major">Major:</label>
                        <select name="major" id="major" required>
                            <option value="">Select Major</option>
                            <option value="SC">💻 SC - Computer Science</option>
                            <option value="SE">🖥️ SE - Software Engineering</option>
                            <option value="MIS">📊 MIS - Management Information Systems</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="year_level">Year Level:</label>
                        <select name="year_level" id="year_level" required>
                            <option value="">Select Level</option>
                            <option value="1">📚 Level 1 - Freshman</option>
                            <option value="2">📖 Level 2 - Sophomore</option>
                            <option value="3">📘 Level 3 - Junior</option>
                            <option value="4">🎓 Level 4 - Senior</option>
                        </select>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="password">Password:</label>
                        <input type="password" name="password" id="password" placeholder="Min 6 characters" required>
                    </div>

                    <div class="form-group">
                        <label for="confirm_password">Confirm Password:</label>
                        <input type="password" name="confirm_password" id="confirm_password" placeholder="Re-enter password" required>
                    </div>
                </div>

                <div class="form-group checkbox-group">
                    <label class="checkbox-inline">
                        <input type="checkbox" id="showPasswordReg"> 👁️ Show Password
                    </label>
                </div>

                <div class="form-group checkbox-group">
                    <label class="checkbox-inline">
                        <input type="checkbox" name="terms" id="terms" required> ✅ I agree to the Terms and Conditions
                    </label>
                </div>

                <button type="submit" class="btn-primary">Register</button>
            </form>
            <?php endif; ?>

            <div class="login-link">
                <p>Already have an account? <a href="index.php">Login here</a></p>
            </div>
        </section>
    </main>

    <footer class="footer-fixed">
        <p>&copy; 2026 Yanbu Industrial College - Course Registration System</p>
    </footer>

    <script src="js/script.js"></script>
</body>
</html>