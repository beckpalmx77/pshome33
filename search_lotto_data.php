<?php
include('includes/Header.php');
require_once 'config/connect_lotto_db.php';

$condition = '';
$params = [];
$message = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // รับค่า input และตัดช่องว่างส่วนเกิน
    $lotto_name = trim($_POST['lotto_name'] ?? '');
    $lotto_phone = trim($_POST['lotto_phone'] ?? '');

    // ตรวจสอบว่ามีค่าป้อนเข้ามาหรือไม่
    if (empty($lotto_name) && empty($lotto_phone)) {
        $message = 'กรุณาป้อนเงื่อนไข (ชื่อร้าน หรือ หมายเลขโทรศัพท์) ก่อน';
    } else {
        if (!empty($lotto_name)) {
            $condition .= " AND lotto_name LIKE :lotto_name";
            $params[':lotto_name'] = "%{$lotto_name}%";
        }
        if (!empty($lotto_phone)) {
            $condition .= " AND lotto_phone LIKE :lotto_phone";
            $params[':lotto_phone'] = "%{$lotto_phone}%";
        }

        // สร้างคำสั่ง SQL และป้องกัน Injection ด้วย bindParam
        $sql = "SELECT * FROM ims_lotto WHERE 1=1 $condition ORDER BY id DESC";
        $stmt = $conn->prepare($sql);

        // Bind Parameters
        foreach ($params as $key => &$value) {
            $stmt->bindParam($key, $value, PDO::PARAM_STR);
        }

        $stmt->execute();
        $result = $stmt->fetchAll();
    }
}
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
            <h6 style="color: blue"><b>ค้นหาข้อมูลการลงทะเบียน และ ตรวจสอบการได้รางวัล SAC LOTTO</b></h6>

            <!-- ฟอร์มสำหรับกรองข้อมูล -->
            <form method="POST" action="">
                <div class="row mb-3">
                    <div class="col-md-3">
                        <input type="text" name="lotto_name" class="form-control" placeholder="ชื่อร้าน">
                    </div>&nbsp;
                    <div class="col-md-3">
                        <input type="text" name="lotto_phone" class="form-control" placeholder="หมายเลขโทรศัพท์">
                    </div>&nbsp;
                    <div class="col-md-3">
                        <button type="submit" class="btn btn-primary"><i class="fa fa-search" aria-hidden="true"></i> ค้นหา</button>
                        <button type="button" id="resetBtn" class="btn btn-secondary"><i class="fa fa-refresh" aria-hidden="true"></i> ล้างค่า</button>
                        <button type="button" id="closeBtn" class="btn btn-danger"><i class="fa fa-times-circle" aria-hidden="true"></i> ปิด</button>
                    </div>
                </div>
            </form>

            <!-- แสดงข้อความแจ้งเตือนหากไม่มีการกรอกข้อมูล -->
            <?php if ($message): ?>
                <div class="alert alert-danger" role="alert" style="color: white; background-color: red;">
                    <?= $message ?>
                </div>
            <?php endif; ?>

            <?php if (!empty($_POST) && $message === ''): ?> <!-- แสดงข้อมูลเมื่อมีการกดปุ่มค้นหาและเงื่อนไขไม่ว่าง -->
                <table id="DataTable"
                       class="display table table-striped table-hover table-responsive table-bordered">
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
                        <!--th width="15%">รูปภาพป้ายไวนิล 1</th>
                        <th width="15%">รูปภาพป้ายไวนิล 2</th>
                        <th width="15%">รูปภาพเลขหลังป้ายไวนิล</th-->
                        <th width="15%">ดูข้อมูล</th>
                        <th width="15%">ตรวจรางวัล</th>
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
                            <td style="text-align: center;">
                                <a href="javascript:void(0);" class="btn btn-info btn-sm"
                                   onclick="openLottoResult('<?= $rows['id']; ?>', '<?= $rows['lotto_name']; ?>', '<?= $rows['lotto_phone']; ?>', '<?= $rows['lotto_number']; ?>')">
                                    <?= $rows['lotto_number']; ?>
                                </a>
                            </td>
                            <td><?= $rows['sale_name']; ?></td>
                            <td style="color: <?= $rows['approve_status'] == 'Y' ? 'green' : ($rows['approve_status'] == 'N' ? 'gray' : 'black'); ?>; text-align: center;">
                                <?= $rows['approve_status'] == 'Y' ? 'อนุมัติ' : ($rows['approve_status'] == 'N' ? 'ยังไม่อนุมัติ' : ''); ?>
                            </td>
                            <td><?= $rows['create_date']; ?></td>


                            <!--td>
                                <?php
                                if (!empty($rows['lotto_file'])) {
                                    foreach (explode(",", $rows['lotto_file']) as $index => $file) {
                                        $filePath = 'uploads/' . htmlspecialchars($file);
                                        echo "<a href='javascript:void(0);' class='open-popup' data-img='$filePath'>รูปที่ " . ($index + 1) . "</a><br>";
                                    }
                                } else {
                                    echo "ไม่มีรูป";
                                }
                                ?>
                            </td>
                            <td>
                                <?php
                                if (!empty($rows['lotto_file1'])) {
                                    foreach (explode(",", $rows['lotto_file1']) as $index => $file) {
                                        $filePath = 'uploads/' . htmlspecialchars($file);
                                        echo "<a href='javascript:void(0);' class='open-popup' data-img='$filePath'>รูปที่ " . ($index + 1) . "</a><br>";
                                    }
                                } else {
                                    echo "ไม่มีรูป";
                                }
                                ?>
                            </td>
                            <td>
                                <?php
                                if (!empty($rows['lotto_file2'])) {
                                    foreach (explode(",", $rows['lotto_file2']) as $index => $file) {
                                        $filePath = 'uploads/' . htmlspecialchars($file);
                                        echo "<a href='javascript:void(0);' class='open-popup' data-img='$filePath'>รูปที่ " . ($index + 1) . "</a><br>";
                                    }
                                } else {
                                    echo "ไม่มีรูป";
                                }
                                ?>
                            </td-->

                            <td style="text-align: center;">
                                <a href="javascript:void(0);" class="btn btn-primary btn-sm"
                                   onclick="openLottoData('<?= $rows['id']; ?>')">
                                    Click
                                </a>
                            </td>
                            <td style="text-align: center;">
                                <a href="javascript:void(0);" class="btn btn-success btn-sm"
                                   onclick="openLottoResult('<?= $rows['id']; ?>', '<?= $rows['lotto_name']; ?>', '<?= $rows['lotto_phone']; ?>', '<?= $rows['lotto_number']; ?>')">
                                    Click
                                </a>
                            </td>
                        </tr>
                    <?php } ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>
    </div>

    <!-- Popup แสดงภาพ -->
    <div id="imagePopup" class="popup">
        <div class="popup-content">
            <span class="close-popup">✖ ปิด</span>
            <img id="popupImage" src="" alt="Preview">
        </div>
    </div>


