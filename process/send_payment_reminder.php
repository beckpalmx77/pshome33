<?php
session_start();
error_reporting(0);

include('../config/connect_db.php');

$line_channel_access_token = 'UeQDGaIitsNRqYib1mPUo1VjLZfY6lQYvLK1LguyO0hIEYYMZHABHfWEu9UvM4hK8QrGR1V5pUNu/SO+7kOvvLoLjecwTGAE9JsslpnkD1+4mpRtyJqDcZZyQa4/WCuDNHNE9fL1sqR1ujE+mXLnwgdB04t89/1O/w1cDnyilFU=';

header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['status' => 'error', 'message' => 'ต้องใช้ POST Method']);
    exit;
}

$house_number = $_POST['house_number'] ?? '';
$ref_year = isset($_POST['ref_year']) ? (int)$_POST['ref_year'] : (int)date('Y');
$ref_month = isset($_POST['ref_month']) ? (int)$_POST['ref_month'] : (int)date('m');

if (empty($house_number)) {
    echo json_encode(['status' => 'error', 'message' => 'กรุณาระบุบ้านเลขที่']);
    exit;
}

// ดึงข้อมูลบ้าน
$sql = "SELECT m.house_number, m.alley, m.common_fee, h.contact_name
        FROM ims_house_master m
        LEFT JOIN ims_house h ON m.house_number = h.house_number
        WHERE m.house_number = :house AND m.status = 'Y'";
$stmt = $conn->prepare($sql);
$stmt->bindParam(':house', $house_number, PDO::PARAM_STR);
$stmt->execute();
$house = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$house) {
    echo json_encode(['status' => 'error', 'message' => "ไม่พบบ้านเลขที่ $house_number"]);
    exit;
}

// ดึง LINE User IDs ของบ้านนี้
$sql_line = "SELECT line_user_id FROM ims_house_line_user WHERE house_number = :house_number AND status = 'Y'";
$stmt_line = $conn->prepare($sql_line);
$stmt_line->bindParam(':house_number', $house_number, PDO::PARAM_STR);
$stmt_line->execute();
$line_users = $stmt_line->fetchAll(PDO::FETCH_ASSOC);

if (empty($line_users)) {
    echo json_encode(['status' => 'error', 'message' => "ไม่พบ LINE User ID สำหรับบ้าน $house_number"]);
    exit;
}

// คำนวณยอดค้าง
$sql_pay = "SELECT period_month_start, period_month_to, period_year
            FROM ims_house_payment
            WHERE house_number = :house AND payment_status = 'Y'";
$stmt_pay = $conn->prepare($sql_pay);
$stmt_pay->bindParam(':house', $house_number, PDO::PARAM_STR);
$stmt_pay->execute();
$payments = $stmt_pay->fetchAll(PDO::FETCH_ASSOC);

$paid_months = [];
foreach ($payments as $p) {
    $start = (int)$p['period_month_start'];
    $end = (int)$p['period_month_to'];
    $year = (int)$p['period_year'];
    if ($start <= $end) {
        for ($m = $start; $m <= $end; $m++) $paid_months[$year][$m] = true;
    } else {
        for ($m = $start; $m <= 12; $m++) $paid_months[$year][$m] = true;
        for ($m = 1; $m <= $end; $m++) $paid_months[$year + 1][$m] = true;
    }
}

$start_year = 2025;
$start_month = 1;
$lookback = ($ref_year - $start_year) * 12 + ($ref_month - $start_month) + 1;
if ($lookback < 1) $lookback = 1;

$fee = (float)$house['common_fee'];
$thai_months = ["มกราคม","กุมภาพันธ์","มีนาคม","เมษายน","พฤษภาคม","มิถุนายน",
                "กรกฎาคม","สิงหาคม","กันยายน","ตุลาคม","พฤศจิกายน","ธันวาคม"];

$overdue_count = 0;
$overdue_months_list = [];
$total_overdue = 0;
$consecutive = 0;
$max_consecutive = 0;

for ($i = 0; $i < $lookback; $i++) {
    $m = $ref_month - $i;
    $y = $ref_year;
    while ($m < 1) { $m += 12; $y--; }

    $paid = isset($paid_months[$y][$m]);
    if (!$paid) {
        $consecutive++;
        $overdue_count++;
        $total_overdue += $fee;
        $overdue_months_list[] = $thai_months[$m - 1] . ' ' . ($y + 543);
        if ($consecutive > $max_consecutive) $max_consecutive = $consecutive;
    } else {
        $consecutive = 0;
    }
}

$overdue_months_list = array_reverse($overdue_months_list);

if ($total_overdue <= 0) {
    echo json_encode(['status' => 'error', 'message' => "บ้าน $house_number ไม่มียอดค้างชำระ"]);
    exit;
}

// ระดับการทวง
if ($max_consecutive >= 1 && $max_consecutive <= 3) $dunning_lv = 'ครั้งที่ 1';
elseif ($max_consecutive >= 4 && $max_consecutive <= 6) $dunning_lv = 'ครั้งที่ 2';
elseif ($max_consecutive >= 7 && $max_consecutive <= 12) $dunning_lv = 'ครั้งที่ 3';
else $dunning_lv = 'ครั้งสุดท้าย';

// แสดงเดือนที่ค้าง (สูงสุด 6 เดือนล่าสุด)
$display_months = array_slice($overdue_months_list, -6);
$months_text = implode("\n", $display_months);

