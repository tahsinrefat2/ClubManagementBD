<!-- navbar.php -->
<nav class="navbar navbar-expand-lg navbar-dark bg-gradient-primary shadow-lg fixed-top">
    <div class="container">
        <!-- Brand Logo -->
        <a class="navbar-brand d-flex align-items-center" href="index.php">
            <div class="logo-container bg-white rounded-circle p-2 me-2 d-flex align-items-center justify-content-center">
                <span class="logo-icon">🏆</span>
            </div>
            <span class="brand-text fw-bold">ClubManagementBD</span>
        </a>
        
        <!-- Mobile Toggle -->
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarContent" aria-controls="navbarContent" aria-expanded="false">
            <span class="navbar-toggler-icon"></span>
        </button>
        
        <!-- Main Nav Content -->
        <div class="collapse navbar-collapse" id="navbarContent">
            <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                <!-- Club Management Dropdown -->
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" id="clubManagementDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="fas fa-building me-1"></i> Club Management
                    </a>
                    <ul class="dropdown-menu dropdown-menu-dark animate slideIn" aria-labelledby="clubManagementDropdown">
                        <li><a class="dropdown-item" href="clubs.php"><i class="fas fa-chess-queen me-2"></i> Clubs</a></li>
                        <li><a class="dropdown-item" href="members.php"><i class="fas fa-users me-2"></i> Members</a></li>
                        <li><a class="dropdown-item" href="finances.php"><i class="fas fa-coins me-2"></i> Finances</a></li>
                        <li><hr class="dropdown-divider bg-secondary"></li>
                        <li><a class="dropdown-item" href="teams.php"><i class="fas fa-people-group me-2"></i> Teams</a></li>
                    </ul>
                </li>
                
                <!-- Players -->
                <li class="nav-item">
                    <a class="nav-link" href="players.php">
                        <i class="fas fa-user-group me-1"></i> Players
                    </a>
                </li>
                
                <!-- Tournament Management Dropdown -->
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" id="tournamentDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="fas fa-trophy me-1"></i> Tournaments
                    </a>
                    <ul class="dropdown-menu dropdown-menu-dark animate slideIn" aria-labelledby="tournamentDropdown">
                        <li><a class="dropdown-item" href="tournaments.php"><i class="fas fa-list-check me-2"></i> Tournaments</a></li>
                        <li><a class="dropdown-item" href="matches.php"><i class="fas fa-stopwatch me-2"></i> Matches</a></li>
                        <li><a class="dropdown-item" href="results.php"><i class="fas fa-medal me-2"></i> Results</a></li>
                        <li><hr class="dropdown-divider bg-secondary"></li>
                        <li><a class="dropdown-item" href="venues.php"><i class="fas fa-location-dot me-2"></i> Venues</a></li>
                    </ul>
                </li>
                
                <!-- News -->
                <li class="nav-item">
                    <a class="nav-link" href="news.php">
                        <i class="fas fa-newspaper me-1"></i> News
                    </a>
                </li>
                
                <!-- Contact -->
                <li class="nav-item">
                    <a class="nav-link" href="contact.php">
                        <i class="fas fa-envelope me-1"></i> Contact
                    </a>
                </li>
            </ul>
            
            
        </div>
    </div>
</nav>

<!-- Main Content Container with proper spacing -->
<div class="main-content">
    <!-- Your page content goes here -->
</div>

<!-- Add this to your head section -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

