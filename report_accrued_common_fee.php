<?php
include "config/connect_db.php";

// 1. รับค่าเดือนและปีจากฟอร์ม ถ้าไม่มีให้ใช้เดือนปีปัจจุบันเป็นค่าเริ่มต้น
$selected_month = isset($_GET['month']) ? (int)$_GET['month'] : date('n');
$selected_year  = isset($_GET['year']) ? (int)$_GET['year'] : date('Y');

// Array เดือนภาษาไทยสำหรับแสดงผล
$thai_months = [
    "มกราคม", "กุมภาพันธ์", "มีนาคม", "เมษายน", "พฤษภาคม", "มิถุนายน",
    "กรกฎาคม", "สิงหาคม", "กันยายน", "ตุลาคม", "พฤศจิกายน", "ธันวาคม"
];
$display_month_name = $thai_months[$selected_month - 1];


// 2. แก้ไข SQL Query ให้ใช้ Placeholder (:month, :year) เพื่อความปลอดภัย
$sql = "
SELECT
    m.house_number,
    h.contact_name,
    h.phone_number
FROM
    ims_house_master AS m
LEFT JOIN
    ims_house AS h ON m.house_number = h.house_number
LEFT JOIN
    ims_house_payment AS p ON m.house_number = p.house_number
    AND p.period_year = :year -- Placeholder for the selected year (e.g., 2025)
    AND :month BETWEEN p.period_month_start AND p.period_month_to -- Placeholder for the selected month (e.g., 8)
    AND p.payment_status = 'Y'
WHERE
    p.id IS NULL
    AND m.status = 'Y'
ORDER BY
    m.house_number;
";

// 3. ใช้ Prepared Statement ในการดึงข้อมูล
$stmt = $conn->prepare($sql);
$stmt->bindParam(':year', $selected_year, PDO::PARAM_INT);
$stmt->bindParam(':month', $selected_month, PDO::PARAM_INT);
$stmt->execute();
$results = $stmt->fetchAll();

