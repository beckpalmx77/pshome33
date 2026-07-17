<?php
include('includes/Header.php');
if (strlen($_SESSION['alogin']) == "" || strlen($_SESSION['house_number']) == "") {
    header("Location: index.php");
    exit();
} else {
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
                        <h1 class="h4 mb-0 text-gray-800">ตรวจสอบและบันทึกข้อมูลการเข้า-ออก (Visitor Logs)</h1>
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="<?php echo $_SESSION['dashboard_page'] ?>">Home</a></li>
                            <li class="breadcrumb-item"><?php echo urldecode($_GET['m']) ?></li>
                            <li class="breadcrumb-item active" aria-current="page"><?php echo urldecode($_GET['s']) ?></li>
                        </ol>
                    </div>

                    <!-- Search Filter Card -->
                    <div class="row mb-3">
                        <div class="col-lg-12">
                            <div class="card shadow-sm border-0">
                                <div class="card-header bg-white py-3 border-0">
                                    <h6 class="m-0 font-weight-bold text-primary"><i class="fa fa-filter mr-1"></i> ตัวกรองการค้นหา</h6>
                                </div>
                                <div class="card-body pt-0">
                                    <form id="filterForm">
                                        <div class="row">
                                            <div class="col-md-3 mb-2">
                                                <label class="small font-weight-bold text-muted">คำค้นหา</label>
                                                <input type="text" class="form-control form-control-sm" id="search_text" placeholder="ชื่อ, เบอร์, ทะเบียนรถ, บ้านเลขที่, เลขบัตร">
                                            </div>
                                            <div class="col-md-2 mb-2">
                                                <label class="small font-weight-bold text-muted">สถานะ</label>
                                                <select class="form-control form-control-sm" id="status">
                                                    <option value="all">ทั้งหมด</option>
                                                    <option value="inside">อยู่ในหมู่บ้าน (Active)</option>
                                                    <option value="left">ออกจากหมู่บ้านแล้ว</option>
                                                </select>
                                            </div>
                                            <div class="col-md-2 mb-2">
                                                <label class="small font-weight-bold text-muted">ประเภทผู้ติดต่อ</label>
                                                <select class="form-control form-control-sm" id="visitor_type">
                                                    <option value="">ทั้งหมด</option>
                                                    <option value="ส่งอาหาร">ส่งอาหาร</option>
                                                    <option value="ส่งพัสดุ/ส่งของ">ส่งพัสดุ/ส่งของ</option>
                                                    <option value="ญาติ/เพื่อน">ญาติ/เพื่อน</option>
                                                    <option value="ติดต่อนิติฯ">ติดต่อนิติฯ</option>
                                                    <option value="ผู้รับเหมา/ช่าง">ผู้รับเหมา/ช่าง</option>
                                                    <option value="อื่นๆ">อื่นๆ</option>
                                                </select>
                                            </div>
                                            <div class="col-md-2 mb-2">
                                                <label class="small font-weight-bold text-muted">ตั้งแต่วันที่</label>
                                                <input type="date" class="form-control form-control-sm" id="date_from">
                                            </div>
                                            <div class="col-md-2 mb-2">
                                                <label class="small font-weight-bold text-muted">ถึงวันที่</label>
                                                <input type="date" class="form-control form-control-sm" id="date_to">
                                            </div>
                                            <div class="col-md-1 mb-2 d-flex align-items-end">
                                                <button type="button" class="btn btn-primary btn-block btn-sm font-weight-bold" id="btnFilterSearch">
                                                    ค้นหา
                                                </button>
                                            </div>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Logs Table Card -->
                    <div class="row">
                        <div class="col-lg-12">
                            <div class="card shadow-sm border-0">
                                <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                                    <h6 class="m-0 font-weight-bold text-dark"><i class="fa fa-list mr-1"></i> รายการผู้มาติดต่อ</h6>
                                    <button type="button" class="btn btn-sm btn-outline-success font-weight-bold" id="btnExportCSV">
                                        <i class="fa fa-file-excel mr-1"></i> ส่งออกข้อมูล (CSV)
                                    </button>
                                </div>
                                <div class="card-body p-0">
                                    <div class="table-responsive">
                                        <table class="table table-bordered table-striped table-hover mb-0" id="logsTable" width="100%">
                                            <thead class="bg-light text-dark small font-weight-bold">
                                                <tr>
                                                    <th style="width: 5%;">ลำดับ</th>
                                                    <th style="width: 8%;">บ้านเลขที่</th>
                                                    <th style="width: 15%;">ผู้มาติดต่อ</th>
                                                    <th style="width: 10%;">ทะเบียนรถ</th>
                                                    <th style="width: 12%;">บัตรแลก</th>
                                                    <th style="width: 15%;">เวลาเข้า (Check In)</th>
                                                    <th style="width: 15%;">เวลาออก (Check Out)</th>
                                                    <th style="width: 12%;">รูปภาพ</th>
                                                    <th style="width: 8%;">การจัดการ</th>
                                                </tr>
                                            </thead>
                                            <tbody id="logsTableBody" class="small text-dark">
                                                <tr>
                                                    <td colspan="9" class="text-center py-4 text-muted">กำลังโหลดข้อมูล...</td>
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

    <!-- Image Lightbox Modal -->
    <div class="modal fade" id="imageModal" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-body text-center p-2">
                    <button type="button" class="close position-absolute" data-dismiss="modal" aria-label="Close" style="right: 15px; top: 10px; z-index: 9999; color: #fff; text-shadow: 0 1px 0 #000; opacity: 0.8;">
                        <span aria-hidden="true">&times;</span>
                    </button>
                    <img src="" id="modalImage" class="img-fluid rounded" alt="ขยายรูปภาพ">
                </div>
            </div>
        </div>
    </div>

    <script src="vendor/jquery/jquery.min.js"></script>
    <script src="vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
    <script src="vendor/jquery-easing/jquery.easing.min.js"></script>
    <script src="js/myadmin.min.js"></script>

    <script>
        let logsData = [];

        $(document).ready(function () {
            // Set default date range to this month
            let today = new Date();
            let year = today.getFullYear();
            let month = String(today.getMonth() + 1).padStart(2, '0');
            let day = String(today.getDate()).padStart(2, '0');
            
            // Set Date from: start of month, Date to: today
            $('#date_from').val(year + '-' + month + '-01');
            $('#date_to').val(year + '-' + month + '-' + day);

            // Fetch initial logs
            loadVisitorLogs();

            // Filter button click
            $('#btnFilterSearch').click(function () {
                loadVisitorLogs();
            });

            // Export to CSV
            $('#btnExportCSV').click(function () {
                exportLogsToCSV();
            });

            // Handle image click to open lightbox
            $(document).on('click', '.log-thumbnail', function () {
                let src = $(this).attr('src');
                $('#modalImage').attr('src', src);
                $('#imageModal').modal('show');
            });
        });

        // Load logs from server
        function loadVisitorLogs() {
            let tbody = $('#logsTableBody');
            tbody.html('<tr><td colspan="9" class="text-center py-4 text-muted"><i class="fa fa-spinner fa-spin mr-1"></i> กำลังดึงข้อมูล...</td></tr>');

            let search = $('#search_text').val().trim();
            let status = $('#status').val();
            let visitor_type = $('#visitor_type').val();
            let date_from = $('#date_from').val();
            let date_to = $('#date_to').val();

            $.ajax({
                type: "POST",
                url: "model/manage_visitor_contact_process.php",
                data: {
                    action: "GET_VISITOR_LOGS",
                    search: search,
                    status: status,
                    date_from: date_from,
                    date_to: date_to,
                    visitor_type: visitor_type
                },
                dataType: "json",
                success: function (data) {
                    logsData = data || [];
                    renderTable(logsData);
                },
                error: function () {
                    tbody.html('<tr><td colspan="9" class="text-center py-4 text-danger"><i class="fa fa-exclamation-triangle mr-1"></i> เกิดข้อผิดพลาดในการเชื่อมต่อเซิร์ฟเวอร์</td></tr>');
                }
            });
        }

        // Render data into table
        function renderTable(data) {
            let tbody = $('#logsTableBody');
            tbody.empty();

            if (data.length === 0) {
                tbody.html('<tr><td colspan="9" class="text-center py-4 text-muted">ไม่พบข้อมูลตามเงื่อนไขที่ค้นหา</td></tr>');
                return;
            }

            data.forEach(function (v, index) {
                // Card exchange details
                let cardExchange = '';
                if (v.card_exchange === 'Y') {
                    cardExchange = `<div><span class="badge badge-success">แลกบัตรแล้ว</span></div>
                                    <div class="mt-1 font-weight-bold">เลข: ${v.card_no || '-'}</div>`;
                } else {
                    cardExchange = '<span class="badge badge-secondary">ไม่ได้แลกบัตร</span>';
                }

                // Check In details
                let checkIn = `<div><strong>${v.check_in_datetime || '-'}</strong></div>
                               <div class="text-muted small mt-1">โดย: ${v.check_in_by || v.create_by || '-'}</div>`;

                // Check Out details
                let checkOut = '';
                if (v.check_in_status === 'Y') {
                    checkOut = '<span class="badge badge-warning font-weight-bold"><i class="fa fa-home mr-1"></i> อยู่ในหมู่บ้าน</span>';
                } else if (v.check_out_datetime) {
                    checkOut = `<div><strong>${v.check_out_datetime}</strong></div>
                                <div class="text-muted small mt-1">โดย: ${v.check_out_by || '-'}</div>`;
                } else {
                    checkOut = '<span class="text-muted">-</span>';
                }

                // Image column rendering (max 5 pictures)
                let pictures = '';
                for (let i = 1; i <= 5; i++) {
                    let picName = v['picture_' + i];
                    if (picName) {
                        pictures += `<img src="uploads/visitor/${picName}" class="img-thumbnail log-thumbnail mr-1 mb-1" style="width: 32px; height: 32px; object-fit: cover; cursor: pointer;">`;
                    }
                }
                if (pictures === '') {
                    pictures = '<span class="text-muted small">ไม่มีรูปภาพ</span>';
                }

                // Action Column
                let actions = '';
                if (v.check_in_status === 'Y') {
                    actions += `<button type="button" class="btn btn-warning btn-xs btn-block font-weight-bold mb-1 btn-checkout" data-id="${v.id}">
                                    <i class="fa fa-sign-out-alt"></i> เช็คเอาท์
                                </button>`;
                }
                actions += `<button type="button" class="btn btn-danger btn-xs btn-block font-weight-bold btn-delete" data-id="${v.id}">
                                <i class="fa fa-trash"></i> ลบ
                            </button>`;

                let tr = $(`
                    <tr>
                        <td class="text-center align-middle">${index + 1}</td>
                        <td class="text-center font-weight-bold align-middle">${v.house_number}</td>
                        <td class="align-middle">
                            <div class="font-weight-bold">${v.visitor_name}</div>
                            <div class="text-muted small">${v.phone_number ? 'โทร: ' + v.phone_number : ''}</div>
                            <div class="text-primary small mt-1">${v.visitor_type || '-'}</div>
                        </td>
                        <td class="text-center align-middle font-weight-bold">${v.license_plate ? '🚗 ' + v.license_plate : '-'}</td>
                        <td class="text-center align-middle">${cardExchange}</td>
                        <td class="align-middle">${checkIn}</td>
                        <td class="align-middle text-center">${checkOut}</td>
                        <td class="align-middle">${pictures}</td>
                        <td class="text-center align-middle">${actions}</td>
                    </tr>
                `);
                tbody.append(tr);
            });

            // Action Events
            $('.btn-checkout').click(function () {
                let id = $(this).data('id');
                if (confirm('ยืนยันเช็คเอาท์ผู้มาติดต่อท่านนี้ใช่หรือไม่?')) {
                    $.ajax({
                        type: "POST",
                        url: "model/manage_visitor_contact_process.php",
                        data: {action: "CHECK_OUT", id: id},
                        dataType: "json",
                        success: function (response) {
                            if (response && response.result === '1') {
                                loadVisitorLogs();
                            } else {
                                alert("ไม่สามารถทำการเช็คเอาท์ได้");
                            }
                        }
                    });
                }
            });

            $('.btn-delete').click(function () {
                let id = $(this).data('id');
                if (confirm('คุณแน่ใจว่าต้องการลบประวัติผู้มาติดต่อรายการนี้? (การลบจะเป็นแบบถาวรและไม่สามารถกู้คืนได้)')) {
                    $.ajax({
                        type: "POST",
                        url: "model/manage_visitor_contact_process.php",
                        data: {action: "DELETE_VISITOR", id: id},
                        dataType: "json",
                        success: function (response) {
                            if (response && response.result === '1') {
                                loadVisitorLogs();
                            } else {
                                alert("ไม่สามารถลบข้อมูลได้");
                            }
                        }
                    });
                }
            });
        }

        // Export Logs to CSV
        function exportLogsToCSV() {
            if (logsData.length === 0) {
                alert("ไม่มีข้อมูลที่จะส่งออก");
                return;
            }

            let csvContent = "\uFEFF"; // UTF-8 BOM for Excel Thai language support
            csvContent += "ลำดับ,บ้านเลขที่,ชื่อผู้ติดต่อ,เบอร์โทร,ประเภท,ทะเบียนรถ,วัตถุประสงค์,แลกบัตร,เลขบัตร,เวลาเข้า,ผู้เช็คอิน,เวลาออก,ผู้เช็คเอาท์\n";

            logsData.forEach(function (v, index) {
                let name = v.visitor_name.replace(/"/g, '""');
                let purpose = (v.purpose || '').replace(/"/g, '""');
                let type = v.visitor_type || '';
                let phone = v.phone_number || '';
                let licPlate = v.license_plate || '';
                let checkInBy = v.check_in_by || v.create_by || '';
                let checkOutBy = v.check_out_by || '';
                let checkOutTime = v.check_in_status === 'Y' ? 'อยู่ในหมู่บ้าน' : (v.check_out_datetime || '');

                let row = [
                    index + 1,
                    `"${v.house_number}"`,
                    `"${name}"`,
                    `"${phone}"`,
                    `"${type}"`,
                    `"${licPlate}"`,
                    `"${purpose}"`,
                    `"${v.card_exchange === 'Y' ? 'แลกแล้ว' : 'ไม่ได้แลก'}"`,
                    `"${v.card_no || ''}"`,
                    `"${v.check_in_datetime || ''}"`,
                    `"${checkInBy}"`,
                    `"${checkOutTime}"`,
                    `"${checkOutBy}"`
                ].join(",");

                csvContent += row + "\n";
            });

            let blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' });
            let url = URL.createObjectURL(blob);
            let link = document.createElement("a");
            link.setAttribute("href", url);
            
            let today = new Date().toISOString().slice(0, 10);
            link.setAttribute("download", "visitor_logs_" + today + ".csv");
            link.style.visibility = 'hidden';
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);
        }
    </script>
</body>
</html>

<?php } ?>
