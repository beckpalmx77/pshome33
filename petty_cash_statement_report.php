<?php
include('includes/Header.php');
if (strlen($_SESSION['alogin']) == "") {
    header("Location: index");
    exit();
} else {
    include("config/connect_db.php");

    // กำหนด Timezone ให้ถูกต้อง
    date_default_timezone_set('Asia/Bangkok');

    // ดึงวันแรกของเดือนปัจจุบันและวันที่ปัจจุบัน จัดรูปแบบเป็น DD-MM-YYYY
    $start_date = date('01-m-Y');
    $end_date = date('d-m-Y');

    ?>
    <!DOCTYPE html>
    <html lang="th">
    <head>
        <meta charset="UTF-8">
        <title>รายงานเคลื่อนไหวเงินสดย่อย (Petty Cash Statement)</title>
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.9.0/css/bootstrap-datepicker.min.css">
    </head>
    <body id="page-top">
    <div id="wrapper">
        <?php include('includes/Side-Bar.php'); ?>
        <div id="content-wrapper" class="d-flex flex-column">
            <div id="content">
                <?php include('includes/Top-Bar.php'); ?>

                <div class="container-fluid" id="container-wrapper">
                    <div class="d-sm-flex align-items-center justify-content-between mb-4">
                        <h1 class="h4 mb-0 text-gray-800"><?php echo urldecode($_GET['s'] ?? 'รายงานเคลื่อนไหวเงินสดย่อย') ?></h1>
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="<?php echo $_SESSION['dashboard_page'] ?? '#' ?>">Home</a>
                            </li>
                            <li class="breadcrumb-item"><?php echo urldecode($_GET['m'] ?? 'รายงาน') ?></li>
                            <li class="breadcrumb-item active"><?php echo urldecode($_GET['s'] ?? 'เคลื่อนไหวเงินสดย่อย') ?></li>
                        </ol>
                    </div>

                    <div class="row">
                        <div class="col-lg-12">
                            <div class="card mb-12">
                                <div class="card-body">
                                    <form id="form_data" method="post"
                                          action=""
                                          enctype="multipart/form-data">
                                        <div class="row">
                                            <div class="col-sm-12">
                                                <div class="form-group">
                                                    <label for="start_date">เริ่มต้นวันที่ :</label>
                                                    <div class="input-group date" id="start_date_group">
                                                        <input type="text" class="form-control" name="start_date" id="start_date" required autocomplete="off" value="<?php echo $start_date; ?>">
                                                        <div class="input-group-append">
                                                            <span class="input-group-text"><i class="fas fa-calendar"></i></span>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="form-group">
                                                    <label for="end_date">ถึงวันที่ :</label>
                                                    <div class="input-group date" id="end_date_group">
                                                        <input type="text" class="form-control" name="end_date" id="end_date" required autocomplete="off" value="<?php echo $end_date; ?>">
                                                        <div class="input-group-append">
                                                            <span class="input-group-text"><i class="fas fa-calendar"></i></span>
                                                        </div>
                                                    </div>
                                                </div>

                                                <br>
                                                <div class="row">
                                                    <div class="col-sm-12">
                                                        <button type="button" class="btn btn-primary" id="btnShowData">
                                                            แสดงข้อมูล <i class="fa fa-eye"></i>
                                                        </button>
                                                        <button type="button" class="btn btn-success" id="btnExportExcel">
                                                            Export Excel <i class="fa fa-file-excel"></i>
                                                        </button>
                                                        <button type="button" class="btn btn-danger" id="btnPrintPdf">
                                                            Print PDF <i class="fa fa-file-pdf"></i>
                                                        </button>
                                                        <a href="how_to_cash_flow.html" target="_blank" class="btn btn-info">
                                                            คู่มือเงินสดย่อย <i class="fa fa-book"></i>
                                                        </a>
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

            const form = document.getElementById('form_data');
            const btnShowData = document.getElementById('btnShowData');
            const btnPrintPdf = document.getElementById('btnPrintPdf');
            const btnExportExcel = document.getElementById('btnExportExcel');
            const startDateInput = document.getElementById('start_date'); // ยังคงใช้อ้างอิงสำหรับ validation
            const endDateInput = document.getElementById('end_date');     // ยังคงใช้อ้างอิงสำหรับ validation

            function validateDatesSelected() {
                if (!startDateInput.value || !endDateInput.value) {
                    alert('กรุณาเลือก "เริ่มต้นวันที่" และ "ถึงวันที่"');
                    return false;
                }
                if (typeof $.fn.datepicker.dates === 'undefined' || typeof $.fn.datepicker.dates['th'] === 'undefined' || typeof $.fn.datepicker.dates['th'].parse === 'undefined') {
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

            // Event listener สำหรับปุ่ม แสดงข้อมูล
            btnShowData.addEventListener('click', function (e) {
                e.preventDefault();
                if (validateDatesSelected()) {
                    form.action = 'show_petty_cash_statement'; // ลิงก์ไปยังหน้าแสดงข้อมูล
                    form.target = "_blank";
                    form.submit();
                }
            });

            // Event listener สำหรับปุ่ม Export Excel
            btnExportExcel.addEventListener('click', function (e) {
                e.preventDefault();
                if (validateDatesSelected()) {
                    form.action = 'export_process/petty_cash_statement_excel.php'; // ลิงก์ไปยังหน้า Excel
                    form.target = "_blank";
                    form.submit();
                }
            });

            // Event listener สำหรับปุ่ม Print PDF
            btnPrintPdf.addEventListener('click', function (e) {
                e.preventDefault();
                if (validateDatesSelected()) {
                    form.action = 'export_process/petty_cash_statement_pdf.php'; // ลิงก์ไปยังหน้า PDF
                    form.target = "_blank";
                    form.submit();
                }
            });

        });
    </script>

    </body>
    </html>
<?php } ?>
