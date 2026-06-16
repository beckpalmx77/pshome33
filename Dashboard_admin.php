<?php
include('includes/Header.php');
if (strlen($_SESSION['alogin']) == "" || strlen($_SESSION['department_id']) == "") {
    header("Location: index.php");
    exit();
}

include('config/connect_db.php');

// ดึงข้อมูลจำนวนบ้านทั้งหมดและจำนวนบ้านที่ลงทะเบียนในคิวรีเดียว
$sql_house_data = "
    SELECT 
        SUM(CASE WHEN h.house_number LIKE '67%' THEN 1 ELSE 0 END) AS count_67,
        SUM(CASE WHEN h.house_number LIKE '68%' THEN 1 ELSE 0 END) AS count_68,
        SUM(CASE WHEN hm.house_number LIKE '67%' THEN 1 ELSE 0 END) AS total_67_master,
        SUM(CASE WHEN hm.house_number LIKE '68%' THEN 1 ELSE 0 END) AS total_68_master
    FROM ims_house_master hm
    LEFT JOIN ims_house h ON h.house_number = hm.house_number;
";
$query_house_data = $conn->prepare($sql_house_data);
$query_house_data->execute();
$results_house_data = $query_house_data->fetch(PDO::FETCH_OBJ);

$count_67 = $results_house_data->count_67 ?? 0;
$count_68 = $results_house_data->count_68 ?? 0;
$count_67_house_master = $results_house_data->total_67_master ?? 0;
$count_68_house_master = $results_house_data->total_68_master ?? 0;

$total_house = $count_67 + $count_68;
$all_total_house = $count_67_house_master + $count_68_house_master;

// ดึงจำนวนสมาชิกทั้งหมด
$sql_member = "SELECT COUNT(*) AS total_user FROM ims_user WHERE account_type = 'user'";
$query_member = $conn->prepare($sql_member);
$query_member->execute();
$result_member = $query_member->fetch(PDO::FETCH_OBJ);
$total_user = $result_member->total_user ?? 0;

// คำนวณค่าที่ใช้ในหน้าเว็บ
$unregistered_house = $all_total_house - $total_house;
$percent_chk = ($all_total_house > 0) ? ($total_house / $all_total_house) * 100 : 0;
$cardClass = ($percent_chk >= 50) ? 'border-left-success' : 'border-left-danger';
$percent_67 = ($count_67_house_master > 0) ? ($count_67 / $count_67_house_master) * 100 : 0;
$percent_68 = ($count_68_house_master > 0) ? ($count_68 / $count_68_house_master) * 100 : 0;

// ดึงยอดรวมค่าส่วนกลาง
$sql_sum_house_data = " SELECT SUM(common_fee) AS common_fee FROM ims_house_master ";
$query_sum_house_data = $conn->prepare($sql_sum_house_data);
$query_sum_house_data->execute();
$result_sum_house_data = $query_sum_house_data->fetch(PDO::FETCH_OBJ);
$total_common_fee = $result_sum_house_data->common_fee ?? 0; // เก็บค่าใส่ตัวแปรเพื่อให้ใช้ง่าย

// ดึงยอดเงินชำระค่าส่วนกลาง ของเดือนปัจจุบัน ที่ยืนยันการจัดเก็บได้
$curr_month = (int)date('m');
$curr_year = (int)date('Y');
$thai_months = [
    1 => "มกราคม", 2 => "กุมภาพันธ์", 3 => "มีนาคม", 4 => "เมษายน",
    5 => "พฤษภาคม", 6 => "มิถุนายน", 7 => "กรกฎาคม", 8 => "สิงหาคม",
    9 => "กันยายน", 10 => "ตุลาคม", 11 => "พฤศจิกายน", 12 => "ธันวาคม"
];

$sql_curr_month_collected = "
    SELECT 
        SUM(
            CASE
                WHEN period_month_to = period_month_start THEN amount
                WHEN period_month_to > period_month_start THEN ROUND(amount / (period_month_to - period_month_start + 1), 2)
                ELSE 0
            END
        ) AS total_collected
    FROM ims_house_payment
    WHERE period_year = :year 
      AND payment_status = 'Y'
      AND :month BETWEEN period_month_start AND period_month_to
";
$query_curr_month_collected = $conn->prepare($sql_curr_month_collected);
$query_curr_month_collected->execute([
    ':year' => $curr_year,
    ':month' => $curr_month
]);
$result_curr_month_collected = $query_curr_month_collected->fetch(PDO::FETCH_OBJ);
$total_collected_curr_month = $result_curr_month_collected->total_collected ?? 0;

// ดึงจำนวนบ้านที่จัดเก็บค่าส่วนกลางของเดือนปัจจุบันได้
$sql_curr_month_houses = "
    SELECT COUNT(DISTINCT house_number) AS houses_count
    FROM ims_house_payment
    WHERE period_year = :year 
      AND payment_status = 'Y'
      AND :month BETWEEN period_month_start AND period_month_to
";
$query_curr_month_houses = $conn->prepare($sql_curr_month_houses);
$query_curr_month_houses->execute([
    ':year' => $curr_year,
    ':month' => $curr_month
]);
$result_curr_month_houses = $query_curr_month_houses->fetch(PDO::FETCH_OBJ);
$total_houses_curr_month = $result_curr_month_houses->houses_count ?? 0;

// ดึงข้อมูลบ้านที่ค้างชำระของเดือนปัจจุบัน
$sql_unpaid_houses = "
    SELECT ROW_NUMBER() OVER (ORDER BY m.alley ASC, m.house_number ASC) AS row_num,
           m.house_number, m.alley, m.area_size, m.common_fee, h.contact_name, h.phone_number
    FROM ims_house_master m
    LEFT JOIN ims_house h ON m.house_number = h.house_number
    WHERE m.status = 'Y' 
      AND m.house_number LIKE '6%'
      AND m.house_number NOT IN (
          SELECT DISTINCT house_number 
          FROM ims_house_payment 
          WHERE period_year = :year 
            AND payment_status = 'Y' 
            AND :month BETWEEN period_month_start AND period_month_to
      )
    ORDER BY m.alley ASC, m.house_number ASC;
