<?php
// Admin: manage instructors (CRUD + assignments to students/courses)
session_start();
require 'db_connect.php';
if (!isset($_SESSION['admin_id'])) { header('Location: admin_login.php'); exit; }

// Handle create/update/delete
$action = $_POST['action'] ?? null;

if ($action === 'create') {
    $stmt = $pdo->prepare("
        INSERT INTO instructors (name, email, phone, role, status, specialization, base)
        VALUES (:name, :email, :phone, :role, :status, :specialization, :base)
    ");
    $stmt->execute([
        ':name' => $_POST['name'],
        ':email' => $_POST['email'],
        ':phone' => $_POST['phone'] ?? null,
        ':role' => $_POST['role'] ?? null,
        ':status' => $_POST['status'] ?? 'Active',
        ':specialization' => $_POST['specialization'] ?? null,
        ':base' => $_POST['base'] ?? null,
    ]);
    header('Location: instrmanage.php?msg=created'); exit;
}

if ($action === 'update') {
    $stmt = $pdo->prepare("
        UPDATE instructors
        SET name=:name, email=:email, phone=:phone, role=:role, status=:status,
            specialization=:specialization, base=:base
        WHERE instructor_id=:id
    ");
    $stmt->execute([
        ':name' => $_POST['name'],
        ':email' => $_POST['email'],
        ':phone' => $_POST['phone'] ?? null,
        ':role' => $_POST['role'] ?? null,
        ':status' => $_POST['status'] ?? 'Active',
        ':specialization' => $_POST['specialization'] ?? null,
        ':base' => $_POST['base'] ?? null,
        ':id' => (int)$_POST['instructor_id'],
    ]);
    header('Location: instrmanage.php?msg=updated'); exit;
}

if ($action === 'delete') {
    // Clean up links first (optional due to FK CASCADE if set)
    $id = (int)$_POST['instructor_id'];
    $pdo->prepare("DELETE FROM student_instructors WHERE instructor_id=:id")->execute([':id'=>$id]);
    $pdo->prepare("DELETE FROM course_instructors WHERE instructor_id=:id")->execute([':id'=>$id]);

    $stmt = $pdo->prepare("DELETE FROM instructors WHERE instructor_id=:id");
    $stmt->execute([':id' => $id]);
    header('Location: instrmanage.php?msg=deleted'); exit;
}

// Fetch instructors
$instructors = $pdo->query("SELECT * FROM instructors ORDER BY created_at DESC")->fetchAll(PDO::FETCH_ASSOC);

// For assignment forms
$students = $pdo->query("SELECT id, name, student_id FROM student_profiles ORDER BY name ASC")->fetchAll(PDO::FETCH_ASSOC);
$courses  = $pdo->query("SELECT course_id, title FROM courses ORDER BY title ASC")->fetchAll(PDO::FETCH_ASSOC);

// Handle assignments
if ($action === 'assign_student') {
    $stmt = $pdo->prepare("INSERT INTO student_instructors (student_id, instructor_id) VALUES (:student_id, :instructor_id)");
    $stmt->execute([
        ':student_id' => (int)$_POST['student_id'],
        ':instructor_id' => (int)$_POST['instructor_id'],
    ]);
    header('Location: instrmanage.php?msg=student_assigned'); exit;
}
if ($action === 'assign_course') {
    $stmt = $pdo->prepare("INSERT INTO course_instructors (course_id, instructor_id) VALUES (:course_id, :instructor_id)");
    $stmt->execute([
        ':course_id' => (int)$_POST['course_id'],
        ':instructor_id' => (int)$_POST['instructor_id'],
    ]);
    header('Location: instrmanage.php?msg=course_assigned'); exit;
}

// Fetch current links
$links_students = $pdo->query("
    SELECT si.id, i.instructor_id, i.name AS instructor_name, sp.id AS student_profile_id,
           sp.name AS student_name, sp.student_id AS student_code
    FROM student_instructors si
    JOIN instructors i ON si.instructor_id = i.instructor_id
    JOIN student_profiles sp ON si.student_id = sp.id
    ORDER BY i.name, sp.name
")->fetchAll(PDO::FETCH_ASSOC);

$links_courses = $pdo->query("
    SELECT ci.id, i.instructor_id, i.name AS instructor_name, c.course_id, c.title AS course_title
    FROM course_instructors ci
    JOIN instructors i ON ci.instructor_id = i.instructor_id
    JOIN courses c ON ci.course_id = c.course_id
    ORDER BY i.name, c.title
")->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html>
<head>
  <title>Manage Instructors</title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
</head>
<body class="bg-light">
<div class="container py-4">
  <div class="d-flex justify-content-between align-items-center mb-3">
    <h3 class="mb-0">Instructors</h3>
    <a href="dashboard.php" class="btn btn-outline-primary btn-sm">Back to Dashboard</a>
  </div>

  <?php if (isset($_GET['msg'])): ?>
    <div class="alert alert-success"><?= htmlspecialchars($_GET['msg']) ?></div>
  <?php endif; ?>

  <div class="card mb-4">
    <div class="card-body">
      <h5 class="card-title mb-3">Add Instructor</h5>
      <form method="post" class="row g-2">
        <input type="hidden" name="action" value="create">
        <div class="col-md-4">
          <label class="form-label">Name</label>
          <input class="form-control" name="name" required>
        </div>
        <div class="col-md-4">
          <label class="form-label">Email</label>
          <input type="email" class="form-control" name="email" required>
        </div>
        <div class="col-md-4">
          <label class="form-label">Phone</label>
          <input class="form-control" name="phone">
        </div>
        <div class="col-md-3">
          <label class="form-label">Role</label>
          <input class="form-control" name="role" placeholder="Flight Instructor">
        </div>
        <div class="col-md-3">
          <label class="form-label">Status</label>
          <select class="form-select" name="status">
            <option value="Active" selected>Active</option>
            <option value="Inactive">Inactive</option>
          </select>
        </div>
        <div class="col-md-3">
          <label class="form-label">Specialization</label>
          <input class="form-control" name="specialization" placeholder="Instrument, Crosswind...">
        </div>
        <div class="col-md-3">
          <label class="form-label">Base</label>
          <input class="form-control" name="base" placeholder="Nairobi HKJK">
        </div>
        <div class="col-12">
          <button class="btn btn-primary mt-2">Save Instructor</button>
        </div>
      </form>
    </div>
  </div>

  <div class="card mb-4">
    <div class="card-body">
      <h5 class="card-title mb-3">Instructor List</h5>
      <table class="table table-sm table-striped">
        <thead class="table-dark">
          <tr>
            <th>Name</th><th>Email</th><th>Phone</th><th>Role</th><th>Status</th><th>Specialization</th><th>Base</th><th>Actions</th>
          </tr>
        </thead>
        <tbody>
        <?php foreach ($instructors as $ins): ?>
          <tr>
            <form method="post">
              <td><input class="form-control form-control-sm" name="name" value="<?= htmlspecialchars($ins['name']) ?>"></td>
              <td><input class="form-control form-control-sm" name="email" value="<?= htmlspecialchars($ins['email']) ?>"></td>
              <td><input class="form-control form-control-sm" name="phone" value="<?= htmlspecialchars($ins['phone'] ?? '') ?>"></td>
              <td><input class="form-control form-control-sm" name="role" value="<?= htmlspecialchars($ins['role'] ?? '') ?>"></td>
              <td>
                <select class="form-select form-select-sm" name="status">
                  <option <?= ($ins['status'] ?? 'Active')==='Active'?'selected':''; ?> value="Active">Active</option>
                  <option <?= ($ins['status'] ?? '')==='Inactive'?'selected':''; ?> value="Inactive">Inactive</option>
                </select>
              </td>
              <td><input class="form-control form-control-sm" name="specialization" value="<?= htmlspecialchars($ins['specialization'] ?? '') ?>"></td>
              <td><input class="form-control form-control-sm" name="base" value="<?= htmlspecialchars($ins['base'] ?? '') ?>"></td>
              <td class="d-flex gap-1">
                <input type="hidden" name="instructor_id" value="<?= (int)$ins['instructor_id'] ?>">
                <button class="btn btn-sm btn-success" name="action" value="update">Update</button>
                <button class="btn btn-sm btn-outline-danger" name="action" value="delete" onclick="return confirm('Delete instructor?');">Delete</button>
              </td>
            </form>
          </tr>
        <?php endforeach; ?>
        <?php if (empty($instructors)): ?>
          <tr><td colspan="8" class="text-center">No instructors yet.</td></tr>
        <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>

  <div class="row g-3">
    <div class="col-lg-6">
      <div class="card">
        <div class="card-body">
          <h5 class="card-title mb-3">Assign Student to Instructor</h5>
          <form method="post" class="row g-2">
            <input type="hidden" name="action" value="assign_student">
            <div class="col-6">
              <label class="form-label">Instructor</label>
              <select class="form-select" name="instructor_id" required>
                <?php foreach ($instructors as $i): ?>
                  <option value="<?= (int)$i['instructor_id'] ?>"><?= htmlspecialchars($i['name']) ?></option>
                <?php endforeach; ?>
              </select>
            </div>
            <div class="col-6">
              <label class="form-label">Student</label>
              <select class="form-select" name="student_id" required>
                <?php foreach ($students as $s): ?>
                  <option value="<?= (int)$s['id'] ?>"><?= htmlspecialchars($s['name']) ?> (<?= htmlspecialchars($s['student_id']) ?>)</option>
                <?php endforeach; ?>
              </select>
            </div>
            <div class="col-12">
              <button class="btn btn-primary">Assign</button>
            </div>
          </form>
          <hr>
          <h6 class="mb-2">Current Assignments</h6>
          <table class="table table-sm">
            <thead><tr><th>Instructor</th><th>Student</th></tr></thead>
            <tbody>
              <?php foreach ($links_students as $ls): ?>
                <tr>
                  <td><?= htmlspecialchars($ls['instructor_name']) ?></td>
                  <td><?= htmlspecialchars($ls['student_name']) ?> (<?= htmlspecialchars($ls['student_code']) ?>)</td>
                </tr>
              <?php endforeach; ?>
              <?php if (empty($links_students)): ?>
                <tr><td colspan="2" class="text-center">No student assignments.</td></tr>
              <?php endif; ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>

    <div class="col-lg-6">
      <div class="card">
        <div class="card-body">
          <h5 class="card-title mb-3">Assign Course to Instructor</h5>
          <form method="post" class="row g-2">
            <input type="hidden" name="action" value="assign_course">
            <div class="col-6">
              <label class="form-label">Instructor</label>
              <select class="form-select" name="instructor_id" required>
                <?php foreach ($instructors as $i): ?>
                  <option value="<?= (int)$i['instructor_id'] ?>"><?= htmlspecialchars($i['name']) ?></option>
                <?php endforeach; ?>
              </select>
            </div>
            <div class="col-6">
              <label class="form-label">Course</label>
              <select class="form-select" name="course_id" required>
                <?php foreach ($courses as $c): ?>
                  <option value="<?= (int)$c['course_id'] ?>"><?= htmlspecialchars($c['title']) ?></option>
                <?php endforeach; ?>
              </select>
            </div>
            <div class="col-12">
              <button class="btn btn-primary">Assign</button>
            </div>
          </form>
          <hr>
          <h6 class="mb-2">Current Course Assignments</h6>
          <table class="table table-sm">
            <thead><tr><th>Instructor</th><th>Course</th></tr></thead>
            <tbody>
              <?php foreach ($links_courses as $lc): ?>
                <tr>
                  <td><?= htmlspecialchars($lc['instructor_name']) ?></td>
                  <td><?= htmlspecialchars($lc['course_title']) ?></td>
                </tr>
              <?php endforeach; ?>
              <?php if (empty($links_courses)): ?>
                <tr><td colspan="2" class="text-center">No course assignments.</td></tr>
              <?php endif; ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>

  </div>

</div>
</body>
</html>
