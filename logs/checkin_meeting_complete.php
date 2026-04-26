<?php
// checkin_meeting_complete.php

$fullname = isset($_GET['name']) ? htmlspecialchars($_GET['name']) : 'ผู้ใช้งาน';
$point = isset($_GET['point']) ? htmlspecialchars($_GET['point']) : '';
$status = isset($_GET['status']) ? $_GET['status'] : 'success';

// กำหนดข้อความและสีตามสถานะ
if ($status == 'duplicate') {
    // กรณีซ้ำ
    $title_text = "คุณลงทะเบียนเรียบร้อยแล้ว";
    $desc_text = "ระบบตรวจสอบพบว่าข้อมูลของท่าน (บ้านเลขที่/เบอร์โทร) ได้ลงทะเบียนในวันนี้ไปแล้ว";
    $icon_class = "bi-exclamation-circle-fill";
    $text_color = "text-warning";
    $icon_color = "#ffc107";
} else {
    // กรณีสำเร็จปกติ
    $title_text = "ลงทะเบียนเรียบร้อยแล้ว";
    $desc_text = "ขอบคุณสำหรับการลงทะเบียน";
    $icon_class = "bi-check-circle-fill";
    $text_color = "text-success";
    $icon_color = "#198754";
}
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>สถานะการลงทะเบียน</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <style>
        .status-icon {
            font-size: 5rem;
            color: <?php echo $icon_color; ?>;
        }
    </style>
</head>
<body class="bg-light d-flex align-items-center justify-content-center" style="min-height: 100vh;">

<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-6 col-lg-5">
            <div class="card shadow-sm text-center p-4">
                <div class="card-body">

                    <div class="mb-4">
                        <img src="img/logo/niti_ps33_header.png" alt="Logo" class="img-fluid" style="max-height: 120px;">
                    </div>

                    <div class="mb-3">
                        <i class="bi <?php echo $icon_class; ?> status-icon"></i>
                    </div>

                    <h2 class="card-title <?php echo $text_color; ?> fw-bold mb-3"><?php echo $title_text; ?></h2>

                    <p class="card-text fs-5">
                        เรียน คุณ <strong><?php echo $fullname; ?></strong><br>
                        <span class="text-muted fs-6"><?php echo $desc_text; ?></span>
                    </p>

                    <?php if(!empty($point)): ?>
                        <p class="text-muted small">จุดพื้นที่: <?php echo $point; ?></p>
                    <?php endif; ?>

                    <div class="mt-4 d-grid">
                        <a href="checkin_meeting_register.php?point=<?php echo urlencode($point); ?>" class="btn btn-primary btn-lg">
                            ตกลง / ปิดหน้าต่าง
                        </a>
                    </div>

                </div>
            </div>
        </div>
    </div>
</div>

</body>
</html>