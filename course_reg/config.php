<?php
session_start();

$host = 'localhost';
$dbname = 'yic_course_registration';
$username = 'root';
$password = '1324';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch(PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}

function isLoggedIn() {
    return isset($_SESSION['student_id']) || isset($_SESSION['admin_id']);
}

function isAdmin() {
    return isset($_SESSION['admin_id']);
}

function isStudent() {
    return isset($_SESSION['student_id']);
}

function redirectIfNotLoggedIn() {
    if (!isLoggedIn()) {
        header('Location: /course_reg/index.php');
        exit;
    }
}

function redirectIfNotAdmin() {
    if (!isAdmin()) {
        header('Location: /course_reg/index.php');
        exit;
    }
}

function redirectIfNotStudent() {
    if (!isStudent()) {
        header('Location: /course_reg/index.php');
        exit;
    }
}
?>