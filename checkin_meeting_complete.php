<?php
// register_success.php

// รับค่าชื่อและจุดเช็คอินที่ส่งมาจากหน้า save (ถ้าไม่มีให้เป็นค่าว่าง)
// ใช้ htmlspecialchars เพื่อความปลอดภัย (ป้องกัน XSS)
$fullname = isset($_GET['name']) ? htmlspecialchars($_GET['name']) : 'ผู้ใช้งาน';
$point = isset($_GET['point']) ? htmlspecialchars($_GET['point']) : '';
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ลงทะเบียนสำเร็จ</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <style>
        .success-icon {
            font-size: 5rem;
            color: #198754; /* สีเขียว Bootstrap */
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
                        <img src="img/logo/niti_ps33_header.png" alt="Company Logo" class="img-fluid" style="max-height: 120px;">
                    </div>

                    <div class="mb-3">
                        <i class="bi bi-check-circle-fill success-icon"></i>
                    </div>

                    <h2 class="card-title text-success fw-bold mb-3">ลงทะเบียนเรียบร้อยแล้ว</h2>

                    <p class="card-text fs-5">
                        ยินดีต้อนรับคุณ <strong><?php echo $fullname; ?></strong>
                    </p>

                </div>
            </div>
        </div>
    </div>
</div>

</body>
</html>