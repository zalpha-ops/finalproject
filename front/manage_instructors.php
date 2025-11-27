<?php
session_start();
include 'db.php';

if (!isset($_SESSION['admin_id'])) {
    header("Location: admin_login.php");
    exit;
}

$error = "";

// Handle Add Instructor
if (isset($_POST['add'])) {
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);

    if ($name && $email) {
        $stmt = $pdo->prepare("INSERT INTO instructors (name, email) VALUES (?, ?)");
        try {
            $stmt->execute([$name, $email]);
        } catch (PDOException $e) {
            $error = "Failed to add instructor: " . ($e->getCode() === '23000' ? "Email already exists." : "Database error.");
        }
    }
}

// Handle Edit Instructor
if (isset($_POST['edit'])) {
    $id = (int)$_POST['instructor_id'];
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);

    if ($id && $name && $email) {
        $stmt = $pdo->prepare("UPDATE instructors SET name = ?, email = ? WHERE instructor_id = ?");
        try {
            $stmt->execute([$name, $email, $id]);
        } catch (PDOException $e) {
            $error = "Failed to update instructor: " . ($e->getCode() === '23000' ? "Email already exists." : "Database error.");
        }
    }
}

// Handle Delete Instructor
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    if ($id) {
        $stmt = $pdo->prepare("DELETE FROM instructors WHERE instructor_id = ?");
        $stmt->execute([$id]);
    }
}

// Fetch all instructors
$stmt = $pdo->query("SELECT instructor_id, name, email FROM instructors ORDER BY instructor_id DESC");
$instructors = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Manage Instructors</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
</head>
<body class="bg-light">
<div class="container mt-5">
    <h2>Manage Instructors</h2>

    <?php if (!empty($error)): ?>
        <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>

    <!-- Add Instructor Form -->
    <form method="POST" class="mb-4">
        <input type="hidden" name="add" value="1">
        <div class="row g-2">
            <div class="col-md-4">
                <input type="text" name="name" class="form-control" placeholder="Instructor Name" required>
            </div>
            <div class="col-md-4">
                <input type="email" name="email" class="form-control" placeholder="Instructor Email" required>
            </div>
            <div class="col-md-4">
                <button type="submit" class="btn btn-success w-100">Add Instructor</button>
            </div>
        </div>
    </form>

    <!-- Instructor List -->
    <table class="table table-bordered table-striped">
        <thead class="table-dark">
            <tr>
                <th>ID</th><th>Name</th><th>Email</th><th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($instructors as $instructor): ?>
                <tr>
                    <td><?php echo $instructor['instructor_id']; ?></td>
                    <td><?php echo htmlspecialchars($instructor['name']); ?></td>
                    <td><?php echo htmlspecialchars($instructor['email']); ?></td>
                    <td class="d-flex gap-2">
                        <!-- Edit Form -->
                        <form method="POST" class="d-flex flex-wrap gap-2">
                            <input type="hidden" name="edit" value="1">
                            <input type="hidden" name="instructor_id" value="<?php echo $instructor['instructor_id']; ?>">
                            <input type="text" name="name" value="<?php echo htmlspecialchars($instructor['name']); ?>" class="form-control form-control-sm" required>
                            <input type="email" name="email" value="<?php echo htmlspecialchars($instructor['email']); ?>" class="form-control form-control-sm" required>
                            <button type="submit" class="btn btn-warning btn-sm">Update</button>
                        </form>
                        <!-- Delete Link -->
                        <a href="manage_instructors.php?delete=<?php echo $instructor['instructor_id']; ?>" 
                           class="btn btn-danger btn-sm"
                           onclick="return confirm('Delete this instructor?');">Delete</a>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <a href="admin_dashboard.php" class="btn btn-secondary mt-3">Back to Dashboard</a>
</div>
</body>
</html>
