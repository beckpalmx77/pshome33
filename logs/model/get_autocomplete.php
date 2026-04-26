<?php
include '../config/connect_db.php';

header('Content-Type: application/json');

$action = $_POST['action'] ?? '';
$search = $_POST['search'] ?? '';

if (empty($action)) {
    echo json_encode([]);
    exit;
}

if ($action === 'GET_PROVINCE_AUTOCOMPLETE') {
    $return_arr = array();
    $sql_get = "SELECT province_name FROM ims_provinces WHERE province_name LIKE :search ORDER BY province_name LIMIT 20";
    $stmt = $conn->prepare($sql_get);
    $stmt->bindValue(':search', '%' . $search . '%', PDO::PARAM_STR);
    $stmt->execute();
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
    foreach ($results as $result) {
        $return_arr[] = $result['province_name'];
    }
    echo json_encode($return_arr);
    exit();
}

if ($action === 'GET_COLOR_AUTOCOMPLETE') {
    $colors = [
        'ขาว', 'ดำ', 'เทา', 'เงิน', 'น้ำเงิน', 'แดง', 'เขียว', 'เหลือง', 'ส้ม', 'น้ำตาล',
        'ม่วง', 'ชมพู', 'ทอง', 'บรอนซ์', 'ครีม', 'เทาอ่อน', 'เทาเข้ม', 'ดำเงา', 'ขาวมุข',
        'White', 'Black', 'Gray', 'Silver', 'Blue', 'Red', 'Green', 'Yellow', 'Orange', 'Brown',
        'Purple', 'Pink', 'Gold', 'Bronze', 'Cream', 'Light Gray', 'Dark Gray', 'Glossy Black', 'Pearl White'
    ];
    $return_arr = array_filter($colors, function($color) use ($search) {
        return mb_stripos($color, $search) !== false;
    });
    echo json_encode(array_values($return_arr));
    exit();
}

if ($action === 'GET_BRAND_AUTOCOMPLETE') {
    $brands = [
        'โตโยต้า', 'ฮอนด้า', 'นิสสัน', 'มาสด้า', 'ซูซูกิ', 'มิตซูบิชิ', 'อีซูซุ', 'ฟอร์ด', 'เชฟโรเลต', 'บีเอ็มดับเบิลยู','เอ็มจี',
        'เมอร์เซเดส', 'อาวดี้', 'โวลโว', 'เปโตรนาส', 'ซามซอง', 'ดีเอส', 'เอสเอสซี', 'ฮาวิ์', 'ยามาฮ่า', 'คาวาซ��กิ',
        'ดูคาติ', 'อพอลโล', 'เจ็ท', 'ยามาฮ่า มอเตอร์ไซค์', 'ฮอนด้า มอเตอร์ไซค์', 'ซูซูกิ มอเตอร์ไซค์', 'คาวาซากิ มอเตอร์ไซค์', 'ดีเอ็นเอ็ม', 'บีทีอาร์', 'ไทยแลนด์',
        'Toyota', 'Honda', 'Nissan', 'Mazda', 'Suzuki', 'Mitsubishi', 'Isuzu', 'Ford', 'Chevrolet', 'BMW','MG',
        'Mercedes-Benz', 'Audi', 'Volvo', 'Petronas', 'Samsung', 'DS', 'SSC', 'Haval', 'Yamaha', 'Kawasaki',
        'Ducati', 'Apollo', 'Jet', 'Yamaha Motorcycle', 'Honda Motorcycle', 'Suzuki Motorcycle', 'Kawasaki Motorcycle', 'DNM', 'BTR', 'Thailand'
    ];
    $return_arr = array_filter($brands, function($brand) use ($search) {
        return mb_stripos($brand, $search) !== false;
    });
    echo json_encode(array_values($return_arr));
    exit();
}

echo json_encode([]);