<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tailscale Blog Template</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            font-family: Arial, sans-serif;
        }
        .navbar {
            background-color: #0073e6;
        }
        .navbar-brand, .nav-link {
            color: white !important;
        }
        .hero-section {
            background-color: #f8f9fa;
            padding: 100px 0 50px 0;
        }
        .post-preview {
            margin-bottom: 30px;
        }
        .post-title {
            font-size: 1.5rem;
            margin-bottom: 10px;
        }
        .post-meta {
            color: #6c757d;
            font-size: 0.9rem;
        }
        .scroll-to-top {
            position: fixed;
            bottom: 20px;
            right: 20px;
            display: none;
            background-color: #0073e6;
            color: white;
            border: none;
            padding: 10px 15px;
            border-radius: 50%;
            cursor: pointer;
        }
        .sidebar {
            position: fixed;
            top: 56px; /* Height of the navbar */
            left: 0;
            height: 100%;
            width: 250px;
            background-color: #f8f9fa;
            padding-top: 20px;
            overflow-y: auto;
        }
        .main-content {
            margin-left: 250px; /* Same as the width of the sidebar */
            padding: 20px;
        }
    </style>
</head>
<body>

<!-- Navbar -->
<nav class="navbar navbar-expand-lg navbar-dark fixed-top">
    <div class="container-fluid">
        <a class="navbar-brand" href="#">Tailscale Blog</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav ms-auto">
                <li class="nav-item">
                    <a class="nav-link" href="#">Home</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="#">About</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="#">Contact</a>
                </li>
            </ul>
        </div>
    </div>
</nav>

<!-- Sidebar -->
<div class="sidebar">
    <div class="container">
        <ul class="nav flex-column">
            <li class="nav-item">
                <a class="nav-link" href="#">Home</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="#">About</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="#">Contact</a>
            </li>
        </ul>
    </div>
</div>

<!-- Main Content -->
<div class="main-content">
    <!-- Hero Section -->
    <div class="hero-section text-center">
        <div class="container">
            <h1>Welcome to Tailscale Blog</h1>
            <p>Your source for the latest news and updates</p>
        </div>
    </div>

    <!-- Blog Posts -->
    <div class="container mt-5">
        <div class="post-preview">
            <a href="#">
                <h2 class="post-title">Post Title 1</h2>
                <p class="post-meta">Posted by <a href="#">Author</a> on July 4, 2024</p>
                <p class="post-content">Lorem ipsum dolor sit amet, consectetur adipiscing elit. Sed do eiusmod tempor incididunt ut labore et dolore magna aliqua...</p>
            </a>
        </div>
        <div class="post-preview">
            <a href="#">
                <h2 class="post-title">Post Title 2</h2>
                <p class="post-meta">Posted by <a href="#">Author</a> on July 4, 2024</p>
                <p class="post-content">Lorem ipsum dolor sit amet, consectetur adipiscing elit. Sed do eiusmod tempor incididunt ut labore et dolore magna aliqua...</p>
            </a>
        </div>
        <!-- Add more posts as needed -->
    </div>
</div>

<!-- Scroll to Top Button -->
<button onclick="scrollToTop()" class="scroll-to-top" id="scrollToTopBtn">↑</button>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
    // Scroll to Top functionality
    var scrollToTopBtn = document.getElementById("scrollToTopBtn");

    window.onscroll = function() {
        if (document.body.scrollTop > 20 || document.documentElement.scrollTop > 20) {
            scrollToTopBtn.style.display = "block";
        } else {
            scrollToTopBtn.style.display = "none";
        }
    };

    function scrollToTop() {
        document.body.scrollTop = 0;
        document.documentElement.scrollTop = 0;
    }
</script>
</body>
</html>
