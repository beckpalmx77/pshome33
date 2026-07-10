<?php
session_start();
error_reporting(0);
include('includes/Header.php');
include('config/connect_db.php');

if (strlen($_SESSION['alogin']) == "") {
    header("Location: index.php");
} else {
    $ref_year = isset($_GET['year']) ? (int)$_GET['year'] : (int)date('Y');
    $ref_month = isset($_GET['month']) ? (int)$_GET['month'] : (int)date('m');
    $filter_alley = isset($_GET['alley']) ? $_GET['alley'] : '';

    $start_year = 2025;
    $start_month = 1;

    $thai_months = [
        "มกราคม", "กุมภาพันธ์", "มีนาคม", "เมษายน", "พฤษภาคม", "มิถุนายน",
        "กรกฎาคม", "สิงหาคม", "กันยายน", "ตุลาคม", "พฤศจิกายน", "ธันวาคม"
    ];

    $lookback_months = ($ref_year - $start_year) * 12 + ($ref_month - $start_month) + 1;
    if ($lookback_months < 1) $lookback_months = 1;

    // ----- ดึงข้อมูลบ้านทั้งหมด -----
    $sql_house = "SELECT m.house_number, m.alley, m.common_fee, h.contact_name, h.phone_number
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

    // ----- ดึงข้อมูลการชำระเงิน -----
    $sql_pay = "SELECT house_number, period_month_start, period_month_to, period_year, amount
                FROM ims_house_payment WHERE payment_status = 'Y'";
    $all_payments = $conn->query($sql_pay)->fetchAll(PDO::FETCH_ASSOC);

    $paid_months_by_house = [];
    foreach ($all_payments as $p) {
        $hn = $p['house_number'];
        $start = (int)$p['period_month_start'];
        $end = (int)$p['period_month_to'];
        $year = (int)$p['period_year'];
        if ($start <= $end) {
            for ($m = $start; $m <= $end; $m++) $paid_months_by_house[$hn][$year][$m] = true;
        } else {
            for ($m = $start; $m <= 12; $m++) $paid_months_by_house[$hn][$year][$m] = true;
            for ($m = 1; $m <= $end; $m++) $paid_months_by_house[$hn][$year + 1][$m] = true;
        }
    }

    // ----- สร้างช่วงเดือน -----
    $months_to_check = [];
    for ($i = 0; $i < $lookback_months; $i++) {
        $m = $ref_month - $i;
        $y = $ref_year;
        while ($m < 1) { $m += 12; $y--; }
        $months_to_check[] = ['y' => $y, 'm' => $m];
    }

    // ----- ตรวจสอบ LINE User และคำนวณยอดค้าง -----
    $sql_line_check = "SELECT house_number FROM ims_house_line_user WHERE status = 'Y'";
    $houses_with_line = [];
    foreach ($conn->query($sql_line_check)->fetchAll(PDO::FETCH_ASSOC) as $r) {
        $houses_with_line[$r['house_number']] = true;
    }

    $reminder_data = [];
    foreach ($houses as $h) {
        $hn = $h['house_number'];
        $fee = (float)$h['common_fee'];
        $total_overdue = 0;
        $max_consecutive = 0;
        $consecutive_unpaid = 0;
        $overdue_count = 0;

        foreach ($months_to_check as $period) {
            $y = $period['y'];
            $m = $period['m'];
            $paid = isset($paid_months_by_house[$hn][$y][$m]);
            if (!$paid) {
                $consecutive_unpaid++;
                $overdue_count++;
                $total_overdue += $fee;
                if ($consecutive_unpaid > $max_consecutive) $max_consecutive = $consecutive_unpaid;
            } else {
                $consecutive_unpaid = 0;
            }
        }

        if ($total_overdue > 0) {
            if ($max_consecutive >= 1 && $max_consecutive <= 3) $dunning_lv = 'ครั้งที่ 1';
            elseif ($max_consecutive >= 4 && $max_consecutive <= 6) $dunning_lv = 'ครั้งที่ 2';
            elseif ($max_consecutive >= 7 && $max_consecutive <= 12) $dunning_lv = 'ครั้งที่ 3';
            else $dunning_lv = 'ครั้งสุดท้าย';

            $has_line = isset($houses_with_line[$hn]);
            $reminder_data[] = [
                'house_number' => $hn,
                'alley' => $h['alley'],
                'contact_name' => $h['contact_name'],
                'phone_number' => $h['phone_number'],
                'common_fee' => $fee,
                'total_overdue' => $total_overdue,
                'overdue_count' => $overdue_count,
                'max_consecutive' => $max_consecutive,
                'dunning_level' => $dunning_lv,
                'has_line' => $has_line,
            ];
        }
    }

    usort($reminder_data, function ($a, $b) {
        return $b['total_overdue'] <=> $a['total_overdue'];
    });

    $total_overdue_sum = array_sum(array_column($reminder_data, 'total_overdue'));
    $total_with_line = count(array_filter($reminder_data, fn($d) => $d['has_line']));
    $total_without_line = count($reminder_data) - $total_with_line;

    // ----- ดึงข้อมูลซอย -----
    $alleys = $conn->query("SELECT DISTINCT alley FROM ims_house_master WHERE status = 'Y' AND alley != '' ORDER BY alley ASC")->fetchAll(PDO::FETCH_ASSOC);
    ?>
    <!DOCTYPE html>
    <html lang="th">
    <head>
        <link rel="stylesheet" href="css/spin_datatables.css"/>
        <link rel="stylesheet" href="vendor/datatables/v11/jquery.dataTables.min.css"/>
        <link rel="stylesheet" href="vendor/datatables/v11/buttons.dataTables.min.css"/>
        <style>
            .card-body { padding: 1rem; }
            .summary-badge { padding: 8px 16px; font-size: 0.9rem; border-radius: 8px; }
            .preview-box {
                border: 2px dashed #ccc;
                border-radius: 8px;
                padding: 16px;
                background: #fafafa;
                min-height: 200px;
                display: none;
            }
            .preview-box.active { display: block; }
            .msg-line { margin-bottom: 8px; padding: 10px; border-radius: 8px; }
            .msg-line.label { background: #e8f5e9; }
            .msg-line.value { background: #fff3e0; }
            .stats-card {
                text-align: center;
                padding: 16px;
                border-radius: 10px;
                color: #fff;
            }
            .stats-card.success { background: linear-gradient(135deg, #43a047, #66bb6a); }
            .stats-card.warning { background: linear-gradient(135deg, #ef6c00, #ffa726); }
            .stats-card.danger { background: linear-gradient(135deg, #c62828, #ef5350); }
            .stats-card.info { background: linear-gradient(135deg, #1565c0, #42a5f5); }
            .send-result { margin-top: 8px; padding: 8px; border-radius: 6px; display: none; }
            .send-result.success { display: block; background: #e8f5e9; color: #2e7d32; }
            .send-result.error { display: block; background: #ffebee; color: #c62828; }
            .dataTables_filter { margin-bottom: 10px; }
            .btn-line { background: #06C755; color: #fff; border: none; }
            .btn-line:hover { background: #05a848; color: #fff; }
            .btn-line:disabled { background: #94e8b0; }
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
                        <h1 class="h3 mb-0 text-gray-800"><?php echo urldecode($_GET['s'] ?? 'จัดการแจ้งเตือนค่าส่วนกลาง') ?></h1>
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="<?php echo $_SESSION['dashboard_page'] ?>">Home</a></li>
                            <li class="breadcrumb-item"><?php echo urldecode($_GET['m'] ?? 'จัดการ') ?></li>
                            <li class="breadcrumb-item active"><?php echo urldecode($_GET['s'] ?? 'แจ้งเตือน') ?></li>
                        </ol>
                    </div>

                    <!-- Filter -->
                    <div class="row">
                        <div class="col-lg-12">
                            <div class="card mb-4">
                                <div class="card-header py-3">
                                    <h6 class="m-0 font-weight-bold text-primary">ค้นหาลูกหนี้คงค้าง</h6>
                                </div>
                                <div class="card-body">
                                    <form action="" method="GET" class="row g-3 align-items-end mb-3">
                                        <input type="hidden" name="m" value="<?= htmlspecialchars($_GET['m'] ?? '') ?>">
                                        <input type="hidden" name="s" value="<?= htmlspecialchars($_GET['s'] ?? '') ?>">
                                        <div class="col-md-2">
                                            <label class="form-label font-weight-bold">ปี (พ.ศ.)</label>
                                            <select name="year" class="form-select">
                                                <?php
                                                $sql_year = "SELECT DISTINCT period_year FROM ims_house_payment ORDER BY period_year DESC";
                                                foreach ($conn->query($sql_year)->fetchAll(PDO::FETCH_ASSOC) as $row_y) {
                                                    $y = $row_y['period_year'];
                                                    echo "<option value=\"$y\"" . (($y == $ref_year) ? ' selected' : '') . ">" . ($y + 543) . "</option>";
                                                }
                                                ?>
                                            </select>
                                        </div>
                                        <div class="col-md-2">
                                            <label class="form-label font-weight-bold">เดือน</label>
                                            <select name="month" class="form-select">
                                                <?php for ($m = 1; $m <= 12; $m++): ?>
                                                    <option value="<?= $m ?>" <?= ($m == $ref_month) ? 'selected' : '' ?>><?= $thai_months[$m - 1] ?></option>
                                                <?php endfor; ?>
                                            </select>
                                        </div>
                                        <div class="col-md-2">
                                            <label class="form-label font-weight-bold">ซอย</label>
                                            <select name="alley" class="form-select">
                                                <option value="">ทั้งหมด</option>
                                                <?php foreach ($alleys as $a): ?>
                                                    <option value="<?= htmlspecialchars($a['alley'] ?? '') ?>" <?= ($filter_alley == $a['alley']) ? 'selected' : '' ?>>ซอย <?= htmlspecialchars($a['alley'] ?? '') ?></option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                        <div class="col-md-2">
                                            <button type="submit" class="btn btn-primary w-100 mt-4"><i class="fas fa-search"></i> ค้นหา</button>
                                        </div>
                                        <div class="col-md-2">
                                            <a href="?m=<?= urlencode($_GET['m'] ?? '') ?>&s=<?= urlencode($_GET['s'] ?? '') ?>" class="btn btn-outline-secondary w-100 mt-4"><i class="fas fa-redo"></i> รีเซ็ต</a>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Stats Cards -->
                    <div class="row mb-4">
                        <div class="col-xl-3 col-md-6 mb-3">
                            <div class="stats-card danger">
                                <div class="h4 mb-0"><?= count($reminder_data) ?></div>
                                <small>บ้านที่ค้างชำระ</small>
                            </div>
                        </div>
                        <div class="col-xl-3 col-md-6 mb-3">
                            <div class="stats-card success">
                                <div class="h4 mb-0"><?= $total_with_line ?></div>
                                <small>มี LINE ID</small>
                            </div>
                        </div>
                        <div class="col-xl-3 col-md-6 mb-3">
                            <div class="stats-card info">
                                <div class="h4 mb-0"><?= number_format($total_overdue_sum, 2) ?></div>
                                <small>ยอดค้างรวม (บาท)</small>
                            </div>
                        </div>
                        <div class="col-xl-3 col-md-6 mb-3">
                            <div class="stats-card warning">
                                <div class="h4 mb-0"><?= $total_without_line ?></div>
                                <small>ไม่มี LINE ID</small>
                            </div>
                        </div>
                    </div>

                    <!-- Action Bar -->
                    <div class="row mb-3">
                        <div class="col-lg-12">
                            <div class="card">
                                <div class="card-body d-flex flex-wrap align-items-center py-3" style="gap: 16px;">
                                    <div class="form-check mb-0" style="padding-left: 1.8em;">
                                        <input type="checkbox" class="form-check-input" id="selectAll">
                                        <label class="form-check-label font-weight-bold" for="selectAll">เลือกทั้งหมด</label>
                                    </div>
                                    <div class="border-start" style="height: 28px;"></div>
                                    <button type="button" class="btn btn-line px-4" id="btnPreviewSelected">
                                        <i class="fas fa-eye"></i> ดูตัวอย่าง
                                    </button>
                                    <button type="button" class="btn btn-success px-4" id="btnSendSelected">
                                        <i class="fab fa-line"></i> ส่งแจ้งเตือน
                                    </button>
                                    <div class="ms-auto">
                                        <span class="badge bg-info fs-6 px-3 py-2" id="selectedCount">เลือก 0 รายการ</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Preview Section -->
                    <div class="row mb-3">
                        <div class="col-lg-12">
                            <div class="card">
                                <div class="card-header py-3">
                                    <h6 class="m-0 font-weight-bold text-primary">ตัวอย่างข้อความแจ้งเตือน</h6>
                                </div>
                                <div class="card-body">
                                    <div id="previewBox" class="preview-box">
                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="msg-line label"><b>🏠 บ้านเลขที่:</b> <span id="previewHouse">-</span></div>
                                                <div class="msg-line label"><b>👤 ผู้ติดต่อ:</b> <span id="previewContact">-</span></div>
                                                <div class="msg-line label"><b>📱 เบอร์โทร:</b> <span id="previewPhone">-</span></div>
                                                <div class="msg-line label"><b>⚠️ ระดับการทวง:</b> <span id="previewLevel">-</span></div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="msg-line value"><b>💰 ยอดค้างรวม:</b> <span id="previewTotal" class="text-danger font-weight-bold">-</span></div>
                                                <div class="msg-line value"><b>📅 จำนวนเดือนที่ค้าง:</b> <span id="previewMonths">-</span></div>
                                                <div class="msg-line value"><b>🏘️ ซอย:</b> <span id="previewAlley">-</span></div>
                                                <div class="msg-line value"><b>🔗 LINE:</b> <span id="previewLine" class="text-success">-</span></div>
                                            </div>
                                        </div>
                                        <hr>
                                        <div class="text-center">
                                            <img src="img/logo/niti_ps33_header200.png" height="40" class="mb-2">
                                            <p class="mb-1"><b>📢 แจ้งเตือนค่าส่วนกลาง</b></p>
                                            <p class="text-muted small">ข้อความ Flex Message จะถูกส่งไปยัง LINE ของผู้ใช้งาน</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Result Section -->
                    <div class="row mb-3">
                        <div class="col-lg-12">
                            <div id="sendResult" class="send-result"></div>
                        </div>
                    </div>

                    <!-- Table -->
                    <div class="row">
                        <div class="col-lg-12">
                            <div class="card mb-4">
                                <div class="card-header py-3">
                                    <h6 class="m-0 font-weight-bold text-primary">รายการลูกหนี้คงค้าง (ตั้งแต่ ม.ค. 2568 - <?= $thai_months[$ref_month - 1] ?> <?= $ref_year + 543 ?>)</h6>
                                </div>
                                <div class="table-responsive p-3">
                                    <table class="table table-striped table-bordered" id="reminderTable" style="width:100%">
                                        <thead class="thead-light">
                                            <tr>
                                                <th class="text-center" style="width:40px;">
                                                    <input type="checkbox" class="form-check-input" id="selectAllHeader">
                                                </th>
                                                <th class="text-center">ลำดับ</th>
                                                <th>บ้านเลขที่</th>
                                                <th>ซอย</th>
                                                <th>ชื่อผู้ติดต่อ</th>
                                                <th>เบอร์โทร</th>
                                                <th class="text-center">ค่าส่วนกลาง/เดือน</th>
                                                <th class="text-center">จำนวนเดือนค้าง</th>
                                                <th class="text-center text-danger">ยอดค้างรวม</th>
                                                <th class="text-center">ระดับการทวง</th>
                                                <th class="text-center">LINE ID</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                        <?php if (empty($reminder_data)): ?>
                                            <tr><td colspan="11" class="text-center text-muted">- ไม่พบรายการ -</td></tr>
                                        <?php else: ?>
                                            <?php $i = 1; ?>
                                            <?php foreach ($reminder_data as $row): ?>
                                                <?php
                                                $line_badge = $row['has_line']
                                                    ? '<span class="badge bg-success"><i class="fab fa-line"></i> เชื่อมต่อ</span>'
                                                    : '<span class="badge bg-secondary"><i class="fas fa-times"></i> ไม่มี</span>';
                                                ?>
                                                <tr>
                                                    <td class="text-center">
                                                        <input type="checkbox" class="form-check-input row-checkbox"
                                                               value="<?= htmlspecialchars($row['house_number'] ?? '') ?>"
                                                               data-contact="<?= htmlspecialchars($row['contact_name'] ?? '-') ?>"
                                                               data-phone="<?= htmlspecialchars($row['phone_number'] ?? '-') ?>"
                                                               data-alley="<?= htmlspecialchars($row['alley'] ?? '-') ?>"
                                                               data-total="<?= $row['total_overdue'] ?>"
                                                               data-count="<?= $row['overdue_count'] ?>"
                                                               data-level="<?= $row['dunning_level'] ?>"
                                                               data-line="<?= $row['has_line'] ? 'yes' : 'no' ?>"
                                                            <?= $row['has_line'] ? '' : 'disabled' ?>>
                                                    </td>
                                                    <td class="text-center"><?= $i++ ?></td>
                                                    <td><?= htmlspecialchars($row['house_number'] ?? '') ?></td>
                                                    <td><?= htmlspecialchars($row['alley'] ?? '') ?></td>
                                                    <td><?= htmlspecialchars($row['contact_name'] ?? '') ?></td>
                                                    <td><?= htmlspecialchars($row['phone_number'] ?? '') ?></td>
                                                    <td class="text-end"><?= number_format($row['common_fee'], 2) ?></td>
                                                    <td class="text-center"><?= $row['overdue_count'] ?></td>
                                                    <td class="text-end text-danger font-weight-bold"><?= number_format($row['total_overdue'], 2) ?></td>
                                                    <td class="text-center"><?= $row['dunning_level'] ?></td>
                                                    <td class="text-center"><?= $line_badge ?></td>
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
                <?php include('includes/Footer.php'); ?>
            </div>
        </div>
    </div>

    <?php include('includes/Modal-Logout.php'); ?>

    <script src="vendor/jquery/jquery.min.js"></script>
    <script src="vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
    <script src="vendor/jquery-easing/jquery.easing.min.js"></script>
    <script src="js/myadmin.min.js"></script>
    <script src="vendor/datatables/v11/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.2/js/dataTables.buttons.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.html5.min.js"></script>

    <script>
        var selectedData = {};

        function updateSelectedCount() {
            var checked = $('.row-checkbox:checked');
            var count = checked.length;
            $('#selectedCount').text('เลือก ' + count + ' รายการ');
            if (count === 0) {
                $('#previewBox').removeClass('active');
            }
        }

        function updatePreview(house) {
            if (!house) {
                $('#previewBox').removeClass('active');
                return;
            }
            var data = selectedData[house];
            if (!data) return;

            $('#previewHouse').text(house);
            $('#previewContact').text(data.contact);
            $('#previewPhone').text(data.phone);
            $('#previewLevel').text(data.level);
            $('#previewTotal').text(numberFormat(data.total) + ' บาท');
            $('#previewMonths').text(data.count + ' เดือน');
            $('#previewAlley').text(data.alley);
            $('#previewLine').text(data.line === 'yes' ? '✅ เชื่อมต่อแล้ว' : '❌ ไม่มี LINE ID');

            $('#previewBox').addClass('active');
        }

        function numberFormat(n) {
            return Number(n).toFixed(2).replace(/\d(?=(\d{3})+\.)/g, '$&,');
        }

        // Select All
        $('#selectAll, #selectAllHeader').on('change', function () {
            var checked = $(this).prop('checked');
            $('.row-checkbox:enabled').prop('checked', checked);
            $('.row-checkbox').each(function () {
                var val = $(this).val();
                if ($(this).prop('checked')) {
                    selectedData[val] = {
                        contact: $(this).data('contact'),
                        phone: $(this).data('phone'),
                        alley: $(this).data('alley'),
                        total: $(this).data('total'),
                        count: $(this).data('count'),
                        level: $(this).data('level'),
                        line: $(this).data('line'),
                    };
                } else {
                    delete selectedData[val];
                }
            });
            updateSelectedCount();
            // Preview first selected
            var keys = Object.keys(selectedData);
            updatePreview(keys.length > 0 ? keys[0] : null);
        });

        // Row checkbox
        $(document).on('change', '.row-checkbox', function () {
            var val = $(this).val();
            if ($(this).prop('checked')) {
                selectedData[val] = {
                    contact: $(this).data('contact'),
                    phone: $(this).data('phone'),
                    alley: $(this).data('alley'),
                    total: $(this).data('total'),
                    count: $(this).data('count'),
                    level: $(this).data('level'),
                    line: $(this).data('line'),
                };
            } else {
                delete selectedData[val];
            }
            updateSelectedCount();
            var keys = Object.keys(selectedData);
            updatePreview(keys.length > 0 ? keys[0] : null);
        });

        // Preview button
        $('#btnPreviewSelected').on('click', function () {
            var keys = Object.keys(selectedData);
            if (keys.length === 0) {
                alertify.error('กรุณาเลือกรายการที่ต้องการดูตัวอย่าง');
                return;
            }
            updatePreview(keys[0]);
        });

        // Send button
        $('#btnSendSelected').on('click', function () {
            var keys = Object.keys(selectedData);
            if (keys.length === 0) {
                alertify.error('กรุณาเลือกรายการที่ต้องการส่งแจ้งเตือน');
                return;
            }

            // Filter only those with LINE
            var withLine = keys.filter(function (k) { return selectedData[k].line === 'yes'; });
            if (withLine.length === 0) {
                alertify.error('รายการที่เลือกไม่มี LINE ID ไม่สามารถส่งแจ้งเตือนได้');
                return;
            }

            if (!confirm('ยืนยันส่งแจ้งเตือนไปยัง ' + withLine.length + ' รายการ?')) {
                return;
            }

            var btn = $(this);
            btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> กำลังส่ง...');

            var successCount = 0;
            var failCount = 0;
            var total = withLine.length;
            var results = [];

            function sendNext(index) {
                if (index >= total) {
                    var html = '<div class="d-flex justify-content-between align-items-center">' +
                        '<span><strong>✅ ส่งสำเร็จ:</strong> ' + successCount + ' รายการ</span>' +
                        '<span><strong>❌ ล้มเหลว:</strong> ' + failCount + ' รายการ</span>' +
                        '<span><strong>📊 รวม:</strong> ' + total + ' รายการ</span>' +
                        '</div><hr><div style="max-height:200px;overflow-y:auto;">';
                    results.forEach(function (r) {
                        var cls = r.status === 'success' ? 'text-success' : 'text-danger';
                        html += '<div class="' + cls + '">[' + r.house + '] ' + r.message + '</div>';
                    });
                    html += '</div>';

                    $('#sendResult').removeClass().addClass('send-result success').html(html).show();
                    btn.prop('disabled', false).html('<i class="fab fa-line"></i> ส่งแจ้งเตือน (LINE)');
                    return;
                }

                var house = withLine[index];
                $.ajax({
                    url: 'process/send_payment_reminder.php',
                    type: 'POST',
                    data: {
                        house_number: house,
                        ref_year: <?= $ref_year ?>,
                        ref_month: <?= $ref_month ?>
                    },
                    dataType: 'json',
                    success: function (res) {
                        if (res.status === 'success') {
                            successCount++;
                            results.push({ house: house, status: 'success', message: res.message });
                        } else {
                            failCount++;
                            results.push({ house: house, status: 'error', message: res.message });
                        }
                    },
                    error: function () {
                        failCount++;
                        results.push({ house: house, status: 'error', message: 'เชื่อมต่อเซิร์ฟเวอร์ล้มเหลว' });
                    },
                    complete: function () {
                        $('#sendResult').html(
                            '<div class="text-center">กำลังส่ง... ' + (index + 1) + '/' + total +
                            ' (สำเร็จ ' + successCount + ', ล้มเหลว ' + failCount + ')</div>'
                        ).show();
                        sendNext(index + 1);
                    }
                });
            }

            sendNext(0);
        });

        // Initial DataTable
        $(document).ready(function () {
            $('#reminderTable').DataTable({
                language: { url: "//cdn.datatables.net/plug-ins/2.0.8/i18n/th.json" },
                pageLength: 20,
                lengthMenu: [[10, 20, 50, 100, -1], [10, 20, 50, 100, "ทั้งหมด"]],
                order: [[8, 'desc']],
                buttons: [
                    {
                        extend: 'excelHtml5',
                        text: '<i class="fas fa-file-excel"></i> Export Excel',
                        className: 'btn btn-success btn-sm',
                        title: 'รายการแจ้งเตือน-<?= $thai_months[$ref_month - 1] ?>-<?= $ref_year + 543 ?>'
                    }
                ],
                dom: "<'row'<'col-sm-12 col-md-6'l><'col-sm-12 col-md-6'f>>" +
                     "<'row'<'col-sm-12'tr>>" +
                     "<'row'<'col-sm-12 col-md-5'i><'col-sm-12 col-md-7'p>>" +
                     "<'row'<'col-sm-12'B>>",
                drawCallback: function () {
                    // Re-check checkboxes after table redraw
                    $('.row-checkbox').each(function () {
                        if (selectedData[$(this).val()]) {
                            $(this).prop('checked', true);
                        }
                    });
                }
            });
        });
    </script>
    </body>
    </html>
<?php } ?>
