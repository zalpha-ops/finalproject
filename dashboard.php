<?php
require 'auth.php'; // makes sure user is logged in
// Optional: ensure only students can access
require_role('student');

// Check if profile is complete
require 'db_connect.php';
include 'check_profile_completion.php';
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Eagle Flight School â€” Student Dashboard</title>

  <!-- Bootstrap CSS -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" />

  <style>
    :root {
      --primary-purple: #6B46C1;
      --dark-purple: #4C1D95;
      --light-purple: #9333EA;
      --black: #0F0F0F;
      --white: #FFFFFF;
    }
    
    body { 
      background: linear-gradient(135deg, var(--black) 0%, var(--dark-purple) 50%, var(--primary-purple) 100%);
      min-height: 100vh;
    }
    
    .navbar { 
      position: fixed;
      top: 0;
      left: 0;
      right: 0;
      z-index: 1030;
      background: linear-gradient(135deg, var(--primary-purple) 0%, var(--dark-purple) 100%);
      box-shadow: 0 5px 20px rgba(107, 70, 193, 0.4);
    }
    
    .navbar-brand, .nav-link, .btn-logout { color: #fff !important; }
    
    .sidebar {
      position: fixed;
      left: 0;
      top: 56px;
      bottom: 0;
      width: 250px;
      z-index: 1000;
      background: linear-gradient(180deg, var(--dark-purple) 0%, var(--black) 100%);
      color: #e5e7eb;
      padding-top: 1rem;
      transition: transform 0.3s ease-in-out;
      box-shadow: 5px 0 20px rgba(0,0,0,0.3);
      overflow-y: auto;
      overflow-x: hidden;
    }
    
    .sidebar a {
      color: #cbd5e1;
      text-decoration: none;
      display: block;
      padding: .75rem 1rem;
      border-radius: .5rem;
      cursor: pointer;
      transition: all 0.3s ease;
      margin: 5px 10px;
    }
    
    .sidebar a:hover {
      background: linear-gradient(135deg, var(--primary-purple), var(--light-purple));
      color: #fff;
      transform: translateX(5px);
    }
    
    .sidebar a.active {
      background: linear-gradient(135deg, var(--primary-purple), var(--light-purple));
      color: #fff;
      box-shadow: 0 5px 15px rgba(107, 70, 193, 0.4);
    }
    
    /* Simple scrolling setup */
    html, body {
      margin: 0;
      padding: 0;
      height: 100%;
      overflow: hidden;
    }
    
    main { 
      position: absolute;
      top: 56px;
      left: 250px;
      right: 0;
      bottom: 0;
      background: #f8fafc;
      padding: 30px;
      overflow-y: scroll;
      overflow-x: hidden;
    }
    
    /* Content container */
    #main-content {
      width: 100%;
      max-width: 100%;
      min-height: 100%;
      padding-bottom: 50px;
    }
    
    /* Prevent layout shift */
    * {
      box-sizing: border-box;
    }
    
    /* Container fluid fix */
    .container-fluid {
      padding: 0;
      margin: 0;
      width: 100%;
    }
    
    /* Student Dashboard Header */
    .dashboard-header-student {
      background: linear-gradient(135deg, var(--primary-purple) 0%, var(--dark-purple) 100%);
      color: white;
      padding: 40px;
      border-radius: 15px;
      margin-bottom: 30px;
      box-shadow: 0 10px 40px rgba(107, 70, 193, 0.4);
    }
    
    /* Student Dashboard Cards */
    .dashboard-card-student {
      background: white;
      border-radius: 15px;
      padding: 30px;
      height: 100%;
      min-height: 280px;
      transition: all 0.3s;
      border: 2px solid transparent;
      box-shadow: 0 5px 15px rgba(0,0,0,0.1);
    }
    
    .dashboard-card-student:hover {
      transform: translateY(-5px);
      border-color: var(--primary-purple);
      box-shadow: 0 15px 40px rgba(107, 70, 193, 0.3);
    }
    
    .card-icon-student {
      font-size: 3.5rem;
      margin-bottom: 15px;
    }
    
    .dashboard-card-student h4 {
      color: #1e293b;
      font-weight: 600;
    }
    
    .btn-purple-student {
      background: linear-gradient(135deg, var(--primary-purple) 0%, var(--dark-purple) 100%);
      border: none;
      color: white;
      padding: 12px;
      font-weight: 600;
      transition: all 0.3s;
      border-radius: 8px;
    }
    
    .btn-purple-student:hover {
      transform: translateY(-2px);
      box-shadow: 0 10px 25px rgba(107, 70, 193, 0.4);
      color: white;
    }
    
    /* Grid layout */
    .row {
      margin: 0;
    }
    
    .g-4 {
      gap: 1.5rem;
    }
    
    /* Hamburger button - ONLY show on mobile */
    #sidebarToggle {
      border: none;
      font-size: 1.5rem;
      padding: 0.25rem 0.5rem;
      display: none; /* Hidden by default on desktop */
    }
    
    /* Mobile styles */
    @media (max-width: 768px) {
      /* Show hamburger ONLY on mobile */
      #sidebarToggle {
        display: block !important;
      }
      
      .sidebar {
        transform: translateX(-100%);
      }
      .sidebar.show {
        transform: translateX(0);
      }
      main {
        left: 0 !important;
        right: 0 !important;
      }
      .sidebar-overlay {
        display: none;
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0,0,0,0.5);
        z-index: 999;
      }
      .sidebar-overlay.show {
        display: block;
      }
    }
    
    /* Desktop styles */
    @media (min-width: 769px) {
      .sidebar.hide {
        transform: translateX(-100%);
      }
      main.expanded {
        left: 0 !important;
      }
    }
  </style>
