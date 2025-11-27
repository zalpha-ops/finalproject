<?php
session_start();
include 'db.php';

// Require login
if (!isset($_SESSION['admin_id'])) {
    header("Location: admin_login.php");
    exit;
}

// Optional role check (uncomment and adapt if you’ve implemented roles)
// if ($_SESSION['role'] !== 'Super Admin' && $_SESSION['role'] !== 'Student Manager') {
//     die("Unauthorized access.");
// }

$error = "";
$success = "";

// Add announcement
if (isset($_POST['add'])) {
    $title = trim($_POST['title']);
    $content = trim($_POST['content']);
    $target_audience = $_POST['target_audience'] ?? 'all';
    $priority = $_POST['priority'] ?? 'normal';
    $expires_at = isset($_POST['expires_at']) && $_POST['expires_at'] !== "" ? trim($_POST['expires_at']) : null;

    if ($title && $content) {
        $stmt = $pdo->prepare("INSERT INTO announcements (title, content, author_id, target_audience, priority, expires_at) VALUES (?, ?, ?, ?, ?, ?)");
        try {
            $stmt->execute([$title, $content, $_SESSION['admin_id'], $target_audience, $priority, $expires_at]);
            $success = "Announcement added.";
        } catch (PDOException $e) {
            $error = "Failed to add announcement. Please try again.";
        }
    } else {
        $error = "Title and content are required.";
    }
}

// Edit announcement
if (isset($_POST['edit'])) {
    $id = (int)$_POST['id'];
    $title = trim($_POST['title']);
    $content = trim($_POST['content']);
    $target_audience = $_POST['target_audience'] ?? 'all';
    $priority = $_POST['priority'] ?? 'normal';
    $expires_at = isset($_POST['expires_at']) && $_POST['expires_at'] !== "" ? trim($_POST['expires_at']) : null;

    if ($id && $title && $content) {
        $stmt = $pdo->prepare("UPDATE announcements SET title=?, content=?, target_audience=?, priority=?, expires_at=? WHERE id=?");
        try {
            $stmt->execute([$title, $content, $target_audience, $priority, $expires_at, $id]);
            $success = "Announcement updated.";
        } catch (PDOException $e) {
            $error = "Failed to update announcement.";
        }
    } else {
        $error = "Title and content are required.";
    }
}

// Delete announcement
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    if ($id) {
        $stmt = $pdo->prepare("DELETE FROM announcements WHERE id=?");
        try {
            $stmt->execute([$id]);
            $success = "Announcement deleted.";
        } catch (PDOException $e) {
            $error = "Failed to delete announcement.";
        }
    }
}

// Fetch all announcements (newest first)
$stmt = $pdo->query("
    SELECT a.id, a.title, a.content, a.target_audience, a.priority, a.created_at, a.expires_at,
           u.username AS author_name
    FROM announcements a
    LEFT JOIN users u ON u.id = a.author_id
    ORDER BY a.id DESC
");
$announcements = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html>
<head>
    <title>Manage Announcements</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
</head>
<body class="bg-light">
<div class="container mt-5">
    <h2>Manage Announcements</h2>

    <?php if (!empty($error)): ?>
        <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>
    <?php if (!empty($success)): ?>
        <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
    <?php endif; ?>

    <!-- Add Announcement Form -->
    <form method="POST" class="mb-4 card p-3">
        <input type="hidden" name="add" value="1">
        <div class="row g-2">
            <div class="col-md-4">
                <input type="text" name="title" class="form-control" placeholder="Title" required>
            </div>
            <div class="col-md-4">
                <textarea name="content" class="form-control" placeholder="Content" rows="2" required></textarea>
            </div>
            <div class="col-md-2">
                <select name="target_audience" class="form-select">
                    <option value="all">All</option>
                    <option value="students">Students</option>
                    <option value="instructors">Instructors</option>
                </select>
            </div>
            <div class="col-md-2">
                <select name="priority" class="form-select">
                    <option value="normal">Normal</option>
                    <option value="high">High</option>
                    <option value="low">Low</option>
                </select>
            </div>
            <div class="col-md-3">
                <input type="datetime-local" name="expires_at" class="form-control" placeholder="Expires (optional)">
            </div>
        </div>
        <div class="mt-2">
            <button type="submit" class="btn btn-success">Add Announcement</button>
        </div>
    </form>

    <!-- Announcements List -->
    <table class="table table-bordered table-striped">
        <thead class="table-dark">
            <tr>
                <th>ID</th><th>Title</th><th>Content</th><th>Audience</th><th>Priority</th><th>Author</th><th>Created</th><th>Expires</th><th>Actions</th>
            </tr>
        </thead>
        <tbody>
        <?php foreach ($announcements as $a): ?>
            <tr>
                <td><?php echo $a['id']; ?></td>
                <td><?php echo htmlspecialchars($a['title']); ?></td>
                <td><?php echo htmlspecialchars(substr($a['content'], 0, 50)) . '...'; ?></td>
                <td><?php echo htmlspecialchars($a['target_audience']); ?></td>
                <td><span class="badge bg-<?php echo $a['priority'] === 'high' ? 'danger' : ($a['priority'] === 'low' ? 'secondary' : 'primary'); ?>"><?php echo htmlspecialchars($a['priority']); ?></span></td>
                <td><?php echo htmlspecialchars($a['author_name'] ?? 'N/A'); ?></td>
                <td><?php echo htmlspecialchars($a['created_at']); ?></td>
                <td><?php echo htmlspecialchars($a['expires_at'] ?? 'Never'); ?></td>
                <td>
                    <a href="manage_announcements.php?delete=<?php echo $a['id']; ?>"
                       class="btn btn-danger btn-sm"
                       onclick="return confirm('Delete this announcement?');">Delete</a>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>

    <a href="admin_dashboard.php" class="btn btn-secondary mt-3">Back to Dashboard</a>
</div>
</body>
</html>
