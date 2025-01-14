<?php
include('../config/connect_db.php');

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
    $searchQuery = " AND (product_id LIKE :product_id or 
        name_t LIKE :name_t OR
        name_e LIKE :name_e OR         
        status LIKE :status ) ";
    $searchArray = array(
        'product_id' => "%$searchValue%",
        'name_t' => "%$searchValue%",
        'name_e' => "%$searchValue%",
        'status' => "%$searchValue%"
    );
}

## Total number of records without filtering
$stmt = $conn->prepare("SELECT COUNT(*) AS allcount FROM ims_product ");
$stmt->execute();
$records = $stmt->fetch();
$totalRecords = $records['allcount'];

## Total number of records with filtering
$stmt = $conn->prepare("SELECT COUNT(*) AS allcount FROM ims_product WHERE 1 " . $searchQuery);
$stmt->execute($searchArray);
$records = $stmt->fetch();
$totalRecordwithFilter = $records['allcount'];

## Fetch records
$stmt = $conn->prepare("SELECT * FROM vims_product WHERE 1 " . $searchQuery
    . " ORDER BY " . $columnName . " " . $columnSortOrder . " LIMIT :limit,:offset");

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
        "product_id" => $row['product_id'],
        "name_t" => $row['name_t'],
        "name_e" => $row['name_e'],
        "quantity" => $row['quantity'],
        "unit_id" => $row['unit_id'],
        "unit_name" => $row['unit_name'],
        "update" => "<button type='button' name='update' id='" . $row['id'] . "' class='btn btn-info btn-xs update' data-toggle='tooltip' title='Update'>Update</button>",
        "delete" => "<button type='button' name='delete' id='" . $row['id'] . "' class='btn btn-danger btn-xs delete' data-toggle='tooltip' title='Delete'>Delete</button>",
        "picture" => "<img src = '" . $row['picture'] . "'  width='32' height='32' title='" . $row['name_t'] . "'>",
        "status" => $row['status']==='Active'? "<div class='text-success'>" . $row['status'] . "</div>" : "<div class='text-muted'> " . $row['status'] . "</div>"
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