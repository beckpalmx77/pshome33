<?php
require 'config/connect_db.php'; // ไฟล์เชื่อมต่อฐานข้อมูล
include 'util/month_util.php';

$id = isset($_GET['id']) ? $_GET['id'] : ''; // รับค่า ID จาก URL

// ตรวจสอบว่าได้รับค่า ID มาหรือไม่
if (!$id) {
    die("ไม่พบข้อมูล");
}

$sql = "SELECT company_name, address_1, address_2, state, zip_code, phone FROM ims_company LIMIT 1";
$stmt = $conn->prepare($sql);
$stmt->execute();
$company = $stmt->fetch(PDO::FETCH_ASSOC);

// ดึงข้อมูลจากฐานข้อมูล
$stmt = $conn->prepare("SELECT * FROM ims_house_payment WHERE id = :id");
$stmt->bindParam(":id", $id, PDO::PARAM_INT);
$stmt->execute();
$data = $stmt->fetch(PDO::FETCH_ASSOC);

$period_month_start_name = $month_arr[$data['period_month_start']];
$period_month_to_name = $month_arr[$data['period_month_to']];

if (!$data) {
    die("ไม่พบข้อมูล");
}
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ใบเสร็จ</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            font-size: 12px;
            font-family: 'THSarabunNew', sans-serif;
        }

        .container {
            width: 210mm; /* กำหนดความกว้างของฟอร์ม */
            height: 148mm; /* กำหนดความสูงของฟอร์ม */
            margin: 0 auto;
            padding: 15mm;
            box-sizing: border-box;
        }

        .header {
            text-align: center;
            margin-bottom: 20px;
        }

        .bordered {
            border: 1px solid black;
        }

        .footer-signature {
            margin-top: 20px;
            text-align: center;
        }

        @media print {
            body {
                font-size: 12px;
            }
            .container {
                width: 210mm;
                height: 148mm;
                margin: 0;
                padding: 15mm;
            }
            .print-btn {
                display: none;
            }
        }
    </style>
</head>
<body>
<div class="container">
    <br>
    <div class="row header">
        <div class="col-2">
            <img src="img/logo/Logo-01-1.png" alt="Company Logo" height="60">
        </div>
        <div class="col-10 text-start">
            <h5><?= htmlspecialchars($company['company_name'] ?? 'ไม่พบข้อมูลบริษัท'); ?></h5>
            <p><?= htmlspecialchars($company['address_1'] . " " . $company['address_2'] . " " . $company['state'] . " " . $company['zip_code']); ?></p>
            <p>โทร: <?= htmlspecialchars($company['phone'] ?? '-'); ?></p>
        </div>
    </div>
    <hr>
    <div class="row">
        <div class="col-6 bordered p-2">
            <b>เลขที่บ้าน:</b> <?= htmlspecialchars($data['house_number']) ?><br>
            <b>รายละเอียด:</b> <?= htmlspecialchars($data['detail']) ?><br>
            <b>ประเภทการชำระ:</b> <?= htmlspecialchars($data['payment_type']) ?>
        </div>
        <div class="col-6 bordered p-2">
            <b>วันที่ชำระ:</b> <?= htmlspecialchars($data['payment_date']) ?><br>
            <b>เลขที่เอกสาร:</b> <?= htmlspecialchars($data['doc_id']) ?><br>
        </div>
    </div>
    <table class="table table-bordered mt-3">
        <thead>
        <tr>
            <th>งวดปี</th>
            <th>เดือนเริ่มต้น</th>
            <th>เดือนสิ้นสุด</th>
            <th>จำนวนเงิน (บาท)</th>
        </tr>
        </thead>
        <tbody>
        <tr>
            <td><?= htmlspecialchars($data['period_year']) ?></td>
            <td><?= htmlspecialchars($period_month_start_name) ?></td>
            <td><?= htmlspecialchars($period_month_to_name) ?></td>
            <td><?= htmlspecialchars($data['amount']) ?></td>
        </tr>
        </tbody>
    </table>
    <div class="row">
        <div class="col-6">
            <p><b>หมายเหตุ:</b> <?= htmlspecialchars($data['remark']) ?></p>
        </div>
        <div class="col-6">
            <table class="table table-bordered">
                <tr>
                    <td><b>รวมทั้งสิ้น</b></td>
                    <td><?= htmlspecialchars($data['amount']) ?> บาท</td>
                </tr>
            </table>
        </div>
    </div>

    <!-- ปุ่ม Print -->
    <div class="container text-center mt-3">
        <button class="btn btn-primary print-btn" onclick="window.print()">พิมพ์ใบเสร็จ</button>
    </div>
</div>
</body>
</html>
