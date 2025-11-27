<?php
// grade.php — Grades section for Eagle Flight School
session_start();
require_once 'db_connect.php';

// Get logged-in user info
$user_id = $_SESSION['user_id'] ?? null;
$username = $_SESSION['username'] ?? null;

if (!$user_id) {
    echo '<div class="alert alert-danger">Please log in to view your grades.</div>';
    exit;
}

// Find the student record ID from student_profiles table
$student_id = null;

// Get student profile by username
$stmt = $pdo->prepare("
    SELECT sp.id 
    FROM student_profiles sp
    JOIN users u ON sp.username = u.username
    WHERE u.id = ?
");
$stmt->execute([$user_id]);
$studentRecord = $stmt->fetch(PDO::FETCH_ASSOC);

if ($studentRecord) {
    $student_id = $studentRecord['id'];
}

if (!$student_id) {
    echo '<div class="alert alert-warning">';
    echo '<strong>No student profile found.</strong><br>';
    echo 'Your login account exists, but you need to complete your student profile.<br>';
    echo 'Username: ' . htmlspecialchars($username) . '<br>';
    echo '<a href="edit_user_profile.php" class="btn btn-sm btn-primary mt-2">Complete Profile</a>';
    echo '</div>';
    exit;
}

// Fetch grades joined with courses
$sql = "
    SELECT g.id, g.student_id, g.course_id,
           c.title AS course_name, c.description, c.created_at, c.updated_at,
           g.grade, g.status, g.exam_date, g.marks, g.comment
    FROM grades g
    INNER JOIN courses c ON g.course_id = c.course_id
    WHERE g.student_id = ?
    ORDER BY g.exam_date DESC
";
$stmt = $pdo->prepare($sql);
$stmt->execute([$student_id]);
$grades = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Calculate stats
$totalMarks = 0; $marksCount = 0; $lastExam = null; $lowGrades = [];
foreach ($grades as $g) {
    if (is_numeric($g['marks'])) {
        $totalMarks += $g['marks'];
        $marksCount++;
        if ($g['marks'] < 70) $lowGrades[] = $g;
    }
    if (!$lastExam) $lastExam = $g;
}
$averageMarks = $marksCount > 0 ? round($totalMarks / $marksCount, 1) : 0;
$avgColor = $averageMarks >= 80 ? 'success' : ($averageMarks >= 70 ? 'warning' : 'danger');

// Calculate pass rate
$passedCount = count(array_filter($grades, fn($g) => $g['status'] === 'Pass'));
$passRate = count($grades) > 0 ? round(($passedCount / count($grades)) * 100, 1) : 0;
?>

<div class="grades-container">
    <!-- Page Header -->
    <div class="grades-header">
        <h2 class="mb-2">📊 My Grades</h2>
        <p class="mb-0 opacity-75">Track your academic performance and course results</p>
    </div>

    <?php if (count($grades) === 0): ?>
        <div class="empty-state-grades">
            <div class="empty-icon-grades">📚</div>
            <h3>No Grades Yet</h3>
            <p>Your grades will appear here once your instructor has entered them.</p>
            <p class="text-muted">Keep working hard and check back soon!</p>
        </div>
    <?php else: ?>

    <!-- Stats Cards -->
    <div class="row g-3 mb-4">
        <div class="col-lg-3 col-md-6">
            <div class="stat-card-grade stat-primary">
                <div class="stat-icon-grade">🎯</div>
                <div class="stat-content-grade">
                    <h3><?= $averageMarks ?>%</h3>
                    <p>Overall Average</p>
                    <small>
                        <?php if ($averageMarks >= 80): ?>
                            Excellent performance! 🌟
                        <?php elseif ($averageMarks >= 70): ?>
                            Good progress! 👍
                        <?php elseif ($averageMarks >= 60): ?>
                            Keep improving! 📚
                        <?php else: ?>
                            Need more practice 💪
                        <?php endif; ?>
                    </small>
                </div>
            </div>
        </div>

        <div class="col-lg-3 col-md-6">
            <div class="stat-card-grade stat-success">
                <div class="stat-icon-grade">✅</div>
                <div class="stat-content-grade">
                    <h3><?= $passRate ?>%</h3>
                    <p>Pass Rate</p>
                    <small><?= $passedCount ?> of <?= count($grades) ?> courses passed</small>
                </div>
            </div>
        </div>

        <div class="col-lg-3 col-md-6">
            <div class="stat-card-grade stat-info">
                <div class="stat-icon-grade">📝</div>
                <div class="stat-content-grade">
                    <h3><?= count($grades) ?></h3>
                    <p>Total Courses</p>
                    <small>Graded courses</small>
                </div>
            </div>
        </div>

        <div class="col-lg-3 col-md-6">
            <div class="stat-card-grade <?= count($lowGrades) > 0 ? 'stat-warning' : 'stat-success' ?>">
                <div class="stat-icon-grade"><?= count($lowGrades) > 0 ? '⚠️' : '🎉' ?></div>
                <div class="stat-content-grade">
                    <h3><?= count($lowGrades) ?></h3>
                    <p>Below 70%</p>
                    <small><?= count($lowGrades) > 0 ? 'Needs attention' : 'All good!' ?></small>
                </div>
            </div>
        </div>
    </div>

    <!-- Last Exam Highlight -->
    <?php if ($lastExam): ?>
    <div class="last-exam-card">
        <div class="last-exam-icon">🎓</div>
        <div class="last-exam-content">
            <h4>Latest Result</h4>
            <h3><?= htmlspecialchars($lastExam['course_name']) ?></h3>
            <div class="last-exam-details">
                <span class="exam-grade grade-<?= strtolower($lastExam['grade']) ?>"><?= htmlspecialchars($lastExam['grade']) ?></span>
                <span class="exam-marks"><?= htmlspecialchars($lastExam['marks']) ?>%</span>
                <span class="exam-date">📅 <?= $lastExam['exam_date'] ? date('M d, Y', strtotime($lastExam['exam_date'])) : 'N/A' ?></span>
            </div>
            <?php if ($lastExam['comment']): ?>
                <p class="exam-comment">"<?= htmlspecialchars($lastExam['comment']) ?>"</p>
            <?php endif; ?>
        </div>
    </div>
    <?php endif; ?>

    <!-- Detailed Grades -->
    <div class="grades-section">
        <h3 class="section-title-grade">📋 Course Grades</h3>

        
        <div class="grades-grid">
            <?php foreach ($grades as $g): ?>
                <?php
                    $markColor = 'secondary';
                    $markClass = 'grade-d';
                    if (is_numeric($g['marks'])) {
                        if ($g['marks'] >= 80) {
                            $markColor = 'success';
                            $markClass = 'grade-a';
                        } elseif ($g['marks'] >= 70) {
                            $markColor = 'info';
                            $markClass = 'grade-b';
                        } elseif ($g['marks'] >= 60) {
                            $markColor = 'primary';
                            $markClass = 'grade-c';
                        } elseif ($g['marks'] >= 50) {
                            $markColor = 'warning';
                            $markClass = 'grade-d';
                        } else {
                            $markColor = 'danger';
                            $markClass = 'grade-f';
                        }
                    }
                    
                    $statusClass = $g['status'] === 'Pass' ? 'status-pass' : ($g['status'] === 'Fail' ? 'status-fail' : 'status-progress');
                ?>
                <div class="grade-card <?= $markClass ?>">
                    <div class="grade-card-header">
                        <h4><?= htmlspecialchars($g['course_name']) ?></h4>
                        <span class="grade-badge"><?= htmlspecialchars($g['grade']) ?></span>
                    </div>
                    
                    <div class="grade-card-body">
                        <div class="grade-marks">
                            <span class="marks-value"><?= htmlspecialchars($g['marks']) ?>%</span>
                            <span class="marks-label">Score</span>
                        </div>
                        
                        <div class="grade-progress">
                            <div class="progress-bar-grade">
                                <div class="progress-fill-grade" style="width: <?= $g['marks'] ?>%"></div>
                            </div>
                        </div>
                        
                        <div class="grade-details">
                            <div class="detail-item-grade">
                                <span class="detail-icon-grade">📅</span>
                                <span><?= $g['exam_date'] ? date('M d, Y', strtotime($g['exam_date'])) : 'No date' ?></span>
                            </div>
                            <div class="detail-item-grade">
                                <span class="detail-icon-grade">
                                    <?= $g['status'] === 'Pass' ? '✅' : ($g['status'] === 'Fail' ? '❌' : '⏳') ?>
                                </span>
                                <span class="<?= $statusClass ?>"><?= htmlspecialchars($g['status']) ?></span>
                            </div>
                        </div>
                        
                        <?php if ($g['comment']): ?>
                            <div class="grade-comment">
                                <strong>Instructor's Comment:</strong>
                                <p><?= htmlspecialchars($g['comment']) ?></p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

    <?php endif; ?>
</div>

<style>
* {
    box-sizing: border-box;
}

.grades-container {
    padding: 0;
    width: 100%;
}

.grades-header {
    background: linear-gradient(135deg, #6B46C1 0%, #4C1D95 100%);
    color: white;
    padding: 40px;
    border-radius: 15px;
    margin-bottom: 30px;
    box-shadow: 0 10px 40px rgba(107, 70, 193, 0.4);
}

.grades-header h2 {
    margin: 0;
    font-size: 2rem;
    font-weight: 700;
}

.stat-card-grade {
    background: white;
    border-radius: 15px;
    padding: 25px;
    box-shadow: 0 5px 15px rgba(0,0,0,0.1);
    transition: all 0.3s ease;
    border-left: 5px solid;
    display: flex;
    align-items: center;
    gap: 15px;
}

.stat-card-grade:hover {
    transform: translateY(-5px);
    box-shadow: 0 15px 40px rgba(107, 70, 193, 0.3);
}

.stat-primary { border-left-color: #6B46C1; }
.stat-success { border-left-color: #10b981; }
.stat-info { border-left-color: #3b82f6; }
.stat-warning { border-left-color: #f59e0b; }

.stat-icon-grade {
    font-size: 3rem;
    flex-shrink: 0;
}

.stat-content-grade h3 {
    margin: 0;
    font-size: 2.5rem;
    font-weight: 700;
    color: #1e293b;
}

.stat-content-grade p {
    margin: 5px 0;
    color: #64748b;
    font-weight: 600;
}

.stat-content-grade small {
    color: #94a3b8;
    font-size: 0.85rem;
}

.last-exam-card {
    background: linear-gradient(135deg, #6B46C1, #9333EA);
    color: white;
    padding: 30px;
    border-radius: 15px;
    margin-bottom: 30px;
    box-shadow: 0 10px 30px rgba(107, 70, 193, 0.4);
    display: flex;
    align-items: center;
    gap: 25px;
}

.last-exam-icon {
    font-size: 4rem;
    flex-shrink: 0;
}

.last-exam-content h4 {
    margin: 0 0 5px 0;
    opacity: 0.9;
    font-size: 0.9rem;
    text-transform: uppercase;
    letter-spacing: 1px;
}

.last-exam-content h3 {
    margin: 0 0 15px 0;
    font-size: 1.8rem;
}

.last-exam-details {
    display: flex;
    gap: 20px;
    align-items: center;
    margin-bottom: 10px;
}

.exam-grade {
    padding: 8px 20px;
    border-radius: 25px;
    font-weight: 700;
    font-size: 1.2rem;
    background: rgba(255,255,255,0.2);
}

.exam-marks {
    font-size: 1.5rem;
    font-weight: 700;
}

.exam-date {
    opacity: 0.9;
}

.exam-comment {
    margin: 15px 0 0 0;
    padding: 15px;
    background: rgba(255,255,255,0.1);
    border-radius: 10px;
    font-style: italic;
}

.grades-section {
    background: white;
    padding: 30px;
    border-radius: 15px;
    box-shadow: 0 5px 15px rgba(0,0,0,0.1);
}

.section-title-grade {
    color: #1e293b;
    margin-bottom: 25px;
    font-size: 1.5rem;
    font-weight: 600;
}

.grades-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
    gap: 20px;
}

.grade-card {
    background: white;
    border-radius: 12px;
    padding: 20px;
    box-shadow: 0 3px 10px rgba(0,0,0,0.1);
    border-left: 5px solid;
    transition: all 0.3s ease;
}

.grade-card:hover {
    transform: translateY(-3px);
    box-shadow: 0 8px 25px rgba(0,0,0,0.15);
}

.grade-a { border-left-color: #10b981; }
.grade-b { border-left-color: #3b82f6; }
.grade-c { border-left-color: #6B46C1; }
.grade-d { border-left-color: #f59e0b; }
.grade-f { border-left-color: #ef4444; }

.grade-card-header {
    display: flex;
    justify-content: space-between;
    align-items: start;
    margin-bottom: 15px;
}

.grade-card-header h4 {
    margin: 0;
    color: #1e293b;
    font-size: 1.1rem;
    flex: 1;
}

.grade-badge {
    background: linear-gradient(135deg, #6B46C1, #9333EA);
    color: white;
    padding: 8px 16px;
    border-radius: 20px;
    font-weight: 700;
    font-size: 1.1rem;
}

.grade-marks {
    text-align: center;
    margin-bottom: 15px;
}

.marks-value {
    display: block;
    font-size: 2.5rem;
    font-weight: 700;
    color: #6B46C1;
}

.marks-label {
    color: #64748b;
    font-size: 0.9rem;
}

.grade-progress {
    margin-bottom: 15px;
}

.progress-bar-grade {
    height: 10px;
    background: #e2e8f0;
    border-radius: 10px;
    overflow: hidden;
}

.progress-fill-grade {
    height: 100%;
    background: linear-gradient(90deg, #6B46C1, #9333EA);
    border-radius: 10px;
    transition: width 0.5s ease;
}

.grade-details {
    display: flex;
    justify-content: space-around;
    padding: 15px 0;
    border-top: 1px solid #e2e8f0;
    border-bottom: 1px solid #e2e8f0;
    margin-bottom: 15px;
}

.detail-item-grade {
    display: flex;
    align-items: center;
    gap: 5px;
    color: #64748b;
    font-size: 0.9rem;
}

.detail-icon-grade {
    font-size: 1.2rem;
}

.status-pass { color: #10b981; font-weight: 600; }
.status-fail { color: #ef4444; font-weight: 600; }
.status-progress { color: #f59e0b; font-weight: 600; }

.grade-comment {
    background: #f8fafc;
    padding: 15px;
    border-radius: 8px;
    margin-top: 15px;
}

.grade-comment strong {
    color: #6B46C1;
    display: block;
    margin-bottom: 5px;
}

.grade-comment p {
    margin: 0;
    color: #475569;
    font-size: 0.95rem;
    line-height: 1.5;
}

.empty-state-grades {
    text-align: center;
    padding: 80px 20px;
    background: white;
    border-radius: 15px;
    box-shadow: 0 5px 15px rgba(0,0,0,0.1);
}

.empty-icon-grades {
    font-size: 6rem;
    margin-bottom: 20px;
    opacity: 0.5;
}

.empty-state-grades h3 {
    color: #1e293b;
    margin-bottom: 10px;
}

.empty-state-grades p {
    color: #64748b;
    font-size: 1.1rem;
}

@media (max-width: 768px) {
    .grades-grid {
        grid-template-columns: 1fr;
    }
    
    .grades-header {
        padding: 30px 20px;
    }
    
    .grades-header h2 {
        font-size: 1.5rem;
    }
    
    .last-exam-card {
        flex-direction: column;
        text-align: center;
    }
    
    .last-exam-details {
        flex-direction: column;
        gap: 10px;
    }
}
</style>
