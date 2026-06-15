<?php
session_start();
include '../includes/db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] != 'admin') {
    header("Location: ../login.php");
    exit();
}

// Add course
if (isset($_POST['add_course'])) {
    $course_code = mysqli_real_escape_string($conn, $_POST['course_code']);
    $course_name = mysqli_real_escape_string($conn, $_POST['course_name']);
    $teacher_id  = intval($_POST['teacher_id']);

    $check = mysqli_query($conn, "SELECT * FROM courses WHERE course_code='$course_code'");
    if (mysqli_num_rows($check) > 0) {
        $error = "Course code already exists!";
    } else {
        $sql = "INSERT INTO courses (course_code, course_name, teacher_id) VALUES ('$course_code','$course_name','$teacher_id')";
        if (mysqli_query($conn, $sql)) {
            $success = "Course added successfully!";
        } else {
            $error = "Error: " . mysqli_error($conn);
        }
    }
}

// Delete course
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    mysqli_query($conn, "DELETE FROM courses WHERE id=$id");
    header("Location: courses.php");
    exit();
}

$courses  = mysqli_query($conn, "SELECT c.*, u.full_name as teacher_name FROM courses c LEFT JOIN users u ON c.teacher_id=u.id ORDER BY c.id DESC");
$teachers = mysqli_query($conn, "SELECT * FROM users WHERE role='teacher'");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Courses - Attendance System</title>
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
        .sidebar a { display: block; color: rgba(255,255,255,0.8); text-decoration: none; padding: 12px 25px; font-size: 14px; transition: all 0.3s; border-left: 3px solid transparent; }
        .sidebar a:hover, .sidebar a.active { background: rgba(255,255,255,0.1); color: white; border-left-color: #00BFA5; }
        .sidebar a span { margin-right: 10px; }
        .main { margin-left: 250px; padding: 25px; }
        .topbar { background: white; padding: 15px 25px; border-radius: 10px; display: flex; justify-content: space-between; align-items: center; margin-bottom: 25px; box-shadow: 0 2px 10px rgba(0,0,0,0.05); }
        .topbar h1 { color: #0A2342; font-size: 20px; }
        .logout-btn { background: #ff5252; color: white; padding: 8px 18px; border-radius: 6px; text-decoration: none; font-size: 13px; }
        .card { background: white; border-radius: 12px; padding: 25px; box-shadow: 0 2px 10px rgba(0,0,0,0.06); margin-bottom: 25px; }
        .card h2 { color: #0A2342; font-size: 16px; margin-bottom: 20px; }
        .form-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 15px; margin-bottom: 15px; }
        .form-group label { display: block; font-size: 13px; font-weight: bold; color: #333; margin-bottom: 6px; }
        .form-group input, .form-group select { width: 100%; padding: 10px 12px; border: 2px solid #e0e0e0; border-radius: 8px; font-size: 13px; font-family: Tahoma, sans-serif; }
        .form-group input:focus, .form-group select:focus { outline: none; border-color: #1565C0; }
        .btn-add { background: #1565C0; color: white; padding: 10px 25px; border: none; border-radius: 8px; font-size: 14px; font-family: Tahoma, sans-serif; cursor: pointer; }
        .btn-add:hover { background: #0A2342; }
        table { width: 100%; border-collapse: collapse; font-size: 13px; }
        th { background: #0A2342; color: white; padding: 12px 15px; text-align: left; white-space: nowrap; }
        td { padding: 11px 15px; border-bottom: 1px solid #f0f0f0; vertical-align: middle; }
        tr:hover td { background: #f8f9fa; }
        .actions { display: flex; gap: 6px; align-items: center; }
        .btn-delete { background: #f44336; color: white; padding: 6px 14px; border-radius: 6px; text-decoration: none; font-size: 12px; white-space: nowrap; display: inline-block; }
        .btn-delete:hover { background: #b71c1c; }
        .badge-course { background: #e8f5e9; color: #2e7d32; padding: 4px 12px; border-radius: 20px; font-size: 11px; font-weight: bold; display: inline-block; }
        .success { background: #e8f5e9; color: #2e7d32; padding: 12px 15px; border-radius: 8px; margin-bottom: 15px; border-left: 4px solid #2e7d32; }
        .error   { background: #ffebee; color: #c62828; padding: 12px 15px; border-radius: 8px; margin-bottom: 15px; border-left: 4px solid #c62828; }
    </style>
</head>
<body>
<div class="sidebar">
    <div class="sidebar-logo">🎓<h2>Attendance System</h2><p>Limkwing University</p></div>
    <a href="dashboard.php"><span>📊</span> Dashboard</a>
    <a href="students.php"><span>👥</span> Students</a>
    <a href="teachers.php"><span>👨‍🏫</span> Teachers</a>
    <a href="courses.php" class="active"><span>📚</span> Courses</a>
    <a href="attendance.php"><span>✅</span> Attendance</a>
    <a href="reports.php"><span>📈</span> Reports</a>
    <a href="../login.php"><span>🚪</span> Logout</a>
</div>
<div class="main">
    <div class="topbar">
        <h1>📚 Manage Courses</h1>
        <div>
            <span style="color:#666;font-size:13px;">👤 <?php echo $_SESSION['user_name']; ?></span>
            &nbsp;&nbsp;
            <a href="../login.php" class="logout-btn">Logout</a>
        </div>
    </div>
    <div class="card">
        <h2>➕ Add New Course</h2>
        <?php if (isset($success)) echo "<div class='success'>✅ $success</div>"; ?>
        <?php if (isset($error))   echo "<div class='error'>❌ $error</div>"; ?>
        <form method="POST">
            <div class="form-grid">
                <div class="form-group">
                    <label>Course Code</label>
                    <input type="text" name="course_code" placeholder="e.g. ICT101" required>
                </div>
                <div class="form-group">
                    <label>Course Name</label>
                    <input type="text" name="course_name" placeholder="e.g. Introduction to Programming" required>
                </div>
                <div class="form-group">
                    <label>Assign Teacher</label>
                    <select name="teacher_id" required>
                        <option value="">Select Teacher</option>
                        <?php
                        while ($t = mysqli_fetch_assoc($teachers)) {
                            echo "<option value='{$t['id']}'>{$t['full_name']}</option>";
                        }
                        ?>
                    </select>
                </div>
            </div>
            <button type="submit" name="add_course" class="btn-add">➕ Add Course</button>
        </form>
    </div>
    <div class="card">
        <h2>📋 Courses List</h2>
        <table>
            <tr>
                <th>#</th>
                <th>Course Code</th>
                <th>Course Name</th>
                <th>Assigned Teacher</th>
                <th>Actions</th>
            </tr>
            <?php
            $i = 1;
            while ($row = mysqli_fetch_assoc($courses)) {
                echo "<tr>
                    <td>$i</td>
                    <td><span class='badge-course'>{$row['course_code']}</span></td>
                    <td><strong>{$row['course_name']}</strong></td>
                    <td>👨‍🏫 {$row['teacher_name']}</td>
                    <td>
                        <div class='actions'>
                            <a href='courses.php?delete={$row['id']}' class='btn-delete' onclick='return confirm(\"Delete this course?\")'>🗑️ Delete</a>
                        </div>
                    </td>
                </tr>";
                $i++;
            }
            if ($i == 1) echo "<tr><td colspan='5' style='text-align:center;color:#999;padding:30px;'>No courses found</td></tr>";
            ?>
        </table>
    </div>
</div>
</body>
</html>