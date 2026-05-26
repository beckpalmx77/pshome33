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

// Column mapping for sorting (Summarized view)
$columns = [
    0 => 'h.gl_date',
    1 => 'h.doc_no',
    2 => 'h.description',
    3 => 'total_amount'
];
$columnName = $columns[$columnIndex] ?? 'h.gl_date';

function convertThaiDate($date) {
    if (strpos($date, '/') !== false) {
        $parts = explode('/', $date);
        if (count($parts) === 3) {
            $year = (int)$parts[2];
            if ($year > 2400) $year -= 543;
            return $year . '-' . $parts[1] . '-' . $parts[0];
        }
    }
    return $date;
}

$date_start = convertThaiDate($_POST['date_start'] ?? '');
$date_end = convertThaiDate($_POST['date_end'] ?? '');
$acc_code = $_POST['acc_code'] ?? '';

try {
    $searchQuery = " ";
    $searchArray = [];

    if ($searchValue != '') {
        $searchQuery .= " AND (h.doc_no LIKE :doc_no OR h.description LIKE :description) ";
        $searchArray['doc_no'] = "%$searchValue%";
        $searchArray['description'] = "%$searchValue%";
    }

    if ($date_start != '') {
        $searchQuery .= " AND h.gl_date >= :date_start ";
        $searchArray['date_start'] = $date_start;
    }

    if ($date_end != '') {
        $searchQuery .= " AND h.gl_date <= :date_end ";
        $searchArray['date_end'] = $date_end;
    }

    // Filter by acc_code in summarized view requires a subquery or JOIN
    $accFilterJoin = "";
    if ($acc_code != '') {
        $accFilterJoin = " JOIN ims_gl_details df ON h.gl_id = df.gl_id AND df.acc_code = :acc_code ";
        $searchArray['acc_code'] = $acc_code;
    }

    // Total records
    $stmt = $conn->prepare("SELECT COUNT(*) AS allcount FROM ims_gl_header");
    $stmt->execute();
    $totalRecords = $stmt->fetch()['allcount'];

    // Filtered records (Grouped)
    $sql_count = "SELECT COUNT(DISTINCT h.gl_id) AS allcount 
                  FROM ims_gl_header h 
                  $accFilterJoin
                  WHERE 1 " . $searchQuery;
    $stmtCount = $conn->prepare($sql_count);
    $stmtCount->execute($searchArray);
    $totalRecordwithFilter = $stmtCount->fetch()['allcount'];

    // Fetch data (Summarized by gl_id)
    $sql = "SELECT h.gl_id, h.gl_date, h.doc_no, h.description, SUM(d.dr_amount) as total_amount 
            FROM ims_gl_header h 
            JOIN ims_gl_details d ON h.gl_id = d.gl_id 
            $accFilterJoin
            WHERE 1 " . $searchQuery . " 
            GROUP BY h.gl_id 
            ORDER BY " . $columnName . " " . $columnSortOrder . " LIMIT :limit, :offset";

    $stmt = $conn->prepare($sql);
    foreach ($searchArray as $key => $search) {
        $stmt->bindValue(':' . $key, $search, PDO::PARAM_STR);
    }
    $stmt->bindValue(':limit', (int)$row, PDO::PARAM_INT);
    $stmt->bindValue(':offset', (int)$rowperpage, PDO::PARAM_INT);
    $stmt->execute();
    $records = $stmt->fetchAll();

    function formatThaiDate($date) {
        if (!$date || $date == '0000-00-00') return '';
        $parts = explode('-', $date);
        if (count($parts) === 3) {
            $year = (int)$parts[0] + 543;
            return $parts[2] . '/' . $parts[1] . '/' . $year;
        }
        return $date;
    }

    $data = [];
    foreach ($records as $row_data) {
        $data[] = [
            "gl_id" => $row_data['gl_id'],
            "gl_date" => date('d-m-Y', strtotime($row_data['gl_date'])),
            "doc_no" => $row_data['doc_no'],
            "description" => $row_data['description'],
            "total_amount" => number_format($row_data['total_amount'], 2)
        ];
    }

    echo json_encode([
        "draw" => intval($draw),
        "iTotalRecords" => (int)$totalRecords,
        "iTotalDisplayRecords" => (int)$totalRecordwithFilter,
        "aaData" => $data
    ]);

} catch (Exception $e) {
    echo json_encode(["error" => $e->getMessage()]);
}
