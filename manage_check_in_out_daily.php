<?php
include('includes/Header.php');
if (strlen($_SESSION['alogin']) == "" || strlen($_SESSION['display_name']) == "") {
    header("Location: index.php");
} else {
    ?>

    <!DOCTYPE html>
    <html lang="th">
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
                <!-- Container Fluid-->
                <div class="container-fluid" id="container-wrapper">
                    <div class="d-sm-flex align-items-center justify-content-between mb-4">
                        <h1 class="h3 mb-0 text-gray-800">รายงาน Check-in/out รายวัน</h1>
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="<?php echo $_SESSION['dashboard_page'] ?>">Home</a>
                            </li>
                            <li class="breadcrumb-item">ระบบลงเวลา</li>
                            <li class="breadcrumb-item active" aria-current="page">รายงาน Check-in/out รายวัน</li>
                        </ol>
                    </div>

                    <div class="row">
                        <div class="col-lg-12">
                            <div class="card mb-12">
                                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                                    <h6 class="m-0 font-weight-bold text-primary">ตารางรายการ Check-in/out</h6>
                                </div>
                                <div class="card-body">
                                    <div class="row mb-3">
                                        <div class="col-md-3">
                                            <label for="date_start">วันที่เริ่มต้น</label>
                                            <input type="date" id="date_start" class="form-control">
                                        </div>
                                        <div class="col-md-3">
                                            <label for="date_end">วันที่สิ้นสุด</label>
                                            <input type="date" id="date_end" class="form-control">
                                        </div>
                                        <div class="col-md-2">
                                            <label>&nbsp;</label>
                                            <button type="button" id="btnSearch" class="btn btn-primary btn-block">ค้นหา</button>
                                        </div>
                                    </div>
                                    <div class="table-responsive p-3">
                                        <table class="table align-items-center table-flush table-hover" id="TableRecordList">
                                            <thead class="thead-light">
                                            <tr>
                                                <th>วันที่</th>
                                                <th>พนักงาน</th>
                                                <th>รูปโปรไฟล์</th>
                                                <th>เวลาเข้า (Check-in)</th>
                                                <th>เวลาออก (Check-out)</th>
                                            </tr>
                                            </thead>
                                            <tbody>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>

    <?php
    include('includes/Modal-Logout.php');
    include('includes/Footer.php');
    ?>

    <!-- Scroll to top -->
    <a class="scroll-to-top rounded" href="#page-top">
        <i class="fas fa-angle-up"></i>
    </a>

    <script src="vendor/jquery/jquery.min.js"></script>
    <script src="vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
    <script src="vendor/jquery-easing/jquery.easing.min.js"></script>
    <script src="js/myadmin.min.js"></script>

    <!-- Page level plugins -->
    <script src="vendor/datatables/v11/bootbox.min.js"></script>
    <script src="vendor/datatables/v11/jquery.dataTables.min.js"></script>
    <link rel="stylesheet" href="vendor/datatables/v11/jquery.dataTables.min.css"/>
    <link rel="stylesheet" href="vendor/datatables/v11/buttons.dataTables.min.css"/>

    <script>
        $(document).ready(function () {
            let dataRecords = $('#TableRecordList').DataTable({
                'lengthMenu': [[10, 20, 50, 100], [10, 20, 50, 100]],
                'language': {
                    search: 'ค้นหา', lengthMenu: 'แสดง _MENU_ รายการ',
                    info: 'หน้าที่ _PAGE_ จาก _PAGES_',
                    infoEmpty: 'ไม่มีข้อมูล',
                    zeroRecords: "ไม่มีข้อมูลตามเงื่อนไข",
                    infoFiltered: '(กรองข้อมูลจากทั้งหมด _MAX_ รายการ)',
                    paginate: {
                        previous: 'ก่อนหน้า',
                        last: 'สุดท้าย',
                        next: 'ต่อไป'
                    }
                },
                'processing': true,
                'serverSide': true,
                'serverMethod': 'post',
                'order': [[0, 'desc']], // เรียงตามวันที่ล่าสุด
                'ajax': {
                    'url': 'model/manage_check_in_out_daily_process.php',
                    'data': function(d) {
                        d.action = "GET_CHECK_IN_OUT_DAILY";
                        d.date_start = $('#date_start').val();
                        d.date_end = $('#date_end').val();
                    }
                },
                'columns': [
                    {data: 'checkin_date'},
                    {data: 'emp_name'},
                    {data: 'line_picture'},
                    {data: 'time_in'},
                    {data: 'time_out'}
                ]
            });

            $('#btnSearch').click(function() {
                dataRecords.draw();
            });
        });
    </script>

    </body>
    </html>

<?php } ?>
