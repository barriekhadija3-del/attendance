<?php
session_start();
include '../includes/db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] != 'admin') {
    header("Location: ../login.php");
    exit();
}

// Add teacher
if (isset($_POST['add_teacher'])) {
    $full_name = mysqli_real_escape_string($conn, $_POST['full_name']);
    $email     = mysqli_real_escape_string($conn, $_POST['email']);
    $password  = MD5($_POST['password']);

    $check = mysqli_query($conn, "SELECT * FROM users WHERE email='$email'");
    if (mysqli_num_rows($check) > 0) {
        $error = "Email already exists!";
    } else {
        $sql = "INSERT INTO users (full_name, email, password, role) VALUES ('$full_name','$email','$password','teacher')";
        if (mysqli_query($conn, $sql)) {
            $success = "Teacher added successfully!";
        } else {
            $error = "Error: " . mysqli_error($conn);
        }
    }
}

// Delete teacher
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    mysqli_query($conn, "DELETE FROM users WHERE id=$id AND role='teacher'");
    header("Location: teachers.php");
    exit();
}

$result = mysqli_query($conn, "SELECT * FROM users WHERE role='teacher' ORDER BY id DESC");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Teachers - Attendance System</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: Tahoma, sans-serif; background: #f0f4f8; }
        .sidebar {
            width: 250px;
            background: linear-gradient(180deg, #0A2342, #1565C0);
            height: 100vh;
            position: fixed;
            top: 0; left: 0;
            padding: 20px 0;
        }
        .sidebar-logo { text-align: center; padding: 20px; border-bottom: 1px solid rgba(255,255,255,0.1); margin-bottom: 20px; }
        .sidebar-logo h2 { color: white; font-size: 16px; margin-top: 8px; }
        .sidebar-logo p  { color: rgba(255,255,255,0.6); font-size: 11px; }
        .sidebar a { display: block; color: rgba(255,255,255,0.8); text-decoration: none; padding: 12px 25px; font-size: 14px; transition: all 0.3s; border-left: 3px solid transparent; }
        .sidebar a:hover, .sidebar a.active { background: rgba(255,255,255,0.1); color: white; border-left-color: #00BFA5; }
        .sidebar a span { margin-right: 10px; }
        .main { margin-left: 250px; padding: 25px; }
        .topbar { background: white; padding: 15px 25px; border-radius: 10px; display: flex; justify-content: space-between; align-items: center; margin-bottom: 25px; box-shadow: 0 2px 10px rgba(0,0,0,0.05); }
        .topbar h1 { color: #0A2342; font-size: 20px; }
        .logout-btn { background: #ff5252; color: white; padding: 8px 18px; border-radius: 6px; text-decoration: none; font-size: 13px; }
        .card { background: white; border-radius: 12px; padding: 25px; box-shadow: 0 2px 10px rgba(0,0,0,0.06); margin-bottom: 25px; }
        .card h2 { color: #0A2342; font-size: 16px; margin-bottom: 20px; }
        .form-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 15px; margin-bottom: 15px; }
        .form-group label { display: block; font-size: 13px; font-weight: bold; color: #333; margin-bottom: 6px; }
        .form-group input { width: 100%; padding: 10px 12px; border: 2px solid #e0e0e0; border-radius: 8px; font-size: 13px; font-family: Tahoma, sans-serif; }
        .form-group input:focus { outline: none; border-color: #1565C0; }
        .btn-add { background: #1565C0; color: white; padding: 10px 25px; border: none; border-radius: 8px; font-size: 14px; font-family: Tahoma, sans-serif; cursor: pointer; }
        table { width: 100%; border-collapse: collapse; font-size: 13px; }
        th { background: #0A2342; color: white; padding: 12px 15px; text-align: left; }
        td { padding: 11px 15px; border-bottom: 1px solid #f0f0f0; }
        tr:hover td { background: #f8f9fa; }
        .btn-delete { background: #f44336; color: white; padding: 5px 12px; border-radius: 5px; text-decoration: none; font-size: 12px; }
        .success { background: #e8f5e9; color: #2e7d32; padding: 12px 15px; border-radius: 8px; margin-bottom: 15px; border-left: 4px solid #2e7d32; }
        .error   { background: #ffebee; color: #c62828; padding: 12px 15px; border-radius: 8px; margin-bottom: 15px; border-left: 4px solid #c62828; }
    </style>
</head>
<body>
<div class="sidebar">
    <div class="sidebar-logo">🎓<h2>Attendance System</h2><p>Limkwing University</p></div>
    <a href="dashboard.php"><span>📊</span> Dashboard</a>
    <a href="students.php"><span>👥</span> Students</a>
    <a href="teachers.php" class="active"><span>👨‍🏫</span> Teachers</a>
    <a href="courses.php"><span>📚</span> Courses</a>
    <a href="attendance.php"><span>✅</span> Attendance</a>
    <a href="reports.php"><span>📈</span> Reports</a>
    <a href="../login.php"><span>🚪</span> Logout</a>
</div>
<div class="main">
    <div class="topbar">
        <h1>👨‍🏫 Manage Teachers</h1>
        <div>
            <span style="color:#666;font-size:13px;">👤 <?php echo $_SESSION['user_name']; ?></span>
            &nbsp;&nbsp;
            <a href="../login.php" class="logout-btn">Logout</a>
        </div>
    </div>
    <div class="card">
        <h2>➕ Add New Teacher</h2>
        <?php if (isset($success)) echo "<div class='success'>$success</div>"; ?>
        <?php if (isset($error))   echo "<div class='error'>$error</div>"; ?>
        <form method="POST">
            <div class="form-grid">
                <div class="form-group">
                    <label>Full Name</label>
                    <input type="text" name="full_name" placeholder="Enter full name" required>
                </div>
                <div class="form-group">
                    <label>Email</label>
                    <input type="email" name="email" placeholder="Enter email" required>
                </div>
                <div class="form-group">
                    <label>Password</label>
                    <input type="password" name="password" placeholder="Enter password" required>
                </div>
            </div>
            <button type="submit" name="add_teacher" class="btn-add">➕ Add Teacher</button>
        </form>
    </div>
    <div class="card">
        <h2>📋 Teachers List</h2>
        <table>
            <tr>
                <th>#</th>
                <th>Full Name</th>
                <th>Email</th>
                <th>Created At</th>
                <th>Actions</th>
            </tr>
            <?php
            $i = 1;
            while ($row = mysqli_fetch_assoc($result)) {
                echo "<tr>
                    <td>$i</td>
                    <td><strong>{$row['full_name']}</strong></td>
                    <td>{$row['email']}</td>
                    <td>{$row['created_at']}</td>
                    <td><a href='teachers.php?delete={$row['id']}' class='btn-delete' onclick='return confirm(\"Delete this teacher?\")'>🗑️ Delete</a></td>
                </tr>";
                $i++;
            }
            if ($i == 1) echo "<tr><td colspan='5' style='text-align:center;color:#999;padding:30px;'>No teachers found</td></tr>";
            ?>
        </table>
    </div>
</div>
</body>
</html>