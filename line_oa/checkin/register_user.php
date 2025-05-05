<?php include('../../config/connect_db.php'); ?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <title>SAC EMPLOYEE SYSTEM</title>
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
            <img src="../img/sac_application.png" style="height: 70px;" alt="SAC Logo">
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
                <label class="form-label">ชื่อ:</label>
                <input type="text" id="name" name="name" class="form-control" readonly>
            </div>

            <div class="mb-3 text-start">
                <label class="form-label">เลือกชื่อพนักงาน:</label>
                <select id="emp_select" class="form-control select2" name="emp_id">
                    <option value="">-- เลือก --</option>
                    <?php
                    $stmt = $conn->prepare("SELECT emp_id, f_name, l_name FROM memployee WHERE status = 'Y'");
                    $stmt->execute();
                    foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $emp) {
                        $fullname = $emp['f_name'] . " " . $emp['l_name'];
                        echo "<option value='{$emp['emp_id']}' data-name='{$fullname}'>{$fullname}</option>";
                    }
                    ?>
                </select>
            </div>

            <div class="mb-3 text-start">
                <label class="form-label">รหัสพนักงาน:</label>
                <input type="text" id="emp_id" class="form-control" readonly>
            </div>

            <div class="mb-3 text-start">
                <label class="form-label">ชื่อพนักงาน:</label>
                <input type="text" id="emp_name" name="emp_name" class="form-control" readonly>
            </div>

            <div class="mb-3 text-start">
                <label class="form-label">เบอร์โทร:</label>
                <input type="tel" id="phone" name="phone" class="form-control" placeholder="กรอกเบอร์โทร">
            </div>

            <button type="submit" class="btn btn-success w-100">ลงทะเบียน</button>
        </form>
    </div>
</div>

<!-- Scripts -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

<script>
    $(document).ready(function () {
        $('#emp_select').select2({ placeholder: "ค้นหารายชื่อพนักงาน", allowClear: true });

        $('#emp_select').on('change', function () {
            let selected = $(this).find('option:selected');
            $('#emp_id').val(selected.val());
            $('#emp_name').val(selected.data('name'));
        });

        liff.init({ liffId: "2007364217-G2VRbv5v" }).then(() => {
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

            fetch("https://ps33.themediathai.com/line_oa/checkin/register_with_line_api.php", {
                method: "POST",
                body: formData
            })
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        if (liff.isInClient()) {
                            liff.sendMessages([{
                                type: "text",
                                text: `✅ ลงทะเบียนสำเร็จ!\n👤 ${formData.get("name")}\n🏢 ${formData.get("emp_name")}\n📞 ${formData.get("phone")}`
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
</body>
</html>
