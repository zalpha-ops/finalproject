<?php
session_start();
include 'db.php';

if (!isset($_SESSION['admin_id'])) {
    header("Location: admin_login.php");
    exit;
}

// Handle Add Course
if (isset($_POST['add'])) {
    $title = trim($_POST['title']);
    $description = trim($_POST['description']);
    $stmt = $pdo->prepare("INSERT INTO courses (title, description) VALUES (?, ?)");
    $stmt->execute([$title, $description]);
}

// Handle Edit Course
if (isset($_POST['edit'])) {
    $id = (int)$_POST['id'];
    $title = trim($_POST['title']);
    $description = trim($_POST['description']);
    $stmt = $pdo->prepare("UPDATE courses SET title=?, description=? WHERE id=?");
    $stmt->execute([$title, $description, $id]);
}

// Handle Delete Course
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    $stmt = $pdo->prepare("DELETE FROM courses WHERE id=?");
    $stmt->execute([$id]);
}

// Fetch all courses
$stmt = $pdo->query("SELECT id, title, description, created_at FROM courses ORDER BY id DESC");
$courses = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Manage Courses</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
</head>
<body class="bg-light">
<div class="container mt-5">
    <h2>Manage Courses</h2>

    <!-- Add Course Form -->
    <form method="POST" class="mb-4">
        <input type="hidden" name="add" value="1">
        <div class="row">
            <div class="col-md-4">
                <input type="text" name="title" class="form-control" placeholder="Course Title" required>
            </div>
            <div class="col-md-6">
                <input type="text" name="description" class="form-control" placeholder="Course Description" required>
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-success w-100">Add Course</button>
            </div>
        </div>
    </form>

    <!-- Course List -->
    <table class="table table-bordered table-striped">
        <thead class="table-dark">
            <tr>
                <th>ID</th><th>Code</th><th>Title</th><th>Description</th><th>Duration (hrs)</th><th>Price</th><th>Created</th><th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($courses as $course): ?>
                <tr>
                    <td><?php echo $course['id']; ?></td>
                    <td><?php echo htmlspecialchars($course['course_code'] ?? 'N/A'); ?></td>
                    <td><?php echo htmlspecialchars($course['title']); ?></td>
                    <td><?php echo htmlspecialchars($course['description'] ?? ''); ?></td>
                    <td><?php echo $course['duration_hours'] ?? 'N/A'; ?></td>
                    <td>$<?php echo number_format($course['price'] ?? 0, 2); ?></td>
                    <td><?php echo $course['created_at']; ?></td>
                    <td>
                        <!-- Edit Form -->
                        <form method="POST" style="display:inline-block;">
                            <input type="hidden" name="edit" value="1">
                            <input type="hidden" name="id" value="<?php echo $course['id']; ?>">
                            <input type="text" name="title" value="<?php echo htmlspecialchars($course['title']); ?>" required class="form-control form-control-sm d-inline w-auto">
                            <input type="text" name="description" value="<?php echo htmlspecialchars($course['description'] ?? ''); ?>" class="form-control form-control-sm d-inline w-auto">
                            <button type="submit" class="btn btn-warning btn-sm">Update</button>
                        </form>
                        <!-- Delete Link -->
                        <a href="manage_courses.php?delete=<?php echo $course['id']; ?>" 
                           class="btn btn-danger btn-sm"
                           onclick="return confirm('Delete this course?');">Delete</a>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <a href="admin_dashboard.php" class="btn btn-secondary mt-3">Back to Dashboard</a>
</div>
</body>
</html>
