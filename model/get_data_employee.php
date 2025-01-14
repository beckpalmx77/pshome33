<?php
include 'config/connect_db.php';

// ตรวจสอบว่าได้รับข้อมูลแผนกมาหรือไม่
if (isset($_GET['department'])) {
    $department = $_GET['department'];

    // เตรียมและเรียกใช้คำสั่ง SQL สำหรับดึงรายชื่อพนักงานตามแผนก
    $stmt = $pdo->prepare('SELECT id, name FROM employees WHERE department = ?');
    $stmt->execute([$department]);

    // ดึงข้อมูลทั้งหมด
    $employees = $stmt->fetchAll();

    // ส่งข้อมูลกลับเป็น JSON
    header('Content-Type: application/json');
    echo json_encode($employees);
}

