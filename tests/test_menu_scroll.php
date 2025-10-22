<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hover Menu</title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/5.1.3/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.10.2/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/5.1.3/js/bootstrap.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <link rel="stylesheet" href="https://cdn.datatables.net/1.11.5/css/jquery.dataTables.min.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@floating-ui/dom@1.6.7"></script>

    <style>
        .dropdown:hover .dropdown-menu {
            display: block;
        }
        .fixed-header {
            position: fixed;
            width: 100%;
            z-index: 1030;
            transition: top 0.3s;
        }
        .navbar-hidden {
            top: -120px; /* Adjust this value based on your total header height */
        }
        .top-header {
            top: 0;
            background-color: #f8f9fa;
            border-bottom: 1px solid #e3e6ea;
        }
        .navbar-fixed-top {
            top: 40px; /* Adjust this value based on your top header height */
        }
        body {
            padding-top: 100px; /* Adjust this value based on your total header height */
        }
    </style>
</head>
<body>
<!-- Header ชั้นบน -->
<div class="fixed-header top-header">
    <div class="container-fluid d-flex justify-content-between p-2">
        <div>Top Header Content</div>
        <div>Other Links or Info</div>
    </div>
</div>

<!-- Header ชั้นล่าง -->
<nav class="navbar navbar-expand-lg navbar-brand bg-blue fixed-header navbar-fixed-top">
    <div class="container-fluid">
        <a class="navbar-brand" href="#">Navbar</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse justify-content-end" id="navbarNav">
            <ul class="navbar-nav">
                <li class="nav-item">
                    <a class="nav-link active" aria-current="page" href="#">Home</a>
                </li>
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                        Dropdown
                    </a>
                    <ul class="dropdown-menu" aria-labelledby="navbarDropdown">
                        <li><a class="dropdown-item" href="#">Action</a></li>
                        <li><a class="dropdown-item" href="#">Another action</a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item" href="#">Something else here</a></li>
                    </ul>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="#">Link</a>
                </li>
            </ul>
        </div>
    </div>
</nav>

<div class="container mt-5">
    <h1>Content</h1>
    <p>Scroll down to see the auto-hide navbar in action.</p>
    <!-- Add more content here to make the page scrollable -->
    <p>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Nulla volutpat aliquam velit. Phasellus iaculis neque purus sodales ultricies. Nulla facilisi. Praesent vitae justo nec orci vehicula elementum ut a sapien. Etiam ultrices erat at felis imperdiet, at viverra nulla vestibulum. Nulla eget condimentum lorem, sed egestas mauris. Curabitur et orci vitae risus pharetra auctor in quis tortor. Nam vitae mauris libero. Donec fermentum tortor sed nisi sagittis, ac sollicitudin mauris volutpat.</p>
    <!-- Repeat the above paragraph multiple times to ensure the page is scrollable -->
</div>


<script>
    $(document).ready(function () {
        var previousScroll = 0;
        $(window).scroll(function () {
            var currentScroll = $(this).scrollTop();
            if (currentScroll > previousScroll) {
                $('.fixed-header').addClass('navbar-hidden');
            } else {
                $('.fixed-header').removeClass('navbar-hidden');
            }
            previousScroll = currentScroll;
        });
    });
</script>
</body>
</html>
