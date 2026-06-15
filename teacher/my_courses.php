<?php
session_start();
include '../includes/db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] != 'teacher') {
    header("Location: ../login.php");
    exit();
}

$teacher_id = intval($_SESSION['user_id']);
$courses = mysqli_query($conn, "SELECT * FROM courses WHERE teacher_id=$teacher_id ORDER BY course_name");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>My Courses - Attendance System</title>
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
        .card { background: white; border-radius: 12px; padding: 25px; box-shadow: 0 2px 10px rgba(0,0,0,0.06); margin-bottom: 25px; }
        .card h2 { color: #0A2342; font-size: 16px; margin-bottom: 20px; }
        table { width: 100%; border-collapse: collapse; font-size: 13px; }
        th { background: #0A2342; color: white; padding: 12px 15px; text-align: left; }
        td { padding: 11px 15px; border-bottom: 1px solid #f0f0f0; vertical-align: middle; }
        tr:hover td { background: #f8f9fa; }
        .badge-course { background: #e8f5e9; color: #2e7d32; padding: 4px 12px; border-radius: 20px; font-size: 11px; font-weight: bold; display: inline-block; }
    </style>
</head>
<body>
<div class="sidebar">
    <div class="sidebar-logo">🎓<h2>Attendance System</h2><p>Limkwing University</p></div>
    <a href="dashboard.php"><span>📊</span> Dashboard</a>
    <a href="mark_attendance.php"><span>✅</span> Mark Attendance</a>
    <a href="my_courses.php" class="active"><span>📚</span> My Courses</a>
    <a href="view_attendance.php"><span>📋</span> View Attendance</a>
    <a href="../login.php"><span>🚪</span> Logout</a>
</div>
<div class="main">
    <div class="topbar">
        <h1>📚 My Courses</h1>
        <div>
            <span style="color:#666;font-size:13px;">👨‍🏫 <?php echo $_SESSION['user_name']; ?></span>
            &nbsp;&nbsp;
            <a href="../login.php" class="logout-btn">Logout</a>
        </div>
    </div>
    <div class="card">
        <h2>📚 Courses Assigned to Me</h2>
        <table>
            <tr>
                <th>#</th>
                <th>Course Code</th>
                <th>Course Name</th>
            </tr>
            <?php
            $i = 1;
            if ($courses && mysqli_num_rows($courses) > 0) {
                while ($row = mysqli_fetch_assoc($courses)) {
                    echo "<tr>
                        <td>$i</td>
                        <td><span class='badge-course'>{$row['course_code']}</span></td>
                        <td><strong>{$row['course_name']}</strong></td>
                    </tr>";
                    $i++;
                }
            } else {
                echo "<tr><td colspan='3' style='text-align:center;color:#999;padding:30px;'>No courses assigned yet</td></tr>";
            }
            ?>
        </table>
    </div>
</div>
</body>
</html>