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

    $sql_year = "SELECT DISTINCT(period_year) AS period_year FROM ims_house_payment WHERE period_year >= 2024 ORDER BY period_year DESC";
    $stmt_year = $conn->prepare($sql_year);
    $stmt_year->execute();
    $YearRecords = $stmt_year->fetchAll();
    ?>
    <!DOCTYPE html>
    <html lang="th">
    <head>
        <meta charset="UTF-8">
        <title>Export รายงานรับชำระ</title>
        <style>
            /* เพิ่มเล็กน้อยสำหรับ radio button ให้ชิดกันสวยงาม */
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
                        <h1 class="h4 mb-0 text-gray-800"><?php echo urldecode($_GET['s']) ?></h1>
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="<?php echo $_SESSION['dashboard_page'] ?>">Home</a>
                            </li>
                            <li class="breadcrumb-item"><?php echo urldecode($_GET['m']) ?></li>
                            <li class="breadcrumb-item active"><?php echo urldecode($_GET['s']) ?></li>
                        </ol>
                    </div>

                    <div class="row">
                        <div class="col-lg-12">
                            <div class="card mb-12">
                                <div class="card-body">
                                    <form id="form_data" method="post"
                                          enctype="multipart/form-data" target="_blank"> <div class="row">
                                            <div class="col-sm-12">
                                                <label>เลือกเดือน :</label><br>
                                                <div>
                                                    <?php foreach ($MonthRecords as $row) {
                                                        // กำหนดให้เดือนปัจจุบันถูกติ๊กไว้โดย default
                                                        $checked = ($row["month"] == $month_num) ? 'checked' : '';
                                                        ?>
                                                        <label class="month-radio">
                                                            <input type="radio" name="month"
                                                                   value="<?php echo $row["month"]; ?>"
                                                                   class="month-radio-item" <?php echo $checked; ?> required>
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
                                                        <button type="submit" class="btn btn-primary" id="btnPrintPdf">
                                                            Print PDF <i class="fa fa-file-pdf"></i>
                                                        </button>
                                                        <button type="submit" class="btn btn-success" id="btnExportExcel">
                                                            Export Excel <i class="fa fa-file-excel"></i>
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
        $(document).ready(function() {
            // เมื่อคลิกปุ่ม Print PDF
            $('#btnPrintPdf').on('click', function(e) {
                e.preventDefault(); // หยุดการ submit ฟอร์มตามปกติ
                $('#form_data').attr('action', 'export_process/payment_house_monthly_report_pdf.php');
                $('#form_data').submit();
            });

            // เมื่อคลิกปุ่ม Export Excel
            $('#btnExportExcel').on('click', function(e) {
                e.preventDefault(); // หยุดการ submit ฟอร์มตามปกติ
                $('#form_data').attr('action', 'export_process/payment_house_monthly_report_excel.php');
                $('#form_data').submit();
            });
        });
    </script>

    </body>
    </html>
<?php } ?>