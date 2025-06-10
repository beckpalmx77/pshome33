<?php
$curr_date = date("d-m-Y");
include('includes/Header.php');
include('config/connect_db.php');

$sql_bank = " SELECT * FROM ims_company ";
$stmt_bank = $conn->prepare($sql_bank);
$stmt_bank->execute();
$BankCurr = $stmt_bank->fetchAll();
foreach ($BankCurr as $row_curr) {
    $bank_name = $row_curr["bank_name"];
    $bank_account_name = $row_curr["bank_account_name"];
    $bank_account_no = $row_curr["bank_account_no"];
}

?>

<!DOCTYPE html>
<html lang="th">

<body id="page-top">
<div id="wrapper">
    <div id="content-wrapper" class="d-flex flex-column">
        <div id="content">
            <div class="container py-4">
                <div class="d-sm-flex align-items-center justify-content-between mb-4">
                    <h1 class="h5 mb-0 text-gray-800">ชำระค่าส่วนกลาง</h1>
                    <br>
                    <!-- โปรไฟล์และข้อมูลผู้ใช้ -->
                    <div class="d-flex align-items-center gap-3">
                        <img id="profilePic"
                             src=""
                             class="rounded-circle"
                             width="50"
                             height="50"
                             alt="Profile Pic"
                             style="margin-right: 1rem;">
                        <div class="text-sm text-muted" id="user-info-liff2"></div>
                    </div>

                </div>

                <div class="row">
                    <div class="col-lg-12">
                        <div class="card mb-12">
                            <!--div class="card-header py-3 d-flex flex-row align-items-center justify-content-between"></div-->
                            <div class="card-body">
                                <form id="transfer_form" method="POST" enctype="multipart/form-data">

                                    <div class="form-group has-success">
                                        <div class="row">
                                            <div class="col-md-2">
                                                <div class="form-group has-success">
                                                    <label for="payment_date"
                                                           class="control-label">วันที่เอกสาร</label>
                                                    <i class="fa fa-calendar"
                                                       aria-hidden="true"></i>
                                                    <input type="text" class="form-control"
                                                           id="payment_date"
                                                           name="payment_date"
                                                           required="required"
                                                           value="<?php echo $curr_date ?>"
                                                           readonly="true"
                                                           placeholder="">
                                                </div>
                                            </div>
                                            <div class="col-md-2">
                                                <div class="form-group has-success">
                                                    <label for="house_number"
                                                           class="control-label">บ้านเลขที่</label>
                                                    <input type="text" name="house_number" class="form-control"
                                                           required
                                                           id="house_number"
                                                           value="<?php echo $house_number ?>">
                                                </div>
                                            </div>
                                            <div class="col-md-2">
                                                <div class="form-group has-success">
                                                    <label class="control-label">ตัวเลือกการชำระ</label>
                                                    <div class="form-check">
                                                        <input class="form-check-input" type="radio"
                                                               name="payment_option" id="option_yearly"
                                                               value="yearly">
                                                        <label class="form-check-label"
                                                               for="option_yearly">ชำระรายปี</label>
                                                    </div>
                                                    <div class="form-check">
                                                        <input class="form-check-input" type="radio"
                                                               name="payment_option" id="option_monthly"
                                                               value="monthly" checked>
                                                        <label class="form-check-label" for="option_monthly">ชำระรายเดือน</label>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-md-2">
                                                <div class="form-group has-success">
                                                    <label for="payment_type"
                                                           class="control-label">จำนวนเดือนที่ชำระ</label>
                                                    <input type="number" name="payment_type" class="form-control"
                                                           required
                                                           id="payment_type"
                                                           value="1">
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- งวดเดือนและงวดปี (แถวเดียว กระชับ) -->
                                    <div class="form-group row align-items-end">
                                        <div class="col-md-4">
                                            <label for="period_month_start">เริ่มงวดเดือน</label>
                                            <select name="period_month_start" id="period_month_start"
                                                    class="form-control" required>
                                                <option value="">เลือก</option>
                                                <?php
                                                $months = [
                                                    1 => 'มกราคม', 2 => 'กุมภาพันธ์', 3 => 'มีนาคม', 4 => 'เมษายน',
                                                    5 => 'พฤษภาคม', 6 => 'มิถุนายน', 7 => 'กรกฎาคม', 8 => 'สิงหาคม',
                                                    9 => 'กันยายน', 10 => 'ตุลาคม', 11 => 'พฤศจิกายน', 12 => 'ธันวาคม'
                                                ];
                                                foreach ($months as $val => $name) {
                                                    echo "<option value='$val'>$name</option>";
                                                }
                                                ?>
                                            </select>
                                        </div>

                                        <div class="col-md-4">
                                            <label for="period_month_to">ถึงงวดเดือน</label>
                                            <select name="period_month_to" id="period_month_to" class="form-control"
                                                    required>
                                                <option value="">เลือก</option>
                                                <?php
                                                foreach ($months as $val => $name) {
                                                    echo "<option value='$val'>$name</option>";
                                                }
                                                ?>
                                            </select>
                                        </div>

                                        <div class="col-md-4">
                                            <label for="period_year">งวดปี</label>
                                            <input type="number" name="period_year" id="period_year"
                                                   class="form-control" required
                                                   value="<?php echo date('Y'); ?>">
                                        </div>
                                    </div>

                                    <div class="form-group has-success">
                                        <div class="row">
                                            <!-- งวดปี -->
                                            <div class="col-md-6">
                                                <!-- ชื่อผู้โอน -->
                                                <div class="form-group has-success">
                                                    <label for="detail" class="control-label">ชื่อผู้ชำระ</label>
                                                    <input type="text" name="detail" class="form-control" required
                                                           id="detail">
                                                    <input type="hidden" id="line_user_id" name="line_user_id"
                                                           readonly="true">
                                                    <input type="hidden" id="displayName" name="displayName"
                                                           readonly="true">
                                                    <input type="hidden" id="pictureUrl" name="pictureUrl"
                                                           readonly="true">
                                                </div>
                                            </div>

                                            <div class="col-md-6">
                                                <!-- จำนวนเงินที่โอน -->
                                                <div class="form-group has-success">
                                                    <label for="common_fee"
                                                           class="control-label">ค่าส่วนกลางรายเดือน (บาท)</label>
                                                    <input type="number" name="common_fee" class="form-control"
                                                           id="common_fee" readonly="true">
                                                </div>
                                            </div>


                                            <div class="col-md-6">
                                                <!-- จำนวนเงินที่โอน -->
                                                <div class="form-group has-success">
                                                    <label for="amount"
                                                           class="control-label">จำนวนเงินที่ชำระ (บาท)</label>
                                                    <input type="number" name="amount" class="form-control"
                                                           required id="amount">
                                                </div>
                                            </div>
                                        </div>
                                    </div>


                                    <!-- หมายเหตุ -->
                                    <div class="form-group has-success">
                                        <label for="remark" class="control-label">หมายเหตุ</label>
                                        <input name="remark" class="form-control" id="remark" value="-">
                                    </div>

                                    <div class="form-group has-success">
                                        <label for="bank_transfer" class="control-label">โอนเงินเข้าบัญชี</label>
                                        <input name="bank_name" class="form-control" id="bank_name"
                                               value="<?php echo $bank_name ?>" readonly="true">
                                        <input name="bank_account_name" class="form-control" id="bank_account_name"
                                               value="<?php echo $bank_account_name ?>" readonly="true">
                                        <input name="bank_account_no" class="form-control" id="bank_account_no"
                                               value="<?php echo $bank_account_no ?>" readonly="true">
                                    </div>

                                    <div class="mb-3">
                                        <label for="picture_payment">แนบ Slip/ใบโอนเงิน/ใบเสร็จ</label>
                                        <input type="file" name="picture_payment" class="form-control"
                                               id="picture_payment" required>
                                        <div class="mt-2">
                                            <img id="preview_image" alt="Preview Slip" class="img-fluid">
                                        </div>
                                    </div>

                                    <!-- Loading Indicator -->
                                    <div id="loading"
                                         style="display: none; text-align: center; margin-top: 20px;">
                                        <img src="img/spin/spin_cir.gif" alt="Loading..." style="width: 50px;">
                                        <p>กำลังบันทึกข้อมูล...</p>
                                    </div>

                                    <button type="submit" id="submit_btn" class="btn btn-primary btn-block">บันทึกข้อมูล
                                    </button>
                                </form>

                                <div id="result"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <!-- Container Fluid-->

        </div>

        <?php
        include('includes/Modal-Logout.php');
        include('includes/Footer.php');
        ?>

    </div>
