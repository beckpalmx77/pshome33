<?php

session_start();
error_reporting(0);
$curr_date = date("d-m-Y");
include('includes/Header.php');
include('config/connect_db.php');

if (strlen($_SESSION['alogin']) === "") {
    header("Location: index.php");
} else {

    $sql_bank = " SELECT * FROM ims_company ";
    $stmt_bank = $conn->prepare($sql_bank);
    $stmt_bank->execute();
    $BankCurr = $stmt_bank->fetchAll();
    foreach ($BankCurr as $row_curr) {
        $bank_name = $row_curr["bank_name"];
        $bank_account_name = $row_curr["bank_account_name"];
        $bank_account_no = $row_curr["bank_account_no"];
    }

    if (($_SESSION['account_type']) === "user") {
        $house_number = $_SESSION['house_number'];
        $f_name = $_SESSION['first_name'];
        $l_name = $_SESSION['last_name'];

        $sql_house_master = " SELECT * FROM ims_house_master where house_number = '" . $house_number . "'";
        $stmt_house_master = $conn->prepare($sql_house_master);
        $stmt_house_master->execute();
        $hmCurr = $stmt_house_master->fetchAll();
        foreach ($hmCurr as $hm_curr) {
            $area_size = $hm_curr["area_size"];
            $common_fee = $hm_curr["common_fee"];
        }

    } else {
        $house_number = "";
        $f_name = "";
        $l_name = "";
        $area_size = "";
        $common_fee = "";
    }

    $months = [
        '01' => 'มกราคม', '02' => 'กุมภาพันธ์', '03' => 'มีนาคม', '04' => 'เมษายน',
        '05' => 'พฤษภาคม', '06' => 'มิถุนายน', '07' => 'กรกฎาคม', '08' => 'สิงหาคม',
        '09' => 'กันยายน', '10' => 'ตุลาคม', '11' => 'พฤศจิกายน', '12' => 'ธันวาคม'
    ];

    ?>

    <!DOCTYPE html>
    <html lang="th">

    <body id="page-top">
    <div id="wrapper">
        <?php include('includes/Side-Bar.php'); ?>

        <div id="content-wrapper" class="d-flex flex-column">
            <div id="content">
                <?php include('includes/Top-Bar.php'); ?>

                <div class="container-fluid" id="container-wrapper">
                    <div class="d-sm-flex align-items-center justify-content-between mb-4">
                        <h1 class="h3 mb-0 text-gray-800">โอนเงินและแนบ Slip/ใบโอนเงิน/ใบเสร็จ</h1>
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="<?php echo $_SESSION['dashboard_page'] ?>">Home</a>
                            </li>
                            <li class="breadcrumb-item active" aria-current="page">โอนเงินและแนบ
                                Slip/ใบโอนเงิน/ใบเสร็จ
                            </li>
                        </ol>
                    </div>

                    <div class="row">
                        <div class="col-lg-12">
                            <div class="card mb-12">
                                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between"></div>
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
                                                                   value="yearly" checked>
                                                            <label class="form-check-label" for="option_yearly">ชำระรายปี</label>
                                                        </div>
                                                        <div class="form-check">
                                                            <input class="form-check-input" type="radio"
                                                                   name="payment_option" id="option_monthly"
                                                                   value="monthly">
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

                                        <div class="form-group row align-items-end">
                                            <div class="col-md-4">
                                                <label for="period_month_start">เริ่มงวดเดือน
                                                    (เลือกเดือนที่ชำระ)</label>
                                                <select name="period_month_start" id="period_month_start"
                                                        class="form-control" style="background-color: #FFDAB9;"
                                                        required>
                                                    <option value="">เลือก</option>
                                                    <?php
                                                    $currentMonth = date('n'); // ดึงเลขเดือนปัจจุบัน (1-12)
                                                    $months = [
                                                        1 => 'มกราคม', 2 => 'กุมภาพันธ์', 3 => 'มีนาคม', 4 => 'เมษายน',
                                                        5 => 'พฤษภาคม', 6 => 'มิถุนายน', 7 => 'กรกฎาคม', 8 => 'สิงหาคม',
                                                        9 => 'กันยายน', 10 => 'ตุลาคม', 11 => 'พฤศจิกายน', 12 => 'ธันวาคม'
                                                    ];
                                                    foreach ($months as $val => $name) {
                                                        // กำหนดให้เดือนปัจจุบันถูกเลือกเป็นค่าเริ่มต้น
                                                        $selected = ($val == $currentMonth) ? 'selected' : '';
                                                        echo "<option value='$val' $selected>$name</option>";
                                                    }
                                                    ?>
                                                </select>
                                            </div>

                                            <div class="col-md-4">
                                                <label for="period_month_to">ถึงงวดเดือน (เลือกเดือนที่ชำระ)</label>
                                                <select name="period_month_to" id="period_month_to" class="form-control"
                                                        style="background-color: #FFDAB9;" required>
                                                    <option value="">เลือก</option>
                                                    <?php
                                                    // กำหนดให้เดือนปัจจุบันถูกเลือกเป็นค่าเริ่มต้นเช่นกัน
                                                    foreach ($months as $val => $name) {
                                                        $selected = ($val == $currentMonth) ? 'selected' : '';
                                                        echo "<option value='$val' $selected>$name</option>";
                                                    }
                                                    ?>
                                                </select>
                                            </div>

                                            <div class="col-md-4">
                                                <label for="period_year">งวดปี</label>
                                                <select name="period_year" id="period_year" class="form-control"
                                                        style="background-color: #0dcaf0" required>
                                                    <?php
                                                    $currentYear = date('Y'); // ดึงปีปัจจุบัน
                                                    $startYear = $currentYear - 1; // 1 ปีก่อนหน้า
                                                    $endYear = $currentYear + 1;  // 1 ปีข้างหน้า

                                                    for ($year = $startYear; $year <= $endYear; $year++) {
                                                        $selected = ($year == $currentYear) ? 'selected' : '';
                                                        echo "<option value='$year' $selected>$year</option>";
                                                    }
                                                    ?>
                                                </select>
                                            </div>
                                        </div>


                                        <div class="form-group has-success">
                                            <div class="row">
                                                <div class="col-md-4">
                                                    <div class="form-group has-success">
                                                        <label for="detail"
                                                               class="control-label">ชื่อผู้โอน/ผู้ชำระ</label>
                                                        <input type="text" name="detail" class="form-control" required
                                                               id="detail"
                                                               value="<?php echo $f_name . " " . $l_name ?>">
                                                    </div>
                                                </div>

                                                <div class="col-md-2">
                                                    <div class="form-group has-success">
                                                        <label for="area_size"
                                                               class="control-label">พื้นที่บ้าน ตรว</label>
                                                        <input type="number" name="area_size" id="area_size"
                                                               class="form-control" readonly="true"
                                                               value="<?php echo $area_size ?>">
                                                    </div>
                                                </div>

                                                <div class="col-md-2">
                                                    <div class="form-group has-success">
                                                        <label for="common_fee"
                                                               class="control-label">ค่าส่วนกลาง</label>
                                                        <input type="number" name="common_fee" id="common_fee"
                                                               class="form-control" readonly="true"
                                                               value="<?php echo $common_fee ?>">
                                                    </div>
                                                </div>

                                                <div class="col-md-4">
                                                    <div class="form-group has-success">
                                                        <label for="amount"
                                                               class="control-label">จำนวนเงินที่โอน</label>
                                                        <input type="number" id="amount" name="amount"
                                                               class="form-control"
                                                               required id="amount">
                                                    </div>
                                                </div>
                                            </div>
                                        </div>


                                        <div class="form-group has-success">
                                            <label for="remark" class="control-label">หมายเหตุ</label>
                                            <input name="remark" class="form-control" id="remark" value="-">
                                        </div>

                                        <div class="col-md-12 mb-3">
                                            <label for="payment_method" class="form-label fw-semibold mb-2">วิธีการชำระเงิน</label>
                                            <div class="d-flex align-items-center gap-3 flex-wrap custom-radio-row">
                                                <div class="d-flex gap-3 flex-shrink-0">
                                                    <div class="form-check form-check-inline custom-radio">
                                                        <input class="form-check-input" type="radio"
                                                               name="payment_method_radio"
                                                               id="method_transfer" value="โอนเงิน">
                                                        <label class="form-check-label" for="method_transfer">💳
                                                            โอนเงิน</label>
                                                    </div>
                                                    <div class="form-check form-check-inline custom-radio">
                                                        <input class="form-check-input" type="radio"
                                                               name="payment_method_radio"
                                                               id="method_cash" value="เงินสด" checked>
                                                        <label class="form-check-label" for="method_cash">💵
                                                            เงินสด</label>
                                                    </div>
                                                </div>

                                                <div class="flex-grow-1">
                                                    <input type="text" class="form-control"
                                                           name="payment_method" id="payment_method"
                                                           placeholder="ระบุวิธีการชำระเงิน" readonly>
                                                </div>
                                            </div>
                                        </div>


                                        <div class="form-group has-success">
                                            <label for="bank_transfer" class="control-label">โอนเงินเข้าบัญชี</label>

                                            <input name="bank_name" class="form-control" id="bank_name"
                                                   value="<?php echo $bank_name . " " . $bank_account_name . " เลขที่บัญชี : " . $bank_account_no ?>"
                                                   readonly="true">
                                        </div>

                                        <div class="form-group has-success">
                                            <label for="picture_payment" class="control-label">แนบ
                                                Slip/ใบโอนเงิน/ใบเสร็จ</label>
                                            <input type="file" name="picture_payment" id="picture_payment"
                                                   class="form-control">
                                            <img id="preview_image" src="#" alt="Preview Image"
                                                 style="display: none; margin-top: 10px; max-width: 300px;"/>
                                        </div>

                                        <div id="loading"
                                             style="display: none; text-align: center; margin-top: 20px;">
                                            <img src="img/spin/spin_cir.gif" alt="Loading..." style="width: 50px;">
                                            <p>กำลังบันทึกข้อมูล...</p>
                                        </div>

                                        <button type="submit" id="submit_btn" class="btn btn-primary btn-block">
                                            บันทึกข้อมูล
                                        </button>
                                    </form>

                                    <div id="result"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <?php
            include('includes/Modal-Logout.php');
            include('includes/Footer.php');
            ?>

        </div>
    </div>

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

    <style>
        .custom-radio .form-check-input {
            transform: scale(1.2);
            margin-right: 6px;
        }

        .custom-radio .form-check-label {
            font-weight: 500;
            cursor: pointer;
        }

        .form-control[readonly] {
            background-color: #e9ecef;
        }

        @media (max-width: 768px) {
            .custom-radio-row {
                flex-direction: column;
                align-items: stretch;
            }
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
            // Preview Image
            $("#picture_payment").on("change", function () {
                const file = this.files[0];

                if (!file) {
                    // ไม่เลือกไฟล์: ซ่อน preview
                    $("#preview_image").hide().attr("src", "");
                    return;
                }

                const allowedTypes = ["image/jpeg", "image/png", "image/gif"];
                if (!allowedTypes.includes(file.type)) {
                    alert("กรุณาอัปโหลดไฟล์รูปภาพเท่านั้น (JPEG, PNG, GIF)");
                    this.value = "";
                    $("#preview_image").hide().attr("src", "");
                    return;
                }

                if (file.size > 30 * 1024 * 1024) {
                    alert("ขนาดไฟล์ต้องไม่เกิน 30 MB");
                    this.value = "";
                    $("#preview_image").hide().attr("src", "");
                    return;
                }

                const reader = new FileReader();
                reader.onload = function (e) {
                    $("#preview_image").attr("src", e.target.result).show();
                };
                reader.readAsDataURL(file);
            });

            // Submit Form with Loading Indicator and prevent duplicate submit
            $("#transfer_form").on("submit", function (event) {
                event.preventDefault();

                $("#submit_btn").prop("disabled", true);
                $("#loading").show();

                let period_month_start = parseInt($("#period_month_start").val());
                let period_month_to = parseInt($("#period_month_to").val());

                if (period_month_start > period_month_to) {
                    alertify.error("กรุณาตรวจสอบเดือนเริ่มต้นและเดือนสิ้นสุดให้ถูกต้อง (เริ่มต้นต้องน้อยกว่าหรือเท่ากับสิ้นสุด)");
                    $("#submit_btn").prop("disabled", false);
                    $("#loading").hide();
                    return;
                }

                let formData = new FormData(this);
                formData.append('period_month_start', $("#period_month_start").val());
                formData.append('period_month_to', $("#period_month_to").val());
                formData.append('payment_type', $("#payment_type").val());
                formData.append('payment_method', $("#payment_method").val());

                $.ajax({
                    url: "model/manage_payment_transfer.php",
                    type: "POST",
                    data: formData,
                    contentType: false,
                    processData: false,
                    success: function (response) {
                        $("#loading").hide();

                        if (response == 1) {
                            alertify.success("ชำระเงิน/โอน และส่ง Slip/ใบโอนเงิน/ใบเสร็จ สำเร็จ");
                            $("#transfer_form")[0].reset();
                            $("#preview_image").hide().attr("src", "");
                            $("#submit_btn").prop("disabled", true);
                        } else {
                            alertify.error("ไม่สามารถบันทึกข้อมูลได้");
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

    <script>
        $(document).ready(function () {
            function toggleFields() {
                const currentMonth = new Date().getMonth() + 1; // ดึงเลขเดือนปัจจุบัน (1-12)

                if ($("#option_monthly").is(":checked")) {
                    $("#period_month_start").prop("disabled", false).val(currentMonth); // กำหนดเป็น เดือนปัจจุบัน
                    $("#period_month_to").prop("disabled", false).val(currentMonth);   // กำหนดเป็น เดือนปัจจุบัน
                    $("#period_year").prop("disabled", false);
                    $("#payment_type").prop("disabled", false);
                    $("#payment_type").val(1);
                } else if ($("#option_yearly").is(":checked")) {
                    $("#period_month_start").prop("disabled", true).val(1); // กำหนดเป็น มกราคม
                    $("#period_month_to").prop("disabled", true).val(12);   // กำหนดเป็น ธันวาคม
                    $("#period_year").prop("disabled", false);
                    $("#payment_type").prop("disabled", true);
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
                            endMonth = ((endMonth - 1) % 12) + 1;
                        }
                        $("#period_month_to").val(endMonth);
                    }
                } else {
                    $("#period_month_to").val('');
                }
            }

            $("#payment_type, #period_month_start, #option_monthly").on("input change", updatePeriodMonthTo);
        });
    </script>

    <script>
        document.addEventListener("DOMContentLoaded", function () {
            const houseNumberInput = document.getElementById("house_number");

            let amount_cal = 0;

            houseNumberInput.addEventListener("change", function () {
                const houseNumber = this.value;

                if (houseNumber.trim() !== "") {
                    fetch("model/get_house_info.php?house_number=" + encodeURIComponent(houseNumber))
                        .then(response => response.json())
                        .then(data => {
                            if (data.success) {
                                document.getElementById("common_fee").value = data.common_fee;
                                document.getElementById("area_size").value = data.area_size;
                                document.getElementById("detail").value = data.contact_name;
                                // Recalculate amount after common_fee is fetched
                                calculateAmount();
                            } else {
                                document.getElementById("common_fee").value = '';
                                document.getElementById("area_size").value = '';
                                document.getElementById("detail").value = '';
                                alert("ไม่พบข้อมูลบ้านเลขที่นี้");
                            }
                        })
                        .catch(error => {
                            console.error("เกิดข้อผิดพลาด:", error);
                        });
                }
            });
        });
    </script>

    <script>
        // เมื่อเปลี่ยน radio จะอัปเดต input ช่อง payment_method
        document.querySelectorAll('input[name="payment_method_radio"]').forEach((radio) => {
            radio.addEventListener('change', function () {
                document.getElementById('payment_method').value = this.value;
            });
        });

        // ตั้งค่าเริ่มต้นให้ตรงกับ radio ที่ถูกเลือกไว้
        document.addEventListener('DOMContentLoaded', function () {
            const selected = document.querySelector('input[name="payment_method_radio"]:checked');
            if (selected) {
                document.getElementById('payment_method').value = selected.value;
            }
        });
    </script>

    <script>
        $(document).ready(function () {
            function calculateAmount() {
                const commonFee = parseFloat($("#common_fee").val()) || 0;
                const isYearly = $("#option_yearly").is(":checked"); // Check if yearly option is selected

                if (isYearly) {
                    const currentDate = new Date();
                    const currentYear = currentDate.getFullYear();
                    const currentMonth = currentDate.getMonth() + 1; // January is 0, so add 1
                    const currentDay = currentDate.getDate();
                    const periodYear = parseInt($("#period_year").val()); // Get the selected period year

                    // Check for yearly discount condition:
                    // Payment in December of the year BEFORE the period year (e.g., Dec 2024 for period 2025)
                    // OR Payment in January (1st-31st) of the period year (e.g., Jan 2025 for period 2025)
                    const applyDiscount = (
                        (currentYear === (periodYear - 1) && currentMonth === 12) ||
                        (currentYear === periodYear && currentMonth === 1 && currentDay <= 31)
                    );

                    if (applyDiscount) {
                        if (commonFee > 0) {
                            $("#amount").val((commonFee * 11).toFixed(2));
                        } else {
                            $("#amount").val('');
                        }
                    } else {
                        // If yearly but outside the discount window, calculate for 12 months
                        if (commonFee > 0) {
                            $("#amount").val((commonFee * 12).toFixed(2));
                        } else {
                            $("#amount").val('');
                        }
                    }
                    $("#amount").prop("readonly", false); // Keep editable if needed
                } else { // Monthly payment
                    const months = parseInt($("#payment_type").val()) || 0;
                    $("#amount").prop("readonly", false); // Amount is editable for monthly

                    if (commonFee > 0 && months > 0) {
                        const amount = commonFee * months;
                        $("#amount").val(amount.toFixed(2));
                    } else {
                        $("#amount").val('');
                    }
                }
            }

            // Call when data changes
            $("#common_fee, #payment_type").on("input change", calculateAmount);
            $("input[name='payment_option']").on("change", calculateAmount);
            $("#period_year").on("change", calculateAmount); // Also re-calculate if year changes

            // Call immediately on page load
            calculateAmount();
        });
    </script>

    </body>
    </html>

<?php } ?>