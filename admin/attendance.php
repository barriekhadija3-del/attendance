<?php
session_start();
include '../includes/db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] != 'admin') {
    header("Location: ../login.php");
    exit();
}

$success = "";
$error   = "";

// Mark attendance
if (isset($_POST['mark_attendance'])) {
    $student_id = intval($_POST['student_id']);
    $course_id  = intval($_POST['course_id']);
    $date       = mysqli_real_escape_string($conn, $_POST['date']);
    $status     = mysqli_real_escape_string($conn, $_POST['status']);
    $marked_by  = intval($_SESSION['user_id']);

    $check = mysqli_query($conn, "SELECT * FROM attendance WHERE student_id=$student_id AND course_id=$course_id AND date='$date'");
    if (mysqli_num_rows($check) > 0) {
        $error = "Attendance already marked for this student on this date!";
    } else {
        $sql = "INSERT INTO attendance (student_id, course_id, date, status, marked_by) VALUES ($student_id, $course_id, '$date', '$status', $marked_by)";
        if (mysqli_query($conn, $sql)) {
            $success = "Attendance marked successfully!";
        } else {
            $error = "Error: " . mysqli_error($conn);
        }
    }
}

// Delete attendance
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    mysqli_query($conn, "DELETE FROM attendance WHERE id=$id");
    header("Location: attendance.php");
    exit();
}

// Filter
$where = "WHERE 1=1";
$filter_date   = "";
$filter_course = "";

if (isset($_GET['filter_date']) && $_GET['filter_date'] != '') {
    $filter_date = mysqli_real_escape_string($conn, $_GET['filter_date']);
    $where .= " AND a.date='$filter_date'";
}
if (isset($_GET['filter_course']) && $_GET['filter_course'] != '') {
    $filter_course = intval($_GET['filter_course']);
    $where .= " AND a.course_id=$filter_course";
}

$attendance_sql = "SELECT a.*, s.full_name as student_name, s.student_id as sid,
                   c.course_name, c.course_code, u.full_name as marked_by_name
                   FROM attendance a
                   JOIN students s ON a.student_id = s.id
                   JOIN courses c  ON a.course_id  = c.id
                   JOIN users u    ON a.marked_by  = u.id
                   $where
                   ORDER BY a.date DESC, a.id DESC";