</div>

<!-- Scroll to top -->
<a class="scroll-to-top rounded" href="#page-top">
    <i class="fas fa-angle-up"></i>
</a>

<script src="vendor/jquery/jquery.min.js"></script>
<script src="vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
<script src="vendor/jquery-easing/jquery.easing.min.js"></script>
<script src="vendor/select2/dist/js/select2.min.js"></script>
<script src="vendor/bootstrap-datepicker/js/bootstrap-datepicker.min.js"></script>
<script src="vendor/bootstrap-touchspin/js/jquery.bootstrap-touchspin.js"></script>
<script src="vendor/clock-picker/clockpicker.js"></script>
<script src="js/myadmin.min.js"></script>
<script src="js/modal/show_department_modal.js"></script>
<script src="js/MyFrameWork/framework_util.js"></script>
<script src="https://static.line-scdn.net/liff/edge/2/sdk.js"></script>
<script src="line_oa/house/jsconfig/config_house_payment.js"></script>
<script src="js/util/month_name.js"></script>

<link href="https://fonts.googleapis.com/css2?family=Kanit&display=swap" rel="stylesheet">

<style>
    body {
        background: url('img/mint-bg.jpg') no-repeat center center fixed;
        background-size: cover;
        font-family: 'Prompt', sans-serif;
        color: #2f4f4f;
    }

    .container-fluid {
        background-color: rgba(230, 255, 240, 0.95); /* soft green with transparency */
        padding: 1rem;
        margin: 0.5rem;
        border-radius: 16px;
        box-shadow: 0 4px 12px rgba(144, 238, 144, 0.3); /* light green shadow */
    }

    .card {
        border: none;
        border-radius: 12px;
        background-color: #eafff0; /* very light mint green */
        box-shadow: 0 0 10px rgba(152, 251, 152, 0.3);
        margin-bottom: 1rem;
    }

    label, .form-check-label {
        font-weight: 500;
        color: #2e7d32; /* darker green for contrast */
    }

    .form-control {
        border-radius: 10px;
        border: 1px solid #a8d5ba; /* light sage green */
        padding: 0.5rem 0.75rem;
        background-color: #ffffff;
        box-shadow: inset 0 1px 3px rgba(0, 0, 0, 0.05);
    }

    .btn-primary {
        background-color: #90ee90; /* light green */
        border-color: #90ee90;
        color: #2e7d32;
        font-weight: bold;
        border-radius: 10px;
    }

    .btn-primary:hover {
        background-color: #7cd67c;
        border-color: #7cd67c;
    }

    #preview_image {
        display: none;
        margin-top: 10px;
        max-width: 100%;
        height: auto;
        border: 1px solid #ccc;
        border-radius: 8px;
        box-shadow: 0 2px 6px rgba(0, 0, 0, 0.1);
    }

    @media (max-width: 768px) {
        .profile-header {
            flex-direction: column;
            align-items: flex-start;
            gap: 1rem;
        }

        .profile-info {
            flex-direction: row;
            justify-content: flex-start;
        }
    }
