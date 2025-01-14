<?php
include('../config/connect_db.php');

try {
    $dept_ids = ['SLD', 'SL1', 'SL2', 'GRQ', 'WHL', 'ACC', 'OFF', 'OFP', 'BTC' ,'ITD'];
    $query = "SELECT emp_id, f_name, l_name, department_id FROM memployee WHERE dept_id_approve IN ('" . implode("','", $dept_ids) . "') ORDER BY dept_id_approve ";

    /*
    $txt = $query ;
    $my_file = fopen("a-leave_select.txt", "w") or die("Unable to open file!");
    fwrite($my_file,  $txt);
    fclose($my_file);
    */

    $stmt = $conn->prepare($query);
    $stmt->execute();

    $employees = [];
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $employees[] = [
            'emp_id' => $row['emp_id'],
            'f_name' => $row['f_name'],
            'l_name' => $row['l_name'],
            'department_id' => $row['department_id']
        ];
    }

    echo json_encode($employees);
} catch (PDOException $e) {
    echo json_encode(['error' => $e->getMessage()]);
}
