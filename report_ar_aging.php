<?php
include('includes/Header.php');
if (strlen($_SESSION['alogin']) == "") {
    header("Location: index.php");
} else {
    include('config/connect_db.php');

    $start_year = 2025;
    $start_month = 1;

    $ref_year = isset($_GET['year']) ? (int)$_GET['year'] : (int)date('Y');
    $ref_month = isset($_GET['month']) ? (int)$_GET['month'] : (int)date('m');

    $filter_alley = isset($_GET['alley']) ? $_GET['alley'] : '';

    $thai_months = [
        "มกราคม", "กุมภาพันธ์", "มีนาคม", "เมษายน", "พฤษภาคม", "มิถุนายน",
        "กรกฎาคม", "สิงหาคม", "กันยายน", "ตุลาคม", "พฤศจิกายน", "ธันวาคม"
    ];

    $lookback_months = ($ref_year - $start_year) * 12 + ($ref_month - $start_month) + 1;
    if ($lookback_months < 1) $lookback_months = 1;

    // ----- ดึงข้อมูลบ้านทั้งหมด -----
    $sql_house = "SELECT m.house_number, m.alley, m.area_size, m.common_fee, m.garbage_collection_fee,
                         h.contact_name, h.phone_number, h.house_status
                  FROM ims_house_master m
                  LEFT JOIN ims_house h ON m.house_number = h.house_number
                  WHERE m.status = 'Y' AND m.house_number LIKE '6%'";
    if ($filter_alley != '') {
        $sql_house .= " AND m.alley = :alley";
    }
    $sql_house .= " ORDER BY m.house_number ASC";

    $stmt_house = $conn->prepare($sql_house);
    if ($filter_alley != '') {
        $stmt_house->bindParam(':alley', $filter_alley, PDO::PARAM_STR);
    }
    $stmt_house->execute();
    $houses = $stmt_house->fetchAll(PDO::FETCH_ASSOC);

    // ----- ดึงข้อมูลการชำระเงินทั้งหมด -----
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
    for ($i = 0; $i < $lookback_months; $i++) {
        $m = $ref_month - $i;
        $y = $ref_year;
        while ($m < 1) {
            $m += 12;
            $y--;
        }
        $months_to_check[] = ['y' => $y, 'm' => $m];
    }

    // ----- คำนวณยอดค้างต่อบ้าน -----
    $aging_data = [];
    foreach ($houses as $h) {
        $hn = $h['house_number'];
        $fee = (float)$h['common_fee'];

        $aging = [
            'bucket_1_3' => 0, 'count_1_3' => 0,
            'bucket_4_6' => 0, 'count_4_6' => 0,
            'bucket_7_12' => 0, 'count_7_12' => 0,
            'bucket_over_12' => 0, 'count_over_12' => 0,
            'total_overdue' => 0, 'max_consecutive' => 0,
            'overdue_months' => [],
        ];

        $consecutive_unpaid = 0;
        foreach ($months_to_check as $idx => $period) {
            $y = $period['y'];
            $m = $period['m'];
            $paid = isset($paid_months_by_house[$hn][$y][$m]);

            if (!$paid) {
                $consecutive_unpaid++;
                $aging['overdue_months'][] = ['y' => $y, 'm' => $m];
                if ($consecutive_unpaid >= 1 && $consecutive_unpaid <= 3) {
                    $aging['bucket_1_3'] += $fee;
                    $aging['count_1_3']++;
                } elseif ($consecutive_unpaid >= 4 && $consecutive_unpaid <= 6) {
                    $aging['bucket_4_6'] += $fee;
                    $aging['count_4_6']++;
                } elseif ($consecutive_unpaid >= 7 && $consecutive_unpaid <= 12) {
                    $aging['bucket_7_12'] += $fee;
                    $aging['count_7_12']++;
                } elseif ($consecutive_unpaid >= 13) {
                    $aging['bucket_over_12'] += $fee;
                    $aging['count_over_12']++;
                }
                $aging['total_overdue'] += $fee;
                if ($consecutive_unpaid > $aging['max_consecutive']) {
                    $aging['max_consecutive'] = $consecutive_unpaid;
                }
            } else {
                $consecutive_unpaid = 0;
            }
        }

        if ($aging['total_overdue'] > 0) {
            // กำหนดระดับการทวง
            $max = $aging['max_consecutive'];
            if ($max >= 1 && $max <= 3) $aging['dunning_level'] = 'ครั้งที่ 1';
            elseif ($max >= 4 && $max <= 6) $aging['dunning_level'] = 'ครั้งที่ 2';
            elseif ($max >= 7 && $max <= 12) $aging['dunning_level'] = 'ครั้งที่ 3';
            else $aging['dunning_level'] = 'ครั้งสุดท้าย';
            $aging_data[] = array_merge($h, $aging);
        }
    }

    usort($aging_data, function ($a, $b) {
        return $b['total_overdue'] <=> $a['total_overdue'];
    });

    // ----- สรุปยอดรวม -----
    $summary = [
        'count_1_3' => 0, 'sum_1_3' => 0,
        'count_4_6' => 0, 'sum_4_6' => 0,
        'count_7_12' => 0, 'sum_7_12' => 0,
        'count_over_12' => 0, 'sum_over_12' => 0,
        'total_overdue' => 0, 'total_houses' => count($aging_data),
    ];
    foreach ($aging_data as $d) {
        $summary['count_1_3'] += $d['count_1_3'];
        $summary['sum_1_3'] += $d['bucket_1_3'];
        $summary['count_4_6'] += $d['count_4_6'];
        $summary['sum_4_6'] += $d['bucket_4_6'];
        $summary['count_7_12'] += $d['count_7_12'];
        $summary['sum_7_12'] += $d['bucket_7_12'];
        $summary['count_over_12'] += $d['count_over_12'];
        $summary['sum_over_12'] += $d['bucket_over_12'];
        $summary['total_overdue'] += $d['total_overdue'];
    }

    // ----- ดึงข้อมูลซอยสำหรับ Dropdown กรอง -----
    $alleys = $conn->query("SELECT DISTINCT alley FROM ims_house_master WHERE status = 'Y' AND alley != '' ORDER BY alley ASC")->fetchAll(PDO::FETCH_ASSOC);
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
                        <h1 class="h3 mb-0 text-gray-800"><?php echo urldecode($_GET['s'] ?? 'รายงานลูกหนี้คงค้าง (AR Aging)') ?></h1>
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="<?php echo $_SESSION['dashboard_page'] ?>">Home</a></li>
                            <li class="breadcrumb-item"><?php echo urldecode($_GET['m'] ?? 'รายงาน') ?></li>
                            <li class="breadcrumb-item active" aria-current="page"><?php echo urldecode($_GET['s'] ?? 'AR Aging') ?></li>
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

                                        <div class="col-md-2">
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

                                        <div class="col-md-2">
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
                                            <label for="alley" class="form-label font-weight-bold">ซอย</label>
                                            <select name="alley" id="alley" class="form-select">
                                                <option value="">ทั้งหมด</option>
                                                <?php foreach ($alleys as $a): ?>
                                                    <option value="<?= htmlspecialchars($a['alley'] ?? '') ?>" <?= ($filter_alley == $a['alley']) ? 'selected' : '' ?>>
                                                        ซอย <?= htmlspecialchars($a['alley'] ?? '') ?>
                                                    </option>
                                                <?php endforeach; ?>
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
                                        <h5><i class="bi bi-file-earmark-text-fill"></i> รายงานลูกหนี้คงค้าง (AR Aging) ตั้งแต่ <span class="text-primary font-weight-bold"><?= $thai_months[$start_month - 1] ?> <?= $start_year + 543 ?></span> ถึง <span class="text-danger font-weight-bold"><?= $thai_months[$ref_month - 1] ?> <?= $ref_year + 543 ?></span></h5>

                                        <!-- Summary Cards -->
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
                                                                <div class="text-xs font-weight-bold text-danger text-uppercase mb-1"> overdue 7-12 เดือน</div>
                                                                <div class="h5 mb-0 font-weight-bold text-gray-800"><?= number_format($summary['sum_7_12'], 2) ?> บาท</div>
                                                                <small><?= $summary['count_7_12'] ?> เดือน</small>
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
                                                                <div class="text-xs font-weight-bold text-dark text-uppercase mb-1"> overdue เกิน 12 เดือน</div>
                                                                <div class="h5 mb-0 font-weight-bold text-gray-800"><?= number_format($summary['sum_over_12'], 2) ?> บาท</div>
                                                                <small><?= $summary['count_over_12'] ?> เดือน</small>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="row mb-4">
                                            <div class="col-md-12">
                                                <div class="card bg-gradient-dark text-white">
                                                    <div class="card-body d-flex justify-content-between align-items-center">
                                                        <h4 class="mb-0">ยอดลูกหนี้คงค้างรวม: <strong><?= number_format($summary['total_overdue'], 2) ?> บาท</strong></h4>
                                                        <span class="badge bg-light text-dark fs-6">จำนวน <?= $summary['total_houses'] ?> หลัง</span>
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
                                                        <th>เบอร์โทร</th>
                                                        <th class="text-center">ค่าส่วนกลาง/เดือน</th>
                                                        <th class="text-center"> overdue 1-3</th>
                                                        <th class="text-center"> overdue 4-6</th>
                                                        <th class="text-center"> overdue 7-12</th>
                                                        <th class="text-center"> overdue >12</th>
                                                        <th class="text-center text-danger font-weight-bold">ยอดค้างรวม</th>
                                                        <th class="text-center">ระดับการทวง</th>
                                                        <th class="text-center">พิมพ์ใบทวง</th>
                                                        <th class="text-center">แจ้งเตือน LINE</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                <?php if (empty($aging_data)): ?>
                                                    <tr>
                                                        <td colspan="14" class="text-center text-muted">- ไม่พบรายการค้างชำระ -</td>
                                                    </tr>
                                                <?php else: ?>
                                                    <?php $i = 1; ?>
                                                    <?php foreach ($aging_data as $row): ?>
                                                        <?php
                                                        $dunning_color = 'success';
                                                        if ($row['max_consecutive'] >= 4 && $row['max_consecutive'] <= 6) $dunning_color = 'warning';
                                                        elseif ($row['max_consecutive'] >= 7 && $row['max_consecutive'] <= 12) $dunning_color = 'danger';
                                                        elseif ($row['max_consecutive'] > 12) $dunning_color = 'dark';
                                                        ?>
                                                        <tr>
                                                            <td class="text-center"><?= $i++ ?></td>
                                                            <td><?= htmlspecialchars($row['house_number'] ?? '') ?></td>
                                                            <td><?= htmlspecialchars($row['alley'] ?? '') ?></td>
                                                            <td><?= htmlspecialchars($row['contact_name'] ?? '') ?></td>
                                                            <td><?= htmlspecialchars($row['phone_number'] ?? '') ?></td>
                                                            <td class="text-end"><?= number_format((float)$row['common_fee'], 2) ?></td>
                                                            <td class="text-end <?= $row['bucket_1_3'] > 0 ? 'text-primary font-weight-bold' : '' ?>">
                                                                <?= $row['bucket_1_3'] > 0 ? number_format($row['bucket_1_3'], 2) : '-' ?>
                                                            </td>
                                                            <td class="text-end <?= $row['bucket_4_6'] > 0 ? 'text-warning font-weight-bold' : '' ?>">
                                                                <?= $row['bucket_4_6'] > 0 ? number_format($row['bucket_4_6'], 2) : '-' ?>
                                                            </td>
                                                            <td class="text-end <?= $row['bucket_7_12'] > 0 ? 'text-danger font-weight-bold' : '' ?>">
                                                                <?= $row['bucket_7_12'] > 0 ? number_format($row['bucket_7_12'], 2) : '-' ?>
                                                            </td>
                                                            <td class="text-end <?= $row['bucket_over_12'] > 0 ? 'text-dark font-weight-bold' : '' ?>">
                                                                <?= $row['bucket_over_12'] > 0 ? number_format($row['bucket_over_12'], 2) : '-' ?>
                                                            </td>
                                                            <td class="text-end text-danger font-weight-bold"><?= number_format($row['total_overdue'], 2) ?></td>
                                                            <td class="text-center">
                                                                <span class="badge bg-<?= $dunning_color ?>">
                                                                    ทวง<?= $row['dunning_level'] ?>
                                                                </span>
                                                            </td>
                                                            <td class="text-center">
                                                                <a href="print_dunning_pdf.php?house=<?= urlencode($row['house_number']) ?>&ref_year=<?= $ref_year ?>&ref_month=<?= $ref_month ?>"
                                                                   target="_blank"
                                                                   class="btn btn-outline-danger btn-sm"
                                                                   title="พิมพ์ใบทวงถาม">
                                                                    <i class="fas fa-file-pdf"></i> PDF
                                                                </a>
                                                            </td>
                                                            <td class="text-center">
                                                                <button type="button"
                                                                        class="btn btn-outline-success btn-sm btn-line-notify"
                                                                        data-house="<?= htmlspecialchars($row['house_number'] ?? '') ?>"
                                                                        data-ref_year="<?= $ref_year ?>"
                                                                        data-ref_month="<?= $ref_month ?>"
                                                                        title="ส่งแจ้งเตือนผ่าน LINE">
                                                                    <i class="fab fa-line"></i> ส่ง LINE
                                                                </button>
                                                            </td>
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

    <style>
        .bg-gradient-dark {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }
        .badge { font-size: 0.75rem; padding: 4px 8px; }
        .btn-outline-danger.btn-sm, .btn-outline-success.btn-sm { padding: 2px 8px; font-size: 0.75rem; }
    </style>

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
                        title: 'AR-Aging-<?= $thai_months[$ref_month - 1] ?>-<?= $ref_year + 543 ?>'
                    }
                ]
            });

            // LINE Notification button handler
            $(document).on('click', '.btn-line-notify', function () {
                var btn = $(this);
                var house = btn.data('house');
                var refYear = btn.data('ref_year');
                var refMonth = btn.data('ref_month');

                btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> กำลังส่ง...');

                $.ajax({
                    url: 'process/send_payment_reminder.php',
                    type: 'POST',
                    data: {
                        house_number: house,
                        ref_year: refYear,
                        ref_month: refMonth
                    },
                    dataType: 'json',
                    success: function (res) {
                        if (res.status === 'success') {
                            alertify.success(res.message);
                        } else {
                            alertify.error(res.message);
                        }
                    },
                    error: function () {
                        alertify.error('เกิดข้อผิดพลาดในการเชื่อมต่อเซิร์ฟเวอร์');
                    },
                    complete: function () {
                        btn.prop('disabled', false).html('<i class="fab fa-line"></i> ส่ง LINE');
                    }
                });
            });
        });
    </script>
    </body>
    </html>
<?php } ?>
