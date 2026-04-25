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
                                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-start">
                                    <button type="button" class="btn btn-outline-success" id="btnReloadTable">
                                        <i class="fas fa-sync"></i> Reload Data
                                    </button>
                                    <a href="manage_common_fee_payment.php?m=ข้อมูลเกี่ยวกับการเงิน&s=ข้อมูลการจัดเก็บค่าส่วนกลาง" class="btn btn-outline-primary ml-2">
                                        <i class="fas fa-list"></i> รายการชำระค่าส่วนกลาง
                                    </a>
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
                                            <table id="TableRecordList" class="display nowrap" style="width:100%;">
                                                <thead>
                                                <tr>
                                                    <th>วันที่เอกสาร</th>
                                                    <th>บ้านเลขที่</th>
                                                    <th>ซอย</th>
                                                    <th>ผู้ชำระ</th>
                                                    <th>picture</th>
                                                    <th>งวดเดือน</th>
                                                    <th>ปี</th>
                                                    <th>ค่าส่วนกลาง</th>
                                                    <th>จำนวนงวด</th>
                                                    <th>ยอดชำระ</th>
                                                    <th>Slip</th>
                                                    <th>สถานะ</th>
                                                    <th>Action</th>
                                                    <th>ใบเสร็จ</th>
                                                    <th>ขนาดพื้นที่ ตรว</th>
                                                    <th>ค่าเก็บขยะ</th>
                                                    <th>ลบข้อมูล</th>
                                                </tr>
                                                </thead>
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
                                                                               class="control-label">วันที่เอกสาร</label>
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
                                                                    <div class="col-md-4">
                                                                        <label for="period_month_start">เริ่มงวดเดือน</label>
                                                                        <select name="period_month_start"
                                                                                id="period_month_start"
                                                                                class="form-control" required>
                                                                            <option value="">เลือก</option>
                                                                            <?php
                                                                            $months = [
                                                                                1 => 'มกราคม', 2 => 'กุมภาพันธ์', 3 => 'มีนาคม', 4 => 'เมษายน',
                                                                                5 => 'พฤษภาคม', 6 => 'มิถุนายน', 7 => 'กรกฎาคม', 8 => 'สิงหาคม',
                                                                                9 => 'กันยายน', 10 => 'ตุลาคม', 11 => 'พฤศจิกายน', 12 => 'ธันวาคม'
                                                                            ];
                                                                            foreach ($months as $val => $name) {
                                                                                echo "<option value='$val'>$name</option>";
                                                                            }
                                                                            ?>
                                                                        </select>
                                                                    </div>

                                                                    <div class="col-md-4">
                                                                        <label for="period_month_to">ถึงงวดเดือน</label>
                                                                        <select name="period_month_to"
                                                                                id="period_month_to"
                                                                                class="form-control"
                                                                                required>
                                                                            <option value="">เลือก</option>
                                                                            <?php
                                                                            foreach ($months as $val => $name) {
                                                                                echo "<option value='$val'>$name</option>";
                                                                            }
                                                                            ?>
                                                                        </select>
                                                                    </div>

                                                                    <div class="col-md-4">
                                                                        <label for="period_year">งวดปี</label>
                                                                        <input type="number" name="period_year"
                                                                               id="period_year"
                                                                               class="form-control" required
                                                                               value="<?php echo date('Y'); ?>">
                                                                    </div>
                                                                </div>

                                                                <div class="form-group row">
                                                                    <div class="col-sm-4">
                                                                        <label for="amount"
                                                                               class="control-label">จำนวนเงิน</label>
                                                                        <input type="text" class="form-control"
                                                                               id="amount"
                                                                               name="amount"
                                                                               required="required"
                                                                               placeholder="">
                                                                    </div>
                                                                    <div class="col-sm-4">
                                                                        <label for="payment_method"
                                                                               class="control-label">วิธีการชำระ</label>
                                                                        <input type="text" class="form-control"
                                                                               id="payment_method"
                                                                               name="payment_method"
                                                                               readonly="true"
                                                                               placeholder="">
                                                                    </div>
                                                                    <div class="col-sm-4">
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
                                                                    <!--div class="col-sm-6 zoom-container">
                                                                        <img id="preview_image" src="#"
                                                                             alt="Preview Image"
                                                                             style="display: none; margin-top: 10px; max-width: 200px;"/>
                                                                    </div-->
                                                                    <div class="col-sm-6 zoom-container">
                                                                        <img id="preview_image" src="#"
                                                                             alt="Preview Image"
                                                                             style="display: none; margin-top: 10px; max-width: 200px; cursor: pointer;"
                                                                             onclick="openImageInNewWindow(this.src)"/>
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

                                                                <div class="form-group row">
                                                                    <div class="col-sm-12">
                                                                        <label for="remark"
                                                                               class="control-label">หมายเหตุ</label>
                                                                        <input type="text" class="form-control"
                                                                               id="remark"
                                                                               name="remark"
                                                                               required="required"
                                                                               placeholder="">
                                                                    </div>
                                                                </div>

                                                            </div>
                                                        </div>

                                                        <div class="modal-footer">
                                                            <input type="hidden" name="id" id="id"/>
                                                            <input type="hidden" name="action" id="action" value=""/>
                                                            <!--button type="button" class="btn btn-success"
                                                                    id="printButton">Print <i
                                                                        class="fa fa-print"></i>
                                                            </button-->
                                                            <button type="button" class="btn btn-primary"
                                                                    id="saveButton">Save <i
                                                                        class="fa fa-check"></i>
                                                            </button>
                                                            <button type="button" class="btn btn-danger"
                                                                    data-dismiss="modal">Close <i
                                                                        class="fa fa-times"></i>
                                                            </button>
                                                        </div>
                                                    </form>

                                                </div>
                                            </div>
                                        </div>

                                        <!-- Modal -->
                                        <div class="modal fade" id="slipModal" tabindex="-1" role="dialog"
                                             aria-labelledby="slipModalLabel" aria-hidden="true">
                                            <div class="modal-dialog modal-dialog-centered" role="document">
                                                <div class="modal-content text-center">
                                                    <div class="modal-header">
                                                        <h5 class="modal-title" id="slipModalLabel">
                                                            หลักฐานการโอนเงิน</h5>
                                                        <button type="button" class="close" data-dismiss="modal"
                                                                aria-label="Close">
                                                            <span aria-hidden="true">&times;</span>
                                                            <!-- ปุ่มปิดมุมขวาบน -->
                                                        </button>
                                                    </div>
                                                    <div class="modal-body">
                                                        <img id="slipImage" src="" alt="Slip Image"
                                                             class="img-fluid rounded shadow-sm">
                                                    </div>
                                                    <div class="modal-footer justify-content-between">
                                                        <!--a id="downloadSlip" href="#" download class="btn btn-success">ดาวน์โหลด</a>
                                                        <button type="button" class="btn btn-primary" id="printSlip">พิมพ์</button-->
                                                        <button type="button" class="btn btn-secondary"
                                                                data-dismiss="modal">ปิด
                                                        </button> <!-- ปุ่มปิดล่าง -->
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
                                                        คุณต้องการลบข้อมูลนี้ใช่หรือไม่?
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

                </div>
            </div>
            <?php include('includes/Footer.php'); ?>
        </div>
    </div>

    <?php
    include('includes/Modal-Logout.php');
    //include('includes/Footer.php');
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
        .dataTables_wrapper {
            overflow-x: auto;
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
            let dataRecords = $('#TableRecordList').DataTable({
                'lengthMenu': [[5, 10, 20, 50, 100], [5, 10, 20, 50, 100]],
                'language': {
                    search: 'ค้นหา บ้านเลขที่', lengthMenu: 'แสดง _MENU_ รายการ',
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
                'scrollX': true,
                'ajax': {
                    'url': 'model/manage_common_fee_payment_not_confirm_process.php',
                    'type': 'POST',
                    'data': function (d) {
                        d.action = 'GET_COMMON_FEE';
                        d.sub_action = 'GET_MASTER';
                        d.page_manage = 'ADMIN';
                        return d;
                    }
                },
                'columns': [
                    {data: 'payment_date', width: '200px'},
                    {data: 'house_number', width: '100px'},
                    {data: 'alley', width: '100px'},
                    {data: 'detail', width: '200px'},
                    {data: 'line_picture_profile', width: '200px'},
                    {data: 'month_name_period', width: '120px'},
                    {data: 'period_year', width: '100px'},
                    {data: 'common_fee', className: 'dt-body-right', width: '120px'},
                    {data: 'payment_type', className: 'dt-body-center', width: '100px'},
                    {data: 'amount', className: 'dt-body-right', width: '120px'},
                    {data: 'slip', width: '80px'},
                    {data: 'payment_status_desc', width: '100px'},
                    {data: 'update', width: '80px'},
                    {data: 'print', width: '80px'},
                    {data: 'area_size', className: 'dt-body-right', width: '100px'},
                    {data: 'garbage_collection_fee', className: 'dt-body-right', width: '120px'},
                    {data: 'delete', width: '80px'},
                ],
                'autoWidth': false // ปิด autowidth เพื่อให้ width ที่กำหนดมีผลจริง
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

            // Reload Data button click
            $('#btnReload').on('click', function() {
                $('#TableRecordList').DataTable().ajax.reload();
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
                        let period_month_start = data.period_month_start;
                        let period_month_to = data.period_month_to;
                        let period_year = data.period_year;
                        let amount = data.amount;
                        let remark = data.remark;
                        let picture_payment = data.picture_payment;
                        let payment_status = data.payment_status;
                        let payment_method = data.payment_method;
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
                        $('#period_month_start').val(period_month_start);
                        $('#period_month_to').val(period_month_to);
                        $('#period_year').val(period_year);
                        $('#amount').val(amount);
                        $('#remark').val(remark);
                        $('#payment_status').val(payment_status);
                        $('#payment_status_desc').val(payment_status_desc);
                        $('#payment_method').val(payment_method);
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
        // ฟังก์ชันเปิดรูปในหน้าต่างใหม่
        function openImageInNewWindow(imageSrc) {
            if (imageSrc && imageSrc !== "#") {
                window.open(imageSrc, '_blank');
            } else {
                alert('ไม่มีรูปภาพที่จะแสดง');
            }
        }
    </script>

    <script>
        $(document).ready(function () {
            $('#printButton').on('click', function (event) {
                event.preventDefault();

                // ดึงค่าจากฟอร์ม
                const formData = $('#recordForm').serializeArray();

                // สร้างฟอร์มชั่วคราวสำหรับ POST
                const tempForm = $('<form>', {
                    method: 'POST',
                    action: 'print_pdf.php',
                    target: '_blank' // เปิดในแท็บใหม่
                });

                // เพิ่มข้อมูลเข้าไปในฟอร์ม
                formData.forEach(function (item) {
                    tempForm.append($('<input>', {
                        type: 'hidden',
                        name: item.name,
                        value: item.value
                    }));
                });

                // เพิ่มฟอร์มชั่วคราวเข้าไปใน DOM และส่งฟอร์ม
                $('body').append(tempForm);
                tempForm.submit();

                // ลบฟอร์มชั่วคราวหลังจากส่ง
                tempForm.remove();
            });
        });
    </script>

    <script>
        $("#TableRecordList").on('click', '.print', function () {
            let id = $(this).attr("id");
            let url = "print_pdf.php?id=" + encodeURIComponent(id);
            window.open(url, "_blank"); // เปิดหน้าใหม่
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

    <script>
        $("#TableRecordList").on('click', '.slip', function () {
            let id = $(this).attr("id");

            $.ajax({
                url: "display_slip.php",
                type: "GET",
                data: {id: id},
                dataType: "json",
                success: function (response) {
                    if (response.status === 1) {
                        $("#slipImage").attr("src", response.image_url);
                        $("#downloadSlip").attr("href", response.image_url);
                        $("#slipModal").modal('show');
                    } else {
                        alert("ไม่พบรูปภาพ");
                    }
                },
                error: function () {
                    alert("เกิดข้อผิดพลาดในการโหลดรูปภาพ");
                }
            });
        });

        $("#printSlip").on('click', function () {
            let imageSrc = $("#slipImage").attr("src");
            let win = window.open('', '_blank');
            win.document.write('<html><head><title>พิมพ์หลักฐาน</title></head><body>');
            win.document.write('<img src="' + imageSrc + '" style="width:100%; max-width:600px;">');
            win.document.write('</body></html>');
            win.document.close();
            win.print();
        });
    </script>

    <script>
        let deleteId = null;

        $("#TableRecordList").on('click', '.delete', function () {
            deleteId = $(this).attr("id");
            $("#confirmDeleteModal").modal("show");
        });

        $("#confirmDeleteBtn").on("click", function () {
            if (deleteId) {
                $.ajax({
                    url: "model/manage_common_fee_payment_process.php",
                    method: "POST",
                    data: {id: deleteId, action: "DELETE"},
                    success: function (response) {
                        $("#confirmDeleteModal").modal("hide");
                        $('#TableRecordList').DataTable().ajax.reload();
                        alertify.success("ลบข้อมูลเรียบร้อยแล้ว");
                    },
                    error: function () {
                        alertify.error("เกิดข้อผิดพลาดในการลบข้อมูล");
                    }
                });
            }
        });

    </script>


    </body>
    </html>

<?php } ?>