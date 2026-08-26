<?php
$curr_date = date("d-m-Y");
include('includes/Header.php');
include('config/connect_db.php');

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

// กำหนดค่า Default ป้องกัน Error กรณีไม่มีตัวแปร
$house_number = isset($house_number) ? $house_number : '';
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <style>
        .month-status-grid {
            display: grid;
            grid-template-columns: repeat(6, 1fr);
            gap: 6px;
            margin: 10px 0;
        }
        @media (min-width: 768px) {
            .month-status-grid {
                grid-template-columns: repeat(12, 1fr);
            }
        }
        .m-status-box {
            text-align: center;
            padding: 6px 2px;
            border-radius: 6px;
            font-size: 11px;
            font-weight: 600;
            border: 1px solid #e2e8f0;
            background: #f8fafc;
            color: #64748b;
            transition: all 0.2s ease;
        }
        .m-status-box.paid {
            background: #dcfce7 !important;
            border-color: #86efac !important;
            color: #166534 !important;
        }
        .m-status-box.pending {
            background: #fef3c7 !important;
            border-color: #fcd34d !important;
            color: #92400e !important;
        }
        .m-status-box.selecting {
            background: #eff6ff;
            border-color: #3b82f6;
            color: #1d4ed8;
            box-shadow: 0 0 0 2px rgba(59, 130, 246, 0.2);
        }
        .m-status-box.conflict {
            background: #fee2e2 !important;
            border-color: #ef4444 !important;
            color: #b91c1c !important;
            animation: pulseWarning 1.5s infinite;
        }
        @keyframes pulseWarning {
            0%, 100% { opacity: 1; transform: scale(1); }
            50% { opacity: 0.85; transform: scale(0.98); }
        }
        .month-legend {
            display: flex;
            flex-wrap: wrap;
            gap: 12px;
            font-size: 12px;
            margin-top: 6px;
            justify-content: flex-end;
        }
        .legend-item {
            display: flex;
            align-items: center;
            gap: 4px;
        }
        .legend-dot {
            width: 10px;
            height: 10px;
            border-radius: 3px;
            display: inline-block;
        }
        .dot-paid { background: #22c55e; }
        .dot-pending { background: #f59e0b; }
        .dot-available { background: #cbd5e1; }
        .dot-conflict { background: #ef4444; }
    </style>
</head>

<body id="page-top">
<div id="wrapper">
    <div id="content-wrapper" class="d-flex flex-column">
        <div id="content">
            <div class="container py-4">
                <div class="d-sm-flex align-items-center justify-content-between mb-4">
                    <h1 class="h5 mb-0 text-gray-800">ชำระค่าส่วนกลาง</h1>
                    <br>
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
                                                           readonly="true"
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
                                                           value="1" min="1" max="12">
                                                </div>
                                            </div>
                                            <input type="hidden" id="month_year_calculator" name="month_year_calculator"
                                                   value="12">
                                        </div>
                                    </div>

                                    <div class="form-group row align-items-end">
                                        <div class="col-md-4">
                                            <label for="period_month_start">เริ่มงวดเดือน (เลือกเดือนที่ชำระ)</label>
                                            <select name="period_month_start" id="period_month_start"
                                                    class="form-control" style="background-color: #FFDAB9;" required>
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
                                                $startYear = $currentYear - 10; // ย้อนหลัง 10 ปีพอ
                                                $endYear = $currentYear + 2;  // ล่วงหน้า 2 ปี

                                                for ($year = $startYear; $year <= $endYear; $year++) {
                                                    $selected = ($year == $currentYear) ? 'selected' : '';
                                                    echo "<option value='$year' $selected>$year</option>";
                                                }
                                                ?>
                                            </select>
                                        </div>
                                    </div>

                                    <!-- กล่องแสดงสถานะการชำระ 12 เดือน และแจ้งเตือนข้อมูลซ้ำซ้อน (Real-time Overlap Check) -->
                                    <div id="month_status_wrapper" class="mb-3 p-3 bg-light rounded border" style="display: none;">
                                        <div class="d-flex justify-content-between align-items-center mb-1">
                                            <small class="font-weight-bold text-gray-700">
                                                <i class="fa fa-calendar-check-o text-primary"></i> ประวัติการชำระค่าส่วนกลาง ปี <span id="lbl_status_year"></span>
                                            </small>
                                            <span id="lbl_house_target" class="badge badge-info text-white"></span>
                                        </div>
                                        <div id="month_status_grid" class="month-status-grid">
                                            <!-- 12 กล่องเดือนจะถูก render ผ่าน JavaScript -->
                                        </div>
                                        <div class="month-legend text-muted">
                                            <span class="legend-item"><span class="legend-dot dot-paid"></span> ชำระแล้ว</span>
                                            <span class="legend-item"><span class="legend-dot dot-pending"></span> รอตรวจสอบ</span>
                                            <span class="legend-item"><span class="legend-dot dot-available"></span> ว่าง</span>
                                            <span class="legend-item"><span class="legend-dot dot-conflict"></span> ทับซ้อน (ซ้ำ)</span>
                                        </div>
                                        <div id="overlap_warning_box" class="mt-2" style="display: none;"></div>
                                    </div>

                                    <div class="form-group has-success">
                                        <div class="row">
                                            <div class="col-md-6">
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
                                                <div class="form-group has-success">
                                                    <label for="common_fee"
                                                           class="control-label">ค่าส่วนกลางรายเดือน (บาท)</label>
                                                    <input type="number" name="common_fee" class="form-control"
                                                           id="common_fee" readonly="true" step="0.01">
                                                </div>
                                            </div>


                                            <div class="col-md-6">
                                                <div class="form-group has-success">
                                                    <label for="amount"
                                                           class="control-label">จำนวนเงินที่ชำระ (บาท)</label>
                                                    <input type="number" name="amount" class="form-control"
                                                           required id="amount" step="0.01">
                                                </div>
                                            </div>
                                        </div>
                                    </div>


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

                <div class="modal fade" id="promotionModal" tabindex="-1" role="dialog"
                     aria-labelledby="promotionModalLabel"
                     aria-hidden="true">
                    <div class="modal-dialog modal-dialog-centered" role="document">
                        <div class="modal-content">
                            <div class="modal-header bg-success text-white">
                                <h5 class="modal-title" id="promotionModalLabel">📣 โปรโมชั่นสำหรับสมาชิก</h5>
                                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                                    <span aria-hidden="true">&times;</span>
                                </button>
                            </div>
                            <div class="modal-body text-center">
                                <p class="h5 text-success">ชำระค่าส่วนกลางรายปีล่วงหน้า</p>
                                <p class="h6 text-primary">รับส่วนลดทันที 1 เดือน (ชำระเพียง 11 เดือน)</p>
                                <p class="h6 text-danger">สำหรับการชำระของปีถัดไปเท่านั้น</p>
                                <img src="img/promotion_banner.png" alt="Promotion Banner" class="img-fluid mt-3"
                                     style="max-width: 50%; height: auto;">
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
        background-color: rgba(230, 255, 240, 0.95);
        padding: 1rem;
        margin: 0.5rem;
        border-radius: 16px;
        box-shadow: 0 4px 12px rgba(144, 238, 144, 0.3);
    }

    .card {
        border: none;
        border-radius: 12px;
        background-color: #eafff0;
        box-shadow: 0 0 10px rgba(152, 251, 152, 0.3);
        margin-bottom: 1rem;
    }

    label, .form-check-label {
        font-weight: 500;
        color: #2e7d32;
    }

    .form-control {
        border-radius: 10px;
        border: 1px solid #a8d5ba;
        padding: 0.5rem 0.75rem;
        background-color: #ffffff;
        box-shadow: inset 0 1px 3px rgba(0, 0, 0, 0.05);
    }

    .btn-primary {
        background-color: #90ee90;
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
        return value.replace(/\s+/g, '').replace(/[^0-9\/]/g, '');
    }

    const houseNumberInput = document.getElementById("house_number");
    houseNumberInput.addEventListener("input", function () { this.value = cleanHouseNumber(this.value); });
    houseNumberInput.addEventListener("change", function () { this.value = cleanHouseNumber(this.value); });
    houseNumberInput.addEventListener("blur", function () { this.value = cleanHouseNumber(this.value); });
</script>


<script>
    // 1. ฟังก์ชันเช็คช่วงเวลาโปรโมชั่น (15 ธ.ค. - 31 ม.ค.)
    function isPromotionPeriod() {
        const currentDate = new Date();
        const currentMonth = currentDate.getMonth() + 1; // 1-12
        const currentDay = currentDate.getDate();

        // เงื่อนไขโปรโมชั่น: เริ่ม 15 ธ.ค. ถึง 31 ม.ค.
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

    // 2. ฟังก์ชันหลักสำหรับ Logic การคำนวณเงินและส่วนลด
    document.addEventListener('DOMContentLoaded', function () {
        const paymentTypeInput = document.getElementById('payment_type');
        const commonFeeInput = document.getElementById('common_fee');
        const amountInput = document.getElementById('amount');
        const paymentOptionInputs = document.querySelectorAll('input[name="payment_option"]');
        const periodYearInput = document.getElementById('period_year');
        const monthYearCalculatorInput = document.getElementById('month_year_calculator');
        const remarkInput = document.getElementById('remark');

        function calculateAmount() {
            const commonFee = parseFloat(commonFeeInput.value) || 0;
            const isYearly = document.getElementById('option_yearly').checked;
            let calculatedAmount = 0;

            if (!isYearly) {
                const paymentMonths = parseInt(paymentTypeInput.value) || 0;
                calculatedAmount = commonFee * paymentMonths;
            } else {
                const monthsToCharge = parseFloat(monthYearCalculatorInput.value) || 12;
                calculatedAmount = commonFee * monthsToCharge;
            }

            amountInput.value = calculatedAmount.toFixed(2);
        }

        // --- ฟังก์ชันสำคัญ: จัดการเงื่อนไขโปรโมชั่นและการ Lock ปี ---
        function updatePaymentLogic() {
            const isYearly = document.getElementById('option_yearly').checked;
            const currentDate = new Date();
            const currentRealYear = currentDate.getFullYear();
            const currentMonth = currentDate.getMonth() + 1;

            const selectedYear = parseInt(periodYearInput.value) || currentRealYear;
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
                    $(remarkInput).val("ชำระรายปีล่วงหน้า (ส่วนลด 1 เดือน)");
                } else {
                    // ไม่เข้าเงื่อนไข (เช่น จ่ายย้อนหลัง หรือ จ่ายปีปัจจุบันตอนเดือน ธ.ค. หรือ หมดโปร)
                    $("#month_year_calculator").val(12);

                    if (isPromo && !isEligibleYear) {
                        // แจ้งเตือน: อยู่ในช่วงโปร แต่เลือกปีผิด (เช่น ธ.ค. 2025 แต่เลือกจ่าย 2025)
                        $(remarkInput).val("ชำระรายปี (ปีปัจจุบัน/ย้อนหลัง - ไม่เข้าเงื่อนไขส่วนลด)");
                    } else {
                        $(remarkInput).val("-");
                    }
                }

                // Lock fields สำหรับรายปี
                $("#period_month_start").prop("disabled", true).val(1);
                $("#period_month_to").prop("disabled", true).val(12);
                $("#payment_type").prop("disabled", true).val(12);

                amountInput.readOnly = false;
                calculateAmount();

            } else {
                // กรณีเลือกจ่ายรายเดือน
                $(remarkInput).val("-");
                $("#period_month_start").prop("disabled", false);
                $("#period_month_to").prop("disabled", false);
                $("#payment_type").prop("disabled", false).val(1);

                // รีเซ็ตค่าเดือนเริ่ม/สิ้นสุดเป็นเดือนปัจจุบันเมื่อสลับกลับมา
                let thisMonth = new Date().getMonth() + 1;
                if(!$("#period_month_start").val()) $("#period_month_start").val(thisMonth);
                if(!$("#period_month_to").val()) $("#period_month_to").val(thisMonth);

                amountInput.readOnly = true;
                calculateAmount();
            }

            if (typeof window.checkPaymentOverlapRealtime === 'function') {
                window.checkPaymentOverlapRealtime();
            }
        }

        // Event Listeners
        paymentTypeInput.addEventListener('input', calculateAmount);
        commonFeeInput.addEventListener('input', () => {
            if (commonFeeInput.value !== '') {
                commonFeeInput.value = parseFloat(commonFeeInput.value).toFixed(2);
            }
            calculateAmount();
        });

        paymentOptionInputs.forEach(el =>
            el.addEventListener('change', updatePaymentLogic)
        );

        // *** เมื่อเปลี่ยนปี (Dropdown) ให้คำนวณโปรโมชั่นใหม่ทันที ***
        periodYearInput.addEventListener('change', updatePaymentLogic);

        monthYearCalculatorInput.addEventListener('change', calculateAmount);

        // ฟังก์ชัน Init (เรียกใช้หลังจาก LIFF ดึงข้อมูลเสร็จ)
        window.initWithCommonFeeInput = function () {
            const currentDate = new Date();
            const currentRealYear = currentDate.getFullYear();
            const currentMonth = currentDate.getMonth() + 1;

            if (isPromotionPeriod()) {
                // ถ้ามีโปร ให้เลือกรายปีไว้ก่อน
                document.getElementById('option_yearly').checked = true;

                // *** Logic เลือกปีเริ่มต้นให้อัตโนมัติ ***
                if (currentMonth === 12) {
                    // ถ้าเป็น ธ.ค. ให้เลือกปีหน้า
                    $("#period_year").val(currentRealYear + 1);
                } else {
                    // ถ้าเป็น ม.ค. ให้เลือกปีปัจจุบัน
                    $("#period_year").val(currentRealYear);
                }

                setTimeout(function () { $('#promotionModal').modal('show'); }, 500);

                // เรียกคำนวณทันที
                updatePaymentLogic();
            } else {
                // ถ้าไม่มีโปร ให้เลือกรายเดือนปกติ
                document.querySelector('input[name="payment_option"][value="monthly"]').checked = true;
                updatePaymentLogic();
            }

            if (typeof window.checkPaymentOverlapRealtime === 'function') {
                window.checkPaymentOverlapRealtime();
            }
        };
    });
</script>

<script>
    // ***************************************************************
    // ส่วนจัดการ Logic เดือนและจำนวนเงิน (แก้ไขให้ไม่ตีกันและตรวจสอบเงื่อนไข)
    // ***************************************************************
    $(document).ready(function () {
        const startSelect = $("#period_month_start");
        const endSelect = $("#period_month_to");
        const typeInput = $("#payment_type");
        const monthlyOption = $("#option_monthly");

        // ฟังก์ชันหลัก: คำนวณและตรวจสอบ
        function updateMonthLogic(source) {
            // ถ้าเลือกเป็นรายปี ไม่ต้องทำอะไร
            if (!monthlyOption.is(":checked")) return;

            let start = parseInt(startSelect.val());
            let end = parseInt(endSelect.val());
            let duration = parseInt(typeInput.val());

            if (isNaN(start)) return; // ถ้ายังไม่ได้เลือกเดือนเริ่ม ก็ทำอะไรไม่ได้

            if (source === 'range') {
                // 1. กรณีผู้ใช้เปลี่ยน "เดือนเริ่ม" หรือ "เดือนสิ้นสุด" -> คำนวณ "จำนวนเดือน"
                if (isNaN(end)) {
                    // ถ้าเดือนจบว่าง ให้ตั้งค่าเท่ากับเดือนเริ่ม
                    end = start;
                    endSelect.val(end);
                }

                // *** ตรวจสอบเงื่อนไข Start ต้องไม่มากกว่า End ***
                if (start > end) {
                    alert('เดือนเริ่มต้น ต้องไม่มากกว่า เดือนสิ้นสุด');
                    // Reset เดือนสิ้นสุดให้เท่ากับเดือนเริ่มต้น
                    end = start;
                    endSelect.val(end);
                }

                // คำนวณจำนวนเดือน
                let newDuration = end - start + 1;
                typeInput.val(newDuration);

            } else if (source === 'duration') {
                // 2. กรณีผู้ใช้เปลี่ยน "จำนวนเดือน" -> คำนวณ "เดือนสิ้นสุด"
                if (isNaN(duration) || duration < 1) {
                    duration = 1; // บังคับขั้นต่ำ 1 เดือน
                }

                let newEnd = start + duration - 1;

                // ถ้าเกิน 12 (ธันวาคม) ให้ปัดกลับมาเป็น 12 และปรับ duration (เพราะจ่ายข้ามปีไม่ได้ในระบบนี้)
                if (newEnd > 12) {
                    newEnd = 12;
                    duration = 12 - start + 1;
                    typeInput.val(duration); // ปรับค่าใน Input ให้ตรงความจริง
                    alert('ไม่สามารถเลือกข้ามปีได้ กรุณาทำรายการแยกปี');
                }

                endSelect.val(newEnd);
            }

            // Trigger ให้ calculateAmount ทำงานด้วย (เพื่ออัพเดทตัวเงิน)
            typeInput.trigger('input');
        }

        // Bind Event Handlers
        startSelect.on('change', function() {
            updateMonthLogic('range');
            window.checkPaymentOverlapRealtime();
        });
        endSelect.on('change', function() {
            updateMonthLogic('range');
            window.checkPaymentOverlapRealtime();
        });

        typeInput.on('input change', function() {
            // หน่วงเวลาเล็กน้อยเพื่อให้ user พิมพ์เสร็จ (กรณีพิมพ์เลข)
            updateMonthLogic('duration');
            window.checkPaymentOverlapRealtime();
        });

        $("#period_year, input[name='payment_option']").on('change', function() {
            window.checkPaymentOverlapRealtime();
        });
    });

    // --- ฟังก์ชันตรวจสอบความซ้ำซ้อนและแสดงสถานะ 12 เดือนแบบ Real-time (ตาม check_payment.html) ---
    window.checkPaymentOverlapRealtime = function() {
        let houseNumber = $("#house_number").val();
        let periodYear = parseInt($("#period_year").val()) || new Date().getFullYear();
        let isYearly = $("#option_yearly").is(":checked");
        let startMonth = isYearly ? 1 : (parseInt($("#period_month_start").val()) || 0);
        let endMonth = isYearly ? 12 : (parseInt($("#period_month_to").val()) || startMonth);

        if (!houseNumber || houseNumber.trim() === '') {
            $("#month_status_wrapper").hide();
            return;
        }

        $("#lbl_status_year").text(periodYear);
        $("#lbl_house_target").text('บ้านเลขที่ ' + houseNumber);
        $("#month_status_wrapper").slideDown(200);

        $.ajax({
            url: "model/check_payment_overlap.php",
            type: "POST",
            data: {
                house_number: houseNumber,
                period_year: periodYear,
                period_month_start: startMonth,
                period_month_to: endMonth
            },
            dataType: "json",
            success: function(res) {
                if (res && res.status === 'success') {
                    renderMonthGrid(res.months_status, startMonth, endMonth, res.has_overlap);

                    if (res.has_overlap) {
                        let alertClass = (res.overlap_type === 'paid') ? 'alert-danger' : 'alert-warning';
                        let iconClass = (res.overlap_type === 'paid') ? 'fa-ban' : 'fa-clock-o';
                        $("#overlap_warning_box").html(
                            `<div class="alert ${alertClass} mb-0 p-2" style="font-size: 13px;">
                                <i class="fa ${iconClass} mr-1"></i> <strong>แจ้งเตือน:</strong> ${res.overlap_message}
                            </div>`
                        ).slideDown();

                        $("#submit_btn").prop("disabled", true);
                    } else {
                        $("#overlap_warning_box").slideUp();
                        $("#submit_btn").prop("disabled", false);
                    }
                }
            },
            error: function(xhr, err) {
                console.error("Check overlap error:", err);
            }
        });
    };

    function renderMonthGrid(monthsStatus, selStart, selEnd, hasOverlap) {
        if (!monthsStatus || !Array.isArray(monthsStatus)) return;
        let gridHtml = '';
        const shortNames = ['', 'ม.ค.', 'ก.พ.', 'มี.ค.', 'เม.ย.', 'พ.ค.', 'มิ.ย.', 'ก.ค.', 'ส.ค.', 'ก.ย.', 'ต.ค.', 'พ.ย.', 'ธ.ค.'];

        monthsStatus.forEach(function(m) {
            let isSelected = (selStart > 0 && selEnd > 0 && m.month >= selStart && m.month <= selEnd);
            let statusClass = m.status; // 'paid', 'pending', 'available'
            let iconHtml = '';

            if (m.status === 'paid') {
                iconHtml = '<i class="fa fa-check text-success"></i> ';
            } else if (m.status === 'pending') {
                iconHtml = '<i class="fa fa-clock-o text-warning"></i> ';
            }

            let conflictClass = '';
            if (isSelected && (m.status === 'paid' || m.status === 'pending')) {
                conflictClass = 'conflict';
            } else if (isSelected) {
                conflictClass = 'selecting';
            }

            let docInfo = m.doc_id ? ` (${m.doc_id})` : '';
            gridHtml += `
                <div class="m-status-box ${statusClass} ${conflictClass}" title="${m.month_name} : ${m.status_text}${docInfo}">
                    <div>${shortNames[m.month]}</div>
                    <div style="font-size: 9px; margin-top: 2px;">${iconHtml}${m.status_text}</div>
                </div>
            `;
        });

        $("#month_status_grid").html(gridHtml);
    }
</script>

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

                                document.getElementById('common_fee').value = parseFloat(data.common_fee).toFixed(2);

                                // เรียกฟังก์ชัน Init เพื่อเช็คโปรโมชั่นและตั้งค่าเริ่มต้น
                                window.initWithCommonFeeInput();

                                document.getElementById('user-info-liff2').innerText = `ชื่อ : ${data.f_name} ${data.l_name}`;
                                $('#profilePic').attr('src', profile.pictureUrl || "../img/user-001.png");

                            } else {
                                alert('ไม่พบผู้ใช้งานในระบบ กรุณาลงทะเบียนก่อน');
                                liff.closeWindow();
                            }
                        })
                        .catch(error => {
                            console.error('เกิดข้อผิดพลาด:', error);
                            alert('เกิดข้อผิดพลาดในการเชื่อมต่อกับเซิร์ฟเวอร์');
                            liff.closeWindow();
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

                const reader = new FileReader();
                reader.onload = function (e) {
                    $("#preview_image").attr("src", e.target.result).show();
                };
                reader.readAsDataURL(newFile);

                const dataTransfer = new DataTransfer();
                dataTransfer.items.add(newFile);
                fileInput.files = dataTransfer.files;

            } catch (err) {
                alert("เกิดข้อผิดพลาดขณะพยายามแปลงไฟล์ HEIC: " + err.message);
                fileInput.value = "";
            }

        } else {
            const reader = new FileReader();
            reader.onload = function (e) {
                $("#preview_image").attr("src", e.target.result).show();
            };
            reader.readAsDataURL(file);
        }
    });
