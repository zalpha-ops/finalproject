<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Eagle Flight School - Professional Pilot Training</title>
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
            --gold: #FFD700;
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            line-height: 1.6;
            overflow-x: hidden;
        }
        
        /* Hero Section */
        .hero {
            background: linear-gradient(135deg, var(--black) 0%, var(--dark-purple) 50%, var(--primary-purple) 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            position: relative;
            overflow: hidden;
        }
        
        .hero::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1000 1000"><polygon fill="rgba(255,255,255,0.05)" points="0,0 1000,300 1000,1000 0,700"/></svg>');
            background-size: cover;
        }
        
        .hero-content {
            position: relative;
            z-index: 2;
            color: var(--white);
        }
        
        .hero h1 {
            font-size: 4rem;
            font-weight: 700;
            margin-bottom: 1rem;
            background: linear-gradient(135deg, var(--white) 0%, var(--gold) 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
        
        .hero .lead {
            font-size: 1.5rem;
            margin-bottom: 2rem;
            opacity: 0.9;
        }
        
        .btn-hero {
            background: linear-gradient(135deg, var(--gold) 0%, #FFA500 100%);
            border: none;
            color: var(--black);
            padding: 1rem 2rem;
            font-size: 1.2rem;
            font-weight: 600;
            border-radius: 50px;
            transition: all 0.3s;
            text-decoration: none;
            display: inline-block;
            margin: 0.5rem;
        }
        
        .btn-hero:hover {
            transform: translateY(-3px);
            box-shadow: 0 15px 35px rgba(255, 215, 0, 0.4);
            color: var(--black);
        }
        
        .btn-outline-hero {
            border: 2px solid var(--white);
            color: var(--white);
            background: transparent;
            padding: 1rem 2rem;
            font-size: 1.2rem;
            font-weight: 600;
            border-radius: 50px;
            transition: all 0.3s;
            text-decoration: none;
            display: inline-block;
            margin: 0.5rem;
        }
        
        .btn-outline-hero:hover {
            background: var(--white);
            color: var(--dark-purple);
            transform: translateY(-3px);
        }
        
        /* Features Section */
        .features {
            padding: 5rem 0;
            background: linear-gradient(135deg, #f8fafc 0%, #e2e8f0 100%);
        }
        
        .feature-card {
            background: var(--white);
            border-radius: 20px;
            padding: 2rem;
            text-align: center;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            transition: all 0.3s;
            border: none;
            height: 100%;
        }
        
        .feature-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 20px 50px rgba(107, 70, 193, 0.2);
        }
        
        .feature-icon {
            font-size: 3rem;
            background: linear-gradient(135deg, var(--primary-purple) 0%, var(--dark-purple) 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            margin-bottom: 1rem;
        }
        
        .feature-card h4 {
            color: var(--dark-purple);
            margin-bottom: 1rem;
            font-weight: 600;
        }
        
        /* Stats Section */
        .stats {
            background: linear-gradient(135deg, var(--primary-purple) 0%, var(--dark-purple) 100%);
            padding: 4rem 0;
            color: var(--white);
        }
        
        .stat-item {
            text-align: center;
            padding: 1rem;
        }
        
        .stat-number {
            font-size: 3rem;
            font-weight: 700;
            color: var(--gold);
            display: block;
        }
        
        .stat-label {
            font-size: 1.1rem;
            opacity: 0.9;
        }
        
        /* Login Section */
        .login-section {
            padding: 5rem 0;
            background: var(--white);
        }
        
        .login-card {
            background: linear-gradient(135deg, var(--primary-purple) 0%, var(--dark-purple) 100%);
            border-radius: 20px;
            padding: 3rem;
            color: var(--white);
            text-align: center;
            box-shadow: 0 15px 40px rgba(107, 70, 193, 0.3);
        }
        
        .login-btn {
            background: var(--white);
            color: var(--dark-purple);
            border: none;
            padding: 1rem 2rem;
            border-radius: 10px;
            font-weight: 600;
            margin: 0.5rem;
            transition: all 0.3s;
            text-decoration: none;
            display: inline-block;
        }
        
        .login-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.2);
            color: var(--dark-purple);
        }
        
        /* Footer */
        .footer {
            background: var(--black);
            color: var(--white);
            padding: 3rem 0 1rem;
        }
        
        .footer h5 {
            color: var(--gold);
            margin-bottom: 1rem;
        }
        
        .footer a {
            color: #cbd5e1;
            text-decoration: none;
            transition: color 0.3s;
        }
        
        .footer a:hover {
            color: var(--gold);
        }
        
        /* Animations */
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        .animate-fade-in {
            animation: fadeInUp 0.8s ease-out;
        }
        
        /* Responsive */
        @media (max-width: 768px) {
            .hero h1 {
                font-size: 2.5rem;
            }
            
            .hero .lead {
                font-size: 1.2rem;
            }
            
            .btn-hero, .btn-outline-hero {
                font-size: 1rem;
                padding: 0.8rem 1.5rem;
            }
        }
    </style>
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-dark fixed-top" style="background: rgba(15, 15, 15, 0.9); backdrop-filter: blur(10px);">
        <div class="container">
            <a class="navbar-brand fw-bold" href="#" style="color: var(--gold);">
                <i class="fas fa-plane me-2"></i>Eagle Flight School
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="#home">Home</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#features">Programs</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#stats">About</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#login">Login</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <section id="home" class="hero">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-lg-6">
                    <div class="hero-content animate-fade-in">
                        <h1><i class="fas fa-plane me-3"></i>Eagle Flight School</h1>
                        <p class="lead">Soar to new heights with professional pilot training. Your journey to the skies starts here.</p>
                        <div class="hero-buttons">
                            <a href="login.php" class="btn-hero">
                                <i class="fas fa-sign-in-alt me-2"></i>Login Portal
                            </a>
                            <a href="#features" class="btn-outline-hero">
                                <i class="fas fa-info-circle me-2"></i>Learn More
                            </a>
                        </div>
                    </div>
                </div>
                <div class="col-lg-6 text-center">
                    <div style="font-size: 15rem; color: rgba(255, 255, 255, 0.1);">
                        <i class="fas fa-plane"></i>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Features Section -->
    <section id="features" class="features">
        <div class="container">
            <div class="row text-center mb-5">
                <div class="col-12">
                    <h2 class="display-4 fw-bold" style="color: var(--dark-purple);">Flight Training Programs</h2>
                    <p class="lead text-muted">Professional aviation training for aspiring pilots</p>
                </div>
            </div>
            <div class="row g-4">
                <div class="col-md-4">
                    <div class="card feature-card">
                        <div class="feature-icon">
                            <i class="fas fa-certificate"></i>
                        </div>
                        <h4>Private Pilot License</h4>
                        <p class="text-muted">Start your aviation journey with comprehensive private pilot training and certification.</p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card feature-card">
                        <div class="feature-icon">
                            <i class="fas fa-plane-departure"></i>
                        </div>
                        <h4>Commercial Pilot</h4>
                        <p class="text-muted">Advanced training for commercial aviation careers with experienced instructors.</p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card feature-card">
                        <div class="feature-icon">
                            <i class="fas fa-compass"></i>
                        </div>
                        <h4>Instrument Rating</h4>
                        <p class="text-muted">Master instrument flying techniques for safe navigation in all weather conditions.</p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card feature-card">
                        <div class="feature-icon">
                            <i class="fas fa-chalkboard-teacher"></i>
                        </div>
                        <h4>Flight Instructor</h4>
                        <p class="text-muted">Become a certified flight instructor and share your passion for aviation.</p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card feature-card">
                        <div class="feature-icon">
                            <i class="fas fa-plane"></i>
                        </div>
                        <h4>Multi-Engine Rating</h4>
                        <p class="text-muted">Expand your skills with multi-engine aircraft training and certification.</p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card feature-card">
                        <div class="feature-icon">
                            <i class="fas fa-book-open"></i>
                        </div>
                        <h4>Ground School</h4>
                        <p class="text-muted">Comprehensive theoretical knowledge covering all aspects of aviation.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Stats Section -->
    <section id="stats" class="stats">
        <div class="container">
            <div class="row">
                <div class="col-md-3">
                    <div class="stat-item">
                        <span class="stat-number">500+</span>
                        <span class="stat-label">Certified Pilots</span>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="stat-item">
                        <span class="stat-number">15+</span>
                        <span class="stat-label">Expert Instructors</span>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="stat-item">
                        <span class="stat-number">10+</span>
                        <span class="stat-label">Years Experience</span>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="stat-item">
                        <span class="stat-number">98%</span>
                        <span class="stat-label">Success Rate</span>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Login Section -->
    <section id="login" class="login-section">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-lg-8">
                    <div class="login-card">
                        <h2 class="mb-4"><i class="fas fa-sign-in-alt me-2"></i>Access Your Account</h2>
                        <p class="mb-4">Choose your role to access the Eagle Flight School management system</p>
                        <div class="d-flex flex-wrap justify-content-center gap-3">
                            <a href="login.php" class="login-btn">
                                <i class="fas fa-user-graduate me-2"></i>Student Login
                            </a>
                            <a href="login.php" class="login-btn">
                                <i class="fas fa-chalkboard-teacher me-2"></i>Instructor Login
                            </a>
                            <a href="login.php" class="login-btn">
                                <i class="fas fa-user-shield me-2"></i>Admin Login
                            </a>
                        </div>
                        <div class="mt-4">
                            <small class="opacity-75">
                                <i class="fas fa-lock me-1"></i>
                                Secure access to your personalized dashboard
                            </small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="footer">
        <div class="container">
            <div class="row">
                <div class="col-md-4">
                    <h5><i class="fas fa-plane me-2"></i>Eagle Flight School</h5>
                    <p class="text-muted">Professional pilot training for the next generation of aviators. Soar to new heights with us.</p>
                </div>
                <div class="col-md-4">
                    <h5>Quick Links</h5>
                    <ul class="list-unstyled">
                        <li><a href="#features">Training Programs</a></li>
                        <li><a href="#login">Student Portal</a></li>
                        <li><a href="system_health.php">System Status</a></li>
                        <li><a href="#stats">About Us</a></li>
                    </ul>
                </div>
                <div class="col-md-4">
                    <h5>Contact Info</h5>
                    <ul class="list-unstyled">
                        <li><i class="fas fa-envelope me-2"></i>info@eagleflightschool.com</li>
                        <li><i class="fas fa-phone me-2"></i>+1 (555) 123-EAGLE</li>
                        <li><i class="fas fa-map-marker-alt me-2"></i>Sky Harbor Airport</li>
                    </ul>
                </div>
            </div>
            <hr class="my-4" style="border-color: #374151;">
            <div class="row align-items-center">
                <div class="col-md-6">
                    <p class="mb-0">&copy; 2024 Eagle Flight School. All rights reserved.</p>
                </div>
                <div class="col-md-6 text-md-end">
                    <small class="text-muted">
                        <i class="fas fa-shield-alt me-1"></i>
                        Secure Management System
                    </small>
                </div>
            </div>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Smooth scrolling for navigation links
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
                e.preventDefault();
                const target = document.querySelector(this.getAttribute('href'));
                if (target) {
                    target.scrollIntoView({
                        behavior: 'smooth',
                        block: 'start'
                    });
                }
            });
        });

        // Add scroll effect to navbar
        window.addEventListener('scroll', function() {
            const navbar = document.querySelector('.navbar');
            if (window.scrollY > 50) {
                navbar.style.background = 'rgba(15, 15, 15, 0.95)';
            } else {
                navbar.style.background = 'rgba(15, 15, 15, 0.9)';
            }
        });

        // Animate stats on scroll
        const observerOptions = {
            threshold: 0.5,
            rootMargin: '0px 0px -100px 0px'
        };

        const observer = new IntersectionObserver(function(entries) {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    const statNumbers = entry.target.querySelectorAll('.stat-number');
                    statNumbers.forEach(stat => {
                        const finalNumber = stat.textContent;
                        const isPercentage = finalNumber.includes('%');
                        const numericValue = parseInt(finalNumber.replace(/\D/g, ''));
                        
                        let current = 0;
                        const increment = numericValue / 50;
                        const timer = setInterval(() => {
                            current += increment;
                            if (current >= numericValue) {
                                current = numericValue;
                                clearInterval(timer);
                            }
                            stat.textContent = Math.floor(current) + (isPercentage ? '%' : '+');
                        }, 30);
                    });
                }
            });
        }, observerOptions);

        const statsSection = document.querySelector('.stats');
        if (statsSection) {
            observer.observe(statsSection);
        }
    </script>
</body>
</html>
