<?php
require_once __DIR__ . '/vendor/autoload.php';

use Mpdf\Mpdf;

function generateStickerPdf($data, $output = 'I') {
    $mpdf = new Mpdf([
        'mode' => 'utf-8',
        'format' => [70, 35], 
        'unit' => 'mm',
        'default_font' => 'sans-serif',
        'margin_top' => 2,
        'margin_bottom' => 2,
        'margin_left' => 3,
        'margin_right' => 3
    ]);

    $html = '
    <html>
    <head>
        <meta charset="UTF-8">
        <style>
            body { font-family: sans-serif; font-size: 9pt; }
            .sticker { 
                border: 1px solid #999; 
                padding: 3mm; 
                border-radius: 2mm;
                background: #fff;
            }
            .row { margin: 1mm 0; }
            .label { font-weight: bold; color: #333; }
            .value { color: #000; }
            .room { font-size: 14pt; font-weight: bold; color: #d00; }
        </style>
    </head>
    <body>
        <div class="sticker">
            <div class="row">
                <span class="label">ห้อง:</span>
                <span class="value room">' . htmlspecialchars($data['room'] ?? '') . '</span>
            </div>
            <div class="row">
                <span class="label">ชื่อ:</span>
                <span class="value">' . htmlspecialchars($data['name'] ?? '') . '</span>
            </div>
            <div class="row">
                <span class="label">เบอร์:</span>
                <span class="value">' . htmlspecialchars($data['phone'] ?? '') . '</span>
            </div>
        </div>
    </body>
    </html>
    ';

    $mpdf->WriteHTML($html);
    $mpdf->Output('sticker_' . ($data['room'] ?? 'print') . '.pdf', $output);
}

if (php_sapi_name() === 'cli' && basename(__FILE__) === basename($argv[0])) {
    $sampleData = [
        'room' => '101/1',
        'name' => 'นายสมชาย ใจดี',
        'phone' => '089-123-4567'
    ];
    generateStickerPdf($sampleData, 'I');
}