</style>

<style>
    html, body {
        height: 100%;
        margin: 0;
    }

    #wrapper {
        min-height: 100vh;
        display: flex;
        justify-content: center;
        align-items: center;
    }
</style>

<script>
    function cleanHouseNumber(value) {
        // ลบช่องว่างทั้งหมด และอักขระที่ไม่ใช่ตัวเลขหรือ /
        return value.replace(/\s+/g, '').replace(/[^0-9\/]/g, '');
    }

    const houseNumberInput = document.getElementById("house_number");

    // กรองตอนพิมพ์
    houseNumberInput.addEventListener("input", function () {
        this.value = cleanHouseNumber(this.value);
    });

    // ตรวจสอบอีกครั้งเมื่อเปลี่ยนค่า (เช่น copy/paste แล้วคลิกออก)
    houseNumberInput.addEventListener("change", function () {
        this.value = cleanHouseNumber(this.value);
    });

    // ตรวจสอบอีกครั้งเมื่อ focus หลุด (leave field)
    houseNumberInput.addEventListener("blur", function () {
        this.value = cleanHouseNumber(this.value);
    });
</script>

<script>
    $(document).ready(function () {
        function toggleFields() {
            if ($("#option_monthly").is(":checked")) {
                $("#period_month_start").prop("disabled", false).val(1); // กำหนดเป็น มกราคม
                $("#period_month_to").prop("disabled", false).val(1); // กำหนดเป็น มกราคม
                $("#period_year").prop("disabled", false);
                $("#payment_type").prop("disabled", false);
                $("#payment_type").val(1);
            } else if ($("#option_yearly").is(":checked")) {
                $("#period_month_start").prop("disabled", true).val(1); // กำหนดเป็น มกราคม
                $("#period_month_to").prop("disabled", true).val(12);   // กำหนดเป็น ธันวาคม
                $("#period_year").prop("disabled", false);
                $("#payment_type").prop("disabled", true);
                $("#period_month_start").val(1); // กำหนดเป็น มกราคม
                $("#period_month_to").val(12); // กำหนดเป็น ธันวาคม
                $("#payment_type").val(12);
            }
        }

        // Initial toggle state
        toggleFields();

        // Update on radio button change
        $("input[name='payment_option']").on("change", toggleFields);
    });
