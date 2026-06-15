<?php
session_start();
include '../includes/db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] != 'admin') {
    header("Location: ../login.php");
    exit();
}

// Get stats
$total_students = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM students"))['total'];
$total_teachers = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM users WHERE role='teacher'"))['total'];
$total_courses  = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM courses"))['total'];
$today_attendance = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM attendance WHERE date=CURDATE()"))['total'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin Dashboard - Attendance System</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: Tahoma, sans-serif; background: #f0f4f8; }

        /* Sidebar */
        .sidebar {
            width: 250px;
            background: linear-gradient(180deg, #0A2342, #1565C0);
            height: 100vh;
            position: fixed;
            top: 0; left: 0;
            padding: 20px 0;
            overflow-y: auto;
        }
        .sidebar-logo {
            text-align: center;
            padding: 20px;
            border-bottom: 1px solid rgba(255,255,255,0.1);
            margin-bottom: 20px;
        }
        .sidebar-logo h2 {
            color: white;
            font-size: 16px;
            margin-top: 8px;
        }
        .sidebar-logo p {
            color: rgba(255,255,255,0.6);
            font-size: 11px;
        }
        .sidebar a {
            display: block;
            color: rgba(255,255,255,0.8);
            text-decoration: none;
            padding: 12px 25px;
            font-size: 14px;
            transition: all 0.3s;
            border-left: 3px solid transparent;
        }
        .sidebar a:hover, .sidebar a.active {
            background: rgba(255,255,255,0.1);
            color: white;
            border-left-color: #00BFA5;
        }
        .sidebar a span { margin-right: 10px; }

        /* Main content */
        .main {
            margin-left: 250px;
            padding: 25px;
        }
        .topbar {
            background: white;
            padding: 15px 25px;
            border-radius: 10px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 25px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        }
        .topbar h1 { color: #0A2342; font-size: 20px; }
        .topbar .user-info { color: #666; font-size: 13px; }
        .logout-btn {
            background: #ff5252;
            color: white;
            padding: 8px 18px;
            border-radius: 6px;
            text-decoration: none;
            font-size: 13px;
            font-family: Tahoma, sans-serif;
        }

        /* Stats cards */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 20px;
            margin-bottom: 25px;
        }
        .stat-card {
            background: white;
            padding: 25px;
            border-radius: 12px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.06);
            text-align: center;
            border-top: 4px solid;
        }
        .stat-card.blue  { border-color: #1565C0; }
        .stat-card.green { border-color: #2E7D32; }
        .stat-card.orange{ border-color: #E65100; }
        .stat-card.teal  { border-color: #00695C; }
        .stat-card .icon { font-size: 36px; margin-bottom: 10px; }
        .stat-card .num  { font-size: 36px; font-weight: bold; color: #0A2342; }
        .stat-card .label{ font-size: 13px; color: #666; margin-top: 5px; }

        /* Quick actions */
        .section-title {
            font-size: 17px;
            font-weight: bold;
            color: #0A2342;
            margin-bottom: 15px;
        }
        .actions-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 15px;
            margin-bottom: 25px;
        }
        .action-card {
            background: white;
            padding: 20px;
            border-radius: 12px;
            text-align: center;
            text-decoration: none;
            box-shadow: 0 2px 10px rgba(0,0,0,0.06);
            transition: transform 0.2s;
        }
        .action-card:hover { transform: translateY(-3px); }
        .action-card .icon { font-size: 32px; margin-bottom: 10px; }
        .action-card h3 { color: #0A2342; font-size: 14px; }
        .action-card p  { color: #666; font-size: 12px; margin-top: 5px; }
    </style>
</head>
<body>

<!-- Sidebar -->
<div class="sidebar">
    <div class="sidebar-logo">
        🎓
        <h2>Attendance System</h2>
        <p>Limkwing University</p>
    </div>
    <a href="dashboard.php" class="active"><span>📊</span> Dashboard</a>
    <a href="students.php"><span>👥</span> Students</a>
    <a href="teachers.php"><span>👨‍🏫</span> Teachers</a>
    <a href="courses.php"><span>📚</span> Courses</a>
    <a href="attendance.php"><span>✅</span> Attendance</a>
    <a href="reports.php"><span>📈</span> Reports</a>
    <a href="../login.php"><span>🚪</span> Logout</a>
</div>

<!-- Main Content -->
<div class="main">
    <div class="topbar">
        <h1>📊 Admin Dashboard</h1>
        <div>
            <span class="user-info">👤 <?php echo $_SESSION['user_name']; ?> | Admin</span>
            &nbsp;&nbsp;
            <a href="../login.php" class="logout-btn">Logout</a>
        </div>
    </div>

    <!-- Stats -->
    <div class="stats-grid">
        <div class="stat-card blue">
            <div class="icon">👥</div>
            <div class="num"><?php echo $total_students; ?></div>
            <div class="label">Total Students</div>
        </div>
        <div class="stat-card green">
            <div class="icon">👨‍🏫</div>
            <div class="num"><?php echo $total_teachers; ?></div>
            <div class="label">Total Teachers</div>
        </div>
        <div class="stat-card orange">
            <div class="icon">📚</div>
            <div class="num"><?php echo $total_courses; ?></div>
            <div class="label">Total Courses</div>
        </div>
        <div class="stat-card teal">
            <div class="icon">✅</div>
            <div class="num"><?php echo $today_attendance; ?></div>
            <div class="label">Today's Attendance</div>
        </div>
    </div>

    <!-- Quick Actions -->
    <p class="section-title">Quick Actions</p>
    <div class="actions-grid">
        <a href="students.php" class="action-card">
            <div class="icon">👥</div>
            <h3>Manage Students</h3>
            <p>Add, edit or remove students</p>
        </a>
        <a href="courses.php" class="action-card">
            <div class="icon">📚</div>
            <h3>Manage Courses</h3>
            <p>Add and assign courses</p>
        </a>
        <a href="attendance.php" class="action-card">
            <div class="icon">✅</div>
            <h3>View Attendance</h3>
            <p>Check attendance records</p>
        </a>
        <a href="teachers.php" class="action-card">
            <div class="icon">👨‍🏫</div>
            <h3>Manage Teachers</h3>
            <p>Add and manage teachers</p>
        </a>
        <a href="reports.php" class="action-card">
            <div class="icon">📈</div>
            <h3>View Reports</h3>
            <p>Attendance reports and stats</p>
        </a>
        <a href="../login.php" class="action-card">
            <div class="icon">🚪</div>
            <h3>Logout</h3>
            <p>Sign out of the system</p>
        </a>
    </div>
</div>

</body>
</html>