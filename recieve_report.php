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
                            <li class="breadcrumb-item"><a href="<?php echo $_SESSION['dashboard_page'] ?>">Home</a></li>
                            <li class="breadcrumb-item"><?php echo urldecode($_GET['m']) ?></li>
                            <li class="breadcrumb-item active"><?php echo urldecode($_GET['s']) ?></li>
                        </ol>
                    </div>

                    <div class="row">
                        <div class="col-lg-12">
                            <div class="card mb-12">
                                <div class="card-body">
                                    <form id="from_data" method="post" action="export_process/export_recieve_report_process.php" enctype="multipart/form-data">
                                        <div class="row">
                                            <div class="col-sm-12">
                                                <label for="month">เลือกเดือน :</label>
                                                <select name="month" id="month" class="form-control" required>
                                                    <option value="<?php echo $month_num; ?>" selected><?php echo $month_name; ?></option>
                                                    <option value="all">ทั้งหมด</option>
                                                    <?php foreach ($MonthRecords as $row) { ?>
                                                        <option value="<?php echo $row["month"]; ?>">
                                                            <?php echo $row["month_name"]; ?>
                                                        </option>
                                                    <?php } ?>
                                                </select>

                                                <label for="year">เลือกปี :</label>
                                                <select name="year" id="year" class="form-control" required>
                                                    <?php foreach ($YearRecords as $row) { ?>
                                                        <option value="<?php echo $row["period_year"]; ?>">
                                                            <?php echo $row["period_year"]; ?>
                                                        </option>
                                                    <?php } ?>
                                                </select>

                                                <br>
                                                <label for="soi">ซอย :</label>
                                                <input type="text" name="soi" id="soi" class="form-control" placeholder="">

                                                <label for="house_no">บ้านเลขที่ :</label>
                                                <input type="text" name="house_no" id="house_no" class="form-control" placeholder="">
                                                <br>

                                                <div class="row">
                                                    <div class="col-sm-12">
                                                        <button type="submit" class="btn btn-success" id="btnExport">
                                                            Export <i class="fa fa-check"></i>
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
                <?php include('includes/Modal-Logout.php'); include('includes/Footer.php'); ?>
            </div>
        </div>
    </div>

    <a class="scroll-to-top rounded" href="#page-top"><i class="fas fa-angle-up"></i></a>

    <!-- JS Scripts -->
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

    <!-- Month Selector Behavior -->
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const monthSelect = document.getElementById('month');

            monthSelect.addEventListener('change', function () {
                if (monthSelect.value === 'all') {
                    monthSelect.classList.add('bg-warning');
                } else {
                    monthSelect.classList.remove('bg-warning');
                }
            });
        });
    </script>
    </body>
    </html>
<?php } ?>
