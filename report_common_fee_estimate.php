<?php
include('includes/Header.php');
if (strlen($_SESSION['alogin']) == "") {
    header("Location: index");
    exit();
} else {
    include("config/connect_db.php");

    $current_year_en = (int)date('Y');
    
    // ดึงปีที่มีในระบบมาใส่ Dropdown
    $sql_year = "SELECT DISTINCT(period_year) AS period_year FROM ims_house_payment WHERE period_year > 0 ORDER BY period_year DESC";
    $stmt_year = $conn->prepare($sql_year);
    $stmt_year->execute();
    $YearRecords = $stmt_year->fetchAll(PDO::FETCH_ASSOC);

    // หากไม่มีข้อมูลปีในฐานข้อมูล ให้สร้างปีปัจจุบันย้อนหลัง 5 ปี
    if (empty($YearRecords)) {
        $YearRecords = [];
        for ($y = $current_year_en; $y >= $current_year_en - 5; $y--) {
            $YearRecords[] = ['period_year' => $y];
        }
    }
    ?>
    <!DOCTYPE html>
    <html lang="th">
    <head>
        <meta charset="UTF-8">
        <title>รายงานเปรียบเทียบประมาณการและยอดจัดเก็บค่าส่วนกลาง</title>
        <link rel="stylesheet" href="css/spin_datatables.css"/>
        <link rel="stylesheet" href="vendor/datatables/v11/jquery.dataTables.min.css"/>
        <link rel="stylesheet" href="vendor/datatables/v11/buttons.dataTables.min.css"/>

        <style>
            .stat-card {
                border-left: 4px solid #4e73df;
                border-radius: 8px;
                box-shadow: 0 0.15rem 1.75rem 0 rgba(58, 59, 69, 0.15);
                transition: transform 0.2s ease-in-out;
            }
            .stat-card:hover {
                transform: translateY(-3px);
            }
            .stat-card.card-target { border-left-color: #4e73df; }
            .stat-card.card-actual { border-left-color: #1cc88a; }
            .stat-card.card-diff-positive { border-left-color: #1cc88a; }
            .stat-card.card-diff-negative { border-left-color: #e74a3b; }
            .stat-card.card-rate { border-left-color: #36b9cc; }

            .text-xs { font-size: .75rem; }
            .font-weight-bold { font-weight: 700!important; }

            .table-estimate th {
                background-color: #eaecf4;
                color: #495057;
                text-align: center;
                vertical-align: middle!important;
            }
            .table-estimate td {
                vertical-align: middle!important;
            }

            .badge-status {
                font-size: 0.85rem;
                padding: 0.35em 0.65em;
                border-radius: 4px;
            }

            /* Print Styles */
            @media print {
                #accordionSidebar, .topbar, .search-card, .btn-action-group, footer, .scroll-to-top {
                    display: none !important;
                }
                #content-wrapper {
                    margin-left: 0 !important;
                    width: 100% !important;
                }
                .container-fluid {
                    padding: 0 !important;
                }
                .card {
                    border: none !important;
                    box-shadow: none !important;
                }
                .chart-container-box {
                    page-break-inside: avoid;
                }
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
                        <h1 class="h3 mb-0 text-gray-800"><?php echo urldecode($_GET['s']) ?></h1>
                        <input type="hidden" id="main_menu" value="<?php echo urldecode($_GET['m']) ?>">
                        <input type="hidden" id="sub_menu" value="<?php echo urldecode($_GET['s']) ?>">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="<?php echo $_SESSION['dashboard_page'] ?>">Home</a>
                            </li>
                            <li class="breadcrumb-item"><?php echo urldecode($_GET['m']) ?></li>
                            <li class="breadcrumb-item active"
                                aria-current="page"><?php echo urldecode($_GET['s']) ?></li>
                        </ol>
                    </div>

                    <!-- Card ตัวเลือกรายงานและสลับหน้าจอ -->
                    <div class="row">
                        <div class="col-lg-12">
                            <div class="card mb-4 search-card">
                                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between bg-primary text-white">
                                    <h6 class="m-0 font-weight-bold"><i class="fas fa-filter mr-1"></i> ตัวเลือกการประมวลผลประมาณการจัดเก็บ</h6>
                                    <div>
                                        <a href="report_house_payment_split_monthly.php?m=<?php echo isset($_GET['m']) ? urlencode($_GET['m']) : urlencode('รายงานต่าง ๆ'); ?>&s=<?php echo urlencode('รายการแสดงการชำระค่าส่วนกลาง'); ?>" class="btn btn-sm btn-light text-primary font-weight-bold">
                                            <i class="fas fa-dollar-sign"></i> แสดงยอดเงินชำระ
                                        </a>
                                        <a href="report_house_payment_split_monthly_2.php?m=<?php echo isset($_GET['m']) ? urlencode($_GET['m']) : urlencode('รายงานต่าง ๆ'); ?>&s=<?php echo urlencode('สรุปจำนวนบ้านที่ชำระค่าส่วนกลาง (ตามปี)'); ?>" class="btn btn-sm btn-light text-primary font-weight-bold ml-1">
                                            <i class="fas fa-home"></i> แสดงจำนวนบ้านที่ชำระ
                                        </a>
                                        <a href="report_house_payment_split_monthly_3.php?m=<?php echo isset($_GET['m']) ? urlencode($_GET['m']) : urlencode('รายงานต่าง ๆ'); ?>&s=<?php echo urlencode('คาดการณ์การจัดเก็บค่าส่วนกลาง (ตามปี)'); ?>" class="btn btn-sm btn-light text-primary font-weight-bold ml-1">
                                            <i class="fas fa-chart-line"></i> คาดการณ์รายซอย
                                        </a>
                                        <a href="report_common_fee_estimate.php?m=<?php echo isset($_GET['m']) ? urlencode($_GET['m']) : urlencode('รายงานต่าง ๆ'); ?>&s=<?php echo urlencode('เปรียบเทียบประมาณการและยอดจัดเก็บค่าส่วนกลาง'); ?>" class="btn btn-sm btn-warning font-weight-bold ml-1">
                                            <i class="fas fa-balance-scale"></i> เปรียบเทียบเป้าหมาย vs ยอดจริง
                                        </a>
                                    </div>
                                </div>
                                <div class="card-body">
                                    <form id="form_estimate_search" class="form-inline align-items-end">
                                        <div class="form-group mr-3 mb-2">
                                            <label for="select_year" class="font-weight-bold mr-2"><i class="far fa-calendar-alt"></i> เลือกปี (พ.ศ.) :</label>
                                            <select name="select_year" id="select_year" class="form-control" style="min-width: 150px;">
                                                <?php foreach ($YearRecords as $row) { 
                                                    $yr = (int)$row["period_year"];
                                                    $selected = ($yr == $current_year_en) ? 'selected' : '';
                                                ?>
                                                    <option value="<?php echo $yr; ?>" <?php echo $selected; ?>>
                                                        <?php echo ($yr + 543) . " (" . $yr . ")"; ?>
                                                    </option>
                                                <?php } ?>
                                            </select>
                                        </div>

                                        <div class="form-group mr-3 mb-2">
                                            <label for="custom_target" class="font-weight-bold mr-2"><i class="fas fa-bullseye"></i> กำหนดเป้าหมายต่อเดือน (บาท) :</label>
                                            <input type="number" step="0.01" id="custom_target" name="custom_target" class="form-control" placeholder="ใช้ค่ามาตรฐานจากฐานข้อมูล" style="min-width: 230px;">
                                            <small class="form-text text-muted ml-2 d-block w-100">(เว้นว่างไว้เพื่อใช้เป้าหมายตามฐานข้อมูลบ้านทั้งหมด)</small>
                                        </div>

                                        <div class="form-group mb-2 btn-action-group">
                                            <button type="button" class="btn btn-primary font-weight-bold mr-2" id="btnProcess">
                                                <i class="fas fa-sync-alt"></i> คำนวณ & แสดงข้อมูล
                                            </button>
                                            <button type="button" class="btn btn-success font-weight-bold mr-2" id="btnExportExcel">
                                                <i class="fas fa-file-excel"></i> ส่งออก Excel
                                            </button>
                                            <button type="button" class="btn btn-secondary font-weight-bold" onclick="window.print();">
                                                <i class="fas fa-print"></i> พิมพ์รายงาน
                                            </button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Summary KPI Cards -->
                    <div class="row mb-4">
                        <!-- Target Year Card -->
                        <div class="col-xl-3 col-md-6 mb-4">
                            <div class="card stat-card card-target h-100 py-2">
                                <div class="card-body">
                                    <div class="row no-gutters align-items-center">
                                        <div class="col mr-2">
                                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                                เป้าหมายประมาณการรวมรายปี</div>
                                            <div class="h5 mb-0 font-weight-bold text-gray-800" id="card_target_year">0.00 บาท</div>
                                            <div class="text-xs text-muted mt-1" id="card_target_monthly_sub">(เฉลี่ย 0.00 บาท/เดือน)</div>
                                        </div>
                                        <div class="col-auto">
                                            <i class="fas fa-bullseye fa-2x text-gray-300"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Actual Year Card -->
                        <div class="col-xl-3 col-md-6 mb-4">
                            <div class="card stat-card card-actual h-100 py-2">
                                <div class="card-body">
                                    <div class="row no-gutters align-items-center">
                                        <div class="col mr-2">
                                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                                ยอดจัดเก็บจริงรวมรายปี</div>
                                            <div class="h5 mb-0 font-weight-bold text-gray-800" id="card_actual_year">0.00 บาท</div>
                                            <div class="text-xs text-muted mt-1" id="card_houses_paid_sub">ชำระแล้ว 0 หลัง</div>
                                        </div>
                                        <div class="col-auto">
                                            <i class="fas fa-wallet fa-2x text-gray-300"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Diff Variance Card -->
                        <div class="col-xl-3 col-md-6 mb-4">
                            <div class="card stat-card card-diff-negative h-100 py-2" id="card_diff_wrapper">
                                <div class="card-body">
                                    <div class="row no-gutters align-items-center">
                                        <div class="col mr-2">
                                            <div class="text-xs font-weight-bold text-uppercase mb-1" id="card_diff_title">
                                                ผลต่างรวมรายปี (ยอดจริง - เป้าหมาย)</div>
                                            <div class="h5 mb-0 font-weight-bold text-gray-800" id="card_diff_year">0.00 บาท</div>
                                            <div class="text-xs font-weight-bold mt-1" id="card_diff_status_badge">
                                                <span class="badge badge-secondary">กำลังโหลด...</span>
                                            </div>
                                        </div>
                                        <div class="col-auto">
                                            <i class="fas fa-balance-scale fa-2x text-gray-300"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Collection Rate Card -->
                        <div class="col-xl-3 col-md-6 mb-4">
                            <div class="card stat-card card-rate h-100 py-2">
                                <div class="card-body">
                                    <div class="row no-gutters align-items-center">
                                        <div class="col mr-2">
                                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                                                อัตราการจัดเก็บสะสม (% ผลสัมฤทธิ์)</div>
                                            <div class="row no-gutters align-items-center">
                                                <div class="col-auto">
                                                    <div class="h5 mb-0 mr-3 font-weight-bold text-gray-800" id="card_rate_year">0.00%</div>
                                                </div>
                                                <div class="col">
                                                    <div class="progress progress-sm mr-2">
                                                        <div class="progress-bar bg-info" id="card_rate_progress" role="progressbar" style="width: 0%"></div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="text-xs text-muted mt-1">เปรียบเทียบยอดจริงกับเป้าหมายรายปี</div>
                                        </div>
                                        <div class="col-auto">
                                            <i class="fas fa-chart-line fa-2x text-gray-300"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Comparison Chart -->
                    <div class="row mb-4 chart-container-box">
                        <div class="col-lg-12">
                            <div class="card mb-4 shadow">
                                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                                    <h6 class="m-0 font-weight-bold text-primary">
                                        <i class="fas fa-chart-bar mr-1"></i> กราฟเปรียบเทียบเป้าหมายประมาณการ vs ยอดจัดเก็บจริง (รายเดือน)
                                    </h6>
                                </div>
                                <div class="card-body">
                                    <div style="position: relative; height: 380px; width: 100%;">
                                        <canvas id="chartEstimateComparison"></canvas>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Detailed Table -->
                    <div class="row">
                        <div class="col-lg-12">
                            <div class="card mb-4 shadow">
                                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                                    <h6 class="m-0 font-weight-bold text-primary">
                                        <i class="fas fa-table mr-1"></i> ตารางเปรียบเทียบประมาณการและยอดจัดเก็บจริง รายเดือน - รายปี
                                    </h6>
                                </div>
                                <div class="table-responsive p-3">
                                    <table class="table table-bordered table-hover align-items-center table-estimate" id="dataTableEstimate" width="100%">
                                        <thead>
                                            <tr>
                                                <th style="width: 12%;">เดือน</th>
                                                <th style="width: 14%;">จำนวนบ้านชำระ</th>
                                                <th style="width: 16%;">เป้าหมายประมาณการ (บาท)</th>
                                                <th style="width: 16%;">ยอดจัดเก็บจริง (บาท)</th>
                                                <th style="width: 18%;">ผลต่าง ยอดจริง - เป้าหมาย (+ / -)</th>
                                                <th style="width: 12%;">อัตราจัดเก็บ (%)</th>
                                                <th style="width: 12%;">สถานะ</th>
                                            </tr>
                                        </thead>
                                        <tbody id="estimateTableBody">
                                            <tr>
                                                <td colspan="7" class="text-center py-4 text-muted">
                                                    <i class="fas fa-spinner fa-spin mr-2"></i> กำลังโหลดข้อมูล...
                                                </td>
                                            </tr>
                                        </tbody>
                                        <tfoot class="bg-light font-weight-bold" id="estimateTableFoot">
                                            <!-- Dynamic Foot Row -->
                                        </tfoot>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>
                <?php
                    include('includes/Modal-Logout.php');
                    include('includes/Footer.php');
                ?>
            </div>
        </div>
    </div>

    <a class="scroll-to-top rounded" href="#page-top"><i class="fas fa-angle-up"></i></a>

    <script src="vendor/jquery/jquery.min.js"></script>
    <script src="vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
    <script src="vendor/jquery-easing/jquery.easing.min.js"></script>
    <script src="js/myadmin.min.js"></script>

    <!-- Chart.js and DataLabels plugin -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-datalabels@2.0.0"></script>

    <script>
        let estimateChart = null;

        function formatMoney(amount) {
            return parseFloat(amount || 0).toLocaleString('th-TH', {
                minimumFractionDigits: 2,
                maximumFractionDigits: 2
            });
        }

        function loadEstimateData() {
            const year = $('#select_year').val();
            const customTarget = $('#custom_target').val();

            $('#estimateTableBody').html(`
                <tr>
                    <td colspan="7" class="text-center py-4 text-muted">
                        <i class="fas fa-spinner fa-spin mr-2"></i> กำลังคำนวณและประมวลผลข้อมูล...
                    </td>
                </tr>
            `);

            $.ajax({
                url: 'process/fetch_common_fee_estimate_data.php',
                type: 'POST',
                data: {
                    year: year,
                    custom_target: customTarget
                },
                dataType: 'json',
                success: function (res) {
                    if (res.status !== 'success') {
                        alert('เกิดข้อผิดพลาด: ' + res.message);
                        return;
                    }

                    // 1. อัปเดต Card KPIs
                    const summary = res.summary;
                    $('#card_target_year').text(formatMoney(summary.total_target_year) + ' บาท');
                    $('#card_target_monthly_sub').text('(เป้าหมายเฉลี่ย ' + formatMoney(res.effective_monthly_target) + ' บาท/เดือน)');
                    
                    $('#card_actual_year').text(formatMoney(summary.total_actual_year) + ' บาท');
                    
                    const diffVal = summary.total_diff_year;
                    const diffSign = summary.total_diff_sign;
                    const diffStr = diffSign + formatMoney(Math.abs(diffVal)) + ' บาท';
                    $('#card_diff_year').text(diffStr);

                    if (diffVal >= 0) {
                        $('#card_diff_wrapper').removeClass('card-diff-negative').addClass('card-diff-positive');
                        $('#card_diff_title').removeClass('text-danger').addClass('text-success');
                        $('#card_diff_status_badge').html('<span class="badge badge-success px-2 py-1"><i class="fas fa-arrow-up mr-1"></i> เกินเป้าหมาย (+' + formatMoney(diffVal) + ')</span>');
                    } else {
                        $('#card_diff_wrapper').removeClass('card-diff-positive').addClass('card-diff-negative');
                        $('#card_diff_title').removeClass('text-success').addClass('text-danger');
                        $('#card_diff_status_badge').html('<span class="badge badge-danger px-2 py-1"><i class="fas fa-arrow-down mr-1"></i> ต่ำกว่าเป้าหมาย (' + formatMoney(diffVal) + ')</span>');
                    }

                    const rateVal = summary.total_achievement_rate;
                    $('#card_rate_year').text(rateVal.toFixed(2) + '%');
                    $('#card_rate_progress').css('width', Math.min(rateVal, 100) + '%');
                    if (rateVal >= 100) {
                        $('#card_rate_progress').removeClass('bg-warning bg-danger').addClass('bg-success');
                    } else if (rateVal >= 80) {
                        $('#card_rate_progress').removeClass('bg-success bg-danger').addClass('bg-warning');
                    } else {
                        $('#card_rate_progress').removeClass('bg-success bg-warning').addClass('bg-danger');
                    }

                    // Count total houses paid
                    let maxPaidHouses = 0;
                    res.months.forEach(m => {
                        if (m.paid_houses > maxPaidHouses) maxPaidHouses = m.paid_houses;
                    });
                    $('#card_houses_paid_sub').text('ชำระแล้วสูงสุด ' + maxPaidHouses + ' จาก ' + res.total_houses + ' หลัง');

                    // 2. แสดงตารางข้อมูล
                    let tbodyHtml = '';
                    res.months.forEach(row => {
                        let diffClass = row.diff_amount >= 0 ? 'text-success font-weight-bold' : 'text-danger font-weight-bold';
                        let diffText = row.diff_sign + formatMoney(row.diff_amount);

                        tbodyHtml += `
                            <tr>
                                <td class="font-weight-bold text-left pl-3">${row.month_name}</td>
                                <td class="text-center">
                                    <span class="font-weight-bold">${row.paid_houses}</span> 
                                    <small class="text-muted">/ ${row.total_houses} หลัง</small>
                                </td>
                                <td class="text-right text-primary font-weight-bold">${formatMoney(row.target_amount)}</td>
                                <td class="text-right text-dark font-weight-bold">${formatMoney(row.actual_amount)}</td>
                                <td class="text-right ${diffClass}">${diffText}</td>
                                <td class="text-center font-weight-bold">${row.achievement_rate.toFixed(2)}%</td>
                                <td class="text-center">
                                    <span class="badge ${row.badge_class} badge-status">${row.status_text}</span>
                                </td>
                            </tr>
                        `;
                    });

                    $('#estimateTableBody').html(tbodyHtml);

                    // 3. แสดง Footer Summary Row
                    let totalDiffClass = summary.total_diff_year >= 0 ? 'text-success' : 'text-danger';
                    let totalDiffText = summary.total_diff_sign + formatMoney(summary.total_diff_year);
                    let tFootHtml = `
                        <tr>
                            <td class="text-left font-weight-bold text-dark pl-3">สรุปรวมทั้งปี (${res.year_th})</td>
                            <td class="text-center text-dark font-weight-bold">-</td>
                            <td class="text-right text-primary font-weight-bold" style="font-size:1.05rem;">${formatMoney(summary.total_target_year)}</td>
                            <td class="text-right text-dark font-weight-bold" style="font-size:1.05rem;">${formatMoney(summary.total_actual_year)}</td>
                            <td class="text-right ${totalDiffClass} font-weight-bold" style="font-size:1.05rem;">${totalDiffText}</td>
                            <td class="text-center font-weight-bold" style="font-size:1.05rem;">${summary.total_achievement_rate.toFixed(2)}%</td>
                            <td class="text-center">
                                <span class="badge ${summary.badge_class} badge-status font-weight-bold">${summary.status_text}</span>
                            </td>
                        </tr>
                    `;
                    $('#estimateTableFoot').html(tFootHtml);

                    // 4. Render Chart.js
                    renderEstimateChart(res.months, res.year_th);
                },
                error: function (xhr, status, err) {
                    console.error('AJAX Error:', err);
                    $('#estimateTableBody').html(`
                        <tr>
                            <td colspan="7" class="text-center py-4 text-danger font-weight-bold">
                                <i class="fas fa-exclamation-triangle mr-2"></i> เกิดข้อผิดพลาดในการโหลดข้อมูล โปรดตรวจสอบระบบ
                            </td>
                        </tr>
                    `);
                }
            });
        }

        function renderEstimateChart(monthsData, yearTh) {
            const ctx = document.getElementById('chartEstimateComparison').getContext('2d');

            if (estimateChart) {
                estimateChart.destroy();
            }

            const labels = monthsData.map(m => m.month_name_short);
            const targetAmounts = monthsData.map(m => m.target_amount);
            const actualAmounts = monthsData.map(m => m.actual_amount);
            const rates = monthsData.map(m => m.achievement_rate);

            estimateChart = new Chart(ctx, {
                type: 'bar',
                plugins: [ChartDataLabels],
                data: {
                    labels: labels,
                    datasets: [
                        {
                            label: 'เป้าหมายประมาณการ (บาท)',
                            data: targetAmounts,
                            backgroundColor: 'rgba(78, 115, 223, 0.65)',
                            borderColor: '#4e73df',
                            borderWidth: 1,
                            borderRadius: 4,
                            order: 2
                        },
                        {
                            label: 'ยอดจัดเก็บได้จริง (บาท)',
                            data: actualAmounts,
                            backgroundColor: 'rgba(28, 200, 138, 0.75)',
                            borderColor: '#1cc88a',
                            borderWidth: 1,
                            borderRadius: 4,
                            order: 3
                        },
                        {
                            label: 'อัตราการจัดเก็บ (%)',
                            data: rates,
                            type: 'line',
                            borderColor: '#e74a3b',
                            backgroundColor: 'rgba(231, 74, 59, 0.1)',
                            borderWidth: 2.5,
                            pointRadius: 4,
                            pointBackgroundColor: '#e74a3b',
                            yAxisID: 'yRate',
                            order: 1
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    interaction: {
                        mode: 'index',
                        intersect: false
                    },
                    plugins: {
                        legend: {
                            position: 'top',
                            labels: {
                                font: {
                                    size: 12,
                                    weight: 'bold'
                                }
                            }
                        },
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    let label = context.dataset.label || '';
                                    if (label) {
                                        label += ': ';
                                    }
                                    if (context.dataset.yAxisID === 'yRate') {
                                        label += context.raw.toFixed(2) + '%';
                                    } else {
                                        label += formatMoney(context.raw) + ' บาท';
                                    }
                                    return label;
                                }
                            }
                        },
                        datalabels: {
                            display: false // ปิด datalabels บนแท่งเพื่อไม่ให้ซ้อนทับกันเกินไป
                        }
                    },
                    scales: {
                        x: {
                            grid: {
                                display: false
                            }
                        },
                        y: {
                            type: 'linear',
                            display: true,
                            position: 'left',
                            title: {
                                display: true,
                                text: 'จำนวนเงิน (บาท)',
                                font: { weight: 'bold' }
                            },
                            ticks: {
                                callback: function(value) {
                                    return (value / 1000).toFixed(0) + 'k';
                                }
                            }
                        },
                        yRate: {
                            type: 'linear',
                            display: true,
                            position: 'right',
                            min: 0,
                            max: 120,
                            title: {
                                display: true,
                                text: 'อัตราจัดเก็บ (%)',
                                font: { weight: 'bold' }
                            },
                            grid: {
                                drawOnChartArea: false
                            },
                            ticks: {
                                callback: function(value) {
                                    return value + '%';
                                }
                            }
                        }
                    }
                }
            });
        }

        $(document).ready(function () {
            // โหลดข้อมูลเมื่อเปิดหน้าครั้งแรก
            loadEstimateData();

            // ปุ่มกดคำนวณใหม่
            $('#btnProcess').click(function () {
                loadEstimateData();
            });

            // เมื่อเปลี่ยนปี
            $('#select_year').change(function () {
                loadEstimateData();
            });

            // ปุ่มส่งออก Excel
            $('#btnExportExcel').click(function () {
                const year = $('#select_year').val();
                const customTarget = $('#custom_target').val();
                window.location.href = `export_process/export_common_fee_estimate_excel.php?year=${year}&custom_target=${customTarget}`;
            });
        });
    </script>
    </body>
    </html>
<?php } ?>
