<?php
$filename = $_GET['file'] ?? '';
$filepath = 'file/' . basename($filename);

if (!empty($filename) && file_exists($filepath)) {
    header('Content-Description: File Transfer');
    header('Content-Type: application/pdf');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    header('Content-Length: ' . filesize($filepath));
    flush();
    readfile($filepath);
    exit;
} else {
    echo "ไม่พบไฟล์ที่ต้องการดาวน์โหลด";
}
?>