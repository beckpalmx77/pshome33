<?php
include('includes/Header.php');
include('config/connect_db.php');

$sql = "
    SELECT 
        id,
        house_number,
        period_year,
        period_month_start,
        period_month_to,
        payment_status,
        picture_payment,
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
<body id="page-top">
<div id="wrapper">
    <?php
    include('includes/Side-Bar.php');
    ?>

    <div id="content-wrapper" class="d-flex flex-column">
        <div id="content">
            <?php
            include('includes/Top-Bar.php');
            ?>
            <!-- Container Fluid-->
            <div class="container-fluid" id="container-wrapper">
                <div class="d-sm-flex align-items-center justify-content-between mb-4">
                    <h1 class="h4 mb-0 text-gray-800"><?php echo urldecode($_GET['s']) ?></h1>
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="<?php echo $_SESSION['dashboard_page'] ?>">Home</a>
                        </li>
                        <li class="breadcrumb-item"><?php echo urldecode($_GET['m']) ?></li>
                        <li class="breadcrumb-item active"
                            aria-current="page"><?php echo urldecode($_GET['s']) ?></li>
                    </ol>
                </div>

                <div class="row">
                    <div class="col-lg-12">
                        <div class="card mb-12">
                            <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                            </div>
                            <div class="card-body">
                                <section class="container-fluid">

                                    <div class="col-md-12 col-md-offset-2">
                                        <label for="item_name"
                                               class="control-label"><b>เพิ่ม <?php echo urldecode($_GET['s']) ?></b></label>

                                        <button type='button' name='btnAdd' id='btnAdd'
                                                class='btn btn-primary btn-xs'>Add
                                            <i class="fa fa-plus"></i>
                                        </button>
                                    </div>

                                    <div class="col-md-12 col-md-offset-2">
                                        <table id='TableRecordList' class='display dataTable'>
                                            <thead>
                                            <tr>
                                                <th>ID</th>
                                                <th>บ้านเลขที่</th>
                                                <th>ปี</th>
                                                <th>เดือนเริ่ม</th>
                                                <th>เดือนถึง</th>
                                                <th>รูปภาพ</th>
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
                                                    <td>
                                                        <?php if (!empty($row['picture_payment'])): ?>
                                                            <a href="#" class="view-picture text-info"
                                                               data-bs-toggle="modal" data-bs-target="#imageModal"
                                                               data-image="<?= !empty($row['picture_payment']) ? 'uploads/slips/' . htmlspecialchars($row['picture_payment']) : '' ?>">
                                                                ดูรูป
                                                            </a>
                                                        <?php else: ?>
                                                            <span class="text-muted">ไม่มีรูปภาพ</span>
                                                        <?php endif; ?>
                                                    </td>
                                                    <td><?= htmlspecialchars($row['payment_status']) ?></td>
                                                    <td><?= $row['dup_count'] ?></td>
                                                    <td>
                                                        <button class="btn btn-danger btn-sm delete-duplicate"
                                                                data-house="<?= $row['house_number'] ?>"
                                                                data-year="<?= $row['period_year'] ?>"
                                                                data-month="<?= $row['period_month_start'] ?>">
                                                            ลบซ้ำ
                                                        </button>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                            </tbody>
                                        </table>

                                        <div id="result"></div>

                                    </div>


                            </div>
                        </div>
                    </div>
                </div>

                <!-- Modal แสดงรูปภาพ -->
                <div class="modal fade" id="imageModal" tabindex="-1" aria-labelledby="imageModalLabel" aria-hidden="true">
                    <div class="modal-dialog modal-dialog-centered modal-lg">
                        <div class="modal-content bg-dark text-white">
                            <div class="modal-header">
                                <h5 class="modal-title" id="imageModalLabel">รูปภาพแนบการชำระ</h5>
                                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <div class="modal-body text-center">
                                <img id="modalImage" src="" class="img-fluid rounded" alt="แนบสลิป" style="max-height: 70vh;">
                            </div>
                        </div>
                    </div>
                </div>


            </div>
        </div>
    </div>
</div>

<?php
include('includes/Modal-Logout.php');
include('includes/Footer.php');
?>


<!-- Scroll to top -->
<a class="scroll-to-top rounded" href="#page-top">
    <i class="fas fa-angle-up"></i>
</a>


<script src="vendor/jquery/jquery.min.js"></script>
<script src="vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
<script src="vendor/jquery-easing/jquery.easing.min.js"></script>
<script src="js/myadmin.min.js"></script>

<script src="js/modal/show_brand_modal.js"></script>
<script src="js/modal/show_m_category_modal.js"></script>

<!-- Page level plugins -->

<!--script src="https://cdnjs.cloudflare.com/ajax/libs/bootbox.js/5.5.2/bootbox.min.js"></script>
<script src="https://cdn.datatables.net/1.11.0/js/jquery.dataTables.min.js"></script>
<link rel="stylesheet" href="https://cdn.datatables.net/1.11.0/css/jquery.dataTables.min.css"/>
<link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.0.0/css/buttons.dataTables.min.css"/-->

<script src="vendor/bootstrap-datepicker/js/bootstrap-datepicker.min.js"></script>

<script src="vendor/date-picker-1.9/js/bootstrap-datepicker.js"></script>
<script src="vendor/date-picker-1.9/locales/bootstrap-datepicker.th.min.js"></script>
<!--link href="vendor/date-picker-1.9/css/date_picker_style.css" rel="stylesheet"/-->
<link href="vendor/date-picker-1.9/css/bootstrap-datepicker.css" rel="stylesheet"/>

<script src="vendor/datatables/v11/bootbox.min.js"></script>
<script src="vendor/datatables/v11/jquery.dataTables.min.js"></script>
<link rel="stylesheet" href="vendor/datatables/v11/jquery.dataTables.min.css"/>
<link rel="stylesheet" href="vendor/datatables/v11/buttons.dataTables.min.css"/>

<style>

    .icon-input-btn {
        display: inline-block;
        position: relative;
    }

    .icon-input-btn input[type="submit"] {
        padding-left: 2em;
    }

    .icon-input-btn .fa {
        display: inline-block;
        position: absolute;
        left: 0.65em;
        top: 30%;
    }
</style>

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

<script>
    $(document).on('click', '.view-picture', function () {
        const imageUrl = $(this).data('image');
        $('#modalImage').attr('src', imageUrl);
    });
</script>


</body>
</html>

