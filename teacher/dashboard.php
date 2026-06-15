<?php
session_start();
include '../includes/db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] != 'teacher') {
    header("Location: ../login.php");
    exit();
}

$teacher_id     = intval($_SESSION['user_id']);
$my_courses     = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM courses WHERE teacher_id=$teacher_id"))['total'];
$total_students = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM students"))['total'];
$my_attendance  = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM attendance WHERE marked_by=$teacher_id"))['total'];
$today_marked   = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM attendance WHERE marked_by=$teacher_id AND date=CURDATE()"))['total'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Teacher Dashboard - Attendance System</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: Tahoma, sans-serif; background: #f0f4f8; }
        .sidebar { width: 250px; background: linear-gradient(180deg, #0A2342, #1565C0); height: 100vh; position: fixed; top: 0; left: 0; padding: 20px 0; overflow-y: auto; }
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
        .stats-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 20px; margin-bottom: 25px; }
        .stat-card { background: white; padding: 25px; border-radius: 12px; box-shadow: 0 2px 10px rgba(0,0,0,0.06); text-align: center; border-top: 4px solid; }
        .stat-card.blue   { border-color: #1565C0; }
        .stat-card.green  { border-color: #2E7D32; }
        .stat-card.orange { border-color: #E65100; }
        .stat-card.teal   { border-color: #00695C; }
        .stat-card .icon  { font-size: 36px; margin-bottom: 10px; }
        .stat-card .num   { font-size: 36px; font-weight: bold; color: #0A2342; }
        .stat-card .label { font-size: 13px; color: #666; margin-top: 5px; }
        .section-title { font-size: 17px; font-weight: bold; color: #0A2342; margin-bottom: 15px; }
        .actions-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 15px; }
        .action-card { background: white; padding: 20px; border-radius: 12px; text-align: center; text-decoration: none; box-shadow: 0 2px 10px rgba(0,0,0,0.06); transition: transform 0.2s; }
        .action-card:hover { transform: translateY(-3px); }
        .action-card .icon { font-size: 32px; margin-bottom: 10px; }
        .action-card h3 { color: #0A2342; font-size: 14px; }
        .action-card p  { color: #666; font-size: 12px; margin-top: 5px; }
    </style>
</head>
<body>
<div class="sidebar">
    <div class="sidebar-logo">🎓<h2>Attendance System</h2><p>Limkwing University</p></div>
    <a href="dashboard.php" class="active"><span>📊</span> Dashboard</a>
    <a href="mark_attendance.php"><span>✅</span> Mark Attendance</a>
    <a href="my_courses.php"><span>📚</span> My Courses</a>
    <a href="view_attendance.php"><span>📋</span> View Attendance</a>
    <a href="../login.php"><span>🚪</span> Logout</a>
</div>
<div class="main">
    <div class="topbar">
        <h1>📊 Teacher Dashboard</h1>
        <div>
            <span style="color:#666;font-size:13px;">👨‍🏫 <?php echo $_SESSION['user_name']; ?> | Teacher</span>
            &nbsp;&nbsp;
            <a href="../login.php" class="logout-btn">Logout</a>
        </div>
    </div>
    <div class="stats-grid">
        <div class="stat-card blue">
            <div class="icon">📚</div>
            <div class="num"><?php echo $my_courses; ?></div>
            <div class="label">My Courses</div>
        </div>
        <div class="stat-card green">
            <div class="icon">👥</div>
            <div class="num"><?php echo $total_students; ?></div>
            <div class="label">Total Students</div>
        </div>
        <div class="stat-card orange">
            <div class="icon">✅</div>
            <div class="num"><?php echo $my_attendance; ?></div>
            <div class="label">Records Marked</div>
        </div>
        <div class="stat-card teal">
            <div class="icon">📅</div>
            <div class="num"><?php echo $today_marked; ?></div>
            <div class="label">Marked Today</div>
        </div>
    </div>
    <p class="section-title">Quick Actions</p>
    <div class="actions-grid">
        <a href="mark_attendance.php" class="action-card">
            <div class="icon">✅</div>
            <h3>Mark Attendance</h3>
            <p>Mark student attendance for your courses</p>
        </a>
        <a href="my_courses.php" class="action-card">
            <div class="icon">📚</div>
            <h3>My Courses</h3>
            <p>View courses assigned to you</p>
        </a>
        <a href="view_attendance.php" class="action-card">
            <div class="icon">📋</div>
            <h3>View Attendance</h3>
            <p>View attendance records you marked</p>
        </a>
    </div>
</div>
</body>
</html>