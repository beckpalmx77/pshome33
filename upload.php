<?php

$target_dir = "uploads/";
$target_file = $target_dir . basename($_FILES["fileToUpload"]["name"]);

if (move_uploaded_file($_FILES["fileToUpload"]["tmp_name"], $target_file)) {
    echo "อัปโหลดไฟล์สำเร็จ: " . htmlspecialchars(basename($_FILES["fileToUpload"]["name"]));
} else {
    echo "เกิดข้อผิดพลาดในการอัปโหลดไฟล์!";
}