";
$query_unpaid_houses = $conn->prepare($sql_unpaid_houses);
$query_unpaid_houses->execute([
    ':year' => $curr_year,
    ':month' => $curr_month
]);
$unpaid_houses_list = $query_unpaid_houses->fetchAll(PDO::FETCH_ASSOC);

// ดึงข้อมูลบ้านที่ชำระแล้วของเดือนปัจจุบัน
$sql_paid_houses = "
    SELECT ROW_NUMBER() OVER (ORDER BY m.alley ASC, p.house_number ASC) AS row_num,
           p.house_number, m.alley, h.contact_name, h.phone_number, 
           CASE
                WHEN p.period_month_to = p.period_month_start THEN p.amount
                WHEN p.period_month_to > p.period_month_start THEN ROUND(p.amount / (p.period_month_to - p.period_month_start + 1), 2)
                ELSE 0
           END AS common_fee,
           p.payment_date
    FROM ims_house_payment p
    LEFT JOIN ims_house_master m ON p.house_number = m.house_number
    LEFT JOIN ims_house h ON p.house_number = h.house_number
    WHERE p.period_year = :year 
      AND p.payment_status = 'Y' 
      AND :month BETWEEN p.period_month_start AND p.period_month_to
    ORDER BY m.alley ASC, p.house_number ASC;
";
$query_paid_houses = $conn->prepare($sql_paid_houses);
$query_paid_houses->execute([
    ':year' => $curr_year,
    ':month' => $curr_month
]);
$paid_houses_list = $query_paid_houses->fetchAll(PDO::FETCH_ASSOC);

// ดึงข้อมูลรายรับของเดือนปัจจุบัน
$sql_monthly_income = "
    SELECT ROW_NUMBER() OVER (ORDER BY reciept_date ASC) AS row_num,
           doc_id, reciept_date, category_name, description, amount
    FROM v_ims_reciepts
    WHERE CAST(rec_month AS UNSIGNED) = :month AND rec_year = :year
    ORDER BY reciept_date ASC;
";
$query_monthly_income = $conn->prepare($sql_monthly_income);
$query_monthly_income->execute([':month' => $curr_month, ':year' => $curr_year]);
$monthly_income_list = $query_monthly_income->fetchAll(PDO::FETCH_ASSOC);

$total_monthly_income = 0;
foreach ($monthly_income_list as $row) {
    $total_monthly_income += $row['amount'];
}

// ดึงข้อมูลรายจ่ายของเดือนปัจจุบัน
$sql_monthly_expense = "
    SELECT ROW_NUMBER() OVER (ORDER BY doc_date ASC) AS row_num,
           doc_no, doc_date, supplier_name, purpose, total_amount
    FROM ims_payment_voucher
    WHERE CAST(doc_month AS UNSIGNED) = :month AND doc_year = :year
    ORDER BY doc_date ASC;
";
$query_monthly_expense = $conn->prepare($sql_monthly_expense);
$query_monthly_expense->execute([':month' => $curr_month, ':year' => $curr_year]);
$monthly_expense_list = $query_monthly_expense->fetchAll(PDO::FETCH_ASSOC);

$total_monthly_expense = 0;
foreach ($monthly_expense_list as $row) {
    $total_monthly_expense += $row['total_amount'];
}

// คำนวณเดือนและปี ย้อนหลัง 1 เดือน สำหรับเงินเดือนพนักงาน
$prev_month = $curr_month - 1;
$prev_year = $curr_year;
if ($prev_month == 0) {
    $prev_month = 12;
    $prev_year--;
}

// ดึงข้อมูลเงินเดือนพนักงานของเดือนที่แล้ว
$sql_staff_salary = "
    SELECT ROW_NUMBER() OVER (ORDER BY doc_date ASC) AS row_num,
           doc_no, doc_date, employee_fullname, total_amount
    FROM v_ims_payroll
    WHERE CAST(payroll_month AS UNSIGNED) = :month AND payroll_year = :year
    ORDER BY doc_date ASC;
";
$query_staff_salary = $conn->prepare($sql_staff_salary);
$query_staff_salary->execute([':month' => $prev_month, ':year' => $prev_year]);
$staff_salary_list = $query_staff_salary->fetchAll(PDO::FETCH_ASSOC);

$total_staff_salary = 0;
foreach ($staff_salary_list as $row) {
    $total_staff_salary += $row['total_amount'];
}

// --- ดึงข้อมูลสรุปสติกเกอร์ ---
// 1. จำนวนรถทั้งหมด (รับ + ยังไม่ได้รับ)
$sql_all_cars = "SELECT 
    SUM(
        (CASE WHEN car_no1 IS NOT NULL AND car_no1 <> '' THEN 1 ELSE 0 END) +
        (CASE WHEN car_no2 IS NOT NULL AND car_no2 <> '' THEN 1 ELSE 0 END) +
        (CASE WHEN car_no3 IS NOT NULL AND car_no3 <> '' THEN 1 ELSE 0 END) +
        (CASE WHEN car_no4 IS NOT NULL AND car_no4 <> '' THEN 1 ELSE 0 END) +
        (CASE WHEN car_no5 IS NOT NULL AND car_no5 <> '' THEN 1 ELSE 0 END) +
        (CASE WHEN car_no6 IS NOT NULL AND car_no6 <> '' THEN 1 ELSE 0 END) +
        (CASE WHEN car_no7 IS NOT NULL AND car_no7 <> '' THEN 1 ELSE 0 END) +
        (CASE WHEN car_no8 IS NOT NULL AND car_no8 <> '' THEN 1 ELSE 0 END)
    ) AS total_cars_count
FROM ims_house;";
$query_all_cars = $conn->prepare($sql_all_cars);
$query_all_cars->execute();
$result_all_cars = $query_all_cars->fetch(PDO::FETCH_OBJ);
$total_cars_count = $result_all_cars->total_cars_count ?? 0;

