<?php
session_start();
require 'db_connect.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'student') {
    echo "<div class='alert alert-danger'>Please login as a student.</div>";
    exit;
}

// Fetch all courses
$sql = "SELECT course_id, title, description, created_at FROM courses ORDER BY created_at DESC";
$stmt = $pdo->query($sql);
$courses = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="courses-page">
    <!-- Page Header -->
    <div class="page-header-courses">
        <h2 class="mb-2">📚 My Courses</h2>
        <p class="mb-0 opacity-75">Explore your enrolled courses and track your progress</p>
    </div>

    <?php if (empty($courses)): ?>
        <div class="empty-state-courses">
            <div class="empty-icon">📚</div>
            <h3>No Courses Available</h3>
            <p>There are no courses available at the moment. Check back later!</p>
        </div>
    <?php else: ?>
        <!-- Courses Grid -->
        <div class="row g-4">
            <?php foreach ($courses as $course): ?>
                <div class="col-lg-4 col-md-6">
                    <div class="course-card-perfect" onclick="viewCourse(<?= $course['course_id'] ?>)">
                        <div class="course-icon-perfect">📖</div>
                        <div class="course-badge-perfect">Active</div>
                        <h4 class="course-title-perfect"><?= htmlspecialchars($course['title']) ?></h4>
                        <p class="course-description-perfect"><?= htmlspecialchars($course['description']) ?></p>
                        <div class="course-footer-perfect">
                            <span class="course-date-perfect">
                                🕐 <?= date('M d, Y', strtotime($course['created_at'])) ?>
                            </span>
                            <button class="btn-view-course">View Course →</button>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<style>
:root {
    --primary-purple: #6B46C1;
    --dark-purple: #4C1D95;
    --light-purple: #9333EA;
}

* {
    box-sizing: border-box;
}

.courses-page {
    padding: 0;
    width: 100%;
}

.page-header-courses {
    background: linear-gradient(135deg, var(--primary-purple) 0%, var(--dark-purple) 100%);
    color: white;
    padding: 40px;
    border-radius: 15px;
    margin-bottom: 30px;
    box-shadow: 0 10px 40px rgba(107, 70, 193, 0.4);
}

.page-header-courses h2 {
    margin: 0;
    font-size: 2rem;
    font-weight: 700;
}

.page-header-courses p {
    margin: 0;
    font-size: 1.1rem;
}

.row {
    margin: 0 !important;
}

.g-4 {
    gap: 1.5rem !important;
}

.course-card-perfect {
    background: white;
    border-radius: 15px;
    padding: 30px;
    height: 100%;
    min-height: 320px;
    box-shadow: 0 5px 15px rgba(0,0,0,0.1);
    transition: all 0.3s ease;
    cursor: pointer;
    border: 2px solid transparent;
    display: flex;
    flex-direction: column;
    position: relative;
}

.course-card-perfect:hover {
    transform: translateY(-5px);
    box-shadow: 0 15px 40px rgba(107, 70, 193, 0.3);
    border-color: var(--primary-purple);
}

.course-icon-perfect {
    font-size: 3.5rem;
    margin-bottom: 15px;
    text-align: center;
}

.course-badge-perfect {
    position: absolute;
    top: 20px;
    right: 20px;
    background: linear-gradient(135deg, var(--primary-purple), var(--light-purple));
    color: white;
    padding: 6px 16px;
    border-radius: 20px;
    font-size: 0.8rem;
    font-weight: 600;
}

.course-title-perfect {
    font-size: 1.4rem;
    font-weight: 700;
    color: #1e293b;
    margin: 0 0 15px 0;
    text-align: center;
}

.course-description-perfect {
    color: #64748b;
    font-size: 0.95rem;
    line-height: 1.6;
    margin: 0 0 auto 0;
    text-align: center;
    flex-grow: 1;
}

.course-footer-perfect {
    display: flex;
    flex-direction: column;
    gap: 15px;
    padding-top: 20px;
    border-top: 1px solid #e2e8f0;
    margin-top: 20px;
}

.course-date-perfect {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 5px;
    color: #64748b;
    font-size: 0.85rem;
}

.btn-view-course {
    background: linear-gradient(135deg, var(--primary-purple), var(--dark-purple));
    color: white;
    border: none;
    padding: 12px 20px;
    border-radius: 8px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.3s ease;
    width: 100%;
}

.btn-view-course:hover {
    transform: translateY(-2px);
    box-shadow: 0 10px 25px rgba(107, 70, 193, 0.4);
}

.empty-state-courses {
    text-align: center;
    padding: 80px 20px;
    background: white;
    border-radius: 15px;
    box-shadow: 0 5px 15px rgba(0,0,0,0.1);
}

.empty-icon {
    font-size: 5rem;
    margin-bottom: 20px;
}

.empty-state-courses h3 {
    color: #1e293b;
    margin-bottom: 10px;
    font-weight: 600;
}

.empty-state-courses p {
    color: #64748b;
}

@media (max-width: 768px) {
    .course-card-perfect {
        min-height: 280px;
    }
    
    .page-header-courses {
        padding: 30px 20px;
    }
    
    .page-header-courses h2 {
        font-size: 1.5rem;
    }
}
</style>

<script>
function viewCourse(courseId) {
    console.log('Viewing course:', courseId);
    
    // Get the main content element
    const mainContent = document.getElementById('main-content');
    const mainElement = document.querySelector('main');
    
    if (!mainContent) {
        console.error('main-content element not found');
        alert('Error: Cannot load course details. Please refresh the page.');
        return;
    }
    
    // Show loading message
    mainContent.innerHTML = '<div style="text-align: center; padding: 50px; background: white; border-radius: 15px; margin: 20px;"><h3 style="color: #6B46C1;">Loading course details...</h3><p>Please wait...</p></div>';
    
    // Scroll main to top
    if (mainElement) {
        mainElement.scrollTop = 0;
    }
    
    // Fetch course details
    fetch('course_detail.php?id=' + courseId)
        .then(response => {
            console.log('Response status:', response.status);
            if (!response.ok) {
                throw new Error('HTTP error! status: ' + response.status);
            }
            return response.text();
        })
        .then(html => {
            console.log('Course details loaded successfully');
            mainContent.innerHTML = html;
            
            // Scroll to top after loading
            if (mainElement) {
                mainElement.scrollTop = 0;
            }
        })
        .catch(err => {
            console.error('Error loading course:', err);
            mainContent.innerHTML = `
                <div style="background: white; padding: 40px; border-radius: 15px; margin: 20px; text-align: center;">
                    <h3 style="color: #dc3545;">Failed to Load Course</h3>
                    <p>Error: ${err.message}</p>
                    <button onclick="location.reload()" style="background: #6B46C1; color: white; border: none; padding: 10px 20px; border-radius: 8px; cursor: pointer;">
                        Refresh Page
                    </button>
                </div>
            `;
        });
}

// Make sure the function is available globally
window.viewCourse = viewCourse;
</script>
