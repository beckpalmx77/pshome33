<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>บันทึกหมายเลขยาง (กล้อง)</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <script src="https://unpkg.com/html5-qrcode" type="text/javascript"></script>

    <style>
        /* จัดขนาดกล้อง */
        #reader {
            width: 100%;
            max-width: 400px;
            margin: 0 auto;
            border: 2px solid #0d6efd;
            border-radius: 8px;
        }

        /* ไฮไลท์ช่องที่กำลังรอสแกน */
        .tyre-serial:focus {
            background-color: #d1e7dd; /* สีเขียวอ่อน */
            border-color: #198754;
            box-shadow: 0 0 0 0.25rem rgba(25, 135, 84, 0.25);
        }
    </style>
</head>
<body class="bg-light">

<div class="container-fluid mt-3">

    <div class="row">
        <div class="col-md-4 text-center mb-3">
            <div class="card shadow-sm sticky-top" style="top: 20px; z-index: 100;">
                <div class="card-header bg-dark text-white">
                    <i class="fas fa-camera"></i> กล้องสแกน
                </div>
                <div class="card-body p-2">
                    <div id="reader"></div>
                    <div class="mt-2 text-muted small">
                        *คลิกที่ช่อง "เบอร์ยาง" แล้วส่องกล้องได้เลย
                    </div>
                </div>
            </div>
            <audio id="beepSound" src="https://www.soundjay.com/button/beep-07.wav"></audio>
        </div>

        <div class="col-md-8">
            <div class="card shadow-sm">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0"><i class="fas fa-list"></i> รายการที่ต้องระบุ Serial</h5>
                </div>
                <div class="card-body">
                    <form id="serialForm">
                        <div class="table-responsive">
                            <table class="table table-bordered table-hover align-middle">
                                <thead class="table-light text-center">
                                <tr>
                                    <th>สินค้า</th>
                                    <th>ยี่ห้อ/รุ่น</th>
                                    <th style="width: 200px;">เบอร์ยาง (Serial)</th>
                                </tr>
                                </thead>
                                <tbody>
                                <?php
                                // จำลองข้อมูล 5 รายการ
                                for($i=1; $i<=5; $i++){
                                    ?>
                                    <tr>
                                        <td>
                                            ยางรถบรรทุก เส้นที่ <?php echo $i; ?><br>
                                            <small class="text-muted">PO-2025-00<?php echo $i; ?></small>
                                        </td>
                                        <td class="text-center">LINGLONG<br>KTA303</td>
                                        <td>
                                            <input type="text"
                                                   class="form-control tyre-serial text-center fw-bold"
                                                   name="serial[]"
                                                   placeholder="คลิกเพื่อสแกน"
                                                   autocomplete="off">
                                        </td>
                                    </tr>
                                <?php } ?>
                                </tbody>
                            </table>
                        </div>
                        <button type="submit" class="btn btn-success w-100 mt-3"><i class="fas fa-save"></i> บันทึกข้อมูล</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

</div>

<script>
    // ตัวแปรเช็คว่ากำลังประมวลผลหรือไม่ (กันสแกนรัว)
    let isProcessing = false;

    // ฟังก์ชันเมื่อกล้องสแกนเจอ Barcode
    function onScanSuccess(decodedText, decodedResult) {
        if (isProcessing) return; // ถ้ากำลังทำงานอยู่ ให้รอแป๊บนึง

        // 1. หาช่อง Input ที่กำลังถูกเลือก (Focus) อยู่
        let activeElement = document.activeElement;

        // 2. ตรวจสอบว่าเป็นช่อง 'tyre-serial' ใช่หรือไม่
        if (activeElement && activeElement.classList.contains('tyre-serial')) {

            isProcessing = true; // ล็อกไว้ก่อน

            // ใส่ค่าลงในช่อง
            activeElement.value = decodedText;

            // เล่นเสียง Beep
            document.getElementById('beepSound').play().catch(e=>{});

            // เอฟเฟกต์กระพริบช่องนิดหน่อย
            activeElement.style.backgroundColor = "#ffc107"; // สีเหลืองชั่วคราว
            setTimeout(() => { activeElement.style.backgroundColor = ""; }, 300);

            // --- Logic ย้ายไปช่องถัดไป ---
            // ดึงรายการช่องทั้งหมดมา
            const allInputs = Array.from(document.querySelectorAll('.tyre-serial'));
            // หาตำแหน่งของช่องปัจจุบัน
            const currentIndex = allInputs.indexOf(activeElement);
            // หาช่องถัดไป
            const nextInput = allInputs[currentIndex + 1];

            if (nextInput) {
                // ย้าย Focus ไปช่องถัดไป
                setTimeout(() => {
                    nextInput.focus();
                    isProcessing = false; // ปลดล็อก พร้อมสแกนต่อ
                }, 500); // หน่วงเวลา 0.5 วิ ให้ user เห็นว่าค่าลงแล้ว
            } else {
                alert("ครบทุกรายการแล้ว!");
                isProcessing = false;
            }

        } else {
            // กรณีสแกนแต่ไม่ได้คลิกช่องไหนไว้เลย
            // อาจจะแจ้งเตือน หรือ ไม่ทำอะไรก็ได้
            console.log("กรุณาคลิกเลือกช่องที่จะใส่ข้อมูลก่อนสแกน");
        }
    }

    // เริ่มต้นกล้อง
    let html5QrcodeScanner = new Html5QrcodeScanner(
        "reader",
        {
            fps: 10,
            qrbox: 250
        },
        false);

    html5QrcodeScanner.render(onScanSuccess);

    // (Optional) ตั้ง Focus ช่องแรกให้อัตโนมัติเมื่อเปิดเว็บ
    window.onload = function() {
        const firstInput = document.querySelector('.tyre-serial');
        if(firstInput) firstInput.focus();
    };
</script>

</body>
</html>