?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>รายงานบ้านที่ค้างชำระค่าส่วนกลาง</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/2.0.8/css/dataTables.bootstrap5.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/buttons/3.0.2/css/buttons.bootstrap5.css">


    <style>
        @import url('https://fonts.googleapis.com/css2?family=Sarabun:wght@400;700&display=swap');
        body { font-family: 'Sarabun', sans-serif; background-color: #f8f9fa; }
        /* ทำให้ปุ่ม Export อยู่ห่างจากช่อง Search เล็กน้อย */
        div.dt-buttons { float: left; margin-right: 1rem; }
    </style>
</head>
<body>

<div class="container mt-5">

    <div class="card mb-4">
        <div class="card-header"><i class="bi bi-search"></i> ค้นหาตามเดือนและปี</div>
        <div class="card-body">
            <form action="" method="GET" class="row g-3 align-items-end">
                <div class="col-md-5">
                    <label for="month" class="form-label">เลือกเดือน</label>
                    <select name="month" id="month" class="form-select">
                        <?php for ($m = 1; $m <= 12; $m++): ?>
                            <option value="<?= $m ?>" <?= ($m == $selected_month) ? 'selected' : '' ?>><?= $thai_months[$m - 1] ?></option>
                        <?php endfor; ?>
                    </select>
                </div>
                <div class="col-md-3">
                    <label for="year" class="form-label">เลือกปี (พ.ศ.)</label>
                    <select name="year" id="year" class="form-select">
                        <?php
                        $current_year = date('Y');
                        for ($y = $current_year - 5; $y <= $current_year + 1; $y++): ?>
                            <option value="<?= $y ?>" <?= ($y == $selected_year) ? 'selected' : '' ?>><?= $y + 543 ?></option>
                        <?php endfor; ?>
                    </select>
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-primary w-100"><i class="bi bi-search"></i> ค้นหา</button>
                </div>
                <div class="col-md-2">
                    <a href="?" class="btn btn-outline-secondary w-100"><i class="bi bi-arrow-clockwise"></i> เดือนปัจจุบัน</a>
                </div>
            </form>
        </div>
    </div>

    <div class="card shadow-sm">
        <div class="card-header bg-danger text-white">
            <h3 class="mb-0"><i class="bi bi-file-earmark-excel-fill"></i> รายงานบ้านที่ค้างชำระค่าส่วนกลาง</h3>
            <p class="mb-0">ข้อมูลประจำเดือน: <strong><?= htmlspecialchars($display_month_name ?? '') ?></strong> ปี <strong><?= $selected_year + 543 ?></strong></p>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table id="reportTable" class="table table-striped table-bordered" style="width:100%">
                    <thead class="table-dark">
                    <tr>
                        <th class="text-center">ลำดับ</th>
                        <th>บ้านเลขที่</th>
                        <th>ชื่อผู้ติดต่อ</th>
                        <th>เบอร์โทรศัพท์</th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php if (empty($results)): ?>
                        <tr><td colspan="4" class="text-center">ไม่พบข้อมูลบ้านที่ค้างชำระในเดือนที่เลือก</td></tr>
                    <?php else: ?>
                        <?php $i = 1; ?>
                        <?php foreach ($results as $row): ?>
                            <tr>
                                <td class="text-center"><?= $i++ ?></td>
                                <td><?= htmlspecialchars($row['house_number'] ?? '') ?></td>
                                <td><?= htmlspecialchars($row['contact_name'] ?? '') ?></td>
                                <td><?= htmlspecialchars($row['phone_number'] ?? '') ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
        <div class="card-footer text-muted">พบข้อมูลบ้านที่ค้างชำระทั้งหมด: <strong><?= count($results) ?></strong> รายการ</div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.datatables.net/2.0.8/js/dataTables.js"></script>
<script src="https://cdn.datatables.net/2.0.8/js/dataTables.bootstrap5.js"></script>

<script src="https://cdn.datatables.net/buttons/3.0.2/js/dataTables.buttons.js"></script>
<script src="https://cdn.datatables.net/buttons/3.0.2/js/buttons.bootstrap5.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/pdfmake.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/vfs_fonts.js"></script>
<script src="https://cdn.datatables.net/buttons/3.0.2/js/buttons.html5.js"></script>
<script src="https://cdn.datatables.net/buttons/3.0.2/js/buttons.print.js"></script>


<script>
    $(document).ready(function () {
        pdfMake.fonts = {
            Sarabun: {
                normal: 'https://fonts.gstatic.com/s/sarabun/v13/DtVjJx26TKEr37c9aAFJn2ok.ttf',
                bold: 'https://fonts.gstatic.com/s/sarabun/v13/DtVmJx26TKEr37c9YK5sulsc.ttf',
                italics: 'https://fonts.gstatic.com/s/sarabun/v13/DtVhJx26TKEr37c9aBBtm2g2.ttf',
                bolditalics: 'https://fonts.gstatic.com/s/sarabun/v13/DtVnJx26TKEr37c9aBBxun0s-A.ttf'
            }
        };

        $('#reportTable').DataTable({
            language: {url: "//cdn.datatables.net/plug-ins/2.0.8/i18n/th.json"},
            responsive: true,
            pageLength: 5,
            lengthMenu: [[5, 10, 20, 100, -1], [5, 10, 20, 100, "ทั้งหมด"]],
            dom: "<'row'<'col-sm-12 col-md-6'l><'col-sm-12 col-md-6'f>>" +
                "<'row'<'col-sm-12'tr>>" +
                "<'row'<'col-sm-12 col-md-5'i><'col-sm-12 col-md-7'p>>" +
                "<'row'<'col-sm-12'B>>",
            buttons: [
                {
                    extend: 'excelHtml5',
                    text: '<i class="bi bi-file-earmark-excel"></i> Export to Excel',
                    className: 'btn btn-success btn-sm',
                    title: 'รายงานบ้านที่ค้างชำระ-<?= $display_month_name ?>-<?= $selected_year + 543 ?>'
                }
            ]
        });
    });
</script>

</body>
</html>