// 2. สรุปข้อมูลบ้านที่รับสติกเกอร์แล้ว
$sql_sticker_summary = "
    SELECT 
        COUNT(*) as total_house_received,
        SUM(car_count) as total_cars_received,
        SUM(CASE WHEN car_count > 2 THEN (car_count - 2) * 100 ELSE 0 END) as total_extra_fee
    FROM (
        SELECT 
            (CASE WHEN car_no1 IS NOT NULL AND car_no1 <> '' THEN 1 ELSE 0 END) +
            (CASE WHEN car_no2 IS NOT NULL AND car_no2 <> '' THEN 1 ELSE 0 END) +
            (CASE WHEN car_no3 IS NOT NULL AND car_no3 <> '' THEN 1 ELSE 0 END) +
            (CASE WHEN car_no4 IS NOT NULL AND car_no4 <> '' THEN 1 ELSE 0 END) +
            (CASE WHEN car_no5 IS NOT NULL AND car_no5 <> '' THEN 1 ELSE 0 END) +
            (CASE WHEN car_no6 IS NOT NULL AND car_no6 <> '' THEN 1 ELSE 0 END) +
            (CASE WHEN car_no7 IS NOT NULL AND car_no7 <> '' THEN 1 ELSE 0 END) +
            (CASE WHEN car_no8 IS NOT NULL AND car_no8 <> '' THEN 1 ELSE 0 END) AS car_count
        FROM ims_house
        WHERE sticker_receive_status = 'Y'
    ) AS subquery
";
$query_sticker_summary = $conn->prepare($sql_sticker_summary);
$query_sticker_summary->execute();
$result_sticker_summary = $query_sticker_summary->fetch(PDO::FETCH_OBJ);

$total_house_received = $result_sticker_summary->total_house_received ?? 0;
$total_cars_received = $result_sticker_summary->total_cars_received ?? 0;
$total_extra_fee_received = $result_sticker_summary->total_extra_fee ?? 0;
// --- จบดึงข้อมูลสรุปสติกเกอร์ ---
?>

