<?php
session_start();
require 'db_connect.php';

// Check if student is logged in
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'student') {
    echo "<div class='alert alert-danger'>Please login as a student to access this page.</div>";
    exit;
}

// Fetch all active announcements
$sql = "SELECT announcement_id, title, message, start_date, end_date, author_admin_id, created_at, updated_at
        FROM announcements
        WHERE start_date <= CURDATE() 
        AND (end_date IS NULL OR end_date >= CURDATE())
        ORDER BY created_at DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute();
$activeAnnouncements = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch past announcements
$sqlPast = "SELECT announcement_id, title, message, start_date, end_date, author_admin_id, created_at, updated_at
            FROM announcements
            WHERE end_date < CURDATE()
            ORDER BY created_at DESC
            LIMIT 10";

$stmtPast = $pdo->prepare($sqlPast);
$stmtPast->execute();
$pastAnnouncements = $stmtPast->fetchAll(PDO::FETCH_ASSOC);

// Function to add icons based on title keywords
function getIcon($title) {
    $title = strtolower($title);
    if (strpos($title, 'urgent') !== false || strpos($title, 'important') !== false) return '🚨';
    if (strpos($title, 'safety') !== false) return '⚠️';
    if (strpos($title, 'maintenance') !== false || strpos($title, 'cancel') !== false) return '🔧';
    if (strpos($title, 'exam') !== false || strpos($title, 'test') !== false) return '📝';
    if (strpos($title, 'result') !== false || strpos($title, 'grade') !== false) return '📊';
    if (strpos($title, 'event') !== false || strpos($title, 'celebration') !== false) return '🎉';
    if (strpos($title, 'lecture') !== false || strpos($title, 'class') !== false) return '🎓';
    if (strpos($title, 'holiday') !== false || strpos($title, 'break') !== false) return '🏖️';
    if (strpos($title, 'weather') !== false) return '🌤️';
    return '📢';
}

// Function to get priority badge
function getPriorityBadge($title) {
    $title = strtolower($title);
    if (strpos($title, 'urgent') !== false || strpos($title, 'important') !== false) {
        return '<span class="badge bg-danger ms-2">URGENT</span>';
    }
    if (strpos($title, 'new') !== false) {
        return '<span class="badge bg-success ms-2">NEW</span>';
    }
    return '';
}

// Function to get card color class
function getCardColorClass($title) {
    $title = strtolower($title);
    if (strpos($title, 'urgent') !== false || strpos($title, 'important') !== false) return 'border-danger';
    if (strpos($title, 'safety') !== false) return 'border-warning';
    if (strpos($title, 'exam') !== false || strpos($title, 'test') !== false) return 'border-primary';
    if (strpos($title, 'event') !== false) return 'border-success';
    return 'border-info';
}
?>

<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3>📢 Announcements</h3>
        <span class="badge bg-primary fs-6"><?= count($activeAnnouncements) ?> Active</span>
    </div>

    <?php if (empty($activeAnnouncements)): ?>
        <div class="alert alert-info shadow-sm">
            <h5 class="alert-heading">ℹ️ No Active Announcements</h5>
            <p class="mb-0">There are no active announcements at this time. Check back later for updates!</p>
        </div>
    <?php else: ?>
        <!-- Active Announcements -->
        <div class="row g-4 mb-5">
            <?php foreach ($activeAnnouncements as $announcement): ?>
                <div class="col-md-6 col-lg-4">
                    <div class="card announcement-card shadow-sm h-100 <?= getCardColorClass($announcement['title']) ?>" style="border-left-width: 5px;">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-start mb-3">
                                <h5 class="card-title mb-0">
                                    <span class="announcement-icon"><?= getIcon($announcement['title']) ?></span>
                                    <?= htmlspecialchars($announcement['title']) ?>
                                    <?= getPriorityBadge($announcement['title']) ?>
                                </h5>
                            </div>
                            
                            <p class="card-text announcement-message">
                                <?= nl2br(htmlspecialchars($announcement['message'])) ?>
                            </p>
                            
                            <hr>
                            
                            <div class="announcement-meta">
                                <small class="text-muted d-block mb-1">
                                    📅
                                    <strong>Active:</strong> <?= date('M d, Y', strtotime($announcement['start_date'])) ?>
                                    <?php if ($announcement['end_date']): ?>
                                        - <?= date('M d, Y', strtotime($announcement['end_date'])) ?>
                                    <?php endif; ?>
                                </small>
                                <small class="text-muted d-block">
                                    🕐
                                    <strong>Posted:</strong> <?= date('M d, Y g:i A', strtotime($announcement['created_at'])) ?>
                                </small>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <!-- Past Announcements -->
    <?php if (!empty($pastAnnouncements)): ?>
        <div class="card shadow-sm">
            <div class="card-header bg-secondary text-white">
                <h5 class="mb-0">🕒 Past Announcements</h5>
            </div>
            <div class="card-body">
                <div class="accordion" id="pastAnnouncementsAccordion">
                    <?php foreach ($pastAnnouncements as $index => $announcement): ?>
                        <div class="accordion-item">
                            <h2 class="accordion-header" id="heading<?= $index ?>">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" 
                                        data-bs-target="#collapse<?= $index ?>" aria-expanded="false">
                                    <?= getIcon($announcement['title']) ?> <?= htmlspecialchars($announcement['title']) ?>
                                    <small class="text-muted ms-3">
                                        (<?= date('M d, Y', strtotime($announcement['created_at'])) ?>)
                                    </small>
                                </button>
                            </h2>
                            <div id="collapse<?= $index ?>" class="accordion-collapse collapse" 
                                 data-bs-parent="#pastAnnouncementsAccordion">
                                <div class="accordion-body">
                                    <p><?= nl2br(htmlspecialchars($announcement['message'])) ?></p>
                                    <hr>
                                    <small class="text-muted">
                                        📅
                                        Active: <?= date('M d, Y', strtotime($announcement['start_date'])) ?>
                                        <?php if ($announcement['end_date']): ?>
                                            - <?= date('M d, Y', strtotime($announcement['end_date'])) ?>
                                        <?php endif; ?>
                                    </small>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    <?php endif; ?>
</div>

<style>
.announcement-card {
    border-radius: 12px;
    transition: all 0.3s ease;
}

.announcement-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 10px 25px rgba(0,0,0,0.15) !important;
}

.announcement-icon {
    font-size: 1.5rem;
    margin-right: 8px;
}

.announcement-message {
    font-size: 0.95rem;
    line-height: 1.6;
    color: #495057;
}

.announcement-meta {
    background: #f8f9fa;
    padding: 10px;
    border-radius: 8px;
    margin-top: 10px;
}

.accordion-button:not(.collapsed) {
    background-color: #e7f1ff;
    color: #0c63e4;
}

.card-header {
    border-radius: 12px 12px 0 0 !important;
}
</style>
