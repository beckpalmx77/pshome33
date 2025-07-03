<?php
session_start();
error_reporting(0); // แนะนำให้ปิดเมื่ออยู่ใน Production

include('../config/connect_db.php'); // ไฟล์เชื่อมต่อฐานข้อมูล
include('../config/lang.php'); // ไฟล์ภาษา
include('../util/record_util.php'); // ยูทิลิตี้สำหรับ Record
include('../util/reorder_record.php'); // ยูทิลิตี้สำหรับการเรียง Record

// กำหนดข้อความแจ้งเตือนความสำเร็จ (สามารถย้ายไปอยู่ในไฟล์ lang.php ได้)
$save_success = "บันทึกข้อมูลสำเร็จ";
$del_success = "ลบข้อมูลสำเร็จ";

// LINE Messaging API Channel Access Token
// *** สำคัญ: แทนที่ด้วย Channel Access Token ของคุณจาก LINE Developers Console ***
$line_channel_access_token = 'UeQDGaIitsNRqYib1mPUo1VjLZfY6lQYvLK1LguyO0hIEYYMZHABHfWEu9UvM4hK8QrGR1V5pUNu/SO+7kOvvLoLjecwTGAE9JsslpnkD1+4mpRtyJqDcZZyQa4/WCuDNHNE9fL1sqR1ujE+mXLnwgdB04t89/1O/w1cDnyilFU=';

// ดึงข้อมูลรายการชำระเงินตาม ID
if ($_POST["action"] === 'GET_DATA') {

    $id = $_POST["id"];
    $return_arr = [];

    // ดึงคอลัมน์ update_count มาด้วยเพื่อใช้ในการตรวจสอบเงื่อนไข
    $sql_get = "SELECT *, update_count FROM v_ims_house_payment WHERE id = :id";
    $stmt = $conn->prepare($sql_get);
    $stmt->bindParam(':id', $id, PDO::PARAM_INT);
    $stmt->execute();
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($results as $result) {
        $return_arr[] = [
            "id" => $result['id'],
            "doc_id" => $result['doc_id'],
            "house_number" => $result['house_number'],
            "payment_date" => $result['payment_date'],
            "amount" => $result['amount'],
            "period_month_start" => $result['period_month_start'],
            "period_month_to" => $result['period_month_to'],
            "month_name_start" => $result['month_name_start'],
            "month_name_to" => $result['month_name_to'],
            "period_year" => $result['period_year'],
            "detail" => $result['detail'],
            "contact_name" => $result['contact_name'],
            "house_name" => $result['house_name'],
            "phone_number" => $result['phone_number'],
            "picture_payment" => $result['picture_payment'],
            "payment_status" => $result['payment_status'],
            "payment_type" => $result['payment_type'],
            "remark" => $result['remark'],
            "created_at" => $result['created_at'],
            "updated_at" => $result['updated_at'],
            "line_picture_profile_show" => $result['line_picture_profile_show'],
            "alley" => $result['alley'],
            "update_count" => $result['update_count'] // เพิ่ม update_count ในข้อมูลที่ส่งกลับ
        ];
    }

    echo json_encode($return_arr);
    exit;
}

