<?php
include('includes/Header.php');
if (strlen($_SESSION['alogin']) == "" || strlen($_SESSION['item_code']) == "") {
    header("Location: index.php");
} else {
    include 'config/connect_db.php';
    $curr_date = date("d-m-Y");
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
                                        </div>

                                        <div class="col-md-12 col-md-offset-2">
                                            <table id='TableRecordList' class='display dataTable'>
                                                <thead>
                                                <tr>
                                                    <th>รหัสพัสดุ/ครุภัณฑ์</th>
                                                    <th>ชื่อพัสดุ/ครุภัณฑ์</th>
                                                    <th>หมวดหมู่</th>
                                                    <th>ยี่ห้อ/รุ่น</th>
                                                    <th>วันที่รับเข้า</th>
                                                    <th>สถานะ</th>
                                                    <th>Action</th>
                                                    <th>Action</th>
                                                </tr>
                                                </thead>
                                                <tfoot>
                                                <tr>
                                                    <th>รหัสพัสดุ/ครุภัณฑ์</th>
                                                    <th>ชื่อพัสดุ/ครุภัณฑ์</th>
                                                    <th>หมวดหมู่</th>
                                                    <th>ยี่ห้อ/รุ่น</th>
                                                    <th>วันที่รับเข้า</th>
                                                    <th>สถานะ</th>
                                                    <th>Action</th>
                                                    <th>Action</th>
                                                </tr>
                                                </tfoot>
                                            </table>

                                            <div id="result"></div>

                                        </div>

                                        <!-- Modal Form -->
                                        <div class="modal fade" id="recordModal" tabindex="-1" role="dialog"
                                             aria-labelledby="recordModalLabel" aria-hidden="true">
                                            <div class="modal-dialog modal-lg" role="document">
                                                <div class="modal-content">
                                                    <div class="modal-header">
                                                        <h5 class="modal-title" id="recordModalLabel">เพิ่ม / แก้ไข
                                                            รายการพัสดุ/ครุภัณฑ์</h5>
                                                        <button type="button" class="close" data-dismiss="modal"
                                                                aria-label="Close">
                                                            <span aria-hidden="true">&times;</span>
                                                        </button>
                                                    </div>
                                                    <form id="recordForm" method="post" enctype="multipart/form-data">
                                                        <div class="modal-body">
                                                            <div class="form-row">
                                                                <div class="form-group col-md-6">
                                                                    <label for="item_code">รหัสพัสดุ/ครุภัณฑ์</label>
                                                                    <input type="text" class="form-control"
                                                                           id="item_code" name="item_code"
                                                                           placeholder="รหัสพัสดุ/ครุภัณฑ์"
                                                                           readonly="true">
                                                                </div>
                                                                <div class="form-group col-md-6">
                                                                    <label for="item_name">ชื่อพัสดุ/ครุภัณฑ์</label>
                                                                    <input type="text" class="form-control"
                                                                           id="item_name" name="item_name"
                                                                           placeholder="ชื่อพัสดุ/ครุภัณฑ์" required>
                                                                </div>
                                                            </div>

                                                            <div class="form-row">
                                                                <div class="form-group col-md-4">
                                                                    <label for="category">หมวดหมู่</label>
                                                                    <select class="form-control" id="category"
                                                                            name="category" required>
                                                                        <?php
                                                                        $sql = "SELECT category_id, category_name FROM m_category WHERE status = 'Y' ";
                                                                        $stmt = $conn->query($sql);
                                                                        while ($row = $stmt->fetch()) {
                                                                            echo "<option value='{$row['category_id']}'>{$row['category_name']}</option>";
                                                                        }
                                                                        ?>
                                                                    </select>
                                                                </div>
                                                                <div class="form-group col-md-4">
                                                                    <label for="brand">ยี่ห้อ</label>
                                                                    <input type="text" class="form-control"
                                                                           id="brand" name="brand"
                                                                           placeholder="">
                                                                </div>
                                                                <div class="form-group col-md-4">
                                                                    <label for="model">รุ่น</label>
                                                                    <input type="text" class="form-control"
                                                                           id="model" name="model"
                                                                           placeholder="">
                                                                </div>
                                                            </div>

                                                            <div class="form-row">
                                                                <div class="form-group col-md-6">
                                                                    <label for="received_date">วันที่รับเข้า</label>
                                                                    <input type="text" class="form-control"
                                                                           id="received_date"
                                                                           name="received_date" ๅ
                                                                           required="required"
                                                                           value="<?php echo $curr_date ?>"
                                                                           readonly="true"
                                                                           placeholder="">
                                                                </div>
                                                                <div class="form-group col-md-6">
                                                                    <label for="status">สถานะ</label>
                                                                    <select id="status" name="status"
                                                                            class="form-control" required>
                                                                        <option value="ใช้งาน">ใช้งาน</option>
                                                                        <option value="เสีย">เสีย</option>
                                                                        <option value="จำหน่าย">จำหน่าย</option>
                                                                    </select>
                                                                </div>
                                                            </div>

                                                            <div class="form-group">
                                                                <label for="details">รายละเอียดเพิ่มเติม</label>
                                                                <textarea id="details" name="details"
                                                                          class="form-control" rows="3"
                                                                          placeholder="รายละเอียดเพิ่มเติม"></textarea>
                                                            </div>

                                                            <div class="form-group">
                                                                <label for="images">รูปภาพ</label>
                                                                <input type="file" class="form-control-file" id="images"
                                                                       name="images[]" multiple accept="image/*">
                                                                <div id="image-preview"
                                                                     class="mt-3 d-flex flex-wrap"></div>
                                                            </div>

                                                        </div>

                                                        <div class="modal-footer">
                                                            <input type="hidden" id="id" name="id" value="">
                                                            <input type="hidden" id="action" name="action" value="">
                                                            <button type="submit" class="btn btn-primary" id="saveBtn">
                                                                บันทึก
                                                            </button>
                                                            <button type="button" class="btn btn-secondary"
                                                                    data-dismiss="modal">ยกเลิก
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

    <!-- Page level plugins -->

    <script src="vendor/bootstrap-datepicker/js/bootstrap-datepicker.min.js"></script>

    <script src="vendor/date-picker-1.9/js/bootstrap-datepicker.js"></script>
    <script src="vendor/date-picker-1.9/locales/bootstrap-datepicker.th.min.js"></script>
    <!--link href="vendor/date-picker-1.9/css/date_picker_style.css" rel="stylesheet"/-->
    <link href="vendor/date-picker-1.9/css/bootstrap-datepicker.css" rel="stylesheet"/>

    <script src="vendor/datatables/v11/bootbox.min.js"></script>
    <script src="vendor/datatables/v11/jquery.dataTables.min.js"></script>
    <link rel="stylesheet" href="vendor/datatables/v11/jquery.dataTables.min.css"/>
    <link rel="stylesheet" href="vendor/datatables/v11/buttons.dataTables.min.css"/>

    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet"/>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>


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
            // กำหนดวันที่ปัจจุบัน
            let today = new Date();
            $('#received_date').datepicker({
                format: "dd-mm-yyyy", // รูปแบบวันที่
                todayHighlight: true, // ไฮไลต์วันที่ปัจจุบัน
                language: "th", // ภาษาไทย (ถ้าเพิ่มไฟล์ภาษาไว้)
                autoclose: true // ปิดปฏิทินอัตโนมัติเมื่อเลือกวันที่
            });
        });
    </script>

    <script>
        $(document).ready(function () {
            let formData = {action: "GET_INVENTORY", sub_action: "GET_MASTER"};
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
                    'url': 'model/manage_ equipment_process.php',
                    'data': formData
                },
                'columns': [
                    {data: 'item_code'},
                    {data: 'item_name'},
                    {data: 'category'},
                    {data: 'brand'},
                    {data: 'details'},
                    {data: 'status'},
                    {data: 'update'},
                    {data: 'delete'}
                ]
            });

        });
    </script>

    <script>
        $("#recordModal").on('submit', '#recordForm', function (event) {
            event.preventDefault();
            $('#save').attr('disabled', 'disabled');

            let formData = new FormData(this);

            // Debug ดูค่าก่อนส่ง
            for (let [key, value] of formData.entries()) {
                console.log(key, value);
            }

            $.ajax({
                url: 'model/manage_equipment_process.php',
                method: "POST",
                data: formData,
                processData: false,
                contentType: false,
                success: function (data) {
                    alertify.success(data);
                    $('#recordForm')[0].reset();
                    $('#recordModal').modal('hide');
                    $('#save').attr('disabled', false);
                    dataRecords.ajax.reload();
                },
                error: function (xhr, status, error) {
                    console.log(xhr.responseText); // ดูข้อความ error จริง
                    alertify.error("เกิดข้อผิดพลาด: " + error);
                    $('#save').attr('disabled', false);
                }
            });
        });
    </script>

    <script>
        $(document).ready(function () {
            $("#btnAdd").click(function () {
                $('#recordModal').modal('show');
                $('#id').val("");
                $('#item_code').val("");
                $('#item_name').val("");
                $('#category').val("");
                $('#brand').val("");
                $('#model').val("");
                $('#details').val("");
                $('#status').val("");
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
                url: 'model/manage_ equipment_process.php',
                dataType: "json",
                data: formData,
                success: function (response) {
                    let len = response.length;
                    for (let i = 0; i < len; i++) {
                        let id = response[i].id;
                        let item_code = response[i].item_code;
                        let item_name = response[i].item_name;
                        let category = response[i].category;
                        let brand = response[i].brand;
                        let model = response[i].model;
                        let details = response[i].details;
                        let received_date = response[i].received_date;
                        let status = response[i].status;

                        $('#recordModal').modal('show');
                        $('#id').val(id);
                        $('#item_code').val(item_code);
                        $('#item_name').val(item_name);
                        $('#category').val(category);
                        $('#brand').val(brand);
                        $('#model').val(model);
                        $('#details').val(details);
                        $('#received_date').val(received_date);
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
        const imagesInput = document.querySelector('input[name="images[]"]');
        const preview = document.getElementById('image-preview');
        let filesArray = [];

        imagesInput.addEventListener('change', function (e) {
            // เพิ่มไฟล์ใหม่ลงใน filesArray
            for (let file of e.target.files) {
                if (!file.type.match('image.*')) continue;
                filesArray.push(file);
            }
            updatePreview();
            // เคลียร์ input เพื่อให้เลือกไฟล์ใหม่ซ้ำได้
            imagesInput.value = '';
        });

        function updatePreview() {
            preview.innerHTML = ''; // เคลียร์ preview เก่า

            filesArray.forEach((file, index) => {
                let reader = new FileReader();
                reader.onload = function (e) {
                    let imgWrapper = document.createElement('div');
                    imgWrapper.style.position = 'relative';
                    imgWrapper.style.display = 'inline-block';
                    imgWrapper.style.margin = '5px';

                    let img = document.createElement('img');
                    img.src = e.target.result;
                    img.classList.add('img-thumbnail');
                    img.style.width = '120px';
                    img.style.height = 'auto';

                    // ปุ่มลบ (กากบาท)
                    let btnRemove = document.createElement('button');
                    btnRemove.textContent = '✖';
                    btnRemove.style.position = 'absolute';
                    btnRemove.style.top = '2px';
                    btnRemove.style.right = '2px';
                    btnRemove.style.background = 'rgba(255,255,255,0.7)';
                    btnRemove.style.border = 'none';
                    btnRemove.style.borderRadius = '50%';
                    btnRemove.style.cursor = 'pointer';
                    btnRemove.style.padding = '0 6px';
                    btnRemove.style.fontWeight = 'bold';
                    btnRemove.title = 'ลบภาพนี้';

                    btnRemove.addEventListener('click', () => {
                        filesArray.splice(index, 1); // ลบไฟล์ออกจากอาร์เรย์
                        updatePreview();
                    });

                    imgWrapper.appendChild(img);
                    imgWrapper.appendChild(btnRemove);
                    preview.appendChild(imgWrapper);
                }
                reader.readAsDataURL(file);
            });
        }
    </script>


    </body>
    </html>

<?php } ?>