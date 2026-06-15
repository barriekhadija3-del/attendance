<?php
session_start();
include '../includes/db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] != 'admin') {
    header("Location: ../login.php");
    exit();
}

// Add student
if (isset($_POST['add_student'])) {
    $student_id = mysqli_real_escape_string($conn, $_POST['student_id']);
    $full_name  = mysqli_real_escape_string($conn, $_POST['full_name']);
    $gender     = mysqli_real_escape_string($conn, $_POST['gender']);
    $class      = mysqli_real_escape_string($conn, $_POST['class']);
    $phone      = mysqli_real_escape_string($conn, $_POST['phone']);

    $sql = "INSERT INTO students (student_id, full_name, gender, class, phone) VALUES ('$student_id','$full_name','$gender','$class','$phone')";
    if (mysqli_query($conn, $sql)) {
        $success = "Student added successfully!";
    } else {
        $error = "Error: " . mysqli_error($conn);
    }
}

// Delete student
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    mysqli_query($conn, "DELETE FROM students WHERE id=$id");
    header("Location: students.php");
    exit();
}

// Search
$search = "";
if (isset($_GET['search'])) {
    $search = mysqli_real_escape_string($conn, $_GET['search']);
    $result = mysqli_query($conn, "SELECT * FROM students WHERE full_name LIKE '%$search%' OR student_id LIKE '%$search%' ORDER BY id DESC");
} else {
    $result = mysqli_query($conn, "SELECT * FROM students ORDER BY id DESC");
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Students - Attendance System</title>
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
            grid-template-columns: repeat(3, 1fr);
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

        .search-bar {
            display: flex;
            gap: 10px;
            margin-bottom: 20px;
        }
        .search-bar input {
            flex: 1;
            padding: 10px 15px;
            border: 2px solid #e0e0e0;
            border-radius: 8px;
            font-size: 13px;
            font-family: Tahoma, sans-serif;
        }
        .search-bar button {
            background: #1565C0;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-family: Tahoma, sans-serif;
        }

        /* Table */
        table { width: 100%; border-collapse: collapse; font-size: 13px; }
        th { background: #0A2342; color: white; padding: 12px 15px; text-align: left; white-space: nowrap; }
        td { padding: 11px 15px; border-bottom: 1px solid #f0f0f0; vertical-align: middle; }
        tr:hover td { background: #f8f9fa; }

        /* Gender badges */
        .badge {
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 11px;
            font-weight: bold;
            display: inline-block;
        }
        .badge-male   { background: #e3f2fd; color: #1565C0; }
        .badge-female { background: #fce4ec; color: #c62828; }

        /* Action buttons */
        .actions {
            display: flex;
            gap: 6px;
            align-items: center;
        }
        .btn-edit {
            background: #ff9800;
            color: white;
            padding: 6px 14px;
            border-radius: 6px;
            text-decoration: none;
            font-size: 12px;
            font-family: Tahoma, sans-serif;
            white-space: nowrap;
            display: inline-block;
        }
        .btn-edit:hover { background: #e65100; }
        .btn-delete {
            background: #f44336;
            color: white;
            padding: 6px 14px;
            border-radius: 6px;
            text-decoration: none;
            font-size: 12px;
            font-family: Tahoma, sans-serif;
            white-space: nowrap;
            display: inline-block;
        }
        .btn-delete:hover { background: #b71c1c; }

        /* Alerts */
        .success { background: #e8f5e9; color: #2e7d32; padding: 12px 15px; border-radius: 8px; margin-bottom: 15px; border-left: 4px solid #2e7d32; }
        .error   { background: #ffebee; color: #c62828; padding: 12px 15px; border-radius: 8px; margin-bottom: 15px; border-left: 4px solid #c62828; }
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
    <a href="students.php" class="active"><span>👥</span> Students</a>
    <a href="teachers.php"><span>👨‍🏫</span> Teachers</a>
    <a href="courses.php"><span>📚</span> Courses</a>
    <a href="attendance.php"><span>✅</span> Attendance</a>
    <a href="reports.php"><span>📈</span> Reports</a>
    <a href="../login.php"><span>🚪</span> Logout</a>
</div>

<!-- Main Content -->
<div class="main">
    <div class="topbar">
        <h1>👥 Manage Students</h1>
        <div>
            <span style="color:#666;font-size:13px;">👤 <?php echo $_SESSION['user_name']; ?></span>
            &nbsp;&nbsp;
            <a href="../login.php" class="logout-btn">Logout</a>
        </div>
    </div>

    <!-- Add Student Form -->
    <div class="card">
        <h2>➕ Add New Student</h2>
        <?php if (isset($success)) echo "<div class='success'>✅ $success</div>"; ?>
        <?php if (isset($error))   echo "<div class='error'>❌ $error</div>"; ?>
        <form method="POST">
            <div class="form-grid">
                <div class="form-group">
                    <label>Student ID</label>
                    <input type="text" name="student_id" placeholder="e.g. LU2024001" required>
                </div>
                <div class="form-group">
                    <label>Full Name</label>
                    <input type="text" name="full_name" placeholder="Enter full name" required>
                </div>
                <div class="form-group">
                    <label>Gender</label>
                    <select name="gender" required>
                        <option value="">Select Gender</option>
                        <option value="Male">Male</option>
                        <option value="Female">Female</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Class</label>
                    <input type="text" name="class" placeholder="e.g. ICT Year 2" required>
                </div>
                <div class="form-group">
                    <label>Phone</label>
                    <input type="text" name="phone" placeholder="e.g. 076123456">
                </div>
            </div>
            <button type="submit" name="add_student" class="btn-add">➕ Add Student</button>
        </form>
    </div>

    <!-- Students List -->
    <div class="card">
        <h2>📋 Students List</h2>
        <form method="GET" class="search-bar">
            <input type="text" name="search" placeholder="Search by name or student ID..." value="<?php echo htmlspecialchars($search); ?>">
            <button type="submit">🔍 Search</button>
            <a href="students.php" style="background:#666;color:white;padding:10px 15px;border-radius:8px;text-decoration:none;font-size:13px;">✖ Clear</a>
        </form>
        <table>
            <tr>
                <th>#</th>
                <th>Student ID</th>
                <th>Full Name</th>
                <th>Gender</th>
                <th>Class</th>
                <th>Phone</th>
                <th>Actions</th>
            </tr>
            <?php
            $i = 1;
            while ($row = mysqli_fetch_assoc($result)) {
                $badge = $row['gender'] == 'Male' ? 'badge-male' : 'badge-female';
                echo "<tr>
                    <td>$i</td>
                    <td><strong>{$row['student_id']}</strong></td>
                    <td>{$row['full_name']}</td>
                    <td><span class='badge $badge'>{$row['gender']}</span></td>
                    <td>{$row['class']}</td>
                    <td>{$row['phone']}</td>
                    <td>
                        <div class='actions'>
                            <a href='edit_student.php?id={$row['id']}' class='btn-edit'>✏️ Edit</a>
                            <a href='students.php?delete={$row['id']}' class='btn-delete' onclick='return confirm(\"Are you sure you want to delete this student?\")'>🗑️ Delete</a>
                        </div>
                    </td>
                </tr>";
                $i++;
            }
            if ($i == 1) echo "<tr><td colspan='7' style='text-align:center;color:#999;padding:30px;'>No students found</td></tr>";
            ?>
        </table>
    </div>
</div>

</body>
</html>