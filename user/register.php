<?php
session_start();
include '../includes/db.php';

// Create students_login table if not exists
mysqli_query($conn, "CREATE TABLE IF NOT EXISTS students_login (
    id INT AUTO_INCREMENT PRIMARY KEY,
    student_id VARCHAR(20) NOT NULL,
    full_name VARCHAR(100) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)");

$success = "";
$error   = "";

if (isset($_POST['register'])) {
    $student_id = mysqli_real_escape_string($conn, $_POST['student_id']);
    $full_name  = mysqli_real_escape_string($conn, $_POST['full_name']);
    $email      = mysqli_real_escape_string($conn, $_POST['email']);
    $password   = MD5($_POST['password']);
    $confirm    = MD5($_POST['confirm']);

    if (MD5($_POST['password']) != MD5($_POST['confirm'])) {
        $error = "Passwords do not match!";
    } else {
        $check = mysqli_query($conn, "SELECT * FROM students_login WHERE email='$email'");
        if (mysqli_num_rows($check) > 0) {
            $error = "Email already registered!";
        } else {
            $sql = "INSERT INTO students_login (student_id, full_name, email, password)
                    VALUES ('$student_id','$full_name','$email','$password')";
            if (mysqli_query($conn, $sql)) {
                $success = "Registration successful! You can now login.";
            } else {
                $error = "Error: " . mysqli_error($conn);
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Register - Attendance System</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: Tahoma, sans-serif;
            background: linear-gradient(135deg, #0A2342, #00695C);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        .register-box {
            background: white;
            border-radius: 20px;
            width: 480px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
            overflow: hidden;
        }
        .register-header {
            background: linear-gradient(135deg, #0A2342, #00695C);
            padding: 30px;
            text-align: center;
            color: white;
        }
        .register-header h1 { font-size: 22px; margin-top: 10px; }
        .register-header p  { font-size: 13px; opacity: 0.8; margin-top: 5px; }
        .register-body { padding: 30px; }
        .form-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 15px;
        }
        .form-group { margin-bottom: 5px; }
        .form-group.full { grid-column: span 2; }
        label { display: block; font-size: 12px; font-weight: bold; color: #333; margin-bottom: 5px; }
        input {
            width: 100%;
            padding: 11px 14px;
            border: 2px solid #e0e0e0;
            border-radius: 8px;
            font-size: 13px;
            font-family: Tahoma, sans-serif;
            transition: border-color 0.3s;
        }
        input:focus { outline: none; border-color: #00695C; }
        .btn-register {
            width: 100%;
            padding: 13px;
            background: linear-gradient(135deg, #0A2342, #00695C);
            color: white;
            border: none;
            border-radius: 10px;
            font-size: 15px;
            font-family: Tahoma, sans-serif;
            cursor: pointer;
            font-weight: bold;
            margin-top: 15px;
        }
        .btn-register:hover { opacity: 0.9; }
        .success { background: #e8f5e9; color: #2e7d32; padding: 12px; border-radius: 8px; margin-bottom: 15px; border-left: 4px solid #2e7d32; font-size: 13px; }
        .error   { background: #ffebee; color: #c62828; padding: 12px; border-radius: 8px; margin-bottom: 15px; border-left: 4px solid #c62828; font-size: 13px; }
        .login-link { text-align: center; margin-top: 15px; font-size: 13px; color: #666; }
        .login-link a { color: #00695C; font-weight: bold; text-decoration: none; }
        .back-link { text-align: center; margin-top: 10px; }
        .back-link a { color: #999; font-size: 12px; text-decoration: none; }
    </style>
</head>
<body>
<div class="register-box">
    <div class="register-header">
        <div style="font-size:40px;">🎓</div>
        <h1>Student Registration</h1>
        <p>Create your account to view your attendance</p>
    </div>
    <div class="register-body">
        <?php if ($success != "") echo "<div class='success'>✅ $success</div>"; ?>
        <?php if ($error   != "") echo "<div class='error'>❌ $error</div>"; ?>
        <form method="POST">
            <div class="form-grid">
                <div class="form-group">
                    <label>Student ID</label>
                    <input type="text" name="student_id" placeholder="e.g. LU2024001" required>
                </div>
                <div class="form-group">
                    <label>Full Name</label>
                    <input type="text" name="full_name" placeholder="Your full name" required>
                </div>
                <div class="form-group full">
                    <label>Email Address</label>
                    <input type="email" name="email" placeholder="your@email.com" required>
                </div>
                <div class="form-group">
                    <label>Password</label>
                    <input type="password" name="password" placeholder="Create password" required>
                </div>
                <div class="form-group">
                    <label>Confirm Password</label>
                    <input type="password" name="confirm" placeholder="Repeat password" required>
                </div>
            </div>
            <button type="submit" name="register" class="btn-register">📝 Create Account</button>
        </form>
        <div class="login-link">Already have an account? <a href="login.php">Login here</a></div>
        <div class="back-link"><a href="../index.php">← Back to Home</a></div>
    </div>
</div>
</body>
</html>