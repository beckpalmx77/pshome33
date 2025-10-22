<?php
include('includes/Header.php');
include('config/connect_db.php');
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
                                            <label for="doc_date"
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
                                                    <th>เลขที่เอกสาร</th>
                                                    <th>ปีเอกสาร</th>
                                                    <th>วันที่ติดต่อ</th>
                                                    <th>หน่วยงานที่ติดต่อ</th>
                                                    <th>ผู้ดำเนินการ</th>
                                                    <th>เรื่องที่ติดต่อ</th>
                                                    <th>ความคืบหน้า/ผลการติดต่อ</th>
                                                    <th>Action</th>
                                                </tr>
                                                </thead>
                                                <tfoot>
                                                <tr>
                                                    <th>เลขที่เอกสาร</th>
                                                    <th>ปีเอกสาร</th>
                                                    <th>วันที่ติดต่อ</th>
                                                    <th>หน่วยงานที่ติดต่อ</th>
                                                    <th>ผู้ดำเนินการ</th>
                                                    <th>เรื่องที่ติดต่อ</th>
                                                    <th>ความคืบหน้า/ผลการติดต่อ</th>
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
                                                                <div class="col-md-3">
                                                                    <label for="doc_date">วันที่ติดต่อ</label>
                                                                    <i class="fa fa-calendar"
                                                                       aria-hidden="true"></i>
                                                                    <input type="text" class="form-control"
                                                                           id="doc_date" name="doc_date"
                                                                           value="<?php echo $curr_date ?>" required
                                                                           readonly>
                                                                </div>
                                                                <div class="col-sm-3">
                                                                    <label for="doc_no"
                                                                           class="control-label">เลขที่เอกสาร</label>
                                                                    <input type="text" class="form-control"
                                                                           id="doc_no"
                                                                           name="doc_no"
                                                                           value=""
                                                                           required="required"
                                                                           readonly="true"
                                                                           placeholder="">
                                                                </div>
                                                                <input type="hidden" class="form-control"
                                                                       id="category_id"
                                                                       name="category_id">
                                                                <div class="col-sm-3">
                                                                    <label for="doc_year"
                                                                           class="control-label">ปีเอกสาร</label>
                                                                    <input type="text" class="form-control"
                                                                           id="doc_year"
                                                                           name="doc_year"
                                                                           required="required"
                                                                           readonly="true"
                                                                           placeholder="">
                                                                </div>
                                                            </div>

                                                            <div class="form-group row">
                                                                <div class="col-sm-6">
                                                                    <label for="contact_name"
                                                                           class="control-label">หน่วยงานที่ติดต่อ</label>
                                                                    <input type="text" class="form-control"
                                                                           id="contact_name"
                                                                           name="contact_name"
                                                                           placeholder="">
                                                                </div>
                                                                <div class="col-sm-6">
                                                                    <label for="actor"
                                                                           class="control-label">ผู้ดำเนินการ</label>
                                                                    <input type="text" class="form-control"
                                                                           id="actor"
                                                                           name="actor"
                                                                           placeholder="">
                                                                </div>
                                                            </div>

                                                            <div class="form-group row">
                                                                <div class="col-sm-12">
                                                                    <label for="topic"
                                                                           class="control-label">เรื่องที่ติดต่อ</label>
                                                                    <input type="text" class="form-control"
                                                                           id="topic"
                                                                           name="topic"
                                                                           placeholder="">
                                                                </div>
                                                            </div>

                                                            <div class="form-group row">
                                                                <div class="col-sm-6">
                                                                    <label for="detail"
                                                                           class="control-label">รายละเอียด</label>
                                                                    <textarea class="form-control"
                                                                              id="detail"
                                                                              name="detail"
                                                                              rows="3"
                                                                              placeholder=""></textarea>
                                                                </div>
                                                                <div class="col-sm-6">
                                                                    <label for="process_detail"
                                                                           class="control-label">ความคืบหน้า/ผลการติดต่อ</label>
                                                                    <textarea class="form-control"
                                                                              id="process_detail"
                                                                              name="process_detail"
                                                                              rows="3"
                                                                              placeholder=""></textarea>
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

                                                            <div class="form-group">
                                                                <label for="status" class="control-label">สถานะ</label>
                                                                <select id="status" name="status"
                                                                        class="form-control" data-live-search="true"
                                                                        title="Please select">
                                                                    <option value="Active" selected>Active</option>
                                                                    <option value="Inactive">Inactive</option>
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

                                </div>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
            <?php include('includes/Footer.php'); ?>
        </div>
    </div>

    <?php
    include('includes/Modal-Logout.php');
    //include('includes/Footer.php');
    ?>


    <!-- Scroll to top -->
    <a class="scroll-to-top rounded" href="#page-top">
        <i class="fas fa-angle-up"></i>
    </a>


    <script src="vendor/jquery/jquery.min.js"></script>
    <script src="vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
    <script src="vendor/jquery-easing/jquery.easing.min.js"></script>
    <script src="js/myadmin.min.js"></script>

    <script src="js/modal/show_brand_modal.js"></script>
    <script src="js/modal/show_m_category_modal.js"></script>

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
            let formDataObj = {action: "GET_DOCUMENT", sub_action: "GET_MASTER"};
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
                    'url': 'model/manage_document_contact_process.php',
                    'data': formDataObj
                },
                'columns': [
                    {data: 'doc_no'},
                    {data: 'doc_year'},
                    {data: 'doc_date'},
                    {data: 'contact_name'},
                    {data: 'actor'},
                    {data: 'topic'},
                    {data: 'process_detail'},
                    {data: 'update'}
                ]
            });
        });

    </script>

    <script>
        $(document).ready(function () {
            // *** FOR SUBMIT FORM ***
            $("#recordModal").on('submit', '#recordForm', function (event) {
                event.preventDefault();
                $('#save').attr('disabled', 'disabled');

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
                        // ตรวจสอบชื่อไฟล์ซ้ำ
                        if (!existingFiles.includes(file.name)) {
                            formData.append('file_attach[]', file);  // เพิ่มไฟล์ใหม่ที่ไม่ซ้ำ
                        }
                    });
                }

                // ส่ง existing files (ชื่อไฟล์) ไปด้วย (ให้ backend รู้ว่าไฟล์เดิมอะไรยังคงอยู่)
                formData.set('existing_files', existingFiles.join(','));

                $.ajax({
                    url: 'model/manage_document_contact_process.php',
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
                    error: function (xhr, status, error) {
                        alertify.error("Error: " + error);
                        $('#save').attr('disabled', false);
                    }
                });
            });
            // *** END FOR SUBMIT FORM ***
        });
    </script>

    <script>
        $(document).ready(function () {
            $("#btnAdd").click(function () {
                $('#recordModal').modal('show');
                $('#id').val("");
                $('#doc_no').val("");
                $('#doc_year').val("");
                $('#contact_name').val("");
                $('#actor').val("");
                $('#topic').val("");
                $('#detail').val("");
                $('#process_detail').val("");
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
                url: 'model/manage_document_contact_process.php',
                dataType: "json",
                data: formData,
                success: function (response) {
                    let len = response.length;
                    for (let i = 0; i < len; i++) {
                        let id = response[i].id;
                        let doc_no = response[i].doc_no;
                        let doc_date = response[i].doc_date;
                        let doc_year = response[i].doc_year;
                        let contact_name = response[i].contact_name;
                        let actor = response[i].actor;
                        let topic = response[i].topic;
                        let detail = response[i].detail;
                        let process_detail = response[i].process_detail;
                        let status = response[i].status;
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
                                let fileUrl = "uploads/document/" + file;
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
                        $('#doc_no').val(doc_no);
                        $('#doc_date').val(doc_date);
                        $('#doc_year').val(doc_year);
                        $('#contact_name').val(contact_name);
                        $('#actor').val(actor);
                        $('#topic').val(topic);
                        $('#detail').val(detail);
                        $('#process_detail').val(process_detail);
                        $('#status').val(status);
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
            alert(id);
        });

    </script>

    <script>
        $(document).ready(function () {
            $('#doc_date').datepicker({
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