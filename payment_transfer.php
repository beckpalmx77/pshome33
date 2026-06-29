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
    $bank_name = "";
    $bank_account_name = "";
    $bank_account_no = "";
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
                                                               value="1" min="1" max="12">
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
                                                    $endYear = $currentYear + 2;  // 2 ปีข้างหน้า

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
                                                <div class="col-md-3">
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

                                                <div class="col-md-1">
                                                    <div class="form-group has-success">
                                                        <label for="area_size"
                                                               class="control-label">พื้นที่ ตรว</label>
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
                                                               class="control-label">จำนวนเงินชำระ</label>
                                                        <input type="number" id="amount" name="amount"
                                                               class="form-control" required
                                                               required id="amount" step="0.01">
                                                    </div>
                                                </div>

                                                <div class="col-md-2">
                                                    <div class="form-group has-success">
                                                        <label for="receive_first"
                                                               class="control-label" style="color: blue;">รับเงินสด</label>
                                                        <input type="number" id="receive_first" name="receive_first"
                                                               class="form-control" step="0.01" placeholder="0.00" style="color: blue; font-weight: bold;">
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
                                                                    value="เงินสด"
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
                                    <p class="h6 text-danger">สำหรับการชำระของปีถัดไปเท่านั้น</p>
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
        // ทำความสะอาดค่าบ้านเลขที่
        function cleanHouseNumber(value) {
            return value.replace(/\s+/g, '').replace(/[^0-9\/]/g, '');
        }
        const houseNumberInput = document.getElementById("house_number");
        houseNumberInput.addEventListener("input", function () { this.value = cleanHouseNumber(this.value); });
        houseNumberInput.addEventListener("change", function () { this.value = cleanHouseNumber(this.value); });
        houseNumberInput.addEventListener("blur", function () { this.value = cleanHouseNumber(this.value); });
    </script>

    <script>
        // -------------------------------------------------------------
        // รวม Logic ทั้งหมดไว้ใน Block นี้ เพื่อลดการตีกันของ Script
        // -------------------------------------------------------------

        // 1. ฟังก์ชันเช็คช่วงเวลาโปรโมชั่น
        function isPromotionPeriod() {
            const currentDate = new Date();
            const currentMonth = currentDate.getMonth() + 1;
            const currentDay = currentDate.getDate();

            // เงื่อนไข: 15 ธ.ค. - 31 ม.ค.
            const promoStartMonthPrevYear = 12;
            const promoStartDayPrevYear = 15;
            const promoEndMonthCurrentYear = 1;
            const promoEndDayCurrentYear = 31;

            if (currentMonth === promoStartMonthPrevYear && currentDay >= promoStartDayPrevYear) {
                return true;
            } else if (currentMonth === promoEndMonthCurrentYear && currentDay <= promoEndDayCurrentYear) {
                return true;
            }
            return false;
        }

        document.addEventListener('DOMContentLoaded', function () {
            // ประกาศตัวแปร Elements
            const paymentOptionRadios = document.querySelectorAll("input[name='payment_option']");
            const periodYearSelect = document.getElementById("period_year");
            const startMonthSelect = document.getElementById("period_month_start");
            const endMonthSelect = document.getElementById("period_month_to");
            const paymentTypeInput = document.getElementById("payment_type");
            const commonFeeInput = document.getElementById("common_fee");
            const amountInput = document.getElementById("amount");
            const remarkInput = document.getElementById("remark");
            const monthYearCalculatorInput = document.getElementById("month_year_calculator");

            // ฟังก์ชันคำนวณเงิน
            function calculateAmount() {
                const commonFee = parseFloat(commonFeeInput.value) || 0;
                const isYearly = document.getElementById("option_yearly").checked;
                let calculatedAmount = 0;

                if (isYearly) {
                    const monthsToCharge = parseFloat(monthYearCalculatorInput.value) || 12;
                    calculatedAmount = commonFee * monthsToCharge;
                } else {
                    const months = parseInt(paymentTypeInput.value) || 0;
                    calculatedAmount = commonFee * months;
                }

                if(commonFee > 0 && calculatedAmount > 0) {
                    amountInput.value = calculatedAmount.toFixed(2);
                } else {
                    amountInput.value = "";
                }
                calculateChange();
            }

            // ฟังก์ชันคำนวณเงินทอน
            function calculateChange() {
                const receiveFirst = parseFloat(document.getElementById("receive_first").value) || 0;
                const amount = parseFloat(amountInput.value) || 0;

                if (receiveFirst > 0) {
                    const change = receiveFirst - amount;
                    if (change >= 0) {
                        remarkInput.value = "รับเงิน " + receiveFirst.toLocaleString() + " บาท หักชำระ " + amount.toLocaleString() + " บาท (ทอน " + change.toLocaleString() + " บาท)";
                    } else {
                        remarkInput.value = "รับเงินไม่เพียงพอ (ขาด " + Math.abs(change).toLocaleString() + " บาท)";
                    }
                } else {
                    // ถ้าเป็น 0 หรือว่าง ให้กลับไปใช้ Logic พื้นฐาน (โปรโมชั่น/ค่าว่าง)
                    updatePaymentLogic();
                }
            }

            // ฟังก์ชันจัดการ Logic รายปี/รายเดือน และโปรโมชั่น
            function updatePaymentLogic() {
                const isYearly = document.getElementById("option_yearly").checked;
                const currentDate = new Date();
                const currentRealYear = currentDate.getFullYear();
                const currentMonth = currentDate.getMonth() + 1;
                const selectedYear = parseInt(periodYearSelect.value) || currentRealYear;
                const isPromo = isPromotionPeriod();

                // เช็คว่าปีที่เลือกเข้าเงื่อนไขโปรหรือไม่
                let isEligibleYear = false;
                if (isPromo) {
                    if (currentMonth === 12 && selectedYear > currentRealYear) isEligibleYear = true;
                    else if (currentMonth === 1 && selectedYear >= currentRealYear) isEligibleYear = true;
                }

                if (isYearly) {
                    if (isEligibleYear) {
                        monthYearCalculatorInput.value = 11;
                        remarkInput.value = "ชำระรายปีล่วงหน้า (ส่วนลด 1 เดือน)";
                    } else {
                        monthYearCalculatorInput.value = 12;
                        if (isPromo && !isEligibleYear) {
                            remarkInput.value = "ชำระรายปี (ปีปัจจุบัน/ย้อนหลัง - ไม่เข้าเงื่อนไขส่วนลด)";
                        } else {
                            remarkInput.value = "-";
                        }
                    }

                    // Lock Inputs
                    startMonthSelect.disabled = true;
                    startMonthSelect.value = 1;
                    endMonthSelect.disabled = true;
                    endMonthSelect.value = 12;
                    paymentTypeInput.disabled = true;
                    paymentTypeInput.value = 12;

                } else {
                    // รายเดือน
                    remarkInput.value = "-";
                    startMonthSelect.disabled = false;
                    endMonthSelect.disabled = false;
                    paymentTypeInput.disabled = false;

                    // ถ้ายังไม่มีค่า ให้ตั้งค่า Default เป็นเดือนปัจจุบัน
                    if(!startMonthSelect.value) startMonthSelect.value = currentMonth;
                    if(!endMonthSelect.value) endMonthSelect.value = currentMonth;
                    if(paymentTypeInput.value > 12) paymentTypeInput.value = 1;
                }
                calculateAmount();
            }

            // ฟังก์ชันจัดการ Logic เดือน (เริ่ม-สิ้นสุด-จำนวนเดือน)
            function updateMonthLogic(source) {
                if (document.getElementById("option_yearly").checked) return;

                let start = parseInt(startMonthSelect.value);
                let end = parseInt(endMonthSelect.value);
                let duration = parseInt(paymentTypeInput.value);

                if (isNaN(start)) return;

                if (source === 'range') {
                    // กรณีเปลี่ยน Start หรือ End
                    if (isNaN(end)) { end = start; endMonthSelect.value = end; }

                    // *** ตรวจสอบ Start ต้องไม่มากกว่า End ***
                    if (start > end) {
                        alert('เดือนเริ่มต้น ต้องไม่มากกว่า เดือนสิ้นสุด');
                        end = start;
                        endMonthSelect.value = end;
                    }

                    // คำนวณจำนวนเดือน
                    duration = end - start + 1;
                    paymentTypeInput.value = duration;

                } else if (source === 'duration') {
                    // กรณีเปลี่ยนจำนวนเดือน
                    if (isNaN(duration) || duration < 1) duration = 1;

                    let newEnd = start + duration - 1;

                    if (newEnd > 12) {
                        alert('ไม่สามารถเลือกข้ามปีได้ กรุณาทำรายการแยกปี');
                        newEnd = 12;
                        duration = 12 - start + 1;
                        paymentTypeInput.value = duration;
                    }
                    endMonthSelect.value = newEnd;
                }
                calculateAmount();
            }

            // --- Bind Events ---
            paymentOptionRadios.forEach(radio => radio.addEventListener("change", updatePaymentLogic));
            periodYearSelect.addEventListener("change", updatePaymentLogic);

            // เมื่อเปลี่ยนเดือน หรือ จำนวนเดือน
            $(startMonthSelect).on('change', function() { updateMonthLogic('range'); });
            $(endMonthSelect).on('change', function() { updateMonthLogic('range'); });
            $(paymentTypeInput).on('input change', function() { updateMonthLogic('duration'); });

            // เมื่อเปลี่ยนค่าส่วนกลาง (จากการดึงบ้านเลขที่)
            $(commonFeeInput).on('input change', calculateAmount);

            // เมื่อเปลี่ยนจำนวนเงินที่รับ
            $("#receive_first").on('input change', calculateChange);

            // --- Init State (ทำงานเมื่อโหลดหน้า) ---
            const currentRealYear = new Date().getFullYear();
            const currentMonth = new Date().getMonth() + 1;

            // ตั้งค่า Payment Method Default
            const selectedMethod = document.querySelector('input[name="payment_method_radio"]:checked');
            if (selectedMethod) document.getElementById('payment_method').value = selectedMethod.value;

            // เช็คโปรโมชั่น
            if (isPromotionPeriod()) {
                setTimeout(function () { $('#promotionModal').modal('show'); }, 500);
                if (currentMonth === 12) $(periodYearSelect).val(currentRealYear + 1);
                else $(periodYearSelect).val(currentRealYear);

                document.getElementById('option_yearly').checked = true;
            }

            // เรียกทำงานครั้งแรก
            updatePaymentLogic();
        });
    </script>

    <script>
        // จัดการดึงข้อมูลบ้าน
        document.addEventListener("DOMContentLoaded", function () {
            const houseNumberInput = document.getElementById("house_number");
            houseNumberInput.addEventListener("blur", handleHouseNumberChange);

            function handleHouseNumberChange() {
                const houseNumber = this.value;
                // Clear old values
                document.getElementById("common_fee").value = '';
                document.getElementById("area_size").value = '';
                document.getElementById("phone_number").value = '';
                document.getElementById("detail").value = '';
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

                                // Trigger ให้คำนวณเงินใหม่
                                $("#common_fee").trigger("change");
                            }
                        })
                        .catch(error => { console.error("เกิดข้อผิดพลาด:", error); });
                }
            }
        });

        // จัดการ Radio วิธีการชำระเงิน
        document.querySelectorAll('input[name="payment_method_radio"]').forEach((radio) => {
            radio.addEventListener('change', function () {
                document.getElementById('payment_method').value = this.value;
            });
        });

        // Preview Image
        $("#picture_payment").change(function() {
            if (this.files && this.files[0]) {
                var reader = new FileReader();
                reader.onload = function(e) {
                    $('#preview_image').attr('src', e.target.result).show();
                }
                reader.readAsDataURL(this.files[0]);
            }
        });
    </script>

    <script>
        // Submit Form
        $(document).ready(function () {
            $("#transfer_form").on("submit", function (event) {
                event.preventDefault();

                const paymentMethod = $("#payment_method").val();
                if (paymentMethod !== "เงินสด" && $("#picture_payment").get(0).files.length === 0) {
                    alertify.error("กรุณาแนบ Slip/ใบโอนเงิน/ใบเสร็จ ก่อนบันทึกข้อมูล");
                    return;
                }

                let period_month_start = parseInt($("#period_month_start").val());
                let period_month_to = parseInt($("#period_month_to").val());
                let period_year = parseInt($("#period_year").val());
                let amount = parseFloat($("#amount").val()) || 0;
                let house_number = document.getElementById("house_number").value;

                if (!house_number || house_number.trim() === "") {
                    alertify.error("กรุณาใส่บ้านเลขที่");
                    return;
                }

                // Double Check Logic (Backend Safety)
                if (period_month_start > period_month_to) {
                    alertify.error("กรุณาตรวจสอบเดือนเริ่มต้นและเดือนสิ้นสุดให้ถูกต้อง");
                    return;
                }

                if (amount <= 0) {
                    alertify.error("จำนวนเงินต้องมากกว่า 0 บาท");
                    return;
                }

                $("#submit_btn").prop("disabled", true);
                $("#loading").show();

                let formData = new FormData(this);
                // Append fields manually to be sure
                formData.append('period_month_start', $("#period_month_start").val());
                formData.append('period_month_to', $("#period_month_to").val());
                formData.append('payment_type', $("#payment_type").val());
                formData.append('payment_method', $("#payment_method").val());
                formData.append('phone_number', $("#phone_number").val());
                formData.append('amount', parseFloat($("#amount").val()).toFixed(2));
                formData.append('receive_first', parseFloat($("#receive_first").val()) || 0);
                formData.append('remark', $("#remark").val());

                $.ajax({
                    url: "model/manage_payment_transfer.php",
                    type: "POST",
                    data: formData,
                    contentType: false,
                    processData: false,
                    success: function (response) {
                        $("#loading").hide();

                        if (response == 1) {
                            alertify.success("บันทึกข้อมูลสำเร็จ");
                            $("#transfer_form")[0].reset();
                            $("#preview_image").hide().attr("src", "");
                            $("#submit_btn").prop("disabled", false); // เปิดปุ่มเพื่อให้ทำรายการต่อได้
                        } else if (response == 2) {
                            const monthNames = ["", "ม.ค.", "ก.พ.", "มี.ค.", "เม.ย.", "พ.ค.", "มิ.ย.", "ก.ค.", "ส.ค.", "ก.ย.", "ต.ค.", "พ.ย.", "ธ.ค."];
                            alertify.error(`งวดเดือน ${monthNames[period_month_start]} ปี ${period_year} มีการชำระแล้ว`);
                            $("#submit_btn").prop("disabled", false);
                        } else {
                            alertify.error("บันทึกไม่สำเร็จ: " + response);
                            $("#submit_btn").prop("disabled", false);
                        }
                    },
                    error: function () {
                        $("#loading").hide();
                        alertify.error("เกิดข้อผิดพลาดในการเชื่อมต่อ");
                        $("#submit_btn").prop("disabled", false);
                    }
                });
            });
        });
    </script>

    </body>
    </html>
<?php } ?>