</script>

<script>
    $(document).ready(function () {
        $("#transfer_form").on("submit", function (event) {
            event.preventDefault();

            if ($("#picture_payment").get(0).files.length === 0) {
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

            $("#submit_btn").prop("disabled", true);
            $("#loading").show();

            let formData = new FormData(this);
            formData.append('period_month_start', $("#period_month_start").val());
            formData.append('period_month_to', $("#period_month_to").val());
            formData.append('payment_type', $("#payment_type").val());
            formData.append('amount', parseFloat($("#amount").val()).toFixed(2));
            formData.append('remark', $("#remark").val());


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
                                const flexMessage = {
                                    "type": "flex",
                                    "altText": "แจ้งการโอนเงินเรียบร้อยแล้ว",
                                    "contents": {
                                        "type": "bubble",
                                        "body": {
                                            "type": "box",
                                            "layout": "vertical",
                                            "spacing": "md",
                                            "contents": [
                                                {
                                                    "type": "image",
                                                    "url": "https://ps33home.com/img/logo/niti_ps33_header200.png",
                                                    "size": "sm",
                                                    "aspectRatio": "200:85",
                                                    "aspectMode": "fit",
                                                    "gravity": "center",
                                                    "margin": "none"
                                                },
                                                {
                                                    "type": "text",
                                                    "text": "แจ้งการโอนเงิน",
                                                    "weight": "bold",
                                                    "size": "xl",
                                                    "color": "#000000",
                                                    "align": "center",
                                                    "margin": "lg"
                                                },
                                                {
                                                    "type": "box",
                                                    "layout": "vertical",
                                                    "margin": "lg",
                                                    "spacing": "sm",
                                                    "contents": [
                                                        {
                                                            "type": "box",
                                                            "layout": "baseline",
                                                            "spacing": "sm",
                                                            "contents": [
                                                                {
                                                                    "type": "text",
                                                                    "text": "บ้านเลขที่:",
                                                                    "color": "#aaaaaa",
                                                                    "size": "sm",
                                                                    "flex": 4
                                                                },
                                                                {
                                                                    "type": "text",
                                                                    "text": `${house_number}`,
                                                                    "wrap": true,
                                                                    "color": "#666666",
                                                                    "size": "sm",
                                                                    "flex": 5
                                                                }
                                                            ]
                                                        },
                                                        {
                                                            "type": "box",
                                                            "layout": "baseline",
                                                            "spacing": "sm",
                                                            "contents": [
                                                                {
                                                                    "type": "text",
                                                                    "text": "จำนวนเงิน:",
                                                                    "color": "#aaaaaa",
                                                                    "size": "sm",
                                                                    "flex": 4
                                                                },
                                                                {
                                                                    "type": "text",
                                                                    "text": `${amount} บาท`,
                                                                    "wrap": true,
                                                                    "color": "#009933",
                                                                    "size": "sm",
                                                                    "flex": 5,
                                                                    "weight": "bold"
                                                                }
                                                            ]
                                                        },
                                                        {
                                                            "type": "box",
                                                            "layout": "baseline",
                                                            "spacing": "sm",
                                                            "contents": [
                                                                {
                                                                    "type": "text",
                                                                    "text": "ช่วงเดือน:",
                                                                    "color": "#aaaaaa",
                                                                    "size": "sm",
                                                                    "flex": 4
                                                                },
                                                                {
                                                                    "type": "text",
                                                                    "text": `${period_month_start_name} - ${period_month_to_name} ${period_year}`,
                                                                    "wrap": true,
                                                                    "color": "#666666",
                                                                    "size": "sm",
                                                                    "flex": 5
                                                                }
                                                            ]
                                                        },
                                                        {
                                                            "type": "box",
                                                            "layout": "baseline",
                                                            "spacing": "sm",
                                                            "contents": [
                                                                {
                                                                    "type": "text",
                                                                    "text": "วันที่รายการ:",
                                                                    "color": "#aaaaaa",
                                                                    "size": "sm",
                                                                    "flex": 4
                                                                },
                                                                {
                                                                    "type": "text",
                                                                    "text": `${date_time}`,
                                                                    "wrap": true,
                                                                    "color": "#666666",
                                                                    "size": "sm",
                                                                    "flex": 5
                                                                }
                                                            ]
                                                        }
                                                    ]
                                                }
                                            ]
                                        },
                                        "footer": {
                                            "type": "box",
                                            "layout": "vertical",
                                            "spacing": "sm",
                                            "contents": [
                                                {
                                                    "type": "button",
                                                    "style": "primary",
                                                    "color": "#1DB954",
                                                    "height": "sm",
                                                    "action": {
                                                        "type": "uri",
                                                        "label": "ดูประวัติการชำระ",
                                                        "uri": "https://liff.line.me/2007370141-13Wzad0L"
                                                    }
                                                }
                                            ],
                                            "flex": 0
                                        }
                                    }
                                };

                                liff.sendMessages([flexMessage])
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
                            alertify.error("ไม่ได้เปิดใน LINE App (ข้อความจะไม่ถูกส่ง)");
                        }
                    } else {
                        let resObj = null;
                        try {
                            resObj = (typeof response === 'object') ? response : JSON.parse(response);
                        } catch (e) {}

                        if (response == 2 || (resObj && (resObj.status === 'duplicate' || resObj.code == 2))) {
                            let msg = (resObj && resObj.message) ? resObj.message : `มีข้อมูลการชำระค่าส่วนกลางงวดเดือน ${period_month_start_name} ปี ${period_year} แล้ว ไม่สามารถบันทึกได้`;
                            alertify.error(msg);
                            $("#submit_btn").prop("disabled", true);
                            if (typeof window.checkPaymentOverlapRealtime === 'function') {
                                window.checkPaymentOverlapRealtime();
                            }
                        } else {
                            let msg = (resObj && resObj.message) ? resObj.message : response;
                            alertify.error("ไม่สามารถบันทึกข้อมูลได้: " + msg);
                            $("#submit_btn").prop("disabled", false);
                        }
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