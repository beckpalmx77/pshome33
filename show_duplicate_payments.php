
<?php
include('config/connect_db.php');

$sql = "
    SELECT 
        id,
        house_number,
        period_year,
        period_month_start,
        period_month_to,
        payment_status,
        COUNT(*) OVER (PARTITION BY house_number, period_year, period_month_start) AS dup_count
    FROM ims_house_payment
    WHERE (house_number, period_year, period_month_start) IN (
        SELECT house_number, period_year, period_month_start
        FROM ims_house_payment
        GROUP BY house_number, period_year, period_month_start
        HAVING COUNT(*) > 1
    )
    ORDER BY house_number, period_year, period_month_start, id
";

$stmt = $conn->prepare($sql);
$stmt->execute();
$results = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <title>รายการข้อมูลซ้ำ</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css" rel="stylesheet">
    <link href="https://cdn.datatables.net/buttons/2.4.1/css/buttons.dataTables.min.css" rel="stylesheet">
</head>
<body class="bg-dark text-white">
    <div class="container py-4">
        <h3 class="mb-4">📋 รายการข้อมูลซ้ำ (บ้าน + ปี + เดือนเริ่ม)</h3>

        <table id="duplicateTable" class="table table-bordered table-hover table-striped table-dark">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>บ้านเลขที่</th>
                    <th>ปี</th>
                    <th>เดือนเริ่ม</th>
                    <th>เดือนถึง</th>
                    <th>สถานะการชำระ</th>
                    <th>รายการซ้ำ</th>
                    <th>จัดการ</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($results as $row): ?>
                    <tr>
                        <td><?= htmlspecialchars($row['id']) ?></td>
                        <td><?= htmlspecialchars($row['house_number']) ?></td>
                        <td><?= htmlspecialchars($row['period_year']) ?></td>
                        <td><?= htmlspecialchars($row['period_month_start']) ?></td>
                        <td><?= htmlspecialchars($row['period_month_to']) ?></td>
                        <td><?= htmlspecialchars($row['payment_status']) ?></td>
                        <td><?= $row['dup_count'] ?></td>
                        <td>
                            <button class="btn btn-danger btn-sm delete-duplicate" data-house="<?= $row['house_number'] ?>"
                                    data-year="<?= $row['period_year'] ?>" data-month="<?= $row['period_month_start'] ?>">
                                ลบซ้ำ
                            </button>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.1/js/dataTables.buttons.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/pdfmake.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/vfs_fonts.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.html5.min.js"></script>

    <script>
        $(document).ready(function () {
            $('#duplicateTable').DataTable({
                dom: 'Bfrtip',
                buttons: [
                    {
                        extend: 'excelHtml5',
                        text: 'Export Excel',
                        className: 'btn btn-success'
                    },
                    {
                        extend: 'pdfHtml5',
                        text: 'Export PDF',
                        className: 'btn btn-danger'
                    }
                ],
                language: {
                    search: 'ค้นหา:',
                    lengthMenu: 'แสดง _MENU_ รายการ',
                    zeroRecords: 'ไม่พบข้อมูลซ้ำ',
                    info: 'แสดง _START_ ถึง _END_ จาก _TOTAL_ รายการ',
                    infoEmpty: 'ไม่มีข้อมูล',
                    infoFiltered: '(กรองจากทั้งหมด _MAX_ รายการ)',
                    paginate: {
                        first: 'หน้าแรก',
                        last: 'หน้าสุดท้าย',
                        next: 'ถัดไป',
                        previous: 'ก่อนหน้า'
                    }
                }
            });

            $(document).on('click', '.delete-duplicate', function () {
                const house = $(this).data('house');
                const year = $(this).data('year');
                const month = $(this).data('month');

                if (confirm(`ลบรายการซ้ำของ ${house} เดือน ${month}/${year} ใช่หรือไม่?`)) {
                    $.post('delete_duplicates.php', {
                        house_number: house,
                        period_year: year,
                        period_month_start: month
                    }, function (response) {
                        alert(response.message);
                        location.reload();
                    }, 'json');
                }
            });
        });
    </script>
</body>
</html>
