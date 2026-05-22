<?php
include('includes/Header.php');
if (strlen($_SESSION['alogin']) == "") {
    header("Location: index.php");
} else {
    include('config/connect_db.php');

    $ref_year = isset($_GET['year']) ? (int)$_GET['year'] : (int)date('Y');
    $ref_month = isset($_GET['month']) ? (int)$_GET['month'] : (int)date('m');
    $start_year = isset($_GET['start_year']) ? (int)$_GET['start_year'] : 2025;
    $filter_category = isset($_GET['category']) ? $_GET['category'] : '';

    $thai_months = [
        "มกราคม", "กุมภาพันธ์", "มีนาคม", "เมษายน", "พฤษภาคม", "มิถุนายน",
        "กรกฎาคม", "สิงหาคม", "กันยายน", "ตุลาคม", "พฤศจิกายน", "ธันวาคม"
    ];

    $start_month = 1;

    // ----- สร้างช่วงเดือนย้อนหลังจากเดือนอ้างอิง ถอยหลังไปจนถึง ม.ค. 2568 -----
    $months_to_check = [];
    $curr_y = $ref_year;
    $curr_m = $ref_month;

    while (true) {
        if ($curr_y < $start_year || ($curr_y == $start_year && $curr_m < $start_month)) {
            break;
        }
        $months_to_check[] = ['y' => $curr_y, 'm' => $curr_m];
        
        $curr_m--;
        if ($curr_m < 1) {
            $curr_m = 12;
            $curr_y--;
        }
    }

    // ----- ดึงข้อมูลบ้านทั้งหมด -----
    $sql_house = "SELECT m.house_number, m.alley, m.area_size, m.common_fee, h.contact_name, h.phone_number
                  FROM ims_house_master m
                  LEFT JOIN ims_house h ON m.house_number = h.house_number
                  WHERE m.status = 'Y' AND m.house_number LIKE '6%'
                  ORDER BY m.house_number ASC";
    $houses = $conn->query($sql_house)->fetchAll(PDO::FETCH_ASSOC);

    // ----- ดึงข้อมูลการชำระเงินทั้งหมด -----
    $sql_pay = "SELECT house_number, period_month_start, period_month_to, period_year
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
            // กรณีคร่อมปี (ถ้ามีในระบบ)
            for ($m = $start; $m <= 12; $m++) {
                $paid_months_by_house[$hn][$year][$m] = true;
            }
            for ($m = 1; $m <= $end; $m++) {
                $paid_months_by_house[$hn][$year + 1][$m] = true;
            }
        }
    }

    // ----- คำนวณยอดค้างต่อบ้าน -----
    $overdue_data = [];
    foreach ($houses as $h) {
        $hn = $h['house_number'];
        $fee = (float)$h['common_fee'];

        $unpaid_count = 0;
        $total_amount = 0;
        $unpaid_list = [];

        foreach ($months_to_check as $period) {
            $y = $period['y'];
            $m = $period['m'];
            
            // ตรวจสอบว่าเดือนนี้จ่ายหรือยัง
            if (!isset($paid_months_by_house[$hn][$y][$m])) {
                $unpaid_count++;
                $total_amount += $fee;
                $unpaid_list[] = $thai_months[$m-1] . " " . ($y + 543);
            }
        }

        if ($unpaid_count > 0) {
            $category = "";
            if ($unpaid_count >= 1 && $unpaid_count < 3) {
                $category = "1-2 เดือน";
            } elseif ($unpaid_count >= 3 && $unpaid_count < 6) {
                $category = "3-5 เดือน";
            } elseif ($unpaid_count == 6) {
                $category = "6 เดือน";
            } elseif ($unpaid_count > 6) {
                $category = "มากกว่า 6 เดือน";
            }

            $overdue_data[] = array_merge($h, [
                'unpaid_count' => $unpaid_count,
                'total_amount' => $total_amount,
                'category' => $category,
                'unpaid_list' => $unpaid_list // Store full array of unpaid months
            ]);
        }
    }

    // ----- สรุปยอดรวมแยกตามประเภท -----
    $summary = [
        'cat_1' => ['count' => 0, 'houses' => 0], // 1-2 เดือน
        'cat_3' => ['count' => 0, 'houses' => 0], // 3-5 เดือน
        'cat_6' => ['count' => 0, 'houses' => 0], // 6 เดือน
        'cat_over_6' => ['count' => 0, 'houses' => 0], // > 6 เดือน
        'total_houses' => count($overdue_data)
    ];

    foreach ($overdue_data as $d) {
        if ($d['unpaid_count'] >= 1 && $d['unpaid_count'] < 3) {
            $summary['cat_1']['houses']++;
        } elseif ($d['unpaid_count'] >= 3 && $d['unpaid_count'] < 6) {
            $summary['cat_3']['houses']++;
        } elseif ($d['unpaid_count'] == 6) {
            $summary['cat_6']['houses']++;
        } elseif ($d['unpaid_count'] > 6) {
            $summary['cat_over_6']['houses']++;
        }
    }

    // ----- กรองข้อมูลตามหมวดหมู่ที่เลือก (ถ้ามี) -----
    if ($filter_category !== '') {
        $threshold = (int)$filter_category;
        $overdue_data = array_filter($overdue_data, function($item) use ($threshold) {
            if ($threshold === 7) { // กรณี "มากกว่า 6 เดือน"
                return $item['unpaid_count'] > 6;
            }
            return $item['unpaid_count'] >= $threshold;
        });
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
                        <h1 class="h3 mb-0 text-gray-800">รายงานสรุปบ้านค้างชำระตามระยะเวลา</h1>
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="<?php echo $_SESSION['dashboard_page'] ?>">Home</a></li>
                            <li class="breadcrumb-item">รายงาน</li>
                            <li class="breadcrumb-item active" aria-current="page">สรุปค้างชำระ</li>
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
                                            <label for="start_year" class="form-label font-weight-bold">ปี เริ่มต้น (พ.ศ.)</label>
                                            <select name="start_year" id="start_year" class="form-select">
                                                <?php
                                                $current_y = (int)date('Y');
                                                for($y = 2020; $y <= $current_y; $y++) {
                                                    $sel = ($y == $start_year) ? 'selected' : '';
                                                    echo "<option value=\"$y\" $sel>" . ($y + 543) . "</option>";
                                                }
                                                ?>
                                            </select>
                                        </div>

                                        <div class="col-md-2">
                                            <label for="year" class="form-label font-weight-bold">ปี อ้างอิง (พ.ศ.)</label>
                                            <select name="year" id="year" class="form-select">
                                                <?php
                                                $current_y = (int)date('Y');
                                                for($y = $current_y - 5; $y <= $current_y + 1; $y++) {
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

                                        <div class="col-md-3">
                                            <label for="category" class="form-label font-weight-bold">กลุ่มระยะเวลา</label>
                                            <select name="category" id="category" class="form-select">
                                                <option value="">ทั้งหมด</option>
                                                <option value="1" <?= ($filter_category == '1') ? 'selected' : '' ?>>ค้าง 1 เดือนขึ้นไป</option>
                                                <option value="3" <?= ($filter_category == '3') ? 'selected' : '' ?>>ค้าง 3 เดือนขึ้นไป</option>
                                                <option value="5" <?= ($filter_category == '5') ? 'selected' : '' ?>>ค้าง 5 เดือนขึ้นไป</option>
                                                <option value="6" <?= ($filter_category == '6') ? 'selected' : '' ?>>ค้าง 6 เดือนขึ้นไป</option>
                                                <option value="7" <?= ($filter_category == '7') ? 'selected' : '' ?>>ค้างมากกว่า 6 เดือน</option>
                                            </select>
                                        </div>

                                        <div class="col-md-2">
                                            <button type="submit" class="btn btn-primary w-100"><i class="bi bi-search"></i> ค้นหา</button>
                                        </div>
                                    </form>

                                    <hr>

                                    <div class="mt-4">
                                        <h5><i class="bi bi-file-earmark-text-fill"></i> ข้อมูล ณ เดือน <span class="text-danger font-weight-bold"><?= $thai_months[$ref_month - 1] ?></span> ปี <strong><?= $ref_year + 543 ?></strong></h5>

                                        <!-- สรุปยอด -->
                                        <div class="row mb-4">
                                            <div class="col-xl-3 col-md-6 mb-3">
                                                <div class="card border-left-info shadow h-100 py-2">
                                                    <div class="card-body">
                                                        <div class="row no-gutters align-items-center">
                                                            <div class="col mr-2">
                                                                <div class="text-xs font-weight-bold text-info text-uppercase mb-1">ค้างชำระ 1-2 เดือน</div>
                                                                <div class="h5 mb-0 font-weight-bold text-gray-800"><?= number_format($summary['cat_1']['houses']) ?> หลัง</div>
                                                            </div>
                                                            <div class="col-auto">
                                                                <i class="fas fa-clock fa-2x text-gray-300"></i>
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
                                                                <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">ค้างชำระ 3-5 เดือน</div>
                                                                <div class="h5 mb-0 font-weight-bold text-gray-800"><?= number_format($summary['cat_3']['houses']) ?> หลัง</div>
                                                            </div>
                                                            <div class="col-auto">
                                                                <i class="fas fa-exclamation-triangle fa-2x text-gray-300"></i>
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
                                                                <div class="text-xs font-weight-bold text-danger text-uppercase mb-1">ค้างชำระ 6 เดือน</div>
                                                                <div class="h5 mb-0 font-weight-bold text-gray-800"><?= number_format($summary['cat_6']['houses']) ?> หลัง</div>
                                                            </div>
                                                            <div class="col-auto">
                                                                <i class="fas fa-file-invoice-dollar fa-2x text-gray-300"></i>
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
                                                                <div class="text-xs font-weight-bold text-dark text-uppercase mb-1">มากกว่า 6 เดือน</div>
                                                                <div class="h5 mb-0 font-weight-bold text-gray-800"><?= number_format($summary['cat_over_6']['houses']) ?> หลัง</div>
                                                            </div>
                                                            <div class="col-auto">
                                                                <i class="fas fa-gavel fa-2x text-gray-300"></i>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="table-responsive">
                                            <table id="reportTable" class="table table-striped table-bordered" style="width:100%">
                                                <thead class="thead-dark">
                                                    <tr>
                                                        <th class="text-center">ลำดับ</th>
                                                        <th>บ้านเลขที่</th>
                                                        <th>ชื่อผู้ติดต่อ</th>
                                                        <th>เบอร์โทรศัพท์</th>
                                                        <th class="text-right">ยอดค้างรวม</th>
                                                        <th>รายการเดือนที่ค้างทั้งหมด</th>
                                                        <th class="text-center">ประวัติ</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <?php $i = 1; foreach ($overdue_data as $row): ?>
                                                        <tr>
                                                            <td class="text-center"><?= $i++ ?></td>
                                                            <td class="font-weight-bold"><?= htmlspecialchars($row['house_number'] ?? '') ?></td>
                                                            <td><?= htmlspecialchars($row['contact_name'] ?? '') ?></td>
                                                            <td><?= htmlspecialchars($row['phone_number'] ?? '') ?></td>
                                                            <td class="text-right font-weight-bold text-danger"><?= number_format($row['total_amount'] ?? 0, 2) ?></td>
                                                            <td class="small">
                                                                <?php 
                                                                if (is_array($row['unpaid_list'])) {
                                                                    foreach ($row['unpaid_list'] as $m_y) {
                                                                        echo '<span class="badge badge-light border mb-1 mr-1">' . htmlspecialchars($m_y) . '</span>';
                                                                    }
                                                                }
                                                                ?>
                                                            </td>
                                                            <td class="text-center">
                                                                <button type="button" class="btn btn-outline-info btn-sm view-history" data-house="<?= htmlspecialchars($row['house_number'] ?? '') ?>">
                                                                    <i class="fas fa-history"></i>
                                                                </button>
                                                            </td>
                                                        </tr>
                                                    <?php endforeach; ?>
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

    <!-- Individual House Payment History Modal -->
    <div class="modal fade" id="houseHistoryModal" tabindex="-1" role="dialog" aria-labelledby="houseHistoryModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title" id="houseHistoryModalLabel"><i class="fas fa-history"></i> ประวัติการชำระเงิน: <span id="modalHouseNumber"></span></h5>
                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div id="historyLoading" class="text-center d-none">
                        <div class="spinner-border text-primary" role="status">
                            <span class="sr-only">Loading...</span>
                        </div>
                        <p>กำลังโหลดข้อมูล...</p>
                    </div>
                    <div id="historyContent">
                        <table class="table table-sm table-striped table-bordered">
                            <thead class="bg-light">
                                <tr>
                                    <th class="text-center">วันที่ชำระ</th>
                                    <th class="text-center">งวดที่ชำระ</th>
                                    <th class="text-right">ยอดเงิน (บาท)</th>
                                </tr>
                            </thead>
                            <tbody id="historyTableBody">
                                <!-- Data will be loaded here -->
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">ปิด</button>
                </div>
            </div>
        </div>
    </div>

    <script src="vendor/jquery/jquery.min.js"></script>
    <script src="vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
    <script src="vendor/jquery-easing/jquery.easing.min.js"></script>
    <script src="js/myadmin.min.js"></script>
    
    <script src="https://cdn.datatables.net/2.0.8/js/dataTables.js"></script>
    <script src="https://cdn.datatables.net/2.0.8/js/dataTables.bootstrap5.js"></script>
    <script src="https://cdn.datatables.net/buttons/3.0.2/js/dataTables.buttons.js"></script>
    <script src="https://cdn.datatables.net/buttons/3.0.2/js/buttons.bootstrap5.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/pdfmake.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/vfs_fonts.js"></script>
    <script src="https://cdn.datatables.net/buttons/3.0.2/js/buttons.html5.js"></script>
    <script src="https://cdn.datatables.net/buttons/3.0.2/js/buttons.print.js"></script>

    <script>
        $(document).ready(function () {
            $('#reportTable').DataTable({
                language: { url: "//cdn.datatables.net/plug-ins/2.0.8/i18n/th.json" },
                pageLength: 5,
                lengthMenu: [5, 10, 25, 50, 100],
                dom: "<'row'<'col-sm-12 col-md-6'l><'col-sm-12 col-md-6'f>>" +
                     "<'row'<'col-sm-12'tr>>" +
                     "<'row'<'col-sm-12 col-md-5'i><'col-sm-12 col-md-7'p>>" +
                     "<'row'<'col-sm-12'B>>",
                buttons: [
                    {
                        extend: 'excelHtml5',
                        text: '<i class="fas fa-file-excel"></i> Export to Excel',
                        className: 'btn btn-success btn-sm',
                        title: 'รายงานค้างชำระแยกตามระยะเวลา'
                    }
                ]
            });

            $('.view-history').click(function() {
                const house = $(this).data('house');
                $('#modalHouseNumber').text(house);
                $('#historyTableBody').html('');
                $('#historyLoading').removeClass('d-none');
                $('#houseHistoryModal').modal('show');

                $.ajax({
                    url: 'model/get_house_payment_history.php',
                    type: 'GET',
                    data: { house_number: house },
                    dataType: 'json',
                    success: function(response) {
                        $('#historyLoading').addClass('d-none');
                        if (response.status === 'success') {
                            let html = '';
                            if (response.data.length > 0) {
                                response.data.forEach(function(item) {
                                    html += `<tr>
                                        <td class="text-center">${item.payment_date}</td>
                                        <td class="text-center">${item.period}</td>
                                        <td class="text-right text-success">${item.amount.toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2})}</td>
                                    </tr>`;
                                });
                            } else {
                                html = '<tr><td colspan="3" class="text-center text-muted">ไม่พบประวัติการชำระเงิน</td></tr>';
                            }
                            $('#historyTableBody').html(html);
                        } else {
                            alert('เกิดข้อผิดพลาด: ' + (response.message || 'ไม่ทราบสาเหตุ'));
                        }
                    },
                    error: function() {
                        $('#historyLoading').addClass('d-none');
                        alert('ไม่สามารถดึงข้อมูลได้');
                    }
                });
            });
        });
    </script>
    </body>
    </html>
<?php } ?>
