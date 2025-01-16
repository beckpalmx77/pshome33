<?php
session_start();
error_reporting(0);
include('includes/Header.php');
include('config/connect_db.php');
$curr_date = date("d-m-Y");

if (strlen($_SESSION['alogin']) == "" || strlen($_SESSION['house_number']) == "") {
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
                        <h1 class="h3 mb-0 text-gray-800"><?php echo urldecode($_GET['s']) ?></h1>
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="<?php echo $_SESSION['dashboard_page'] ?>">Home</a>
                            </li>
                            <li class="breadcrumb-item"><?php echo urldecode($_GET['m']) ?></li>
                            <li class="breadcrumb-item active"
                                aria-current="page"><?php echo urldecode($_GET['s']) ?></li>
                        </ol>
                    </div>

                    <div class="row">
                        <div class="col-lg-12">
                            <div class="card mb-12">
                                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                                </div>
                                <div class="card-body">
                                    <section class="container-fluid">

                                        <!--div class="col-md-12 col-md-offset-2">
                                            <label for="name_t"
                                                   class="control-label"><b>เพิ่ม <?php echo urldecode($_GET['s']) ?></b></label>
                                            <button type='button' name='btnAdd' id='btnAdd'
                                                    class='btn btn-primary btn-xs'>Add
                                                <i class="fa fa-plus"></i>
                                            </button>
                                        </div-->

                                        <div class="col-md-12 col-md-offset-2">
                                            <table id='TableRecordList' class='display dataTable'>
                                                <thead>
                                                <tr>
                                                    <th>เลขที่เอกสาร</th>
                                                    <th>วันที่ชำระ</th>
                                                    <th>บ้านเลขที่</th>
                                                    <th>ผู้ชำระ</th>
                                                    <th>งวดเดือนเริ่มต้น</th>
                                                    <th>ถึงงวดเดือน</th>
                                                    <th>ปี</th>
                                                    <th>ยอดชำระ</th>
                                                    <th>สถานะ</th>
                                                    <th>Action</th>
                                                </tr>
                                                </thead>
                                                <tfoot>
                                                <tr>
                                                    <th>เลขที่เอกสาร</th>
                                                    <th>วันที่ชำระ</th>
                                                    <th>บ้านเลขที่</th>
                                                    <th>ผู้ชำระ</th>
                                                    <th>งวดเดือนเริ่มต้น</th>
                                                    <th>ถึงงวดเดือน</th>
                                                    <th>ปี</th>
                                                    <th>ยอดชำระ</th>
                                                    <th>สถานะ</th>
                                                    <th>Action</th>
                                                </tr>
                                                </tfoot>
                                            </table>

                                            <div id="result"></div>

                                        </div>

                                        <div class="modal fade" id="recordModal">
                                            <div class="modal-dialog modal-lg">
                                                <div class="modal-content">
                                                    <div class="modal-header">
                                                        <h4 class="modal-title">Modal title</h4>
                                                        <button type="button" class="close" data-dismiss="modal"
                                                                aria-hidden="true">×
                                                        </button>
                                                    </div>
                                                    <form method="post" id="recordForm">
                                                        <div class="modal-body">
                                                            <div class="modal-body">

                                                                <div class="form-group row">
                                                                    <div class="col-sm-6">
                                                                        <label for="doc_id"
                                                                               class="control-label">เลขที่เอกสาร</label>
                                                                        <input type="text" class="form-control"
                                                                               id="doc_id"
                                                                               name="doc_id"
                                                                               required="required"
                                                                               readonly="true"
                                                                               placeholder="">
                                                                    </div>
                                                                    <div class="col-sm-6">
                                                                        <label for="payment_date"
                                                                               class="control-label">วันที่ชำระ</label>
                                                                        <input type="text" class="form-control"
                                                                               id="payment_date"
                                                                               name="payment_date"
                                                                               required="required"
                                                                               readonly="true"
                                                                               placeholder="">
                                                                    </div>
                                                                </div>

                                                                <div class="form-group row">
                                                                    <div class="col-sm-6">
                                                                        <label for="house_number"
                                                                               class="control-label">บ้านเลขที่</label>
                                                                        <input type="text" class="form-control"
                                                                               id="house_number"
                                                                               name="house_number"
                                                                               required="required"
                                                                               readonly="true"
                                                                               placeholder="">
                                                                    </div>
                                                                    <div class="col-sm-6">
                                                                        <label for="detail"
                                                                               class="control-label">ผู้ชำระ</label>
                                                                        <input type="text" class="form-control"
                                                                               id="detail"
                                                                               name="detail"
                                                                               required="required"
                                                                               readonly="true"
                                                                               placeholder="">
                                                                    </div>
                                                                </div>

                                                                <div class="form-group row">
                                                                    <div class="col-sm-4">
                                                                        <label for="month_name_start"
                                                                               class="control-label">เดือนเริ่มต้น</label>
                                                                        <input type="text" class="form-control"
                                                                               id="month_name_start"
                                                                               name="month_name_start"
                                                                               required="required"
                                                                               readonly="true"
                                                                               placeholder="">
                                                                    </div>
                                                                    <div class="col-sm-4">
                                                                        <label for="month_name_to"
                                                                               class="control-label">ถึงงวดเดือน</label>
                                                                        <input type="text" class="form-control"
                                                                               id="month_name_to"
                                                                               name="month_name_to"
                                                                               required="required"
                                                                               readonly="true"
                                                                               placeholder="">
                                                                    </div>
                                                                    <div class="col-sm-4">
                                                                        <label for="period_year"
                                                                               class="control-label">ปี</label>
                                                                        <input type="text" class="form-control"
                                                                               id="period_year"
                                                                               name="period_year"
                                                                               required="required"
                                                                               readonly="true"
                                                                               placeholder="">
                                                                    </div>
                                                                </div>

                                                                <div class="form-group row">
                                                                    <div class="col-sm-6">
                                                                        <label for="amount"
                                                                               class="control-label">จำนวนเงินโอน</label>
                                                                        <input type="text" class="form-control"
                                                                               id="amount"
                                                                               name="amount"
                                                                               required="required"
                                                                               readonly="true"
                                                                               placeholder="">
                                                                    </div>
                                                                    <div class="col-sm-6">
                                                                        <label for="payment_status_desc"
                                                                               class="control-label">สถานะ</label>
                                                                        <input type="text" class="form-control"
                                                                               id="payment_status_desc"
                                                                               name="payment_status_desc"
                                                                               required="required"
                                                                               readonly="true"
                                                                               placeholder="">
                                                                    </div>
                                                                </div>

                                                                <div class="form-group row">
                                                                    <div class="col-sm-6 zoom-container">
                                                                        <img id="preview_image" src="#"
                                                                             alt="Preview Image"
                                                                             style="display: none; margin-top: 10px; max-width: 200px;"/>
                                                                    </div>
                                                                    <div class="col-sm-6">
                                                                        <label>สถานะการอนุมัติ</label><br>
                                                                        <input type="radio" id="approved"
                                                                               name="payment_status" value="Y">
                                                                        <label for="approved" class="btn btn-success">ยืนยันการชำระ</label>
                                                                        <input type="radio" id="rejected"
                                                                               name="payment_status" value="N">
                                                                        <label for="rejected" class="btn btn-danger">ยังไม่ยืนยันการชำระ</label>
                                                                    </div>
                                                                </div>

                                                            </div>
                                                        </div>

                                                        <div class="modal-footer">
                                                            <input type="hidden" name="id" id="id"/>
                                                            <input type="hidden" name="action" id="action" value=""/>
                                                            <button type="button" class="btn btn-primary"
                                                                    id="saveButton">Save <i
                                                                        class="fa fa-check"></i>
                                                            </button>
                                                            <button type="button" class="btn btn-danger"
                                                                    data-dismiss="modal">Close <i
                                                                        class="fa fa-window-close"></i>
                                                            </button>
                                                        </div>
                                                    </form>

                                                </div>
                                            </div>
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

    <script src="js/util/calculate_datetime.js"></script>
    <script src="js/modal/show_department_modal.js"></script>
    <script src="js/modal/show_worktime_modal.js"></script>

    <!-- Page level plugins -->

    <!--script src="https://cdnjs.cloudflare.com/ajax/libs/bootbox.js/5.5.2/bootbox.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.0/js/jquery.dataTables.min.js"></script>
    <link rel="stylesheet" href="https://cdn.datatables.net/1.11.0/css/jquery.dataTables.min.css"/>
    <link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.0.0/css/buttons.dataTables.min.css"/-->

    <script src="vendor/bootstrap-datepicker/js/bootstrap-datepicker.min.js"></script>

    <script src="vendor/date-picker-1.9/js/bootstrap-datepicker.js"></script>
    <script src="vendor/date-picker-1.9/locales/bootstrap-datepicker.th.min.js"></script>
    <!--link href="vendor/date-picker-1.9/css/date_picker_style.css" rel="stylesheet"/-->
    <link href="vendor/date-picker-1.9/css/bootstrap-datepicker.css" rel="stylesheet"/>

    <script src="vendor/datatables/v11/bootbox.min.js"></script>
    <script src="vendor/datatables/v11/jquery.dataTables.min.js"></script>
    <link rel="stylesheet" href="vendor/datatables/v11/jquery.dataTables.min.css"/>
    <link rel="stylesheet" href="vendor/datatables/v11/buttons.dataTables.min.css"/>


    <style>

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

    <style>
        .zoom-container {
            position: relative;
            overflow: hidden;
            display: inline-block; /* เพื่อควบคุมขนาดของพื้นที่ */
        }

        .zoom-container img {
            transition: transform 0.3s ease; /* ให้ภาพขยายแบบนุ่มนวล */
        }

        .zoom-container:hover img {
            transform: scale(1.5); /* กำหนดระดับการ Zoom */
            cursor: zoom-out; /* เปลี่ยน cursor */
        }
    </style>

    <script>
        $(document).ready(function () {
            $(".icon-input-btn").each(function () {
                let btnFont = $(this).find(".btn").css("font-size");
                let btnColor = $(this).find(".btn").css("color");
                $(this).find(".fa").css({'font-size': btnFont, 'color': btnColor});
            });
        });
    </script>

    <script>
        $(document).ready(function () {
            let formData = {action: "GET_COMMON_FEE", sub_action: "GET_MASTER", page_manage: "ADMIN",};
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
                'ajax': {
                    'url': 'model/manage_common_fee_payment_process.php',
                    'data': formData
                },
                'columns': [
                    {data: 'doc_id'},
                    {data: 'payment_date'},
                    {data: 'house_number'},
                    {data: 'detail'},
                    {data: 'month_name_start'},
                    {data: 'month_name_to'},
                    {data: 'period_year'},
                    {data: 'amount'},
                    {data: 'payment_status'},
                    {data: 'update'},
                ]
            });
        });
    </script>

    <script>
        $(document).ready(function () {
            // เมื่อคลิกปุ่ม Save
            $('#saveButton').on('click', function (event) {
                event.preventDefault(); // ป้องกันการรีเฟรชหรือการส่งฟอร์มปกติ

                // เก็บข้อมูลฟอร์ม
                let recordForm = $('#recordForm');
                let formData = recordForm.serialize();

                // Disable ปุ่ม Save
                $(this).attr('disabled', true);

                // ส่งข้อมูลผ่าน AJAX
                $.ajax({
                    url: 'model/manage_common_fee_payment_process.php',
                    method: "POST",
                    data: formData,
                    success: function (data) {
                        alertify.success(data); // แสดงข้อความแจ้งเตือนสำเร็จ
                        recordForm[0].reset(); // รีเซ็ตฟอร์ม
                        $('#recordModal').modal('hide'); // ปิด Modal
                        $('#saveButton').attr('disabled', false); // เปิดใช้งานปุ่ม Save
                        $('#TableRecordList').DataTable().ajax.reload();
                    },
                    error: function (xhr, status, error) {
                        alertify.error("Error: " + error); // แสดงข้อความแจ้งเตือนข้อผิดพลาด
                        $('#saveButton').attr('disabled', false); // เปิดใช้งานปุ่ม Save
                    }
                });
            });
        });
    </script>

    <script>
        $("#TableRecordList").on('click', '.update', function () {
            let id = $(this).attr("id");
            let formData = {action: "GET_DATA", id: id};

            $.ajax({
                type: "POST",
                url: 'model/manage_common_fee_payment_process.php',
                dataType: "json",
                data: formData,
                success: function (response) {
                    // ตรวจสอบว่า response มีข้อมูลหรือไม่
                    if (response && response.length > 0) {
                        let data = response[0]; // ใช้ข้อมูลตัวแรกจาก response array

                        let id = data.id;
                        let doc_id = data.doc_id;
                        let detail = data.detail;
                        let payment_date = data.payment_date;
                        let house_number = data.house_number;
                        let month_name_start = data.month_name_start;
                        let month_name_to = data.month_name_to;
                        let period_year = data.period_year;
                        let amount = data.amount;
                        let picture_payment = data.picture_payment;
                        let payment_status = data.payment_status;
                        let payment_status_desc = (payment_status === "Y") ? "ชำระเรียบร้อยแล้ว" : "ยังไม่ยืนยันการชำระ";

                        if (payment_status === "Y") {
                            $('input[name="payment_status"][value="Y"]').prop('checked', true);
                            $('#saveButton').attr('disabled', true);
                        } else {
                            $('input[name="payment_status"][value="N"]').prop('checked', true);
                            $('#saveButton').attr('disabled', false);
                        }

                        // path ของไฟล์ภาพ
                        let image_path = 'uploads/slips/' + picture_payment;

                        // แสดง modal
                        $('#recordModal').modal('show');
                        $('#id').val(id);
                        $('#doc_id').val(doc_id);
                        $('#detail').val(detail);
                        $('#payment_date').val(payment_date);
                        $('#house_number').val(house_number);
                        $('#month_name_start').val(month_name_start);
                        $('#month_name_to').val(month_name_to);
                        $('#period_year').val(period_year);
                        $('#amount').val(amount);
                        $('#payment_status').val(payment_status);
                        $('#payment_status_desc').val(payment_status_desc);
                        $('.modal-title').html("<i class='fa fa-plus'></i> Edit Record");
                        $('#action').val('UPDATE');
                        $('#saveButton').val('Save');

                        // ตรวจสอบว่า path ของไฟล์ภาพมีค่าแล้วหรือไม่
                        if (data.picture_payment) {
                            $('#preview_image').attr('src', image_path); // ตั้งค่า src ของรูปภาพ
                            $('#preview_image').show(); // แสดงภาพ
                        } else {
                            $('#preview_image').hide(); // ซ่อนภาพถ้าไม่มี
                        }
                    }
                },
                error: function (response) {
                    alertify.error("error : " + response);
                }
            });
        });
    </script>


    <script>
        $(document).ready(function () {
            $('#doc_date').datepicker({
                format: "dd-mm-yyyy",
                todayHighlight: true,
                language: "th",
                autoclose: true
            });
        });
    </script>

    </body>
    </html>

<?php } ?>