<?php
// ส่วนนี้จำลองการสร้าง QR Code จาก Pay Solutions (คุณสามารถแทนที่ด้วยโค้ดเรียก API จริง)
$qr_image_url = "https://api.paysolutions.asia/qr/v1/promptpay/demo-image"; // ตัวอย่าง

// ข้อมูลบัญชีธนาคาร
$bank_account = [
    'bank_name' => 'กสิกรไทย',
    'account_name' => 'บริษัท เอ บี ซี จำกัด',
    'account_number' => '123-4-56789-0'
];
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <title>หน้ารับชำระเงิน</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background-color: #ffffff; color: #27c76f; }
        .tab-content { margin-top: 20px; }
        .card { background-color: #ffffff; border: none; }
        .nav-tabs .nav-link.active { background-color: #343a40; }
    </style>
</head>
<body>

<div class="container py-5">
    <h2 class="text-center mb-4">เลือกวิธีชำระเงิน</h2>

    <ul class="nav nav-tabs justify-content-center" id="paymentTab" role="tablist">
        <li class="nav-item" role="presentation">
            <button class="nav-link active" id="bank-tab" data-bs-toggle="tab" data-bs-target="#bank" type="button" role="tab">โอนผ่านธนาคาร</button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="qr-tab" data-bs-toggle="tab" data-bs-target="#qr" type="button" role="tab">สแกน QR Code</button>
        </li>
    </ul>

    <div class="tab-content" id="paymentTabContent">
        <!-- โอนเงินผ่านธนาคาร -->
        <div class="tab-pane fade show active" id="bank" role="tabpanel">
            <div class="card p-4">
                <h4>รายละเอียดบัญชี</h4>
                <p><strong>ธนาคาร:</strong> <?= $bank_account['bank_name'] ?></p>
                <p><strong>ชื่อบัญชี:</strong> <?= $bank_account['account_name'] ?></p>
                <p><strong>เลขที่บัญชี:</strong> <?= $bank_account['account_number'] ?></p>
                <p class="text-warning">โปรดโอนเงินและแนบหลักฐานการโอน</p>
            </div>
        </div>

        <!-- QR Code -->
        <div class="tab-pane fade" id="qr" role="tabpanel">
            <div class="card p-4 text-center">
                <h4>สแกนเพื่อชำระเงิน</h4>
                <img src="<?= $qr_image_url ?>" alt="QR Code" class="img-fluid mt-3" style="max-width: 300px;">
                <p class="mt-3 text-info">โปรดแจ้งการชำระหลังโอน</p>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
