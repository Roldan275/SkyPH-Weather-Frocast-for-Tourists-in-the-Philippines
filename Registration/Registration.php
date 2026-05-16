<?php
/**
 * SECTION 1: PHP LOGIC
 * This must be at the VERY top. NO spaces or lines above <?php
 */
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // 1. Clear any accidental output
    if (ob_get_length()) ob_clean();
    
    // 2. Set headers
    header('Content-Type: application/json');
    error_reporting(E_ALL);
    ini_set('display_errors', 0); // Don't let PHP errors break the JSON string

    try {
        // --- CHECK THIS PATH ---
        // If your db.php is in the SAME folder, use: include 'db.php';
        // If it is one folder up, use: include '../db.php';
        if (!file_exists('../db.php')) {
            throw new Exception("Database connection file (db.php) not found.");
        }
        include '../db.php';

        $first_name = trim($_POST['first_name'] ?? '');
        $middle_name = trim($_POST['middle_name'] ?? '');
        $last_name = trim($_POST['last_name'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        $confirm_password = $_POST['confirm_password'] ?? '';

        if (empty($first_name) || empty($last_name) || empty($email) || empty($password)) {
            echo json_encode(['success' => false, 'message' => 'Please fill in all fields.']);
        } 
        elseif ($password !== $confirm_password) {
            echo json_encode(['success' => false, 'message' => 'Passwords do not match.']);
        } 
        else {
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

            // Check if email exists
            $check = $conn->prepare("SELECT id FROM users_table WHERE email = ?");
            $check->bind_param("s", $email);
            $check->execute();
            $check->store_result();

            if ($check->num_rows > 0) {
                echo json_encode(['success' => false, 'message' => 'Email is already registered.']);
            } else {
                $stmt = $conn->prepare("INSERT INTO users_table (first_name, middle_name, last_name, email, password) VALUES (?, ?, ?, ?, ?)");
                $stmt->bind_param("sssss", $first_name, $middle_name, $last_name, $email, $hashedPassword);

                if ($stmt->execute()) {
                    echo json_encode(['success' => true, 'message' => 'Registration successful!']);
                } else {
                    echo json_encode(['success' => false, 'message' => 'Database Error: ' . $stmt->error]);
                }
                $stmt->close();
            }
            $check->close();
        }
        $conn->close();

    } catch (Exception $e) {
        // If the code crashes, send the error as JSON so JavaScript can read it
        echo json_encode(['success' => false, 'message' => 'System Error: ' . $e->getMessage()]);
    }
    
    // Stop the script so no HTML is sent
    exit(); 
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sky-PH | Register</title>
    <style>
        * { box-sizing: border-box; font-family: 'Segoe UI', Arial, sans-serif; }
        body {
            min-height: 100vh; margin: 0; display: flex; justify-content: center; align-items: center;
            background: url('https://images.unsplash.com/photo-1507525428034-b723cf961d3e') no-repeat center/cover;
        }
        .overlay { position: absolute; inset: 0; background: rgba(0,0,0,0.5); backdrop-filter: blur(8px); z-index: 1; }
        .login-container {
            position: relative; z-index: 2; background: rgba(255, 255, 255, 0.15);
            backdrop-filter: blur(20px); border: 1px solid rgba(255, 255, 255, 0.3);
            padding: 35px; border-radius: 20px; width: 420px; text-align: center; color: white;
        }
        .close-btn { position: absolute; top: 15px; right: 15px; background: none; border: none; color: white; font-size: 20px; cursor: pointer; }
        h2 { margin-bottom: 25px; color: #fff; }
        .input-group { margin-bottom: 15px; text-align: left; }
        .input-group label { display: block; font-size: 13px; margin-bottom: 5px; }
        .input-group input { width: 100%; padding: 12px; border-radius: 10px; border: none; outline: none; background: #fff; color: #333; }
        .login-btn { width: 100%; padding: 14px; border: none; border-radius: 10px; background: #2a7be4; color: white; font-weight: bold; cursor: pointer; margin-top: 10px; }
        .signup { margin-top: 20px; font-size: 14px; }
        .signup a { color: #82b1ff; text-decoration: none; font-weight: bold; }
    </style>
</head>
<body>

<div class="overlay"></div>

<div class="login-container">
    <button class="close-btn" onclick="window.history.back()">&times;</button>
    <h2>REGISTER</h2>

    <form id="registerForm">
        <div class="input-group">
            <label>First Name</label>
            <input type="text" name="first_name" required>
        </div>
        <div class="input-group">
            <label>Middle Name</label>
            <input type="text" name="middle_name">
        </div>
        <div class="input-group">
            <label>Last Name</label>
            <input type="text" name="last_name" required>
        </div>
        <div class="input-group">
            <label>Email</label>
            <input type="email" name="email" required>
        </div>
        <div class="input-group">
            <label>Password</label>
            <input type="password" name="password" required>
        </div>
        <div class="input-group">
            <label>Confirm Password</label>
            <input type="password" name="confirm_password" required>
        </div>
        <button type="submit" class="login-btn">Sign Up</button>
    </form>

    <p class="signup">
        Already have an Account? <a href="../Login WeatherForcast/login.html">Login</a>
    </p>
</div>

<script>
    document.getElementById('registerForm').addEventListener('submit', function(event) {
        event.preventDefault();
        
        const btn = this.querySelector('button');
        btn.disabled = true;
        btn.innerText = 'Registering...';

        fetch('', { // Sending to the same file
            method: 'POST',
            body: new FormData(this)
        })
        .then(response => {
            // If the response is empty or broken, this will catch it
            return response.text().then(text => {
                try {
                    return JSON.parse(text);
                } catch (e) {
                    throw new Error("The server sent back an invalid response: " + text);
                }
            });
        })
        .then(data => {
            if (data.success) {
                alert(data.message);
                window.location.href = '../Login WeatherForcast/login.html';
            } else {
                alert(data.message);
                btn.disabled = false;
                btn.innerText = 'Sign Up';
            }
        })
        .catch(error => {
            console.error(error);
            alert("Error: " + error.message);
            btn.disabled = false;
            btn.innerText = 'Sign Up';
        });
    });
</script>
</body>
</html>