<?php
include('../config/connect_db.php');

$draw = $_POST['draw'];
$start = (int)$_POST['start'];
$length = (int)$_POST['length'];
$searchValue = $_POST['search']['value'];

$where = "1=1";
if (!empty($searchValue)) {
    $where .= " AND (topic LIKE :search OR meeting_year LIKE :search OR meeting_date LIKE :search OR meeting_day LIKE :search OR meeting_time LIKE :search OR meeting_location LIKE :search) ";
}

$sqlTotal = "SELECT COUNT(*) FROM ims_meeting_config";
$totalRecords = $conn->query($sqlTotal)->fetchColumn();

$sqlFiltered = "SELECT COUNT(*) FROM ims_meeting_config WHERE {$where}";
$stmt = $conn->prepare($sqlFiltered);
if (!empty($searchValue)) $stmt->bindValue(':search', '%' . $searchValue . '%', PDO::PARAM_STR);
$stmt->execute();
$totalFiltered = $stmt->fetchColumn();

$orderCol = isset($_POST['order'][0]['column']) ? (int)$_POST['order'][0]['column'] : 0;
$orderDir = isset($_POST['order'][0]['dir']) && $_POST['order'][0]['dir'] === 'asc' ? 'ASC' : 'DESC';
$cols = ['meeting_year', 'meeting_date', 'meeting_day', 'meeting_time', 'meeting_location'];
$orderBy = isset($cols[$orderCol]) ? $cols[$orderCol] : 'meeting_year';
$orderSql = "ORDER BY {$orderBy} {$orderDir}, meeting_date DESC";

$sql = "SELECT * FROM ims_meeting_config WHERE {$where} {$orderSql} LIMIT {$start}, {$length}";
$stmt = $conn->prepare($sql);
if (!empty($searchValue)) $stmt->bindValue(':search', '%' . $searchValue . '%', PDO::PARAM_STR);
$stmt->execute();
$data = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo json_encode([
    "draw" => intval($draw),
    "recordsTotal" => $totalRecords,
    "recordsFiltered" => $totalFiltered,
    "data" => $data
]);
