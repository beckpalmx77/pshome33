<?php
include('includes/Header.php');

if (strlen($_SESSION['alogin']) == "") {
    header("Location: index");
    exit();
} else {
    include("config/connect_db.php");

    $month_num = ltrim(date('m'), '0');
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

    $sql_year = "SELECT DISTINCT(payroll_year) AS period_year FROM v_ims_payroll WHERE payroll_year >= 2024 ORDER BY payroll_year DESC";
    $stmt_year = $conn->prepare($sql_year);
    $stmt_year->execute();
    $YearRecords = $stmt_year->fetchAll();
    ?>
    <!DOCTYPE html>
    <html lang="th">
    <head>
        <meta charset="UTF-8">
        <title>Export รายงานเงินเดือน</title>
        <style>
            .month-radio {
                margin-right: 15px;
                margin-bottom: 5px;
                display: inline-block;
            }
            .month-radio input {
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
                        <h1 class="h4 mb-0 text-gray-800"><?php echo urldecode($_GET['s'] ?? 'รายงานเงินเดือน') ?></h1>
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="<?php echo $_SESSION['dashboard_page'] ?? '#' ?>">Home</a></li>
                            <li class="breadcrumb-item"><?php echo urldecode($_GET['m'] ?? 'รายงาน') ?></li>
                            <li class="breadcrumb-item active"><?php echo urldecode($_GET['s'] ?? 'เงินเดือน') ?></li>
                        </ol>
                    </div>

                    <div class="row">
                        <div class="col-lg-12">
                            <div class="card mb-12">
                                <div class="card-body">
                                    <form id="form_data" method="get" action="print_payroll_slip_pdf.php" target="_blank" enctype="multipart/form-data">
                                        <div class="row">
                                            <div class="col-sm-12">
                                                <label>เลือกเดือน :</label><br>
                                                <div>
                                                    <?php foreach ($MonthRecords as $row) {
                                                        $checked = ((int)$row["month_id"] == (int)$month_num) ? 'checked' : '';
                                                        ?>
                                                        <label class="month-radio">
                                                            <input type="radio" name="payroll_month"
                                                                   value="<?php echo $row["month_id"]; ?>"
                                                                   class="month-radio-item" <?php echo $checked; ?>>
                                                            <?php echo $row["month_name"]; ?>
                                                        </label>
                                                    <?php } ?>
                                                </div>

                                                <br>
                                                <label for="payroll_year">เลือกปี :</label>
                                                <select name="payroll_year" id="payroll_year" class="form-control" required>
                                                    <?php foreach ($YearRecords as $row) { ?>
                                                        <option value="<?php echo $row["period_year"]; ?>">
                                                            <?php echo $row["period_year"]; ?>
                                                        </option>
                                                    <?php } ?>
                                                </select>
                                                <br>
                                                <div class="row">
                                                    <div class="col-sm-12">
                                                        <button type="submit" class="btn btn-primary" id="btnPrintPdf"> Print PDF <i class="fa fa-file-pdf"></i>
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
                <?php include('includes/Modal-Logout.php'); ?>
                <?php include('includes/Footer.php'); ?>
            </div>
        </div>
    </div>

    <a class="scroll-to-top rounded" href="#page-top"><i class="fas fa-angle-up"></i></a>

    <script src="vendor/jquery/jquery.min.js"></script>
    <script src="vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
    <script src="vendor/jquery-easing/jquery.easing.min.js"></script>
    <script src="js/myadmin.min.js"></script>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const monthRadios = document.querySelectorAll('.month-radio-item');
            const form = document.getElementById('form_data');
            const btnPrintPdf = document.getElementById('btnPrintPdf');

            // Set initial state: ensure only the default current month is selected on load.
            let defaultMonthChecked = false;
            monthRadios.forEach(rb => {
                const monthId = parseInt(rb.value);
                const currentMonthNum = parseInt('<?php echo $month_num; ?>');
                if (monthId === currentMonthNum) {
                    rb.checked = true;
                    defaultMonthChecked = true;
                } else {
                    rb.checked = false; // Ensure other months are unchecked on load
                }
            });

            // If no month is default-checked, ensure the first month in the list is selected.
            if (!defaultMonthChecked && monthRadios.length > 0) {
                monthRadios[0].checked = true;
            }

            // เนื่องจากใช้ radio button อยู่แล้ว การเลือกแค่เดือนเดียวจะถูกบังคับโดย HTML
            // ส่วน JavaScript นี้จึงเพียงแค่ตรวจสอบว่ามีการเลือกเดือนหรือไม่ก่อน submit
            function validateMonthSelected() {
                const selected = Array.from(monthRadios).find(rb => rb.checked);
                if (!selected) {
                    alert('กรุณาเลือกเดือน');
                    return false;
                }
                return true;
            }

            btnPrintPdf.addEventListener('click', function (e) {
                // ป้องกันการ submit ฟอร์มหากการตรวจสอบไม่ผ่าน
                if (!validateMonthSelected()) {
                    e.preventDefault();
                }
                // ถ้าผ่าน การ submit จะเกิดขึ้นตามปกติด้วย method="get" และ target="_blank"
                // ที่ตั้งไว้ในแท็ก form แล้ว
            });
        });
    </script>
    </body>
    </html>
<?php } ?>