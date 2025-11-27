<?php
include 'db_connect.php';

try {
    // Safely read POST values with null coalescing
    $name            = $_POST['name']            ?? null;
    $student_id      = $_POST['student_id']      ?? null;
    $cohort          = $_POST['cohort']          ?? null;
    $track           = $_POST['track']           ?? null;
    $email           = $_POST['email']           ?? null;
    $phone           = $_POST['phone']           ?? null;
    $dob             = $_POST['dob']             ?? null;
    $address         = $_POST['address']         ?? null;
    $enrollment_year = $_POST['enrollment_year'] ?? null;
    $status          = $_POST['status']          ?? 'Active';
    $base            = $_POST['base']            ?? null;
    $instructor_id   = $_POST['instructor_id']   ?? null;
    $medical_class   = $_POST['medical_class']   ?? null;
    $medical_expiry  = $_POST['medical_expiry']  ?? null;
    $next_checkride  = $_POST['next_checkride']  ?? null;
    $achievements    = $_POST['achievements']    ?? null;

    // 1. Insert into student_profiles
    $stmt = $pdo->prepare("
        INSERT INTO student_profiles 
        (name, student_id, cohort, track, email, phone, dob, address, enrollment_year, status, base, instructor_id, medical_class, medical_expiry, next_checkride, achievements) 
        VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)
    ");

    $stmt->execute([
        $name, $student_id, $cohort, $track, $email, $phone, $dob, $address,
        $enrollment_year, $status, $base, $instructor_id,
        $medical_class, $medical_expiry, $next_checkride, $achievements
    ]);

    $new_student_id = $pdo->lastInsertId();

    // 2. Insert Guardian
    $guardian_name     = $_POST['guardian_name']     ?? null;
    $guardian_email    = $_POST['guardian_email']    ?? null;
    $guardian_phone    = $_POST['guardian_phone']    ?? null;
    $relationship      = $_POST['relationship']      ?? null;
    $emergency_contact = $_POST['emergency_contact'] ?? null;
    $alt_contact       = $_POST['alt_contact']       ?? null;

    $stmt = $pdo->prepare("
        INSERT INTO guardians (student_id, name, email, phone, relationship, emergency_contact, alt_contact) 
        VALUES (?,?,?,?,?,?,?)
    ");
    $stmt->execute([
        $new_student_id, $guardian_name, $guardian_email, $guardian_phone,
        $relationship, $emergency_contact, $alt_contact
    ]);

    // 3. Insert Training Hours
    $stmt = $pdo->prepare("
        INSERT INTO training_hours (student_id, category, required_hours, completed_hours, date_recorded) 
        VALUES (?,?,?,?,?)
    ");
    $today = date('Y-m-d');

    if (!empty($_POST['hours_total'])) {
        $stmt->execute([$new_student_id, 'Total', null, (int)$_POST['hours_total'], $today]);
    }
    if (!empty($_POST['hours_solo'])) {
        $stmt->execute([$new_student_id, 'Solo', null, (int)$_POST['hours_solo'], $today]);
    }
    if (!empty($_POST['hours_instr'])) {
        $stmt->execute([$new_student_id, 'Instrument', null, (int)$_POST['hours_instr'], $today]);
    }

    echo "<div style='padding:20px; font-family:Arial; color:green;'>✅ Student profile saved successfully!</div>";

} catch (Exception $e) {
    echo "<div style='padding:20px; font-family:Arial; color:red;'>❌ Error: " . htmlspecialchars($e->getMessage()) . "</div>";
}
?>
