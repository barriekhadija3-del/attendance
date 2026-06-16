<?php
session_start();
include '../includes/db.php';

$error = "";

if (isset($_POST['login'])) {
    $email    = mysqli_real_escape_string($conn, $_POST['email']);
    $password = MD5($_POST['password']);

    $sql    = "SELECT * FROM students_login WHERE email='$email' AND password='$password'";
    $result = mysqli_query($conn, $sql);

    if (mysqli_num_rows($result) > 0) {
        $user = mysqli_fetch_assoc($result);
        $_SESSION['student_id']   = $user['id'];
        $_SESSION['student_name'] = $user['full_name'];
        $_SESSION['student_sid']  = $user['student_id'];
        header("Location: dashboard.php");
        exit();
    } else {
        $error = "Invalid email or password!";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Login - Attendance System</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: Tahoma, sans-serif;
            background: linear-gradient(135deg, #0A2342, #00695C);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .login-box {
            background: white;
            border-radius: 20px;
            width: 400px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
            overflow: hidden;
        }
        .login-header {
            background: linear-gradient(135deg, #0A2342, #00695C);
            padding: 35px 30px;
            text-align: center;
            color: white;
        }
        .login-header h1 { font-size: 22px; margin-top: 10px; }
        .login-header p  { font-size: 13px; opacity: 0.8; margin-top: 5px; }
        .login-body { padding: 30px; }
        .form-group { margin-bottom: 18px; }
        label { display: block; font-size: 13px; font-weight: bold; color: #333; margin-bottom: 6px; }
        input {
            width: 100%;
            padding: 12px 15px;
            border: 2px solid #e0e0e0;
            border-radius: 8px;
            font-size: 13px;
            font-family: Tahoma, sans-serif;
        }
        input:focus { outline: none; border-color: #00695C; }
        .btn-login {
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
        }
        .btn-login:hover { opacity: 0.9; }
        .error { background: #ffebee; color: #c62828; padding: 12px; border-radius: 8px; margin-bottom: 15px; border-left: 4px solid #c62828; font-size: 13px; }
        .register-link { text-align: center; margin-top: 15px; font-size: 13px; color: #666; }
        .register-link a { color: #00695C; font-weight: bold; text-decoration: none; }
        .back-link { text-align: center; margin-top: 10px; }
        .back-link a { color: #999; font-size: 12px; text-decoration: none; }
        .demo-info { background: #e8f5e9; border-radius: 8px; padding: 12px; font-size: 12px; color: #2e7d32; margin-top: 15px; }
    </style>
</head>
<body>
<div class="login-box">
    <div class="login-header">
        <div style="font-size:40px;">🎓</div>
        <h1>Student Login</h1>
        <p>Sign in to view your attendance records</p>
    </div>
    <div class="login-body">
        <?php if ($error != "") echo "<div class='error'>❌ $error</div>"; ?>
        <form method="POST">
            <div class="form-group">
                <label>Email Address</label>
                <input type="email" name="email" placeholder="Enter your email" required>
            </div>
            <div class="form-group">
                <label>Password</label>
                <input type="password" name="password" placeholder="Enter your password" required>
            </div>
            <button type="submit" name="login" class="btn-login">🔐 Login</button>
        </form>
        <div class="register-link">Don't have an account? <a href="register.php">Register here</a></div>
        <div class="back-link"><a href="../index.php">← Back to Home</a></div>
        <div class="demo-info">💡 Register first with your Student ID to access your attendance records</div>
    </div>
</div>
</body>
</html>