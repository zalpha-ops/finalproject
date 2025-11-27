<?php
session_start();
require 'db_connect.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'student') {
    echo "<div class='alert alert-danger'>Please login as a student.</div>";
    exit;
}

$courseId = $_GET['id'] ?? 0;
$userId = $_SESSION['user_id'];

// Get course details
$courseStmt = $pdo->prepare("SELECT * FROM courses WHERE course_id = ?");
$courseStmt->execute([$courseId]);
$course = $courseStmt->fetch(PDO::FETCH_ASSOC);

if (!$course) {
    echo "<div class='alert alert-danger'>Course not found.</div>";
    exit;
}

// Get student profile
$studentStmt = $pdo->prepare("
    SELECT sp.id, sp.name FROM student_profiles sp
    JOIN users u ON sp.username = u.username
    WHERE u.id = ?
");
$studentStmt->execute([$userId]);
$student = $studentStmt->fetch(PDO::FETCH_ASSOC);

// Get assignments for this course
$assignmentsStmt = $pdo->prepare("
    SELECT a.*, 
           s.file_path, s.submitted_at, s.grade, s.feedback, s.status as submission_status
    FROM assignments a
    LEFT JOIN assignment_submissions s ON a.id = s.assignment_id AND s.student_id = ?
    WHERE a.course_id = ?
    ORDER BY a.due_date DESC
");
$assignmentsStmt->execute([$student['id'] ?? 0, $courseId]);
$assignments = $assignmentsStmt->fetchAll(PDO::FETCH_ASSOC);

// Get course instructor - simplified query
$instructor = null;
try {
    $instructorStmt = $pdo->prepare("
        SELECT i.name, i.email 
        FROM instructors i
        JOIN course_instructors ci ON i.instructor_id = ci.instructor_id
        WHERE ci.course_id = ?
        LIMIT 1
    ");
    $instructorStmt->execute([$courseId]);
    $instructor = $instructorStmt->fetch(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    // If instructor query fails, just continue without instructor info
    $instructor = null;
}
?>

<div class="course-detail-container">
    <!-- Back Button -->
    <button class="back-btn" onclick="goBackToCourses()">
        ← Back to Courses
    </button>

    <!-- Course Header -->
    <div class="course-detail-header">
        <div class="course-detail-icon">📖</div>
        <div>
            <h1><?= htmlspecialchars($course['title']) ?></h1>
            <p><?= htmlspecialchars($course['description']) ?></p>
            <?php if ($instructor): ?>
                <div class="instructor-info">
                    <span>👨‍🏫 Instructor: <?= htmlspecialchars($instructor['name']) ?></span>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Course Content Tabs -->
    <div class="tabs">
        <button class="tab-btn active" onclick="showTab('overview', this)">Overview</button>
        <button class="tab-btn" onclick="showTab('assignments', this)">Assignments (<?= count($assignments) ?>)</button>
        <button class="tab-btn" onclick="showTab('materials', this)">Materials</button>
    </div>

    <!-- Overview Tab -->
    <div id="overview-tab" class="tab-content active">
        <div class="content-card">
            <h3>Course Overview</h3>
            <p><?= nl2br(htmlspecialchars($course['description'])) ?></p>
            
            <div class="course-stats">
                <div class="stat-item">
                    <div class="stat-icon">📝</div>
                    <div>
                        <div class="stat-value"><?= count($assignments) ?></div>
                        <div class="stat-label">Assignments</div>
                    </div>
                </div>
                <div class="stat-item">
                    <div class="stat-icon">✅</div>
                    <div>
                        <div class="stat-value"><?= count(array_filter($assignments, fn($a) => $a['submission_status'] === 'submitted')) ?></div>
                        <div class="stat-label">Submitted</div>
                    </div>
                </div>
                <div class="stat-item">
                    <div class="stat-icon">⏳</div>
                    <div>
                        <div class="stat-value"><?= count(array_filter($assignments, fn($a) => !$a['submission_status'])) ?></div>
                        <div class="stat-label">Pending</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Assignments Tab -->
    <div id="assignments-tab" class="tab-content">
        <?php if (empty($assignments)): ?>
            <div class="empty-state">
                <div class="empty-icon">📝</div>
                <h3>No Assignments Yet</h3>
                <p>Your instructor hasn't posted any assignments for this course.</p>
            </div>
        <?php else: ?>
            <div class="assignments-list">
                <?php foreach ($assignments as $assignment): ?>
                    <div class="assignment-card">
                        <div class="assignment-header">
                            <h4><?= htmlspecialchars($assignment['title']) ?></h4>
                            <?php if ($assignment['submission_status'] === 'submitted'): ?>
                                <span class="badge badge-success">✅ Submitted</span>
                            <?php elseif ($assignment['submission_status'] === 'graded'): ?>
                                <span class="badge badge-primary">📊 Graded</span>
                            <?php else: ?>
                                <span class="badge badge-warning">⏳ Pending</span>
                            <?php endif; ?>
                        </div>
                        
                        <p class="assignment-description"><?= nl2br(htmlspecialchars($assignment['description'])) ?></p>
                        
                        <div class="assignment-meta">
                            <span>📅 Due: <?= date('M d, Y', strtotime($assignment['due_date'])) ?></span>
                            <?php if ($assignment['grade']): ?>
                                <span>📊 Grade: <?= htmlspecialchars($assignment['grade']) ?></span>
                            <?php endif; ?>
                        </div>

                        <?php if ($assignment['submission_status'] === 'graded' && $assignment['feedback']): ?>
                            <div class="feedback-box">
                                <strong>Instructor Feedback:</strong>
                                <p><?= nl2br(htmlspecialchars($assignment['feedback'])) ?></p>
                            </div>
                        <?php endif; ?>

                        <?php if (!$assignment['submission_status']): ?>
                            <button class="submit-btn" onclick="showSubmitForm(<?= $assignment['id'] ?>)">
                                Submit Assignment
                            </button>
                        <?php elseif ($assignment['file_path']): ?>
                            <div class="submission-info">
                                <span>📎 Submitted: <?= date('M d, Y g:i A', strtotime($assignment['submitted_at'])) ?></span>
                            </div>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>

    <!-- Materials Tab -->
    <div id="materials-tab" class="tab-content">
        <div class="content-card">
            <h3>Course Materials</h3>
            <p>Course materials and resources will be available here.</p>
        </div>
    </div>
</div>

<!-- Assignment Submission Modal -->
<div id="submitModal" class="modal">
    <div class="modal-content">
        <span class="close" onclick="closeSubmitModal()">&times;</span>
        <h3>Submit Assignment</h3>
        <form id="submitForm" enctype="multipart/form-data">
            <input type="hidden" id="assignmentId" name="assignment_id">
            <input type="hidden" name="student_id" value="<?= $student['id'] ?? 0 ?>">
            <input type="hidden" name="course_id" value="<?= $courseId ?>">
            
            <div class="form-group">
                <label>Upload File:</label>
                <input type="file" name="assignment_file" required class="form-control">
                <small>Accepted formats: PDF, DOC, DOCX, ZIP (Max 10MB)</small>
            </div>
            
            <div class="form-group">
                <label>Comments (Optional):</label>
                <textarea name="comments" rows="3" class="form-control" placeholder="Add any comments..."></textarea>
            </div>
            
            <button type="submit" class="submit-btn">Submit Assignment</button>
        </form>
    </div>
</div>

<style>
* {
    box-sizing: border-box;
}

.course-detail-container {
    padding: 0;
    width: 100%;
    max-width: 100%;
    margin: 0;
}

.back-btn {
    background: white;
    border: 2px solid #6B46C1;
    color: #6B46C1;
    padding: 10px 20px;
    border-radius: 10px;
    font-weight: 600;
    cursor: pointer;
    margin-bottom: 20px;
    transition: all 0.3s ease;
}

.back-btn:hover {
    background: #6B46C1;
    color: white;
}

.course-detail-header {
    background: linear-gradient(135deg, #6B46C1 0%, #4C1D95 100%);
    color: white;
    padding: 40px;
    border-radius: 20px;
    margin-bottom: 30px;
    display: flex;
    gap: 20px;
    align-items: center;
    box-shadow: 0 10px 30px rgba(107, 70, 193, 0.3);
}

.course-detail-icon {
    font-size: 4rem;
}

.course-detail-header h1 {
    margin: 0 0 10px 0;
    font-size: 2.5rem;
}

.course-detail-header p {
    margin: 0 0 10px 0;
    opacity: 0.9;
    font-size: 1.1rem;
}

.instructor-info {
    opacity: 0.9;
    font-size: 0.95rem;
}

.tabs {
    display: flex;
    gap: 10px;
    margin-bottom: 20px;
    border-bottom: 2px solid #e2e8f0;
    position: relative;
    z-index: 10;
}

.tab-btn {
    background: none;
    border: none;
    padding: 15px 25px;
    font-size: 1rem;
    font-weight: 600;
    color: #64748b;
    cursor: pointer;
    border-bottom: 3px solid transparent;
    transition: all 0.3s ease;
    position: relative;
    z-index: 10;
}

.tab-btn.active {
    color: #6B46C1;
    border-bottom-color: #6B46C1;
}

.tab-btn:hover {
    color: #6B46C1;
    background: rgba(107, 70, 193, 0.1);
}

.tab-content {
    display: none;
}

.tab-content.active {
    display: block;
}

.content-card {
    background: white;
    padding: 30px;
    border-radius: 15px;
    box-shadow: 0 5px 15px rgba(0,0,0,0.1);
}

.content-card h3 {
    color: #1e293b;
    margin-bottom: 15px;
}

.course-stats {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 20px;
    margin-top: 30px;
}

.stat-item {
    display: flex;
    align-items: center;
    gap: 15px;
    padding: 20px;
    background: #f8fafc;
    border-radius: 12px;
    border-left: 4px solid #6B46C1;
}

.stat-item .stat-icon {
    font-size: 2.5rem;
}

.stat-value {
    font-size: 2rem;
    font-weight: 700;
    color: #6B46C1;
}

.stat-label {
    color: #64748b;
    font-size: 0.9rem;
}

.assignments-list {
    display: flex;
    flex-direction: column;
    gap: 20px;
}

.assignment-card {
    background: white;
    padding: 25px;
    border-radius: 15px;
    box-shadow: 0 5px 15px rgba(0,0,0,0.1);
    border-left: 4px solid #6B46C1;
}

.assignment-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 15px;
}

.assignment-header h4 {
    margin: 0;
    color: #1e293b;
}

.badge {
    padding: 6px 15px;
    border-radius: 20px;
    font-size: 0.85rem;
    font-weight: 600;
}

.badge-success {
    background: #10b981;
    color: white;
}

.badge-primary {
    background: #6B46C1;
    color: white;
}

.badge-warning {
    background: #f59e0b;
    color: white;
}

.assignment-description {
    color: #64748b;
    margin: 15px 0;
    line-height: 1.6;
}

.assignment-meta {
    display: flex;
    gap: 20px;
    color: #64748b;
    font-size: 0.9rem;
    margin: 15px 0;
}

.feedback-box {
    background: #f0fdf4;
    border-left: 4px solid #10b981;
    padding: 15px;
    border-radius: 8px;
    margin: 15px 0;
}

.feedback-box strong {
    color: #059669;
}

.feedback-box p {
    margin: 10px 0 0 0;
    color: #064e3b;
}

.submit-btn {
    background: linear-gradient(135deg, #6B46C1, #9333EA);
    color: white;
    border: none;
    padding: 12px 30px;
    border-radius: 10px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.3s ease;
}

.submit-btn:hover {
    transform: scale(1.05);
    box-shadow: 0 5px 15px rgba(107, 70, 193, 0.4);
}

.submission-info {
    color: #10b981;
    font-weight: 600;
    margin-top: 15px;
}

.modal {
    display: none;
    position: fixed;
    z-index: 1000;
    left: 0;
    top: 0;
    width: 100%;
    height: 100%;
    background: rgba(0,0,0,0.5);
}

.modal-content {
    background: white;
    margin: 5% auto;
    padding: 30px;
    border-radius: 15px;
    width: 90%;
    max-width: 600px;
    box-shadow: 0 20px 60px rgba(0,0,0,0.3);
}

.close {
    color: #aaa;
    float: right;
    font-size: 28px;
    font-weight: bold;
    cursor: pointer;
}

.close:hover {
    color: #000;
}

.form-group {
    margin-bottom: 20px;
}

.form-group label {
    display: block;
    margin-bottom: 8px;
    font-weight: 600;
    color: #1e293b;
}

.form-control {
    width: 100%;
    padding: 12px;
    border: 2px solid #e2e8f0;
    border-radius: 8px;
    font-size: 1rem;
}

.form-control:focus {
    outline: none;
    border-color: #6B46C1;
}

.empty-state {
    text-align: center;
    padding: 60px 20px;
    background: white;
    border-radius: 15px;
    box-shadow: 0 5px 15px rgba(0,0,0,0.1);
}

.empty-icon {
    font-size: 5rem;
    margin-bottom: 20px;
}
</style>

<script>
function showTab(tabName, element) {
    console.log('showTab called with:', tabName, element);
    
    // Remove active class from all tabs and buttons
    document.querySelectorAll('.tab-content').forEach(tab => tab.classList.remove('active'));
    document.querySelectorAll('.tab-btn').forEach(btn => btn.classList.remove('active'));
    
    // Add active class to selected tab and button
    const targetTab = document.getElementById(tabName + '-tab');
    console.log('Target tab:', targetTab);
    
    if (targetTab) {
        targetTab.classList.add('active');
    }
    
    if (element) {
        element.classList.add('active');
    } else {
        // Fallback: find the button by text content
        document.querySelectorAll('.tab-btn').forEach(btn => {
            if (btn.textContent.toLowerCase().includes(tabName)) {
                btn.classList.add('active');
            }
        });
    }
    
    console.log('Tab switched to:', tabName);
}

function showSubmitForm(assignmentId) {
    document.getElementById('assignmentId').value = assignmentId;
    document.getElementById('submitModal').style.display = 'block';
}

function closeSubmitModal() {
    document.getElementById('submitModal').style.display = 'none';
}

document.getElementById('submitForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    
    fetch('submit_assignment.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('Assignment submitted successfully!');
            closeSubmitModal();
            location.reload();
        } else {
            alert('Error: ' + data.message);
        }
    })
    .catch(err => {
        alert('Error submitting assignment');
        console.error(err);
    });
});

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
            });
    }
}

function goBackToCourses() {
    loadPage('course.php');
}
</script>
