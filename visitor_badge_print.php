<?php
include("config/connect_db.php");
include('includes/Header.php');
if (strlen($_SESSION['alogin']) == "") {
    header("Location: index");
    exit();
} else {
    // กำหนด Timezone ให้ถูกต้อง
    date_default_timezone_set('Asia/Bangkok');

    // ดึงวันที่ปัจจุบันและจัดรูปแบบเป็น DD-MM-YYYY
    $current_date = date('d-m-Y');

    $latest_badge_number = 0; // กำหนดค่าเริ่มต้นเป็น 0

    try {
        // SQL Query เพื่อดึงค่าสูงสุดจากคอลัมน์ 'badge_number' ในตารางของคุณ
        // **สำคัญ: เปลี่ยน 'your_table_name' เป็นชื่อตารางจริงของคุณ เช่น 'visitors' หรือ 'visitor_badges'**
        // โดยใช้ CAST เพื่อแปลง badge_number ให้เป็น UNSIGNED INTEGER ก่อนหาค่า MAX
        // การ CAST เป็นสิ่งสำคัญเพื่อให้ MAX() ทำงานได้ถูกต้องกับ string ที่เป็นตัวเลข
        $stmt = $conn->prepare("SELECT MAX(CAST(badge_number AS UNSIGNED)) AS latest_number FROM visitor_badges");
        $stmt->execute();
        $row = $stmt->fetch();

        if ($row && $row['latest_number'] !== null) {
            $latest_badge_number = (int)$row['latest_number']; // แปลงเป็น integer
        }

    } catch (\PDOException $e) {
        // จัดการข้อผิดพลาดในการ Query ฐานข้อมูล
        // ในการใช้งานจริง ควรบันทึกข้อผิดพลาดนี้ใน Log File เพื่อความปลอดภัย แทนที่จะ echo ออกไป
        error_log("Database Error: " . $e->getMessage()); // ตัวอย่างการบันทึก error
        echo "เกิดข้อผิดพลาดในการดึงข้อมูล. กรุณาลองใหม่อีกครั้ง.";
        // กำหนดค่าเริ่มต้นถ้าเกิดข้อผิดพลาดในการดึงข้อมูล เพื่อให้โปรแกรมยังคงทำงานได้
        $latest_badge_number = 0;
    }

    // คำนวณหมายเลขใหม่ (+1)
    $new_start_number = $latest_badge_number + 1;

    // สำหรับหมายเลขสิ้นสุด ในกรณีนี้คือค่าเดียวกับหมายเลขเริ่มต้น
    $new_end_number = $new_start_number;

    ?>
    <!DOCTYPE html>
    <html lang="th">
    <head>
        <meta charset="UTF-8">
        <title>พิมพ์ป้ายแลกบัตรผู้มาติดต่อ</title>
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.9.0/css/bootstrap-datepicker.min.css">
        <style>
            /* สามารถเพิ่ม CSS สำหรับป้ายได้ที่นี่ หากต้องการออกแบบให้แสดงผลในหน้าจอ */
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
                        <h1 class="h4 mb-0 text-gray-800">พิมพ์ป้ายแลกบัตรผู้มาติดต่อ</h1>
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="<?php echo $_SESSION['dashboard_page'] ?? '#' ?>">Home</a></li>
                            <li class="breadcrumb-item">ระบบจัดการผู้ติดต่อ</li>
                            <li class="breadcrumb-item active">พิมพ์ป้าย</li>
                        </ol>
                    </div>

                    <div class="row">
                        <div class="col-lg-12">
                            <div class="card mb-12">
                                <div class="card-body">
                                    <form id="form_data" method="post"
                                          action=""
                                          data-print-action="export_process/visitor_badge_print_process.php"
                                          enctype="multipart/form-data">
                                        <div class="row">
                                            <div class="col-sm-12">
                                                <!--div class="form-group">
                                                    <label for="visitor_name">ชื่อผู้มาติดต่อ :</label>
                                                    <input type="text" name="visitor_name" id="visitor_name" class="form-control" required placeholder="-">
                                                </div-->
                                                <input type="hidden" name="visitor_name" id="visitor_name" class="form-control" value="-">
                                                <div class="form-group">
                                                    <label for="start_badge_number">หมายเลขบัตร เริ่มต้น :</label>
                                                    <input type="number" name="start_badge_number" id="start_badge_number"
                                                           class="form-control" required min="1"
                                                           value="<?php echo htmlspecialchars($new_start_number ?? ''); ?>">
                                                </div>
                                                <div class="form-group">
                                                    <label for="end_badge_number">หมายเลขบัตร ถึง :</label>
                                                    <input type="number" name="end_badge_number" id="end_badge_number"
                                                           class="form-control" required min="1"
                                                           value="<?php echo htmlspecialchars($new_end_number ?? ''); ?>">
                                                </div>
                                                <div class="form-group">
                                                    <label for="print_date">วันที่พิมพ์ :</label>
                                                    <input type="text" name="print_date" id="print_date" class="form-control" readonly value="<?php echo $current_date; ?>">
                                                </div>

                                                <br>
                                                <div class="row">
                                                    <div class="col-sm-12">
                                                        <button type="button" class="btn btn-primary" id="btnPrintBadges">
                                                            พิมพ์ป้ายแลกบัตร <i class="fa fa-print"></i>
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
            // Initialise Datepicker for print_date if needed (though it's readonly here)
            $('#print_date').datepicker({
                format: 'dd-mm-yyyy',
                language: 'th',
                todayHighlight: true,
                autoclose: true
            });

            const form = document.getElementById('form_data');
            const btnPrintBadges = document.getElementById('btnPrintBadges');
            const startBadgeInput = document.getElementById('start_badge_number');
            const endBadgeInput = document.getElementById('end_badge_number');

            // Event listener สำหรับปุ่ม Print Badges
            btnPrintBadges.addEventListener('click', function (e) {
                e.preventDefault(); // ป้องกันการ submit form โดยตรง

                // Basic validation for badge numbers
                const startNum = parseInt(startBadgeInput.value);
                const endNum = parseInt(endBadgeInput.value);

                if (isNaN(startNum) || isNaN(endNum) || startNum <= 0 || endNum <= 0) {
                    alert('กรุณากรอกหมายเลขบัตรเริ่มต้นและสิ้นสุดเป็นตัวเลขที่ถูกต้องและมากกว่า 0');
                    return;
                }

                if (startNum > endNum) {
                    alert('หมายเลขบัตรเริ่มต้นต้องไม่มากกว่าหมายเลขบัตรสิ้นสุด');
                    return;
                }

                form.action = form.dataset.printAction; // กำหนด action เป็น URL สำหรับ Print Process
                form.target = "_blank"; // เปิดในแท็บใหม่สำหรับ PDF/Print
                form.submit(); // Submit form
                form.target = ""; // รีเซ็ต target กลับเป็นค่าเริ่มต้น
            });
        });
    </script>

    </body>
    </html>
<?php } ?>