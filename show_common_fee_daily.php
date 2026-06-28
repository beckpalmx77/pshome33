<?php
session_start();
error_reporting(0);
include("config/connect_db.php");

// รับ input จากผู้ใช้
$start_date = $_POST["doc_date_start"] ?? '';
$end_date = $_POST["doc_date_to"] ?? '';
$export = $_POST["export"] ?? '';

// กำหนดเงื่อนไขการกรอง payment method
$payment_method_sql = "";
if (isset($_POST["payment_method"])) {
    $pm = $_POST["payment_method"];
    if ($pm === "cash") {
        $payment_method_sql = " AND payment_method = 'เงินสด' ";
    } elseif ($pm === "bank") {
        $payment_method_sql = " AND payment_method = 'โอนเงิน' ";
    } elseif ($pm === "all") {
        $payment_method_sql = "";
    }
}

// ถ้าไม่มีการเลือกวันที่ ให้ใช้วันที่ปัจจุบันเป็นค่าเริ่มต้นเพื่อป้องกัน Query Error หรือดึงข้อมูลทั้งหมด
if (empty($start_date) || empty($end_date)) {
    $start_date = date('d-m-Y');
    $end_date = date('d-m-Y');
}

$house_payment_data = fetchPaymentData($conn, 'v_ims_house_payment', $start_date, $end_date, $payment_method_sql);

function fetchPaymentData($conn, $table, $start_date, $end_date, $payment_method_sql)
{
    // ใช้ Parameter Binding เพื่อความปลอดภัย
    $sql = "
    SELECT * FROM $table 
    WHERE 1=1 $payment_method_sql
    AND STR_TO_DATE(payment_date, '%d-%m-%Y') 
        BETWEEN STR_TO_DATE(:start_date, '%d-%m-%Y') 
        AND STR_TO_DATE(:end_date, '%d-%m-%Y')
    ORDER BY STR_TO_DATE(payment_date, '%d-%m-%Y') ASC, id ASC;
    ";

    //$myfile = fopen("a-param.txt", "w") or die("Unable to open file!");
    //fwrite($myfile, $sql . " " . $start_date  . " " . $end_date);
    //fclose($myfile);

    $query = $conn->prepare($sql);
    $query->bindParam(':start_date', $start_date);
    $query->bindParam(':end_date', $end_date);
    $query->execute();
    return $query->fetchAll(PDO::FETCH_OBJ);
}

// Export CSV
if ($export == '1') {
    exportToCSV($house_payment_data, $start_date, $end_date);
    exit;
}

