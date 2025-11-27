<?php
session_start();
require 'db_connect.php';
require 'auth.php';
require_role('admin');

$message = '';
$messageType = '';
$importResults = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['csv_file'])) {
    $uploadedFile = $_FILES['csv_file'];
    
    // Validate file
    if ($uploadedFile['error'] !== UPLOAD_ERR_OK) {
        $message = "File upload error.";
        $messageType = 'danger';
    } elseif ($uploadedFile['size'] > 5 * 1024 * 1024) { // 5MB limit
        $message = "File too large. Maximum size is 5MB.";
        $messageType = 'danger';
    } elseif (pathinfo($uploadedFile['name'], PATHINFO_EXTENSION) !== 'csv') {
        $message = "Please upload a CSV file.";
        $messageType = 'danger';
    } else {
        // Process CSV file
        $csvFile = fopen($uploadedFile['tmp_name'], 'r');
        
        if ($csvFile === false) {
            $message = "Could not read CSV file.";
            $messageType = 'danger';
        } else {
            $header = fgetcsv($csvFile); // Read header row
            $rowNumber = 1;
            $successCount = 0;
            $errorCount = 0;
            $errors = [];
            
            // Expected columns: name, email, phone, student_id, cohort, track, username, password
            $expectedColumns = ['name', 'email', 'phone', 'student_id', 'cohort', 'track', 'username', 'password'];
            
            // Validate header
            if (!$header || count(array_intersect($expectedColumns, array_map('strtolower', $header))) < 6) {
                $message = "Invalid CSV format. Required columns: " . implode(', ', $expectedColumns);
                $messageType = 'danger';
            } else {
                // Process each row
                while (($row = fgetcsv($csvFile)) !== false) {
                    $rowNumber++;
                    
                    if (count($row) < count($header)) {
                        $errors[] = "Row $rowNumber: Incomplete data";
                        $errorCount++;
                        continue;
                    }
                    
                    // Map CSV columns to data
                    $studentData = array_combine(array_map('strtolower', $header), $row);
                    
                    // Validate required fields
                    $name = trim($studentData['name'] ?? '');
                    $email = trim($studentData['email'] ?? '');
                    $phone = trim($studentData['phone'] ?? '');
                    $studentId = trim($studentData['student_id'] ?? '');
                    $cohort = trim($studentData['cohort'] ?? '');
                    $track = trim($studentData['track'] ?? '');
                    $username = trim($studentData['username'] ?? '');
                    $password = trim($studentData['password'] ?? '');
                    
                    // Auto-generate username if not provided
                    if (empty($username)) {
                        $username = strtolower(str_replace(' ', '.', $name));
                        $username = preg_replace('/[^a-z0-9.]/', '', $username);
                    }
                    
                    // Auto-generate password if not provided
                    if (empty($password)) {
                        $password = 'temp' . rand(1000, 9999);
                    }
                    
                    // Validate required fields
                    if (empty($name) || empty($email) || empty($studentId)) {
                        $errors[] = "Row $rowNumber: Missing required fields (name, email, student_id)";
                        $errorCount++;
                        continue;
                    }
                    
                    // Validate email format
                    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                        $errors[] = "Row $rowNumber: Invalid email format";
                        $errorCount++;
                        continue;
                    }
                    
                    try {
                        // Check if username already exists
                        $checkUser = $pdo->prepare("SELECT id FROM users WHERE username = ?");
                        $checkUser->execute([$username]);
                        if ($checkUser->fetch()) {
                            $errors[] = "Row $rowNumber: Username '$username' already exists";
                            $errorCount++;
                            continue;
                        }
                        
                        // Check if email already exists
                        $checkEmail = $pdo->prepare("SELECT id FROM student_profiles WHERE email = ?");
                        $checkEmail->execute([$email]);
                        if ($checkEmail->fetch()) {
                            $errors[] = "Row $rowNumber: Email '$email' already exists";
                            $errorCount++;
                            continue;
                        }
                        
                        // Check if student ID already exists
                        $checkStudentId = $pdo->prepare("SELECT id FROM student_profiles WHERE student_id = ?");
                        $checkStudentId->execute([$studentId]);
                        if ($checkStudentId->fetch()) {
                            $errors[] = "Row $rowNumber: Student ID '$studentId' already exists";
                            $errorCount++;
                            continue;
                        }
                        
                        // Start transaction
                        $pdo->beginTransaction();
                        
                        // Create user account
                        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
                        $userStmt = $pdo->prepare("
                            INSERT INTO users (username, password_hash, role, created_at) 
                            VALUES (?, ?, 'student', NOW())
                        ");
                        $userStmt->execute([$username, $hashedPassword]);
                        $userId = $pdo->lastInsertId();
                        
                        // Create student profile
                        $profileStmt = $pdo->prepare("
                            INSERT INTO student_profiles (username, name, email, phone, student_id, cohort, track, created_at) 
                            VALUES (?, ?, ?, ?, ?, ?, ?, NOW())
                        ");
                        $profileStmt->execute([$username, $name, $email, $phone, $studentId, $cohort, $track]);
                        
                        // Commit transaction
                        $pdo->commit();
                        
                        $successCount++;
                        $importResults[] = [
                            'row' => $rowNumber,
                            'name' => $name,
                            'username' => $username,
                            'password' => $password,
                            'status' => 'success'
                        ];
                        
                    } catch (Exception $e) {
                        $pdo->rollBack();
                        $errors[] = "Row $rowNumber: Database error - " . $e->getMessage();
                        $errorCount++;
                    }
                }
                
                fclose($csvFile);
                
                // Set result message
                if ($successCount > 0 && $errorCount === 0) {
                    $message = "✅ Successfully imported $successCount students!";
                    $messageType = 'success';
                } elseif ($successCount > 0 && $errorCount > 0) {
                    $message = "⚠️ Imported $successCount students with $errorCount errors.";
                    $messageType = 'warning';
                } else {
                    $message = "❌ Import failed. $errorCount errors found.";
                    $messageType = 'danger';
                }
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Import Students - Eagle Flight School</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        :root {
            --primary-purple: #6B46C1;
            --dark-purple: #4C1D95;
        }
        
        body {
            background: linear-gradient(135deg, #0F0F0F 0%, #4C1D95 50%, #6B46C1 100%);
            min-height: 100vh;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        .page-header {
            background: linear-gradient(135deg, var(--primary-purple) 0%, var(--dark-purple) 100%);
            color: white;
            padding: 30px;
            border-radius: 15px;
            margin-bottom: 30px;
            box-shadow: 0 10px 40px rgba(107, 70, 193, 0.4);
        }
        
        .import-card {
            background: white;
            border-radius: 15px;
            padding: 30px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            margin-bottom: 20px;
        }
        
        .btn-purple {
            background: linear-gradient(135deg, var(--primary-purple) 0%, var(--dark-purple) 100%);
            border: none;
            color: white;
        }
        
        .btn-purple:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(107, 70, 193, 0.4);
            color: white;
        }
        
        .csv-format {
            background: #f8f9fa;
            border-left: 4px solid var(--primary-purple);
            padding: 15px;
            border-radius: 8px;
            font-family: monospace;
        }
        
        .results-table {
            max-height: 400px;
            overflow-y: auto;
        }
    </style>
</head>
<body>
<div class="container py-5">
    <div class="page-header">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h2>📊 Import Students from CSV</h2>
                <p class="mb-0">Bulk enroll multiple students at once</p>
            </div>
            <a href="admin_dashboard.php" class="btn btn-light">← Back to Dashboard</a>
        </div>
    </div>

    <!-- Messages -->
    <?php if ($message): ?>
        <div class="alert alert-<?= $messageType ?> alert-dismissible fade show" role="alert">
            <?= $message ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <!-- Upload Form -->
    <div class="import-card">
        <h4 class="mb-4">📁 Upload CSV File</h4>
        
        <form method="POST" enctype="multipart/form-data">
            <div class="mb-4">
                <label for="csv_file" class="form-label fw-bold">Select CSV File</label>
                <input type="file" name="csv_file" id="csv_file" class="form-control" accept=".csv" required>
                <div class="form-text">Maximum file size: 5MB. Only CSV files are accepted.</div>
            </div>
            
            <button type="submit" class="btn btn-purple btn-lg">
                <i class="fas fa-upload me-2"></i>Import Students
            </button>
        </form>
    </div>

    <!-- Quick Guide -->
    <div class="import-card">
        <div class="row align-items-center">
            <div class="col-md-8">
                <h5 class="mb-2">📋 CSV Format</h5>
                <p class="mb-0 text-muted">Required: name, email, student_id | Optional: phone, cohort, track, username, password</p>
            </div>
            <div class="col-md-4 text-end">
                <a href="sample_students.csv" class="btn btn-outline-primary" download>
                    <i class="fas fa-download me-2"></i>Download Sample
                </a>
            </div>
        </div>
    </div>

    <!-- Import Results -->
    <?php if (!empty($importResults)): ?>
    <div class="import-card">
        <h4 class="mb-4">📊 Import Results</h4>
        
        <div class="results-table">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>Row</th>
                        <th>Name</th>
                        <th>Username</th>
                        <th>Password</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($importResults as $result): ?>
                        <tr>
                            <td><?= $result['row'] ?></td>
                            <td><?= htmlspecialchars($result['name']) ?></td>
                            <td><?= htmlspecialchars($result['username']) ?></td>
                            <td><code><?= htmlspecialchars($result['password']) ?></code></td>
                            <td><span class="badge bg-success">✅ Success</span></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        
        <div class="alert alert-success mt-3">
            <i class="fas fa-check-circle me-2"></i>
            <strong>Next Steps:</strong>
            <ul class="mb-0 mt-2">
                <li>Share login credentials with students</li>
                <li>Students should change their passwords on first login</li>
                <li>Check <a href="view_students.php">View Students</a> to see all enrolled students</li>
            </ul>
        </div>
    </div>
    <?php endif; ?>

    <!-- Error Details -->
    <?php if (!empty($errors)): ?>
    <div class="import-card">
        <h4 class="mb-4 text-danger">❌ Import Errors</h4>
        
        <div class="alert alert-danger">
            <ul class="mb-0">
                <?php foreach (array_slice($errors, 0, 10) as $error): ?>
                    <li><?= htmlspecialchars($error) ?></li>
                <?php endforeach; ?>
                <?php if (count($errors) > 10): ?>
                    <li><em>... and <?= count($errors) - 10 ?> more errors</em></li>
                <?php endif; ?>
            </ul>
        </div>
    </div>
    <?php endif; ?>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>