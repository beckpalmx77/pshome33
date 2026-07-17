<?php
include('includes/Header.php');
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ระบบผู้มาติดต่อ (Visitor)</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.13.0/css/all.min.css" rel="stylesheet">
    <style>
        /* Mobile-first enhancements */
        .preview-img-container img {
            width: 70px;
            height: 70px;
            object-fit: cover;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .visitor-card {
            border-left: 4px solid #007bff;
            transition: all 0.2s;
        }
        .visitor-card.card-checked-in {
            border-left-color: #28a745;
        }
        .tab-btn {
            border-radius: 20px;
        }
        .form-control-sm {
            height: calc(1.5em + 0.75rem + 2px);
            padding: 0.375rem 0.75rem;
            font-size: 0.875rem;
        }
    </style>
</head>
<body class="bg-light">
    <div class="container py-3">
        <div class="row">
            <div class="col-12">
                <h4 class="text-center mb-3 font-weight-bold text-primary">ระบบผู้มาติดต่อ (Visitor)</h4>
                
                <!-- Tab Menu -->
                <div class="d-flex justify-content-center mb-3">
                    <div class="btn-group p-1 bg-white rounded-pill shadow-sm" style="border: 1px solid #dee2e6;">
                        <button type="button" class="btn btn-primary btn-sm px-4 rounded-pill font-weight-bold" id="tabCheckIn">
                            <i class="fa fa-sign-in-alt mr-1"></i> บันทึกเข้า
                        </button>
                        <button type="button" class="btn btn-light btn-sm px-4 rounded-pill font-weight-bold" id="tabActiveList">
                            <i class="fa fa-users mr-1"></i> ผู้มาติดต่อที่ยังอยู่ <span class="badge badge-danger ml-1" id="activeCount">0</span>
                        </button>
                    </div>
                </div>

                <!-- Check In Tab Content -->
                <div id="checkInSection">
                    <!-- Search House -->
                    <div class="card shadow-sm border-0 mb-3">
                        <div class="card-body">
                            <div class="form-group mb-0">
                                <label class="control-label font-weight-bold text-secondary">ค้นหาบ้านเลขที่</label>
                                <div class="input-group">
                                    <input type="text" class="form-control form-control-sm border-right-0"
                                           id="house_number"
                                           placeholder="กรอกบ้านเลขที่ เช่น 68/1">
                                    <div class="input-group-append">
                                        <button type="button" class="btn btn-primary btn-sm" id="btnSearch">
                                            <i class="fa fa-search"></i> ค้นหา
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Search Result House Info -->
                    <div id="resultSection" style="display:none;">
                        <div class="card shadow-sm border-0 mb-3 bg-white visitor-card">
                            <div class="card-body py-3">
                                <h6 class="font-weight-bold mb-1 text-primary"><i class="fa fa-home mr-1"></i> บ้านเลขที่: <span id="displayHouseNumber" class="text-dark"></span></h6>
                                <p class="mb-1 text-muted small"><strong>เจ้าของบ้าน:</strong> <span id="contact_name"></span></p>
                                <p class="mb-0 text-muted small"><strong>เบอร์โทร:</strong> <span id="phone_number"></span> | <strong>ซอย:</strong> <span id="alley"></span></p>
                                
                                <button type="button" class="btn btn-success btn-block btn-sm mt-3 font-weight-bold" id="btnNew">
                                    <i class="fa fa-plus-circle mr-1"></i> เพิ่มบันทึกผู้มาติดต่อใหม่
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Visitor Form Section -->
                    <div id="formSection" style="display:none;">
                        <div class="card shadow-sm border-0 mb-3">
                            <div class="card-body">
                                <h5 class="font-weight-bold text-dark border-bottom pb-2 mb-3">
                                    <i class="fa fa-user-edit mr-1 text-primary"></i> ข้อมูลผู้มาติดต่อ
                                </h5>
                                
                                <div class="form-group">
                                    <label class="control-label small font-weight-bold">ชื่อ-นามสกุล <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control form-control-sm" id="visitor_name" placeholder="ระบุชื่อผู้มาติดต่อ">
                                </div>
                                <div class="form-group">
                                    <label class="control-label small font-weight-bold">เบอร์โทรศัพท์</label>
                                    <input type="text" class="form-control form-control-sm" id="visitor_phone" placeholder="ระบุเบอร์โทรศัพท์">
                                </div>
                                <div class="form-group">
                                    <label class="control-label small font-weight-bold">ทะเบียนรถ</label>
                                    <input type="text" class="form-control form-control-sm" id="license_plate" placeholder="เช่น กข 1234 กทม">
                                </div>
                                <div class="form-group">
                                    <label class="control-label small font-weight-bold">ประเภท</label>
                                    <select class="form-control form-control-sm" id="visitor_type">
                                        <option value="">-- เลือก --</option>
                                        <option value="ส่งอาหาร">ส่งอาหาร (Food Delivery)</option>
                                        <option value="ส่งพัสดุ/ส่งของ">ส่งพัสดุ/ส่งของ (Parcel)</option>
                                        <option value="ญาติ/เพื่อน">ญาติ/เพื่อน (Visitor)</option>
                                        <option value="ติดต่อนิติฯ">ติดต่อนิติฯ (Niti Contact)</option>
                                        <option value="ผู้รับเหมา/ช่าง">ผู้รับเหมา/ช่าง (Contractor)</option>
                                        <option value="อื่นๆ">อื่นๆ (Other)</option>
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label class="control-label small font-weight-bold">วัตถุประสงค์</label>
                                    <input type="text" class="form-control form-control-sm" id="purpose" placeholder="เช่น มาหาญาติ, ส่งอาหาร">
                                </div>
                                <div class="form-group">
                                    <label class="control-label small font-weight-bold">หมายเหตุ</label>
                                    <input type="text" class="form-control form-control-sm" id="note" placeholder="ข้อมูลเพิ่มเติม">
                                </div>

                                <!-- Photo Upload Grid -->
                                <div class="form-group">
                                    <label class="control-label small font-weight-bold">ถ่ายรูปภาพ (สูงสุด 5 รูป)</label>
                                    <div class="row">
                                        <?php for($i=1; $i<=5; $i++): ?>
                                        <div class="col-4 mb-2">
                                            <div class="bg-light border rounded text-center p-2 position-relative" style="height: 90px; overflow: hidden;">
                                                <input type="file" class="position-absolute" id="picture_<?php echo $i; ?>" accept="image/*" capture="environment" style="opacity: 0; top: 0; left: 0; width: 100%; height: 100%; cursor: pointer; z-index: 10;">
                                                <div id="preview_picture_<?php echo $i; ?>" class="h-100 d-flex align-items-center justify-content-center">
                                                    <i class="fa fa-camera fa-2x text-secondary"></i>
                                                </div>
                                            </div>
                                            <small class="d-block text-center text-muted">รูป <?php echo $i; ?></small>
                                        </div>
                                        <?php endfor; ?>
                                    </div>
                                </div>

                                <!-- Card Exchange -->
                                <div class="card bg-light border-0 mb-3">
                                    <div class="card-body p-3">
                                        <div class="custom-control custom-checkbox mb-2">
                                            <input type="checkbox" class="custom-control-input" id="card_exchange" value="Y">
                                            <label class="custom-control-label font-weight-bold" for="card_exchange">แลกบัตรเข้าหมู่บ้านแล้ว</label>
                                        </div>
                                        <div class="form-group mb-0">
                                            <label class="control-label small">หมายเลขบัตรแลก</label>
                                            <input type="text" class="form-control form-control-sm" id="card_no" placeholder="ระบุเลขบัตรแลก">
                                        </div>
                                    </div>
                                </div>

                                <!-- Check in status -->
                                <div class="custom-control custom-switch mb-3">
                                    <input type="checkbox" class="custom-control-input" id="check_in_status" value="Y" checked>
                                    <label class="custom-control-label font-weight-bold text-success" for="check_in_status">อยู่ในหมู่บ้าน (Check In)</label>
                                </div>

                                <div class="form-group small text-muted border-top pt-2">
                                    <div>สถานะ: <span class="badge badge-secondary" id="status_badge">-</span></div>
                                    <div class="row mt-1">
                                        <div class="col-6">เข้า: <span id="display_check_in_time">-</span></div>
                                        <div class="col-6">ออก: <span id="display_check_out_time">-</span></div>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-6">
                                        <button type="button" class="btn btn-primary btn-block font-weight-bold" id="btnSave">
                                            <i class="fa fa-save mr-1"></i> บันทึกข้อมูล
                                        </button>
                                    </div>
                                    <div class="col-6">
                                        <button type="button" class="btn btn-warning btn-block font-weight-bold" id="btnCheckOut" style="display:none;">
                                            <i class="fa fa-sign-out-alt mr-1"></i> บันทึกออก
                                        </button>
                                    </div>
                                </div>
                                <button type="button" class="btn btn-danger btn-block font-weight-bold mt-2 btn-sm" id="btnDelete" style="display:none;">
                                    <i class="fa fa-trash-alt mr-1"></i> ลบข้อมูล
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Local House Visitor List Section -->
                    <div id="listSection" style="display:none;">
                        <div class="card shadow-sm border-0">
                            <div class="card-header bg-white border-bottom py-2">
                                <h6 class="mb-0 font-weight-bold text-dark"><i class="fa fa-history mr-1"></i> ประวัติผู้มาติดต่อบ้านนี้ (วันนี้)</h6>
                            </div>
                            <div class="card-body p-0">
                                <div id="visitorListBody" class="list-group list-group-flush"></div>
                            </div>
                        </div>
                    </div>

                    <!-- Not Found Warning -->
                    <div id="notFoundSection" style="display:none;" class="alert alert-warning shadow-sm border-0">
                        <i class="fa fa-exclamation-triangle mr-1"></i> ไม่พบบ้านเลขที่: <span id="notFoundHouseNumber" class="font-weight-bold"></span>
                    </div>
                </div>

                <!-- Active List Tab Content -->
                <div id="activeListSection" style="display:none;">
                    <div class="card shadow-sm border-0">
                        <div class="card-header bg-white d-flex justify-content-between align-items-center py-2">
                            <h6 class="mb-0 font-weight-bold text-dark"><i class="fa fa-street-view mr-1 text-success"></i> รายการที่อยู่ในหมู่บ้านขณะนี้</h6>
                            <button type="button" class="btn btn-sm btn-outline-secondary" id="btnRefreshActive">
                                <i class="fa fa-sync"></i> รีเฟรช
                            </button>
                        </div>
                        <div class="card-body p-3">
                            <div class="input-group mb-3">
                                <div class="input-group-prepend">
                                    <span class="input-group-text bg-white border-right-0"><i class="fa fa-search text-muted"></i></span>
                                </div>
                                <input type="text" class="form-control form-control-sm border-left-0" id="searchActiveVisitor" placeholder="ค้นหาชื่อ, ทะเบียน, บ้านเลขที่, เลขบัตร...">
                            </div>
                            <div id="activeVisitorListBody">
                                <div class="text-center text-muted p-4">กำลังโหลดข้อมูล...</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Script imports -->
    <script src="vendor/jquery/jquery.min.js"></script>
    <script src="vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/jqueryui/1.12.1/jquery-ui.min.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jqueryui/1.12.1/jquery-ui.min.js"></script>

    <script>
        let currentHouseNumber = '';
        let currentVisitorId = 0;
        let uploadedPictures = {picture_1: '', picture_2: '', picture_3: '', picture_4: '', picture_5: ''};
        let currentVisitors = [];
        let allActiveVisitors = [];

        $(document).ready(function () {
            // Auto complete for house number
            $("#house_number").autocomplete({
                source: function (request, response) {
                    $.ajax({
                        type: "POST",
                        url: 'model/manage_visitor_contact_process.php',
                        data: {action: "GET_HOUSE_AUTOCOMPLETE", search: request.term},
                        dataType: "json",
                        success: function (data) {
                            if (Array.isArray(data)) {
                                response(data.map(function(item) {
                                    return {label: item, value: item};
                                }));
                            } else {
                                response([]);
                            }
                        }
                    });
                },
                minLength: 1,
                select: function (event, ui) {
                    $("#house_number").val(ui.item.value);
                    $("#btnSearch").click();
                }
            });

            // Tab Switching
            $('#tabCheckIn').click(function () {
                $(this).removeClass('btn-light').addClass('btn-primary');
                $('#tabActiveList').removeClass('btn-primary').addClass('btn-light');
                $('#checkInSection').show();
                $('#activeListSection').hide();
            });

            $('#tabActiveList').click(function () {
                $(this).removeClass('btn-light').addClass('btn-primary');
                $('#tabCheckIn').removeClass('btn-primary').addClass('btn-light');
                $('#checkInSection').hide();
                $('#activeListSection').show();
                loadAllActiveVisitors();
            });

            // Search Action
            $("#btnSearch").click(function () {
                let house_number = $('#house_number').val().trim();
                if (house_number === '') {
                    alert("กรุณากรอกบ้านเลขที่");
                    return;
                }
                $.ajax({
                    type: "POST",
                    url: 'model/manage_visitor_contact_process.php',
                    data: {action: "GET_DATA_BY_HOUSE", house_number: house_number},
                    dataType: "json",
                    success: function (response) {
                        $('#resultSection').hide();
                        $('#formSection').hide();
                        $('#listSection').hide();
                        $('#notFoundSection').hide();
                        $('#btnCheckOut').hide();
                        $('#btnDelete').hide();

                        if (response && response.house) {
                            let house = response.house;
                            currentHouseNumber = house.house_number;
                            $('#displayHouseNumber').text(house.house_number);
                            $('#contact_name').text(house.contact_name || '-');
                            $('#phone_number').text(house.phone_number || '-');
                            $('#alley').text(house.alley || '-');

                            $('#resultSection').show();
                            
                            currentVisitors = response.visitors || [];
                            if (currentVisitors.length > 0) {
                                displayLocalVisitorList(currentVisitors);
                                $('#listSection').show();
                            }
                        } else {
                            currentHouseNumber = '';
                            $('#notFoundHouseNumber').text(house_number);
                            $('#notFoundSection').show();
                        }
                    }
                });
            });

            $("#house_number").keypress(function (e) {
                if (e.which == 13) {
                    $("#btnSearch").click();
                }
            });

            // Click New Visitor Button
            $("#btnNew").click(function () {
                resetForm();
                $('#formSection').show();
                currentVisitorId = 0;
                uploadedPictures = {picture_1: '', picture_2: '', picture_3: '', picture_4: '', picture_5: ''};
                $('#check_in_status').prop('checked', true);
            });

            // Save Visitor Action
            $("#btnSave").click(function () {
                saveVisitor();
            });

            // Direct check out in edit form
            $("#btnCheckOut").click(function () {
                if (currentVisitorId > 0) {
                    checkOutVisitorDirect(currentVisitorId, function() {
                        $('#btnSearch').click();
                        loadActiveCount();
                    });
                }
            });

            // Delete Visitor Action
            $("#btnDelete").click(function () {
                if (currentVisitorId > 0 && confirm('ต้องการลบข้อมูลนี้หรือไม่?')) {
                    $.ajax({
                        type: "POST",
                        url: 'model/manage_visitor_contact_process.php',
                        data: {action: "DELETE_VISITOR", id: currentVisitorId},
                        dataType: "json",
                        success: function (response) {
                            if (response && response.result === '1') {
                                alert("ลบข้อมูลสำเร็จ");
                                resetForm();
                                $('#formSection').hide();
                                $('#btnSearch').click();
                                loadActiveCount();
                            } else {
                                alert("ไม่สามารถลบข้อมูลได้");
                            }
                        }
                    });
                }
            });

            // Card exchange auto fill datetime
            $('#card_exchange').change(function() {
                // Keep it optional
            });

            // Refresh Active List
            $('#btnRefreshActive').click(function () {
                loadAllActiveVisitors();
            });

            // Search filtering for active list
            $('#searchActiveVisitor').on('keyup', function () {
                filterActiveList($(this).val());
            });

            // Handle file upload previews
            for (let i = 1; i <= 5; i++) {
                $(`#picture_${i}`).change(function() {
                    handlePicturePreview(this, i);
                });
            }

            // Load active count initially
            loadActiveCount();
        });

        // Load all visitors currently inside
        function loadAllActiveVisitors() {
            let container = $('#activeVisitorListBody');
            container.html('<div class="text-center text-muted p-4"><i class="fa fa-spinner fa-spin mr-1"></i> กำลังโหลดข้อมูล...</div>');

            $.ajax({
                type: "POST",
                url: 'model/manage_visitor_contact_process.php',
                data: {action: "GET_ACTIVE_VISITORS"},
                dataType: "json",
                success: function (data) {
                    allActiveVisitors = data || [];
                    displayActiveVisitorsList(allActiveVisitors);
                    $('#activeCount').text(allActiveVisitors.length);
                },
                error: function() {
                    container.html('<div class="text-center text-danger p-4">เกิดข้อผิดพลาดในการดึงข้อมูล</div>');
                }
            });
        }

        function loadActiveCount() {
            $.ajax({
                type: "POST",
                url: 'model/manage_visitor_contact_process.php',
                data: {action: "GET_ACTIVE_VISITORS"},
                dataType: "json",
                success: function (data) {
                    if (data) {
                        $('#activeCount').text(data.length);
                    }
                }
            });
        }

        // Display Active Visitors
        function displayActiveVisitorsList(visitors) {
            let container = $('#activeVisitorListBody');
            container.empty();

            if (visitors.length === 0) {
                container.html('<div class="text-center text-muted p-4">ไม่มีผู้ติดต่อที่อยู่ในหมู่บ้าน ณ ขณะนี้</div>');
                return;
            }

            visitors.forEach(function(v) {
                let pic = v.picture_1 ? 'uploads/visitor/' + v.picture_1 : 'img/avatar.png'; // default fallback
                let cardExchangeText = v.card_exchange === 'Y' ? `<span class="badge badge-success small">แลกบัตร: ${v.card_no || '-'}</span>` : '<span class="badge badge-light text-muted small">ไม่ได้แลกบัตร</span>';
                let phone = v.phone_number ? `<a href="tel:${v.phone_number}" class="btn btn-outline-info btn-xs py-0 px-1 ml-1"><i class="fa fa-phone small"></i> โทร</a>` : '';
                let licPlate = v.license_plate ? `<span class="badge badge-dark ml-1">🚗 ${v.license_plate}</span>` : '';

                let item = $(`
                    <div class="card mb-2 shadow-xs border-0 visitor-item-card" data-search="${v.visitor_name} ${v.license_plate} ${v.house_number} ${v.card_no}">
                        <div class="card-body p-2 d-flex align-items-center">
                            <div class="mr-2" style="width: 50px; height: 50px; flex-shrink: 0;">
                                <img src="${pic}" class="w-100 h-100 rounded img-thumbnail" style="object-fit:cover;" onerror="this.src='img/logo/logo.png'">
                            </div>
                            <div class="flex-grow-1" style="min-width: 0;">
                                <div class="d-flex justify-content-between align-items-center">
                                    <span class="badge badge-primary font-weight-bold">บ้าน ${v.house_number}</span>
                                    <span class="text-muted small"><i class="fa fa-clock mr-1"></i>${v.check_in_datetime.substr(11,5)} น.</span>
                                </div>
                                <h6 class="mb-0 mt-1 font-weight-bold text-dark text-truncate">${v.visitor_name} ${phone}</h6>
                                <p class="mb-0 text-muted small text-truncate">
                                    ${v.visitor_type || 'ผู้มาเยือน'} ${licPlate}
                                </p>
                                <div class="mt-1">${cardExchangeText}</div>
                            </div>
                            <div class="ml-2">
                                <button type="button" class="btn btn-warning btn-sm font-weight-bold btn-checkout-direct" data-id="${v.id}">
                                    ออก
                                </button>
                            </div>
                        </div>
                    </div>
                `);
                container.append(item);
            });

            $('.btn-checkout-direct').click(function(e) {
                e.stopPropagation();
                let id = $(this).data('id');
                if (confirm('ยืนยันบันทึกการออกจากหมู่บ้าน?')) {
                    checkOutVisitorDirect(id, function() {
                        loadAllActiveVisitors();
                    });
                }
            });

            // Click card to edit/view details
            $('.visitor-item-card').click(function() {
                let card = $(this);
                let id = card.find('.btn-checkout-direct').data('id');
                // Switch to form tab and populate data
                $('#tabCheckIn').click();
                
                // Fetch details by house of this visitor
                let visitor = allActiveVisitors.find(item => item.id == id);
                if (visitor) {
                    $('#house_number').val(visitor.house_number);
                    
                    // Search house first to populate house details
                    $.ajax({
                        type: "POST",
                        url: 'model/manage_visitor_contact_process.php',
                        data: {action: "GET_DATA_BY_HOUSE", house_number: visitor.house_number},
                        dataType: "json",
                        success: function (response) {
                            if (response && response.house) {
                                let house = response.house;
                                currentHouseNumber = house.house_number;
                                $('#displayHouseNumber').text(house.house_number);
                                $('#contact_name').text(house.contact_name || '-');
                                $('#phone_number').text(house.phone_number || '-');
                                $('#alley').text(house.alley || '-');
                                $('#resultSection').show();
                                displayLocalVisitorList(response.visitors || []);
                                $('#listSection').show();
                                
                                // Show and fill form
                                editVisitor(id, response.visitors || []);
                            }
                        }
                    });
                }
            });
        }

        // Filter active list locally
        function filterActiveList(query) {
            query = query.toLowerCase().trim();
            $('.visitor-item-card').each(function () {
                let text = $(this).data('search').toLowerCase();
                if (text.indexOf(query) > -1) {
                    $(this).show();
                } else {
                    $(this).hide();
                }
            });
        }

        // Direct Check out API call
        function checkOutVisitorDirect(id, callback) {
            $.ajax({
                type: "POST",
                url: 'model/manage_visitor_contact_process.php',
                data: {action: "CHECK_OUT", id: id},
                dataType: "json",
                success: function (response) {
                    if (response && response.result === '1') {
                        alert("บันทึกการออกสำเร็จ");
                        if (callback) callback();
                    } else {
                        alert("เกิดข้อผิดพลาดในการบันทึก");
                    }
                },
                error: function() {
                    alert("เกิดข้อผิดพลาดในการเชื่อมต่อ");
                }
            });
        }

        // Display local visitor history (today)
        function displayLocalVisitorList(visitors) {
            let container = $('#visitorListBody');
            container.empty();

            visitors.forEach(function(v) {
                let cardExchange = v.card_exchange === 'Y' ? '<span class="badge badge-success small">บัตร</span>' : '<span class="badge badge-light text-muted small">-</span>';
                let statusBadge = v.check_in_status === 'Y' 
                    ? '<span class="badge badge-success small">อยู่ในหมู่บ้าน</span>' 
                    : '<span class="badge badge-secondary small">ออกแล้ว</span>';
                
                let checkInTime = v.check_in_datetime ? v.check_in_datetime.substr(11, 5) + ' น.' : '-';
                let checkOutTime = v.check_out_datetime ? v.check_out_datetime.substr(11, 5) + ' น.' : '-';
                let licPlate = v.license_plate ? ` | 🚗 ${v.license_plate}` : '';

                let item = $(`
                    <button type="button" class="list-group-item list-group-item-action p-2 d-flex justify-content-between align-items-center btnEditLocal" data-id="${v.id}">
                        <div>
                            <div class="font-weight-bold text-dark small">${v.visitor_name} (${v.visitor_type || '-'})</div>
                            <div class="text-muted" style="font-size: 11px;">
                                เข้า: ${checkInTime} | ออก: ${checkOutTime} ${licPlate}
                            </div>
                        </div>
                        <div class="text-right">
                            <div>${statusBadge}</div>
                            <div class="mt-1">${cardExchange}</div>
                        </div>
                    </button>
                `);
                container.append(item);
            });

            $('.btnEditLocal').click(function() {
                let id = $(this).data('id');
                editVisitor(id, visitors);
            });
        }

        // Load details to form for editing
        function editVisitor(id, visitors) {
            let visitor = visitors.find(function(v) { return v.id == id; });
            if (visitor) {
                currentVisitorId = id;
                $('#visitor_name').val(visitor.visitor_name || '');
                $('#visitor_phone').val(visitor.phone_number || '');
                $('#license_plate').val(visitor.license_plate || '');
                $('#visitor_type').val(visitor.visitor_type || '');
                $('#purpose').val(visitor.purpose || '');
                $('#note').val(visitor.note || '');

                $('#card_exchange').prop('checked', visitor.card_exchange === 'Y');
                $('#card_no').val(visitor.card_no || '');

                $('#check_in_status').prop('checked', visitor.check_in_status === 'Y');
                
                if (visitor.check_in_status === 'Y') {
                    $('#status_badge').removeClass('badge-secondary').addClass('badge-success').text('อยู่ในหมู่บ้าน');
                    $('#btnCheckOut').show();
                } else {
                    $('#status_badge').removeClass('badge-success').addClass('badge-secondary').text('ออกแล้ว');
                    $('#btnCheckOut').hide();
                }

                $('#display_check_in_time').text(visitor.check_in_datetime || '-');
                $('#display_check_out_time').text(visitor.check_out_datetime || '-');
                
                $('#btnDelete').show();

                uploadedPictures = {
                    picture_1: visitor.picture_1 || '',
                    picture_2: visitor.picture_2 || '',
                    picture_3: visitor.picture_3 || '',
                    picture_4: visitor.picture_4 || '',
                    picture_5: visitor.picture_5 || ''
                };

                displayPicturePreviews();
                $('#formSection').show();
                
                // scroll form into view
                $('#formSection')[0].scrollIntoView({ behavior: 'smooth' });
            }
        }

        // Preview images loaded from server
        function displayPicturePreviews() {
            let baseUrl = 'uploads/visitor/';
            for (let i = 1; i <= 5; i++) {
                let pic = uploadedPictures['picture_' + i];
                let container = $('#preview_picture_' + i);
                container.empty();
                if (pic) {
                    container.html('<img src="' + baseUrl + pic + '" class="w-100 h-100 rounded" style="object-fit:cover;">');
                } else {
                    container.html('<i class="fa fa-camera fa-2x text-secondary"></i>');
                }
            }
        }

        // Preview local selected image files
        function handlePicturePreview(input, index) {
            let file = input.files[0];
            let container = $(`#preview_picture_${index}`);
            if (file) {
                let reader = new FileReader();
                reader.onload = function(e) {
                    container.html('<img src="' + e.target.result + '" class="w-100 h-100 rounded" style="object-fit:cover;">');
                };
                reader.readAsDataURL(file);
            } else {
                container.html('<i class="fa fa-camera fa-2x text-secondary"></i>');
            }
        }

        // Reset form to blank state
        function resetForm() {
            $('#visitor_name').val('');
            $('#visitor_phone').val('');
            $('#license_plate').val('');
            $('#visitor_type').val('');
            $('#purpose').val('');
            $('#note').val('');
            $('#card_exchange').prop('checked', false);
            $('#card_no').val('');
            $('#check_in_status').prop('checked', true);
            $('#status_badge').removeClass('badge-success').addClass('badge-secondary').text('-');
            $('#display_check_in_time').text('-');
            $('#display_check_out_time').text('-');
            $('#btnCheckOut').hide();
            $('#btnDelete').hide();

            // Clear file inputs
            for (let i = 1; i <= 5; i++) {
                $(`#picture_${i}`).val('');
                $(`#preview_picture_${i}`).html('<i class="fa fa-camera fa-2x text-secondary"></i>');
            }
        }

        // Send Form Data to server
        function saveVisitor() {
            let house_number = currentHouseNumber;
            let visitor_name = $('#visitor_name').val().trim();
            let visitor_phone = $('#visitor_phone').val().trim();
            let license_plate = $('#license_plate').val().trim();
            let visitor_type = $('#visitor_type').val().trim();
            let purpose = $('#purpose').val().trim();
            let note = $('#note').val().trim();
            let card_exchange = $('#card_exchange').is(':checked') ? 'Y' : 'N';
            let card_no = $('#card_no').val().trim();

            if (house_number === '') {
                alert("กรุณาค้นหาบ้านก่อน");
                return;
            }
            if (visitor_name === '') {
                alert("กรุณากรอกชื่อผู้มาติดต่อ");
                return;
            }

            let formData = new FormData();
            formData.append('action', 'SAVE_VISITOR');
            formData.append('id', currentVisitorId);
            formData.append('house_number', house_number);
            formData.append('visitor_name', visitor_name);
            formData.append('visitor_phone', visitor_phone);
            formData.append('license_plate', license_plate);
            formData.append('visitor_type', visitor_type);
            formData.append('purpose', purpose);
            formData.append('note', note);
            formData.append('card_exchange', card_exchange);
            formData.append('card_no', card_no);
            formData.append('check_in_status', $('#check_in_status').is(':checked') ? 'Y' : 'N');

            // Attach files
            for (let i = 1; i <= 5; i++) {
                let fileInput = $(`#picture_${i}`)[0];
                if (fileInput.files[0]) {
                    formData.append(`picture_${i}`, fileInput.files[0]);
                }
                formData.append(`existing_picture_${i}`, uploadedPictures[`picture_${i}`] || '');
            }

            // Disable save button to avoid double submits
            $('#btnSave').attr('disabled', true);

            $.ajax({
                type: 'POST',
                url: 'model/manage_visitor_contact_process.php',
                data: formData,
                processData: false,
                contentType: false,
                dataType: 'json',
                success: function (response) {
                    $('#btnSave').attr('disabled', false);
                    if (response && response.result === '1') {
                        alert("บันทึกข้อมูลเรียบร้อยแล้ว");
                        resetForm();
                        $('#formSection').hide();
                        $('#btnSearch').click();
                        loadActiveCount();
                    } else {
                        alert("ไม่สามารถบันทึกได้");
                    }
                },
                error: function () {
                    $('#btnSave').attr('disabled', false);
                    alert("เกิดข้อผิดพลาดในการส่งข้อมูล");
                }
            });
        }
    </script>
</body>
</html>