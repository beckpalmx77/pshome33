<?php
session_start();
error_reporting(0); // It's recommended to turn this off in a production environment.

include('../config/connect_db.php'); // Database connection file
include('../config/lang.php'); // Language file
include('../util/record_util.php'); // Utility for records
include('../util/reorder_record.php'); // Utility for reordering records

// Define success messages (can be moved to lang.php)
$save_success = "บันทึกข้อมูลสำเร็จ";
$del_success = "ลบข้อมูลสำเร็จ";

// LINE Messaging API Channel Access Token
// *** IMPORTANT: Replace with your Channel Access Token from the LINE Developers Console ***
$line_channel_access_token = 'UeQDGaIitsNRqYib1mPUo1VjLZfY6lQYvLK1LguyO0hIEYYMZHABHfWEu9UvM4hK8QrGR1V5pUNu/SO+7kOvvLoLjecwTGAE9JsslpnkD1+4mpRtyJqDcZZyQa4/WCuDNHNE9fL1sqR1ujE+mXLnwgdB04t89/1O/w1cDnyilFU=';

// Fetch payment data by ID
if ($_POST["action"] === 'GET_DATA') {

    $id = $_POST["id"];
    $return_arr = [];

    // Fetch update_count column for conditional checks
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
            "payment_method" => $result['payment_method'],
            "remark" => $result['remark'],
            "created_at" => $result['created_at'],
            "create_by" => $result['create_by'],
            "approve_by" => $result['approve_by'],
            "updated_at" => $result['updated_at'],
            "line_picture_profile_show" => $result['line_picture_profile_show'],
            "alley" => $result['alley'],
            "update_count" => $result['update_count'] // Add update_count to the returned data
        ];
    }

    echo json_encode($return_arr);
    exit;
}

