<?php
session_start();
include 'db.php';

if (!isset($_SESSION['admin_id'])) {
    header("Location: admin_login.php");
    exit;
}

// Handle Add Student
if (isset($_POST['add'])) {
    $name = $_POST['name'];
    $email = $_POST['email'];
    $stmt = $pdo->prepare("INSERT INTO students (name, email) VALUES (?, ?)");
    $stmt->execute([$name, $email]);
}

// Handle Edit Student
if (isset($_POST['edit'])) {
    $id = $_POST['id'];
    $name = $_POST['name'];
    $email = $_POST['email'];
    $stmt = $pdo->prepare("UPDATE students SET name=?, email=? WHERE id=?");
    $stmt->execute([$name, $email, $id]);
}

// Handle Delete Student
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    $stmt = $pdo->prepare("DELETE FROM students WHERE id=?");
    $stmt->execute([$id]);
}

// Fetch all students
$stmt = $pdo->query("SELECT * FROM students");
$students = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Manage Students</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
</head>
<body class="bg-light">
<div class="container mt-5">
    <h2>Manage Students</h2>

    <!-- Add Student Form -->
    <form method="POST" class="mb-4">
        <input type="hidden" name="add" value="1">
        <div class="row">
            <div class="col-md-4">
                <input type="text" name="name" class="form-control" placeholder="Student Name" required>
            </div>
            <div class="col-md-4">
                <input type="email" name="email" class="form-control" placeholder="Student Email" required>
            </div>
            <div class="col-md-4">
                <button type="submit" class="btn btn-success w-100">Add Student</button>
            </div>
        </div>
    </form>

    <!-- Student List -->
    <table class="table table-bordered table-striped">
        <thead class="table-dark">
            <tr>
                <th>ID</th><th>Name</th><th>Email</th><th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($students as $student): ?>
                <tr>
                    <td><?php echo $student['id']; ?></td>
                    <td><?php echo $student['name']; ?></td>
                    <td><?php echo $student['email']; ?></td>
                    <td>
                        <!-- Edit Form -->
                        <form method="POST" style="display:inline-block;">
                            <input type="hidden" name="edit" value="1">
                            <input type="hidden" name="id" value="<?php echo $student['id']; ?>">
                            <input type="text" name="name" value="<?php echo $student['name']; ?>" required>
                            <input type="email" name="email" value="<?php echo $student['email']; ?>" required>
                            <button type="submit" class="btn btn-warning btn-sm">Update</button>
                        </form>
                        <!-- Delete Link -->
                        <a href="manage_students.php?delete=<?php echo $student['id']; ?>" 
                           class="btn btn-danger btn-sm"
                           onclick="return confirm('Delete this student?');">Delete</a>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <a href="admin_dashboard.php" class="btn btn-secondary mt-3">Back to Dashboard</a>
</div>
</body>
</html>
