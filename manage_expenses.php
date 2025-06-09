<?php
include('includes/Header.php');
$curr_date = date("d-m-Y");
if (strlen($_SESSION['alogin']) == "") {
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
                <!-- Container Fluid-->
                <div class="container-fluid" id="container-wrapper">
                    <div class="d-sm-flex align-items-center justify-content-between mb-4">
                        <h1 class="h4 mb-0 text-gray-800"><?php echo urldecode($_GET['s']) ?></h1>
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
                                            <label for="description"
                                                   class="control-label"><b>เพิ่ม <?php echo urldecode($_GET['s']) ?></b></label>

                                            <button type='button' name='btnAdd' id='btnAdd'
                                                    class='btn btn-primary btn-xs'>Add
                                                <i class="fa fa-plus"></i>
                                            </button>
                                        </div>

                                        <div class="col-md-12 col-md-offset-2">
                                            <table id='TableRecordList' class='display dataTable'>
                                                <thead>
                                                <tr>
                                                    <th>เลขที่</th>
                                                    <th>วันที่</th>
                                                    <th>เดือน</th>
                                                    <th>ปี</th>
                                                    <th>รายการ</th>
                                                    <th>ประเภทค่าใช้จ่าย</th>
                                                    <th>จำนวนรายการ</th>
                                                    <th>หน่วยนับ</th>
                                                    <th>จำนวนเงิน</th>
                                                    <th>การตรวจสอบ</th>
                                                    <th>Action</th>
                                                    <th>Action</th>
                                                </tr>
                                                </thead>
                                                <tfoot>
                                                <tr>
                                                    <th>เลขที่</th>
                                                    <th>วันที่</th>
                                                    <th>เดือน</th>
                                                    <th>ปี</th>
                                                    <th>รายการ</th>
                                                    <th>ประเภทค่าใช้จ่าย</th>
                                                    <th>จำนวนรายการ</th>
                                                    <th>หน่วยนับ</th>
                                                    <th>จำนวนเงิน</th>
                                                    <th>การตรวจสอบ</th>
                                                    <th>Action</th>
                                                    <th>Action</th>
                                                </tr>
                                                </tfoot>
                                            </table>

                                            <div id="result"></div>

                                        </div>

                                        <div class="modal fade" id="recordModal">
                                            <div class="modal-dialog modal-lg">
                                                <div class="modal-content">

                                                    <div class="modal-header">
                                                        <h4 class="modal-title">Modal title</h4>
                                                        <button type="button" class="close" data-dismiss="modal"
                                                                aria-hidden="true">×
                                                        </button>
                                                    </div>

                                                    <form method="post" id="recordForm" enctype="multipart/form-data">
                                                        <div class="modal-body">
                                                            <div class="form-group row">
                                                                <div class="col-sm-4">
                                                                    <label for="expense_date"
                                                                           class="control-label">วันที่ทำรายการ</label>
                                                                    <i class="fa fa-calendar"
                                                                       aria-hidden="true"></i>
                                                                    <input type="text" class="form-control"
                                                                           id="expense_date"
                                                                           name="expense_date"
                                                                           value="<?php echo $curr_date ?>"
                                                                           required="required"
                                                                           readonly="true"
                                                                           placeholder="">
                                                                </div>

                                                                <div class="col-sm-8">
                                                                    <label for="description"
                                                                           class="control-label">รายการค่าใช้จ่าย</label>
                                                                    <input list="descriptionList" type="text" class="form-control"
                                                                           id="description" name="description" required="required" placeholder="">
                                                                    <datalist id="descriptionList">
                                                                        <?php
                                                                        $stmt = $conn->prepare("SELECT DISTINCT description FROM ims_expenses WHERE description IS NOT NULL AND description != '' ORDER BY description ASC");
                                                                        $stmt->execute();
                                                                        $descriptions = $stmt->fetchAll(PDO::FETCH_COLUMN);
                                                                        foreach ($descriptions as $description) {
                                                                            echo '<option value="' . htmlspecialchars($description) . '">';
                                                                        }
                                                                        ?>
                                                                    </datalist>
                                                                </div>
                                                            </div>

                                                            <div class="form-group row align-items-center">
                                                                <!-- category_id -->
                                                                <div class="col-md-2">
                                                                    <label for="category_id" class="control-label">รหัสประเภท</label>
                                                                    <input type="text" class="form-control" id="category_id" name="category_id" required readonly="true">
                                                                </div>

                                                                <!-- category_name -->
                                                                <div class="col-md-6">
                                                                    <label for="category_name" class="control-label">ประเภทค่าใช้จ่าย</label>
                                                                    <input type="text" class="form-control" id="category_name" name="category_name" readonly>
                                                                </div>

                                                                <!-- ปุ่ม Click -->
                                                                <div class="col-md-4">
                                                                    <label class="control-label d-block">&nbsp;</label>
                                                                    <a data-toggle="modal" href="#Search-PG-Modal" class="btn btn-primary w-100">
                                                                        Click <i class="fa fa-search" aria-hidden="true"></i>
                                                                    </a>
                                                                </div>
                                                            </div>

                                                            <div class="form-group row align-items-end">
                                                                <!-- จำนวน -->
                                                                <div class="col-sm-4">
                                                                    <label for="qty" class="control-label">จำนวน</label>
                                                                    <input type="text" class="form-control" id="qty" name="qty" required>
                                                                </div>

                                                                <!-- ซ่อน unit_id ไว้ -->
                                                                <input type="hidden" id="unit_id" name="unit_id" required>

                                                                <!-- หน่วยนับ -->
                                                                <div class="col-sm-4">
                                                                    <label for="unit_name" class="control-label">หน่วยนับ</label>
                                                                    <input type="text" class="form-control" id="unit_name" name="unit_name" readonly required>
                                                                </div>

                                                                <!-- ปุ่มเลือก -->
                                                                <div class="col-sm-4">
                                                                    <label class="control-label d-block">เลือก</label>
                                                                    <a data-toggle="modal" href="#SearchUnitModal" class="btn btn-primary w-100">
                                                                        Click <i class="fa fa-search"></i>
                                                                    </a>
                                                                </div>
                                                            </div>

                                                            <div class="form-group row">
                                                                <div class="col-sm-6">
                                                                    <label for="amount" class="control-label">จำนวนเงิน
                                                                        (บาท)</label>
                                                                    <input type="text" class="form-control"
                                                                           id="amount" name="amount" required
                                                                           placeholder="">
                                                                </div>

                                                                <div class="col-sm-6">
                                                                    <label for="inv" class="control-label">หมายเลขใบเสร็จฯ
                                                                        / Invoice</label>
                                                                    <input type="text" class="form-control" id="inv"
                                                                           name="inv" placeholder="">
                                                                </div>
                                                            </div>

                                                            <div class="form-group">
                                                                <label for="file_attach">แนบไฟล์ / รูปภาพ</label>
                                                                <div id="dropArea">ลากไฟล์มาวางที่นี่ หรือ <strong>คลิกเพื่อเลือกไฟล์</strong>
                                                                    <input type="file" id="file_attach"
                                                                           name="file_attach[]" multiple hidden>
                                                                </div>
                                                                <div id="previewList" class="preview-grid"></div>
                                                            </div>

                                                            <input type="hidden" id="existing_files"
                                                                   name="existing_files">

                                                            <div class="form-group">
                                                                <label>ไฟล์แนบ</label>
                                                                <div id="filePreview"
                                                                     class="border rounded p-2 bg-light"></div>
                                                            </div>

                                                            <div class="form-group row">
                                                                <div class="col-sm-12">
                                                                    <label for="remark"
                                                                           class="control-label">หมายเหตุ</label>
                                                                    <input type="text" class="form-control"
                                                                           id="remark"
                                                                           name="remark"
                                                                           placeholder="">
                                                                </div>
                                                            </div>

                                                            <div class="form-group">
                                                                <label for="approve_status" class="control-label">การตรวจสอบรายการ</label>
                                                                <select id="approve_status" name="approve_status"
                                                                        class="form-control" data-live-search="true"
                                                                        title="Please select">
                                                                    <option value="N" selected>ยังไม่ยืนยัน
                                                                        (รอตรวจสอบ)
                                                                    </option>
                                                                    <option value="Y">ยืนยันรายการ (อนุมัติ)
                                                                    </option>
                                                                </select>
                                                            </div>

                                                        </div>
                                                        <div class="modal-footer">
                                                            <input type="hidden" name="id" id="id"/>
                                                            <input type="hidden" name="action" id="action" value=""/>
                                                            <span class="icon-input-btn">
                                                                <i class="fa fa-check"></i>
                                                            <input type="submit" name="save" id="save"
                                                                   class="btn btn-primary" value="Save"/>
                                                            </span>
                                                            <button type="button" class="btn btn-danger"
                                                                    data-dismiss="modal">Close <i
                                                                        class="fa fa-times"></i>
                                                            </button>
                                                        </div>
                                                    </form>

                                                </div>
                                            </div>
                                        </div>

                                        <div class="modal fade" id="Search-PG-Modal">
                                            <div class="modal-dialog modal-lg">
                                                <div class="modal-content">
                                                    <div class="modal-header">
                                                        <h4 class="modal-title">Modal title</h4>
                                                        <button type="button" class="close" data-dismiss="modal"
                                                                aria-hidden="true">×
                                                        </button>
                                                    </div>

                                                    <div class="container"></div>
                                                    <div class="modal-body">

                                                        <div class="modal-body">

                                                            <table cellpadding="0" cellspacing="0" border="0"
                                                                   class="display"
                                                                   id="TableCatList"
                                                                   width="100%">
                                                                <thead>
                                                                <tr>
                                                                    <th>รหัส</th>
                                                                    <th>ประเภทค่าใช้จ่าย</th>
                                                                    <th>Action</th>
                                                                </tr>
                                                                </thead>
                                                                <tfoot>
                                                                <tr>
                                                                    <th>รหัส</th>
                                                                    <th>ประเภทค่าใช้จ่าย</th>
                                                                    <th>Action</th>
                                                                </tr>
                                                                </tfoot>
                                                            </table>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="modal fade" id="SearchUnitModal">
                                            <div class="modal-dialog modal-lg">
                                                <div class="modal-content">
                                                    <div class="modal-header">
                                                        <h4 class="modal-title">Modal title</h4>
                                                        <button type="button" class="close" data-dismiss="modal"
                                                                aria-hidden="true">×
                                                        </button>
                                                    </div>

                                                    <div class="container"></div>
                                                    <div class="modal-body">

                                                        <div class="modal-body">

                                                            <table cellpadding="0" cellspacing="0" border="0"
                                                                   class="display"
                                                                   id="TableUnitList"
                                                                   width="100%">
                                                                <thead>
                                                                <tr>
                                                                    <th>รหัส</th>
                                                                    <th>หน่วยนับ</th>
                                                                    <th>Action</th>
                                                                </tr>
                                                                </thead>
                                                                <tfoot>
                                                                <tr>
                                                                    <th>รหัส</th>
                                                                    <th>หน่วยนับ</th>
                                                                    <th>Action</th>
                                                                </tr>
                                                                </tfoot>
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
    </div>

    <?php
    include('includes/Modal-Logout.php');
    include('includes/Footer.php');
    ?>


    <!-- Scroll to top -->
    <a class="scroll-to-top rounded" href="#page-top">
        <i class="fas fa-angle-up"></i>
    </a>


    <script src="vendor/jquery/jquery.min.js"></script>
    <script src="vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
    <script src="vendor/jquery-easing/jquery.easing.min.js"></script>
    <script src="js/myadmin.min.js"></script>


    <!--script src="js/modal/show_brand_modal.js"></script-->
    <script src="js/modal/show_category_modal.js"></script>
    <script src="js/modal/show_unit_modal.js"></script>

    <!-- Page level plugins -->

    <!--script src="https://cdnjs.cloudflare.com/ajax/libs/bootbox.js/5.5.2/bootbox.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.0/js/jquery.dataTables.min.js"></script>
    <link rel="stylesheet" href="https://cdn.datatables.net/1.11.0/css/jquery.dataTables.min.css"/>
    <link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.0.0/css/buttons.dataTables.min.css"/-->

    <script src="vendor/bootstrap-datepicker/js/bootstrap-datepicker.min.js"></script>

    <script src="vendor/date-picker-1.9/js/bootstrap-datepicker.js"></script>
    <script src="vendor/date-picker-1.9/locales/bootstrap-datepicker.th.min.js"></script>
    <!--link href="vendor/date-picker-1.9/css/date_picker_style.css" rel="stylesheet"/-->
    <link href="vendor/date-picker-1.9/css/bootstrap-datepicker.css" rel="stylesheet"/>

    <script src="vendor/datatables/v11/bootbox.min.js"></script>
    <script src="vendor/datatables/v11/jquery.dataTables.min.js"></script>
    <link rel="stylesheet" href="vendor/datatables/v11/jquery.dataTables.min.css"/>
    <link rel="stylesheet" href="vendor/datatables/v11/buttons.dataTables.min.css"/>

    <style>

        .icon-input-btn {
            display: inline-block;
            position: relative;
        }

        .icon-input-btn input[type="submit"] {
            padding-left: 2em;
        }

        .icon-input-btn .fa {
            display: inline-block;
            position: absolute;
            left: 0.65em;
            top: 30%;
        }
    </style>

    <style>
        #dropArea {
            border: 2px dashed #ccc;
            padding: 20px;
            text-align: center;
            border-radius: 10px;
            transition: 0.2s;
            background-color: #f8f9fa;
        }

        #dropArea.dragover {
            background-color: #e2e6ea;
            border-color: #007bff;
        }

        .preview-grid {
            display: flex;
            flex-wrap: wrap;
            margin-top: 10px;
            gap: 10px;
        }

        .preview-item {
            position: relative;
            width: 100px;
            text-align: center;
        }

        .preview-item img {
            width: 100%;
            border-radius: 5px;
        }

        .remove-btn {
            position: absolute;
            top: -10px;
            right: -10px;
            background-color: red;
            color: white;
            border-radius: 50%;
            border: none;
            width: 20px;
            height: 20px;
            font-size: 12px;
            cursor: pointer;
        }

    </style>

    <script>
        $(document).ready(function () {
            $(".icon-input-btn").each(function () {
                let btnFont = $(this).find(".btn").css("font-size");
                let btnColor = $(this).find(".btn").css("color");
                $(this).find(".fa").css({'font-size': btnFont, 'color': btnColor});
            });
        });
    </script>

    <script>
        $(document).ready(function () {
            let formDataObj = {action: "GET_EXPENSE", sub_action: "GET_MASTER"};
            let dataRecords = $('#TableRecordList').DataTable({
                'lengthMenu': [[5, 10, 20, 50, 100], [5, 10, 20, 50, 100]],
                'language': {
                    search: 'ค้นหา', lengthMenu: 'แสดง _MENU_ รายการ',
                    info: 'หน้าที่ _PAGE_ จาก _PAGES_',
                    infoEmpty: 'ไม่มีข้อมูล',
                    zeroRecords: "ไม่มีข้อมูลตามเงื่อนไข",
                    infoFiltered: '(กรองข้อมูลจากทั้งหมด _MAX_ รายการ)',
                    paginate: {
                        previous: 'ก่อนหน้า',
                        last: 'สุดท้าย',
                        next: 'ต่อไป'
                    }
                },
                'processing': true,
                'serverSide': true,
                'serverMethod': 'post',
                <?php  if ($_SESSION['deviceType'] !== 'computer') {
                    echo "'scrollX': true,";
                }?>
                'ajax': {
                    'url': 'model/manage_expense_process.php',
                    'data': formDataObj
                },
                'columns': [
                    {data: 'doc_id'},
                    {data: 'expense_date'},
                    {data: 'month_name'},
                    {data: 'exp_year'},
                    {data: 'description'},
                    {data: 'category_name'},
                    {data: 'qty', className: 'text-right'},
                    {data: 'unit_name'},
                    {data: 'amount', className: 'text-right'},
                    {data: 'approve_status'},
                    {data: 'update'},
                    {data: 'delete'}
                ]
            });

        });
    </script>

    <script>
        // *** FOR SUBMIT FORM ***
        $("#recordModal").on('submit', '#recordForm', function (event) {
            event.preventDefault();
            $('#save').attr('disabled', 'disabled');

            // ตรวจสอบ category_id ว่ามีการเลือกหรือยัง
            let categoryId = $('#category_id').val();
            if (!categoryId || categoryId === '0') {
                alertify.error("กรุณาเลือกหมวดหมู่ค่าใช้จ่าย (Category)");
                $('#save').attr('disabled', false);
                return; // ยกเลิกการ submit
            }

            if ($('#unit_id').val().trim() === '') {
                alertify.error('กรุณาเลือกหน่วยนับ');
                $('#unit_name').focus();
                e.preventDefault();
                return false;
            }

            // ใช้ FormData กับฟอร์มโดยตรง
            const formElement = document.getElementById('recordForm');
            const formData = new FormData(formElement);

            // ดึง existing files จาก hidden input ที่เป็น string เช่น "file1.jpg,file2.png"
            let existingFilesStr = $('#existing_files').val() || '';
            let existingFiles = existingFilesStr ? existingFilesStr.split(',') : [];

            // รวมชื่อไฟล์ใหม่จาก filesArray (ที่เป็น FileList หรือ array ของ File objects)
            // โดยกรองไฟล์ซ้ำจากชื่อไฟล์
            if (typeof filesArray !== 'undefined' && filesArray.length > 0) {
                filesArray.forEach(file => {
                    if (!existingFiles.includes(file.name)) {
                        formData.append('file_attach[]', file);
                    }
                });
            }

            formData.set('existing_files', existingFiles.join(','));

            $.ajax({
                url: 'model/manage_expense_process.php',
                method: "POST",
                data: formData,
                contentType: false,
                processData: false,
                success: function (data) {
                    alertify.success(data);
                    $('#recordForm')[0].reset();
                    $('#recordModal').modal('hide');
                    $('#save').attr('disabled', false);
                    $('#TableRecordList').DataTable().ajax.reload();
                },
                error: function(xhr, status, error) {
                    alertify.error("Error: " + error);
                    $('#save').attr('disabled', false);
                }
            });
        });
        // *** END FOR SUBMIT FORM ***
    </script>


    <script>
        $(document).ready(function () {
            $("#btnAdd").click(function () {

                let today = new Date();
                let day = String(today.getDate()).padStart(2, '0');
                let month = String(today.getMonth() + 1).padStart(2, '0'); // January is 0!
                let year = today.getFullYear();
                let formattedDate = day + '-' + month + '-' + year;

                $('#recordModal').modal('show');
                $('#id').val("");
                $('#expense_date').val(formattedDate);
                $('#description').val("");
                $('#category_id').val("");
                $('#category_name').val("");
                $('#unit_id').val("");
                $('#unit_name').val("");
                $('#remark').val("");
                $('#inv').val("");
                $('#qty').val("");
                $('#amount').val("");

                $('#file_attach').val("");
                filesArray = [];
                $('#previewList').html("");
                $('#filePreview').html("");

                $('.modal-title').html("<i class='fa fa-plus'></i> ADD Record");
                $('#action').val('ADD');
                $('#save').val('Save');
            });
        });
    </script>

    <script>

        $("#TableRecordList").on('click', '.update', function () {
            let id = $(this).attr("id");
            //alert(id);
            let formData = {action: "GET_DATA", id: id};
            $.ajax({
                type: "POST",
                url: 'model/manage_expense_process.php',
                dataType: "json",
                data: formData,
                success: function (response) {
                    let len = response.length;
                    for (let i = 0; i < len; i++) {
                        let id = response[i].id;
                        let expense_date = response[i].expense_date;
                        let description = response[i].description;
                        let category_id = response[i].category_id;
                        let category_name = response[i].category_name;
                        let inv = response[i].inv;
                        let qty = response[i].qty;
                        let unit_id = response[i].unit_id;
                        let unit_name = response[i].unit_name;
                        let amount = response[i].amount;
                        let remark = response[i].remark;
                        let approve_status = response[i].approve_status;
                        let file_attach = response[i].file_attach;

                        let fileHTML = "";
                        let fileList = [];

                        $('#file_attach').val("");
                        filesArray = [];
                        $('#previewList').html("");
                        $('#filePreview').html("");

                        if (file_attach) {
                            fileList = file_attach.split(",").map(f => f.trim()).filter(f => f);

                            fileList.forEach(function (file, index) {
                                let fileUrl = "uploads/files/" + file;
                                let extension = file.split('.').pop().toLowerCase();
                                let iconHTML = "";

                                if (["jpg", "jpeg", "png", "gif", "bmp", "webp"].includes(extension)) {
                                    iconHTML = `<img src="${fileUrl}" class="img-thumbnail" style="width: 80px; height: 80px; object-fit: cover;">`;
                                } else if (extension === "pdf") {
                                    iconHTML = `<i class="fa fa-file-pdf text-danger" style="font-size:40px;"></i>`;
                                } else {
                                    iconHTML = `<i class="fa fa-file text-secondary" style="font-size:40px;"></i>`;
                                }

                                fileHTML += `
        <div class="text-center m-2" style="display:inline-block;" id="fileBox_${index}">
            <a href="${fileUrl}" target="_blank">${iconHTML}</a>
            <div class="small text-truncate" style="max-width: 100px;">${file}</div>
            <button type="button" class="btn btn-sm btn-danger btnRemoveFile" data-index="${index}" data-filename="${file}">ลบ</button>
        </div>
        `;
                            });
                        } else {
                            fileHTML = "<div class='text-muted'>ไม่มีไฟล์แนบ</div>";
                        }

                        $("#filePreview").html(fileHTML);

// เก็บชื่อไฟล์ใน hidden input เพื่อส่งกลับ PHP ตอน update
                        $("#existing_files").val(fileList.join(","));


                        $('#recordModal').modal('show');
                        $('#id').val(id);
                        $('#expense_date').val(expense_date);
                        $('#description').val(description);
                        $('#category_id').val(category_id);
                        $('#category_name').val(category_name);
                        $('#inv').val(inv);
                        $('#qty').val(qty);
                        $('#unit_id').val(unit_id);
                        $('#unit_name').val(unit_name);
                        $('#amount').val(amount);
                        $('#remark').val(remark);
                        $('#approve_status').val(approve_status);
                        $('.modal-title').html("<i class='fa fa-plus'></i> Edit Record");
                        $('#action').val('UPDATE');
                        $('#save').val('Save');
                    }
                },
                error: function (response) {
                    alertify.error("error : " + response);
                }
            });
        });

    </script>

    <script>

        $("#TableRecordList").on('click', '.delete', function () {
            let id = $(this).attr("id");
            //alert(id);
            let formData = {action: "GET_DATA", id: id};
            $.ajax({
                type: "POST",
                url: 'model/manage_expense_process.php',
                dataType: "json",
                data: formData,
                success: function (response) {
                    let len = response.length;
                    for (let i = 0; i < len; i++) {
                        let id = response[i].id;
                        let expense_date = response[i].expense_date;
                        let description = response[i].description;
                        let category_id = response[i].category_id;
                        let category_name = response[i].category_name;
                        let inv = response[i].inv;
                        let qty = response[i].qty;
                        let unit_id = response[i].unit_id;
                        let unit_name = response[i].unit_name;
                        let amount = response[i].amount;
                        let remark = response[i].remark;
                        let approve_status = response[i].approve_status;
                        let file_attach = response[i].file_attach;

                        let fileHTML = "";
                        let fileList = [];

                        $('#file_attach').val("");
                        filesArray = [];
                        $('#previewList').html("");
                        $('#filePreview').html("");

                        if (file_attach) {
                            fileList = file_attach.split(",").map(f => f.trim()).filter(f => f);

                            fileList.forEach(function (file, index) {
                                let fileUrl = "uploads/files/" + file;
                                let extension = file.split('.').pop().toLowerCase();
                                let iconHTML = "";

                                if (["jpg", "jpeg", "png", "gif", "bmp", "webp"].includes(extension)) {
                                    iconHTML = `<img src="${fileUrl}" class="img-thumbnail" style="width: 80px; height: 80px; object-fit: cover;">`;
                                } else if (extension === "pdf") {
                                    iconHTML = `<i class="fa fa-file-pdf text-danger" style="font-size:40px;"></i>`;
                                } else {
                                    iconHTML = `<i class="fa fa-file text-secondary" style="font-size:40px;"></i>`;
                                }

                                fileHTML += `
        <div class="text-center m-2" style="display:inline-block;" id="fileBox_${index}">
            <a href="${fileUrl}" target="_blank">${iconHTML}</a>
            <div class="small text-truncate" style="max-width: 100px;">${file}</div>
            <button type="button" class="btn btn-sm btn-danger btnRemoveFile" data-index="${index}" data-filename="${file}">ลบ</button>
        </div>
        `;
                            });
                        } else {
                            fileHTML = "<div class='text-muted'>ไม่มีไฟล์แนบ</div>";
                        }

                        $("#filePreview").html(fileHTML);

// เก็บชื่อไฟล์ใน hidden input เพื่อส่งกลับ PHP ตอน update
                        $("#existing_files").val(fileList.join(","));


                        $('#recordModal').modal('show');
                        $('#id').val(id);
                        $('#expense_date').val(expense_date);
                        $('#description').val(description);
                        $('#category_id').val(category_id);
                        $('#category_name').val(category_name);
                        $('#inv').val(inv);
                        $('#qty').val(qty);
                        $('#unit_id').val(unit_id);
                        $('#unit_name').val(unit_name);
                        $('#amount').val(amount);
                        $('#remark').val(remark);
                        $('#approve_status').val(approve_status);
                        $('.modal-title').html("<i class='fa fa-plus'></i> Edit Record");
                        $('#action').val('DELETE');
                        $('#save').val('Confirm Delete');
                    }
                },
                error: function (response) {
                    alertify.error("error : " + response);
                }
            });
        });

    </script>

    <script>
        $(document).ready(function () {
            $('#expense_date').datepicker({
                format: "dd-mm-yyyy",
                todayHighlight: true,
                language: "th",
                autoclose: true
            });
        });
    </script>

    <script>
        const dropArea = document.getElementById("dropArea");
        const fileInput = document.getElementById("file_attach");
        const previewList = document.getElementById("previewList");
        let filesArray = [];
        const maxFileSizeMB = 20;

        dropArea.addEventListener("click", () => fileInput.click());

        // Drag events
        ["dragenter", "dragover"].forEach(eventName => {
            dropArea.addEventListener(eventName, e => {
                e.preventDefault();
                dropArea.classList.add("dragover");
            });
        });
        ["dragleave", "drop"].forEach(eventName => {
            dropArea.addEventListener(eventName, e => {
                e.preventDefault();
                dropArea.classList.remove("dragover");
            });
        });

        // Drop files
        dropArea.addEventListener("drop", e => {
            handleFiles([...e.dataTransfer.files]);
        });

        fileInput.addEventListener("change", e => {
            handleFiles([...e.target.files]);
        });

        function handleFiles(files) {
            files.forEach(file => {
                if (file.size > maxFileSizeMB * 1024 * 1024) {
                    alert(`ไฟล์ ${file.name} มีขนาดเกิน ${maxFileSizeMB} MB`);
                } else {
                    filesArray.push(file);
                }
            });
            renderPreviews();
        }

        function renderPreviews() {
            previewList.innerHTML = "";

            filesArray.forEach((file, index) => {
                const div = document.createElement("div");
                div.classList.add("preview-item");

                const isImage = file.type.startsWith("image/");
                const icon = isImage ? "🖼️" : "📄";

                if (isImage) {
                    const reader = new FileReader();
                    reader.onload = e => {
                        const img = document.createElement("img");
                        img.src = e.target.result;
                        div.appendChild(img);
                    };
                    reader.readAsDataURL(file);
                } else {
                    div.innerHTML += `<div style="font-size:2rem">${icon}</div>`;
                }

                div.innerHTML += `<small>${file.name}</small>
                              <button class="remove-btn" onclick="removeFile(${index})">×</button>`;
                previewList.appendChild(div);
            });

            updateInputFiles();
        }

        function removeFile(index) {
            filesArray.splice(index, 1);
            renderPreviews();
        }

        function updateInputFiles() {
            const dataTransfer = new DataTransfer();
            filesArray.forEach(file => dataTransfer.items.add(file));
            fileInput.files = dataTransfer.files;
        }
    </script>

    <script>
        $(document).on("click", ".btnRemoveFile", function () {
            let filename = $(this).data("filename");
            let index = $(this).data("index");

            // ลบ div แสดงไฟล์บนหน้าจอ
            $("#fileBox_" + index).remove();

            // อัปเดต hidden input ที่เก็บชื่อไฟล์
            let currentFiles = $("#existing_files").val().split(",").map(f => f.trim());
            let updatedFiles = currentFiles.filter(f => f !== filename);
            $("#existing_files").val(updatedFiles.join(","));
        });

    </script>

    </body>
    </html>

<?php } ?>