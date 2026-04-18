<?php
session_start();
error_reporting(0);
include('includes/Header.php');
include('config/connect_db.php');

if (strlen($_SESSION['alogin']) == "" || strlen($_SESSION['house_number']) == "") {
    header("Location: index.php");
} else {
    ?>

    <!DOCTYPE html>
    <html lang="th">
    <head>
        <link rel="stylesheet" href="css/spin_datatables.css"/>
        <link rel="stylesheet" href="vendor/datatables/v11/jquery.dataTables.min.css"/>
        <link rel="stylesheet" href="vendor/datatables/v11/buttons.dataTables.min.css"/>

        <style>
            .card-body {
                padding: 1rem;
            }
            .dataTables_wrapper {
                overflow-x: auto;
            }
            .dataTables_wrapper .dataTables_paginate .paginate_button {
                padding: 0.3em 0.6em;
            }
            .icon-input-btn {
                display: inline-block;
                position: relative;
            }
            .icon-input-btn input[type="submit"] {
                padding-left: 2em;
            }
            .icon-input-btn .fa {
                display: inline-block;
                position: absolute;
                left: 0.65em;
                top: 30%;
            }
        </style>
    </head>
    <body id="page-top">
    <div id="wrapper">
        <?php
        include('includes/Side-Bar.php');
        ?>

        <div id="content-wrapper" class="d-flex flex-column">
            <div id="content">
                <?php
                include('includes/Top-Bar.php');
                ?>
                <div class="container-fluid" id="container-wrapper">
                    <div class="d-sm-flex align-items-center justify-content-between mb-4">
                        <h1 class="h3 mb-0 text-gray-800">รายการบ้านที่รับสติกเกอร์แล้ว</h1>
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="<?php echo $_SESSION['dashboard_page'] ?>">Home</a></li>
                            <li class="breadcrumb-item">ข้อมูลสติกเกอร์</li>
                            <li class="breadcrumb-item active" aria-current="page">รายการรับสติกเกอร์</li>
                        </ol>
                    </div>

                    <div class="row">
                        <div class="col-lg-12">
                            <div class="card mb-12">
                                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                                </div>
                                <div class="card-body">
                                    <section class="container-fluid">

                                        <div class="col-md-12 col-md-offset-2">
                                            <table id="TableRecordList" class="display nowrap" style="width:100%;">
                                                <thead>
                                                <tr>
                                                    <th>บ้านเลขที่</th>
                                                    <th>ทะเบียนรถ 1</th>
                                                    <th>ทะเบียนรถ 2</th>
                                                    <th>ทะเบียนรถ 3</th>
                                                    <th>ทะเบียนรถ 4</th>
                                                    <th>ทะเบียนรถ 5</th>
                                                    <th>วันที่รับสติกเกอร์</th>
                                                </tr>
                                                </thead>
                                            </table>
                                            <div id="result"></div>
                                        </div>

                                    </section>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <?php include('includes/Footer.php'); ?>
        </div>
    </div>

    <a class="scroll-to-top rounded" href="#page-top">
        <i class="fas fa-angle-up"></i>
    </a>

    <script src="vendor/jquery/jquery.min.js"></script>
    <script src="vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
    <script src="vendor/jquery-easing/jquery.easing.min.js"></script>
    <script src="js/sb-admin-2.min.js"></script>
    <script src="vendor/datatables/v11/jquery.dataTables.min.js"></script>
    <script src="vendor/datatables/v11/dataTables.buttons.min.js"></script>
    <script src="vendor/datatables/v11/jszip.min.js"></script>
    <script src="vendor/datatables/v11/pdfmake.min.js"></script>
    <script src="vendor/datatables/v11/vfs_fonts.js"></script>
    <script src="vendor/datatables/v11/buttons.html5.min.js"></script>
    <script src="vendor/datatables/v11/buttons.print.min.js"></script>

    <script>
        $(document).ready(function() {
            var table = $('#TableRecordList').DataTable({
                "processing": true,
                "serverSide": false,
                "ajax": {
                    "url": "model/get_sticker_received_list.php",
                    "type": "POST"
                },
                "columns": [
                    { "data": "house_number" },
                    { "data": "car_no1" },
                    { "data": "car_no2" },
                    { "data": "car_no3" },
                    { "data": "car_no4" },
                    { "data": "car_no5" },
                    { "data": "sticker_receive_date" }
                ],
                "language": {
                    "emptyTable": "ไม่พบข้อมูล",
                    "info": "แสดง _START_ ถึง _END_ จาก _TOTAL_ รายการ",
                    "infoEmpty": "แสดง 0 ถึง 0 จาก 0 รายการ",
                    "infoFiltered": "(กรองจาก _MAX_ รายการ)",
                    "lengthMenu": "แสดง _MENU_ รายการ",
                    "loadingRecords": "กำลังโหลด...",
                    "processing": "กำลังประมวลผล...",
                    "search": "ค้นหา:",
                    "zeroRecords": "ไม่พบรายการที่ตรงกัน",
                    "paginate": {
                        "first": "หน้าแรก",
                        "last": "หน้าสุดท้าย",
                        "next": "ถัดไป",
                        "previous": "ก่อนหน้า"
                    }
                },
                "dom": 'Blfrtip',
                "buttons": [
                    'copy', 'excel', 'print'
                ],
                "order": [[0, "asc"]],
                "pageLength": 10
            });
        });
    </script>
    </body>
    </html>

<?php } ?>