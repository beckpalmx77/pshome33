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
        <link rel="icon" href="img/favicon.ico" type="image/x-icon">
        <link href="../img/logo/logo.png" rel="icon">
        <title>PS33 Home System</title>
        <link rel="stylesheet"
              href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.9.0/css/bootstrap-datepicker.min.css">

    <style>
        .preview-box {
            width: 120px;
            height: 120px;
            margin: 10px;
            position: relative;
            border: 1px solid #ddd;
            border-radius: 4px;
            overflow: hidden;
            background-color: #f8f9fc;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: transform 0.2s;
        }
        .preview-box:hover {
            transform: scale(1.05);
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
        }
        .preview-box img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        .preview-box .pdf-icon {
            font-size: 50px;
            color: #e74a3b;
        }
        .preview-box .file-name {
            font-size: 9px;
            word-break: break-all;
            padding: 5px;
            text-align: center;
            background: rgba(255,255,255,0.8);
            width: 100%;
            position: absolute;
            bottom: 0;
            color: #333;
        }
        .remove-btn {
            position: absolute;
            top: 2px;
            right: 2px;
            z-index: 10;
            background: rgba(231, 74, 59, 0.9);
            color: white;
            border: none;
            border-radius: 50%;
            width: 22px;
            height: 22px;
            cursor: pointer;
            font-size: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
        }
    </style>
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
                                        <label>เลขที่เอกสาร (สร้างอัตโนมัติ)</label>
                                        <input type="text" id="doc_no" class="form-control" readonly>
                                    </div>
                                </div>

                                <div class="col-md-2">
                                    <div class="form-group">
                                        <label>วันที่ (Click เลือกวันที่)</label>
                                        <i class="fa fa-calendar" aria-hidden="true"></i>
                                        <input type="text" class="form-control" id="doc_date" name="doc_date"
                                               value="<?php echo $curr_date ?>" readonly required>
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="payment_method" class="form-label fw-semibold mb-2">
                                            วิธีการชำระเงิน
                                        </label>
                                        <div class="d-flex align-items-center flex-wrap">

                                            <div class="form-check form-check-inline d-flex align-items-center me-2">
                                                <input class="form-check-input me-1" type="radio"
                                                       name="payment_method_radio"
                                                       id="method_transfer" value="โอนเงิน">
                                                <label class="form-check-label" for="method_transfer">💳 โอนเงิน   หมายเลขบัญชีฯ</label>
                                            </div>

                                            <input type="text" class="form-control ms-2 me-2" name="bank_no"
                                                   id="bank_no"
                                                   placeholder="" style="width: 200px;">&nbsp;

                                            <div class="form-check form-check-inline d-flex align-items-center">
                                                <input class="form-check-input me-1" type="radio"
                                                       name="payment_method_radio"
                                                       id="method_cash" value="เงินสด" checked>
                                                <label class="form-check-label" for="method_cash">💵 เงินสด</label>
                                            </div>

                                            <input type="text" class="form-control ms-2 me-2" name="payment_method"
                                                    id="payment_method"
                                                    value="เงินสด"
                                                    placeholder="" style="width: 200px;">
                                         </div>
                                     </div>
                                 </div>
                                 <div class="col-md-2">
                                     <div class="form-group" style="padding-top: 35px;">
                                         <div class="custom-control custom-checkbox">
                                             <input type="checkbox" class="custom-control-input" id="petty_cash_status" name="petty_cash_status" value="Y">
                                             <label class="custom-control-label" for="petty_cash_status" style="cursor: pointer;">ใช้เงินสดย่อย</label>
                                         </div>
                                     </div>
                                 </div>

                            </div>

                            <input type="hidden" id="requester" name="requester" class="form-control" value="-">
                            <input type="hidden" id="requester_id" name="requester_id" value="-">
                            <input type="hidden" id="supplier_id" name="supplier_id">

                            <div class="row align-items-end gx-3 gy-2">
                                <div class="col-md-4 position-relative">
                                    <label for="supplier_name" class="control-label">
                                        จ่ายให้แก่ <b>(ถ้าไม่พบในระบบ สามารถเพิ่มชื่อโดยพิมพ์ในช่องนี้ได้)</b>
                                    </label>
                                    <input type="text" class="form-control" id="supplier_name" name="supplier_name"
                                           autocomplete="off" required placeholder="">
                                    <div id="supplier_list" class="list-group position-absolute"
                                         style="z-index: 1000;"></div>
                                </div>

                                <!--div class="col-md-1 d-flex flex-column justify-content-end">
                                    <label class="control-label" style="visibility: hidden;">เลือก</label>
                                    <a data-toggle="modal" href="#SearchSupModal" class="btn btn-primary w-100">
                                        Click <i class="fa fa-search" aria-hidden="true"></i>
                                    </a>
                                </div-->
                                <div class="col-md-7">
                                    <label for="address" class="control-label">ที่อยู่</label>
                                    <input type="text" class="form-control" name="address" id="address" placeholder="">
                                </div>
                                 <div class="col-md-7">
                                     <label for="purpose" class="control-label">จ่ายเพื่อ <span class="text-danger">*</span></label>
                                     <input type="text" class="form-control" name="purpose" id="purpose" placeholder="" required>
                                 </div>
                            </div>

                            <div class="d-flex align-items-center mt-4 mb-3">
                                <h5 class="text-primary mb-0 mr-2">รายการจ่าย</h5>
                                <button class="btn btn-primary" id="addRow" type="button">+ เพิ่มรายการ</button>
                            </div>

                            <table class="table table-bordered" id="detailTable">
                                <thead class="thead-primary">
                                <tr>
                                    <th style="width: 25%;">รายการจ่าย (พิมพ์ชื่อ ถ้าไม่พบรายการ)</th>
                                    <th style="width: 10%;">จำนวน</th>
                                    <th style="width: 10%;">ใบเสร็จ/inv</th>
                                    <th style="width: 10%;">ราคาต่อหน่วย</th>
                                    <th style="width: 15%;">รวมเงิน</th>
                                    <th style="width: 15%;">หน่วยนับ</th>
                                    <th style="width: 10%;">หมายเหตุ</th>
                                    <th style="width: 5%;">ลบ</th>
                                </tr>
                                </thead>
                                <tbody></tbody>
                                <tfoot>
                                <tr>
                                    <td colspan="3" class="text-right"><strong>รวมเงินทั้งหมด:</strong></td>
                                    <td><input type="text" class="form-control text-right" id="totalAmount" readonly>
                                    </td>
                                    <td colspan="3"></td>
                                </tr>
                                </tfoot>
                            </table>
                            <br>

                            <div class="row">
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label>ผู้จัดทำ</label>
                                        <?php
                                        $session_user_name = trim(($_SESSION['first_name'] ?? '') . ' ' . ($_SESSION['last_name'] ?? ''));
                                        if (empty($session_user_name)) {
                                            $session_user_name = $_SESSION['alogin'] ?? $_SESSION['username'] ?? '';
                                        }
                                        ?>
                                        <input type="text" id="create_name" class="form-control" value="<?php echo htmlspecialchars($session_user_name); ?>">
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label>ผู้ตรวจสอบ</label>
                                        <input type="text" id="checker_name" class="form-control" value="">
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label>ผู้อนุมัติ</label>
                                        <input type="text" id="approve_name" class="form-control" value="">
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label>ผู้รับเงิน</label>
                                        <input type="text" id="receipt_name" class="form-control" value="">
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label>สถานะการยืนยัน</label>
                                        <select id="approve_status" name="approve_status" class="form-control">
                                            <option value="Y" selected>ยืนยัน</option>
                                            <option value="N">ไม่ยืนยัน</option>
                                        </select>
                                    </div>
                                </div>

                            </div>

                            <input type="file" id="pictures" multiple accept="image/*,application/pdf">
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
                        <div class="modal-dialog modal-lg">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title">เลือกรายการพัสดุ</h5>
                                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                        <span aria-hidden="true">&times;</span>
                                    </button>
                                </div>
                                <div class="modal-body">
                                    <table class="table table-bordered display" id="productTable">
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
                        <div class="modal-dialog modal-lg">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title">เลือกหน่วยนับ</h5>
                                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                        <span aria-hidden="true">&times;</span>
                                    </button>
                                </div>
                                <div class="modal-body">
                                    <table class="table table-bordered display" id="unitTable">
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
                $('#purpose').val(queryString["purpose"]);
                $('#payment_method').val(queryString["payment_method"]);
                $('#requester').val(queryString["requester"]);
                $('#supplier_id').val(queryString["supplier_id"]);
                $('#supplier_name').val(queryString["supplier_name"]);
                $('#address').val(queryString["address"]);
                $('#picture_doc').val(queryString["picture_doc"]);
                $('#totalAmount').val(queryString["total_amount"]);

                if (queryString["create_name"]) {
                    $('#create_name').val(queryString["create_name"]);
                }
                $('#checker_name').val(queryString["checker_name"]);
                $('#approve_name').val(queryString["approve_name"]);
                $('#receipt_name').val(queryString["receipt_name"]);

                $('#approve_status').val(queryString["approve_status"]);
                if (queryString["petty_cash_status"] === 'Y') {
                    $('#petty_cash_status').prop('checked', true);
                } else {
                    $('#petty_cash_status').prop('checked', false);
                }

                loadDetailData(queryString["doc_no"]);

                if (queryString["picture_doc"]) {
                    let filenames = queryString["picture_doc"].split(',');
                    let imagePreviewContainer = $('#imagePreview');
                    imagePreviewContainer.html('');

                    filenames.forEach(file => {
                        file = file.trim();
                        if (!file) return;

                        let fileExtension = file.split('.').pop().toLowerCase();
                        let isImage = ['jpg', 'jpeg', 'png', 'gif', 'bmp', 'webp'].includes(fileExtension);
                        let filePath = file;
                        if (!file.includes('/') && !file.includes('\\')) {
                            filePath = 'uploads/files/' + file;
                        }

                        let previewBox = $('<div>').addClass('preview-box');
                        
                        let removeBtn = $('<button>')
                            .addClass('remove-btn')
                            .html('&times;')
                            .attr('title', 'ลบไฟล์นี้')
                            .on('click', function () {
                                previewBox.remove();
                                let deleted = $('#deleted_images').val();
                                let deletedArray = deleted ? deleted.split(',') : [];
                                if (!deletedArray.includes(file)) {
                                    deletedArray.push(file);
                                    $('#deleted_images').val(deletedArray.join(','));
                                }
                            });

                        let contentLink = $('<a>').attr({'href': filePath, 'target': '_blank', 'title': 'คลิกเพื่อดูไฟล์'});

                        if (isImage) {
                            let img = $('<img>').attr('src', filePath);
                            // Fallback for different directory if not found in uploads/files
                            img.on('error', function() {
                                if (!file.includes('/') && filePath.startsWith('uploads/files/')) {
                                    $(this).attr('src', 'uploads/inventory/' + file);
                                }
                            });
                            contentLink.append(img);
                        } else if (fileExtension === 'pdf') {
                            contentLink.append($('<i>').addClass('fa fa-file-pdf-o pdf-icon'));
                        } else {
                            contentLink.append($('<i>').addClass('fa fa-file pdf-icon'));
                        }

                        contentLink.append($('<div>').addClass('file-name').text(file));
                        previewBox.append(removeBtn).append(contentLink);
                        imagePreviewContainer.append(previewBox);
                    });
                }
            }
        });
    </script>

    <script>
        function loadDetailData(docNo) {
            $.ajax({
                url: 'model/manage_voucher_process.php',
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
    <td style="width: 25%;">
        <div class="d-flex align-items-center">
            <input type="hidden" class="form-control product_id" value="${item.product_id}" readonly>
            <input type="text" class="form-control product_name" value="${item.product_name}" style="flex: 1; margin-right: 5px;">
            <a href="#itemModal" data-toggle="modal" class="btn btn-primary btn-select-item" style="white-space: nowrap;">
                <i class="fa fa-search"></i>
            </a>
        </div>
    </td>
    <td style="width: 10%;"><input type="number" class="form-control item-quantity" value="${item.quantity}" min="1"></td>
    <td style="width: 10%;"><input type="text" class="form-control item-inv" value="${item.inv}"></td>
    <td style="width: 10%;"><input type="number" class="form-control item-price" value="${item.price}" min="0"></td>
    <td style="width: 15%;"><input type="number" class="form-control item-amount" value="${(item.quantity * item.price).toFixed(2)}" readonly></td>
    <td style="width: 15%;">
        <div class="d-flex">
            <input type="hidden" class="form-control item-unit-code" value="${item.unit_id}" readonly style="flex: 1;">
            <input type="text" class="form-control item-unit-name" value="${item.unit_name}" readonly style="flex: 1;">
            <a href="#unitModal" data-toggle="modal" class="btn btn-primary ml-2 btn-select-unit" style="white-space: nowrap;">
                <i class="fa fa-search"></i>
            </a>
        </div>
    </td>
    <td style="width: 10%;"><input type="text" class="form-control item-remark" value="${item.remark || ''}"></td>
    <td style="width: 5%;"><button class="btn btn-danger btn-sm remove-row" type="button">ลบ</button></td>
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
  <td style="width: 25%;">
    <div class="d-flex align-items-center">
      <input type="hidden" class="form-control product_id" readonly>
      <input type="text" class="form-control product_name" style="flex: 1; margin-right: 5px;">
      <a href="#itemModal" data-toggle="modal" class="btn btn-primary btn-select-item" style="white-space: nowrap;">
        <i class="fa fa-search"></i>
      </a>
    </div>
  </td>
  <td style="width: 10%;"><input type="number" class="form-control item-quantity" min="1" required style="width: 100%;"></td>
  <td style="width: 10%;"><input type="text" class="form-control item-inv" style="width: 100%;"></td>
  <td style="width: 10%;"><input type="number" class="form-control item-price" min="0" required style="width: 100%;"></td>
  <td style="width: 15%;"><input type="number" class="form-control item-amount" min="0" required style="width: 100%;" readonly></td>
  <td style="width: 15%;">
    <div class="d-flex">
      <input type="hidden" class="form-control item-unit-code" readonly style="flex: 1;">
      <input type="text" class="form-control item-unit-name" readonly style="flex: 1;">
      <a href="#unitModal" data-toggle="modal" class="btn btn-primary ml-2 btn-select-unit" style="white-space: nowrap;">
        <i class="fa fa-search"></i>
      </a>
    </div>
  </td>
  <td style="width: 10%;"><input type="text" class="form-control item-remark" style="width: 100%;"></td>
  <td style="width: 5%;"><button class="btn btn-danger btn-sm remove-row" type="button">ลบ</button></td>
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
                if ($('#productTable').length && $.fn.DataTable.isDataTable('#productTable')) {
                    $('#productTable').DataTable().destroy();
                }
                $('#productTable').DataTable({
                    "processing": true,
                    "serverSide": false,
                    "ajax": {
                        "url": "model/get_products.php",
                        "type": "GET",
                        "dataSrc": ""
                    },
                    "columns": [
                        {"data": "product_id"},
                        {"data": "product_name"},
                        {
                            "data": null,
                            "render": function(data, type, row) {
                                return '<button class="btn btn-sm btn-primary select-this" data-code="' + row.product_id + '" data-name="' + row.product_name + '">เลือก</button>';
                            },
                            "orderable": false
                        }
                    ],
                    "language": {
                        "emptyTable": "ไม่พบข้อมูล",
                        "info": "แสดง _START_ ถึง _END_ จาก _TOTAL_ รายการ",
                        "infoEmpty": "แสดง 0 ถึง 0 จาก 0 รายการ",
                        "lengthMenu": "แสดง _MENU_ รายการ",
                        "search": "ค้นหา:",
                        "zeroRecords": "ไม่พบรายการที่ตรงกัน",
                        "paginate": {
                            "first": "หน้าแรก",
                            "last": "หน้าสุดท้าย",
                            "next": "ถัดไป",
                            "previous": "ก่อนหน้า"
                        }
                    },
                    "lengthMenu": [[10, 25, 50, -1], [10, 25, 50, "All"]],
                    "pageLength": 10,
                    "ordering": true
                });
            }
        });
    </script>


    <script>
        $(document).ready(function () {
            let currentRow = null;

            $(document).on('click', '.btn-select-unit', function () {
                currentRow = $(this).closest('tr');
                loadUnitTable();
                $('#unitModal').modal('show');
            });

            $(document).on('click', '.select-this-unit', function () {
                const code = $(this).data('code');
                const name = $(this).data('name');

                if (currentRow) {
                    currentRow.find('.item-unit-code').val(code);
                    currentRow.find('.item-unit-name').val(name);
                }

                $('#unitModal').modal('hide');
            });

            function loadUnitTable() {
                if ($('#unitTable').length && $.fn.DataTable.isDataTable('#unitTable')) {
                    $('#unitTable').DataTable().destroy();
                }
                $('#unitTable').DataTable({
                    "processing": true,
                    "serverSide": false,
                    "ajax": {
                        "url": "model/get_unit.php",
                        "type": "GET",
                        "dataSrc": ""
                    },
                    "columns": [
                        {"data": "unit_id"},
                        {"data": "unit_name"},
                        {
                            "data": null,
                            "render": function(data, type, row) {
                                return '<button class="btn btn-sm btn-primary select-this-unit" data-code="' + row.unit_id + '" data-name="' + row.unit_name + '">เลือก</button>';
                            },
                            "orderable": false
                        }
                    ],
                    "language": {
                        "emptyTable": "ไม่พบข้อมูล",
                        "info": "แสดง _START_ ถึง _END_ จาก _TOTAL_ รายการ",
                        "infoEmpty": "แสดง 0 ถึง 0 จาก 0 รายการ",
                        "lengthMenu": "แสดง _MENU_ รายการ",
                        "search": "ค้นหา:",
                        "zeroRecords": "ไม่พบรายการที่ตรงกัน",
                        "paginate": {
                            "first": "หน้าแรก",
                            "last": "หน้าสุดท้าย",
                            "next": "ถัดไป",
                            "previous": "ก่อนหน้า"
                        }
                    },
                    "lengthMenu": [[10, 25, 50, -1], [10, 25, 50, "All"]],
                    "pageLength": 10,
                    "ordering": true
                });
            }
        });
    </script>

    <script>
        $(document).ready(function () {
            function calculateAmount(row) {
                const quantity = parseFloat(row.find('.item-quantity').val()) || 0;
                const price = parseFloat(row.find('.item-price').val()) || 0;
                const amount = quantity * price;
                row.find('.item-amount').val(amount.toFixed(2));
            }

            $(document).on('input', '.item-quantity, .item-price', function () {
                const row = $(this).closest('tr');
                calculateAmount(row);
                calculateTotalAmount();
            });

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
            $('#save').on('click', async function (e) {
                e.preventDefault();

                if (!$('#doc_date').val() || !$('#supplier_name').val() || !$('#purpose').val()) {
                    let missingMsg = "กรุณากรอกข้อมูลหลักให้ครบถ้วน";
                    if (!$('#purpose').val()) {
                        missingMsg = "กรุณากรอกช่อง 'จ่ายเพื่อ' (วัตถุประสงค์การจ่ายเงิน)";
                    }
                    let box = bootbox.alert({
                        title: "แจ้งเตือน",
                        message: missingMsg,
                        centerVertical: true,
                        backdrop: true
                    });
                    box.find('.modal-header').addClass('bg-danger text-white');
                    box.find('.close').addClass('text-white');
                    return;
                }

                const details = [];
                let valid = true;

                $('#detailTable tbody tr').each(function () {
                    const product_id = $(this).find('.product_id').val();
                    const product_name = $(this).find('.product_name').val();
                    const quantity = parseFloat($(this).find('.item-quantity').val());
                    const inv = $(this).find('.item-inv').val();
                    const price = parseFloat($(this).find('.item-price').val());
                    const unit_id = $(this).find('.item-unit-code').val();
                    const unit_name = $(this).find('.item-unit-name').val();
                    const remark = $(this).find('.item-remark').val();

                    if (!product_name || isNaN(quantity) || isNaN(price) || !unit_id || quantity <= 0 || price < 0) {
                        valid = false;
                        alertify.error("กรุณากรอกข้อมูลให้ครบและถูกต้องในทุกรายการ");
                        return false;
                    }

                    details.push({product_id, product_name, quantity, inv, price, unit_id, unit_name, remark});
                });

                if (!valid) return;

                $('#save').prop('disabled', true);

                try {
                    const newlyUploadedFilenames = await uploadImages();

                    let existingPictureDoc = $('#picture_doc').val();
                    let existingFilenames = existingPictureDoc ? existingPictureDoc.split(',').map(name => name.trim()).filter(name => name) : [];

                    let deletedFilenames = $('#deleted_images').val();
                    let deletedArray = deletedFilenames ? deletedFilenames.split(',').map(name => name.trim()).filter(name => name) : [];

                    let currentFilenames = existingFilenames.filter(filename => !deletedArray.includes(filename));

                    const finalPictureDoc = currentFilenames.concat(newlyUploadedFilenames).join(',');

                    const payload = {
                        action: $('#action').val(),
                        doc_no: $('#doc_no').val(),
                        date: $('#doc_date').val(),
                        requester: $('#requester').val(),
                        supplier_id: $('#supplier_id').val(),
                        supplier_name: $('#supplier_name').val(),
                        address: $('#address').val(),
                        create_name: $('#create_name').val(),
                        checker_name: $('#checker_name').val(),
                        approve_name: $('#approve_name').val(),
                        receipt_name: $('#receipt_name').val(),
                        payment_method: $('#payment_method').val(),
                        purpose: $('#purpose').val(),
                        picture_doc: finalPictureDoc,
                        approve_status: $('#approve_status').val(),
                        petty_cash_status: $('#petty_cash_status').is(':checked') ? 'Y' : 'N',
                        details: details
                    };

                    $.ajax({
                        url: 'model/manage_voucher_data_detail_process.php',
                        method: 'POST',
                        contentType: 'application/json',
                        data: JSON.stringify(payload),
                        success: function (res) {
                            if (res.status === 'success') {
                                alertify.success('บันทึกสำเร็จแล้ว');
                                //closeAndReload();
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

            $(document).on('click', '.supplier-item', function () {
                let name = $(this).data('name');
                let address = $(this).data('address');
                let id = $(this).data('id');

                $('#supplier_name').val(name);
                $('#address').val(address);
                $('#supplier_id').val(id);
                $('#supplier_list').fadeOut();
            });

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
        let uploadedImages = [];
        document.getElementById('pictures').addEventListener('change', function (e) {
            const files = Array.from(e.target.files);
            const previewArea = document.getElementById('preview-area');

            files.forEach((file) => {
                const fileIndex = uploadedImages.push(file) - 1;

                let previewBox = document.createElement('div');
                previewBox.classList.add('preview-box');

                let removeBtn = document.createElement('button');
                removeBtn.classList.add('remove-btn');
                removeBtn.innerHTML = '&times;';
                removeBtn.setAttribute('data-file-index', fileIndex);
                removeBtn.onclick = function() {
                    const idx = parseInt(this.getAttribute('data-file-index'));
                    uploadedImages.splice(idx, 1);
                    previewBox.remove();
                    // Re-index remaining buttons
                    document.querySelectorAll('#preview-area .remove-btn').forEach((btn, i) => {
                        btn.setAttribute('data-file-index', i);
                    });
                };

                let contentLink = document.createElement('a');
                contentLink.setAttribute('target', '_blank');
                contentLink.setAttribute('title', 'คลิกเพื่อดูไฟล์');

                if (file.type.startsWith('image/')) {
                    const reader = new FileReader();
                    reader.onload = function (e) {
                        contentLink.setAttribute('href', e.target.result);
                        let img = document.createElement('img');
                        img.setAttribute('src', e.target.result);
                        contentLink.appendChild(img);
                    };
                    reader.readAsDataURL(file);
                } else if (file.type === 'application/pdf') {
                    contentLink.setAttribute('href', URL.createObjectURL(file));
                    let icon = document.createElement('i');
                    icon.classList.add('fa', 'fa-file-pdf-o', 'pdf-icon');
                    contentLink.appendChild(icon);
                } else {
                    contentLink.setAttribute('href', URL.createObjectURL(file));
                    let icon = document.createElement('i');
                    icon.classList.add('fa', 'fa-file', 'pdf-icon');
                    contentLink.appendChild(icon);
                }

                let nameLabel = document.createElement('div');
                nameLabel.classList.add('file-name');
                nameLabel.textContent = file.name;

                contentLink.appendChild(nameLabel);
                previewBox.appendChild(removeBtn);
                previewBox.appendChild(contentLink);
                previewArea.appendChild(previewBox);
            });
        });

        async function uploadImages() {
            const formData = new FormData();
            uploadedImages.forEach(file => formData.append('images[]', file));

            if (uploadedImages.length === 0) {
                return Promise.resolve([]);
            }

            const response = await fetch('upload_img_doc.php', {
                method: 'POST',
                body: formData
            });

            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }

            const result = await response.json();
            if (result.status === 'success') {
                return result.filenames;
            } else {
                throw new Error(result.message || 'Image upload failed on server.');
            }
        }
    </script>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const radioButtons = document.querySelectorAll('input[name="payment_method_radio"]');
            const paymentMethodInput = document.getElementById('payment_method');

            function updatePaymentMethodInput() {
                radioButtons.forEach(radio => {
                    if (radio.checked) {
                        paymentMethodInput.value = radio.value;
                        if (radio.value === 'เงินสด' || radio.value === 'โอนเงิน') {
                            paymentMethodInput.setAttribute('readonly', true);
                        } else {
                            paymentMethodInput.removeAttribute('readonly');
                            paymentMethodInput.focus();
                        }
                    }
                });
            }

            radioButtons.forEach(radio => {
                radio.addEventListener('change', updatePaymentMethodInput);
            });

            updatePaymentMethodInput();
        });
    </script>


    </body>
    </html>

    <?php
}
?>