</script>

<script>
    $(document).ready(function () {
        function updatePeriodMonthTo() {
            if ($("#option_monthly").is(":checked")) {
                const startMonth = parseInt($("#period_month_start").val());
                const paymentMonths = parseInt($("#payment_type").val());

                if (!isNaN(startMonth) && !isNaN(paymentMonths)) {
                    let endMonth = startMonth + paymentMonths - 1;
                    if (endMonth > 12) {
                        endMonth = endMonth % 12;
                        if (endMonth === 0) endMonth = 12;
                    }
                    $("#period_month_to").val(endMonth);
                }
            }
        }

        $("#payment_type, #period_month_start").on("input change", updatePeriodMonthTo);
    });

</script>

<script src="https://static.line-scdn.net/liff/edge/2/sdk.js"></script>

<script>
    liff.init({liffId: LIFF_ID})
        .then(() => {
            if (!liff.isLoggedIn()) {
                liff.login();
            } else {
                liff.getProfile().then(profile => {
                    const userId = profile.userId;
                    const pictureUrl = profile.pictureUrl;
                    const displayName = profile.displayName;

                    fetch('model/save_user_profile_payment.php', {
                        method: 'POST',
                        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
                        body: `userId=${encodeURIComponent(userId)}&pictureUrl=${encodeURIComponent(pictureUrl)}&displayName=${encodeURIComponent(displayName)}`
                    });

                    fetch('model/get_house_number_smart.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded'
                        },
                        body: 'userId=' + encodeURIComponent(userId)
                    })
                        .then(response => response.json())
                        .then(data => {
                            if (data.house_number) {
                                document.getElementById('line_user_id').value = userId;
                                document.getElementById('pictureUrl').value = pictureUrl;
                                document.getElementById('displayName').value = displayName;
                                document.getElementById('house_number').value = data.house_number || '';
                                document.getElementById('detail').value = (data.f_name || '') + ' ' + (data.l_name || '');
                                document.getElementById('common_fee').value = data.common_fee;
                                document.getElementById('amount').value = data.common_fee * document.getElementById('payment_type').value;
                                //document.getElementById('user-info-liff1').innerText = `บ้านเลขที่: ${data.house_number}`;
                                document.getElementById('user-info-liff2').innerText = `ชื่อ : ${data.f_name} ${data.l_name}`;
                                $('#profilePic').attr('src', profile.pictureUrl || "../img/user-001.png");


                            } else {
                                alert('ไม่พบผู้ใช้งานในระบบ กรุณาลงทะเบียนก่อน');
                                liff.closeWindow(); // กลับไปที่ LINE OA
                            }
                        })
                        .catch(error => {
                            console.error('เกิดข้อผิดพลาด:', error);
                            alert('เกิดข้อผิดพลาดในการเชื่อมต่อกับเซิร์ฟเวอร์');
                            liff.closeWindow(); // ปิดหน้าจอเมื่อ error ก็ได้
                        });
                });
            }
        });
