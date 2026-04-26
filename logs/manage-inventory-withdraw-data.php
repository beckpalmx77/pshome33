<?php
include('includes/Header.php');
if (strlen($_SESSION['alogin']) == "" || strlen($_SESSION['department_id']) == "") {
    header("Location: index.php");
    exit();
} else {
    $curr_date = date("d-m-Y");
    $transaction_type = "-";
    ?>

    <!DOCTYPE html>
    <html lang="th">
    <head>
        <meta charset="UTF-8">
        <title>เบิกพัสดุ/ครุภัณฑ์/สินค้า/</title>
        <link rel="stylesheet"
              href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.9.0/css/bootstrap-datepicker.min.css">

    </head>
    <body id="page-top">
    <div id="wrapper">
        <div id="content-wrapper" class="d-flex flex-column">
            <div id="content">
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

                                <input type="hidden" id="transaction_type" class="form-control"
                                       value="<?php echo $transaction_type; ?>">
                                <input type="hidden" id="purpose" class="form-control"
                                       value="<?php echo $transaction_type; ?>">
                                <input type="hidden" id="supplier_id" name="supplier_id"
                                       value="<?php echo $transaction_type; ?>">
                                <input type="hidden" id="supplier_name" name="supplier_name"
                                       value="<?php echo $transaction_type; ?>">

                                <!--div class="col-md-6">
                                    <div class="form-group">
                                        <label for="supplier_name" class="control-label">ชื่อผู้ขาย <b>(ถ้าไม่พบในระบบ
                                                สามารถเพิ่มชื่อโดยพิมพ์ในช่องนี้ได้)</b></label>
                                        <input type="text" class="form-control" id="supplier_name" name="supplier_name"
                                               autocomplete="off" placeholder="">
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
                                </div-->


                                <div class="col-md-6">
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
                                    <th style="width: 12%; display: none;">ราคาต่อหน่วย</th>
                                    <th style="width: 15%; display: none;">รวมเงิน</th>
                                    <th style="width: 12%;">รหัสหน่วยนับ</th>
                                    <th style="width: 20%;">หน่วยนับ</th>
                                    <th>ลบ</th>
                                </tr>
                                </thead>
                                <tbody></tbody>
                                <tfoot>
                                <tr style="display: none;">
                                    <td colspan="3" class="text-right"><strong>รวมเงินทั้งหมด:</strong></td>
                                    <td style="display: none;"></td>
                                    <td><input type="text" class="form-control text-right" id="totalAmount" readonly>
                                    </td>
                                    <td colspan="3"></td>
                                </tr>
                                </tfoot>
                            </table>
                            <br>

                            <input type="file" id="pictures" multiple accept="image/*">
                            <input type="hidden" id="picture_doc" name="picture_doc">
                            <input type="hidden" id="deleted_images" name="deleted_images" value="">
                            <div id="preview-area" class="row mt-2"></div>
                            <div id="imagePreview" class="mt-2 d-flex flex-wrap"></div>

                            <div class="modal-footer">
                                <input type="hidden" name="id" id="id"/>
                                <input type="hidden" name="action" id="action" value=""/>

                                <button type="submit" name="save" id="save" class="btn btn-primary">
                                    บันทึก <i class="fa fa-save"></i>
                                </button>

                                <button type="button" class="btn btn-danger" onclick="closeAndReload()">
                                    ปิด <i class="fa fa-times"></i>
                                </button>
                            </div>
                        </div>
                    </div>

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
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>

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

    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.bundle.min.js"></script>

    <script src="vendor/datatables/v11/bootbox.min.js"></script>
    <script src="vendor/datatables/v11/jquery.dataTables.min.js"></script>
    <link rel="stylesheet" href="vendor/datatables/v11/jquery.dataTables.min.css"/>
    <link rel="stylesheet" href="vendor/datatables/v11/buttons.dataTables.min.css"/>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.9.0/js/bootstrap-datepicker.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.9.0/locales/bootstrap-datepicker.th.min.js"></script>
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

            $('#action').val(queryString["action"]);

            if (queryString["action"] !== 'ADD' && queryString["doc_no"]) {
                $('#doc_no').val(queryString["doc_no"]);
                $('#doc_date').val(queryString["doc_date"]);
                $('#requester').val(queryString["requester"]);
                $('#supplier_id').val(queryString["supplier_id"]);
                $('#supplier_name').val(queryString["supplier_name"]);
                $('#purpose').val(queryString["purpose"]);
                $('#transaction_type').val(queryString["transaction_type"]);
                $('#picture_doc').val(queryString["picture_doc"]);
                $('#totalAmount').val(queryString["total_amount"]);
                loadDetailData(queryString["doc_no"]);

                if (queryString["picture_doc"]) {
                    let filenames = queryString["picture_doc"].split(',');
                    let imagePreviewContainer = $('#imagePreview');
                    imagePreviewContainer.html('');

                    filenames.forEach(file => {
                        file = file.trim();
                        if (!file) return;

                        let imageBox = $('<div>').addClass('position-relative m-2').css({display: 'inline-block'});

                        let img = $('<img>')
                            .attr('src', 'uploads/inventory/' + file)
                            .css({width: '120px', height: 'auto', border: '1px solid #ccc', padding: '2px'});

                        let deleteBtn = $('<button>')
                            .addClass('btn btn-danger btn-sm position-absolute top-0 end-0')
                            .html('&times;')
                            .css({zIndex: 2, padding: '2px 6px', borderRadius: '50%'})
                            .on('click', function () {
                                // Remove image from display
                                imageBox.remove();

                                // Add filename to deleted_images hidden field
                                let deleted = $('#deleted_images').val();
                                let deletedArray = deleted ? deleted.split(',') : [];
                                if (!deletedArray.includes(file)) {
                                    deletedArray.push(file);
                                    $('#deleted_images').val(deletedArray.join(','));
                                }
                            });

                        imageBox.append(img).append(deleteBtn);
                        imagePreviewContainer.append(imageBox);
                    });
                }
            }
        });
    </script>

    <script>
        function loadDetailData(docNo) {
            $.ajax({
                url: 'model/manage_inventory_process.php',
                method: 'GET',
                data: {
                    action: 'GET_DATA_DETAIL',
                    doc_no: docNo
                },
                dataType: 'json',
                success: function (response) {
                    if (Array.isArray(response)) {
                        $('#detailTable tbody').empty(); // Clear before loading new data
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
    <td style="width: 12%; display: none;"><input type="number" class="form-control item-price" value="0" min="0"></td>
    <td style="width: 15%; display: none;"><input type="number" class="form-control item-amount" value="${(item.quantity * item.price).toFixed(2)}" readonly></td>
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
                        calculateTotalAmount(); // Recalculate total after loading details
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
  <td style="width: 12%; display: none;"><input type="number" class="form-control item-price" min="0" required style="width: 100%;"></td>
  <td style="width: 15%; display: none;"><input type="number" class="form-control item-amount" min="0" required style="width: 100%;" readonly></td>
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
                calculateTotalAmount();
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
            // Function to calculate individual item amount
            function calculateAmount(row) {
                const quantity = parseFloat(row.find('.item-quantity').val()) || 0;
                const price = parseFloat(row.find('.item-price').val()) || 0;
                const amount = quantity * price;
                row.find('.item-amount').val(amount.toFixed(2));
            }

            // Detect input changes for quantity or price
            $(document).on('input', '.item-quantity, .item-price', function () {
                const row = $(this).closest('tr');
                calculateAmount(row);
                calculateTotalAmount(); // Recalculate total amount for the whole table
            });

            // Initial calculation if any items are pre-loaded
            calculateTotalAmount();
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
            // Ensure the click event is attached only once
            $('#save').off('click').on('click', async function (e) {
                e.preventDefault();

                // Check main data
                if (!$('#doc_date').val() || !$('#requester').val()) {
                    alertify.error('กรุณากรอกข้อมูลหลักให้ครบถ้วน');
                    return;
                }

                // Check detail data
                const details = [];
                let valid = true;

                $('#detailTable tbody tr').each(function () {
                    const product_id = $(this).find('.product_id').val();
                    const product_name = $(this).find('.product_name').val();
                    const quantity = parseFloat($(this).find('.item-quantity').val());
                    const price = parseFloat($(this).find('.item-price').val());
                    const unit_id = $(this).find('.item-unit-code').val();
                    const unit_name = $(this).find('.item-unit-name').val();

                    if (!product_id || !product_name || isNaN(quantity) || !unit_id || quantity <= 0) {
                        valid = false;
                        alertify.error("กรุณากรอกข้อมูลให้ครบและถูกต้องในทุกรายการ");
                        return false;
                    }

                    details.push({product_id, product_name, quantity, price, unit_id, unit_name});
                });

                if (!valid) return;

                $('#save').prop('disabled', true);

                try {
                    const newlyUploadedFilenames = await uploadImages(); // Filenames of newly uploaded images

                    // Get existing image filenames from the hidden input field (picture_doc)
                    let existingPictureDoc = $('#picture_doc').val();
                    let existingFilenames = existingPictureDoc ? existingPictureDoc.split(',').map(name => name.trim()).filter(name => name) : [];

                    // Get filenames of images marked for deletion
                    let deletedFilenames = $('#deleted_images').val();
                    let deletedArray = deletedFilenames ? deletedFilenames.split(',').map(name => name.trim()).filter(name => name) : [];

                    // Filter out deleted images from the existing ones
                    let currentFilenames = existingFilenames.filter(filename => !deletedArray.includes(filename));

                    // Combine current (non-deleted existing) filenames with newly uploaded filenames
                    const finalPictureDoc = currentFilenames.concat(newlyUploadedFilenames).join(',');

                    const payload = {
                        action: $('#action').val(),
                        doc_no: $('#doc_no').val(),
                        date: $('#doc_date').val(),
                        requester: $('#requester').val(),
                        supplier_id: $('#supplier_id').val(),
                        supplier_name: $('#supplier_name').val(),
                        purpose: $('#purpose').val(),
                        transaction_type: $('#transaction_type').val(),
                        picture_doc: finalPictureDoc, // Use the combined string here
                        details: details
                    };

                    $.ajax({
                        url: 'model/manage_inventory_data_detail_process.php',
                        method: 'POST',
                        contentType: 'application/json',
                        data: JSON.stringify(payload),
                        success: function (res) {
                            if (res.status === 'success') {
                                alertify.success('บันทึกสำเร็จแล้ว'); // This should now only trigger once per successful save
                                setTimeout(function () {
                                    closeAndReload(); // Close the window and reload parent table
                                }, 1000); // 1000 milliseconds = 1 seconds
                            } else {
                                alert('เกิดข้อผิดพลาด: ' + res.message);
                                $('#save').prop('disabled', false);
                            }
                        },
                        error: function (xhr, status, error) {
                            console.error("AJAX Error:", status, error);
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

            // When an item is clicked
            $(document).on('click', '.supplier-item', function () {
                let name = $(this).data('name');
                let id = $(this).data('id');

                $('#supplier_name').val(name);
                $('#supplier_id').val(id);
                $('#supplier_list').fadeOut();
            });

            // Click outside the list to hide it
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
        let uploadedImages = [];  // For preview of newly selected files

        document.getElementById('pictures').addEventListener('change', function (e) {
            const files = Array.from(e.target.files);
            const previewArea = document.getElementById('preview-area');

            files.forEach((file) => {
                const reader = new FileReader();
                reader.onload = function (e) {
                    const imgDiv = document.createElement('div');
                    imgDiv.classList.add('col-md-2', 'position-relative');

                    const fileIndex = uploadedImages.push(file) - 1;

                    imgDiv.innerHTML = `
                    <img src="${e.target.result}" class="img-thumbnail mb-2" style="width:100%; height:120px; object-fit:cover;">
                    <button type="button" class="btn btn-sm btn-danger position-absolute top-0 end-0 remove-new-img" data-file-index="${fileIndex}">&times;</button>
                `;
                    previewArea.appendChild(imgDiv);
                };
                reader.readAsDataURL(file);
            });
        });

        document.getElementById('preview-area').addEventListener('click', function (e) {
            if (e.target.classList.contains('remove-new-img')) {
                const fileIndex = parseInt(e.target.getAttribute('data-file-index'));
                uploadedImages.splice(fileIndex, 1);
                e.target.parentElement.remove();

                $('#preview-area .remove-new-img').each(function (i) {
                    $(this).attr('data-file-index', i);
                });
            }
        });

        async function uploadImages() {
            const formData = new FormData();
            uploadedImages.forEach(file => formData.append('images[]', file));

            if (uploadedImages.length === 0) {
                console.log('No new images to upload.');
                return Promise.resolve([]);
            }

            console.log('Attempting to upload images...');
            try {
                const response = await fetch('upload_img_doc.php', {
                    method: 'POST',
                    body: formData
                });

                console.log('Response from upload_img_doc.php (raw):', response);

                if (!response.ok) {
                    const errorText = await response.text();
                    console.error('HTTP error from upload_img_doc.php:', response.status, errorText);
                    throw new Error(`HTTP error! status: ${response.status}, message: ${errorText}`);
                }

                const result = await response.json();
                console.log('Response from upload_img_doc.php (JSON):', result);

                if (result.status === 'success') {
                    return result.filenames;
                } else {
                    throw new Error(result.message || 'Image upload failed on server.');
                }
            } catch (err) {
                console.error('Error during image upload:', err);
                throw err; // Re-throw the error to be caught by the calling function
            }
        }

    </script>

    <!--script>
        $(document).ready(function () {
            $('#save').on('click', async function (e) {
                e.preventDefault();

                if (!$('#doc_date').val() || !$('#requester').val()) {
                    alertify.error('กรุณากรอกข้อมูลหลักให้ครบถ้วน');
                    return;
                }

                const details = [];
                let valid = true;

                $('#detailTable tbody tr').each(function () {
                    const product_id = $(this).find('.product_id').val();
                    const product_name = $(this).find('.product_name').val();
                    const quantity = parseFloat($(this).find('.item-quantity').val());
                    const price = 0;
                    const unit_id = $(this).find('.item-unit-code').val();
                    const unit_name = $(this).find('.item-unit-name').val();

                    if (!product_id || !product_name || isNaN(quantity) || !unit_id || quantity <= 0) {
                        valid = false;
                        alertify.error("กรุณากรอกข้อมูลให้ครบและถูกต้องในทุกรายการ");
                        return false;
                    }

                    details.push({product_id, product_name, quantity, price, unit_id, unit_name});
                });

                if (!valid) return;

                $('#save').prop('disabled', true);

                try {
                    const newlyUploadedFilenames = await uploadImages();
                    console.log('Newly uploaded filenames:', newlyUploadedFilenames);

                    let existingPictureDoc = $('#picture_doc').val();
                    let existingFilenames = existingPictureDoc ? existingPictureDoc.split(',').map(name => name.trim()).filter(name => name) : [];
                    console.log('Existing picture_doc from hidden field:', existingPictureDoc);
                    console.log('Parsed existing filenames:', existingFilenames);

                    let deletedFilenames = $('#deleted_images').val();
                    let deletedArray = deletedFilenames ? deletedFilenames.split(',').map(name => name.trim()).filter(name => name) : [];
                    console.log('Deleted filenames:', deletedArray);

                    let currentFilenames = existingFilenames.filter(filename => !deletedArray.includes(filename));
                    console.log('Current (non-deleted existing) filenames:', currentFilenames);

                    const finalPictureDoc = currentFilenames.concat(newlyUploadedFilenames).join(',');
                    console.log('Final picture_doc string to be saved:', finalPictureDoc);


                    const payload = {
                        action: $('#action').val(),
                        doc_no: $('#doc_no').val(),
                        date: $('#doc_date').val(),
                        requester: $('#requester').val(),
                        supplier_id: $('#supplier_id').val(),
                        supplier_name: $('#supplier_name').val(),
                        purpose: $('#purpose').val(),
                        picture_doc: finalPictureDoc,
                        details: details
                    };

                    console.log('Payload sent to manage_inventory_data_detail_process.php:', payload);

                    $.ajax({
                        url: 'model/manage_inventory_data_detail_process.php',
                        method: 'POST',
                        contentType: 'application/json',
                        data: JSON.stringify(payload),
                        success: function (res) {
                            console.log('Response from manage_inventory_data_detail_process.php:', res);
                            if (res.status === 'success') {
                                alertify.success('บันทึกสำเร็จแล้ว');
                                setTimeout(function () {
                                    closeAndReload();
                                }, 1000);
                            } else {
                                alert('เกิดข้อผิดพลาด: ' + res.message);
                                $('#save').prop('disabled', false);
                            }
                        },
                        error: function (xhr, status, error) {
                            console.error("AJAX Error for manage_inventory_data_detail_process.php:", status, error, xhr.responseText);
                            alertify.error('ไม่สามารถเชื่อมต่อเซิร์ฟเวอร์ได้');
                            $('#save').prop('disabled', false);
                        }
                    });
                } catch (err) {
                    alertify.error('อัปโหลดรูปภาพไม่สำเร็จ: ' + err.message);
                    $('#save').prop('disabled', false);
                }
            });
        });
    </script-->


    </body>
    </html>

    <?php
} // end else session check
?>