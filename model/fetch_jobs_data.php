<?php
// fetch_sales.php
include '../config/connect_db.php';

$start_date = $_POST['start_date'];
$end_date = $_POST['end_date'];

$sql_jobs = " SELECT CONCAT(e.emp_id , \" \" , e.f_name) AS employee_name,substring(j.job_date,1,2) AS job_date,SUM(j.total_money) AS total_money
    FROM job_transaction j
    JOIN job_memployee e ON j.emp_id = e.emp_id
    WHERE j.job_date_calendar BETWEEN ? AND ?
    GROUP BY e.emp_id,e.f_name, j.job_date
    ORDER BY e.emp_id,e.f_name, j.job_date ";

/*
$myfile = fopen("job-getdata.txt", "w") or die("Unable to open file!");
fwrite($myfile, $sql_jobs . " | " . $start_date . " | " . $end_date);
fclose($myfile);
*/

$stmt = $conn->prepare($sql_jobs);

$stmt->execute([$start_date, $end_date]);

$jobs = $stmt->fetchAll(PDO::FETCH_ASSOC);

$result = [];
$totalAmount = 0;

foreach ($jobs as $job) {
    $result[$job['employee_name']][$job['job_date']] = $job['total_money'];
    $result[$job['employee_name']]['total'] = isset($result[$job['employee_name']]['total']) ? $result[$job['employee_name']]['total'] + $job['total_money'] : $job['total_money'];
    $totalAmount += $job['total_money'];
}

echo json_encode(['data' => $result, 'total' => $totalAmount]);


