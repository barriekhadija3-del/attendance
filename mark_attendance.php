<?php
session_start();
include '../includes/db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] != 'teacher') {
    header("Location: ../login.php");
    exit();
}

$teacher_id = intval($_SESSION['user_id']);
$success = "";
$error   = "";

if (isset($_POST['mark_attendance'])) {
    $student_id = intval($_POST['student_id']);
    $course_id  = intval($_POST['course_id']);
    $date       = mysqli_real_escape_string($conn, $_POST['date']);
    $status     = mysqli_real_escape_string($conn, $_POST['status']);

    $check = mysqli_query($conn, "SELECT * FROM attendance WHERE student_id=$student_id AND course_id=$course_id AND date='$date'");
    if (mysqli_num_rows($check) > 0) {
        $error = "Attendance already marked for this student on this date!";
    } else {
        $sql = "INSERT INTO attendance (student_id, course_id, date, status, marked_by) VALUES ($student_id, $course_id, '$date', '$status', $teacher_id)";
        if (mysqli_query($conn, $sql)) {
            $success = "Attendance marked successfully!";
        } else {
            $error = "Error: " . mysqli_error($conn);
        }
    }
}

$students = mysqli_query($conn, "SELECT * FROM students ORDER BY full_name");
$courses  = mysqli_query($conn, "SELECT * FROM courses WHERE teacher_id=$teacher_id ORDER BY course_name");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Mark Attendance - Attendance System</title>
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
        .form-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 15px; margin-bottom: 15px; }
        .form-group label { display: block; font-size: 13px; font-weight: bold; color: #333; margin-bottom: 6px; }
        .form-group input, .form-group select { width: 100%; padding: 10px 12px; border: 2px solid #e0e0e0; border-radius: 8px; font-size: 13px; font-family: Tahoma, sans-serif; }
        .form-group input:focus, .form-group select:focus { outline: none; border-color: #1565C0; }
        .btn-add { background: #1565C0; color: white; padding: 10px 25px; border: none; border-radius: 8px; font-size: 14px; font-family: Tahoma, sans-serif; cursor: pointer; }
        .btn-add:hover { background: #0A2342; }
        .alert-success { background: #e8f5e9; color: #2e7d32; padding: 12px 15px; border-radius: 8px; margin-bottom: 15px; border-left: 4px solid #2e7d32; }
        .alert-error   { background: #ffebee; color: #c62828; padding: 12px 15px; border-radius: 8px; margin-bottom: 15px; border-left: 4px solid #c62828; }
    </style>
</head>
<body>
<div class="sidebar">
    <div class="sidebar-logo">🎓<h2>Attendance System</h2><p>Limkwing University</p></div>
    <a href="dashboard.php"><span>📊</span> Dashboard</a>
    <a href="mark_attendance.php" class="active"><span>✅</span> Mark Attendance</a>
    <a href="my_courses.php"><span>📚</span> My Courses</a>
    <a href="view_attendance.php"><span>📋</span> View Attendance</a>
    <a href="../login.php"><span>🚪</span> Logout</a>
</div>
<div class="main">
    <div class="topbar">
        <h1>✅ Mark Attendance</h1>
        <div>
            <span style="color:#666;font-size:13px;">👨‍🏫 <?php echo $_SESSION['user_name']; ?></span>
            &nbsp;&nbsp;
            <a href="../login.php" class="logout-btn">Logout</a>
        </div>
    </div>
    <div class="card">
        <h2>➕ Mark Student Attendance</h2>
        <?php if ($success != "") echo "<div class='alert-success'>✅ $success</div>"; ?>
        <?php if ($error   != "") echo "<div class='alert-error'>❌ $error</div>"; ?>
        <form method="POST">
            <div class="form-grid">
                <div class="form-group">
                    <label>Select Student</label>
                    <select name="student_id" required>
                        <option value="">-- Select Student --</option>
                        <?php while ($s = mysqli_fetch_assoc($students)) {
                            echo "<option value='{$s['id']}'>{$s['full_name']} ({$s['student_id']})</option>";
                        } ?>
                    </select>
                </div>
                <div class="form-group">
                    <label>Select Course</label>
                    <select name="course_id" required>
                        <option value="">-- Select Course --</option>
                        <?php while ($c = mysqli_fetch_assoc($courses)) {
                            echo "<option value='{$c['id']}'>{$c['course_name']} ({$c['course_code']})</option>";
                        } ?>
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
</div>
</body>
</html>