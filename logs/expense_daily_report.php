<?php
include('includes/Header.php');
if (strlen($_SESSION['alogin']) == "") {
    header("Location: index");
    exit();
} else {
    include("config/connect_db.php");

    // กำหนด Timezone ให้ถูกต้อง
    date_default_timezone_set('Asia/Bangkok');

    // ดึงวันที่ปัจจุบันและจัดรูปแบบเป็น DD-MM-YYYY
    $current_date = date('d-m-Y');

    ?>
    <!DOCTYPE html>
    <html lang="th">
    <head>
        <meta charset="UTF-8">
        <title>Export รายงานค่าใช้จ่าย</title>
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.9.0/css/bootstrap-datepicker.min.css">
        <style>
            /* ไม่จำเป็นต้องมี month-checkbox แล้ว แต่ถ้าอยากเก็บไว้ก็ทำได้ */
            /* .month-checkbox {
                margin-right: 15px;
                margin-bottom: 5px;
                display: inline-block;
            }

            .month-checkbox input {
                margin-right: 5px;
            } */
        </style>
    </head>
    <body id="page-top">
    <div id="wrapper">
        <?php include('includes/Side-Bar.php'); ?>
        <div id="content-wrapper" class="d-flex flex-column">
            <div id="content">
                <?php include('includes/Top-Bar.php'); ?>

                <div class="container-fluid" id="container-wrapper">
                    <div class="d-sm-flex align-items-center justify-content-between mb-4">
                        <h1 class="h4 mb-0 text-gray-800"><?php echo urldecode($_GET['s'] ?? 'รายงานค่าใช้จ่าย') ?></h1>
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="<?php echo $_SESSION['dashboard_page'] ?? '#' ?>">Home</a>
                            </li>
                            <li class="breadcrumb-item"><?php echo urldecode($_GET['m'] ?? 'รายงาน') ?></li>
                            <li class="breadcrumb-item active"><?php echo urldecode($_GET['s'] ?? 'ค่าใช้จ่าย') ?></li>
                        </ol>
                    </div>

                    <div class="row">
                        <div class="col-lg-12">
                            <div class="card mb-12">
                                <div class="card-body">
                                    <form id="form_data" method="post"
                                          action=""
                                          data-excel-action="export_process/export_expense_daily_report_process.php"
                                          data-pdf-action="export_process/expense_daily_report_pdf.php"
                                          enctype="multipart/form-data">
                                        <div class="row">
                                            <div class="col-sm-12">
                                                <div class="form-group">
                                                    <label for="start_date">เริ่มต้นวันที่ :</label>
                                                    <div class="input-group date" id="start_date_group">
                                                        <input type="text" class="form-control" name="start_date" id="start_date" required autocomplete="off" value="<?php echo $current_date; ?>">
                                                        <div class="input-group-append">
                                                            <span class="input-group-text"><i class="fas fa-calendar"></i></span>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="form-group">
                                                    <label for="end_date">ถึงวันที่ :</label>
                                                    <div class="input-group date" id="end_date_group">
                                                        <input type="text" class="form-control" name="end_date" id="end_date" required autocomplete="off" value="<?php echo $current_date; ?>">
                                                        <div class="input-group-append">
                                                            <span class="input-group-text"><i class="fas fa-calendar"></i></span>
                                                        </div>
                                                    </div>
                                                </div>

                                                <br>
                                                <div class="row">
                                                    <div class="col-sm-12">
                                                        <button type="button" class="btn btn-success" id="btnExportExcel">
                                                            Export Excel <i class="fa fa-file-excel"></i>
                                                        </button>
                                                        <button type="button" class="btn btn-primary" id="btnPrintPdf">
                                                            Print PDF <i class="fa fa-file-pdf"></i>
                                                        </button>
                                                    </div>
                                                </div>

                                            </div>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>
                <?php include('includes/Modal-Logout.php');
                include('includes/Footer.php'); ?>
            </div>
        </div>
    </div>

    <a class="scroll-to-top rounded" href="#page-top"><i class="fas fa-angle-up"></i></a>

    <script src="vendor/jquery/jquery.min.js"></script>
    <script src="vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
    <script src="vendor/jquery-easing/jquery.easing.min.js"></script>
    <script src="vendor/select2/dist/js/select2.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.9.0/js/bootstrap-datepicker.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.9.0/locales/bootstrap-datepicker.th.min.js"></script>
    <script src="vendor/bootstrap-touchspin/js/jquery.bootstrap-touchspin.js"></script>
    <script src="vendor/clock-picker/clockpicker.js"></script>
    <script src="js/myadmin.min.js"></script>
    <script src="js/MyFrameWork/framework_util.js"></script>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            // Initialise Datepickers
            $('#start_date_group, #end_date_group').datepicker({
                format: 'dd-mm-yyyy', // รูปแบบวันที่ที่แสดง
                language: 'th',      // ใช้ภาษาไทย
                todayHighlight: true, // เน้นวันที่ปัจจุบัน
                autoclose: true      // ปิด Datepicker เมื่อเลือกวันที่
            });

            // ไม่จำเป็นต้อง set value ผ่าน JS อีก เพราะตั้งค่าใน PHP แล้ว
            // const startDateInput = document.getElementById('start_date');
            // const endDateInput = document.getElementById('end_date');
            // const today = new Date();
            // const day = String(today.getDate()).padStart(2, '0');
            // const month = String(today.getMonth() + 1).padStart(2, '0'); // Month is 0-indexed
            // const year = today.getFullYear();
            // const formattedDate = `${day}-${month}-${year}`;
            // startDateInput.value = formattedDate;
            // endDateInput.value = formattedDate;


            const form = document.getElementById('form_data');
            const btnExportExcel = document.getElementById('btnExportExcel');
            const btnPrintPdf = document.getElementById('btnPrintPdf');
            const startDateInput = document.getElementById('start_date'); // ยังคงใช้อ้างอิงสำหรับ validation
            const endDateInput = document.getElementById('end_date');     // ยังคงใช้อ้างอิงสำหรับ validation


            function validateDatesSelected() {
                if (!startDateInput.value || !endDateInput.value) {
                    alert('กรุณาเลือก "เริ่มต้นวันที่" และ "ถึงวันที่"');
                    return false;
                }
                // Optional: Add logic to ensure start_date <= end_date
                // ตรวจสอบว่า `$.fn.datepicker.dates['th'].parse` ถูกโหลดมาแล้ว
                if (typeof $.fn.datepicker.dates === 'undefined' || typeof $.fn.datepicker.dates['th'] === 'undefined' || typeof $.fn.datepicker.dates['th'].parse === 'undefined') {
                    console.warn("datepicker locale 'th' parse function not available. Date validation might not be accurate.");
                    // Fallback to simpler date parsing if locale parse is not available
                    const parseDate = (dateStr) => {
                        const parts = dateStr.split('-');
                        return new Date(parts[2], parts[1] - 1, parts[0]);
                    };
                    const startDate = parseDate(startDateInput.value);
                    const endDate = parseDate(endDateInput.value);

                    if (startDate > endDate) {
                        alert('วันที่เริ่มต้นต้องไม่มากกว่าวันที่สิ้นสุด');
                        return false;
                    }

                } else {
                    const startDate = $.fn.datepicker.dates['th'].parse(startDateInput.value);
                    const endDate = $.fn.datepicker.dates['th'].parse(endDateInput.value);

                    if (startDate > endDate) {
                        alert('วันที่เริ่มต้นต้องไม่มากกว่าวันที่สิ้นสุด');
                        return false;
                    }
                }
                return true;
            }

            // Event listener สำหรับปุ่ม Export Excel
            btnExportExcel.addEventListener('click', function (e) {
                e.preventDefault(); // ป้องกันการ submit form โดยตรง
                if (validateDatesSelected()) {
                    form.action = form.dataset.excelAction; // กำหนด action เป็น URL สำหรับ Export Excel
                    form.submit(); // Submit form
                }
            });

            // Event listener สำหรับปุ่ม Print PDF
            btnPrintPdf.addEventListener('click', function (e) {
                e.preventDefault(); // ป้องกันการ submit form โดยตรง
                if (validateDatesSelected()) {
                    form.action = form.dataset.pdfAction; // กำหนด action เป็น URL สำหรับ Print PDF
                    form.target = "_blank"; // เปิดในแท็บใหม่สำหรับ PDF
                    form.submit(); // Submit form
                    form.target = ""; // รีเซ็ต target กลับเป็นค่าเริ่มต้น เพื่อไม่ให้กระทบกับการ submit ครั้งถัดไป
                }
            });

        });
    </script>

    </body>
    </html>
<?php } ?>