<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Attendance Management System</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: Tahoma, sans-serif;
            background: linear-gradient(135deg, #0A2342 0%, #1565C0 50%, #00695C 100%);
            min-height: 100vh;
        }

        /* NAVBAR */
        .navbar {
            background: rgba(0,0,0,0.3);
            padding: 15px 40px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            backdrop-filter: blur(10px);
        }
        .navbar .logo {
            color: white;
            font-size: 18px;
            font-weight: bold;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .navbar .nav-links { display: flex; gap: 15px; }
        .navbar .nav-links a {
            color: rgba(255,255,255,0.85);
            text-decoration: none;
            padding: 8px 20px;
            border-radius: 25px;
            font-size: 13px;
            transition: all 0.3s;
            border: 1px solid rgba(255,255,255,0.2);
        }
        .navbar .nav-links a:hover { background: rgba(255,255,255,0.15); color: white; }
        .navbar .nav-links a.register-btn {
            background: #00BFA5;
            border-color: #00BFA5;
            color: white;
            font-weight: bold;
        }
        .navbar .nav-links a.register-btn:hover { background: #00897B; }

        /* HERO */
        .hero {
            text-align: center;
            padding: 80px 20px 60px;
            color: white;
        }
        .hero .badge {
            display: inline-block;
            background: rgba(255,255,255,0.15);
            color: #00BFA5;
            padding: 6px 20px;
            border-radius: 25px;
            font-size: 12px;
            font-weight: bold;
            margin-bottom: 20px;
            border: 1px solid rgba(0,191,165,0.4);
        }
        .hero h1 {
            font-size: 48px;
            font-weight: bold;
            margin-bottom: 15px;
            line-height: 1.2;
        }
        .hero h1 span { color: #00BFA5; }
        .hero p {
            font-size: 16px;
            color: rgba(255,255,255,0.8);
            max-width: 600px;
            margin: 0 auto 40px;
            line-height: 1.6;
        }

        /* LOGIN CARDS */
        .login-cards {
            display: flex;
            justify-content: center;
            gap: 25px;
            padding: 0 20px 60px;
            flex-wrap: wrap;
        }
        .login-card {
            background: white;
            border-radius: 20px;
            padding: 35px 30px;
            width: 280px;
            text-align: center;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
            transition: transform 0.3s;
        }
        .login-card:hover { transform: translateY(-8px); }
        .login-card .card-icon {
            font-size: 50px;
            margin-bottom: 15px;
        }
        .login-card h3 {
            font-size: 20px;
            color: #0A2342;
            margin-bottom: 8px;
        }
        .login-card p {
            font-size: 13px;
            color: #666;
            margin-bottom: 25px;
            line-height: 1.5;
        }
        .login-card .card-btn {
            display: block;
            padding: 12px;
            border-radius: 10px;
            text-decoration: none;
            font-size: 14px;
            font-weight: bold;
            font-family: Tahoma, sans-serif;
            transition: all 0.3s;
        }
        .btn-admin { background: #0A2342; color: white; }
        .btn-admin:hover { background: #1565C0; }
        .btn-teacher { background: #00695C; color: white; }
        .btn-teacher:hover { background: #00897B; }
        .btn-student { background: #00BFA5; color: white; }
        .btn-student:hover { background: #00897B; }
        .btn-register {
            display: block;
            padding: 10px;
            border-radius: 10px;
            text-decoration: none;
            font-size: 13px;
            font-family: Tahoma, sans-serif;
            margin-top: 10px;
            border: 2px solid #00BFA5;
            color: #00695C;
            font-weight: bold;
        }
        .btn-register:hover { background: #E0F2F1; }

        /* STATS */
        .stats {
            display: flex;
            justify-content: center;
            gap: 40px;
            padding: 40px 20px;
            background: rgba(0,0,0,0.2);
            flex-wrap: wrap;
        }
        .stat-item { text-align: center; color: white; }
        .stat-item .stat-num {
            font-size: 36px;
            font-weight: bold;
            color: #00BFA5;
        }
        .stat-item .stat-label {
            font-size: 13px;
            color: rgba(255,255,255,0.7);
            margin-top: 5px;
        }

        /* FEATURES */
        .features {
            padding: 60px 40px;
            max-width: 1100px;
            margin: 0 auto;
        }
        .features h2 {
            text-align: center;
            color: white;
            font-size: 28px;
            margin-bottom: 40px;
        }
        .features-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 20px;
        }
        .feature-item {
            background: rgba(255,255,255,0.1);
            border-radius: 15px;
            padding: 25px;
            text-align: center;
            color: white;
            border: 1px solid rgba(255,255,255,0.15);
            backdrop-filter: blur(5px);
        }
        .feature-item .f-icon { font-size: 36px; margin-bottom: 12px; }
        .feature-item h4 { font-size: 15px; margin-bottom: 8px; }
        .feature-item p { font-size: 12px; color: rgba(255,255,255,0.7); line-height: 1.5; }

        /* FOOTER */
        .footer {
            text-align: center;
            padding: 25px;
            color: rgba(255,255,255,0.5);
            font-size: 12px;
            background: rgba(0,0,0,0.3);
        }
    </style>
</head>
<body>

<!-- NAVBAR -->
<div class="navbar">
    <div class="logo">🎓 Attendance System</div>
    <div class="nav-links">
        <a href="user/register.php" class="register-btn">📝 Register</a>
        <a href="user/login.php">👤 Student Login</a>
        <a href="login.php">👑 Admin/Teacher</a>
    </div>
</div>

<!-- HERO -->
<div class="hero">
    <div class="badge">🏫 Limkwing University Sierra Leone</div>
    <h1>Student <span>Attendance</span><br>Management System</h1>
    <p>A modern web-based system to track, manage and report student attendance. Built for Limkwing University by BICT Group-1.</p>
</div>

<!-- LOGIN CARDS -->
<div class="login-cards">
    <div class="login-card">
        <div class="card-icon">👑</div>
        <h3>Admin Login</h3>
        <p>Full system control — manage students, teachers, courses and attendance records</p>
        <a href="admin/dashboard.php" class="card-btn btn-admin">Login as Admin</a>
    </div>
    <div class="login-card">
        <div class="card-icon">👨‍🏫</div>
        <h3>Teacher Login</h3>
        <p>Mark attendance for your assigned courses and view student records</p>
        <a href="teacher/dashboard.php" class="card-btn btn-teacher">Login as Teacher</a>
    </div>
    <div class="login-card">
        <div class="card-icon">🎓</div>
        <h3>Student Portal</h3>
        <p>View your attendance records, courses and check your attendance percentage</p>
        <a href="user/login.php" class="card-btn btn-student">Student Login</a>
        <a href="user/register.php" class="btn-register">📝 Register Here</a>
    </div>
</div>

<!-- STATS -->
<div class="stats">
    <div class="stat-item">
        <div class="stat-num">3</div>
        <div class="stat-label">User Roles</div>
    </div>
    <div class="stat-item">
        <div class="stat-num">100%</div>
        <div class="stat-label">Web Based</div>
    </div>
    <div class="stat-item">
        <div class="stat-num">16</div>
        <div class="stat-label">Features</div>
    </div>
    <div class="stat-item">
        <div class="stat-num">PHP</div>
        <div class="stat-label">Technology</div>
    </div>
    <div class="stat-item">
        <div class="stat-num">MySQL</div>
        <div class="stat-label">Database</div>
    </div>
</div>

<!-- FEATURES -->
<div class="features">
    <h2>✨ System Features</h2>
    <div class="features-grid">
        <div class="feature-item">
            <div class="f-icon">✅</div>
            <h4>Mark Attendance</h4>
            <p>Teachers mark Present, Absent or Late for each student per course</p>
        </div>
        <div class="feature-item">
            <div class="f-icon">📊</div>
            <h4>Live Reports</h4>
            <p>View attendance percentage and progress bars for every student</p>
        </div>
        <div class="feature-item">
            <div class="f-icon">🔐</div>
            <h4>Secure Login</h4>
            <p>Role-based access for Admin, Teacher and Student users</p>
        </div>
        <div class="feature-item">
            <div class="f-icon">👥</div>
            <h4>Student Management</h4>
            <p>Add, edit, delete and search student records easily</p>
        </div>
        <div class="feature-item">
            <div class="f-icon">📚</div>
            <h4>Course Management</h4>
            <p>Create courses and assign teachers to specific courses</p>
        </div>
        <div class="feature-item">
            <div class="f-icon">🖨️</div>
            <h4>Print Reports</h4>
            <p>Print attendance reports directly from the browser</p>
        </div>
    </div>
</div>

<!-- FOOTER -->
<div class="footer">
    © 2026 Student Attendance Management System | Limkwing University Sierra Leone | BICT Group-1
</div>

</body>
</html>