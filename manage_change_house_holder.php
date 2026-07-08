<?php
include('includes/Header.php');
include('config/connect_db.php');
if (empty($_SESSION['alogin'])) {
    header("Location: index.php");
    exit;
}
// ดึงรายชื่อบ้านทั้งหมดจาก ims_house_master พร้อมชื่อผู้อยู่อาศัยปัจจุบัน (ถ้ามี)
$sql_houses = "SELECT hm.house_number, h.contact_name 
               FROM ims_house_master hm 
               LEFT JOIN ims_house h ON hm.house_number = h.house_number 
               WHERE hm.status = 'Y' 
               ORDER BY CAST(hm.house_number AS UNSIGNED), hm.house_number";
$stmt_houses = $conn->prepare($sql_houses);
$stmt_houses->execute();
$houses = $stmt_houses->fetchAll(PDO::FETCH_ASSOC);

// รับค่าเลขที่บ้านเริ่มต้นจาก URL (ถ้ามี)
$preset_house = $_GET['house_number'] ?? '';
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <style>
        .ui-autocomplete {
            z-index: 2147483647 !important;
            border-radius: 6px !important;
            box-shadow: 0 4px 12px rgba(0,0,0,0.15) !important;
            border: 1px solid #d1d3e2 !important;
            background: white !important;
            padding: 5px 0 !important;
            max-height: 250px;
            overflow-y: auto;
            overflow-x: hidden;
        }
        .ui-menu-item {
            padding: 6px 12px !important;
            cursor: pointer;
            list-style: none;
        }
        .ui-menu-item:hover, .ui-state-active {
            background-color: #4e73df !important;
            color: white !important;
            border: none !important;
        }
    </style>
