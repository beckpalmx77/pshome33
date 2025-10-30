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
                                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
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
                                                        <button type="button" class="btn btn-secondary"
                                                                data-dismiss="modal">ปิด
                                                        </button>
                                                        <button type="button" class="btn btn-primary" id="printSlipButton">
                                                            <i class="fa fa-print"></i> พิมพ์สลิป
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
                    processing: '<div class="custom-spinner"></div>'
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
                'preXhr': function (xhr, data) {},
                'xhr': function (data) {},
                'initComplete': function (settings, json) {}
            });

            $('#saveButton').on('click', function (event) {
                event.preventDefault();
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
                            let picture_payment = data.picture_payment;
                            let payment_status = data.payment_status;
                            let payment_method = data.payment_method;
                            let payment_status_desc = (payment_status === "Y") ? "ชำระเรียบร้อยแล้ว" : "ยังไม่ยืนยันการชำระ";
                            if (payment_status === "Y") {
                                $('input[name="payment_status"][value="Y"]').prop('checked', true);
                            } else {
                                $('input[name="payment_status"][value="N"]').prop('checked', true);
                            }
                            let image_path = 'uploads/slips/' + picture_payment;
                            let create_by = data.create_by;
                            let created_at = data.created_at;
                            let approve_by = data.approve_by;
                            let updated_at = data.updated_at;

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
                            $('#payment_status').val(payment_status);
                            $('#payment_status_desc').val(payment_status_desc);
                            $('#payment_method').val(payment_method);
                            $('#create_by').val(create_by);
                            $('#created_at').val(created_at);
                            $('#approve_by').val(approve_by);
                            $('#updated_at').val(updated_at);
                            $('.modal-title').html("<i class='fa fa-plus'></i> Edit Record");
                            $('#action').val('UPDATE');
                            $('#saveButton').val('Save');
                            if (data.picture_payment) {
                                $('#preview_image').attr('src', image_path);
                                $('#preview_image').show();
                            } else {
                                $('#preview_image').hide();
                            }
                        }
                    },
                    error: function (response) {
                        alertify.error("error : " + response);
                    }
                });
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
            $("#printSlipButton").on('click', function() {
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
                printWindow.onload = function() {
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
        });

        // ฟังก์ชันเปิดรูปในหน้าต่างใหม่
        function openImageInNewWindow(imageSrc) {
            if (imageSrc && imageSrc !== "#") {
                window.open(imageSrc, '_blank');
            } else {
                alert('ไม่มีรูปภาพที่จะแสดง');
            }
        }
    </script>
    </body>
    </html>

<?php } ?>