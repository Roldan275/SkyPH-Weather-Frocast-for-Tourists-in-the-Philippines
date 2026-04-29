<?php

error_reporting(E_ALL);
ini_set('display_errors', 1);

// DATABASE CONNECTION
// Fix: Pointing to the root folder where your db.php actually sits
include '../db.php';

// GET FORM DATA
$email = $_POST['email'] ?? '';
$password = $_POST['password'] ?? '';

// VALIDATION
if (empty($email) || empty($password)) {
    echo "Please enter email and password!";
    exit();
}

// CHECK IF EMAIL EXISTS
$stmt = $conn->prepare("SELECT password FROM users_table WHERE email = ?");
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows == 0) {
    echo "Invalid email or password!";
    exit();
}

$row = $result->fetch_assoc();
$hashed_password = $row['password'];

// VERIFY PASSWORD
if (password_verify($password, $hashed_password)) {
    // LOGIN SUCCESSFUL
    // Note: Ensure the 'Dashboard' folder exists and contains 'index.php'
    header("Location: ../Dashboard/index.php");
    exit();
} else {
    echo "Invalid email or password!";
}

$stmt->close();
$conn->close();
?>