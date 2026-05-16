<?php
session_start();
header('Content-Type: application/json');

// Database connection (Adjust with your actual credentials)
$host = 'localhost';
$db   = 'sky-ph';
$user = 'root';
$pass = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$db", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $email = $_POST['email'] ?? '';
        $password = $_POST['password'] ?? '';

        // 1. Fetch user from database
        $stmt = $pdo->prepare("SELECT id, password FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        // 2. Verify password
        if ($user && password_verify($password, $user['password'])) {
            // Success! Set session variables
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['email'] = $email;

            echo json_encode(['success' => true, 'message' => 'Login successful!']);
        } else {
            // Failure
            echo json_encode(['success' => false, 'message' => 'Invalid email or password.']);
        }
    }
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}
?>