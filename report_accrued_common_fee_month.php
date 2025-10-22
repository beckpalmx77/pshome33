<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
// 1. เรียกใช้ Header ซึ่งควรจะมีการ session_start() อยู่ข้างใน
include('includes/Header.php');

// 2. ตรวจสอบ Session การล็อกอิน
if (strlen($_SESSION['alogin']) == "" || strlen($_SESSION['house_number']) == "") {
    header("Location: index.php");
} else {

    // 3. เรียกใช้ไฟล์เชื่อมต่อฐานข้อมูล
    include('config/connect_db.php');

    // 4. ส่วนประมวลผล PHP
    $selected_month = isset($_GET['month']) ? (int)$_GET['month'] : date('n');
    $selected_year = isset($_GET['year']) ? (int)$_GET['year'] : date('Y');

    $thai_months = [
        "มกราคม", "กุมภาพันธ์", "มีนาคม", "เมษายน", "พฤษภาคม", "มิถุนายน",
        "กรกฎาคม", "สิงหาคม", "กันยายน", "ตุลาคม", "พฤศจิกายน", "ธันวาคม"
    ];
    $display_month_name = $thai_months[$selected_month - 1];

    // === UPDATED SQL QUERY ===
    $sql = "
        SELECT
            m.house_number,
            m.alley,
            m.area_size,
            m.common_fee,
            h.contact_name,
            h.phone_number
        FROM
            ims_house_master AS m
        LEFT JOIN
            ims_house AS h ON m.house_number = h.house_number
        LEFT JOIN
            ims_house_payment AS p ON m.house_number = p.house_number
            AND p.period_year = :year
            AND :month BETWEEN p.period_month_start AND p.period_month_to
            AND p.payment_status = 'Y'
        WHERE
            p.id IS NULL
            AND m.status = 'Y'
            AND m.house_number LIKE '6%'
        ORDER BY
            m.house_number;
    ";
    // === END UPDATED SQL QUERY ===

    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':year', $selected_year, PDO::PARAM_INT);
    $stmt->bindParam(':month', $selected_month, PDO::PARAM_INT);
    $stmt->execute();
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $keyed_results = [];
    foreach ($results as $result) {
        $keyed_results[$result['house_number']] = $result;
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
                                    <h6 class="m-0 font-weight-bold text-primary">
                                        กรุณาเลือกเดือนและปีที่ต้องการตรวจสอบ</h6>
                                </div>
                                <div class="card-body">
                                    <form action="" method="GET" class="row g-3 align-items-end mb-4">
                                        <input type="hidden" name="m" value="<?= htmlspecialchars($_GET['m'] ?? '') ?>">
                                        <input type="hidden" name="s" value="<?= htmlspecialchars($_GET['s'] ?? '') ?>">
                                        <div class="col-md-5">
                                            <label for="month" class="form-label">เลือกเดือน</label>
                                            <select name="month" id="month" class="form-select">
                                                <?php for ($m = 1; $m <= 12; $m++): ?>
                                                    <option value="<?= $m ?>" <?= ($m == $selected_month) ? 'selected' : '' ?>><?= $thai_months[$m - 1] ?></option>
                                                <?php endfor; ?>
                                            </select>
                                        </div>
                                        <div class="col-md-3">
                                            <label for="year" class="form-label">เลือกปี (พ.ศ.)</label>
                                            <select name="year" id="year" class="form-select">
                                                <?php
                                                $current_year = date('Y');
                                                for ($y = $current_year - 5; $y <= $current_year + 1; $y++): ?>
                                                    <option value="<?= $y ?>" <?= ($y == $selected_year) ? 'selected' : '' ?>><?= $y + 543 ?></option>
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
                                                        class="bi bi-arrow-clockwise"></i> เดือนปัจจุบัน</a>
                                        </div>
                                    </form>

                                    <hr>

                                    <div class="mt-4">
                                        <h5><i class="bi bi-file-earmark-text-fill"></i> ผลการค้นหา:
                                            บ้านที่ค้างชำระประจำเดือน
                                            <strong><?= htmlspecialchars($display_month_name) ?></strong> ปี
                                            <strong><?= $selected_year + 543 ?></strong></h5>
                                        <div class="table-responsive mt-3">
                                            <table id="reportTable" class="table table-striped table-bordered"
                                                   style="width:100%">
                                                <thead class="thead-light">
                                                <tr>
                                                    <th class="text-center">ลำดับ</th>
                                                    <th>บ้านเลขที่</th>
                                                    <th>ซอย</th>
                                                    <th>พื้นที่ (ตรว)</th>
                                                    <th>ค่าส่วนกลาง</th>
                                                    <th>ชื่อผู้ติดต่อ</th>
                                                    <th>เบอร์โทรศัพท์</th>
                                                </tr>
                                                </thead>
                                                <tbody>
                                                <?php if (empty($keyed_results)): ?>
                                                    <tr>
                                                        <td colspan="4" class="text-center">
                                                            ไม่พบข้อมูลบ้านที่ค้างชำระในเดือนที่เลือก
                                                        </td>
                                                    </tr>
                                                <?php else: ?>
                                                    <?php $i = 1; ?>
                                                    <?php foreach ($keyed_results as $house_number => $row): ?>
                                                        <tr>
                                                            <td class="text-center"><?= $i++ ?></td>
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
                lengthMenu: [[5, 10, 20, 100, -1], [5, 10, 20, 100, "ทั้งหมด"]],
                dom: "<'row'<'col-sm-12 col-md-6'l><'col-sm-12 col-md-6'f>>" +
                    "<'row'<'col-sm-12'tr>>" +
                    "<'row'<'col-sm-12 col-md-5'i><'col-sm-12 col-md-7'p>>" +
                    "<'row'<'col-sm-12'B>>",
                buttons: [
                    {
                        extend: 'excelHtml5',
                        text: '<i class="bi bi-file-earmark-excel"></i> Export to Excel',
                        className: 'btn btn-success btn-sm',
                        title: 'รายงานบ้านที่ค้างชำระ-<?= $display_month_name ?>-<?= $selected_year + 543 ?>'
                    }
                ]
            });
        });
    </script>
    </body>
    </html>

<?php } ?>