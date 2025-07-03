<?php
include('includes/Header.php');
if (strlen($_SESSION['alogin']) == "") {
    header("Location: index");
    exit();
} else {
    include("config/connect_db.php");

    $sql_alley = "SELECT DISTINCT(alley) AS alley FROM ims_house_master ORDER BY CAST(alley AS UNSIGNED) ";
    $stmt_alley = $conn->prepare($sql_alley);
    $stmt_alley->execute();
    $AlleyRecords = $stmt_alley->fetchAll();

    // กำหนด Timezone ให้ถูกต้อง
    date_default_timezone_set('Asia/Bangkok');

    // ดึงวันที่ปัจจุบันและจัดรูปแบบเป็น DD-MM-YYYY
    $current_date = date('d-m-Y');

    ?>
    <!DOCTYPE html>
    <html lang="th">
    <head>
        <meta charset="UTF-8">
        <title>รายละเอียดบ้าน</title>
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
                                          data-excel-action="export_process/house_master_report_process.php"
                                          data-pdf-action="export_process/house_master_report_pdf.php" enctype="multipart/form-data">
                                        <div class="row">
                                            <div class="col-sm-12">
                                                <div class="form-group">
                                                    <label for="alley_start">หมายเลขซอย เริ่มต้น :</label>
                                                    <select name="alley_start" id="alley_start" class="form-control" required>
                                                        <?php foreach ($AlleyRecords as $row) { ?>
                                                            <option value="<?php echo $row["alley"]; ?>">
                                                                <?php echo $row["alley"]; ?>
                                                            </option>
                                                        <?php } ?>
                                                    </select>
                                                </div>
                                                <div class="form-group">
                                                    <label for="alley_to">หมายเลขซอย ถึง :</label>
                                                    <select name="alley_to" id="alley_to" class="form-control" required>
                                                        <?php foreach ($AlleyRecords as $row) { ?>
                                                            <option value="<?php echo $row["alley"]; ?>">
                                                                <?php echo $row["alley"]; ?>
                                                            </option>
                                                        <?php } ?>
                                                    </select>
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
            // ลบการ Initialise Datepickers ที่ไม่เกี่ยวข้องกับฟอร์มนี้
            // $('#start_date_group, #end_date_group').datepicker({
            //     format: 'dd-mm-yyyy',
            //     language: 'th',
            //     todayHighlight: true,
            //     autoclose: true
            // });

            const form = document.getElementById('form_data');
            const btnExportExcel = document.getElementById('btnExportExcel');
            const btnPrintPdf = document.getElementById('btnPrintPdf');

            // ลบฟังก์ชัน validateDatesSelected() ที่ไม่เกี่ยวข้องกับฟอร์มนี้
            // function validateDatesSelected() { ... }

            // Event listener สำหรับปุ่ม Export Excel
            btnExportExcel.addEventListener('click', function (e) {
                e.preventDefault(); // ป้องกันการ submit form โดยตรง
                // ไม่ต้องมีการ validate dates แล้ว ใช้ required attribute บน select แทน
                form.action = form.dataset.excelAction; // กำหนด action เป็น URL สำหรับ Export Excel
                form.submit(); // Submit form
            });

            // Event listener สำหรับปุ่ม Print PDF
            btnPrintPdf.addEventListener('click', function (e) {
                e.preventDefault(); // ป้องกันการ submit form โดยตรง
                // ไม่ต้องมีการ validate dates แล้ว ใช้ required attribute บน select แทน
                form.action = form.dataset.pdfAction; // กำหนด action เป็น URL สำหรับ Print PDF
                form.target = "_blank"; // เปิดในแท็บใหม่สำหรับ PDF
                form.submit(); // Submit form
                form.target = ""; // รีเซ็ต target กลับเป็นค่าเริ่มต้น เพื่อไม่ให้กระทบกับการ submit ครั้งถัดไป
            });

        });
    </script>

    </body>
    </html>
<?php } ?>