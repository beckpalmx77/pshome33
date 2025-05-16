<?php
$dir = __DIR__; // โฟลเดอร์เดียวกับสคริปต์
$allowed_ext = ['png', 'jpeg', 'jpg', 'webp'];
$log_file = $dir . '/failed_to_convert.txt';
$files = scandir($dir);

// เคลียร์ log เก่าก่อนเริ่มใหม่
file_put_contents($log_file, "รายชื่อไฟล์ที่แปลงไม่สำเร็จ:\n");

foreach ($files as $file) {
    $file_path = $dir . '/' . $file;

    if (is_file($file_path)) {
        $ext = strtolower(pathinfo($file, PATHINFO_EXTENSION));

        if (in_array($ext, $allowed_ext) && $ext !== 'jpg') {
            $base_name = pathinfo($file, PATHINFO_FILENAME);
            $new_file_path = $dir . '/' . $base_name . '.jpg';

            switch ($ext) {
                case 'png':
                    $img = @imagecreatefrompng($file_path);
                    break;
                case 'jpeg':
                case 'jpg':
                    $img = @imagecreatefromjpeg($file_path);
                    break;
                case 'webp':
                    $img = @imagecreatefromwebp($file_path);
                    break;
                default:
                    $img = null;
                    break;
            }

            if ($img) {
                imagejpeg($img, $new_file_path, 90);
                imagedestroy($img);
                echo "✔ แปลงแล้ว: $file → $base_name.jpg\n";
            } else {
                echo "✘ โหลดไม่สำเร็จ: $file\n";
                file_put_contents($log_file, $file . "\n", FILE_APPEND);
            }
        }
    }
}
?>
