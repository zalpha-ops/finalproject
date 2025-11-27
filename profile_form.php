<?php
include 'db_connect.php';

// Fetch instructors for dropdown
$stmt = $pdo->query("SELECT instructor_id, name FROM instructors");
$instructors = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Profile Form — Eagle Flight School</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" />
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
<div class="container py-4">
  <h2 class="mb-4 text-primary fw-bold"><i class="fas fa-user"></i> Student Profile Form</h2>

  <form method="POST" action="save_profile.php" class="row g-4">

    <!-- Identity -->
    <div class="col-12">
      <div class="card shadow-sm">
        <div class="card-header fw-semibold"><i class="fas fa-id-card"></i> Identity</div>
        <div class="card-body row g-3">
          <div class="col-md-6"><label class="form-label">Full Name</label><input type="text" name="name" class="form-control" required></div>
          <div class="col-md-6"><label class="form-label">Student ID</label><input type="text" name="student_id" class="form-control" required></div>
          <div class="col-md-6"><label class="form-label">Cohort</label><input type="text" name="cohort" class="form-control"></div>
          <div class="col-md-6"><label class="form-label">Track</label><input type="text" name="track" class="form-control"></div>
        </div>
      </div>
    </div>

    <!-- Personal & Contact -->
    <div class="col-12">
      <div class="card shadow-sm">
        <div class="card-header bg-info fw-semibold"><i class="fas fa-address-book"></i> Personal & Contact</div>
        <div class="card-body row g-3">
          <div class="col-md-6"><label class="form-label">Email</label><input type="email" name="email" class="form-control"></div>
          <div class="col-md-6"><label class="form-label">Phone</label><input type="text" name="phone" class="form-control"></div>
          <div class="col-md-6"><label class="form-label">Date of Birth</label><input type="date" name="dob" class="form-control"></div>
          <div class="col-md-6"><label class="form-label">Address</label><input type="text" name="address" class="form-control"></div>
        </div>
      </div>
    </div>

    <!-- Enrollment -->
    <div class="col-12">
      <div class="card shadow-sm">
        <div class="card-header bg-secondary fw-semibold"><i class="fas fa-school"></i> Enrollment</div>
        <div class="card-body row g-3">
          <div class="col-md-6"><label class="form-label">Enrollment Year</label><input type="number" name="enrollment_year" class="form-control"></div>
          <div class="col-md-6"><label class="form-label">Status</label>
            <select name="status" class="form-select"><option>Active</option><option>Inactive</option></select>
          </div>
          <div class="col-md-6"><label class="form-label">Base</label><input type="text" name="base" class="form-control"></div>
          <div class="col-md-6"><label class="form-label">Instructor</label>
            <select name="instructor_id" class="form-select">
              <?php foreach ($instructors as $inst): ?>
                <option value="<?= $inst['instructor_id'] ?>"><?= htmlspecialchars($inst['name']) ?></option>
              <?php endforeach; ?>
            </select>
          </div>
        </div>
      </div>
    </div>

    <
    <!-- Medical -->
    <div class="col-12">
      <div class="card shadow-sm">
        <div class="card-header bg-danger fw-semibold"><i class="fas fa-heartbeat"></i> Medical</div>
        <div class="card-body row g-3">
          <div class="col-md-4"><label class="form-label">Medical Class</label><input type="text" name="medical_class" class="form-control"></div>
          <div class="col-md-4"><label class="form-label">Medical Expiry</label><input type="date" name="medical_expiry" class="form-control"></div>
          <div class="col-md-4"><label class="form-label">Next Checkride</label><input type="date" name="next_checkride" class="form-control"></div>
        </div>
      </div>
    </div>

    <!-- Guardian -->
    <div class="col-12">
      <div class="card shadow-sm">
        <div class="card-header bg-warning fw-semibold"><i class="fas fa-users"></i> Guardian & Emergency</div>
        <div class="card-body row g-3">
          <div class="col-md-4"><label class="form-label">Guardian Name</label><input type="text" name="guardian_name" class="form-control"></div>
          <div class="col-md-4"><label class="form-label">Guardian Email</label><input type="email" name="guardian_email" class="form-control"></div>
          <div class="col-md-4"><label class="form-label">Guardian Phone</label><input type="text" name="guardian_phone" class="form-control"></div>
          <div class="col-md-4"><label class="form-label">Relationship</label><input type="text" name="relationship" class="form-control"></div>
          <div class="col-md-4"><label class="form-label">Emergency Contact</label><input type="text" name="emergency_contact" class="form-control"></div>
          <div class="col-md-4"><label class="form-label">Alternate Contact</label><input type="text" name="alt_contact" class="form-control"></div>
        </div>
      </div>
    </div>

    <!-- Achievements -->
    <div class="col-12">
      <div class="card shadow-sm">
        <div class="card-header bg-dark text-white fw-semibold">Achievements</div>
        <div class="card-body">
          <label class="form-label">Achievements</label>
          <textarea name="achievements" class="form-control" rows="3"></textarea>
        </div>
      </div>
    </div>

    <!-- Submit -->
    <div class="col-12 text-center">
      <button type="submit" class="btn btn-lg btn-primary px-5">Save Profile</button>
    </div>
  </form>
</div>
</body>
</html>
