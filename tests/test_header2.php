<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hide and Show Header on Scroll</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            margin: 0;
            padding: 0;
            font-family: Arial, sans-serif;
        }
        .header-panel {
            background-color: #f8f9fa;
            padding: 10px 20px;
            border-bottom: 1px solid #dee2e6;
            display: flex;
            justify-content: space-between;
            align-items: center;
            position: fixed;
            width: 100%;
            top: 0;
            left: 0;
            z-index: 1000;
            transition: top 0.3s ease-in-out;
        }
        .header-placeholder {
            height: 60px; /* Adjust this height to match the header's height */
        }
        .logo {
            font-size: 1.5rem;
            font-weight: bold;
            color: #333;
        }
        .nav-links {
            display: flex;
            gap: 20px;
        }
        .nav-links a {
            text-decoration: none;
            color: #333;
            font-weight: 500;
        }
        .nav-links a:hover {
            color: #007bff;
        }
    </style>
</head>
<body>

<div class="header-placeholder"></div> <!-- Placeholder for the sticky header -->
<header class="header-panel" id="header">
    <div class="logo">My Logo</div>
    <nav class="nav-links">
        <a href="#">Home</a>
        <a href="#">About</a>
        <a href="#">Services</a>
        <a href="#">Contact</a>
    </nav>
</header>

<!-- Content to scroll -->
<div style="height: 2000px; padding: 20px;">
    <h1>Scroll down to see the header hide and show</h1>
    <p>Content goes here...</p>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
    var lastScrollTop = 0;
    var header = document.getElementById("header");
    var isScrollingDown = false;

    window.onscroll = function() {
        var scrollTop = window.pageYOffset || document.documentElement.scrollTop;

        if (scrollTop > lastScrollTop) {
            if (!isScrollingDown && scrollTop > 100) {
                // Scroll down - hide the header
                header.style.top = "-0px";
                isScrollingDown = true;
            }
        } else {
            if (isScrollingDown) {
                // Scroll up - show the header
                header.style.top = "0";
                isScrollingDown = false;
            }
        }

        lastScrollTop = scrollTop;
    };
</script>
<p>Test</p>
<p>Test</p>
<p>Test</p>
<p>Test</p>
<p>Test</p>
<p>Test</p>
<p>Test</p>
<p>Test</p>
<p>Test</p>
<p>Test</p>
<p>Test</p>
<p>Test</p>
<p>Test</p>
<p>Test</p>
<p>Test</p>
<p>Test</p>
<p>Test</p>
<p>Test</p>
<p>Test</p>
<p>Test</p>
<p>Test</p>
<p>Test</p>
<p>Test</p>
<p>Test</p>
<p>Test</p>
<p>Test</p>
<p>Test</p>
<p>Test</p>
<p>Test</p>
<p>Test</p>
<p>Test</p>
<p>Test</p>
<p>Test</p>
<p>Test</p>
<p>Test</p>
<p>Test</p>
<p>Test</p>
<p>Test</p>
<p>Test</p>
<p>Test</p>
<p>Test</p>
<p>Test</p>
<p>Test</p>
<p>Test</p>
<p>Test</p>
<p>Test</p>
<p>Test</p>
<p>Test</p>
<p>Test</p>
<p>Test</p>
<p>Test</p>
<p>Test</p>
<p>Test</p>
<p>Test</p>
<p>Test</p>
<p>Test</p>
<p>Test</p>
<p>Test</p>
<p>Test</p>
<p>Test</p>
<p>Test</p>
<p>Test</p>
<p>Test</p>
<p>Test</p>
<p>Test</p>
<p>Test</p>
<p>Test</p>
<p>Test</p>
<p>Test</p>
<p>Test</p>
<p>Test</p>
<p>Test</p><p>Test</p>
<p>Test</p>
<p>Test</p>
<p>Test</p>
<p>Test</p>
<p>Test</p><p>Test</p>
<p>Test</p>
<p>Test</p>
<p>Test</p>
<p>Test</p>
<p>Test</p><p>Test</p>
<p>Test</p>
<p>Test</p>
<p>Test</p>
<p>Test</p>
<p>Test</p><p>Test</p>
<p>Test</p>
<p>Test</p>
<p>Test</p>
<p>Test</p>
<p>Test</p><p>Test</p>
<p>Test</p>
<p>Test</p>
<p>Test</p>
<p>Test</p>
<p>Test</p><p>Test</p>
<p>Test</p>
<p>Test</p>
<p>Test</p>
<p>Test</p>
<p>Test</p><p>Test</p>
<p>Test</p>
<p>Test</p>
<p>Test</p>
<p>Test</p>
<p>Test</p><p>Test</p>
<p>Test</p>
<p>Test</p>
<p>Test</p>
<p>Test</p>
<p>Test</p><p>Test</p>
<p>Test</p>
<p>Test</p>
<p>Test</p>
<p>Test</p>
<p>Test</p>












<p>Test</p>
<p>Test</p>
<p>Test</p>
<p>Test</p>
<p>Test</p>
<p>Test</p>
</body>
</html>
