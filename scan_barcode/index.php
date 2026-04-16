<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Scan to Textbox</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://unpkg.com/html5-qrcode" type="text/javascript"></script>

    <style>
        #reader {
            width: 100%;
            max-width: 400px;
            margin: 0 auto;
            border: 2px solid #0d6efd;
            border-radius: 10px;
        }
    </style>
</head>
<body class="bg-light">

<div class="container mt-5">
    <div class="card shadow">
        <div class="card-header bg-primary text-white text-center">
            <h4>สแกน Barcode ลง Textbox</h4>
        </div>
        <div class="card-body text-center">

            <div id="reader" class="mb-4"></div>

            <form>
                <div class="mb-3">
                    <label class="form-label fw-bold">รหัสที่สแกนได้:</label>
                    <input type="text" id="barcode_input" class="form-control form-control-lg text-center"
                           placeholder="ค่าจะปรากฏที่นี่..." autofocus>
                </div>

                <div class="d-grid gap-2">
                    <button type="button" class="btn btn-warning" onclick="clearInput()">ล้างค่า</button>
                </div>
            </form>

        </div>
    </div>
</div>

<audio id="beepSound" src="https://www.soundjay.com/button/beep-07.wav"></audio>

<script>
    let isScanning = true;

    function onScanSuccess(decodedText, decodedResult) {
        // เมื่อสแกนติด

        // 1. นำค่าใส่ลงใน Textbox
        let inputField = document.getElementById('barcode_input');

        // ถ้าต้องการให้ค่าเปลี่ยนทันที (Replace)
        inputField.value = decodedText;

        // 2. เล่นเสียงแจ้งเตือน
        document.getElementById('beepSound').play().catch(e=>{});

        // 3. (Optional) Effect ให้ช่อง input กระพริบเพื่อให้รู้ว่าค่าเข้าแล้ว
        inputField.style.backgroundColor = "#d1e7dd"; // สีเขียวอ่อน
        setTimeout(() => {
            inputField.style.backgroundColor = ""; // คืนค่าสีเดิม
        }, 500);

        // 4. (ตัวเลือก) หยุดกล้องหลังจากสแกนได้แล้วหรือไม่?
        // html5QrcodeScanner.clear(); // ถ้าอยากให้สแกนครั้งเดียวแล้วปิดกล้อง ให้เอา Comment ออก
    }

    function clearInput() {
        document.getElementById('barcode_input').value = "";
        document.getElementById('barcode_input').focus();
    }

    // ตั้งค่ากล้อง
    let html5QrcodeScanner = new Html5QrcodeScanner(
        "reader",
        {
            fps: 10,    // ความเร็วการสแกน
            qrbox: 250  // ขนาดกรอบเล็ง
        },
        /* verbose= */ false
    );

    html5QrcodeScanner.render(onScanSuccess);

</script>

</body>
</html>