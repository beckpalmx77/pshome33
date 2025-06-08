<?php

include('config/connect_lotto_db.php');
$id = $_GET['id'];

$stmt = $conn->prepare("SELECT * FROM ims_lotto WHERE id = :id");
$stmt->bindParam(':id', $id);
$stmt->execute();
$data = $stmt->fetch(PDO::FETCH_ASSOC);

// ฟังก์ชันแยกรูปภาพและกรองค่า null หรือค่าว่าง
function getImages($imageString) {
    return array_filter(array_map('trim', explode(',', $imageString ?? '')));
}

// แยกรูปภาพเฉพาะที่มีข้อมูล
$images = getImages($data['lotto_file']);
$images1 = getImages($data['lotto_file1']);
$images2 = getImages($data['lotto_file2']);
$images3 = getImages($data['lotto_file3']);
$images4 = getImages($data['lotto_file4']);
$images5 = getImages($data['lotto_file5']);
$images6 = getImages($data['lotto_file6']);
$images7 = getImages($data['lotto_file7']);
$images8 = getImages($data['lotto_file8']);

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ผลการลงทะเบียน</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
</head>
<body>
<div class="container py-4">
    <div class="card shadow-lg">
        <div class="card-body">
            <h3 class="card-title text-center mb-4">🎯 ผลการลงทะเบียน</h3>
            <div class="mb-3">
                <strong>🏪 ชื่อร้านค้า:</strong> <?= htmlspecialchars($data['lotto_name']) ?>
            </div>
            <div class="mb-3">
                <strong>📞 โทรศัพท์:</strong> <?= htmlspecialchars($data['lotto_phone']) ?>
            </div>
            <div class="mb-3">
                <strong>🗺️ จังหวัด:</strong> <?= htmlspecialchars($data['lotto_province']) ?>
            </div>
            <div class="mb-3">
                <strong>🧑‍💼 ชื่อ Sale:</strong> <?= htmlspecialchars($data['sale_name']) ?>
            </div>
            <div class="mb-4">
                <strong>🎟️ หมายเลข:</strong> <?= htmlspecialchars($data['lotto_number']) ?>
            </div>
            <div class="mb-4">
                <strong>📝 หมายเหตุ:</strong> <?= htmlspecialchars($data['remark']) ?>
            </div>

            <?php
            // ฟังก์ชันแสดงรูปภาพ
            function displayImages($images, $title) {
                if (!empty($images)) { ?>
                    <h5 class="text-left mb-3"><?= $title ?></h5>
                    <div class="row text-left">
                        <?php foreach ($images as $image): ?>
                            <div class="col-md-4 mb-3">
                                <div class="card">
                                    <img src="uploads/<?= htmlspecialchars($image) ?>" class="card-img-top img-fluid" alt="รูปภาพ">
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php }
            }

            // แสดงผลเฉพาะรายการที่มีรูปภาพ
            displayImages($images, "🖼️ รูปภาพป้ายไวนิลที่บันทึก");
            displayImages($images1, "🖼️ รูปภาพเพิ่มเติม");
            displayImages($images2, "🖼️ รูปภาพเลขหลังป้ายไวนิล");
            displayImages($images3, "🖼️ รูปภาพเพิ่มเติม");
            displayImages($images4, "🖼️ รูปภาพเพิ่มเติม");
            displayImages($images5, "🖼️ รูปภาพเพิ่มเติม");
            displayImages($images6, "🖼️ รูปภาพเพิ่มเติม");
            displayImages($images7, "🖼️ รูปภาพเพิ่มเติม");
            displayImages($images8, "🖼️ รูปภาพเพิ่มเติม");
            ?>

            <div class="col-md-12">
                <div class="form-group">
                    <button type="button" name="closetBtn" id="closetBtn" class="form-control btn btn-danger">
                        <i class="fa fa-close" aria-hidden="true"></i> ปิด
                    </button>
                </div>
            </div>

        </div>
    </div>
</div>

<script>
    $(document).ready(function () {
        $('#closetBtn').click(function () {
            window.close(); // ปิดหน้าต่าง
        });
    });
</script>

</body>
</html>
