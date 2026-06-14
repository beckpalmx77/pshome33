<?php
/**
 * ระบบอัปโหลดไฟล์สลิปเก็บลงโฟลเดอร์ uploads/test และตรวจสอบด้วย EasySlip API V2
 */

ini_set('display_errors', 1);
error_reporting(E_ALL);

$upload_results = [];
$order_total_price = 150.00; // สมมติยอดเงินขั้นต่ำที่ระบบต้องการ

// 1. กำหนดโฟลเดอร์ปลายทางที่ต้องการเก็บไฟล์
$target_dir = "uploads/test/";

// ตรวจสอบว่ามีโฟลเดอร์นี้หรือยัง ถ้ายังไม่มีให้ระบบสร้างให้อัตโนมัติ (สิทธิ์ 0755)
if (!file_exists($target_dir)) {
    mkdir($target_dir, 0755, true);
}

function is_slip_already_used($payload) {
    // ระบบจริง: เช็คค่าซ้ำใน Database
    return false;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['slip_images'])) {

    $files = $_FILES['slip_images'];
    $file_count = count($files['name']);

    // ตั้งค่า EasySlip API V2
    //$api_url = 'https://api.easyslip.com/v2/verify/image';
    $api_url = 'https://api.easyslip.com/v2/verify/bank';
    $access_token = '6ff15234-a0b0-4cad-9ea8-1d84d3ad58c5'; // ⚠️ แทนที่ด้วย Access Token V2 ของคุณ

    for ($i = 0; $i < $file_count; $i++) {
        $file_name = $files['name'][$i];
        $file_tmp  = $files['tmp_name'][$i];
        $file_error = $files['error'][$i];

        if ($file_error !== UPLOAD_ERR_OK) {
            $upload_results[] = [
                'file_name' => $file_name,
                'is_success' => false,
                'message' => 'ไฟล์มีปัญหาหรืออัปโหลดไม่สำเร็จ'
            ];
            continue;
        }

        // 2. ตั้งชื่อไฟล์ใหม่เพื่อป้องกันชื่อซ้ำกัน (ใช้ เวลาปัจจุบัน + สุ่มตัวเลข)
        $file_extension = pathinfo($file_name, PATHINFO_EXTENSION);
        $new_file_name = time() . '_' . uniqid() . '.' . $file_extension;
        $target_file_path = $target_dir . $new_file_name;

        // 3. ย้ายไฟล์จากโฟลเดอร์ชั่วคราว (Temp) ไปยังโฟลเดอร์ uploads/test/
        if (move_uploaded_file($file_tmp, $target_file_path)) {

            // 4. ส่งไฟล์ที่บันทึกสำเร็จในเครื่องไปยัง EasySlip API
            $mime_type = mime_content_type($target_file_path);
            $cfile = new CURLFile($target_file_path, $mime_type, $new_file_name);
            $data = array('image' => $cfile);

            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $api_url);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

            // 🛠️ ข้ามการเช็ค SSL สำหรับกรณีรันบน Localhost/Wamp (หากขึ้นเว็บจริงแล้วลบ 2 บรรทัดนี้ได้)
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);

            curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                "Authorization: Bearer $access_token"
            ));

            $response = curl_exec($ch);
            $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);

            if (curl_errno($ch)) {
                $upload_results[] = [
                    'file_name' => $file_name,
                    'is_success' => false,
                    'message' => 'บันทึกไฟล์ลงโฟลเดอร์แล้ว แต่ติดต่อ API ล้มเหลว: ' . curl_error($ch)
                ];
            } else {
                $api_result = json_decode($response, true);

                if ($http_code === 200 && isset($api_result['status']) && $api_result['status'] === 200) {
                    $slip_data = $api_result['data'];
                    $payload = $slip_data['payload'];
                    $amount = (float)$slip_data['amount'];

                    if (is_slip_already_used($payload)) {
                        $upload_results[] = [
                            'file_name' => $file_name,
                            'is_success' => false,
                            'message' => 'สลิปนี้เคยถูกใช้งานไปแล้ว (บันทึกไฟล์สำเร็จ)'
                        ];
                    } elseif ($amount < $order_total_price) {
                        $upload_results[] = [
                            'file_name' => $file_name,
                            'is_success' => false,
                            'message' => 'ยอดเงินไม่ครบตามออเดอร์ (บันทึกไฟล์สำเร็จ)'
                        ];
                    } else {
                        $upload_results[] = [
                            'file_name' => $file_name,
                            'is_success' => true,
                            'message' => 'อัปโหลดและตรวจสอบผ่านเรียบร้อย',
                            'saved_path' => $target_file_path, // ส่งพาธไฟล์ที่บันทึกกลับไปแสดงผล
                            'data' => $slip_data
                        ];
                    }
                } else {
                    $msg = isset($api_result['message']) ? $api_result['message'] : 'สลิปไม่ถูกต้อง หรือไม่พบ QR Code';
                    $upload_results[] = [
                        'file_name' => $file_name,
                        'is_success' => false,
                        'message' => $msg . ' (บันทึกไฟล์ลงเซิร์ฟเวอร์แล้ว)'
                    ];
                }
            }
            //curl_close($ch);

        } else {
            $upload_results[] = [
                'file_name' => $file_name,
                'is_success' => false,
                'message' => 'เกิดข้อผิดพลาด ไม่สามารถย้ายไฟล์เข้าโฟลเดอร์ ' . $target_dir . ' ได้'
            ];
        }
    }
}
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ระบบอัปโหลดและตรวจสอบสลิป V2</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-8">

            <div class="card shadow-sm mb-4">
                <div class="card-header bg-dark text-white py-3">
                    <h5 class="card-title mb-0 fw-bold">📤 อัปโหลดสลิปเก็บเข้าระบบ & ตรวจสอบ API</h5>
                </div>
                <div class="card-body p-4">
                    <form action="" method="POST" enctype="multipart/form-data">
                        <div class="mb-4">
                            <label for="slip_images" class="form-label fw-semibold">เลือกไฟล์ภาพสลิป (บันทึกไปที่โฟลเดอร์ <?php echo $target_dir; ?>)</label>
                            <input class="form-control form-control-lg" type="file" id="slip_images" name="slip_images[]" accept="image/*" multiple required>
                        </div>
                        <button type="submit" class="btn btn-primary btn-lg w-100 fw-bold">อัปโหลดและตรวจสอบ</button>
                    </form>
                </div>
            </div>

            <?php if (!empty($upload_results)): ?>
                <h4 class="fw-bold text-secondary mb-3">📊 ผลการดำเนินงาน</h4>

                <?php foreach ($upload_results as $index => $result): ?>
                    <div class="card shadow-sm mb-3 border-start border-4 <?php echo $result['is_success'] ? 'border-success' : 'border-danger'; ?>">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <h6 class="fw-bold mb-0 text-truncate" style="max-width: 70%;">
                                    📄 ไฟล์ที่ <?php echo $index + 1; ?>: <?php echo htmlspecialchars($result['file_name']); ?>
                                </h6>
                                <?php if ($result['is_success']): ?>
                                    <span class="badge bg-success">สำเร็จ</span>
                                <?php else: ?>
                                    <span class="badge bg-danger">ไม่ผ่าน</span>
                                <?php endif; ?>
                            </div>

                            <p class="mb-1 text-muted small">
                                <strong>ผลลัพธ์:</strong> <?php echo htmlspecialchars($result['message']); ?>
                            </p>

                            <?php if (isset($result['saved_path'])): ?>
                                <p class="mb-0 text-primary small">
                                    <strong>พาธที่เก็บไฟล์:</strong> <code><?php echo htmlspecialchars($result['saved_path']); ?></code>
                                </p>
                            <?php endif; ?>

                            <?php if ($result['is_success'] && isset($result['data'])): ?>
                                <div class="table-responsive mt-3">
                                    <table class="table table-sm table-bordered mb-0" style="font-size: 13px;">
                                        <tbody class="table-light">
                                        <tr>
                                            <td width="30%"><strong>ผู้โอน -> ผู้รับ:</strong></td>
                                            <td><?php echo htmlspecialchars($result['data']['sender']['account']['name']); ?> ➡️ <?php echo htmlspecialchars($result['data']['receiver']['account']['name']); ?></td>
                                        </tr>
                                        <tr>
                                            <td><strong>จำนวนเงิน:</strong></td>
                                            <td class="text-success fw-bold"><?php echo number_format($result['data']['amount'], 2); ?> บาท</td>
                                        </tr>
                                        </tbody>
                                    </table>
                                </div>
                            <?php endif; ?>

                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>

        </div>
    </div>
</div>

</body>
</html>