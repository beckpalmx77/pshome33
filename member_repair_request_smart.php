<?php
include('includes/Header.php');
include('config/connect_db.php');
$curr_date = date("d-m-Y");
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1"/>
    <style>
        .gap-2 > * {
            margin: 4px;
        }
        .img-preview-clickable:hover {
            opacity: 0.8;
            border-color: #4e73df !important;
        }
    </style>
</head>
<body id="page-top">
<div id="wrapper">
    <div id="content-wrapper" class="d-flex flex-column">
        <div id="content">
            <div class="container-fluid" id="container-wrapper" style="padding-top: 20px;">
                <div class="d-sm-flex align-items-center justify-content-between mb-4">
                    <h4 class="h5 mb-0 text-gray-800"><i class="fa fa-tools text-primary"></i> ระบบแจ้งซ่อมสำหรับสมาชิก (Smart)</h4>
                    <div class="text-sm text-muted font-weight-bold" id="user-info-liff">กำลังโหลดข้อมูลผู้ใช้...</div>
                </div>

                <div class="row">
                    <!-- Left: Request Form -->
                    <div class="col-lg-5">
                        <div class="card mb-4 shadow-sm border-left-primary">
                            <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                                <h6 class="m-0 font-weight-bold text-primary"><i class="fa fa-wrench"></i> แบบฟอร์มแจ้งซ่อม / แจ้งปัญหา</h6>
                            </div>
                            <div class="card-body">
                                <form method="POST" enctype="multipart/form-data" id="repair_form">
                                    <div class="form-group">
                                        <label for="display_name" class="font-weight-bold">ผู้แจ้งซ่อม (LINE)</label>
                                        <input type="text" class="form-control bg-light" id="display_name" readonly placeholder="กำลังโหลด...">
                                    </div>
                                    <div class="row">
                                        <div class="col-6">
                                            <div class="form-group">
                                                <label for="house_number_display" class="font-weight-bold">บ้านเลขที่</label>
                                                <input type="text" class="form-control bg-light" id="house_number_display" readonly placeholder="กำลังโหลด...">
                                                <input type="hidden" id="house_number" name="house_number">
                                            </div>
                                        </div>
                                        <div class="col-6">
                                            <div class="form-group">
                                                <label for="place_name" class="font-weight-bold">สถานที่เกิดเหตุ</label>
                                                <input type="text" class="form-control" id="place_name" name="place_name" required placeholder="ระบุเลขที่บ้าน หรือ ซอย">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label for="subject" class="font-weight-bold">เรื่อง / หัวข้อการแจ้งซ่อม <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control" id="subject" name="subject" required placeholder="เช่น ท่อประปารั่วซึม, ไฟถนนดับ, ประตูรั้วชำรุด">
                                    </div>
                                    <div class="form-group">
                                        <label for="remark" class="font-weight-bold">รายละเอียดปัญหา <span class="text-danger">*</span></label>
                                        <textarea class="form-control" id="remark" name="remark" rows="4" required placeholder="ระบุอาการชำรุด บริเวณที่เป็นปัญหา หรือวันเวลาที่สะดวกให้ช่างเข้าดำเนินการ"></textarea>
                                    </div>
                                    <div class="form-group">
                                        <label for="images" class="font-weight-bold">แนบรูปภาพปัญหา (สูงสุด 3 รูป)</label>
                                        <div class="custom-file">
                                            <input type="file" class="custom-file-input" id="images" name="images[]" accept="image/*" multiple>
                                            <label class="custom-file-label" for="images">เลือกรูปภาพ...</label>
                                        </div>
                                        <small class="form-text text-muted">รองรับไฟล์รูปภาพ (.jpg, .jpeg, .png, .webp, .gif) ขนาดไม่เกิน 10MB ต่อรูป</small>
                                        <div id="imagePreviewContainer" class="d-flex flex-wrap gap-2 mt-2"></div>
                                    </div>
                                    <button type="submit" class="btn btn-primary btn-block shadow-sm">
                                        <i class="fa fa-paper-plane"></i> ส่งข้อมูลแจ้งซ่อม
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>

                    <!-- Right: History List -->
                    <div class="col-lg-7">
                        <div class="card mb-4 shadow-sm">
                            <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                                <h6 class="m-0 font-weight-bold text-dark"><i class="fa fa-history"></i> ประวัติการแจ้งซ่อมของท่าน</h6>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-bordered table-hover align-items-center table-flush" id="dataTableRepair">
                                        <thead class="thead-light">
                                        <tr>
                                            <th>วันเวลาที่แจ้ง</th>
                                            <th>สถานที่</th>
                                            <th>รายละเอียดการแจ้งซ่อม</th>
                                            <th>รูปภาพ</th>
                                            <th>สถานะ / ช่างผู้ดำเนินการ</th>
                                        </tr>
                                        </thead>
                                        <tbody id="history_body">
                                        <tr>
                                            <td colspan="5" class="text-center text-muted py-4">กำลังโหลดข้อมูลผู้ใช้จาก LINE...</td>
                                        </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <?php include('includes/Footer.php'); ?>
    </div>
