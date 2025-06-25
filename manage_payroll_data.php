<?php
// manage_payroll_data.php - สำหรับจัดการข้อมูลเงินเดือนพนักงานรายวัน
session_start();
error_reporting(0); // ปิดการแสดง error ใน Production environment
include('includes/Header.php'); // Only include it once

// include('config/connect_db.php'); // ไม่ได้ใช้ PDO ตรงๆ ในหน้านี้ แต่จะเรียกใช้ใน process file

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

            /* Fix for DataTables search input width */
            div.dataTables_wrapper div.dataTables_filter input {
                width: auto;
                margin-left: 0.5em;
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

                                <div class="col-md-1 d-flex align-items-end">
                                    <div class="form-group">
                                        <label class="control-label" style="visibility:hidden;">เลือกพนักงาน</label>
                                        <a data-toggle="modal" href="#SearchEmployeeModal" class="btn btn-primary">
                                            Click <i class="fa fa-search" aria-hidden="true"></i>
                                        </a>
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
                                                data-live-search="true" required>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-2">
                                    <div class="form-group has-success">
                                        <label for="payroll_year" class="control-label">ปี</label>
                                        <select id="payroll_year" name="payroll_year"
                                                class="form-control"
                                                data-live-search="true" required>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-2">
                                    <div class="form-group">
                                        <label for="work_day_month" class="control-label">จำนวนวันในเดือน</label>
                                        <input type="text" id="work_day_month" name="work_day_month" class="form-control" readonly>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label for="payment_method" class="form-label fw-semibold mb-2">
                                        วิธีการรับเงิน
                                    </label>
                                    <div class="d-flex align-items-center flex-wrap">

                                        <!-- 💳 โอนเงิน -->
                                        <div class="form-check form-check-inline d-flex align-items-center me-2">
                                            <input class="form-check-input me-1" type="radio"
                                                   name="payment_method_radio"
                                                   id="method_transfer" value="โอนเงิน">
                                            <label class="form-check-label" for="method_transfer">💳 โอนเงิน   หมายเลขบัญชีฯ</label>
                                        </div>

                                        <!-- ช่องกรอกเลขบัญชี -->
                                        <input type="text" class="form-control ms-2 me-2" name="bank_no"
                                               id="bank_no"
                                               placeholder="" style="width: 200px;">&nbsp;

                                        <!-- 💵 เงินสด -->
                                        <div class="form-check form-check-inline d-flex align-items-center">
                                            <input class="form-check-input me-1" type="radio"
                                                   name="payment_method_radio"
                                                   id="method_cash" value="เงินสด" checked>
                                            <label class="form-check-label" for="method_cash">💵 เงินสด</label>
                                        </div>

                                        <!-- ช่องแสดงค่าที่เลือก -->
                                        <input type="text" class="form-control ms-2 me-2" name="payment_method"
                                               id="payment_method"
                                               placeholder="" style="width: 200px;">
                                    </div>
                                </div>

                            </div>


                            <hr>

                            <div class="d-flex align-items-center mt-4 mb-3">
                                <h5 class="text-primary mb-0 mr-2">รายการรายได้/รายหัก</h5>
                                <button class="btn btn-primary" id="addRow" type="button">+ เพิ่มรายการ</button>
                            </div>

                            <div class="table-responsive">
                                <table id='detailTable' class='display dataTable table table-bordered'>
                                    <thead>
                                    <tr>
                                        <th style="width: 25%;">รายการ (รายได้/รายการหักเงิน)</th>
                                        <th style="width: 15%;">ประเภท</th>
                                        <th style="width: 20%;">จำนวน</th>
                                        <th style="width: 20%;">จำนวนเงิน</th>
                                        <th style="width: 20%;">รวมเงิน</th>
                                        <th style="width: 10%;">ลบ</th>
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

                            <div class="modal-footer justify-content-end">
                                <input type="hidden" name="action" id="action" value=""/>
                                <button type="submit" name="save" id="save" class="btn btn-primary">
                                    บันทึก <i class="fa fa-save"></i>
                                </button>
                                <button type="button" id="printSlipBtn" class="btn btn-info ml-2">
                                    พิมพ์สลิป <i class="fa fa-print"></i>
                                </button>
                                <button type="button" class="btn btn-danger ml-2" onclick="closeAndReload()">
                                    ปิด <i class="fa fa-window-close"></i>
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

                    <div class="modal fade" id="itemModal" tabindex="-1" role="dialog" aria-labelledby="itemModalLabel"
                         aria-hidden="true">
                        <div class="modal-dialog modal-lg" role="document">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title">เลือกรายการรายได้/รายการหัก</h5>
                                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                        <span aria-hidden="true">&times;</span>
                                    </button>
                                </div>
                                <div class="modal-body">
                                    <div class="table-responsive">
                                        <table class="table table-bordered display" id="incomedeductTable" width="100%">
                                            <thead>
                                            <tr>
                                                <th>รหัส</th>
                                                <th>รายละเอียด</th>
                                                <th>+/-</th>
                                                <th>ประเภท</th>
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
    <script src="vendor/date-picker-1.9/locales/bootstrap-datepicker.th.min.js"></link>
        <link href="vendor/date-picker-1.9/css/bootstrap-datepicker.css" rel="stylesheet"/>

        <script src="vendor/datatables/v11/bootbox.min.js"></script>
        <script src="vendor/datatables/v11/jquery.dataTables.min.js"></script>
    <link rel="stylesheet" href="vendor/datatables/v11/jquery.dataTables.min.css"/>
    <link rel="stylesheet" href="vendor/datatables/v11/buttons.dataTables.min.css"/>

    <script src="js/modal/show_employee_payroll_modal.js"></script>


    <script>
        // Global variable to keep track of added detail items
        let detailItems = [];
        let currentRowForSelection = null; // To store the current row when selecting an income/deduction item

        // Function to add a new detail row to the table
        function addNewDetailRow() {
            // Check if it's the very first row being added
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
                        <input type="number" class="form-control text-right item-quantity" min="0" step="0.01" value="0" required>
                    </td>
                    <td>
                        <input type="number" class="form-control text-right item-amount-per-unit" min="0" step="0.01" value="0" required>
                    </td>
                    <td>
                        <input type="number" class="form-control text-right item-total-amount" value="0.00" readonly tabindex="-1">
                    </td>
                    <td class="text-center">
                        <button class="btn btn-danger btn-sm rounded-circle remove-row" type="button" title="ลบรายการนี้">
                            <i class="fas fa-trash-alt"></i>
                        </button>
                    </td>
                </tr>
            `;
            $('#detailTable tbody').append(newRow);
            calculateTotalAmount();
        }

        // START: ฟังก์ชันใหม่สำหรับคำนวณและแสดงจำนวนวันในเดือน
        function updateWorkDayMonth() {
            const selectedMonth = parseInt($('#payroll_month').val());
            const selectedYear = parseInt($('#payroll_year').val());

            if (!isNaN(selectedMonth) && !isNaN(selectedYear) && selectedMonth > 0 && selectedYear > 0) {
                // Month in Date object is 0-indexed (0-11), so use selectedMonth directly for new Date(year, month, 0)
                // new Date(year, month, 0) gives the last day of the *previous* month if 'month' is 0-indexed.
                // To get the last day of the *selected* month, use selectedMonth (1-indexed) directly with day 0.
                // Example: new Date(2025, 6, 0) for July 2025 will return June 30.
                // Correct way for 1-indexed month: new Date(year, month, 0).getDate()
                // For example, for July (7), new Date(2025, 7, 0) will give the last day of July.
                const daysInMonth = new Date(selectedYear, selectedMonth, 0).getDate();
                $('#work_day_month').val(daysInMonth);
            } else {
                $('#work_day_month').val(''); // Clear if month/year not selected or invalid
            }
        }
        // END: ฟังก์ชันใหม่สำหรับคำนวณและแสดงจำนวนวันในเดือน


        $(document).ready(function () {
            // Initialize Datepicker
            $('#doc_date').datepicker({
                format: "dd-mm-yyyy",
                todayHighlight: true,
                language: "th",
                autoclose: true
            });

            // Populate Payroll Month Dropdown
            const months = [
                {value: 1, text: 'มกราคม'}, {value: 2, text: 'กุมภาพันธ์'}, {value: 3, text: 'มีนาคม'},
                {value: 4, text: 'เมษายน'}, {value: 5, text: 'พฤษภาคม'}, {value: 6, text: 'มิถุนายน'},
                {value: 7, text: 'กรกฎาคม'}, {value: 8, text: 'สิงหาคม'}, {value: 9, text: 'กันยายน'},
                {value: 10, text: 'ตุลาคม'}, {value: 11, text: 'พฤศจิกายน'}, {value: 12, text: 'ธันวาคม'}
            ];
            const currentMonth = new Date().getMonth() + 1; // getMonth() is 0-indexed
            let monthOptions = '<option value="">-- เลือกเดือน --</option>';
            months.forEach(month => {
                monthOptions += `<option value="${month.value}" ${month.value === currentMonth ? 'selected' : ''}>${month.text}</option>`;
            });
            $('#payroll_month').html(monthOptions);

            // Populate Payroll Year Dropdown
            const currentYear = new Date().getFullYear();
            let yearOptions = '<option value="">-- เลือกปี --</option>';
            for (let i = currentYear - 1; i <= currentYear + 5; i++) {
                yearOptions += `<option value="${i}" ${i === currentYear ? 'selected' : ''}>${i}</option>`;
            }
            $('#payroll_year').html(yearOptions);

            // Parse URL parameters
            let urlParams = new URLSearchParams(window.location.search);
            $("#sub_menu").html(urlParams.get("sub_menu") || "");
            $("#main_menu").html(urlParams.get("main_menu") || "");
            $('#action').val(urlParams.get("action"));

            $('#doc_no').val(urlParams.get("doc_no"));
            $('#doc_date').val(urlParams.get("doc_date"));
            $('#emp_id').val(urlParams.get("emp_id"));
            $('#employee_fullname').val(urlParams.get("employee_fullname"));

            const paymentMethodFromDB = urlParams.get("payment_method");
            $('#payment_method').val(paymentMethodFromDB); // Set hidden field

            if (paymentMethodFromDB === 'โอนเงิน') {
                $('#method_transfer').prop('checked', true); // Check 'โอนเงิน' radio
            } else { // Default to 'เงินสด' if not 'โอนเงิน' or if value is 'เงินสด'
                $('#method_cash').prop('checked', true); // Check 'เงินสด' radio
            }

            $('#bank_no').val(urlParams.get("bank_no"));


            // Set salary_type and salary from URL params
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

            // *** START OF MODIFICATION: Set payroll_month and payroll_year from URL params ***
            const payrollMonthUrl = urlParams.get("payroll_month");
            const payrollYearUrl = urlParams.get("payroll_year");

            if (payrollMonthUrl) {
                $('#payroll_month').val(payrollMonthUrl);
            }
            if (payrollYearUrl) {
                $('#payroll_year').val(payrollYearUrl);
            }
            // *** END OF MODIFICATION ***


            // Check action from URL parameters
            const action = urlParams.get("action");
            const docNo = urlParams.get("doc_no");

            if (action === 'ADD') {
                addNewDetailRow();
            } else if (docNo) { // ถ้ามี docNo แสดงว่าเป็นโหมดแก้ไข
                loadPayrollData(docNo);
            }

            // START: เรียกใช้ฟังก์ชันอัปเดตจำนวนวันในเดือนเมื่อโหลดหน้าเว็บ
            updateWorkDayMonth();

            // START: เพิ่ม event listener เมื่อมีการเปลี่ยนเดือนหรือปี
            $('#payroll_month, #payroll_year').on('change', function () {
                updateWorkDayMonth();
            });
            // END: เพิ่ม event listener

            // Add new row to detail table (button click handler)
            $('#addRow').on('click', function () {
                addNewDetailRow();
            });

            // Remove detail row
            $(document).on('click', '.remove-row', function () {
                $(this).closest('tr').remove();
                calculateTotalAmount();
            });

            // Calculate item total and grand total on quantity/amount per unit change
            $(document).on('input', '.item-quantity, .item-amount-per-unit', function () {
                const row = $(this).closest('tr');
                const quantity = parseFloat(row.find('.item-quantity').val()) || 0;
                const amountPerUnit = parseFloat(row.find('.item-amount-per-unit').val()) || 0;
                const totalAmount = quantity * amountPerUnit;
                row.find('.item-total-amount').val(totalAmount.toFixed(2));
                calculateTotalAmount();
            });

            // Handle selection from Employee Modal
            // This part is handled by show_employee_payroll_modal.js,
            // so we only need to listen for the event it triggers.
            // The show_employee_payroll_modal.js file already updates #emp_id, #employee_fullname, #salary_type, #salary
            // upon selection, and hides the modal.

            // Initialize DataTables for modals
            let employeeDataTable; // This will now be managed by show_employee_payroll_modal.js
            let incomeDeductDataTable;

            // Initialize Employee DataTable when modal is shown (This part is primarily for reference,
            // the actual DataTable logic for employees is in show_employee_payroll_modal.js)
            $('#SearchEmployeeModal').on('shown.bs.modal', function () {
                // The DataTable for employees is initialized and managed by show_employee_payroll_modal.js
                // We don't need to re-initialize or reload it here, as show_employee_payroll_modal.js handles it.
                // However, ensure the script is loaded and functions correctly.
            });

            // Initialize Income/Deduct DataTable when modal is shown
            $('#itemModal').on('shown.bs.modal', function () {
                if (!incomeDeductDataTable) {
                    incomeDeductDataTable = $('#incomedeductTable').DataTable({
                        "processing": true,
                        "serverSide": false, // Usually, income/deduct types are not that many, so client-side is fine
                        "ajax": {
                            "url": "model/get_income_deduct.php", // Your API to fetch income/deduct data
                            "type": "GET",
                            "dataSrc": "" // Assuming your PHP returns a direct array of objects
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
                            // Add data attributes to the select button for easy retrieval
                            $(row).find('.select-this').attr({
                                'data-code': data.icd_type_id,
                                'data-desc': data.icd_type_desc,
                                'data-sign': data.icd_type_sign,
                                'data-sign_desc': data.icd_type_sign_desc
                            });
                        }
                    });
                } else {
                    incomeDeductDataTable.ajax.reload(null, false); // Reload data if already initialized
                }
            });

        }); // End of document.ready

        // Function to calculate total amount
        function calculateTotalAmount() {
            let total = 0;
            $('#detailTable tbody tr').each(function () {
                const amount = parseFloat($(this).find('.item-total-amount').val()) || 0;
                const sign = $(this).find('.icd_type_sign').val(); // Get the sign ('+' or '-')

                if (sign === '+') {
                    total += amount; // Add for income
                } else if (sign === '-') {
                    total -= amount; // Subtract for deduction
                }
            });
            $('#total_amount').val(total.toFixed(2));
        }

        // Function to load existing payroll data for editing
        function loadPayrollData(doc_no) {
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
                                    <a href="#itemModal" data-toggle="modal" class="btn btn-primary ml-2 btn-select-icd_type" style="white-space: nowrap;" title="เลือกรายการ">
                                        <i class="fa fa-search"></i>
                                    </a>
                                </div>
                            </td>
                            <td>
                                <input type="hidden" class="form-control icd_type_id" value="${item.icd_type_id || ''}" readonly>
                                <input type="hidden" class="form-control icd_type_sign" value="${item.icd_type_sign || ''}" readonly>
                                <input type="text" class="form-control icd_type_sign_desc" value="${item.icd_type_sign_desc || ''}" readonly style="flex: 1;">
                            </td>
                            <td><input type="number" class="form-control text-right item-quantity" min="0" step="0.01" value="${item.quantity || 0}" required></td>
                            <td><input type="number" class="form-control text-right item-amount-per-unit" min="0" step="0.01" value="${item.amount_per_unit || 0}" required></td>
                            <td><input type="number" class="form-control text-right item-total-amount" value="${(item.amount || 0).toFixed(2)}" readonly></td>
                            <td class="text-center"><button class="btn btn-danger btn-sm rounded-circle remove-row" type="button" title="ลบรายการนี้"><i class="fas fa-trash-alt"></i></button></td>
                        </tr>
                    `;
                            $('#detailTable tbody').append(newRow);
                        });
                        calculateTotalAmount();
                        updateWorkDayMonth(); // อัปเดตจำนวนวันเมื่อโหลดข้อมูล
                    } else {
                        alertify.error("ไม่พบข้อมูลเงินเดือน: " + response.message);
                    }
                },
                error: function (xhr, status, error) {
                    console.error("AJAX Error loading payroll data:", status, error);
                    alertify.error("ไม่สามารถโหลดข้อมูลเงินเดือนได้");
                }
            });
        }

        // Save Button Handler
        $('#save').on('click', function (e) {
            e.preventDefault();

            // Basic Validation
            if (!$('#doc_date').val() || !$('#emp_id').val() || !$('#payroll_month').val() || !$('#payroll_year').val()) {
                alertify.error('กรุณากรอกข้อมูลหลักให้ครบถ้วน (วันที่, พนักงาน, เดือน/ปีเงินเดือน)');
                return;
            }

            const details = [];
            let isValidDetails = true;
            $('#detailTable tbody tr').each(function () {
                const icd_type_id = $(this).find('.icd_type_id').val();
                const icd_type_desc = $(this).find('.icd_type_desc').val();
                const icd_type_sign = $(this).find('.icd_type_sign').val();
                const icd_type_sign_desc = $(this).find('.icd_type_sign_desc').val();
                const quantity = parseFloat($(this).find('.item-quantity').val());
                const amount_per_unit = parseFloat($(this).find('.item-amount-per-unit').val());
                const total_amount = parseFloat($(this).find('.item-total-amount').val());

                if (!icd_type_id || !icd_type_desc || isNaN(quantity) || quantity < 0 || isNaN(amount_per_unit) || amount_per_unit < 0 || isNaN(total_amount) || total_amount < 0) {
                    isValidDetails = false;
                    alertify.error("กรุณากรอกข้อมูลรายการเงินเดือน/หักให้ครบถ้วนและถูกต้อง");
                    return false; // Break .each loop
                }
                details.push({
                    icd_type_id: icd_type_id,
                    icd_type_desc: icd_type_desc,
                    icd_type_sign: icd_type_sign,
                    icd_type_sign_desc: icd_type_sign_desc,
                    quantity: quantity,
                    amount_per_unit: amount_per_unit,
                    amount: total_amount // Use total_amount as the final amount
                });
            });

            if (!isValidDetails || details.length === 0) {
                alertify.error("กรุณาเพิ่มอย่างน้อย 1 รายการเงินเดือน/หัก");
                return;
            }

            $('#save').prop('disabled', true); // Disable button to prevent multiple submissions

            const payload = {
                action: $('#action').val(), // 'ADD' or 'UPDATE'
                doc_no: $('#doc_no').val(),
                doc_date: $('#doc_date').val(),
                emp_id: $('#emp_id').val(),
                employee_fullname: $('#employee_fullname').val(),
                payroll_month: $('#payroll_month').val(),
                payroll_year: $('#payroll_year').val(),
                payment_method: $('#payment_method').val(),
                bank_no: $('#bank_no').val(),
                work_day_month: $('#work_day_month').val(), // *** เพิ่มค่าจำนวนวันในเดือนที่นี่ ***
                details: details
            };

            $.ajax({
                url: 'model/manage_payroll_data_detail_process.php',
                method: 'POST',
                contentType: 'application/json', // Send data as JSON
                data: JSON.stringify(payload),
                dataType: 'json',
                success: function (response) {
                    if (response.status === 'success') {
                        alertify.success(response.message);
                        // Optional: update doc_no if it was newly generated on ADD
                        if (response.doc_no) {
                            $('#doc_no').val(response.doc_no);
                        }
                        //closeAndReload();
                    } else {
                        alertify.error('ข้อผิดพลาด: ' + response.message);
                    }
                    $('#save').prop('disabled', false); // Re-enable button
                },
                error: function (xhr, status, error) {
                    console.error("AJAX Error:", status, error);
                    alertify.error("เกิดข้อผิดพลาดในการบันทึกข้อมูล: " + error);
                    $('#save').prop('disabled', false); // Re-enable button
                }
            });
        });

        // Event listener for selecting item from modal
        // Note: The event delegation is important for dynamically added rows
        $(document).on('click', '.btn-select-icd_type', function () {
            currentRowForSelection = $(this).closest('tr');
            // The itemModal will be shown via data-toggle="modal"
            // No need to call $('#itemModal').modal('show'); explicitly here.
        });


        // Handle selection from Income/Deduct Modal
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
                // Re-calculate row total if quantity/amount per unit were already entered
                const quantity = parseFloat(currentRowForSelection.find('.item-quantity').val()) || 0;
                const amountPerUnit = parseFloat(currentRowForSelection.find('.item-amount-per-unit').val()) || 0;
                const totalAmount = quantity * amountPerUnit;
                currentRowForSelection.find('.item-total-amount').val(totalAmount.toFixed(2));
                calculateTotalAmount(); // Update grand total
            }

            $('#itemModal').modal('hide');
        });

        // Function to close current page and reload previous page (assuming it's a list page)
        function closeAndReload() {
            // Check if opener exists and has reload function
            if (window.opener && window.opener.location) {
                window.opener.location.reload();
            }
            window.close(); // Close the current window
        }

    </script>

    <script>
        $('#printSlipBtn').on('click', function () {
            const doc_no = $('#doc_no').val();
            let url = "print_slip_pdf?doc_no=";
            window.open(url + encodeURIComponent(doc_no), "_blank");
        });
    </script>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const radioButtons = document.querySelectorAll('input[name="payment_method_radio"]');
            const paymentMethodInput = document.getElementById('payment_method');

            // Function to update the text input based on radio selection
            function updatePaymentMethodInput() {
                radioButtons.forEach(radio => {
                    if (radio.checked) {
                        paymentMethodInput.value = radio.value;
                        if (radio.value === 'เงินสด' || radio.value === 'โอนเงิน') {
                            paymentMethodInput.setAttribute('readonly', true);
                        } else {
                            paymentMethodInput.removeAttribute('readonly');
                            paymentMethodInput.focus(); // Focus on the input if "Other" is selected
                        }
                    }
                });
            }

            // Add event listeners to radio buttons
            radioButtons.forEach(radio => {
                radio.addEventListener('change', updatePaymentMethodInput);
            });

            // Initialize on page load (e.g., if "เงินสด" is checked by default)
            updatePaymentMethodInput();
        });
    </script>

    </body>
    </html>

    <?php
} // end else session check
?>