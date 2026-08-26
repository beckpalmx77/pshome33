<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
error_reporting(0); // It's recommended to turn this off in a production environment.

include('../config/connect_db.php'); // Database connection file
include('../util/gl_util.php'); // Utility for GL
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
        $remark = $_POST["remark"];
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
                payment_method = :payment_method,
                remark =:remark   
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
            $query->bindParam(':remark', $remark, PDO::PARAM_STR);
            $query->bindParam(':id', $id, PDO::PARAM_INT);
            $query->execute();

            echo $save_success;

            // --- Step 2.1: Accounting Posting (GL) ---
            $sql_fetch_doc = "SELECT doc_id, payment_date, house_number FROM ims_house_payment WHERE id = :id";
            $stmt_doc = $conn->prepare($sql_fetch_doc);
            $stmt_doc->bindParam(':id', $id);
            $stmt_doc->execute();
            $doc_info = $stmt_doc->fetch(PDO::FETCH_ASSOC);
            $doc_id = $doc_info['doc_id'];
            $payment_date = $doc_info['payment_date'];
            $house_no = $doc_info['house_number'];

            // ลบรายการเดิมออกก่อนเสมอ (ถ้ามี)
            RemoveGLByDocNo($conn, $doc_id);

            // ถ้าสถานะเป็น 'Y' ให้ลงบัญชีใหม่
            if ($payment_status === 'Y') {
                $gl_entries = [];
                
                // Debit: Cash or Bank (1101/1102)
                $payment_acc = GetAccountCodeMapping($conn, $payment_method, 'payment');
                $gl_entries[] = [
                    'acc_code' => $payment_acc,
                    'dr' => $amount,
                    'cr' => 0
                ];

                // Credit: Common Fee Revenue (4101)
                $gl_entries[] = [
                    'acc_code' => '4101', 
                    'dr' => 0,
                    'cr' => $amount
                ];

                $gl_desc = "รับชำระค่าส่วนกลาง บ้านเลขที่ $house_no (งวด $month_name_start - $month_name_to ปี $period_year) ตามเอกสาร $doc_id";
                PostToGL($conn, $payment_date, $doc_id, $gl_desc, $gl_entries, 'RV');
            }

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
    $draw = intval($_POST['draw']);
    $row = intval($_POST['start']);
    $rowperpage = intval($_POST['length']); // Number of rows per page
    $searchValue = isset($_POST['search']['value']) ? trim($_POST['search']['value']) : ''; // User-input search value
    $searchHouseNumber = isset($_POST['searchHouseNumber']) ? trim($_POST['searchHouseNumber']) : ''; // Exact House Number search

    $thaiMonths = [
        1 => 'มกราคม', 2 => 'กุมภาพันธ์', 3 => 'มีนาคม', 4 => 'เมษายน',
        5 => 'พฤษภาคม', 6 => 'มิถุนายน', 7 => 'กรกฎาคม', 8 => 'สิงหาคม',
        9 => 'กันยายน', 10 => 'ตุลาคม', 11 => 'พฤศจิกายน', 12 => 'ธันวาคม'
    ];

    $searchArray = array();

    $where_house_number = "";
    $where_house_number_qualified = "";
    if ($_SESSION['account_type'] === "user") {
        $where_house_number = " AND house_number = '" . $_SESSION['house_number'] . "'";
        $where_house_number_qualified = " AND h.house_number = '" . $_SESSION['house_number'] . "'";
    }

    ## Total number of records without filtering
    $sql_total = "SELECT COUNT(id) AS allcount FROM ims_house_payment WHERE 1=1 " . $where_house_number;
    $stmt = $conn->prepare($sql_total);
    $stmt->execute();
    $totalRecords = $stmt->fetchColumn() ?: 0;

    ## Search Query & Index Hint
    $searchQuery = "";
    $forceIndex = "";

    if ($searchHouseNumber !== '') {
        $searchQuery = " AND h.house_number = :house_number_exact ";
        $searchArray['house_number_exact'] = $searchHouseNumber;

        $sql_filter = "SELECT COUNT(id) AS allcount FROM ims_house_payment WHERE house_number = :house_number_exact " . $where_house_number;
        $stmt = $conn->prepare($sql_filter);
        $stmt->execute(['house_number_exact' => $searchHouseNumber]);
        $totalRecordwithFilter = $stmt->fetchColumn() ?: 0;
    } elseif ($searchValue !== '') {
        $searchQuery = " AND (h.doc_id LIKE :search1 OR 
                             h.house_number LIKE :search2 OR 
                             h.detail LIKE :search3 OR 
                             house.alley LIKE :search4 OR 
                             h.remark LIKE :search5) ";
        $searchArray['search1'] = "%$searchValue%";
        $searchArray['search2'] = "%$searchValue%";
        $searchArray['search3'] = "%$searchValue%";
        $searchArray['search4'] = "%$searchValue%";
        $searchArray['search5'] = "%$searchValue%";

        $sql_filter = "SELECT COUNT(h.id) AS allcount FROM ims_house_payment h 
                        LEFT JOIN ims_house house ON h.house_number = house.house_number
                        WHERE 1=1 " . $searchQuery . $where_house_number_qualified;
        $stmt = $conn->prepare($sql_filter);
        $stmt->execute($searchArray);
        $totalRecordwithFilter = $stmt->fetchColumn() ?: 0;
    } else {
        $totalRecordwithFilter = $totalRecords;
        $forceIndex = "FORCE INDEX (PRIMARY)";
    }

    ## Fetch records (optimized: removed duplicate ims_month joins, using direct index scan)
    $sql_getdata = "SELECT 
        h.id, h.runno, h.doc_id, h.payment_date, h.house_number, h.detail, 
        h.period_month_start, h.period_month_to, h.period_year, h.amount, 
        h.picture_payment, h.remark, h.payment_type, h.payment_status,
        h.created_at, h.updated_at, h.print_first_date, h.print_last_date, h.print_status,
        house.alley,
        house.contact_name,
        house.phone_number,
        h.line_user_id,
        h.line_picture_profile_show,
        hm.area_size,
        hm.garbage_collection_fee,
        hm.common_fee,
        h.payment_method,
        h.create_by,
        h.approve_by,
        h.update_count
     FROM ims_house_payment h {$forceIndex}
     LEFT JOIN ims_house house ON h.house_number = house.house_number
     LEFT JOIN ims_house_master hm ON hm.house_number = h.house_number
     WHERE 1=1 " . $searchQuery . $where_house_number_qualified
     . " ORDER BY h.id DESC LIMIT :limit, :offset";

    $stmt = $conn->prepare($sql_getdata);

    // Bind values
    foreach ($searchArray as $key => $search) {
        $stmt->bindValue(':' . $key, $search, PDO::PARAM_STR);
    }

    $stmt->bindValue(':limit', (int)$row, PDO::PARAM_INT);
    $stmt->bindValue(':offset', (int)$rowperpage, PDO::PARAM_INT);
    $stmt->execute();
    $empRecords = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $data = array();

    $isUser = $_SESSION['account_type'] === "user";
    $isManager = $_SESSION['account_type'] === "manager";
    $isMaster = $_POST['sub_action'] === "GET_MASTER";

    $statusMeta = [
        'Y' => ['desc' => "ชำระเรียบร้อยแล้ว", 'color' => 'green', 'can_print' => true],
        'N' => ['desc' => "ยังไม่ยืนยันการชำระ", 'color' => 'gray', 'can_print' => false],
    ];

    foreach ($empRecords as $rowItem) {
        if ($isMaster) {
            $status = $rowItem['payment_status'];
            $meta = $statusMeta[$status] ?? ['desc' => '-', 'color' => 'gray', 'can_print' => false];

            $month_start_no = (int)($rowItem['period_month_start'] ?? 0);
            $month_to_no = (int)($rowItem['period_month_to'] ?? 0);
            $month_name_start = $thaiMonths[$month_start_no] ?? '';
            $month_name_to = $thaiMonths[$month_to_no] ?? '';
            $month_name_period = ($month_name_start && $month_name_to) ? "{$month_name_start} - {$month_name_to}" : ($month_name_start ?: $month_name_to);

            $data[] = [
                "id" => $rowItem['id'],
                "doc_id" => $rowItem['doc_id'],
                "payment_date" => $rowItem['payment_date'],
                "detail" => $rowItem['detail'],
                "house_number" => $rowItem['house_number'],
                "alley" => $rowItem['alley'],
                "contact_name" => $rowItem['contact_name'],
                "phone_number" => $rowItem['phone_number'],
                "payment_type" => $rowItem['payment_type'],
                "period_month_start" => $rowItem['period_month_start'],
                "period_month_to" => $rowItem['period_month_to'],
                "month_name_start" => $month_name_start,
                "month_name_to" => $month_name_to,
                "month_name_period" => $month_name_period,
                "period_year" => $rowItem['period_year'],
                "area_size" => $rowItem['area_size'],
                "garbage_collection_fee" => $rowItem['garbage_collection_fee'],
                "common_fee" => $rowItem['common_fee'],
                "amount" => $rowItem['amount'],
                "payment_status" => $rowItem['payment_status'],
                "line_picture_profile" => "<img src='" . ($rowItem['line_picture_profile_show'] ?: 'img/icon/none_img.png') . "' alt='image' style='width: 50px; height: auto;'>",
                "payment_status_desc" => "<span style='color: {$meta['color']}'>{$meta['desc']}</span>",
                "print" => "<button type='button' name='print' id='{$rowItem['id']}' class='btn btn-outline-success btn-xs print' " . ($meta['can_print'] ? "" : "disabled") . ">Print</button>",
                "slip" => "<button type='button' name='slip' id='{$rowItem['id']}' class='btn btn-info btn-xs slip'>Slip</button>",
                "update" => $isUser ? "<button type='button' class='btn btn-info btn-xs update' disabled>Update</button>"
                    : "<button type='button' name='update' id='{$rowItem['id']}' class='btn btn-info btn-xs update'>Update</button>",
                "delete" => $isUser || $isManager ? "<button type='button' class='btn btn-danger btn-xs delete' disabled>Delete</button>"
                    : "<button type='button' name='delete' id='{$rowItem['id']}' class='btn btn-danger btn-xs delete'>Delete</button>",
                "remark" => $rowItem['remark']
            ];
        } else {
            $data[] = [
                "id" => $rowItem['id'],
                "house_number" => $rowItem['house_number'],
                "contact_name" => $rowItem['contact_name'],
                "select" => "<button type='button' name='select' id='{$rowItem['house_number']}@{$rowItem['contact_name']}' class='btn btn-outline-success btn-xs select'>select <i class='fa fa-check'></i></button>"
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