</div>

<!-- Scroll to top -->
<a class="scroll-to-top rounded" href="#page-top">
    <i class="fas fa-angle-up"></i>
</a>

<!-- Modal for Image Preview -->
<div class="modal fade" id="imageModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header border-0 pb-0">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body text-center pt-0">
                <img src="" id="modalImage" class="img-fluid rounded" style="max-height: 80vh;">
            </div>
        </div>
    </div>
</div>

<script src="vendor/jquery/jquery.min.js"></script>
<script src="vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
<script src="vendor/jquery-easing/jquery.easing.min.js"></script>
<script src="js/myadmin.min.js"></script>
<script src="vendor/datatables/v11/jquery.dataTables.min.js"></script>
<link rel="stylesheet" href="vendor/datatables/v11/jquery.dataTables.min.css"/>

<!-- LINE LIFF SDK -->
<script src="https://static.line-scdn.net/liff/edge/2/sdk.js"></script>
<script src="line_oa/house/jsconfig/config_house_repair.js"></script>

<script>
    window.lineUserId = '';
    window.lineDisplayName = '';
    window.houseNumber = '';

    $(document).ready(function () {
        // Initialize LIFF
        liff.init({liffId: LIFF_ID})
            .then(() => {
                if (!liff.isLoggedIn()) {
                    liff.login();
                } else {
                    liff.getProfile().then(profile => {
                        window.lineUserId = profile.userId;
                        window.lineDisplayName = profile.displayName;
                        $('#display_name').val(profile.displayName);
                        
                        // Fetch house number by LINE User ID
                        fetch('model/get_house_number_smart.php', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/x-www-form-urlencoded'
                            },
                            body: 'userId=' + encodeURIComponent(profile.userId)
                        })
                        .then(response => response.json())
                        .then(data => {
                            if (data.house_number) {
                                window.houseNumber = data.house_number;
                                $('#house_number_display').val(data.house_number);
                                $('#house_number').val(data.house_number);
                                $('#place_name').val(data.house_number);
                                $('#user-info-liff').html(`<i class="fa fa-home"></i> บ้านเลขที่: ${data.house_number} | <i class="fa fa-user"></i> คุณ${data.f_name} ${data.l_name}`);
                                
                                // Load history table
                                loadHistoryTable(profile.userId, data.house_number);
                            } else {
                                alert("ไม่พบข้อมูลผู้ใช้งาน LINE นี้ในฐานข้อมูลสมาชิก กรุณาลงทะเบียนบ้านก่อนใช้งานแจ้งซ่อม");
                                liff.closeWindow();
                            }
                        })
                        .catch(err => {
                            alert("เกิดข้อผิดพลาดในการดึงข้อมูลสมาชิก: " + err);
                        });
                    });
                }
            })
            .catch(err => {
                console.error("LIFF initialization failed", err);
                $('#history_body').html('<tr><td colspan="5" class="text-center text-danger">ไม่สามารถเชื่อมต่อ LINE LIFF ได้</td></tr>');
            });

        // Show image filename in custom file input label
        $('.custom-file-input').on('change', function() {
            let files = $(this)[0].files;
            let labelText = '';
            if (files.length === 1) {
                labelText = files[0].name;
            } else {
                labelText = files.length + ' รูปภาพถูกเลือก';
            }
            $(this).next('.custom-file-label').html(labelText);

            // Preview selected images
            $('#imagePreviewContainer').html('');
            let limit = Math.min(files.length, 3);
            for (let i = 0; i < limit; i++) {
                let file = files[i];
                let reader = new FileReader();
                reader.onload = function(e) {
                    let imgHtml = `<div class="position-relative m-1">
                        <img src="${e.target.result}" class="img-thumbnail" style="width: 80px; height: 80px; object-fit: cover;">
                    </div>`;
                    $('#imagePreviewContainer').append(imgHtml);
                }
                reader.readAsDataURL(file);
            }
        });

        // Form Submit via AJAX
        $('#repair_form').on('submit', function (e) {
            e.preventDefault();

            let subject = $('#subject').val().trim();
            let remark = $('#remark').val().trim();
            if (subject === '' || remark === '') {
                alertify.error('กรุณากรอกหัวข้อและรายละเอียดการแจ้งซ่อม');
                return;
            }

            let formData = new FormData(this);
            formData.append('action', 'ADD_REPAIR');
            formData.append('line_user_id', window.lineUserId);
            formData.append('line_display_name', window.lineDisplayName);

            // Disable submit button
            let submitBtn = $(this).find('button[type="submit"]');
            submitBtn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> กำลังส่งข้อมูล...');

            $.ajax({
                url: 'model/member_repair_request_smart_process.php',
                type: 'POST',
                data: formData,
                contentType: false,
                processData: false,
                dataType: 'json',
                success: function (response) {
                    submitBtn.prop('disabled', false).html('<i class="fa fa-paper-plane"></i> ส่งข้อมูลแจ้งซ่อม');
                    if (response.status === 'success') {
                        alertify.success(response.message);
                        $('#repair_form')[0].reset();
                        $('#imagePreviewContainer').html('');
                        $('.custom-file-label').html('เลือกรูปภาพ...');
                        
                        // Set placeholder values back
                        $('#display_name').val(window.lineDisplayName);
                        $('#house_number_display').val(window.houseNumber);
                        $('#house_number').val(window.houseNumber);
                        $('#place_name').val(window.houseNumber);

                        // Reload history
                        loadHistoryTable(window.lineUserId, window.houseNumber);
                    } else {
                        alertify.error(response.message);
                    }
                },
                error: function () {
                    submitBtn.prop('disabled', false).html('<i class="fa fa-paper-plane"></i> ส่งข้อมูลแจ้งซ่อม');
                    alertify.error('เกิดข้อผิดพลาดในการส่งข้อมูล');
                }
            });
        });

        // Click on image preview to zoom
        $(document).on('click', '.img-preview-clickable', function () {
            let src = $(this).attr('src');
            $('#modalImage').attr('src', src);
            $('#imageModal').modal('show');
        });
    });

    function loadHistoryTable(lineUserId, houseNumber) {
        $('#history_body').html('<tr><td colspan="5" class="text-center"><i class="fa fa-spinner fa-spin"></i> กำลังโหลดประวัติการแจ้งซ่อม...</td></tr>');

        $.ajax({
            url: 'model/member_repair_request_smart_process.php',
            type: 'POST',
            data: {
                action: 'GET_HISTORY',
                line_user_id: lineUserId,
                house_number: houseNumber
            },
            dataType: 'json',
            success: function (response) {
                $('#dataTableRepair').DataTable().destroy();
                let html = '';
                if (response.length > 0) {
                    response.forEach(req => {
                        let statusBadge = '<span class="badge badge-warning p-2"><i class="fa fa-clock-o"></i> รอดำเนินการ (Pending)</span>';
                        if (req.emp_id && req.emp_id !== '0' && req.emp_id !== '') {
                            let empName = req.emp_fullname;
                            if (req.emp_nickname) {
                                empName += ' (' + req.emp_nickname + ')';
                            }
                            if (req.check_type === 'REPAIR_DONE') {
                                statusBadge = `<span class="badge badge-success p-2"><i class="fa fa-check-circle"></i> ดำเนินการเสร็จสิ้น (Completed)</span><br><small class="text-muted mt-1 d-block">ช่าง: ${empName}</small>`;
                            } else {
                                statusBadge = `<span class="badge badge-info p-2"><i class="fa fa-spinner fa-spin"></i> กำลังดำเนินการ (In Progress)</span><br><small class="text-muted mt-1 d-block">ช่าง: ${empName}</small>`;
                            }
                        }

                        let photosHtml = '-';
                        if (req.photo_path && req.photo_path.trim() !== '') {
                            photosHtml = '';
                            let photos = req.photo_path.split(',');
                            photos.forEach(photo => {
                                photo = photo.trim();
                                if (photo !== '') {
                                    photosHtml += `<img src="line_oa/checkin/uploads/${photo}" class="img-thumbnail m-1 img-preview-clickable" style="width: 50px; height: 50px; object-fit: cover; cursor: pointer; border: 1px solid #ddd; padding: 2px;">`;
                                }
                            });
                        }

                        html += `<tr>
                            <td>${req.checkin_time}</td>
                            <td>${req.place_name}</td>
                            <td>
                                <strong>${req.subject}</strong>
                                <p class="small text-muted mb-0 mt-1">${req.remark.replace(/\n/g, '<br>')}</p>
                            </td>
                            <td class="text-center">${photosHtml}</td>
                            <td>${statusBadge}</td>
                        </tr>`;
                    });
                } else {
                    html = '<tr><td colspan="5" class="text-center text-muted py-4">ไม่พบประวัติการแจ้งซ่อมของท่าน</td></tr>';
                }
                $('#history_body').html(html);

                // Init DataTable
                $('#dataTableRepair').DataTable({
                    "order": [[0, "desc"]],
                    "destroy": true,
                    "language": {
                        "lengthMenu": "แสดง _MENU_ รายการต่อหน้า",
                        "zeroRecords": "ไม่พบข้อมูล",
                        "info": "แสดงหน้าที่ _PAGE_ จากทั้งหมด _PAGES_ หน้า",
                        "infoEmpty": "ไม่มีข้อมูล",
                        "infoFiltered": "(กรองจากทั้งหมด _MAX_ รายการ)",
                        "search": "ค้นหา:",
                        "paginate": {
                            "first": "หน้าแรก",
                            "last": "หน้าสุดท้าย",
                            "next": "ถัดไป",
                            "previous": "ก่อนหน้า"
                        }
                    }
                });
            },
            error: function () {
                $('#history_body').html('<tr><td colspan="5" class="text-center text-danger">เกิดข้อผิดพลาดในการโหลดประวัติการแจ้งซ่อม</td></tr>');
            }
        });
    }
</script>
</body>
</html>
