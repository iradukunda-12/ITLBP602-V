
<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$username = htmlspecialchars($_SESSION['username'] ?? 'User');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>LOLSchool Student Management System</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
          rel="stylesheet">

    <style>
        body {
            background-color: #f4f6f9;
            font-family: Arial, sans-serif;
        }

        .header {
            background-color: #12345a;
            color: white;
            padding: 25px;
        }

        .dashboard-card {
            border: none;
            border-radius: 12px;
            transition: 0.2s;
            height: 100%;
        }

        .dashboard-card:hover {
            transform: translateY(-4px);
        }

        .dashboard-card a {
            text-decoration: none;
        }
    </style>
</head>

<body>

<header class="header text-center">
    <h1>LOLSchool</h1>
    <p class="mb-0">Student Management System</p>
    <small>Nyamagabe, Rwanda</small>
</header>

<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>Dashboard</h2>

        <div>
            <span class="me-2">Welcome, <?= $username ?>!</span>
            <a href="logout.php" class="btn btn-danger btn-sm">Logout</a>
        </div>
    </div>

    <p>Welcome to LOLSchool Student Management System.</p>

    <div class="row g-4">

        <div class="col-md-4">
            <div class="card dashboard-card shadow-sm p-4">
                <h4>Students</h4>
                <p>Register, view and manage students.</p>
                <a href="students.php" class="btn btn-primary">Manage Students</a>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card dashboard-card shadow-sm p-4">
                <h4>Classes</h4>
                <p>View the available classes.</p>
                <a href="classes.php" class="btn btn-primary">Manage Classes</a>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card dashboard-card shadow-sm p-4">
                <h4>Subjects</h4>
                <p>View the subjects taught at school.</p>
                <a href="subjects.php" class="btn btn-primary">Manage Subjects</a>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card dashboard-card shadow-sm p-4">
                <h4>Grades</h4>
                <p>Enter and view student marks.</p>
                <a href="grades.php" class="btn btn-success">Manage Grades</a>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card dashboard-card shadow-sm p-4">
                <h4>Attendance</h4>
                <p>Record and view student attendance.</p>
                <a href="attendance.php" class="btn btn-success">Manage Attendance</a>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card dashboard-card shadow-sm p-4">
                <h4>Announcements</h4>
                <p>View school announcements.</p>
                <a href="announcements.php" class="btn btn-success">View Announcements</a>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card dashboard-card shadow-sm p-4">
                <h4>Teachers</h4>
                <p>View and manage teachers.</p>
                <a href="teachers.php" class="btn btn-info">Manage Teachers</a>
            </div>
        </div>

    </div>
</div>

<footer class="text-center py-3 text-muted">
    <p>&copy; 2026 LOLSchool Student Management System</p>
</footer>

</body>
</html>
