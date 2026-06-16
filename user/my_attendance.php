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

$attendance = null;
if ($student) {
    $sid = $student['id'];
    $attendance = mysqli_query($conn,
        "SELECT a.*, c.course_name, c.course_code, u.full_name as teacher_name
         FROM attendance a
         JOIN courses c ON a.course_id = c.id
         JOIN users u   ON a.marked_by = u.id
         WHERE a.student_id = $sid
         ORDER BY a.date DESC");
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>My Attendance - Attendance System</title>
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
        .card { background: white; border-radius: 12px; padding: 25px; box-shadow: 0 2px 10px rgba(0,0,0,0.06); }
        .card h2 { color: #0A2342; font-size: 16px; margin-bottom: 20px; }
        table { width: 100%; border-collapse: collapse; font-size: 13px; }
        th { background: #0A2342; color: white; padding: 12px 15px; text-align: left; }
        td { padding: 11px 15px; border-bottom: 1px solid #f0f0f0; vertical-align: middle; }
        tr:hover td { background: #f8f9fa; }
        .status-present { background: #e8f5e9; color: #2e7d32; padding: 4px 12px; border-radius: 20px; font-size: 11px; font-weight: bold; display: inline-block; }
        .status-absent  { background: #ffebee; color: #c62828; padding: 4px 12px; border-radius: 20px; font-size: 11px; font-weight: bold; display: inline-block; }
        .status-late    { background: #fff8e1; color: #f57f17; padding: 4px 12px; border-radius: 20px; font-size: 11px; font-weight: bold; display: inline-block; }
        @media print { .sidebar, .topbar { display: none !important; } .main { margin-left: 0 !important; } }
    </style>
</head>
<body>
<div class="sidebar">
    <div class="sidebar-logo">🎓<h2>Student Portal</h2><p>Attendance System</p></div>
    <a href="dashboard.php"><span>📊</span> My Dashboard</a>
    <a href="my_attendance.php" class="active"><span>✅</span> My Attendance</a>
    <a href="my_courses.php"><span>📚</span> My Courses</a>
    <a href="my_profile.php"><span>👤</span> My Profile</a>
    <a href="../index.php"><span>🏠</span> Home</a>
    <a href="logout.php"><span>🚪</span> Logout</a>
</div>
<div class="main">
    <div class="topbar">
        <h1>✅ My Attendance Records</h1>
        <div>
            <span style="color:#666;font-size:13px;">👤 <?php echo $_SESSION['student_name']; ?></span>
            &nbsp;&nbsp;
            <button onclick="window.print()" style="background:#2e7d32;color:white;padding:8px 18px;border-radius:6px;border:none;cursor:pointer;font-family:Tahoma;font-size:13px;">🖨️ Print</button>
            &nbsp;
            <a href="logout.php" class="logout-btn">Logout</a>
        </div>
    </div>
    <div class="card">
        <h2>📋 Full Attendance History</h2>
        <table>
            <tr>
                <th>#</th>
                <th>Course Code</th>
                <th>Course Name</th>
                <th>Teacher</th>
                <th>Date</th>
                <th>Status</th>
            </tr>
            <?php
            $i = 1;
            if ($attendance && mysqli_num_rows($attendance) > 0) {
                while ($row = mysqli_fetch_assoc($attendance)) {
                    $sc = 'status-'.strtolower($row['status']);
                    $ic = $row['status']=='Present'?'✅':($row['status']=='Absent'?'❌':'⏰');
                    echo "<tr>
                        <td>$i</td>
                        <td><strong>{$row['course_code']}</strong></td>
                        <td>{$row['course_name']}</td>
                        <td>{$row['teacher_name']}</td>
                        <td>{$row['date']}</td>
                        <td><span class='$sc'>$ic {$row['status']}</span></td>
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