// อัปเดตสถานะการชำระเงิน
if ($_POST["action"] === 'UPDATE') {

    if (!empty($_POST["house_number"])) {
        $id = $_POST["id"];
        // กำหนดค่า payment_status เป็น 'Y' หรือ 'N' จากข้อมูลที่ส่งมา
        $payment_status = ($_POST["payment_status"] === "Y") ? "Y" : "N";

        $period_month_start = $_POST["period_month_start"];
        $period_month_to = $_POST["period_month_to"];
        $period_year = $_POST["period_year"];
        $amount = $_POST["amount"];

        // กำหนดผู้ที่อนุมัติจาก session
        $approve_by = (isset($_SESSION['first_name']) && isset($_SESSION['last_name'])) ? $_SESSION['first_name'] . " " . $_SESSION['last_name'] : "Unknown User";

        // --- ขั้นตอนที่ 1: ดึงข้อมูล payment_status และ update_count ปัจจุบันจากฐานข้อมูล ---
        $sql_find_current = "SELECT payment_status, update_count,month_name_start,month_name_to,period_year,amount FROM v_ims_house_payment WHERE id = :id";
        $stmt_find_current = $conn->prepare($sql_find_current);
        $stmt_find_current->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt_find_current->execute();
        $current_data = $stmt_find_current->fetch(PDO::FETCH_ASSOC);

        // ตรวจสอบว่าพบข้อมูลหรือไม่
        $nRows = $current_data ? 1 : 0;

        if ($nRows > 0) {
            // ดึงค่า update_count ปัจจุบัน
            $current_update_count = $current_data['update_count'];
            $month_name_start = $current_data['month_name_start'];
            $month_name_to = $current_data['month_name_to'];

            $text_send  = "\n\r📅 งวดเดือน " .  $month_name_start . " - " . $month_name_to . " ปี " . $period_year . "\n\r" . "💵 ยอดชำระ : " . $amount . " บาท" ;

            // กำหนดค่า update_count ใหม่ เริ่มต้นด้วยค่าปัจจุบัน
            $new_update_count = $current_update_count;

            // เพิ่มค่า update_count เฉพาะเมื่อ payment_status ที่จะอัปเดตเป็น 'Y' เท่านั้น
            if ($payment_status === 'Y') {
                $new_update_count++;
            }

            // --- ขั้นตอนที่ 2: อัปเดตข้อมูลในฐานข้อมูล ---
            $sql_update = "UPDATE ims_house_payment SET 
                payment_status = :payment_status, 
                approve_by = :approve_by, 
                period_month_start = :period_month_start, 
                period_month_to = :period_month_to,
                period_year = :period_year, 
                amount = :amount, 
                update_count = :new_update_count 
            WHERE id = :id";

            $query = $conn->prepare($sql_update);
            $query->bindParam(':payment_status', $payment_status, PDO::PARAM_STR);
            $query->bindParam(':approve_by', $approve_by, PDO::PARAM_STR);
            $query->bindParam(':period_month_start', $period_month_start, PDO::PARAM_STR);
            $query->bindParam(':period_month_to', $period_month_to, PDO::PARAM_STR);
            $query->bindParam(':period_year', $period_year, PDO::PARAM_STR);
            $query->bindParam(':amount', $amount, PDO::PARAM_STR);
            $query->bindParam(':new_update_count', $new_update_count, PDO::PARAM_INT);
            $query->bindParam(':id', $id, PDO::PARAM_INT);
            $query->execute();

            echo $save_success;

            // --- ขั้นตอนที่ 3: ดำเนินการส่ง LINE Notification หากเงื่อนไขตรง ---
            // เงื่อนไข: payment_status เป็น 'Y' และเป็นการอัปเดตครั้งแรก (update_count = 1)
            if ($payment_status === 'Y' && $new_update_count === 1) {
                // ดึง line_user_id จาก table ims_house_line_user โดยใช้ house_number
                $house_number_to_notify = $_POST["house_number"];
                $sql_get_line_users = "SELECT line_user_id FROM ims_house_line_user WHERE house_number = :house_number";
                $stmt_line_users = $conn->prepare($sql_get_line_users);
                $stmt_line_users->bindParam(':house_number', $house_number_to_notify, PDO::PARAM_STR);
                $stmt_line_users->execute();
                $line_users = $stmt_line_users->fetchAll(PDO::FETCH_ASSOC);

                if (!empty($line_users)) {
                    $message_text = "✅✅✅ ตรวจสอบและอนุมัติรายการชำระเรียบร้อยแล้ว บ้านเลขที่ " . $house_number_to_notify . " (ID: {$id}) " . $text_send ;
/*
                    $myfile = fopen("a_permission.txt", "w") or die("Unable to open file!");
                    fwrite($myfile, " Row Text = " . $message_text);
                    fclose($myfile);
*/
                    foreach ($line_users as $user) {
                        $target_line_user_id = $user['line_user_id'];

                        // LINE Messaging API Endpoint สำหรับ Push Message
                        $line_api_url = "https://api.line.me/v2/bot/message/push";

                        // สร้าง Headers สำหรับ LINE Messaging API
                        $headers = array(
                            'Content-Type: application/json',
                            'Authorization: Bearer ' . $line_channel_access_token // ใช้ Channel Access Token
                        );

                        // สร้าง Body ของ Request ในรูปแบบ JSON
                        $post_data = json_encode([
                            "to" => $target_line_user_id,
                            "messages" => [
                                [
                                    "type" => "text",
                                    "text" => $message_text
                                ]
                            ]
                        ]);

                        // ส่ง Request ด้วย cURL
                        $ch = curl_init();
                        curl_setopt($ch, CURLOPT_URL, $line_api_url);
                        curl_setopt($ch, CURLOPT_POST, 1);
                        curl_setopt($ch, CURLOPT_POSTFIELDS, $post_data);
                        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
                        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
                        // สำหรับ production ควรตั้งค่า SSL verification ให้เหมาะสม
                        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
                        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 1);

                        //$result = curl_exec($ch);
                        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);

                        // ตรวจสอบข้อผิดพลาด cURL
                        if (curl_errno($ch)) {
                            error_log("cURL Error for LINE Push Message (ID: {$id}, User: {$target_line_user_id}): " . curl_error($ch));
                        } else {
                            // LINE API จะคืนค่า {} สำหรับ success (HTTP 200) หรือ error object สำหรับ fail
                            if ($http_code !== 200) {
                                $line_response = json_decode($result, true);
                                error_log("LINE Push Message failed (HTTP Status: {$http_code}) for ID: {$id}, User: {$target_line_user_id}. Response: " . print_r($line_response, true));
                            } else {
                                // ส่งสำเร็จ (ไม่จำเป็นต้อง log ทุกครั้งใน production)
                                // error_log("LINE Push Message sent successfully to User: {$target_line_user_id} for ID: {$id}");
                            }
                        }
                        curl_close($ch);
                    }
                } else {
                    // หากไม่พบ line_user_id สำหรับ house_number นี้
                    error_log("ไม่พบ Line User ID สำหรับบ้านเลขที่: " . $house_number_to_notify . " ใน ims_house_line_user");
                }
            }

        } else {
            // กรณีไม่พบรายการที่ต้องการอัปเดต (ID ไม่ถูกต้อง)
            echo "ไม่พบรายการที่ต้องการอัปเดต (ID: {$id})";
        }
    } else {
        // กรณีข้อมูล house_number ไม่ครบถ้วน
        echo "ข้อมูล House Number ไม่ครบถ้วน";
    }

    exit; // หยุดการทำงานหลังจากประมวลผลการอัปเดต
}

