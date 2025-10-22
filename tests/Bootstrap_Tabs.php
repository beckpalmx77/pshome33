<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="description" content="">
    <meta name="author" content="">
    <title>ใจพร้อม | JaiPrompt</title>

    <link rel="icon" href="img/favicon.ico" type="image/x-icon">
    <link href="../img/logo/logo.png" rel="icon">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/jquery@3.5.1/dist/jquery.slim.min.js"></script>

</head>
<body>

<div class="container mt-3">
    <h2>Toggleable Tabs</h2>
    <br>
    <!-- Nav tabs -->
    <ul class="nav nav-tabs" role="tablist">
        <li class="nav-item">
            <a class="nav-link active" data-bs-toggle="tab" href="#home">Home</a>
        </li>
        <li class="nav-item">
            <a class="nav-link" data-bs-toggle="tab" href="#menu1">Menu 1</a>
        </li>
        <li class="nav-item">
            <a class="nav-link" data-bs-toggle="tab" href="#menu2">Menu 2</a>
        </li>
    </ul>

    <!-- Tab panes -->
    <div class="tab-content">
        <div id="home" class="container tab-pane active"><br>
            <h3>HOME</h3>
            <form id="form1" action="" method="POST">
                <div class="col-sm-8">
                    <label for="supplier_name1"
                           class="control-label">ชื่อผู้จำหน่าย 1 </label>
                    <input type="text" class="form-control"
                           id="supplier_name1"
                           name="supplier_name1"
                           required="required"
                           placeholder="ชื่อผู้จำหน่าย 1">
                </div>
            </form>
            <p>Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.</p>
        </div>
        <div id="menu1" class="container tab-pane fade"><br>
            <h3>Menu 1</h3>
            <form id="form2" action="" method="POST">
                <div class="col-sm-8">
                    <label for="supplier_name2"
                           class="control-label">ชื่อผู้จำหน่าย 2</label>
                    <input type="text" class="form-control"
                           id="supplier_name2"
                           name="supplier_name2"
                           required="required"
                           placeholder="ชื่อผู้จำหน่าย 2 ">
                </div>
            </form>
            <p>Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat.</p>
        </div>
        <div id="menu2" class="container tab-pane fade"><br>
            <h3>Menu 2</h3>
            <form id="form3" action="" method="POST">
                <div class="col-sm-8">
                    <label for="supplier_name3"
                           class="control-label">ชื่อผู้จำหน่าย 3</label>
                    <input type="text" class="form-control"
                           id="supplier_name3"
                           name="supplier_name3"
                           required="required"
                           placeholder="ชื่อผู้จำหน่าย 3">
                </div>
            </form>
            <p>Sed ut perspiciatis unde omnis iste natus error sit voluptatem accusantium doloremque laudantium, totam rem aperiam.</p>
        </div>
    </div>
</div>

</body>
</html>