<!DOCTYPE html>
<html lang="th">
<body id="page-top">
<div id="wrapper">
    <?php include('includes/Side-Bar.php'); ?>

    <div id="content-wrapper" class="d-flex flex-column">
        <div id="content">
            <?php include('includes/Top-Bar.php'); ?>

            <div class="container-fluid"> <div class="card shadow mb-4">
                    <div class="card-header py-3 <?= $topbar_theme ?> text-white d-flex justify-content-between align-items-center">
                        <h6 class="m-0 font-weight-bold">
                            สรุปข้อมูลภาพรวม
                        </h6>
                        <a href="#collapseCard" data-toggle="collapse" role="button" aria-expanded="true"
                           aria-controls="collapseCard" id="toggleCollapse">
                            <i class="fas fa-chevron-down text-white" id="collapseIcon"></i>
                        </a>
                    </div>

                    <div class="collapse show" id="collapseCard">
                        <div class="card-body">
                            <div class="row">
                                <!-- Left Column: Pie Chart -->
                                <div class="col-xl-6 col-lg-12 mb-4">
                                    <div class="card <?= $cardClass ?> shadow h-100 py-2">
                                        <div class="card-body text-center d-flex flex-column justify-content-center">
                                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-3">
                                                เปรียบเทียบบ้านที่ลงทะเบียนแล้ว กับบ้านทั้งหมด
                                            </div>
                                            <canvas id="totalHousePieChart" style="max-width: 100%; max-height: 380px;"></canvas>
                                            <p class="text-center mt-3">
                                                จำนวนบ้านทั้งหมด: <strong><?= number_format($all_total_house) ?> หลัง</strong><br>
                                                ลงทะเบียนแล้ว: <strong><?= number_format($total_house) ?> หลัง</strong><br>
                                                คิดเป็น <strong><?= number_format($percent_chk, 2) ?>%</strong><br>
                                                <small><strong>(ประมาณการยอดรวมค่าส่วนกลาง กรณีที่จัดเก็บได้ 621 หลัง : <?= number_format($total_common_fee, 2) ?> บาท ต่อเดือน)</strong></small>
                                            </p>
                                        </div>
                                    </div>
                                </div>

                                <!-- Right Column: Info Cards Stacked -->
                                <div class="col-xl-6 col-lg-12">
                                    <!-- LINE OA & Registration Row -->
                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <div class="card border-left-danger shadow h-100 py-2">
                                                <div class="card-body">
                                                    <div class="text-xs font-weight-bold text-danger text-uppercase mb-1">LINE OA พฤกษา 33</div>
                                                    <div class="row no-gutters align-items-center">
                                                        <div class="col-auto mr-2">
                                                            <img src="img/icon/PS33-COMMONFEE-LINEOA.png" style="max-height: 80px;">
                                                        </div>
                                                        <div class="col">
                                                            <div class="small text-gray-800 font-weight-bold">Scan เพื่อเข้าระบบ</div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <div class="card border-left-primary shadow h-100 py-2">
                                                <div class="card-body">
                                                    <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">บ้านลงทะเบียน / Line User</div>
                                                    <div class="h6 mb-0 font-weight-bold text-gray-800">
                                                        <?= number_format($total_house) ?> หลัง / <?= number_format($total_user) ?> User
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Registration Groups -->
                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <div class="card border-left-info shadow h-100 py-2">
                                                <div class="card-body">
                                                    <div class="text-xs font-weight-bold text-info text-uppercase mb-1">บ้านกลุ่ม 67 (ลงทะเบียนแล้ว)</div>
                                                    <div class="h6 mb-0 font-weight-bold text-gray-800"><?= number_format($count_67) ?> / <?= number_format($count_67_house_master) ?></div>
                                                    <div class="progress progress-sm mr-2 mt-2">
                                                        <div class="progress-bar bg-info" role="progressbar" style="width: <?= $percent_67 ?>%"></div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <div class="card border-left-success shadow h-100 py-2">
                                                <div class="card-body">
                                                    <div class="text-xs font-weight-bold text-success text-uppercase mb-1">บ้านกลุ่ม 68 (ลงทะเบียนแล้ว)</div>
                                                    <div class="h6 mb-0 font-weight-bold text-gray-800"><?= number_format($count_68) ?> / <?= number_format($count_68_house_master) ?></div>
                                                    <div class="progress progress-sm mr-2 mt-2">
                                                        <div class="progress-bar bg-success" role="progressbar" style="width: <?= $percent_68 ?>%"></div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Common Fee Collected -->
                                    <div class="card border-left-warning shadow mb-3">
                                        <div class="card-body">
                                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">ยอดจัดเก็บค่าส่วนกลางเดือนปัจจุบัน</div>
                                            <div class="h5 mb-0 font-weight-bold text-gray-800"><?= number_format($total_collected_curr_month, 2) ?> บาท</div>
                                            <div class="d-flex justify-content-between align-items-center flex-wrap mt-2">
                                                <span class="badge bg-light text-warning border"><i class="fas fa-calendar-alt"></i> <?= $thai_months[$curr_month] ?></span>
                                                <div class="btn-group">
                                                    <button type="button" class="btn btn-outline-success btn-xs" data-toggle="modal" data-target="#paidHousesModal">ชำระแล้ว</button>
                                                    <button type="button" class="btn btn-outline-danger btn-xs" data-toggle="modal" data-target="#unpaidHousesModal">ค้างชำระ</button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Finance Summary -->
                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <div class="card border-left-success shadow h-100 py-2">
                                                <div class="card-body">
                                                    <div class="text-xs font-weight-bold text-success text-uppercase mb-1">รายรับอื่นๆ รวมเดือนนี้</div>
                                                    <div class="h6 mb-0 font-weight-bold text-gray-800"><?= number_format($total_monthly_income, 2) ?></div>
                                                    <button type="button" class="btn btn-outline-success btn-xs mt-2" data-toggle="modal" data-target="#monthlyIncomeModal">
                                                        <i class="fas fa-list"></i> รายละเอียด
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <div class="card border-left-danger shadow h-100 py-2">
                                                <div class="card-body">
                                                    <div class="row">
                                                        <div class="col-6">
                                                            <div class="text-xs font-weight-bold text-danger text-uppercase mb-1">รายจ่ายรวมเดือนนี้</div>
                                                            <div class="h6 mb-0 font-weight-bold text-gray-800"><?= number_format($total_monthly_expense, 2) ?></div>
                                                            <button type="button" class="btn btn-outline-danger btn-xs mt-2" data-toggle="modal" data-target="#monthlyExpenseModal">
                                                                <i class="fas fa-list"></i> รายละเอียด
                                                            </button>
                                                        </div>
                                                        <div class="col-6 border-left">
                                                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">ยอดจ่ายเงินเดือน (<?= $thai_months[$prev_month] ?>)</div>
                                                            <div class="h6 mb-0 font-weight-bold text-gray-800"><?= number_format($total_staff_salary, 2) ?></div>
                                                            <button type="button" class="btn btn-outline-info btn-xs mt-2" disabled>
                                                                <i class="fas fa-list"></i> รายละเอียด
                                                            </button>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Bottom Row: Bar Chart Full Width -->
                            <div class="row mt-2">
                                <div class="col-12">
                                    <div class="card shadow mb-4">
                                        <div class="card-header py-3 bg-light text-primary d-flex align-items-center">
                                            <h6 class="m-0 font-weight-bold"><i class="fas fa-chart-bar"></i> กราฟสรุปยอดรวมค่าส่วนกลางรายเดือน ปี พ.ศ. <?= date('Y') + 543 ?></h6>
                                        </div>
                                        <div class="card-body">
                                            <div class="chart-container" style="position: relative; height:40vh; width:100%">
                                                <canvas id="monthlyBarChart"></canvas>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Sticker Summary Section -->
                <div class="card shadow mb-4">
                    <div class="card-header py-3 bg-light text-primary d-flex align-items-center">
                        <h6 class="m-0 font-weight-bold"><i class="fas fa-car"></i> สรุปการรับสติกเกอร์</h6>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-xl col-md-6 mb-4">
                                <div class="card bg-info text-white shadow h-100 py-2">
                                    <div class="card-body">
                                        <div class="row no-gutters align-items-center">
                                            <div class="col mr-2">
                                                <div class="text-xs font-weight-bold text-uppercase mb-1">จำนวนบ้านที่รับสติกเกอร์ (หลัง)</div>
                                                <div class="h5 mb-0 font-weight-bold"><?= number_format($total_house_received) ?> หลัง</div>
                                            </div>
                                            <div class="col-auto">
                                                <i class="fas fa-home fa-2x text-gray-300"></i>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="col-xl col-md-6 mb-4">
                                <div class="card bg-primary text-white shadow h-100 py-2">
                                    <div class="card-body">
                                        <div class="row no-gutters align-items-center">
                                            <div class="col mr-2">
                                                <div class="text-xs font-weight-bold text-uppercase mb-1">จำนวนรถที่รับสติกเกอร์ (คัน)</div>
                                                <div class="h5 mb-0 font-weight-bold"><?= number_format($total_cars_received) ?> คัน</div>
                                            </div>
                                            <div class="col-auto">
                                                <i class="fas fa-car fa-2x text-gray-300"></i>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="col-xl col-md-6 mb-4">
                                <div class="card bg-info text-white shadow h-100 py-2">
                                    <div class="card-body">
                                        <div class="row no-gutters align-items-center">
                                            <div class="col mr-2">
                                                <div class="text-xs font-weight-bold text-uppercase mb-1">จำนวนรถทั้งหมด (คัน)</div>
                                                <div class="h5 mb-0 font-weight-bold"><?= number_format($total_cars_count) ?> คัน</div>
                                                <div class="text-xs mt-1">(รับ + ยังไม่ได้รับ)</div>
                                            </div>
                                            <div class="col-auto">
                                                <i class="fas fa-car-side fa-2x text-gray-300"></i>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="col-xl col-md-6 mb-4">
                                <div class="card bg-primary text-white shadow h-100 py-2">
                                    <div class="card-body">
                                        <div class="row no-gutters align-items-center">
                                            <div class="col mr-2">
                                                <div class="text-xs font-weight-bold text-uppercase mb-1">เงินค่าสติกเกอร์รถเพิ่ม (บาท)</div>
                                                <div class="h5 mb-0 font-weight-bold"><?= number_format($total_extra_fee_received, 2) ?> บาท</div>
                                            </div>
                                            <div class="col-auto">
                                                <i class="fas fa-hand-holding-usd fa-2x text-gray-300"></i>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="col-xl col-md-6 mb-4">
                                <div class="card bg-success text-white shadow h-100 py-2">
                                    <div class="card-body d-flex flex-column justify-content-center align-items-center">
                                        <div class="text-xs font-weight-bold text-uppercase mb-2 text-center">ดูรายละเอียดการรับสติกเกอร์</div>
                                        <button type="button" class="btn btn-light btn-sm shadow-sm font-weight-bold" data-toggle="modal" data-target="#stickerDetailModal">
                                            <i class="fas fa-search"></i> Click Details
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

            </div> <!-- End of container-fluid -->
        </div> <!-- End of content -->
        <?php include('includes/Footer.php'); ?>
    </div> <!-- End of content-wrapper -->
    <?php include('includes/Modal-Logout.php'); ?>
