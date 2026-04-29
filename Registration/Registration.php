<?php

error_reporting(E_ALL);
ini_set('display_errors', 1);

// Set content type to JSON
header('Content-Type: application/json');

// --- THE BIG CHANGE ---
// In your screenshot, db.php is in the root folder.
// Since this file is inside the "Registration" folder, you need to go up one level.
include '../db.php'; 

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $first_name = $_POST['first_name'] ?? '';
    $middle_name = $_POST['middle_name'] ?? '';
    $last_name = $_POST['last_name'] ?? '';
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';

    // VALIDATION
    if (empty($first_name) || empty($last_name) || empty($email) || empty($password)) {
        echo json_encode(['success' => false, 'message' => 'Please fill in all required fields!']);
        exit();
    }

    // CHECK PASSWORD MATCH
    if ($password !== $confirm_password) {
        echo json_encode(['success' => false, 'message' => 'Passwords do not match!']);
        exit();
    }

    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

    // CHECK IF EMAIL EXISTS
    $check = $conn->prepare("SELECT id FROM users_table WHERE email = ?");
    $check->bind_param("s", $email);
    $check->execute();
    $check->store_result();

    if ($check->num_rows > 0) {
        echo json_encode(['success' => false, 'message' => 'Email already exists!']);
        $check->close();
        exit();
    }
    $check->close(); // Moved this up to avoid closing it twice

    // INSERT NEW USER
    $stmt = $conn->prepare("INSERT INTO users_table (first_name, middle_name, last_name, email, password) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("sssss", $first_name, $middle_name, $last_name, $email, $hashedPassword);

    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Registration successful!']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Database Error: ' . $stmt->error]);
    }

    $stmt->close();
}

$conn->close();
?>