<?php
require 'vendor/autoload.php';

use Dompdf\Dompdf;
use Dompdf\Options;

// ตั้งค่าการแสดงผล PDF
$options = new Options();
$options->set('isFontSubsettingEnabled', true); // เปิดใช้งานการฝังฟอนต์
$options->set('isHtml5ParserEnabled', true); // ใช้ HTML5 Parser
$options->set('defaultFont', 'THSarabunNew'); // ตั้งฟอนต์เริ่มต้น

$dompdf = new Dompdf($options);

// เนื้อหา HTML
ob_start();
include 'document_inv.php'; // ไฟล์ HTML ของคุณ
$html = ob_get_clean();

// โหลด HTML และตั้งค่าหน้ากระดาษ
$dompdf->loadHtml($html);
$dompdf->setPaper('A4', 'portrait');
$dompdf->render();

// ส่งไฟล์ PDF ให้ดาวน์โหลด
$dompdf->stream('invoice.pdf', ['Attachment' => 1]);

