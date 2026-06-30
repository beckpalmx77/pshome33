<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);
include("config/connect_db.php");

// รับ input จากผู้ใช้
$start_date = $_POST["start_date"] ?? $_GET["start_date"] ?? '';
$end_date = $_POST["end_date"] ?? $_GET["end_date"] ?? '';

if (empty($start_date) || empty($end_date)) {
    echo "<script>alert('กรุณาเลือกช่วงวันที่ให้ถูกต้อง'); window.close();</script>";
    exit;
}

// -------------------------------------------------------------
// 1. คำนวณยอดยกมาก่อนวันที่เริ่มต้น (Opening Balance)
// -------------------------------------------------------------
$total_in_open = 0;
$total_out_open = 0;

try {
    // 1.1 ยอดรับเข้าสะสมก่อน start_date
    $sql_in_open = "SELECT SUM(amount) AS total_in FROM ims_petty_cash 
                    WHERE status = 'Y' AND transaction_type = '+' 
                    AND doc_date < STR_TO_DATE(:start_date, '%d-%m-%Y')";
    $stmt_in_open = $conn->prepare($sql_in_open);
    $stmt_in_open->execute([':start_date' => $start_date]);
    $res_in_open = $stmt_in_open->fetch(PDO::FETCH_ASSOC);
    $total_in_open = (float)($res_in_open['total_in'] ?? 0);

    // 1.2 ยอดจ่ายออกสะสมก่อน start_date
    $sql_out_open = "SELECT SUM(amount) AS total_out FROM ims_expenses 
                     WHERE petty_cash_status = 'Y' AND approve_status = 'Y' 
                     AND CASE 
                         WHEN expense_date REGEXP '^[0-9]{4}-[0-9]{2}-[0-9]{2}$' THEN STR_TO_DATE(expense_date, '%Y-%m-%d')
                         WHEN expense_date REGEXP '^[0-9]{2}-[0-9]{2}-[0-9]{4}$' THEN STR_TO_DATE(expense_date, '%d-%m-%Y')
                         ELSE NULL 
                     END < STR_TO_DATE(:start_date, '%d-%m-%Y')";
    $stmt_out_open = $conn->prepare($sql_out_open);
    $stmt_out_open->execute([':start_date' => $start_date]);
    $res_out_open = $stmt_out_open->fetch(PDO::FETCH_ASSOC);
    $total_out_open = (float)($res_out_open['total_out'] ?? 0);

} catch (PDOException $e) {
    die("เกิดข้อผิดพลาดในการคำนวณยอดยกมา: " . $e->getMessage());
}

$opening_balance = $total_in_open - $total_out_open;

// -------------------------------------------------------------
// 2. ดึงข้อมูลรายการเดินบัญชีระหว่างวันที่ (Statement Items)
// -------------------------------------------------------------
$statement_data = [];
try {
    $sql = "SELECT 
                'receipt' AS source_type,
                id AS source_id,
                doc_date AS txn_date,
                doc_no AS doc_id,
                description AS description,
                received_from AS contact_person,
                amount AS inflow,
                0.00 AS outflow
            FROM ims_petty_cash
            WHERE status = 'Y' AND transaction_type = '+'
              AND doc_date BETWEEN STR_TO_DATE(:start_date, '%d-%m-%Y') AND STR_TO_DATE(:end_date, '%d-%m-%Y')

            UNION ALL

            SELECT 
                'expense' AS source_type,
                id AS source_id,
                CASE 
                    WHEN expense_date REGEXP '^[0-9]{4}-[0-9]{2}-[0-9]{2}$' THEN STR_TO_DATE(expense_date, '%Y-%m-%d')
                    WHEN expense_date REGEXP '^[0-9]{2}-[0-9]{2}-[0-9]{4}$' THEN STR_TO_DATE(expense_date, '%d-%m-%Y')
                    ELSE NULL 
                END AS txn_date,
                doc_id AS doc_id,
                description AS description,
                receipt_name AS contact_person,
                0.00 AS inflow,
                amount AS outflow
            FROM ims_expenses
            WHERE petty_cash_status = 'Y' AND approve_status = 'Y'
              AND (CASE 
                    WHEN expense_date REGEXP '^[0-9]{4}-[0-9]{2}-[0-9]{2}$' THEN STR_TO_DATE(expense_date, '%Y-%m-%d')
                    WHEN expense_date REGEXP '^[0-9]{2}-[0-9]{2}-[0-9]{4}$' THEN STR_TO_DATE(expense_date, '%d-%m-%Y')
                    ELSE NULL 
              END BETWEEN STR_TO_DATE(:start_date, '%d-%m-%Y') AND STR_TO_DATE(:end_date, '%d-%m-%Y'))

            ORDER BY txn_date ASC, doc_id ASC";

    $query = $conn->prepare($sql);
    $query->execute([
        ':start_date' => $start_date,
        ':end_date' => $end_date
    ]);
    $statement_data = $query->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    die("เกิดข้อผิดพลาดในการดึงข้อมูล Statement: " . $e->getMessage());
}

