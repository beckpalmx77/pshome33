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
        function cleanHouseNumber(value) {
            return value.replace(/\s+/g, '').replace(/[^0-9\/]/g, '');
        }

        const houseNumberInput = document.getElementById("house_number");

        houseNumberInput.addEventListener("input", function () { this.value = cleanHouseNumber(this.value); });
        houseNumberInput.addEventListener("change", function () { this.value = cleanHouseNumber(this.value); });
        houseNumberInput.addEventListener("blur", function () { this.value = cleanHouseNumber(this.value); });
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
                    // ถ้าเป็นรายปี ให้ lock เดือนไว้
                    $("#period_month_start").val(1);
                    $("#period_month_to").val(12);
                }
            }

            $("#payment_type, #period_month_start, #option_monthly").on("input change", updatePeriodMonthTo);
        });
    </script>

    <script>
        // ฟังก์ชันเช็คช่วงเวลาโปรโมชั่น
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
            }
            else if (currentMonth === promoEndMonthCurrentYear && currentDay <= promoEndDayCurrentYear) {
                return true;
            }
            return false;
        }

        // ฟังก์ชันคำนวณและจัดการ Logic การชำระเงิน
        function updatePaymentLogic() {
            const isYearly = $("#option_yearly").is(":checked");
            const currentDate = new Date();
            const currentRealYear = currentDate.getFullYear();
            const currentMonth = currentDate.getMonth() + 1;

            const selectedYear = parseInt($("#period_year").val()) || currentRealYear;
            const isPromo = isPromotionPeriod();

            // *** Logic สำคัญ: ตรวจสอบปีที่เข้าเงื่อนไข ***
            let isEligibleYear = false;

            if (isPromo) {
                // กรณี 1: อยู่ในช่วงเดือนธันวาคม (12) -> ต้องเลือกปีหน้า (Next Year) ถึงจะได้ส่วนลด
                if (currentMonth === 12 && selectedYear > currentRealYear) {
                    isEligibleYear = true;
                }
                // กรณี 2: อยู่ในช่วงเดือนมกราคม (1) -> เลือกปีนี้ (Current Year) หรือปีหน้าก็ได้
                else if (currentMonth === 1 && selectedYear >= currentRealYear) {
                    isEligibleYear = true;
                }
            }

            if (isYearly) {
                if (isEligibleYear) {
                    // เข้าเงื่อนไขส่วนลด
                    $("#month_year_calculator").val(11);
                    $("#remark").val("ชำระรายปีล่วงหน้า (ส่วนลด 1 เดือน)");
                } else {
                    // ไม่เข้าเงื่อนไข (เช่น จ่ายย้อนหลัง หรือ จ่ายปีปัจจุบันตอนเดือน ธ.ค. หรือ หมดโปร)
                    $("#month_year_calculator").val(12);

                    if (isPromo && !isEligibleYear) {
                        // แจ้งเตือน: อยู่ในช่วงโปร แต่เลือกปีผิด (เช่น ธ.ค. 2025 แต่เลือกจ่าย 2025)
                        $("#remark").val("ชำระรายปี (ปีปัจจุบัน/ย้อนหลัง - ไม่เข้าเงื่อนไขส่วนลด)");
                    } else {
                        $("#remark").val("-");
                    }
                }

                // Lock fields สำหรับรายปี
                $("#period_month_start").prop("disabled", true).val(1);
                $("#period_month_to").prop("disabled", true).val(12);
                $("#payment_type").prop("disabled", true).val(12);

            } else {
                // กรณีเลือกจ่ายรายเดือน
                $("#remark").val("-");
                $("#period_month_start").prop("disabled", false);
                $("#period_month_to").prop("disabled", false);
                $("#payment_type").prop("disabled", false).val(1);
            }

            // คำนวณยอดเงินทันที
            calculateAmount();
        }

        function calculateAmount() {
            const commonFee = parseFloat($("#common_fee").val()) || 0;
            const isYearly = $("#option_yearly").is(":checked");

            if (isYearly) {
                if (commonFee > 0) {
                    $("#amount").val((commonFee * parseFloat($("#month_year_calculator").val())).toFixed(2));
                } else {
                    $("#amount").val('');
                }
            } else {
                const months = parseInt($("#payment_type").val()) || 0;
                if (commonFee > 0 && months > 0) {
                    const amount = commonFee * months;
                    $("#amount").val(amount.toFixed(2));
                } else {
                    $("#amount").val('');
                }
            }
        }

        $(document).ready(function () {
            // เมื่อมีการเปลี่ยนข้อมูลในฟอร์ม ให้เรียก updatePaymentLogic
            $("input[name='payment_option']").on("change", updatePaymentLogic);
            $("#period_year").on("change", updatePaymentLogic); // ดักจับการเปลี่ยนปี

            $("#common_fee, #payment_type").on("input change", calculateAmount);
        });

        // ทำงานเมื่อโหลดหน้าเว็บ
        document.addEventListener('DOMContentLoaded', function () {
            const currentDate = new Date();
            const currentRealYear = currentDate.getFullYear();
            const currentMonth = currentDate.getMonth() + 1;

            // เช็คโปรโมชั่นเพื่อตั้งค่าเริ่มต้น
            if (isPromotionPeriod()) {
                setTimeout(function () { $('#promotionModal').modal('show'); }, 500);

                // *** Logic เลือกปีเริ่มต้นให้อัตโนมัติ ***
                if (currentMonth === 12) {
                    // ถ้าเป็น ธ.ค. ให้เลือกปีหน้า
                    $("#period_year").val(currentRealYear + 1);
                } else {
                    // ถ้าเป็น ม.ค. ให้เลือกปีปัจจุบัน
                    $("#period_year").val(currentRealYear);
                }

                // เลือกรายปีเป็นค่าเริ่มต้น
                document.getElementById('option_yearly').checked = true;
            }

            // เรียก Logic ครั้งแรก
            updatePaymentLogic();
        });
    </script>

    <script>
        document.addEventListener("DOMContentLoaded", function () {
            const houseNumberInput = document.getElementById("house_number");
            houseNumberInput.addEventListener("blur", handleHouseNumberChange);

            function handleHouseNumberChange() {
                const houseNumber = this.value;
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

                                // เมื่อได้ค่าส่วนกลางมาใหม่ ให้คำนวณเงินใหม่โดยอิงตาม Logic ปีที่เลือกอยู่
                                updatePaymentLogic();
                            }
                        })
                        .catch(error => {
                            console.error("เกิดข้อผิดพลาด:", error);
                        });
                }
            }
        });
    </script>

    <script>
        // Payment Method Radio Logic
        document.querySelectorAll('input[name="payment_method_radio"]').forEach((radio) => {
            radio.addEventListener('change', function () {
                document.getElementById('payment_method').value = this.value;
            });
        });

        document.addEventListener('DOMContentLoaded', function () {
            const selected = document.querySelector('input[name="payment_method_radio"]:checked');
            if (selected) {
                document.getElementById('payment_method').value = selected.value;
            }
        });
    </script>

    <script>
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

                if (house_number === null || house_number.trim() === "") {
                    alertify.error("กรุณาใส่บ้านเลขที่");
                    return;
                }

                const monthNames = ["", "มกราคม", "กุมภาพันธ์", "มีนาคม", "เมษายน", "พฤษภาคม", "มิถุนายน", "กรกฎาคม", "สิงหาคม", "กันยายน", "ตุลาคม", "พฤศจิกายน", "ธันวาคม"];
                let period_month_start_name = monthNames[period_month_start];
                let period_month_to_name = monthNames[period_month_to];

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
                formData.append('period_month_start', $("#period_month_start").val());
                formData.append('period_month_to', $("#period_month_to").val());
                formData.append('payment_type', $("#payment_type").val());
                formData.append('payment_method', $("#payment_method").val());
                formData.append('phone_number', $("#phone_number").val());
                formData.append('amount', parseFloat($("#amount").val()).toFixed(2));
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
                            alertify.success("บันทึกข้อมูลการชำระเงินและส่ง Slip สำเร็จ");
                            $("#transfer_form")[0].reset();
                            $("#preview_image").hide().attr("src", "");
                            $("#submit_btn").prop("disabled", true);
                        } else if (response == 2) {
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
            const startMonthSelect = document.getElementById('period_month_start');
            const endMonthSelect = document.getElementById('period_month_to');
            const paymentTypeInput = document.getElementById('payment_type');

            function calculateAndSetMonths() {
                const startMonth = parseInt(startMonthSelect.value);
                const endMonth = parseInt(endMonthSelect.value);

                if (startMonth && endMonth) {
                    if (startMonth > endMonth) {
                        alert('ต้องเลือกเดือน "เริ่มงวด" ให้น้อยกว่าหรือเท่ากับเดือน "ถึงงวด" กรุณาเลือกใหม่ให้ถูกต้อง');
                        paymentTypeInput.value = 1;
                        return;
                    }
                    const numberOfMonths = endMonth - startMonth + 1;
                    paymentTypeInput.value = numberOfMonths;
                } else {
                    paymentTypeInput.value = 1;
                }
            }

            startMonthSelect.addEventListener('change', calculateAndSetMonths);
            endMonthSelect.addEventListener('change', calculateAndSetMonths);
        });
    </script>

    </body>
    </html>

<?php } ?>