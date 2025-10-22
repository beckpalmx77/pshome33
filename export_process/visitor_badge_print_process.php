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