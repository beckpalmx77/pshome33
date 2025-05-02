<?php
// รวม autoload สำหรับ DOMPDF
require 'vendor/autoload.php';
use Dompdf\Dompdf;
use Dompdf\Options;

// เชื่อมต่อฐานข้อมูล
include 'config/connect_db.php';

// SQL Query ที่ใช้ดึงข้อมูล
$sql = "SELECT 
            hp.id AS id,
            hp.doc_id AS doc_id,
            hp.house_number AS house_number,
            hp.detail AS detail,
            hp.period_year AS period_year,
            hp.period_month_start AS period_month_start,
            hp.period_month_to AS period_month_to,
            hp.payment_date AS payment_date,
            hp.amount AS amount,
            hp.remark AS remark,
            hp.picture_payment AS picture_payment,
            hp.payment_status AS payment_status,
            hp.created_at AS created_at,
            hp.updated_at AS updated_at,
            s.month_name AS month_name_start,
            t.month_name AS month_name_to,
            ih.contact_name AS contact_name,
            ih.alley AS alley,
            ih.phone_number AS phone_number,
            hp.runno AS runno
        FROM ims_house_payment hp
        LEFT JOIN ims_month s ON s.month = hp.period_month_start
        LEFT JOIN ims_month t ON t.month = hp.period_month_to
        LEFT JOIN ims_house ih ON ih.house_number = hp.house_number
        WHERE hp.id = :id";  // ใช้ :id เพื่อกรองผลข้อมูลตาม id

// เตรียมคำสั่ง SQL
$stmt = $conn->prepare($sql);
$stmt->bindParam(':id', $id, PDO::PARAM_INT);

// ตัวอย่าง ID ที่จะดึงข้อมูล
$id = 1;
$stmt->execute();

// ดึงข้อมูลจากฐานข้อมูล
$row = $stmt->fetch(PDO::FETCH_ASSOC);

// ตรวจสอบว่าได้ข้อมูลหรือไม่
if (!$row) {
    die("ไม่พบข้อมูล.");
}

// HTML ใบเสร็จ
$html = '
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        @font-face {
            font-family: "THSarabunNew";
            font-style: normal;
            font-weight: normal;
            src: url("font/THSarabunNew.ttf");
        }
        body {
            font-family: "THSarabunNew", sans-serif;
            margin: 20px;
        }
        .header {
            text-align: center;
            margin-bottom: 20px;
        }
        .header h1 {
            margin: 0;
            font-size: 24px;
        }
        .header p {
            margin: 0;
            font-size: 14px;
        }
        .details {
            margin-bottom: 20px;
        }
        .details table {
            width: 100%;
            border-collapse: collapse;
        }
        .details td {
            padding: 8px;
            border: 1px solid #ddd;
        }
        .total {
            text-align: right;
            font-weight: bold;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>ใบเสร็จรับเงิน</h1>
        <p>บริษัท XYZ จำกัด</p>
        <p>ที่อยู่: 123/45 ถนนตัวอย่าง แขวง/ตำบล ตัวอย่าง เขต/อำเภอ ตัวอย่าง จังหวัด ตัวอย่าง 10110</p>
        <p>โทร: 02-123-4567</p>
    </div>
    <div class="details">
        <table>
            <tr>
                <td>เลขที่เอกสาร: <strong>' . $row["doc_id"] . '</strong></td>
                <td>วันที่: <strong>' . $row["payment_date"] . '</strong></td>
            </tr>
            <tr>
                <td>ชื่อผู้ชำระ: <strong>' . $row["contact_name"] . '</strong></td>
                <td>บ้านเลขที่: <strong>' . $row["house_number"] . '</strong></td>
            </tr>
            <tr>
                <td>เดือนเริ่มต้น: <strong>' . $row["month_name_start"] . ' ' . $row["period_year"] . '</strong></td>
                <td>ถึงงวดเดือน: <strong>' . $row["month_name_to"] . ' ' . $row["period_year"] . '</strong></td>
            </tr>
            <tr>
                <td>หมายเหตุ: <strong>' . $row["remark"] . '</strong></td>
                <td>หมายเลขการชำระ: <strong>' . $row["runno"] . '</strong></td>
            </tr>
        </table>
    </div>
    <div class="details">
        <table>
            <tr>
                <td class="total">จำนวนเงินโอน: </td>
                <td class="total">' . number_format($row["amount"], 2) . ' บาท</td>
            </tr>
        </table>
    </div>
</body>
</html>
';

// กำหนดตัวเลือก DOMPDF
$options = new Options();
$options->set('isHtml5ParserEnabled', true);
$options->set('isRemoteEnabled', true);
$dompdf = new Dompdf($options);

// โหลด HTML ที่สร้างขึ้น
$dompdf->loadHtml($html);

// ตั้งค่าขนาดกระดาษเป็น A4
$dompdf->setPaper('A4', 'portrait');

// สร้าง PDF จาก HTML
$dompdf->render();

// ส่ง PDF ให้ผู้ใช้งานดาวน์โหลด
$dompdf->stream("receipt_" . $row["doc_id"] . ".pdf", ["Attachment" => 1]); // เปลี่ยนเป็น 0 หากต้องการให้แสดงในเบราว์เซอร์
