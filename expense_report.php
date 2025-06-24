<?php
include('includes/Header.php');
if (strlen($_SESSION['alogin']) == "") {
    header("Location: index");
    exit();
} else {
    include("config/connect_db.php");

    $month_num = ltrim(date('m'), '0'); // Remove leading zero
    $sql_curr_month = "SELECT * FROM ims_month WHERE month = ?";
    $stmt_curr_month = $conn->prepare($sql_curr_month);
    $stmt_curr_month->execute([$month_num]);
    $MonthCurr = $stmt_curr_month->fetchAll();
    foreach ($MonthCurr as $row_curr) {
        $month_name = $row_curr["month_name"];
    }

    $sql_month = "SELECT * FROM ims_month ORDER BY month ASC";
    $stmt_month = $conn->prepare($sql_month);
    $stmt_month->execute();
    $MonthRecords = $stmt_month->fetchAll();

    // สมมติว่า v_ims_expenses มีคอลัมน์ exp_year
    $sql_year = "SELECT DISTINCT(exp_year) AS period_year FROM v_ims_expenses WHERE exp_year >= 2024 ORDER BY exp_year DESC";
    $stmt_year = $conn->prepare($sql_year);
    $stmt_year->execute();
    $YearRecords = $stmt_year->fetchAll();
    ?>
    <!DOCTYPE html>
    <html lang="th">
    <head>
        <meta charset="UTF-8">
        <title>Export รายงานค่าใช้จ่าย</title>
        <style>
            /* เพิ่มเล็กน้อยสำหรับ checkbox ให้ชิดกันสวยงาม */
            .month-checkbox {
                margin-right: 15px;
                margin-bottom: 5px;
                display: inline-block;
            }

            .month-checkbox input {
                margin-right: 5px;
            }
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
                                          data-excel-action="export_process/export_expense_report_process.php"
                                          data-pdf-action="export_process/expense_report_pdf.php"
                                          enctype="multipart/form-data">
                                        <div class="row">
                                            <div class="col-sm-12">
                                                <label>เลือกเดือน :</label><br>
                                                <div>
                                                    <label class="month-checkbox">
                                                        <input type="checkbox" name="months[]" value="all"
                                                               id="check_all">
                                                        ทั้งหมด
                                                    </label>
                                                    <?php foreach ($MonthRecords as $row) {
                                                        // กำหนดให้เดือนปัจจุบันถูกติ๊กไว้โดย default
                                                        $checked = ((int)$row["month_id"] == (int)$month_num) ? 'checked' : '';
                                                        ?>
                                                        <label class="month-checkbox">
                                                            <input type="checkbox" name="months[]"
                                                                   value="<?php echo $row["month_id"]; ?>"
                                                                   class="month-checkbox-item" <?php echo $checked; ?>>
                                                            <?php echo $row["month_name"]; ?>
                                                        </label>
                                                    <?php } ?>
                                                </div>

                                                <br>
                                                <label for="year">เลือกปี :</label>
                                                <select name="year" id="year" class="form-control" required>
                                                    <?php foreach ($YearRecords as $row) { ?>
                                                        <option value="<?php echo $row["period_year"]; ?>">
                                                            <?php echo $row["period_year"]; ?>
                                                        </option>
                                                    <?php } ?>
                                                </select>
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
    <script src="vendor/bootstrap-datepicker/js/bootstrap-datepicker.min.js"></script>
    <script src="vendor/bootstrap-touchspin/js/jquery.bootstrap-touchspin.js"></script>
    <script src="vendor/clock-picker/clockpicker.js"></script>
    <script src="js/myadmin.min.js"></script>
    <script src="vendor/date-picker-1.9/js/bootstrap-datepicker.js"></script>
    <script src="vendor/date-picker-1.9/locales/bootstrap-datepicker.th.min.js"></script>
    <link href="vendor/date-picker-1.9/css/bootstrap-datepicker.css" rel="stylesheet"/>
    <script src="js/MyFrameWork/framework_util.js"></script>

    <script>
        // จัดการ checkbox "ทั้งหมด" กับ checkbox เดือนอื่นๆ
        document.addEventListener('DOMContentLoaded', function () {
            const checkAll = document.getElementById('check_all');
            const monthCheckboxes = document.querySelectorAll('.month-checkbox-item');

            // ถ้าติ๊ก "ทั้งหมด" ให้ติ๊ก/ไม่ติ๊ก checkbox เดือนอื่นทั้งหมดด้วย
            checkAll.addEventListener('change', function () {
                monthCheckboxes.forEach(cb => {
                    cb.checked = checkAll.checked;
                });
            });

            // ถ้า checkbox เดือนอื่นๆ มีการติ๊ก/ไม่ติ๊ก ให้จัดการสถานะ "ทั้งหมด"
            monthCheckboxes.forEach(cb => {
                cb.addEventListener('change', function () {
                    if (!this.checked) {
                        checkAll.checked = false;
                    } else {
                        // ถ้า checkbox เดือนอื่นทุกตัวถูกติ๊ก ให้ติ๊ก "ทั้งหมด" ด้วย
                        const allChecked = Array.from(monthCheckboxes).every(cb => cb.checked);
                        checkAll.checked = allChecked;
                    }
                });
            });

            // ตั้งค่าเริ่มต้น ถ้า checkbox เดือนทั้งหมดถูกติ๊ก ให้ติ๊ก "ทั้งหมด" ด้วย
            const allCheckedInit = Array.from(monthCheckboxes).every(cb => cb.checked);
            checkAll.checked = allCheckedInit;


            // --- ส่วนของ JavaScript สำหรับจัดการปุ่ม Export และ Print PDF ---
            const form = document.getElementById('form_data');
            const btnExportExcel = document.getElementById('btnExportExcel');
            const btnPrintPdf = document.getElementById('btnPrintPdf');

            function validateMonthsSelected() {
                const selectedMonths = Array.from(monthCheckboxes).filter(cb => cb.checked && cb.value !== 'all');
                if (selectedMonths.length === 0 && !checkAll.checked) {
                    alert('กรุณาเลือกอย่างน้อย 1 เดือน หรือเลือก "ทั้งหมด"');
                    return false;
                }
                return true;
            }

            // Event listener สำหรับปุ่ม Export Excel
            btnExportExcel.addEventListener('click', function (e) {
                e.preventDefault(); // ป้องกันการ submit form โดยตรง
                if (validateMonthsSelected()) {
                    form.action = form.dataset.excelAction; // กำหนด action เป็น URL สำหรับ Export Excel
                    form.submit(); // Submit form
                }
            });

            // Event listener สำหรับปุ่ม Print PDF
            btnPrintPdf.addEventListener('click', function (e) {
                e.preventDefault(); // ป้องกันการ submit form โดยตรง
                if (validateMonthsSelected()) {
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