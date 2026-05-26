<?php
include('../config/connect_db.php');

$action = $_POST['action'] ?? '';
$date_start = $_POST['date_start'] ?? '';
$date_end = $_POST['date_end'] ?? '';

if ($action === 'GET_TRIAL_BALANCE') {
    $searchQuery = "";
    $params = [];
    if ($date_start != '') {
        $searchQuery .= " AND h.gl_date >= :date_start ";
        $params['date_start'] = $date_start;
    }
    if ($date_end != '') {
        $searchQuery .= " AND h.gl_date <= :date_end ";
        $params['date_end'] = $date_end;
    }

    $sql = "SELECT a.acc_code, a.acc_name, a.acc_group,
                   SUM(d.dr_amount) as total_dr, 
                   SUM(d.cr_amount) as total_cr
            FROM ims_chart_of_accounts a
            LEFT JOIN ims_gl_details d ON a.acc_code = d.acc_code
            LEFT JOIN ims_gl_header h ON d.gl_id = h.gl_id
            WHERE 1 $searchQuery
            GROUP BY a.acc_code, a.acc_name, a.acc_group
            ORDER BY a.acc_code ASC";

    $stmt = $conn->prepare($sql);
    $stmt->execute($params);
    $records = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $data = [];
    $sum_dr = 0;
    $sum_cr = 0;

    foreach ($records as $row) {
        $dr = (float)$row['total_dr'];
        $cr = (float)$row['total_cr'];
        $sum_dr += $dr;
        $sum_cr += $cr;

        $data[] = [
            "acc_code" => $row['acc_code'],
            "acc_name" => $row['acc_name'],
            "acc_group" => $row['acc_group'],
            "dr" => number_format($dr, 2),
            "cr" => number_format($cr, 2)
        ];
    }

    echo json_encode([
        "aaData" => $data,
        "total_dr" => number_format($sum_dr, 2),
        "total_cr" => number_format($sum_cr, 2)
    ]);
}

if ($action === 'GET_PNL') {
    $searchQuery = "";
    $params = [];
    if ($date_start != '') {
        $searchQuery .= " AND h.gl_date >= :date_start ";
        $params['date_start'] = $date_start;
    }
    if ($date_end != '') {
        $searchQuery .= " AND h.gl_date <= :date_end ";
        $params['date_end'] = $date_end;
    }

    // Only Revenue and Expense groups
    $sql = "SELECT a.acc_code, a.acc_name, a.acc_group,
                   SUM(d.dr_amount) as total_dr, 
                   SUM(d.cr_amount) as total_cr
            FROM ims_chart_of_accounts a
            LEFT JOIN ims_gl_details d ON a.acc_code = d.acc_code
            LEFT JOIN ims_gl_header h ON d.gl_id = h.gl_id
            WHERE a.acc_group IN ('Revenue', 'Expense') $searchQuery
            GROUP BY a.acc_code, a.acc_name, a.acc_group
            ORDER BY a.acc_group DESC, a.acc_code ASC";

    $stmt = $conn->prepare($sql);
    $stmt->execute($params);
    $records = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $revenue = [];
    $expense = [];
    $total_rev = 0;
    $total_exp = 0;

    foreach ($records as $row) {
        $balance = 0;
        if ($row['acc_group'] === 'Revenue') {
            $balance = (float)$row['total_cr'] - (float)$row['total_dr'];
            $total_rev += $balance;
            $revenue[] = ["name" => $row['acc_name'], "amount" => number_format($balance, 2)];
        } else {
            $balance = (float)$row['total_dr'] - (float)$row['total_cr'];
            $total_exp += $balance;
            $expense[] = ["name" => $row['acc_name'], "amount" => number_format($balance, 2)];
        }
    }

    echo json_encode([
        "revenue" => $revenue,
        "expense" => $expense,
        "total_revenue" => number_format($total_rev, 2),
        "total_expense" => number_format($total_exp, 2),
        "net_profit" => number_format($total_rev - $total_exp, 2)
    ]);
}