</div> <!-- End of wrapper -->

    <!-- Modal แสดงรายละเอียดรายรับประจำเดือน -->
    <div class="modal fade" id="monthlyIncomeModal" tabindex="-1" role="dialog" aria-labelledby="monthlyIncomeModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-xl" role="document">
            <div class="modal-content">
                <div class="modal-header bg-success text-white">
                    <h5 class="modal-title" id="monthlyIncomeModalLabel"><i class="fas fa-hand-holding-usd"></i> รายละเอียดรายรับประจำเดือน <?= $thai_months[$curr_month] ?> <?= $curr_year + 543 ?></h5>
                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="table-responsive">
                        <table id="monthlyIncomeTable" class="display nowrap table table-striped table-bordered" style="width:100%">
                            <thead>
                                <tr>
                                    <th class="text-center">ลำดับ</th>
                                    <th>เลขที่เอกสาร</th>
                                    <th>วันที่</th>
                                    <th>หมวดหมู่</th>
                                    <th>รายละเอียด</th>
                                    <th class="text-right">จำนวนเงิน (บาท)</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php 
                                foreach ($monthly_income_list as $row) {
                                    echo "<tr>";
                                    echo "<td class='text-center'>".htmlspecialchars($row['row_num'] ?? '')."</td>";
                                    echo "<td>".htmlspecialchars($row['doc_id'] ?? '')."</td>";
                                    echo "<td class='text-center'>".htmlspecialchars($row['reciept_date'] ?? '')."</td>";
                                    echo "<td>".htmlspecialchars($row['category_name'] ?? '')."</td>";
                                    echo "<td>".htmlspecialchars($row['description'] ?? '')."</td>";
                                    echo "<td class='text-right text-success font-weight-bold'>".number_format($row['amount'], 2)."</td>";
                                    echo "</tr>";
                                }
                                ?>
                            </tbody>
                            <tfoot>
                                <tr>
                                    <th colspan="5" class="text-right">ยอดรวมรายรับ:</th>
                                    <th class="text-right text-success font-weight-bold"><?= number_format($total_monthly_income, 2) ?></th>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">ปิด</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal แสดงรายละเอียดรายจ่ายประจำเดือน -->
    <div class="modal fade" id="monthlyExpenseModal" tabindex="-1" role="dialog" aria-labelledby="monthlyExpenseModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-xl" role="document">
            <div class="modal-content">
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title" id="monthlyExpenseModalLabel"><i class="fas fa-file-invoice-dollar"></i> รายละเอียดรายจ่ายประจำเดือน <?= $thai_months[$curr_month] ?> <?= $curr_year + 543 ?></h5>
                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="table-responsive">
                        <table id="monthlyExpenseTable" class="display nowrap table table-striped table-bordered" style="width:100%">
                            <thead>
                                <tr>
                                    <th class="text-center">ลำดับ</th>
                                    <th>เลขที่เอกสาร</th>
                                    <th>วันที่</th>
                                    <th>จ่ายให้แก่</th>
                                    <th>รายละเอียด</th>
                                    <th class="text-right">จำนวนเงิน (บาท)</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php 
                                foreach ($monthly_expense_list as $row) {
                                    echo "<tr>";
                                    echo "<td class='text-center'>".htmlspecialchars($row['row_num'] ?? '')."</td>";
                                    echo "<td>".htmlspecialchars($row['doc_no'] ?? '')."</td>";
                                    echo "<td class='text-center'>".htmlspecialchars($row['doc_date'] ?? '')."</td>";
                                    echo "<td>".htmlspecialchars($row['supplier_name'] ?? '')."</td>";
                                    echo "<td>".htmlspecialchars($row['purpose'] ?? '')."</td>";
                                    echo "<td class='text-right text-danger font-weight-bold'>".number_format($row['total_amount'], 2)."</td>";
                                    echo "</tr>";
                                }
                                ?>
                            </tbody>
                            <tfoot>
                                <tr>
                                    <th colspan="5" class="text-right">ยอดรวมรายจ่าย:</th>
                                    <th class="text-right text-danger font-weight-bold"><?= number_format($total_monthly_expense, 2) ?></th>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">ปิด</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal แสดงรายละเอียดเงินเดือนพนักงานประจำเดือน -->
    <div class="modal fade" id="staffSalaryModal" tabindex="-1" role="dialog" aria-labelledby="staffSalaryModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-xl" role="document">
            <div class="modal-content">
                <div class="modal-header bg-info text-white">
                    <h5 class="modal-title" id="staffSalaryModalLabel"><i class="fas fa-users-cog"></i> รายละเอียดเงินเดือนพนักงานประจำเดือน <?= $thai_months[$prev_month] ?> <?= $prev_year + 543 ?></h5>
                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="table-responsive">
                        <table id="staffSalaryTable" class="display nowrap table table-striped table-bordered" style="width:100%">
                            <thead>
                                <tr>
                                    <th class="text-center">ลำดับ</th>
                                    <th>เลขที่เอกสาร</th>
                                    <th>วันที่</th>
                                    <th>ชื่อพนักงาน</th>
                                    <th class="text-right">จำนวนเงิน (บาท)</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php 
                                foreach ($staff_salary_list as $row) {
                                    echo "<tr>";
                                    echo "<td class='text-center'>".htmlspecialchars($row['row_num'] ?? '')."</td>";
                                    echo "<td>".htmlspecialchars($row['doc_no'] ?? '')."</td>";
                                    echo "<td class='text-center'>".htmlspecialchars($row['doc_date'] ?? '')."</td>";
                                    echo "<td>".htmlspecialchars($row['employee_fullname'] ?? '')."</td>";
                                    echo "<td class='text-right text-info font-weight-bold'>".number_format($row['total_amount'], 2)."</td>";
                                    echo "</tr>";
                                }
                                ?>
                            </tbody>
                            <tfoot>
                                <tr>
                                    <th colspan="4" class="text-right">ยอดรวมเงินเดือน:</th>
                                    <th class="text-right text-info font-weight-bold"><?= number_format($total_staff_salary, 2) ?></th>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">ปิด</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal แสดงบ้านที่ชำระแล้วของเดือนปัจจุบัน -->
    <div class="modal fade" id="paidHousesModal" tabindex="-1" role="dialog" aria-labelledby="paidHousesModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-xl" role="document">
            <div class="modal-content">
                <div class="modal-header bg-success text-white">
                    <h5 class="modal-title" id="paidHousesModalLabel"><i class="fas fa-check-circle"></i> บ้านที่ชำระค่าส่วนกลางประจำเดือน <?= $thai_months[$curr_month] ?> <?= $curr_year + 543 ?></h5>
                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="row mb-2">
                        <div class="col-md-4">
                            <div class="input-group">
                                <div class="input-group-prepend">
                                    <span class="input-group-text"><i class="fas fa-search"></i> เลขที่บ้าน</span>
                                </div>
                                <input type="text" id="search_paid_house_number" class="form-control" placeholder="ค้นหาเลขที่บ้าน...">
                            </div>
                        </div>
                    </div>
                    <div class="table-responsive">
                        <table id="paidHousesTable" class="display nowrap table table-striped table-bordered" style="width:100%">
                            <thead>
                                <tr>
                                    <th class="text-center">ลำดับ</th>
                                    <th>บ้านเลขที่</th>
                                    <th>ซอย</th>
                                    <th>ชื่อผู้ติดต่อ</th>
                                    <th>เบอร์โทรศัพท์</th>
                                    <th class="text-center">วันที่ชำระ</th>
                                    <th class="text-right">จำนวนเงิน (บาท)</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php 
                                foreach ($paid_houses_list as $row_paid) {
                                    echo "<tr>";
                                    echo "<td class='text-center'>".htmlspecialchars($row_paid['row_num'] ?? '')."</td>";
                                    echo "<td class='font-weight-bold'>".htmlspecialchars($row_paid['house_number'] ?? '')."</td>";
                                    echo "<td>".htmlspecialchars($row_paid['alley'] ?? '')."</td>";
                                    echo "<td>".htmlspecialchars($row_paid['contact_name'] ?? '')."</td>";
                                    echo "<td>".htmlspecialchars($row_paid['phone_number'] ?? '')."</td>";
                                    echo "<td class='text-center'>".($row_paid['payment_date'] ? date('d/m/Y', strtotime($row_paid['payment_date'])) : '-')."</td>";
                                    echo "<td class='text-right text-success font-weight-bold'>".number_format($row_paid['common_fee'] ?? 0, 2)."</td>";
                                    echo "</tr>";
                                }
                                ?>
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

    <!-- Modal แสดงบ้านที่ค้างชำระของเดือนปัจจุบัน -->
    <div class="modal fade" id="unpaidHousesModal" tabindex="-1" role="dialog" aria-labelledby="unpaidHousesModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-xl" role="document">
            <div class="modal-content">
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title" id="unpaidHousesModalLabel"><i class="fas fa-exclamation-triangle"></i> บ้านที่ค้างชำระค่าส่วนกลางประจำเดือน <?= $thai_months[$curr_month] ?> <?= $curr_year + 543 ?></h5>
                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="row mb-2">
                        <div class="col-md-4">
                            <div class="input-group">
                                <div class="input-group-prepend">
                                    <span class="input-group-text"><i class="fas fa-search"></i> เลขที่บ้าน</span>
                                </div>
                                <input type="text" id="search_house_number" class="form-control" placeholder="ค้นหาเลขที่บ้าน...">
                            </div>
                        </div>
                    </div>
                    <div class="table-responsive">
                        <table id="unpaidHousesTable" class="display nowrap table table-striped table-bordered" style="width:100%">
                            <thead>
                                <tr>
                                    <th class="text-center">ลำดับ</th>
                                    <th>บ้านเลขที่</th>
                                    <th>ซอย</th>
                                    <th>ชื่อผู้ติดต่อ</th>
                                    <th>เบอร์โทรศัพท์</th>
                                    <th class="text-right">ค่าส่วนกลาง/เดือน (บาท)</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php 
                                foreach ($unpaid_houses_list as $row_unpaid) {
                                    echo "<tr>";
                                    echo "<td class='text-center'>".htmlspecialchars($row_unpaid['row_num'] ?? '')."</td>";
                                    echo "<td class='font-weight-bold'>".htmlspecialchars($row_unpaid['house_number'] ?? '')."</td>";
                                    echo "<td>".htmlspecialchars($row_unpaid['alley'] ?? '')."</td>";
                                    echo "<td>".htmlspecialchars($row_unpaid['contact_name'] ?? '')."</td>";
                                    echo "<td>".htmlspecialchars($row_unpaid['phone_number'] ?? '')."</td>";
                                    echo "<td class='text-right text-danger font-weight-bold'>".number_format($row_unpaid['common_fee'] ?? 0, 2)."</td>";
                                    echo "</tr>";
                                }
                                ?>
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

    <!-- Modal แสดงรายละเอียดการรับสติกเกอร์ -->
    <div class="modal fade" id="stickerDetailModal" tabindex="-1" role="dialog" aria-labelledby="stickerDetailModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-xl" role="document">
            <div class="modal-content">
                <div class="modal-header bg-info text-white">
                    <h5 class="modal-title" id="stickerDetailModalLabel"><i class="fas fa-car"></i> รายละเอียดการรับสติกเกอร์</h5>
                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="table-responsive">
                        <table id="stickerDetailTable" class="display nowrap table table-striped table-bordered" style="width:100%">
                            <thead>
                                <tr>
                                    <th>บ้านเลขที่</th>
                                    <th>ทะเบียน 1</th>
                                    <th>ทะเบียน 2</th>
                                    <th>ทะเบียน 3</th>
                                    <th>ทะเบียน 4</th>
                                    <th>ทะเบียน 5</th>
                                    <th>ทะเบียน 6</th>
                                    <th>ทะเบียน 7</th>
                                    <th>ทะเบียน 8</th>
                                    <th class="text-right">จำนวนรถ</th>
                                    <th class="text-right">ค่าสติกเกอร์ (บาท)</th>
                                    <th>วันที่รับสติกเกอร์</th>
                                </tr>
                            </thead>
                        </table>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">ปิด</button>
                </div>
            </div>
        </div>
    </div>

    <!-- DataTables Buttons and Custom CSS Dependencies -->
    <link rel="stylesheet" href="css/spin_datatables_v2.css"/>
    <link rel="stylesheet" href="vendor/datatables/v11/buttons.dataTables.min.css"/>

    <style>
        @media (min-width: 1200px) {
            .modal-xl {
                max-width: 95%;
            }
        }
    </style>

