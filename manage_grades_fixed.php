<?php
// Fixed Grade Management System
session_start();
require 'db_connect.php';

// Check if admin is logged in
if (!isset($_SESSION['admin_id']) && !isset($_SESSION['user_id'])) {
    header("Location: quick_login.php");
    exit;
}

$error = null;
$success = null;

// Fetch all students for dropdown
try {
    $students = $pdo->query("SELECT id, name, username FROM student_profiles ORDER BY name ASC")->fetchAll();
} catch (Exception $e) {
    $students = [];
    $error = "Could not load students: " . $e->getMessage();
}

$student_id = $_GET['student_id'] ?? null;
$student = null;
$courses = [];
$grades = [];

if ($student_id) {
    // Fetch student info
    try {
        $studentStmt = $pdo->prepare("SELECT id, name, username FROM student_profiles WHERE id = ?");
        $studentStmt->execute([$student_id]);
        $student = $studentStmt->fetch();
    } catch (Exception $e) {
        $error = "Could not load student: " . $e->getMessage();
    }

    if ($student) {
        // Fetch all courses
        try {
            $courses = $pdo->query("SELECT id, course_name, description FROM courses ORDER BY course_name ASC")->fetchAll();
        } catch (Exception $e) {
            $courses = [];
            $error = "Could not load courses: " . $e->getMessage();
        }

        // Fetch existing grades
        try {
            $gradesStmt = $pdo->prepare("SELECT * FROM grades WHERE student_username = ?");
            $gradesStmt->execute([$student['username']]);
            foreach ($gradesStmt->fetchAll() as $g) {
                $grades[$g['course_id']] = $g;
            }
        } catch (Exception $e) {
            $grades = [];
        }

        // Handle form submit
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $updated = 0;
            foreach ($_POST['grades'] as $course_id => $data) {
                $points = !empty($data['points']) ? (float)$data['points'] : null;
                $grade = !empty($data['grade']) ? $data['grade'] : null;
                $semester = $data['semester'] ?? 'Fall 2024';
                $year = (int)($data['year'] ?? 2024);

                // Auto-grading scale
                if ($points !== null) {
                    if ($points >= 90) {
                        $grade = 'A';
                    } elseif ($points >= 80) {
                        $grade = 'B';
                    } elseif ($points >= 70) {
                        $grade = 'C';
                    } elseif ($points >= 60) {
                        $grade = 'D';
                    } else {
                        $grade = 'F';
                    }
                }

                try {
                    // Check if grade record exists
                    $checkStmt = $pdo->prepare("SELECT id FROM grades WHERE student_username = ? AND course_id = ?");
                    $checkStmt->execute([$student['username'], $course_id]);
                    
                    if ($checkStmt->fetch()) {
                        // Update existing record
                        $stmt = $pdo->prepare("
                            UPDATE grades 
                            SET grade = ?, points = ?, semester = ?, year = ?
                            WHERE student_username = ? AND course_id = ?
                        ");
                        $stmt->execute([$grade, $points, $semester, $year, $student['username'], $course_id]);
                    } else {
                        // Insert new record
                        $stmt = $pdo->prepare("
                            INSERT INTO grades (student_username, course_id, grade, points, semester, year)
                            VALUES (?, ?, ?, ?, ?, ?)
                        ");
                        $stmt->execute([$student['username'], $course_id, $grade, $points, $semester, $year]);
                    }
                    $updated++;
                } catch (Exception $e) {
                    $error = "Error updating grades: " . $e->getMessage();
                    break;
                }
            }

            if (!$error) {
                $success = "✅ Updated $updated grade records successfully!";
                // Reload grades
                try {
                    $gradesStmt = $pdo->prepare("SELECT * FROM grades WHERE student_username = ?");
                    $gradesStmt->execute([$student['username']]);
                    $grades = [];
                    foreach ($gradesStmt->fetchAll() as $g) {
                        $grades[$g['course_id']] = $g;
                    }
                } catch (Exception $e) {
                    // Ignore reload errors
                }
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Grades - Eagle Flight School</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary-purple: #6B46C1;
            --dark-purple: #4C1D95;
        }
        
        body {
            background: linear-gradient(135deg, #f8fafc 0%, #e2e8f0 100%);
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        .page-header {
            background: linear-gradient(135deg, var(--primary-purple) 0%, var(--dark-purple) 100%);
            color: white;
            padding: 2rem;
            border-radius: 15px;
            margin-bottom: 2rem;
        }
        
        .card {
            border-radius: 15px;
            border: none;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }
        
        .btn-purple {
            background: linear-gradient(135deg, var(--primary-purple) 0%, var(--dark-purple) 100%);
            border: none;
            color: white;
        }
        
        .btn-purple:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(107, 70, 193, 0.4);
            color: white;
        }
        
        .grade-input {
            width: 80px;
        }
        
        .points-input {
            width: 100px;
        }
    </style>
</head>
<body>
<div class="container py-4">
    <div class="page-header">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h2 class="mb-2"><i class="fas fa-chart-line me-2"></i>Manage Student Grades</h2>
                <p class="mb-0 opacity-75">Update and track student academic performance</p>
            </div>
            <a href="admin_dashboard.php" class="btn btn-outline-light">
                <i class="fas fa-arrow-left me-2"></i>Dashboard
            </a>
        </div>
    </div>

    <?php if ($error): ?>
        <div class="alert alert-danger">
            <i class="fas fa-exclamation-triangle me-2"></i>
            <?= htmlspecialchars($error) ?>
        </div>
    <?php endif; ?>

    <?php if ($success): ?>
        <div class="alert alert-success">
            <i class="fas fa-check-circle me-2"></i>
            <?= htmlspecialchars($success) ?>
        </div>
    <?php endif; ?>

    <!-- Student Selection -->
    <div class="card mb-4">
        <div class="card-body">
            <h5 class="card-title"><i class="fas fa-user-graduate me-2"></i>Select Student</h5>
            <form method="get">
                <div class="row align-items-end">
                    <div class="col-md-8">
                        <label class="form-label">Student</label>
                        <select name="student_id" class="form-select" required>
                            <option value="">-- Choose Student --</option>
                            <?php foreach ($students as $s): ?>
                                <option value="<?= $s['id'] ?>" <?= ($student_id == $s['id']) ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($s['name']) ?> (<?= htmlspecialchars($s['username']) ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <button type="submit" class="btn btn-purple">
                            <i class="fas fa-search me-2"></i>Load Grades
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <?php if ($student && !empty($courses)): ?>
        <!-- Grades Management -->
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="fas fa-clipboard-list me-2"></i>
                    Grades for <?= htmlspecialchars($student['name']) ?>
                </h5>
            </div>
            <div class="card-body">
                <form method="post">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle">
                            <thead class="table-dark">
                                <tr>
                                    <th>Course</th>
                                    <th>Description</th>
                                    <th>Points</th>
                                    <th>Grade</th>
                                    <th>Semester</th>
                                    <th>Year</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($courses as $course): 
                                    $existing = $grades[$course['id']] ?? null;
                                ?>
                                <tr>
                                    <td><strong><?= htmlspecialchars($course['course_name']) ?></strong></td>
                                    <td><?= htmlspecialchars($course['description'] ?? 'No description') ?></td>
                                    <td>
                                        <input type="number" 
                                               name="grades[<?= $course['id'] ?>][points]" 
                                               value="<?= htmlspecialchars($existing['points'] ?? '') ?>" 
                                               class="form-control points-input"
                                               min="0" max="100" step="0.1"
                                               placeholder="0-100">
                                    </td>
                                    <td>
                                        <select name="grades[<?= $course['id'] ?>][grade]" class="form-select grade-input">
                                            <option value="">-</option>
                                            <?php 
                                            $grade_options = ['A', 'B', 'C', 'D', 'F'];
                                            foreach ($grade_options as $g) {
                                                $selected = ($existing['grade'] ?? '') === $g ? 'selected' : '';
                                                echo "<option value='$g' $selected>$g</option>";
                                            }
                                            ?>
                                        </select>
                                    </td>
                                    <td>
                                        <select name="grades[<?= $course['id'] ?>][semester]" class="form-select">
                                            <?php 
                                            $semesters = ['Fall 2024', 'Spring 2025', 'Summer 2025'];
                                            foreach ($semesters as $sem) {
                                                $selected = ($existing['semester'] ?? 'Fall 2024') === $sem ? 'selected' : '';
                                                echo "<option value='$sem' $selected>$sem</option>";
                                            }
                                            ?>
                                        </select>
                                    </td>
                                    <td>
                                        <select name="grades[<?= $course['id'] ?>][year]" class="form-select">
                                            <?php 
                                            $years = [2024, 2025, 2026];
                                            foreach ($years as $yr) {
                                                $selected = ($existing['year'] ?? 2024) == $yr ? 'selected' : '';
                                                echo "<option value='$yr' $selected>$yr</option>";
                                            }
                                            ?>
                                        </select>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>

                    <div class="d-flex gap-2 mt-3">
                        <button type="submit" class="btn btn-purple">
                            <i class="fas fa-save me-2"></i>Save All Grades
                        </button>
                        <a href="manage_grades_fixed.php" class="btn btn-outline-secondary">
                            <i class="fas fa-times me-2"></i>Cancel
                        </a>
                        <a href="admin_dashboard.php" class="btn btn-outline-primary">
                            <i class="fas fa-home me-2"></i>Dashboard
                        </a>
                    </div>
                </form>
            </div>
        </div>
    <?php elseif ($student && empty($courses)): ?>
        <div class="alert alert-warning">
            <i class="fas fa-exclamation-triangle me-2"></i>
            No courses found. Please add courses first.
        </div>
    <?php endif; ?>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
// Auto-calculate grade based on points
document.addEventListener('DOMContentLoaded', function() {
    const pointsInputs = document.querySelectorAll('input[type="number"]');
    
    pointsInputs.forEach(input => {
        input.addEventListener('input', function() {
            const points = parseFloat(this.value);
            const row = this.closest('tr');
            const gradeSelect = row.querySelector('select[name*="[grade]"]');
            
            if (!isNaN(points)) {
                let grade = '';
                if (points >= 90) grade = 'A';
                else if (points >= 80) grade = 'B';
                else if (points >= 70) grade = 'C';
                else if (points >= 60) grade = 'D';
                else grade = 'F';
                
                gradeSelect.value = grade;
            } else {
                gradeSelect.value = '';
            }
        });
    });
});
</script>
</body>
</html>