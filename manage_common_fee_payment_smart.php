<?php

include('includes/Header.php');
include('config/connect_db.php');
$curr_date = date("d-m-Y");

?>

<!DOCTYPE html>
<meta charset="UTF-8"/>
<meta name="viewport" content="width=device-width, initial-scale=1"/>
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

            <div class="container-fluid" id="container-wrapper">
                <div class="d-sm-flex align-items-center justify-content-between mb-4">
                    <h4 class="h5 mb-0 text-gray-800">ประวัติการชำระค่าส่วนกลาง</h4>
                    <div class="text-sm text-muted" id="user-info-liff"></div>
                </div>

                <div class="row">
                    <div class="col-lg-12">
                        <div class="card mb-12">

                            <div class="card-body">
                                <section class="container-fluid">
                                    <div class="col-md-12">
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
                                                <th>ใบเสร็จ</th>
                                            </tr>
                                            </thead>
                                        </table>
                                        <div id="result"></div>
                                    </div>

                                    <!-- Modal แสดงรายละเอียด -->
                                    <div class="modal fade" id="recordModal">
                                        <div class="modal-dialog modal-lg">
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <h4 class="modal-title">รายละเอียดการชำระ</h4>
                                                    <button type="button" class="close" data-dismiss="modal">×</button>
                                                </div>
                                                <form method="post" id="recordForm">
                                                    <div class="modal-body">
                                                        <div class="form-group row">
                                                            <div class="col-sm-6">
                                                                <label>เลขที่เอกสาร</label>
                                                                <input type="text" class="form-control" id="doc_id"
                                                                       name="doc_id" readonly>
                                                            </div>
                                                            <div class="col-sm-6">
                                                                <label>วันที่เอกสาร</label>
                                                                <input type="text" class="form-control"
                                                                       id="payment_date" name="payment_date" readonly>
                                                            </div>
                                                        </div>
                                                        <div class="form-group row">
                                                            <div class="col-sm-6">
                                                                <label>บ้านเลขที่</label>
                                                                <input type="text" class="form-control"
                                                                       id="house_number" name="house_number" readonly>
                                                            </div>
                                                            <div class="col-sm-6">
                                                                <label>ผู้ชำระ</label>
                                                                <input type="text" class="form-control" id="detail"
                                                                       name="detail" readonly>
                                                            </div>
                                                        </div>
                                                        <div class="form-group row">
                                                            <div class="col-sm-4">
                                                                <label>เดือนเริ่มต้น</label>
                                                                <input type="text" class="form-control"
                                                                       id="month_name_start" name="month_name_start"
                                                                       readonly>
                                                            </div>
                                                            <div class="col-sm-4">
                                                                <label>ถึงงวดเดือน</label>
                                                                <input type="text" class="form-control"
                                                                       id="month_name_to" name="month_name_to" readonly>
                                                            </div>
                                                            <div class="col-sm-4">
                                                                <label>ปี</label>
                                                                <input type="text" class="form-control" id="period_year"
                                                                       name="period_year" readonly>
                                                            </div>
                                                        </div>
                                                        <div class="form-group row">
                                                            <div class="col-sm-6">
                                                                <label>จำนวนเงินโอน</label>
                                                                <input type="text" class="form-control" id="amount"
                                                                       name="amount" readonly>
                                                            </div>
                                                            <div class="col-sm-6">
                                                                <label>สถานะ</label>
                                                                <input type="text" class="form-control"
                                                                       id="payment_status_desc"
                                                                       name="payment_status_desc" readonly>
                                                            </div>
                                                        </div>
                                                        <div class="form-group row">
                                                            <div class="col-sm-6 text-center">
                                                                <img id="preview_image" src="#" alt="Preview"
                                                                     style="display:none; max-width:100%; height:auto; margin-top:10px;"
                                                                     onclick="openImageInNewWindow(this.src)"/>
                                                            </div>
                                                            <div class="col-sm-6">
                                                                <label>สถานะการอนุมัติ</label><br>
                                                                <input type="radio" id="approved" name="payment_status"
                                                                       value="Y">
                                                                <label for="approved" class="btn btn-success">ยืนยันการชำระ</label>
                                                                <input type="radio" id="rejected" name="payment_status"
                                                                       value="N">
                                                                <label for="rejected" class="btn btn-danger">ยังไม่ยืนยัน</label>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="modal-footer">
                                                        <input type="hidden" name="id" id="id"/>
                                                        <input type="hidden" name="action" id="action" value=""/>
                                                        <button type="button" class="btn btn-danger"
                                                                data-dismiss="modal">Close
                                                        </button>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Modal แสดง Slip -->
                                    <div class="modal fade" id="slipModal" tabindex="-1" role="dialog"
                                         aria-labelledby="slipModalLabel" aria-hidden="true">
                                        <div class="modal-dialog modal-dialog-centered" role="document">
                                            <div class="modal-content text-center">
                                                <div class="modal-header">
                                                    <h5 class="modal-title" id="slipModalLabel">หลักฐานการโอนเงิน</h5>
                                                    <button type="button" class="close" data-dismiss="modal"><span>&times;</span>
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
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                </section>
                            </div>
                        </div>
                    </div>
                </div>
            </div> <!-- /container-wrapper -->
        </div> <!-- /content -->
    </div> <!-- /content-wrapper -->
