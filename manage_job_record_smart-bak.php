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
                    <h4 class="h5 mb-0 text-gray-800">รายงาน/เหตุการณ์</h4>
                    <div class="text-sm text-muted" id="user-info-liff"></div>
                </div>

                <div class="row">
                    <div class="col-lg-12">
                        <div class="card mb-12">
                            <div class="card-body">
                                <section class="container-fluid">
                                    <div class="col-md-12 col-md-offset-2">
                                        <table id='TableRecordList' class='display dataTable'>
                                            <thead>
                                            <tr>
                                                <th>ชื่อ Line</th>
                                                <th>ชื่อผู้รายงาน</th>
                                                <th>วันที่-เวลา</th>
                                                <th>รายละเอียด</th>
                                                <th>Action</th>
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
    :root {
        --pastel-blue: #d0ecff;
        --pastel-blue-dark: #78b9de;
        --text-color: #2f4f61;
        --background-light: #f5fbff;
        --button-success: #bde7f3;
        --button-danger: #ffb8c6;
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
        box-shadow: 0 2px 10px rgba(168, 211, 240, 0.25);
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

    .d-sm-flex {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 1rem;
        flex-wrap: wrap;
    }

    h1.h5 {
        color: var(--pastel-blue-dark);
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
        color: var(--pastel-blue-dark);
    }

    .card {
        background: white;
        border: 1px solid var(--pastel-blue);
        border-radius: 8px;
        box-shadow: 0 4px 8px rgba(168, 211, 240, 0.15);
        margin-bottom: 1rem;
    }

    .card-header {
        border-bottom: 1px solid var(--pastel-blue);
        padding: 1rem 1.5rem;
        background-color: var(--pastel-blue);
        color: black; /* เปลี่ยนจาก white เป็น black */
        font-weight: 600;
        border-radius: 8px 8px 0 0;
    }

    .card-body {
        padding: 1rem 1.5rem;
        color: var(--text-color);
    }

    table.dataTable {
        width: 100%;
        border-collapse: collapse;
        margin-top: 10px;
    }

    table.dataTable th,
    table.dataTable td {
        border: 1px solid var(--pastel-blue);
        padding: 8px 12px;
        text-align: center;
        font-size: 0.9rem;
        color: var(--text-color);
    }

    table.dataTable thead th,
    table.dataTable tfoot th {
        background-color: var(--pastel-blue);
        color: white;
        font-weight: 600;
    }

    .form-control {
        width: 100%;
        padding: 8px 10px;
        border: 1px solid var(--pastel-blue);
        border-radius: 4px;
        font-size: 0.9rem;
        color: var(--text-color);
        background-color: #f5fbff;
    }

    .form-group {
        margin-bottom: 1rem;
    }

    label.control-label {
        font-weight: 600;
        margin-bottom: 0.3rem;
        display: block;
        color: var(--pastel-blue-dark);
    }

    .row {
        display: flex;
        flex-wrap: wrap;
        margin-left: -10px;
        margin-right: -10px;
    }

    .col-sm-4, .col-sm-6, .col-md-12 {
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
        color: #236a7f;
    }

    .btn-success:hover {
        background-color: #a5d8ea;
    }

    .btn-danger {
        background-color: var(--button-danger);
        color: #7f2a3a;
    }

    .btn-danger:hover {
        background-color: #fba1b4;
    }

    .btn-secondary {
        background-color: #aaa;
        color: white;
    }

    .btn-secondary:hover {
        background-color: #888;
    }

    .modal-content {
        border-radius: 8px;
        padding: 1rem;
        border: 1px solid var(--pastel-blue);
        box-shadow: 0 6px 15px rgba(168, 211, 240, 0.3);
        background-color: white;
        color: var(--text-color);
    }

    .modal-header {
        border-bottom: 1px solid var(--pastel-blue);
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding-bottom: 0.5rem;
    }

    .modal-header h4, .modal-header h5 {
        margin: 0;
        color: var(--pastel-blue-dark);
    }

    .modal-body {
        padding-top: 10px;
        padding-bottom: 10px;
    }

    .close {
        font-size: 1.5rem;
        font-weight: bold;
        color: var(--pastel-blue-dark);
        cursor: pointer;
        background: transparent;
        border: none;
    }

    .zoom-container img {
        max-width: 200px;
        border-radius: 6px;
        box-shadow: 0 2px 8px rgba(168, 211, 240, 0.5);
        cursor: pointer;
        transition: transform 0.3s ease;
    }

    .zoom-container img:hover {
        transform: scale(1.05);
    }

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
        $('#doc_date').datepicker({
            format: "dd-mm-yyyy",
            todayHighlight: true,
            language: "th",
            autoclose: true
        });
    });
</script>

<script>
    $(document).ready(function () {
        loadDataTable();
    });
</script>

<script>
    const deviceType = "<?= $_SESSION['deviceType'] ?? 'computer' ?>";

    function loadDataTable() {
        const formData = {
            action: "GET_JOB",
            sub_action: "GET_MASTER"
        };

        $('#TableRecordList').DataTable({
            destroy: true,
            lengthMenu: [[5, 10, 20, 50, 100], [5, 10, 20, 50, 100]],
            language: {
                search: 'ค้นหา',
                lengthMenu: 'แสดง _MENU_ รายการ',
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
            processing: true,
            serverSide: true,
            serverMethod: 'post',
            scrollX: deviceType !== 'computer',
            ajax: {
                url: 'model/manage_job_record_smart_process.php',
                type: 'POST',
                data: function (d) {
                    d.action = "GET_JOB";
                    d.sub_action = "GET_MASTER";
                },
                error: function (xhr, error, thrown) {
                    console.error("DataTable AJAX error:", xhr.responseText);
                }
            },
            columns: [
                {data: 'display_name'},
                {data: 'emp_name'},
                {
                    data: 'line_picture_profile',
                    render: function (data) {
                        const imageUrl = data || 'img/icon/none_img.png';
                        return `<img src="${imageUrl}" alt="image" style="width: 50px; height: auto;">`;
                    }
                },
                {data: 'checkin_time'},
                {data: 'remark'}
            ]
        });

        $('#TableRecordList').on('processing.dt', function (e, settings, processing) {
            $('#loading-spinner').toggle(processing);
        });
    }
</script>


</body>
</html>