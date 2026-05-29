<?php
session_start();
include('../config/connect_db.php');
include('../util/month_util.php');

if (isset($_POST['action']) && $_POST['action'] === 'GET_CASH_FLOW') {
    $year = $_POST['year'];
    $months = $_POST['months'];

    if (empty($months)) {
        echo json_encode([]);
        exit;
    }

    $monthList = implode(',', array_map('intval', $months));

    // 1. Inflow from House Payments (Common Fees)
    // ใช้ payment_date เพื่อดูว่าเงินเข้าจริงวันไหน
    $sql_house = "SELECT MONTH(payment_date) as month, YEAR(payment_date) as year, SUM(amount) as total 
                  FROM ims_house_payment 
                  WHERE payment_status = 'Y' AND YEAR(payment_date) = :year AND MONTH(payment_date) IN ($monthList)
                  GROUP BY MONTH(payment_date), YEAR(payment_date)";
    $stmt_house = $conn->prepare($sql_house);
    $stmt_house->execute(['year' => $year]);
    $house_data = $stmt_house->fetchAll(PDO::FETCH_ASSOC);

    // 2. Inflow from Other Receipts
    $sql_rec = "SELECT rec_month as month, rec_year as year, SUM(amount) as total 
                FROM ims_reciepts 
                WHERE approve_status = 'Y' AND rec_year = :year AND CAST(rec_month AS UNSIGNED) IN ($monthList)
                GROUP BY rec_month, rec_year";
    $stmt_rec = $conn->prepare($sql_rec);
    $stmt_rec->execute(['year' => $year]);
    $rec_data = $stmt_rec->fetchAll(PDO::FETCH_ASSOC);

    // 3. Inflow from Petty Cash (+)
    $sql_petty_in = "SELECT MONTH(doc_date) as month, YEAR(doc_date) as year, SUM(amount) as total 
                     FROM ims_petty_cash 
                     WHERE status = 'Y' AND transaction_type = '+' AND YEAR(doc_date) = :year AND MONTH(doc_date) IN ($monthList)
                     GROUP BY MONTH(doc_date), YEAR(doc_date)";
    $stmt_petty_in = $conn->prepare($sql_petty_in);
    $stmt_petty_in->execute(['year' => $year]);
    $petty_in_data = $stmt_petty_in->fetchAll(PDO::FETCH_ASSOC);

    // 4. Outflow from Expenses
    $sql_exp = "SELECT exp_month as month, exp_year as year, SUM(amount) as total 
                FROM ims_expenses 
                WHERE approve_status = 'Y' AND exp_year = :year AND CAST(exp_month AS UNSIGNED) IN ($monthList)
                GROUP BY exp_month, exp_year";
    $stmt_exp = $conn->prepare($sql_exp);
    $stmt_exp->execute(['year' => $year]);
    $exp_data = $stmt_exp->fetchAll(PDO::FETCH_ASSOC);

    // 5. Outflow from Petty Cash (-)
    $sql_petty_out = "SELECT MONTH(doc_date) as month, YEAR(doc_date) as year, SUM(amount) as total 
                      FROM ims_petty_cash 
                      WHERE status = 'Y' AND transaction_type = '-' AND YEAR(doc_date) = :year AND MONTH(doc_date) IN ($monthList)
                      GROUP BY MONTH(doc_date), YEAR(doc_date)";
    $stmt_petty_out = $conn->prepare($sql_petty_out);
    $stmt_petty_out->execute(['year' => $year]);
    $petty_out_data = $stmt_petty_out->fetchAll(PDO::FETCH_ASSOC);

    // Aggregate Data
    $result = [];
    foreach ($months as $m) {
        $m = (int)$m;
        $inflow = 0;
        $outflow = 0;

        foreach ($house_data as $row) { if ((int)$row['month'] === $m) $inflow += $row['total']; }
        foreach ($rec_data as $row) { if ((int)$row['month'] === $m) $inflow += $row['total']; }
        foreach ($petty_in_data as $row) { if ((int)$row['month'] === $m) $inflow += $row['total']; }
        
        foreach ($exp_data as $row) { if ((int)$row['month'] === $m) $outflow += $row['total']; }
        foreach ($petty_out_data as $row) { if ((int)$row['month'] === $m) $outflow += $row['total']; }

        $result[] = [
            'month' => $m,
            'month_name' => $month_arr[$m],
            'year' => $year,
            'inflow' => $inflow,
            'outflow' => $outflow
        ];
    }

    echo json_encode($result);
}