<script src="vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
<script src="vendor/jquery-easing/jquery.easing.min.js"></script>
<script src="js/myadmin.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-datalabels@2.0.0"></script>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/jqueryui/1.12.1/jquery-ui.min.css">
<script src="https://cdnjs.cloudflare.com/ajax/libs/jqueryui/1.12.1/jquery-ui.min.js"></script>

<!-- DataTables Buttons Scripts -->
<script src="https://cdn.datatables.net/buttons/2.4.2/js/dataTables.buttons.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.html5.min.js"></script>

<script>
    // Pie Chart Logic
    const ctxTotal = document.getElementById("totalHousePieChart").getContext('2d');
    new Chart(ctxTotal, {
        type: 'pie',
        data: {
            labels: ['ลงทะเบียนแล้ว', 'ยังไม่ลงทะเบียน'],
            datasets: [{
                data: [<?= $total_house ?>, <?= $unregistered_house ?>],
                backgroundColor: ['#28a745', '#dc3545'],
                borderWidth: 1
            }]
        },
        options: { responsive: true, plugins: { legend: { position: 'bottom' } } }
    });

    // Collapse Icon Toggle
    $('#collapseCard').on('show.bs.collapse', function () {
        $('#collapseIcon').removeClass('fa-chevron-down').addClass('fa-chevron-up');
    });
    $('#collapseCard').on('hide.bs.collapse', function () {
        $('#collapseIcon').removeClass('fa-chevron-up').addClass('fa-chevron-down');
    });

    // Monthly Bar Chart Logic
    const currentYear = new Date().getFullYear();
    const chartCanvas = document.getElementById('monthlyBarChart');
    const ctxBar = chartCanvas.getContext('2d');

    fetch('model/generate_graph_payment_monthly.php?year=' + currentYear)
        .then(response => response.json())
        .then(data => {
            if (data.error) {
                console.error('Error fetching chart data:', data.error);
                return;
            }

            const labels = data.data.map(item => item.month_name);
            const amounts = data.data.map(item => item.total_amount);

            const gradient = ctxBar.createLinearGradient(0, 0, 0, 300);
            gradient.addColorStop(0, 'rgba(78, 115, 223, 0.85)');
            gradient.addColorStop(1, 'rgba(102, 126, 234, 0.3)');
            
            const hoverGradient = ctxBar.createLinearGradient(0, 0, 0, 300);
            hoverGradient.addColorStop(0, 'rgba(78, 115, 223, 1)');
            hoverGradient.addColorStop(1, 'rgba(102, 126, 234, 0.6)');

            new Chart(ctxBar, {
                type: 'bar',
                plugins: [ChartDataLabels],
                data: {
                    labels: labels,
                    datasets: [{
                        label: 'ยอดชำระต่อเดือน (บาท)',
                        data: amounts,
                        backgroundColor: gradient,
                        hoverBackgroundColor: hoverGradient,
                        borderColor: '#4e73df',
                        borderWidth: 1.5,
                        borderRadius: 6,
                        borderSkipped: 'bottom'
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    layout: {
                        padding: {
                            top: 25
                        }
                    },
                    plugins: {
                        legend: {
                            display: false
                        },
                        datalabels: {
                            anchor: 'end',
                            align: 'top',
                            formatter: function(value) {
                                return value > 0 ? value.toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2}) : '';
                            },
                            font: {
                                weight: 'bold',
                                size: 11
                            },
                            color: '#5a5c69',
                            offset: 2
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            max: <?= (float)$total_common_fee ?>,
                            grace: '10%',
                            title: { display: true, text: 'ยอดรวม (บาท)' }
                        },
                        x: {
                            title: { display: true, text: 'เดือน' }
                        }
                    }
                }
            });
        })
        .catch(error => console.error('Error fetching data:', error));

    // Initialize Unpaid Houses DataTable
    const unpaidTable = $('#unpaidHousesTable').DataTable({
        'paging': true,
        'lengthChange': true,
        'pageLength': 5,
        'lengthMenu': [[5, 10, 20, 50, 100, -1], [5, 10, 20, 50, 100, "ทั้งหมด"]],
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
        'order': [[0, 'asc'], [2, 'asc'], [1, 'asc']],
        'scrollY': '45vh',
        'scrollCollapse': true,
        'scrollX': true,
        'autoWidth': false,
        dom: 'Blfrtip',
        buttons: [
            {
                extend: 'excelHtml5',
                text: '<i class="fas fa-file-excel"></i> Export to Excel',
                className: 'btn btn-success btn-sm',
                title: 'บ้านค้างชำระค่าส่วนกลางประจำเดือน <?= $thai_months[$curr_month] ?> <?= $curr_year + 543 ?>'
            }
        ]
    });

    // Initialize Paid Houses DataTable
    const paidTable = $('#paidHousesTable').DataTable({
        'paging': true,
        'lengthChange': true,
        'pageLength': 5,
        'lengthMenu': [[5, 10, 20, 50, 100, -1], [5, 10, 20, 50, 100, "ทั้งหมด"]],
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
        'order': [[0, 'asc'], [2, 'asc'], [1, 'asc']],
        'scrollY': '45vh',
        'scrollCollapse': true,
        'scrollX': true,
        'autoWidth': false,
        dom: 'Blfrtip',
        buttons: [
            {
                extend: 'excelHtml5',
                text: '<i class="fas fa-file-excel"></i> Export to Excel',
                className: 'btn btn-success btn-sm',
                title: 'บ้านที่ชำระค่าส่วนกลางแล้วประจำเดือน <?= $thai_months[$curr_month] ?> <?= $curr_year + 543 ?>'
            }
        ]
    });

    // ค้นหาตามเลขที่บ้าน (Column Index 1)
    $('#search_house_number').on('keyup', function () {
        unpaidTable.column(1).search(this.value).draw();
    });

    // ค้นหาตามเลขที่บ้าน (Paid Table)
    $('#search_paid_house_number').on('keyup', function () {
        paidTable.column(1).search(this.value).draw();
    });

    // Re-adjust columns when the modal is shown to fix layout issues
    $('#unpaidHousesModal').on('shown.bs.modal', function () {
        unpaidTable.columns.adjust().draw();
    });

    $('#paidHousesModal').on('shown.bs.modal', function () {
        paidTable.columns.adjust().draw();
    });

    // Initialize Monthly Income DataTable
    const incomeTable = $('#monthlyIncomeTable').DataTable({
        'paging': true,
        'lengthChange': true,
        'pageLength': 10,
        'lengthMenu': [[10, 20, 50, 100, -1], [10, 20, 50, 100, "ทั้งหมด"]],
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
        'order': [[0, 'asc']],
        'scrollY': '45vh',
        'scrollCollapse': true,
        'scrollX': true,
        'autoWidth': false,
        dom: 'Blfrtip',
        buttons: [
            {
                extend: 'excelHtml5',
                text: '<i class="fas fa-file-excel"></i> Export to Excel',
                className: 'btn btn-success btn-sm',
                title: 'รายละเอียดรายรับประจำเดือน <?= $thai_months[$curr_month] ?> <?= $curr_year + 543 ?>'
            }
        ]
    });

    // Initialize Monthly Expense DataTable
    const expenseTable = $('#monthlyExpenseTable').DataTable({
        'paging': true,
        'lengthChange': true,
        'pageLength': 10,
        'lengthMenu': [[10, 20, 50, 100, -1], [10, 20, 50, 100, "ทั้งหมด"]],
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
        'order': [[0, 'asc']],
        'scrollY': '45vh',
        'scrollCollapse': true,
        'scrollX': true,
        'autoWidth': false,
        dom: 'Blfrtip',
        buttons: [
            {
                extend: 'excelHtml5',
                text: '<i class="fas fa-file-excel"></i> Export to Excel',
                className: 'btn btn-success btn-sm',
                title: 'รายละเอียดรายจ่ายประจำเดือน <?= $thai_months[$curr_month] ?> <?= $curr_year + 543 ?>'
            }
        ]
    });

    $('#monthlyIncomeModal').on('shown.bs.modal', function () {
        incomeTable.columns.adjust().draw();
    });

    $('#monthlyExpenseModal').on('shown.bs.modal', function () {
        expenseTable.columns.adjust().draw();
    });

    // Initialize Staff Salary DataTable
    const staffSalaryTable = $('#staffSalaryTable').DataTable({
        'paging': true,
        'lengthChange': true,
        'pageLength': 10,
        'lengthMenu': [[10, 20, 50, 100, -1], [10, 20, 50, 100, "ทั้งหมด"]],
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
        'order': [[0, 'asc']],
        'scrollY': '45vh',
        'scrollCollapse': true,
        'scrollX': true,
        'autoWidth': false,
        dom: 'Blfrtip',
        buttons: [
            {
                extend: 'excelHtml5',
                text: '<i class="fas fa-file-excel"></i> Export to Excel',
                className: 'btn btn-success btn-sm',
                title: 'รายละเอียดเงินเดือนพนักงานประจำเดือน <?= $thai_months[$prev_month] ?> <?= $prev_year + 543 ?>'
            }
        ]
    });

    $('#staffSalaryModal').on('shown.bs.modal', function () {
        staffSalaryTable.columns.adjust().draw();
    });

    // Initialize Sticker Detail DataTable
    const stickerTable = $('#stickerDetailTable').DataTable({
        'paging': true,
        'lengthChange': true,
        'pageLength': 10,
        'lengthMenu': [[10, 20, 50, 100, -1], [10, 20, 50, 100, "ทั้งหมด"]],
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
        "ajax": {
            "url": "model/get_sticker_received_list.php",
            "type": "POST",
            "dataSrc": "data"
        },
        "columns": [
            { "data": "house_number" },
            { "data": "car_no1" },
            { "data": "car_no2" },
            { "data": "car_no3" },
            { "data": "car_no4" },
            { "data": "car_no5" },
            { "data": "car_no6" },
            { "data": "car_no7" },
            { "data": "car_no8" },
            { "data": "car_count", "className": "text-right" },
            { 
                "data": "extra_car_fee", 
                "className": "text-right",
                "render": function(data) {
                    return parseFloat(data).toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2});
                }
            },
            { "data": "sticker_receive_date" }
        ],
        'order': [[0, 'asc']],
        'scrollY': '45vh',
        'scrollCollapse': true,
        'scrollX': true,
        'autoWidth': false,
        dom: 'Blfrtip',
        buttons: [
            {
                extend: 'excelHtml5',
                text: '<i class="fas fa-file-excel"></i> Export to Excel',
                className: 'btn btn-success btn-sm',
                title: 'รายละเอียดการรับสติกเกอร์'
            }
        ]
    });

    $('#stickerDetailModal').on('shown.bs.modal', function () {
        stickerTable.columns.adjust().draw();
    });
</script>
</body>
</html>