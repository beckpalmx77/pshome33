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
            .detail-input {
                width: 100%;
                box-sizing: border-box;
            }

            .datepicker {
                z-index: 9999 !important; /* Ensure datepicker is above modals if any */
            }

            /* Style for the remaining balance display */
            #remaining_balance_display {
                font-size: 1.2em;
                font-weight: bold;
                color: #28a745; /* Green color for positive balance */
            }

            #remaining_balance_display.negative {
                color: #dc3545; /* Red color for negative balance */
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
                                        <input type="text" id="installment_id" class="form-control" readonly>
                                    </div>
                                </div>

                                <div class="col-md-2">
                                    <div class="form-group">
                                        <label>วันที่เอกสาร</label>
                                        <i class="fa fa-calendar" aria-hidden="true"></i>
                                        <input type="text" class="form-control" id="doc_date" name="doc_date"
                                               value="<?php echo $curr_date ?>" readonly required>
                                    </div>
                                </div>

                                <div class="col-md-2">
                                    <div class="form-group">
                                        <label>บ้านเลขที่</label>
                                        <i class="fa fa-home" aria-hidden="true"></i>
                                        <input type="text" class="form-control" id="house_number" name="house_number"
                                               value="" required>
                                    </div>
                                </div>

                                <div class="col-md-2">
                                    <div class="form-group">
                                        <label>ผู้ทำสัญญา/ผ่อนชำระ</label>
                                        <i class="fa fa-user-circle" aria-hidden="true"></i>
                                        <input type="text" class="form-control" id="debtor" name="debtor"
                                               value="" required>
                                    </div>
                                </div>

                            </div>

                            <div class="row">

                                <div class="col-md-2">
                                    <div class="form-group">
                                        <label>ยอดเงินต้น</label>
                                        <i class="fa fa-money" aria-hidden="true"></i>
                                        <input type="number" class="form-control" id="principal_amount"
                                               name="principal_amount"
                                               value="0.00" required>
                                    </div>
                                </div>

                                <div class="col-md-2">
                                    <div class="form-group">
                                        <label>ค่าปรับล่าช้า</label>
                                        <i class="fa fa-link" aria-hidden="true"></i>
                                        <input type="number" class="form-control" id="interest_rate"
                                               name="interest_rate"
                                               value="0.00">
                                    </div>
                                </div>

                                <div class="col-md-2">
                                    <div class="form-group">
                                        <label>เงินทำสัญญา</label>
                                        <i class="fa fa-money" aria-hidden="true"></i>
                                        <input type="number" class="form-control" id="down_payment"
                                               name="down_payment"
                                               value="0.00" required>
                                    </div>
                                </div>

                                <div class="col-md-2">
                                    <div class="form-group">
                                        <label>จำนวนงวด</label>
                                        <i class="fa fa-bookmark" aria-hidden="true"></i>
                                        <input type="number" class="form-control" id="num_installments"
                                               name="num_installments"
                                               value="1" required>
                                    </div>
                                </div>

                                <div class="col-md-2">
                                    <div class="form-group">
                                        <label>ยอดเงินที่ต้องผ่อนชำระ</label>
                                        <i class="fa fa-bookmark" aria-hidden="true"></i>
                                        <input type="number" class="form-control" id="principal_amount_balance"
                                               name="principal_amount_balance"
                                               value="0.00" required>
                                    </div>
                                </div>

                                <div class="col-md-2">
                                    <div class="form-group">
                                        <label>ยอดผ่อนแต่ละงวด</label>
                                        <i class="fa fa-money" aria-hidden="true"></i>
                                        <input type="text" class="form-control" id="installment_per_period"
                                               name="installment_per_period"
                                               value="" required>
                                    </div>
                                </div>

                            </div>

                            <div class="row">
                                <div class="col-md-2">
                                    <div class="form-group">
                                        <label>วันที่ครบกำหนดชำระแต่ละงวด</label>
                                        <i class="fa fa-calendar-check-o" aria-hidden="true"></i>
                                        <input type="varchar" class="form-control" id="payment_due_day_period"
                                               name="payment_due_day_period"
                                               value="">
                                    </div>
                                </div>
                            </div>

                            <input type="hidden" class="form-control" id="status" name="status" value="active">

                            <input type="file" id="pictures" multiple accept="image/*,application/pdf">
                            <input type="hidden" id="installment_img" name="installment_img">
                            <input type="hidden" id="deleted_images" name="deleted_images" value="">
                            <div id="preview-area" class="row mt-2"></div>
                            <div id="imagePreview" class="mt-2 d-flex flex-wrap"></div>

                            <div class="d-flex align-items-center mt-4 mb-3">
                                <h4 class="h5 mb-0 text-gray-800">รายละเอียดงวดผ่อนชำระ</h4>
                                <div class="ml-auto">
                                    <button type="button" class="btn btn-success" id="addRow">
                                        <i class="fas fa-plus"></i> เพิ่มงวด
                                    </button>
                                </div>
                            </div>

                            <div class="table-responsive">
                                <table class="table table-bordered" id="detailTable">
                                    <thead>
                                    <tr>
                                        <th style="width: 50px;">#</th>
                                        <th>ยอดรวมที่ต้องชำระ</th>
                                        <th>เงินต้นต่องวด</th>
                                        <th>ยอดที่ชำระงวดนี้</th>
                                        <th>วันที่ชำระ</th>
                                        <th>วิธีการชำระ</th>
                                        <th>สถานะ</th>
                                        <th style="width: 50px;">พิมพ์</th>
                                        <th style="width: 50px;">ลบ</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    </tbody>
                                </table>
                            </div>

                            <div class="row mt-3">
                                <div class="col-md-12 text-right">
                                    <label>คงเหลือในการผ่อนชำระ:</label>
                                    <span id="remaining_balance_display">0.00</span> บาท
                                </div>
                            </div>

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

                </div>

            </div>

        </div>

    </div>

    <?php
    include('includes/Modal-Logout.php'); // Assuming this is needed. Not in original snippet.
    include('includes/Footer.php'); // Assuming this is needed. Not in original snippet.
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
    <script src="js/modal/show_supplier_modal.js"></script>

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

        let rowIdx = 0; // Global counter for rows

        function addRow(detailData = {}) {
            rowIdx++;
            const tableBody = $('#detailTable tbody');
            // Get installment_per_period from the main form
            const installmentPerPeriod = parseFloat($('#installment_per_period').val()) || 0;

            const newRow = `
                <tr id="R${rowIdx}">
                    <td class="text-center">${rowIdx}</td>
                    <td><input type="number" class="form-control detail-input amount-due" name="amount_due[]" value="${detailData.amount_due || installmentPerPeriod.toFixed(2)}" step="0.01" min="0" required readonly></td>
                    <td><input type="number" class="form-control detail-input principal-per-installment" name="principal_per_installment[]" value="${detailData.principal_per_installment || installmentPerPeriod.toFixed(2)}" step="0.01" min="0" required readonly></td>
                    <td><input type="number" class="form-control detail-input amount-paid" name="amount_paid[]" value="${detailData.amount_paid || ''}" step="0.01" min="0"></td>
                    <td><input type="text" class="form-control detail-input datepicker-input" name="payment_date[]" value="${detailData.payment_date || ''}" readonly></td>
                    <td>
                        <select class="form-control detail-input payment-method" name="payment_method[]">
                            <option value="">เลือก</option>
                            <option value="เงินสด" ${detailData.payment_method === 'เงินสด' ? 'selected' : ''}>เงินสด</option>
                            <option value="โอนเงิน" ${detailData.payment_method === 'โอนเงิน' ? 'selected' : ''}>โอนเงิน</option>
                        </select>
                    </td>
                    <td>
                        <select class="form-control detail-input status-select" name="status[]">
                            <option value="due" ${detailData.status === 'due' ? 'selected' : ''}>ยังไม่ชำระ</option>
                            <option value="paid" ${detailData.status === 'paid' ? 'selected' : ''}>ชำระแล้ว</option>
                            <option value="overdue" ${detailData.status === 'overdue' ? 'selected' : ''}>ค้างชำระ</option>
                        </select>
                    </td>
                    <td class="text-center">
                        <button type="button" class="btn btn-success print-row"><i class="fa fa-print"></i></button>
                    </td>
                    <td class="text-center">
                        <button type="button" class="btn btn-danger remove-row"><i class="fas fa-minus"></i></button>
                    </td>
                    <input type="hidden" name="installment_number[]" value="${detailData.installment_number || rowIdx}">
                    <input type="hidden" name="line_no[]" value="${detailData.line_no || rowIdx}">
                    <input type="hidden" name="detail_id[]" value="${detailData.id || ''}"> </tr>
            `;
            tableBody.append(newRow);

            // Initialize datepicker for the new row's payment_date input
            $(`#R${rowIdx} .datepicker-input`).datepicker({
                format: "dd-mm-yyyy",
                todayHighlight: true,
                language: "th",
                autoclose: true
            });

            // Re-calculate remaining balance after adding a row
            calculateRemainingBalance();
        }

        function calculateRemainingBalance() {
            let totalPrincipalAmount = parseFloat($('#principal_amount').val()) || 0;
            let totalInterest_rate = parseFloat($('#interest_rate').val()) || 0;
            let totalDownPayment = parseFloat($('#down_payment').val()) || 0;
            let totalAmountPaidInDetails = 0;

            $('#detailTable tbody tr').each(function () {
                const amountPaid = parseFloat($(this).find('.amount-paid').val()) || 0;
                totalAmountPaidInDetails += amountPaid;
            });

            // Calculate the remaining balance
            let remainingBalance = (totalPrincipalAmount + totalInterest_rate) - totalDownPayment - totalAmountPaidInDetails;

            const remainingBalanceDisplay = $('#remaining_balance_display');
            remainingBalanceDisplay.text(remainingBalance.toFixed(2));

            // Apply styling based on balance
            if (remainingBalance < 0) {
                remainingBalanceDisplay.addClass('negative');
            } else {
                remainingBalanceDisplay.removeClass('negative');
            }
        }


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

            // Load existing main picture
            if (queryString["installment_img"]) {
                $('#installment_img').val(queryString["installment_img"]); // Assuming installment_img is for the main document
                let filenames = queryString["installment_img"].split(',');
                let imagePreviewContainer = $('#imagePreview');
                imagePreviewContainer.html('');

                filenames.forEach(file => {
                    file = file.trim();
                    if (!file) return;

                    let fileExtension = file.split('.').pop().toLowerCase();
                    let isImage = ['jpg', 'jpeg', 'png', 'gif', 'bmp', 'webp'].includes(fileExtension);

                    let fileBox = $('<div>').addClass('position-relative m-2').css({display: 'inline-block'});
                    let filePath = 'uploads/installment/' + file;

                    if (isImage) {
                        let imgLink = $('<a>').attr({'href': filePath, 'target': '_blank'});
                        let img = $('<img>')
                            .attr('src', filePath)
                            .css({width: '120px', height: 'auto', border: '1px solid #ccc', padding: '2px'});
                        imgLink.append(img);
                        fileBox.append(imgLink);
                    } else if (fileExtension === 'pdf') {
                        let pdfPlaceholder = $('<div>').css({
                            'width': '120px', 'height': '120px', 'background-color': '#f0f0f0',
                            'border': '1px solid #ccc', 'display': 'flex', 'flex-direction': 'column',
                            'justify-content': 'center', 'align-items': 'center', 'text-align': 'center',
                            'overflow': 'hidden', 'padding': '5px'
                        });
                        pdfPlaceholder.append($('<p>').text('PDF File').css('font-weight', 'bold'));
                        pdfPlaceholder.append($('<p>').text(file).css({
                            'font-size': '0.7em',
                            'word-break': 'break-all'
                        }));
                        pdfPlaceholder.append($('<a>').attr({
                            'href': filePath,
                            'target': '_blank'
                        }).text('View').addClass('btn btn-sm btn-primary mt-1'));
                        fileBox.append(pdfPlaceholder);
                    }

                    let deleteBtn = $('<button>')
                        .addClass('btn btn-danger btn-sm position-absolute top-0 end-0')
                        .html('&times;')
                        .css({zIndex: 2, padding: '2px 6px', borderRadius: '50%'})
                        .on('click', function () {
                            fileBox.remove();
                            let deleted = $('#deleted_images').val();
                            let deletedArray = deleted ? deleted.split(',') : [];
                            if (!deletedArray.includes(file)) {
                                deletedArray.push(file);
                                $('#deleted_images').val(deletedArray.join(','));
                            }
                        });

                    fileBox.append(deleteBtn);
                    imagePreviewContainer.append(fileBox);
                });
            }

            // Load detail data if in UPDATE mode and installment_id is present
            if (queryString["action"] === 'UPDATE' && queryString["installment_id"]) {

                $('#installment_id').val(queryString["installment_id"]);
                $('#principal_amount').val(queryString["principal_amount"]);
                $('#down_payment').val(queryString["down_payment"]);
                $('#num_installments').val(queryString["num_installments"]);
                $('#installment_per_period').val(queryString["installment_per_period"]);

                $('#doc_date').val(queryString["doc_date"]);
                $('#house_number').val(queryString["house_number"]);
                $('#debtor').val(queryString["debtor"]);

                $('#interest_rate').val(queryString["interest_rate"]);
                $('#payment_due_day_period').val(queryString["payment_due_day_period"]);


                $.ajax({
                    url: 'model/get_installment_details.php', // New endpoint to fetch details
                    method: 'GET',
                    data: {installment_id: queryString["installment_id"]},
                    dataType: 'json',
                    success: function (res) {
                        if (res.status === 'success' && res.details.length > 0) {
                            // Reset rowIdx before loading from DB to ensure correct sequence
                            rowIdx = 0;
                            res.details.forEach(detail => addRow(detail));
                        } else if (res.details.length === 0) {
                            alertify.message('ไม่พบรายละเอียดงวดผ่อนชำระสำหรับเอกสารนี้');
                        } else {
                            alertify.error('ไม่สามารถโหลดรายละเอียดงวดผ่อนชำระได้: ' + res.message);
                        }
                        calculateRemainingBalance(); // Calculate after loading details
                    },
                    error: function (xhr, status, error) {
                        console.error("AJAX Error loading details:", status, error);
                        alertify.error('ไม่สามารถเชื่อมต่อเซิร์ฟเวอร์เพื่อโหลดรายละเอียดได้');
                    }
                });
            } else {
                // If not in update mode, initialize remaining balance
                calculateRemainingBalance();
            }


            // Add row button click
            $('#addRow').click(function () {
                addRow();
            });

            // Remove row button click (delegated event for dynamic rows)
            $('#detailTable tbody').on('click', '.remove-row', function () {
                $(this).closest('tr').remove();
                // Re-index row numbers and hidden inputs after removal
                $('#detailTable tbody tr').each(function (i) {
                    const currentLineNum = i + 1;
                    $(this).find('td:first').text(currentLineNum); // Update displayed #
                    $(this).find('input[name="installment_number[]"]').val(currentLineNum); // Update hidden installment_number
                    $(this).find('input[name="line_no[]"]').val(currentLineNum); // Update hidden line_no
                });
                rowIdx = $('#detailTable tbody tr').length; // Update global rowIdx based on remaining rows
                calculateRemainingBalance(); // Re-calculate after removing a row
            });

            // *** START: Corrected print-row click handler ***
            $('#detailTable tbody').on('click', '.print-row', function () {
                // Get the parent row of the clicked button
                const row = $(this).closest('tr');

                // Get installment_id from the main form (it's a global field)
                const installment_id = $('#installment_id').val();

                // Get line_no from the hidden input within the current row
                const line_no = row.find('input[name="line_no[]"]').val();

                // Construct the URL
                if (installment_id && line_no) {
                    let url = "print_pdf_installment.php?installment_id=" + encodeURIComponent(installment_id) + "&line_no=" + encodeURIComponent(line_no);
                    window.open(url, "_blank");
                } else {
                    alertify.error("ไม่สามารถพิมพ์ได้: ไม่พบข้อมูลเลขที่เอกสารผ่อนชำระหรือหมายเลขงวด");
                }
            });
            // *** END: Corrected print-row click handler ***

            // Event listener for changes in 'principal_amount', 'down_payment', 'installment_per_period', and '.amount-paid'
            $('#principal_amount, #down_payment').on('input', calculateRemainingBalance);
            $('#detailTable tbody').on('input', '.amount-paid', calculateRemainingBalance);

            // Update amount_due for new rows based on installment_per_period
            $('#installment_per_period').on('input', function () {
                const newInstallmentPerPeriod = parseFloat($(this).val()) || 0;
                $('#detailTable tbody tr').each(function () {
                    const status = $(this).find('.status-select').val();
                    // Only update if the status is 'due' (unpaid)
                    if (status === 'due') {
                        $(this).find('.amount-due').val(newInstallmentPerPeriod.toFixed(2));
                        $(this).find('.principal-per-installment').val(newInstallmentPerPeriod.toFixed(2));
                    }
                });
            });

            // Update status and payment_date when amount_paid is entered
            $('#detailTable tbody').on('input', '.amount-paid', function () {
                const amountPaid = parseFloat($(this).val()) || 0;
                const row = $(this).closest('tr');
                const amountDue = parseFloat(row.find('.amount-due').val()) || 0;
                const statusSelect = row.find('.status-select');
                const paymentDateInput = row.find('.datepicker-input');

                if (amountPaid > 0) {
                    paymentDateInput.val('<?php echo $curr_date; ?>'); // Set current date
                    if (amountPaid >= amountDue) {
                        statusSelect.val('paid'); // Set status to 'ชำระแล้ว'
                    } else {
                        statusSelect.val('due'); // Keep as 'ยังไม่ชำระ' if partial payment
                    }
                } else {
                    paymentDateInput.val(''); // Clear date if amount paid is zero
                    statusSelect.val('due'); // Set status to 'ยังไม่ชำระ'
                }
            });

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

                if (!$('#doc_date').val() || ($('#installment_id').val() === '' && $('#action').val() !== 'ADD')) {
                    alertify.error('กรุณากรอกวันที่เอกสารและเลขที่เอกสารผ่อนชำระ');
                    return;
                }

                // ** START: New validation for house_number **
                if (!$('#house_number').val()) {
                    alertify.error('กรุณากรอกบ้านเลขที่');
                    return;
                }

                // ** START: New validation for house_number **
                if (!$('#debtor').val()) {
                    alertify.error('กรุณากรอกชื่อผู้ทำสัญญา');
                    return;
                }

                const detailRows = [];
                let isValidDetails = true;
                $('#detailTable tbody tr').each(function () {
                    const row = $(this);
                    const detail = {
                        line_no: row.find('input[name="line_no[]"]').val(),
                        id: row.find('input[name="detail_id[]"]').val(),
                        installment_number: row.find('input[name="installment_number[]"]').val(),
                        amount_due: row.find('input[name="amount_due[]"]').val(),
                        principal_per_installment: row.find('input[name="principal_per_installment[]"]').val(),
                        amount_paid: row.find('input[name="amount_paid[]"]').val(),
                        payment_date: row.find('input[name="payment_date[]"]').val(),
                        payment_method: row.find('select[name="payment_method[]"]').val(),
                        status: row.find('select[name="status[]"]').val()
                    };

                    // Basic validation for detail rows
                    if (!detail.principal_per_installment || !detail.payment_method) {
                        alertify.error('กรุณากรอกข้อมูลในตารางรายละเอียดงวดผ่อนชำระให้ครบถ้วน');
                        isValidDetails = false;
                        return false; // Break .each loop
                    }

                    detailRows.push(detail);
                });

                if (!isValidDetails) {
                    return;
                }

                if (detailRows.length === 0) {
                    alertify.error('กรุณาเพิ่มรายละเอียดงวดผ่อนชำระอย่างน้อย 1 รายการ');
                    return;
                }

                $('#save').prop('disabled', true);

                try {
                    const newlyUploadedFilenames = await uploadImages();

                    let existingPicturePayment = $('#installment_img').val();
                    let existingFilenames = existingPicturePayment ? existingPicturePayment.split(',').map(name => name.trim()).filter(name => name) : [];

                    let deletedFilenames = $('#deleted_images').val();
                    let deletedArray = deletedFilenames ? deletedFilenames.split(',').map(name => name.trim()).filter(name => name) : [];

                    let currentFilenames = existingFilenames.filter(filename => !deletedArray.includes(filename));

                    const finalPicturePayment = currentFilenames.concat(newlyUploadedFilenames).join(',');

                    const payload = {
                        action: $('#action').val(),
                        installment_id: $('#installment_id').val(),
                        house_number: $('#house_number').val(),
                        debtor: $('#debtor').val(),
                        doc_date: $('#doc_date').val(),
                        start_date: $('#start_date').val(), // This field is not defined in the HTML
                        down_payment: $('#down_payment').val(),
                        principal_amount: $('#principal_amount').val(),
                        principal_amount_balance: $('#principal_amount_balance').val(),
                        interest_rate: $('#interest_rate').val(), // This field is not defined in the HTML
                        installment_per_period: $('#installment_per_period').val(),
                        num_installments: $('#num_installments').val(),
                        payment_due_day_period: $('#payment_due_day_period').val(),
                        status: $('#status').val(),
                        installment_img: finalPicturePayment,
                        deleted_images: JSON.stringify(deletedArray), // แปลง array เป็น JSON string
                        details: detailRows
                    };

                    $.ajax({
                        url: 'model/manage_installment_process.php',
                        method: 'POST',
                        contentType: 'application/json',
                        data: JSON.stringify(payload),
                        success: function (res) {
                            if (res.status === 'success') {
                                alertify.success('บันทึกสำเร็จแล้ว');
                                if ($('#action').val() === 'ADD' && res.installment_id) {
                                    $('#installment_id').val(res.installment_id);
                                    $('#action').val('UPDATE');
                                }
                                $('#deleted_images').val('');
                                calculateRemainingBalance(); // Recalculate after successful save
                            } else {
                                alertify.error('เกิดข้อผิดพลาด: ' + (res.message || 'Unknown error'));
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
        let uploadedImages = [];
        document.getElementById('pictures').addEventListener('change', function (e) {
            const files = Array.from(e.target.files);
            const previewArea = document.getElementById('preview-area');

            files.forEach((file) => {
                const fileIndex = uploadedImages.push(file) - 1;

                const filePreviewBox = document.createElement('div');
                filePreviewBox.classList.add('col-md-2', 'position-relative', 'mb-2');

                const removeButton = document.createElement('button');
                removeButton.setAttribute('type', 'button');
                removeButton.classList.add('btn', 'btn-sm', 'btn-danger', 'position-absolute', 'top-0', 'end-0', 'remove-new-img');
                removeButton.setAttribute('data-file-index', fileIndex);
                removeButton.innerHTML = '&times;';
                removeButton.style.cssText = 'z-index: 2; padding: 2px 6px; border-radius: 50%;';

                if (file.type.startsWith('image/')) {
                    const reader = new FileReader();
                    reader.onload = function (e) {
                        const img = document.createElement('img');
                        img.setAttribute('src', e.target.result);
                        img.classList.add('img-thumbnail');
                        img.style.cssText = 'width:100%; height:120px; object-fit:cover;';

                        const imgLink = document.createElement('a');
                        imgLink.setAttribute('href', e.target.result);
                        imgLink.setAttribute('target', '_blank');
                        imgLink.appendChild(img);

                        filePreviewBox.appendChild(imgLink);
                        filePreviewBox.appendChild(removeButton);
                        previewArea.appendChild(filePreviewBox);
                    };
                    reader.readAsDataURL(file);
                } else if (file.type === 'application/pdf') {
                    const pdfPlaceholder = document.createElement('div');
                    pdfPlaceholder.style.cssText = 'width:100%; height:120px; background-color: #f0f0f0; border: 1px solid #ccc; display: flex; flex-direction: column; justify-content: center; align-items: center; text-align: center; overflow: hidden;';

                    const pdfText = document.createElement('p');
                    pdfText.textContent = 'PDF File';
                    pdfText.style.cssText = 'font-weight: bold; margin-bottom: 5px;';

                    const fileNameShort = document.createElement('p');
                    fileNameShort.textContent = file.name;
                    fileNameShort.style.cssText = 'font-size: 0.7em; word-break: break-all; padding: 0 5px;';

                    const viewLink = document.createElement('a');
                    viewLink.setAttribute('href', URL.createObjectURL(file));
                    viewLink.setAttribute('target', '_blank');
                    viewLink.textContent = 'View';
                    viewLink.classList.add('btn', 'btn-sm', 'btn-primary', 'mt-1');

                    pdfPlaceholder.appendChild(pdfText);
                    pdfPlaceholder.appendChild(fileNameShort);
                    pdfPlaceholder.appendChild(viewLink);
                    filePreviewBox.appendChild(pdfPlaceholder);
                    filePreviewBox.appendChild(removeButton);
                    previewArea.appendChild(filePreviewBox);
                }
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
                return Promise.resolve([]);
            }

            const response = await fetch('upload_img_installment.php', {
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
        // Function to calculate principal_amount_balance and installment_per_period
        function calculateInstallmentDetails() {
            // Get values from input fields
            // Ensure you have input fields with these IDs in your manage_installment_data.php
            // For example: <input type="number" id="principal_amount_input">
            let principalAmount = parseFloat($('#principal_amount').val()) || 0;
            let downPayment = parseFloat($('#down_payment').val()) || 0;
            let numInstallments = parseInt($('#num_installments').val()) || 0;

            let interest_rate = parseInt($('#interest_rate').val()) || 0;

            let principal_amount_balance = (principalAmount + interest_rate) - downPayment;
            let installment_per_period = 0;

            if (numInstallments > 0) {
                installment_per_period = principal_amount_balance / numInstallments;
            }

            // Update the display fields
            // Ensure you have display elements with these IDs in your manage_installment_data.php
            // For example: <input type="text" id="principal_amount_balance_display" readonly>
            // Or: <span id="principal_amount_balance_display"></span>
            $('#principal_amount_balance').val(principal_amount_balance.toFixed(2)); // Use .val() if it's an input field
            $('#installment_per_period').val(installment_per_period.toFixed(2));   // Use .val() if it's an input field
        }

        $(document).ready(function () {
            // Attach the calculateInstallmentDetails function to the 'input' event of the relevant fields
            // This ensures the calculation happens as the user types or changes values
            $('#principal_amount, #interest_rate, #down_payment, #num_installments').on('input', function () {
                calculateInstallmentDetails();
            });

            // Call the function once on page load, in case the fields are pre-filled (e.g., during an 'UPDATE' action)
            calculateInstallmentDetails();
        });
    </script>

    </body>
    </html>

    <?php
}
?>