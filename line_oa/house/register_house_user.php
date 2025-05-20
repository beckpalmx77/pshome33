<?php include('../../config/connect_db.php');
    header('Content-Type: text/html; charset=utf-8');
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <title>PS33</title>
    <link rel="icon" href="img/favicon.ico" type="image/x-icon"/>

    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet"/>

    <!-- Select2 -->
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet"/>

    <!-- LIFF SDK -->
    <script src="https://static.line-scdn.net/liff/edge/2/sdk.js"></script>
</head>
<body class="bg-light">

<div class="container mt-5 d-flex justify-content-center align-items-center min-vh-100">
    <div class="card shadow-lg p-4 text-center" style="width: 100%; max-width: 500px;">
        <div class="mb-4">
            <img src="ps33_logo.png" width="70" height="117" alt="Logo" />
        </div>
        <h4 class="mb-4">ลงทะเบียนระบบ PS33</h4>

        <form id="registerForm">
            <div class="mb-3 text-center">
                <img id="profilePic" src="" class="rounded-circle" width="100" alt="Profile Pic">
            </div>

            <input type="hidden" id="lineUserId" name="lineUserId">
            <input type="hidden" id="picture" name="picture">
            <input type="hidden" id="statusMessage" name="statusMessage">

            <div class="mb-3 text-start">
                <label class="form-label">ชื่อ User Line:</label>
                <input type="text" id="name" name="name" class="form-control" readonly>
            </div>

            <div class="mb-3 text-start">
                <label class="form-label">เลขที่บ้าน: (ใส่เฉพาะเลขที่บ้าน รูปแบบเช่น 99/99)</label>
                <input type="text" id="house_number" name="house_number" class="form-control" required>
            </div>

            <div class="mb-3 text-start">
                <label class="form-label">หมายเลขซอย: </label>
                <input type="text" id="alley" name="alley" class="form-control" required>
            </div>

            <div class="mb-3 text-start">
                <label class="form-label">ชื่อ:</label>
                <input type="text" id="f_name" name="f_name" class="form-control" required>
            </div>

            <div class="mb-3 text-start">
                <label class="form-label">นามสกุล:</label>
                <input type="text" id="l_name" name="l_name" class="form-control" required>
            </div>

            <div class="mb-3 text-start">
                <label class="form-label">เบอร์โทร: (ใช้เป็น user name เข้าระบบ)</label>
                <input type="tel" id="phone" name="phone" class="form-control" placeholder="" required>
            </div>

            <div class="mb-3 text-start">
                <label class="form-label">รหัสผ่าน:</label>
                <input type="password" id="password" name="password" class="form-control" placeholder="" required>
            </div>

            <button type="submit" class="btn btn-success w-100">ลงทะเบียน</button>
        </form>
    </div>
</div>

<!-- Scripts -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script src="jsconfig/config_house_register.js"></script>

<script>
    $(document).ready(function () {
        liff.init({ liffId: LIFF_ID }).then(() => {
            if (liff.isLoggedIn()) {
                liff.getProfile().then(profile => {
                    $('#lineUserId').val(profile.userId);
                    $('#name').val(profile.displayName);
                    $('#profilePic').attr('src', profile.pictureUrl || "../img/user-001.png");
                    $('#picture').val(profile.pictureUrl || "");
                    $('#statusMessage').val(profile.statusMessage || "ไม่มีข้อความสถานะ");
                });
            } else {
                liff.login();
            }
        }).catch(err => console.error("LIFF Error:", err));

        $('#registerForm').on('submit', function (e) {
            e.preventDefault();
            let formData = new FormData(this);

            fetch("https://ps33.themediathai.com/line_oa/house/register_house_with_line_api.php", {
                method: "POST",
                body: formData
            })
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        if (liff.isInClient()) {
                            liff.sendMessages([{
                                type: "text",
                                text: `✅ ลงทะเบียนสำเร็จ!\n👤 ${formData.get("f_name")}\n🏢 ${formData.get("l_name")}\n📞 ${formData.get("phone")}`
                            }]).then(() => {
                                alert("✅ ลงทะเบียนสำเร็จ!");
                                liff.closeWindow();
                            }).catch(err => {
                                alert("ลงทะเบียนสำเร็จ แต่ส่งข้อความไม่สำเร็จ");
                                liff.closeWindow();
                            });
                        } else {
                            alert("✅ ลงทะเบียนสำเร็จ! (ไม่ได้เปิดในแอป LINE)");
                            liff.closeWindow();
                        }
                    } else {
                        alert("❌ ลงทะเบียนไม่สำเร็จ: " + data.message);
                    }
                })
                .catch(err => {
                    console.error("AJAX Error:", err);
                    alert("❌ เกิดข้อผิดพลาดในการเชื่อมต่อ");
                });
        });
    });
</script>

<script>
    document.getElementById('phone').addEventListener('input', function () {
        const phonePattern = /^0[689]\d{8}$/;
        const phoneInput = this;
        if (!phonePattern.test(phoneInput.value)) {
            phoneInput.setCustomValidity('กรุณากรอกเบอร์โทรให้ถูกต้อง (เช่น 0812345678)');
        } else {
            phoneInput.setCustomValidity('');
        }
    });
</script>

</body>
</html>

