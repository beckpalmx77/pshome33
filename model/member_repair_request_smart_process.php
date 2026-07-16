<?php
session_start();
error_reporting(0);

include('../config/connect_db.php');

$response = ['status' => 'error', 'message' => 'Invalid action'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'GET_HISTORY') {
        $line_user_id = $_POST['line_user_id'] ?? '';
        $house_number = $_POST['house_number'] ?? '';

        if (empty($line_user_id) && empty($house_number)) {
            echo json_encode([]);
            exit();
        }

        try {
            $sql = "SELECT j.*, CONCAT(e.prefix, e.f_name, ' ', e.l_name) AS emp_fullname, e.nick_name AS emp_nickname
                    FROM jobrecord j
                    LEFT JOIN memployee e ON j.emp_id = e.emp_id
                    WHERE (j.user_id = :line_user_id OR j.place_name = :house_number) AND (j.check_type LIKE 'REPAIR%')
                    ORDER BY j.id DESC";
            $stmt = $conn->prepare($sql);
            $stmt->execute([
                ':line_user_id' => $line_user_id,
                ':house_number' => $house_number
            ]);
            $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

            $data = [];
            foreach ($results as $row) {
                // Extract Subject and Remark
                $remark_raw = $row['remark'];
                $subject_disp = "แจ้งซ่อมทั่วไป";
                $remark_disp = $remark_raw;
                if (preg_match('/^\[หัวข้อ:\s*(.*?)\]\s*(.*)$/su', $remark_raw, $matches)) {
                    $subject_disp = $matches[1];
                    $remark_disp = $matches[2];
                }

                $data[] = [
                    'checkin_time' => date('d/m/Y H:i', strtotime($row['checkin_time'])),
                    'place_name' => $row['place_name'],
                    'subject' => $subject_disp,
                    'remark' => $remark_disp,
                    'photo_path' => $row['photo_path'],
                    'emp_id' => $row['emp_id'],
                    'emp_fullname' => $row['emp_fullname'],
                    'emp_nickname' => $row['emp_nickname'],
                    'check_type' => $row['check_type']
                ];
            }
            echo json_encode($data);
            exit();
        } catch (Exception $e) {
            echo json_encode([]);
            exit();
        }
    }

    if ($action === 'ADD_REPAIR') {
        $house_number = trim($_POST['house_number'] ?? '');
        $place_name = trim($_POST['place_name'] ?? '');
        $subject = trim($_POST['subject'] ?? '');
        $remark = trim($_POST['remark'] ?? '');
        $line_user_id = trim($_POST['line_user_id'] ?? '');
        $line_display_name = trim($_POST['line_display_name'] ?? '');

        if (empty($line_user_id)) {
            echo json_encode(['status' => 'error', 'message' => 'ไม่พบข้อมูลผู้ใช้งาน LINE']);
            exit();
        }

        if (empty($subject) || empty($remark)) {
            echo json_encode(['status' => 'error', 'message' => 'กรุณากรอกหัวข้อและรายละเอียดการแจ้งซ่อม']);
            exit();
        }

        $photoNames = [];
        $uploadDir = "../line_oa/checkin/uploads/";

        // Create directory if it doesn't exist
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        // Process file uploads (up to 3 files)
        if (isset($_FILES['images']) && !empty($_FILES['images']['name'][0])) {
            $totalFiles = count($_FILES['images']['name']);
            $limit = min($totalFiles, 3);
            for ($i = 0; $i < $limit; $i++) {
                $fileName = $_FILES['images']['name'][$i];
                $tmpName = $_FILES['images']['tmp_name'][$i];
                $fileError = $_FILES['images']['error'][$i];

                if ($fileError === UPLOAD_ERR_OK) {
                    $ext = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
                    $allowedExts = ['jpg', 'jpeg', 'png', 'webp', 'gif'];

                    if (in_array($ext, $allowedExts)) {
                        $newFileName = "repair_" . uniqid() . "_" . $i . "." . $ext;
                        $targetPath = $uploadDir . $newFileName;

                        if (move_uploaded_file($tmpName, $targetPath)) {
                            $photoNames[] = $newFileName;
                        }
                    }
                }
            }
        }

        $photoPaths = implode(",", $photoNames);
        $token_checkin = uniqid("repair_", true);
        $timestamp = date('Y-m-d H:i:s');
        $check_type = 'REPAIR_PENDING';
        $emp_id = ''; // Not assigned yet

        $full_remark = "[หัวข้อ: " . $subject . "] " . $remark;

        try {
            $sql_ins = "INSERT INTO jobrecord (user_id, display_name, place_name, latitude, longitude, checkin_time, photo_path, check_type, token_checkin, remark, emp_id) 
                        VALUES (?, ?, ?, 0.0, 0.0, ?, ?, ?, ?, ?, ?)";
            $stmt = $conn->prepare($sql_ins);
            $result = $stmt->execute([
                $line_user_id, 
                $line_display_name, 
                $place_name, 
                $timestamp, 
                $photoPaths, 
                $check_type, 
                $token_checkin, 
                $full_remark, 
                $emp_id
            ]);

            if ($result) {
                $response = ['status' => 'success', 'message' => 'ส่งข้อมูลแจ้งซ่อมเรียบร้อยแล้ว ระบบจะเร่งดำเนินการตรวจสอบโดยเร็ว'];
            } else {
                $response = ['status' => 'error', 'message' => 'เกิดข้อผิดพลาดในการบันทึกข้อมูล'];
            }
        } catch (Exception $e) {
            $response = ['status' => 'error', 'message' => 'Database error: ' . $e->getMessage()];
        }
    }
}

header('Content-Type: application/json');
echo json_encode($response);
?>
