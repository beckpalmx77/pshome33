<?php
session_start();
error_reporting(0);
include("config/connect_db.php");

// รับ input จากผู้ใช้
$start_date = $_POST["doc_date_start"] ?? '';
$end_date = $_POST["doc_date_to"] ?? '';
$export = $_POST["export"] ?? '';

// กำหนดเงื่อนไขการกรอง payment method
$payment_method_sql = ""; // ค่าเริ่มต้น: ไม่กรอง
if (isset($_POST["payment_method"])) {
    $pm = $_POST["payment_method"];
    if ($pm === "cash") {
        $payment_method_sql = " AND payment_method = 'เงินสด' ";
    } elseif ($pm === "bank") {
        $payment_method_sql = " AND payment_method = 'โอนเงิน' ";
    }
}

$expenses_data = fetchExpensesData($conn, 'v_ims_expenses', $start_date, $end_date, $payment_method_sql);

function fetchExpensesData($conn, $table, $start_date, $end_date, $payment_method_sql)
{
    if (empty($start_date) || empty($end_date)) {
        return [];
    }

    $sql = "
    SELECT * FROM $table 
    WHERE 1=1 $payment_method_sql
    AND CASE 
        WHEN expense_date REGEXP '^[0-9]{4}-[0-9]{2}-[0-9]{2}$' THEN STR_TO_DATE(expense_date, '%Y-%m-%d')
        WHEN expense_date REGEXP '^[0-9]{2}-[0-9]{2}-[0-9]{4}$' THEN STR_TO_DATE(expense_date, '%d-%m-%Y')
        ELSE NULL 
    END BETWEEN STR_TO_DATE(:start_date, '%d-%m-%Y') AND STR_TO_DATE(:end_date, '%d-%m-%Y')
    ORDER BY CASE 
        WHEN expense_date REGEXP '^[0-9]{4}-[0-9]{2}-[0-9]{2}$' THEN STR_TO_DATE(expense_date, '%Y-%m-%d')
        WHEN expense_date REGEXP '^[0-9]{2}-[0-9]{2}-[0-9]{4}$' THEN STR_TO_DATE(expense_date, '%d-%m-%Y')
        ELSE NULL 
    END ASC;
    ";

    $query = $conn->prepare($sql);
    $query->bindParam(':start_date', $start_date);
    $query->bindParam(':end_date', $end_date);
    $query->execute();
    return $query->fetchAll(PDO::FETCH_OBJ);
}

// ถ้าส่งคำขอ export ให้ส่งออก CSV แทน
if ($export == '1') {
    exportToCSV($expenses_data, $start_date, $end_date);
    exit;
}

function exportToCSV($data, $start_date, $end_date)
{
    $filename = "expenses_report_{$start_date}_to_{$end_date}.csv";

    echo "\xEF\xBB\xBF"; // UTF-8 BOM

    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename=' . $filename);

    $output = fopen('php://output', 'w');

    // Header
    fputcsv($output, ['ลำดับ', 'วันที่ใช้จ่าย', 'เลขที่เอกสาร', 'ปี', 'รายละเอียดค่าใช้จ่าย', 'หมวดหมู่', 'จ่ายให้', 'วิธีชำระ', 'หมายเหตุ', 'จำนวนเงิน (บาท)', 'สถานะ']);

    $sum_amount = 0; // ตัวแปรเก็บยอดรวม

    // Rows
    foreach ($data as $index => $row) {
        $approve_status_desc = ($row->approve_status == 'Y') ? "อนุมัติแล้ว" : "รออนุมัติ";
        $amount = floatval($row->amount);
        $sum_amount += $amount;

        // ฟอร์แมตวันที่ให้เป็น dd/mm/yyyy สำหรับการส่งออก
        $expense_date_formatted = $row->expense_date;
        if (!empty($row->expense_date)) {
            $date_obj = DateTime::createFromFormat('Y-m-d', $row->expense_date);
            if (!$date_obj) {
                $date_obj = DateTime::createFromFormat('d-m-Y', $row->expense_date);
            }
            if ($date_obj) {
                $expense_date_formatted = $date_obj->format('d/m/Y');
            }
        }

        fputcsv($output, [
            $index + 1,
            $expense_date_formatted,
            $row->doc_id,
            $row->exp_year,
            $row->description,
            $row->category_name,
            $row->receipt_name,
            $row->payment_method,
            $row->remark,
            number_format($amount, 2),
            $approve_status_desc
        ]);
    }

    // แสดงแถวสุดท้ายเป็นยอดรวม
    fputcsv($output, [
        '', '', '', '', '', '', '', 'รวมทั้งหมด',
        number_format($sum_amount, 2),
        ''
    ]);

    fclose($output);
}
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <link rel="icon" href="img/favicon.ico" type="image/x-icon">
    <link href="img/logo/logo.png" rel="icon">
    <title>PS33 House System - แสดงข้อมูลค่าใช้จ่าย</title>

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
    </style>
