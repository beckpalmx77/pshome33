This is a significant modification, transforming a single-entry form into a master-detail system with image uploads and a dedicated backend. I will break this down into several steps.

**Step 1: Database Table Design (Conceptual)**
First, we need two new tables for your expenses: one for the master (header) data and one for the detail items.

**`ims_expenses_master` (Header Table)**

| Column Name     | Data Type     | Constraints/Description                                      |
| :-------------- | :------------ | :----------------------------------------------------------- |
| `doc_no`        | VARCHAR(20)   | PRIMARY KEY, Unique document number (e.g., EXP-MM-YYYY-NNNN) |
| `doc_date`      | VARCHAR(10)   | Date of the expense document (DD-MM-YYYY)                    |
| `requester`     | VARCHAR(255)  | Name of the requester                                        |
| `supplier_id`   | VARCHAR(20)   | Foreign Key to `ims_supplier.supplier_id` (if applicable)    |
| `supplier_name` | VARCHAR(255)  | Name of the supplier                                         |
| `purpose`       | TEXT          | Purpose/description of the expense                           |
| `total_amount`  | DECIMAL(12,2) | Total amount of all detail items                             |
| `picture_doc`   | TEXT          | Comma-separated list of image filenames                      |
| `created_at`    | DATETIME      | Timestamp of record creation                                 |
| `updated_at`    | DATETIME      | Timestamp of last update                                     |

**`ims_expenses_detail` (Detail Table)**

| Column Name   | Data Type     | Constraints/Description                                      |
| :------------ | :------------ | :----------------------------------------------------------- |
| `id`          | INT           | PRIMARY KEY, AUTO_INCREMENT                                  |
| `doc_no`      | VARCHAR(20)   | FOREIGN KEY to `ims_expenses_master.doc_no`                  |
| `line_no`     | INT           | Line number for detail items (1, 2, 3...)                    |
| `product_id`  | VARCHAR(20)   | ID of the product/service from `ims_product` (if applicable) |
| `product_name`| VARCHAR(255)  | Name of the product/service                                  |
| `quantity`    | DECIMAL(12,2) | Quantity of the item                                         |
| `price`       | DECIMAL(12,2) | Price per unit                                               |
| `unit_id`     | VARCHAR(20)   | Foreign Key to `ims_unit.unit_id` (if applicable)            |
| `unit_name`   | VARCHAR(255)  | Name of the unit (e.g., Each, Kilogram)                      |

---

**Step 2: Frontend Modification (`manage_expenses.php`)**

I will provide the modified `manage_expenses.php` file. This version incorporates:
* Header fields for expense details.
* A dynamic detail table for adding/removing expense items.
* Functionality to calculate total amount.
* Image upload, preview, and deletion capabilities (leveraging the logic previously discussed for `manage_purchase_data.php`).
* Placeholder modals for selecting products, units, suppliers, and requesters (assuming you have `get_products.php`, `get_unit.php`, `get_suppliers.php`, `get_requester.php` scripts).

**Important:** This code assumes you have a `Header.php` file for common includes and that the `uploads/files/` directory exists and is writable.

