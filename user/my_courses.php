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

$courses = null;
if ($student) {
    $sid = $student['id'];
    $courses = mysqli_query($conn,
        "SELECT DISTINCT c.*, u.full_name as teacher_name,
         COUNT(a.id) as total_classes,
         SUM(CASE WHEN a.status='Present' THEN 1 ELSE 0 END) as present_count
         FROM attendance a
         JOIN courses c ON a.course_id = c.id
         JOIN users u   ON c.teacher_id = u.id
         WHERE a.student_id = $sid
         GROUP BY c.id");
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>My Courses - Attendance System</title>
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
        .courses-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 20px; }
        .course-card { background: white; border-radius: 12px; padding: 20px; box-shadow: 0 2px 10px rgba(0,0,0,0.06); border-top: 4px solid #00695C; }
        .course-card .code { font-size: 22px; font-weight: bold; color: #00695C; margin-bottom: 5px; }
        .course-card .name { font-size: 15px; color: #0A2342; font-weight: bold; margin-bottom: 8px; }
        .course-card .teacher { font-size: 12px; color: #666; margin-bottom: 15px; }
        .progress-bar { background: #e0e0e0; border-radius: 10px; height: 8px; }
        .progress-fill { border-radius: 10px; height: 8px; }
        .course-stats { display: flex; justify-content: space-between; margin-top: 10px; font-size: 12px; color: #666; }
    </style>
</head>
<body>
<div class="sidebar">
    <div class="sidebar-logo">🎓<h2>Student Portal</h2><p>Attendance System</p></div>
    <a href="dashboard.php"><span>📊</span> My Dashboard</a>
    <a href="my_attendance.php"><span>✅</span> My Attendance</a>
    <a href="my_courses.php" class="active"><span>📚</span> My Courses</a>
    <a href="my_profile.php"><span>👤</span> My Profile</a>
    <a href="../index.php"><span>🏠</span> Home</a>
    <a href="logout.php"><span>🚪</span> Logout</a>
</div>
<div class="main">
    <div class="topbar">
        <h1>📚 My Courses</h1>
        <div>
            <span style="color:#666;font-size:13px;">👤 <?php echo $_SESSION['student_name']; ?></span>
            &nbsp;&nbsp;
            <a href="logout.php" class="logout-btn">Logout</a>
        </div>
    </div>
    <div class="courses-grid">
        <?php
        if ($courses && mysqli_num_rows($courses) > 0) {
            while ($row = mysqli_fetch_assoc($courses)) {
                $pct = $row['total_classes'] > 0 ? round(($row['present_count'] / $row['total_classes']) * 100) : 0;
                $col = $pct >= 75 ? '#2e7d32' : ($pct >= 50 ? '#f57f17' : '#c62828');
                echo "<div class='course-card'>
                    <div class='code'>📚 {$row['course_code']}</div>
                    <div class='name'>{$row['course_name']}</div>
                    <div class='teacher'>👨‍🏫 {$row['teacher_name']}</div>
                    <div class='progress-bar'>
                        <div class='progress-fill' style='width:{$pct}%;background:{$col};'></div>
                    </div>
                    <div class='course-stats'>
                        <span>Classes: {$row['total_classes']}</span>
                        <span style='color:{$col};font-weight:bold;'>{$pct}%</span>
                    </div>
                </div>";
            }
        } else {
            echo "<div style='grid-column:span 3;text-align:center;color:#999;padding:50px;background:white;border-radius:12px;'>No courses found yet. Your teacher needs to mark your attendance first.</div>";
        }
        ?>
    </div>
</div>
</body>
</html>