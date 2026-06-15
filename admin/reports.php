<?php
session_start();
include '../includes/db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] != 'admin') {
    header("Location: ../login.php");
    exit();
}

// Overall stats
$total_attendance = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM attendance"))['total'];
$total_present    = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM attendance WHERE status='Present'"))['total'];
$total_absent     = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM attendance WHERE status='Absent'"))['total'];
$total_late       = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM attendance WHERE status='Late'"))['total'];

// Filters
$filter_student = isset($_GET['filter_student']) ? intval($_GET['filter_student']) : 0;
$filter_course  = isset($_GET['filter_course'])  ? intval($_GET['filter_course'])  : 0;

$where = "WHERE 1=1";
if ($filter_student > 0) { $where .= " AND a.student_id=$filter_student"; }
if ($filter_course  > 0) { $where .= " AND a.course_id=$filter_course"; }

$report_sql = "SELECT a.*, s.full_name as student_name, s.student_id as sid,
               c.course_name, c.course_code
               FROM attendance a
               JOIN students s ON a.student_id = s.id
               JOIN courses c  ON a.course_id  = c.id
               $where
               ORDER BY a.date DESC";

$report   = mysqli_query($conn, $report_sql);
$students = mysqli_query($conn, "SELECT * FROM students ORDER BY full_name");
$courses  = mysqli_query($conn, "SELECT * FROM courses ORDER BY course_name");

// Student summary
$summary_sql = "SELECT s.full_name, s.student_id as sid, s.class,
    COUNT(*) as total,
    SUM(CASE WHEN a.status='Present' THEN 1 ELSE 0 END) as present,
    SUM(CASE WHEN a.status='Absent'  THEN 1 ELSE 0 END) as absent,
    SUM(CASE WHEN a.status='Late'    THEN 1 ELSE 0 END) as late
    FROM attendance a
    JOIN students s ON a.student_id = s.id
    GROUP BY a.student_id
    ORDER BY s.full_name";

