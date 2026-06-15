<?php
session_start();
include '../includes/db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] != 'teacher') {
    header("Location: ../login.php");
    exit();
}

$teacher_id = intval($_SESSION['user_id']);
$attendance = mysqli_query($conn, "SELECT a.*, s.full_name as student_name, s.student_id as sid, c.course_name, c.course_code
    FROM attendance a
    JOIN students s ON a.student_id = s.id
    JOIN courses c  ON a.course_id  = c.id
    WHERE a.marked_by = $teacher_id
    ORDER BY a.date DESC");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>View Attendance - Attendance System</title>
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
        th { background: #0A2342; color: white; padding: 12px 15px; text-align: left; white-space: nowrap; }
        td { padding: 11px 15px; border-bottom: 1px solid #f0f0f0; vertical-align: middle; }
        tr:hover td { background: #f8f9fa; }
        .status-present { background: #e8f5e9; color: #2e7d32; padding: 4px 12px; border-radius: 20px; font-size: 11px; font-weight: bold; display: inline-block; }
        .status-absent  { background: #ffebee; color: #c62828; padding: 4px 12px; border-radius: 20px; font-size: 11px; font-weight: bold; display: inline-block; }
        .status-late    { background: #fff8e1; color: #f57f17; padding: 4px 12px; border-radius: 20px; font-size: 11px; font-weight: bold; display: inline-block; }
    </style>
</head>
<body>
<div class="sidebar">
    <div class="sidebar-logo">🎓<h2>Attendance System</h2><p>Limkwing University</p></div>
    <a href="dashboard.php"><span>📊</span> Dashboard</a>
    <a href="mark_attendance.php"><span>✅</span> Mark Attendance</a>
    <a href="my_courses.php"><span>📚</span> My Courses</a>
    <a href="view_attendance.php" class="active"><span>📋</span> View Attendance</a>
    <a href="../login.php"><span>🚪</span> Logout</a>
</div>
<div class="main">
    <div class="topbar">
        <h1>📋 View Attendance</h1>
        <div>
            <span style="color:#666;font-size:13px;">👨‍🏫 <?php echo $_SESSION['user_name']; ?></span>
            &nbsp;&nbsp;
            <a href="../login.php" class="logout-btn">Logout</a>
        </div>
    </div>
    <div class="card">
        <h2>📋 Attendance Records I Marked</h2>
        <table>
            <tr>
                <th>#</th>
                <th>Student ID</th>
                <th>Student Name</th>
                <th>Course</th>
                <th>Date</th>
                <th>Status</th>
            </tr>
            <?php
            $i = 1;
            if ($attendance && mysqli_num_rows($attendance) > 0) {
                while ($row = mysqli_fetch_assoc($attendance)) {
                    $status_class = 'status-' . strtolower($row['status']);
                    if ($row['status'] == 'Present') { $icon = '✅'; }
                    elseif ($row['status'] == 'Absent') { $icon = '❌'; }
                    else { $icon = '⏰'; }
                    echo "<tr>
                        <td>$i</td>
                        <td><strong>{$row['sid']}</strong></td>
                        <td>{$row['student_name']}</td>
                        <td>{$row['course_name']} <small style='color:#999'>({$row['course_code']})</small></td>
                        <td>{$row['date']}</td>
                        <td><span class='$status_class'>$icon {$row['status']}</span></td>
                    </tr>";
                    $i++;
                }
            } else {
                echo "<tr><td colspan='6' style='text-align:center;color:#999;padding:30px;'>No attendance records found</td></tr>";
            }
            ?>
        </table>
    </div>
</div>
</body>
</html>