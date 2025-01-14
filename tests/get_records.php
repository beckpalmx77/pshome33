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
    $searchQuery = " AND (email LIKE :email or 
        first_name LIKE :first_name OR
        last_name LIKE :last_name OR         
        status LIKE :status ) ";
    $searchArray = array(
        'email' => "%$searchValue%",
        'first_name' => "%$searchValue%",
        'last_name' => "%$searchValue%",
        'status' => "%$searchValue%"
    );
}

## Total number of records without filtering
$stmt = $conn->prepare("SELECT COUNT(*) AS allcount FROM ims_user ");
$stmt->execute();
$records = $stmt->fetch();
$totalRecords = $records['allcount'];

## Total number of records with filtering
$stmt = $conn->prepare("SELECT COUNT(*) AS allcount FROM ims_user WHERE 1 " . $searchQuery);
$stmt->execute($searchArray);
$records = $stmt->fetch();
$totalRecordwithFilter = $records['allcount'];

## Fetch records
$stmt = $conn->prepare("SELECT * FROM ims_user WHERE 1 " . $searchQuery . " ORDER BY " . $columnName . " " . $columnSortOrder . " LIMIT :limit,:offset");

// Bind values
foreach ($searchArray as $key => $search) {
    $stmt->bindValue(':' . $key, $search, PDO::PARAM_STR);
}

$stmt->bindValue(':limit', (int)$row, PDO::PARAM_INT);
$stmt->bindValue(':offset', (int)$rowperpage, PDO::PARAM_INT);
$stmt->execute();
$empRecords = $stmt->fetchAll();
$data = array();
$loop = 1;
foreach ($empRecords as $row) {

    $data[] = array(
        "id" => $loop++,
        "email" => $row['email'],
        "first_name" => $row['first_name'],
        "last_name" => $row['last_name'],
        "update" => "<button type='button' name='update' id='" . $row['id'] . "' class='btn btn-info btn-xs update'>Update</button>",
        "delete" => "<button type='button' name='delete' id='" . $row['id'] . "' class='btn btn-danger btn-xs delete'>Delete</button>",
        //"link" => "<a href='#' id='" . $row['id'] . "' onclick='MyFunction(" . $row['id'] . ");' data-toggle=" . $row['id'] . ">LINK</a>",
        "rec_id" => "<input type='hidden' id='rec_id' " . $row['id'] .  " name='rec_id' " . $row['id'] .  ">",
        "status" => $row['status']
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