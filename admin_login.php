<?php
session_start();
include 'db.php';

$error = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'];
    $password = $_POST['password'];

    $stmt = $pdo->prepare("SELECT * FROM admins WHERE email = ?");
    $stmt->execute([$email]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($row && password_verify($password, $row['password'])) {
        // Login success - Set all required session variables
        $_SESSION['admin_id'] = $row['id'];
        $_SESSION['user_id'] = $row['id'];  // For auth.php compatibility
        $_SESSION['username'] = $row['email'];  // Use email as username for display
        $_SESSION['role'] = $row['role'];
        header("Location: admin_dashboard.php");
        exit;
    } else {
        $error = "Invalid email or password.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Eagle Flight School - Admin Login</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #1e3a8a 0%, #3b82f6 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        .login-container {
            background: white;
            border-radius: 16px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
            width: 100%;
            max-width: 420px;
            padding: 40px;
            animation: slideIn 0.5s ease-out;
        }

        @keyframes slideIn {
            from {
                opacity: 0;
                transform: translateY(-30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .logo-section {
            text-align: center;
            margin-bottom: 30px;
        }

        .logo-section h1 {
            color: #1e3a8a;
            font-size: 32px;
            font-weight: 700;
            margin-bottom: 8px;
            letter-spacing: -0.5px;
        }

        .logo-section p {
            color: #6b7280;
            font-size: 16px;
        }

        .admin-badge {
            display: inline-block;
            background: linear-gradient(135deg, #1e3a8a 0%, #3b82f6 100%);
            color: white;
            padding: 6px 16px;
            border-radius: 20px;
            font-size: 13px;
            font-weight: 600;
            margin-bottom: 20px;
        }

        .form-group {
            margin-bottom: 24px;
        }

        label {
            display: block;
            color: #374151;
            font-weight: 600;
            margin-bottom: 8px;
            font-size: 14px;
        }

        input[type="email"],
        input[type="password"] {
            width: 100%;
            padding: 12px 16px;
            border: 2px solid #e5e7eb;
            border-radius: 8px;
            font-size: 15px;
            transition: all 0.3s ease;
            outline: none;
        }

        input[type="email"]:focus,
        input[type="password"]:focus {
            border-color: #3b82f6;
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
        }

        .error-message {
            background: #fee2e2;
            color: #dc2626;
            padding: 12px 16px;
            border-radius: 8px;
            margin-bottom: 20px;
            font-size: 14px;
            border-left: 4px solid #dc2626;
        }

        .login-btn {
            width: 100%;
            padding: 14px;
            background: linear-gradient(135deg, #1e3a8a 0%, #3b82f6 100%);
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            margin-top: 8px;
        }

        .login-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(30, 58, 138, 0.3);
        }

        .login-btn:active {
            transform: translateY(0);
        }

        .info-section {
            margin-top: 30px;
            padding-top: 24px;
            border-top: 1px solid #e5e7eb;
        }

        .info-text {
            color: #6b7280;
            font-size: 13px;
            line-height: 1.6;
            margin-bottom: 12px;
            text-align: center;
        }

        .student-link {
            text-align: center;
            margin-top: 20px;
        }

        .student-link a {
            color: #3b82f6;
            text-decoration: none;
            font-size: 14px;
            font-weight: 500;
        }

        .student-link a:hover {
            text-decoration: underline;
        }

        .footer {
            text-align: center;
            margin-top: 24px;
            padding-top: 20px;
            border-top: 1px solid #e5e7eb;
            color: #9ca3af;
            font-size: 12px;
        }

        @media (max-width: 480px) {
            .login-container {
                padding: 30px 24px;
            }

            .logo-section h1 {
                font-size: 28px;
            }
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="logo-section">
            <h1>🦅 Eagle Flight School</h1>
            <span class="admin-badge">🔐 Admin Portal</span>
            <p>Administrative Access</p>
        </div>

        <form method="POST">
            <?php if ($error): ?>
                <div class="error-message">
                    <?= htmlspecialchars($error) ?>
                </div>
            <?php endif; ?>

            <div class="form-group">
                <label for="email">Email Address</label>
                <input type="email" id="email" name="email" required placeholder="Enter your admin email">
            </div>

            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" required placeholder="Enter your password">
            </div>

            <button type="submit" class="login-btn">Login to Admin Panel</button>
        </form>

        <div class="student-link">
            <a href="studentlogin.php">← Student Login</a>
        </div>

        <div class="info-section">
            <p class="info-text">Authorized personnel only. All access is monitored and logged.</p>
        </div>

        <div class="footer">
            © <?= date('Y') ?> Eagle Flight School. All rights reserved.
        </div>
    </div>
</body>
</html>
