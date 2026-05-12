<?php
include('includes/Header.php');
if (strlen($_SESSION['alogin']) == "") {
    header("Location: index.php");
} else {
    include('config/connect_db.php');

    $ref_year = isset($_GET['year']) ? (int)$_GET['year'] : (int)date('Y');
    $ref_month = isset($_GET['month']) ? (int)$_GET['month'] : (int)date('m');

    $thai_months = [
        "มกราคม", "กุมภาพันธ์", "มีนาคม", "เมษายน", "พฤษภาคม", "มิถุนายน",
        "กรกฎาคม", "สิงหาคม", "กันยายน", "ตุลาคม", "พฤศจิกายน", "ธันวาคม"
    ];

    $lookback_months = 24;

    // ----- ดึงข้อมูลบ้านทั้งหมด -----
    $sql_house = "SELECT m.house_number, m.alley, m.area_size, m.common_fee, h.contact_name, h.phone_number
                  FROM ims_house_master m
                  LEFT JOIN ims_house h ON m.house_number = h.house_number
                  WHERE m.status = 'Y' AND m.house_number LIKE '6%'
                  ORDER BY m.house_number ASC";
    $houses = $conn->query($sql_house)->fetchAll(PDO::FETCH_ASSOC);

    // ----- ดึงข้อมูลการชำระเงินทั้งหมด (ทุกปี) -----
    $sql_pay = "SELECT house_number, period_month_start, period_month_to, period_year, amount
                FROM ims_house_payment
                WHERE payment_status = 'Y'";
    $all_payments = $conn->query($sql_pay)->fetchAll(PDO::FETCH_ASSOC);

    $paid_months_by_house = [];
    foreach ($all_payments as $p) {
        $hn = $p['house_number'];
        $start = (int)$p['period_month_start'];
        $end = (int)$p['period_month_to'];
        $year = (int)$p['period_year'];

        if ($start <= $end) {
            for ($m = $start; $m <= $end; $m++) {
                $paid_months_by_house[$hn][$year][$m] = true;
            }
        } else {
            // wrap-around: start -> Dec of same year, Jan -> end of next year
            for ($m = $start; $m <= 12; $m++) {
                $paid_months_by_house[$hn][$year][$m] = true;
            }
            for ($m = 1; $m <= $end; $m++) {
                $paid_months_by_house[$hn][$year + 1][$m] = true;
            }
        }
    }

    // ----- สร้างช่วงเดือนย้อนหลังจากเดือนอ้างอิง -----
    $months_to_check = [];
    $total_checked = 0;
    for ($i = 0; $i < $lookback_months; $i++) {
        $m = $ref_month - $i;
        $y = $ref_year;
        while ($m < 1) {
            $m += 12;
            $y--;
        }
        $months_to_check[] = ['y' => $y, 'm' => $m];
        $total_checked++;
    }

    // ----- คำนวณยอดค้างต่อบ้าน -----
    $aging_data = [];
    foreach ($houses as $h) {
        $hn = $h['house_number'];
        $fee = (float)$h['common_fee'];

        $aging = [
            'bucket_1_3' => 0,
            'bucket_4_6' => 0,
            'bucket_over_6' => 0,
            'count_1_3' => 0,
            'count_4_6' => 0,
            'count_over_6' => 0,
            'total_overdue' => 0,
        ];

        $consecutive_unpaid = 0;
        foreach ($months_to_check as $idx => $period) {
            $y = $period['y'];
            $m = $period['m'];
            $paid = isset($paid_months_by_house[$hn][$y][$m]);

            if (!$paid) {
                $consecutive_unpaid++;
                if ($consecutive_unpaid >= 1 && $consecutive_unpaid <= 3) {
                    $aging['bucket_1_3'] += $fee;
                    $aging['count_1_3']++;
                } elseif ($consecutive_unpaid >= 4 && $consecutive_unpaid <= 6) {
                    $aging['bucket_4_6'] += $fee;
                    $aging['count_4_6']++;
                } elseif ($consecutive_unpaid >= 7) {
                    $aging['bucket_over_6'] += $fee;
                    $aging['count_over_6']++;
                }
                $aging['total_overdue'] += $fee;
            } else {
                $consecutive_unpaid = 0;
            }
        }

        if ($aging['total_overdue'] > 0) {
            $aging_data[] = array_merge($h, $aging);
        }
    }

    // เรียงตามยอดค้างมากไปน้อย
    usort($aging_data, function ($a, $b) {
        return $b['total_overdue'] <=> $a['total_overdue'];
    });

    // ----- สรุปยอดรวม -----
    $summary = [
        'count_1_3' => 0, 'sum_1_3' => 0,
        'count_4_6' => 0, 'sum_4_6' => 0,
        'count_over_6' => 0, 'sum_over_6' => 0,
        'total_overdue' => 0, 'total_houses' => count($aging_data),
    ];
    foreach ($aging_data as $d) {
        $summary['count_1_3'] += $d['count_1_3'];
        $summary['sum_1_3'] += $d['bucket_1_3'];
        $summary['count_4_6'] += $d['count_4_6'];
        $summary['sum_4_6'] += $d['bucket_4_6'];
        $summary['count_over_6'] += $d['count_over_6'];
        $summary['sum_over_6'] += $d['bucket_over_6'];
        $summary['total_overdue'] += $d['total_overdue'];
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
                        <h1 class="h3 mb-0 text-gray-800"><?php echo urldecode($_GET['s'] ?? 'รายงานยอดค้างชำระ (Aging Report)') ?></h1>
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="<?php echo $_SESSION['dashboard_page'] ?>">Home</a></li>
                            <li class="breadcrumb-item"><?php echo urldecode($_GET['m'] ?? 'รายงาน') ?></li>
                            <li class="breadcrumb-item active" aria-current="page"><?php echo urldecode($_GET['s'] ?? 'Aging Report') ?></li>
                        </ol>
                    </div>

                    <div class="row">
                        <div class="col-lg-12">
                            <div class="card mb-4">
                                <div class="card-header py-3">
                                    <h6 class="m-0 font-weight-bold text-primary">ตัวเลือกการค้นหา</h6>
                                </div>
                                <div class="card-body">
                                    <form action="" method="GET" class="row g-3 align-items-end mb-4">
                                        <input type="hidden" name="m" value="<?= htmlspecialchars($_GET['m'] ?? '') ?>">
                                        <input type="hidden" name="s" value="<?= htmlspecialchars($_GET['s'] ?? '') ?>">

                                        <div class="col-md-3">
                                            <label for="year" class="form-label font-weight-bold">ปี อ้างอิง (พ.ศ.)</label>
                                            <select name="year" id="year" class="form-select">
                                                <?php
                                                $sql_year = "SELECT DISTINCT period_year FROM ims_house_payment ORDER BY period_year DESC";
                                                foreach ($conn->query($sql_year)->fetchAll(PDO::FETCH_ASSOC) as $row_y) {
                                                    $y = $row_y['period_year'];
                                                    $sel = ($y == $ref_year) ? 'selected' : '';
                                                    echo "<option value=\"$y\" $sel>" . ($y + 543) . "</option>";
                                                }
                                                ?>
                                            </select>
                                        </div>

                                        <div class="col-md-3">
                                            <label for="month" class="form-label font-weight-bold">เดือน อ้างอิง</label>
                                            <select name="month" id="month" class="form-select">
                                                <?php for ($m = 1; $m <= 12; $m++): ?>
                                                    <option value="<?= $m ?>" <?= ($m == $ref_month) ? 'selected' : '' ?>>
                                                        <?= $thai_months[$m - 1] ?>
                                                    </option>
                                                <?php endfor; ?>
                                            </select>
                                        </div>

                                        <div class="col-md-2">
                                            <button type="submit" class="btn btn-primary w-100"><i class="bi bi-search"></i> ค้นหา</button>
                                        </div>
                                        <div class="col-md-2">
                                            <a href="?m=<?= urlencode($_GET['m'] ?? '') ?>&s=<?= urlencode($_GET['s'] ?? '') ?>" class="btn btn-outline-secondary w-100"><i class="bi bi-arrow-clockwise"></i> ค่าปัจจุบัน</a>
                                        </div>
                                    </form>

                                    <hr>

                                    <div class="mt-4">
                                        <h5><i class="bi bi-file-earmark-text-fill"></i> รายงานยอดค้างชำระ (Aging Report) ณ เดือน <span class="text-danger font-weight-bold"><?= $thai_months[$ref_month - 1] ?></span> ปี <strong><?= $ref_year + 543 ?></strong></h5>

                                        <!-- แผงสรุป -->
                                        <div class="row mb-4">
                                            <div class="col-xl-3 col-md-6 mb-3">
                                                <div class="card border-left-primary shadow h-100 py-2">
                                                    <div class="card-body">
                                                        <div class="row no-gutters align-items-center">
                                                            <div class="col mr-2">
                                                                <div class="text-xs font-weight-bold text-primary text-uppercase mb-1"> overdue 1-3 เดือน</div>
                                                                <div class="h5 mb-0 font-weight-bold text-gray-800"><?= number_format($summary['sum_1_3'], 2) ?> บาท</div>
                                                                <small><?= $summary['count_1_3'] ?> เดือน</small>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-xl-3 col-md-6 mb-3">
                                                <div class="card border-left-warning shadow h-100 py-2">
                                                    <div class="card-body">
                                                        <div class="row no-gutters align-items-center">
                                                            <div class="col mr-2">
                                                                <div class="text-xs font-weight-bold text-warning text-uppercase mb-1"> overdue 4-6 เดือน</div>
                                                                <div class="h5 mb-0 font-weight-bold text-gray-800"><?= number_format($summary['sum_4_6'], 2) ?> บาท</div>
                                                                <small><?= $summary['count_4_6'] ?> เดือน</small>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-xl-3 col-md-6 mb-3">
                                                <div class="card border-left-danger shadow h-100 py-2">
                                                    <div class="card-body">
                                                        <div class="row no-gutters align-items-center">
                                                            <div class="col mr-2">
                                                                <div class="text-xs font-weight-bold text-danger text-uppercase mb-1"> overdue เกิน 6 เดือน</div>
                                                                <div class="h5 mb-0 font-weight-bold text-gray-800"><?= number_format($summary['sum_over_6'], 2) ?> บาท</div>
                                                                <small><?= $summary['count_over_6'] ?> เดือน</small>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-xl-3 col-md-6 mb-3">
                                                <div class="card border-left-dark shadow h-100 py-2">
                                                    <div class="card-body">
                                                        <div class="row no-gutters align-items-center">
                                                            <div class="col mr-2">
                                                                <div class="text-xs font-weight-bold text-dark text-uppercase mb-1"> ยอดค้างรวมทั้งหมด</div>
                                                                <div class="h5 mb-0 font-weight-bold text-gray-800"><?= number_format($summary['total_overdue'], 2) ?> บาท</div>
                                                                <small><?= $summary['total_houses'] ?> บ้าน</small>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="table-responsive mt-3">
                                            <table id="reportTable" class="table table-striped table-bordered" style="width:100%">
                                                <thead class="thead-light">
                                                    <tr>
                                                        <th class="text-center">ลำดับ</th>
                                                        <th>บ้านเลขที่</th>
                                                        <th>ซอย</th>
                                                        <th>ชื่อผู้ติดต่อ</th>
                                                        <th>ค่าส่วนกลาง/เดือน</th>
                                                        <th class="text-center"> overdue 1-3 เดือน</th>
                                                        <th class="text-center"> overdue 4-6 เดือน</th>
                                                        <th class="text-center"> overdue เกิน 6 เดือน</th>
                                                        <th class="text-center text-danger font-weight-bold">ยอดค้างรวม</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                <?php if (empty($aging_data)): ?>
                                                    <tr>
                                                        <td colspan="9" class="text-center text-muted">- ไม่พบรายการค้างชำระ -</td>
                                                    </tr>
                                                <?php else: ?>
                                                    <?php $i = 1; ?>
                                                    <?php foreach ($aging_data as $row): ?>
                                                        <tr>
                                                            <td class="text-center"><?= $i++ ?></td>
                                                            <td><?= htmlspecialchars($row['house_number'] ?? '') ?></td>
                                                            <td><?= htmlspecialchars($row['alley'] ?? '') ?></td>
                                                            <td><?= htmlspecialchars($row['contact_name'] ?? '') ?></td>
                                                            <td class="text-end"><?= number_format((float)$row['common_fee'], 2) ?></td>
                                                            <td class="text-end <?= $row['bucket_1_3'] > 0 ? 'text-warning font-weight-bold' : '' ?>">
                                                                <?= $row['bucket_1_3'] > 0 ? number_format($row['bucket_1_3'], 2) : '-' ?>
                                                            </td>
                                                            <td class="text-end <?= $row['bucket_4_6'] > 0 ? 'text-danger font-weight-bold' : '' ?>">
                                                                <?= $row['bucket_4_6'] > 0 ? number_format($row['bucket_4_6'], 2) : '-' ?>
                                                            </td>
                                                            <td class="text-end <?= $row['bucket_over_6'] > 0 ? 'text-danger font-weight-bold' : '' ?>">
                                                                <?= $row['bucket_over_6'] > 0 ? number_format($row['bucket_over_6'], 2) : '-' ?>
                                                            </td>
                                                            <td class="text-end text-danger font-weight-bold"><?= number_format($row['total_overdue'], 2) ?></td>
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
                <?php include('includes/Footer.php'); ?>
            </div>
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
                pageLength: 20,
                lengthMenu: [[10, 20, 50, 100, -1], [10, 20, 50, 100, "ทั้งหมด"]],
                dom: "<'row'<'col-sm-12 col-md-6'l><'col-sm-12 col-md-6'f>>" +
                     "<'row'<'col-sm-12'tr>>" +
                     "<'row'<'col-sm-12 col-md-5'i><'col-sm-12 col-md-7'p>>" +
                     "<'row'<'col-sm-12'B>>",
                buttons: [
                    {
                        extend: 'excelHtml5',
                        text: '<i class="bi bi-file-earmark-excel"></i> Export to Excel',
                        className: 'btn btn-success btn-sm',
                        title: 'Aging-Report-<?= $thai_months[$ref_month - 1] ?>-<?= $ref_year + 543 ?>'
                    }
                ]
            });
        });
    </script>
    </body>
    </html>
<?php } ?>
