<?php

include('config/connect_lotto_db.php');

// ป้องกันค่าที่ส่งมาเป็น null หรือไม่ถูกต้อง
$id = filter_input(INPUT_GET, 'id', FILTER_SANITIZE_NUMBER_INT);
if (!$id) {
    die("ไม่พบข้อมูลที่ต้องการ");
}

$stmt = $conn->prepare("SELECT * FROM ims_lotto WHERE id = :id");
$stmt->bindParam(':id', $id, PDO::PARAM_INT);
$stmt->execute();
$data = $stmt->fetch(PDO::FETCH_ASSOC);

// ถ้าไม่พบข้อมูล
if (!$data) {
    die("ไม่พบข้อมูลที่ต้องการ");
}

// ตรวจสอบค่าก่อน explode เพื่อป้องกัน error
$images = isset($data['lotto_file']) ? explode(',', $data['lotto_file']) : [];
$images1 = isset($data['lotto_file1']) ? explode(',', $data['lotto_file1']) : [];
$images2 = isset($data['lotto_file2']) ? explode(',', $data['lotto_file2']) : [];
$images3 = isset($data['lotto_file3']) ? explode(',', $data['lotto_file3']) : [];
$images4 = isset($data['lotto_file4']) ? explode(',', $data['lotto_file4']) : [];
$images5 = isset($data['lotto_file5']) ? explode(',', $data['lotto_file5']) : [];
$images6 = isset($data['lotto_file6']) ? explode(',', $data['lotto_file6']) : [];
$images7 = isset($data['lotto_file7']) ? explode(',', $data['lotto_file7']) : [];
$images8 = isset($data['lotto_file8']) ? explode(',', $data['lotto_file8']) : [];

?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="img/favicon.ico" type="image/x-icon">
    <link href="../img/logo/logo.png" rel="icon">
    <title>สงวนออโต้คาร์ | SANGUAN AUTO CAR</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
</head>
<body>
<div class="container py-4">
    <div class="card shadow-lg">
        <div class="card-body">
            <h3 class="card-title text-center mb-4">🎯 ผลการลงทะเบียน</h3>
            <img src="<?= ($data['approve_status'] == 'Y') ? 'img/logo/approve.png' : 'img/logo/none-approve.png' ?>" alt="Approval Status">

            <div class="mb-3"><strong>🏪 ชื่อร้านค้า:</strong> <?= htmlspecialchars($data['lotto_name']) ?></div>
            <div class="mb-3"><strong>📞 โทรศัพท์:</strong> <?= htmlspecialchars($data['lotto_phone']) ?></div>
            <div class="mb-3"><strong>🗺️ จังหวัด:</strong> <?= htmlspecialchars($data['lotto_province']) ?></div>
            <div class="mb-3"><strong>🧑‍💼 ชื่อ Sale:</strong> <?= htmlspecialchars($data['sale_name']) ?></div>
            <div class="mb-4"><strong>🎟️ หมายเลข:</strong> <?= htmlspecialchars($data['lotto_number']) ?></div>
            <div class="mb-4"><strong>📝 หมายเหตุ:</strong> <?= htmlspecialchars($data['remark']) ?></div>

            <?php
            $all_images = ['images' => $images, 'images1' => $images1];
            foreach ($all_images as $key => $img_array):
                if (!empty($img_array)): ?>
                    <h5 class="text-center mb-3">🖼️ รูปภาพป้ายไวนิลที่บันทึก (Click ที่รูปเพื่อขยาย)</h5>
                    <div class="row text-center">
                        <?php foreach ($img_array as $image):
                            $image = trim($image);
                            if (!empty($image)): ?>
                                <div class="col-md-4 mb-3">
                                    <div class="card">
                                        <a href="uploads/<?= htmlspecialchars($image) ?>" target="_blank">
                                            <img src="uploads/<?= htmlspecialchars($image) ?>" class="card-img-top img-fluid" alt="รูปภาพ">
                                        </a>
                                    </div>
                                </div>
                            <?php endif;
                        endforeach; ?>
                    </div>
                <?php endif;
            endforeach; ?>

            <?php
            $all_images = ['images3' => $images3, 'images4' => $images4, 'images5' => $images5];
            foreach ($all_images as $key => $img_array):
                if (!empty($img_array)): ?>
                    <h5 class="text-center mb-3">🖼️ รูปภาพป้ายไวนิลที่บันทึก (Click ที่รูปเพื่อขยาย)</h5>
                    <div class="row text-center">
                        <?php foreach ($img_array as $image):
                            $image = trim($image);
                            if (!empty($image)): ?>
                                <div class="col-md-4 mb-3">
                                    <div class="card">
                                        <a href="uploads/<?= htmlspecialchars($image) ?>" target="_blank">
                                            <img src="uploads/<?= htmlspecialchars($image) ?>" class="card-img-top img-fluid" alt="รูปภาพ">
                                        </a>
                                    </div>
                                </div>
                            <?php endif;
                        endforeach; ?>
                    </div>
                <?php endif;
            endforeach; ?>

            <?php
            $all_images = ['images6' => $images6, 'images7' => $images7, 'images8' => $images8];
            foreach ($all_images as $key => $img_array):
                if (!empty($img_array)): ?>
                    <h5 class="text-center mb-3">🖼️ รูปภาพป้ายไวนิลที่บันทึก (Click ที่รูปเพื่อขยาย)</h5>
                    <div class="row text-center">
                        <?php foreach ($img_array as $image):
                            $image = trim($image);
                            if (!empty($image)): ?>
                                <div class="col-md-4 mb-3">
                                    <div class="card">
                                        <a href="uploads/<?= htmlspecialchars($image) ?>" target="_blank">
                                            <img src="uploads/<?= htmlspecialchars($image) ?>" class="card-img-top img-fluid" alt="รูปภาพ">
                                        </a>
                                    </div>
                                </div>
                            <?php endif;
                        endforeach; ?>
                    </div>
                <?php endif;
            endforeach; ?>

            <?php if (!empty($images2)): ?>
                <h5 class="text-center mb-3">🖼️ รูปภาพเลขหลังป้ายไวนิลที่บันทึก (Click ที่รูปเพื่อขยาย)</h5>
                <div class="row text-center">
                    <?php foreach ($images2 as $image2): ?>
                        <?php $image2 = trim($image2); ?>
                        <?php if (!empty($image2)): ?>
                            <div class="col-md-4 mb-3">
                                <div class="card">
                                    <a href="uploads/<?= htmlspecialchars($image2) ?>" target="_blank">
                                        <img src="uploads/<?= htmlspecialchars($image2) ?>" class="card-img-top img-fluid" alt="รูปภาพ">
                                    </a>
                                </div>
                            </div>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

            <div class="col-md-12">
                <div class="form-group">
                    <button type="button" name="closeBtn" id="closeBtn" class="form-control btn btn-danger">
                        <span><i class="fa fa-reply" aria-hidden="true"></i> ปิด</span>
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    $(document).ready(function () {
        $("#closeBtn").click(function () {
            window.close(); // ปิดหน้าต่างปัจจุบัน
        });
    });
</script>

</body>
</html>
