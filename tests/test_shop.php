<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>DataTables Ajax Example</title>
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
    <link href="../vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet" type="text/css">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.11.5/css/jquery.dataTables.min.css">
    <script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@floating-ui/dom@1.6.7"></script>
    <link href="https://fonts.googleapis.com/css?family=Lato" rel="stylesheet" type="text/css">
    <link href="https://fonts.googleapis.com/css?family=Montserrat" rel="stylesheet" type="text/css">

    <style>
        body, h1, h2, h3, h4, h5, h6 {
            font-family: 'Prompt', sans-serif !important;
        }

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

        .custom-card {
            border: 2px solid #000; /* กำหนดสีและความหนาของเส้นกรอบ */
            border-radius: 10px; /* กำหนดความโค้งของมุม */
        }

        .navbar {
            background-color: #057ea3 !important; /* สีพื้นหลังสีน้ำเงิน */
        }

        .navbar-nav .nav-link, .navbar-brand {
            color: #ffffff !important; /* ตัวอักษรสีขาว */
        }

        .navbar-nav .nav-link:hover, .navbar-brand:hover {
            color: #cbcbcb !important; /* ตัวอักษรสีขาวเมื่อ hover */
        }
    </style>
</head>

<body>

<nav class="navbar navbar-expand-lg navbar-light bg-light fixed-top">
    <div class="container-fluid">
        <a class="navbar-brand" href="#">Navbar</a>
        <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarNav"
                aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse justify-content-end" id="navbarNav">
            <ul class="navbar-nav">
                <li class="nav-item">
                    <a class="nav-link active" aria-current="page" href="#">Home</a>
                </li>
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button"
                       data-toggle="dropdown" aria-expanded="false">
                        Dropdown
                    </a>
                    <ul class="dropdown-menu" aria-labelledby="navbarDropdown">
                        <li><a class="dropdown-item" href="#">Action</a></li>
                        <li><a class="dropdown-item" href="#">Another action</a></li>
                        <li>
                            <hr class="dropdown-divider">
                        </li>
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

<div class="card mt-5">
    <div class="card-body">
        <div class="container-fluid" id="container-wrapper">
            <table id="example" class="display" style="width:100%">
                <thead>
                <tr>
                    <th>รหัสสินค้า</th>
                    <th>ชื่อสินค้า</th>
                    <th>ราคา</th>
                    <th>เพิ่มลงในตะกร้า</th>
                </tr>
                </thead>
                <tbody>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Cart Section -->
<div class="card mt-4">
    <div class="card-body">
        <h3>Shopping Cart</h3>
        <div id="cart-items" class="mb-4">
            <!-- Cart items will be dynamically added here -->
        </div>
        <div class="text-right">
            <strong>Total: <span id="cart-total">0</span></strong>
        </div>
    </div>
</div>

<script>
    $(document).ready(function () {
        let cart = [];
        const cartItemsContainer = $('#cart-items');
        const cartTotal = $('#cart-total');

        const table = $('#example').DataTable({
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
                "url": "fetch_products.php",
                "type": "GET"
            },
            "columns": [
                {"data": "name"},
                {"data": "description"},
                {"data": "price"},
                {
                    "data": null,
                    "defaultContent": "<button class='btn btn-primary add-to-cart'>Add to Cart</button>"
                }
            ]
        });

        $('#example tbody').on('click', '.add-to-cart', function () {
            const data = table.row($(this).parents('tr')).data();
            const productName = data.description;
            const productPrice = parseFloat(data.price);

            const product = cart.find(item => item.name === productName);
            if (product) {
                product.quantity += 1;
            } else {
                cart.push({name: productName, price: productPrice, quantity: 1});
            }

            updateCart();

        });

        function updateCart() {
            cartItemsContainer.empty();
            let total = 0;
            cart.forEach(item => {
                total += item.price * item.quantity;
                const cartItem = $(`
                    <div class="cart-item d-flex justify-content-between mb-2">
                        <span>${item.name} (x${item.quantity})</span>
                        <span>$${(item.price * item.quantity).toFixed(2)}</span>
                    </div>
                `);
                cartItemsContainer.append(cartItem);
            });
            cartTotal.text(total.toFixed(2));
        }
    });
</script>

</body>
</html>
