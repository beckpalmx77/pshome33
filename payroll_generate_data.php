<?php
// payroll_generate_view.php

// 1. โครงสร้างหลัก: Header และ Session Check
include('includes/Header.php');

// สมมติว่าไฟล์ index.php คือหน้า login
if (strlen($_SESSION['alogin']) == "") {
    header("Location: index.php");
    exit();
} else {
    // 2. การเชื่อมต่อฐานข้อมูลและการเตรียมข้อมูล
    include("config/connect_db.php"); // ไฟล์เชื่อมต่อฐานข้อมูล

    // 2.1 ดึงข้อมูลเดือน (สมมติว่ามีตาราง ims_month)
    $month_num = ltrim(date('m'), '0'); // เดือนปัจจุบัน (ไม่มี 0 นำหน้า)
    $sql_month = "SELECT * FROM ims_month ORDER BY month ASC";
    $stmt_month = $conn->prepare($sql_month);
    $stmt_month->execute();
    $MonthRecords = $stmt_month->fetchAll(PDO::FETCH_ASSOC);

    // 2.2 เตรียมข้อมูลปี
    $current_year = date('Y');
    $YearRecords = [];
    for ($y = $current_year + 1; $y >= $current_year - 5; $y--) { // แสดงปีล่วงหน้า 1 ปี และย้อนหลัง 5 ปี
        $YearRecords[] = ["period_year" => $y];
    }

    // กำหนดค่า default สำหรับ doc_date (วันที่จ่ายเงินเดือน) เป็นวันที่ปัจจุบัน
    $default_doc_date = date('d/m/Y');

    // กำหนดค่าสำหรับ Breadcrumb
    $main_menu_name = isset($_GET['m']) ? urldecode($_GET['m']) : 'Payroll';
    $sub_menu_name = isset($_GET['s']) ? urldecode($_GET['s']) : 'สร้างเงินเดือนอัตโนมัติ';
    $dashboard_page = isset($_SESSION['dashboard_page']) ? $_SESSION['dashboard_page'] : 'dashboard.php';
    ?>

    <!DOCTYPE html>
    <html lang="th">
    <head>
        <meta charset="UTF-8">
        <title><?php echo $sub_menu_name; ?></title>
        <link href="vendor/date-picker-1.9/css/bootstrap-datepicker.css" rel="stylesheet"/>
        <link href="vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet"/>
        <link href="vendor/datatables/dataTables.bootstrap4.min.css" rel="stylesheet">
        <style>
            /* CSS ที่กำหนดเอง */
            .month-radio {
                margin-right: 15px;
                margin-bottom: 5px;
                display: inline-block;
            }

            .month-radio input {
                margin-right: 5px;
            }

            /* Style สำหรับข้อความแจ้งเตือน */
            .alert-fixed {
                position: fixed;
                top: 20px;
                right: 20px;
                z-index: 1050;
                width: 300px;
                display: none; /* ซ่อนไว้ก่อน */
            }
        </style>
    </head>
    <body id="page-top">
    <div id="wrapper">
        <?php
        // 3. โครงสร้างหลัก: Side-Bar
        include('includes/Side-Bar.php');
        ?>

        <div id="content-wrapper" class="d-flex flex-column">
            <div id="content">
                <?php
                // 3. โครงสร้างหลัก: Top-Bar
                include('includes/Top-Bar.php');
                ?>

                <div class="container-fluid" id="container-wrapper">
                    <div class="d-sm-flex align-items-center justify-content-between mb-4">
                        <h1 class="h3 mb-0 text-gray-800"><?php echo $sub_menu_name; ?></h1>
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="<?php echo $dashboard_page; ?>">Home</a></li>
                            <li class="breadcrumb-item"><?php echo $main_menu_name; ?></li>
                            <li class="breadcrumb-item active"
                                aria-current="page"><?php echo $sub_menu_name; ?></li>
                        </ol>
                    </div>

                    <div id="alert_area" class="alert-fixed">
                    </div>

                    <div class="row">
                        <div class="col-lg-12">
                            <div class="card mb-4">
                                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                                    <h6 class="m-0 font-weight-bold text-primary">
                                        กำหนดเงื่อนไขการสร้างข้อมูลเงินเดือน</h6>
                                </div>
                                <div class="card-body">
                                    <form id="form_data" method="post">
                                        <input type="hidden" name="action" value="GENERATE_PAYROLL">

                                        <div class="row">
                                            <div class="col-sm-12">
                                                <div class="col-sm-3">
                                                    <label for="doc_date">📅 วันที่จ่ายเงินเดือน (Doc Date) :</label>
                                                    <div class="input-group date" id="date_doc_date">
                                                        <input type="text" class="form-control" name="doc_date"
                                                               id="doc_date"
                                                               value="<?php echo $default_doc_date; ?>" required>
                                                        <div class="input-group-append">
                                                            <span class="input-group-text"><i
                                                                        class="fas fa-calendar"></i></span>
                                                        </div>
                                                    </div>
                                                </div>
                                                <br>

                                                <label>เลือกเดือน (Payroll Month) :</label><br>
                                                <div>
                                                    <?php foreach ($MonthRecords as $row) {
                                                        $checked = ($row["month"] == $month_num) ? 'checked' : '';
                                                        ?>
                                                        <label class="month-radio">
                                                            <input type="radio" name="payroll_month"
                                                                   value="<?php echo $row["month"]; ?>"
                                                                   class="month-radio-item" <?php echo $checked; ?>
                                                                   required>
                                                            <?php echo $row["month_name"]; ?>
                                                        </label>
                                                    <?php } ?>
                                                </div>

                                                <br>
                                                <div class="col-sm-3">
                                                    <label for="payroll_year">เลือกปี (Payroll Year) :</label>
                                                    <select name="payroll_year" id="payroll_year" class="form-control"
                                                            required>
                                                        <?php foreach ($YearRecords as $row) {
                                                            $selected = ($row["period_year"] == $current_year) ? 'selected' : '';
                                                            ?>
                                                            <option value="<?php echo $row["period_year"]; ?>" <?php echo $selected; ?>>
                                                                <?php echo $row["period_year"]; ?>
                                                            </option>
                                                        <?php } ?>
                                                    </select>
                                                    <div>
                                                        <br>

                                                        <div class="row">
                                                            <div class="col-sm-12">
                                                                <button type="button" class="btn btn-primary"
                                                                        id="btnGenerate">
                                                                    สร้างข้อมูลเงินเดือนอัตโนมัติ (Generate) <i
                                                                            class="fas fa-magic"></i>
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
                    <div class="row mt-4">
                        <div class="col-lg-12">
                            <div class="card mb-4">
                                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                                    <h6 class="m-0 font-weight-bold text-primary">
                                        พนักงานทั้งหมดที่พร้อมสำหรับสร้างเงินเดือน</h6>
                                </div>
                                <div class="card-body">
                                    <div class="table-responsive">
                                        <table class="table table-bordered" id="employeeDataTable" width="100%"
                                               cellspacing="0">
                                            <thead>
                                            <tr>
                                                <th>รหัสพนักงาน</th>
                                                <th>ชื่อ-นามสกุล</th>
                                                <th>ตำแหน่ง</th>
                                                <th>ประเภทพนักงาน</th>
                                                <th>ฐานเงินเดือน</th>
                                                <th>วันที่เริ่มงาน</th>
                                                <th>สถานะ</th>
                                            </tr>
                                            </thead>
                                            <tbody>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <?php
            // 3. โครงสร้างหลัก: Modal Logout และ Footer
            include('includes/Modal-Logout.php');
            include('includes/Footer.php');
            ?>
        </div>
    </div>

    <a class="scroll-to-top rounded" href="#page-top"><i class="fas fa-angle-up"></i></a>

    <script src="vendor/jquery/jquery.min.js"></script>
    <script src="vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
    <script src="vendor/jquery-easing/jquery.easing.min.js"></script>
    <script src="js/myadmin.min.js"></script>
    <script src="vendor/bootstrap-datepicker/js/bootstrap-datepicker.min.js"></script>
    <script src="vendor/date-picker-1.9/locales/bootstrap-datepicker.th.min.js"></script>

    <script src="vendor/datatables/jquery.dataTables.min.js"></script>
    <script src="vendor/datatables/dataTables.bootstrap4.min.js"></script>
    <script>
        // ฟังก์ชันสำหรับแสดงข้อความแจ้งเตือน
        function showAlert(message, type) {
            const alertArea = $('#alert_area');
            let alertClass = '';

            // กำหนดสีตามสถานะ
            if (type === 'success') {
                alertClass = 'alert-success';
            } else if (type === 'error') {
                alertClass = 'alert-danger';
            } else {
                alertClass = 'alert-info';
            }

            alertArea.html(`
                <div class="alert ${alertClass} alert-dismissible fade show" role="alert">
                    <strong>${type.toUpperCase()}:</strong> ${message}
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
            `).fadeIn();

            // ซ่อนข้อความแจ้งเตือนอัตโนมัติหลังจาก 5 วินาที
            setTimeout(() => {
                alertArea.fadeOut();
            }, 5000);
        }

        $(document).ready(function () {
            // ตั้งค่า Datepicker
            $('#date_doc_date').datepicker({
                format: 'dd/mm/yyyy',
                todayBtn: 'linked',
                todayHighlight: true,
                autoclose: true,
                language: 'th'
            });

            // การจัดการปุ่ม Generate ด้วย Ajax
            $('#btnGenerate').on('click', function (e) {
                // 1. ยืนยันการทำงาน
                if (confirm('ยืนยันการสร้างข้อมูลเงินเดือนสำหรับเดือนและปีที่เลือกหรือไม่? ข้อมูลที่มีอยู่แล้วจะไม่ถูกสร้างซ้ำ')) {

                    const form = $('#form_data');
                    const formData = form.serialize(); // รวบรวมข้อมูลฟอร์ม (รวมถึง input hidden "action")
                    const button = $(this);

                    // ปิดปุ่มและเปลี่ยนข้อความเพื่อแสดงสถานะกำลังโหลด
                    button.attr('disabled', true).html('กำลังสร้าง... <i class="fas fa-spinner fa-spin"></i>');

                    // 2. ส่งข้อมูลผ่าน Ajax
                    $.ajax({
                        type: 'POST',
                        url: 'model/generate_payroll_process.php',
                        data: formData,
                        dataType: 'json',
                        success: function (response) {
                            // 3. จัดการผลลัพธ์
                            showAlert(response.message, response.status);
                        },
                        error: function (jqXHR, textStatus, errorThrown) {
                            // 4. จัดการข้อผิดพลาด
                            console.error("AJAX Error: ", textStatus, errorThrown, jqXHR.responseText);
                            showAlert("เกิดข้อผิดพลาดในการเชื่อมต่อหรือประมวลผล: " + errorThrown + " (" + textStatus + ")", 'error');
                        },
                        complete: function () {
                            // 5. คืนค่าปุ่ม
                            button.attr('disabled', false).html('สร้างข้อมูลเงินเดือนอัตโนมัติ (Generate) <i class="fas fa-magic"></i>');
                        }
                    });
                }
            });

            // *** การตั้งค่า Datatable สำหรับตารางพนักงาน (Server-side) ***
            $('#employeeDataTable').DataTable({
                "processing": true,
                "serverSide": true,
                "ajax": {
                    // ต้องสร้างไฟล์นี้เพื่อดึงข้อมูลพนักงานทั้งหมด
                    "url": "model/get_employees_for_datatable.php",
                    "type": "POST"
                },
                "columns": [
                    // ชื่อคอลัมน์ (data) ต้องตรงกับ Key ใน JSON ที่ส่งมาจาก get_employees_for_datatable.php
                    {"data": "emp_id"},         // รหัสพนักงาน
                    {"data": "full_name"},      // ชื่อ-นามสกุล
                    {"data": "position_desc"},// ตำแหน่ง
                    {"data": "salary_type"},// ประเภท
                    {"data": "salary", "className": "text-right"}, // ฐานเงินเดือน (กำหนดให้ชิดขวา)
                    {"data": "start_work_date"},      // วันที่เริ่มงาน
                    {"data": "status"}         // สถานะ (เช่น Active)
                ],
                "columnDefs": [
                    {"orderable": false, "targets": [3, 4]} // กำหนดคอลัมน์ที่ไม่ให้เรียง
                ],
                "order": [[0, 'asc']], // เรียงตามรหัสพนักงาน
                "language": {
                    // ต้องมีไฟล์ภาษาไทย (thai.json) ใน path ที่กำหนด
                    "url": "vendor/datatables/thai.json"
                }
            });
            // **************************************************
        });
    </script>

    </body>
    </html>
    <?php
} // ปิด else ของ session check
?>