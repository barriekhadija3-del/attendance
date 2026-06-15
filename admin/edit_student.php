<?php
session_start();
include '../includes/db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] != 'admin') {
    header("Location: ../login.php");
    exit();
}

$id = intval($_GET['id']);
$student = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM students WHERE id=$id"));

if (!$student) {
    header("Location: students.php");
    exit();
}

// Update student
if (isset($_POST['update_student'])) {
    $student_id = mysqli_real_escape_string($conn, $_POST['student_id']);
    $full_name  = mysqli_real_escape_string($conn, $_POST['full_name']);
    $gender     = mysqli_real_escape_string($conn, $_POST['gender']);
    $class      = mysqli_real_escape_string($conn, $_POST['class']);
    $phone      = mysqli_real_escape_string($conn, $_POST['phone']);

    $sql = "UPDATE students SET student_id='$student_id', full_name='$full_name', gender='$gender', class='$class', phone='$phone' WHERE id=$id";
    if (mysqli_query($conn, $sql)) {
        header("Location: students.php");
        exit();
    } else {
        $error = "Error: " . mysqli_error($conn);
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Edit Student - Attendance System</title>
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
        .form-group input, .form-group select { width: 100%; padding: 10px 12px; border: 2px solid #e0e0e0; border-radius: 8px; font-size: 13px; font-family: Tahoma, sans-serif; }
        .form-group input:focus, .form-group select:focus { outline: none; border-color: #1565C0; }
        .btn-update { background: #2e7d32; color: white; padding: 10px 25px; border: none; border-radius: 8px; font-size: 14px; font-family: Tahoma, sans-serif; cursor: pointer; }
        .btn-back { background: #666; color: white; padding: 10px 25px; border-radius: 8px; text-decoration: none; font-size: 14px; margin-left: 10px; }
        .error { background: #ffebee; color: #c62828; padding: 12px 15px; border-radius: 8px; margin-bottom: 15px; border-left: 4px solid #c62828; }
    </style>
</head>
<body>
<div class="sidebar">
    <div class="sidebar-logo">🎓<h2>Attendance System</h2><p>Limkwing University</p></div>
    <a href="dashboard.php"><span>📊</span> Dashboard</a>
    <a href="students.php" class="active"><span>👥</span> Students</a>
    <a href="teachers.php"><span>👨‍🏫</span> Teachers</a>
    <a href="courses.php"><span>📚</span> Courses</a>
    <a href="attendance.php"><span>✅</span> Attendance</a>
    <a href="reports.php"><span>📈</span> Reports</a>
    <a href="../login.php"><span>🚪</span> Logout</a>
</div>
<div class="main">
    <div class="topbar">
        <h1>✏️ Edit Student</h1>
        <div>
            <span style="color:#666;font-size:13px;">👤 <?php echo $_SESSION['user_name']; ?></span>
            &nbsp;&nbsp;
            <a href="../login.php" class="logout-btn">Logout</a>
        </div>
    </div>
    <div class="card">
        <h2>✏️ Edit Student Details</h2>
        <?php if (isset($error)) echo "<div class='error'>$error</div>"; ?>
        <form method="POST">
            <div class="form-grid">
                <div class="form-group">
                    <label>Student ID</label>
                    <input type="text" name="student_id" value="<?php echo $student['student_id']; ?>" required>
                </div>
                <div class="form-group">
                    <label>Full Name</label>
                    <input type="text" name="full_name" value="<?php echo $student['full_name']; ?>" required>
                </div>
                <div class="form-group">
                    <label>Gender</label>
                    <select name="gender" required>
                        <option value="Male"   <?php echo $student['gender']=='Male'   ? 'selected':''; ?>>Male</option>
                        <option value="Female" <?php echo $student['gender']=='Female' ? 'selected':''; ?>>Female</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Class</label>
                    <input type="text" name="class" value="<?php echo $student['class']; ?>" required>
                </div>
                <div class="form-group">
                    <label>Phone</label>
                    <input type="text" name="phone" value="<?php echo $student['phone']; ?>">
                </div>
            </div>
            <button type="submit" name="update_student" class="btn-update">💾 Update Student</button>
            <a href="students.php" class="btn-back">← Back</a>
        </form>
    </div>
</div>
</body>
</html>