// สร้าง Flex Message
$flex_message = [
    "type" => "bubble",
    "body" => [
        "type" => "box",
        "layout" => "vertical",
        "contents" => [
            [
                "type" => "image",
                "url" => "https://ps33home.com/img/logo/niti_ps33_header200.png",
                "size" => "sm",
                "aspectRatio" => "200:85",
                "aspectMode" => "fit",
                "gravity" => "center",
                "margin" => "none"
            ],
            [
                "type" => "text",
                "text" => "📢 แจ้งเตือนค่าส่วนกลาง",
                "weight" => "bold",
                "size" => "xl"
            ],
            [
                "type" => "box",
                "layout" => "vertical",
                "margin" => "lg",
                "spacing" => "sm",
                "contents" => [
                    [
                        "type" => "box",
                        "layout" => "baseline",
                        "spacing" => "sm",
                        "contents" => [
                            ["type" => "text", "text" => "บ้านเลขที่:", "color" => "#aaaaaa", "size" => "sm", "flex" => 3],
                            ["type" => "text", "text" => $house_number, "wrap" => true, "size" => "sm", "color" => "#111111", "flex" => 5]
                        ]
                    ],
                    [
                        "type" => "box",
                        "layout" => "baseline",
                        "spacing" => "sm",
                        "contents" => [
                            ["type" => "text", "text" => "ชื่อผู้ติดต่อ:", "color" => "#aaaaaa", "size" => "sm", "flex" => 3],
                            ["type" => "text", "text" => ($house['contact_name'] ?? '-'), "wrap" => true, "size" => "sm", "color" => "#111111", "flex" => 5]
                        ]
                    ],
                    [
                        "type" => "box",
                        "layout" => "baseline",
                        "spacing" => "sm",
                        "contents" => [
                            ["type" => "text", "text" => "สถานะ:", "color" => "#aaaaaa", "size" => "sm", "flex" => 3],
                            ["type" => "text", "text" => "⚠️ ค้างชำระ", "wrap" => true, "size" => "sm", "color" => "#e74c3c", "flex" => 5]
                        ]
                    ],
                    [
                        "type" => "box",
                        "layout" => "baseline",
                        "spacing" => "sm",
                        "contents" => [
                            ["type" => "text", "text" => "ระดับการทวง:", "color" => "#aaaaaa", "size" => "sm", "flex" => 3],
                            ["type" => "text", "text" => $dunning_lv, "wrap" => true, "size" => "sm", "color" => "#e74c3c", "flex" => 5]
                        ]
                    ],
                    [
                        "type" => "box",
                        "layout" => "baseline",
                        "spacing" => "sm",
                        "contents" => [
                            ["type" => "text", "text" => "ยอดค้างรวม:", "color" => "#aaaaaa", "size" => "sm", "flex" => 3],
                            ["type" => "text", "text" => number_format($total_overdue, 2) . " บาท", "wrap" => true, "size" => "sm", "color" => "#e74c3c", "flex" => 5]
                        ]
                    ],
                    [
                        "type" => "box",
                        "layout" => "baseline",
                        "spacing" => "sm",
                        "contents" => [
                            ["type" => "text", "text" => "จำนวนเดือน:", "color" => "#aaaaaa", "size" => "sm", "flex" => 3],
                            ["type" => "text", "text" => "$overdue_count เดือน", "wrap" => true, "size" => "sm", "color" => "#111111", "flex" => 5]
                        ]
                    ],
                ]
            ],
            [
                "type" => "separator",
                "margin" => "lg"
            ],
            [
                "type" => "text",
                "text" => "เดือนที่ค้างชำระ",
                "weight" => "bold",
                "size" => "sm",
                "margin" => "lg"
            ],
            [
                "type" => "text",
                "text" => $months_text,
                "size" => "sm",
                "color" => "#666666",
                "wrap" => true,
                "margin" => "sm"
            ]
        ]
    ],
    "footer" => [
        "type" => "box",
        "layout" => "vertical",
        "spacing" => "sm",
        "contents" => [
            [
                "type" => "button",
                "style" => "link",
                "height" => "sm",
                "action" => [
                    "type" => "uri",
                    "label" => "ชำระค่าส่วนกลาง",
                    "uri" => "https://ps33home.com/payment_transfer_smart.php"
                ]
            ],
            [
                "type" => "spacer",
                "size" => "sm"
            ]
        ]
    ]
];

// ส่ง LINE Push Message ไปยังทุก LINE User ID ของบ้านนี้
$sent_count = 0;
$errors = [];

foreach ($line_users as $user) {
    $target_line_user_id = $user['line_user_id'];

    $line_api_url = "https://api.line.me/v2/bot/message/push";
    $headers = [
        'Content-Type: application/json',
        'Authorization: Bearer ' . $line_channel_access_token
    ];

    $post_data = json_encode([
        "to" => $target_line_user_id,
        "messages" => [
            [
                "type" => "flex",
                "altText" => "📢 แจ้งเตือนค่าส่วนกลาง $house_number - $dunning_lv",
                "contents" => $flex_message
            ]
        ]
    ]);

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $line_api_url);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $post_data);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 1);

    $result = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);

    if (curl_errno($ch)) {
        $errors[] = "cURL Error: " . curl_error($ch);
    } elseif ($http_code !== 200) {
        $errors[] = "LINE API Error (HTTP $http_code): " . $result;
    } else {
        $sent_count++;
    }
    curl_close($ch);
}

if ($sent_count > 0) {
    echo json_encode([
        'status' => 'success',
        'message' => "ส่งแจ้งเตือนไปยัง $sent_count คน เรียบร้อยแล้ว",
        'sent_count' => $sent_count,
        'total_overdue' => $total_overdue,
    ]);
} else {
    echo json_encode([
        'status' => 'error',
        'message' => "ไม่สามารถส่งข้อความได้: " . implode(" | ", $errors)
    ]);
}
?>
