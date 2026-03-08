<?php
session_start();
require_once 'config/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);

    if (empty($username) || empty($password)) {
        $_SESSION['error_message'] = "Please enter both username and password.";
        header("Location: index.php");
        exit();
    }

    try {
        $stmt = $pdo->prepare("SELECT id, username, password, role, full_name FROM users WHERE username = :username");
        $stmt->bindParam(':username', $username, PDO::PARAM_STR);
        $stmt->execute();
        
        $user = $stmt->fetch();

        // Check if user exists and password is correct
        if ($user && password_verify($password, $user['password'])) {
            // Password is correct! Start a session.
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['role'] = $user['role'];
            $_SESSION['full_name'] = $user['full_name'];
            
            // Redirect to dashboard
            header("Location: dashboard.php");
            exit();
        } else {
            // Incorrect username or password
            $_SESSION['error_message'] = "Invalid username or password.";
            header("Location: index.php");
            exit();
        }
    } catch(PDOException $e) {
        $_SESSION['error_message'] = "System Error. Please try again later.";
        error_log("Login Error: " . $e->getMessage()); // Log it to backend instead of showing to user
        header("Location: index.php");
        exit();
    }
} else {
    // If someone tries to access this page directly without POST, send them back to login
    header("Location: index.php");
    exit();
}
?>
