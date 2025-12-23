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
        <link href="vendor/datatables/dataTables.bootstrap4.min.css" rel="stylesheet">
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/alertifyjs@1.13.1/build/css/alertify.min.css"/>
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/alertifyjs@1.13.1/build/css/themes/default.min.css"/>
        <style>
            .alert-fixed { position: fixed; top: 20px; right: 20px; z-index: 1050; width: 350px; display: none; }
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
                                                    <label for="discount_value">ส่วนลด (บาท) :</label>
                                                    <input type="number" step="0.01" class="form-control" name="discount_value" id="discount_value" value="0.00">
                                                </div>
                                            </div>
                                            <div class="col-sm-9">
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

    <script src="vendor/jquery/jquery.min.js"></script>
    <script src="vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
    <script src="vendor/jquery-easing/jquery.easing.min.js"></script>
    <script src="js/myadmin.min.js"></script>
    <script src="vendor/bootstrap-datepicker/js/bootstrap-datepicker.min.js"></script>
    <script src="vendor/date-picker-1.9/locales/bootstrap-datepicker.th.min.js"></script>
    <script src="vendor/datatables/jquery.dataTables.min.js"></script>
    <script src="vendor/datatables/dataTables.bootstrap4.min.js"></script>
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

            // ตั้งค่า DataTable ให้ดึงข้อมูลจาก ims_house_meeting โดยส่ง param ปี/วันที่ ไปด้วย
            const table = $('#meetingDataTable').DataTable({
                "processing": true,
                "serverSide": true,
                "ajax": {
                    "url": "model/get_generated_meeting_data.php",
                    "type": "POST",
                    "data": function (d) {
                        // ส่งค่า Filter ไปยัง Server
                        d.meeting_year = $('#meeting_year').val();
                        d.meeting_date = $('#meeting_date').val();
                    }
                },
                "columns": [
                    {"data": "house_number"},
                    {"data": "meeting_year"},
                    {"data": "meeting_date"},
                    {"data": "meeting_name"},
                    {"data": "discount_value", "className": "text-right"},
                    {"data": "meeting_status", "className": "text-center"}
                ],
                "order": [[0, 'asc']], // เรียงตามบ้านเลขที่
                "language": { "url": "vendor/datatables/thai.json" }
            });

            // ปุ่ม Search: แค่ Reload ตาราง (Logic การดึงข้อมูลอยู่ใน Ajax data function แล้ว)
            $('#btnSearch').on('click', function(){
                table.ajax.reload();
            });

            // เมื่อเปลี่ยนปี หรือ วันที่ ให้ reload ตารางอัตโนมัติเพื่อให้เห็นข้อมูลปัจจุบัน
            $('#meeting_year, #meeting_date').on('change', function(){
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