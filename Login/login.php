<?php
session_start();
include '../db.php'; // Path to your database connection

// --- LOGIN LOGIC ---
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $password = $_POST['password'];

    // Search for user
    $sql = "SELECT * FROM users_table WHERE email = '$email'";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();
        
        // Verify password
        // Change to: if ($password === $user['password']) if not using password_hash
        if (password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_name'] = $user['first_name'];

            // Redirect to Dashboard
            header("Location: ../Dashboard/index.php");
            exit();
        } else {
            echo "<script>alert('Incorrect password!');</script>";
        }
    } else {
        echo "<script>alert('No account found with that email.');</script>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sky-PH | Login</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        /* RESET & BASE */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        body {
            height: 100vh;
            background: url('https://images.unsplash.com/photo-1507525428034-b723cf961d3e') no-repeat center/cover;
            display: flex;
            justify-content: center;
            align-items: center;
            position: relative;
        }

        /* GLASSMORPHISM OVERLAY */
        .overlay {
            position: absolute;
            width: 100%;
            height: 100%;
            backdrop-filter: blur(8px);
            background: rgba(0, 0, 0, 0.3);
            z-index: 1;
        }

        .login-container {
            position: relative;
            background: rgba(255, 255, 255, 0.15); /* Translucent */
            backdrop-filter: blur(15px);
            padding: 40px;
            border-radius: 20px;
            width: 380px;
            text-align: center;
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.2);
            border: 1px solid rgba(255, 255, 255, 0.2);
            z-index: 2;
            color: white;
        }

        .close-btn {
            position: absolute;
            top: 15px;
            right: 15px;
            background: none;
            border: none;
            color: white;
            font-size: 24px;
            cursor: pointer;
            transition: 0.3s;
        }

        .close-btn:hover {
            color: #3498db;
            transform: rotate(90deg);
        }

        h2 {
            margin-bottom: 25px;
            letter-spacing: 3px;
            font-weight: 600;
        }

        .input-group {
            margin-bottom: 20px;
            text-align: left;
        }

        .input-group label {
            font-size: 13px;
            color: rgba(255, 255, 255, 0.8);
            margin-bottom: 5px;
            display: block;
        }

        .input-group input {
            width: 100%;
            padding: 12px;
            border: none;
            border-radius: 8px;
            background: rgba(255, 255, 255, 0.9);
            outline: none;
            color: #333;
            transition: 0.3s;
        }

        .input-group input:focus {
            box-shadow: 0 0 10px rgba(52, 152, 219, 0.5);
        }

        .options {
            display: flex;
            justify-content: space-between;
            font-size: 12px;
            margin-bottom: 20px;
        }

        .options a {
            text-decoration: none;
            color: #3498db;
            transition: 0.3s;
        }

        .options a:hover {
            color: #5dade2;
        }

        .login-btn {
            width: 100%;
            padding: 12px;
            border: none;
            border-radius: 30px;
            background: #3498db;
            color: white;
            font-size: 16px;
            font-weight: bold;
            cursor: pointer;
            transition: 0.3s;
            box-shadow: 0 5px 15px rgba(52, 152, 219, 0.3);
        }

        .login-btn:hover {
            background: #2980b9;
            transform: translateY(-2px);
        }

        .signup {
            margin-top: 20px;
            font-size: 13px;
        }

        .signup a {
            color: #3498db;
            text-decoration: none;
            font-weight: bold;
        }
        
        .signup a:hover {
            text-decoration: underline;
        }
    </style>
</head>

<body>

<div class="overlay"></div>

<div class="login-container">
    <button class="close-btn" onclick="window.location.href='../HomePage.html'">&times;</button>
    <h2>LOGIN</h2>

    <form id="loginForm" action="login.php" method="POST">
        <div class="input-group">
            <label><i class="fa-solid fa-envelope"></i> Email</label>
            <input type="email" name="email" placeholder="Enter your email" required>
        </div>

        <div class="input-group">
            <label><i class="fa-solid fa-lock"></i> Password</label>
            <input type="password" name="password" placeholder="Enter your password" required>
        </div>

        <div class="options">
            <label><input type="checkbox"> Remember me</label>
            <a href="#">Forgot password?</a>
        </div>

        <button type="submit" class="login-btn">Login</button>
    </form>

    <div class="signup">
        Don’t have an account? 
        <a href="../Registration/registration.html">Signup</a>
    </div>
</div>

<script>
    document.getElementById('loginForm').addEventListener('submit', function(event) {
        var email = this.email.value.trim();
        var password = this.password.value.trim();

        if (!email || !password) {
            alert('Please enter email and password.');
            event.preventDefault();
        }
    });
</script>

</body>
</html>