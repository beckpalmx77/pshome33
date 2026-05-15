<?php
// house_meeting_generate_view.php
include('includes/Header.php');

if (strlen($_SESSION['alogin']) == "") {
    header("Location: index.php");
    exit();
} else {
    include("config/connect_db.php");

    $current_year = date('Y');
    $YearRecords = [];
    for ($y = $current_year + 1; $y >= $current_year - 5; $y--) {
        $YearRecords[] = ["period_year" => $y];
    }
    $default_date = date('d/m/Y');

    $main_menu_name = isset($_GET['m']) ? urldecode($_GET['m']) : 'การประชุมหมู่บ้าน';
    $sub_menu_name = isset($_GET['s']) ? urldecode($_GET['s']) : 'สร้างข้อมูลการประชุม (Generate)';
    $dashboard_page = isset($_SESSION['dashboard_page']) ? $_SESSION['dashboard_page'] : 'dashboard.php';
    ?>

    <!DOCTYPE html>
    <html lang="th">
    <head>
        <meta charset="UTF-8">
        <title><?php echo $sub_menu_name; ?></title>
        <link href="vendor/date-picker-1.9/css/bootstrap-datepicker.css" rel="stylesheet"/>
        <link href="vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet"/>
        <link rel="stylesheet" href="css/spin_datatables.css"/>
        <link rel="stylesheet" href="vendor/datatables/v11/jquery.dataTables.min.css"/>
        <link rel="stylesheet" href="vendor/datatables/v11/buttons.dataTables.min.css"/>
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/alertifyjs@1.13.1/build/css/alertify.min.css"/>
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/alertifyjs@1.13.1/build/css/themes/default.min.css"/>
        <style>
            .alert-fixed { position: fixed; top: 20px; right: 20px; z-index: 1050; width: 350px; display: none; }

            .card-body { padding: 1rem; }

            .dataTables_wrapper { overflow-x: auto; }

            .dataTables_length { margin-top: 10px; margin-right: 20px; float: left; }
            .dt-buttons { margin-top: 10px; }
            .dataTables_wrapper .dataTables_paginate .paginate_button { padding: 0.3em 0.6em; }

            .badge-status {
                display: inline-block; padding: 0.25em 0.6em;
                font-size: 0.78rem; font-weight: 600;
                border-radius: 50px; text-align: center;
                min-width: 80px;
            }
            .badge-status.badge-warning { background-color: #f6c23e; color: #fff; }
            .badge-status.badge-success { background-color: #1cc88a; color: #fff; }
            .badge-status.badge-danger  { background-color: #e74a3b; color: #fff; }
            .badge-status.badge-secondary { background-color: #858796; color: #fff; }
        </style>
    </head>
    <body id="page-top">
    <div id="wrapper">
        <?php include('includes/Side-Bar.php'); ?>

        <div id="content-wrapper" class="d-flex flex-column">
            <div id="content">
                <?php include('includes/Top-Bar.php'); ?>

                <div class="container-fluid" id="container-wrapper">
                    <div class="d-sm-flex align-items-center justify-content-between mb-4">
                        <h1 class="h4 mb-0 text-gray-800"><?php echo urldecode($_GET['s']) ?></h1>
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="<?php echo $dashboard_page ?>">Home</a></li>
                            <li class="breadcrumb-item"><?php echo urldecode($_GET['m']) ?></li>
                            <li class="breadcrumb-item active"><?php echo urldecode($_GET['s']) ?></li>
                        </ol>
                    </div>

                    <div id="alert_area" class="alert-fixed"></div>

                    <div class="row">
                        <div class="col-lg-12">
                            <div class="card mb-4">
                                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                                    <h6 class="m-0 font-weight-bold text-primary">กำหนดข้อมูลการประชุม (Generate Condition)</h6>
                                </div>
                                <div class="card-body">
                                    <form id="form_data" method="post">
                                        <input type="hidden" name="action" value="GENERATE_MEETING">

                                        <div class="row">
                                            <div class="col-sm-3">
                                                <div class="form-group">
                                                    <label for="meeting_year">ปีการประชุม : <span class="text-danger">*</span></label>
                                                    <select name="meeting_year" id="meeting_year" class="form-control" required>
                                                        <?php foreach ($YearRecords as $row) {
                                                            $selected = ($row["period_year"] == $current_year) ? 'selected' : '';
                                                            echo "<option value='{$row["period_year"]}' $selected>{$row["period_year"]}</option>";
                                                        } ?>
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="col-sm-3">
                                                <div class="form-group">
                                                    <label for="meeting_date">วันที่ประชุม : <span class="text-danger">*</span></label>
                                                    <div class="input-group date" id="datepicker_meeting">
                                                        <input type="text" class="form-control" name="meeting_date" id="meeting_date" value="<?php echo $default_date; ?>" required>
                                                        <div class="input-group-append">
                                                            <span class="input-group-text"><i class="fas fa-calendar"></i></span>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-sm-6">
                                                <div class="form-group">
                                                    <label for="meeting_name">ชื่อการประชุม : <span class="text-danger">*</span></label>
                                                    <input type="text" class="form-control" name="meeting_name" id="meeting_name" placeholder="" value ="ประชุมสามัญประจำปีครั้งที่" required>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-sm-3">
                                                <div class="form-group">
                                                    <label for="meeting_time">เวลาประชุม :</label>
                                                    <input type="text" class="form-control" name="meeting_time" id="meeting_time_gen" value="10.00 น. – 12.00 น.">
                                                </div>
                                            </div>
                                            <div class="col-sm-3">
                                                <div class="form-group">
                                                    <label for="meeting_location">สถานที่ประชุม :</label>
                                                    <input type="text" class="form-control" name="meeting_location" id="meeting_location_gen" value="สำนักงานนิติบุคคลหมู่บ้านจัดสรรพฤกษา 33">
                                                </div>
                                            </div>
                                            <div class="col-sm-3">
                                                <div class="form-group">
                                                    <label for="discount_value">ส่วนลด (บาท) :</label>
                                                    <input type="number" step="0.01" class="form-control" name="discount_value" id="discount_value" value="0.00">
                                                </div>
                                            </div>
                                            <div class="col-sm-3">
                                                <div class="form-group">
                                                    <label for="remark">หมายเหตุ :</label>
                                                    <input type="text" class="form-control" name="remark" id="remark" placeholder="">
                                                </div>
                                            </div>
                                        </div>
                                        <hr>
                                        <div class="row">
                                            <div class="col-sm-12 text-right">
                                                <button type="button" class="btn btn-primary" id="btnGenerate">
                                                    <i class="fas fa-magic"></i> สร้างข้อมูลการประชุมอัตโนมัติ (Generate All Houses)
                                                </button>
                                                <button type="button" class="btn btn-info ml-2" id="btnSearch">
                                                    <i class="fas fa-search"></i> ดูข้อมูลที่มีอยู่ (View Data)
                                                </button>
                                            </div>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Meeting Config Header Card -->
                    <div class="row mt-4" id="configHeaderCard" style="display:none;">
                        <div class="col-lg-12">
                            <div class="card mb-4 border-primary">
                                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between bg-primary text-white">
                                    <h6 class="m-0 font-weight-bold">
                                        <i class="fas fa-info-circle"></i> ข้อมูลการประชุม (Meeting Config)
                                    </h6>
                                    <div>
                                        <button type="button" class="btn btn-light btn-sm" id="btnEditConfig">
                                            <i class="fas fa-edit"></i> แก้ไขหนังสือเชิญประชุม
                                        </button>
                                    </div>
                                </div>
                                <div class="card-body">
                                    <input type="hidden" id="config_meeting_year">
                                    <input type="hidden" id="config_meeting_date">
                                    <div class="row">
                                        <div class="col-md-3">
                                            <strong>หัวข้อ:</strong> <span id="disp_topic" class="text-muted">-</span>
                                        </div>
                                        <div class="col-md-3">
                                            <strong>วันประชุม:</strong> <span id="disp_meeting_day" class="text-muted">-</span>
                                        </div>
                                        <div class="col-md-3">
                                            <strong>เวลาประชุม:</strong> <span id="disp_meeting_time" class="text-muted">-</span>
                                        </div>
                                        <div class="col-md-3">
                                            <strong>สถานที่ประชุม:</strong> <span id="disp_meeting_location" class="text-muted">-</span>
                                        </div>
                                    </div>
                                    <hr>
                                    <div class="row">
                                        <div class="col-md-12">
                                            <strong>ระเบียบวาระการประชุม:</strong>
                                        </div>
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
                                        <div class="col-md-12">
                                            <strong>หมายเหตุ:</strong> <span id="disp_remark" class="text-muted">-</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Detail Data Card: House List -->
                    <div class="row mt-4">
                        <div class="col-lg-12">
                            <div class="card mb-4">
                                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                                    <h6 class="m-0 font-weight-bold text-success">
                                        ข้อมูลการประชุมที่สร้างแล้ว (Generated Data List)
                                    </h6>
                                </div>
                                <div class="card-body">
                                    <div class="table-responsive">
                                        <table class="table table-bordered table-striped" id="meetingDataTable" width="100%" cellspacing="0">
                                            <thead>
                                            <tr>
                                                <th>บ้านเลขที่</th>
                                                <th>ปีการประชุม</th>
                                                <th>วันที่ประชุม</th>
                                                <th>ชื่อการประชุม</th>
                                                <th>ส่วนลด</th>
                                                <th>สถานะ</th>
                                            </tr>
                                            </thead>
                                            <tbody></tbody>
                                        </table>
                                    </div>
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

    <a class="scroll-to-top rounded" href="#page-top"><i class="fas fa-angle-up"></i></a>

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

    <script src="vendor/jquery/jquery.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jqueryui/1.12.1/jquery-ui.min.js"></script>
    <script src="vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
    <script src="vendor/jquery-easing/jquery.easing.min.js"></script>
    <script src="js/myadmin.min.js"></script>
    <script src="vendor/bootstrap-datepicker/js/bootstrap-datepicker.min.js"></script>
    <script src="vendor/date-picker-1.9/locales/bootstrap-datepicker.th.min.js"></script>
    <script src="vendor/datatables/v11/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/1.7.1/js/dataTables.buttons.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/1.7.1/js/buttons.html5.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/1.7.1/js/buttons.print.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/pdfmake.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/vfs_fonts.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/alertifyjs@1.13.1/build/alertify.min.js"></script>

    <script>
        function showAlert(message, type) {
            const alertArea = $('#alert_area');
            let alertClass = type === 'success' ? 'alert-success' : (type === 'error' ? 'alert-danger' : 'alert-info');
            alertArea.html(`
                <div class="alert ${alertClass} alert-dismissible fade show shadow" role="alert">
                    <strong>${type.toUpperCase()}:</strong> ${message}
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
            `).fadeIn();
            setTimeout(() => { alertArea.fadeOut(); }, 5000);
        }

        $(document).ready(function () {
            $('#datepicker_meeting').datepicker({
                format: 'dd/mm/yyyy',
                todayBtn: 'linked',
                todayHighlight: true,
                autoclose: true,
                language: 'th'
            });

            const table = $('#meetingDataTable').DataTable({
                "processing": true,
                "serverSide": true,
                "lengthMenu": [[10, 25, 50, 100, -1], [10, 25, 50, 100, "\u0E17\u0E31\u0E49\u0E07\u0E2B\u0E21\u0E14"]],
                "pageLength": 25,
                "dom": 'Blfrtip',
                "buttons": ['copy', 'excel', 'print'],
                "ajax": {
                    "url": "model/get_generated_meeting_data.php",
                    "type": "POST",
                    "data": function (d) {
                        d.meeting_year = $('#meeting_year').val();
                        d.meeting_date = $('#meeting_date').val();
                    }
                },
                "columns": [
                    { "data": "house_number", "className": "text-center" },
                    { "data": "meeting_year", "className": "text-center" },
                    { "data": "meeting_date", "className": "text-center" },
                    { "data": "meeting_name" },
                    {
                        "data": "discount_value",
                        "className": "text-right",
                        "render": function (data) {
                            return parseFloat(data || 0).toLocaleString('th-TH', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
                        }
                    },
                    {
                        "data": "meeting_status",
                        "className": "text-center",
                        "render": function (data) {
                            var map = {
                                'N': '<span class="badge-status badge-warning">\u0E23\u0E2D\u0E14\u0E33\u0E40\u0E19\u0E34\u0E19\u0E01\u0E32\u0E23</span>',
                                'Y': '<span class="badge-status badge-success">\u0E14\u0E33\u0E40\u0E19\u0E34\u0E19\u0E01\u0E32\u0E23\u0E41\u0E25\u0E49\u0E27</span>',
                                'C': '<span class="badge-status badge-danger">\u0E22\u0E01\u0E40\u0E25\u0E34\u0E01</span>'
                            };
                            return map[data] || '<span class="badge-status badge-secondary">' + data + '</span>';
                        }
                    }
                ],
                "order": [[0, 'asc']],
                "language": {
                    "search": "\u0E04\u0E49\u0E19\u0E2B\u0E32:",
                    "lengthMenu": "\u0E41\u0E2A\u0E14\u0E07 _MENU_ \u0E23\u0E32\u0E22\u0E01\u0E32\u0E23",
                    "info": "\u0E41\u0E2A\u0E14\u0E07 _START_ \u0E16\u0E36\u0E07 _END_ \u0E08\u0E32\u0E01 _TOTAL_ \u0E23\u0E32\u0E22\u0E01\u0E32\u0E23",
                    "infoEmpty": "\u0E41\u0E2A\u0E14\u0E07 0 \u0E16\u0E36\u0E07 0 \u0E08\u0E32\u0E01 0 \u0E23\u0E32\u0E22\u0E01\u0E32\u0E23",
                    "infoFiltered": "(\u0E01\u0E23\u0E2D\u0E07\u0E08\u0E32\u0E01\u0E17\u0E31\u0E49\u0E07\u0E2B\u0E21\u0E14 _MAX_ \u0E23\u0E32\u0E22\u0E01\u0E32\u0E23)",
                    "zeroRecords": "\u0E44\u0E21\u0E48\u0E1E\u0E1A\u0E02\u0E49\u0E2D\u0E21\u0E39\u0E25",
                    "emptyTable": "\u0E44\u0E21\u0E48\u0E21\u0E35\u0E02\u0E49\u0E2D\u0E21\u0E39\u0E25\u0E43\u0E19\u0E15\u0E32\u0E23\u0E32\u0E07",
                    "loadingRecords": "\u0E01\u0E33\u0E25\u0E31\u0E07\u0E42\u0E2B\u0E25\u0E14\u0E02\u0E49\u0E2D\u0E21\u0E39\u0E25...",
                    "processing": "\u0E01\u0E33\u0E25\u0E31\u0E07\u0E1B\u0E23\u0E30\u0E21\u0E27\u0E25\u0E1C\u0E25...",
                    "paginate": {
                        "first": "\u0E41\u0E23\u0E01",
                        "last": "\u0E2A\u0E38\u0E14\u0E17\u0E49\u0E32\u0E22",
                        "next": "\u0E16\u0E31\u0E14\u0E44\u0E1B",
                        "previous": "\u0E01\u0E48\u0E2D\u0E19\u0E2B\u0E19\u0E49\u0E32"
                    }
                },
                "retrieve": true
            });

            // โหลด Meeting Config และแสดงใน Header Card
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
                        // ยังไม่มี config ให้แสดงค่าเริ่มต้น
                        $('#disp_topic').text('-');
                        $('#disp_meeting_day').text('-');
                        $('#disp_meeting_time').text('-');
                        $('#disp_meeting_location').text('-');
                        $('#disp_remark').text('-');
                        for (var i = 1; i <= 7; i++) {
                            $('#disp_agenda_' + i).hide();
                        }
                        $('#configHeaderCard').show();
                    }
                });
            }

            // ปุ่ม Search: โหลด Config + Reload ตาราง
            $('#btnSearch').on('click', function(){
                var year = $('#meeting_year').val();
                var date = $('#meeting_date').val();
                loadMeetingConfig(year, date);
                table.ajax.reload();
            });

            // ปุ่ม Edit Config - เปิด Modal แก้ไขวาระ (โหลดข้อมูลเก่าจาก DB)
            $('#btnEditConfig').on('click', function(){
                var year = $('#config_meeting_year').val();
                var date = $('#config_meeting_date').val();
                if (!year || !date) return;

                $('#agenda_year').val(year);
                $('#agenda_date').val(date);

                $.getJSON('model/meeting_config.php', { meeting_year: year, meeting_date: date }, function(res){
                    if (res.status === 'success' && res.data) {
                        var d = res.data;
                        if (d.topic) $('#agenda_topic').val(d.topic);
                        if (d.meeting_day) $('#meeting_day').val(d.meeting_day);
                        if (d.meeting_time) $('#meeting_time').val(d.meeting_time);
                        if (d.meeting_location) $('#meeting_location').val(d.meeting_location);
                        for (var i = 1; i <= 7; i++) {
                            if (d['agenda_' + i]) $('input[name="agenda_' + i + '"]').val(d['agenda_' + i]);
                        }
                        if (d.remark) $('#agenda_remark').val(d.remark);
                    } else {
                        // ยังไม่มีข้อมูล - ใช้ค่า default จาก HTML (value attributes)
                    }
                });

                $('#agendaModal').modal('show');
            });

            // Preview Template
            $('#btnPreviewAgenda').on('click', function(){
                saveMeetingConfig(function(){
                    var action = $('#agendaForm').attr('action');
                    $('#agendaForm').attr('action', action + '?preview=1').submit().attr('action', action);
                    $('#agendaModal').modal('hide');
                });
            });

            // พิมพ์หนังสือเชิญ - บันทึกข้อมูลก่อน
            $('#agendaForm').on('submit', function(e){
                var year = $('#agenda_year').val();
                var date = $('#agenda_date').val();
                if (!year || !date) {
                    e.preventDefault();
                    showAlert('กรุณาระบุปีและวันที่ประชุมก่อนพิมพ์', 'error');
                    return;
                }
                e.preventDefault();
                saveMeetingConfig(function(){
                    $('#agendaForm')[0].submit();
                    $('#agendaModal').modal('hide');
                });
            });

            // บันทึกข้อมูลหนังสือเชิญประชุม
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

            // เมื่อเปลี่ยนปี หรือ วันที่ ให้ซ่อน Header Config และ reload ตาราง
            $('#meeting_year, #meeting_date').on('change', function(){
                $('#configHeaderCard').hide();
                table.ajax.reload();
            });

            // ปุ่ม Generate
            $('#btnGenerate').on('click', function () {
                if ($('#meeting_name').val().trim() === "") {
                    showAlert('กรุณาระบุชื่อการประชุม', 'error');
                    $('#meeting_name').focus();
                    return;
                }
                if ($('#meeting_date').val().trim() === "") {
                    showAlert('กรุณาระบุวันที่ประชุม', 'error');
                    $('#meeting_date').focus();
                    return;
                }

                alertify.confirm(
                    'ยืนยันการสร้างข้อมูล',
                    `ระบบจะทำการสร้างข้อมูลการประชุมสำหรับ <b>บ้านทุกหลัง (Active)</b><br>ปี: <b>${$('#meeting_year').val()}</b> วันที่: <b>${$('#meeting_date').val()}</b><br>ยืนยันหรือไม่?`,
                    function () {
                        const formData = $('#form_data').serialize();
                        const btn = $('#btnGenerate');
                        btn.attr('disabled', true).html('กำลังประมวลผล... <i class="fas fa-spinner fa-spin"></i>');

                        $.ajax({
                            type: 'POST',
                            url: 'model/generate_house_meeting_process.php',
                            data: formData,
                            dataType: 'json',
                            success: function (response) {
                                if(response.status === 'success'){
                                    showAlert(response.message, 'success');
                                    // สำคัญ: รีโหลดตารางเพื่อแสดงข้อมูลที่เพิ่ง Generate
                                    table.ajax.reload();
                                } else {
                                    showAlert(response.message, 'error');
                                }
                            },
                            error: function (jqXHR, textStatus, errorThrown) {
                                showAlert(`Error: ${errorThrown}`, 'error');
                            },
                            complete: function () {
                                btn.attr('disabled', false).html('<i class="fas fa-magic"></i> สร้างข้อมูลการประชุมอัตโนมัติ (Generate All Houses)');
                            }
                        });
                    },
                    function () { }
                ).set('labels', {ok: 'ยืนยัน', cancel: 'ยกเลิก'});
            });
        });
    </script>
    </body>
    </html>
    <?php
}
?>