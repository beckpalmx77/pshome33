<?php

require_once __DIR__ . '/vendor/autoload.php';

$defaultConfig = (new Mpdf\Config\ConfigVariables())->getDefaults();
$fontDirs = $defaultConfig['fontDir'];

$defaultFontConfig = (new Mpdf\Config\FontVariables())->getDefaults();
$fontData = $defaultFontConfig['fontdata'];

$mpdf = new \Mpdf\Mpdf([
    'mode' => 'utf-8',
    'format' => 'A4',
    'fontDir' => array_merge($fontDirs, [__DIR__ . '/vendor/mpdf/mpdf/ttfontdata']),
    'fontdata' => array_merge($fontData, [
        'thsarabunnew' => [
            'R' => 'THSarabunNew.ttf',
            'B' => 'THSarabunNew-Bold.ttf',
            'I' => 'THSarabunNew-Italic.ttf',
            'BI' => 'THSarabunNew-BoldItalic.ttf'
        ]
    ]),
    'default_font' => 'thsarabunnew'
]);

// เขียน HTML พร้อมใช้ฟอนต์ภาษาไทย
$html = '<h1 style="font-family: thsarabunnew;">Hello world! สวัสดี</h1>';

// นำ HTML ไปใส่ใน PDF
$mpdf->WriteHTML($html);

// แสดงผล PDF
$mpdf->Output();


