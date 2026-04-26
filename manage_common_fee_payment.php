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
    <head>
        <link rel="stylesheet" href="css/spin_datatables.css"/>
        <link href="vendor/date-picker-1.9/css/bootstrap-datepicker.css" rel="stylesheet"/>
        <link rel="stylesheet" href="vendor/datatables/v11/jquery.dataTables.min.css"/>
        <link rel="stylesheet" href="vendor/datatables/v11/buttons.dataTables.min.css"/>

        <style>
            /* ปรับให้หน้าเพจกระชับขึ้น */
            .card-body {
                padding: 1rem;
            }

            .modal-body {
                padding: 1rem;
            }

            .modal-footer {
                padding: 0.75rem 1rem;
            }

            .form-group.row {
                margin-bottom: 0.5rem;
            }

            /* CSS สำหรับ Footer */
            .sticky-footer.bg-white {
                padding: 1rem 0;
            }

            /* CSS สำหรับตาราง */
            .dataTables_wrapper {
                overflow-x: auto;
            }

            .dataTables_wrapper .dataTables_paginate .paginate_button {
                padding: 0.3em 0.6em;
            }

            .zoom-container {
                position: relative;
                overflow: hidden;
                display: inline-block;
            }

            .zoom-container img {
                transition: transform 0.3s ease;
            }

            .zoom-container:hover img {
                transform: scale(1.5);
                cursor: zoom-out;
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
                        <input type="hidden" id="user_type" name="user_type"
                               value="<?php echo $_SESSION['account_type'] ?>">
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
                                    <button type="button" id="btnReload" class="btn btn-outline-success btn-xs" data-toggle="tooltip" title="Reload Data">
                                        <i class="fa fa-refresh"></i> Reload
                                    </button>
                                    <a href="manage_common_fee_payment_not_confirm.php?m=ข้อมูลเกี่ยวกับการเงิน&s=ค่าส่วนกลางที่ยังไม่ยืนยันการชำระ" class="btn btn-outline-danger ml-2">
                                        <i class="fas fa-search-dollar"></i> ค้นหายังไม่ชำระ
                                    </a>
                                    <a href="show_duplicate_payments.php?m=ข้อมูลเกี่ยวกับการเงิน&s=ตรวจสอบรายการชำระค่าส่วนกลางบันทึกซ้ำ" class="btn btn-outline-warning ml-2">
                                        <i class="fas fa-copy"></i> ตรวจสอบบันทึกซ้ำ
                                    </a>
                                </div>
                                <div class="card-body">
                                    <section class="container-fluid">

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
                                                                        <select class="form-control"
                                                                                id="payment_method"
                                                                                name="payment_method"
                                                                                required>
                                                                            <option value="โอนเงิน">โอนเงิน</option>
                                                                            <option value="เงินสด">เงินสด</option>
                                                                        </select>
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
                                                                        <button type="button" class="btn btn-info" id="scanQRBtn">
                                                                            <i class="fas fa-qrcode"></i> Scan QR Code เพื่อ Verify
                                                                        </button>
                                                                    </div>
                                                                </div>

                                                                <div class="form-group row" id="qrScannerSection" style="display: none;">
                                                                    <div class="col-sm-12">
                                                                        <div id="reader" style="width: 100%;"></div>
                                                                        <button type="button" class="btn btn-secondary btn-sm mt-2" id="closeScannerBtn">ปิดกล้อง</button>
                                                                    </div>
                                                                </div>

                                                                <div class="form-group row" id="qrVerifyResult" style="display: none;">
                                                                    <div class="col-sm-12">
                                                                        <div class="alert" id="qrResultAlert"></div>
                                                                    </div>
                                                                </div>

                                                                <div class="form-group row">
                                                                    <div class="col-sm-4">
                                                                        <label for="create_by"
                                                                               class="control-label">สร้างรายการ
                                                                            โดย</label>
                                                                        <input type="text" class="form-control"
                                                                               id="create_by"
                                                                               name="create_by"
                                                                               readonly="true"
                                                                               placeholder="">
                                                                    </div>
                                                                    <div class="col-sm-4">
                                                                        <label for="created_at"
                                                                               class="control-label">วัน-เวลา
                                                                            สร้างรายการ</label>
                                                                        <input type="text" class="form-control"
                                                                               id="created_at"
                                                                               name="created_at"
                                                                               readonly="true"
                                                                               placeholder="">
                                                                    </div>
                                                                    <div class="col-sm-4">
                                                                        <label for="remark"
                                                                               class="control-label">หมายเหตุ
                                                                        </label>
                                                                        <input type="text" class="form-control"
                                                                               id="remark"
                                                                               name="remark"
                                                                               placeholder="">
                                                                    </div>
                                                                </div>
                                                                <div class="form-group row">
                                                                    <div class="col-sm-4">
                                                                        <label for="approve_by"
                                                                               class="control-label">ปรับปรุงข้อมูล/อนุมัติ
                                                                            โดย</label>
                                                                        <input type="text" class="form-control"
                                                                               id="approve_by"
                                                                               name="approve_by"
                                                                               readonly="true"
                                                                               placeholder="">
                                                                    </div>
                                                                    <div class="col-sm-4">
                                                                        <label for="updated_at"
                                                                               class="control-label">วัน-เวลา
                                                                            ปรับปรุงข้อมูล</label>
                                                                        <input type="text" class="form-control"
                                                                               id="updated_at"
                                                                               name="updated_at"
                                                                               readonly="true"
                                                                               placeholder="">
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
                                                                        class="fa fa-times"></i>
                                                            </button>
                                                        </div>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>

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
                                                        </button>
                                                    </div>
                                                    <div class="modal-body">
                                                        <img id="slipImage" src="" alt="Slip Image"
                                                             class="img-fluid rounded shadow-sm">
                                                    </div>
                                                    <div class="modal-footer justify-content-between">
                                                        <button type="button" class="btn btn-primary"
                                                                id="printSlipButton">
                                                            <i class="fa fa-print"></i> พิมพ์สลิป
                                                        </button>
                                                        <button type="button" class="btn btn-secondary"
                                                                data-dismiss="modal">ปิด
                                                        </button>
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

    <?php
    include('includes/Modal-Logout.php');
    ?>

    <a class="scroll-to-top rounded" href="#page-top">
        <i class="fas fa-angle-up"></i>
    </a>

    <script src="vendor/jquery/jquery.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jsQR/1.4.0/jsQR.min.js"></script>
    <script src="vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
    <script src="vendor/jquery-easing/jquery.easing.min.js"></script>
    <script src="js/myadmin.min.js"></script>
    <script src="js/util/calculate_datetime.js"></script>
    <script src="vendor/bootstrap-datepicker/js/bootstrap-datepicker.min.js"></script>
    <script src="vendor/date-picker-1.9/js/bootstrap-datepicker.js"></script>
    <script src="vendor/date-picker-1.9/locales/bootstrap-datepicker.th.min.js"></script>
    <script src="vendor/datatables/v11/bootbox.min.js"></script>
    <script src="vendor/datatables/v11/jquery.dataTables.min.js"></script>

    <script>
        $(document).ready(function () {
            $(".icon-input-btn").each(function () {
                let btnFont = $(this).find(".btn").css("font-size");
                let btnColor = $(this).find(".btn").css("color");
                $(this).find(".fa").css({'font-size': btnFont, 'color': btnColor});
            });

            let dataRecords = $('#TableRecordList').DataTable({
                'lengthMenu': [[5, 10, 20, 50, 100], [5, 10, 20, 50, 100]],
                'language': {
                    search: 'ค้นหา บ้านเลขที่',
                    lengthMenu: 'แสดง _MENU_ รายการ',
                    info: 'หน้าที่ _PAGE_ จาก _PAGES_',
                    infoEmpty: 'ไม่มีข้อมูล',
                    zeroRecords: "ไม่มีข้อมูลตามเงื่อนไข",
                    infoFiltered: '(กรองข้อมูลจากทั้งหมด _MAX_ รายการ)',
                    paginate: {
                        previous: 'ก่อนหน้า',
                        last: 'สุดท้าย',
                        next: 'ต่อไป'
                    },
                    processing: '<div class="custom-spinner"></div>',
                    loadingRecords: '<div class="text-center p-3"><span class="spinner-border spinner-border-sm text-primary" role="status"></span> กำลังโหลด...</div>'
                },
                'processing': true,
                'serverSide': true,
                'serverMethod': 'post',
                'scrollX': true,
                'ajax': {
                    'url': 'model/manage_common_fee_payment_process.php',
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
                'autoWidth': false,
                'preXhr': function (xhr, data) {
                },
                'xhr': function (data) {
                },
                'initComplete': function (settings, json) {
                }
            });

            // Auto-refresh every 5 seconds (no page flicker)
            //setInterval(function() {
                //dataRecords.ajax.reload(null, false);
            //}, 5000);

            $('#saveButton').on('click', function (event) {
                event.preventDefault();

                // ----------------------------------------------------------------
                // START : ส่วนที่เพิ่มตรวจสอบ Period Month Start > Period Month To
                // ----------------------------------------------------------------
                let startMonth = parseInt($('#period_month_start').val());
                let toMonth = parseInt($('#period_month_to').val());

                // ตรวจสอบว่ามีการเลือกทั้งคู่และเป็นตัวเลข
                if (!isNaN(startMonth) && !isNaN(toMonth)) {
                    if (startMonth > toMonth) {
                        // แจ้งเตือนผู้ใช้
                        if (typeof alertify !== 'undefined') {
                            alertify.error("งวดเดือนเริ่มต้น ต้องไม่มากกว่า งวดเดือนสิ้นสุด");
                        } else {
                            alert("งวดเดือนเริ่มต้น ต้องไม่มากกว่า งวดเดือนสิ้นสุด");
                        }
                        // โฟกัสไปที่ช่องที่ผิดเพื่อให้แก้ไข และหยุดการทำงาน
                        $('#period_month_to').focus();
                        return;
                    }
                }
                // ----------------------------------------------------------------
                // END : ส่วนที่เพิ่มตรวจสอบ
                // ----------------------------------------------------------------

                let recordForm = $('#recordForm');
                let formData = recordForm.serialize();
                $(this).attr('disabled', true);
                $.ajax({
                    url: 'model/manage_common_fee_payment_process.php',
                    method: "POST",
                    data: formData,
                    success: function (data) {
                        alertify.success(data);
                        recordForm[0].reset();
                        $('#recordModal').modal('hide');
                        $('#saveButton').attr('disabled', false);
                        $('#TableRecordList').DataTable().ajax.reload();
                    },
                    error: function (xhr, status, error) {
                        alertify.error("Error: " + error);
                        $('#saveButton').attr('disabled', false);
                    }
                });
            });

            // Reload Data button click
            $('#btnReload').on('click', function() {
                $('#TableRecordList').DataTable().ajax.reload();
            });

            $("#TableRecordList").on('click', '.print', function () {
                let id = $(this).attr("id");
                let url = "";
                let user_type = $('#user_type').val();
                if (user_type === 'user') {
                    url = "print_pdf_smart.php?id=" + encodeURIComponent(id);
                } else {
                    url = "print_pdf.php?id=" + encodeURIComponent(id);
                }
                window.open(url, "_blank");
            });

            $('#doc_date').datepicker({
                format: "dd-mm-yyyy",
                todayHighlight: true,
                language: "th",
                autoclose: true
            });

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

            // **เพิ่มฟังก์ชันสำหรับปุ่มพิมพ์สลิป**
            $("#printSlipButton").on('click', function () {
                let imageUrl = $("#slipImage").attr("src");

                // ตรวจสอบว่ามี URL รูปภาพหรือไม่
                if (!imageUrl || imageUrl === '#') {
                    alert('ไม่พบรูปภาพสลิปที่จะพิมพ์');
                    return;
                }

                // สร้างหน้าต่างใหม่เพื่อแสดงรูปภาพและสั่งพิมพ์
                let printWindow = window.open('', '_blank');
                printWindow.document.write('<html><head><title>พิมพ์สลิป</title>');
                // CSS สำหรับรูปภาพเพื่อให้แสดงผลดีเมื่อพิมพ์
                // printWindow.document.write('<style>body{margin: 0; padding: 0;} img{width: 100%; height: auto; display: block;}</style>');
                printWindow.document.write('<style>body{margin: 0; padding: 0;} img{max-width: 100mm; height: auto; display: block; margin: 20px auto;}</style>');
                printWindow.document.write('</head><body>');
                printWindow.document.write('<img src="' + imageUrl + '" alt="Slip Image for Print">');
                printWindow.document.write('</body></html>');

                printWindow.document.close();

                // สั่งพิมพ์เมื่อหน้าต่างโหลดเสร็จ
                printWindow.onload = function () {
                    printWindow.print();
                    // printWindow.close(); // เลือกว่าจะปิดหน้าต่างทันทีหรือไม่
                };
            });

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

            $("#TableRecordList").on('click', '.update', function () {
                let id = $(this).attr("id");
                let formData = {action: "GET_DATA", id: id};

                $.ajax({
                    type: "POST",
                    url: 'model/manage_common_fee_payment_process.php',
                    dataType: "json",
                    data: formData,
                    success: function (response) {
                        if (response && response.length > 0) {
                            let data = response[0];

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

                            if (picture_payment && picture_payment !== "") {
                                $("#preview_image").attr("src", picture_payment);
                                $("#preview_image").show();
                            } else {
                                $("#preview_image").attr("src", "#");
                                $("#preview_image").hide();
                            }

                            $("#recordModal .modal-title").text("แก้ไขข้อมูลการชำระค่าส่วนกลาง");
                            $("#id").val(id);
                            $("#doc_id").val(doc_id);
                            $("#payment_date").val(payment_date);
                            $("#house_number").val(house_number);
                            $("#detail").val(detail);
                            $("#period_month_start").val(period_month_start);
                            $("#period_month_to").val(period_month_to);
                            $("#period_year").val(period_year);
                            $("#amount").val(amount);
                            $("#payment_method").val(payment_method);
                            $("#payment_status_desc").val(payment_status_desc);
                            $("#remark").val(remark);
                            $("#create_by").val(data.create_by);
                            $("#created_at").val(data.created_at);
                            $("#approve_by").val(data.approve_by);
                            $("#updated_at").val(data.updated_at);
                            $("#action").val("UPDATE");

                            $("#recordModal").modal("show");
                        }
                    },
                    error: function () {
                        alertify.error("เกิดข้อผิดพลาดในการดึงข้อมูล");
                    }
                });
            });
        });

        // ฟังก์ชันเปิดรูปในหน้าต่างใหม่
        function openImageInNewWindow(imageSrc) {
            if (imageSrc && imageSrc !== "#") {
                window.open(imageSrc, '_blank');
            } else {
                alert('ไม่มีรูปภาพที่จะแสดง');
            }
        }

        // ============================================
        // QR Code Scanner for Bank Slip Verify (from uploaded image)
        // ============================================
        let html5QrCode = null;

        $("#scanQRBtn").on("click", function() {
            // Get the image src from preview or slip
            let imageSrc = $("#preview_image").attr("src");
            if (!imageSrc || imageSrc === "#") {
                // Try slip image in modal
                imageSrc = $("#slipImage").attr("src");
            }
            
            if (!imageSrc || imageSrc === "#" || imageSrc === "") {
                alert("ไม่พบรูปภาพสำหรับ scan QR Code");
                return;
            }

            $("#qrVerifyResult").show();
            $("#qrResultAlert").attr("class", "alert alert-info");
            $("#qrResultAlert").html("กำลังอ่าน QR Code...");

            scanQRFromImage(imageSrc);
        });

        function scanQRFromImage(imageSrc) {
            $("#qrResultAlert").attr("class", "alert alert-info");
            $("#qrResultAlert").html("กำลังอ่าน QR Code...");

            let img = new Image();
            img.crossOrigin = "Anonymous";
            img.onload = function() {
                let canvas = document.createElement("canvas");
                canvas.width = img.width;
                canvas.height = img.height;
                let ctx = canvas.getContext("2d");
                ctx.drawImage(img, 0, 0);
                
                let imageData = ctx.getImageData(0, 0, canvas.width, canvas.height);
                let code = jsQR(imageData.data, imageData.width, imageData.height, {
                    inversionAttempts: "dontInvert"
                });
                
                if (code) {
                    verifyQRCode(code.data);
                } else {
                    // Try with inversion
                    let codeInvert = jsQR(imageData.data, imageData.width, imageData.height, {
                        inversionAttempts: "invertFirst"
                    });
                    if (codeInvert) {
                        verifyQRCode(codeInvert.data);
                    } else {
                        $("#qrResultAlert").attr("class", "alert alert-danger");
                        $("#qrResultAlert").html("<strong>ไม่พบ QR Code</strong><br>ไม่สามารถอ่าน QR Code จากรูปภาพ กรุณาตรวจสอบว่ารูปภาพมี QR Code ที่ชัดเจน");
                    }
                }
            };
            img.onerror = function() {
                $("#qrResultAlert").attr("class", "alert alert-danger");
                $("#qrResultAlert").html("<strong>Error</strong>: ไม่สามารถโหลดรูปภาพได้");
            };
            img.src = imageSrc;
        }

        function verifyQRCode(qrString) {
            $("#qrVerifyResult").show();
            
            try {
                let qrData = parseThaiQR(qrString);
                let inputAmount = parseFloat($("#amount").val().replace(/,/g, '')) || 0;
                
                let alertClass = "alert-danger";
                let alertMessage = "<strong>QR Code ไม่ถูกต้อง</strong>";
                let isValid = false;

                // ตรวจสอบ Amount
                if (qrData.amount === inputAmount) {
                    isValid = true;
                    alertClass = "alert-success";
                    alertMessage = "<strong>✓ จำนวนเงินตรงกัน</strong><br>" +
                                "จำนวนเงิน: " + qrData.amount.toLocaleString() + " บาท<br>" +
                                "ธนาคาร: " + qrData.senderBank + "<br>" +
                                "บัญชีผู้รับ: " + qrData.receiverAccount;
                } else {
                    alertClass = "alert-warning";
                    alertMessage = "<strong>⚠ จำนวนเงินไม่ตรงกัน</strong><br>" +
                                "จาก QR: " + qrData.amount.toLocaleString() + " บาท<br>" +
                                "จากฟอร์ม: " + inputAmount.toLocaleString() + " บาท";
                }

                $("#qrResultAlert").attr("class", "alert " + alertClass);
                $("#qrResultAlert").html(alertMessage);

            } catch (e) {
                $("#qrResultAlert").attr("class", "alert alert-danger");
                $("#qrResultAlert").html("<strong>Error:</strong> ไม่สามารถอ่าน QR Code ได้: " + e.message);
            }
        }

        function parseThaiQR(qrString) {
            // Thai QR Payment standard (PromptPay)
            // Format: 000201010211...
            
            let result = {
                amount: 0,
                receiverAccount: "",
                senderBank: "",
                timestamp: ""
            };

            if (!qrString || qrString.length < 50) {
                throw new Error("Invalid QR Code format");
            }

            // Parse using character-based extraction
            // EMVCo Thai QR Code standard
            
            let pos = 0;
            let data = qrString;
            
            // Find Amount (54)
            let amountMatch = data.match(/54[0-9]{2}(\d{2})/);
            if (amountMatch) {
                let amountLen = parseInt(amountMatch[1]);
                let amountStart = data.indexOf("54") + 4;
                let amountEnd = amountStart + amountLen;
                if (amountEnd <= data.length) {
                    result.amount = parseFloat(data.substring(amountStart, amountEnd)) || 0;
                }
            }

            // Find Receiver Account (38)
            let accMatch = data.match(/38[0-9]{2}(\d{2})/);
            if (accMatch) {
                let accLen = parseInt(accMatch[1]);
                let accStart = data.indexOf("38") + 4;
                let accEnd = accStart + accLen;
                if (accEnd <= data.length) {
                    result.receiverAccount = data.substring(accStart, accEnd);
                }
            }

            // Find Sender Bank (30)
            let bankMatch = data.match(/30[0-9]{2}(\d{2})/);
            if (bankMatch) {
                let bankLen = parseInt(bankMatch[1]);
                let bankStart = data.indexOf("30") + 4;
                let bankEnd = bankStart + bankLen;
                if (bankEnd <= data.length) {
                    result.senderBank = data.substring(bankStart, bankEnd);
                }
            }

            if (result.amount === 0) {
                // Try alternative parsing - look for specific value after ID
                let parts = data.split("5802TH");
                if (parts.length > 1) {
                    // Try to extract amount from hex or numeric
                    let amountPattern = /54(\d{2})(\d+)/;
                    let match = data.match(amountPattern);
                    if (match) {
                        result.amount = parseFloat(match[2]) / 100;
                    }
                }
            }

            return result;
        }
    </script>
    </body>
    </html>

<?php } ?>