$attendance = mysqli_query($conn, $attendance_sql);
$students   = mysqli_query($conn, "SELECT * FROM students ORDER BY full_name");
$courses    = mysqli_query($conn, "SELECT * FROM courses ORDER BY course_name");
$courses2   = mysqli_query($conn, "SELECT * FROM courses ORDER BY course_name");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Attendance - Attendance System</title>
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
        .sidebar-logo {
            text-align: center;
            padding: 20px;
            border-bottom: 1px solid rgba(255,255,255,0.1);
            margin-bottom: 20px;
        }
        .sidebar-logo h2 { color: white; font-size: 16px; margin-top: 8px; }
        .sidebar-logo p  { color: rgba(255,255,255,0.6); font-size: 11px; }
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

        .main { margin-left: 250px; padding: 25px; }

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
        .logout-btn {
            background: #ff5252;
            color: white;
            padding: 8px 18px;
            border-radius: 6px;
            text-decoration: none;
            font-size: 13px;
        }

        .card {
            background: white;
            border-radius: 12px;
            padding: 25px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.06);
            margin-bottom: 25px;
        }
        .card h2 { color: #0A2342; font-size: 16px; margin-bottom: 20px; }

        .form-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 15px;
            margin-bottom: 15px;
        }
        .form-group label {
            display: block;
            font-size: 13px;
            font-weight: bold;
            color: #333;
            margin-bottom: 6px;
        }
        .form-group input,
        .form-group select {
            width: 100%;
            padding: 10px 12px;
            border: 2px solid #e0e0e0;
            border-radius: 8px;
            font-size: 13px;
            font-family: Tahoma, sans-serif;
        }
        .form-group input:focus,
        .form-group select:focus {
            outline: none;
            border-color: #1565C0;
        }

        .btn-add {
            background: #1565C0;
            color: white;
            padding: 10px 25px;
            border: none;
            border-radius: 8px;
            font-size: 14px;
            font-family: Tahoma, sans-serif;
            cursor: pointer;
        }
        .btn-add:hover { background: #0A2342; }

        .filter-bar {
            display: flex;
            gap: 10px;
            margin-bottom: 20px;
            align-items: flex-end;
            flex-wrap: wrap;
        }
        .filter-bar .fg label {
            display: block;
            font-size: 12px;
            font-weight: bold;
            color: #333;
            margin-bottom: 5px;
        }
        .filter-bar .fg input,
        .filter-bar .fg select {
            padding: 10px 12px;
            border: 2px solid #e0e0e0;
            border-radius: 8px;
            font-size: 13px;
            font-family: Tahoma, sans-serif;
        }
        .btn-filter {
            background: #1565C0;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-family: Tahoma, sans-serif;
            font-size: 13px;
        }
        .btn-clear {
            background: #666;
            color: white;
            padding: 10px 15px;
            border-radius: 8px;
            text-decoration: none;
            font-size: 13px;
        }

        table { width: 100%; border-collapse: collapse; font-size: 13px; }
        th {
            background: #0A2342;
            color: white;
            padding: 12px 15px;
            text-align: left;
            white-space: nowrap;
        }
        td {
            padding: 11px 15px;
            border-bottom: 1px solid #f0f0f0;
            vertical-align: middle;
        }
        tr:hover td { background: #f8f9fa; }

        .actions { display: flex; gap: 6px; }
        .btn-delete {
            background: #f44336;
            color: white;
            padding: 6px 14px;
            border-radius: 6px;
            text-decoration: none;
            font-size: 12px;
            white-space: nowrap;
            display: inline-block;
        }
        .btn-delete:hover { background: #b71c1c; }

        .status-present { background: #e8f5e9; color: #2e7d32; padding: 4px 12px; border-radius: 20px; font-size: 11px; font-weight: bold; display: inline-block; }
        .status-absent  { background: #ffebee; color: #c62828; padding: 4px 12px; border-radius: 20px; font-size: 11px; font-weight: bold; display: inline-block; }
        .status-late    { background: #fff8e1; color: #f57f17; padding: 4px 12px; border-radius: 20px; font-size: 11px; font-weight: bold; display: inline-block; }

        .alert-success { background: #e8f5e9; color: #2e7d32; padding: 12px 15px; border-radius: 8px; margin-bottom: 15px; border-left: 4px solid #2e7d32; }
        .alert-error   { background: #ffebee; color: #c62828; padding: 12px 15px; border-radius: 8px; margin-bottom: 15px; border-left: 4px solid #c62828; }
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
    <a href="dashboard.php"><span>📊</span> Dashboard</a>
    <a href="students.php"><span>👥</span> Students</a>
    <a href="teachers.php"><span>👨‍🏫</span> Teachers</a>
    <a href="courses.php"><span>📚</span> Courses</a>
    <a href="attendance.php" class="active"><span>✅</span> Attendance</a>
    <a href="reports.php"><span>📈</span> Reports</a>
    <a href="../login.php"><span>🚪</span> Logout</a>
</div>

<!-- Main Content -->
<div class="main">
    <div class="topbar">
        <h1>✅ Manage Attendance</h1>
        <div>
            <span style="color:#666;font-size:13px;">👤 <?php echo $_SESSION['user_name']; ?></span>
            &nbsp;&nbsp;
            <a href="../login.php" class="logout-btn">Logout</a>
        </div>
    </div>

    <!-- Mark Attendance Form -->
    <div class="card">
        <h2>➕ Mark Attendance</h2>
        <?php if ($success != "") echo "<div class='alert-success'>✅ $success</div>"; ?>
        <?php if ($error   != "") echo "<div class='alert-error'>❌ $error</div>"; ?>
        <form method="POST">
            <div class="form-grid">
                <div class="form-group">
                    <label>Select Student</label>
                    <select name="student_id" required>
                        <option value="">-- Select Student --</option>
                        <?php
                        while ($s = mysqli_fetch_assoc($students)) {
                            echo "<option value='{$s['id']}'>{$s['full_name']} ({$s['student_id']})</option>";
                        }
                        ?>
                    </select>
                </div>
                <div class="form-group">
                    <label>Select Course</label>
                    <select name="course_id" required>
                        <option value="">-- Select Course --</option>
                        <?php
                        while ($c = mysqli_fetch_assoc($courses)) {
                            echo "<option value='{$c['id']}'>{$c['course_name']} ({$c['course_code']})</option>";
                        }
                        ?>
                    </select>
                </div>
                <div class="form-group">
                    <label>Date</label>
                    <input type="date" name="date" value="<?php echo date('Y-m-d'); ?>" required>
                </div>
                <div class="form-group">
                    <label>Status</label>
                    <select name="status" required>
                        <option value="Present">✅ Present</option>
                        <option value="Absent">❌ Absent</option>
                        <option value="Late">⏰ Late</option>
                    </select>
                </div>
            </div>
            <button type="submit" name="mark_attendance" class="btn-add">✅ Mark Attendance</button>
        </form>
    </div>

    <!-- Attendance Records -->
    <div class="card">
        <h2>📋 Attendance Records</h2>

        <!-- Filter Bar -->
        <form method="GET" class="filter-bar">
            <div class="fg">
                <label>Filter by Date</label>
                <input type="date" name="filter_date" value="<?php echo $filter_date; ?>">
            </div>
            <div class="fg">
                <label>Filter by Course</label>
                <select name="filter_course">
                    <option value="">All Courses</option>
                    <?php
                    while ($c = mysqli_fetch_assoc($courses2)) {
                        $sel = ($filter_course == $c['id']) ? 'selected' : '';
                        echo "<option value='{$c['id']}' $sel>{$c['course_name']}</option>";
                    }
                    ?>
                </select>
            </div>
            <button type="submit" class="btn-filter">🔍 Filter</button>
            <a href="attendance.php" class="btn-clear">✖ Clear</a>
        </form>

        <table>
            <tr>
                <th>#</th>
                <th>Student ID</th>
                <th>Student Name</th>
                <th>Course</th>
                <th>Date</th>
                <th>Status</th>
                <th>Marked By</th>
                <th>Actions</th>
            </tr>
            <?php
            $i = 1;
            if ($attendance && mysqli_num_rows($attendance) > 0) {
                while ($row = mysqli_fetch_assoc($attendance)) {
                    $status_class = 'status-' . strtolower($row['status']);
                    $status_icon  = $row['status'] == 'Present' ? '✅' : ($row['status'] == 'Absent' ? '❌' : '⏰');
                    echo "<tr>
                        <td>$i</td>
                        <td><strong>{$row['sid']}</strong></td>
                        <td>{$row['student_name']}</td>
                        <td>{$row['course_name']} <small style='color:#999'>({$row['course_code']})</small></td>
                        <td>{$row['date']}</td>
                        <td><span class='$status_class'>$status_icon {$row['status']}</span></td>
                        <td>{$row['marked_by_name']}</td>
                        <td>
                            <div class='actions'>
                                <a href='attendance.php?delete={$row['id']}' class='btn-delete' onclick='return confirm(\"Delete this record?\")'>🗑️ Delete</a>
                            </div>
                        </td>
                    </tr>";
                    $i++;
                }
            } else {
                echo "<tr><td colspan='8' style='text-align:center;color:#999;padding:30px;'>No attendance records found</td></tr>";
            }
            ?>
        </table>
    </div>
</div>

</body>
</html>