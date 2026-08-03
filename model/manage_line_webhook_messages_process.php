<?php
session_start();
error_reporting(0);

include('../config/connect_db.php');

if ($_POST["action"] === 'GET_MESSAGES') {

    ## Delete empty/null records
    $stmtDelete = $conn->prepare("DELETE FROM ims_line_webhook_messages WHERE (line_user_id IS NULL OR line_user_id = '') OR (line_display_name IS NULL OR line_display_name = '') OR (message_type IS NULL OR message_type = '') OR (message_text IS NULL OR message_text = '')");
    $stmtDelete->execute();

    ## Read value
    $draw = $_POST['draw'];
    $row = $_POST['start'];
    $rowperpage = $_POST['length']; // Rows display per page
    $columnIndex = $_POST['order'][0]['column']; // Column index
    $columnName = $_POST['columns'][$columnIndex]['data']; // Column name
    $columnSortOrder = $_POST['order'][0]['dir']; // asc or desc
    $searchValue = $_POST['search']['value']; // Search value

    $searchArray = array();

    ## Search
    $searchQuery = " ";
    if ($searchValue != '') {
        $searchQuery = " AND (message_text LIKE :message_text OR line_display_name LIKE :line_display_name) ";
        $searchArray = array(
            'message_text' => "%$searchValue%",
            'line_display_name' => "%$searchValue%",
        );
    }

    ## Total number of records without filtering (grouped)
    $stmt = $conn->prepare("SELECT COUNT(DISTINCT DATE_FORMAT(created_at, '%Y-%m-%d %H:%i')) AS allcount FROM ims_line_webhook_messages WHERE 1");
    $stmt->execute();
    $records = $stmt->fetch();
    $totalRecords = $records['allcount'];

    ## Total number of records with filtering (grouped)
    $stmt = $conn->prepare("SELECT COUNT(DISTINCT DATE_FORMAT(created_at, '%Y-%m-%d %H:%i')) AS allcount FROM ims_line_webhook_messages WHERE 1 " . $searchQuery);
    $stmt->execute($searchArray);
    $records = $stmt->fetch();
    $totalRecordwithFilter = $records['allcount'];

    ## Fetch records (grouped by minute)
    $sql_record = "SELECT 
                    DATE_FORMAT(created_at, '%Y-%m-%d %H:%i') as minute_group,
                    GROUP_CONCAT(IF(message_type = 'text', message_text, NULL) SEPARATOR ' | ') as texts,
                    GROUP_CONCAT(IF(message_type = 'image', photo_path, NULL) SEPARATOR ',') as images,
                    line_display_name,
                    group_id
                   FROM ims_line_webhook_messages 
                   WHERE 1 " . $searchQuery . " 
                   GROUP BY minute_group, line_user_id
                   ORDER BY minute_group DESC 
                   LIMIT :limit, :offset";

    $stmt = $conn->prepare($sql_record);

    foreach ($searchArray as $key => $search) {
        $stmt->bindValue(':' . $key, $search, PDO::PARAM_STR);
    }

    $stmt->bindValue(':limit', (int)$row, PDO::PARAM_INT);
    $stmt->bindValue(':offset', (int)$rowperpage, PDO::PARAM_INT);
    $stmt->execute();
    $empRecords = $stmt->fetchAll();
    $data = array();

    foreach ($empRecords as $row) {
        $imgs = !empty($row['images']) ? explode(',', $row['images']) : [];
        $imgColumns = array();
        for ($i = 0; $i < 5; $i++) {
            $colName = "image" . ($i + 1);
            if (isset($imgs[$i]) && !empty($imgs[$i])) {
                $imgColumns[$colName] = '<img src="uploads/visitor/' . $imgs[$i] . '" class="img-thumbnail m-1 img-preview" style="height: 50px; cursor: pointer;" data-img="uploads/visitor/' . $imgs[$i] . '">';
            } else {
                $imgColumns[$colName] = '-';
            }
        }

        $data[] = array(
            "minute_group" => $row['minute_group'],
            "line_display_name" => $row['line_display_name'],
            "texts" => $row['texts'] ? $row['texts'] : '-',
            "image1" => $imgColumns['image1'],
            "image2" => $imgColumns['image2'],
            "image3" => $imgColumns['image3'],
            "image4" => $imgColumns['image4'],
            "image5" => $imgColumns['image5']
        );
    }

    ## Response Return Value
    $response = array(
        "draw" => intval($draw),
        "iTotalRecords" => $totalRecords,
        "iTotalDisplayRecords" => $totalRecordwithFilter,
        "aaData" => $data
    );

    echo json_encode($response);
}
?>