<?php
require_once '../config.php';
redirectIfNotAdmin();

$admin_major = $_SESSION['admin_major'];
$search = $_GET['search'] ?? '';
$level = $_GET['level'] ?? '';

$sql = "SELECT * FROM students WHERE student_major = ?";
$params = [$admin_major];

if ($search) {
    $sql .= " AND (student_name LIKE ? OR student_email LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
}
if ($level) {
    $sql .= " AND student_year_level = ?";
    $params[] = $level;
}
$sql .= " ORDER BY student_name";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$students = $stmt->fetchAll();

$levels = $pdo->prepare("SELECT DISTINCT student_year_level FROM students WHERE student_major = ? ORDER BY student_year_level");
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
            <h2>Welcome, <?php echo htmlspecialchars($_SESSION['full_name']); ?></h2>
            <p>Managing students in <?php echo $admin_major; ?> Department</p>
        </section>
     
        <div class="card">
            <h2>Filter Students</h2>
            <form method="GET" class="filter-form" style="display: flex; gap: 1rem; flex-wrap: wrap;">
                <select name="level">
                    <option value="">All Levels</option>
                    <?php foreach($levels as $l): ?>
                    <option value="<?php echo $l['student_year_level']; ?>" <?php echo $level == $l['student_year_level'] ? 'selected' : ''; ?>>Level <?php echo $l['student_year_level']; ?></option>
                    <?php endforeach; ?>
                </select>
                <input type="text" name="search" placeholder="Search by Student Name" value="<?php echo htmlspecialchars($search); ?>">
                <button type="submit" class="btn-secondary">Filter</button>
                <a href="dashboard.php" class="btn-secondary">Reset</a>
            </form>
        </div>

        <div class="card">
            <h2>Students in <?php echo $admin_major; ?> Department</h2>
            <div class="table-responsive">
                <table class="data-table">
                    <thead><tr><th>Student ID</th><th>Name</th><th>Email</th><th>Major</th><th>Level</th><th>Action</th></tr></thead>
                    <tbody>
                        <?php foreach($students as $student): ?>
                        <tr>
                            <td><?php echo $student['student_id']; ?></td>
                            <td><?php echo htmlspecialchars($student['student_name']); ?></td>
                            <td><?php echo htmlspecialchars($student['student_email']); ?></td>
                            <td><?php echo $student['student_major']; ?></td>
                            <td>Level <?php echo $student['student_year_level']; ?></td>
                            <td><a href="student_detail.php?id=<?php echo $student['student_id']; ?>" class="btn-view">Manage</a></td>
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