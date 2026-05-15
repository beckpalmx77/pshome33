<?php
include('includes/Header.php');
if (strlen($_SESSION['alogin']) == "") {
    header("Location: index.php");
} else {
    include("config/connect_db.php");

    $current_year = date('Y');
    $YearRecords = [];
    for ($y = $current_year + 1; $y >= $current_year - 0; $y--) {
        $YearRecords[] = $y;
    }
    $default_date = date('d/m/Y');

    $url_year = isset($_GET['year']) ? $_GET['year'] : '';
    $url_date = isset($_GET['date']) ? $_GET['date'] : '';
    if (!empty($url_date)) $default_date = $url_date;
    ?>

    <!DOCTYPE html>
    <html lang="th">
    <head>
        <link href="vendor/date-picker-1.9/css/bootstrap-datepicker.css" rel="stylesheet"/>
        <link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.4.1/css/buttons.dataTables.min.css"/>
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/alertifyjs@1.13.1/build/css/alertify.min.css"/>
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/alertifyjs@1.13.1/build/css/themes/default.min.css"/>
        <style>
            .icon-input-btn { display: inline-block; position: relative; }
            .icon-input-btn input[type="submit"] { padding-left: 2em; }
            .icon-input-btn .fa { display: inline-block; position: absolute; left: 0.65em; top: 30%; }
            .dt-buttons .dt-button {
                background-color: #4e73df; color: white; border: none;
                border-radius: 4px; padding: 5px 15px; margin-right: 5px;
            }
            .dt-buttons .dt-button:hover { background-color: #2e59d9; }
            .btn-custom-print { background-color: #1cc88a !important; }
        </style>
    </head>
    <body id="page-top">
    <div id="wrapper">
        <?php include('includes/Side-Bar.php'); ?>

        <div id="content-wrapper" class="d-flex flex-column">
            <div id="content">
                <?php include('includes/Top-Bar.php'); ?>
                <div class="container-fluid" id="container-wrapper">
                    <?php
                    $sub_menu_name = isset($_GET['s']) ? urldecode($_GET['s']) : 'จัดการข้อมูลการประชุม';
                    $main_menu_name = isset($_GET['m']) ? urldecode($_GET['m']) : 'การประชุมหมู่บ้าน';
                    $dash_page = isset($_SESSION['dashboard_page']) ? $_SESSION['dashboard_page'] : 'dashboard.php';
                    ?>
                    <div class="d-sm-flex align-items-center justify-content-between mb-4">
                        <h1 class="h3 mb-0 text-gray-800"><?php echo $sub_menu_name; ?></h1>
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="<?php echo $dash_page; ?>">Home</a></li>
                            <li class="breadcrumb-item"><?php echo $main_menu_name; ?></li>
                            <li class="breadcrumb-item active" aria-current="page"><?php echo $sub_menu_name; ?></li>
                        </ol>
                    </div>

                    <div class="row">
                        <div class="col-lg-12">
                            <div class="card mb-12">
                                <div class="card-body">
                                    <section class="container-fluid">

                                        <div class="row mb-3 align-items-end">
                                            <div class="col-md-2">
                                                <div class="form-group mb-0">
                                                    <label for="filter_year" class="font-weight-bold small mb-1">ปีการประชุม:</label>
                                                    <select class="form-control form-control-sm" id="filter_year">
                                                        <option value="">-- แสดงทั้งหมด --</option>
                                                        <?php foreach ($YearRecords as $year) {
                                                            $selected = ($year == $current_year) ? 'selected' : '';
                                                            ?>
                                                            <option value="<?php echo $year; ?>" <?php echo $selected; ?>>
                                                                <?php echo $year; ?>
                                                            </option>
                                                        <?php } ?>
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="col-md-3">
                                                <div class="form-group mb-0">
                                                    <label for="filter_date" class="font-weight-bold small mb-1">วันที่ประชุม:</label>
                                                    <div class="input-group input-group-sm date" id="datepicker_filter">
                                                        <input type="text" class="form-control" id="filter_date" value="<?php echo $default_date; ?>">
                                                        <div class="input-group-append">
                                                            <span class="input-group-text"><i class="fas fa-calendar"></i></span>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-md-7">
                                                <div class="d-flex align-items-center justify-content-end flex-wrap" style="gap:6px;">
                                                    <a href="manage_meeting_register_summary.php?m=<?php echo urlencode('บันทึกข้อมูลหลัก'); ?>&s=<?php echo urlencode('รายละเอียดการประชุมหมู่บ้าน'); ?>" class="btn btn-outline-secondary btn-sm">
                                                        <i class="fas fa-arrow-left"></i> หัวข้อ-รายละเอียด การประชุม
                                                    </a>
                                                    <!--a href="manage_house_meeting_checkin.php?m=<?php echo urlencode('การประชุมหมู่บ้าน'); ?>&s=<?php echo urlencode('ตรวจสอบการลงทะเบียน'); ?>" class="btn btn-outline-info btn-sm">
                                                        <i class="fas fa-check-circle"></i> ตรวจสอบการลงทะเบียน
                                                    </a-->
                                                    <div id="buttons_container" style="white-space:nowrap;"></div>
                                                    <button type="button" id="btnSearch" class="btn btn-info btn-sm" data-toggle="tooltip" title="ค้นหาข้อมูล">
                                                        <i class="fas fa-search"></i> ค้นหา
                                                    </button>
                                                    <button type="button" id="btnReload" class="btn btn-outline-success btn-sm" data-toggle="tooltip" title="Reload Data">
                                                        <i class="fa fa-refresh"></i> รีโหลด
                                                    </button>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- hidden year/date for auto-load -->
                                        <input type="hidden" id="url_year" value="<?php echo $url_year; ?>">
                                        <input type="hidden" id="url_date" value="<?php echo $url_date; ?>">

                                        <!-- Meeting Config Header Card -->
                                        <div class="row" id="configHeaderCard" style="display:none;">
                                            <div class="col-lg-12">
                                                <div class="card mb-3 border-primary">
                                                    <div class="card-header py-2 d-flex flex-row align-items-center justify-content-between bg-primary text-white">
                                                        <h6 class="m-0 font-weight-bold">
                                                            <i class="fas fa-info-circle"></i> ข้อมูลการประชุม
                                                        </h6>
                                                        <div>
                                                            <button type="button" class="btn btn-light btn-sm" id="btnEditConfig">
                                                                <i class="fas fa-edit"></i> แก้ไขหนังสือเชิญประชุม
                                                            </button>
                                                        </div>
                                                    </div>
                                                    <div class="card-body py-2">
                                                        <input type="hidden" id="config_meeting_year">
                                                        <input type="hidden" id="config_meeting_date">
                                                        <div class="row">
                                                            <div class="col-md-3"><strong>หัวข้อ:</strong> <span id="disp_topic" class="text-muted">-</span></div>
                                                            <div class="col-md-3"><strong>วันประชุม:</strong> <span id="disp_meeting_day" class="text-muted">-</span></div>
                                                            <div class="col-md-3"><strong>เวลาประชุม:</strong> <span id="disp_meeting_time" class="text-muted">-</span></div>
                                                            <div class="col-md-3"><strong>สถานที่ประชุม:</strong> <span id="disp_meeting_location" class="text-muted">-</span></div>
                                                        </div>
                                                        <hr>
                                                        <div class="row">
                                                            <div class="col-md-12"><strong>ระเบียบวาระการประชุม:</strong></div>
                                                        </div>
                                                        <div class="row mt-1" id="agendaDisplay">
                                                            <div class="col-md-12">
                                                                <span id="disp_agenda_1" class="badge badge-info mr-1 mb-1" style="display:none;"></span>
                                                                <span id="disp_agenda_2" class="badge badge-info mr-1 mb-1" style="display:none;"></span>
                                                                <span id="disp_agenda_3" class="badge badge-info mr-1 mb-1" style="display:none;"></span>
                                                                <span id="disp_agenda_4" class="badge badge-info mr-1 mb-1" style="display:none;"></span>
                                                                <span id="disp_agenda_5" class="badge badge-info mr-1 mb-1" style="display:none;"></span>
                                                                <span id="disp_agenda_6" class="badge badge-info mr-1 mb-1" style="display:none;"></span>
                                                                <span id="disp_agenda_7" class="badge badge-info mr-1 mb-1" style="display:none;"></span>
                                                            </div>
                                                        </div>
                                                        <hr>
                                                        <div class="row">
                                                            <div class="col-md-12"><strong>วันที่ประชุม:</strong> <span id="disp_meeting_date" class="text-muted">-</span></div>
                                                        </div>
                                                        <div class="row">
                                                            <div class="col-md-12"><strong>หมายเหตุ:</strong> <span id="disp_remark" class="text-muted">-</span></div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="col-md-12">
                                            <table id='TableRecordList' class='display dataTable table table-bordered table-striped' style="width:100%">
                                                <thead>
                                                <tr>
                                                    <th>บ้านเลขที่</th>
                                                    <th>ซอย</th>
                                                    <th>ปี / วันที่</th>
                                                    <th>ชื่อการประชุม</th>
                                                    <th>ผู้เข้าร่วมประชุม</th>
                                                    <th>สถานะการเข้า</th>
                                                    <th>จัดการ</th>
                                                </tr>
                                                </thead>
                                            </table>
                                            <div id="result"></div>
                                        </div>

                                        <div class="modal fade" id="recordModal">
                                            <div class="modal-dialog modal-lg">
                                                <div class="modal-content">
                                                    <div class="modal-header">
                                                        <h4 class="modal-title">บันทึกผลการเข้าร่วมประชุม</h4>
                                                        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                                                    </div>
                                                    <form method="post" id="recordForm">
                                                        <div class="modal-body">
                                                            <div class="row">
                                                                <div class="col-md-6">
                                                                    <div class="form-group">
                                                                        <label for="house_number" class="control-label">บ้านเลขที่</label>
                                                                        <input type="text" class="form-control" id="house_number" name="house_number" readonly>
                                                                    </div>
                                                                </div>
                                                                <div class="col-md-6">
                                                                    <div class="form-group">
                                                                        <label for="meeting_date_show" class="control-label">วันที่ประชุม</label>
                                                                        <input type="text" class="form-control" id="meeting_date_show" readonly>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            <div class="form-group">
                                                                <label for="meeting_name" class="control-label">ชื่อการประชุม</label>
                                                                <input type="text" class="form-control" id="meeting_name" name="meeting_name" readonly>
                                                            </div>
                                                            <hr>
                                                            <div class="form-group">
                                                                <label for="attendance_name" class="control-label font-weight-bold text-primary">
                                                                    ระบุชื่อผู้เข้าร่วมประชุม (Attendance Name)
                                                                </label>
                                                                <input type="text" class="form-control" id="attendance_name" name="attendance_name" placeholder="เช่น นาย ก. (เจ้าบ้าน)">
                                                            </div>
                                                            <div class="form-group">
                                                                <label class="control-label font-weight-bold text-primary">สถานะการเข้าร่วม (Status)</label>
                                                                <div class="custom-control custom-checkbox">
                                                                    <input type="checkbox" class="custom-control-input" id="chk_meeting_status">
                                                                    <label class="custom-control-label" for="chk_meeting_status" style="cursor: pointer;">
                                                                        ยืนยันการเข้าร่วมประชุม (Attended)
                                                                    </label>
                                                                </div>
                                                                <input type="hidden" name="meeting_status" id="meeting_status" value="N">
                                                            </div>
                                                        </div>
                                                        <div class="modal-footer">
                                                            <input type="hidden" name="id" id="id"/>
                                                            <input type="hidden" name="action" id="action" value=""/>
                                                            <button type="button" class="btn btn-secondary" data-dismiss="modal">ปิด</button>
                                                            <span class="icon-input-btn">
                                                                <i class="fa fa-save"></i>
                                                                <input type="submit" name="save" id="save" class="btn btn-primary" value="บันทึกข้อมูล"/>
                                                            </span>
                                                        </div>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>

                                    </section>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Modal แก้ไขหนังสือเชิญประชุม -->
                    <div class="modal fade" id="agendaModal" tabindex="-1" role="dialog">
                        <div class="modal-dialog modal-lg" role="document">
                            <div class="modal-content">
                                <form id="agendaForm" method="POST" action="print_meeting_invitation_pdf.php" target="_blank">
                                    <div class="modal-header bg-success text-white">
                                        <h5 class="modal-title"><i class="fas fa-edit"></i> แก้ไขหนังสือเชิญประชุม</h5>
                                        <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
                                    </div>
                                    <div class="modal-body">
                                        <input type="hidden" name="meeting_year" id="agenda_year">
                                        <input type="hidden" name="meeting_date" id="agenda_date">

                                        <h6 class="text-primary font-weight-bold">หัวข้อและวัน เวลา สถานที่ประชุม</h6>
                                        <div class="row">
                                            <div class="col-md-4">
                                                <div class="form-group">
                                                    <label>ชื่อการประชุม (Topic)</label>
                                                    <input type="text" class="form-control" name="topic" id="agenda_topic" value="ประชุมวิสามัญประจำปี">
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <div class="form-group">
                                                    <label>วันประชุม</label>
                                                    <input type="text" class="form-control" name="meeting_day" id="meeting_day" value="วันอาทิตย์ที่ 22 มิถุนายน">
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <div class="form-group">
                                                    <label>เวลาประชุม</label>
                                                    <input type="text" class="form-control" name="meeting_time" id="meeting_time" value="10.00 น. – 12.00 น.">
                                                </div>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-md-4">
                                                <div class="form-group">
                                                    <label>สถานที่ประชุม</label>
                                                    <input type="text" class="form-control" name="meeting_location" id="meeting_location" value="สำนักงานนิติบุคคลหมู่บ้านจัดสรรพฤกษา 33">
                                                </div>
                                            </div>
                                        </div>

                                        <hr>
                                        <h6 class="text-primary font-weight-bold">ระเบียบวาระการประชุม</h6>
                                        <div class="form-group">
                                            <label>วาระที่ 1</label>
                                            <input type="text" class="form-control" name="agenda_1" value="เรื่องแจ้งเพื่อทราบ">
                                        </div>
                                        <div class="form-group">
                                            <label>วาระที่ 2</label>
                                            <input type="text" class="form-control" name="agenda_2" value="เรื่องชี้แจงการดำเนินการของคณะกรรมการ">
                                        </div>
                                        <div class="form-group">
                                            <label>วาระที่ 3</label>
                                            <input type="text" class="form-control" name="agenda_3" value="เรื่องพิจารณา">
                                        </div>
                                        <div class="form-group">
                                            <label>วาระที่ 4</label>
                                            <input type="text" class="form-control" name="agenda_4" value="เรื่องอื่น ๆ (ถ้ามี)">
                                        </div>
                                        <div class="form-group">
                                            <label>วาระที่ 5</label>
                                            <input type="text" class="form-control" name="agenda_5" value="">
                                        </div>
                                        <div class="form-group">
                                            <label>วาระที่ 6</label>
                                            <input type="text" class="form-control" name="agenda_6" value="">
                                        </div>
                                        <div class="form-group">
                                            <label>วาระที่ 7</label>
                                            <input type="text" class="form-control" name="agenda_7" value="">
                                        </div>

                                        <hr>
                                        <div class="form-group">
                                            <label>หมายเหตุ (เพิ่มเติม)</label>
                                            <textarea class="form-control" name="remark" id="agenda_remark" rows="3">หมายเหตุ: โปรดนำบัตรประชาชนมาด้วยทุกครั้งเพื่อแสดงตนลงเพื่อความเรียบร้อยในการลงทะเบียนเข้าร่วมประชุม
ถ้าไม่ได้เข้าร่วมประชุมด้วยตนเอง กรุณาลงรายละเอียด หนังสือมอบฉันทะ ที่แนบมากับหนังสือเชิญประชุมนี้</textarea>
                                        </div>
                                    </div>
                                    <div class="modal-footer">
                                        <button type="submit" class="btn btn-success"><i class="fas fa-file-pdf"></i> พิมพ์หนังสือเชิญ</button>
                                        <button type="button" class="btn btn-info" id="btnPreviewAgenda"><i class="fas fa-eye"></i> ดูตัวอย่าง</button>
                                        <button type="button" class="btn btn-secondary" data-dismiss="modal">ยกเลิก</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
            <?php include('includes/Footer.php'); ?>
        </div>
    </div>

    <?php include('includes/Modal-Logout.php'); ?>

    <a class="scroll-to-top rounded" href="#page-top">
        <i class="fas fa-angle-up"></i>
    </a>

    <script src="vendor/jquery/jquery.min.js"></script>
    <script src="vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
    <script src="vendor/jquery-easing/jquery.easing.min.js"></script>
    <script src="js/myadmin.min.js"></script>

    <script src="vendor/datatables/v11/jquery.dataTables.min.js"></script>
    <script src="vendor/datatables/v11/bootbox.min.js"></script>
    <link rel="stylesheet" href="vendor/datatables/v11/jquery.dataTables.min.css"/>

    <script src="https://cdn.datatables.net/buttons/2.4.1/js/dataTables.buttons.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.html5.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.print.min.js"></script>

    <script src="https://cdn.jsdelivr.net/npm/alertifyjs@1.13.1/build/alertify.min.js"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/alertifyjs@1.13.1/build/css/alertify.min.css"/>

    <script src="vendor/bootstrap-datepicker/js/bootstrap-datepicker.min.js"></script>
    <script src="vendor/date-picker-1.9/locales/bootstrap-datepicker.th.min.js"></script>

    <script>
        $(document).ready(function () {
            $('#datepicker_filter').datepicker({
                format: 'dd/mm/yyyy',
                todayBtn: 'linked',
                todayHighlight: true,
                autoclose: true,
                language: 'th'
            });

            // Auto-load จาก URL params
            var urlYear = $('#url_year').val();
            var urlDate = $('#url_date').val();
            if (urlYear && urlDate) {
                $('#filter_year').val(urlYear);
                $('#filter_date').val(urlDate);
                // รอให้ DataTable พร้อมแล้วค่อย search
                setTimeout(function(){
                    $('#btnSearch').trigger('click');
                }, 500);
            }

            $('#chk_meeting_status').change(function() {
                if($(this).is(":checked")) {
                    $('#meeting_status').val('Y');
                } else {
                    $('#meeting_status').val('N');
                }
            });

            function loadMeetingConfig(year, date) {
                if (!year || !date) {
                    $('#configHeaderCard').hide();
                    return;
                }
                $.getJSON('model/meeting_config.php', { meeting_year: year, meeting_date: date }, function(res){
                    $('#config_meeting_year').val(year);
                    $('#config_meeting_date').val(date);
                    if (res.status === 'success' && res.data) {
                        var d = res.data;
                        $('#disp_topic').text(d.topic || '-');
                        $('#disp_meeting_day').text(d.meeting_day || '-');
                        $('#disp_meeting_time').text(d.meeting_time || '-');
                        $('#disp_meeting_location').text(d.meeting_location || '-');
                        $('#disp_meeting_date').text(d.meeting_date || '-');
                        $('#disp_remark').text(d.remark || '-');
                        for (var i = 1; i <= 7; i++) {
                            var val = d['agenda_' + i] || '';
                            var $el = $('#disp_agenda_' + i);
                            if (val.trim() !== '') {
                                $el.text('วาระที่ ' + i + ': ' + val).show();
                            } else {
                                $el.hide();
                            }
                        }
                        $('#configHeaderCard').show();
                    } else {
                        $('#disp_topic').text('-');
                        $('#disp_meeting_day').text('-');
                        $('#disp_meeting_time').text('-');
                        $('#disp_meeting_location').text('-');
                        $('#disp_meeting_date').text(date);
                        $('#disp_remark').text('-');
                        for (var i = 1; i <= 7; i++) {
                            $('#disp_agenda_' + i).hide();
                        }
                        $('#configHeaderCard').show();
                    }
                });
            }

            let dataRecords = $('#TableRecordList').DataTable({
                'dom': 'Blfrtip',
                'lengthMenu': [[10, 20, 50, 100, -1], [10, 20, 50, 100, "แสดงทั้งหมด"]],
                'buttons': [
                    {
                        text: '<i class="fa fa-file-excel"></i> Export Excel (All Data)',
                        className: 'btn btn-success btn-sm',
                        action: function ( e, dt, node, config ) {
                            let year = $('#filter_year').val();
                            let date = $('#filter_date').val();
                            window.location.href = 'export_process/export_meeting_excel_process.php?meeting_year=' + year + '&meeting_date=' + encodeURIComponent(date);
                        }
                    },
                    {
                        text: '<i class="fa fa-print"></i> พิมพ์ใบเซ็นชื่อ (แยกซอย)',
                        className: 'btn btn-custom-print btn-sm',
                        action: function ( e, dt, node, config ) {
                            let year = $('#filter_year').val();
                            if(year === "") {
                                alert("กรุณาเลือกปีก่อนพิมพ์ใบเซ็นชื่อ");
                                return;
                            }
                            window.open('print_meeting_form.php?meeting_year=' + year, '_blank');
                        }
                    }
                ],
                'language': {
                    search: 'ค้นหา:', lengthMenu: 'แสดง _MENU_ รายการ',
                    info: 'หน้าที่ _PAGE_ จาก _PAGES_',
                    infoEmpty: 'ไม่มีข้อมูล',
                    zeroRecords: "ไม่พบข้อมูล",
                    paginate: { previous: 'ก่อนหน้า', next: 'ถัดไป' }
                },
                'processing': true,
                'serverSide': true,
                'serverMethod': 'post',
                'ajax': {
                    'url': 'model/manage_house_meeting_record_process.php',
                    'data': function(d) {
                        d.action = "GET_MEETING_LIST";
                        d.meeting_year = $('#filter_year').val();
                        d.meeting_date = $('#filter_date').val();
                    }
                },
                'columns': [
                    {data: 'house_number'},
                    {data: 'alley', defaultContent: '-'},
                    {
                        data: 'meeting_date',
                        render: function (data, type, row) {
                            return row.meeting_year + ' : ' + data;
                        }
                    },
                    {data: 'meeting_name'},
                    {data: 'attendance_name', defaultContent: '-'},
                    {
                        data: 'meeting_status',
                        render: function (data, type, row) {
                            return (data === 'Y') ? '<span class="badge badge-success"><i class="fa fa-check"></i> เข้าร่วม</span>' : '<span class="badge badge-secondary"><i class="fa fa-times"></i> ไม่เข้าร่วม</span>';
                        },
                        className: "text-center"
                    },
                    {
                        data: 'id',
                        render: function (data, type, row) {
                            return '<button type="button" name="update" id="' + data + '" class="btn btn-warning btn-sm update" title="บันทึกผล"><i class="fa fa-edit"></i> Check-in</button>';
                        },
                        className: "text-center"
                    }
                ]
            });

            $('#btnReload').on('click', function () {
                $('#TableRecordList').DataTable().ajax.reload();
            });

            $('#btnSearch').on('click', function(){
                var year = $('#filter_year').val();
                var date = $('#filter_date').val();
                loadMeetingConfig(year, date);
                dataRecords.ajax.reload();
            });

            $('#filter_year, #filter_date').on('change', function(){
                $('#configHeaderCard').hide();
            });

            $('#btnEditConfig').on('click', function(){
                var year = $('#config_meeting_year').val();
                var date = $('#config_meeting_date').val();
                if (!year || !date) return;
                $('#agenda_year').val(year);
                $('#agenda_date').val(date);
                $.getJSON('model/meeting_config.php', { meeting_year: year, meeting_date: date }, function(res){
                    if (res.status === 'success' && res.data) {
                        var d = res.data;
                        $('#agenda_topic').val(d.topic || '');
                        $('#meeting_day').val(d.meeting_day || '');
                        $('#meeting_time').val(d.meeting_time || '');
                        $('#meeting_location').val(d.meeting_location || '');
                        $('#agenda_remark').val(d.remark || '');
                        for (var i = 1; i <= 7; i++) {
                            $('input[name="agenda_' + i + '"]').val(d['agenda_' + i] || '');
                        }
                    }
                    $('#agendaModal').modal('show');
                });
            });

            $('#btnPreviewAgenda').on('click', function(){
                saveMeetingConfig(function(){
                    var action = $('#agendaForm').attr('action');
                    $('#agendaForm').attr('action', action + '?preview=1').submit().attr('action', action);
                    $('#agendaModal').modal('hide');
                });
            });

            $('#agendaForm').on('submit', function(e){
                var year = $('#agenda_year').val();
                var date = $('#agenda_date').val();
                if (!year || !date) {
                    e.preventDefault();
                    alertify.error('กรุณาระบุปีและวันที่ประชุมก่อนพิมพ์');
                    return;
                }
                e.preventDefault();
                saveMeetingConfig(function(){
                    $('#agendaForm')[0].submit();
                    $('#agendaModal').modal('hide');
                });
            });

            function saveMeetingConfig(callback) {
                var year = $('#agenda_year').val();
                var date = $('#agenda_date').val();
                var data = {
                    meeting_year: year,
                    meeting_date: date,
                    meeting_day: $('#meeting_day').val(),
                    meeting_time: $('#meeting_time').val(),
                    meeting_location: $('#meeting_location').val(),
                    topic: $('#agenda_topic').val(),
                    remark: $('#agenda_remark').val()
                };
                for (var i = 1; i <= 7; i++) {
                    data['agenda_' + i] = $('input[name="agenda_' + i + '"]').val();
                }
                $.ajax({
                    type: 'POST',
                    url: 'model/meeting_config.php',
                    data: data,
                    dataType: 'json',
                    complete: function() {
                        loadMeetingConfig(year, date);
                        if (callback) callback();
                    }
                });
            }

            dataRecords.buttons().container().appendTo('#buttons_container');

            $("#recordModal").on('submit', '#recordForm', function (event) {
                event.preventDefault();
                $('#save').attr('disabled', 'disabled');
                let formData = $(this).serialize();
                $.ajax({
                    url: 'model/manage_house_meeting_record_process.php',
                    method: "POST",
                    data: formData,
                    dataType: "json",
                    success: function (data) {
                        if (data.status === 'success') {
                            alertify.success(data.message);
                            $('#recordForm')[0].reset();
                            $('#recordModal').modal('hide');
                            dataRecords.ajax.reload();
                        } else {
                            alertify.error(data.message);
                        }
                        $('#save').attr('disabled', false);
                    },
                    error: function() { alertify.error("เชื่อมต่อผิดพลาด"); $('#save').attr('disabled', false); }
                })
            });

            $("#TableRecordList").on('click', '.update', function () {
                let id = $(this).attr("id");
                $.ajax({
                    type: "POST",
                    url: 'model/manage_house_meeting_record_process.php',
                    dataType: "json",
                    data: {action: "GET_DATA", id: id},
                    success: function (response) {
                        let data = response[0] || response;
                        $('#recordModal').modal('show');
                        $('#id').val(data.id);
                        $('#house_number').val(data.house_number);
                        $('#meeting_date_show').val(data.meeting_date + ' (' + data.meeting_year + ')');
                        $('#meeting_name').val(data.meeting_name);
                        $('#attendance_name').val(data.attendance_name);
                        if (data.meeting_status === 'Y') {
                            $('#chk_meeting_status').prop('checked', true);
                            $('#meeting_status').val('Y');
                        } else {
                            $('#chk_meeting_status').prop('checked', false);
                            $('#meeting_status').val('N');
                        }
                        $('#action').val('UPDATE_ATTENDANCE');
                    },
                    error: function () { alertify.error("ดึงข้อมูลไม่ได้"); }
                });
            });
        });
    </script>
    </body>
    </html>
<?php } ?>