?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <link rel="icon" href="img/favicon.ico" type="image/x-icon">
    <title>แสดงข้อมูลความเคลื่อนไหวเงินสดย่อย</title>

    <link href="bootstrap/css/bootstrap.min.css" rel="stylesheet"/>
    <link rel="stylesheet" href="fontawesome/css/font-awesome.css"/>

    <!-- DataTables CSS -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.5/css/dataTables.bootstrap5.min.css"/>
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css"/>
    <link rel="stylesheet" href="https://cdn.datatables.net/responsive/2.5.0/css/responsive.dataTables.min.css"/>

    <style>
        table.dataTable tbody td,
        table.dataTable thead th {
            white-space: nowrap;
        }
        .dataTables_wrapper {
            overflow-x: auto;
        }
        .inflow-val {
            color: #2e7d32;
            font-weight: bold;
        }
        .outflow-val {
            color: #c62828;
            font-weight: bold;
        }
        .balance-val {
            font-weight: bold;
            color: #0d6efd;
        }
    </style>
</head>
<body>
<div class="container-fluid py-4">
    <div class="card shadow">
        <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
            <div>
                <i class="fa fa-university" aria-hidden="true"></i> แสดงข้อมูลความเคลื่อนไหวเงินสดย่อย (Petty Cash Statement)
                <?php echo " วันที่ " . htmlentities($start_date) . " ถึง " . htmlentities($end_date); ?>
            </div>
        </div>
        <div class="card-body">

            <!-- ปุ่มสั่งงาน -->
            <div class="mb-3 d-flex gap-2">
                <form method="post" action="export_process/petty_cash_statement_pdf.php" target="_blank" style="display: inline-block;">
                    <input type="hidden" name="start_date" value="<?php echo htmlentities($start_date); ?>">
                    <input type="hidden" name="end_date" value="<?php echo htmlentities($end_date); ?>">
                    <button type="submit" class="btn btn-danger">
                        <i class="fa fa-file-pdf-o"></i> Print PDF
                    </button>
                </form>
                <form method="post" action="export_process/petty_cash_statement_excel.php" target="_blank" style="display: inline-block;">
                    <input type="hidden" name="start_date" value="<?php echo htmlentities($start_date); ?>">
                    <input type="hidden" name="end_date" value="<?php echo htmlentities($end_date); ?>">
                    <button type="submit" class="btn btn-success">
                        <i class="fa fa-file-excel-o"></i> Export Excel
                    </button>
                </form>
                <button class="btn btn-secondary" onclick="window.close()"><i class="fa fa-times"></i> ปิดหน้าจอนี้</button>
            </div>

            <table id="StatementTable" class="table table-striped table-bordered align-middle">
                <thead>
                <tr class="table-primary">
                    <th class="text-center" style="width: 5%;">ลำดับ</th>
                    <th class="text-center" style="width: 12%;">วันที่</th>
                    <th class="text-center" style="width: 15%;">เลขที่เอกสาร</th>
                    <th class="text-center" style="width: 10%;">ประเภท</th>
                    <th class="text-center" style="width: 25%;">รายละเอียด</th>
                    <th class="text-center" style="width: 15%;">รับจาก / จ่ายให้</th>
                    <th class="text-center" style="width: 10%;">รับเข้า (บาท)</th>
                    <th class="text-center" style="width: 10%;">จ่ายออก (บาท)</th>
                    <th class="text-center" style="width: 10%;">ยอดคงเหลือ (บาท)</th>
                </tr>
                </thead>
                <tbody>
                <!-- 1. แถวยอดยกมา (Opening Balance) -->
                <tr class="table-info">
                    <td class="text-center">-</td>
                    <td class="text-center">ก่อนวันที่ <?php echo htmlentities($start_date); ?></td>
                    <td class="text-center">-</td>
                    <td class="text-center"><span class="badge bg-secondary text-white">ยอดยกมา</span></td>
                    <td>ยอดสะสมยกมาก่อนช่วงวันที่กรอง</td>
                    <td class="text-center">-</td>
                    <td class="text-end">-</td>
                    <td class="text-end">-</td>
                    <td class="text-end balance-val"><?php echo number_format($opening_balance, 2); ?></td>
                </tr>

                <?php 
                $running_balance = $opening_balance;
                $total_inflow = 0;
                $total_outflow = 0;
                $row_idx = 1;
                
                foreach ($statement_data as $row): 
                    $inflow = (float)$row['inflow'];
                    $outflow = (float)$row['outflow'];
                    $total_inflow += $inflow;
                    $total_outflow += $outflow;
                    $running_balance = $running_balance + $inflow - $outflow;

                    // ฟอร์แมตประเภทและป้ายกำกับ
                    if ($row['source_type'] === 'receipt') {
                        $type_badge = "<span class='badge bg-success text-white'>รับเข้า</span>";
                    } else {
                        $type_badge = "<span class='badge bg-danger text-white'>จ่ายออก</span>";
                    }

                    // แปลงวันที่แสดงผลเป็น dd/mm/yyyy
                    $txn_date_formatted = '';
                    if (!empty($row['txn_date'])) {
                        $date_obj = DateTime::createFromFormat('Y-m-d', $row['txn_date']);
                        if ($date_obj) {
                            $txn_date_formatted = $date_obj->format('d/m/Y');
                        } else {
                            $txn_date_formatted = $row['txn_date'];
                        }
                    }
                ?>
                    <tr>
                        <td class="text-center"><?php echo $row_idx++; ?></td>
                        <td class="text-center"><?php echo htmlentities($txn_date_formatted); ?></td>
                        <td class="text-center"><?php echo htmlentities($row['doc_id']); ?></td>
                        <td class="text-center"><?php echo $type_badge; ?></td>
                        <td><?php echo htmlentities($row['description']); ?></td>
                        <td><?php echo htmlentities($row['contact_person'] ?: '-'); ?></td>
                        <td class="text-end inflow-val"><?php echo $inflow > 0 ? number_format($inflow, 2) : '-'; ?></td>
                        <td class="text-end outflow-val"><?php echo $outflow > 0 ? number_format($outflow, 2) : '-'; ?></td>
                        <td class="text-end balance-val"><?php echo number_format($running_balance, 2); ?></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
                <tfoot>
                <tr class="fw-bold table-light">
                    <th colspan="6" class="text-end">รวมประจำงวด (<?php echo htmlentities($start_date . ' - ' . $end_date); ?>):</th>
                    <th class="text-end inflow-val"><?php echo number_format($total_inflow, 2); ?></th>
                    <th class="text-end outflow-val"><?php echo number_format($total_outflow, 2); ?></th>
                    <th class="text-end balance-val"><?php echo number_format($running_balance, 2); ?></th>
                </tr>
                </tfoot>
            </table>

        </div>
    </div>
