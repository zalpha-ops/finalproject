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
    $message = trim($_POST['message']);
    $start_date = trim($_POST['start_date']);
    $end_date = isset($_POST['end_date']) && $_POST['end_date'] !== "" ? trim($_POST['end_date']) : null;

    if ($title && $message && $start_date) {
        $stmt = $pdo->prepare("INSERT INTO announcements (title, message, start_date, end_date, author_admin_id) VALUES (?, ?, ?, ?, ?)");
        try {
            $stmt->execute([$title, $message, $start_date, $end_date, $_SESSION['admin_id']]);
            $success = "Announcement added.";
        } catch (PDOException $e) {
            $error = "Failed to add announcement. Please try again.";
        }
    } else {
        $error = "Title, message, and start date are required.";
    }
}

// Edit announcement
if (isset($_POST['edit'])) {
    $announcement_id = (int)$_POST['announcement_id'];
    $title = trim($_POST['title']);
    $message = trim($_POST['message']);
    $start_date = trim($_POST['start_date']);
    $end_date = isset($_POST['end_date']) && $_POST['end_date'] !== "" ? trim($_POST['end_date']) : null;

    if ($announcement_id && $title && $message && $start_date) {
        $stmt = $pdo->prepare("UPDATE announcements SET title=?, message=?, start_date=?, end_date=? WHERE announcement_id=?");
        try {
            $stmt->execute([$title, $message, $start_date, $end_date, $announcement_id]);
            $success = "Announcement updated.";
        } catch (PDOException $e) {
            $error = "Failed to update announcement.";
        }
    } else {
        $error = "Title, message, and start date are required.";
    }
}

// Delete announcement
if (isset($_GET['delete'])) {
    $announcement_id = (int)$_GET['delete'];
    if ($announcement_id) {
        $stmt = $pdo->prepare("DELETE FROM announcements WHERE announcement_id=?");
        try {
            $stmt->execute([$announcement_id]);
            $success = "Announcement deleted.";
        } catch (PDOException $e) {
            $error = "Failed to delete announcement.";
        }
    }
}

// Fetch all announcements (newest first)
$stmt = $pdo->query("
    SELECT a.announcement_id, a.title, a.message, a.start_date, a.end_date, a.created_at, a.updated_at,
           ad.email AS author_email
    FROM announcements a
    JOIN admins ad ON ad.id = a.author_admin_id
    ORDER BY a.announcement_id DESC
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
    <form method="POST" class="mb-4">
        <input type="hidden" name="add" value="1">
        <div class="row g-2">
            <div class="col-md-3">
                <input type="text" name="title" class="form-control" placeholder="Title" required>
            </div>
            <div class="col-md-5">
                <input type="text" name="message" class="form-control" placeholder="Message" required>
            </div>
            <div class="col-md-2">
                <input type="date" name="start_date" class="form-control" required>
            </div>
            <div class="col-md-2">
                <input type="date" name="end_date" class="form-control" placeholder="Optional">
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
                <th>ID</th><th>Title</th><th>Message</th><th>Start</th><th>End</th><th>Author</th><th>Created</th><th>Updated</th><th>Actions</th>
            </tr>
        </thead>
        <tbody>
        <?php foreach ($announcements as $a): ?>
            <tr>
                <td><?php echo $a['announcement_id']; ?></td>
                <td><?php echo htmlspecialchars($a['title']); ?></td>
                <td><?php echo htmlspecialchars($a['message']); ?></td>
                <td><?php echo htmlspecialchars($a['start_date']); ?></td>
                <td><?php echo htmlspecialchars($a['end_date'] ?? ''); ?></td>
                <td><?php echo htmlspecialchars($a['author_email']); ?></td>
                <td><?php echo htmlspecialchars($a['created_at']); ?></td>
                <td><?php echo htmlspecialchars($a['updated_at']); ?></td>
                <td>
                    <!-- Edit Form -->
                    <form method="POST" class="d-inline">
                        <input type="hidden" name="edit" value="1">
                        <input type="hidden" name="announcement_id" value="<?php echo $a['announcement_id']; ?>">
                        <input type="text" name="title" value="<?php echo htmlspecialchars($a['title']); ?>" class="form-control form-control-sm d-inline w-auto" required>
                        <input type="text" name="message" value="<?php echo htmlspecialchars($a['message']); ?>" class="form-control form-control-sm d-inline w-auto" required>
                        <input type="date" name="start_date" value="<?php echo htmlspecialchars($a['start_date']); ?>" class="form-control form-control-sm d-inline w-auto" required>
                        <input type="date" name="end_date" value="<?php echo htmlspecialchars($a['end_date']); ?>" class="form-control form-control-sm d-inline w-auto">
                        <button type="submit" class="btn btn-warning btn-sm">Update</button>
                    </form>

                    <!-- Delete Link -->
                    <a href="manage_announcements.php?delete=<?php echo $a['announcement_id']; ?>"
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
