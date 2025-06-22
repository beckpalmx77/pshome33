<?php
include 'includes/Header.php';
// manage_payroll_data.php - สำหรับจัดการข้อมูลเงินเดือนพนักงานรายวัน
session_start();
error_reporting(0); // ปิดการแสดง error ใน Production environment
include('includes/Header.php');
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
                                        <label for="doc_no" class="form-label">เลขที่เอกสาร (Doc ID)</label>
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

                                <div class="col-md-2"> <div class="form-group">
                                        <label for="employee_fullname" class="control-label">พนักงาน</label>
                                        <input type="text" id="employee_fullname" name="employee_fullname"
                                               class="form-control"
                                               autocomplete="off" required readonly> <input type="hidden" id="emp_id" name="emp_id">
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
                                        <input type="text" id="salary_type" name="salary_type" class="form-control" readonly>
                                    </div>
                                </div>

                                <div class="col-md-2">
                                    <div class="form-group">
                                        <label for="salary" class="form-label">เงินเดือน/ค่าจ้าง</label>
                                        <input type="text" id="salary" name="salary" class="form-control" readonly>
                                    </div>
                                </div>

                            </div>


                            <div class="row mt-8">
                                <div class="col-md-3">
                                    <div class="form-group has-success">
                                        <label>เดือนเงินเดือน</label>
                                        <select id="payroll_month" name="payroll_month" class="form-select"
                                                required></select>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group has-success">
                                        <label>ปีเงินเดือน</label>
                                        <select id="payroll_year" name="payroll_year" class="form-select"
                                                required></select>
                                    </div>
                                </div>
                            </div>

                            <hr>

                            <div class="d-flex align-items-center mt-4 mb-3">
                                <h5 class="text-primary mb-0 mr-2">รายการรายได้/รายหัก</h5>
                                <button class="btn btn-primary" id="addRow" type="button">+ เพิ่มรายการ</button>
                            </div>

                            <table id='detailTable' class='display dataTable'>
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
                                    <td><input type="text" class="form-control text-right" id="total_amount" readonly>
                                    </td>
                                    <td></td>
                                </tr>
                                </tfoot>
                            </table>
                            <br>

                            <div class="modal-footer justify-content-right">
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

                    <div class="modal fade" id="SearchEmployeeModal">
                        <div class="modal-dialog modal-lg">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h4 class="modal-title">เลือกพนักงาน</h4>
                                    <button type="button" class="close" data-dismiss="modal"
                                            aria-hidden="true">×
                                    </button>
                                </div>
                                <div class="container"></div>
                                <div class="modal-body">
                                    <div class="modal-body">
                                        <table id="TableEmployeeList" class="display" width="100%">
                                            <thead>
                                            <tr>
                                                <th>รหัสพนักงาน</th>
                                                <th>ชื่อพนักงาน</th>
                                                <th>Action</th>
                                            </tr>
                                            </thead>
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
                                    <table class="table table-bordered" id="incomedeductTable">
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

    <?php
    include('includes/Modal-Logout.php'); // ถ้ามี Modal Logout
    include('includes/Footer.php');    // ถ้ามี Footer
    ?>

    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.bundle.min.js"></script>

    <script src="vendor/datatables/v11/bootbox.min.js"></script>
    <script src="vendor/datatables/v11/jquery.dataTables.min.js"></script>
    <link rel="stylesheet" href="vendor/datatables/v11/jquery.dataTables.min.css"/>
    <link rel="stylesheet" href="vendor/datatables/v11/buttons.dataTables.min.css"/>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.9.0/js/bootstrap-datepicker.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.9.0/locales/bootstrap-datepicker.th.min.js"></script>

    <link href="https://cdn.jsdelivr.net/npm/tom-select@2.3.1/dist/css/tom-select.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/tom-select@2.3.1/dist/js/tom-select.complete.min.js"></script>

    <script src="js/modal/show_employee_payroll_modal.js"></script>

    <script>
        // Global variable to keep track of added detail items
        let detailItems = [];
        let currentRowForSelection = null; // To store the current row when selecting employee

        // Function to add a new detail row to the table
        function addNewDetailRow() {
            const newRow = `
<tr>
    <td>
    <div class="d-flex">
      <input type="text" class="form-control icd_type_desc" value="" readonly style="flex: 1;">
      <a href="#itemModal" data-toggle="modal" class="btn btn-primary ml-2 btn-select-icd_type" style="white-space: nowrap;">
        <i class="fa fa-search"></i>
      </a>
    </div>
    </td>
    <td>
        <input type="hidden" class="form-control icd_type_id" value="" readonly>
        <input type="hidden" class="form-control icd_type_sign" value="" readonly>
        <input type="text" class="form-control icd_type_sign_desc" value="" readonly style="flex: 1;">
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

        $(document).ready(function () {
            // Initialize Datepicker
            $('.datepicker').datepicker({
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
                monthOptions += `<option><option value="${month.value}" ${month.value === currentMonth ? 'selected' : ''}>${month.text}</option>`;
            });
            $('#payroll_month').html(monthOptions);

            // Populate Payroll Year Dropdown
            const currentYear = new Date().getFullYear();
            let yearOptions = '<option value="">-- เลือกปี --</option>';
            for (let i = currentYear - 5; i <= currentYear + 5; i++) {
                yearOptions += `<option value="${i}" ${i === currentYear ? 'selected' : ''}>${i}</option>`;
            }
            $('#payroll_year').html(yearOptions);


            // Parse URL parameters
            let urlParams = new URLSearchParams(window.location.search);
            $("#sub_menu").html(urlParams.get("sub_menu") || "");
            $("#main_menu").html(urlParams.get("main_menu") || "");
            $('#action').val(urlParams.get("action"));

            // Check action from URL parameters
            const action = urlParams.get("action");
            const docNo = urlParams.get("doc_no");

            if (action === 'ADD') {
                addNewDetailRow(); // เรียกฟังก์ชันโดยตรง
            } else if (docNo) { // ถ้ามี docNo แสดงว่าเป็นโหมดแก้ไข
                loadPayrollData(docNo);
            }


            // Add new row to detail table (button click handler)
            $('#addRow').on('click', function () {
                addNewDetailRow(); // ปุ่มก็เรียกฟังก์ชันนี้เช่นกัน
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
            $(document).on('click', '.select-employee-btn', function () {
                const empId = $(this).data('id');
                const empName = $(this).data('name');
                const empCode = $(this).data('code');
                const salaryType = $(this).data('salary-type');
                const salary = $(this).data('salary');

                $('#emp_id').val(empId);
                $('#employee_fullname').val(`${empName} (${empCode})`);

                // Set salary_type and salary
                if (salaryType === 'D') {
                    $('#salary_type').val('รายวัน');
                } else if (salaryType === 'M') {
                    $('#salary_type').val('รายเดือน');
                } else {
                    $('#salary_type').val('');
                }
                $('#salary').val(parseFloat(salary).toFixed(2));

                $('#SearchEmployeeModal').modal('hide');
            });
        });

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
                // If sign is neither '+' nor '-', it will not affect the total.
            });
            $('#total_amount').val(total.toFixed(2));
        }

        // Function to load employee data for the modal table
        function loadEmployeeModalTable() {
            $.ajax({
                url: 'api/employees_api.php', // ตรวจสอบเส้นทางให้ถูกต้อง
                method: 'POST',
                data: {action: 'get_all'},
                dataType: 'json',
                success: function (response) {
                    const tableBody = $('#TableEmployeeList tbody');
                    tableBody.empty();
                    if (response.success && response.data.length > 0) {
                        response.data.forEach(emp => {
                            tableBody.append(`
                                <tr>
                                    <td>${emp.employee_code}</td>
                                    <td>${emp.first_name} ${emp.last_name}</td>
                                    <td>
                                        <button class="btn btn-sm btn-primary select-employee-btn"
                                                data-id="${emp.employee_id}"
                                                data-name="${emp.first_name} ${emp.last_name}"
                                                data-code="${emp.employee_code}"
                                                data-salary-type="${emp.salary_type}"
                                                data-salary="${emp.salary}">เลือก</button>
                                    </td>
                                </tr>
                            `);
                        });
                    } else {
                        tableBody.append('<tr><td colspan="3" class="text-center">ไม่พบข้อมูลพนักงาน</td></tr>');
                    }
                },
                error: function (xhr, status, error) {
                    console.error("AJAX Error loading employee modal data:", status, error);
                    alertify.error('ไม่สามารถโหลดข้อมูลพนักงานได้');
                }
            });
        }

        // Trigger load employee modal table when modal is shown
        $('#SearchEmployeeModal').on('show.bs.modal', function () {
            //loadEmployeeModalTable();
        });


        // Function to load existing payroll data for editing
        function loadPayrollData(doc_no) {
            $.ajax({
                url: 'api/payroll_api.php', // คุณจะต้องสร้างไฟล์นี้ หรือตรวจสอบว่ามีอยู่แล้ว
                method: 'POST',
                data: {action: 'get_single', doc_no: doc_no},
                dataType: 'json',
                success: function (response) {
                    if (response.success && response.data) {
                        const header = response.data.header;
                        const details = response.data.details;

                        $('#doc_no').val(header.doc_no);
                        $('#doc_date').val(header.doc_date); // Assuming format DD-MM-YYYY
                        $('#emp_id').val(header.emp_id);
                        $('#employee_fullname').val(header.employee_fullname); // You might need to fetch this based on emp_id
                        $('#payroll_month').val(header.payroll_month);
                        $('#payroll_year').val(header.payroll_year);

                        // If you handle salary_type and salary externally when loading for EDIT,
                        // ensure those external parts are called here.
                        // Example if you need to set them from loaded header data:
                        $('#salary_type').val(header.salary_type_desc); // Assuming header.salary_type_desc exists
                        $('#salary').val(parseFloat(header.salary).toFixed(2)); // Assuming header.salary exists


                        $('#detailTable tbody').empty();
                        details.forEach(item => {
                            const newRow = `
                                <tr>
                                    <td>
                                        <div class="d-flex">
                                            <input type="text" class="form-control icd_type_desc" value="${item.icd_type_desc}" readonly style="flex: 1;">
                                            <a href="#itemModal" data-toggle="modal" class="btn btn-primary ml-2 btn-select-icd_type" style="white-space: nowrap;">
                                                <i class="fa fa-search"></i>
                                            </a>
                                        </div>
                                    </td>
                                    <td>
                                        <input type="hidden" class="form-control icd_type_id" value="${item.icd_type_id}" readonly>
                                        <input type="hidden" class="form-control icd_type_sign" value="${item.icd_type_sign}" readonly>
                                        <input type="text" class="form-control icd_type_sign_desc" value="${item.icd_type_sign_desc}" readonly style="flex: 1;">
                                    </td>
                                    <td><input type="number" class="form-control text-right item-quantity" min="0" step="0.01" value="${item.quantity}" required></td>
                                    <td><input type="number" class="form-control text-right item-amount-per-unit" min="0" step="0.01" value="${item.amount_per_unit}" required></td>
                                    <td><input type="number" class="form-control text-right item-total-amount" value="${item.amount}" readonly></td>
                                    <td class="text-center"><button class="btn btn-danger btn-sm rounded-circle remove-row" type="button" title="ลบรายการนี้"><i class="fas fa-trash-alt"></i></button></td>
                                </tr>
                            `;
                            $('#detailTable tbody').append(newRow);
                        });
                        calculateTotalAmount();
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

        // Save Button Handler+
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
                payroll_month: $('#payroll_month').val(),
                payroll_year: $('#payroll_year').val(),
                details: details
            };

            $.ajax({
                url: 'model/manage_payroll_data_detail_process.php', // คุณจะต้องสร้างไฟล์นี้เพื่อจัดการข้อมูลเงินเดือน
                method: 'POST',
                contentType: 'application/json', // ส่งข้อมูลเป็น JSON
                data: JSON.stringify(payload),
                dataType: 'json',
                success: function (response) {
                    if (response.success) {
                        alertify.success(response.message);
                        // Optional: update doc_no if it was newly generated on ADD
                        if (response.doc_no) {
                            $('#doc_no').val(response.doc_no);
                        }
                        closeAndReload();
                    } else {
                        alertify.error('ข้อผิดพลาด: ' + response.message);
                    }
                    $('#save').prop('disabled', false); // Re-enable button
                },
                error: function (xhr, status, error) {
                    console.error("AJAX Error:", status, error, xhr.responseText);
                    alertify.error('ไม่สามารถเชื่อมต่อเซิร์ฟเวอร์ได้ หรือเกิดข้อผิดพลาด');
                    $('#save').prop('disabled', false); // Re-enable button
                }
            });
        });

        // Function to close current window and reload parent table (if applicable)
        function closeAndReload() {
            if (window.opener && !window.opener.closed) {
                // Assuming parent window has a DataTable with ID 'TableRecordList'
                if (window.opener.$('#TableRecordList').length) {
                    window.opener.$('#TableRecordList').DataTable().ajax.reload(null, false);
                }
            }
            window.close();
        }
    </script>


    <script>
        $(document).ready(function () {
            let currentRow = null;

            $(document).on('click', '.btn-select-icd_type', function () {
                currentRow = $(this).closest('tr');
                loadIncomeDeductTable();
                $('#itemModal').modal('show');
            });

            $(document).on('click', '.select-this', function () {
                const code = $(this).data('code');
                const desc = $(this).data('desc');
                const sign = $(this).data('sign');
                const sign_desc = $(this).data('sign_desc');

                if (currentRow) {
                    currentRow.find('.icd_type_id').val(code);
                    currentRow.find('.icd_type_desc').val(desc);
                    currentRow.find('.icd_type_sign').val(sign);
                    currentRow.find('.icd_type_sign_desc').val(sign_desc);
                }

                $('#itemModal').modal('hide');
            });

            function loadIncomeDeductTable() {
                $.ajax({
                    url: 'model/get_income_deduct.php',
                    method: 'GET',
                    dataType: 'json',
                    success: function (data) {
                        let html = '';
                        data.forEach(item => {
                            html += `
<tr>
    <td>${item.icd_type_id}</td>
    <td>${item.icd_type_desc}</td>
    <td>${item.icd_type_sign}</td>
    <td>${item.icd_type_sign_desc}</td>
    <td>
        <button class="btn btn-sm btn-primary select-this"
                data-code="${item.icd_type_id}"
                data-desc="${item.icd_type_desc}"
                data-sign="${item.icd_type_sign}"
                data-sign_desc="${item.icd_type_sign_desc}">เลือก</button>
    </td>
</tr>`;
                        });
                        $('#incomedeductTable tbody').html(html);
                    },
                    error: function (xhr, status, error) {
                        console.error("AJAX Error loading income/deduct data:", status, error);
                        alert('โหลดข้อมูลไม่สำเร็จ');
                    }
                });
            }
        });
    </script>


    </body>
    </html>

    <?php
} // end else session check
?>