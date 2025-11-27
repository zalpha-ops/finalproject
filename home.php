<?php
session_start();
require 'db_connect.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'student') {
    echo "<div class='alert alert-danger'>Please login as a student.</div>";
    exit;
}

$userId = $_SESSION['user_id'];
$username = $_SESSION['username'];

// Get student stats
$coursesStmt = $pdo->query("SELECT COUNT(*) FROM courses");
$totalCourses = $coursesStmt->fetchColumn();

$gradesStmt = $pdo->prepare("
    SELECT COUNT(*) FROM grades g
    JOIN student_profiles sp ON g.student_id = sp.id
    JOIN users u ON sp.username = u.username
    WHERE u.id = ?
");
$gradesStmt->execute([$userId]);
$totalGrades = $gradesStmt->fetchColumn();

$avgStmt = $pdo->prepare("
    SELECT AVG(g.marks) FROM grades g
    JOIN student_profiles sp ON g.student_id = sp.id
    JOIN users u ON sp.username = u.username
    WHERE u.id = ? AND g.marks IS NOT NULL
");
$avgStmt->execute([$userId]);
$avgMarks = round($avgStmt->fetchColumn() ?? 0, 1);
?>

<div class="modern-home">
    <!-- Welcome Header -->
    <div class="welcome-header">
        <h2>Welcome back, <?= htmlspecialchars($username) ?>! 🎓</h2>
        <p class="text-muted">Ready to continue your learning journey?</p>
    </div>

    <!-- Quick Stats -->
    <div class="row g-4 mb-5">
        <div class="col-md-3">
            <div class="stat-card" onclick="loadPage('course.php')">
                <div class="stat-icon">📚</div>
                <div class="stat-value"><?= $totalCourses ?></div>
                <div class="stat-label">Available Courses</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card" onclick="loadPage('grade.php')">
                <div class="stat-icon">📊</div>
                <div class="stat-value"><?= $totalGrades ?></div>
                <div class="stat-label">Completed Assessments</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card" onclick="loadPage('progress.php')">
                <div class="stat-icon">📈</div>
                <div class="stat-value"><?= $avgMarks ?>%</div>
                <div class="stat-label">Average Score</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card" onclick="loadPage('announcement.php')">
                <div class="stat-icon">📢</div>
                <div class="stat-value">New</div>
                <div class="stat-label">Announcements</div>
            </div>
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="row g-4">
        <div class="col-md-6">
            <div class="action-card" onclick="loadPage('course.php')">
                <div class="action-icon">📘</div>
                <div class="action-content">
                    <h4>My Courses</h4>
                    <p>View and access your enrolled courses</p>
                </div>
                <div class="action-arrow">→</div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="action-card" onclick="loadPage('grade.php')">
                <div class="action-icon">📊</div>
                <div class="action-content">
                    <h4>My Grades</h4>
                    <p>Check your performance and results</p>
                </div>
                <div class="action-arrow">→</div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="action-card" onclick="loadPage('sheduled.php')">
                <div class="action-icon">📅</div>
                <div class="action-content">
                    <h4>Schedule</h4>
                    <p>View your class schedule and events</p>
                </div>
                <div class="action-arrow">→</div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="action-card" onclick="loadPage('progress.php')">
                <div class="action-icon">📈</div>
                <div class="action-content">
                    <h4>Progress Tracking</h4>
                    <p>Monitor your learning progress</p>
                </div>
                <div class="action-arrow">→</div>
            </div>
        </div>
    </div>
</div>

<style>
* {
    box-sizing: border-box;
}

.modern-home {
    padding: 20px;
    width: 100%;
    max-width: 1200px;
    margin: 0 auto;
}

.welcome-header {
    background: linear-gradient(135deg, #6B46C1 0%, #4C1D95 100%);
    color: white;
    padding: 40px;
    border-radius: 20px;
    margin-bottom: 30px;
    box-shadow: 0 10px 30px rgba(107, 70, 193, 0.3);
}

.welcome-header h2 {
    margin: 0;
    font-size: 2rem;
    font-weight: 700;
}

.welcome-header p {
    margin: 10px 0 0 0;
    opacity: 0.9;
    color: white;
}

.stat-card {
    background: white;
    padding: 25px;
    border-radius: 15px;
    text-align: center;
    box-shadow: 0 5px 15px rgba(0,0,0,0.1);
    transition: all 0.3s ease;
    cursor: pointer;
    border: 2px solid transparent;
    min-height: 150px;
    display: flex;
    flex-direction: column;
    justify-content: center;
}

.stat-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 15px 35px rgba(107, 70, 193, 0.3);
    border-color: #6B46C1;
}

.stat-icon {
    font-size: 3rem;
    margin-bottom: 15px;
}

.stat-value {
    font-size: 2.5rem;
    font-weight: 700;
    color: #6B46C1;
    margin-bottom: 5px;
}

.stat-label {
    color: #64748b;
    font-size: 0.9rem;
    font-weight: 500;
}

.action-card {
    background: white;
    padding: 30px;
    border-radius: 15px;
    display: flex;
    align-items: center;
    gap: 20px;
    box-shadow: 0 5px 15px rgba(0,0,0,0.1);
    transition: all 0.3s ease;
    cursor: pointer;
    border: 2px solid transparent;
}

.action-card:hover {
    transform: translateX(10px);
    box-shadow: 0 15px 35px rgba(107, 70, 193, 0.3);
    border-color: #6B46C1;
}

.action-icon {
    font-size: 3rem;
    flex-shrink: 0;
}

.action-content {
    flex-grow: 1;
}

.action-content h4 {
    margin: 0 0 5px 0;
    color: #1e293b;
    font-size: 1.3rem;
}

.action-content p {
    margin: 0;
    color: #64748b;
    font-size: 0.9rem;
}

.action-arrow {
    font-size: 2rem;
    color: #6B46C1;
    font-weight: bold;
    flex-shrink: 0;
}
</style>

<script>
function loadPage(page) {
    const mainContent = document.getElementById('main-content');
    if (mainContent) {
        fetch(page)
            .then(response => response.text())
            .then(html => {
                mainContent.innerHTML = html;
            })
            .catch(err => {
                console.error('Error loading page:', err);
                alert('Failed to load page. Please try again.');
            });
    } else {
        console.error('main-content element not found');
    }
}
</script>