$summary = mysqli_query($conn, $summary_sql);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reports - Attendance System</title>
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
            overflow-y: auto;
        }
        .sidebar-logo { text-align: center; padding: 20px; border-bottom: 1px solid rgba(255,255,255,0.1); margin-bottom: 20px; }
        .sidebar-logo h2 { color: white; font-size: 16px; margin-top: 8px; }
        .sidebar-logo p  { color: rgba(255,255,255,0.6); font-size: 11px; }
        .sidebar a {
            display: block; color: rgba(255,255,255,0.8); text-decoration: none;
            padding: 12px 25px; font-size: 14px; transition: all 0.3s;
            border-left: 3px solid transparent;
        }
        .sidebar a:hover, .sidebar a.active {
            background: rgba(255,255,255,0.1); color: white; border-left-color: #00BFA5;
        }
        .sidebar a span { margin-right: 10px; }
        .main { margin-left: 250px; padding: 25px; }
        .topbar {
            background: white; padding: 15px 25px; border-radius: 10px;
            display: flex; justify-content: space-between; align-items: center;
            margin-bottom: 25px; box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        }
        .topbar h1 { color: #0A2342; font-size: 20px; }
        .logout-btn { background: #ff5252; color: white; padding: 8px 18px; border-radius: 6px; text-decoration: none; font-size: 13px; }
        .stats-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 20px; margin-bottom: 25px; }
        .stat-card {
            background: white; padding: 20px; border-radius: 12px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.06); text-align: center; border-top: 4px solid;
        }
        .stat-card.blue   { border-color: #1565C0; }
        .stat-card.green  { border-color: #2E7D32; }
        .stat-card.red    { border-color: #C62828; }
        .stat-card.orange { border-color: #E65100; }
        .stat-card .icon  { font-size: 30px; margin-bottom: 8px; }
        .stat-card .num   { font-size: 32px; font-weight: bold; color: #0A2342; }
        .stat-card .label { font-size: 12px; color: #666; margin-top: 4px; }
        .card { background: white; border-radius: 12px; padding: 25px; box-shadow: 0 2px 10px rgba(0,0,0,0.06); margin-bottom: 25px; }
        .card h2 { color: #0A2342; font-size: 16px; margin-bottom: 20px; }
        .filter-bar { display: flex; gap: 10px; margin-bottom: 20px; align-items: flex-end; flex-wrap: wrap; }
        .filter-bar .fg label { display: block; font-size: 12px; font-weight: bold; color: #333; margin-bottom: 5px; }
        .filter-bar .fg select {
            padding: 10px 12px; border: 2px solid #e0e0e0; border-radius: 8px;
            font-size: 13px; font-family: Tahoma, sans-serif;
        }
        .btn-filter { background: #1565C0; color: white; padding: 10px 20px; border: none; border-radius: 8px; cursor: pointer; font-family: Tahoma, sans-serif; font-size: 13px; }
        .btn-clear  { background: #666; color: white; padding: 10px 15px; border-radius: 8px; text-decoration: none; font-size: 13px; }
        .btn-print  { background: #2e7d32; color: white; padding: 10px 20px; border: none; border-radius: 8px; cursor: pointer; font-family: Tahoma, sans-serif; font-size: 13px; }
        table { width: 100%; border-collapse: collapse; font-size: 13px; }
        th { background: #0A2342; color: white; padding: 12px 15px; text-align: left; white-space: nowrap; }
        td { padding: 11px 15px; border-bottom: 1px solid #f0f0f0; vertical-align: middle; }
        tr:hover td { background: #f8f9fa; }
        .status-present { background: #e8f5e9; color: #2e7d32; padding: 4px 12px; border-radius: 20px; font-size: 11px; font-weight: bold; display: inline-block; }
        .status-absent  { background: #ffebee; color: #c62828; padding: 4px 12px; border-radius: 20px; font-size: 11px; font-weight: bold; display: inline-block; }
        .status-late    { background: #fff8e1; color: #f57f17; padding: 4px 12px; border-radius: 20px; font-size: 11px; font-weight: bold; display: inline-block; }
        .progress-wrap  { display: flex; align-items: center; gap: 8px; }
        .progress-bar   { background: #e0e0e0; border-radius: 10px; height: 10px; width: 80px; }
        .progress-fill  { border-radius: 10px; height: 10px; }
        @media print {
            .sidebar, .topbar, .filter-bar, .no-print { display: none !important; }
            .main { margin-left: 0 !important; }
        }
    </style>
</head>
<body>

<div class="sidebar">
    <div class="sidebar-logo">
        🎓
        <h2>Attendance System</h2>
        <p>Limkwing University</p>
    </div>
    <a href="dashboard.php"><span>📊</span> Dashboard</a>
    <a href="students.php"><span>👥</span> Students</a>
    <a href="teachers.php"><span>👨‍🏫</span> Teachers</a>
    <a href="courses.php"><span>📚</span> Courses</a>
    <a href="attendance.php"><span>✅</span> Attendance</a>
    <a href="reports.php" class="active"><span>📈</span> Reports</a>
    <a href="../login.php"><span>🚪</span> Logout</a>
</div>

<div class="main">
    <div class="topbar">
        <h1>📈 Attendance Reports</h1>
        <div>
            <span style="color:#666;font-size:13px;">👤 <?php echo $_SESSION['user_name']; ?></span>
            &nbsp;&nbsp;
            <a href="../login.php" class="logout-btn">Logout</a>
        </div>
    </div>

    <!-- Stats -->
    <div class="stats-grid">
        <div class="stat-card blue">
            <div class="icon">✅</div>
            <div class="num"><?php echo $total_present; ?></div>
            <div class="label">Total Present</div>
        </div>
        <div class="stat-card red">
            <div class="icon">❌</div>
            <div class="num"><?php echo $total_absent; ?></div>
            <div class="label">Total Absent</div>
        </div>
        <div class="stat-card orange">
            <div class="icon">⏰</div>
            <div class="num"><?php echo $total_late; ?></div>
            <div class="label">Total Late</div>
        </div>
        <div class="stat-card green">
            <div class="icon">📋</div>
            <div class="num"><?php echo $total_attendance; ?></div>
            <div class="label">Total Records</div>
        </div>
    </div>

    <!-- Student Summary -->
    <div class="card">
        <h2>👥 Student Attendance Summary</h2>
        <table>
            <tr>
                <th>#</th>
                <th>Student ID</th>
                <th>Full Name</th>
                <th>Class</th>
                <th>Present</th>
                <th>Absent</th>
                <th>Late</th>
                <th>Total</th>
                <th>Attendance %</th>
            </tr>
            <?php
            $i = 1;
            if ($summary && mysqli_num_rows($summary) > 0) {
                while ($row = mysqli_fetch_assoc($summary)) {
                    $percent = ($row['total'] > 0) ? round(($row['present'] / $row['total']) * 100) : 0;
                    $color   = ($percent >= 75) ? '#2e7d32' : (($percent >= 50) ? '#f57f17' : '#c62828');
                    echo "<tr>";
                    echo "<td>" . $i . "</td>";
                    echo "<td><strong>" . $row['sid'] . "</strong></td>";
                    echo "<td>" . $row['full_name'] . "</td>";
                    echo "<td>" . $row['class'] . "</td>";
                    echo "<td><span class='status-present'>✅ " . $row['present'] . "</span></td>";
                    echo "<td><span class='status-absent'>❌ " . $row['absent'] . "</span></td>";
                    echo "<td><span class='status-late'>⏰ " . $row['late'] . "</span></td>";
                    echo "<td><strong>" . $row['total'] . "</strong></td>";
                    echo "<td>
                        <div class='progress-wrap'>
                            <div class='progress-bar'>
                                <div class='progress-fill' style='width:" . $percent . "%;background:" . $color . ";'></div>
                            </div>
                            <span style='color:" . $color . ";font-weight:bold;'>" . $percent . "%</span>
                        </div>
                    </td>";
                    echo "</tr>";
                    $i++;
                }
            } else {
                echo "<tr><td colspan='9' style='text-align:center;color:#999;padding:30px;'>No attendance records found</td></tr>";
            }
            ?>
        </table>
    </div>

    <!-- Detailed Report -->
    <div class="card">
        <h2>📋 Detailed Attendance Report</h2>
        <form method="GET" class="filter-bar no-print">
            <div class="fg">
                <label>Filter by Student</label>
                <select name="filter_student">
                    <option value="">All Students</option>
                    <?php
                    while ($s = mysqli_fetch_assoc($students)) {
                        $sel = ($filter_student == $s['id']) ? 'selected' : '';
                        echo "<option value='" . $s['id'] . "' " . $sel . ">" . $s['full_name'] . "</option>";
                    }
                    ?>
                </select>
            </div>
            <div class="fg">
                <label>Filter by Course</label>
                <select name="filter_course">
                    <option value="">All Courses</option>
                    <?php
                    while ($c = mysqli_fetch_assoc($courses)) {
                        $sel = ($filter_course == $c['id']) ? 'selected' : '';
                        echo "<option value='" . $c['id'] . "' " . $sel . ">" . $c['course_name'] . "</option>";
                    }
                    ?>
                </select>
            </div>
            <button type="submit" class="btn-filter">🔍 Filter</button>
            <a href="reports.php" class="btn-clear">✖ Clear</a>
            <button type="button" class="btn-print" onclick="window.print()">🖨️ Print Report</button>
        </form>

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
            if ($report && mysqli_num_rows($report) > 0) {
                while ($row = mysqli_fetch_assoc($report)) {
                    $status_class = 'status-' . strtolower($row['status']);
                    if ($row['status'] == 'Present') { $status_icon = '✅'; }
                    elseif ($row['status'] == 'Absent') { $status_icon = '❌'; }
                    else { $status_icon = '⏰'; }

                    echo "<tr>";
                    echo "<td>" . $i . "</td>";
                    echo "<td><strong>" . $row['sid'] . "</strong></td>";
                    echo "<td>" . $row['student_name'] . "</td>";
                    echo "<td>" . $row['course_name'] . " <small style='color:#999'>(" . $row['course_code'] . ")</small></td>";
                    echo "<td>" . $row['date'] . "</td>";
                    echo "<td><span class='" . $status_class . "'>" . $status_icon . " " . $row['status'] . "</span></td>";
                    echo "</tr>";
                    $i++;
                }
            } else {
                echo "<tr><td colspan='6' style='text-align:center;color:#999;padding:30px;'>No records found</td></tr>";
            }
            ?>
        </table>
    </div>
</div>

</body>
</html>