</script>

<script>
    $("#picture_payment").on("change", async function () {
        const fileInput = this;
        const file = fileInput.files[0];
        const allowedTypes = ["image/jpeg", "image/png", "image/gif", "image/heic"];
        const maxSizeMB = 30;

        if (!file) return;

        if (!allowedTypes.includes(file.type)) {
            alert("กรุณาอัปโหลดไฟล์รูปภาพเท่านั้น (JPEG, PNG, GIF, HEIC)");
            fileInput.value = "";
            return;
        }

        if (file.size > maxSizeMB * 1024 * 1024) {
            alert("ขนาดไฟล์ต้องไม่เกิน 30 MB");
            fileInput.value = "";
            return;
        }

        // ถ้าเป็น HEIC ให้แปลงเป็น JPG
        if (file.type === "image/heic") {
            try {
                const convertedBlob = await heic2any({
                    blob: file,
                    toType: "image/jpeg",
                    quality: 0.9,
                });

                const newFile = new File([convertedBlob], file.name.replace(/\.[^/.]+$/, ".jpg"), {
                    type: "image/jpeg",
                });

                // แสดง preview
                const reader = new FileReader();
                reader.onload = function (e) {
                    $("#preview_image").attr("src", e.target.result).show();
                };
                reader.readAsDataURL(newFile);

                // ใส่ไฟล์ใหม่แทนใน input (จำลองด้วย DataTransfer)
                const dataTransfer = new DataTransfer();
                dataTransfer.items.add(newFile);
                fileInput.files = dataTransfer.files;

            } catch (err) {
                alert("เกิดข้อผิดพลาดขณะพยายามแปลงไฟล์ HEIC: " + err.message);
                fileInput.value = "";
            }

        } else {
            // Preview ปกติ
            const reader = new FileReader();
            reader.onload = function (e) {
                $("#preview_image").attr("src", e.target.result).show();
            };
            reader.readAsDataURL(file);
        }
    });

</script>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const paymentTypeInput = document.getElementById('payment_type');
        const commonFeeInput = document.getElementById('common_fee');
        const amountInput = document.getElementById('amount');
        const paymentOptionInputs = document.querySelectorAll('input[name="payment_option"]');

        function getSelectedPaymentOption() {
            for (const el of paymentOptionInputs) {
                if (el.checked) return el.value;
            }
            return null;
        }

        function calculateAmount() {
            const commonFee = parseFloat(commonFeeInput.value) || 0;
            const paymentOption = getSelectedPaymentOption();

            let paymentType = 0;

            if (paymentOption === 'monthly') {
                paymentType = parseInt(paymentTypeInput.value) || 0;
            } else if (paymentOption === 'yearly') {
                paymentType = 12;
            }

            const total = paymentType * commonFee;
            amountInput.value = total.toFixed(2);

            // debug log
            console.log('[calculateAmount]');
            console.log('commonFee:', commonFee);
            console.log('paymentOption:', paymentOption);
            console.log('paymentType:', paymentType);
            console.log('total amount:', amountInput.value);
        }

        function handlePaymentOptionChange() {
            const selectedOption = getSelectedPaymentOption();

            console.log('[handlePaymentOptionChange] selectedOption:', selectedOption);

            if (selectedOption === 'yearly') {
                paymentTypeInput.value = 12;
                //paymentTypeInput.disabled = false;  // ไม่ disable
                amountInput.readOnly = false;
            } else if (selectedOption === 'monthly') {
                paymentTypeInput.value = 1;
                //paymentTypeInput.disabled = false;  // ไม่ disable
                amountInput.readOnly = true;
            }

            calculateAmount();
        }

        paymentTypeInput.addEventListener('input', () => {
            console.log('[paymentTypeInput input] value:', paymentTypeInput.value);
            calculateAmount();
        });
        commonFeeInput.addEventListener('input', () => {
            console.log('[commonFeeInput input] value:', commonFeeInput.value);
            calculateAmount();
        });
        paymentOptionInputs.forEach(el =>
            el.addEventListener('change', handlePaymentOptionChange)
        );

        // ✅ เรียกหลังจากได้ data.common_fee แล้ว
        window.initWithCommonFeeInput = function () {
            // ตั้งค่ารายเดือนเป็น default
            document.querySelector('input[name="payment_option"][value="monthly"]').checked = true;
            paymentTypeInput.value = 1;
            //paymentTypeInput.disabled = false;
            amountInput.readOnly = true;

            console.log('[initWithCommonFeeInput] init default monthly');
            calculateAmount();
        };
    });
