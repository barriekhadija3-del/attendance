<?php
session_start();
include '../includes/db.php';

if (!isset($_SESSION['student_id'])) {
    header("Location: login.php");
    exit();
}

$student_sid = $_SESSION['student_sid'];
$student = mysqli_fetch_assoc(mysqli_query($conn,
    "SELECT * FROM students WHERE student_id='$student_sid'"));
$login = mysqli_fetch_assoc(mysqli_query($conn,
    "SELECT * FROM students_login WHERE id={$_SESSION['student_id']}"));
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>My Profile - Attendance System</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: Tahoma, sans-serif; background: #f0f4f8; }
        .sidebar { width: 250px; background: linear-gradient(180deg, #0A2342, #00695C); height: 100vh; position: fixed; top: 0; left: 0; padding: 20px 0; overflow-y: auto; }
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
        .profile-header { background: linear-gradient(135deg, #0A2342, #00695C); border-radius: 15px; padding: 30px; color: white; text-align: center; margin-bottom: 25px; }
        .avatar { width: 100px; height: 100px; background: rgba(255,255,255,0.2); border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 50px; margin: 0 auto 15px; }
        .card { background: white; border-radius: 12px; padding: 25px; box-shadow: 0 2px 10px rgba(0,0,0,0.06); margin-bottom: 20px; }
        .card h2 { color: #0A2342; font-size: 16px; margin-bottom: 20px; }
        .info-row { display: flex; padding: 12px 0; border-bottom: 1px solid #f0f0f0; }
        .info-row:last-child { border-bottom: none; }
        .info-label { width: 150px; font-weight: bold; color: #666; font-size: 13px; }
        .info-value { color: #0A2342; font-size: 13px; }
    </style>
</head>
<body>
<div class="sidebar">
    <div class="sidebar-logo">🎓<h2>Student Portal</h2><p>Attendance System</p></div>
    <a href="dashboard.php"><span>📊</span> My Dashboard</a>
    <a href="my_attendance.php"><span>✅</span> My Attendance</a>
    <a href="my_courses.php"><span>📚</span> My Courses</a>
    <a href="my_profile.php" class="active"><span>👤</span> My Profile</a>
    <a href="../index.php"><span>🏠</span> Home</a>
    <a href="logout.php"><span>🚪</span> Logout</a>
</div>
<div class="main">
    <div class="topbar">
        <h1>👤 My Profile</h1>
        <a href="logout.php" class="logout-btn">Logout</a>
    </div>
    <div class="profile-header">
        <div class="avatar">🎓</div>
        <h2><?php echo $_SESSION['student_name']; ?></h2>
        <p>Student ID: <?php echo $student_sid; ?></p>
    </div>
    <div class="card">
        <h2>📋 Personal Information</h2>
        <?php if ($student) { ?>
        <div class="info-row"><div class="info-label">Full Name</div><div class="info-value"><?php echo $student['full_name']; ?></div></div>
        <div class="info-row"><div class="info-label">Student ID</div><div class="info-value"><?php echo $student['student_id']; ?></div></div>
        <div class="info-row"><div class="info-label">Gender</div><div class="info-value"><?php echo $student['gender']; ?></div></div>
        <div class="info-row"><div class="info-label">Class</div><div class="info-value"><?php echo $student['class']; ?></div></div>
        <div class="info-row"><div class="info-label">Phone</div><div class="info-value"><?php echo $student['phone']; ?></div></div>
        <?php } else { echo "<p style='color:#999;'>Student record not found. Contact admin.</p>"; } ?>
    </div>
    <div class="card">
        <h2>🔐 Account Information</h2>
        <div class="info-row"><div class="info-label">Email</div><div class="info-value"><?php echo $login['email']; ?></div></div>
        <div class="info-row"><div class="info-label">Registered</div><div class="info-value"><?php echo $login['created_at']; ?></div></div>
        <div class="info-row"><div class="info-label">Role</div><div class="info-value">🎓 Student</div></div>
    </div>
</div>
</body>
</html>