<?php
include('includes/Header.php');
if (strlen($_SESSION['alogin']) == "" || strlen($_SESSION['department_id']) == "") {
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
                <!--div class="col-xl-12 col-md-12 mb-12"><h2>เอกสารการลาประจำวัน</h2></div>
                <div class="col-xl-12 col-md-12 mb-12">
                    <div class="card h-100">
                        <div class="card-body">
                            <div class="row no-gutters align-items-center">
                                <table id='TableRecordList' class='display dataTable'>
                                    <thead>
                                    <tr>
                                        <th>ปี</th>
                                        <th>วันที่เอกสาร</th>
                                        <th>ชื่อ-นามสกุล</th>
                                        <th>หน่วยงาน</th>
                                        <th>ประเภทการลา</th>
                                        <th>วันที่ลาเริ่มต้น</th>
                                        <th>วันที่ลาสิ้นสุด</th>
                                        <th>จำนวนวัน</th>
                                        <th>สถานะ</th>
                                        <th>รูปภาพ</th>
                                        <th>Action</th>
                                    </tr>
                                    </thead>
                                    <tfoot>
                                    <tr>
                                        <th>ปี</th>
                                        <th>วันที่เอกสาร</th>
                                        <th>ชื่อ-นามสกุล</th>
                                        <th>หน่วยงาน</th>
                                        <th>ประเภทการลา</th>
                                        <th>วันที่ลาเริ่มต้น</th>
                                        <th>วันที่ลาสิ้นสุด</th>
                                        <th>จำนวนวัน</th>
                                        <th>สถานะ</th>
                                        <th>รูปภาพ</th>
                                        <th>Action</th>
                                    </tr>
                                    </tfoot>
                                </table>

                            </div>
                        </div>
                    </div>
                </div-->
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
    <script src="vendor/chart.js/Chart.min.js"></script>
    <script src="js/chart/chart-area-demo.js"></script>

    <link href='vendor/calendar/main.css' rel='stylesheet'/>
    <script src='vendor/calendar/main.js'></script>
    <script src='vendor/calendar/locales/th.js'></script>

    <script src='js/clock_time.js'></script>

    <script src="vendor/datatables/v11/bootbox.min.js"></script>
    <script src="vendor/datatables/v11/jquery.dataTables.min.js"></script>
    <link rel="stylesheet" href="vendor/datatables/v11/jquery.dataTables.min.css"/>
    <link rel="stylesheet" href="vendor/datatables/v11/buttons.dataTables.min.css"/>

    <script>
        $(document).ready(function () {
            let formData = {action: "GET_LEAVE_DOCUMENT", sub_action: "GET_MASTER", page_manage: "USER",};
            let dataRecords = $('#TableRecordList').DataTable({
                'lengthMenu': [[5, 10, 20, 50, 100], [5, 10, 20, 50, 100]],
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
                'autoWidth': true,
                'searching': true,
                <?php  if ($_SESSION['deviceType'] !== 'computer') {
                    echo "'scrollX': true,";
                }?>
                'ajax': {
                    'url': 'model/manage_leave_document_process.php',
                    'data': formData
                },
                'columns': [
                    {data: 'doc_year'},
                    {data: 'doc_date'},
                    {data: 'full_name'},
                    {data: 'department_id'},
                    {data: 'leave_type_detail'},
                    {data: 'dt_leave_start'},
                    {data: 'dt_leave_to'},
                    {data: 'leave_day'},
                    {data: 'status'},
                    {data: 'image'},
                    {data: 'update'},
                ]
            });

        });

    </script>

    </body>

    </html>

<?php } ?>

