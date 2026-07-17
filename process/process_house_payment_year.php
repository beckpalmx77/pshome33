<?php
// process/process_house_payment_year.php
require_once '../config/connect_db.php'; // ถอยกลับ 1 ชั้นเพื่อหา config

// ตรวจสอบสิทธิ์ (ถ้ามี session start มาแล้ว)
// if (strlen($_SESSION['alogin']) == "") exit(json_encode(['status'=>'error','message'=>'Unauthorized']));

$process_year = isset($_POST['year']) ? $_POST['year'] : date('Y');

try {
    if (!isset($conn)) {
        throw new Exception("Database connection failed.");
    }

    // 1. ดึงรายชื่อบ้านทั้งหมดจาก Master (เพื่อ Loop ทุกบ้าน)
    $sqlMaster = "SELECT house_number FROM ims_house_master ORDER BY house_number";
    $stmtMaster = $conn->query($sqlMaster);
    $houses = $stmtMaster->fetchAll(PDO::FETCH_ASSOC);

    // 2. เตรียม SQL ดึงข้อมูลการจ่ายเงิน (เฉพาะบ้านและปีที่ระบุ และยืนยันการชำระเงินแล้ว)
    $sqlPayment = "SELECT period_month_start, period_month_to, amount 
                   FROM ims_house_payment 
                   WHERE house_number = :house 
                   AND period_year = :year 
                   AND period_year > 0
                   AND payment_status = 'Y'";
    $stmtGetPayment = $conn->prepare($sqlPayment);

    // 3. เตรียม SQL Upsert (Update ถ้ามี, Insert ถ้าไม่มี)
    $sqlUpsert = "INSERT INTO ims_house_payment_split_monthly_summary 
                  (house_number, period_year, 
                   amount_period_month_1, amount_period_month_2, amount_period_month_3, 
                   amount_period_month_4, amount_period_month_5, amount_period_month_6, 
                   amount_period_month_7, amount_period_month_8, amount_period_month_9, 
                   amount_period_month_10, amount_period_month_11, amount_period_month_12,
                   total, updated_at) 
                  VALUES 
                  (:house, :year, :m1, :m2, :m3, :m4, :m5, :m6, :m7, :m8, :m9, :m10, :m11, :m12, :total, NOW())
                  ON DUPLICATE KEY UPDATE
                   amount_period_month_1 = VALUES(amount_period_month_1),
                   amount_period_month_2 = VALUES(amount_period_month_2),
                   amount_period_month_3 = VALUES(amount_period_month_3),
                   amount_period_month_4 = VALUES(amount_period_month_4),
                   amount_period_month_5 = VALUES(amount_period_month_5),
                   amount_period_month_6 = VALUES(amount_period_month_6),
                   amount_period_month_7 = VALUES(amount_period_month_7),
                   amount_period_month_8 = VALUES(amount_period_month_8),
                   amount_period_month_9 = VALUES(amount_period_month_9),
                   amount_period_month_10 = VALUES(amount_period_month_10),
                   amount_period_month_11 = VALUES(amount_period_month_11),
                   amount_period_month_12 = VALUES(amount_period_month_12),
                   total = VALUES(total),
                   updated_at = NOW()";
    $stmtUpsert = $conn->prepare($sqlUpsert);

    $conn->beginTransaction();

    foreach ($houses as $h) {
        $houseNumber = trim($h['house_number']);
        if (empty($houseNumber)) continue;

        // ดึงข้อมูลการจ่ายเงินของปีนี้
        $stmtGetPayment->execute([':house' => $houseNumber, ':year' => $process_year]);
        $payments = $stmtGetPayment->fetchAll(PDO::FETCH_ASSOC);

        // ตัวแปรพักยอด 12 เดือน (Default 0.00)
        $monthlyAmounts = array_fill(1, 12, 0.00);

        foreach ($payments as $p) {
            $monthStart = (int)$p['period_month_start'];
            $monthTo = (int)$p['period_month_to'];
            $amount = (float)$p['amount'];

            if ($monthStart < 1 || $monthStart > 12) continue;
            if ($monthTo < $monthStart || $monthTo > 12) $monthTo = $monthStart;

            $monthCount = ($monthTo - $monthStart) + 1;
            if ($monthCount > 0) {
                $avgAmount = $amount / $monthCount;
                for ($m = $monthStart; $m <= $monthTo; $m++) {
                    $monthlyAmounts[$m] += $avgAmount;
                }
            }
        }

        // เตรียมข้อมูลส่งเข้า Upsert
        $params = [
            ':house' => $houseNumber,
            ':year'  => $process_year,
            ':total' => 0
        ];
        $sumTotal = 0;
        for ($i = 1; $i <= 12; $i++) {
            $val = $monthlyAmounts[$i];
            $params[":m$i"] = round($val, 2);
            $sumTotal += $val;
        }
        $params[':total'] = round($sumTotal, 2);

        $stmtUpsert->execute($params);
    }

    $conn->commit();
    echo json_encode(['status' => 'success', 'message' => "Processed data for year $process_year successfully."]);

} catch (Exception $e) {
    if (isset($conn) && $conn->inTransaction()) {
        $conn->rollBack();
    }
    // ส่ง HTTP 500 หรือ JSON Error Message
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}