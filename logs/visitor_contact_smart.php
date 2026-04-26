<?php
include('includes/Header.php');
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ติดต่อลูกบ้าน</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.13.0/css/all.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    <div class="container py-3">
        <div class="row">
            <div class="col-12">
                <h4 class="text-center mb-3">ติดต่อลูกบ้าน</h4>
                
                <div class="card mb-3">
                    <div class="card-body">
                        <div class="form-group">
                            <label class="control-label">บ้านเลขที่</label>
                            <div class="input-group">
                                <input type="text" class="form-control form-control-sm"
                                       id="house_number"
                                       placeholder="กรอกบ้านเลขที่">
                                <div class="input-group-append">
                                    <button type="button" class="btn btn-info btn-sm" id="btnSearch">
                                        <i class="fa fa-search"></i>
                                    </button>
                                    <button type="button" class="btn btn-success btn-sm" id="btnSave" style="display:none;">
                                        <i class="fa fa-save"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                        <button type="button" class="btn btn-primary btn-block btn-sm" id="btnNew" disabled>
                            <i class="fa fa-plus"></i> บันทึกใหม่
                        </button>
                    </div>
                </div>

                <div id="resultSection" style="display:none;">
                    <div class="card mb-2">
                        <div class="card-body py-2">
                            <small class="text-muted">บ้านเลขที่: <span id="displayHouseNumber"></span></small><br>
                            <strong><span id="contact_name"></span></strong><br>
                            <small>เบอร์: <span id="phone_number"></span> ซอย: <span id="alley"></span></small>
                        </div>
                    </div>
                </div>

                <div id="formSection" style="display:none;">
                    <div class="card mb-2">
                        <div class="card-body">
                            <h5 class="mb-2">ข้อมูลผู้มาติดต่อ</h5>
                            
                            <div class="form-group">
                                <label class="control-label">ชื่อ <span class="text-danger">*</span></label>
                                <input type="text" class="form-control form-control-sm"
                                       id="visitor_name" placeholder="">
                            </div>
                            <div class="form-group">
                                <label class="control-label">เบอร์</label>
                                <input type="text" class="form-control form-control-sm"
                                       id="visitor_phone" placeholder="">
                            </div>
                            <div class="form-group">
                                <label class="control-label">ประเภท</label>
                                <select class="form-control form-control-sm" id="visitor_type">
                                    <option value="">-- เลือก --</option>
                                    <option value="ส่งอาหาร">ส่งอาหาร</option>
                                    <option value="ส่งพัสดุ/ส่งของ">ส่งพัสดุ/ส่งของ</option>
                                    <option value="ญาติ">ญาติ</option>
                                    <option value="ติดต่อนิติฯ">ติดต่อนิติฯ</option>
                                    <option value="อื่นๆ">อื่นๆ</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label class="control-label">วัตถุประสงค์</label>
                                <input type="text" class="form-control form-control-sm"
                                       id="purpose" placeholder="">
                            </div>
                            <div class="form-group">
                                <label class="control-label">หมายเหตุ</label>
                                <input type="text" class="form-control form-control-sm"
                                       id="note" placeholder="">
                            </div>

                            <div class="form-group">
                                <label class="control-label"><strong>รูปภาพ</strong></label>
                                <div class="row">
                                    <div class="col-12 mb-2">
                                        <input type="file" class="form-control form-control-sm" id="picture_1" accept="image/*">
                                        <div id="preview_picture_1" class="mt-1"></div>
                                    </div>
                                    <div class="col-12 mb-2">
                                        <input type="file" class="form-control form-control-sm" id="picture_2" accept="image/*">
                                        <div id="preview_picture_2" class="mt-1"></div>
                                    </div>
                                    <div class="col-12 mb-2">
                                        <input type="file" class="form-control form-control-sm" id="picture_3" accept="image/*">
                                        <div id="preview_picture_3" class="mt-1"></div>
                                    </div>
                                    <div class="col-12 mb-2">
                                        <input type="file" class="form-control form-control-sm" id="picture_4" accept="image/*">
                                        <div id="preview_picture_4" class="mt-1"></div>
                                    </div>
                                    <div class="col-12 mb-2">
                                        <input type="file" class="form-control form-control-sm" id="picture_5" accept="image/*">
                                        <div id="preview_picture_5" class="mt-1"></div>
                                    </div>
                                </div>
                            </div>

                            <div class="form-group">
                                <div class="form-check">
                                    <input type="checkbox" class="form-check-input" id="card_exchange" value="Y">
                                    <label class="form-check-label" for="card_exchange">แลกบัตรแล้ว</label>
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="control-label">หมายเลขบัตร</label>
                                <input type="text" class="form-control form-control-sm" id="card_no" placeholder="เลขบัตร">
                            </div>

                            <div class="form-group">
                                <div class="form-check">
                                    <input type="checkbox" class="form-check-input" id="check_in_status" value="Y">
                                    <label class="form-check-label" for="check_in_status">อยู่ในหมู่บ้าน</label>
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="control-label small">สถานะ</label>
                                <div>
                                    <span class="badge badge-secondary" id="status_badge">-</span>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-6">
                                    <label class="control-label small">เข้า</label>
                                    <input type="text" class="form-control form-control-sm" id="check_in_datetime" readonly>
                                </div>
                                <div class="col-6">
                                    <label class="control-label small">ออก</label>
                                    <input type="text" class="form-control form-control-sm" id="check_out_datetime" readonly>
                                </div>
                            </div>

                            <div class="mt-2">
                                <!--button type="button" class="btn btn-primary btn-sm mb-1" id="btnCheckIn">
                                    <i class="fa fa-sign-in-alt"></i> Check In
                                </button-->
                                <button type="button" class="btn btn-warning btn-sm mr-1 mb-1" id="btnCheckOut">
                                    <i class="fa fa-sign-out-alt"></i> ออก
                                </button>
                                <button type="button" class="btn btn-danger btn-sm mb-1" id="btnDelete">
                                    <i class="fa fa-trash"></i> ลบ
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <div id="listSection" style="display:none;">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0">รายการ</h5>
                        </div>
                        <div class="card-body p-0">
                            <div id="visitorListBody"></div>
                        </div>
                    </div>
                </div>

                <div id="notFoundSection" style="display:none;" class="alert alert-warning">
                    ไม่พบบ้าน: <span id="notFoundHouseNumber"></span>
                </div>
            </div>
        </div>
    </div>

    <script src="vendor/jquery/jquery.min.js"></script>
    <script src="vendor/bootstrap/js/bootstrap.bundle.min.js"></script>

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/jqueryui/1.12.1/jquery-ui.min.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jqueryui/1.12.1/jquery-ui.min.js"></script>

    <script>
        let currentHouseNumber = '';
        let currentVisitorId = 0;
        let uploadedPictures = {picture_1: '', picture_2: '', picture_3: '', picture_4: '', picture_5: ''};
        let currentVisitors = [];

        $(document).ready(function () {
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

            $("#btnSearch").click(function () {
                let house_number = $('#house_number').val();
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
                        $('#btnSave').hide();
                        $('#btnNew').prop('disabled', true);

                        if (response && response.house) {
                            let house = response.house;
                            currentHouseNumber = house.house_number;
                            $('#displayHouseNumber').text(house.house_number);
                            $('#contact_name').text(house.contact_name || '');
                            $('#phone_number').text(house.phone_number || '');
                            $('#alley').text(house.alley || '');

                            $('#resultSection').show();
                            $('#btnNew').prop('disabled', false);

                            currentVisitors = response.visitors || [];
                            if (currentVisitors.length > 0) {
                                displayVisitorList(currentVisitors);
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

            $("#btnNew").click(function () {
                resetForm();
                $('#formSection').show();
                $('#btnSave').show();
                currentVisitorId = 0;
                uploadedPictures = {picture_1: '', picture_2: '', picture_3: '', picture_4: '', picture_5: ''};
            });

            $("#btnSave").click(function () {
                saveVisitor();
            });

            $("#btnCheckIn").click(function () {
                if (currentVisitorId > 0) {
                    let now = getCurrentDatetime();
                    $('#check_in_datetime').val(now);
                    $('#check_out_datetime').val('');
                    $('#check_in_status').prop('checked', true);
                    saveCheckInOut();
                } else {
                    alert("กรุณาเลือกรายการก่อน");
                }
            });

            $("#btnCheckOut").click(function () {
                if (currentVisitorId > 0) {
                    let now = getCurrentDatetime();
                    $('#check_out_datetime').val(now);
                    $('#check_in_status').prop('checked', false);
                    saveCheckInOut();
                } else {
                    alert("กรุณาเลือกรายการก่อน");
                }
            });

            $("#btnDelete").click(function () {
                if (currentVisitorId > 0) {
                    if (confirm('ต้องการลบหรือไม่?')) {
                        deleteVisitor();
                    }
                }
            });

            $('#card_exchange').change(function() {
                $('#card_exchange_date').val($(this).is(':checked') ? getCurrentDatetime() : '');
            });

            $('#picture_1, #picture_2, #picture_3, #picture_4, #picture_5').change(function() {
                handlePicturePreview(this);
            });

            function getCurrentDatetime() {
                let now = new Date();
                return now.getFullYear() + '-' + 
                       String(now.getMonth() + 1).padStart(2, '0') + '-' + 
                       String(now.getDate()).padStart(2, '0') + ' ' + 
                       String(now.getHours()).padStart(2, '0') + ':' + 
                       String(now.getMinutes()).padStart(2, '0') + ':' + 
                       String(now.getSeconds()).padStart(2, '0');
            }

            function displayVisitorList(visitors) {
                let container = $('#visitorListBody');
                container.empty();

                let activeVisitors = visitors.filter(function(v) {
                    return !v.check_out_datetime;
                });

                if (activeVisitors.length === 0) {
                    container.html('<div class="text-center text-muted p-3">ไม่มีผู้ติดต่อที่อยู่ในหมู่บ้าน</div>');
                    return;
                }

                activeVisitors.forEach(function(v, index) {
                    let cardExchange = v.card_exchange === 'Y' ? '<span class="badge badge-success">บัตร</span>' : '<span class="badge badge-secondary">-</span>';
                    let checkInTime = v.check_in_datetime ? v.check_in_datetime.replace(' ', '<br>') : '-';

                    let card = $('<div class="card mb-2">');
                    card.append('<div class="card-body p-2">');
                    card.find('.card-body').append('<h6 class="mb-1">' + v.visitor_name + '</h6>');
                    card.find('.card-body').append('<p class="mb-0 small">' + (v.visitor_type || '-') + ' | ' + (v.phone_number || '-') + '</p>');
                    card.find('.card-body').append('<p class="mb-0 small">' + cardExchange + ' | เข้า: ' + checkInTime + '</p>');
                    card.find('.card-body').append('<button class="btn btn-sm btn-primary btnEdit w-100 mt-1" data-id="' + v.id + '">แก้ไข</button>');
                    container.append(card);
                });

                $('.btnEdit').click(function() {
                    let id = $(this).data('id');
                    editVisitor(id, visitors);
                });
            }

            function editVisitor(id, visitors) {
                let visitor = visitors.find(function(v) { return v.id == id; });
                if (visitor) {
                    currentVisitorId = id;
                    $('#visitor_name').val(visitor.visitor_name || '');
                    $('#visitor_phone').val(visitor.phone_number || '');
                    $('#visitor_type').val(visitor.visitor_type || '');
                    $('#purpose').val(visitor.purpose || '');
                    $('#note').val(visitor.note || '');

                    $('#card_exchange').prop('checked', visitor.card_exchange === 'Y');
                    $('#card_no').val(visitor.card_no || '');
                    $('#card_exchange_date').val(visitor.card_exchange_date || '');

                    $('#check_in_datetime').val(visitor.check_in_datetime || '');
                    $('#check_out_datetime').val(visitor.check_out_datetime || '');
                    
                    if (visitor.check_in_status === 'Y') {
                        $('#status_badge').removeClass('badge-secondary').addClass('badge-success').text('อยู่ในหมู่บ้าน');
                    } else {
                        $('#status_badge').removeClass('badge-success').addClass('badge-secondary').text('ไม่อยู่');
                    }

                    uploadedPictures = {
                        picture_1: visitor.picture_1 || '',
                        picture_2: visitor.picture_2 || '',
                        picture_3: visitor.picture_3 || '',
                        picture_4: visitor.picture_4 || '',
                        picture_5: visitor.picture_5 || ''
                    };

                    displayPicturePreviews();
                    $('#formSection').show();
                    $('#btnSave').show();
                }
            }

            function displayPicturePreviews() {
                let baseUrl = 'uploads/visitor/';
                for (let i = 1; i <= 5; i++) {
                    let pic = uploadedPictures['picture_' + i];
                    let container = $('#preview_picture_' + i);
                    container.empty();
                    if (pic) {
                        container.html('<img src="' + baseUrl + pic + '" style="width:50px;height:50px;object-fit:cover;" class="img-thumbnail">');
                    }
                }
            }

            function handlePicturePreview(input) {
                let file = input.files[0];
                if (file) {
                    let reader = new FileReader();
                    let fieldId = $(input).attr('id');
                    reader.onload = function(e) {
                        $('#preview_picture_' + fieldId).html('<img src="' + e.target.result + '" style="width:50px;height:50px;object-fit:cover;" class="img-thumbnail">');
                    };
                    reader.readAsDataURL(file);
                }
            }

            function resetForm() {
                $('#visitor_name').val('');
                $('#visitor_phone').val('');
                $('#visitor_type').val('');
                $('#purpose').val('');
                $('#note').val('');
                $('#card_exchange').prop('checked', false);
                $('#card_no').val('');
                $('#card_exchange_date').val('');
                $('#check_in_datetime').val('');
                $('#check_out_datetime').val('');
                $('#status_badge').removeClass('badge-success').addClass('badge-secondary').text('-');

                $('#picture_1, #picture_2, #picture_3, #picture_4, #picture_5').val('');
                for (let i = 1; i <= 5; i++) {
                    $('#preview_picture_' + i).empty();
                }
            }

            function saveVisitor() {
                let house_number = currentHouseNumber;
                let visitor_name = $('#visitor_name').val().trim();
                let visitor_phone = $('#visitor_phone').val().trim();
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
                    alert("กรุณากรอกชื่อ");
                    return;
                }

                let formData = new FormData();
                formData.append('action', 'SAVE_VISITOR');
                formData.append('id', currentVisitorId);
                formData.append('house_number', house_number);
                formData.append('visitor_name', visitor_name);
                formData.append('visitor_phone', visitor_phone);
                formData.append('visitor_type', visitor_type);
                formData.append('purpose', purpose);
                formData.append('note', note);
                formData.append('card_exchange', card_exchange);
                formData.append('card_no', card_no);
                formData.append('check_in_status', $('#check_in_status').is(':checked') ? 'Y' : 'N');

                if ($('#picture_1')[0].files[0]) formData.append('picture_1', $('#picture_1')[0].files[0]);
                if ($('#picture_2')[0].files[0]) formData.append('picture_2', $('#picture_2')[0].files[0]);
                if ($('#picture_3')[0].files[0]) formData.append('picture_3', $('#picture_3')[0].files[0]);
                if ($('#picture_4')[0].files[0]) formData.append('picture_4', $('#picture_4')[0].files[0]);
                if ($('#picture_5')[0].files[0]) formData.append('picture_5', $('#picture_5')[0].files[0]);

                formData.append('existing_picture_1', uploadedPictures.picture_1);
                formData.append('existing_picture_2', uploadedPictures.picture_2);
                formData.append('existing_picture_3', uploadedPictures.picture_3);
                formData.append('existing_picture_4', uploadedPictures.picture_4);
                formData.append('existing_picture_5', uploadedPictures.picture_5);

                $.ajax({
                    type: 'POST',
                    url: 'model/manage_visitor_contact_process.php',
                    data: formData,
                    processData: false,
                    contentType: false,
                    dataType: 'json',
                    success: function (response) {
                        if (response && response.result === '1') {
                            alert("บันทึกสำเร็จ");
                            currentVisitorId = response.id || 0;
                            $('#btnSearch').click();
                        } else {
                            alert("ไม่สามารถบันทึกได้");
                        }
                    },
                    error: function () {
                        alert("เกิดข้อผิดพลาด");
                    }
                });
            }

            function saveCheckInOut() {
                $.ajax({
                    type: "POST",
                    url: 'model/manage_visitor_contact_process.php',
                    data: {
                        action: "SAVE_CHECK_IN_OUT",
                        id: currentVisitorId,
                        check_in_status: $('#check_in_status').is(':checked') ? 'Y' : 'N',
                        check_in_datetime: $('#check_in_datetime').val(),
                        check_out_datetime: $('#check_out_datetime').val()
                    },
                    dataType: "json",
                    success: function (response) {
                        // Silent save
                    }
                });
            }
        });
    </script>
</body>
</html>