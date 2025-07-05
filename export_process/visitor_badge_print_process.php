<?php
// export_process/visitor_badge_print_process.php

// ตรวจสอบ session และการเชื่อมต่อ DB
// **ปรับพาธของ connect_db.php ให้ถูกต้องตามโครงสร้างไฟล์ของคุณ**
include('../config/connect_db.php');
session_start();

// ตรวจสอบการเข้าสู่ระบบ
if (strlen($_SESSION['alogin']) == "") {
    header("Location: ../index"); // พาผู้ใช้กลับไปหน้า Login
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $visitor_name = $_POST['visitor_name'] ?? '';
    $start_badge_number = (int)($_POST['start_badge_number'] ?? 0);
    $end_badge_number = (int)($_POST['end_badge_number'] ?? 0);
    $print_date_form = $_POST['print_date'] ?? date('d-m-Y');

    if ($start_badge_number <= 0 || $end_badge_number <= 0 || $start_badge_number > $end_badge_number) {
        die("Invalid badge number range. Please go back and correct it.");
    }

    // เตรียมคำสั่ง SQL สำหรับบันทึกข้อมูล
    $stmt_insert = $conn->prepare("INSERT INTO visitor_badges (badge_number, visitor_name, print_date) VALUES (:badge_number, :visitor_name, NOW())");

    // เตรียมคำสั่ง SQL สำหรับตรวจสอบว่า badge_number มีอยู่แล้วหรือไม่
    $stmt_check_exist = $conn->prepare("SELECT COUNT(*) FROM visitor_badges WHERE badge_number = :badge_number");


    echo "<!DOCTYPE html><html><head><title>พิมพ์ป้ายแลกบัตร</title>";
    echo "<meta charset='UTF-8'>";
    echo "<link href='https://fonts.googleapis.com/css2?family=Prompt:wght@400;700&display=swap' rel='stylesheet'>";
    echo "<style>";
    echo "body { font-family: 'Prompt', sans-serif; margin: 0; padding: 0; overflow: hidden; }"; // overflow: hidden อาจช่วยในบางกรณี

    // สไตล์สำหรับแต่ละหน้า (ป้าย 1 ใบต่อ 1 หน้า A4 แนวนอน)
    echo ".a4-page { ";
    echo "    width: 297mm; "; // A4 height
    echo "    height: 210mm; "; // A4 width
    echo "    padding: 10mm; /* ลด padding ลงอีก เหลือ 1 ซม. รอบด้าน */";
    echo "    box-sizing: border-box; ";
    echo "    display: flex; ";
    echo "    flex-direction: column; ";
    echo "    justify-content: space-between; ";
    echo "    align-items: center; ";
    echo "    position: relative; ";
    echo "    page-break-after: always; ";
    echo "    overflow: hidden; /* ป้องกันเนื้อหาล้น div */";
    echo "}";

    // ตำแหน่ง Logo (มุมบนซ้าย)
    echo ".logo-container { ";
    echo "    position: absolute; ";
    echo "    top: 10mm; /* ปรับตาม padding ใหม่ */";
    echo "    left: 10mm; /* ปรับตาม padding ใหม่ */";
    echo "    display: flex; ";
    echo "    align-items: center; ";
    echo "}";
    echo ".logo-container img { ";
    echo "    height: 45px; /* ลดขนาดโลโก้ลงเล็กน้อย */";
    echo "    margin-right: 8px; /* ลด margin-right ลงเล็กน้อย */";
    echo "}";
    echo ".logo-text { ";
    echo "    font-size: 0.85em; /* ลดขนาด font ลงเล็กน้อย */";
    echo "    font-weight: bold; ";
    echo "    color: #333; ";
    echo "    line-height: 1.1; /* ลด line-height ให้กระชับขึ้น */";
    echo "}";

    // สไตล์ข้อความ "ผู้มาติดต่อ" / "VISITOR" (อยู่กึ่งกลางหน้า)
    echo ".main-content { ";
    echo "    text-align: center; ";
    echo "    flex-grow: 1; ";
    echo "    display: flex; ";
    echo "    flex-direction: column; ";
    echo "    justify-content: center; ";
    echo "    align-items: center; ";
    echo "    /* อาจจะต้องจำกัดความกว้างของ main-content ถ้าข้อความยาวมากๆ */";
    echo "    max-width: calc(100% - 20mm); /* เผื่อขอบ 10mm ซ้ายขวา */";
    echo "}";
    echo ".thai-text { ";
    echo "    font-size: 3.5em; /* ลดขนาด font ลง */";
    echo "    font-weight: 700; ";
    echo "    margin-bottom: 8px; /* ลด margin-bottom ลงเล็กน้อย */";
    echo "    line-height: 1; ";
    echo "}";
    echo ".eng-text { ";
    echo "    font-size: 2.5em; /* ลดขนาด font ลง */";
    echo "    font-weight: 400; ";
    echo "    line-height: 1; ";
    echo "}";

    // ตำแหน่ง หมายเลขบัตร (มุมล่างขวา)
    echo ".badge-number-display { ";
    echo "    position: absolute; ";
    echo "    bottom: 10mm; /* ปรับตาม padding ใหม่ */";
    echo "    right: 10mm; /* ปรับตาม padding ใหม่ */";
    echo "    font-size: 1.4em; /* ลดขนาด font ลงเล็กน้อย */";
    echo "    font-weight: bold; ";
    echo "    color: #333; ";
    echo "}";

    // สำหรับการพิมพ์จริง
    echo "@media print { ";
    echo "    body { margin: 0; padding: 0; } ";
    echo "    .a4-page { ";
    echo "        margin: 0; ";
    echo "        border: none; ";
    echo "        box-shadow: none; ";
    echo "        page-break-after: always; ";
    echo "    }";
    echo "    @page { ";
    echo "        size: A4 landscape; "; // กำหนดขนาดหน้ากระดาษเป็น A4 แนวนอน
    echo "        margin: 0; "; // กำหนดขอบกระดาษเป็น 0
    echo "        /* ถ้ายังล้น ลองใช้ @page { size: A4 landscape; margin: 0.5cm; } เพื่อเพิ่มขอบเผื่อเครื่องพิมพ์ */";
    echo "    } ";
    echo "}";
    echo "</style>";
    echo "</head><body>";

    for ($i = $start_badge_number; $i <= $end_badge_number; $i++) {
        $formatted_badge_number = sprintf("%04d", $i);

        $badge_exists = false;
        try {
            $stmt_check_exist->bindParam(':badge_number', $formatted_badge_number);
            $stmt_check_exist->execute();
            $count = $stmt_check_exist->fetchColumn();
            if ($count > 0) {
                $badge_exists = true;
            }
        } catch (Exception $e) { // ใช้ Exception หรือเปลี่ยนเป็น mysqli_sql_exception หากใช้ mysqli
            error_log("Database error during existence check for badge " . $formatted_badge_number . ": " . $e->getMessage());
        }

        if (!$badge_exists) {
            try {
                $stmt_insert->bindParam(':badge_number', $formatted_badge_number);
                $stmt_insert->bindParam(':visitor_name', $visitor_name);
                $stmt_insert->execute();
            } catch (Exception $e) { // ใช้ Exception หรือเปลี่ยนเป็น mysqli_sql_exception หากใช้ mysqli
                error_log("Failed to insert badge number " . $formatted_badge_number . ": " . $e->getMessage());
            }
        } else {
            error_log("Badge number " . $formatted_badge_number . " already exists. Printing without re-insertion.");
        }

        echo "<div class='a4-page'>";
        echo "    <div class='logo-container'>";
        echo "        <img src='../img/header/niti_ps33_header.png' alt='PRUKSA 33 Logo'>";
        echo "        <div class='logo-text'>";
        echo "            PRUKSA 33 <br>";
        echo "            นิติบุคคลหมู่บ้านพฤกษา 33";
        echo "        </div>";
        echo "    </div>";

        echo "    <div class='main-content'>";
        echo "        <div class='thai-text'>ผู้มาติดต่อ</div>";
        echo "        <div class='eng-text'>VISITOR</div>";
        echo "    </div>";

        echo "    <div class='badge-number-display'>";
        echo "        หมายเลข " . htmlspecialchars($formatted_badge_number);
        echo "    </div>";
        echo "</div>";
    }

    echo "</body></html>";
    echo "<script>window.print();</script>";

} else {
    header("Location: ../visitor_badge_print.php");
    exit();
}
?>