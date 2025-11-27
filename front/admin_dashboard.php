<?php
session_start();
if (!isset($_SESSION['admin_id'])) {
    header("Location: admin_login.php");
    exit;
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Admin Dashboard</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
</head>
<body class="bg-light">
<div class="container mt-5">
    <h2 class="mb-4">Admin Dashboard</h2>
    <div class="row g-4">
        <!-- Students Card -->
        <div class="col-md-4">
            <div class="card text-center shadow">
                <div class="card-body">
                    <h5 class="card-title">Students</h5>
                    <p class="card-text">Manage student records</p>
                    <a href="manage_students.php" class="btn btn-primary btn-sm">Go</a>
                </div>
            </div>
        </div>

        <!-- Instructors Card -->
        <div class="col-md-4">
            <div class="card text-center shadow">
                <div class="card-body">
                    <h5 class="card-title">Instructors</h5>
                    <p class="card-text">Manage instructor records</p>
                    <a href="manage_instructors.php" class="btn btn-primary btn-sm">Go</a>
                </div>
            </div>
        </div>

        <!-- Courses Card -->
        <div class="col-md-4">
            <div class="card text-center shadow">
                <div class="card-body">
                    <h5 class="card-title">Courses</h5>
                    <p class="card-text">Manage courses</p>
                    <a href="manage_courses.php" class="btn btn-primary btn-sm">Go</a>
                </div>
            </div>
        </div>

        <!-- Announcements Card -->
        <div class="col-md-4">
            <div class="card text-center shadow">
                <div class="card-body">
                    <h5 class="card-title">Announcements</h5>
                    <p class="card-text">Post updates for students and instructors</p>
                    <a href="manage_announcements.php" class="btn btn-primary btn-sm">Go</a>
                </div>
            </div>
        </div>

        <!-- Reports Card (optional placeholder) -->
        <div class="col-md-4">
            <div class="card text-center shadow">
                <div class="card-body">
                    <h5 class="card-title">Reports</h5>
                    <p class="card-text">View analytics and performance</p>
                    <a href="manage_reports.php" class="btn btn-primary btn-sm">Go</a>
                </div>
            </div>
        </div>

        <!-- Logs Card (optional placeholder) -->
        <div class="col-md-4">
            <div class="card text-center shadow">
                <div class="card-body">
                    <h5 class="card-title">Logs</h5>
                    <p class="card-text">Audit trail of admin actions</p>
                    <a href="manage_logs.php" class="btn btn-primary btn-sm">Go</a>
                </div>
            </div>
        </div>
    </div>

    <div class="mt-4">
        <a href="logout.php" class="btn btn-danger">Logout</a>
    </div>
</div>
</body>
</html>
