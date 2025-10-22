<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>DataTables Ajax Example</title>
    <link href="../vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet" type="text/css">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.11.5/css/jquery.dataTables.min.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@floating-ui/dom@1.6.7"></script>

    <style>
        body, h1, h2, h3, h4, h5, h6 {
            font-family: 'Prompt', sans-serif !important;
        }
    </style>

    <style>
        .dropdown:hover .dropdown-menu {
            display: block;
        }
        .navbar-fixed-top {
            top: 0;
            right: 0;
            left: 0;
            z-index: 1030;
        }
        .navbar-hidden {
            top: -60px; /* Adjust this value based on your navbar height */
            transition: top 0.3s;
        }
        body {
            padding-top: 70px; /* Adjust this value based on your navbar height */
        }
    </style>

    <style>
        .custom-card {
            border: 2px solid #000; /* กำหนดสีและความหนาของเส้นกรอบ */
            border-radius: 10px; /* กำหนดความโค้งของมุม */
        }
    </style>

    <style>
        .dropdown:hover .dropdown-menu {
            display: block;
        }
    </style>

</head>
<body>

<nav class="navbar navbar-expand-lg navbar-light bg-light fixed-top">
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

<!--nav class="navbar navbar-expand-lg navbar-light bg-light">
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
</nav-->


<div class="card">
    <div class="card-body">
        <div class="container-fluid" id="container-wrapper">
            <table id="example" class="display" style="width:100%">
                <thead>
                <tr>
                    <th>รหัสพนักงาน</th>
                    <th>ชื่อ</th>
                    <th>นามสกุล</th>
                    <!-- Add more columns as needed -->
                </tr>
                </thead>
                <tbody>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!--script>
    $(document).ready(function () {
        let previousScroll = 0;
        $(window).scroll(function () {
            let currentScroll = $(this).scrollTop();
            if (currentScroll > previousScroll) {
                $('.navbar').addClass('navbar-hidden');
            } else {
                $('.navbar').removeClass('navbar-hidden');
            }
            previousScroll = currentScroll;
        });
    });
</script-->

<script>
    $(document).ready(function () {

        $('#example').DataTable({
            "lengthMenu": [[7, 10, 20, 50, 100], [7, 10, 20, 50, 100]],
            "language": {
                search: 'ค้นหา', lengthMenu: 'แสดง _MENU_ รายการ',
                info: 'หน้าที่ _PAGE_ จาก _PAGES_',
                infoEmpty: 'ไม่มีข้อมูล',
                zeroRecords: "ไม่มีข้อมูลตามเงื่อนไข",
                infoFiltered: '(กรองข้อมูลจากทั้งหมด _MAX_ รายการ)',
                responsive: true,
                paginate: {
                    previous: 'ก่อนหน้า',
                    last: 'สุดท้าย',
                    next: 'ต่อไป'
                }
            },
            "ajax": {
                "url": "fetch_data.php",
                "type": "GET"
            },
            "columns": [
                {"data": "emp_id"},
                {"data": "first_name"},
                {"data": "last_name"}
                // Add more columns as needed
            ]
        });
    });
</script>



</body>
</html>