```php
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
        <title>บันทึกค่าใช้จ่าย</title>
        <link rel="stylesheet"
              href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.9.0/css/bootstrap-datepicker.min.css">
        <link rel="stylesheet" href="vendor/datatables/v11/jquery.dataTables.min.css"/>
        <link rel="stylesheet" href="vendor/datatables/v11/buttons.dataTables.min.css"/>
        <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet"/>

        <style>
            .list-group {
                max-height: 200px;
                overflow-y: auto;
                -webkit-overflow-scrolling: touch;
            }
            .list-group-item {
                cursor: pointer;
            }
            .list-group-item:hover {
                background-color: #f8f9fa;
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
                                        <label>วัตถุประสงค์ / รายละเอียดค่าใช้จ่าย</label>
                                        <input type="text" id="purpose" class="form-control" required>
                                    </div>
                                </div>
                            </div>

                            <div class="row mt-2">
                                <div class="col-md-4 position-relative">
                                    <div class="form-group">
                                        <label for="requester" class="control-label">ผู้ขอเบิก</label>
                                        <input type="text" id="requester" name="requester" class="form-control"
                                               autocomplete="off" required>
                                        <input type="hidden" id="requester_id" name="requester_id">
                                        <div id="requester_list" class="list-group position-absolute"
                                             style="z-index: 1000; width: 100%;"></div>
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

                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="supplier_name" class="control-label">ชื่อผู้ขาย / ผู้รับเงิน <b>(ถ้าไม่พบในระบบ สามารถพิมพ์เพิ่มได้)</b></label>
                                        <input type="text" class="form-control" id="supplier_name" name="supplier_name"
                                               autocomplete="off" required placeholder="">
                                        <input type="hidden" id="supplier_id" name="supplier_id">
                                        <div id="supplier_list" class="list-group position-absolute"
                                             style="z-index:1000; width: 100%;"></div>
                                    </div>
                                </div>

                                <div class="col-md-1 d-flex align-items-end">
                                    <div class="form-group">
                                        <label class="control-label" style="visibility:hidden;">เลือกผู้ขาย</label>
                                        <a data-toggle="modal" href="#SearchSupModal" class="btn btn-primary">
                                            Click <i class="fa fa-search" aria-hidden="true"></i>
                                        </a>
                                    </div>
                                </div>
                            </div>

                            <hr> <div class="d-flex align-items-center mt-4 mb-3">
                                <h5 class="text-primary mb-0 mr-2">รายการค่าใช้จ่าย</h5>
                                <button class="btn btn-primary" id="addRow" type="button">+ เพิ่มรายการ</button>
                            </div>

                            <table class="table table-bordered" id="detailTable">
                                <thead class="thead-primary">
                                <tr>
                                    <th style="width: 15%;">รหัส (ถ้ามี)</th>
                                    <th style="width: 25%;">รายการ / รายละเอียด</th>
                                    <th style="width: 10%;">จำนวน</th>
                                    <th style="width: 12%;">ราคาต่อหน่วย</th>
                                    <th style="width: 15%;">รวมเงิน</th>
                                    <th style="width: 10%;">หน่วยนับ (ถ้ามี)</th>
                                    <th>ลบ</th>
                                </tr>
                                </thead>
                                <tbody></tbody>
                                <tfoot>
                                <tr>
                                    <td colspan="4" class="text-right"><strong>รวมเงินทั้งหมด:</strong></td>
                                    <td><input type="text" class="form-control text-right" id="totalAmount" readonly>
                                    </td>
                                    <td colspan="2"></td>
                                </tr>
                                </tfoot>
                            </table>
                            <br>

                            <h5>รูปภาพประกอบ (ถ้ามี)</h5>
                            <input type="file" id="pictures" multiple accept="image/*">
                            <input type="hidden" id="picture_doc" name="picture_doc">
                            <input type="hidden" id="deleted_images" name="deleted_images" value="">
                            <div id="imagePreview" class="mt-2 d-flex flex-wrap"></div>


                            <div class="modal-footer">
                                <input type="hidden" name="id" id="id"/>
                                <input type="hidden" name="action" id="action" value=""/>

                                <button type="submit" name="save" id="save" class="btn btn-primary">
                                    บันทึก <i class="fa fa-save"></i>
                                </button>

                                <button type="button" class="btn btn-danger" onclick="closeAndReload()">
                                    ปิด <i class="fa fa-window-close"></i>
                                </button>
                            </div>
                        </div>
                    </div>

                    <div class="modal fade" id="itemModal" tabindex="-1" role="dialog" aria-labelledby="itemModalLabel"
                         aria-hidden="true">
                        <div class="modal-dialog modal-lg" role="document">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title">เลือกรายการ/พัสดุ</h5>
                                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                        <span aria-hidden="true">&times;</span>
                                    </button>
                                </div>
                                <div class="modal-body">
                                    <table class="table table-bordered" id="productTable">
                                        <thead>
                                        <tr>
                                            <th>รหัส</th>
                                            <th>ชื่อรายการ</th>
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
                                    <h4 class="modal-title">เลือกผู้ขาย / ผู้รับเงิน</h4>
                                    <button type="button" class="close" data-dismiss="modal"
                                            aria-hidden="true">×
                                    </button>
                                </div>
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

                    <div class="modal fade" id="SearchRequesterModal">
                        <div class="modal-dialog modal-lg">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h4 class="modal-title">เลือกผู้ขอเบิก</h4>
                                    <button type="button" class="close" data-dismiss="modal"
                                            aria-hidden="true">×
                                    </button>
                                </div>
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

    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.bundle.min.js"></script>
    <script src="vendor/datatables/v11/bootbox.min.js"></script>
    <script src="vendor/datatables/v11/jquery.dataTables.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.9.0/js/bootstrap-datepicker.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.9.0/locales/bootstrap-datepicker.th.min.js"></script>
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

            $("#sub_menu").html(queryString["sub_menu"] || "บันทึกค่าใช้จ่าย");
            $("#main_menu").html(queryString["main_menu"] || "ค่าใช้จ่าย");

            $('#action').val(queryString["action"]); // 'ADD' or 'UPDATE'

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

                // Load existing images for UPDATE mode
                if (queryString["picture_doc"]) {
                    let filenames = queryString["picture_doc"].split(',');
                    let imagePreviewContainer = $('#imagePreview');
                    imagePreviewContainer.html(''); // Clear any existing preview

                    filenames.forEach(file => {
                        file = file.trim();
                        if (!file) return;

                        let imageBox = $('<div>').addClass('position-relative m-2').css({display: 'inline-block'});
                        let img = $('<img>')
                            .attr('src', 'uploads/files/' + file) // Assuming 'uploads/files/' is the path
                            .css({width: '120px', height: 'auto', border: '1px solid #ccc', padding: '2px', 'object-fit': 'cover'});

                        let deleteBtn = $('<button>')
                            .addClass('btn btn-danger btn-sm position-absolute')
                            .css({top: '0', right: '0', zIndex: 2, padding: '2px 6px', borderRadius: '50%'})
                            .html('&times;')
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
            } else {
                 // Set action to ADD if no doc_no in query string
                 $('#action').val('ADD');
            }
        });
    </script>

    <script>
        // Datepicker initialization
        $(document).ready(function () {
            $('#doc_date').datepicker({
                format: "dd-mm-yyyy",
                todayHighlight: true,
                language: "th",
                autoclose: true
            });
        });

        // Function to close current window and reload parent data table
        function closeAndReload() {
            if (window.opener && !window.opener.closed) {
                // Assuming the parent window has a DataTable with ID 'TableRecordList'
                if (window.opener.$('#TableRecordList').length) {
                    window.opener.$('#TableRecordList').DataTable().ajax.reload(null, false);
                }
            }
            window.close();
        }
    </script>

    <script>
        // Detail Table Dynamic Rows
        $(document).ready(function () {
            $('#addRow').on('click', function () {
                $('#detailTable tbody').append(`
