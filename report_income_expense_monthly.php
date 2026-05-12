<?php
include('includes/Header.php');
if (strlen($_SESSION['alogin']) == "" || strlen($_SESSION['house_number']) == "") {
    header("Location: index.php");
} else {
    include('config/connect_db.php');

    $selected_year = isset($_GET['year']) ? (int)$_GET['year'] : date('Y');
    $selected_month = isset($_GET['month']) ? (int)$_GET['month'] : (int)date('m');

    $thai_months = [
        "มกราคม", "กุมภาพันธ์", "มีนาคม", "เมษายน", "พฤษภาคม", "มิถุนายน",
        "กรกฎาคม", "สิงหาคม", "กันยายน", "ตุลาคม", "พฤศจิกายน", "ธันวาคม"
    ];

    // expense category mapping: display_name => [category_id(s) or description keywords]
    $expense_category_map = [
        'รปภ' => ['category_ids' => ['T008'], 'desc_keywords' => ['รปภ', 'รักษาความปลอดภัย']],
        'สวน' => ['category_ids' => [], 'desc_keywords' => ['ตัดหญ้า', 'ต้นไม้', 'สวน', 'สนาม']],
        'น้ำไฟ' => ['category_ids' => ['T001', 'T002'], 'desc_keywords' => []],
    ];

    $prev_year = $selected_year - 1;
    // ----- รายรับ: ค่าส่วนกลาง (จาก ims_house_payment) -----
    $sql_income_cf = "SELECT COALESCE(SUM(amount), 0) AS total
                      FROM ims_house_payment
                      WHERE payment_status = 'Y'
                        AND (
                          (period_month_start <= period_month_to
                           AND period_year = :year1
                           AND period_month_start <= :month1
                           AND period_month_to >= :month1)
                          OR
                          (period_month_start > period_month_to
                           AND (
                             (period_year = :year2 AND period_month_start <= :month2)
                             OR
                             (period_year = :prev_year AND period_month_to >= :month3)
                           )
                          )
                        )";
    $stmt = $conn->prepare($sql_income_cf);
    $stmt->bindValue(':year1', $selected_year, PDO::PARAM_INT);
    $stmt->bindValue(':month1', $selected_month, PDO::PARAM_INT);
    $stmt->bindValue(':year2', $selected_year, PDO::PARAM_INT);
    $stmt->bindValue(':month2', $selected_month, PDO::PARAM_INT);
    $stmt->bindValue(':prev_year', $prev_year, PDO::PARAM_INT);
    $stmt->bindValue(':month3', $selected_month, PDO::PARAM_INT);
    $stmt->execute();
    $income_common_fee = (float)$stmt->fetch(PDO::FETCH_ASSOC)['total'];

    $month_str = str_pad($selected_month, 2, '0', STR_PAD_LEFT);

    // ----- รายรับ: ค่าปรับ (จาก ims_reciepts ที่หมวดหมู่เกี่ยวข้องกับค่าปรับ) -----
    $sql_income_fine = "SELECT COALESCE(SUM(r.amount), 0) AS total
                        FROM ims_reciepts r
                        LEFT JOIN ims_category c ON r.category_id = c.category_id
                        WHERE r.approve_status = 'Y'
                          AND r.rec_year = :year
                          AND r.rec_month = :month
                          AND (c.category_name LIKE '%ปรับ%' OR r.description LIKE '%ปรับ%')";
    $stmt = $conn->prepare($sql_income_fine);
    $stmt->bindParam(':year', $selected_year, PDO::PARAM_STR);
    $stmt->bindParam(':month', $month_str, PDO::PARAM_STR);
    $stmt->execute();
    $income_fine = (float)$stmt->fetch(PDO::FETCH_ASSOC)['total'];

    // ----- รายรับอื่นๆ (จาก ims_reciepts ที่เหลือ) -----
    $sql_income_other = "SELECT COALESCE(SUM(r.amount), 0) AS total
                         FROM ims_reciepts r
                         LEFT JOIN ims_category c ON r.category_id = c.category_id
                         WHERE r.approve_status = 'Y'
                           AND r.rec_year = :year
                           AND r.rec_month = :month
                           AND (c.category_name NOT LIKE '%ปรับ%' OR c.category_name IS NULL)
                           AND (r.description NOT LIKE '%ปรับ%' OR r.description IS NULL)";
    $stmt = $conn->prepare($sql_income_other);
    $stmt->bindParam(':year', $selected_year, PDO::PARAM_STR);
    $stmt->bindParam(':month', $month_str, PDO::PARAM_STR);
    $stmt->execute();
    $income_other = (float)$stmt->fetch(PDO::FETCH_ASSOC)['total'];

    $total_income = $income_common_fee + $income_fine + $income_other;

    // ----- รายจ่ายแยกหมวดหมู่ -----
    $expenses_by_cat = [];
    foreach ($expense_category_map as $display_name => $rules) {
        $conditions = [];
        $params = [':year' => $selected_year, ':month' => $month_str];

        if (!empty($rules['category_ids'])) {
            $placeholders = [];
            foreach ($rules['category_ids'] as $i => $cid) {
                $key = ':cid_' . $i;
                $placeholders[] = $key;
                $params[$key] = $cid;
            }
            $conditions[] = 'e.category_id IN (' . implode(',', $placeholders) . ')';
        }
        if (!empty($rules['desc_keywords'])) {
            $kw_conds = [];
            foreach ($rules['desc_keywords'] as $i => $kw) {
                $key = ':kw_' . $i;
                $kw_conds[] = 'e.description LIKE ' . $key;
                $params[$key] = '%' . $kw . '%';
            }
            $conditions[] = '(' . implode(' OR ', $kw_conds) . ')';
        }

        $where = implode(' OR ', $conditions);
        $sql_exp = "SELECT COALESCE(SUM(e.amount), 0) AS total
                    FROM ims_expenses e
                    WHERE e.approve_status = 'Y'
                      AND e.exp_year = :year
                      AND e.exp_month = :month
                      AND (" . $where . ")";
        $stmt = $conn->prepare($sql_exp);
        foreach ($params as $key => $val) {
            $stmt->bindValue($key, $val, PDO::PARAM_STR);
        }
        $stmt->execute();
        $expenses_by_cat[$display_name] = (float)$stmt->fetch(PDO::FETCH_ASSOC)['total'];
    }

    // ----- รายจ่ายรวมทั้งหมด (เพื่อคำนวณ "อื่นๆ") -----
    $sql_exp_all = "SELECT COALESCE(SUM(e.amount), 0) AS total
                    FROM ims_expenses e
                    WHERE e.approve_status = 'Y'
                      AND e.exp_year = :year
                      AND e.exp_month = :month";
    $stmt = $conn->prepare($sql_exp_all);
    $stmt->bindParam(':year', $selected_year, PDO::PARAM_STR);
    $stmt->bindParam(':month', $month_str, PDO::PARAM_STR);
    $stmt->execute();
    $total_expense = (float)$stmt->fetch(PDO::FETCH_ASSOC)['total'];

    $categorized_expense = array_sum($expenses_by_cat);
    $expense_other = max(0, $total_expense - $categorized_expense);

    $net_profit = $total_income - $total_expense;
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
                        <h1 class="h3 mb-0 text-gray-800"><?php echo urldecode($_GET['s'] ?? 'รายงานรายรับ-รายจ่ายประจำเดือน') ?></h1>
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="<?php echo $_SESSION['dashboard_page'] ?>">Home</a></li>
                            <li class="breadcrumb-item"><?php echo urldecode($_GET['m'] ?? 'รายงาน') ?></li>
                            <li class="breadcrumb-item active" aria-current="page"><?php echo urldecode($_GET['s'] ?? 'รายงานรายรับ-รายจ่าย') ?></li>
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
                                            <label for="year" class="form-label font-weight-bold">ปี (พ.ศ.)</label>
                                            <select name="year" id="year" class="form-select">
                                                <?php
                                                $sql_year = "SELECT DISTINCT period_year FROM ims_house_payment ORDER BY period_year DESC";
                                                $stmt_y = $conn->prepare($sql_year);
                                                $stmt_y->execute();
                                                while ($row_y = $stmt_y->fetch(PDO::FETCH_ASSOC)) {
                                                    $y = $row_y['period_year'];
                                                    $sel = ($y == $selected_year) ? 'selected' : '';
                                                    echo "<option value=\"$y\" $sel>" . ($y + 543) . "</option>";
                                                }
                                                ?>
                                            </select>
                                        </div>

                                        <div class="col-md-3">
                                            <label for="month" class="form-label font-weight-bold">เดือน</label>
                                            <select name="month" id="month" class="form-select">
                                                <?php for ($m = 1; $m <= 12; $m++): ?>
                                                    <option value="<?= $m ?>" <?= ($m == $selected_month) ? 'selected' : '' ?>>
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
                                        <h5><i class="bi bi-file-earmark-text-fill"></i> รายงานรายรับ-รายจ่าย ประจำเดือน <span class="text-danger font-weight-bold"><?= $thai_months[$selected_month - 1] ?></span> ปี <strong><?= $selected_year + 543 ?></strong></h5>

                                        <button type="button" class="btn btn-success mb-3" onclick="exportToExcel()">
                                            <i class="bi bi-file-earmark-excel"></i> Export Excel
                                        </button>

                                        <div class="table-responsive mt-3">
                                            <table class="table table-striped table-bordered" style="width:100%">
                                                <thead class="thead-light">
                                                    <tr>
                                                        <th class="text-center" style="width:40%">รายการ</th>
                                                        <th class="text-center" style="width:30%">หมวดหมู่</th>
                                                        <th class="text-center" style="width:30%">จำนวนเงิน (บาท)</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <tr class="table-primary">
                                                        <td colspan="3"><b>รายรับ (Income)</b></td>
                                                    </tr>
                                                    <tr>
                                                        <td>รายรับ</td>
                                                        <td>ค่าส่วนกลาง</td>
                                                        <td class="text-end font-weight-bold text-success"><?= number_format($income_common_fee, 2) ?></td>
                                                    </tr>
                                                    <tr>
                                                        <td>รายรับ</td>
                                                        <td>ค่าปรับ</td>
                                                        <td class="text-end font-weight-bold text-success"><?= number_format($income_fine, 2) ?></td>
                                                    </tr>
                                                    <tr>
                                                        <td>รายรับ</td>
                                                        <td>รายรับอื่นๆ</td>
                                                        <td class="text-end font-weight-bold text-success"><?= number_format($income_other, 2) ?></td>
                                                    </tr>
                                                    <tr class="table-success">
                                                        <td colspan="2" class="text-end"><b>รวมรายรับ</b></td>
                                                        <td class="text-end font-weight-bold"><?= number_format($total_income, 2) ?></td>
                                                    </tr>

                                                    <tr class="table-danger">
                                                        <td colspan="3"><b>รายจ่าย (Expenses)</b></td>
                                                    </tr>
                                                    <?php foreach ($expenses_by_cat as $cat => $val): ?>
                                                    <tr>
                                                        <td>รายจ่าย</td>
                                                        <td><?= htmlspecialchars($cat) ?></td>
                                                        <td class="text-end font-weight-bold text-danger"><?= number_format($val, 2) ?></td>
                                                    </tr>
                                                    <?php endforeach; ?>
                                                    <tr>
                                                        <td>รายจ่าย</td>
                                                        <td>อื่นๆ</td>
                                                        <td class="text-end font-weight-bold text-danger"><?= number_format($expense_other, 2) ?></td>
                                                    </tr>
                                                    <tr class="table-danger">
                                                        <td colspan="2" class="text-end"><b>รวมรายจ่าย</b></td>
                                                        <td class="text-end font-weight-bold"><?= number_format($total_expense, 2) ?></td>
                                                    </tr>

                                                    <tr class="<?= $net_profit >= 0 ? 'table-info' : 'table-warning' ?>">
                                                        <td colspan="2" class="text-end"><b>รายได้สุทธิ (Net)</b></td>
                                                        <td class="text-end font-weight-bold" style="font-size:1.1em; color:<?= $net_profit >= 0 ? 'green' : 'red' ?>">
                                                            <?= number_format($net_profit, 2) ?>
                                                        </td>
                                                    </tr>
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

    <!-- Export form -->
    <form method="post" action="" id="exportForm" style="display:none">
        <input type="hidden" name="export_excel" value="1">
        <input type="hidden" name="year" value="<?= $selected_year ?>">
        <input type="hidden" name="month" value="<?= $selected_month ?>">
    </form>

    <script src="vendor/jquery/jquery.min.js"></script>
    <script src="vendor/bootstrap/js/bootstrap.bundle.min.js"></script>

    <script>
        function exportToExcel() {
            var rows = [];
            // header
            rows.push(['รายการ', 'หมวดหมู่', 'จำนวนเงิน (บาท)']);
            // income section
            rows.push(['รายรับ (Income)', '', '']);
            rows.push(['รายรับ', 'ค่าส่วนกลาง', '<?= $income_common_fee ?>']);
            rows.push(['รายรับ', 'ค่าปรับ', '<?= $income_fine ?>']);
            rows.push(['รายรับ', 'รายรับอื่นๆ', '<?= $income_other ?>']);
            rows.push(['รวมรายรับ', '', '<?= $total_income ?>']);
            // expense section
            rows.push(['รายจ่าย (Expenses)', '', '']);
            <?php foreach ($expenses_by_cat as $cat => $val): ?>
            rows.push(['รายจ่าย', '<?= $cat ?>', '<?= $val ?>']);
            <?php endforeach; ?>
            rows.push(['รายจ่าย', 'อื่นๆ', '<?= $expense_other ?>']);
            rows.push(['รวมรายจ่าย', '', '<?= $total_expense ?>']);
            rows.push(['รายได้สุทธิ (Net)', '', '<?= $net_profit ?>']);

            var csv = "\uFEFF";
            rows.forEach(function (row) {
                csv += row.join(',') + '\n';
            });

            var blob = new Blob([csv], { type: 'text/csv;charset=utf-8;' });
            var link = document.createElement('a');
            link.href = URL.createObjectURL(blob);
            link.download = 'รายงานรายรับ-รายจ่าย-<?= $thai_months[$selected_month - 1] ?>-<?= $selected_year + 543 ?>.csv';
            link.click();
        }
    </script>
    </body>
    </html>
<?php } ?>
