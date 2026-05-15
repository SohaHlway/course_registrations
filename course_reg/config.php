<?php
session_start();

$host = 'localhost';
$dbname = 'yic_course_registration';
$username = 'root';
$password = '1324';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}

function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

function isAdmin() {
    return isset($_SESSION['user_type']) && $_SESSION['user_type'] == 'admin';
}

function redirectIfNotLoggedIn() {
    if (!isLoggedIn()) {
        header('Location: ../index.php');
        exit;
    }
}

function redirectIfNotAdmin() {
    if (!isLoggedIn() || !isAdmin()) {
        header('Location: ../index.php');
        exit;
    }
}
?>