</head>
<body id="page-top">
<div id="wrapper">
    <!-- Sidebar -->
    <?php include('includes/Side-Bar.php'); ?>

    <!-- Content Wrapper -->
    <div id="content-wrapper" class="d-flex flex-column">
        <div id="content">
            <!-- Topbar -->
            <?php include('includes/Top-Bar.php'); ?>

            <!-- Container Fluid -->
            <div class="container-fluid" id="container-wrapper">
                <div class="d-sm-flex align-items-center justify-content-between mb-4">
                    <h1 class="h3 mb-0 text-gray-800">จัดการการเปลี่ยนสิทธิ์และย้ายผู้อยู่อาศัย</h1>
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="<?php echo $_SESSION['dashboard_page'] ?>">Home</a></li>
                        <li class="breadcrumb-item">ข้อมูลสมาชิก/ลูกบ้าน</li>
                        <li class="breadcrumb-item active" aria-current="page">เปลี่ยนสิทธิ์ผู้อยู่อาศัย</li>
                    </ol>
                </div>

                <div class="row">
                    <div class="col-lg-12">
                        <div class="card mb-4">
                            <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between bg-primary text-white">
                                <h6 class="m-0 font-weight-bold"><i class="fa fa-exchange"></i> ฟอร์มเปลี่ยนสิทธิ์ผู้อยู่อาศัย / เปลี่ยนผู้เช่า / เปลี่ยนเจ้าของบ้าน</h6>
                                <a href="how_to_change_data.html" target="_blank" class="btn btn-sm btn-light font-weight-bold text-primary shadow-sm"><i class="fa fa-book"></i> คู่มือการใช้งาน</a>
                            </div>
                            <div class="card-body">
                                <form id="formChangeHolder">
                                    <div class="row">
                                        <!-- Left Side: Inputs & Setup -->
                                        <div class="col-md-7 border-right">
                                            <h5 class="font-weight-bold text-primary mb-3"><i class="fa fa-edit"></i> รายละเอียดการเปลี่ยนสิทธิ์และย้ายผู้อยู่อาศัย</h5>
                                            
                                            <div class="row g-3">
                                                <div class="col-md-12 form-group">
                                                    <label for="change_type" class="control-label font-weight-bold">1. เลือกประเภทการดำเนินการ</label>
                                                    <select class="form-control" id="change_type" name="change_type" required>
                                                        <option value="CHANGE_OWNER">กรณี 1: เจ้าของเดิม ขายและเปลี่ยนเจ้าของใหม่ (CHANGE_OWNER)</option>
                                                        <option value="MOVE_OUT_IN">กรณี 2: เจ้าของปล่อยให้เช่า / มีผู้เช่าย้ายเข้า (MOVE_OUT_IN)</option>
                                                        <option value="CHANGE_TENANT">กรณี 3: เปลี่ยนตัวผู้เช่าใหม่ (ผู้เช่าเดิมย้ายออก) (CHANGE_TENANT)</option>
                                                        <option value="MOVE_HOUSE_BETWEEN">กรณี 4: ย้ายบ้านข้ามบ้านเลขที่ (เช่น 67/1 ไป 67/8)</option>
                                                    </select>
                                                </div>
                                            </div>

                                            <hr class="my-3">

                                            <!-- Container A: Single House Selection (Cases 1, 2, 3) -->
                                            <div id="singleHouseContainer" class="row g-3">
                                                <div class="col-md-12 form-group">
                                                    <label for="house_number" class="control-label font-weight-bold">2. ค้นหาบ้านเลขที่ที่ต้องการดำเนินการ</label>
                                                    <input type="text" class="form-control" id="house_number" name="house_number" placeholder="พิมพ์ค้นหาบ้านเลขที่..." value="<?php echo htmlspecialchars($preset_house); ?>" autocomplete="off">
                                                </div>
                                            </div>

                                            <!-- Container B: Move House Between (Case 4) -->
                                            <div id="moveHouseContainer" class="row g-3" style="display: none;">
                                                <div class="col-md-6 form-group">
                                                    <label for="old_house_number" class="control-label font-weight-bold">2. ค้นหาบ้านเลขที่เดิม (ย้ายออก)</label>
                                                    <input type="text" class="form-control" id="old_house_number" name="old_house_number" placeholder="พิมพ์ค้นหาบ้านเดิม..." autocomplete="off">
                                                </div>
                                                <div class="col-md-6 form-group">
                                                    <label for="new_house_number" class="control-label font-weight-bold">3. ค้นหาบ้านเลขที่ใหม่ (ย้ายเข้า)</label>
                                                    <input type="text" class="form-control" id="new_house_number" name="new_house_number" placeholder="พิมพ์ค้นหาบ้านใหม่..." autocomplete="off">
                                                </div>
                                            </div>

                                            <div id="newResidentContainer">
                                                <hr class="my-3">
                                                <div class="row g-3">
                                                    <div class="col-md-6 form-group">
                                                        <label for="new_contact_name" class="control-label font-weight-bold">ชื่อผู้อยู่อาศัยใหม่ / ผู้ติดต่อใหม่</label>
                                                        <input type="text" class="form-control" id="new_contact_name" name="new_contact_name" placeholder="ชื่อ-นามสกุล...">
                                                    </div>
                                                    <div class="col-md-6 form-group">
                                                        <label for="new_phone_number" class="control-label font-weight-bold">เบอร์โทรศัพท์ติดต่อใหม่</label>
                                                        <input type="text" class="form-control" id="new_phone_number" name="new_phone_number" placeholder="เบอร์โทรศัพท์..." maxlength="15">
                                                    </div>

                                                    <div class="col-md-6 form-group">
                                                        <label for="new_house_status" class="control-label font-weight-bold">สถานะผู้อยู่อาศัยใหม่</label>
                                                        <select class="form-control" id="new_house_status" name="new_house_status">
                                                            <option value="O">บ้านตนเอง / เจ้าของร่วม (Owner)</option>
                                                            <option value="R">บ้านเช่า / ผู้เช่า (Tenant)</option>
                                                            <option value="-">บ้านว่าง / รอผู้อยู่อาศัย</option>
                                                        </select>
                                                    </div>
                                                </div>
                                            </div>

                                            <div id="newCarContainer">
                                                <hr class="my-3">
                                                <h6 class="font-weight-bold text-secondary mb-2"><i class="fa fa-car"></i> ข้อมูลยานพาหนะใหม่ (หากประสงค์ลงทะเบียนรถยนต์ทันที)</h6>
                                                <div class="row g-2">
                                                    <div class="col-md-4 form-group">
                                                        <label for="car_no1" class="font-weight-bold small">ทะเบียนรถยนต์</label>
                                                        <input type="text" class="form-control form-control-sm" id="car_no1" name="car_no1" placeholder="เช่น กข 1234">
                                                    </div>
                                                    <div class="col-md-4 form-group">
                                                        <label for="car_no1_province" class="font-weight-bold small">จังหวัด</label>
                                                        <input type="text" class="form-control form-control-sm" id="car_no1_province" name="car_no1_province" placeholder="เช่น กรุงเทพ">
                                                    </div>
                                                    <div class="col-md-4 form-group">
                                                        <label for="car_no1_brand" class="font-weight-bold small">ยี่ห้อ</label>
                                                        <input type="text" class="form-control form-control-sm" id="car_no1_brand" name="car_no1_brand" placeholder="เช่น Toyota">
                                                    </div>
                                                    <div class="col-md-6 form-group">
                                                        <label for="car_no1_color" class="font-weight-bold small">สี</label>
                                                        <input type="text" class="form-control form-control-sm" id="car_no1_color" name="car_no1_color" placeholder="เช่น ดำ">
                                                    </div>
                                                    <div class="col-md-6 form-group">
                                                        <label for="car_no1_type" class="font-weight-bold small">ประเภทรถ</label>
                                                        <select class="form-control form-control-sm" id="car_no1_type" name="car_no1_type">
                                                            <option value="">-- เลือกประเภทรถ --</option>
                                                            <option value="รถเก๋ง">รถเก๋ง</option>
                                                            <option value="รถกระบะ">รถกระบะ</option>
                                                            <option value="รถตู้">รถตู้</option>
                                                            <option value="รถอเนกประสงค์ (SUV)">รถอเนกประสงค์ (SUV)</option>
                                                            <option value="รถจักรยานยนต์">รถจักรยานยนต์</option>
                                                        </select>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="form-group mt-3">
                                                <label for="remark" class="control-label font-weight-bold">หมายเหตุการดำเนินการ</label>
                                                <textarea class="form-control" id="remark" name="remark" rows="2" placeholder="ระบุเหตุผลการดำเนินการ..."></textarea>
                                            </div>

                                            <!-- Security Options Card -->
                                            <div class="card my-3 border-danger" id="securityOptionsCard">
                                                <div class="card-header bg-danger text-white py-2">
                                                    <h6 class="mb-0 font-weight-bold"><i class="fa fa-shield"></i> การจัดการระบบและสิทธิ์ข้อมูลส่วนบุคคล (PDPA)</h6>
                                                </div>
                                                <div class="card-body py-2">
                                                    <div class="custom-control custom-checkbox mt-1">
                                                        <input type="checkbox" class="custom-control-input" id="deactivate_old_user" name="deactivate_old_user" value="1" checked>
                                                        <label class="custom-control-label text-danger font-weight-bold" for="deactivate_old_user">ระงับบัญชีผู้ใช้ระบบเว็บ (ims_user) ของผู้อยู่อาศัยเดิม</label>
                                                    </div>
                                                    <div class="custom-control custom-checkbox mt-1">
                                                        <input type="checkbox" class="custom-control-input" id="deactivate_line" name="deactivate_line" value="1" checked>
                                                        <label class="custom-control-label" for="deactivate_line">ยกเลิกสิทธิ์ผูก LINE OA ของผู้อยู่อาศัยเดิม</label>
                                                    </div>
                                                    <div class="custom-control custom-checkbox mt-1">
                                                        <input type="checkbox" class="custom-control-input" id="deactivate_stickers" name="deactivate_stickers" value="1" checked>
                                                        <label class="custom-control-label" for="deactivate_stickers">ระงับสิทธิ์ยานพาหนะและล้างทะเบียนรถยนต์เดิมทั้งหมด</label>
                                                    </div>
                                                </div>
                                            </div>

                                            <!-- Checklist -->
                                            <div class="card my-3 border-info">
                                                <div class="card-header bg-info text-white py-2">
                                                    <h6 class="mb-0 font-weight-bold"><i class="fa fa-tasks"></i> รายการตรวจสอบความถูกต้อง (Checklist)</h6>
                                                </div>
                                                <div class="card-body py-2">
                                                    <ul class="list-unstyled mb-0">
                                                        <li class="custom-control custom-checkbox mt-1">
                                                            <input type="checkbox" class="custom-control-input chk-item" id="chkDoc" required>
                                                            <label class="custom-control-label" for="chkDoc">ตรวจสอบความถูกต้องของสำเนาสัญญา/หลักฐานสิทธิ์การอยู่อาศัยใหม่แล้ว</label>
                                                        </li>
                                                        <li class="custom-control custom-checkbox mt-1">
                                                            <input type="checkbox" class="custom-control-input chk-item" id="chkDebt" required>
                                                            <label class="custom-control-label" for="chkDebt">ตรวจสอบยอดเงินค้างชำระและการจัดการหนี้สินของบ้านเลขที่ดังกล่าวเรียบร้อย</label>
                                                        </li>
                                                    </ul>
                                                </div>
                                            </div>

                                            <div class="text-right mb-3">
                                                <input type="hidden" name="action" value="CHANGE_HOUSE_HOLDER_FULL">
                                                <button type="button" class="btn btn-secondary" onclick="window.history.back();">ยกเลิก</button>
                                                <button type="button" class="btn btn-warning" onclick="showImpactPreview();"><i class="fa fa-stethoscope"></i> ทดสอบ/ตรวจสอบผลกระทบ</button>
                                                <button type="submit" class="btn btn-primary" id="btnSubmit"><i class="fa fa-save"></i> บันทึกข้อมูลและสิทธิ์</button>
                                            </div>
                                        </div>

                                        <!-- Right Side: Details Analysis & Status -->
                                        <div class="col-md-5 pl-md-4">
                                            <h5 class="font-weight-bold text-secondary mb-3"><i class="fa fa-info-circle"></i> ข้อมูลบ้านปัจจุบันในระบบ</h5>
                                            
                                            <div class="card border-left-primary shadow-sm mb-3">
                                                <div class="card-body">
                                                    <div class="d-flex justify-content-between mb-2">
                                                        <span class="text-muted">ชื่อผู้ติดต่อปัจจุบัน:</span>
                                                        <strong id="curContact">-</strong>
                                                    </div>
                                                    <div class="d-flex justify-content-between mb-2">
                                                        <span class="text-muted">เบอร์โทรศัพท์เดิม:</span>
                                                        <strong id="curPhone">-</strong>
                                                    </div>
                                                    <div class="d-flex justify-content-between mb-2">
                                                        <span class="text-muted">สถานะการอยู่อาศัย:</span>
                                                        <span class="badge badge-secondary" id="curStatus">-</span>
                                                    </div>
                                                    <div class="d-flex justify-content-between mb-2">
                                                        <span class="text-muted">สถานะบัญชีเว็บ:</span>
                                                        <span class="badge badge-light border" id="curWebStatus">-</span>
                                                    </div>
                                                    <div class="d-flex justify-content-between mb-2">
                                                        <span class="text-muted">ยอดค้างชำระค่าส่วนกลาง:</span>
                                                        <strong id="curDebt" class="text-success">0.00 บาท</strong>
                                                    </div>
                                                    <div class="d-flex justify-content-between mb-2">
                                                        <span class="text-muted">จำนวน LINE เชื่อมต่อ:</span>
                                                        <strong id="curLineCount" class="text-danger">0 บัญชี</strong>
                                                    </div>
                                                    <div class="d-flex justify-content-between">
                                                        <span class="text-muted">จำนวนสัตว์เลี้ยงสะสม:</span>
                                                        <strong id="curPets" class="text-warning">0 ตัว</strong>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="card mb-3">
                                                <div class="card-header bg-dark text-white py-2 font-weight-bold"><i class="fa fa-share-alt"></i> บัญชี LINE OA ผูกสิทธิ์ปัจจุบัน</div>
                                                <div class="card-body p-0">
                                                    <ul class="list-group list-group-flush" id="lineUsersList">
                                                        <li class="list-group-item text-muted text-center py-3">เลือกเลขที่บ้านเพื่อโหลดรายชื่อผู้เชื่อม LINE...</li>
                                                    </ul>
                                                </div>
                                            </div>                                            <div class="card mb-3">
                                                <div class="card-header bg-secondary text-white py-2 font-weight-bold"><i class="fa fa-car"></i> ยานพาหนะที่ลงทะเบียนปัจจุบัน</div>
                                                <div class="card-body p-0">
                                                    <ul class="list-group list-group-flush" id="carList">
                                                        <li class="list-group-item text-muted text-center py-3">เลือกเลขที่บ้านเพื่อโหลดรายการทะเบียนรถ...</li>
                                                    </ul>
                                                </div>
                                            </div>

                                            <div class="alert alert-warning py-3 px-3 fs-7" id="alertArea" style="display: none;">
                                                <strong id="alertTitle"><i class="fa fa-exclamation-triangle"></i> ข้อควรทราบ:</strong>
                                                <p class="mb-0 mt-1 small" id="alertText"></p>
                                            </div>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Footer & Modal -->
            <?php include('includes/Modal-Logout.php'); ?>
            <?php include('includes/Footer.php'); ?>
        </div>
    </div>
