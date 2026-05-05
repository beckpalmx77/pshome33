<?php
session_start();
error_reporting(0);
include('includes/Header.php');
include('config/connect_db.php');
$curr_date = date("d-m-Y");

if (strlen($_SESSION['alogin']) == "" || strlen($_SESSION['position_desc']) == "") {
    header("Location: index.php");
} else {
    ?>

    <!DOCTYPE html>
    <html lang="th">
    <body id="page-top">
    <div id="wrapper">
        <?php
        include('includes/Side-Bar.php');
        ?>

        <div id="content-wrapper" class="d-flex flex-column">
            <div id="content">
                <?php
                include('includes/Top-Bar.php');
                ?>
                <div class="container-fluid" id="container-wrapper">
                    <div class="d-sm-flex align-items-center justify-content-between mb-4">
                        <h1 class="h3 mb-0 text-gray-800"><?php echo urldecode($_GET['s']) ?></h1>
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="<?php echo $_SESSION['dashboard_page'] ?>">Home</a>
                            </li>
                            <li class="breadcrumb-item"><?php echo urldecode($_GET['m']) ?></li>
                            <li class="breadcrumb-item active"
                                aria-current="page"><?php echo urldecode($_GET['s']) ?></li>
                        </ol>
                    </div>

                    <div class="row">
                        <div class="col-lg-12">
                            <div class="card mb-12">
                                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                                </div>
                                <div class="card-body">
                                    <section class="container-fluid">

                                        <div class="col-md-12 col-md-offset-2">
                                            <label for="name_t"
                                                   class="control-label"><b>เพิ่ม <?php echo urldecode($_GET['s']) ?></b></label>
                                            <button type='button' name='btnAdd' id='btnAdd'
                                                    class='btn btn-primary btn-xs'>Add
                                                <i class="fa fa-plus"></i>
                                            </button>
                                            <button type="button" id="btnReload" class="btn btn-outline-success btn-xs" data-toggle="tooltip" title="Reload Data">
                                                <i class="fa fa-refresh"></i> Reload
                                            </button>
                                            <button type="button" id="btnExport" class="btn btn-outline-primary btn-xs" data-toggle="tooltip" title="Export Excel">
                                                <i class="fa fa-file-excel-o"></i> Export Excel
                                            </button>
                                            <select id="export_status" class="form-control form-control-sm d-inline-block" style="width: auto; display: inline-block;">
                                                <option value="ALL">ทั้งหมด</option>
                                                <option value="Y">ทำงานปกติ</option>
                                                <option value="N">ลาออก</option>
                                            </select>
                                        </div>

                                        <div class="col-md-12 col-md-offset-2">
                                            <table id='TableRecordList' class='display dataTable'>
                                                <thead>
                                                <tr>
                                                    <th>รหัสพนักงาน</th>
                                                    <th>ชื่อ-นามสกุล</th>
                                                    <th>ชื่อเล่น</th>
                                                    <th>ตำแหน่ง</th>
                                                    <th>วันเริ่มงาน</th>
                                                    <th>เวลาทำงาน</th>
                                                    <th>สถานะ</th>
                                                    <th>Action</th>
                                                </tr>
                                                </thead>
                                                <tfoot>
                                                <tr>
                                                    <th>รหัสพนักงาน</th>
                                                    <th>ชื่อ-นามสกุล</th>
                                                    <th>ชื่อเล่น</th>
                                                    <th>ตำแหน่ง</th>
                                                    <th>วันเริ่มงาน</th>
                                                    <th>เวลาทำงาน</th>
                                                    <th>สถานะ</th>
                                                    <th>Action</th>
                                                </tr>
                                                </tfoot>
                                            </table>

                                            <div id="result"></div>

                                        </div>

                                        <div class="modal fade" id="recordModal">
                                            <div class="modal-dialog modal-xl">
                                                <div class="modal-content">
                                                    <div class="modal-header">
                                                        <h4 class="modal-title">รายละเอียดพนักงาน</h4>
                                                        <button type="button" class="close" data-dismiss="modal"
                                                                aria-hidden="true">×
                                                        </button>
                                                    </div>
                                                    <form method="post" id="recordForm" enctype="multipart/form-data">
                                                        <div class="modal-body">
                                                            <div class="form-group row">
                                                                <div class="col-sm-3">
                                                                    <label for="emp_id" class="control-label">รหัสพนักงาน</label>
                                                                    <input type="text" class="form-control"
                                                                           id="emp_id" name="emp_id" readonly="true"
                                                                           placeholder="รหัสพนักงาน">
                                                                </div>

                                                                <div class="col-sm-3">
                                                                    <label>รูปภาพ (JPG, PNG, PDF)</label>
                                                                    <input type="file" name="image" id="image"
                                                                           class="form-control" accept="image/*,application/pdf">
                                                                    <input type="hidden" name="old_image"
                                                                           id="old_image">
                                                                    <br>
                                                                    <img id="preview-image" src="#"
                                                                         style="max-width:100px; display:none; cursor: pointer;"
                                                                         class="img-thumbnail" title="คลิกเพื่อดูไฟล์"/>
                                                                </div>

                                                                <div class="col-sm-3">
                                                                    <label for="prefix" class="control-label">คำนำหน้าชื่อ</label>
                                                                    <select id="prefix" name="prefix"
                                                                            class="form-control"
                                                                            data-live-search="true"
                                                                            title="Please select">
                                                                        <option value="นาย">นาย</option>
                                                                        <option value="นาง">นาง</option>
                                                                        <option value="นางสาว">นางสาว</option>
                                                                    </select>
                                                                </div>

                                                                <div class="col-sm-3">
                                                                    <label for="sex" class="control-label">เพศ</label>
                                                                    <select id="sex" name="sex"
                                                                            class="form-control"
                                                                            data-live-search="true"
                                                                            title="Please select">
                                                                        <option value="-">ไม่ระบุ</option>
                                                                        <option value="M">ชาย</option>
                                                                        <option value="F">หญิง</option>
                                                                    </select>
                                                                </div>
                                                            </div>

                                                            <div class="form-group row">
                                                                <div class="col-sm-3">
                                                                    <label for="f_name"
                                                                           class="control-label">ชื่อ</label>
                                                                    <input type="text" class="form-control"
                                                                           id="f_name" name="f_name" required="required"
                                                                           placeholder="ชื่อ">
                                                                </div>
                                                                <div class="col-sm-3">
                                                                    <label for="l_name"
                                                                           class="control-label">นามสกุล</label>
                                                                    <input type="text" class="form-control"
                                                                           id="l_name" name="l_name" required="required"
                                                                           placeholder="นามสกุล">
                                                                </div>
                                                                <div class="col-sm-3">
                                                                    <label for="nick_name" class="control-label">ชื่อเล่น</label>
                                                                    <input type="text" class="form-control"
                                                                           id="nick_name" name="nick_name"
                                                                           required="required"
                                                                           placeholder="ชื่อเล่น">
                                                                </div>
                                                                <div class="col-sm-3">
                                                                    <label for="start_work_date" class="control-label">วันทีเริ่มงาน</label>
                                                                    <i class="fa fa-calendar" aria-hidden="true"></i>
                                                                    <input type="text" class="form-control"
                                                                           id="start_work_date" name="start_work_date"
                                                                           required="required" value="" readonly="true"
                                                                           placeholder="วันทีเริ่มงาน">
                                                                </div>
                                                            </div>

                                                            <div class="form-group row">
                                                                <div class="col-sm-3">
                                                                    <label for="work_age"
                                                                           class="control-label">อายุงาน</label>
                                                                    <input type="text" class="form-control"
                                                                           id="work_age" name="work_age" value=""
                                                                           readonly="true"
                                                                           placeholder="อายุงาน">
                                                                </div>
                                                                <div class="col-sm-3">
                                                                    <label for="status" class="control-label">สถานะการทำงาน</label>
                                                                    <select id="status" name="status"
                                                                            class="form-control" data-live-search="true"
                                                                            title="Please select">
                                                                        <option value="Y" selected>ทำงานปกติ</option>
                                                                        <option value="N">ลาออก</option>
                                                                    </select>
                                                                </div>
                                                                <div class="col-sm-3">
                                                                    <label for="week_holiday" class="control-label">วันหยุดประจำสัปดาห์</label>
                                                                    <select id="week_holiday" name="week_holiday"
                                                                            class="form-control" data-live-search="true"
                                                                            title="Please select">
                                                                        <option value="0">ไม่ระบุ</option>
                                                                        <option value="1">วันจันทร์</option>
                                                                        <option value="2">วันอังคาร</option>
                                                                        <option value="3">วันพุธ</option>
                                                                        <option value="4">วันพฤหัสบดี</option>
                                                                        <option value="5">วันศุกร์</option>
                                                                        <option value="6">วันเสาร์</option>
                                                                        <option value="7">วันอาทิตย์</option>
                                                                    </select>
                                                                </div>
                                                                <div class="col-sm-3">
                                                                    <label for="phone"
                                                                           class="control-label">โทรศัพท์</label>
                                                                    <input type="text" class="form-control"
                                                                           id="phone" name="phone" placeholder="">
                                                                </div>
                                                            </div>

                                                            <div class="form-group row">
                                                                <div class="col-sm-2">
                                                                    <label for="salary_type" class="control-label">ประเภทพนักงาน</label>
                                                                    <select id="salary_type" name="salary_type"
                                                                            class="form-control" data-live-search="true"
                                                                            title="Please select">
                                                                        <option value="M">รายเดือน</option>
                                                                        <option value="D">รายวัน</option>
                                                                    </select>
                                                                </div>
                                                                <div class="col-sm-2">
                                                                    <label for="salary"
                                                                           class="control-label">เงินเดือน/ค่าจ้าง</label>
                                                                    <input type="text" class="form-control"
                                                                           id="salary" name="salary" placeholder="">
                                                                </div>
                                                                <div class="col-sm-4">
                                                                    <input type="hidden" id="position_id"
                                                                           name="position_id">
                                                                    <label for="position_desc" class="control-label">ตำแหน่ง</label>
                                                                    <div class="input-group">
                                                                        <input type="text" class="form-control"
                                                                               id="position_desc" name="position_desc"
                                                                               readonly="true" placeholder="">
                                                                        <div class="input-group-append">
                                                                            <a data-toggle="modal"
                                                                               href="#SearchPositionModal"
                                                                               class="btn btn-primary">
                                                                                เลือก <i class="fa fa-search"
                                                                                         aria-hidden="true"></i>
                                                                            </a>
                                                                        </div>
                                                                    </div>
                                                                </div>

                                                                <div class="col-sm-4">
                                                                    <input type="hidden" class="form-control"
                                                                           id="work_time_id" name="work_time_id">
                                                                    <label for="work_time_detail" class="control-label">ตารางเวลาทำงาน</label>
                                                                    <div class="input-group">
                                                                        <input type="text" class="form-control"
                                                                               id="work_time_detail"
                                                                               name="work_time_detail"
                                                                               readonly placeholder="ตารางเวลาทำงาน">
                                                                        <div class="input-group-append">
                                                                            <a data-toggle="modal"
                                                                               href="#SearchWorkTimeModal"
                                                                               class="btn btn-primary">
                                                                                เลือก <i class="fa fa-search"
                                                                                         aria-hidden="true"></i>
                                                                            </a>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>

                                                            <div class="form-group row">
                                                                <div class="col-sm-3">
                                                                    <label for="salary_type_1" class="control-label">ประวัติเงินเดือนก่อนหน้า</label>
                                                                    <select id="salary_type_1" name="salary_type_1"
                                                                            class="form-control" data-live-search="true"
                                                                            title="Please select">
                                                                        <option value="-">เงินเดือนก่อนหน้า</option>
                                                                    </select>
                                                                </div>
                                                                <div class="col-sm-8">
                                                                    <label for="salary_history"
                                                                           class="control-label">เงินเดือน/ค่าจ้าง ก่อนหน้า</label>
                                                                    <input type="text" class="form-control"
                                                                           id="salary_history" name="salary_history" placeholder="">
                                                                </div>
                                                            </div>

                                                        </div>

                                                        <div class="modal-footer">
                                                            <input type="hidden" name="id" id="id"/>
                                                            <input type="hidden" name="action" id="action" value=""/>
                                                            <span class="icon-input-btn">
                                                            <input type="submit" name="save" id="save"
                                                                   class="btn btn-primary" value="บันทึก"/>
                                                            </span>
                                                            <button type="button" class="btn btn-danger"
                                                                    data-dismiss="modal">ปิด <i class="fa fa-times"></i>
                                                            </button>
                                                        </div>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="modal fade" id="imagePreviewModal" tabindex="-1" role="dialog" aria-hidden="true">
                                            <div class="modal-dialog modal-lg">
                                                <div class="modal-content">
                                                    <div class="modal-header">
                                                        <h5 class="modal-title">แสดงรูปภาพ</h5>
                                                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                                            <span aria-hidden="true">&times;</span>
                                                        </button>
                                                    </div>
                                                    <div class="modal-body text-center">
                                                        <img src="" id="full-preview-image" style="max-width: 100%; height: auto; border-radius: 5px;" />
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="modal fade" id="SearchPositionModal">
                                            <div class="modal-dialog modal-lg">
                                                <div class="modal-content">
                                                    <div class="modal-header">
                                                        <h4 class="modal-title">รายชื่อตำแหน่ง</h4>
                                                        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">X</button>
                                                    </div>
                                                    <div class="modal-body">
                                                        <table cellpadding="0" cellspacing="0" border="0" class="display" id="TablePositionList" width="100%">
                                                            <thead>
                                                            <tr>
                                                                <th>รหัสตำแหน่ง</th>
                                                                <th>ชื่อตำแหน่ง</th>
                                                                <th>Action</th>
                                                            </tr>
                                                            </thead>
                                                        </table>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="modal fade" id="SearchWorkTimeModal">
                                            <div class="modal-dialog modal-lg">
                                                <div class="modal-content">
                                                    <div class="modal-header">
                                                        <h4 class="modal-title">รายชื่อตารางเวลาทำงาน</h4>
                                                        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">X</button>
                                                    </div>
                                                    <div class="modal-body">
                                                        <table cellpadding="0" cellspacing="0" border="0" class="display" id="TableWorkTimeList" width="100%">
                                                            <thead>
                                                            <tr>
                                                                <th>รหัสเวลาทำงาน</th>
                                                                <th>รายละเอียดเวลาทำงาน</th>
                                                                <th>Action</th>
                                                            </tr>
                                                            </thead>
                                                        </table>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                </div>
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

    <a class="scroll-to-top rounded" href="#page-top">
        <i class="fas fa-angle-up"></i>
    </a>

    <script src="vendor/jquery/jquery.min.js"></script>
    <script src="vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
    <script src="vendor/jquery-easing/jquery.easing.min.js"></script>
    <script src="js/myadmin.min.js"></script>
    <script src="js/modal/show_position_modal.js"></script>
    <script src="js/modal/show_worktime_modal.js"></script>
    <script src="js/util/calculate_datetime.js"></script>
    <script src="vendor/bootstrap-datepicker/js/bootstrap-datepicker.min.js"></script>
    <script src="vendor/date-picker-1.9/js/bootstrap-datepicker.js"></script>
    <script src="vendor/date-picker-1.9/locales/bootstrap-datepicker.th.min.js"></script>
    <link href="vendor/date-picker-1.9/css/bootstrap-datepicker.css" rel="stylesheet"/>
    <script src="vendor/datatables/v11/bootbox.min.js"></script>
    <script src="vendor/datatables/v11/jquery.dataTables.min.js"></script>
    <link rel="stylesheet" href="vendor/datatables/v11/jquery.dataTables.min.css"/>
    <link rel="stylesheet" href="vendor/datatables/v11/buttons.dataTables.min.css"/>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.min.js"></script>
    <script>pdfjsLib.GlobalWorkerOptions.workerSrc = 'https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.worker.min.js';</script>

    <script>
        $(document).ready(function () {
            // Path ของโลโก้ PDF (ตรวจสอบให้แน่ใจว่าไฟล์มีอยู่จริง)
            const pdfLogo = "img/pdf_logo.png";

            // DataTables Setup
            let formData = {action: "GET_EMPLOYEE", sub_action: "GET_MASTER", page_manage: "ADMIN",};
            let dataRecords = $('#TableRecordList').DataTable({
                'lengthMenu': [[7, 10, 20, 50, 100], [7, 10, 20, 50, 100]],
                'language': {
                    search: 'ค้นหา', lengthMenu: 'แสดง _MENU_ รายการ',
                    info: 'หน้าที่ _PAGE_ จาก _PAGES_',
                    infoEmpty: 'ไม่มีข้อมูล',
                    zeroRecords: "ไม่มีข้อมูลตามเงื่อนไข",
                    infoFiltered: '(กรองข้อมูลจากทั้งหมด _MAX_ รายการ)',
                    paginate: { previous: 'ก่อนหน้า', last: 'สุดท้าย', next: 'ต่อไป' }
                },
                'processing': true,
                'serverSide': true,
                'serverMethod': 'post',
                <?php if ($_SESSION['deviceType'] !== 'computer') { echo "'scrollX': true,"; } ?>
                'ajax': { 'url': 'model/manage_employee_process.php', 'data': formData },
                'columns': [
                    {data: 'emp_id'}, {data: 'full_name'}, {data: 'nick_name'}, {data: 'position_desc'},
                    {data: 'start_work_date'}, {data: 'work_time_detail'}, {data: 'status'}, {data: 'update'},
                ]
            });

            $('#btnReload').on('click', function () {
                $('#TableRecordList').DataTable().ajax.reload();
            });

            $('#btnExport').on('click', function () {
                let exportStatus = $('#export_status').val();
                let exportFormData = {action: "EXPORT_EXCEL", export_status: exportStatus};
                $.ajax({
                    url: 'model/manage_employee_process.php',
                    method: 'POST',
                    data: exportFormData,
                    success: function (response) {
                        if (response.trim() === 'error') {
                            alertify.error('ไม่สามารถ export ข้อมูลได้');
                            return;
                        }
                        let data = JSON.parse(response);
                        if (data.length === 0) {
                            alertify.warning('ไม่มีข้อมูลสำหรับ export');
                            return;
                        }

                        let headers = [
                            'รหัสพนักงาน', 'คำนำหน้า', 'ชื่อ', 'นามสกุล', 'ชื่อเล่น',
                            'เพศ', 'วันเริ่มงาน', 'อายุงาน', 'สถานะ', 'วันหยุดประจำสัปดาห์',
                            'โทรศัพท์', 'ประเภทพนักงาน', 'เงินเดือน/ค่าจ้าง',
                            'เงินเดือนก่อนหน้า', 'ตำแหน่ง', 'ตารางเวลาทำงาน'
                        ];

                        let csvContent = '\uFEFF';
                        csvContent += headers.join(',') + '\n';

                        data.forEach(function (row) {
                            let status = row.status === 'Y' ? 'ทำงานปกติ' : 'ลาออก';
                            let sex = '';
                            if (row.sex === 'M') sex = 'ชาย';
                            else if (row.sex === 'F') sex = 'หญิง';
                            else sex = 'ไม่ระบุ';

                            let salary_type = row.salary_type === 'M' ? 'รายเดือน' : 'รายวัน';

                            let week_holiday = '';
                            let holidayMap = {
                                '0': 'ไม่ระบุ', '1': 'วันจันทร์', '2': 'วันอังคาร',
                                '3': 'วันพุธ', '4': 'วันพฤหัสบดี', '5': 'วันศุกร์',
                                '6': 'วันเสาร์', '7': 'วันอาทิตย์'
                            };
                            week_holiday = holidayMap[row.week_holiday] || 'ไม่ระบุ';

                            let values = [
                                '"' + (row.emp_id || '') + '"',
                                '"' + (row.prefix || '') + '"',
                                '"' + (row.f_name || '') + '"',
                                '"' + (row.l_name || '') + '"',
                                '"' + (row.nick_name || '') + '"',
                                '"' + sex + '"',
                                '"' + (row.start_work_date || '') + '"',
                                '"' + (row.work_age || '') + '"',
                                '"' + status + '"',
                                '"' + week_holiday + '"',
                                '"' + (row.phone || '') + '"',
                                '"' + salary_type + '"',
                                '"' + (row.salary || '') + '"',
                                '"' + (row.salary_history || '') + '"',
                                '"' + (row.position_desc || '') + '"',
                                '"' + (row.work_time_detail || '') + '"'
                            ];
                            csvContent += values.join(',') + '\n';
                        });

                        let blob = new Blob([csvContent], {type: 'text/csv;charset=utf-8;'});
                        let link = document.createElement('a');
                        let url = URL.createObjectURL(blob);
                        link.setAttribute('href', url);
                        link.setAttribute('download', 'employee_' + new Date().toISOString().slice(0, 10) + '.csv');
                        link.style.visibility = 'hidden';
                        document.body.appendChild(link);
                        link.click();
                        document.body.removeChild(link);

                        alertify.success('Export Excel สำเร็จ');
                    },
                    error: function () {
                        alertify.error('เกิดข้อผิดพลาดในการ export');
                    }
                });
            });

            // Helper function: รับนามสกุลไฟล์
            function getFileExt(filename) {
                return filename.split('.').pop().toLowerCase();
            }

            // Function: แปลง PDF หน้าแรกเป็นรูปภาพ PNG
            async function convertPdfToImage(pdfData, scale = 2) {
                try {
                    const loadingTask = pdfjsLib.getDocument({ data: pdfData });
                    const pdf = await loadingTask.promise;
                    const page = await pdf.getPage(1);
                    
                    const viewport = page.getViewport({ scale: scale });
                    const canvas = document.createElement('canvas');
                    const context = canvas.getContext('2d');
                    canvas.height = viewport.height;
                    canvas.width = viewport.width;

                    await page.render({ canvasContext: context, viewport: viewport }).promise;
                    return canvas.toDataURL('image/png');
                } catch (error) {
                    console.error('Error converting PDF:', error);
                    return null;
                }
            }

            // Preview รูปภาพ/ไฟล์ เมื่อเลือกไฟล์ใหม่จากเครื่อง
            $('#image').on('change', async function () {
                const input = this;
                if (input.files && input.files[0]) {
                    const file = input.files[0];
                    const reader = new FileReader();
                    const ext = getFileExt(file.name);

                    if (ext === 'pdf') {
                        // อ่านไฟล์ PDF เป็น ArrayBuffer
                        const arrayBuffer = await file.arrayBuffer();
                        const fileName = 'ps33_' + file.name;
                        
                        // แปลง PDF เป็นรูปภาพ
                        const imageData = await convertPdfToImage(arrayBuffer, 2);
                        
                        if (imageData) {
                            // แสดงรูปที่แปลงแล้ว
                            $('#preview-image').attr('src', imageData).show();
                            $('#preview-image').data('type', 'image');
                            $('#preview-image').data('pdf-name', fileName);
                        } else {
                            // ถ้าแปลงไม่ได้ ใช้ logo
                            $('#preview-image').attr('src', pdfLogo).show();
                            $('#preview-image').data('type', 'pdf');
                            $('#preview-image').data('pdf-name', fileName);
                        }
                    } else {
                        // สำหรับรูปภาพปกติ
                        reader.onload = function (e) {
                            $('#preview-image').attr('src', e.target.result).show();
                            $('#preview-image').data('type', 'image');
                        };
                        reader.readAsDataURL(file);
                    }
                }
            });

            // คลิกที่ Thumbnail เพื่อดูรูปใหญ่ หรือ เปิด PDF New Window
            $('#preview-image').on('click', function() {
                const type = $(this).data('type');
                const src = $(this).attr('src');

                if (type === 'pdf') {
                    const pdfData = $(this).data('pdf-data');
                    const pdfName = $(this).data('pdf-name') || 'ps33_pdf';
                    if (pdfData) {
                        // กรณีเป็นไฟล์ที่เพิ่งเลือกใหม่
                        const newTab = window.open();
                        newTab.document.write('<iframe src="' + pdfData + '" frameborder="0" style="border:0; top:0px; left:0px; bottom:0px; right:0px; width:100%; height:100%;" allowfullscreen></iframe>');
                        newTab.document.title = pdfName;
                    } else if (src !== pdfLogo) {
                        // กรณีฉุกเฉิน
                        const newTab = window.open(src, pdfName);
                        newTab.document.title = pdfName;
                    }
                } else {
                    // กรณีเป็นรูปภาพ JPG/PNG
                    $('#full-preview-image').attr('src', src);
                    $('#imagePreviewModal').modal('show');
                }
            });

            // Form Submit
            $("#recordModal").on('submit', '#recordForm', function (event) {
                event.preventDefault();
                $('#save').attr('disabled', 'disabled');
                let formData = new FormData(this);
                $.ajax({
                    url: 'model/manage_employee_process.php',
                    method: "POST",
                    data: formData,
                    contentType: false,
                    processData: false,
                    success: function (data) {
                        alertify.success(data);
                        $('#recordForm')[0].reset();
                        $('#recordModal').modal('hide');
                        $('#save').attr('disabled', false);
                        dataRecords.ajax.reload();
                    },
                    error: function () {
                        alertify.error("ไม่สามารถส่งข้อมูลได้");
                        $('#save').attr('disabled', false);
                    }
                });
            });

            // Add Record
            $("#btnAdd").click(function () {
                $('#recordModal').modal('show');
                $('#recordForm')[0].reset();
                $('#preview-image').hide();
                $('#id, #old_image').val("");
                $('.modal-title').html("<i class='fa fa-plus'></i> ADD Record");
                $('#action').val('ADD');
            });
        });

        // Edit Record
        $("#TableRecordList").on('click', '.update', function () {
            let id = $(this).attr("id");
            let formData = {action: "GET_DATA", id: id};
            const pdfLogo = "img/pdf_logo.png";

            $.ajax({
                type: "POST",
                url: 'model/manage_employee_process.php',
                dataType: "json",
                data: formData,
                success: function (response) {
                    if(response.length > 0) {
                        let res = response[0];
                        $('#recordModal').modal('show');
                        $('#id').val(res.id);
                        $('#emp_id').val(res.emp_id);
                        $('#f_name').val(res.f_name);
                        $('#l_name').val(res.l_name);
                        $('#prefix').val(res.prefix);
                        $('#sex').val(res.sex);
                        $('#nick_name').val(res.nick_name);
                        $('#phone').val(res.phone);
                        $('#start_work_date').val(res.start_work_date);
                        $('#position_id').val(res.position_id);
                        $('#position_desc').val(res.position_desc);
                        $('#work_time_id').val(res.work_time_id);
                        $('#work_time_detail').val(res.work_time_detail);
                        $('#salary_type').val(res.salary_type);
                        $('#salary').val(res.salary);
                        $('#salary_history').val(res.salary_history);
                        $('#week_holiday').val(res.week_holiday);
                        $('#status').val(res.status);
                        $('#old_image').val(res.image);

                        $('#work_age').val(getAge(res.start_work_date));

                        // จัดการแสดงผลไฟล์เดิมที่มีใน Server
                        if (res.image) {
                            const ext = res.image.split('.').pop().toLowerCase();
                            const fullPath = 'uploads/employees/' + res.image;

                            if (ext === 'pdf') {
                                $('#preview-image').attr('src', pdfLogo).show();
                                $('#preview-image').data('type', 'pdf');
                                // ผูก event เปิดไฟล์เดิมจาก Server
                                $('#preview-image').off('click').on('click', function() {
                                    const pdfTitle = 'ps33_' + res.image;
                                    const newTab = window.open(fullPath, pdfTitle);
                                    newTab.document.title = pdfTitle;
                                });
                            } else {
                                $('#preview-image').attr('src', fullPath).show();
                                $('#preview-image').data('type', 'image');
                                // ผูก event เปิด modal สำหรับรูปเดิม
                                $('#preview-image').off('click').on('click', function() {
                                    $('#full-preview-image').attr('src', fullPath);
                                    $('#imagePreviewModal').modal('show');
                                });
                            }
                        } else {
                            $('#preview-image').hide();
                        }

                        $('.modal-title').html("<i class='fa fa-edit'></i> Edit Record");
                        $('#action').val('UPDATE');
                    }
                }
            });
        });

        // Datepicker & Age
        $(document).ready(function () {
            $('#start_work_date').datepicker({
                format: "dd-mm-yyyy",
                todayHighlight: true,
                language: "th",
                autoclose: true
            }).on('changeDate', function(e) {
                $('#work_age').val(getAge($(this).val()));
            });
        });

        function getAge(startWorkDate) {
            if (!startWorkDate) return "";
            const parts = startWorkDate.split(/[-/]/);
            if (parts.length !== 3) return "";
            const startDate = new Date(parts[2], parts[1] - 1, parts[0]);
            const today = new Date();
            let years = today.getFullYear() - startDate.getFullYear();
            let months = today.getMonth() - startDate.getMonth();
            let days = today.getDate() - startDate.getDate();
            if (days < 0) { months--; const lastMonth = new Date(today.getFullYear(), today.getMonth(), 0); days += lastMonth.getDate(); }
            if (months < 0) { years--; months += 12; }
            return years + " ปี " + months + " เดือน " + days + " วัน";
        }
    </script>
    </body>
    </html>
<?php } ?>