// Update payment status
if ($_POST["action"] === 'UPDATE') {

    if (!empty($_POST["house_number"])) {
        $id = $_POST["id"];
        // Set payment_status to 'Y' or 'N' based on the submitted data
        $payment_status = ($_POST["payment_status"] === "Y") ? "Y" : "N";

        $period_month_start = $_POST["period_month_start"];
        $period_month_to = $_POST["period_month_to"];
        $period_year = $_POST["period_year"];
        $amount = $_POST["amount"];
        $payment_method = $_POST["payment_method"];

/*
        $myfile = fopen("a-param.txt", "w") or die("Unable to open file!");
        fwrite($myfile, $payment_status  . " | " . $payment_method);
        fclose($myfile);
*/

        // ตรวจสอบเงื่อนไข: ถ้าเลือกเดือนมกราคมถึงธันวาคม (1 ถึง 12)
        if ($period_month_start == 1 && $period_month_to == 12) {
            // กำหนดค่า payment_type เป็น 12 ทันที
            $payment_type = 12; // <-- แก้ไขตรงนี้
        } else {
            // ถ้าไม่ใช่กรณี 1-12 ให้คำนวณจำนวนเดือนปกติ
            if ($period_month_to >= $period_month_start) {
                $payment_type = $period_month_to - $period_month_start + 1;
            } else {
                // กรณีข้ามปี (เช่น เริ่ม ธ.ค. -> สิ้นสุด ม.ค.)
                $payment_type = (12 - $period_month_start) + $period_month_to + 1;
            }
        }

        // Set the approver from the session
        $approve_by = (isset($_SESSION['first_name']) && isset($_SESSION['last_name'])) ? $_SESSION['first_name'] . " " . $_SESSION['last_name'] : "Unknown User";

        // --- Step 1: Fetch current payment_status and update_count from the database ---
        $sql_find_current = "SELECT payment_status, update_count, month_name_start, month_name_to, period_year, amount FROM v_ims_house_payment WHERE id = :id";
        $stmt_find_current = $conn->prepare($sql_find_current);
        $stmt_find_current->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt_find_current->execute();
        $current_data = $stmt_find_current->fetch(PDO::FETCH_ASSOC);

        // Check if data was found
        $nRows = $current_data ? 1 : 0;

        if ($nRows > 0) {
            // Get the current update_count
            $current_update_count = $current_data['update_count'];
            $month_name_start = $current_data['month_name_start'];
            $month_name_to = $current_data['month_name_to'];

            // Set the new update_count, starting with the current value
            $new_update_count = $current_update_count;

            // Increment update_count only if the payment status is being updated to 'Y'
            if ($payment_status === 'Y') {
                $new_update_count++;
            }

            // --- Step 2: Update the data in the database ---
            $sql_update = "UPDATE ims_house_payment SET 
                payment_status = :payment_status, 
                approve_by = :approve_by, 
                period_month_start = :period_month_start, 
                period_month_to = :period_month_to,
                period_year = :period_year,
                payment_type = :payment_type,  
                amount = :amount, 
                update_count = :new_update_count,
                payment_method = :payment_method   
            WHERE id = :id";

            $query = $conn->prepare($sql_update);
            $query->bindParam(':payment_status', $payment_status, PDO::PARAM_STR);
            $query->bindParam(':approve_by', $approve_by, PDO::PARAM_STR);
            $query->bindParam(':period_month_start', $period_month_start, PDO::PARAM_STR);
            $query->bindParam(':period_month_to', $period_month_to, PDO::PARAM_STR);
            $query->bindParam(':period_year', $period_year, PDO::PARAM_STR);
            $query->bindParam(':payment_type', $payment_type, PDO::PARAM_STR);
            $query->bindParam(':amount', $amount, PDO::PARAM_STR);
            $query->bindParam(':new_update_count', $new_update_count, PDO::PARAM_INT);
            $query->bindParam(':payment_method', $payment_method, PDO::PARAM_STR);
            $query->bindParam(':id', $id, PDO::PARAM_INT);
            $query->execute();

            echo $save_success;

            // --- Step 3: Send LINE Notification if conditions are met ---
            // Condition: payment_status is 'Y' and it's the first update (update_count = 1)
            if ($payment_status === 'Y' && $new_update_count === 1) {
                // Fetch line_user_id from the ims_house_line_user table using house_number
                $house_number_to_notify = $_POST["house_number"];
                $sql_get_line_users = "SELECT line_user_id FROM ims_house_line_user WHERE house_number = :house_number";
                $stmt_line_users = $conn->prepare($sql_get_line_users);
                $stmt_line_users->bindParam(':house_number', $house_number_to_notify, PDO::PARAM_STR);
                $stmt_line_users->execute();
                $line_users = $stmt_line_users->fetchAll(PDO::FETCH_ASSOC);

                if (!empty($line_users)) {

                    // *** START of Flex Message implementation ***
                    $flex_message_content = [
                        "type" => "bubble",
                        "body" => [
                            "type" => "box",
                            "layout" => "vertical",
                            "contents" => [
                                [
                                    "type" => "image",
                                    "url" => "https://ps33home.com/img/logo/niti_ps33_header200.png", // 👈 **URL โลโก้ของคุณ**
                                    "size" => "sm",
                                    "aspectRatio" => "200:85",
                                    "aspectMode" => "fit",
                                    "gravity" => "center",
                                    "margin" => "none"
                                ],
                                [
                                    "type" => "text",
                                    "text" => "แจ้งการชำระค่าส่วนกลาง",
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
                                                [
                                                    "type" => "text",
                                                    "text" => "สถานะ:",
                                                    "color" => "#aaaaaa",
                                                    "size" => "sm",
                                                    "flex" => 2
                                                ],
                                                [
                                                    "type" => "text",
                                                    "text" => "✅ การชำระเงินเรียบร้อย",
                                                    "wrap" => true,
                                                    "size" => "sm",
                                                    "color" => "#669933",
                                                    "flex" => 5
                                                ]
                                            ]
                                        ],
                                        [
                                            "type" => "box",
                                            "layout" => "baseline",
                                            "spacing" => "sm",
                                            "contents" => [
                                                [
                                                    "type" => "text",
                                                    "text" => "บ้านเลขที่:",
                                                    "color" => "#aaaaaa",
                                                    "size" => "sm",
                                                    "flex" => 2
                                                ],
                                                [
                                                    "type" => "text",
                                                    "text" => $house_number_to_notify,
                                                    "wrap" => true,
                                                    "size" => "sm",
                                                    "color" => "#111111",
                                                    "flex" => 5
                                                ]
                                            ]
                                        ],
                                        [
                                            "type" => "box",
                                            "layout" => "baseline",
                                            "spacing" => "sm",
                                            "contents" => [
                                                [
                                                    "type" => "text",
                                                    "text" => "งวดเดือน:",
                                                    "color" => "#aaaaaa",
                                                    "size" => "sm",
                                                    "flex" => 2
                                                ],
                                                [
                                                    "type" => "text",
                                                    "text" => "{$month_name_start} - {$month_name_to} ปี {$period_year}",
                                                    "wrap" => true,
                                                    "size" => "sm",
                                                    "color" => "#111111",
                                                    "flex" => 5
                                                ]
                                            ]
                                        ],
                                        [
                                            "type" => "box",
                                            "layout" => "baseline",
                                            "spacing" => "sm",
                                            "contents" => [
                                                [
                                                    "type" => "text",
                                                    "text" => "ยอดชำระ:",
                                                    "color" => "#aaaaaa",
                                                    "size" => "sm",
                                                    "flex" => 2
                                                ],
                                                [
                                                    "type" => "text",
                                                    "text" => "{$amount} บาท",
                                                    "wrap" => true,
                                                    "size" => "sm",
                                                    "color" => "#111111",
                                                    "flex" => 5
                                                ]
                                            ]
                                        ],
                                        // Add the new line here
                                        [
                                            "type" => "box",
                                            "layout" => "baseline",
                                            "spacing" => "sm",
                                            "contents" => [
                                                [
                                                    "type" => "text",
                                                    "text" => "หมายเหตุ:",
                                                    "color" => "#aaaaaa",
                                                    "size" => "sm",
                                                    "flex" => 2
                                                ],
                                                [
                                                    "type" => "text",
                                                    "text" => "รับใบเสร็จที่นิติฯ / จัดส่งที่บ้าน",
                                                    "wrap" => true,
                                                    "size" => "sm",
                                                    "color" => "#111111",
                                                    "flex" => 5
                                                ]
                                            ]
                                        ]
                                    ]
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
                                    "style" => "primary", // ✨ แก้ไขตรงนี้เป็น primary
                                    "color" => "#1DB954", // (Optional) สามารถกำหนดสี Hex code ได้
                                    "height" => "sm",
                                    "action" => [
                                        "type" => "uri",
                                        "label" => "ดูประวัติการชำระ", // แก้ไขข้อความให้สั้นลง
                                        "uri" => "https://liff.line.me/2007370141-13Wzad0L"
                                    ]
                                ]
                                // ไม่จำเป็นต้องใช้ spacer แล้วก็ได้ถ้าต้องการให้ปุ่มเต็มความกว้าง
                            ],
                            "flex" => 0
                        ]
                    ];

                    foreach ($line_users as $user) {

                        $target_line_user_id = $user['line_user_id'];

                        // LINE Messaging API Endpoint for Push Message
                        $line_api_url = "https://api.line.me/v2/bot/message/push";

                        // Create Headers for the LINE Messaging API
                        $headers = array(
                            'Content-Type: application/json',
                            'Authorization: Bearer ' . $line_channel_access_token
                        );

                        // Create the JSON payload for the request body
                        $post_data = json_encode([
                            "to" => $target_line_user_id,
                            "messages" => [
                                [
                                    "type" => "flex",
                                    "altText" => "อนุมัติการชำระค่าส่วนกลาง", // This text is shown in the notification
                                    "contents" => $flex_message_content
                                ]
                            ]
                        ]);

                        // Send the request using cURL
                        $ch = curl_init();
                        curl_setopt($ch, CURLOPT_URL, $line_api_url);
                        curl_setopt($ch, CURLOPT_POST, 1);
                        curl_setopt($ch, CURLOPT_POSTFIELDS, $post_data);
                        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
                        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
                        // For production, adjust SSL verification
                        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
                        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 1);

                        $result = curl_exec($ch);
                        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);

                        // Check for cURL errors
                        if (curl_errno($ch)) {
                            error_log("cURL Error for LINE Push Message (ID: {$id}, User: {$target_line_user_id}): " . curl_error($ch));
                        } else {
                            // Check for LINE API errors
                            if ($http_code !== 200) {
                                $line_response = json_decode($result, true);
                                error_log("LINE Push Message failed (HTTP Status: {$http_code}) for ID: {$id}, User: {$target_line_user_id}. Response: " . print_r($line_response, true));
                            }
                        }
                        curl_close($ch);
                    }
                    // *** END of Flex Message implementation ***
                } else {
                    // If no line_user_id is found for this house number
                    error_log("ไม่พบ Line User ID สำหรับบ้านเลขที่: " . $house_number_to_notify . " ใน ims_house_line_user");
                }
            }

        } else {
            // If the record to update is not found (invalid ID)
            echo "ไม่พบรายการที่ต้องการอัปเดต (ID: {$id})";
        }
    } else {
        // If house_number data is missing
        echo "ข้อมูล House Number ไม่ครบถ้วน";
    }

    exit; // Stop execution after processing the update
}

// Delete data
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

// Fetch data for the DataTable
if ($_POST["action"] === 'GET_COMMON_FEE') {

    ## Read value from DataTable's request
    $draw = $_POST['draw'];
    $row = $_POST['start'];
    $rowperpage = $_POST['length']; // Number of rows per page
    $columnIndex = $_POST['order'][0]['column']; // Index of the column to sort by
    $columnName = $_POST['columns'][$columnIndex]['data']; // Column name for sorting
    $columnSortOrder = 'desc'; // Always sort descending
    $searchValue = $_POST['search']['value']; // User-input search value

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
        . " ORDER BY id DESC " . " LIMIT :limit,:offset"; // Sort by ID descending

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
    $isManager = $_SESSION['account_type'] === "manager";
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
                "delete" => $isUser || $isManager ? "<button type='button' class='btn btn-danger btn-xs delete' disabled>Delete</button>"
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