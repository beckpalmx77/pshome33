<?php
session_start();
// ตั้งค่าให้แสดง error ทั้งหมดสำหรับการ debug
error_reporting(E_ALL);
ini_set('display_errors', 1);

include('config/connect_db.php');
include('config/lang.php');
include('util/GetData.php');
include('includes/CheckDevice.php');

// ล้างค่า session ที่เกี่ยวข้องกับการล็อกอินเดิม
if (isset($_SESSION['alogin'])) {
    $_SESSION['alogin'] = '';
}

$username = $_POST['username'];
$password = $_POST['password']; // รับค่ารหัสผ่านมาโดยตรงเพื่อใช้ตรวจสอบ

$sql = "SELECT iu.*, ih.house_number, ih.contact_name, pm.dashboard_page, ihu.line_picture_profile, ihu.line_phone
        FROM ims_user iu
        LEFT JOIN ims_permission pm ON pm.permission_id = iu.account_type
        LEFT JOIN ims_house ih ON ih.phone_number = iu.user_id
        LEFT JOIN ims_house_line_user ihu ON ihu.line_phone = iu.user_id
        WHERE iu.user_id = :username";

$query = $conn->prepare($sql);
$query->bindParam(':username', $username, PDO::PARAM_STR);
$query->execute();
$results = $query->fetchAll(PDO::FETCH_OBJ);

if ($query->rowCount() == 1) {
    foreach ($results as $result) {
        // ใช้ password_verify() เพื่อตรวจสอบรหัสผ่านที่ป้อนกับรหัสผ่านที่ถูก hash ในฐานข้อมูล
        if (password_verify($password, $result->password)) {

            // ตั้งค่า Session เมื่อล็อกอินสำเร็จ
            $_SESSION['alogin'] = $result->user_id;
            $_SESSION['login_id'] = $result->id;
            $_SESSION['username'] = $result->email;
            $_SESSION['emp_id'] = $result->emp_id;
            $_SESSION['first_name'] = $result->first_name;
            $_SESSION['last_name'] = $result->last_name;
            $_SESSION['email'] = $result->email;
            $_SESSION['account_type'] = $result->account_type;
            $_SESSION['user_picture'] = $result->picture;
            $_SESSION['lang'] = $result->lang;
            $_SESSION['dashboard_page'] = $result->dashboard_page;
            $_SESSION['system_name'] = $system_name;
            $_SESSION['system_name_1'] = $system_name_1;
            $_SESSION['system_name_2'] = $system_name_2;
            $_SESSION['approve_permission'] = $result->approve_permission;
            $_SESSION['role'] = $result->role;
            $_SESSION['house_number'] = $result->house_number;
            $_SESSION['line_picture_profile'] = $result->line_picture_profile;
            $_SESSION['phone_number'] = $result->line_phone;
            $_SESSION['user_signature'] = $result->user_signature;

            // ตั้งค่า Cookie ที่ปลอดภัยยิ่งขึ้น (ไม่เก็บรหัสผ่าน)
            if ($remember == "on") {
                // เก็บแค่ username เพื่อใช้กรอกอัตโนมัติเท่านั้น
                // ไม่จำเป็นต้องเก็บค่า 'remember_chk' เพราะสามารถตรวจสอบจาก cookie username ได้
                setcookie("username", $username, time() + (86400 * 30), "/"); // Cookie มีอายุ 30 วัน
            } else {
                // ถ้าไม่ได้เลือก remember me ให้ลบ cookie ทิ้ง
                setcookie("username", "", time() - 3600, "/");
            }

            // ส่งค่า dashboard page ที่ถูกต้องกลับไป
            if ($_SESSION['deviceType'] === 'computer' || $_SESSION['deviceType'] === 'tablet') {
                echo $result->dashboard_page;
            } else {
                echo $result->dashboard_page;
            }

        } else {
            // รหัสผ่านไม่ถูกต้อง
            echo 0;
        }
    }
} else {
    // ไม่พบผู้ใช้
    echo 0;
}
?>