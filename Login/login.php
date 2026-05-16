<?php
session_start();
include '../db.php'; // Adjust path if needed

header('Content-Type: application/json; charset=utf-8');

$response = [
    "success" => false,
    "message" => "",
    "redirect" => "Dashboard/index.php"
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($email === '' || $password === '') {
        $response["message"] = "Email and password are required.";
        echo json_encode($response);
        exit();
    }

    // Use prepared statements to prevent SQL injection
    $stmt = $conn->prepare("SELECT id, password FROM users_table WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($user = $result->fetch_assoc()) {
        // If using hashed passwords (recommended):
        if (password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $response["success"] = true;
        }
        // If using plain text (for testing only):
        else if ($password === $user['password']) {
            $_SESSION['user_id'] = $user['id'];
            $response["success"] = true;
        } else {
            $response["message"] = "Incorrect password.";
        }
    } else {
        $response["message"] = "Email not found.";
    }
}

echo json_encode($response);
exit();