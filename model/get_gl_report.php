<?php
include('../config/connect_db.php');

header('Content-Type: application/json');

// Get POST values with defaults
$draw = $_POST['draw'] ?? 0;
$row = $_POST['start'] ?? 0;
$rowperpage = $_POST['length'] ?? 10;
$columnIndex = $_POST['order'][0]['column'] ?? 0;
$columnSortOrder = $_POST['order'][0]['dir'] ?? 'desc';
$searchValue = $_POST['search']['value'] ?? '';

// Column mapping for sorting
$columns = [
    0 => 'h.gl_date',
    1 => 'h.doc_no',
    2 => 'h.description',
    3 => 'd.acc_code',
    4 => 'a.acc_name',
    5 => 'd.dr_amount',
    6 => 'd.cr_amount'
];
$columnName = $columns[$columnIndex] ?? 'h.gl_date';

$date_start = $_POST['date_start'] ?? '';
$date_end = $_POST['date_end'] ?? '';
$acc_code = $_POST['acc_code'] ?? '';

try {
    $searchQuery = " ";
    $searchArray = [];

    if ($searchValue != '') {
        $searchQuery .= " AND (h.doc_no LIKE :doc_no OR h.description LIKE :description OR a.acc_name LIKE :acc_name) ";
        $searchArray['doc_no'] = "%$searchValue%";
        $searchArray['description'] = "%$searchValue%";
        $searchArray['acc_name'] = "%$searchValue%";
    }

    if ($date_start != '') {
        $searchQuery .= " AND h.gl_date >= :date_start ";
        $searchArray['date_start'] = $date_start;
    }

    if ($date_end != '') {
        $searchQuery .= " AND h.gl_date <= :date_end ";
        $searchArray['date_end'] = $date_end;
    }

    if ($acc_code != '') {
        $searchQuery .= " AND d.acc_code = :acc_code ";
        $searchArray['acc_code'] = $acc_code;
    }

    // Total records
    $stmt = $conn->prepare("SELECT COUNT(*) AS allcount FROM ims_gl_details d JOIN ims_gl_header h ON d.gl_id = h.gl_id");
    $stmt->execute();
    $totalRecords = $stmt->fetch()['allcount'];

    // Filtered records
    $stmtCount = $conn->prepare("SELECT COUNT(*) AS allcount FROM ims_gl_details d 
                            JOIN ims_gl_header h ON d.gl_id = h.gl_id 
                            LEFT JOIN ims_chart_of_accounts a ON d.acc_code = a.acc_code
                            WHERE 1 " . $searchQuery);
    $stmtCount->execute($searchArray);
    $totalRecordwithFilter = $stmtCount->fetch()['allcount'];

    // Sum totals
    $stmtSum = $conn->prepare("SELECT SUM(dr_amount) as total_dr, SUM(cr_amount) as total_cr FROM ims_gl_details d 
                            JOIN ims_gl_header h ON d.gl_id = h.gl_id 
                            LEFT JOIN ims_chart_of_accounts a ON d.acc_code = a.acc_code
                            WHERE 1 " . $searchQuery);
    $stmtSum->execute($searchArray);
    $totals = $stmtSum->fetch();
    $total_dr = number_format($totals['total_dr'] ?? 0, 2);
    $total_cr = number_format($totals['total_cr'] ?? 0, 2);

    // Fetch data
    $sql = "SELECT h.gl_id, h.gl_date, h.doc_no, h.description, d.acc_code, a.acc_name, d.dr_amount, d.cr_amount 
            FROM ims_gl_details d 
            JOIN ims_gl_header h ON d.gl_id = h.gl_id 
            LEFT JOIN ims_chart_of_accounts a ON d.acc_code = a.acc_code
            WHERE 1 " . $searchQuery . " 
            ORDER BY " . $columnName . " " . $columnSortOrder . " LIMIT :limit, :offset";

    $stmt = $conn->prepare($sql);
    foreach ($searchArray as $key => $search) {
        $stmt->bindValue(':' . $key, $search, PDO::PARAM_STR);
    }
    $stmt->bindValue(':limit', (int)$row, PDO::PARAM_INT);
    $stmt->bindValue(':offset', (int)$rowperpage, PDO::PARAM_INT);
    $stmt->execute();
    $records = $stmt->fetchAll();

    $data = [];
    foreach ($records as $row_data) {
        $data[] = [
            "gl_id" => $row_data['gl_id'],
            "gl_date" => date('d-m-Y', strtotime($row_data['gl_date'])),
            "doc_no" => $row_data['doc_no'],
            "description" => $row_data['description'],
            "acc_code" => $row_data['acc_code'],
            "acc_name" => $row_data['acc_name'],
            "dr_amount" => number_format($row_data['dr_amount'], 2),
            "cr_amount" => number_format($row_data['cr_amount'], 2)
        ];
    }

    echo json_encode([
        "draw" => intval($draw),
        "iTotalRecords" => (int)$totalRecords,
        "iTotalDisplayRecords" => (int)$totalRecordwithFilter,
        "aaData" => $data,
        "total_dr" => $total_dr,
        "total_cr" => $total_cr
    ]);

} catch (Exception $e) {
    echo json_encode(["error" => $e->getMessage()]);
}
