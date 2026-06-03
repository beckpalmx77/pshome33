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
            /* Card Container */
            .search-card {
                border: none;
                border-radius: 12px;
                box-shadow: 0 4px 20px rgba(0,0,0,0.05);
                background: #ffffff;
                overflow: hidden;
            }
            .search-card-header {
                background: linear-gradient(135deg, #4e73df 0%, #224abe 100%) !important;
                border: none;
                padding: 12px 20px !important;
            }
            .search-card-header h6 {
                color: #ffffff !important;
                font-size: 1rem;
                display: flex;
                align-items: center;
                gap: 8px;
                margin: 0;
            }
            
            /* Grid layout for months */
            .month-grid {
                display: grid;
                grid-template-columns: repeat(auto-fill, minmax(130px, 1fr));
                gap: 12px;
                margin-top: 10px;
                margin-bottom: 25px;
            }
            
            .month-radio-card {
                position: relative;
                display: block;
                text-align: center;
                background: #f8f9fc;
                border: 1px solid #d1d3e2;
                border-radius: 8px;
                padding: 12px 10px;
                cursor: pointer;
                font-weight: 600;
                color: #5a5c69;
                transition: all 0.2s ease-in-out;
                user-select: none;
                margin-bottom: 0;
            }
            
            .month-radio-card:hover {
                background: #eaecf4;
                border-color: #b7b9cc;
                color: #2e59d9;
            }
            
            /* Hide default radio input */
            .month-radio-card input[type="radio"] {
                position: absolute;
                opacity: 0;
                width: 0;
                height: 0;
            }
            
            /* Styled selected state via jQuery class */
            .month-radio-card.active {
                background: linear-gradient(135deg, #4e73df 0%, #224abe 100%) !important;
                border-color: #224abe !important;
                color: #ffffff !important;
                box-shadow: 0 4px 10px rgba(78, 115, 223, 0.25);
            }
            
            /* Form Control styling */
            .form-control {
                border-radius: 8px;
                border: 1px solid #d1d3e2;
                padding: 0.6rem 1rem;
                height: 45px;
                transition: all 0.2s;
            }
            .form-control:focus {
                border-color: #4e73df;
                box-shadow: 0 0 0 0.2rem rgba(78, 115, 223, 0.25);
            }
            
            /* Action Button container */
            .btn-action-container {
                display: flex;
                gap: 15px;
                margin-top: 25px;
            }
            
            .btn-pdf {
                background: linear-gradient(135deg, #e74a3b 0%, #be2617 100%) !important;
                border: none;
                color: white !important;
                padding: 0.75rem 1.5rem;
                border-radius: 8px;
                font-weight: 600;
                height: 48px;
                display: inline-flex;
                align-items: center;
                justify-content: center;
                gap: 8px;
                transition: all 0.2s;
                flex: 1;
            }
            .btn-pdf:hover {
                background: linear-gradient(135deg, #be2617 0%, #90160a 100%) !important;
                transform: translateY(-1px);
                box-shadow: 0 4px 12px rgba(231, 74, 59, 0.2);
            }
            
            .btn-excel {
                background: linear-gradient(135deg, #1cc88a 0%, #13855c 100%) !important;
                border: none;
                color: white !important;
                padding: 0.75rem 1.5rem;
                border-radius: 8px;
                font-weight: 600;
                height: 48px;
                display: inline-flex;
                align-items: center;
                justify-content: center;
                gap: 8px;
                transition: all 0.2s;
                flex: 1;
            }
            .btn-excel:hover {
                background: linear-gradient(135deg, #13855c 0%, #0e6243 100%) !important;
                transform: translateY(-1px);
                box-shadow: 0 4px 12px rgba(28, 200, 138, 0.2);
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
                        <h1 class="h4 mb-0 text-gray-800"><?php echo urldecode($_GET['s'] ?? 'รายงานรับชำระค่าส่วนกลาง') ?></h1>
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="<?php echo $_SESSION['dashboard_page'] ?>">Home</a></li>
                            <li class="breadcrumb-item"><?php echo urldecode($_GET['m'] ?? 'รายงาน') ?></li>
                            <li class="breadcrumb-item active"><?php echo urldecode($_GET['s'] ?? 'รายงานรับชำระ') ?></li>
                        </ol>
                    </div>
 
                    <div class="row">
                        <div class="col-lg-12">
                            <div class="card mb-4 search-card">
                                <div class="card-header py-3 search-card-header">
                                    <h6><i class="fas fa-file-invoice"></i> เงื่อนไขและตัวเลือกรายงาน</h6>
                                </div>
                                <div class="card-body">
                                    <form id="form_data" method="post" enctype="multipart/form-data" target="_blank">
                                        <div class="row">
                                            <div class="col-sm-12">
                                                <label class="font-weight-bold text-gray-800">เลือกเดือนสำหรับรายงาน :</label>
                                                <div class="month-grid">
                                                    <?php foreach ($MonthRecords as $row) {
                                                        $checked = ($row["month"] == $month_num) ? 'checked' : '';
                                                        ?>
                                                        <label class="month-radio-card">
                                                            <input type="radio" name="month"
                                                                   value="<?php echo $row["month"]; ?>"
                                                                   class="month-radio-item" <?php echo $checked; ?> required>
                                                            <span><?php echo $row["month_name"]; ?></span>
                                                        </label>
                                                    <?php } ?>
                                                </div>
 
                                                <div class="form-row">
                                                    <div class="col-md-4 col-sm-12">
                                                        <label for="year" class="font-weight-bold text-gray-800">เลือกปี (ค.ศ.) :</label>
                                                        <select name="year" id="year" class="form-control" required>
                                                            <?php foreach ($YearRecords as $row) { ?>
                                                                <option value="<?php echo $row["period_year"]; ?>">
                                                                    <?php echo $row["period_year"]; ?>
                                                                </option>
                                                            <?php } ?>
                                                        </select>
                                                    </div>
                                                </div>
 
                                                <div class="btn-action-container">
                                                    <button type="submit" class="btn btn-pdf" id="btnPrintPdf">
                                                        <i class="fas fa-file-pdf"></i> ออกรายงาน PDF
                                                    </button>
                                                    <button type="submit" class="btn btn-excel" id="btnExportExcel">
                                                        <i class="fas fa-file-excel"></i> ออกรายงาน Excel
                                                    </button>
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
            // ฟังก์ชันจัดการคลาส active ของปุ่มเลือกเดือน
            function updateMonthRadioState() {
                $('.month-radio-card').removeClass('active');
                $('input[name="month"]:checked').closest('.month-radio-card').addClass('active');
            }
 
            // อัพเดตสถานะตอนโหลดหน้าครั้งแรก
            updateMonthRadioState();
 
            // ตรวจจับเมื่อเลือกเปลี่ยนเดือน
            $('input[name="month"]').on('change', function() {
                updateMonthRadioState();
            });
 
            // เมื่อคลิกปุ่ม Print PDF
            $('#btnPrintPdf').on('click', function(e) {
                e.preventDefault();
                $('#form_data').attr('action', 'export_process/payment_house_monthly_report_pdf');
                $('#form_data').submit();
            });
 
            // เมื่อคลิกปุ่ม Export Excel
            $('#btnExportExcel').on('click', function(e) {
                e.preventDefault();
                $('#form_data').attr('action', 'export_process/payment_house_monthly_report_excel');
                $('#form_data').submit();
            });
        });
    </script>
 
    </body>
    </html>
<?php } ?>