<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

include('includes/Header.php');

if (strlen($_SESSION['alogin']) == "" || strlen($_SESSION['house_number']) == "") {
    header("Location: index.php");
} else {
    include('config/connect_db.php');

    // 1. รับค่าตัวแปร
    $selected_year = isset($_GET['year']) ? (int)$_GET['year'] : date('Y');
    $start_month = isset($_GET['start_month']) ? (int)$_GET['start_month'] : date('n');
    $end_month = isset($_GET['end_month']) ? (int)$_GET['end_month'] : date('n');

    // สลับค่าถ้าเดือนเริ่มมากกว่าเดือนจบ
    if ($start_month > $end_month) {
        $temp = $start_month;
        $start_month = $end_month;
        $end_month = $temp;
    }

    $thai_months = [
        "มกราคม", "กุมภาพันธ์", "มีนาคม", "เมษายน", "พฤษภาคม", "มิถุนายน",
        "กรกฎาคม", "สิงหาคม", "กันยายน", "ตุลาคม", "พฤศจิกายน", "ธันวาคม"
    ];

    // ข้อความหัวตาราง
    if ($start_month == $end_month) {
        $display_range_name = $thai_months[$start_month - 1];
    } else {
        $display_range_name = $thai_months[$start_month - 1] . " - " . $thai_months[$end_month - 1];
    }

    // ---------------------------------------------------------
    // STEP 1: ดึงข้อมูลบ้านทั้งหมด (Master Data)
    // ---------------------------------------------------------
    $sql_house = "SELECT m.house_number, m.alley, m.area_size, m.common_fee, h.contact_name, h.phone_number 
                  FROM ims_house_master AS m 
                  LEFT JOIN ims_house AS h ON m.house_number = h.house_number
                  WHERE m.status = 'Y' AND m.house_number LIKE '6%' 
                  ORDER BY m.house_number ASC";
    $stmt_house = $conn->prepare($sql_house);
    $stmt_house->execute();
    $all_houses = $stmt_house->fetchAll(PDO::FETCH_ASSOC);

    // ---------------------------------------------------------
    // STEP 2: ดึงประวัติการจ่ายเงินทั้งหมดของปีที่เลือก มาพักไว้ใน Array
    // ---------------------------------------------------------
    $sql_pay = "SELECT house_number, period_month_start, period_month_to 
                FROM ims_house_payment 
                WHERE period_year = :year AND payment_status = 'Y'";
    $stmt_pay = $conn->prepare($sql_pay);
    $stmt_pay->bindParam(':year', $selected_year, PDO::PARAM_INT);
    $stmt_pay->execute();
    $raw_payments = $stmt_pay->fetchAll(PDO::FETCH_ASSOC);

    // จัดกลุ่ม Payment ตามบ้านเลขที่ เพื่อให้ค้นหาง่าย
    $payments_by_house = [];
    foreach ($raw_payments as $pay) {
        $payments_by_house[$pay['house_number']][] = [
            'start' => (int)$pay['period_month_start'],
            'end' => (int)$pay['period_month_to']
        ];
    }

    // ---------------------------------------------------------
    // STEP 3: Process Loop (ระเบิดรายการตามเดือน)
    // ---------------------------------------------------------
    $final_report_data = [];

    foreach ($all_houses as $house) {
        $h_num = $house['house_number'];

        // วนลูปตรวจสอบทีละเดือน (ตามช่วงที่ User เลือก)
        for ($m = $start_month; $m <= $end_month; $m++) {

            $is_paid = false;

            // เช็คว่าเดือน $m นี้ จ่ายหรือยัง?
            if (isset($payments_by_house[$h_num])) {
                foreach ($payments_by_house[$h_num] as $p_range) {
                    // ถ้าเดือนปัจจุบัน อยู่ในช่วงที่จ่ายแล้ว (Start <= m <= End)
                    if ($m >= $p_range['start'] && $m <= $p_range['end']) {
                        $is_paid = true;
                        break; // เจอแล้วว่าจ่าย ก็หยุดเช็คเดือนนี้
                    }
                }
            }

            // ถ้ายังไม่จ่าย ($is_paid == false) ให้เพิ่มลงในรายงาน
            if (!$is_paid) {
                $row = $house; // เอาข้อมูลบ้านมาตั้งต้น
                $row['owing_month_num'] = $m; // เก็บเลขเดือน
                $row['owing_month_name'] = $thai_months[$m - 1]; // เก็บชื่อเดือน
                $row['owing_year'] = $selected_year + 543; // เก็บปี

                $final_report_data[] = $row;
            }
        }
    }
    ?>

    <!DOCTYPE html>
    <html lang="th">
    <body id="page-top">
    <div id="wrapper">
        <?php include('includes/Side-Bar.php'); ?>

        <div id="content-wrapper" class="d-flex flex-column">
            <div id="content">
                <?php include('includes/Top-Bar.php'); ?>
                <div class="container-fluid" id="container-wrapper">
                    <div class="d-sm-flex align-items-center justify-content-between mb-4">
                        <h1 class="h3 mb-0 text-gray-800"><?php echo urldecode($_GET['s'] ?? 'รายงานบ้านที่ค้างชำระ') ?></h1>
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="<?php echo $_SESSION['dashboard_page'] ?>">Home</a>
                            </li>
                            <li class="breadcrumb-item"><?php echo urldecode($_GET['m'] ?? 'รายงาน') ?></li>
                            <li class="breadcrumb-item active"
                                aria-current="page"><?php echo urldecode($_GET['s'] ?? 'รายงานบ้านที่ค้างชำระ') ?></li>
                        </ol>
                    </div>

                    <div class="row">
                        <div class="col-lg-12">
                            <div class="card mb-4">
                                <div class="card-header py-3">
                                    <h6 class="m-0 font-weight-bold text-primary">ตัวเลือกการค้นหา
                                        (ตรวจสอบรายเดือน)</h6>
                                </div>
                                <div class="card-body">
                                    <form action="" method="GET" class="row g-3 align-items-end mb-4">
                                        <input type="hidden" name="m" value="<?= htmlspecialchars($_GET['m'] ?? '') ?>">
                                        <input type="hidden" name="s" value="<?= htmlspecialchars($_GET['s'] ?? '') ?>">

                                        <div class="col-md-2">
                                            <label for="year" class="form-label font-weight-bold">ประจำปี (พ.ศ.)</label>
                                            <select name="year" id="year" class="form-select">
                                                <?php
                                                // คำสั่ง SQL ดึงปีจากตาราง (ใช้ PDO เพื่อให้เข้ากับ $conn เดิม)
                                                $sql_year = "SELECT DISTINCT period_year FROM ims_house_payment ORDER BY period_year DESC";
                                                $stmt_year = $conn->prepare($sql_year);
                                                $stmt_year->execute();

                                                while ($row_y = $stmt_year->fetch(PDO::FETCH_ASSOC)) {
                                                    $y = $row_y['period_year'];
                                                    $is_selected = ($y == $selected_year) ? 'selected' : '';
                                                    ?>
                                                    <option value="<?= $y ?>" <?= $is_selected ?>>
                                                        <?= $y + 543 ?>
                                                    </option>
                                                <?php } ?>
                                            </select>
                                        </div>

                                        <div class="col-md-3">
                                            <label for="start_month"
                                                   class="form-label font-weight-bold">ตั้งแต่เดือน</label>
                                            <select name="start_month" id="start_month" class="form-select">
                                                <?php for ($m = 1; $m <= 12; $m++): ?>
                                                    <option value="<?= $m ?>" <?= ($m == $start_month) ? 'selected' : '' ?>>
                                                        <?= $thai_months[$m - 1] ?>
                                                    </option>
                                                <?php endfor; ?>
                                            </select>
                                        </div>

                                        <div class="col-md-3">
                                            <label for="end_month" class="form-label font-weight-bold">ถึงเดือน</label>
                                            <select name="end_month" id="end_month" class="form-select">
                                                <?php for ($m = 1; $m <= 12; $m++): ?>
                                                    <option value="<?= $m ?>" <?= ($m == $end_month) ? 'selected' : '' ?>>
                                                        <?= $thai_months[$m - 1] ?>
                                                    </option>
                                                <?php endfor; ?>
                                            </select>
                                        </div>

                                        <div class="col-md-2">
                                            <button type="submit" class="btn btn-primary w-100"><i
                                                        class="bi bi-search"></i> ค้นหา
                                            </button>
                                        </div>
                                        <div class="col-md-2">
                                            <a href="?m=<?= urlencode($_GET['m'] ?? '') ?>&s=<?= urlencode($_GET['s'] ?? '') ?>"
                                               class="btn btn-outline-secondary w-100"><i
                                                        class="bi bi-arrow-clockwise"></i> ค่าปัจจุบัน</a>
                                        </div>
                                    </form>

                                    <hr>

                                    <div class="mt-4">
                                        <h5><i class="bi bi-file-earmark-text-fill"></i> ผลการค้นหา:
                                            รายการค้างชำระช่วง <span
                                                    class="text-danger font-weight-bold"><?= htmlspecialchars($display_range_name) ?></span>
                                            ปี <strong><?= $selected_year + 543 ?></strong>
                                        </h5>

                                        <div class="table-responsive mt-3">
                                            <table id="reportTable" class="table table-striped table-bordered"
                                                   style="width:100%">
                                                <thead class="thead-light">
                                                <tr>
                                                    <th class="text-center">ลำดับ</th>
                                                    <th class="text-center">ประจำปี</th>
                                                    <th class="text-center">ประจำเดือน</th>
                                                    <th>บ้านเลขที่</th>
                                                    <th>ซอย</th>
                                                    <th>พื้นที่ (ตรว)</th>
                                                    <th>ค่าส่วนกลาง</th>
                                                    <th>ชื่อผู้ติดต่อ</th>
                                                    <th>เบอร์โทรศัพท์</th>
                                                </tr>
                                                </thead>
                                                <tbody>
                                                <?php if (empty($final_report_data)): ?>
                                                    <tr>
                                                        <td colspan="9" class="text-center text-muted">-
                                                            ไม่พบรายการค้างชำระในช่วงเวลาที่เลือก -
                                                        </td>
                                                    </tr>
                                                <?php else: ?>
                                                    <?php $i = 1; ?>
                                                    <?php foreach ($final_report_data as $row): ?>
                                                        <tr>
                                                            <td class="text-center"><?= $i++ ?></td>
                                                            <td class="text-center"><?= $row['owing_year'] ?></td>
                                                            <td class="text-center text-danger font-weight-bold"><?= $row['owing_month_name'] ?></td>
                                                            <td><?= htmlspecialchars($row['house_number'] ?? '') ?></td>
                                                            <td><?= htmlspecialchars($row['alley'] ?? '') ?></td>
                                                            <td><?= htmlspecialchars($row['area_size'] ?? '') ?></td>
                                                            <td><?= htmlspecialchars($row['common_fee'] ?? '') ?></td>
                                                            <td><?= htmlspecialchars($row['contact_name'] ?? '') ?></td>
                                                            <td><?= htmlspecialchars($row['phone_number'] ?? '') ?></td>
                                                        </tr>
                                                    <?php endforeach; ?>
                                                <?php endif; ?>
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <?php include('includes/Footer.php'); ?>
        </div>
    </div>

    <a class="scroll-to-top rounded" href="#page-top"><i class="fas fa-angle-up"></i></a>
    <?php include('includes/Modal-Logout.php'); ?>

    <script src="vendor/jquery/jquery.min.js"></script>
    <script src="vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.datatables.net/2.0.8/js/dataTables.js"></script>
    <script src="https://cdn.datatables.net/2.0.8/js/dataTables.bootstrap5.js"></script>
    <script src="https://cdn.datatables.net/buttons/3.0.2/js/dataTables.buttons.js"></script>
    <script src="https://cdn.datatables.net/buttons/3.0.2/js/buttons.bootstrap5.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/pdfmake.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/vfs_fonts.js"></script>
    <script src="https://cdn.datatables.net/buttons/3.0.2/js/buttons.html5.js"></script>
    <script src="https://cdn.datatables.net/buttons/3.0.2/js/buttons.print.js"></script>
    <link rel="stylesheet" href="https://cdn.datatables.net/2.0.8/css/dataTables.bootstrap5.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/buttons/3.0.2/css/buttons.bootstrap5.css">

    <script>
        $(document).ready(function () {
            pdfMake.fonts = {
                Sarabun: {
                    normal: 'https://fonts.gstatic.com/s/sarabun/v13/DtVjJx26TKEr37c9aAFJn2ok.ttf',
                    bold: 'https://fonts.gstatic.com/s/sarabun/v13/DtVmJx26TKEr37c9YK5sulsc.ttf',
                    italics: 'https://fonts.gstatic.com/s/sarabun/v13/DtVhJx26TKEr37c9aBBtm2g2.ttf',
                    bolditalics: 'https://fonts.gstatic.com/s/sarabun/v13/DtVnJx26TKEr37c9aBBxun0s-A.ttf'
                }
            };

            $('#reportTable').DataTable({
                language: {url: "//cdn.datatables.net/plug-ins/2.0.8/i18n/th.json"},
                responsive: true,
                pageLength: 5,
                lengthMenu: [[5, 10, 20, 50, 100, -1], [5, 10, 20, 50, 100, "ทั้งหมด"]],
                dom: "<'row'<'col-sm-12 col-md-6'l><'col-sm-12 col-md-6'f>>" +
                    "<'row'<'col-sm-12'tr>>" +
                    "<'row'<'col-sm-12 col-md-5'i><'col-sm-12 col-md-7'p>>" +
                    "<'row'<'col-sm-12'B>>",
                buttons: [
                    {
                        extend: 'excelHtml5',
                        text: '<i class="bi bi-file-earmark-excel"></i> Export to Excel',
                        className: 'btn btn-success btn-sm',
                        title: 'รายการค้างชำระแยกเดือน-<?= $display_range_name ?>-<?= $selected_year + 543 ?>'
                    }
                ]
            });
        });
    </script>
    </body>
    </html>
<?php } ?>