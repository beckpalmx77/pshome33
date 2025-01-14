<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="description" content="">
    <meta name="author" content="">
    <link rel="icon" href="img/favicon.ico" type="image/x-icon">
    <link href="../img/logo/logo.png" rel="icon">
    <title>MyAdmin Dashboard</title>

    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>

    <link href="../vendor/fontawesome-free/css/all.min.css" rel="stylesheet" type="text/css">
    <link href="../vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet" type="text/css">
    <link href="../css/myadmin.css" rel="stylesheet">
    <link rel="stylesheet" href="../vendor/alertify/css/alertify.core.css"/>
    <link rel="stylesheet" href="../vendor/alertify/css/alertify.default.css"/>
    <link rel="stylesheet" href="../vendor/alertify/css/main.css"/>

    <script src="../vendor/datatables/jquery.dataTables.min.js"></script>
    <script src="../vendor/datatables/dataTables.bootstrap4.min.js"></script>

    <script src="../vendor/alertify/js/alertify.js"></script>

    <link href="../css/select2_4010.css" rel="stylesheet"/>
    <script src="../js/select2_4010.js"></script>

    <!-- CSS only -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.1/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-F3w7mX95PdgyTmZZMECAngseQB83DfGTowi0iMjiWaeVhAn4FJkqJByhZMI3AhiU" crossorigin="anonymous">
    <!-- JavaScript Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.1/dist/js/bootstrap.bundle.min.js" integrity="sha384-/bQdsTh/da6pkI1MST/rWKFNjaCP5gBSY4sEBT38Q/9RBh9AH40zEOg7Hlq2THRZ" crossorigin="anonymous"></script>



    <script src="../vendor/date-picker-1.9/js/bootstrap-datepicker.js"></script>
    <script src="../vendor/date-picker-1.9/locales/bootstrap-datepicker.th.min.js"></script>
    <link href="../vendor/date-picker-1.9/css/bootstrap-datepicker.css" rel="stylesheet"/>

    <?php
    session_start();
    error_reporting(0);
    ?>

    <style>
        body, h1, h2, h3, h4, h5, h6 {
            font-family: 'Prompt', sans-serif !important;
        }
    </style>

    <script>
        $(document).ready(function () {
            $('#doc_date').datepicker({
                format: "yyyy-mm-dd",
                todayHighlight: true,
                language: "th",
                autoclose: true
            });
        });
    </script>

</head>

<body>

<div class="card-body">
    <section class="container-fluid">
        <div class="col-sm-2">
            <label for="doc_date"
                   class="control-label">วันที่เอกสาร</label>
            <input type="text" class="form-control"
                   id="doc_date"
                   name="doc_date"
                   placeholder="วันที่เอกสาร">
        </div>
    </section>
</div>


</body>