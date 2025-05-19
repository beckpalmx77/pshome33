<?php

include('includes/Header.php');
include('config/connect_db.php');
$curr_date = date("d-m-Y");

?>

    <!DOCTYPE html>
    <html lang="th">
    <body id="page-top">
    <div id="wrapper">
        <?php
        //include('includes/Side-Bar.php');
        ?>

        <div id="content-wrapper" class="d-flex flex-column">
            <div id="content">
                <?php
                //include('includes/Top-Bar.php');
                ?>
                <!-- Container Fluid-->
                <div class="container-fluid" id="container-wrapper">
                    <div class="d-sm-flex align-items-center justify-content-between mb-4">
                        <h1 class="h5 mb-0 text-gray-800">ประวัติการชำระค่าส่วนกลาง</h1>
                        <ol class="breadcrumb">
                            <div class="text-sm text-muted" id="user-info-liff"></div>
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
                                            <table id='TableRecordList' class='display dataTable'>
                                                <thead>
                                                <tr>
                                                    <th>วันที่เอกสาร</th>
                                                    <th>งวดเดือนเริ่มต้น</th>
                                                    <th>ถึงงวดเดือน</th>
                                                    <th>ปี</th>
                                                    <th>ยอดชำระ</th>
                                                    <th>สถานะ</th>
                                                    <th>ผู้ชำระ</th>
                                                    <th>Slip</th>
                                                    <th>Action</th>
                                                </tr>
                                                </thead>
                                                <tfoot>
                                                <tr>
                                                    <th>วันที่เอกสาร</th>
                                                    <th>งวดเดือนเริ่มต้น</th>
                                                    <th>ถึงงวดเดือน</th>
                                                    <th>ปี</th>
                                                    <th>ยอดชำระ</th>
                                                    <th>สถานะ</th>
                                                    <th>ผู้ชำระ</th>
                                                    <th>Slip</th>
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

                                                            </div>
                                                        </div>

                                                        <div class="modal-footer">
                                                            <input type="hidden" name="id" id="id"/>
                                                            <input type="hidden" name="action" id="action" value=""/>
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
                                        <div class="modal fade" id="slipModal" tabindex="-1" role="dialog" aria-labelledby="slipModalLabel" aria-hidden="true">
                                            <div class="modal-dialog modal-dialog-centered" role="document">
                                                <div class="modal-content text-center">
                                                    <div class="modal-header">
                                                        <h5 class="modal-title" id="slipModalLabel">หลักฐานการโอนเงิน</h5>
                                                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                                            <span aria-hidden="true">&times;</span> <!-- ปุ่มปิดมุมขวาบน -->
                                                        </button>
                                                    </div>
                                                    <div class="modal-body">
                                                        <img id="slipImage" src="" alt="Slip Image" class="img-fluid rounded shadow-sm">
                                                    </div>
                                                    <div class="modal-footer justify-content-between">
                                                        <!--a id="downloadSlip" href="#" download class="btn btn-success">ดาวน์โหลด</a>
                                                        <button type="button" class="btn btn-primary" id="printSlip">พิมพ์</button-->
                                                        <button type="button" class="btn btn-secondary" data-dismiss="modal">ปิด</button> <!-- ปุ่มปิดล่าง -->
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
                    action: 'print_pdf_smart.php',
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
            let url = "print_pdf_smart.php?id=" + encodeURIComponent(id);
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


    <script src="https://static.line-scdn.net/liff/edge/2/sdk.js"></script>
    <script src="line_oa/house/jsconfig/config_house_history_payment.js"></script>

    <script>
        let houseNumber = ''; // ตัวแปรไว้ใช้ร่วมกับ DataTable

        liff.init({ liffId: LIFF_ID })
            .then(() => {
                if (!liff.isLoggedIn()) {
                    liff.login();
                } else {
                    liff.getProfile().then(profile => {
                        const userId = profile.userId;
                        const pictureUrl = profile.pictureUrl;
                        const displayName = profile.displayName;

                        fetch('model/save_user_profile.php', {
                            method: 'POST',
                            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                            body: `userId=${encodeURIComponent(userId)}&pictureUrl=${encodeURIComponent(pictureUrl)}&displayName=${encodeURIComponent(displayName)}`
                        });


                        // ส่ง userId ไปยัง backend
                        fetch('model/get_house_number.php', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/x-www-form-urlencoded'
                            },
                            body: 'userId=' + encodeURIComponent(userId)
                        })
                            .then(response => response.json())
                            .then(data => {
                                if (data.house_number) {
                                    houseNumber = data.house_number;
                                    document.getElementById('user-info-liff').innerText =
                                        `บ้านเลขที่: ${data.house_number} | ชื่อ: ${data.f_name} ${data.l_name}`;

                                    // โหลด DataTable หลังจากได้ house_number แล้ว
                                    loadDataTable(houseNumber);
                                } else {
                                    alert("ไม่พบผู้ใช้งาน กรุณาลงทะเบียนก่อน");
                                    liff.closeWindow(); // ปิดหน้าต่าง
                                }
                            });
                    });
                }
            });

        function loadDataTable(houseNumber) {
            $('#TableRecordList').DataTable({
                destroy: true,
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
                <?php if ($_SESSION['deviceType'] !== 'computer') echo "'scrollX': true,"; ?>
                'ajax': {
                    'url': 'model/manage_common_fee_payment_smart_process.php',
                    'data': function (d) {
                        d.action = "GET_COMMON_FEE";
                        d.sub_action = "GET_MASTER";
                        d.page_manage = "ADMIN";
                        d.house_number = houseNumber; // ✅ ส่งค่าที่ได้จาก LIFF ไป
                    }
                },
                'columns': [
                    {data: 'payment_date'},
                    {data: 'month_name_start'},
                    {data: 'month_name_to'},
                    {data: 'period_year'},
                    {data: 'amount'},
                    {data: 'payment_status'},
                    {data: 'detail'},
                    {data: 'slip'},
                    {data: 'print'},
                ]
            });
        }
    </script>

    <script>
        $("#TableRecordList").on('click', '.slip', function () {
            let id = $(this).attr("id");

            $.ajax({
                url: "display_slip.php",
                type: "GET",
                data: { id: id },
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


    </body>
    </html>