</div>

<style>
    .popup {
        display: none;
        position: fixed;
        z-index: 9999;
        left: 0;
        top: 0;
        width: 100%;
        height: 100%;
        background-color: rgba(0, 0, 0, 0.8);
        justify-content: center;
        align-items: center;
    }

    .popup-content {
        position: relative;
        max-width: 90%;
        max-height: 90%;
        text-align: center;
    }

    .popup-content img {
        max-width: 100%;
        max-height: 100%;
        border-radius: 10px;
    }

    .close-popup {
        position: absolute;
        top: 10px;
        right: 15px;
        background: rgba(0, 0, 0, 0.6);
        color: white;
        font-size: 18px;
        padding: 5px 10px;
        border-radius: 5px;
        cursor: pointer;
        transition: 0.3s;
    }

    .close-popup:hover {
        background: rgba(0, 0, 0, 0.9);
    }


</style>

<script>
    $(document).ready(function () {
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

<script>
    function openLottoData(id) {
        window.open(`show_data_lotto?id=${id}`);
    }
</script>

<script>
    function openLottoResult(id, lotto_name, phone, lotto_number ) {
        // ส่งค่าหลายค่าไปยัง URL
        window.open(`show_data_lotto_prize_result?id=${id}&lotto_name=${lotto_name}&phone=${phone}&lotto_number=${lotto_number}`);
    }
</script>

<script>
    $(document).ready(function () {
        $('#closeBtn').click(function (event) {
            event.preventDefault(); // ป้องกันการโหลดหน้าใหม่โดยตรงจาก `<a>`
            window.location.href = "sac_lotto";
        });
    });
</script>

<script>
    $(document).ready(function () {
        $(".open-popup").click(function () {
            let imgSrc = $(this).attr("data-img");
            $("#popupImage").attr("src", imgSrc);
            $("#imagePopup").fadeIn();
        });

        $(".close-popup, #imagePopup").click(function () {
            $("#imagePopup").fadeOut();
        });

        $(".popup-content").click(function (event) {
            event.stopPropagation();
        });
    });

</script>

<script>
    $(document).ready(function () {
        // Initialize DataTable
        $('#DataTable').DataTable({
            "language": {
                "lengthMenu": "แสดง _MENU_ รายการ",
                "zeroRecords": "ไม่พบข้อมูล",
                "info": "แสดง _START_ ถึง _END_ จาก _TOTAL_ รายการ",
                "infoEmpty": "แสดง 0 ถึง 0 จาก 0 รายการ",
                "infoFiltered": "(จากทั้งหมด _MAX_ รายการ)",
                "search": "ค้นหา:",
                "paginate": {
                    "first": "แรก",
                    "last": "สุดท้าย",
                    "next": "ถัดไป",
                    "previous": "ก่อนหน้า"
                }
            }
        });

    });
</script>

</body>
</html>
