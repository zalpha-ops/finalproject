<?php
session_start();
require 'db.php';

$error = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    
    // Debug: Uncomment to see what's being submitted
    // $error = "DEBUG: Username='$username', Password length=" . strlen($password);

    if (empty($username) || empty($password)) {
        $error = "Username and password are required.";
    } else {
        // Try to authenticate user across all roles
        $authenticated = false;
        
        // 1. Try STUDENT LOGIN first
        if (!$authenticated) {
            try {
                // Try users table for students
                $stmt = $pdo->prepare("SELECT id, username, password, role 
                                       FROM users WHERE username = :username AND role = 'student' LIMIT 1");
                $stmt->execute([':username' => $username]);
                $user = $stmt->fetch(PDO::FETCH_ASSOC);
                $passwordField = 'password';
            } catch (Exception $e) {
                $user = null;
            }

            if ($user) {
                $passwordHash = $user[$passwordField] ?? '';
                $isValid = false;
                
                // Try SHA-256 hash first (for users table)
                if (hash('sha256', $password) === $passwordHash) {
                    $isValid = true;
                }
                // Try bcrypt verification (for student_profiles table)
                elseif (password_verify($password, $passwordHash)) {
                    $isValid = true;
                }
                
                // Check status (handle both 'Active' and 'active')
                $status = strtolower($user['status'] ?? 'active');
                if ($isValid && $status === 'active') {
                    $_SESSION['user_id'] = $user['id'];
                    $_SESSION['username'] = $user['username'];
                    $_SESSION['role'] = 'student';
                    $_SESSION['name'] = $user['name'] ?? $user['username'];
                    $authenticated = true;
                    header("Location: dashboard.php");
                    exit;
                } elseif ($isValid && $status !== 'active') {
                    $error = "Your account is not active. Status: " . $user['status'];
                }
            }
        }

        // 2. Try INSTRUCTOR LOGIN
        if (!$authenticated) {
            try {
                $stmt = $pdo->prepare("SELECT instructor_id, name, username, password, status 
                                       FROM instructors WHERE username = :username LIMIT 1");
                $stmt->execute([':username' => $username]);
                $user = $stmt->fetch(PDO::FETCH_ASSOC);
                $passwordField = 'password';
            } catch (Exception $e) {
                $user = null;
            }

            if ($user && strtolower($user['status'] ?? 'active') === 'active' && password_verify($password, $user[$passwordField] ?? '')) {
                $_SESSION['instructor_id'] = $user['instructor_id'];
                $_SESSION['instructor_name'] = $user['name'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['user_id'] = $user['instructor_id']; // For auth.php compatibility
                $_SESSION['role'] = 'instructor';
                $authenticated = true;
                header("Location: instructor_dashboard.php");
                exit;
            }
        }

        // 3. Try ADMIN LOGIN
        if (!$authenticated) {
            try {
                $stmt = $pdo->prepare("SELECT id, email, username, password, role 
                                       FROM users WHERE username = :username AND role = 'admin' LIMIT 1");
                $stmt->execute([':username' => $username]);
                $user = $stmt->fetch(PDO::FETCH_ASSOC);
                
                if ($user && password_verify($password, $user['password'] ?? '')) {
                    $_SESSION['admin_id'] = $user['id'];
                    $_SESSION['user_id'] = $user['id'];
                    $_SESSION['username'] = $user['username'];
                    $_SESSION['email'] = $user['email'];
                    $_SESSION['role'] = 'admin';
                    $authenticated = true;
                    header("Location: admin_dashboard.php");
                    exit;
                }
            } catch (Exception $e) {
                // Admin login failed
            }
        }
        
        // If we reach here, authentication failed
        if (!$authenticated) {
            $error = "Invalid username or password.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Eagle Flight School - Login</title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <style>
    :root {
      --primary-purple: #6B46C1;
      --dark-purple: #4C1D95;
      --light-purple: #9333EA;
      --accent-purple: #A855F7;
      --black: #0F0F0F;
      --dark-gray: #1A1A1A;
      --white: #FFFFFF;
      --gray-100: #F8F9FA;
      --gray-200: #E9ECEF;
      --gray-600: #6C757D;
    }
    
    body {
      margin: 0;
      padding: 0;
      min-height: 100vh;
      font-family: 'Inter', 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
      overflow-x: hidden;
    }
    
    .login-wrapper {
      display: flex;
      min-height: 100vh;
      position: relative;
    }
    
    .aviation-side {
      flex: 1;
      background: linear-gradient(
        135deg,
        rgba(0, 0, 0, 0.4) 0%,
        rgba(107, 70, 193, 0.3) 50%,
        rgba(0, 0, 0, 0.6) 100%
      ),
      url('https://images.unsplash.com/photo-1436491865332-7a61a109cc05?ixlib=rb-4.0.3&auto=format&fit=crop&w=2074&q=80') center/cover;
      position: relative;
      display: flex;
      align-items: center;
      justify-content: center;
      color: white;
    }
    
    .aviation-content {
      text-align: center;
      z-index: 2;
      padding: 40px;
      max-width: 500px;
    }
    
    .aviation-content h1 {
      font-size: 3.5rem;
      font-weight: 800;
      margin-bottom: 1rem;
      text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.5);
      background: linear-gradient(135deg, #ffffff 0%, #e2e8f0 100%);
      -webkit-background-clip: text;
      -webkit-text-fill-color: transparent;
      background-clip: text;
    }
    
    .aviation-content p {
      font-size: 1.25rem;
      margin-bottom: 2rem;
      text-shadow: 1px 1px 2px rgba(0, 0, 0, 0.5);
      opacity: 0.95;
    }
    
    .aviation-features {
      display: grid;
      grid-template-columns: 1fr 1fr;
      gap: 1.5rem;
      margin-top: 2rem;
    }
    
    .feature-item {
      background: rgba(255, 255, 255, 0.1);
      backdrop-filter: blur(10px);
      border: 1px solid rgba(255, 255, 255, 0.2);
      border-radius: 12px;
      padding: 1.5rem;
      text-align: center;
      transition: transform 0.3s ease;
    }
    
    .feature-item:hover {
      transform: translateY(-5px);
      background: rgba(255, 255, 255, 0.15);
    }
    
    .feature-icon {
      font-size: 2rem;
      margin-bottom: 0.5rem;
      display: block;
    }
    
    .login-side {
      flex: 0 0 480px;
      background: white;
      display: flex;
      align-items: center;
      justify-content: center;
      padding: 2rem;
      box-shadow: -10px 0 30px rgba(0, 0, 0, 0.1);
      position: relative;
      z-index: 3;
    }
    
    .login-container {
      width: 100%;
      max-width: 400px;
    }
    
    .login-card {
      background: white;
      border-radius: 0;
      box-shadow: none;
      border: none;
      height: 100%;
      display: flex;
      flex-direction: column;
    }
    
    .login-header {
      background: white;
      color: var(--dark-purple);
      padding: 0 0 2rem 0;
      text-align: center;
      position: relative;
    }
    
    .company-logo {
      width: 80px;
      height: 80px;
      background: linear-gradient(135deg, var(--primary-purple) 0%, var(--dark-purple) 100%);
      border-radius: 20px;
      display: flex;
      align-items: center;
      justify-content: center;
      margin: 0 auto 1.5rem;
      font-size: 32px;
      color: white;
      box-shadow: 0 10px 30px rgba(107, 70, 193, 0.3);
    }
    
    .login-header h1 {
      font-size: 28px;
      font-weight: 800;
      margin-bottom: 8px;
      letter-spacing: -0.025em;
      color: var(--dark-purple);
    }
    
    .login-header p {
      font-size: 16px;
      color: var(--gray-600);
      margin: 0;
      font-weight: 400;
    }
    
    .login-body {
      flex: 1;
      display: flex;
      flex-direction: column;
      justify-content: center;
    }
    
    .form-group {
      margin-bottom: 20px;
    }
    
    .form-label {
      display: block;
      font-size: 14px;
      font-weight: 500;
      color: #374151;
      margin-bottom: 6px;
    }
    
    .form-control {
      width: 100%;
      padding: 12px 16px;
      font-size: 16px;
      border: 1px solid #d1d5db;
      border-radius: 8px;
      background: white;
      transition: all 0.2s ease;
      font-family: inherit;
    }
    
    .form-control:focus {
      outline: none;
      border-color: var(--primary-purple);
      box-shadow: 0 0 0 3px rgba(107, 70, 193, 0.1);
    }
    
    .form-control::placeholder {
      color: #9ca3af;
    }
    
    .btn-login {
      width: 100%;
      background: linear-gradient(135deg, var(--primary-purple) 0%, var(--dark-purple) 100%);
      border: none;
      color: white;
      padding: 12px 24px;
      font-size: 16px;
      font-weight: 600;
      border-radius: 8px;
      cursor: pointer;
      transition: all 0.2s ease;
      display: flex;
      align-items: center;
      justify-content: center;
      gap: 8px;
    }
    
    .btn-login:hover {
      transform: translateY(-1px);
      box-shadow: 0 4px 12px rgba(107, 70, 193, 0.4);
    }
    
    .btn-login:active {
      transform: translateY(0);
    }
    
    .btn-login:disabled {
      opacity: 0.6;
      cursor: not-allowed;
      transform: none;
    }
    
    .divider {
      display: flex;
      align-items: center;
      margin: 24px 0;
      color: #6b7280;
      font-size: 14px;
    }
    
    .divider::before,
    .divider::after {
      content: '';
      flex: 1;
      height: 1px;
      background: #e5e7eb;
    }
    
    .divider span {
      padding: 0 16px;
    }
    
    .forgot-password {
      display: inline-flex;
      align-items: center;
      gap: 4px;
      color: var(--primary-purple);
      text-decoration: none;
      font-size: 14px;
      font-weight: 500;
      transition: color 0.2s ease;
    }
    
    .forgot-password:hover {
      color: var(--dark-purple);
    }
    
    .help-text {
      text-align: center;
      margin-top: 24px;
      padding-top: 24px;
      border-top: 1px solid #e5e7eb;
    }
    
    .help-text p {
      color: #6b7280;
      font-size: 14px;
      margin: 0 0 12px;
    }
    
    .whatsapp-link {
      display: inline-flex;
      align-items: center;
      gap: 6px;
      color: #25D366;
      text-decoration: none;
      font-weight: 500;
      font-size: 14px;
      transition: color 0.2s ease;
    }
    
    .whatsapp-link:hover {
      color: #128C7E;
    }
    
    .alert {
      padding: 12px 16px;
      border-radius: 8px;
      margin-bottom: 20px;
      font-size: 14px;
      border: 1px solid transparent;
    }
    
    .alert-danger {
      background: #fef2f2;
      border-color: #fecaca;
      color: #dc2626;
    }
    
    .loading-spinner {
      width: 20px;
      height: 20px;
      border: 2px solid transparent;
      border-top: 2px solid currentColor;
      border-radius: 50%;
      animation: spin 1s linear infinite;
    }
    
    @keyframes spin {
      to { transform: rotate(360deg); }
    }
    
    /* Responsive adjustments */
    @media (max-width: 1024px) {
      .login-wrapper {
        flex-direction: column;
      }
      
      .aviation-side {
        flex: 0 0 40vh;
        min-height: 40vh;
      }
      
      .aviation-content h1 {
        font-size: 2.5rem;
      }
      
      .aviation-features {
        grid-template-columns: 1fr;
        gap: 1rem;
      }
      
      .login-side {
        flex: 1;
        padding: 2rem 1rem;
      }
    }
    
    @media (max-width: 768px) {
      .aviation-side {
        flex: 0 0 35vh;
        min-height: 35vh;
      }
      
      .aviation-content {
        padding: 20px;
      }
      
      .aviation-content h1 {
        font-size: 2rem;
      }
      
      .aviation-content p {
        font-size: 1rem;
      }
      
      .feature-item {
        padding: 1rem;
      }
      
      .login-side {
        padding: 1.5rem 1rem;
      }
      
      .company-logo {
        width: 60px;
        height: 60px;
        font-size: 24px;
      }
      
      .login-header h1 {
        font-size: 24px;
      }
    }
    
    @media (max-width: 480px) {
      .aviation-content h1 {
        font-size: 1.75rem;
      }
      
      .login-side {
        padding: 1rem;
      }
      
      .company-logo {
        width: 50px;
        height: 50px;
        font-size: 20px;
      }
      
      .login-header h1 {
        font-size: 20px;
      }
    }
  </style>
</head>
<body>
<div class="login-wrapper">
  <!-- Aviation Side -->
  <div class="aviation-side">
    <div class="aviation-content">
      <h1>Soar to New Heights</h1>
      <p>Professional flight training for the next generation of aviators</p>
      
      <div class="aviation-features">
        <div class="feature-item">
          <span class="feature-icon">✈️</span>
          <h4>Professional Training</h4>
          <p>Expert instructors with years of experience</p>
        </div>
        <div class="feature-item">
          <span class="feature-icon">🎯</span>
          <h4>Modern Fleet</h4>
          <p>State-of-the-art aircraft and simulators</p>
        </div>
        <div class="feature-item">
          <span class="feature-icon">📚</span>
          <h4>Comprehensive Courses</h4>
          <p>From PPL to ATPL certification programs</p>
        </div>
        <div class="feature-item">
          <span class="feature-icon">🏆</span>
          <h4>Success Rate</h4>
          <p>95% first-time pass rate on checkrides</p>
        </div>
      </div>
    </div>
  </div>

  <!-- Login Side -->
  <div class="login-side">
    <div class="login-container">
      <div class="login-card">
        <div class="login-header">
          <div class="company-logo">
            <i class="fas fa-plane"></i>
          </div>
          <h1>Eagle Flight School</h1>
          <p>Sign in to your account</p>
        </div>
        
        <div class="login-body">
          <?php if ($error): ?>
            <div class="alert alert-danger">
              <i class="fas fa-exclamation-circle me-2"></i>
              <?= htmlspecialchars($error) ?>
            </div>
          <?php endif; ?>

          <form method="post" id="loginForm">
            <div class="form-group">
              <label class="form-label">Username</label>
              <input 
                type="text" 
                name="username" 
                class="form-control" 
                placeholder="Enter your username"
                required 
                autocomplete="username"
                autofocus
              >
            </div>

            <div class="form-group">
              <label class="form-label">Password</label>
              <input 
                type="password" 
                name="password" 
                class="form-control" 
                placeholder="Enter your password"
                required 
                autocomplete="current-password"
              >
            </div>

            <button type="submit" class="btn-login" id="loginBtn">
              <span class="login-text">
                <i class="fas fa-sign-in-alt me-2"></i>
                Sign In
              </span>
              <div class="loading-spinner" style="display: none;"></div>
            </button>
          </form>

          <div class="divider">
            <span>Need help?</span>
          </div>

          <div style="text-align: center;">
            <a href="forgot_password.php" class="forgot-password">
              <i class="fas fa-key"></i>
              Forgot your password?
            </a>
          </div>

          <div class="help-text">
            <p>Having trouble signing in?</p>
            <a href="https://wa.me/254768503219?text=Hello%20Eagle%20Flight%20School,%20I%20need%20help%20with%20login" 
               class="whatsapp-link" 
               target="_blank">
              <i class="fab fa-whatsapp"></i>
              Contact Support
            </a>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
// Professional login form handling
document.getElementById('loginForm').addEventListener('submit', function(e) {
  const loginBtn = document.getElementById('loginBtn');
  const loginText = loginBtn.querySelector('.login-text');
  const spinner = loginBtn.querySelector('.loading-spinner');
  
  // Show loading state
  loginBtn.disabled = true;
  loginText.style.display = 'none';
  spinner.style.display = 'block';
  
  // Re-enable after 10 seconds as fallback
  setTimeout(() => {
    loginBtn.disabled = false;
    loginText.style.display = 'flex';
    spinner.style.display = 'none';
  }, 10000);
});

// Auto-focus username field
document.querySelector('input[name="username"]').focus();
</script>
</body>
</html>
