<?php

$upload_urls = [
    "http://171.100.56.194:8888/file_uploads/sac_lotto/upload.php",
    "http://171.100.56.194:8888/sac_lotto/upload.php"
];

// URL สำหรับเก็บไฟล์ Log
$log_url = "http://171.100.56.194:8888/file_uploads/sac_lotto/upload_log.txt";

// ตรวจสอบระบบปฏิบัติการ
$os = strtolower(PHP_OS);
$local_folder = __DIR__ . DIRECTORY_SEPARATOR . "uploads" . DIRECTORY_SEPARATOR;

if (strpos($os, 'win') !== false) {
    $local_folder = __DIR__ . "\\uploads\\";
} elseif (strpos($os, 'linux') !== false) {
    if (file_exists('/etc/os-release')) {
        $os_info = file_get_contents('/etc/os-release');
        if (strpos($os_info, 'Ubuntu') !== false) {
            $local_folder = __DIR__ . "/uploads/";
        }
    }
}

// ตรวจสอบว่าโฟลเดอร์มีอยู่จริง ถ้าไม่มีให้สร้างขึ้นมา
if (!is_dir($local_folder)) {
    mkdir($local_folder, 0777, true);
}

// กำหนดวันที่ปัจจุบัน
$current_date = date("Y-m-d H:i:s");
$start_time = microtime(true); // ⏳ เริ่มจับเวลา

// ตรวจสอบไฟล์ในโฟลเดอร์
$files = glob($local_folder . "*.*");
if (!$files) {
    file_put_contents($log_url, "$current_date - ❌ ไม่มีไฟล์ที่ต้องอัปโหลด\n", FILE_APPEND);
    exit;
}

// เริ่ม Log
$log_text = "============================================\n";
$log_text .= "$current_date - เริ่มต้นอัปโหลดที่: " . date("H:i:s") . "\n";

foreach ($files as $file_path) {
    if (!file_exists($file_path)) {
        $log_text .= "❌ ไฟล์ไม่พบ: " . basename($file_path) . "\n";
        continue;
    }

    $file_date = date("Y-m-d", filemtime($file_path));
    if ($file_date !== date("Y-m-d")) {
        $log_text .= "⏭ ข้ามไฟล์: " . basename($file_path) . " (อัปเดตล่าสุด: $file_date)\n";
        continue;
    }

    $cfile = new CURLFile($file_path, mime_content_type($file_path), basename($file_path));

    foreach ($upload_urls as $upload_url) {
        $data = array("fileToUpload" => $cfile);
        $curl = curl_init();

        curl_setopt($curl, CURLOPT_URL, $upload_url);
        curl_setopt($curl, CURLOPT_POST, true);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);

        $response = curl_exec($curl);
        $http_code = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        curl_close($curl);

        if ($http_code == 200) {
            $log_text .= "✅ อัปโหลดสำเร็จ: $upload_url → " . basename($file_path) . "\n";
        } else {
            $log_text .= "❌ อัปโหลดล้มเหลว: $upload_url → " . basename($file_path) . " → " . $response . "\n";
        }
    }
}

// 📌 จับเวลาสิ้นสุด และคำนวณเวลาอัปโหลด
$end_time = microtime(true);
$elapsed_time = round($end_time - $start_time, 2); // ⏱ คำนวณเป็นวินาที

// บันทึกเวลาเสร็จสิ้น
$log_text .= "$current_date - สิ้นสุดอัปโหลดที่: " . date("H:i:s") . "\n";
$log_text .= "⏳ ใช้เวลา: $elapsed_time วินาที\n";
$log_text .= "============================================\n";

// 🔹 บันทึก Log ลงไฟล์
file_put_contents($log_url, $log_text, FILE_APPEND);

echo "📌 อัปโหลดเสร็จสิ้น และบันทึก Log เรียบร้อย\n";