<?php
// manage_payroll_data.php - สำหรับจัดการข้อมูลเงินเดือนพนักงานรายวัน
session_start();
error_reporting(0); // ปิดการแสดง error ใน Production environment
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
        <title>บันทึกข้อมูลเงินเดือนรายวัน</title>
        <link rel="stylesheet"
              href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.9.0/css/bootstrap-datepicker.min.css">
        <style>
            body {
                font-family: 'Inter', sans-serif;
                background-color: #f8f9fa;
            }

            .container-fluid {
                margin-top: 20px;
            }

            .card {
                border-radius: 10px;
                box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            }

            .table th, .table td {
                vertical-align: middle;
            }

            .table tbody tr:hover {
                background-color: #e2f0ff;
            }

            .modal-content {
                border-radius: 1rem;
            }

            .modal-header {
                background-color: #0d6efd;
                color: white;
                border-top-left-radius: 1rem;
                border-top-right-radius: 1rem;
            }

            .modal-header .btn-close {
                filter: invert(1);
            }

            div.dataTables_wrapper div.dataTables_filter input {
                width: auto;
                margin-left: 0.5em;
            }

            .file-preview-box {
                position: relative;
                margin-bottom: 1rem;
            }
            .remove-btn {
                position: absolute;
                top: -5px;
                right: 5px;
                z-index: 10;
                border-radius: 50%;
                padding: 2px 6px;
                font-size: 12px;
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
                            <li class="breadcrumb-item"><a
                                        href="<?php echo $_SESSION['dashboard_page'] ?? '#' ?>">Home</a></li>
                            <li class="breadcrumb-item"><span id="main_menu"></span></li>
                        </ol>
                    </div>

                    <div class="card shadow mb-4">
                        <div class="card-body">
                            <div class="row g-3 align-items-end">

                                <div class="col-md-2">
                                    <div class="form-group">
                                        <label for="doc_no" class="form-label">เลขที่เอกสาร</label>
                                        <input type="text" id="doc_no" name="doc_no" class="form-control" readonly>
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

                                <div class="col-md-2">
                                    <div class="form-group">
                                        <label for="employee_fullname" class="control-label">พนักงาน</label>
                                        <input type="text" id="employee_fullname" name="employee_fullname"
                                               class="form-control"
                                               autocomplete="off" required readonly>
                                        <input type="hidden" id="emp_id" name="emp_id">
                                    </div>
                                </div>

                                <div class="col-md-2">
                                    <div class="form-group">
                                        <label for="salary_type" class="form-label">ประเภทพนักงาน</label>
                                        <input type="text" id="salary_type" name="salary_type" class="form-control"
                                               readonly>
                                    </div>
                                </div>

                                <div class="col-md-2">
                                    <div class="form-group">
                                        <label for="salary" class="form-label">เงินเดือน/ค่าจ้าง</label>
                                        <input type="text" id="salary" name="salary" class="form-control" readonly>
                                    </div>
                                </div>

                            </div>

                            <div class="row mt-3">
                                <div class="col-md-2">
                                    <div class="form-group has-success">
                                        <label for="payroll_month" class="control-label">เดือน</label>
                                        <select id="payroll_month" name="payroll_month"
                                                class="form-control"
                                                data-live-search="true" required readonly>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-2">
                                    <div class="form-group has-success">
                                        <label for="payroll_year" class="control-label">ปี</label>
                                        <select id="payroll_year" name="payroll_year"
                                                class="form-control"
                                                data-live-search="true" required readonly>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-2">
                                    <div class="form-group">
                                        <label for="work_day_month" class="control-label">จำนวนวันในเดือน</label>
                                        <input type="text" id="work_day_month" name="work_day_month"
                                               class="form-control" readonly>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label for="payment_method" class="form-label fw-semibold mb-2">
                                        วิธีการรับเงิน
                                    </label>
                                    <div class="d-flex align-items-center flex-wrap">

                                        <div class="form-check form-check-inline d-flex align-items-center me-2">
                                            <input class="form-check-input me-1" type="radio"
                                                   name="payment_method_radio"
                                                   id="method_transfer" value="โอนเงิน" readonly>
                                            <label class="form-check-label" for="method_transfer">💳 โอนเงิน
                                                หมายเลขบัญชีฯ</label>
                                        </div>

                                        <input type="text" class="form-control ms-2 me-2" name="bank_no"
                                               id="bank_no"
                                               placeholder="" readonly style="width: 200px;">&nbsp;

                                        <div class="form-check form-check-inline d-flex align-items-center">
                                            <input class="form-check-input me-1" type="radio"
                                                   name="payment_method_radio"
                                                   id="method_cash" value="เงินสด" checked>
                                            <label class="form-check-label" for="method_cash">💵 เงินสด</label>
                                        </div>

                                        <input type="text" class="form-control ms-2 me-2" name="payment_method"
                                               id="payment_method"
                                               placeholder="" readonly style="width: 200px;">
                                    </div>
                                </div>
                            </div>

                            <hr>

                            <div class="table-responsive">
                                <table id='detailTable' class='display dataTable table table-bordered'>
                                    <thead>
                                    <tr>
                                        <th style="width: 20%;">รายการ (รายได้/รายการหักเงิน)</th>
                                        <th style="width: 10%;">ประเภท</th>
                                        <th style="width: 15%;">จำนวน</th>
                                        <th style="width: 15%;">จำนวนเงิน</th>
                                        <th style="width: 15%;">รวมเงิน</th>
                                        <th style="width: 20%;">หมายเหตุ</th>
                                        <!--th style="width: 5%;">ลบ</th-->
                                    </tr>
                                    </thead>
                                    <tbody>
                                    </tbody>
                                    <tfoot>
                                    <tr>
                                        <td colspan="4" class="text-right"><strong>รวมเงิน :</strong></td>
                                        <td><input type="text" class="form-control text-right" id="total_amount"
                                                   readonly>
                                        </td>
                                        <td></td>
                                    </tr>
                                    </tfoot>
                                </table>
                            </div>
                            <br>

                            <div class="mb-4">
                                <h5 class="text-primary mb-2">ไฟล์แนบ</h5>
                                <div id="existing-files-preview" class="row mt-2"></div>
                                <hr>

                                <!-- div class="form-group mt-3">
                                    <label for="file_attach_input" class="form-label fw-semibold">เพิ่มไฟล์ใหม่ (รูปภาพ/PDF):</label>
                                    <input type="file" id="file_attach_input" class="form-control" multiple accept="image/*,application/pdf">
                                </div-->

                                <div id="new-files-preview" class="row mt-2"></div>

                            </div>
                            <input type="hidden" id="deleted_images" name="deleted_images" value="">

                            <div class="modal-footer justify-content-end">
                                <input type="hidden" name="action" id="action" value=""/>
                                <!--button type="submit" name="save" id="save" class="btn btn-primary">
                                    บันทึก <i class="fa fa-save"></i>
                                </button-->
                                <button type="button" id="printSlipBtn" class="btn btn-info ml-2">
                                    พิมพ์สลิป <i class="fa fa-print"></i>
                                </button>
                                <button type="button" class="btn btn-danger ml-2" onclick="closeAndReload()">
                                    ปิด <i class="fa fa-times"></i>
                                </button>
                            </div>

                        </div>
                    </div>

                    <div class="modal fade" id="SearchEmployeeModal">
                        <div class="modal-dialog modal-lg">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h4 class="modal-title">เลือกพนักงาน</h4>
                                    <button type="button" class="close" data-dismiss="modal"
                                            aria-hidden="true">×
                                    </button>
                                </div>
                                <div class="modal-body">
                                    <div class="table-responsive">
                                        <table id="TableEmployeeList" class="display table table-bordered" width="100%">
                                            <thead>
                                            <tr>
                                                <th>รหัสพนักงาน</th>
                                                <th>ชื่อพนักงาน</th>
                                                <th>Action</th>
                                            </tr>
                                            </thead>
                                            <tbody>
                                            </tbody>
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

    <?php
    include('includes/Modal-Logout.php');
    include('includes/Footer.php');
    ?>

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
    <script src="js/modal/show_employee_payroll_modal.js"></script>

    <script>
        let detailItems = [];
        let currentRowForSelection = null;

        function addNewDetailRow() {
            const isFirstRow = $('#detailTable tbody tr').length === 0;
            const newRow = `
                <tr>
                    <td>
                        <div class="d-flex">
                            <input type="text" class="form-control icd_type_desc" value="${isFirstRow ? 'เงินรายได้' : ''}" readonly style="flex: 1;">
                            <a href="#itemModal" data-toggle="modal" class="btn btn-primary ml-2 btn-select-icd_type" style="white-space: nowrap;" title="เลือกรายการ">
                                <i class="fa fa-search"></i>
                            </a>
                        </div>
                    </td>
                    <td>
                        <input type="hidden" class="form-control icd_type_id" value="${isFirstRow ? 'IC-0001' : ''}" readonly>
                        <input type="hidden" class="form-control icd_type_sign" value="${isFirstRow ? '+' : ''}" readonly>
                        <input type="text" class="form-control icd_type_sign_desc" value="${isFirstRow ? 'รายรับ' : ''}" readonly style="flex: 1;">
                    </td>
                    <td>
                        <input type="number" class="form-control text-right item-quantity" min="0" step="0.01" value="0" readonly>
                    </td>
                    <td>
                        <input type="number" class="form-control text-right item-amount-per-unit" min="0" step="0.01" value="0" readonly>
                    </td>
                    <td>
                        <input type="number" class="form-control text-right item-total-amount" value="0.00" readonly tabindex="-1">
                    </td>
                    <td>
                        <input type="text" class="form-control item-remark" placeholder="" readonly>
                    </td>
                    <!--td class="text-center">
                        <button class="btn btn-danger btn-sm rounded-circle remove-row" type="button" title="ลบรายการนี้">
                            <i class="fas fa-trash-alt"></i>
                        </button>
                    </td-->
                </tr>
            `;
            $('#detailTable tbody').append(newRow);
            calculateTotalAmount();
        }

        function updateWorkDayMonth() {
            const selectedMonth = parseInt($('#payroll_month').val());
            const selectedYear = parseInt($('#payroll_year').val());

            if (!isNaN(selectedMonth) && !isNaN(selectedYear) && selectedMonth > 0 && selectedYear > 0) {
                const daysInMonth = new Date(selectedYear, selectedMonth, 0).getDate();
                $('#work_day_month').val(daysInMonth);
            } else {
                $('#work_day_month').val('');
            }
        }

        function loadPayrollMasterData(doc_no) {
            $.ajax({
                url: 'model/manage_payroll_data_detail_process.php',
                method: 'POST',
                data: { action: 'GET_DATA_BY_DOC_NO', doc_no: doc_no },
                dataType: 'json',
                success: function(response) {
                    if (response.status === 'success' && response.data) {
                        const masterData = response.data;
                        const fileAttach = masterData.file_attach;

                        if (fileAttach) {
                            const files = fileAttach.split(',');
                            const previewArea = $('#existing-files-preview');
                            previewArea.empty();

                            files.forEach(filename => {
                                if (filename.trim() === '') return;

                                const filePath = `uploads/payroll/${filename}`;
                                const isPdf = filename.toLowerCase().endsWith('.pdf');
                                let previewElement;

                                if (isPdf) {
                                    previewElement = `
                                        <div class="col-md-2 file-preview-box" data-filename="${filename}">
                                            <button type="button" class="btn btn-danger btn-sm remove-btn remove-existing-img" data-filename="${filename}">&times;</button>
                                            <a href="${filePath}" target="_blank" class="d-block p-2 text-center border rounded" style="height: 120px; display: flex; flex-direction: column; justify-content: center; align-items: center;">
                                                <i class="fa fa-file-pdf fa-2x text-danger"></i>
                                                <small style="word-break: break-word;">${filename}</small>
                                            </a>
                                        </div>
                                    `;
                                } else {
                                    previewElement = `
                                        <div class="col-md-2 file-preview-box" data-filename="${filename}">
                                            <button type="button" class="btn btn-danger btn-sm remove-btn remove-existing-img" data-filename="${filename}">&times;</button>
                                            <a href="${filePath}" target="_blank">
                                                <img src="${filePath}" class="img-thumbnail" style="width:100%; height:120px; object-fit:cover;">
                                            </a>
                                        </div>
                                    `;
                                }
                                previewArea.append(previewElement);
                            });
                        }
                    } else {
                        alertify.error("ไม่สามารถโหลดข้อมูลหลักได้: " + response.message);
                    }
                },
                error: function() {
                    alertify.error("เกิดข้อผิดพลาดในการเชื่อมต่อเพื่อโหลดข้อมูล");
                }
            });
        }

        $(document).ready(function () {
            $('#doc_date').datepicker({
                format: "dd-mm-yyyy",
                todayHighlight: true,
                language: "th",
                autoclose: true
            });

            const months = [
                {value: 1, text: 'มกราคม'}, {value: 2, text: 'กุมภาพันธ์'}, {value: 3, text: 'มีนาคม'},
                {value: 4, text: 'เมษายน'}, {value: 5, text: 'พฤษภาคม'}, {value: 6, text: 'มิถุนายน'},
                {value: 7, text: 'กรกฎาคม'}, {value: 8, text: 'สิงหาคม'}, {value: 9, text: 'กันยายน'},
                {value: 10, text: 'ตุลาคม'}, {value: 11, text: 'พฤศจิกายน'}, {value: 12, text: 'ธันวาคม'}
            ];
            const currentMonth = new Date().getMonth() + 1;
            let monthOptions = '<option value="">-- เลือกเดือน --</option>';
            months.forEach(month => {
                monthOptions += `<option value="${month.value}" ${month.value === currentMonth ? 'selected' : ''}>${month.text}</option>`;
            });
            $('#payroll_month').html(monthOptions);

            const currentYear = new Date().getFullYear();
            let yearOptions = '<option value="">-- เลือกปี --</option>';
            for (let i = currentYear - 1; i <= currentYear + 5; i++) {
                yearOptions += `<option value="${i}" ${i === currentYear ? 'selected' : ''}>${i}</option>`;
            }
            $('#payroll_year').html(yearOptions);

            let urlParams = new URLSearchParams(window.location.search);
            $("#sub_menu").html(urlParams.get("sub_menu") || "");
            $("#main_menu").html(urlParams.get("main_menu") || "");
            $('#action').val(urlParams.get("action"));
            $('#doc_no').val(urlParams.get("doc_no"));
            $('#doc_date').val(urlParams.get("doc_date"));
            $('#emp_id').val(urlParams.get("emp_id"));
            $('#employee_fullname').val(urlParams.get("employee_fullname"));

            const paymentMethodFromDB = urlParams.get("payment_method");
            $('#payment_method').val(paymentMethodFromDB);
            if (paymentMethodFromDB === 'โอนเงิน') {
                $('#method_transfer').prop('checked', true);
            } else {
                $('#method_cash').prop('checked', true);
            }

            $('#bank_no').val(urlParams.get("bank_no"));
            const salaryTypeUrl = urlParams.get("salary_type");
            const salaryValue = urlParams.get("salary");

            if (salaryTypeUrl === 'D') {
                $('#salary_type').val('รายวัน');
            } else if (salaryTypeUrl === 'M') {
                $('#salary_type').val('รายเดือน');
            } else {
                $('#salary_type').val('');
            }
            if (salaryValue) {
                $('#salary').val(parseFloat(salaryValue).toFixed(2));
            }

            const payrollMonthUrl = urlParams.get("payroll_month");
            const payrollYearUrl = urlParams.get("payroll_year");
            if (payrollMonthUrl) $('#payroll_month').val(payrollMonthUrl);
            if (payrollYearUrl) $('#payroll_year').val(payrollYearUrl);

            const action = urlParams.get("action");
            const docNo = urlParams.get("doc_no");

            if (action === 'ADD') {
                addNewDetailRow();
            } else if (docNo) {
                loadPayrollMasterData(docNo);
                loadPayrollData(docNo);
            }

            updateWorkDayMonth();
            $('#payroll_month, #payroll_year').on('change', function () {
                updateWorkDayMonth();
            });

            $('#addRow').on('click', function () {
                addNewDetailRow();
            });

            $(document).on('click', '.remove-row', function () {
                $(this).closest('tr').remove();
                calculateTotalAmount();
            });

            $(document).on('input', '.item-quantity, .item-amount-per-unit', function () {
                const row = $(this).closest('tr');
                const quantity = parseFloat(row.find('.item-quantity').val()) || 0;
                const amountPerUnit = parseFloat(row.find('.item-amount-per-unit').val()) || 0;
                const totalAmount = quantity * amountPerUnit;
                row.find('.item-total-amount').val(totalAmount.toFixed(2));
                calculateTotalAmount();
            });

            let incomeDeductDataTable;
            $('#itemModal').on('shown.bs.modal', function () {
                if (!incomeDeductDataTable) {
                    incomeDeductDataTable = $('#incomedeductTable').DataTable({
                        "processing": true,
                        "serverSide": false,
                        "ajax": {
                            "url": "model/get_income_deduct.php",
                            "type": "GET",
                            "dataSrc": ""
                        },
                        "columns": [
                            {"data": "icd_type_id"},
                            {"data": "icd_type_desc"},
                            {"data": "icd_type_sign"},
                            {"data": "icd_type_sign_desc"},
                            {
                                "data": null,
                                "defaultContent": '<button class="btn btn-sm btn-primary select-this">เลือก</button>',
                                "orderable": false
                            }
                        ],
                        "createdRow": function (row, data, dataIndex) {
                            $(row).find('.select-this').attr({
                                'data-code': data.icd_type_id,
                                'data-desc': data.icd_type_desc,
                                'data-sign': data.icd_type_sign,
                                'data-sign_desc': data.icd_type_sign_desc
                            });
                        }
                    });
                } else {
                    incomeDeductDataTable.ajax.reload(null, false);
                }
            });
        });

        function calculateTotalAmount() {
            let total = 0;
            $('#detailTable tbody tr').each(function () {
                const amount = parseFloat($(this).find('.item-total-amount').val()) || 0;
                const sign = $(this).find('.icd_type_sign').val();

                if (sign === '+') {
                    total += amount;
                } else if (sign === '-') {
                    total -= amount;
                }
            });
            $('#total_amount').val(total.toFixed(2));
        }

        function loadPayrollData(doc_no) {
            // This function now only needs to load the detail table
            $.ajax({
                url: 'model/manage_payroll_detail_process.php',
                method: 'POST',
                contentType: 'application/json',
                data: JSON.stringify({action: 'GET_DETAIL_DATA', doc_no: doc_no}),
                dataType: 'json',
                success: function (response) {
                    if (response.success && response.data) {
                        const details = response.data.details;
                        $('#detailTable tbody').empty();
                        details.forEach(item => {
                            const newRow = `
                                <tr>
                                    <td>
                                        <div class="d-flex">
                                            <input type="text" class="form-control icd_type_desc" value="${item.icd_type_desc || ''}" readonly style="flex: 1;">
                                            <!-- a href="#itemModal" data-toggle="modal" class="btn btn-primary ml-2 btn-select-icd_type" style="white-space: nowrap;" title="เลือกรายการ"><i class="fa fa-search"></i></a-->
                                        </div>
                                    </td>
                                    <td>
                                        <input type="hidden" class="form-control icd_type_id" value="${item.icd_type_id || ''}" readonly>
                                        <input type="hidden" class="form-control icd_type_sign" value="${item.icd_type_sign || ''}" readonly>
                                        <input type="text" class="form-control icd_type_sign_desc" value="${item.icd_type_sign_desc || ''}" readonly style="flex: 1;">
                                    </td>
                                    <td><input type="number" class="form-control text-right item-quantity" min="0" step="0.01" value="${item.quantity || 0}" required readonly></td>
                                    <td><input type="number" class="form-control text-right item-amount-per-unit" min="0" step="0.01" value="${item.amount_per_unit || 0}" readonly></td>
                                    <td><input type="number" class="form-control text-right item-total-amount" value="${(item.amount || 0).toFixed(2)}" readonly></td>
                                    <td><input type="text" class="form-control item-remark" value="${item.remark || ''}" placeholder="" readonly></td>
                                    <!--td class="text-center"><button class="btn btn-danger btn-sm rounded-circle remove-row" type="button" title="ลบรายการนี้"><i class="fas fa-trash-alt"></i></button></td-->
                                </tr>
                            `;
                            $('#detailTable tbody').append(newRow);
                        });
                        calculateTotalAmount();
                    } else {
                        alertify.error("ไม่พบข้อมูลรายการเงินเดือน: " + response.message);
                    }
                },
                error: function () {
                    alertify.error("ไม่สามารถโหลดข้อมูลรายการเงินเดือนได้");
                }
            });
        }

        $('#save').on('click', async function (e) {
            e.preventDefault();
            if (!$('#doc_date').val() || !$('#emp_id').val() || !$('#payroll_month').val() || !$('#payroll_year').val()) {
                alertify.error('กรุณากรอกข้อมูลหลักให้ครบถ้วน');
                return;
            }
            const details = [];
            let isValidDetails = true;
            $('#detailTable tbody tr').each(function () {
                const icd_type_id = $(this).find('.icd_type_id').val();
                if (!icd_type_id) {
                    isValidDetails = false;
                    return false;
                }
                details.push({
                    icd_type_id: icd_type_id,
                    icd_type_desc: $(this).find('.icd_type_desc').val(),
                    icd_type_sign: $(this).find('.icd_type_sign').val(),
                    icd_type_sign_desc: $(this).find('.icd_type_sign_desc').val(),
                    quantity: parseFloat($(this).find('.item-quantity').val()),
                    amount_per_unit: parseFloat($(this).find('.item-amount-per-unit').val()),
                    amount: parseFloat($(this).find('.item-total-amount').val()),
                    remark: $(this).find('.item-remark').val()
                });
            });
            if (!isValidDetails) {
                alertify.error("กรุณากรอกข้อมูลรายการเงินเดือน/หักให้ครบถ้วน");
                return;
            }
            $('#save').prop('disabled', true);
            let newlyUploadedFiles = [];
            try {
                newlyUploadedFiles = await uploadImages();
            } catch (error) {
                alertify.error('เกิดข้อผิดพลาดในการอัปโหลดไฟล์ใหม่: ' + error.message);
                $('#save').prop('disabled', false);
                return;
            }
            let existingFilesKept = [];
            $('#existing-files-preview .file-preview-box').each(function() {
                existingFilesKept.push($(this).data('filename'));
            });
            const allFiles = [...existingFilesKept, ...newlyUploadedFiles];
            const payload = {
                action: $('#action').val(),
                doc_no: $('#doc_no').val(),
                doc_date: $('#doc_date').val(),
                emp_id: $('#emp_id').val(),
                employee_fullname: $('#employee_fullname').val(),
                payroll_month: $('#payroll_month').val(),
                payroll_year: $('#payroll_year').val(),
                payment_method: $('#payment_method').val(),
                bank_no: $('#bank_no').val(),
                work_day_month: $('#work_day_month').val(),
                file_attach: allFiles.join(','),
                deleted_files: $('#deleted_images').val(),
                details: details
            };
            $.ajax({
                url: 'model/manage_payroll_data_detail_process.php',
                method: 'POST',
                contentType: 'application/json',
                data: JSON.stringify(payload),
                dataType: 'json',
                success: function (response) {
                    if (response.status === 'success') {
                        alertify.success(response.message);
                        if (response.doc_no) $('#doc_no').val(response.doc_no);
                    } else {
                        alertify.error('ข้อผิดพลาด: ' + response.message);
                    }
                    $('#save').prop('disabled', false);
                },
                error: function () {
                    alertify.error("เกิดข้อผิดพลาดในการบันทึกข้อมูล");
                    $('#save').prop('disabled', false);
                }
            });
        });

        $(document).on('click', '.btn-select-icd_type', function () {
            currentRowForSelection = $(this).closest('tr');
        });

        $(document).on('click', '#incomedeductTable .select-this', function () {
            const code = $(this).data('code');
            const desc = $(this).data('desc');
            const sign = $(this).data('sign');
            const sign_desc = $(this).data('sign_desc');
            if (currentRowForSelection) {
                currentRowForSelection.find('.icd_type_id').val(code);
                currentRowForSelection.find('.icd_type_desc').val(desc);
                currentRowForSelection.find('.icd_type_sign').val(sign);
                currentRowForSelection.find('.icd_type_sign_desc').val(sign_desc);
                const quantity = parseFloat(currentRowForSelection.find('.item-quantity').val()) || 0;
                const amountPerUnit = parseFloat(currentRowForSelection.find('.item-amount-per-unit').val()) || 0;
                currentRowForSelection.find('.item-total-amount').val((quantity * amountPerUnit).toFixed(2));
                calculateTotalAmount();
            }
            $('#itemModal').modal('hide');
        });

        function closeAndReload() {
            if (window.opener && window.opener.location) {
                window.opener.location.reload();
            }
            window.close();
        }

        $('#printSlipBtn').on('click', function () {
            const doc_no = $('#doc_no').val();
            if(doc_no) window.open("print_slip_pdf?doc_no=" + encodeURIComponent(doc_no), "_blank");
        });

        document.addEventListener('DOMContentLoaded', function () {
            const radioButtons = document.querySelectorAll('input[name="payment_method_radio"]');
            const paymentMethodInput = document.getElementById('payment_method');
            function updatePaymentMethodInput() {
                radioButtons.forEach(radio => {
                    if (radio.checked) {
                        paymentMethodInput.value = radio.value;
                        paymentMethodInput.setAttribute('readonly', true);
                    }
                });
            }
            radioButtons.forEach(radio => radio.addEventListener('change', updatePaymentMethodInput));
            updatePaymentMethodInput();
        });

        let newUploadedImages = [];
        $('#file_attach_input').on('change', function (e) {
            const files = Array.from(e.target.files);
            const previewArea = $('#new-files-preview');
            previewArea.empty();
            newUploadedImages = [];
            files.forEach((file) => {
                const fileIndex = newUploadedImages.push(file) - 1;
                const filePreviewBox = document.createElement('div');
                filePreviewBox.className = 'col-md-2 file-preview-box';
                const removeButton = document.createElement('button');
                removeButton.type = 'button';
                removeButton.className = 'btn btn-danger btn-sm remove-btn remove-new-img';
                removeButton.setAttribute('data-file-index', fileIndex);
                removeButton.innerHTML = '&times;';
                filePreviewBox.appendChild(removeButton);

                if (file.type.startsWith('image/')) {
                    const reader = new FileReader();
                    reader.onload = function (e) {
                        const imgLink = document.createElement('a');
                        imgLink.href = e.target.result;
                        imgLink.target = '_blank';
                        const img = document.createElement('img');
                        img.src = e.target.result;
                        img.className = 'img-thumbnail';
                        img.style.cssText = 'width:100%; height:120px; object-fit:cover;';
                        imgLink.appendChild(img);
                        filePreviewBox.appendChild(imgLink);
                    };
                    reader.readAsDataURL(file);
                } else if (file.type === 'application/pdf') {
                    const pdfPlaceholder = document.createElement('a');
                    pdfPlaceholder.href = URL.createObjectURL(file);
                    pdfPlaceholder.target = '_blank';
                    pdfPlaceholder.className = 'd-block p-2 text-center border rounded';
                    pdfPlaceholder.style.cssText = 'height: 120px; display: flex; flex-direction: column; justify-content: center; align-items: center; text-decoration: none; color: inherit;';
                    pdfPlaceholder.innerHTML = `<i class="fa fa-file-pdf fa-2x text-danger"></i><small style="word-break: break-word;">${file.name}</small>`;
                    filePreviewBox.appendChild(pdfPlaceholder);
                }
                previewArea.append(filePreviewBox);
            });
        });

        $('#new-files-preview').on('click', '.remove-new-img', function () {
            const fileIndex = parseInt($(this).attr('data-file-index'));
            newUploadedImages.splice(fileIndex, 1, null); // Replace with null to not mess up indices
            $(this).closest('.file-preview-box').remove();
        });

        $(document).on('click', '.remove-existing-img', function() {
            const filename = $(this).data('filename');
            const hiddenInput = $('#deleted_images');
            let currentDeleted = hiddenInput.val();
            hiddenInput.val(currentDeleted ? `${currentDeleted},${filename}` : filename);
            $(this).closest('.file-preview-box').remove();
        });

        async function uploadImages() {
            const formData = new FormData();
            const validFiles = newUploadedImages.filter(f => f); // Filter out null values
            if (validFiles.length === 0) return Promise.resolve([]);

            validFiles.forEach(file => formData.append('images[]', file));

            const response = await fetch('upload_img_payroll.php', { method: 'POST', body: formData });
            if (!response.ok) throw new Error(`HTTP error! status: ${response.status}`);

            const result = await response.json();
            if (result.status === 'success') return result.filenames;

            throw new Error(result.message || 'Image upload failed on server.');
        }
    </script>
    </body>
    </html>

    <?php
}
?>