</head>
<body>
<div class="container-fluid py-4">
    <div class="card">
        <div class="card-header bg-primary text-white">
            <i class="fa fa-signal" aria-hidden="true"></i> แสดงข้อมูลรายการรายจ่าย-ค่าใช้จ่าย
            <?php echo " วันที่ " . htmlentities($start_date) . " ถึง " . htmlentities($end_date); ?>
        </div>
        <div class="card-body">

            <!-- ปุ่มสั่งงานหลัก -->
            <form method="post" style="display: inline-block;">
                <input type="hidden" name="doc_date_start" id="doc_date_start"
                       value="<?php echo htmlentities($start_date); ?>">
                <input type="hidden" name="doc_date_to" id="doc_date_to" value="<?php echo htmlentities($end_date); ?>">
                <input type="hidden" name="payment_method" id="payment_method"
                       value="<?php echo isset($_POST["payment_method"]) ? htmlentities($_POST["payment_method"]) : 'all'; ?>">
                <input type="hidden" name="export" value="1"/>
                <button type="submit" class="btn btn-success mb-3">
                    <i class="fa fa-file-excel-o"></i> Export Excel
                </button>
                <button type="button" class="btn btn-primary mb-3"
                        onclick="openPrintWindow()">
                    <i class="fa fa-print"></i> Print
                </button>
            </form>

            <button class="btn btn-danger mb-3" onclick="window.close()">ปิด (Close)</button>

            <h4><span class="badge bg-info">แสดงข้อมูลค่าใช้จ่าย</span></h4>

            <table id="ExpensesTable" class="table table-striped table-bordered">
                <thead>
                <tr>
                    <th>ลำดับ</th>
                    <th>วันที่ใช้จ่าย</th>
                    <th>เลขที่เอกสาร</th>
                    <th>ปี</th>
                    <th>รายละเอียดค่าใช้จ่าย</th>
                    <th>หมวดหมู่</th>
                    <th>จ่ายให้</th>
                    <th>วิธีชำระ</th>
                    <th>หมายเหตุ</th>
                    <th>จำนวนเงิน (บาท)</th>
                    <th>สถานะ</th>
                </tr>
                </thead>
                <tbody>
                <?php foreach ($expenses_data as $index => $row_expense): ?>
                    <?php
                    $approve_status_desc = '';
                    switch ($row_expense->approve_status) {
                        case 'Y':
                            $approve_status_desc = "<span style='color: green;'>อนุมัติแล้ว</span>";
                            break;
                        case 'N':
                            $approve_status_desc = "<span style='color: red;'>รออนุมัติ</span>";
                            break;
                        default:
                            $approve_status_desc = "<span>ไม่ระบุสถานะ</span>";
                    }

                    $expense_date_formatted = $row_expense->expense_date;
                    if (!empty($row_expense->expense_date)) {
                        $date_obj = DateTime::createFromFormat('Y-m-d', $row_expense->expense_date);
                        if (!$date_obj) {
                            $date_obj = DateTime::createFromFormat('d-m-Y', $row_expense->expense_date);
                        }
                        if ($date_obj) {
                            $expense_date_formatted = $date_obj->format('d/m/Y');
                        }
                    }
                    ?>
                    <tr>
                        <td><?php echo htmlentities($index + 1); ?></td>
                        <td><?php echo htmlentities($expense_date_formatted); ?></td>
                        <td><?php echo htmlentities($row_expense->doc_id); ?></td>
                        <td><?php echo htmlentities($row_expense->exp_year); ?></td>
                        <td><?php echo htmlentities($row_expense->description); ?></td>
                        <td><?php echo htmlentities($row_expense->category_name); ?></td>
                        <td><?php echo htmlentities($row_expense->receipt_name); ?></td>
                        <td><?php echo htmlentities($row_expense->payment_method); ?></td>
                        <td><?php echo htmlentities($row_expense->remark); ?></td>
                        <td class="text-end"><?php echo htmlentities(number_format($row_expense->amount, 2)); ?></td>
                        <td><?php echo $approve_status_desc; ?></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
                <tfoot>
                <tr>
                    <th colspan="9" class="text-end">รวมทั้งหมด:</th>
                    <th class="text-end" id="totalAmountFooter"></th>
                    <th colspan="1"></th>
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
        $('#ExpensesTable').DataTable({
            responsive: true,
            'lengthMenu': [[10, 20, 50, 100], [10, 20, 50, 100]],
            'language': {
                search: 'ค้นหา',
                lengthMenu: 'แสดง _MENU_ รายการ',
                info: 'หน้าที่ _PAGE_ จาก _PAGES_',
                infoEmpty: 'ไม่มีข้อมูล',
                zeroRecords: "ไม่มีข้อมูลตามเงื่อนไข",
                infoFiltered: '(กรองข้อมูลจากทั้งหมด _MAX_ รายการ)',
                paginate: {
                    previous: 'ก่อนหน้า',
                    next: 'ต่อไป',
                    last: 'สุดท้าย'
                }
            },
            footerCallback: function (row, data, start, end, display) {
                let api = this.api();

                let parseValue = function (i) {
                    return typeof i === 'string'
                        ? parseFloat(i.replace(/[,]/g, '')) || 0
                        : typeof i === 'number'
                            ? i : 0;
                };

                let total = api
                    .column(9, {page: 'all'})
                    .data()
                    .reduce(function (a, b) {
                        return parseValue(a) + parseValue(b);
                    }, 0);

                $(api.column(9).footer()).html(
                    total.toLocaleString('en-US', {minimumFractionDigits: 2})
                );
            }
        });
    });
</script>

<script>
    function openPrintWindow() {
        const startDate = document.querySelector('input[name="doc_date_start"]').value;
        const endDate = document.querySelector('input[name="doc_date_to"]').value;
        const paymentMethod = document.querySelector('input[name="payment_method"]').value;

        const url = `expense_pdf_out?doc_date_start=${encodeURIComponent(startDate)}&doc_date_to=${encodeURIComponent(endDate)}&payment_method=${encodeURIComponent(paymentMethod)}`;
        window.open(url, '_blank');
    }
</script>

</body>
</html>
