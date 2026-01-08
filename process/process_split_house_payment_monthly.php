<?php
// 1. เรียกใช้ไฟล์เชื่อมต่อฐานข้อมูล
require_once '../config/connect_db.php';

if (!isset($conn)) {
    die("Error: Variable \$conn not found.");
}

// ตั้งค่า Environment
set_time_limit(0);
ini_set('memory_limit', '4096M');

echo "<h1>Starting Process: Full House & Year Sync</h1>";
flush();

try {
    // ---------------------------------------------------------
    // Step 1: ดึง "ปีทั้งหมด" ที่มีในระบบ (เพื่อนำมาสร้างยอด 0 ให้บ้านที่ไม่มีข้อมูล)
    // ---------------------------------------------------------
    $sqlYears = "SELECT DISTINCT period_year FROM ims_house_payment WHERE period_year > 0 ORDER BY period_year DESC";
    $stmtYears = $conn->query($sqlYears);
    $allYears = $stmtYears->fetchAll(PDO::FETCH_COLUMN);

    if (empty($allYears)) {
        die("No payment years found in database.");
    }

    echo "Processing for years: " . implode(", ", $allYears) . "<br><hr>";

    // ---------------------------------------------------------
    // Step 2: เตรียม Query ต่างๆ
    // ---------------------------------------------------------

    // ดึงรายชื่อบ้านทั้งหมด
    $sqlMaster = "SELECT house_number FROM ims_house_master ORDER BY house_number";
    $stmtMaster = $conn->query($sqlMaster);

    // ดึงข้อมูลการจ่ายเงินของบ้านระบุ (เอาเงื่อนไข amount > 0 ออก เพื่อรับยอด 0)
    $sqlPayment = "SELECT 
                    period_year, 
                    period_month_start, 
                    period_month_to, 
                    amount 
                   FROM ims_house_payment 
                   WHERE house_number = :house 
                   AND period_year > 0";
    // ตัด AND amount > 0 ออก ตามโจทย์
    $stmtGetPayment = $conn->prepare($sqlPayment);

    // SQL Upsert (Insert หรือ Update)
    $sqlUpsert = "INSERT INTO ims_house_payment_split_monthly_summary 
                  (house_number, period_year, 
                   amount_period_month_1, amount_period_month_2, amount_period_month_3, 
                   amount_period_month_4, amount_period_month_5, amount_period_month_6, 
                   amount_period_month_7, amount_period_month_8, amount_period_month_9, 
                   amount_period_month_10, amount_period_month_11, amount_period_month_12,
                   total,
                   updated_at) 
                  VALUES 
                  (:house, :year, 
                   :m1, :m2, :m3, :m4, :m5, :m6, 
                   :m7, :m8, :m9, :m10, :m11, :m12, 
                   :total,
                   NOW())
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

    $countHouseProcessed = 0;
    $countRecordsUpserted = 0;

    // ---------------------------------------------------------
    // Step 3: วนลูปบ้านทีละหลัง
    // ---------------------------------------------------------
    while ($rowMaster = $stmtMaster->fetch(PDO::FETCH_ASSOC)) {
        $houseNumber = trim($rowMaster['house_number']);
        if (empty($houseNumber)) continue;

        // ดึงข้อมูลการจ่ายเงินที่มีอยู่จริง (ถ้ามี)
        $stmtGetPayment->execute([':house' => $houseNumber]);
        $payments = $stmtGetPayment->fetchAll(PDO::FETCH_ASSOC);

        // จัดกลุ่มข้อมูลการจ่ายเงินตามปี (เพื่อความง่ายในการเรียกใช้)
        $paymentByYear = [];
        foreach ($payments as $pay) {
            $y = (int)$pay['period_year'];
            if (!isset($paymentByYear[$y])) $paymentByYear[$y] = [];
            $paymentByYear[$y][] = $pay;
        }

        // ---------------------------------------------------------
        // Step 4: วนลูป "ทุกปีที่มีในระบบ" (บังคับสร้าง Record)
        // ---------------------------------------------------------
        foreach ($allYears as $targetYear) {

            // เตรียมตัวแปรเก็บยอดรายเดือน (Default = 0)
            $monthlyAmounts = array_fill(1, 12, 0.00);

            // ถ้ามีข้อมูลการจ่ายเงินในปีนี้ ให้คำนวณ
            if (isset($paymentByYear[$targetYear])) {
                foreach ($paymentByYear[$targetYear] as $p) {
                    $monthStart = (int)$p['period_month_start'];
                    $monthTo = (int)$p['period_month_to'];
                    $amount = (float)$p['amount'];

                    if ($monthStart < 1 || $monthStart > 12) continue;
                    if ($monthTo < $monthStart || $monthTo > 12) $monthTo = $monthStart;

                    $monthCount = ($monthTo - $monthStart) + 1;

                    // ป้องกันการหารด้วยศูนย์ (เผื่อ case หลุด)
                    if ($monthCount > 0) {
                        $avgAmount = $amount / $monthCount;

                        for ($m = $monthStart; $m <= $monthTo; $m++) {
                            $monthlyAmounts[$m] += $avgAmount;
                        }
                    }
                }
            }

            // ---------------------------------------------------------
            // Step 5: บันทึกลง DB (ไม่ว่ายอดจะเป็น 0 หรือไม่ ก็บันทึก)
            // ---------------------------------------------------------
            $params = [
                ':house' => $houseNumber,
                ':year'  => $targetYear,
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
            $countRecordsUpserted++;
        }

        $countHouseProcessed++;
    }

    $conn->commit();
    echo "<h3 style='color:green;'>Success! Processed $countHouseProcessed houses. Total summary records: $countRecordsUpserted.</h3>";

} catch (Exception $e) {
    if ($conn->inTransaction()) {
        $conn->rollBack();
    }
    echo "<h3 style='color:red;'>Error: " . $e->getMessage() . "</h3>";
}