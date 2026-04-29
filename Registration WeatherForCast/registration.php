```php
<?php

error_reporting(E_ALL);
ini_set('display_errors', 1);

// DATABASE CONNECTION (UPDATED DATABASE NAME)
$conn = new mysqli("localhost", "root", "", "sky-ph");

// CHECK CONNECTION
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// GET FORM DATA
$first_name = $_POST['first_name'] ?? '';
$middle_name = $_POST['middle_name'] ?? '';
$last_name = $_POST['last_name'] ?? '';
$email = $_POST['email'] ?? '';
$password = $_POST['password'] ?? '';
$confirm_password = $_POST['confirm_password'] ?? '';

// VALIDATION
if (empty($first_name) || empty($last_name) || empty($email) || empty($password)) {
    echo "Please fill in all required fields!";
    exit();
}

// CHECK PASSWORD MATCH
if ($password !== $confirm_password) {
    echo "Passwords do not match!";
    exit();
}

// CHECK IF EMAIL ALREADY EXISTS
$stmt = $conn->prepare("SELECT id FROM users_table WHERE email = ?");
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    echo "Email already registered!";
    exit();
}
$stmt->close();

// HASH PASSWORD
$hashed_password = password_hash($password, PASSWORD_DEFAULT);

// INSERT USER
$stmt = $conn->prepare("INSERT INTO users_table (first_name, middle_name, last_name, email, password) VALUES (?, ?, ?, ?, ?)");
$stmt->bind_param("sssss", $first_name, $middle_name, $last_name, $email, $hashed_password);

if ($stmt->execute()) {
    header("Location: ../Login WeatherForcast/login.html");
    exit();
} else {
    echo "Error: " . $stmt->error;
}

$stmt->close();
$conn->close();
?>
```
