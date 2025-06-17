<?php
include('includes/Header.php');
if (strlen($_SESSION['alogin']) == "" || strlen($_SESSION['department_id']) == "") {
    header("Location: index.php");
    exit();
} else {
    $curr_date = date("d-m-Y");
    ?>

    <!DOCTYPE html>
    <html lang="th">
    <head>
        <meta charset="UTF-8">
        <title>ฟอร์มขอเบิกพัสดุ</title>
        <!-- Bootstrap Datepicker CSS -->
        <link rel="stylesheet"
              href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.9.0/css/bootstrap-datepicker.min.css">

    </head>
    <body id="page-top">
    <div id="wrapper">
        <!--?php include('includes/Side-Bar.php'); ?-->

        <div id="content-wrapper" class="d-flex flex-column">
            <div id="content">
                <!--?php include('includes/Top-Bar.php'); ?-->

                <div class="container-fluid" id="container-wrapper">
                    <div class="d-sm-flex align-items-center justify-content-between mb-4">
                        <h1 class="h3 mb-0 text-gray-800"><span id="sub_menu"></span></h1>
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="<?php echo $_SESSION['dashboard_page'] ?>">Home</a>
                            </li>
                            <li class="breadcrumb-item"><span id="main_menu"></span></li>
                        </ol>
                    </div>

                    <div class="card shadow mb-4">
                        <div class="card-body">

                            <div class="row">
                                <div class="col-md-2">
                                    <div class="form-group">
                                        <label>เลขที่เอกสาร</label>
                                        <input type="text" id="doc_no" class="form-control" readonly="true">
                                    </div>
                                </div>

                                <div class="col-md-2">
                                    <div class="form-group">
                                        <label>วันที่</label>
                                        <i class="fa fa-calendar" aria-hidden="true"></i>
                                        <input type="text" class="form-control" id="doc_date" name="doc_date"
                                               required="required" value="<?php echo $curr_date ?>" readonly="true"
                                               placeholder="">
                                    </div>
                                </div>

                                <div class="col-md-8">
                                    <div class="form-group">
                                        <label>วัตถุประสงค์</label>
                                        <input type="text" id="purpose" class="form-control" required>
                                    </div>
                                </div>
                            </div>

                            <!-- บรรทัดใหม่สำหรับผู้ขอเบิก และผู้ขาย -->
                            <div class="row mt-2">
                                <div class="col-md-4 position-relative">
                                    <div class="form-group">
                                        <label for="requester" class="control-label">ผู้ขอเบิก</label>
                                        <input type="text" id="requester" name="requester" class="form-control"
                                               autocomplete="off" required>
                                        <input type="hidden" id="requester_id" name="requester_id">
                                        <div id="requester_list" class="list-group position-absolute"
                                             style="z-index: 1000;"></div>
                                    </div>
                                </div>

                                <div class="col-md-1 d-flex align-items-end">
                                    <div class="form-group">
                                        <label class="control-label" style="visibility:hidden;">เลือกผู้ขอเบิก</label>
                                        <a data-toggle="modal" href="#SearchRequesterModal" class="btn btn-primary">
                                            Click <i class="fa fa-search" aria-hidden="true"></i>
                                        </a>
                                    </div>
                                </div>


                                <input type="hidden" class="form-control" id="supplier_id" name="supplier_id">

                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="supplier_name" class="control-label">ชื่อผู้ขาย <b>(ถ้าไม่พบในระบบ สามารถเพิ่มชื่อโดยพิมพ์ในช่องนี้ได้)</b></label>
                                        <input type="text" class="form-control" id="supplier_name" name="supplier_name"
                                               autocomplete="off" required placeholder="">
                                        <input type="hidden" id="supplier_id" name="supplier_id">
                                        <div id="supplier_list" class="list-group position-absolute"
                                             style="z-index:1000;"></div>
                                    </div>
                                </div>

                                <div class="col-md-1 d-flex align-items-end">
                                    <div class="form-group">
                                        <label class="control-label" style="visibility:hidden;">เลือกชื่อผู้ขาย</label>
                                        <a data-toggle="modal" href="#SearchSupModal" class="btn btn-primary">
                                            Click <i class="fa fa-search" aria-hidden="true"></i>
                                        </a>
                                    </div>
                                </div>

                            </div>


                            <div class="d-flex align-items-center mt-4 mb-3">
                                <h5 class="text-primary mb-0 mr-2">รายการพัสดุ</h5>
                                <button class="btn btn-primary" id="addRow" type="button">+ เพิ่มรายการ</button>
                            </div>

                            <table class="table table-bordered" id="detailTable">
                                <thead class="thead-primary">
                                <tr>
                                    <th style="width: 15%;">รหัส</th>
                                    <th style="width: 20%;">ชื่อพัสดุ</th>
                                    <th style="width: 11%;">จำนวน</th>
                                    <th style="width: 12%;">ราคาต่อหน่วย</th>
                                    <th style="width: 15%;">รวมเงิน</th>
                                    <th style="width: 12%;">รหัสหน่วยนับ</th>
                                    <th style="width: 20%;">หน่วยนับ</th>
                                    <th>ลบ</th>
                                </tr>
                                </thead>
                                <tbody></tbody>
                                <tfoot>
                                <tr>
                                    <td colspan="4" class="text-right"><strong>รวมเงินทั้งหมด:</strong></td>
                                    <td><input type="text" class="form-control text-right" id="totalAmount" readonly>
                                    </td>
                                    <td colspan="3"></td>
                                </tr>
                                </tfoot>
                            </table>
                            <br>

                            <input type="file" id="pictures" multiple accept="image/*">
                            <input type="hidden" id="picture_doc" name="picture_doc">
                            <div id="preview-area" class="row mt-2"></div>
                            <div id="imagePreview" class="mt-2 d-flex flex-wrap"></div>

                            <div class="modal-footer">
                                <input type="hidden" name="id" id="id"/>
                                <input type="hidden" name="action" id="action" value=""/>

                                <!-- ปุ่ม Save พร้อมไอคอนด้านขวา -->
                                <button type="submit" name="save" id="save" class="btn btn-primary">
                                    บันทึก <i class="fa fa-save"></i>
                                </button>

                                <!-- ปุ่ม Close พร้อมไอคอนด้านขวา -->
                                <button type="button" class="btn btn-danger" onclick="closeAndReload()">
                                    ปิด <i class="fa fa-window-close"></i>
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Modal เลือกสินค้า -->
                    <div class="modal fade" id="itemModal" tabindex="-1" role="dialog" aria-labelledby="itemModalLabel"
                         aria-hidden="true">
                        <div class="modal-dialog modal-lg" role="document">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title">เลือกรายการพัสดุ</h5>
                                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                        <span aria-hidden="true">&times;</span>
                                    </button>
                                </div>
                                <div class="modal-body">
                                    <table class="table table-bordered" id="productTable">
                                        <thead>
                                        <tr>
                                            <th>รหัส</th>
                                            <th>ชื่อพัสดุ</th>
                                            <th>เลือก</th>
                                        </tr>
                                        </thead>
                                        <tbody>
                                        <!-- JS จะโหลดข้อมูลจาก get_products.php -->
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Modal สำหรับเลือกหน่วย -->
                    <div class="modal fade" id="unitModal" tabindex="-1" role="dialog" aria-labelledby="unitModalLabel"
                         aria-hidden="true">
                        <div class="modal-dialog modal-lg" role="document">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title">เลือกหน่วยนับ</h5>
                                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                        <span aria-hidden="true">&times;</span>
                                    </button>
                                </div>
                                <div class="modal-body">
                                    <table class="table table-bordered" id="unitTable">
                                        <thead>
                                        <tr>
                                            <th>รหัสหน่วย</th>
                                            <th>ชื่อหน่วย</th>
                                            <th>เลือก</th>
                                        </tr>
                                        </thead>
                                        <tbody>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="modal fade" id="SearchSupModal">
                        <div class="modal-dialog modal-lg">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h4 class="modal-title">เลือกผู้ขาย</h4>
                                    <button type="button" class="close" data-dismiss="modal"
                                            aria-hidden="true">×
                                    </button>
                                </div>

                                <div class="container"></div>
                                <div class="modal-body">

                                    <div class="modal-body">

                                        <table cellpadding="0" cellspacing="0" border="0"
                                               class="display"
                                               id="TableSupplierList"
                                               width="100%">
                                            <thead>
                                            <tr>
                                                <th>รหัสผู้ขาย</th>
                                                <th>ชื่อผู้ขาย</th>
                                                <th>Action</th>
                                            </tr>
                                            </thead>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="modal fade" id="SearchRequesterModal">
                        <div class="modal-dialog modal-lg">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h4 class="modal-title">เลือกผู้ขอเบิก</h4>
                                    <button type="button" class="close" data-dismiss="modal"
                                            aria-hidden="true">×
                                    </button>
                                </div>
                                <div class="container"></div>
                                <div class="modal-body">
                                    <div class="modal-body">
                                        <table id="TableRequesterList" class="display" width="100%">
                                            <thead>
                                            <tr>
                                                <th>รหัสผู้เบิก</th>
                                                <th>ชื่อผู้เบิก</th>
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

    <!-- Bootstrap 4 JS -->
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.bundle.min.js"></script>

    <script src="vendor/datatables/v11/bootbox.min.js"></script>
    <script src="vendor/datatables/v11/jquery.dataTables.min.js"></script>
    <link rel="stylesheet" href="vendor/datatables/v11/jquery.dataTables.min.css"/>
    <link rel="stylesheet" href="vendor/datatables/v11/buttons.dataTables.min.css"/>

    <!-- Bootstrap Datepicker JS -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.9.0/js/bootstrap-datepicker.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.9.0/locales/bootstrap-datepicker.th.min.js"></script>
    <!-- ใส่ใน <head> -->
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet"/>

    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script src="js/modal/show_supplier_modal.js"></script>
    <script src="js/modal/show_requester_modal.js"></script>

    <script>
        $(document).ready(function () {
            let queryString = {};
            if (window.location.search.includes('?')) {
                let params = window.location.search.split('?')[1].split('&');
                for (let i = 0; i < params.length; i++) {
                    let [key, value] = params[i].split('=');
                    queryString[key] = decodeURIComponent(value || '');
                }
            }

            $("#sub_menu").html(queryString["sub_menu"] || "");
            $("#main_menu").html(queryString["main_menu"] || "");

            // ✅ ต้องอยู่ภายใน $(document).ready)

            $('#action').val(queryString["action"]);

            if (queryString["action"] !== 'ADD' && queryString["doc_no"]) {
                $('#doc_no').val(queryString["doc_no"]);
                $('#doc_date').val(queryString["doc_date"]);
                $('#requester').val(queryString["requester"]);
                $('#supplier_id').val(queryString["supplier_id"]);
                $('#supplier_name').val(queryString["supplier_name"]);
                $('#purpose').val(queryString["purpose"]);
                $('#picture_doc').val(queryString["picture_doc"]);
                $('#totalAmount').val(queryString["total_amount"]);
                loadDetailData(queryString["doc_no"]);

                if (queryString["picture_doc"]) {
                    let filenames = queryString["picture_doc"].split(',');
                    let imagePreviewContainer = $('#imagePreview'); // สมมุติว่าคุณมี div นี้ไว้แสดงรูป
                    imagePreviewContainer.html(''); // ล้างก่อน
                    filenames.forEach(file => {
                        let img = $('<img>')
                            .attr('src', 'uploads/files/' + file.trim()) // แก้ path ตามจริง
                            .css({ width: '120px', margin: '5px', border: '1px solid #ccc' });
                        imagePreviewContainer.append(img);
                    });
                }

            }
        });
    </script>

    <script>
        function loadDetailData(docNo) {
            $.ajax({
                url: 'model/manage_purchase_process.php',
                method: 'GET',
                data: {
                    action: 'GET_DATA_DETAIL',
                    doc_no: docNo
                },
                dataType: 'json',
                success: function (response) {
                    if (Array.isArray(response)) {
                        $('#detailTable tbody').empty(); // ล้างก่อนโหลดใหม่
                        response.forEach(item => {
                            $('#detailTable tbody').append(`
<tr>
    <td style="width: 15%;">
        <div class="d-flex">
            <input type="text" class="form-control product_id" value="${item.product_id}" readonly style="flex: 1;">
            <a href="#itemModal" data-toggle="modal" class="btn btn-primary ml-2 btn-select-item" style="white-space: nowrap;">
                <i class="fa fa-search"></i>
            </a>
        </div>
    </td>
    <td style="width: 20%;"><input type="text" class="form-control product_name" value="${item.product_name}"></td>
    <td style="width: 11%;"><input type="number" class="form-control item-quantity" value="${item.quantity}" min="1"></td>
    <td style="width: 12%;"><input type="number" class="form-control item-price" value="${item.price}" min="0"></td>
    <td style="width: 15%;"><input type="number" class="form-control item-amount" value="${(item.quantity * item.price).toFixed(2)}" readonly></td>
    <td style="width: 12%;">
        <div class="d-flex">
            <input type="text" class="form-control item-unit-code" value="${item.unit_id}" readonly style="flex: 1;">
            <a href="#unitModal" data-toggle="modal" class="btn btn-primary ml-2 btn-select-unit" style="white-space: nowrap;">
                <i class="fa fa-search"></i>
            </a>
        </div>
    </td>
    <td style="width: 20%;"><input type="text" class="form-control item-unit-name" value="${item.unit_name}" readonly></td>
    <td><button class="btn btn-danger btn-sm remove-row" type="button">ลบ</button></td>
</tr>
                    `);
                        });
                    } else if (response.error) {
                        alertify.error("Error: " + response.error);
                    } else {
                        alertify.error("ข้อมูลว่างเปล่า");
                    }
                },
                error: function (xhr, status, error) {
                    console.error("AJAX Error:", status, error);
                    alertify.error("โหลดข้อมูลไม่สำเร็จ");
                }
            });
        }


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
        $(document).ready(function () {
            $('#addRow').on('click', function () {
                $('#detailTable tbody').append(`
<tr>
  <td style="width: 15%;">
    <div class="d-flex">
      <input type="text" class="form-control product_id" readonly style="flex: 1;">
      <a href="#itemModal" data-toggle="modal" class="btn btn-primary ml-2 btn-select-item" style="white-space: nowrap;">
        <i class="fa fa-search"></i>
      </a>
    </div>
  </td>
  <td style="width: 20%;"><input type="text" class="form-control product_name" style="width: 100%;"></td>
  <td style="width: 11%;"><input type="number" class="form-control item-quantity" min="1" required style="width: 100%;"></td>
  <td style="width: 12%;"><input type="number" class="form-control item-price" min="0" required style="width: 100%;"></td>
  <td style="width: 15%;"><input type="number" class="form-control item-amount" min="0" required style="width: 100%;" readonly></td>
  <td style="width: 12%;">
    <div class="d-flex">
      <input type="text" class="form-control item-unit-code" readonly style="flex: 1;">
      <a href="#unitModal" data-toggle="modal" class="btn btn-primary ml-2 btn-select-unit" style="white-space: nowrap;">
        <i class="fa fa-search"></i>
      </a>
    </div>
  </td>
  <td style="width: 20%;"><input type="text" class="form-control item-unit-name" readonly style="width: 100%;"></td>
  <td style="width: auto;"><button class="btn btn-danger btn-sm remove-row" type="button">ลบ</button></td>
</tr>
        `);
            });

            $(document).on('click', '.remove-row', function () {
                $(this).closest('tr').remove();
            });
        });
    </script>

    <script>
        $(document).ready(function () {
            let currentRow = null;

            $(document).on('click', '.btn-select-item', function () {
                currentRow = $(this).closest('tr');
                loadProductTable();
                $('#itemModal').modal('show');
            });

            $(document).on('click', '.select-this', function () {
                const code = $(this).data('code');
                const name = $(this).data('name');

                if (currentRow) {
                    currentRow.find('.product_id').val(code);
                    currentRow.find('.product_name').val(name);
                }

                $('#itemModal').modal('hide');
            });

            function loadProductTable() {
                $.ajax({
                    url: 'model/get_products.php',
                    method: 'GET',
                    dataType: 'json',
                    success: function (data) {
                        let html = '';
                        data.forEach(item => {
                            html += `
<tr>
    <td>${item.product_id}</td>
    <td>${item.product_name}</td>
    <td>
        <button class="btn btn-sm btn-primary select-this"
                data-code="${item.product_id}"
                data-name="${item.product_name}">เลือก</button>
    </td>
</tr>`;
                        });
                        $('#productTable tbody').html(html);
                    },
                    error: function () {
                        alert('โหลดข้อมูลสินค้าไม่สำเร็จ');
                    }
                });
            }
        });
    </script>


    <script>
        $(document).ready(function () {
            let currentRow = null;

            // เมื่อคลิกปุ่มเลือกหน่วยนับ
            $(document).on('click', '.btn-select-unit', function () {
                currentRow = $(this).closest('tr');
                loadUnitTable(); // โหลดข้อมูลหน่วยนับ
                $('#unitModal').modal('show');
            });

            // เมื่อเลือกหน่วยนับจาก modal
            $(document).on('click', '.select-this-unit', function () {
                const code = $(this).data('code');
                const name = $(this).data('name');

                if (currentRow) {
                    currentRow.find('.item-unit-code').val(code);
                    currentRow.find('.item-unit-name').val(name);
                }

                $('#unitModal').modal('hide');
            });

            // โหลดข้อมูลจากฐานข้อมูล ims_unit
            function loadUnitTable() {
                $.ajax({
                    url: 'model/get_unit.php',
                    method: 'GET',
                    dataType: 'json',
                    success: function (data) {
                        let html = '';
                        data.forEach(unit => {
                            html += `
<tr>
    <td>${unit.unit_id}</td>
    <td>${unit.unit_name}</td>
    <td>
        <button class="btn btn-sm btn-primary select-this-unit"
                data-code="${unit.unit_id}"
                data-name="${unit.unit_name}">เลือก</button>
    </td>
</tr>`;
                        });
                        $('#unitTable tbody').html(html);
                    },
                    error: function () {
                        alert('โหลดข้อมูลหน่วยนับไม่สำเร็จ');
                    }
                });
            }
        });
    </script>

    <script>
        $(document).ready(function () {
            // ฟังก์ชันคำนวณรวมเงิน
            function calculateAmount(row) {
                const quantity = parseFloat(row.find('.item-quantity').val()) || 0;
                const price = parseFloat(row.find('.item-price').val()) || 0;
                const amount = quantity * price;
                row.find('.item-amount').val(amount.toFixed(2));
            }

            // ตรวจจับเมื่อกรอกจำนวนหรือราคาต่อหน่วย
            $(document).on('input', '.item-quantity, .item-price', function () {
                const row = $(this).closest('tr');
                calculateAmount(row);
            });

            // หากมีการเพิ่มแถวใหม่ ก็จะใช้ listener เดิมได้เพราะใช้ .on()
        });

        function calculateTotalAmount() {
            let total = 0;
            $('#detailTable tbody tr').each(function () {
                const amount = parseFloat($(this).find('.item-amount').val()) || 0;
                total += amount;
            });
            $('#totalAmount').val(total.toFixed(2));
        }

        $(document).on('click', '.remove-row', function () {
            $(this).closest('tr').remove();
            calculateTotalAmount();
        });

        $(document).on('input', '.item-quantity, .item-price', function () {
            const row = $(this).closest('tr');
            const quantity = parseFloat(row.find('.item-quantity').val()) || 0;
            const price = parseFloat(row.find('.item-price').val()) || 0;
            const amount = quantity * price;
            row.find('.item-amount').val(amount.toFixed(2));

            calculateTotalAmount();
        });


    </script>

    <script>
        function closeAndReload() {
            if (window.opener && !window.opener.closed) {
                window.opener.$('#TableRecordList').DataTable().ajax.reload(null, false);
            }
            window.close();
        }
    </script>

    <script>
        $(document).ready(function () {
            $('#save').on('click', async function (e) {
                e.preventDefault();

                // ตรวจสอบข้อมูลหลัก
                if (!$('#doc_date').val() || !$('#requester').val()) {
                    alertify.error('กรุณากรอกข้อมูลหลักให้ครบถ้วน');
                    return;
                }

                // ตรวจสอบรายละเอียด
                const details = [];
                let valid = true;

                $('#detailTable tbody tr').each(function () {
                    const product_id = $(this).find('.product_id').val();
                    const product_name = $(this).find('.product_name').val();
                    const quantity = parseFloat($(this).find('.item-quantity').val());
                    const price = parseFloat($(this).find('.item-price').val());
                    const unit_id = $(this).find('.item-unit-code').val();
                    const unit_name = $(this).find('.item-unit-name').val();

                    if (!product_id || !product_name || isNaN(quantity) || isNaN(price) || !unit_id || quantity <= 0 || price < 0) {
                        valid = false;
                        alertify.error("กรุณากรอกข้อมูลให้ครบและถูกต้องในทุกรายการ");
                        return false;
                    }

                    details.push({product_id, product_name, quantity, price, unit_id, unit_name});
                });

                if (!valid) return;

                $('#save').prop('disabled', true);

                try {
                    const uploadedFilenames = await uploadImages();  // <-- upload แล้วรอผลลัพธ์
                    const picture_doc = uploadedFilenames.join(',');

                    const payload = {
                        action: $('#action').val(),
                        doc_no: $('#doc_no').val(),
                        date: $('#doc_date').val(),
                        requester: $('#requester').val(),
                        supplier_id: $('#supplier_id').val(),
                        supplier_name: $('#supplier_name').val(),
                        purpose: $('#purpose').val(),
                        picture_doc: picture_doc,
                        details: details
                    };

                    $.ajax({
                        url: 'model/manage_purchase_data_detail_process.php',
                        method: 'POST',
                        contentType: 'application/json',
                        data: JSON.stringify(payload),
                        success: function (res) {
                            if (res.status === 'success') {
                                alertify.success('บันทึกสำเร็จแล้ว');
                                location.reload();
                            } else {
                                alert('เกิดข้อผิดพลาด: ' + res.message);
                                $('#save').prop('disabled', false);
                            }
                        },
                        error: function () {
                            alertify.error('ไม่สามารถเชื่อมต่อเซิร์ฟเวอร์ได้');
                            $('#save').prop('disabled', false);
                        }
                    });
                } catch (err) {
                    alertify.error('อัปโหลดรูปภาพไม่สำเร็จ');
                    $('#save').prop('disabled', false);
                }
            });
        });
    </script>


    <script>
        $(document).ready(function () {
            $('#supplier_name').on('keyup', function () {
                let query = $(this).val();

                if (query.length >= 2) {
                    $.ajax({
                        url: 'model/get_suppliers.php',
                        method: 'POST',
                        data: {query: query},
                        success: function (data) {
                            $('#supplier_list').fadeIn().html(data);
                        }
                    });
                } else {
                    $('#supplier_list').fadeOut();
                }
            });

            // เมื่อคลิกรายการ
            $(document).on('click', '.supplier-item', function () {
                let name = $(this).data('name');
                let id = $(this).data('id');

                $('#supplier_name').val(name);
                $('#supplier_id').val(id);
                $('#supplier_list').fadeOut();
            });

            // คลิกนอก list ให้หาย
            $(document).on('click', function (e) {
                if (!$(e.target).closest('#supplier_name, #supplier_list').length) {
                    $('#supplier_list').fadeOut();
                }
            });
        });
    </script>

    <script>
        $(document).ready(function () {
            $('#requester').on('keyup', function () {
                let query = $(this).val();

                if (query.length >= 2) {
                    $.ajax({
                        url: 'model/get_requester.php',
                        method: 'POST',
                        data: {query: query},
                        success: function (data) {
                            $('#requester_list').fadeIn().html(data);
                        }
                    });
                } else {
                    $('#requester_list').fadeOut();
                }
            });

            $(document).on('click', '.requester-item', function () {
                const name = $(this).data('name');
                const id = $(this).data('id');
                $('#requester').val(name);
                $('#requester_id').val(id);
                $('#requester_list').fadeOut();
            });

            $(document).on('click', function (e) {
                if (!$(e.target).closest('#requester, #requester_list').length) {
                    $('#requester_list').fadeOut();
                }
            });
        });
    </script>


    <script>
        let uploadedImages = [];  // สำหรับ preview
        let uploadedFileNames = []; // สำหรับเก็บชื่อไฟล์ที่ upload แล้วบน server

        document.getElementById('pictures').addEventListener('change', function (e) {
            const files = Array.from(e.target.files);
            const previewArea = document.getElementById('preview-area');

            files.forEach((file, index) => {
                const reader = new FileReader();
                reader.onload = function (e) {
                    const imgDiv = document.createElement('div');
                    imgDiv.classList.add('col-md-2', 'position-relative');

                    imgDiv.innerHTML = `
                    <img src="${e.target.result}" class="img-thumbnail mb-2" style="width:100%; height:120px; object-fit:cover;">
                    <button type="button" class="btn btn-sm btn-danger position-absolute top-0 end-0 remove-img" data-index="${uploadedImages.length}">&times;</button>
                `;
                    previewArea.appendChild(imgDiv);
                    uploadedImages.push(file);
                };
                reader.readAsDataURL(file);
            });
        });

        // ลบรูปออกจาก preview
        document.getElementById('preview-area').addEventListener('click', function (e) {
            if (e.target.classList.contains('remove-img')) {
                const index = parseInt(e.target.getAttribute('data-index'));
                uploadedImages.splice(index, 1);
                uploadedFileNames.splice(index, 1);
                e.target.parentElement.remove();
            }
        });

        // ฟังก์ชัน upload รูปภาพทั้งหมดก่อน submit
        async function uploadImages() {
            const formData = new FormData();
            uploadedImages.forEach(file => formData.append('images[]', file));

            const response = await fetch('upload_img_doc.php', {
                method: 'POST',
                body: formData
            });
            const result = await response.json();
            return result.filenames;  // ควรเป็น array ของชื่อไฟล์ที่ upload สำเร็จ
        }
    </script>


    </body>
    </html>

    <?php
} // end else session check
?>