</div> <!-- /wrapper -->


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
    /* สีพาสเทลส้ม */
    :root {
        --pastel-orange: #ffb47d;
        --pastel-orange-dark: #e6995c;
        --text-color: #4a3c2b;
        --background-light: #fff7f0;
        --button-success: #a2d5ac;
        --button-danger: #f7a1a1;
    }

    body {
        font-family: "Segoe UI", Tahoma, Geneva, Verdana, sans-serif;
        margin: 0;
        background-color: var(--background-light);
        color: var(--text-color);
    }

    #wrapper {
        display: flex;
        flex-direction: column;
        min-height: 100vh;
        background-color: white;
        max-width: 1200px;
        margin: 20px auto;
        border-radius: 8px;
        box-shadow: 0 2px 10px rgba(255, 180, 125, 0.25);
        padding: 20px;
    }

    #content-wrapper {
        display: flex;
        flex-direction: column;
        flex-grow: 1;
    }

    #content {
        flex-grow: 1;
    }

    .container-fluid {
        padding: 0 15px;
    }

    /* Header */
    .d-sm-flex {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 1rem;
        flex-wrap: wrap;
    }

    h1.h5 {
        color: var(--pastel-orange-dark);
        font-weight: 700;
        margin: 0;
    }

    ol.breadcrumb {
        list-style: none;
        padding-left: 0;
        margin: 0;
    }

    .text-sm {
        font-size: 0.9rem;
        color: var(--pastel-orange-dark);
    }

    /* Card */
    .card {
        background: white;
        border: 1px solid var(--pastel-orange);
        border-radius: 8px;
        box-shadow: 0 4px 8px rgba(255, 180, 125, 0.1);
        margin-bottom: 1rem;
    }

    .card-header {
        border-bottom: 1px solid var(--pastel-orange);
        padding: 1rem 1.5rem;
        background-color: var(--pastel-orange);
        color: white;
        font-weight: 600;
        border-radius: 8px 8px 0 0;
    }

    .card-body {
        padding: 1rem 1.5rem;
        color: var(--text-color);
    }

    /* Table */
    table.dataTable {
        width: 100%;
        border-collapse: collapse;
        margin-top: 10px;
    }

    table.dataTable th,
    table.dataTable td {
        border: 1px solid var(--pastel-orange);
        padding: 8px 12px;
        text-align: center;
        font-size: 0.9rem;
        color: var(--text-color);
    }

    table.dataTable thead th {
        background-color: var(--pastel-orange);
        color: white;
        font-weight: 600;
    }

    table.dataTable tfoot th {
        background-color: var(--pastel-orange);
        color: white;
        font-weight: 600;
    }

    /* Form inputs */
    .form-control {
        width: 100%;
        padding: 8px 10px;
        border: 1px solid var(--pastel-orange);
        border-radius: 4px;
        font-size: 0.9rem;
        color: var(--text-color);
        background-color: #fff7f0;
    }

    .form-group {
        margin-bottom: 1rem;
    }

    label.control-label {
        font-weight: 600;
        margin-bottom: 0.3rem;
        display: block;
        color: var(--pastel-orange-dark);
    }

    .row {
        display: flex;
        flex-wrap: wrap;
        margin-left: -10px;
        margin-right: -10px;
    }

    .col-sm-4,
    .col-sm-6,
    .col-md-12 {
        padding-left: 10px;
        padding-right: 10px;
    }

    .col-sm-4 {
        width: 33.3333%;
    }

    .col-sm-6 {
        width: 50%;
    }

    .col-md-12 {
        width: 100%;
    }

    /* Buttons */
    .btn {
        cursor: pointer;
        padding: 8px 16px;
        border: none;
        border-radius: 5px;
        font-weight: 600;
        font-size: 0.9rem;
        transition: background-color 0.3s ease;
        margin-right: 8px;
        display: inline-block;
        text-align: center;
    }

    .btn-success {
        background-color: var(--button-success);
        color: #2e662d;
    }

    .btn-success:hover {
        background-color: #82c884;
    }

    .btn-danger {
        background-color: var(--button-danger);
        color: #7a2323;
    }

    .btn-danger:hover {
        background-color: #f08a8a;
    }

    .btn-secondary {
        background-color: #aaa;
        color: white;
    }

    .btn-secondary:hover {
        background-color: #888;
    }

    /* Modal */
    .modal-content {
        border-radius: 8px;
        padding: 1rem;
        border: 1px solid var(--pastel-orange);
        box-shadow: 0 6px 15px rgba(255, 180, 125, 0.3);
        background-color: white;
        color: var(--text-color);
    }

    .modal-header {
        border-bottom: 1px solid var(--pastel-orange);
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding-bottom: 0.5rem;
    }

    .modal-header h4,
    .modal-header h5 {
        margin: 0;
        color: var(--pastel-orange-dark);
    }

    .modal-body {
        padding-top: 10px;
        padding-bottom: 10px;
    }

    .close {
        font-size: 1.5rem;
        font-weight: bold;
        color: var(--pastel-orange-dark);
        cursor: pointer;
        background: transparent;
        border: none;
    }

    /* Image preview */
    .zoom-container img {
        max-width: 200px;
        border-radius: 6px;
        box-shadow: 0 2px 8px rgba(255, 180, 125, 0.5);
        cursor: pointer;
        transition: transform 0.3s ease;
    }

    .zoom-container img:hover {
        transform: scale(1.05);
    }

    /* Responsive */
    @media (max-width: 768px) {
        .col-sm-4, .col-sm-6 {
            width: 100%;
        }

        .d-sm-flex {
            flex-direction: column;
            align-items: flex-start;
        }
    }
</style>


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

    liff.init({liffId: LIFF_ID})
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
                        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
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
                                    `${data.house_number} | ${data.f_name} ${data.l_name}`;

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
            'lengthMenu': [[3, 5, 10, 20, 50, 100], [3, 5, 10, 20, 50, 100]],
            'language': {
                search: 'ค้นหา', lengthMenu: 'เลือกแสดง _MENU_ รายการ',
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
            "searching": false,
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


</body>
</html>