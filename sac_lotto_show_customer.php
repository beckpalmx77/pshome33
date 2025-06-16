<?php
include('includes/Header.php');
require_once 'config/connect_lotto_db.php';

// ตรวจสอบว่ามีการส่งข้อมูลเงื่อนไขมาหรือไม่
$condition = '';
$params = [];

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (!empty($_POST['lotto_name'])) {
        $condition .= " AND lotto_name LIKE :lotto_name";
        $params[':lotto_name'] = "%" . $_POST['lotto_name'] . "%";
    }
    if (!empty($_POST['lotto_phone'])) {
        $condition .= " AND lotto_phone LIKE :lotto_phone";
        $params[':lotto_phone'] = "%" . $_POST['lotto_phone'] . "%";
    }
    if (!empty($_POST['lotto_province'])) {
        $condition .= " AND lotto_province LIKE :lotto_province";
        $params[':lotto_province'] = "%" . $_POST['lotto_province'] . "%";
    }
}

// สร้างคำสั่ง SQL โดยเพิ่มเงื่อนไขกรองข้อมูล
$sql = "SELECT * FROM ims_lotto WHERE 1=1 $condition ORDER BY id DESC";
$stmt = $conn->prepare($sql);
$stmt->execute($params);
$result = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.1/dist/js/bootstrap.bundle.min.js"></script>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.11.3/css/jquery.dataTables.css">
    <script src="https://code.jquery.com/jquery-3.5.1.js"></script>
    <script src="https://cdn.datatables.net/1.11.3/js/jquery.dataTables.js"></script>
    <title>SAC LOTTO LIST</title>
</head>
<body>
<div class="container">
    <div class="row">
        <div class="col-md-12"><br>
            <div><img src="img/logo/logo text-01.png" width="200" height="79"/></div>
            <h6 style="color: blue"><b>SAC LOTTO LIST</b></h6>

            <!-- ฟอร์มสำหรับกรองข้อมูล -->
            <form method="POST" action="">
                <div class="row mb-3">
                    <div class="col-md-3">
                        <input type="text" name="lotto_name" class="form-control" placeholder="ชื่อร้าน">
                    </div>
                    <div class="col-md-3">
                        <input type="text" name="lotto_phone" class="form-control" placeholder="หมายเลขโทรศัพท์">
                    </div>
                    <div class="col-md-3">
                        <input type="text" name="lotto_province" class="form-control" placeholder="จังหวัด">
                    </div>
                    <div class="col-md-3">
                        <button type="submit" class="btn btn-primary">ค้นหา</button>
                        <button type="button" id="resetBtn" class="btn btn-secondary">ล้างค่า</button>
                    </div>
                </div>
            </form>

            <?php if (!empty($_POST)): ?> <!-- แสดงข้อมูลเมื่อมีการกดปุ่มค้นหา -->
                <table id="DataTable" class="display table table-striped table-hover table-responsive table-bordered">
                    <thead>
                    <tr>
                        <th width="5%">ลำดับ</th>
                        <th width="25%">ชื่อร้าน</th>
                        <th width="10%">หมายเลขโทรศัพท์</th>
                        <th width="15%">จังหวัด</th>
                        <th width="15%">หมายเลขที่เลือก</th>
                        <th width="15%">ชื่อ Sale</th>
                        <th width="15%">การอนุมัติ</th>
                        <th width="15%">วันที่บันทึก</th>
                        <th width="15%">รูปภาพ</th>
                        <th width="10%">Action</th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php
                    $line_no = 0;
                    foreach ($result as $rows) {
                        $line_no++;
                        ?>
                        <tr>
                            <td><?= $line_no; ?></td>
                            <td><?= $rows['lotto_name']; ?></td>
                            <td><?= $rows['lotto_phone']; ?></td>
                            <td><?= $rows['lotto_province']; ?></td>
                            <td><?= $rows['lotto_number']; ?></td>
                            <td><?= $rows['sale_name']; ?></td>
                            <td style="color: <?= $rows['approve_status'] == 'Y' ? 'green' : ($rows['approve_status'] == 'N' ? 'gray' : 'black'); ?>; text-align: center;">
                                <?= $rows['approve_status'] == 'Y' ? 'อนุมัติ' : ($rows['approve_status'] == 'N' ? 'ยังไม่อนุมัติ' : ''); ?>
                            </td>
                            <td><?= $rows['create_date']; ?></td>
                            <td>
                                <?php
                                if (!empty($rows['lotto_file'])) {
                                    $files = explode(",", $rows['lotto_file']);
                                    $index = 1;
                                    foreach ($files as $file) {
                                        echo '<a href="uploads/' . $file . '" target="_blank">รูปที่ ' . $index . '</a><br>';
                                        $index++;
                                    }
                                } else {
                                    echo "ไม่มีรูปภาพ";
                                }
                                ?>
                            </td>
                            <td>
                                <button class="btn btn-outline-success" onclick="openPopup(<?= $rows['id']; ?>)">Update Approve Status</button>
                            </td>
                        </tr>
                    <?php } ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
    $(document).ready(function () {
        $('#DataTable').DataTable();

        $("#resetBtn").click(function () {
            window.location.href = window.location.pathname;
        });
    });

    function openPopup(id) {
        $.ajax({
            url: 'model/get_approve_status.php',
            type: 'GET',
            data: {id: id},
            success: function (data) {
                $('#approve_status').val(data);
                $('#lotto_id').val(id);
                $('#approveModal').modal('show');
            }
        });
    }
</script>

</body>
</html>
