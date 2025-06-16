<?php
include('config/connect_lotto_db.php');

if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    $txt = "";
    $txt2 = "";

    // รับค่าที่ส่งมาจากฟอร์ม
    $period_no = $_POST['period_no'];
    $period_month = $_POST['period_month'];
    $period_year = $_POST['period_year'];

    // สร้างคำสั่ง SQL สำหรับดึงข้อมูลจาก ims_lotto_period
    $sql = "SELECT ims_lotto_period.*,prize.prize_id,prize.detail,prize.prize FROM ims_lotto_period
                LEFT JOIN ims_lotto_prize prize ON prize.prize_id = ims_lotto_period.lotto_type
                WHERE period_no = :period_no AND period_month = :period_month AND period_year = :period_year ORDER BY lotto_type DESC";
    /*
        $txt .= $sql .  "\n\r" . " | " . $period_no . " | " . $period_month . " | " . $period_year;
        $my_file = fopen("cond.txt", "w") or die("Unable to open file!");
        fwrite($my_file, " sql = " . $txt);
        fclose($my_file);
    */
    // เตรียมคำสั่ง SQL
    $stmt = $conn->prepare($sql);

    // ผูกค่าพารามิเตอร์กับตัวแปร
    $stmt->bindParam(':period_no', $period_no, PDO::PARAM_STR);
    $stmt->bindParam(':period_month', $period_month, PDO::PARAM_STR);
    $stmt->bindParam(':period_year', $period_year, PDO::PARAM_STR);

    // ทำการ execute คำสั่ง SQL
    $stmt->execute();

    // ตรวจสอบผลลัพธ์
    if ($stmt->rowCount() > 0) {
        // ถ้ามีผลลัพธ์ให้แสดง
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {

            // ตรวจสอบประเภทของรางวัล (เลขท้าย 2 ตัว หรือ 3 ตัว)
            if ($row['lotto_type'] == 2) {
                $lotto_type_desc = "เลขท้าย 3 ตัว ";
                $where = "WHERE lotto_number = '" . $row['lotto_number_result'] . "'";
/*
                $my_file = fopen("cond1.txt", "w") or die("Unable to open file!");
                fwrite($my_file, " 3 digit where = " . $where);
                fclose($my_file);
*/
            } else {
                $lotto_type_desc = "เลขท้าย 2 ตัว ";
                $lotto_number_result_last2 = substr($row['lotto_number_result'], -2);
                $where = "WHERE lotto_number LIKE '%" . $lotto_number_result_last2 . "'"; // เปรียบเทียบเลขท้าย 2 ตัว
/*
                $my_file = fopen("cond2.txt", "w") or die("Unable to open file!");
                fwrite($my_file, " 2 digit where = " . $where);
                fclose($my_file);
*/
            }
            echo "<br>";
            echo "<div class='result-item'>";
            echo "<b>" . $lotto_type_desc . "</b>";
            echo "<b> รางวัล: " . htmlspecialchars($row['prize']) . "</b>";
            echo "<b> หมายเลขที่ออก: " . htmlspecialchars($row['lotto_number_result']) . "</b><br>";

            // ค้นหาผู้ถูกรางวัลจากตาราง ims_lotto
            $sql_str = "SELECT * FROM ims_lotto " . $where;
/*
            $txt2 .= $sql_str . "\n\r";
            $my_file = fopen("cond3.txt", "w") or die("Unable to open file!");
            fwrite($my_file,  $txt2);
            fclose($my_file);
*/
            $stmt_lotto = $conn->prepare($sql_str);
            $stmt_lotto->execute();

            if ($stmt_lotto->rowCount() > 0) {
                // ถ้ามีผู้ถูกรางวัล
                echo "<table id='winnersTable' class='display' style='width: 100%; border-collapse: collapse;'>";
                echo "<thead><tr><th>ลำดับ</th><th>ผู้ถูกรางวัล</th><th>เบอร์โทร</th><th>จังหวัด</th><th>หมายเลขที่เลือก</th><th>ชื่อ sale</th></tr></thead>";
                echo "<tbody>";

                $rank = 1; // เริ่มต้นที่ลำดับที่ 1
                while ($lotto = $stmt_lotto->fetch(PDO::FETCH_ASSOC)) {
                    echo "<tr style='border: 1px solid #ddd;'>";
                    echo "<td style='border: 1px solid #ddd; padding: 8px;'>" . $rank++ . "</td>"; // แสดงลำดับและเพิ่มค่าลำดับ
                    echo "<td style='border: 1px solid #ddd; padding: 8px;'>" . htmlspecialchars($lotto['lotto_name']) . "</td>";
                    echo "<td style='border: 1px solid #ddd; padding: 8px;'>" . htmlspecialchars($lotto['lotto_phone']) . "</td>";
                    echo "<td style='border: 1px solid #ddd; padding: 8px;'>" . htmlspecialchars($lotto['lotto_province']) . "</td>";
                    echo "<td style='border: 1px solid #ddd; padding: 8px;'>" . htmlspecialchars($lotto['lotto_number']) . "</td>";
                    echo "<td style='border: 1px solid #ddd; padding: 8px;'>" . htmlspecialchars($lotto['sale_name']) . "</td>";
                    echo "</tr>";
                }
                echo "</tbody></table>";
            } else {
                // ถ้าไม่พบผู้ถูกรางวัล
                echo "ไม่มีผู้ถูกรางวัลสำหรับเลขนี้.<br>";
            }
            echo "</div>";
        }
    } else {
        // ถ้าไม่พบข้อมูล
        echo "ไม่พบผลรางวัลที่ค้นหา";
    }
}

