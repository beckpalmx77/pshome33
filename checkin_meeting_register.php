<?php
// checkin_meeting_register.php

// รับค่าจุดเช็คอินจาก URL เช่น checkin_meeting_register.php?point=RoomA
$point = isset($_GET['point']) ? $_GET['point'] : 'General';
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ลงทะเบียนเข้าร่วมประชุม</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

<div class="container mt-4">
    <div class="card shadow-sm">
        <div class="card-header bg-primary text-white text-center">
            <h5>📝 ลงทะเบียนเข้าร่วมประชุม</h5>
            <!--small>จุดลงทะเบียน: <strong><?php echo htmlspecialchars($point); ?></strong></small-->
        </div>
        <div class="card-body">

            <div class="text-center mb-3">
                <img src="img/logo/niti_ps33_header.png" alt="Company Logo" class="img-fluid" style="max-height: 70px;">
            </div>
            <div id="gps-status" class="alert alert-warning text-center p-2" style="font-size: 0.9rem;">
                กำลังดึงพิกัด GPS... 🛰️ <br>
                (กรุณากด Allow/อนุญาต)
            </div>

            <form action="save_checkin_meeting_data.php" method="POST">

                <input type="hidden" name="checkin_point" value="<?php echo htmlspecialchars($point); ?>">
                <input type="hidden" name="lat_addr" id="lat_addr" value="">
                <input type="hidden" name="long_addr" id="long_addr" value="">

                <div class="mb-3">
                    <label class="form-label">ชื่อ-นามสกุล <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" name="fullname" required placeholder="">
                </div>

                <div class="mb-3">
                    <label class="form-label">บ้านเลขที่</label>
                    <input type="text" class="form-control" name="house_number" placeholder="">
                </div>

                <div class="mb-3">
                    <label class="form-label">เบอร์โทรศัพท์</label>
                    <input type="tel" class="form-control" name="phone_number" placeholder="">
                </div>

                <div class="d-grid gap-2">
                    <button type="submit" id="btnSubmit" class="btn btn-secondary" disabled>
                        รอพิกัด GPS...
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    function getLocation() {
        const statusDiv = document.getElementById('gps-status');

        if (navigator.geolocation) {
            navigator.geolocation.getCurrentPosition(showPosition, showError, {
                enableHighAccuracy: true,
                timeout: 10000,
                maximumAge: 0
            });
        } else {
            statusDiv.innerHTML = "❌ เครื่องนี้ไม่รองรับ GPS";
            statusDiv.className = "alert alert-danger text-center";
        }
    }

    function showPosition(position) {
        document.getElementById('lat_addr').value = position.coords.latitude;
        document.getElementById('long_addr').value = position.coords.longitude;

        const statusDiv = document.getElementById('gps-status');
        statusDiv.innerHTML = "✅ พิกัด: " + position.coords.latitude.toFixed(5) + ", " + position.coords.longitude.toFixed(5);
        statusDiv.className = "alert alert-success text-center p-2";

        const btnSubmit = document.getElementById('btnSubmit');
        btnSubmit.disabled = false;
        btnSubmit.className = "btn btn-success btn-lg";
        btnSubmit.innerText = "บันทึกข้อมูล";
    }

    function showError(error) {
        const statusDiv = document.getElementById('gps-status');
        let msg = "ไม่สามารถดึงพิกัดได้";
        if(error.code == error.PERMISSION_DENIED) msg = "กรุณาอนุญาตการเข้าถึงตำแหน่ง (GPS)";

        statusDiv.innerHTML = "❌ " + msg;
        statusDiv.className = "alert alert-danger text-center p-2";
    }

    window.onload = getLocation;
</script>

</body>
</html>