<style>
    /* Modern Navbar Styling */
    :root {
        --navbar-height: 100px;
        --primary-dark: #1e3a8a;
        --primary-light: #3b82f6;
        --accent-color: #0ea5e9;
        --hover-bg: rgba(255, 255, 255, 0.15);
    }
    
    
    
    /* Main content container with proper spacing */
    .main-content {
        padding-top: 30 px;
        width: 100%;
        position: relative;
        z-index: 1;
    }
    
    /* Make sure page titles start below navbar */
    h1, h2, h3, .page-title {
        margin-top: var(--navbar-height);
        padding-top: 10px;
    }
    
    /* Ensure form elements don't get hidden */
    .form-group, .form-control, input, select, textarea {
        position: relative;
        z-index: 1;
    }
    
    .navbar {
        height: var(--navbar-height);
        transition: all 0.3s ease;
        z-index: 1030; /* High z-index to ensure it stays on top */
    }
    
    /* Gradient Background with Modern Look */
    .bg-gradient-primary {
        background: linear-gradient(135deg, var(--primary-dark) 0%, var(--primary-light) 100%);
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
    }
    
    /* Logo Animation */
    .logo-container {
        width: 36px;
        height: 36px;
        overflow: hidden;
        transition: all 0.4s ease;
    }
    
    .logo-icon {
        font-size: 1.2rem;
        display: inline-block;
        transition: transform 0.5s ease;
    }
    
    .navbar-brand:hover .logo-icon {
        transform: rotate(20deg) scale(1.2);
    }
    
    .brand-text {
        transition: color 0.3s ease;
    }
    
    .navbar-brand:hover .brand-text {
        color: var(--accent-color);
    }
    
    /* Navigation Links */
    .navbar .nav-link {
        position: relative;
        padding: 0.6rem 1rem;
        border-radius: 0.25rem;
        transition: all 0.3s ease;
        margin: 0 0.1rem;
    }
    
    .navbar .nav-link::after {
        content: '';
        position: absolute;
        width: 0;
        height: 2px;
        bottom: 0;
        left: 50%;
        background-color: white;
        transition: all 0.3s ease;
        transform: translateX(-50%);
        opacity: 0;
    }
    
    .navbar .nav-link:hover {
        background-color: var(--hover-bg);
        transform: translateY(-2px);
    }
    
    .navbar .nav-link:hover::after {
        width: 70%;
        opacity: 1;
    }
    
    /* Dropdown Styling */
    .dropdown-menu-dark {
        background-color: rgba(30, 58, 138, 0.95);
        backdrop-filter: blur(10px);
        border: 1px solid rgba(255, 255, 255, 0.1);
        box-shadow: 0 8px 32px rgba(0, 0, 0, 0.2);
        padding: 0.5rem;
        margin-top: 0rem;
        border-radius: 0.5rem;
    }
    
    .dropdown-menu-dark .dropdown-item {
        color: rgba(255, 255, 255, 0.8);
        transition: all 0.2s ease;
        border-radius: 0.25rem;
        padding: 0.5rem 1rem;
        margin-bottom: 0.2rem;
    }
    
    .dropdown-menu-dark .dropdown-item:hover {
        background-color: var(--hover-bg);
        color: white;
        transform: translateX(5px);
    }
    
    /* Animation Classes */
    .animate {
        animation-duration: 0.3s;
        animation-fill-mode: both;
    }
    
    .slideIn {
        animation-name: slideIn;
    }
    
    @keyframes slideIn {
        0% {
            opacity: 0;
            transform: translateY(-10px);
        }
        100% {
            opacity: 1;
            transform: translateY(0);
        }
    }
    
    
    /* Navbar Scrolled Effect */
    .navbar-scrolled {
        height: 60px;
        background: var(--primary-dark) !important;
        box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
    }
    
    /* Mobile Toggle Styling */
    .navbar-toggler {
        border: none;
        padding: 0.5rem;
        transition: all 0.3s ease;
    }
    
    .navbar-toggler:focus {
        box-shadow: none;
        outline: 2px solid rgba(255, 255, 255, 0.3);
    }
    
    /* Responsive Dropdown Positioning */
    @media (max-width: 991.98px) {
        .navbar-collapse {
            background-color: var(--primary-dark);
            border-radius: 0.5rem;
            padding: 1rem;
            margin-top: 0.5rem;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.2);
        }
        
        .dropdown-menu {
            border: none;
            background-color: rgba(0, 0, 0, 0.1);
            box-shadow: none;
        }
        
        /* Additional mobile spacing */
        body {
            padding-top: calc(var(--navbar-height) + 10px);
        }
        
        .main-content {
            padding-top: 20px;
        }
    }
</style>

<script>
// Add this script to enhance navbar functionality
document.addEventListener('DOMContentLoaded', function() {
    // Navbar scroll effect
    window.addEventListener('scroll', function() {
        const navbar = document.querySelector('.navbar');
        if (window.scrollY > 50) {
            navbar.classList.add('navbar-scrolled');
        } else {
            navbar.classList.remove('navbar-scrolled');
        }
    });
    
    // Fix dropdown issues on hover for desktop
    if (window.innerWidth > 992) {
        const dropdowns = document.querySelectorAll('.navbar .dropdown');
        
        dropdowns.forEach(dropdown => {
            const dropdownToggle = dropdown.querySelector('.dropdown-toggle');
            const dropdownMenu = dropdown.querySelector('.dropdown-menu');
            
            // Open dropdown on hover
            dropdown.addEventListener('mouseenter', function() {
                dropdownMenu.classList.add('show');
                dropdownToggle.setAttribute('aria-expanded', 'true');
            });
            
            // Close dropdown when mouse leaves
            dropdown.addEventListener('mouseleave', function() {
                dropdownMenu.classList.remove('show');
                dropdownToggle.setAttribute('aria-expanded', 'false');
            });
        });
    }
    
    // Fix dropdown click on mobile
    const dropdownToggleList = document.querySelectorAll('.dropdown-toggle');
    dropdownToggleList.forEach(function(dropdownToggle) {
        dropdownToggle.addEventListener('click', function(e) {
            if (window.innerWidth <= 992) {
                e.preventDefault();
                e.stopPropagation();
                const dropdownMenu = this.nextElementSibling;
                dropdownMenu.classList.toggle('show');
                this.setAttribute('aria-expanded', dropdownMenu.classList.contains('show'));
            }
        });
    });
    
    // Close dropdowns when clicking outside
    document.addEventListener('click', function(e) {
        if (!e.target.closest('.dropdown')) {
            const openDropdownMenus = document.querySelectorAll('.dropdown-menu.show');
            openDropdownMenus.forEach(menu => {
                menu.classList.remove('show');
                menu.previousElementSibling.setAttribute('aria-expanded', 'false');
            });
        }
    });
    
    // Fix the content spacing issue
    document.body.style.paddingTop = (document.querySelector('.navbar').offsetHeight + 20) + 'px';
    
    // Update spacing on window resize
    window.addEventListener('resize', function() {
        document.body.style.paddingTop = (document.querySelector('.navbar').offsetHeight + 20) + 'px';
    });
});
</script>