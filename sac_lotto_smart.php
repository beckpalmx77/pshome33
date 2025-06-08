<?php

include 'config/connect_lotto_db.php';

try {
    // Query to get lotto_name
    $stmt = $conn->prepare("SELECT lotto_name FROM ims_lotto");
    $stmt->execute();

    // Fetch all the results into an associative array
    $lottoNames = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch(PDOException $e) {
    echo "Connection failed: " . $e->getMessage();
}

?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <meta name="description" content="ระบบจัดการข้อมูลรถยนต์ สงวนออโต้คาร์">
    <meta name="author" content="Sanguan Auto Car">
    <link rel="icon" href="img/favicon.ico" type="image/x-icon">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css">
    <title>สงวนออโต้คาร์ | SANGUAN AUTO CAR</title>
</head>
<body>
<div class="container mt-5">
    <h3>ฟอร์มลงทะเบียน</h3>
    <form id="registerForm" method="POST" enctype="multipart/form-data">
        <div class="mb-3">
            <label for="lotto_name" class="form-label">ชื่อร้านค้า</label>
            <select class="form-select" id="lotto_name" name="lotto_name" required>
                <option value="">เลือกชื่อร้านค้า</option>
                <?php foreach ($lottoNames as $lotto): ?>
                    <option value="<?= htmlspecialchars($lotto['lotto_name']); ?>"><?= htmlspecialchars($lotto['lotto_name']); ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="mb-3">
            <label for="lotto_phone" class="form-label">เบอร์โทร</label>
            <input type="text" class="form-control" id="lotto_phone" name="lotto_phone" required>
        </div>
        <div class="mb-3">
            <label for="lotto_province" class="form-label">จังหวัด</label>
            <input type="text" class="form-control" id="lotto_province" name="lotto_province" required>
        </div>
        <div class="mb-3">
            <label for="lotto_number" class="form-label">หมายเลข</label>
            <input type="text" class="form-control" id="lotto_number" name="lotto_number" required>
        </div>
        <div class="mb-3">
            <label for="sale_name" class="form-label">ชื่อผู้ขาย</label>
            <input type="text" class="form-control" id="sale_name" name="sale_name">
        </div>
        <div class="mb-3">
            <label for="lotto_file" class="form-label">อัปโหลดไฟล์หลัก</label>
            <input type="file" class="form-control" id="lotto_file" name="lotto_file" accept="image/*" onchange="previewImage(this, 'preview_lotto_file')">
            <div class="mt-2">
                <img id="preview_lotto_file" src="" alt="Preview Image" class="img-fluid" style="display: none;">
            </div>
        </div>
        <div class="mb-3">
            <label for="lotto_file1" class="form-label">อัปโหลดไฟล์ที่ 1</label>
            <input type="file" class="form-control" id="lotto_file1" name="lotto_file1" accept="image/*" onchange="previewImage(this, 'preview_lotto_file1')">
            <div class="mt-2">
                <img id="preview_lotto_file1" src="" alt="Preview Image" class="img-fluid" style="display: none;">
            </div>
        </div>
        <div class="mb-3">
            <label for="lotto_file2" class="form-label">อัปโหลดไฟล์ที่ 2</label>
            <input type="file" class="form-control" id="lotto_file2" name="lotto_file2" accept="image/*" onchange="previewImage(this, 'preview_lotto_file2')">
            <div class="mt-2">
                <img id="preview_lotto_file2" src="" alt="Preview Image" class="img-fluid" style="display: none;">
            </div>
        </div>
        <div class="mb-3">
            <label for="lotto_file3" class="form-label">อัปโหลดไฟล์ที่ 3</label>
            <input type="file" class="form-control" id="lotto_file3" name="lotto_file3" accept="image/*" onchange="previewImage(this, 'preview_lotto_file3')">
            <div class="mt-2">
                <img id="preview_lotto_file3" src="" alt="Preview Image" class="img-fluid" style="display: none;">
            </div>
        </div>
        <div class="mb-3">
            <label for="lotto_file4" class="form-label">อัปโหลดไฟล์ที่ 4</label>
            <input type="file" class="form-control" id="lotto_file4" name="lotto_file4" accept="image/*" onchange="previewImage(this, 'preview_lotto_file4')">
            <div class="mt-2">
                <img id="preview_lotto_file4" src="" alt="Preview Image" class="img-fluid" style="display: none;">
            </div>
        </div>
        <div class="mb-3">
            <label for="lotto_file5" class="form-label">อัปโหลดไฟล์ที่ 5</label>
            <input type="file" class="form-control" id="lotto_file5" name="lotto_file5" accept="image/*" onchange="previewImage(this, 'preview_lotto_file5')">
            <div class="mt-2">
                <img id="preview_lotto_file5" src="" alt="Preview Image" class="img-fluid" style="display: none;">
            </div>
        </div>
        <button type="submit" class="btn btn-primary">ลงทะเบียน</button>
    </form>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
    function previewImage(input, previewId) {
        const file = input.files[0];
        const reader = new FileReader();
        reader.onload = function (e) {
            const imgElement = document.getElementById(previewId);
            imgElement.src = e.target.result;
            imgElement.style.display = 'block';
        };
        if (file) {
            reader.readAsDataURL(file);
        }
    }
</script>



</body>
</html>
