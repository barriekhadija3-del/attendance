<?php
session_start();
include '../includes/db.php';

if (!isset($_SESSION['student_id'])) {
    header("Location: login.php");
    exit();
}

$student_name = $_SESSION['student_name'];
$student_sid  = $_SESSION['student_sid'];

// Get student record from students table
$student = mysqli_fetch_assoc(mysqli_query($conn,
    "SELECT * FROM students WHERE student_id='$student_sid'"));

$total      = 0;
$present    = 0;
$absent     = 0;
$late       = 0;
$attendance = null;

if ($student) {
    $sid = $student['id'];
    $stats = mysqli_fetch_assoc(mysqli_query($conn,
        "SELECT COUNT(*) as total,
         SUM(CASE WHEN status='Present' THEN 1 ELSE 0 END) as present,
         SUM(CASE WHEN status='Absent'  THEN 1 ELSE 0 END) as absent,
         SUM(CASE WHEN status='Late'    THEN 1 ELSE 0 END) as late
         FROM attendance WHERE student_id=$sid"));
    $total   = $stats['total'] ?? 0;
    $present = $stats['present'] ?? 0;
    $absent  = $stats['absent'] ?? 0;
    $late    = $stats['late'] ?? 0;
    $percent = $total > 0 ? round(($present / $total) * 100) : 0;
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
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Dashboard - Attendance System</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: Tahoma, sans-serif; background: #f0f4f8; }
        .sidebar {
            width: 250px;
            background: linear-gradient(180deg, #0A2342, #00695C);
            height: 100vh;
            position: fixed;
            top: 0; left: 0;
            padding: 20px 0;
            overflow-y: auto;
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

        /* Profile Card */
        .profile-card {
            background: linear-gradient(135deg, #0A2342, #00695C);
            border-radius: 15px;
            padding: 25px;
            color: white;
            margin-bottom: 25px;
            display: flex;
            align-items: center;
            gap: 20px;
        }
        .profile-avatar {
            width: 80px; height: 80px;
            background: rgba(255,255,255,0.2);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 36px;
        }
        .profile-info h2 { font-size: 22px; margin-bottom: 5px; }
        .profile-info p  { font-size: 13px; opacity: 0.8; }

        /* Stats */
        .stats-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 20px; margin-bottom: 25px; }
        .stat-card { background: white; padding: 20px; border-radius: 12px; box-shadow: 0 2px 10px rgba(0,0,0,0.06); text-align: center; border-top: 4px solid; }
        .stat-card.blue   { border-color: #1565C0; }
        .stat-card.green  { border-color: #2E7D32; }
        .stat-card.red    { border-color: #C62828; }
        .stat-card.orange { border-color: #E65100; }
        .stat-card .icon  { font-size: 28px; margin-bottom: 8px; }
        .stat-card .num   { font-size: 30px; font-weight: bold; color: #0A2342; }
        .stat-card .label { font-size: 12px; color: #666; margin-top: 4px; }

        /* Progress */
        .progress-card { background: white; border-radius: 12px; padding: 25px; box-shadow: 0 2px 10px rgba(0,0,0,0.06); margin-bottom: 25px; }
        .progress-card h2 { color: #0A2342; font-size: 16px; margin-bottom: 15px; }
        .progress-bar-wrap { background: #e0e0e0; border-radius: 10px; height: 20px; width: 100%; }
        .progress-bar-fill { border-radius: 10px; height: 20px; transition: width 0.5s; }
        .progress-labels { display: flex; justify-content: space-between; margin-top: 8px; font-size: 12px; color: #666; }

        /* Table */
        .card { background: white; border-radius: 12px; padding: 25px; box-shadow: 0 2px 10px rgba(0,0,0,0.06); }
        .card h2 { color: #0A2342; font-size: 16px; margin-bottom: 20px; }
        table { width: 100%; border-collapse: collapse; font-size: 13px; }
        th { background: #0A2342; color: white; padding: 12px 15px; text-align: left; white-space: nowrap; }
        td { padding: 11px 15px; border-bottom: 1px solid #f0f0f0; vertical-align: middle; }
        tr:hover td { background: #f8f9fa; }
        .status-present { background: #e8f5e9; color: #2e7d32; padding: 4px 12px; border-radius: 20px; font-size: 11px; font-weight: bold; display: inline-block; }
        .status-absent  { background: #ffebee; color: #c62828; padding: 4px 12px; border-radius: 20px; font-size: 11px; font-weight: bold; display: inline-block; }
        .status-late    { background: #fff8e1; color: #f57f17; padding: 4px 12px; border-radius: 20px; font-size: 11px; font-weight: bold; display: inline-block; }
        .warning { background: #fff3e0; border: 1px solid #ffb74d; border-radius: 10px; padding: 15px; color: #e65100; font-size: 13px; margin-bottom: 20px; }
    </style>
</head>
<body>
<div class="sidebar">
    <div class="sidebar-logo">
        🎓
        <h2>Student Portal</h2>
        <p>Attendance System</p>
    </div>
    <a href="dashboard.php" class="active"><span>📊</span> My Dashboard</a>
    <a href="my_attendance.php"><span>✅</span> My Attendance</a>
    <a href="my_courses.php"><span>📚</span> My Courses</a>
    <a href="my_profile.php"><span>👤</span> My Profile</a>
    <a href="../index.php"><span>🏠</span> Home</a>
    <a href="logout.php"><span>🚪</span> Logout</a>
</div>
<div class="main">
    <div class="topbar">
        <h1>🎓 Student Dashboard</h1>
        <div>
            <span style="color:#666;font-size:13px;">👤 <?php echo $student_name; ?></span>
            &nbsp;&nbsp;
            <a href="logout.php" class="logout-btn">Logout</a>
        </div>
    </div>

    <!-- Profile Card -->
    <div class="profile-card">
        <div class="profile-avatar">🎓</div>
        <div class="profile-info">
            <h2><?php echo $student_name; ?></h2>
            <p>Student ID: <?php echo $student_sid; ?></p>
            <?php if ($student) { ?>
            <p>Class: <?php echo $student['class']; ?> | Gender: <?php echo $student['gender']; ?></p>
            <?php } ?>
        </div>
    </div>

    <?php if (!$student) { ?>
    <div class="warning">
        ⚠️ Your Student ID <strong><?php echo $student_sid; ?></strong> is not yet registered in the system.
        Please contact your Admin to add your record so your attendance can be tracked.
    </div>
    <?php } ?>

    <!-- Stats -->
    <div class="stats-grid">
        <div class="stat-card blue">
            <div class="icon">📋</div>
            <div class="num"><?php echo $total; ?></div>
            <div class="label">Total Classes</div>
        </div>
        <div class="stat-card green">
            <div class="icon">✅</div>
            <div class="num"><?php echo $present; ?></div>
            <div class="label">Present</div>
        </div>
        <div class="stat-card red">
            <div class="icon">❌</div>
            <div class="num"><?php echo $absent; ?></div>
            <div class="label">Absent</div>
        </div>
        <div class="stat-card orange">
            <div class="icon">⏰</div>
            <div class="num"><?php echo $late; ?></div>
            <div class="label">Late</div>
        </div>
    </div>

    <?php if ($total > 0) { ?>
    <!-- Progress Bar -->
    <div class="progress-card">
        <h2>📊 Your Attendance Percentage</h2>
        <?php
        $color = $percent >= 75 ? '#2e7d32' : ($percent >= 50 ? '#f57f17' : '#c62828');
        ?>
        <div class="progress-bar-wrap">
            <div class="progress-bar-fill" style="width:<?php echo $percent; ?>%;background:<?php echo $color; ?>;"></div>
        </div>
        <div class="progress-labels">
            <span>0%</span>
            <span style="color:<?php echo $color; ?>;font-weight:bold;font-size:16px;"><?php echo $percent; ?>% Attendance</span>
            <span>100%</span>
        </div>
        <?php if ($percent < 75) { ?>
        <p style="color:#c62828;font-size:13px;margin-top:10px;">⚠️ Warning: Your attendance is below 75%. Please attend more classes!</p>
        <?php } else { ?>
        <p style="color:#2e7d32;font-size:13px;margin-top:10px;">✅ Great! Your attendance is above 75%. Keep it up!</p>
        <?php } ?>
    </div>
    <?php } ?>

    <!-- Attendance Records -->
    <div class="card">
        <h2>📋 My Recent Attendance Records</h2>
        <table>
            <tr>
                <th>#</th>
                <th>Course</th>
                <th>Teacher</th>
                <th>Date</th>
                <th>Status</th>
            </tr>
            <?php
            $i = 1;
            if ($attendance && mysqli_num_rows($attendance) > 0) {
                while ($row = mysqli_fetch_assoc($attendance)) {
                    $sc = 'status-' . strtolower($row['status']);
                    $ic = $row['status']=='Present' ? '✅' : ($row['status']=='Absent' ? '❌' : '⏰');
                    echo "<tr>
                        <td>$i</td>
                        <td><strong>{$row['course_name']}</strong> <small style='color:#999'>({$row['course_code']})</small></td>
                        <td>{$row['teacher_name']}</td>
                        <td>{$row['date']}</td>
                        <td><span class='$sc'>$ic {$row['status']}</span></td>
                    </tr>";
                    $i++;
                }
            } else {
                echo "<tr><td colspan='5' style='text-align:center;color:#999;padding:30px;'>No attendance records found yet</td></tr>";
            }
            ?>
        </table>
    </div>
</div>
</body>
</html>