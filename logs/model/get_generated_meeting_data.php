<?php
// model/get_generated_meeting_data.php
include('../config/connect_db.php');

// Parameters from Datatable
$draw = $_POST['draw'];
$start = $_POST['start'];
$length = $_POST['length'];
$searchValue = $_POST['search']['value'];

// Parameters from Custom Filters (ส่งมาจาก Ajax data function)
$meeting_year = isset($_POST['meeting_year']) ? $_POST['meeting_year'] : '';
$meeting_date = isset($_POST['meeting_date']) ? $_POST['meeting_date'] : '';

// Columns Mapping
$columns = [
    0 => 'house_number',
    1 => 'meeting_year',
    2 => 'meeting_date',
    3 => 'meeting_name',
    4 => 'discount_value',
    5 => 'meeting_status'
];

// Base SQL: ดึงจาก ims_house_meeting
$sql = "SELECT * FROM ims_house_meeting WHERE status = 'Y' ";

// Filter by Year and Date (ถ้ามีการส่งค่ามา)
if (!empty($meeting_year)) {
    $sql .= " AND meeting_year = :meeting_year ";
}
if (!empty($meeting_date)) {
    $sql .= " AND meeting_date = :meeting_date ";
}

// Global Search (พิมพ์ในช่อง Search มุมขวาตาราง)
if (!empty($searchValue)) {
    $sql .= " AND (house_number LIKE :search OR meeting_name LIKE :search) ";
}

// Order By
if (isset($_POST['order'])) {
    $columnIdx = $_POST['order'][0]['column'];
    $columnName = $columns[$columnIdx] ?? 'house_number';
    $dir = $_POST['order'][0]['dir'];
    $sql .= " ORDER BY $columnName $dir ";
} else {
    $sql .= " ORDER BY house_number ASC ";
}

// Limit
$sql .= " LIMIT :start, :length ";

$stmt = $conn->prepare($sql);

// Bind Parameters
if (!empty($meeting_year)) {
    $stmt->bindValue(':meeting_year', $meeting_year);
}
if (!empty($meeting_date)) {
    $stmt->bindValue(':meeting_date', $meeting_date);
}
if (!empty($searchValue)) {
    $stmt->bindValue(':search', "%$searchValue%");
}

$stmt->bindValue(':start', (int)$start, PDO::PARAM_INT);
$stmt->bindValue(':length', (int)$length, PDO::PARAM_INT);

$stmt->execute();
$data = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Count Total Filtered
$sqlCount = "SELECT COUNT(*) FROM ims_house_meeting WHERE status = 'Y' ";
if (!empty($meeting_year)) $sqlCount .= " AND meeting_year = :meeting_year ";
if (!empty($meeting_date)) $sqlCount .= " AND meeting_date = :meeting_date ";
if (!empty($searchValue)) $sqlCount .= " AND (house_number LIKE :search OR meeting_name LIKE :search) ";

$stmtCount = $conn->prepare($sqlCount);
if (!empty($meeting_year)) $stmtCount->bindValue(':meeting_year', $meeting_year);
if (!empty($meeting_date)) $stmtCount->bindValue(':meeting_date', $meeting_date);
if (!empty($searchValue)) $stmtCount->bindValue(':search', "%$searchValue%");
$stmtCount->execute();
$totalFiltered = $stmtCount->fetchColumn();

// Count Total All (Without Filter)
$sqlTotal = "SELECT COUNT(*) FROM ims_house_meeting WHERE status = 'Y'";
$stmtTotal = $conn->query($sqlTotal);
$totalRecords = $stmtTotal->fetchColumn();

echo json_encode([
    "draw" => intval($draw),
    "iTotalRecords" => $totalRecords,
    "iTotalDisplayRecords" => $totalFiltered,
    "aaData" => $data
]);