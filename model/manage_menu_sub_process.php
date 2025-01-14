<?php
session_start();
error_reporting(0);

include('../config/connect_db.php');
include('../config/lang.php');
include('../util/record_util.php');

if ($_POST["action"] === 'GET_DATA') {
    $id = $_POST["id"];
    $return_arr = array();
    $sql_get = "SELECT menu_sub.*,menu_main.label as main_label FROM menu_sub
    LEFT JOIN  menu_main 
    ON menu_main.main_menu_id =  menu_sub.main_menu_id
    WHERE menu_sub.id = " . $id;
    $statement = $conn->query($sql_get);
    $results = $statement->fetchAll(PDO::FETCH_ASSOC);

    foreach ($results as $result) {
        $return_arr[] = array("id" => $result['id'],
            "sub_menu_id" => $result['sub_menu_id'],
            "main_menu_id" => $result['main_menu_id'],
            "label" => $result['label'],
            "sort" => $result['sort'],
            "main_label" => $result['main_label'],
            "link" => $result['link'],
            "icon" => $result['icon'],
            "privilege" => $result['privilege']);
    }

    echo json_encode($return_arr);

}

if ($_POST["action"] === 'SEARCH') {
    if ($_POST["label"] !== '') {
        $label = $_POST["label"];
        $sql_find = "SELECT * FROM menu_sub WHERE label = '" . $label . "'";
        $nRows = $conn->query($sql_find)->fetchColumn();
        if ($nRows > 0) {
            echo 2;
        } else {
            echo 1;
        }
    }
}

if ($_POST["action"] === 'ADD') {
    if ($_POST["label"] !== '') {
        $main_menu_id = $_POST["main_menu_id"];
        $prefix_menu_id = "S" . substr($_POST["main_menu_id"],3);
        $sub_menu_id = $prefix_menu_id . sprintf('%02s', LAST_ID_COND($conn, "menu_sub", $prefix_menu_id,'sub_menu_id'));
        $label = $_POST["label"];
        $label_en = $_POST["label"];
        $link = $_POST["link"];
        $icon = $_POST["icon"];
        $privilege = $_POST["privilege"];
        $sql_find = "SELECT * FROM menu_sub WHERE label = '" . $label . "'";

        $nRows = $conn->query($sql_find)->fetchColumn();
        if ($nRows > 0) {
            echo $dup;
        } else {
            $sql = "INSERT INTO menu_sub(sub_menu_id,main_menu_id,label,label_en,link,icon,privilege) 
            VALUES (:sub_menu_id,:main_menu_id,:label,:label_en,:link,:icon,:privilege)";
            $query = $conn->prepare($sql);
            $query->bindParam(':sub_menu_id', $sub_menu_id, PDO::PARAM_STR);
            $query->bindParam(':main_menu_id', $main_menu_id, PDO::PARAM_STR);
            $query->bindParam(':label', $label, PDO::PARAM_STR);
            $query->bindParam(':label_en', $label_en, PDO::PARAM_STR);
            $query->bindParam(':link', $link, PDO::PARAM_STR);
            $query->bindParam(':icon', $icon, PDO::PARAM_STR);
            $query->bindParam(':icon', $icon, PDO::PARAM_STR);
            $query->bindParam(':privilege', $privilege, PDO::PARAM_STR);
            $query->execute();
            $lastInsertId = $conn->lastInsertId();

            if ($lastInsertId) {
                echo $save_success;
            } else {
                echo $error;
            }
        }
    }
}


if ($_POST["action"] === 'UPDATE') {
    if ($_POST["main_menu_id"] != '') {
        $id = $_POST["id"];
        $main_menu_id = $_POST["main_menu_id"];
        $label = $_POST["label"];
        $link = $_POST["link"];
        $icon = $_POST["icon"];
        $privilege = $_POST["privilege"];
        $sql_find = "SELECT * FROM menu_sub WHERE id = '" . $id . "'";
        $nRows = $conn->query($sql_find)->fetchColumn();
        if ($nRows > 0) {
            $sql_update = "UPDATE menu_sub SET label=:label
            ,link=:link,icon=:icon,privilege=:privilege
            WHERE id = :id";
            $query = $conn->prepare($sql_update);
            $query->bindParam(':label', $label, PDO::PARAM_STR);
            $query->bindParam(':link', $link, PDO::PARAM_STR);
            $query->bindParam(':icon', $icon, PDO::PARAM_STR);
            $query->bindParam(':privilege', $privilege, PDO::PARAM_STR);
            $query->bindParam(':id', $id, PDO::PARAM_STR);
            $query->execute();
            echo $save_success;
        }
    }
}


if ($_POST["action"] === 'DELETE') {
    $id = $_POST["id"];
    $sql_find = "SELECT * FROM menu_sub WHERE id = " . $id;
    $nRows = $conn->query($sql_find)->fetchColumn();
    if ($nRows > 0) {
        try {
            $sql = "DELETE FROM menu_sub WHERE id = " . $id;
            $query = $conn->prepare($sql);
            $query->execute();
            echo $del_success;
        } catch (Exception $e) {
            echo 'Message: ' . $e->getMessage();
        }
    }
}

if ($_POST["action"] === 'GET_SUB_MENU') {
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
        $searchQuery = " AND (main_menu_id LIKE :main_menu_id or
        label LIKE :label ) ";
        $searchArray = array(
            'main_menu_id' => "%$searchValue%",
            'label' => "%$searchValue%",
        );
    }

## Total number of records without filtering
    $stmt = $conn->prepare("SELECT COUNT(*) AS allcount FROM menu_sub ");
    $stmt->execute();
    $records = $stmt->fetch();
    $totalRecords = $records['allcount'];

## Total number of records with filtering
    $stmt = $conn->prepare("SELECT COUNT(*) AS allcount FROM menu_sub WHERE 1 " . $searchQuery);
    $stmt->execute($searchArray);
    $records = $stmt->fetch();
    $totalRecordwithFilter = $records['allcount'];

## Fetch records
    $stmt = $conn->prepare("SELECT * FROM menu_sub WHERE 1 " . $searchQuery
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

        if ($_POST['sub_action'] === "GET_MASTER") {
            $data[] = array(
                "id" => $row['id'],
                "sub_menu_id" => $row['sub_menu_id'],
                "main_menu_id" => $row['main_menu_id'],
                "label" => $row['label'],
                "link" => $row['link'],
                "sort" => $row['sort'],
                "icon" => $row['icon'],
                "update" => "<button type='button' name='update' id='" . $row['id'] . "' class='btn btn-info btn-xs update' data-toggle='tooltip' title='Update'>Update</button>",
                "delete" => "<button type='button' name='delete' id='" . $row['id'] . "' class='btn btn-danger btn-xs delete' data-toggle='tooltip' title='Delete'>Delete</button>",
                "privilege" => $row['privilege'] === 'Active' ? "<div class='text-success'>" . $row['privilege'] . "</div>" : "<div class='text-muted'> " . $row['privilege'] . "</div>"
            );
        } else {
            $data[] = array(
                "id" => $row['id'],
                "main_menu_id" => $row['main_menu_id'],
                "label" => $row['label'],
                "select" => "<button type='button' name='select' id='" . $row['main_menu_id'] . "@" . $row['label'] . "' class='btn btn-outline-success btn-xs select' data-toggle='tooltip' title='select'>select <i class='fa fa-check' aria-hidden='true'></i>
</button>",
            );
        }

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