</head>
<body>
  <!-- Navbar -->
  <nav class="navbar navbar-expand-lg">
    <div class="container-fluid">
      <!-- Hamburger Menu Button -->
      <button class="btn btn-outline-light me-3" id="sidebarToggle" type="button">
        ☰
      </button>
      <a class="navbar-brand fw-bold" href="#">Eagle Flight School</a>
      
      <!-- User Profile Dropdown -->
      <div class="ms-auto">
        <div class="dropdown">
          <button class="btn btn-outline-light dropdown-toggle" type="button" id="userDropdown" data-bs-toggle="dropdown" aria-expanded="false">
            <span class="me-2">👤</span>
            <?= htmlspecialchars($_SESSION['username']) ?>
          </button>
          <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="userDropdown" style="min-width: 300px;">
            <li class="px-3 py-2 border-bottom">
              <div class="d-flex align-items-center">
                <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center" style="width: 50px; height: 50px; font-size: 24px;">
                  <?= strtoupper(substr($_SESSION['username'], 0, 1)) ?>
                </div>
                <div class="ms-3">
                  <div class="fw-bold"><?= htmlspecialchars($_SESSION['username']) ?></div>
                  <small class="text-muted">Student</small>
                </div>
              </div>
            </li>
            <li><a class="dropdown-item" href="#" onclick="loadUserProfile(); return false;">
              <span class="me-2">👤</span> View Profile
            </a></li>
            <li><a class="dropdown-item" href="#" onclick="loadEditProfile(); return false;">
              <span class="me-2">✏️</span> Edit Profile
            </a></li>
            <li><hr class="dropdown-divider"></li>
            <li><a class="dropdown-item text-danger" href="logout.php">
              <span class="me-2">🚪</span> Logout
            </a></li>
          </ul>
        </div>
      </div>
    </div>
  </nav>

  <!-- Sidebar Overlay for Mobile -->
  <div class="sidebar-overlay" id="sidebarOverlay"></div>

  <div class="container-fluid">
    <div class="row">
      <!-- Sidebar -->
      <nav class="sidebar" id="sidebar">
        <div class="position-sticky">
           <a data-page="home.php" class="active">🏠 Home</a>
          <a data-page="course.php">📘 Courses</a>
          <a data-page="sheduled.php">📅 Schedule</a>
         <a data-page="grade.php">📊 Grades</a>
          <a data-page="announcement.php">📢 Announcements</a>
           <a data-page="progress.php">📈 Progress</a>
        </div>
      </nav>

      <!-- Main content -->
      <main id="main-content">
        <?php if (isset($_SESSION['profile_incomplete']) && $_SESSION['profile_incomplete']): ?>
          <!-- First Login / Profile Incomplete Alert -->
          <div class="alert alert-warning alert-dismissible fade show" role="alert">
            <h5 class="alert-heading">ðŸ‘‹ Welcome to Eagle Flight School!</h5>
            <p class="mb-2">It looks like this is your first time logging in. Please complete your profile to get started.</p>
            <hr>
            <button type="button" class="btn btn-primary" onclick="loadProfile()">
              Complete My Profile Now â†’
            </button>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
          </div>
        <?php endif; ?>
        
        <!-- Dashboard Header -->
        <div class="dashboard-header-student">
            <h2 class="mb-2">Welcome back, <?= htmlspecialchars($_SESSION['username']) ?>! 🎓</h2>
            <p class="mb-0 opacity-75">Ready to continue your learning journey?</p>
        </div>

        <!-- Student Dashboard Cards -->
        <div class="row g-4">
            <!-- My Courses Card -->
            <div class="col-lg-4 col-md-6">
                <div class="dashboard-card-student text-center" onclick="document.querySelector('[data-page=\'course.php\']').click()" style="cursor: pointer;">
                    <div class="card-icon-student">📘</div>
                    <h4 class="mb-3">My Courses</h4>
                    <p class="text-muted mb-4">View and access your enrolled courses</p>
                    <div class="d-grid">
                        <button class="btn btn-purple-student">View Courses</button>
                    </div>
                </div>
            </div>

            <!-- My Grades Card -->
            <div class="col-lg-4 col-md-6">
                <div class="dashboard-card-student text-center" onclick="document.querySelector('[data-page=\'grade.php\']').click()" style="cursor: pointer;">
                    <div class="card-icon-student">📊</div>
                    <h4 class="mb-3">My Grades</h4>
                    <p class="text-muted mb-4">Check your performance and results</p>
                    <div class="d-grid">
                        <button class="btn btn-purple-student">View Grades</button>
                    </div>
                </div>
            </div>

            <!-- Schedule Card -->
            <div class="col-lg-4 col-md-6">
                <div class="dashboard-card-student text-center" onclick="document.querySelector('[data-page=\'sheduled.php\']').click()" style="cursor: pointer;">
                    <div class="card-icon-student">📅</div>
                    <h4 class="mb-3">Schedule</h4>
                    <p class="text-muted mb-4">View your class schedule and events</p>
                    <div class="d-grid">
                        <button class="btn btn-purple-student">View Schedule</button>
                    </div>
                </div>
            </div>

            <!-- Progress Card -->
            <div class="col-lg-4 col-md-6">
                <div class="dashboard-card-student text-center" onclick="document.querySelector('[data-page=\'progress.php\']').click()" style="cursor: pointer;">
                    <div class="card-icon-student">📈</div>
                    <h4 class="mb-3">Progress</h4>
                    <p class="text-muted mb-4">Monitor your learning progress</p>
                    <div class="d-grid">
                        <button class="btn btn-purple-student">View Progress</button>
                    </div>
                </div>
            </div>

            <!-- Announcements Card -->
            <div class="col-lg-4 col-md-6">
                <div class="dashboard-card-student text-center" onclick="document.querySelector('[data-page=\'announcement.php\']').click()" style="cursor: pointer;">
                    <div class="card-icon-student">📢</div>
                    <h4 class="mb-3">Announcements</h4>
                    <p class="text-muted mb-4">View important announcements</p>
                    <div class="d-grid">
                        <button class="btn btn-purple-student">View Announcements</button>
                    </div>
                </div>
            </div>

            <!-- My Profile Card -->
            <div class="col-lg-4 col-md-6">
                <div class="dashboard-card-student text-center" onclick="loadUserProfile()" style="cursor: pointer;">
                    <div class="card-icon-student">👤</div>
                    <h4 class="mb-3">My Profile</h4>
                    <p class="text-muted mb-4">Update your personal information</p>
                    <div class="d-grid">
                        <button class="btn btn-purple-student">View Profile</button>
                    </div>
                </div>
            </div>
        </div>
      </main>
    </div>
  </div>

  <!-- Bootstrap JS -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

  <!-- Dynamic content loader -->
  <script>
    // Global function to view course details
    function viewCourse(courseId) {
      console.log('Viewing course:', courseId);
      
      const mainContent = document.getElementById('main-content');
      const mainElement = document.querySelector('main');
      
      if (!mainContent) {
        console.error('main-content element not found');
        alert('Error: Cannot load course details. Please refresh the page.');
        return;
      }
      
      // Show loading message
      mainContent.innerHTML = '<div style="text-align: center; padding: 50px; background: white; border-radius: 15px; margin: 20px;"><h3 style="color: #6B46C1;">Loading course details...</h3><p>Please wait...</p></div>';
      
      // Scroll main to top
      if (mainElement) {
        mainElement.scrollTop = 0;
      }
      
      // Fetch course details
      fetch('course_detail.php?id=' + courseId)
        .then(response => {
          console.log('Response status:', response.status);
          if (!response.ok) {
            throw new Error('HTTP error! status: ' + response.status);
          }
          return response.text();
        })
        .then(html => {
          console.log('Course details loaded successfully');
          mainContent.innerHTML = html;
          
          // Scroll to top after loading
          if (mainElement) {
            mainElement.scrollTop = 0;
          }
        })
        .catch(err => {
          console.error('Error loading course:', err);
          mainContent.innerHTML = `
            <div style="background: white; padding: 40px; border-radius: 15px; margin: 20px; text-align: center;">
              <h3 style="color: #dc3545;">Failed to Load Course</h3>
              <p>Error: ${err.message}</p>
              <button onclick="location.reload()" style="background: #6B46C1; color: white; border: none; padding: 10px 20px; border-radius: 8px; cursor: pointer;">
                Refresh Page
              </button>
            </div>
          `;
        });
    }
    
    // Make sure the function is available globally
    window.viewCourse = viewCourse;
    
    // Global tab switching function for course pages
    window.showTab = function(tabName, element) {
      console.log('showTab called with:', tabName, element);
      
      // Remove active class from all tabs and buttons
      document.querySelectorAll('.tab-content').forEach(tab => tab.classList.remove('active'));
      document.querySelectorAll('.tab-btn').forEach(btn => btn.classList.remove('active'));
      
      // Add active class to selected tab and button
      const targetTab = document.getElementById(tabName + '-tab');
      console.log('Target tab:', targetTab);
      
      if (targetTab) {
        targetTab.classList.add('active');
      }
      
      if (element) {
        element.classList.add('active');
      } else {
        // Fallback: find the button by text content
        document.querySelectorAll('.tab-btn').forEach(btn => {
          if (btn.textContent.toLowerCase().includes(tabName)) {
            btn.classList.add('active');
          }
        });
      }
      
      console.log('Tab switched to:', tabName);
    };
    
    // Global function to show assignment submission form
    window.showSubmitForm = function(assignmentId) {
      document.getElementById('assignmentId').value = assignmentId;
      document.getElementById('submitModal').style.display = 'block';
    };
    
    // Global function to close submission modal
    window.closeSubmitModal = function() {
      const modal = document.getElementById('submitModal');
      if (modal) {
        modal.style.display = 'none';
      }
    };
    
    // Global function to go back to courses
    window.goBackToCourses = function() {
      const mainContent = document.getElementById('main-content');
      if (mainContent) {
        fetch('course.php')
          .then(response => response.text())
          .then(html => {
            mainContent.innerHTML = html;
          })
          .catch(err => {
            console.error('Error loading courses:', err);
          });
      }
    };
    
    // Sidebar toggle functionality
    const sidebar = document.getElementById('sidebar');
    const sidebarToggle = document.getElementById('sidebarToggle');
    const sidebarOverlay = document.getElementById('sidebarOverlay');
    const mainContent = document.getElementById('main-content');
    
    // Toggle sidebar
    sidebarToggle.addEventListener('click', function() {
      sidebar.classList.toggle('show');
      sidebar.classList.toggle('hide');
      sidebarOverlay.classList.toggle('show');
      mainContent.classList.toggle('expanded');
    });
    
    // Close sidebar when clicking overlay (mobile)
    sidebarOverlay.addEventListener('click', function() {
      sidebar.classList.remove('show');
      sidebarOverlay.classList.remove('show');
    });
    
    // Close sidebar when clicking a link (mobile only)
    const links = document.querySelectorAll('.sidebar a');
    links.forEach(link => {
      link.addEventListener('click', function(e) {
        e.preventDefault();
        
        // Remove active class from all links
        links.forEach(l => l.classList.remove('active'));
        this.classList.add('active');
        
        // Load page content
        const page = this.getAttribute('data-page');
        fetch(page)
          .then(response => response.text())
          .then(html => {
            document.getElementById('main-content').innerHTML = html;
          })
          .catch(err => {
            document.getElementById('main-content').innerHTML =
              "<div class='alert alert-danger'>Failed to load " + page + "</div>";
          });
        
        // Close sidebar on mobile after clicking
        if (window.innerWidth <= 768) {
          sidebar.classList.remove('show');
          sidebarOverlay.classList.remove('show');
        }
      });
    });
    
    // Function to load user profile view
    function loadUserProfile() {
      fetch('view_user_profile.php')
        .then(response => response.text())
        .then(html => {
          document.getElementById('main-content').innerHTML = html;
        })
        .catch(err => {
          document.getElementById('main-content').innerHTML =
            "<div class='alert alert-danger'>Failed to load profile</div>";
        });
      
      // Close sidebar on mobile
      if (window.innerWidth <= 768) {
        sidebar.classList.remove('show');
        sidebarOverlay.classList.remove('show');
      }
    }
    
    // Function to load edit profile page
    function loadEditProfile() {
      fetch('edit_user_profile.php')
        .then(response => response.text())
        .then(html => {
          document.getElementById('main-content').innerHTML = html;
        })
        .catch(err => {
          document.getElementById('main-content').innerHTML =
            "<div class='alert alert-danger'>Failed to load edit profile</div>";
        });
      
      // Close sidebar on mobile
      if (window.innerWidth <= 768) {
        sidebar.classList.remove('show');
        sidebarOverlay.classList.remove('show');
      }
    }
    
    // Auto-load profile if incomplete
    <?php if (isset($_SESSION['profile_incomplete']) && $_SESSION['profile_incomplete']): ?>
    // Optionally auto-load profile on first login
    // setTimeout(() => loadProfile(), 1000);
    <?php endif; ?>
    
    // Handle window resize
    window.addEventListener('resize', function() {
      if (window.innerWidth > 768) {
        // Desktop: remove mobile classes
        sidebarOverlay.classList.remove('show');
      }
    });
    
    // Auto-load home page disabled - showing default dashboard cards instead
    // Uncomment below to auto-load home.php on page load
    /*
    window.addEventListener('DOMContentLoaded', function() {
      fetch('home.php')
        .then(response => response.text())
        .then(html => {
          document.getElementById('main-content').innerHTML = html;
        })
        .catch(err => {
          console.error('Error loading home page:', err);
        });
    });
    */
  </script>
</body>
</html>


