<?php
// payroll_list_view.php - หน้าจอแสดงรายการและจัดการเงินเดือน

// จำลองการ include ไฟล์ภายนอกตามโครงสร้างของคุณ
include('includes/Header.php');
// ตรวจสอบ Session (ปรับตามการใช้งานจริงของคุณ)
if (strlen($_SESSION['alogin']) == "" || strlen($_SESSION['doc_no']) == "") {
    header("Location: index.php");
} else {
    // กำหนดตัวแปรสำหรับเมนูและชื่อหน้าจอ (สมมติค่า default)
    $main_menu_name = isset($_GET['m']) ? urldecode($_GET['m']) : 'Payroll';
    $sub_menu_name = isset($_GET['s']) ? urldecode($_GET['s']) : 'รายการจัดทำเงินเดือน';
    $dashboard_page = isset($_SESSION['dashboard_page']) ? $_SESSION['dashboard_page'] : 'dashboard.php';
    ?>

    <!DOCTYPE html>
    <html lang="th">
    <head>
        <meta charset="UTF-8">
        <title><?php echo $sub_menu_name; ?></title>
        <link rel="stylesheet" href="vendor/datatables/v11/jquery.dataTables.min.css"/>
        <link rel="stylesheet" href="vendor/datatables/v11/buttons.dataTables.min.css"/>
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
                        <h1 class="h3 mb-0 text-gray-800"><?php echo $sub_menu_name; ?></h1>
                        <input type="hidden" id="main_menu" value="<?php echo $main_menu_name; ?>">
                        <input type="hidden" id="sub_menu" value="<?php echo $sub_menu_name; ?>">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="<?php echo $dashboard_page; ?>">Home</a>
                            </li>
                            <li class="breadcrumb-item"><?php echo $main_menu_name; ?></li>
                            <li class="breadcrumb-item active"
                                aria-current="page"><?php echo $sub_menu_name; ?></li>
                        </ol>
                    </div>

                    <div class="row">
                        <div class="col-lg-12">
                            <div class="card mb-12">
                                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                                    <h6 class="m-0 font-weight-bold text-primary">รายการเงินเดือนที่จัดทำแล้ว</h6>
                                </div>
                                <div class="card-body">
                                    <section class="container-fluid">

                                        <div class="col-md-12 col-md-offset-2 mb-3">
                                            <button type='button' name='btnGenerate' id='btnGenerate'
                                                    class='btn btn-success btn-xs'>สร้างเงินเดือนอัตโนมัติ 🚀
                                            </button>

                                            <button type='button' name='btnAdd' id='btnAdd'
                                                    class='btn btn-primary btn-xs'>เพิ่มรายการด้วยตนเอง
                                                <i class="fa fa-plus"></i>
                                            </button>

                                        </div>

                                        <div class="col-md-12 col-md-offset-2">
                                            <table id='TableRecordList' class='display dataTable'>
                                                <thead>
                                                <tr>
                                                    <th>เลขที่เอกสาร (Doc No.)</th>
                                                    <th>วันที่ (Doc Date)</th>
                                                    <th>ชื่อพนักงาน</th>
                                                    <th>เดือน</th>
                                                    <th>ปี</th>
                                                    <th>แก้ไข</th>
                                                    <th>ลบ</th>
                                                </tr>
                                                </thead>
                                                <tbody>
                                                </tbody>
                                            </table>

                                            <div id="result"></div>

                                        </div>


                                    </section>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="modal fade" id="confirmDeleteModal" tabindex="-1" role="dialog"
                         aria-labelledby="confirmDeleteLabel" aria-hidden="true">
                        <div class="modal-dialog modal-dialog-centered" role="document">
                            <div class="modal-content">
                                <div class="modal-header bg-danger text-white">
                                    <h5 class="modal-title" id="confirmDeleteLabel">ยืนยันการลบ</h5>
                                    <button type="button" class="close text-white"
                                            data-dismiss="modal" aria-label="Close">
                                        <span aria-hidden="true">&times;</span>
                                    </button>
                                </div>
                                <div class="modal-body">
                                    คุณต้องการลบข้อมูลเงินเดือนนี้ใช่หรือไม่?
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary"
                                            data-dismiss="modal">ยกเลิก
                                    </button>
                                    <button type="button" class="btn btn-danger"
                                            id="confirmDeleteBtn">ลบข้อมูล
                                    </button>
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

    <a class="scroll-to-top rounded" href="#page-top">
        <i class="fas fa-angle-up"></i>
    </a>

    <script src="vendor/jquery/jquery.min.js"></script>
    <script src="vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
    <script src="vendor/jquery-easing/jquery.easing.min.js"></script>
    <script src="js/myadmin.min.js"></script>

    <script src="vendor/datatables/v11/bootbox.min.js"></script>
    <script src="vendor/datatables/v11/jquery.dataTables.min.js"></script>

    <script>
        $(document).ready(function () {
            // DataTables Initialization
            let formData = {action: "GET_PAYROLL", sub_action: "GET_MASTER"};
            let dataRecords = $('#TableRecordList').DataTable({
                'lengthMenu': [[6, 12, 24, 48, 100], [6, 12, 24, 48, 100]],
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
                'ajax': {
                    'url': 'model/manage_payroll_process.php', // ไฟล์ประมวลผลสำหรับ DataTables
                    'data': formData
                },
                'columns': [
                    {data: 'doc_no'},
                    {data: 'doc_date'},
                    {data: 'employee_fullname'}, // ต้องมีการ Join เพื่อดึงชื่อพนักงาน
                    {data: 'payroll_month'},
                    {data: 'payroll_year'},
                    {data: 'update', orderable: false, searchable: false},
                    {data: 'delete', orderable: false, searchable: false}
                ]
            });

            // ----------------------------------------------------------------------
            // Functionality
            // ----------------------------------------------------------------------

            // 1. ปุ่ม "สร้างเงินเดือนอัตโนมัติ" (Generate)
            $("#btnGenerate").click(function () {
                let main_menu = document.getElementById("main_menu").value;
                let sub_menu = document.getElementById("sub_menu").value;
                let url = "payroll_generate_view.php" // เปลี่ยนไปใช้ไฟล์หน้าจอ generate ที่สร้างไว้ก่อนหน้า
                    + '?m=' + encodeURIComponent(main_menu)
                    + '&s=' + encodeURIComponent('สร้างข้อมูลเงินเดือนอัตโนมัติ');
                window.open(url, '_self'); // เปิดในหน้าต่างเดิม
            });

            // 2. ปุ่ม "เพิ่มรายการด้วยตนเอง" (Add)
            $("#btnAdd").click(function () {
                let main_menu = document.getElementById("main_menu").value;
                let sub_menu = document.getElementById("sub_menu").value;
                let url = "manage_payroll_data.php" // ไฟล์สำหรับเพิ่ม/แก้ไข (Manage)
                    + '?m=' + encodeURIComponent(main_menu)
                    + '&s=' + encodeURIComponent('เพิ่มรายการเงินเดือน')
                    + '&action=ADD';
                window.open(url, '_blank');
            });

            // 3. ปุ่ม "แก้ไข" (Update)
            $("#TableRecordList").on('click', '.update', function () {
                let id = $(this).attr("id");
                // ... (ส่วนการดึงข้อมูลและ redirect ไปยัง manage_payroll_data.php ตามโค้ดต้นฉบับ)
                // เพื่อความสั้น ขอละส่วน Ajax ที่ดึงข้อมูลมาทั้งหมดก่อน Redirect
                let main_menu = document.getElementById("main_menu").value;
                let sub_menu = document.getElementById("sub_menu").value;
                let url = "manage_payroll_data.php?title=จัดทำเงินเดือน(Payroll Transaction)"
                    + '&m=' + encodeURIComponent(main_menu)
                    + '&s=' + encodeURIComponent('แก้ไขรายการเงินเดือน')
                    + '&id=' + id
                    + '&action=UPDATE';
                window.open(url, '_blank');
            });

            // 4. ปุ่ม "ลบ" (Delete)
            let deleteId = null;
            $("#TableRecordList").on('click', '.delete', function () {
                deleteId = $(this).attr("id");
                $("#confirmDeleteModal").modal("show");
            });

            $("#confirmDeleteBtn").on("click", function () {
                if (deleteId) {
                    $.ajax({
                        url: "model/manage_payroll_delete_process.php", // ไฟล์ประมวลผลการลบ
                        method: "POST",
                        data: {id: deleteId, action: "DELETE"},
                        success: function (response) {
                            $("#confirmDeleteModal").modal("hide");
                            $('#TableRecordList').DataTable().ajax.reload();
                            // ใช้ alertify.js หรือแค่ alert
                            console.log("ลบข้อมูลเรียบร้อยแล้ว");
                        },
                        error: function () {
                            console.log("เกิดข้อผิดพลาดในการลบข้อมูล");
                        }
                    });
                }
            });

        });
    </script>

    <style>
        /* CSS สำหรับปุ่มที่มี Icon (หากต้องการใช้ตามต้นฉบับ) */
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


    </body>
    </html>

<?php } ?>