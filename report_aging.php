<?php
include('includes/Header.php');
if (strlen($_SESSION['alogin']) == "") {
    header("Location: index.php");
    exit();
} else {
    include('config/connect_db.php');

    $ref_year = isset($_GET['year']) ? (int)$_GET['year'] : (int)date('Y');
    $ref_month = isset($_GET['month']) ? (int)$_GET['month'] : (int)date('m');

    $thai_months = [
        "มกราคม", "กุมภาพันธ์", "มีนาคม", "เมษายน", "พฤษภาคม", "มิถุนายน",
        "กรกฎาคม", "สิงหาคม", "กันยายน", "ตุลาคม", "พฤศจิกายน", "ธันวาคม"
    ];

    $start_year = $ref_year;
    $start_month = 1;
    $lookback_months = ($ref_year - $start_year) * 12 + ($ref_month - $start_month) + 1;
    if ($lookback_months < 1) $lookback_months = 1;

    // ----- ดึงข้อมูลบ้านทั้งหมด -----
    $sql_house = "SELECT m.house_number, m.alley, m.area_size, m.common_fee, h.contact_name, h.phone_number
                  FROM ims_house_master m
                  LEFT JOIN ims_house h ON m.house_number = h.house_number
                  WHERE m.status = 'Y' AND m.house_number LIKE '6%'
                  ORDER BY m.house_number ASC";
    $houses = $conn->query($sql_house)->fetchAll(PDO::FETCH_ASSOC);

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
            'bucket_1_3' => 0,
            'bucket_4_6' => 0,
            'bucket_over_6' => 0,
            'count_1_3' => 0,
            'count_4_6' => 0,
            'count_over_6' => 0,
            'total_overdue' => 0,
        ];

        foreach ($months_to_check as $idx => $period) {
            $y = $period['y'];
            $m = $period['m'];
            $paid = isset($paid_months_by_house[$hn][$y][$m]);

            if (!$paid) {
                $months_old = $idx + 1;
                if ($months_old >= 1 && $months_old <= 3) {
                    $aging['bucket_1_3'] += $fee;
                    $aging['count_1_3']++;
                } elseif ($months_old >= 4 && $months_old <= 6) {
                    $aging['bucket_4_6'] += $fee;
                    $aging['count_4_6']++;
                } elseif ($months_old >= 7) {
                    $aging['bucket_over_6'] += $fee;
                    $aging['count_over_6']++;
                }
                $aging['total_overdue'] += $fee;
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
        .form-select, .form-control {
            border-radius: 8px;
            border: 1px solid #d1d3e2;
            padding: 0.6rem 1rem;
            height: 45px;
            transition: all 0.2s;
        }
        .form-select:focus, .form-control:focus {
            border-color: #4e73df;
            box-shadow: 0 0 0 0.2rem rgba(78, 115, 223, 0.25);
        }
        .btn-search {
            background: linear-gradient(135deg, #4e73df 0%, #224abe 100%);
            border: none;
            color: white;
            padding: 0.6rem 1.2rem;
            border-radius: 8px;
            font-weight: 600;
            height: 45px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            transition: all 0.2s;
        }
        .btn-search:hover {
            background: linear-gradient(135deg, #224abe 0%, #1e3d96 100%);
            color: white;
            transform: translateY(-1px);
        }
        .btn-reset {
            background: #ffffff;
            border: 1px solid #d1d3e2;
            color: #5a5c69;
            padding: 0.6rem 1.2rem;
            border-radius: 8px;
            font-weight: 600;
            height: 45px;
            transition: all 0.2s;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
        }
        .btn-reset:hover {
            background: #eaecf4;
            color: #3a3b45;
        }

        /* Modern Summary Cards */
        .summary-card {
            border: none;
            border-radius: 12px;
            transition: all 0.3s;
            box-shadow: 0 4px 15px rgba(0,0,0,0.04);
            overflow: hidden;
            position: relative;
        }
        .summary-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 25px rgba(0,0,0,0.08);
        }
        .summary-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 4px;
            height: 100%;
        }
        .summary-card.primary::before { background: #4e73df; }
        .summary-card.warning::before { background: #f6c23e; }
        .summary-card.danger::before { background: #e74a3b; }
        .summary-card.dark::before { background: #5a5c69; }

        .summary-icon-wrapper {
            width: 44px;
            height: 44px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            background: #f8f9fc;
        }
        .summary-card.primary .summary-icon-wrapper { color: #4e73df; background: rgba(78,115,223,0.1); }
        .summary-card.warning .summary-icon-wrapper { color: #f6c23e; background: rgba(246,194,62,0.1); }
        .summary-card.danger .summary-icon-wrapper { color: #e74a3b; background: rgba(231,74,59,0.1); }
        .summary-card.dark .summary-icon-wrapper { color: #5a5c69; background: rgba(90,92,105,0.1); }

        /* Table Design */
        .table-responsive {
            max-height: 60vh;
            overflow-y: auto;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.03);
        }
        #reportTable {
            border: 1px solid #e3e6f0 !important;
            border-collapse: collapse !important;
        }
        #reportTable thead th {
            background-color: #f8f9fc;
            border-bottom: 2px solid #e3e6f0 !important;
            border-left: 1px solid #e3e6f0 !important;
            border-right: 1px solid #e3e6f0 !important;
            border-top: none !important;
        }
        #reportTable tbody td {
            border: 1px solid #eaecf4 !important;
        }
        .fixedHeader-floating {
            background-color: white !important;
            z-index: 1000;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }
        .dataTables_wrapper {
            overflow-x: auto;
        }
        .dataTables_wrapper .dataTables_paginate .paginate_button {
            padding: 0.3em 0.6em;
        }
    </style>
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
                            <div class="card mb-4 search-card">
                                <div class="card-header py-3 search-card-header">
                                    <h6><i class="fas fa-filter"></i> ตัวเลือกการค้นหา</h6>
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
                                            <button type="submit" class="btn btn-search w-100"><i class="fas fa-search"></i> ค้นหา</button>
                                        </div>
                                        <div class="col-md-2">
                                            <a href="?m=<?= urlencode($_GET['m'] ?? '') ?>&s=<?= urlencode($_GET['s'] ?? '') ?>" class="btn btn-reset w-100"><i class="fas fa-sync-alt"></i> ค่าปัจจุบัน</a>
                                        </div>
                                    </form>

                                    <hr>

                                    <div class="mt-4">
                                        <h5 class="mb-4 text-gray-800"><i class="fas fa-file-invoice-dollar text-primary"></i> รายงานยอดค้างชำระ (Aging Report) ณ เดือน <span class="text-danger font-weight-bold"><?= $thai_months[$ref_month - 1] ?></span> ปี <strong><?= $ref_year + 543 ?></strong></h5>

                                        <!-- แผงสรุป -->
                                        <div class="row mb-4">
                                            <div class="col-xl-3 col-md-6 mb-3">
                                                <div class="card summary-card primary h-100 py-2">
                                                    <div class="card-body">
                                                        <div class="row align-items-center">
                                                            <div class="col mr-2">
                                                                <div class="text-xs font-weight-bold text-primary text-uppercase mb-1"> overdue 1-3 เดือน</div>
                                                                <div class="h5 mb-0 font-weight-bold text-gray-800"><?= number_format($summary['sum_1_3'], 2) ?> บาท</div>
                                                                <span class="badge bg-light text-primary border mt-1"><i class="fas fa-calendar-alt"></i> <?= $summary['count_1_3'] ?> เดือน</span>
                                                            </div>
                                                            <div class="col-auto">
                                                                <div class="summary-icon-wrapper">
                                                                    <i class="fas fa-clock fa-lg"></i>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-xl-3 col-md-6 mb-3">
                                                <div class="card summary-card warning h-100 py-2">
                                                    <div class="card-body">
                                                        <div class="row align-items-center">
                                                            <div class="col mr-2">
                                                                <div class="text-xs font-weight-bold text-warning text-uppercase mb-1"> overdue 4-6 เดือน</div>
                                                                <div class="h5 mb-0 font-weight-bold text-gray-800"><?= number_format($summary['sum_4_6'], 2) ?> บาท</div>
                                                                <span class="badge bg-light text-warning border mt-1"><i class="fas fa-calendar-alt"></i> <?= $summary['count_4_6'] ?> เดือน</span>
                                                            </div>
                                                            <div class="col-auto">
                                                                <div class="summary-icon-wrapper">
                                                                    <i class="fas fa-exclamation-triangle fa-lg"></i>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-xl-3 col-md-6 mb-3">
                                                <div class="card summary-card danger h-100 py-2">
                                                    <div class="card-body">
                                                        <div class="row align-items-center">
                                                            <div class="col mr-2">
                                                                <div class="text-xs font-weight-bold text-danger text-uppercase mb-1"> overdue เกิน 6 เดือน</div>
                                                                <div class="h5 mb-0 font-weight-bold text-gray-800"><?= number_format($summary['sum_over_6'], 2) ?> บาท</div>
                                                                <span class="badge bg-light text-danger border mt-1"><i class="fas fa-calendar-alt"></i> <?= $summary['count_over_6'] ?> เดือน</span>
                                                            </div>
                                                            <div class="col-auto">
                                                                <div class="summary-icon-wrapper">
                                                                    <i class="fas fa-exclamation-circle fa-lg"></i>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-xl-3 col-md-6 mb-3">
                                                <div class="card summary-card dark h-100 py-2">
                                                    <div class="card-body">
                                                        <div class="row align-items-center">
                                                            <div class="col mr-2">
                                                                <div class="text-xs font-weight-bold text-secondary text-uppercase mb-1"> ยอดค้างรวมทั้งหมด</div>
                                                                <div class="h5 mb-0 font-weight-bold text-gray-800"><?= number_format($summary['total_overdue'], 2) ?> บาท</div>
                                                                <span class="badge bg-light text-secondary border mt-1"><i class="fas fa-home"></i> <?= $summary['total_houses'] ?> บ้าน</span>
                                                            </div>
                                                            <div class="col-auto">
                                                                <div class="summary-icon-wrapper">
                                                                    <i class="fas fa-wallet fa-lg"></i>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="table-responsive mt-3">
                                            <table id="reportTable" class="display nowrap" style="width:100%">
                                                <thead>
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

    <link rel="stylesheet" href="css/spin_datatables_v2.css"/>
    <link rel="stylesheet" href="vendor/datatables/v11/jquery.dataTables.min.css"/>
    <link rel="stylesheet" href="vendor/datatables/v11/buttons.dataTables.min.css"/>
    <link rel="stylesheet" href="https://cdn.datatables.net/fixedheader/3.2.3/css/fixedHeader.dataTables.min.css"/>

    <script src="vendor/datatables/v11/bootbox.min.js"></script>
    <script src="vendor/datatables/v11/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/fixedheader/3.2.3/js/dataTables.fixedHeader.min.js"></script>
    
    <script src="https://cdn.datatables.net/buttons/2.4.2/js/dataTables.buttons.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.html5.min.js"></script>

    <script>
        $(document).ready(function () {
            $('#reportTable').DataTable({
                'pageLength': 5,
                'lengthMenu': [[5, 10, 20, 50, 100, -1], [5, 10, 20, 50, 100, "ทั้งหมด"]],
                'fixedHeader': true,
                'language': {
                    search: 'ค้นหาข้อมูล',
                    lengthMenu: 'แสดง _MENU_ รายการ',
                    info: 'หน้าที่ _PAGE_ จาก _PAGES_',
                    infoEmpty: 'ไม่มีข้อมูล',
                    zeroRecords: "ไม่มีข้อมูลตามเงื่อนไข",
                    infoFiltered: '(กรองข้อมูลจากทั้งหมด _MAX_ รายการ)',
                    paginate: {
                        previous: 'ก่อนหน้า',
                        last: 'สุดท้าย',
                        next: 'ต่อไป'
                    }
                },
                'scrollX': true,
                'autoWidth': false,
                dom: "<'row'<'col-sm-12 col-md-6'l><'col-sm-12 col-md-6'f>>" +
                     "<'row'<'col-sm-12 mb-3'B>>" +
                     "<'row'<'col-sm-12'tr>>" +
                     "<'row'<'col-sm-12 col-md-5'i><'col-sm-12 col-md-7'p>>",
                buttons: [
                    {
                        extend: 'excelHtml5',
                        text: '<i class="fas fa-file-excel"></i> Export to Excel',
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
