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
    } elseif ($pm === "all") {
        // ไม่ต้องเพิ่มเงื่อนไข
        $payment_method_sql = "";
    }
}

$house_payment_data = fetchPaymentData($conn, 'v_ims_house_payment', $start_date, $end_date, $payment_method_sql);

function fetchPaymentData($conn, $table, $start_date, $end_date, $payment_method_sql)
{
    $sql = "
    SELECT * FROM $table 
    WHERE 1=1 $payment_method_sql
    AND STR_TO_DATE(payment_date, '%d-%m-%Y') 
        BETWEEN STR_TO_DATE(:start_date, '%d-%m-%Y') 
        AND STR_TO_DATE(:end_date, '%d-%m-%Y')
    ORDER BY STR_TO_DATE(payment_date, '%d-%m-%Y');
    ";

    $query = $conn->prepare($sql);
    $query->bindParam(':start_date', $start_date);
    $query->bindParam(':end_date', $end_date);
    $query->execute();
    return $query->fetchAll(PDO::FETCH_OBJ);
}

// ถ้าส่งคำขอ export ให้ส่งออก CSV แทน
if ($export == '1') {
    exportToCSV($house_payment_data, $start_date, $end_date);
    exit; // จบการทำงานหลังส่งไฟล์
}

function exportToCSV($data, $start_date, $end_date)
{
    $filename = "payment_report_{$start_date}_to_{$end_date}.csv";

    echo "\xEF\xBB\xBF"; // UTF-8 BOM

    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename=' . $filename);

    $output = fopen('php://output', 'w');

    // Header
    fputcsv($output, ['#', 'วันที่', 'ผู้ชำระ', 'บ้านเลขที่', 'พื้นที่บ้าน ตรว', 'ชำระโดย', 'งวดเดือน', 'ปี', 'จำนวนงวด', 'ค่าส่วนกลาง', 'สถานะ', 'วิธีชำระ']);

    $sum_amount = 0; // ตัวแปรเก็บยอดรวม

    // Rows
    foreach ($data as $index => $row) {
        $payment_status_desc = ($row->payment_status == 'Y') ? "ยืนยันการชำระ" : "ยังไม่ยืนยันการชำระ";

        // แปลงค่า amount เป็นตัวเลขก่อนรวม
        $amount = floatval($row->amount);
        $sum_amount += $amount;

        fputcsv($output, [
            $index + 1,
            $row->payment_date,
            $row->detail,
            $row->house_number,
            $row->area_size,
            $row->payment_method,
            $row->month_name_start . " - " . $row->month_name_start,
            $row->period_year,
            $row->payment_type,
            number_format($amount, 2), // รูปแบบ 2 ตำแหน่งทศนิยม
            $payment_status_desc,
            $row->payment_method
        ]);
    }

    // แสดงแถวสุดท้ายเป็นยอดรวม
    fputcsv($output, [
        '', '', '', '', '', '', '', '', 'รวมทั้งหมด',
        number_format($sum_amount, 2),
        '', ''
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
    <meta name="description" content="">
    <meta name="author" content="">
    <link rel="icon" href="img/favicon.ico" type="image/x-icon">
    <link href="img/logo/logo.png" rel="icon">
    <title>PS33 House System</title>

    <link href="bootstrap/css/bootstrap.min.css" rel="stylesheet"/>
    <link rel="stylesheet" href="fontawesome/css/font-awesome.css"/>

    <!-- DataTables CSS -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.5/css/dataTables.bootstrap5.min.css"/>

    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css"/>
    <link rel="stylesheet" href="https://cdn.datatables.net/responsive/2.5.0/css/responsive.dataTables.min.css"/>

    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/responsive/2.5.0/js/dataTables.responsive.min.js"></script>

    <style>
        /* ไม่ให้ข้อความตกบรรทัด */
        table.dataTable tbody td,
        table.dataTable thead th {
            white-space: nowrap;
        }

        /* เวลาตารางกว้างเกิน ให้มี scroll แนวนอน */
        .dataTables_wrapper {
            overflow-x: auto;
        }
    </style>

    <style>
        table {
            width: 100%;
        }

        .status.approved {
            color: green;
            font-weight: bold;
        }

        .status.rejected {
            color: red;
            font-weight: bold;
        }

        .status.pending {
            color: black;
            font-style: italic;
        }
    </style>
</head>
<body>
<div class="container-fluid">
    <div class="card">
        <div class="card-header bg-primary text-white">
            <i class="fa fa-signal" aria-hidden="true"></i> แสดงข้อมูลการรับชำระค่าส่วนกลาง
            <?php echo " วันที่ " . htmlentities($start_date) . " ถึง " . htmlentities($end_date); ?>
        </div>
        <div class="card-body">

            <!-- ปุ่ม Export Excel -->
            <form method="post" style="display: inline-block;">
                <input type="hidden" name="doc_date_start" id="doc_date_start" value="<?php echo htmlentities($start_date); ?>">
                <input type="hidden" name="doc_date_to" id = "doc_date_to" value="<?php echo htmlentities($end_date); ?>">
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

            <h4><span class="badge bg-info">แสดงข้อมูลการรับชำระค่าส่วนกลาง</span></h4>

            <table id="PaymentTable" class="table table-striped table-bordered">
                <thead>
                <tr>
                    <th>#</th>
                    <th>วันที่</th>
                    <th>ผู้ชำระ</th>
                    <th>บ้านเลขที่</th>
                    <th>พื้นที่บ้าน ตรว</th>
                    <th>ค่าส่วนกลาง</th>
                    <th>ชำระโดย</th>
                    <th>งวดเดือน</th>
                    <th>ปี</th>
                    <th>จำนวนงวด</th>
                    <th>จำนวนเงินที่ชำระ</th>
                    <th>สถานะ</th>
                    <th>วิธีชำระ</th>
                </tr>
                </thead>
                <tbody>
                <?php foreach ($house_payment_data as $index => $row_house_payment): ?>
                    <?php
                    $payment_status_desc = '';
                    switch ($row_house_payment->payment_status) {
                        case 'Y':
                            $payment_status_desc = "<span style='color: green;'>ยืนยันการชำระ</span>";
                            break;
                        case 'N':
                            $payment_status_desc = "<span style='color: red;'>ยังไม่ยืนยันการชำระ</span>";
                            break;
                        default:
                            $payment_status_desc = "<span>ไม่ระบุสถานะ</span>";
                    }
                    ?>
                    <tr>
                        <td><?php echo htmlentities($index + 1); ?></td>
                        <td><?php echo htmlentities($row_house_payment->payment_date); ?></td>
                        <td><?php echo htmlentities($row_house_payment->detail); ?></td>
                        <td><?php echo htmlentities($row_house_payment->house_number); ?></td>
                        <td><?php echo htmlentities($row_house_payment->area_size); ?></td>
                        <td><?php echo htmlentities($row_house_payment->common_fee); ?></td>
                        <td><?php echo htmlentities($row_house_payment->payment_method); ?></td>
                        <td><?php echo htmlentities($row_house_payment->month_name_start . " - " . $row_house_payment->month_name_start); ?></td>
                        <td><?php echo htmlentities($row_house_payment->period_year); ?></td>
                        <td><?php echo htmlentities($row_house_payment->payment_type); ?></td>
                        <td><?php echo htmlentities($row_house_payment->amount); ?></td>
                        <td><?php echo $payment_status_desc; ?></td>
                        <td><?php echo htmlentities($row_house_payment->payment_method); ?></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
                <tfoot>
                <tr>
                    <th colspan="10" class="text-end">รวมทั้งหมด:</th>
                    <th id="totalAmountFooter"></th>
                    <th colspan="2"></th>
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

<!-- CSS -->
<link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.4.1/css/buttons.dataTables.min.css">

<!-- JS -->
<script src="https://cdn.datatables.net/buttons/2.4.1/js/dataTables.buttons.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.html5.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/pdfmake.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/vfs_fonts.js"></script>

<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.5.25/jspdf.plugin.autotable.min.js"></script>

<script>
    $(document).ready(function () {
        $('#PaymentTable').DataTable({
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

                // ฟังก์ชันลบ format และแปลงเป็นตัวเลข
                let parseValue = function (i) {
                    return typeof i === 'string'
                        ? parseFloat(i.replace(/[,]/g, '')) || 0
                        : typeof i === 'number'
                            ? i : 0;
                };

                // รวมคอลัมน์จำนวนเงินที่ index 10 (คอลัมน์ "จำนวนเงินที่ชำระ")
                let total = api
                    .column(10, { page: 'all' }) // ใช้ข้อมูลทั้งตาราง
                    .data()
                    .reduce(function (a, b) {
                        return parseValue(a) + parseValue(b);
                    }, 0);

                // แสดงผลรวมใน footer
                $(api.column(10).footer()).html(
                    total.toLocaleString('en-US', { minimumFractionDigits: 2 }) + ' บาท'
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

        const url = `receipt_pdf_out?doc_date_start=${encodeURIComponent(startDate)}&doc_date_to=${encodeURIComponent(endDate)}&payment_method=${encodeURIComponent(paymentMethod)}`;
        window.open(url, '_blank');
    }
</script>

</body>
</html>