<?php
require "../../config/connect_db.php";
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json");

$channelAccessToken = 'UeQDGaIitsNRqYib1mPUo1VjLZfY6lQYvLK1LguyO0hIEYYMZHABHfWEu9UvM4hK8QrGR1V5pUNu/SO+7kOvvLoLjecwTGAE9JsslpnkD1+4mpRtyJqDcZZyQa4/WCuDNHNE9fL1sqR1ujE+mXLnwgdB04t89/1O/w1cDnyilFU=';

function sanitizeFileName($filename)
{
    return preg_replace('/[^a-zA-Z0-9-_\.]/', '_', $filename);
}

$response = ['status' => 'error', 'message' => '', 'image_urls' => []];

if (isset($_POST['user_id'], $_POST['remark'])) {
    $userId = $_POST['user_id'];
    $displayName = $_POST['display_name'] ?? '';
    $place_name = $_POST['place_name'] ?? '';
    $remark = $_POST['remark'] ?? '';
    $check_type = $_POST['check_type'] ?? 'IN';
    $latitude = '0';
    $longitude = '0';
    $timestamp = date('Y-m-d H:i:s');
    $token_checkin = uniqid("ps33_", true);
    $photoNames = [];

    try {
        // ดึงข้อมูลผู้ใช้งาน
        $sql_get_data = "SELECT house_number, f_name, l_name FROM ims_house_line_user WHERE line_user_id = ?";
        $stmt = $conn->prepare($sql_get_data);
        $stmt->execute([$userId]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$row) {
            http_response_code(404);
            $response['message'] = "ไม่พบข้อมูลผู้ใช้ในระบบ";
            echo json_encode($response);
            exit;
        }

        $f_name = $row['f_name'];
        $l_name = $row['l_name'];
        $house_number = $row['house_number'];

        // อัปโหลดภาพ (ถ้ามี)
        if (isset($_FILES['photo']) && is_array($_FILES['photo']['tmp_name'])) {
            $uploadDir = "uploads/";
            if (!file_exists($uploadDir)) {
                mkdir($uploadDir, 0777, true);
            }

            foreach ($_FILES["photo"]["tmp_name"] as $index => $tmpName) {
                if (!is_uploaded_file($tmpName)) continue;

                $originalName = pathinfo($_FILES["photo"]["name"][$index], PATHINFO_FILENAME);
                $originalName = sanitizeFileName($originalName);
                $newFileName = uniqid("checkin_") . "_" . $originalName . ".jpg";
                $newFilePath = $uploadDir . $newFileName;

                $imageInfo = getimagesize($tmpName);
                if (!$imageInfo) continue;

                $mime = $imageInfo['mime'];
                switch ($mime) {
                    case 'image/jpeg':
                        $image = imagecreatefromjpeg($tmpName);
                        break;
                    case 'image/png':
                        $image = imagecreatefrompng($tmpName);
                        break;
                    case 'image/webp':
                        $image = imagecreatefromwebp($tmpName);
                        break;
                    case 'image/gif':
                        $image = imagecreatefromgif($tmpName);
                        break;
                    default:
                        continue 2;
                }

                if ($image && imagejpeg($image, $newFilePath, 90)) {
                    $photoNames[] = $newFileName;
                    imagedestroy($image);
                }
            }
        }

        $photoPaths = implode(",", $photoNames);

        // บันทึกข้อมูลลงฐานข้อมูล
        $stmt = $conn->prepare("
            INSERT INTO afront_contact (
                user_id, display_name, place_name, checkin_time,
                photo_path, check_type, token_checkin, remark,
                f_name, l_name, house_number, latitude, longitude
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");

        $success = $stmt->execute([
            $userId, $displayName, $place_name, $timestamp,
            $photoPaths, $check_type, $token_checkin, $remark,
            $f_name, $l_name, $house_number, $latitude, $longitude
        ]);

        if (!$success) {
            http_response_code(500);
            $response['message'] = "ไม่สามารถบันทึกข้อมูลลงฐานข้อมูลได้";
            echo json_encode($response);
            exit;
        }

        // เตรียม URL รูปภาพสำหรับส่งกลับ frontend (เต็ม URL)
        $baseUrl = "https://ps33home.com/line_oa/house/uploads/";
        $imageUrls = [];
        foreach ($photoNames as $photo) {
            $imageUrls[] = $baseUrl . $photo;
        }

        $response['status'] = 'success';
        $response['message'] = 'บันทึกสำเร็จ';
        $response['image_urls'] = $imageUrls;

        echo json_encode($response);

    } catch (Exception $e) {
        http_response_code(500);
        $response['message'] = "เกิดข้อผิดพลาด: " . $e->getMessage();
        echo json_encode($response);
    }
} else {
    http_response_code(400);
    $response['message'] = "ข้อมูลไม่ครบถ้วน";
    echo json_encode($response);
}
