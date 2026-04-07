<?php
require_once __DIR__ . '/vendor/autoload.php';

use Mpdf\Mpdf;

$mpdf = new Mpdf([
    'mode' => 'utf-8',
    'format' => 'A4',
    'default_font' => 'sans-serif'
]);

$row = [
    'id' => '001',
    'name' => 'นายสมชาย ใจดี',
    'address' => '123 ถนนสุขุมวิท ซอย 5',
    'room' => '101/1'
];

$html = '
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <style>
        body { font-family: sans-serif; padding: 20px; }
        .header { text-align: center; margin-bottom: 30px; }
        .info-box { border: 1px solid #ccc; padding: 20px; border-radius: 8px; }
        .info-row { display: flex; margin: 15px 0; border-bottom: 1px solid #eee; padding-bottom: 10px; }
        .label { width: 100px; font-weight: bold; color: #333; }
        .value { flex: 1; color: #555; }
        .footer { text-align: center; margin-top: 30px; color: #888; font-size: 12px; }
    </style>
</head>
<body>
    <div class="header">
        <h1>แบบฟอร์มสติ๊กเกอร์</h1>
    </div>
    
    <div class="info-box">
        <div class="info-row">
            <span class="label">รหัส:</span>
            <span class="value">' . htmlspecialchars($row['id']) . '</span>
        </div>
        <div class="info-row">
            <span class="label">ชื่อ:</span>
            <span class="value">' . htmlspecialchars($row['name']) . '</span>
        </div>
        <div class="info-row">
            <span class="label">ที่อยู่:</span>
            <span class="value">' . htmlspecialchars($row['address']) . '</span>
        </div>
        <div class="info-row">
            <span class="label">ห้อง:</span>
            <span class="value">' . htmlspecialchars($row['room']) . '</span>
        </div>
    </div>
    
    <div class="footer">
        สร้างเมื่อ ' . date('d/m/Y H:i') . '
    </div>
</body>
</html>
';

$mpdf->WriteHTML($html);
$mpdf->Output('sticker_output.pdf', 'I');