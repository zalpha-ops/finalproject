<?php
session_start();
require 'db_connect.php';

if (!isset($_SESSION['instructor_id'])) {
    header("Location: login.php");
    exit;
}

$courseId = $_GET['course_id'] ?? 0;

// Get course details
$courseStmt = $pdo->prepare("SELECT * FROM courses WHERE course_id = ?");
$courseStmt->execute([$courseId]);
$course = $courseStmt->fetch(PDO::FETCH_ASSOC);

// Verify instructor has access to this course
$accessCheck = $pdo->prepare("
    SELECT COUNT(*) FROM course_instructors 
    WHERE course_id = ? AND instructor_id = ?
");
$accessCheck->execute([$courseId, $_SESSION['instructor_id']]);
if ($accessCheck->fetchColumn() == 0) {
    die("<div class='alert alert-danger'>You don't have access to this course.</div>");
}

// Get all submissions for this course
$submissionsStmt = $pdo->prepare("
    SELECT 
        s.id as submission_id,
        s.file_path,
        s.comments,
        s.submitted_at,
        s.grade,
        s.feedback,
        s.status,
        a.id as assignment_id,
        a.title as assignment_title,
        sp.name as student_name,
        sp.student_id,
        sp.email
    FROM assignment_submissions s
    JOIN assignments a ON s.assignment_id = a.id
    JOIN student_profiles sp ON s.student_id = sp.id
    WHERE a.course_id = ?
    ORDER BY s.submitted_at DESC
");
$submissionsStmt->execute([$courseId]);
$submissions = $submissionsStmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Submissions - <?= htmlspecialchars($course['title']) ?></title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
    <style>
        :root {
            --primary-purple: #6B46C1;
            --dark-purple: #4C1D95;
        }
        
        body {
            background: linear-gradient(135deg, #0F0F0F 0%, #4C1D95 50%, #6B46C1 100%);
            min-height: 100vh;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        .page-header {
            background: linear-gradient(135deg, var(--primary-purple) 0%, var(--dark-purple) 100%);
            color: white;
            padding: 30px;
            border-radius: 15px;
            margin-bottom: 30px;
            box-shadow: 0 10px 40px rgba(107, 70, 193, 0.4);
        }
        
        .submission-card {
            background: white;
            border-radius: 15px;
            padding: 25px;
            margin-bottom: 20px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            border-left: 5px solid var(--primary-purple);
        }
        
        .btn-purple {
            background: linear-gradient(135deg, var(--primary-purple) 0%, var(--dark-purple) 100%);
            border: none;
            color: white;
        }
        
        .btn-purple:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(107, 70, 193, 0.4);
            color: white;
        }
    </style>
</head>
<body>
<div class="container py-5">
    <div class="page-header">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h2>📝 Assignment Submissions</h2>
                <p class="mb-0"><?= htmlspecialchars($course['title']) ?></p>
            </div>
            <a href="instructor_dashboard.php" class="btn btn-light">← Back to Dashboard</a>
        </div>
    </div>

    <?php if (empty($submissions)): ?>
        <div class="alert alert-info">
            No submissions yet for this course.
        </div>
    <?php else: ?>
        <?php foreach ($submissions as $submission): ?>
            <div class="submission-card">
                <div class="row">
                    <div class="col-md-8">
                        <h5><?= htmlspecialchars($submission['assignment_title']) ?></h5>
                        <p class="mb-2">
                            <strong>Student:</strong> <?= htmlspecialchars($submission['student_name']) ?> 
                            (<?= htmlspecialchars($submission['student_id']) ?>)
                        </p>
                        <p class="mb-2">
                            <strong>Submitted:</strong> <?= date('M d, Y g:i A', strtotime($submission['submitted_at'])) ?>
                        </p>
                        <?php if ($submission['comments']): ?>
                            <p class="mb-2">
                                <strong>Comments:</strong> <?= nl2br(htmlspecialchars($submission['comments'])) ?>
                            </p>
                        <?php endif; ?>
                        <?php if ($submission['file_path']): ?>
                            <p class="mb-2">
                                <strong>File:</strong> 
                                <a href="<?= htmlspecialchars($submission['file_path']) ?>" target="_blank" class="btn btn-sm btn-outline-primary">
                                    📎 Download Submission
                                </a>
                            </p>
                        <?php endif; ?>
                        
                        <?php if ($submission['status'] === 'graded'): ?>
                            <div class="alert alert-success mt-3">
                                <strong>Grade:</strong> <?= htmlspecialchars($submission['grade']) ?><br>
                                <strong>Feedback:</strong> <?= nl2br(htmlspecialchars($submission['feedback'])) ?>
                            </div>
                        <?php endif; ?>
                    </div>
                    <div class="col-md-4 text-end">
                        <?php if ($submission['status'] !== 'graded'): ?>
                            <button class="btn btn-purple" onclick="showGradeForm(<?= $submission['submission_id'] ?>)">
                                Grade Assignment
                            </button>
                        <?php else: ?>
                            <span class="badge bg-success fs-6">✅ Graded</span>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>

<!-- Grading Modal -->
<div class="modal fade" id="gradeModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Grade Assignment</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="gradeForm">
                <div class="modal-body">
                    <input type="hidden" id="submissionId" name="submission_id">
                    
                    <div class="mb-3">
                        <label class="form-label">Grade:</label>
                        <input type="text" name="grade" class="form-control" required placeholder="e.g., A, 95%, Pass">
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Feedback:</label>
                        <textarea name="feedback" class="form-control" rows="4" required placeholder="Provide feedback to the student..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-purple">Submit Grade</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
const gradeModal = new bootstrap.Modal(document.getElementById('gradeModal'));

function showGradeForm(submissionId) {
    document.getElementById('submissionId').value = submissionId;
    gradeModal.show();
}

document.getElementById('gradeForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    
    fetch('grade_assignment.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('Grade submitted successfully!');
            location.reload();
        } else {
            alert('Error: ' + data.message);
        }
    })
    .catch(err => {
        alert('Error submitting grade');
        console.error(err);
    });
});
</script>
</body>
</html>