// ลบข้อมูล
if ($_POST["action"] === 'DELETE') {
    $id = $_POST["id"];
    $sql_find = "SELECT COUNT(*) FROM ims_house_payment WHERE id = :id";
    $stmt = $conn->prepare($sql_find);
    $stmt->bindParam(':id', $id, PDO::PARAM_INT);
    $stmt->execute();
    $nRows = $stmt->fetchColumn();

    if ($nRows > 0) {
        try {
            $sql = "DELETE FROM ims_house_payment WHERE id = :id";
            $query = $conn->prepare($sql);
            $query->bindParam(':id', $id, PDO::PARAM_INT);
            $query->execute();
            echo $del_success;
        } catch (Exception $e) {
            echo 'Message: ' . $e->getMessage();
        }
    }

    exit;
}

// ดึงข้อมูลสำหรับตาราง DataTable
if ($_POST["action"] === 'GET_COMMON_FEE') {

    ## Read value from DataTable's request
    $draw = $_POST['draw'];
    $row = $_POST['start'];
    $rowperpage = $_POST['length']; // จำนวนแถวที่แสดงต่อหน้า
    $columnIndex = $_POST['order'][0]['column']; // Index ของคอลัมน์ที่ใช้เรียงลำดับ
    $columnName = $_POST['columns'][$columnIndex]['data']; // ชื่อคอลัมน์ที่ใช้เรียงลำดับ
    $columnSortOrder = 'desc'; // กำหนดให้เรียงลำดับจากมากไปน้อยเสมอ
    $searchValue = $_POST['search']['value']; // ค่าค้นหาที่ผู้ใช้ป้อน

    $searchArray = array();

    ## Search Query
    $searchQuery = " ";
    if ($searchValue != '') {
        $searchQuery = " AND (house_number LIKE :house_number) ";
        $searchArray = array(
            'house_number' => "%$searchValue%"
        );
    }

    $where_house_number = " ";
    if ($_SESSION['account_type'] === "user") {
        $where_house_number = " AND house_number = '" . $_SESSION['house_number'] . "'";
    }

    ## Total number of records without filtering
    $sql_getdata = "SELECT COUNT(*) AS allcount FROM v_ims_house_payment WHERE 1=1 " . $where_house_number;
    $stmt = $conn->prepare($sql_getdata);
    $stmt->execute();
    $records = $stmt->fetch();
    $totalRecords = $records['allcount'];

    ## Total number of records with filtering
    $sql_getdata = "SELECT COUNT(*) AS allcount FROM v_ims_house_payment WHERE 1=1 " . $searchQuery . $where_house_number;
    $stmt = $conn->prepare($sql_getdata);
    $stmt->execute($searchArray);
    $records = $stmt->fetch();
    $totalRecordwithFilter = $records['allcount'];

    ## Fetch records
    $sql_getdata = "SELECT * FROM v_ims_house_payment WHERE 1=1 " . $searchQuery . $where_house_number
        . " ORDER BY id DESC " . " LIMIT :limit,:offset"; // เรียงตาม ID จากมากไปน้อย

    $stmt = $conn->prepare($sql_getdata);

    // Bind values
    foreach ($searchArray as $key => $search) {
        $stmt->bindValue(':' . $key, $search, PDO::PARAM_STR);
    }

    $stmt->bindValue(':limit', (int)$row, PDO::PARAM_INT);
    $stmt->bindValue(':offset', (int)$rowperpage, PDO::PARAM_INT);
    $stmt->execute();
    $empRecords = $stmt->fetchAll();
    $data = array();

    $isUser = $_SESSION['account_type'] === "user";
    $isMaster = $_POST['sub_action'] === "GET_MASTER";

    $statusMeta = [
        'Y' => ['desc' => "ชำระเรียบร้อยแล้ว", 'color' => 'green', 'can_print' => true],
        'N' => ['desc' => "ยังไม่ยืนยันการชำระ", 'color' => 'gray', 'can_print' => false],
    ];

    foreach ($empRecords as $row) {
        if ($isMaster) {
            $status = $row['payment_status'];
            $meta = $statusMeta[$status] ?? ['desc' => '-', 'color' => 'gray', 'can_print' => false];

            $data[] = [
                "id" => $row['id'],
                "doc_id" => $row['doc_id'],
                "payment_date" => $row['payment_date'],
                "detail" => $row['detail'],
                "house_number" => $row['house_number'],
                "alley" => $row['alley'],
                "contact_name" => $row['contact_name'],
                "phone_number" => $row['phone_number'],
                "payment_type" => $row['payment_type'],
                "period_month_start" => $row['period_month_start'],
                "period_month_to" => $row['period_month_to'],
                "month_name_start" => $row['month_name_start'],
                "month_name_to" => $row['month_name_to'],
                "month_name_period" => $row['month_name_start'] . " - " . $row['month_name_to'],
                "period_year" => $row['period_year'],
                "area_size" => $row['area_size'],
                "garbage_collection_fee" => $row['garbage_collection_fee'],
                "common_fee" => $row['common_fee'],
                "amount" => $row['amount'],
                "payment_status" => $row['payment_status'],
                "line_picture_profile" => "<img src='" . ($row['line_picture_profile_show'] ?: 'img/icon/none_img.png') . "' alt='image' style='width: 50px; height: auto;'>",
                "payment_status_desc" => "<span style='color: {$meta['color']}'>{$meta['desc']}</span>",
                "print" => "<button type='button' name='print' id='{$row['id']}' class='btn btn-outline-success btn-xs print' " . ($meta['can_print'] ? "" : "disabled") . ">Print</button>",
                "slip" => "<button type='button' name='slip' id='{$row['id']}' class='btn btn-info btn-xs slip'>Slip</button>",
                "update" => $isUser ? "<button type='button' class='btn btn-info btn-xs update' disabled>Update</button>"
                    : "<button type='button' name='update' id='{$row['id']}' class='btn btn-info btn-xs update'>Update</button>",
                "delete" => $isUser ? "<button type='button' class='btn btn-danger btn-xs delete' disabled>Delete</button>"
                    : "<button type='button' name='delete' id='{$row['id']}' class='btn btn-danger btn-xs delete'>Delete</button>",
                "remark" => $row['remark']
            ];
        } else {
            $data[] = [
                "id" => $row['id'],
                "house_number" => $row['house_number'],
                "contact_name" => $row['contact_name'],
                "select" => "<button type='button' name='select' id='{$row['house_number']}@{$row['contact_name']}' class='btn btn-outline-success btn-xs select'>select <i class='fa fa-check'></i></button>"
            ];
        }
    }

    ## Response Return Value for DataTable
    $response = array(
        "draw" => intval($draw),
        "iTotalRecords" => $totalRecords,
        "iTotalDisplayRecords" => $totalRecordwithFilter,
        "aaData" => $data
    );

    echo json_encode($response);

}
?>