</div>

<!-- Modal for impact preview -->
<div class="modal fade" id="previewModal" tabindex="-1" role="dialog" aria-labelledby="previewModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header bg-warning text-dark">
                <h5 class="modal-title font-weight-bold" id="previewModalLabel"><i class="fa fa-stethoscope"></i> ตรวจสอบการเปลี่ยนแปลงและผลกระทบก่อนบันทึกจริง</h5>
                <button type="button" class="close text-dark" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body" id="previewModalBody">
                <!-- Content generated dynamically -->
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">ปิดหน้าต่าง</button>
                <button type="button" class="btn btn-primary" id="btnConfirmFromPreview"><i class="fa fa-check-circle"></i> ข้อมูลถูกต้อง ยืนยันบันทึก</button>
            </div>
        </div>
    </div>
</div>

<script src="vendor/jquery/jquery.min.js"></script>
<script src="vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
<script src="vendor/jquery-easing/jquery.easing.min.js"></script>
<script src="js/myadmin.min.js"></script>

<!-- jQuery UI Autocomplete Loader -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/jqueryui/1.12.1/jquery-ui.min.css">
<script src="https://cdnjs.cloudflare.com/ajax/libs/jqueryui/1.12.1/jquery-ui.min.js"></script>

<script>
    let currentHouseData = null;

    $(document).ready(function () {
        // ระบบ Autocomplete สำหรับค้นหาบ้านเลขที่ (กรณี 1, 2, 3)
        $("#house_number").autocomplete({
            source: function (request, response) {
                $.ajax({
                    type: "POST",
                    url: 'model/manage_house_process.php',
                    data: {action: "GET_HOUSE_AUTOCOMPLETE", search: request.term},
                    dataType: "json",
                    success: function (data) {
                        if (Array.isArray(data)) {
                            response(data);
                        } else {
                            response([]);
                        }
                    },
                    error: function () {
                        response([]);
                    }
                });
            },
            minLength: 1,
            select: function (event, ui) {
                let house_number = ui.item.value;
                if (house_number) {
                    loadHouseDetailForChange(house_number);
                } else {
                    clearHouseDetails();
                }
            }
        });

        // ระบบ Autocomplete สำหรับค้นหาบ้านเลขที่เดิม (กรณี 4)
        $("#old_house_number").autocomplete({
            source: function (request, response) {
                $.ajax({
                    type: "POST",
                    url: 'model/manage_house_process.php',
                    data: {action: "GET_HOUSE_AUTOCOMPLETE", search: request.term},
                    dataType: "json",
                    success: function (data) {
                        if (Array.isArray(data)) {
                            response(data);
                        } else {
                            response([]);
                        }
                    },
                    error: function () {
                        response([]);
                    }
                });
            },
            minLength: 1,
            select: function (event, ui) {
                let house_number = ui.item.value;
                if (house_number) {
                    loadHouseDetailForChange(house_number);
                } else {
                    clearHouseDetails();
                }
            }
        });

        // ระบบ Autocomplete สำหรับค้นหาบ้านเลขที่ใหม่ (กรณี 4)
        $("#new_house_number").autocomplete({
            source: function (request, response) {
                $.ajax({
                    type: "POST",
                    url: 'model/manage_house_process.php',
                    data: {action: "GET_HOUSE_AUTOCOMPLETE", search: request.term},
                    dataType: "json",
                    success: function (data) {
                        if (Array.isArray(data)) {
                            response(data);
                        } else {
                            response([]);
                        }
                    },
                    error: function () {
                        response([]);
                    }
                });
            },
            minLength: 1
        });

        // เมื่อมีการเคลียร์ค่าในช่องบ้านเลขที่
        $('#house_number, #old_house_number').on('keyup change', function () {
            let house_number = $(this).val();
            if (!house_number) {
                clearHouseDetails();
            }
        });

        // จัดการ default values และข้อความเมื่อประเภทรายการเปลี่ยน
        $('#change_type').on('change', function () {
            let type = $(this).val();
            let remarkInput = $('#remark');
            let statusSelect = $('#new_house_status');

            if (type === 'MOVE_HOUSE_BETWEEN') {
                // ซ่อนฟอร์มและจัดการ inputs สำหรับการย้ายข้ามบ้าน
                $('#singleHouseContainer').hide();
                $('#moveHouseContainer').show();
                
                $('#newResidentContainer').hide();
                $('#new_contact_name').removeAttr('required');
                $('#new_phone_number').removeAttr('required');
                
                $('#newCarContainer').hide();
                $('#securityOptionsCard').hide();
                
                $('input[name="action"]').val('MOVE_HOUSE_MEMBER_SIMPLIFIED');
                
                remarkInput.attr('placeholder', 'ระบุรายละเอียด เช่น ย้ายลูกบ้านไปบ้านหลังใหม่ภายในโครงการ');
                remarkInput.val('ย้ายบ้านและสิทธิ์จากบ้านเลขที่เดิมไปยังบ้านเลขที่ใหม่');
            } else {
                // แสดงฟอร์มการจัดการบ้านหลังเดียว
                $('#singleHouseContainer').show();
                $('#moveHouseContainer').hide();
                
                $('#newResidentContainer').show();
                $('#new_contact_name').attr('required', 'required');
                $('#new_phone_number').attr('required', 'required');
                
                $('#newCarContainer').show();
                $('#securityOptionsCard').show();
                
                $('input[name="action"]').val('CHANGE_HOUSE_HOLDER_FULL');

                if (type === 'CHANGE_OWNER') {
                    statusSelect.val('O');
                    remarkInput.attr('placeholder', 'เช่น โอนกรรมสิทธิ์เปลี่ยนเจ้าของใหม่ อ้างอิงเอกสาร ทด.13');
                    remarkInput.val('โอนกรรมสิทธิ์เปลี่ยนเจ้าของใหม่ อ้างอิงเอกสาร ทด.13');
                    $('#deactivate_line').prop('checked', true);
                } else if (type === 'MOVE_OUT_IN') {
                    statusSelect.val('R');
                    remarkInput.attr('placeholder', 'เช่น เจ้าของปล่อยเช่า / มีผู้เช่ารายใหม่ย้ายเข้า');
                    remarkInput.val('เจ้าของปล่อยเช่า / มีผู้เช่ารายใหม่ย้ายเข้า');
                    $('#deactivate_line').prop('checked', true);
                } else if (type === 'CHANGE_TENANT') {
                    statusSelect.val('R');
                    remarkInput.attr('placeholder', 'เช่น เปลี่ยนตัวผู้เช่าใหม่ ย้ายเข้าแทนผู้เช่าเดิมที่หมดสัญญาเช่า');
                    remarkInput.val('เปลี่ยนตัวผู้เช่าใหม่ ย้ายเข้าแทนผู้เช่าเดิมที่หมดสัญญาเช่า');
                    $('#deactivate_line').prop('checked', true);
                }
            }
        });

        // ทริกเกอร์เปลี่ยนข้อมูลแรกสุด
        $('#change_type').trigger('change');

        // ถ้ามีบ้านเลขที่ตั้งต้นใน URL ให้สั่งโหลดทันที
        let presetHouse = '<?php echo $preset_house; ?>';
        if (presetHouse) {
            loadHouseDetailForChange(presetHouse);
        }

        // หน้าจอซับมิตฟอร์ม
        $('#formChangeHolder').on('submit', function (e) {
            e.preventDefault();
            
            // ตรวจสอบเช็คลิสต์ก่อนส่งฟอร์ม
            let allChecked = true;
            $('.chk-item').each(function () {
                if (!$(this).is(':checked')) {
                    allChecked = false;
                }
            });

            if (!allChecked) {
                alertify.error("กรุณาตรวจสอบและเลือกรายการใน Checklist ให้ครบถ้วน");
                return;
            }

            let changeType = $('#change_type').val();
            let changeTypeText = $('#change_type option:selected').text();

            if (changeType === 'MOVE_HOUSE_BETWEEN') {
                let oldHouse = $('#old_house_number').val();
                let newHouse = $('#new_house_number').val();

                if (!oldHouse || !newHouse) {
                    alertify.error("กรุณาระบุบ้านเลขที่เดิมและบ้านเลขที่ใหม่");
                    return;
                }

                if (oldHouse === newHouse) {
                    alertify.error("บ้านเลขที่เดิมและบ้านเลขที่ใหม่ต้องไม่ซ้ำกัน");
                    return;
                }

                bootbox.confirm({
                    title: "<span class='text-danger fw-bold'><i class='fa fa-exchange-alt'></i> ยืนยันการโอนย้ายข้อมูลผู้อยู่อาศัย</span>",
                    message: "คุณแน่ใจหรือไม่ว่าต้องการโอนย้ายข้อมูลของผู้อยู่อาศัยจากบ้านเลขที่ " + oldHouse + " ไปยัง " + newHouse + "?<br><br>ระบบจะทำการย้ายประวัติ ข้อมูล LINE ทะเบียนรถยนต์ และสัตว์เลี้ยงทั้งหมดไปยังบ้านหลังใหม่โดยอัตโนมัติ ซึ่งไม่สามารถยกเลิกย้อนหลังได้",
                    buttons: {
                        cancel: { label: 'ยกเลิก', className: 'btn-secondary' },
                        confirm: { label: 'ยืนยันโอนย้าย', className: 'btn-primary' }
                    },
                    callback: function (result) {
                        if (result) {
                            submitForm();
                        }
                    }
                });
            } else {
                let houseNumber = $('#house_number').val();
                let newName = $('#new_contact_name').val();
                let newPhone = $('#new_phone_number').val();

                if (!houseNumber) {
                    alertify.error("กรุณาระบุหรือค้นหาบ้านเลขที่");
                    return;
                }

                if (!newName || !newPhone) {
                    alertify.error("กรุณากรอกชื่อและเบอร์โทรศัพท์ผู้อยู่อาศัยใหม่");
                    return;
                }

                bootbox.confirm({
                    title: "<span class='text-danger fw-bold'><i class='fa fa-exchange-alt'></i> ยืนยันการเปลี่ยนสิทธิ์ผู้อยู่อาศัย</span>",
                    message: "คุณแน่ใจหรือไม่ว่าต้องการดำเนินการเปลี่ยนสิทธิ์ข้อมูลของบ้านเลขที่ " + houseNumber + "?<br><br><strong>ประเภทรายการ:</strong> " + changeTypeText + "<br><strong>ผู้อยู่อาศัยใหม่:</strong> " + newName + " (" + newPhone + ")<br><br>ระบบจะปรับปรุงสถานะและตัดสิทธิ์ระบบเดิมตามที่คุณเลือก ซึ่งไม่สามารถยกเลิกย้อนหลังได้",
                    buttons: {
                        cancel: { label: 'ยกเลิก', className: 'btn-secondary' },
                        confirm: { label: 'ยืนยันดำเนินการ', className: 'btn-danger' }
                    },
                    callback: function (result) {
                        if (result) {
                            submitForm();
                        }
                    }
                });
            }
        });

        function submitForm() {
            $('#btnSubmit').attr('disabled', 'disabled').html('<i class="fa fa-spinner fa-spin"></i> กำลังดำเนินการ...');
            let formData = $('#formChangeHolder').serialize();
            
            $.ajax({
                url: 'model/manage_house_process.php',
                type: 'POST',
                data: formData,
                dataType: 'json',
                success: function (response) {
                    $('#btnSubmit').removeAttr('disabled').html('<i class="fa fa-save"></i> บันทึกข้อมูลและสิทธิ์');
                    if (response.status === 'success') {
                        alertify.success(response.message);
                        setTimeout(function () {
                            window.location.href = "manage_house.php?m=" + encodeURIComponent('<?php echo $_GET['m'] ?>') + "&s=" + encodeURIComponent('ข้อมูลเลขที่บ้าน');
                        }, 1500);
                    } else {
                        alertify.error(response.message);
                    }
                },
                error: function (response) {
                    $('#btnSubmit').removeAttr('disabled').html('<i class="fa fa-save"></i> บันทึกข้อมูลและสิทธิ์');
                    alertify.error("เกิดข้อผิดพลาดในการเชื่อมต่อเซิร์ฟเวอร์");
                }
            });
        }

        // ยืนยันบันทึกข้อมูลจากปุ่มใน Modal ทดสอบการเปลี่ยนแปลง
        $('#btnConfirmFromPreview').on('click', function () {
            $('#previewModal').modal('hide');
            $('#formChangeHolder').submit();
        });
    });

    function updateRightSideCard(d) {
        // ปรับข้อมูลการแสดงผลปัจจุบันด้านขวา
        $('#curContact').text(d.contact_name);
        $('#curPhone').text(d.phone_number || '-');
        
        let statusText = d.house_status === 'O' ? 'บ้านตนเอง' : (d.house_status === 'R' ? 'บ้านเช่า' : 'บ้านว่าง');
        $('#curStatus').text(statusText).attr('class', 'badge ' + (d.house_status === 'O' ? 'badge-primary' : (d.house_status === 'R' ? 'badge-warning' : 'badge-secondary')));
        
        $('#curWebStatus').text(d.web_user_status).attr('class', 'badge ' + (d.web_user_status === 'Active' ? 'badge-success' : 'badge-light border'));
        
        let debtAmount = parseFloat(d.outstanding_amount);
        $('#curDebt').text(debtAmount.toLocaleString('th-TH', { minimumFractionDigits: 2 }) + ' บาท');
        if (debtAmount > 0) {
            $('#curDebt').attr('class', 'text-danger font-weight-bold');
        } else {
            $('#curDebt').attr('class', 'text-success font-weight-bold');
        }

        $('#curLineCount').text(d.line_count + ' บัญชี');
        $('#curPets').text(d.pet_count + ' ตัว');

        // อัปเดตรายชื่อผู้ผูกไลน์
        let lineList = $('#lineUsersList');
        lineList.empty();
        if (d.line_users && d.line_users.length > 0) {
            d.line_users.forEach(function (user) {
                let typeBadge = user.user_type === 'Owner' ? 'เจ้าของ' : (user.user_type === 'Tenant' ? 'ผู้เช่า' : 'ผู้อยู่อาศัย');
                lineList.append('<li class="list-group-item d-flex justify-content-between align-items-center py-2">' +
                    '<span><i class="fab fa-line text-success"></i> ' + user.line_user_name + '</span>' +
                    '<span class="badge badge-info">' + typeBadge + '</span>' +
                    '</li>');
            });
        } else {
            lineList.append('<li class="list-group-item text-muted text-center py-3">บ้านหลังนี้ไม่มีบัญชี LINE OA ผูกสิทธิ์อยู่</li>');
        }

        // อัปเดตรายการรถยนต์ที่ลงทะเบียน
        let carList = $('#carList');
        carList.empty();
        if (d.cars && d.cars.length > 0) {
            d.cars.forEach(function (car) {
                let carDetail = (car.brand ? car.brand : '') + 
                                (car.color ? ' สี' + car.color : '') + 
                                (car.type ? ' (' + car.type + ')' : '');
                carList.append('<li class="list-group-item d-flex justify-content-between align-items-center py-2">' +
                    '<span><i class="fa fa-car text-primary"></i> <strong>' + car.car_no + '</strong> ' + (car.province ? car.province : '') + '</span>' +
                    '<span class="small text-muted">' + carDetail + '</span>' +
                    '</li>');
            });
        } else {
            carList.append('<li class="list-group-item text-muted text-center py-3">บ้านหลังนี้ไม่มีรถยนต์ลงทะเบียนไว้</li>');
        }

        // วิเคราะห์การแจ้งเตือน
        let alertArea = $('#alertArea');
        let alertText = $('#alertText');
        if (debtAmount > 0) {
            alertText.html("<strong class='text-danger'><i class='fa fa-exclamation-triangle'></i> ตรวจพบยอดค้างชำระค่าส่วนกลาง!</strong> โปรดประสานงานเคลียร์ยอดค้างชำระให้เป็นศูนย์เรียบร้อยก่อนทำการเปลี่ยนเจ้าของบ้าน");
            alertArea.attr('class', 'alert alert-danger py-2 px-3 small').show();
        } else {
            alertText.html("<i class='fa fa-info-circle'></i> ข้อมูลบ้านพร้อมทำรายการ ระบบจะเคลียร์บัญชีและรถยนต์เดิม และเปิดลิงก์สำหรับสมัครบัญชีคนใหม่");
            alertArea.attr('class', 'alert alert-info py-2 px-3 small').show();
        }
    }

    function loadHouseDetailForChange(houseNo) {
        $.ajax({
            url: 'model/manage_house_process.php',
            type: 'POST',
            data: { action: 'GET_HOUSE_DETAIL_FOR_CHANGE', house_number: houseNo },
            dataType: 'json',
            success: function (response) {
                if (response.status === 'success') {
                    let d = response.data;
                    currentHouseData = d;
                    updateRightSideCard(d);
                } else {
                    alertify.error(response.message);
                }
            },
            error: function () {
                alertify.error("ไม่สามารถเชื่อมต่อเพื่อดึงข้อมูลได้");
            }
        });
    }

    function clearHouseDetails() {
        currentHouseData = null;
        $('#curContact').text('-');
        $('#curPhone').text('-');
        $('#curStatus').text('-').attr('class', 'badge badge-secondary');
        $('#curWebStatus').text('-').attr('class', 'badge badge-light border');
        $('#curDebt').text('0.00 บาท').attr('class', 'text-success font-weight-bold');
        $('#curLineCount').text('0 บัญชี');
        $('#curPets').text('0 ตัว');
        $('#lineUsersList').html('<li class="list-group-item text-muted text-center py-3">เลือกเลขที่บ้านเพื่อโหลดรายชื่อผู้เชื่อม LINE...</li>');
        $('#carList').html('<li class="list-group-item text-muted text-center py-3">เลือกเลขที่บ้านเพื่อโหลดรายการทะเบียนรถ...</li>');
        $('#alertArea').hide();
    }

    // ฟังก์ชันทดสอบและจำลองผลกระทบก่อนการเปลี่ยนแปลง
    function showImpactPreview() {
        let changeType = $('#change_type').val();
        
        if (changeType === 'MOVE_HOUSE_BETWEEN') {
            let oldHouse = $('#old_house_number').val();
            let newHouse = $('#new_house_number').val();

            if (!oldHouse || !newHouse) {
                alertify.error("กรุณาระบุบ้านเลขที่เดิมและบ้านเลขที่ใหม่ก่อนทดสอบ");
                return;
            }
            if (oldHouse === newHouse) {
                alertify.error("บ้านเลขที่เดิมและใหม่ต้องไม่ซ้ำกัน");
                return;
            }

            // ถ้ายังไม่ได้โหลดข้อมูล หรือข้อมูลที่มีไม่ตรงกับบ้านเลขที่ที่กรอก ให้ดึงข้อมูลมาใหม่ก่อน
            if (!currentHouseData || currentHouseData.house_number !== oldHouse) {
                alertify.success("กำลังค้นหาและดึงข้อมูลบ้านเดิมเพื่อประเมินผลกระทบ...");
                $.ajax({
                    url: 'model/manage_house_process.php',
                    type: 'POST',
                    data: { action: 'GET_HOUSE_DETAIL_FOR_CHANGE', house_number: oldHouse },
                    dataType: 'json',
                    success: function (response) {
                        if (response.status === 'success') {
                            currentHouseData = response.data;
                            updateRightSideCard(response.data);
                            showImpactPreview();
                        } else {
                            alertify.error("ไม่พบข้อมูลบ้านเดิม: " + response.message);
                        }
                    },
                    error: function () {
                        alertify.error("ไม่สามารถเชื่อมต่อระบบเพื่อดึงข้อมูลได้");
                    }
                });
                return;
            }

            let html = `
                <div class="alert alert-info py-2 mb-3">
                    <h5 class="mb-1"><i class="fa fa-exchange"></i> ย้ายผู้อยู่อาศัยข้ามบ้านเลขที่: <strong>${oldHouse}</strong> &rarr; <strong>${newHouse}</strong></h5>
                    <p class="mb-0 small text-muted">ระบบจะย้ายข้อมูลประวัติ ทะเบียนรถ และสิทธิ์ทั้งหมดข้ามบ้านเลขที่โดยอัตโนมัติ</p>
                </div>
                
                <h6 class="font-weight-bold text-dark mb-2"><i class="fa fa-tasks"></i> รายการเปรียบเทียบข้อมูลและการเปลี่ยนแปลง:</h6>
                <table class="table table-bordered table-sm small">
                    <thead class="bg-light">
                        <tr>
                            <th style="width: 25%;">หัวข้อ</th>
                            <th style="width: 38%;">บ้านเดิม (${oldHouse})</th>
                            <th style="width: 37%;">บ้านใหม่ (${newHouse})</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td class="font-weight-bold">ชื่อผู้อยู่อาศัย</td>
                            <td><span class="text-danger"><del>${currentHouseData.contact_name}</del></span> &rarr; <span class="text-muted">บ้านว่าง / รอผู้อยู่อาศัย</span></td>
                            <td><span class="text-success font-weight-bold">${currentHouseData.contact_name}</span> (ย้ายเข้า)</td>
                        </tr>
                        <tr>
                            <td class="font-weight-bold">เบอร์โทรศัพท์</td>
                            <td><span class="text-danger"><del>${currentHouseData.phone_number || 'ไม่มีเบอร์'}</del></span> &rarr; <span class="text-muted">-</span></td>
                            <td><span class="text-success font-weight-bold">${currentHouseData.phone_number || 'ไม่มีเบอร์'}</span> (ย้ายเข้า)</td>
                        </tr>
                        <tr>
                            <td class="font-weight-bold">บัญชี LINE OA</td>
                            <td colspan="2"><span class="text-primary font-weight-bold">สลับพิกัดสิทธิ์ในระบบ:</span> ย้ายรายชื่อผู้เชื่อมต่อ LINE ของเบอร์โทร ${currentHouseData.phone_number || '-'} จำนวน ${currentHouseData.line_count} บัญชี ไปผูกกับบ้านใหม่ <strong>${newHouse}</strong></td>
                        </tr>
                        <tr>
                            <td class="font-weight-bold">ข้อมูลยานพาหนะ</td>
                            <td><span class="text-danger">เคลียร์ข้อมูลรถเดิมทั้งหมดเป็นค่าว่าง</span> และระงับสติกเกอร์เดิม</td>
                            <td>ย้ายข้อมูลทะเบียนรถเดิม <strong>${currentHouseData.car_no1 || 'ไม่มีรถ'}</strong> ไปผูกกับบ้านใหม่</td>
                        </tr>
                        <tr>
                            <td class="font-weight-bold">สัตว์เลี้ยง</td>
                            <td colspan="2"><span class="text-primary font-weight-bold">โอนย้ายประวัติสัตว์เลี้ยง:</span> ย้ายประวัติรูปภาพและทะเบียนสัตว์เลี้ยงสะสมจำนวน ${currentHouseData.pet_count} ตัว ไปผูกกับบ้านใหม่ <strong>${newHouse}</strong></td>
                        </tr>
                        <tr>
                            <td class="font-weight-bold text-danger">หนี้ค่าส่วนกลาง</td>
                            <td>ยอดค้างปัจจุบัน: <strong class="${parseFloat(currentHouseData.outstanding_amount) > 0 ? 'text-danger' : 'text-success'}">${parseFloat(currentHouseData.outstanding_amount).toLocaleString('th-TH', {minimumFractionDigits: 2})} บาท</strong></td>
                            <td>*หนี้สินของบ้านเดิมจะไม่โอนย้ายตามตัวลูกบ้าน กรุณาเคลียร์หนี้ที่ค้างอยู่ของบ้านเก่าก่อนย้ายออก</td>
                        </tr>
                    </tbody>
                </table>
            `;
            $('#previewModalBody').html(html);
            $('#previewModal').modal('show');
        } else {
            let houseNumber = $('#house_number').val();
            let newName = $('#new_contact_name').val();
            let newPhone = $('#new_phone_number').val();
            let newStatus = $('#new_house_status').val();
            let newStatusText = $('#new_house_status option:selected').text();

            if (!houseNumber) {
                alertify.error("กรุณาระบุหรือค้นหาบ้านเลขที่ก่อนทดสอบ");
                return;
            }
            if (!newName || !newPhone) {
                alertify.error("กรุณากรอกชื่อและเบอร์โทรศัพท์ผู้อยู่อาศัยใหม่ก่อนทดสอบ");
                return;
            }

            // ถ้ายังไม่ได้โหลดข้อมูล หรือข้อมูลที่มีไม่ตรงกับบ้านเลขที่ที่ระบุ ให้ดึงข้อมูลมาก่อน
            if (!currentHouseData || currentHouseData.house_number !== houseNumber) {
                alertify.success("กำลังค้นหาและดึงข้อมูลบ้านเพื่อประเมินผลกระทบ...");
                $.ajax({
                    url: 'model/manage_house_process.php',
                    type: 'POST',
                    data: { action: 'GET_HOUSE_DETAIL_FOR_CHANGE', house_number: houseNumber },
                    dataType: 'json',
                    success: function (response) {
                        if (response.status === 'success') {
                            currentHouseData = response.data;
                            updateRightSideCard(response.data);
                            showImpactPreview();
                        } else {
                            alertify.error("ไม่พบข้อมูลบ้าน: " + response.message);
                        }
                    },
                    error: function () {
                        alertify.error("ไม่สามารถเชื่อมต่อเพื่อดึงข้อมูลได้");
                    }
                });
                return;
            }

            let changeTypeText = $('#change_type option:selected').text();
            let deactUser = $('#deactivate_old_user').is(':checked');
            let deactLine = $('#deactivate_line').is(':checked');
            let deactSticker = $('#deactivate_stickers').is(':checked');

            let html = `
                <div class="alert alert-info py-2 mb-3">
                    <h5 class="mb-1"><i class="fa fa-home"></i> การเปลี่ยนสิทธิ์บ้านเลขที่: <strong>${houseNumber}</strong></h5>
                    <p class="mb-0 small"><strong>ประเภทรายการ:</strong> ${changeTypeText}</p>
                </div>
                
                <h6 class="font-weight-bold text-dark mb-2"><i class="fa fa-user-edit"></i> สรุปการเปลี่ยนแปลงข้อมูลผู้อยู่อาศัย:</h6>
                <table class="table table-bordered table-sm small mb-3">
                    <thead class="bg-light">
                        <tr>
                            <th>รายการ</th>
                            <th>ข้อมูลเดิมในระบบ</th>
                            <th>ข้อมูลที่จะบันทึกใหม่</th>
                            <th>สถานะการอัปเดต</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td class="font-weight-bold">ชื่อผู้ติดต่อหลัก</td>
                            <td>${currentHouseData.contact_name || '-'}</td>
                            <td class="text-success font-weight-bold">${newName}</td>
                            <td><span class="badge badge-success">อัปเดต</span></td>
                        </tr>
                        <tr>
                            <td class="font-weight-bold">เบอร์โทรศัพท์</td>
                            <td>${currentHouseData.phone_number || '-'}</td>
                            <td class="text-success font-weight-bold">${newPhone}</td>
                            <td><span class="badge badge-success">อัปเดต</span></td>
                        </tr>
                        <tr>
                            <td class="font-weight-bold">สถานะผู้อยู่อาศัย</td>
                            <td>${currentHouseData.house_status === 'O' ? 'บ้านตนเอง' : (currentHouseData.house_status === 'R' ? 'บ้านเช่า' : 'บ้านว่าง')}</td>
                            <td class="text-success font-weight-bold">${newStatusText}</td>
                            <td><span class="badge badge-success">อัปเดต</span></td>
                        </tr>
                    </tbody>
                </table>

                <h6 class="font-weight-bold text-dark mb-2"><i class="fa fa-shield"></i> สรุปผลกระทบระบบและความปลอดภัย (PDPA):</h6>
                <ul class="list-group list-group-flush border rounded mb-3 small">
                    <li class="list-group-item d-flex justify-content-between align-items-center py-2">
                        <span>1. บัญชีเว็บ (ims_user) ของเบอร์เก่า (${currentHouseData.phone_number || 'ไม่มีเบอร์'}):</span>
                        ${deactUser ? '<span class="text-danger font-weight-bold"><i class="fa fa-ban"></i> จะถูกระงับสิทธิ์การใช้งาน (Inactive)</span>' : '<span class="text-muted">ไม่มีผลกระทบ</span>'}
                    </li>
                    <li class="list-group-item d-flex justify-content-between align-items-center py-2">
                        <span>2. การเชื่อมต่อ LINE OA ของผู้อยู่อาศัยเดิม:</span>
                        ${deactLine ? 
                            (changeType === 'CHANGE_OWNER' ? 
                                '<span class="text-danger font-weight-bold"><i class="fa fa-trash"></i> ปลดการเชื่อมต่อ LINE ทุกคนของบ้านนี้ออก</span>' : 
                                '<span class="text-warning font-weight-bold"><i class="fa fa-user-minus"></i> ปลดเฉพาะเบอร์เดิม (' + currentHouseData.phone_number + ') คงเหลือเบอร์อื่นไว้</span>') 
                            : '<span class="text-muted">ไม่มีผลกระทบ</span>'}
                    </li>
                    <li class="list-group-item d-flex justify-content-between align-items-center py-2">
                        <span>3. ทะเบียนรถยนต์และสติ๊กเกอร์เดิม:</span>
                        ${deactSticker ? '<span class="text-danger font-weight-bold"><i class="fa fa-eraser"></i> ล้างค่าทะเบียนเก่าทั้งหมด และยกเลิกสติกเกอร์เดิม</span>' : '<span class="text-muted">คงเดิม</span>'}
                    </li>
                    <li class="list-group-item d-flex justify-content-between align-items-center py-2">
                        <span>4. การจัดการข้อมูลสัตว์เลี้ยง (ims_house_pet):</span>
                        ${changeType === 'CHANGE_OWNER' ? 
                            '<span class="text-danger font-weight-bold"><i class="fa fa-arrow-right"></i> ย้ายประวัติสัตว์เลี้ยงเดิม ('+ currentHouseData.pet_count +' ตัว) เป็นสถานะ ย้ายออก</span>' : 
                            '<span class="text-success font-weight-bold"><i class="fa fa-edit"></i> เปลี่ยนชื่อผู้รับผิดชอบสัตว์เลี้ยงเป็น ' + newName + '</span>'}
                    </li>
                </ul>
            `;
            
            if (changeType === 'CHANGE_OWNER' && parseFloat(currentHouseData.outstanding_amount) > 0) {
                html += `
                    <div class="alert alert-danger py-2 px-3 small">
                        <strong><i class="fa fa-exclamation-triangle"></i> คำเตือนยอดค้างชำระ:</strong> บ้านเลขที่นี้มียอดค้างชำระค่าส่วนกลางอยู่ <strong>${parseFloat(currentHouseData.outstanding_amount).toLocaleString('th-TH', {minimumFractionDigits: 2})} บาท</strong> กรุณาติดตามเก็บยอดค้างให้เรียบร้อยก่อนโอนกรรมสิทธิ์!
                    </div>
                `;
            }

            $('#previewModalBody').html(html);
            $('#previewModal').modal('show');
        }
    }
</script>
</body>
</html>