function exportToCSV($data, $start_date, $end_date)
{
    $filename = "payment_report_{$start_date}_to_{$end_date}.csv";
    echo "\xEF\xBB\xBF"; // UTF-8 BOM
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename=' . $filename);

    $output = fopen('php://output', 'w');
    fputcsv($output, ['#', 'วันที่', 'เลขที่เอกสาร', 'ผู้ชำระ', 'บ้านเลขที่', 'พื้นที่บ้าน ตรว', 'ชำระโดย', 'งวดเดือน', 'ปี', 'จำนวนงวด', 'ค่าส่วนกลาง', 'จำนวนเงิน', 'สถานะ', 'ผู้สร้างรายการ', 'วิธีชำระ']);

    $sum_amount = 0;
    foreach ($data as $index => $row) {
        $payment_status_desc = ($row->payment_status == 'Y') ? "ยืนยันการชำระ" : "ยังไม่ยืนยันการชำระ";
        $amount = floatval($row->amount);
        $sum_amount += $amount;

        fputcsv($output, [
            $index + 1,
            $row->payment_date,
            $row->doc_id,
            $row->detail,
            $row->house_number,
            $row->area_size,
            $row->payment_method,
            $row->month_name_start . " - " . $row->month_name_to,
            $row->period_year,
            $row->payment_type,
            number_format($row->common_fee, 2),
            number_format($amount, 2),
            $payment_status_desc,
            $row->create_by,
            $row->payment_method
        ]);
    }
    // Summary Row
    fputcsv($output, ['', '', '', '', '', '', '', '', '', '', 'รวมทั้งหมด', number_format($sum_amount, 2), '', '', '']);
    fclose($output);
}
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>PS33 House System</title>

    <link href="bootstrap/css/bootstrap.min.css" rel="stylesheet"/>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css"/>

    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css"/>
    <link rel="stylesheet" href="https://cdn.datatables.net/responsive/2.5.0/css/responsive.bootstrap5.min.css"/>
    <link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.4.1/css/buttons.bootstrap5.min.css">

    <style>
        /* ปรับแต่งตาราง */
        table.dataTable thead th {
            background-color: #f8f9fa;
            white-space: nowrap;
            vertical-align: middle;
            text-align: center;
        }

        table.dataTable tbody td {
            white-space: nowrap;
            vertical-align: middle;
        }

        /* จัดตัวเลขชิดขวา */
        .text-end {
            text-align: right !important;
        }

        /* สถานะ */
        .status-confirmed { color: #198754; font-weight: bold; }
        .status-pending { color: #dc3545; font-weight: bold; }
        .status-unknown { color: #6c757d; }
    </style>
</head>
<body>
<div class="container-fluid mt-3">
    <div class="card shadow-sm">
        <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
            <span><i class="fa fa-signal" aria-hidden="true"></i> แสดงข้อมูลการรับชำระค่าส่วนกลาง</span>
            <small><?php echo " วันที่ " . htmlentities($start_date) . " ถึง " . htmlentities($end_date); ?></small>
        </div>
        <div class="card-body">

            <form method="post" class="mb-3 d-flex gap-2 flex-wrap">
                <input type="hidden" name="doc_date_start" value="<?php echo htmlentities($start_date); ?>">
                <input type="hidden" name="doc_date_to" value="<?php echo htmlentities($end_date); ?>">
                <input type="hidden" name="payment_method" value="<?php echo isset($_POST["payment_method"]) ? htmlentities($_POST["payment_method"]) : 'all'; ?>">

                <button type="submit" name="export" value="1" class="btn btn-success">
                    <i class="fa fa-file-excel-o"></i> Export Excel
                </button>

                <button type="button" class="btn btn-primary" onclick="openPrintWindow()">
                    <i class="fa fa-print"></i> Print PDF
                </button>

                <button type="button" class="btn btn-danger ms-auto" onclick="window.close()">
                    <i class="fa fa-times"></i> ปิด (Close)
                </button>
            </form>

            <div class="table-responsive">
                <table id="PaymentTable" class="table table-striped table-bordered table-hover w-100">
                    <thead>
                    <tr>
                        <th>#</th>
                        <th>วันที่</th>
                        <th>เลขที่เอกสาร</th>
                        <th>ผู้ชำระ</th>
                        <th>บ้านเลขที่</th>
                        <th>พื้นที่ (ตรว.)</th>
                        <th class="text-end">ค่าส่วนกลาง</th>
                        <th>ชำระโดย</th>
                        <th>งวดเดือน</th>
                        <th>ปี</th>
                        <th class="text-end">จำนวนงวด</th>
                        <th class="text-end">ยอดชำระ (บาท)</th> <th class="text-center">สถานะ</th>
                        <th>ผู้สร้าง</th>
                        <th>วิธีชำระ</th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($house_payment_data as $index => $row): ?>
                        <tr>
                            <td class="text-center"><?php echo $index + 1; ?></td>
                            <td><?php echo htmlentities($row->payment_date); ?></td>
                            <td><?php echo htmlentities($row->doc_id); ?></td>
                            <td><?php echo htmlentities($row->detail); ?></td>
                            <td class="text-center"><?php echo htmlentities($row->house_number); ?></td>
                            <td class="text-center"><?php echo htmlentities($row->area_size); ?></td>
                            <td class="text-end"><?php echo number_format($row->common_fee, 2); ?></td>
                            <td class="text-center"><?php echo htmlentities($row->payment_method); ?></td>
                            <td><?php echo htmlentities($row->month_name_start); ?></td>
                            <td class="text-center"><?php echo htmlentities($row->period_year); ?></td>
                            <td class="text-end"><?php echo htmlentities($row->payment_type); ?></td>
                            <td class="text-end fw-bold text-primary"><?php echo number_format($row->amount, 2); ?></td>
                            <td class="text-center">
                                <?php if ($row->payment_status == 'Y'): ?>
                                    <span class="badge bg-success">ยืนยันแล้ว</span>
                                <?php elseif ($row->payment_status == 'N'): ?>
                                    <span class="badge bg-danger">รอตรวจสอบ</span>
                                <?php else: ?>
                                    <span class="badge bg-secondary">ไม่ระบุ</span>
                                <?php endif; ?>
                            </td>
                            <td><?php echo htmlentities($row->create_by); ?></td>
                            <td><?php echo htmlentities($row->payment_method); ?></td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                    <tfoot>
                    <tr class="table-secondary">
                        <th colspan="11" class="text-end fw-bold">รวมทั้งหมด:</th>
                        <th id="totalAmountFooter" class="text-end fw-bold text-decoration-underline"></th>
                        <th colspan="3"></th>
                    </tr>
                    </tfoot>
                </table>
            </div>

        </div>
    </div>
</div>

<script src="js/jquery-3.6.0.js"></script>
<script src="bootstrap/js/bootstrap.bundle.min.js"></script>

<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
<script src="https://cdn.datatables.net/responsive/2.5.0/js/dataTables.responsive.min.js"></script>
<script src="https://cdn.datatables.net/responsive/2.5.0/js/responsive.bootstrap5.min.js"></script>

<script>
    $(document).ready(function () {
        $('#PaymentTable').DataTable({
            responsive: false, // ปิด responsive เพื่อบังคับให้แสดงครบและมี scroll แนวนอนแทน (เหมาะกับ report)
            scrollX: true,     // เปิด Scroll แนวนอน
            pageLength: 10,
            lengthMenu: [[10, 20, 50, 100, -1], [10, 20, 50, 100, "ทั้งหมด"]],
            order: [[0, 'asc']], // เรียงตามลำดับ # (index 0)
            language: {
                search: 'ค้นหา:',
                lengthMenu: 'แสดง _MENU_ รายการ',
                info: 'แสดง _START_ ถึง _END_ จาก _TOTAL_ รายการ',
                infoEmpty: 'ไม่มีข้อมูล',
                zeroRecords: "ไม่พบข้อมูลที่ค้นหา",
                infoFiltered: '(กรองจากทั้งหมด _MAX_ รายการ)',
                paginate: {
                    previous: 'ก่อนหน้า',
                    next: 'ต่อไป'
                }
            },
            footerCallback: function (row, data, start, end, display) {
                let api = this.api();

                // ฟังก์ชันลบ comma และแปลงเป็น float
                let parseValue = function (i) {
                    return typeof i === 'string' ?
                        parseFloat(i.replace(/,/g, '')) || 0 :
                        typeof i === 'number' ? i : 0;
                };

                // คำนวณยอดรวมของคอลัมน์ Index 11 (ยอดชำระ)
                let total = api
                    .column(11, { page: 'current' }) // ใช้ 'current' ถ้ายากให้รวมเฉพาะหน้า, ใช้ 'all' ถ้ารวมทั้งหมด
                    .data()
                    .reduce(function (a, b) {
                        return parseValue(a) + parseValue(b);
                    }, 0);

                // แสดงผลที่ Footer
                $(api.column(11).footer()).html(
                    total.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 })
                );
            }
        });
    });

    function openPrintWindow() {
        const startDate = document.querySelector('input[name="doc_date_start"]').value;
        const endDate = document.querySelector('input[name="doc_date_to"]').value;
        const paymentMethod = document.querySelector('input[name="payment_method"]').value;

        // ตรวจสอบว่ามีค่า url หรือไม่
        const url = `receipt_pdf_out?doc_date_start=${encodeURIComponent(startDate)}&doc_date_to=${encodeURIComponent(endDate)}&payment_method=${encodeURIComponent(paymentMethod)}`;
        window.open(url, '_blank');
    }
</script>

</body>
</html>