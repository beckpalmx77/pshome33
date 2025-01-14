<?php
include '../config/connect_db.php';


// Number of records fetch
$numberofrecords = 10;

if(!isset($_POST['searchTerm'])){

    // Fetch records
    $stmt = $conn->prepare("SELECT * FROM ims_unit ORDER BY unit_name LIMIT :limit");
    $stmt->bindValue(':limit', (int)$numberofrecords, PDO::PARAM_INT);
    $stmt->execute();
    $ims_unitList = $stmt->fetchAll();

}else{

    $search = $_POST['searchTerm'];// Search text

    // Fetch records
    $stmt = $conn->prepare("SELECT * FROM ims_unit WHERE unit_name like :unit_name ORDER BY unit_name LIMIT :limit");
    $stmt->bindValue(':unit_name', '%'.$search.'%', PDO::PARAM_STR);
    $stmt->bindValue(':limit', (int)$numberofrecords, PDO::PARAM_INT);
    $stmt->execute();
    $ims_unitList = $stmt->fetchAll();

}

$response = array();

// Read Data
foreach($ims_unitList as $user){
    $response[] = array(
        "id" => $user['unit_name'],
        "text" => $user['unit_name']
    );
}

echo json_encode($response);
exit();