</script>

<script>
    $(document).ready(function () {
        $("#transfer_form").on("submit", function (event) {
            event.preventDefault();

            let period_month_start = parseInt($("#period_month_start").val());
            let period_month_to = parseInt($("#period_month_to").val());
            let period_year = parseInt($("#period_year").val());
            let amount = parseFloat($("#amount").val()) || 0;
            let house_number = $("#house_number").val();
            let period_month_start_name = monthNames[period_month_start];
            let period_month_to_name = monthNames[period_month_to];

            function padZero(n) {
                return n < 10 ? '0' + n : n;
            }

            let date = new Date();
            let current_date = padZero(date.getDate()) + "-" + padZero(date.getMonth() + 1) + "-" + date.getFullYear();
            let current_time = padZero(date.getHours()) + ":" + padZero(date.getMinutes()) + ":" + padZero(date.getSeconds());
            let date_time = current_date + " " + current_time;

            if (period_month_start > period_month_to) {
                alertify.error("กรุณาตรวจสอบเดือนเริ่มต้นและเดือนสิ้นสุดให้ถูกต้อง");
                return;
            }

            if (amount <= 0) {
                alertify.error("จำนวนเงินต้องมากกว่า 0 บาท");
                return;
            }

            // 🔒 ปิดปุ่ม + แสดงโหลด
            $("#submit_btn").prop("disabled", true);
            $("#loading").show();

            let formData = new FormData(this);
            formData.append('period_month_start', $("#period_month_start").val());
            formData.append('period_month_to', $("#period_month_to").val());
            formData.append('payment_type', $("#payment_type").val());

            $.ajax({
                url: "model/manage_payment_transfer_smart.php",
                type: "POST",
                data: formData,
                contentType: false,
                processData: false,
                success: function (response) {
                    $("#loading").hide();

                    if (response == 1) {
                        alertify.success("บันทึกข้อมูลการชำระเงินและส่ง Slip สำเร็จ");
                        $("#transfer_form")[0].reset();
                        $("#preview_image").hide().attr("src", "");
                        $("#submit_btn").prop("disabled", true);

                        if (liff.isInClient()) {
                            liff.getProfile().then(profile => {
                                const message = `📤 แจ้งการโอนเงินเรียบร้อยแล้ว!\nจำนวน ${amount} บาท\nบ้านเลขที่: ${house_number}
                                \n📅 เดือน: ${period_month_start_name} - ${period_month_to_name} \nปี: ${period_year}
                                \nวันที่ทำรายการ: ${date_time}\nโปรดตรวจสอบรายการในประวัติการชำระค่าส่วนกลาง`;
                                liff.sendMessages([{type: "text", text: message}])
                                    .then(() => {
                                        setTimeout(() => {
                                            liff.closeWindow();
                                        }, 2000);
                                    })
                                    .catch(err => {
                                        console.error("ส่งข้อความล้มเหลว:", err);
                                        alertify.error("ส่งข้อความกลับ LINE ไม่สำเร็จ");
                                        liff.closeWindow();
                                    });
                            });
                        } else {
                            alertify.success("ไม่ได้เปิดใน LINE App (ข้อความจะไม่ถูกส่ง)");
                        }

                    } else {
                        alertify.error("ไม่สามารถบันทึกข้อมูลได้: " + response);
                        $("#submit_btn").prop("disabled", false);
                    }
                },
                error: function () {
                    $("#loading").hide();
                    alertify.error("เกิดข้อผิดพลาดในการส่งข้อมูล");
                    $("#submit_btn").prop("disabled", false);
                }
            });
        });
    });
</script>



</body>
</html>