<tr>
  <td style="width: 15%;">
    <div class="d-flex align-items-center">
      <input type="text" class="form-control product_id" style="flex: 1;">
      <a href="#itemModal" data-toggle="modal" class="btn btn-primary ml-1 btn-select-item" style="white-space: nowrap; padding: 6px 10px;">
        <i class="fa fa-search"></i>
      </a>
    </div>
  </td>
  <td style="width: 25%;"><input type="text" class="form-control product_name" required></td>
  <td style="width: 10%;"><input type="number" class="form-control item-quantity" min="1" required></td>
  <td style="width: 12%;"><input type="number" class="form-control item-price" min="0" required></td>
  <td style="width: 15%;"><input type="number" class="form-control item-amount" readonly></td>
  <td style="width: 10%;">
    <div class="d-flex align-items-center">
      <input type="text" class="form-control item-unit-name">
      <a href="#unitModal" data-toggle="modal" class="btn btn-primary ml-1 btn-select-unit" style="white-space: nowrap; padding: 6px 10px;">
        <i class="fa fa-search"></i>
      </a>
      <input type="hidden" class="item-unit-code">
    </div>
  </td>
  <td style="width: auto;"><button class="btn btn-danger btn-sm remove-row" type="button">ลบ</button></td>
</tr>
                `);
            });

            $(document).on('click', '.remove-row', function () {
                $(this).closest('tr').remove();
                calculateTotalAmount();
            });

            // Calculate item amount and total amount on input change
            $(document).on('input', '.item-quantity, .item-price', function () {
                const row = $(this).closest('tr');
                const quantity = parseFloat(row.find('.item-quantity').val()) || 0;
                const price = parseFloat(row.find('.item-price').val()) || 0;
                const amount = quantity * price;
                row.find('.item-amount').val(amount.toFixed(2));
                calculateTotalAmount();
            });
        });

        // Function to calculate overall total amount
        function calculateTotalAmount() {
            let total = 0;
            $('#detailTable tbody tr').each(function () {
                const amount = parseFloat($(this).find('.item-amount').val()) || 0;
                total += amount;
            });
            $('#totalAmount').val(total.toFixed(2));
        }
    </script>

    <script>
        // Load Detail Data for Update
        function loadDetailData(docNo) {
            $.ajax({
                url: 'model/manage_expenses_process.php', // New backend for expenses
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
        <div class="d-flex align-items-center">
            <input type="text" class="form-control product_id" value="${item.product_id}" style="flex: 1;">
            <a href="#itemModal" data-toggle="modal" class="btn btn-primary ml-1 btn-select-item" style="white-space: nowrap; padding: 6px 10px;">
                <i class="fa fa-search"></i>
            </a>
        </div>
    </td>
    <td style="width: 25%;"><input type="text" class="form-control product_name" value="${item.product_name}" required></td>
    <td style="width: 10%;"><input type="number" class="form-control item-quantity" value="${item.quantity}" min="1" required></td>
    <td style="width: 12%;"><input type="number" class="form-control item-price" value="${item.price}" min="0" required></td>
    <td style="width: 15%;"><input type="number" class="form-control item-amount" value="${(item.quantity * item.price).toFixed(2)}" readonly></td>
    <td style="width: 10%;">
        <div class="d-flex align-items-center">
            <input type="text" class="form-control item-unit-name" value="${item.unit_name}">
            <a href="#unitModal" data-toggle="modal" class="btn btn-primary ml-1 btn-select-unit" style="white-space: nowrap; padding: 6px 10px;">
                <i class="fa fa-search"></i>
            </a>
            <input type="hidden" class="item-unit-code" value="${item.unit_id}">
        </div>
    </td>
    <td style="width: auto;"><button class="btn btn-danger btn-sm remove-row" type="button">ลบ</button></td>
</tr>
                            `);
                        });
                        calculateTotalAmount(); // Recalculate total after loading details
                    } else if (response.status === 'error') {
                        alertify.error("Error: " + response.message);
                    } else {
                        alertify.error("ไม่พบข้อมูลรายการค่าใช้จ่าย");
                    }
                },
                error: function (xhr, status, error) {
                    console.error("AJAX Error:", status, error);
                    alertify.error("โหลดข้อมูลรายการค่าใช้จ่ายไม่สำเร็จ");
                }
            });
        }
    </script>

    <script>
        // Autocomplete and Modal for Supplier
        $(document).ready(function () {
            $('#supplier_name').on('keyup', function () {
                let query = $(this).val();
                if (query.length >= 2) {
                    $.ajax({
                        url: 'model/get_suppliers.php', // Assuming this path
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
                let id = $(this).data('id');
                $('#supplier_name').val(name);
                $('#supplier_id').val(id);
                $('#supplier_list').fadeOut();
            });

            $(document).on('click', function (e) {
                if (!$(e.target).closest('#supplier_name, #supplier_list').length) {
                    $('#supplier_list').fadeOut();
                }
            });
        });

        // Autocomplete and Modal for Requester
        $(document).ready(function () {
            $('#requester').on('keyup', function () {
                let query = $(this).val();
                if (query.length >= 2) {
                    $.ajax({
                        url: 'model/get_requester.php', // Assuming this path
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
        // Modals for Product and Unit Selection (Similar to purchase form)
        let currentRowForProduct = null;
        $(document).on('click', '.btn-select-item', function () {
            currentRowForProduct = $(this).closest('tr');
            loadProductTable();
            $('#itemModal').modal('show');
        });

        $(document).on('click', '.select-this', function () {
            const code = $(this).data('code');
            const name = $(this).data('name');
            if (currentRowForProduct) {
                currentRowForProduct.find('.product_id').val(code);
                currentRowForProduct.find('.product_name').val(name);
            }
            $('#itemModal').modal('hide');
        });

        function loadProductTable() {
            $.ajax({
                url: 'model/get_products.php', // Assuming this path
                method: 'GET',
                dataType: 'json',
                success: function (data) {
                    let html = '';
                    data.forEach(item => {
                        html += `
<tr>
    <td>${item.product_id || ''}</td>
    <td>${item.product_name}</td>
    <td>
        <button class="btn btn-sm btn-primary select-this"
                data-code="${item.product_id || ''}"
                data-name="${item.product_name}">เลือก</button>
    </td>
</tr>`;
                    });
                    $('#productTable tbody').html(html);
                    if ($.fn.DataTable.isDataTable('#productTable')) {
                         $('#productTable').DataTable().destroy();
                    }
                    $('#productTable').DataTable({
                        "paging": true,
                        "searching": true,
                        "ordering": true,
                        "info": false
                    });
                },
                error: function () {
                    alertify.error('โหลดข้อมูลรายการไม่สำเร็จ');
                }
            });
        }

        let currentRowForUnit = null;
        $(document).on('click', '.btn-select-unit', function () {
            currentRowForUnit = $(this).closest('tr');
            loadUnitTable();
            $('#unitModal').modal('show');
        });

        $(document).on('click', '.select-this-unit', function () {
            const code = $(this).data('code');
            const name = $(this).data('name');
            if (currentRowForUnit) {
                currentRowForUnit.find('.item-unit-code').val(code);
                currentRowForUnit.find('.item-unit-name').val(name);
            }
            $('#unitModal').modal('hide');
        });

        function loadUnitTable() {
            $.ajax({
                url: 'model/get_unit.php', // Assuming this path
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
                     if ($.fn.DataTable.isDataTable('#unitTable')) {
                         $('#unitTable').DataTable().destroy();
                    }
                    $('#unitTable').DataTable({
                        "paging": true,
                        "searching": true,
                        "ordering": true,
                        "info": false
                    });
                },
                error: function () {
                    alertify.error('โหลดข้อมูลหน่วยนับไม่สำเร็จ');
                }
            });
        }
    </script>


    <script>
        let uploadedImages = []; // Stores newly selected files for upload

        document.getElementById('pictures').addEventListener('change', function (e) {
            const files = Array.from(e.target.files);
            const imagePreviewContainer = document.getElementById('imagePreview');

            files.forEach((file) => {
                const reader = new FileReader();
                reader.onload = function (e) {
                    const imgDiv = document.createElement('div');
                    imgDiv.classList.add('col-md-2', 'position-relative', 'mb-2'); // Added mb-2 for spacing

                    // Store the file object and get its index in the uploadedImages array
                    const fileIndex = uploadedImages.push(file) - 1;

                    imgDiv.innerHTML = `
                    <img src="${e.target.result}" class="img-thumbnail" style="width:100%; height:120px; object-fit:cover;">
                    <button type="button" class="btn btn-sm btn-danger position-absolute top-0 end-0 remove-new-img" data-file-index="${fileIndex}">&times;</button>
                `;
                    imagePreviewContainer.appendChild(imgDiv);
                };
                reader.readAsDataURL(file);
            });
        });

        // Event listener for removing newly selected images from preview
        document.getElementById('imagePreview').addEventListener('click', function (e) {
            if (e.target.classList.contains('remove-new-img')) {
                const fileIndex = parseInt(e.target.getAttribute('data-file-index'));
                uploadedImages.splice(fileIndex, 1); // Remove the file from the array
                e.target.parentElement.remove(); // Remove the image box from DOM

                // Re-index data-file-index attributes of remaining new images
                $('#imagePreview .remove-new-img').each(function(i) {
                    $(this).attr('data-file-index', i);
                });
            }
        });

        // Function to upload new images
        async function uploadImages() {
            const formData = new FormData();
            uploadedImages.forEach(file => formData.append('images[]', file));

            if (uploadedImages.length === 0) {
                return Promise.resolve([]); // Return empty array if no new images to upload
            }

            try {
                const response = await fetch('upload_img_doc_expenses.php', { // **NEW UPLOAD SCRIPT**
                    method: 'POST',
                    body: formData
                });

                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }

                const result = await response.json();
                if (result.status === 'success') {
                    return result.filenames; // Array of successfully uploaded filenames
                } else {
                    throw new Error(result.message || 'Image upload failed on server.');
                }
            } catch (error) {
                console.error("Upload error:", error);
                throw new Error("Failed to upload images: " + error.message);
            }
        }
    </script>


    <script>
        // Save Button Logic
        $(document).ready(function () {
            $('#save').on('click', async function (e) {
                e.preventDefault();

                // 1. Validate Header Data
                if (!$('#doc_date').val() || !$('#requester').val() || !$('#purpose').val() || !$('#supplier_name').val()) {
                    alertify.error('กรุณากรอกข้อมูลส่วนหัวให้ครบถ้วน');
                    return;
                }

                // 2. Validate Detail Data
                const details = [];
                let detailValid = true;

                $('#detailTable tbody tr').each(function () {
                    const productId = $(this).find('.product_id').val();
                    const productName = $(this).find('.product_name').val();
                    const quantity = parseFloat($(this).find('.item-quantity').val());
                    const price = parseFloat($(this).find('.item-price').val());
                    const unitId = $(this).find('.item-unit-code').val(); // Hidden unit_id
                    const unitName = $(this).find('.item-unit-name').val(); // Displayed unit_name

                    if (!productName || isNaN(quantity) || isNaN(price) || quantity <= 0 || price < 0) {
                        detailValid = false;
                        alertify.error("กรุณากรอกข้อมูลรายการค่าใช้จ่ายให้ครบและถูกต้องในทุกบรรทัด");
                        return false; // Break .each loop
                    }

                    details.push({
                        product_id: productId,
                        product_name: productName,
                        quantity: quantity,
                        price: price,
                        unit_id: unitId,
                        unit_name: unitName
                    });
                });

                if (!detailValid) return; // Stop if detail validation failed

                // If no detail rows, prompt user (optional, depends on your business logic)
                if (details.length === 0) {
                     alertify.error('กรุณาเพิ่มรายการค่าใช้จ่ายอย่างน้อยหนึ่งรายการ');
                     return;
                }


                $('#save').prop('disabled', true); // Disable button to prevent multiple submissions

                try {
                    // 3. Upload Images
                    const newlyUploadedFilenames = await uploadImages();

                    // 4. Consolidate Image Filenames
                    let existingPictureDoc = $('#picture_doc').val(); // Current saved picture_doc string
                    let existingFilenames = existingPictureDoc ? existingPictureDoc.split(',').map(name => name.trim()).filter(name => name) : [];

                    let deletedFilenames = $('#deleted_images').val(); // Filenames marked for deletion
                    let deletedArray = deletedFilenames ? deletedFilenames.split(',').map(name => name.trim()).filter(name => name) : [];

                    // Filter out deleted images from the existing ones
                    let currentNonDeletedFilenames = existingFilenames.filter(filename => !deletedArray.includes(filename));

                    // Combine current (non-deleted existing) filenames with newly uploaded filenames
                    const finalPictureDoc = currentNonDeletedFilenames.concat(newlyUploadedFilenames).join(',');


                    // 5. Prepare Payload
                    const payload = {
                        action: $('#action').val(), // 'ADD' or 'UPDATE'
                        doc_no: $('#doc_no').val(),
                        doc_date: $('#doc_date').val(),
                        requester: $('#requester').val(),
                        supplier_id: $('#supplier_id').val(),
                        supplier_name: $('#supplier_name').val(),
                        purpose: $('#purpose').val(),
                        total_amount: parseFloat($('#totalAmount').val()) || 0,
                        picture_doc: finalPictureDoc, // The combined string of image filenames
                        details: details
                    };

                    // 6. Send Data to Backend
                    $.ajax({
                        url: 'model/manage_expenses_process.php', // **NEW BACKEND PROCESS SCRIPT**
                        method: 'POST',
                        contentType: 'application/json',
                        data: JSON.stringify(payload),
                        success: function (res) {
                            if (res.status === 'success') {
                                alertify.success('บันทึกข้อมูลค่าใช้จ่ายสำเร็จแล้ว');
                                closeAndReload(); // Close window and reload parent table
                            } else {
                                alertify.error('เกิดข้อผิดพลาดในการบันทึก: ' + (res.message || 'Unknown error'));
                                $('#save').prop('disabled', false); // Re-enable button
                            }
                        },
                        error: function (xhr, status, error) {
                            console.error("AJAX Error:", status, error);
                            alertify.error('ไม่สามารถเชื่อมต่อเซิร์ฟเวอร์เพื่อบันทึกข้อมูลได้');
                            $('#save').prop('disabled', false); // Re-enable button
                        }
                    });

                } catch (err) {
                    console.error("Error during save process:", err);
                    alertify.error('มีข้อผิดพลาดเกิดขึ้น: ' + err.message);
                    $('#save').prop('disabled', false); // Re-enable button
                }
            });
        });
    </script>


    </body>
    </html>

    <?php
} // end else session check
?>
