<?php
session_start();
error_reporting(0);

include('../config/connect_db.php');
include('../config/lang.php');
include('../util/record_util.php');

function formatThaiDateLong($date) {
    if (!$date || $date == '0000-00-00') return '-';
    $thai_days = ['อาทิตย์', 'จันทร์', 'อังคาร', 'พุธ', 'พฤหัสบดี', 'ศุกร์', 'เสาร์'];
    $thai_months = [
        1 => 'มกราคม', 2 => 'กุมภาพันธ์', 3 => 'มีนาคม', 4 => 'เมษายน',
        5 => 'พฤษภาคม', 6 => 'มิถุนายน', 7 => 'กรกฎาคม', 8 => 'สิงหาคม',
        9 => 'กันยายน', 10 => 'ตุลาคม', 11 => 'พฤศจิกายน', 12 => 'ธันวาคม'
    ];
    $date_obj = strtotime($date);
    $day_of_week = date('w', $date_obj);
    $d = date('j', $date_obj);
    $m = (int)date('n', $date_obj);
    $y = date('Y', $date_obj) + 543;
    return "วัน" . $thai_days[$day_of_week] . "ที่ $d " . $thai_months[$m] . " $y";
}

function formatThaiDateTime($datetime) {
    if (!$datetime) return '-';
    $date_obj = strtotime($datetime);
    $d = date('d/m', $date_obj);
    $y = date('Y', $date_obj) + 543;
    $t = date('H:i:s', $date_obj);
    return "$d/$y $t";
}

if ($_POST["action"] === 'GET_CHECK_IN_OUT_DAILY') {

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
        $searchQuery = " AND (emp_name LIKE :emp_name or display_name LIKE :display_name or checkin_date LIKE :checkin_date) ";
        $searchArray = array(
            'emp_name' => "%$searchValue%",
            'display_name' => "%$searchValue%",
            'checkin_date' => "%$searchValue%",
        );
    }

    // Add Date Filter
    if ($_POST["date_start"] != '' && $_POST["date_end"] != '') {
        $searchQuery .= " AND DATE(DATE_SUB(checkin_time, INTERVAL 9 HOUR)) BETWEEN :date_start AND :date_end ";
        $searchArray['date_start'] = $_POST["date_start"];
        $searchArray['date_end'] = $_POST["date_end"];
    } elseif ($_POST["date_start"] != '') {
        $searchQuery .= " AND DATE(DATE_SUB(checkin_time, INTERVAL 9 HOUR)) >= :date_start ";
        $searchArray['date_start'] = $_POST["date_start"];
    } elseif ($_POST["date_end"] != '') {
        $searchQuery .= " AND DATE(DATE_SUB(checkin_time, INTERVAL 9 HOUR)) <= :date_end ";
        $searchArray['date_end'] = $_POST["date_end"];
    }

    $base_sql = "SELECT 
                    user_id, 
                    display_name,
                    emp_name, 
                    DATE(DATE_SUB(checkin_time, INTERVAL 9 HOUR)) as shift_date,
                    line_picture_profile,
                    MIN(checkin_time) as time_in,
                    MAX(checkin_time) as time_out,
                    COUNT(*) as record_count
                FROM v_checkins_time
                WHERE 1=1 " . $searchQuery . "
                GROUP BY user_id, shift_date, emp_name, display_name, line_picture_profile";

    ## Total number of records without filtering
    $sql_total = "SELECT COUNT(*) as allcount FROM ($base_sql) as t";
    // We'll simplify the count queries to match the new logic
    $stmt_total = $conn->prepare("SELECT COUNT(*) AS allcount FROM (SELECT 1 FROM v_checkins_time GROUP BY user_id, DATE(DATE_SUB(checkin_time, INTERVAL 9 HOUR))) as t");
    $stmt_total->execute();
    $totalRecords = $stmt_total->fetch()['allcount'];

    ## Total number of records with filtering
    $stmt_filter = $conn->prepare("SELECT COUNT(*) AS allcount FROM (SELECT 1 FROM v_checkins_time WHERE 1=1 " . $searchQuery . " GROUP BY user_id, DATE(DATE_SUB(checkin_time, INTERVAL 9 HOUR))) as t");
    $stmt_filter->execute($searchArray);
    $totalRecordwithFilter = $stmt_filter->fetch()['allcount'];

    ## Fetch records
    $sql_data = $base_sql . " ORDER BY shift_date DESC, emp_name ASC LIMIT :limit, :offset";
    $stmt = $conn->prepare($sql_data);

    // Bind values
    foreach ($searchArray as $key => $search) {
        $stmt->bindValue(':' . $key, $search, PDO::PARAM_STR);
    }

    $stmt->bindValue(':limit', (int)$row, PDO::PARAM_INT);
    $stmt->bindValue(':offset', (int)$rowperpage, PDO::PARAM_INT);
    $stmt->execute();
    $empRecords = $stmt->fetchAll();
    $data = array();

    foreach ($empRecords as $row) {
        $data[] = array(
            "checkin_date" => formatThaiDateLong($row['shift_date']),
            "emp_name" => $row['emp_name'] ?: $row['display_name'],
            "line_picture_profile" => $row['line_picture_profile'],
            "time_in" => formatThaiDateTime($row['time_in']),
            "time_out" => ($row['time_out'] && ($row['time_out'] !== $row['time_in'] || $row['record_count'] > 1)) ? formatThaiDateTime($row['time_out']) : '-',
            "line_picture" => '<img src="' . ($row['line_picture_profile'] ?: 'img/icon/none_img.png') . '" alt="image" style="width: 50px; height: auto;">'
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
