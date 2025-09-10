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
        $phone_number = $_SESSION['phone_number'];

        $sql_house_master = " SELECT * FROM v_ims_house where house_number = '" . $house_number . "'";
        $stmt_house_master = $conn->prepare($sql_house_master);
        $stmt_house_master->execute();
        $hmCurr = $stmt_house_master->fetchAll();
        foreach ($hmCurr as $hm_curr) {
            $area_size = $hm_curr["area_size"];
            $common_fee = $hm_curr["common_fee"];
            $phone_number = $hm_curr["phone_number"];
        }

    } else {
        $house_number = "";
        $f_name = "";
        $l_name = "";
        $phone_number = "";
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
                        <h1 class="h3 mb-0 text-gray-800">ชำระเงิน-โอนเงิน แนบ Slip/ใบโอนเงิน/ใบเสร็จ</h1>
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
                                                               id="house_number" value="<?php echo $house_number ?>">
                                                    </div>
                                                </div>
                                                <div class="col-md-2">
                                                    <div class="form-group has-success">
                                                        <label class="control-label">การชำระ (Click เพื่อคำนวณ)</label>
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
                                                <input type="hidden" id="month_year_calculator"
                                                       name="month_year_calculator" value="12">
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
                                                    $startYear = $currentYear - 20; // 20 ปีก่อนหน้า
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
                                                               class="control-label">หมายเลขโทรศัพท์</label>
                                                        <input type="text" name="phone_number" id="phone_number"
                                                               class="form-control" required
                                                               value="<?php echo $phone_number ?>">
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

                                                <div class="col-md-2">
                                                    <div class="form-group has-success">
                                                        <label for="amount"
                                                               class="control-label">จำนวนเงินที่ชำระ</label>
                                                        <input type="number" id="amount" name="amount"
                                                               class="form-control" required
                                                               required id="amount">
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="form-group has-success">
                                            <div class="row g-3">
                                                <div class="col-md-7"><label for="payment_method"
                                                                             class="form-label fw-semibold mb-2">วิธีการชำระเงิน</label>
                                                    <div class="d-flex align-items-center gap-3 flex-wrap custom-radio-row">
                                                        <div class="d-flex gap-3">
                                                            <div class="form-check form-check-inline custom-radio">
                                                                <input class="form-check-input" type="radio"
                                                                       name="payment_method_radio" id="method_transfer"
                                                                       value="โอนเงิน">
                                                                <label class="form-check-label" for="method_transfer">💳
                                                                    โอนเงิน</label>
                                                            </div>
                                                            <div class="form-check form-check-inline custom-radio">
                                                                <input class="form-check-input" type="radio"
                                                                       name="payment_method_radio" id="method_cash"
                                                                       value="เงินสด" checked>
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
                                                <div class="col-md-5">
                                                    <label for="bank_transfer"
                                                           class="control-label">โอนเงินเข้าบัญชี</label>
                                                    <input name="bank_name" class="form-control" id="bank_name"
                                                           value="<?php echo $bank_name . " " . $bank_account_name . " เลขที่บัญชี : " . $bank_account_no ?>"
                                                           readonly="true">
                                                </div>
                                            </div>

                                            <div class="form-group has-success">
                                                <div class="row g-3">
                                                    <div class="col-md-12">
                                                        <div class="form-group has-success">
                                                            <label for="remark" class="control-label">หมายเหตุ</label>
                                                            <input name="remark" class="form-control" id="remark"
                                                                   value="-">
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

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

                    <div class="modal fade" id="promotionModal" tabindex="-1" role="dialog"
                         aria-labelledby="promotionModalLabel"
                         aria-hidden="true">
                        <div class="modal-dialog modal-dialog-centered" role="document">
                            <div class="modal-content">
                                <div class="modal-header bg-success text-white">
                                    <h5 class="modal-title" id="promotionModalLabel">📣 ประชาสัมพันธ์</h5>
                                    <button type="button" class="close text-white" data-dismiss="modal"
                                            aria-label="Close">
                                        <span aria-hidden="true">&times;</span>
                                    </button>
                                </div>
                                <div class="modal-body text-center">
                                    <p class="h5 text-success">ชำระค่าส่วนกลางรายปีล่วงหน้า</p>
                                    <p class="h6 text-primary">รับส่วนลดทันที 1 เดือน (ชำระเพียง 11 เดือน)</p>
                                    <p class="h6 text-danger">โปรโมชั่นนี้มีผลถึงวันที่ 31 มกราคม</p>
                                    <img src="img/promotion_banner.png" alt="Promotion Banner" class="img-fluid mt-3"
                                         style="max-width: 100%; height: auto;">
                                </div>
                                <div class="modal-footer justify-content-center">
                                    <button type="button" class="btn btn-secondary" data-dismiss="modal">ปิด</button>
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

            // Add event listener for 'change' and 'blur'
            houseNumberInput.addEventListener("blur", handleHouseNumberChange);

            function handleHouseNumberChange() {
                const houseNumber = this.value;

                // Clear existing values immediately
                document.getElementById("common_fee").value = '';
                document.getElementById("area_size").value = '';
                document.getElementById("phone_number").value = '';
                document.getElementById("detail").value = ''; // Clear contact_name too, as it's related
                document.getElementById("amount").value = '';

                if (houseNumber.trim() !== "") {
                    fetch("model/get_house_info.php?house_number=" + encodeURIComponent(houseNumber))
                        .then(response => response.json())
                        .then(data => {
                            if (data.success) {
                                document.getElementById("common_fee").value = data.common_fee;
                                document.getElementById("area_size").value = data.area_size;
                                document.getElementById("detail").value = data.contact_name;
                                document.getElementById("phone_number").value = data.phone_number;

                                // Recalculate amount after house info is loaded, based on current options
                                $('#common_fee').trigger('input'); // This calls calculateAmount()

                                // Removed: applyPromotionLogic(); // Removed call to not show popup on house_number input
                            } else {
                                // Already cleared, just show alert if not found
                                //alert("ไม่พบข้อมูลบ้านเลขที่นี้");
                            }
                        })
                        .catch(error => {
                            console.error("เกิดข้อผิดพลาด:", error);
                            //alert("เกิดข้อผิดพลาดในการดึงข้อมูลบ้าน");
                        });
                }
            }
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
                    // Always use month_year_calculator for yearly payments
                    if (commonFee > 0) {
                        $("#amount").val((commonFee * parseFloat($("#month_year_calculator").val())).toFixed(2));
                    } else {
                        $("#amount").val('');
                    }
                    $("#amount").prop("readonly", false); // Keep editable if needed
                } else { // Monthly payment
                    const months = parseInt($("#payment_type").val()) || 0;
                    $("#amount").prop("readonly", true); // Make amount readonly for monthly

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
            $("#month_year_calculator").on("change", calculateAmount); // Trigger calculation on month_year_calculator change

            // Call immediately on page load
            calculateAmount();
        });
    </script>

    <script>
        // Moved the promotion logic into a named function
        function applyPromotionLogic() {
            const currentDate = new Date();
            const currentYear = currentDate.getFullYear();
            const currentMonth = currentDate.getMonth() + 1; // getMonth() returns 0-11
            const currentDay = currentDate.getDate();

            // Define the promotion period for popup (Dec 15 of current year to Jan 31 of next year)
            const promoStartMonthPrevYear = 12; // December
            const promoStartDayPrevYear = 15;

            const promoEndMonthCurrentYear = 1; // January
            const promoEndDayCurrentYear = 31;

            let showPopup = false;

            // Condition 1: From Dec 15 of current year
            if (currentMonth === promoStartMonthPrevYear && currentDay >= promoStartDayPrevYear) {
                showPopup = true;
            }
            // Condition 2: To Jan 31 of next year
            else if (currentMonth === promoEndMonthCurrentYear && currentDay <= promoEndDayCurrentYear) {
                showPopup = true;
            }

            if (showPopup) {
                // Use a slight delay to ensure Bootstrap's JS is fully loaded
                setTimeout(function () {
                    $('#promotionModal').modal('show');
                }, 500); // 500ms delay

                // Set month_year_calculator to 11 for the discount if promotion is active
                $("#month_year_calculator").val(11);

                // Also, pre-select the "yearly" option if within the promotion period
                document.getElementById('option_yearly').checked = true;
                document.getElementById('option_monthly').checked = false;
                // Trigger change event to update related fields and recalculate amount
                const event = new Event('change');
                document.getElementById('option_yearly').dispatchEvent(event);
            }
            // If not in promotion period, ensure month_year_calculator is reset to default 12 for yearly
            else {
                // Check if 'option_yearly' is checked, and if so, set calculator back to 12
                // This ensures that if user manually selects yearly outside promo, they pay for 12 months
                if (document.getElementById('option_yearly').checked) {
                    $("#month_year_calculator").val(12);
                    // Also ensure monthly is not checked and yearly is checked
                    document.getElementById('option_monthly').checked = false;
                    document.getElementById('option_yearly').checked = true;
                    // Trigger change to recalculate if yearly was already selected and promo expired/not active
                    const event = new Event('change');
                    document.getElementById('option_yearly').dispatchEvent(event);
                }
            }
        }

        document.addEventListener('DOMContentLoaded', function () {
            applyPromotionLogic(); // Call on DOMContentLoaded
        });
    </script>

    <script>
        $(document).ready(function () {
            $("#transfer_form").on("submit", function (event) {
                event.preventDefault();

                // ตรวจสอบว่าได้มีการเลือกไฟล์รูปภาพหรือไม่
                const paymentMethod = $("#payment_method").val(); // Get the selected payment method

                if (paymentMethod !== "เงินสด" && $("#picture_payment").get(0).files.length === 0) {
                    alertify.error("กรุณาแนบ Slip/ใบโอนเงิน/ใบเสร็จ ก่อนบันทึกข้อมูล");
                    return; // หยุดการทำงานของฟังก์ชัน submit
                }

                let period_month_start = parseInt($("#period_month_start").val());
                let period_month_to = parseInt($("#period_month_to").val());
                let period_year = parseInt($("#period_year").val()); // ดึงค่า period_year มาใช้งาน
                let amount = parseFloat($("#amount").val()) || 0;

                let house_number = document.getElementById("house_number").value;

                if (house_number === null || house_number.trim() === "") {
                    alertify.error("กรุณาใส่บ้านเลขที่");
                    return; // Stops the function from running further
                }

                // ต้องมั่นใจว่า monthNames ถูกกำหนดไว้แล้ว ตัวอย่างเช่น:
                const monthNames = ["", "มกราคม", "กุมภาพันธ์", "มีนาคม", "เมษายน", "พฤษภาคม", "มิถุนายน", "กรกฎาคม", "สิงหาคม", "กันยายน", "ตุลาคม", "พฤศจิกายน", "ธันวาคม"];
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
                formData.append('payment_method', $("#payment_method").val());
                formData.append('phone_number', $("#phone_number").val());
                // **ส่งค่า amount ที่จัดรูปแบบแล้วไปหลังบ้าน**
                formData.append('amount', parseFloat($("#amount").val()).toFixed(2));

                $.ajax({
                    url: "model/manage_payment_transfer.php",
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
                        } else if (response == 2) {
                            // ปรับข้อความแจ้งเตือนให้แสดงปีด้วย
                            alertify.error(`มีข้อมูลการชำระค่าส่วนกลางงวดเดือน ${period_month_start_name} ปี ${period_year} แล้ว ไม่สามารถบันทึกได้`);
                            $("#submit_btn").prop("disabled", false);
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

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // อ้างอิงถึง Element ต่างๆ ที่เราจะใช้งาน
            const startMonthSelect = document.getElementById('period_month_start');
            const endMonthSelect = document.getElementById('period_month_to');
            const paymentTypeInput = document.getElementById('payment_type');
            const periodYearSelect = document.getElementById('period_year');

            // ฟังก์ชันสำหรับคำนวณและอัปเดตค่า
            function calculateAndSetMonths() {
                const startMonth = parseInt(startMonthSelect.value);
                const endMonth = parseInt(endMonthSelect.value);
                const year = parseInt(periodYearSelect.value);

                // ตรวจสอบว่าได้เลือกเดือนเริ่มต้นและสิ้นสุดแล้ว
                if (startMonth && endMonth) {
                    // ตรวจสอบเงื่อนไข: period_month_start ต้องไม่น้อยกว่า period_month_to
                    if (startMonth > endMonth) {
                        alert('ต้องเลือกเดือน "เริ่มงวด" ให้น้อยกว่าหรือเท่ากับเดือน "ถึงงวด" กรุณาเลือกใหม่ให้ถูกต้อง');
                        // ตั้งค่า payment_type กลับไปที่ค่าเริ่มต้น
                        paymentTypeInput.value = 1;
                        return; // หยุดการทำงานของฟังก์ชัน
                    }

                    // คำนวณจำนวนเดือน
                    const numberOfMonths = endMonth - startMonth + 1;

                    // อัปเดตค่าในช่อง payment_type
                    paymentTypeInput.value = numberOfMonths;
                } else {
                    // ถ้ายังไม่ได้เลือกเดือนใดเดือนหนึ่ง ให้ค่าเป็น 1
                    paymentTypeInput.value = 1;
                }
            }

            // เพิ่ม Event Listener เพื่อเรียกฟังก์ชันเมื่อค่ามีการเปลี่ยนแปลง
            startMonthSelect.addEventListener('change', calculateAndSetMonths);
            endMonthSelect.addEventListener('change', calculateAndSetMonths);
            periodYearSelect.addEventListener('change', calculateAndSetMonths);
        });
    </script>


    </body>
    </html>

<?php } ?>