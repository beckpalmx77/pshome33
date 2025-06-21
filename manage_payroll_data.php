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

            .position-relative {
                position: relative;
            }

            .list-group {
                position: absolute;
                z-index: 1000;
                width: 100%;
                border: 1px solid #ddd;
                max-height: 200px;
                overflow-y: auto;
            }

            .list-group-item:hover {
                background-color: #f0f0f0;
                cursor: pointer;
            }
        </style>
    </head>
    <body id="page-top">
    <div id="wrapper">
        <?php include('includes/Side-Bar.php'); // ถ้ามี Sidebar ?>

        <div id="content-wrapper" class="d-flex flex-column">
            <div id="content">
                <?php include('includes/Top-Bar.php'); // ถ้ามี Topbar ?>
                <!-- Container Fluid-->
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
                            <!-- Master Section for Payroll Header -->
                            <div class="row g-3 align-items-end">

                                <!-- Doc ID -->
                                <div class="col-md-2">
                                    <div class="form-group">
                                        <label for="doc_id" class="form-label">เลขที่เอกสาร (Doc ID)</label>
                                        <input type="text" id="doc_id" name="doc_id" class="form-control" readonly>
                                    </div>
                                </div>

                                <!-- Document Date -->
                                <div class="col-md-2">
                                    <div class="form-group">
                                        <label>วันที่</label>
                                        <i class="fa fa-calendar" aria-hidden="true"></i>
                                        <input type="text" class="form-control" id="doc_date" name="doc_date"
                                               required="required" value="<?php echo $curr_date ?>" readonly="true"
                                               placeholder="">
                                    </div>
                                </div>

                                <!-- Employee Name -->
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="employee_name" class="form-label">ชื่อพนักงาน</label>
                                        <input type="text" id="employee_name" name="employee_name" class="form-control" autocomplete="off" required>
                                        <input type="hidden" id="emp_id" name="emp_id">
                                    </div>
                                </div>

                                <!-- Search Button -->
                                <div class="col-md-2">
                                    <div class="form-group">
                                        <label class="form-label text-white">ค้นหา</label> <!-- ใช้ text-white เพื่อจัดระยะเท่ากัน -->
                                        <a href="#SearchEmployee_NameModalLabel" data-bs-toggle="modal" class="btn btn-primary w-100">
                                            Click <i class="fa fa-search" aria-hidden="true"></i>
                                        </a>
                                    </div>
                                </div>

                            </div>


                            <div class="row mt-3"> <!-- New row for month and year -->
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label>เดือนเงินเดือน</label>
                                        <select id="payroll_month" name="payroll_month" class="form-select" required></select>
                                    </div>
                                </div>
                                <div class="col-md-2">
                                    <div class="form-group">
                                        <label>ปีเงินเดือน</label>
                                        <select id="payroll_year" name="payroll_year" class="form-select" required></select>
                                    </div>
                                </div>
                            </div>
                            <hr>

                            <!-- Detail Section for Payroll Items (Gross/Deductions) -->
                            <div class="d-flex align-items-center mt-4 mb-3">
                                <h5 class="text-primary mb-0 mr-2">รายการรายได้/รายหัก</h5>
                                <button class="btn btn-primary" id="addRow" type="button">+ เพิ่มรายการ</button>
                            </div>

                            <table class="table table-bordered" id="detailTable">
                                <thead class="table-primary">
                                <tr>
                                    <th style="width: 25%;">รายการ (เช่น เงินเดือน, ลาเกิน)</th>
                                    <th style="width: 15%;">จำนวนวัน/หน่วย</th>
                                    <th style="width: 20%;">จำนวนเงิน (ต่อวัน/หน่วย)</th>
                                    <th style="width: 20%;">รวมเงิน</th>
                                    <th style="width: 10%;">ลบ</th>
                                </tr>
                                </thead>
                                <tbody>
                                <!-- Detail rows will be added here by JavaScript -->
                                </tbody>
                                <tfoot>
                                <tr>
                                    <td colspan="3" class="text-end"><strong>รวมเงินสุทธิ (ประมาณการ):</strong></td>
                                    <td><input type="text" class="form-control text-end" id="total_amount" readonly>
                                    </td>
                                    <td></td>
                                </tr>
                                </tfoot>
                            </table>
                            <br>

                            <div class="modal-footer justify-content-center">
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

                    <!-- Modals for selecting employee -->
                    <div class="modal fade" id="SearchEmployee_NameModal" tabindex="-1"
                         aria-labelledby="SearchEmployee_NameModalLabel" aria-hidden="true">
                        <div class="modal-dialog modal-lg modal-dialog-centered">
                            <div class="modal-content rounded-4 shadow">
                                <div class="modal-header bg-primary text-white rounded-top-4">
                                    <h5 class="modal-title" id="SearchEmployee_NameModalLabel">เลือกชื่อพนักงาน</h5>
                                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                                            aria-label="Close"></button>
                                </div>
                                <div class="modal-body p-4">
                                    <div class="table-responsive">
                                        <table id="TableEmployee_NameList" class="table table-hover table-bordered"
                                               style="width:100%">
                                            <thead>
                                            <tr>
                                                <th>รหัสพนักงาน</th>
                                                <th>ชื่อพนักงาน</th>
                                                <th>Action</th>
                                            </tr>
                                            </thead>
                                            <tbody>
                                            <!-- Employee data will be loaded here via AJAX -->
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- End Modals -->

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
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet"/>

    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

    <script>
        // Global variable to keep track of added detail items
        let detailItems = [];
        let currentRowForSelection = null; // To store the current row when selecting employee

        $(document).ready(function () {
            // Initialize Datepicker
            $('.datepicker').datepicker({
                format: "dd-mm-yyyy",
                todayHighlight: true,
                language: "th",
                autoclose: true
            });

            // Populate Payroll Month Dropdown
            // Note: This needs a 'model/get_month_names.php' file or similar to provide month names
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
            for (let i = currentYear - 5; i <= currentYear + 5; i++) {
                yearOptions += `<option value="${i}" ${i === currentYear ? 'selected' : ''}>${i}</option>`;
            }
            $('#payroll_year').html(yearOptions);


            // Parse URL parameters
            let urlParams = new URLSearchParams(window.location.search);
            $("#sub_menu").html(urlParams.get("sub_menu") || "");
            $("#main_menu").html(urlParams.get("main_menu") || "");
            $('#action').val(urlParams.get("action"));

            // Load data if in EDIT mode
            if (urlParams.get("action") !== 'ADD' && urlParams.get("doc_id")) {
                loadPayrollData(urlParams.get("doc_id"));
            } else {
                // For ADD mode, generate a temporary doc_id or leave blank
                // Example: Generate a simple timestamp-based ID
                // $('#doc_id').val('NEW-' + Date.now());
                // Or fetch from server for proper numbering
            }

            // Add new row to detail table
            $('#addRow').on('click', function () {
                const newRow = `
                <tr>
                    <td><input type="text" class="form-control item-desc" placeholder="เช่น เงินเดือน, ลาเกิน" required></td>
                    <td><input type="number" class="form-control item-quantity" min="0" step="0.01" value="0" required></td>
                    <td><input type="number" class="form-control item-amount-per-unit" min="0" step="0.01" value="0" required></td>
                    <td><input type="number" class="form-control item-total-amount" value="0.00" readonly></td>
                    <td><button class="btn btn-danger btn-sm rounded-pill remove-row" type="button"><i class="fas fa-trash-alt"></i></button></td>
                </tr>
                `;
                $('#detailTable tbody').append(newRow);
                calculateTotalAmount();
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

            // Autocomplete for Employee Name
            $('#employee_name').on('keyup', function () {
                let query = $(this).val();
                if (query.length >= 2) {
                    $.ajax({
                        url: 'api/employees_api.php', // ใช้ API ที่สร้างไว้ก่อนหน้า
                        method: 'POST',
                        data: {action: 'get_all'}, // ดึงพนักงานทั้งหมด
                        dataType: 'json',
                        success: function (response) {
                            if (response.success && response.data.length > 0) {
                                let html = '';
                                response.data.forEach(emp => {
                                    if (emp.first_name.includes(query) || emp.last_name.includes(query) || emp.employee_code.includes(query)) {
                                        html += `<a href="#" class="list-group-item list-group-item-action employee_name-item" data-id="${emp.employee_id}" data-name="${emp.first_name} ${emp.last_name} (${emp.employee_code})">
                                            ${emp.first_name} ${emp.last_name} (${emp.employee_code})
                                        </a>`;
                                    }
                                });
                                $('#employee_name_list').fadeIn().html(html);
                            } else {
                                $('#employee_name_list').fadeOut();
                            }
                        }
                    });
                } else {
                    $('#employee_name_list').fadeOut();
                }
            });

            $(document).on('click', '.employee_name-item', function () {
                const name = $(this).data('name');
                const id = $(this).data('id');
                $('#employee_name').val(name);
                $('#emp_id').val(id);
                $('#employee_name_list').fadeOut();
            });

            $(document).on('click', function (e) {
                if (!$(e.target).closest('#employee_name, #employee_name_list').length) {
                    $('#employee_name_list').fadeOut();
                }
            });

            // Handle selection from Employee Modal
            $(document).on('click', '.select-employee-btn', function () {
                const empId = $(this).data('id');
                const empName = $(this).data('name');
                const empCode = $(this).data('code');

                $('#emp_id').val(empId);
                $('#employee_name').val(`${empName} (${empCode})`);
                $('#SearchEmployee_NameModal').modal('hide');
            });
        });

        // Function to calculate total amount
        function calculateTotalAmount() {
            let total = 0;
            $('#detailTable tbody tr').each(function () {
                const amount = parseFloat($(this).find('.item-total-amount').val()) || 0;
                total += amount;
            });
            $('#total_amount').val(total.toFixed(2));
        }

        // Function to load employee data for the modal table
        function loadEmployeeModalTable() {
            $.ajax({
                url: 'api/employees_api.php',
                method: 'POST',
                data: {action: 'get_all'},
                dataType: 'json',
                success: function (response) {
                    const tableBody = $('#TableEmployee_NameList tbody');
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
                                                data-code="${emp.employee_code}">เลือก</button>
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
        $('#SearchEmployee_NameModal').on('show.bs.modal', function () {
            loadEmployeeModalTable();
        });


        // Function to load existing payroll data for editing
        function loadPayrollData(doc_id) {
            $.ajax({
                url: 'api/payroll_api.php', // คุณจะต้องสร้างไฟล์นี้
                method: 'POST',
                data: {action: 'get_single', doc_id: doc_id},
                dataType: 'json',
                success: function (response) {
                    if (response.success && response.data) {
                        const header = response.data.header;
                        const details = response.data.details;

                        $('#doc_id').val(header.doc_id);
                        $('#doc_date').val(header.doc_date); // Assuming format DD-MM-YYYY
                        $('#emp_id').val(header.emp_id);
                        $('#employee_name').val(header.employee_name); // You might need to fetch this based on emp_id
                        $('#payroll_month').val(header.payroll_month);
                        $('#payroll_year').val(header.payroll_year);

                        $('#detailTable tbody').empty();
                        details.forEach(item => {
                            const newRow = `
                                <tr>
                                    <td><input type="text" class="form-control item-desc" value="${item.description}" required></td>
                                    <td><input type="number" class="form-control item-quantity" min="0" step="0.01" value="${item.quantity}" required></td>
                                    <td><input type="number" class="form-control item-amount-per-unit" min="0" step="0.01" value="${item.amount / item.quantity}" required></td>
                                    <td><input type="number" class="form-control item-total-amount" value="${item.amount}" readonly></td>
                                    <td><button class="btn btn-danger btn-sm rounded-pill remove-row" type="button"><i class="fas fa-trash-alt"></i></button></td>
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
                const description = $(this).find('.item-desc').val();
                const quantity = parseFloat($(this).find('.item-quantity').val());
                const amount = parseFloat($(this).find('.item-total-amount').val());

                if (!description || isNaN(quantity) || quantity < 0 || isNaN(amount) || amount < 0) {
                    isValidDetails = false;
                    alertify.error("กรุณากรอกข้อมูลรายการเงินเดือน/หักให้ครบถ้วนและถูกต้อง");
                    return false; // Break .each loop
                }
                details.push({
                    description: description,
                    quantity: quantity,
                    amount: amount
                });
            });

            if (!isValidDetails || details.length === 0) {
                alertify.error("กรุณาเพิ่มอย่างน้อย 1 รายการเงินเดือน/หัก");
                return;
            }

            $('#save').prop('disabled', true); // Disable button to prevent multiple submissions

            const payload = {
                action: $('#action').val(), // 'ADD' or 'UPDATE'
                doc_id: $('#doc_id').val(),
                doc_date: $('#doc_date').val(),
                emp_id: $('#emp_id').val(),
                payroll_month: $('#payroll_month').val(),
                payroll_year: $('#payroll_year').val(),
                details: details
            };

            $.ajax({
                url: 'api/payroll_api.php', // คุณจะต้องสร้างไฟล์นี้เพื่อจัดการข้อมูลเงินเดือน
                method: 'POST',
                contentType: 'application/json', // ส่งข้อมูลเป็น JSON
                data: JSON.stringify(payload),
                dataType: 'json',
                success: function (response) {
                    if (response.success) {
                        alertify.success(response.message);
                        // Optional: update doc_id if it was newly generated on ADD
                        if (response.doc_id) {
                            $('#doc_id').val(response.doc_id);
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

    </body>
    </html>

    <?php
} // end else session check
?>