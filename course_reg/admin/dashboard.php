<?php
require_once '../config.php';
redirectIfNotAdmin();

$admin_major = $_SESSION['admin_major'];
$search = $_GET['search'] ?? '';
$level = $_GET['level'] ?? '';

$sql = "SELECT * FROM users WHERE user_type = 'student' AND major = ?";
$params = [$admin_major];

if ($search) {
    $sql .= " AND (username LIKE ? OR full_name LIKE ? OR email LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
    $params[] = "%$search%";
}
if ($level) {
    $sql .= " AND year_level = ?";
    $params[] = $level;
}
$sql .= " ORDER BY username";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$students = $stmt->fetchAll();

$levels = $pdo->prepare("SELECT DISTINCT year_level FROM users WHERE user_type = 'student' AND major = ? ORDER BY year_level");
$levels->execute([$admin_major]);
$levels = $levels->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - YIC</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
    <header class="header-fixed">
        <div class="logo">YIC Admin Panel</div>
        <div class="header-buttons">
            <a href="profile.php" class="back-btn-header">My Profile</a>
            <a href="../logout.php" class="logout-btn-header">Logout</a>
        </div>
    </header>

    <main class="container">
        <section class="welcome-card">
            <h2>Welcome, <?php echo $_SESSION['full_name']; ?></h2>
            <p>Managing students in <?php echo $admin_major; ?> Department</p>
        </section>
     
        <div class="card">
            <h2>Filter Students</h2>
            <div class="filter-form">
                <form method="GET" style="display: flex; gap: 1rem; flex-wrap: wrap; width: 100%;">
                    <select name="level">
                        <option value="">All Levels</option>
                        <?php foreach($levels as $l): ?>
                        <option value="<?php echo $l['year_level']; ?>" <?php echo $level == $l['year_level'] ? 'selected' : ''; ?>>Level <?php echo $l['year_level']; ?></option>
                        <?php endforeach; ?>
                    </select>
                    <input type="text" name="search" placeholder="Search by Student ID or Name" value="<?php echo htmlspecialchars($search); ?>">
                    <button type="submit" class="btn-secondary">Filter</button>
                    <a href="dashboard.php" class="btn-secondary">Reset</a>
                </form>
            </div>
        </div>

        <div class="card">
            <h2>Students in <?php echo $admin_major; ?> Department</h2>
            <div class="table-responsive">
                <table class="data-table">
                    <thead><tr><th>Student ID</th><th>Name</th><th>Email</th><th>Major</th><th>Level</th><th>Action</th></tr></thead>
                    <tbody>
                        <?php foreach($students as $student): ?>
                        <tr>
                            <td><?php echo $student['username']; ?></td>
                            <td><?php echo $student['full_name']; ?></td>
                            <td><?php echo $student['email']; ?></td>
                            <td><?php echo $student['major']; ?></td>
                            <td>Level <?php echo $student['year_level']; ?></td>
                            <td><a href="student_detail.php?id=<?php echo $student['user_id']; ?>" class="btn-view">Manage</a></td>
                        </tr>
                        <?php endforeach; ?>
                        <?php if(empty($students)): ?>
                        <tr><td colspan="6" style="text-align:center">No students found</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </main>

    <footer class="footer-fixed">
        <p>&copy; 2026 Yanbu Industrial College - Course Registration System</p>
    </footer>
</body>
</html>