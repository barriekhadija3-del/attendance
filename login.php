<?php
session_start();
include 'includes/db.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email    = mysqli_real_escape_string($conn, $_POST['email']);
    $password = MD5($_POST['password']);

    $sql    = "SELECT * FROM users WHERE email='$email' AND password='$password'";
    $result = mysqli_query($conn, $sql);

    if (mysqli_num_rows($result) > 0) {
        $user = mysqli_fetch_assoc($result);
        $_SESSION['user_id']   = $user['id'];
        $_SESSION['user_name'] = $user['full_name'];
        $_SESSION['user_role'] = $user['role'];

        if ($user['role'] == 'admin') {
            header("Location: admin/dashboard.php");
        } else {
            header("Location: teacher/dashboard.php");
        }
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
    <title>Login - Attendance System</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: Tahoma, sans-serif;
            background: linear-gradient(135deg, #0A2342, #1565C0);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .login-box {
            background: white;
            padding: 40px;
            border-radius: 15px;
            width: 400px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
        }
        .logo {
            text-align: center;
            margin-bottom: 30px;
        }
        .logo h1 {
            color: #0A2342;
            font-size: 24px;
            margin-top: 10px;
        }
        .logo p {
            color: #666;
            font-size: 13px;
            margin-top: 5px;
        }
        .form-group {
            margin-bottom: 20px;
        }
        label {
            display: block;
            margin-bottom: 6px;
            color: #333;
            font-size: 14px;
            font-weight: bold;
        }
        input {
            width: 100%;
            padding: 12px 15px;
            border: 2px solid #e0e0e0;
            border-radius: 8px;
            font-size: 14px;
            font-family: Tahoma, sans-serif;
            transition: border-color 0.3s;
        }
        input:focus {
            outline: none;
            border-color: #1565C0;
        }
        .btn {
            width: 100%;
            padding: 13px;
            background: linear-gradient(135deg, #0A2342, #1565C0);
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 15px;
            font-family: Tahoma, sans-serif;
            cursor: pointer;
            font-weight: bold;
            margin-top: 10px;
        }
        .btn:hover { opacity: 0.9; }
        .error {
            background: #ffebee;
            color: #c62828;
            padding: 10px 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            font-size: 13px;
            border-left: 4px solid #c62828;
        }
        .demo-info {
            margin-top: 20px;
            padding: 12px;
            background: #e3f2fd;
            border-radius: 8px;
            font-size: 12px;
            color: #1565C0;
        }
    </style>
</head>
<body>
    <div class="login-box">
        <div class="logo">
            🎓
            <h1>Attendance System</h1>
            <p>Limkwing University — Sierra Leone</p>
        </div>

        <?php if (isset($error)) echo "<div class='error'>$error</div>"; ?>

        <form method="POST">
            <div class="form-group">
                <label>Email Address</label>
                <input type="email" name="email" placeholder="Enter your email" required>
            </div>
            <div class="form-group">
                <label>Password</label>
                <input type="password" name="password" placeholder="Enter your password" required>
            </div>
            <button type="submit" class="btn">Login</button>
        </form>

        <div class="demo-info">
            <strong>Demo Credentials:</strong><br>
            Admin: admin@sleos.com / admin123<br>
            Teacher: teacher@sleos.com / teacher123
        </div>
    </div>
</body>
</html>