</div>

<script src="js/jquery-3.6.0.js"></script>
<script src="bootstrap/js/bootstrap.bundle.min.js"></script>

<!-- DataTables JS -->
<script src="https://cdn.datatables.net/1.13.5/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.5/js/dataTables.bootstrap5.min.js"></script>
<script src="https://cdn.datatables.net/responsive/2.5.0/js/dataTables.responsive.min.js"></script>

<script>
    $(document).ready(function () {
        $('#StatementTable').DataTable({
            responsive: true,
            ordering: false, // ปิดการกดจัดเรียงผ่าน Datatables เพราะจะทำให้ Running Balance สับสนผิดลำดับจริง
            pageLength: 50,
            lengthMenu: [[10, 25, 50, 100, -1], [10, 25, 50, 100, "ทั้งหมด"]],
            language: {
                search: 'ค้นหาในหน้านี้:',
                lengthMenu: 'แสดง _MENU_ รายการ',
                info: 'หน้าที่ _PAGE_ จาก _PAGES_',
                infoEmpty: 'ไม่มีข้อมูล',
                zeroRecords: "ไม่มีข้อมูลตามเงื่อนไข",
                infoFiltered: '(กรองข้อมูลจากทั้งหมด _MAX_ รายการ)',
                paginate: {
                    previous: 'ก่อนหน้า',
                    next: 'ต่อไป'
                }
            }
        });
    });
</script>
</body>
</html>
