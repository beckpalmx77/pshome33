<?php
include('includes/Header.php');
if (strlen($_SESSION['alogin']) == "" || strlen($_SESSION['doc_no']) == "") {
    header("Location: index.php");
} else {
    include('config/connect_db.php');
    $curr_date = date("d-m-Y");
    
    // Fetch active bank accounts for the dropdown filter and add form
    $sql_accounts = "SELECT id, account_code, account_name, bank_name, account_no FROM ims_bank_account WHERE status = 'Y' ORDER BY account_code";
    $query_acc = $conn->prepare($sql_accounts);
    $query_acc->execute();
    $bank_accounts = $query_acc->fetchAll(PDO::FETCH_ASSOC);
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
                        <h1 class="h3 mb-0 text-gray-800">บันทึกธุรกรรมธนาคาร (ฝาก/ถอน/ปรับยอด)</h1>
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="<?php echo $_SESSION['dashboard_page'] ?>">Home</a></li>
                            <li class="breadcrumb-item">การเงิน/ธนาคาร</li>
                            <li class="breadcrumb-item active" aria-current="page">บันทึกธุรกรรมธนาคาร</li>
                        </ol>
                    </div>

                    <div class="row">
                        <div class="col-lg-12">
                            <div class="card mb-12">
                                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                                    <h6 class="m-0 font-weight-bold text-primary">ความเคลื่อนไหวทางบัญชีโครงการ (Ledger Statement)</h6>
                                </div>
                                <div class="card-body">
                                    <section class="container-fluid">
                                        
                                        <!-- Filters -->
                                        <div class="row mb-4 p-3 bg-light rounded border">
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label for="filter_account"><b>กรองบัญชีธนาคาร:</b></label>
                                                    <select id="filter_account" class="form-control">
                                                        <option value="">-- แสดงบัญชีทั้งหมด --</option>
                                                        <?php foreach ($bank_accounts as $acc) { ?>
                                                            <option value="<?php echo $acc['id']; ?>">
                                                                <?php echo "[{$acc['account_code']}] {$acc['bank_name']} - {$acc['account_no']} ({$acc['account_name']})"; ?>
                                                            </option>
                                                        <?php } ?>
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="col-md-2">
                                                <div class="form-group">
                                                    <label for="filter_start_date"><b>ตั้งแต่วันที่:</b></label>
                                                    <input type="text" id="filter_start_date" class="form-control datepicker" placeholder="dd-mm-yyyy" readonly>
                                                </div>
                                            </div>
                                            <div class="col-md-2">
                                                <div class="form-group">
                                                    <label for="filter_end_date"><b>ถึงวันที่:</b></label>
                                                    <input type="text" id="filter_end_date" class="form-control datepicker" placeholder="dd-mm-yyyy" readonly>
                                                </div>
                                            </div>
                                            <div class="col-md-2 d-flex align-items-end">
                                                <div class="form-group w-100">
                                                    <button type="button" id="btnFilterSearch" class="btn btn-primary w-100">
                                                        <i class="fa fa-search"></i> ค้นหา
                                                    </button>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="col-md-12 col-md-offset-2 mb-3">
                                            <button type='button' name='btnAdd' id='btnAdd' class='btn btn-primary btn-sm'>
                                                <i class="fa fa-plus"></i> บันทึกธุรกรรมการเงิน
                                            </button>
                                            <button type="button" id="btnReload" class="btn btn-outline-success btn-sm" data-toggle="tooltip" title="Reload Data">
                                                <i class="fa fa-refresh"></i> โหลดข้อมูลใหม่
                                            </button>
                                        </div>

                                        <div class="col-md-12 col-md-offset-2">
                                            <table id='TableRecordList' class='display dataTable table table-striped table-bordered text-nowrap' style="width:100%">
                                                <thead>
                                                <tr>
                                                    <th>วันเวลาที่ทำรายการ</th>
                                                    <th>เดือน</th>
                                                    <th>ปี</th>
                                                    <th>เลขที่ธุรกรรม</th>
                                                    <th>บัญชีธนาคาร</th>
                                                    <th>ประเภทรายการ</th>
                                                    <th>ฝาก/เข้า (+)</th>
                                                    <th>ถอน/ออก (-)</th>
                                                    <th>ค่าธรรมเนียม</th>
                                                    <th>ยอดเงินคงเหลือสะสม</th>
                                                    <th>เลขอ้างอิงสลิป/เช็ค</th>
                                                    <th>สลิปหลักฐาน</th>
                                                    <th>หมายเหตุ</th>
                                                    <th>แก้ไข</th>
                                                    <th>ลบรายการ</th>
                                                </tr>
                                                </thead>
                                                <tfoot>
                                                <tr>
                                                    <th>วันเวลาที่ทำรายการ</th>
                                                    <th>เดือน</th>
                                                    <th>ปี</th>
                                                    <th>เลขที่ธุรกรรม</th>
                                                    <th>บัญชีธนาคาร</th>
                                                    <th>ประเภทรายการ</th>
                                                    <th>ฝาก/เข้า (+)</th>
                                                    <th>ถอน/ออก (-)</th>
                                                    <th>ค่าธรรมเนียม</th>
                                                    <th>ยอดเงินคงเหลือสะสม</th>
                                                    <th>เลขอ้างอิงสลิป/เช็ค</th>
                                                    <th>สลิปหลักฐาน</th>
                                                    <th>หมายเหตุ</th>
                                                    <th>แก้ไข</th>
                                                    <th>ลบรายการ</th>
                                                </tr>
                                                </tfoot>
                                            </table>

                                            <div id="result"></div>
                                        </div>

                                        <!-- Modal Form -->
                                        <div class="modal fade" id="recordModal">
                                            <div class="modal-dialog modal-lg">
                                                <div class="modal-content">
                                                    <div class="modal-header">
                                                        <h4 class="modal-title">บันทึกธุรกรรมฝาก/ถอน/ปรับยอด</h4>
                                                        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                                                    </div>
                                                    <form method="post" id="recordForm" enctype="multipart/form-data">
                                                        <div class="modal-body">
                                                            <div class="row">
                                                                <div class="col-md-6">
                                                                    <div class="form-group">
                                                                        <label for="bank_account_id" class="control-label"><b>* บัญชีธนาคาร (Bank Account)</b></label>
                                                                        <select id="bank_account_id" name="bank_account_id" class="form-control" required>
                                                                            <option value="">-- เลือกบัญชีธนาคาร --</option>
                                                                            <?php foreach ($bank_accounts as $acc) { ?>
                                                                                <option value="<?php echo $acc['id']; ?>">
                                                                                    <?php echo "[{$acc['account_code']}] {$acc['bank_name']} - {$acc['account_no']} ({$acc['account_name']})"; ?>
                                                                                </option>
                                                                            <?php } ?>
                                                                        </select>
                                                                    </div>
                                                                </div>
                                                                <div class="col-md-6">
                                                                    <div class="form-group">
                                                                        <label for="transaction_date" class="control-label"><b>* วันเวลาทำรายการ (ใน Slip)</b></label>
                                                                        <input type="text" class="form-control" id="transaction_date" name="transaction_date" required placeholder="dd-mm-yyyy hh:ii">
                                                                    </div>
                                                                </div>
                                                            </div>

                                                            <div class="row">
                                                                <div class="col-md-6">
                                                                    <div class="form-group">
                                                                        <label for="transaction_type" class="control-label"><b>* ประเภทธุรกรรม (Transaction Type)</b></label>
                                                                        <select id="transaction_type" name="transaction_type" class="form-control" required>
                                                                            <option value="">-- เลือกประเภทรายการ --</option>
                                                                            <option value="DEPOSIT">เงินฝาก (Deposit)</option>
                                                                            <option value="WITHDRAW">เงินถอน (Withdrawal)</option>
                                                                            <option value="INTEREST">ดอกเบี้ยรับ (Interest)</option>
                                                                            <option value="FEE">ค่าธรรมเนียมธนาคาร (Fee)</option>
                                                                            <option value="ADJUST_ADD">ปรับปรุงยอดเพิ่ม (+)</option>
                                                                            <option value="ADJUST_SUB">ปรับปรุงยอดลด (-)</option>
                                                                        </select>
                                                                    </div>
                                                                </div>
                                                                <div class="col-md-6">
                                                                    <div class="form-group">
                                                                        <label for="amount" class="control-label"><b>* จำนวนเงิน (Amount)</b></label>
                                                                        <input type="number" step="0.01" class="form-control" id="amount" name="amount" required min="0.01" placeholder="0.00">
                                                                    </div>
                                                                </div>
                                                            </div>

                                                            <div class="row">
                                                                <div class="col-md-6">
                                                                    <div class="form-group">
                                                                        <label for="fee" class="control-label">ค่าธรรมเนียม (ถ้ามี)</label>
                                                                        <input type="number" step="0.01" class="form-control" id="fee" name="fee" value="0.00" min="0">
                                                                    </div>
                                                                </div>
                                                                <div class="col-md-6">
                                                                    <div class="form-group">
                                                                        <label for="ref_no" class="control-label">เลขอ้างอิงเพิ่มเติม (Ref No. / Check No.)</label>
                                                                        <input type="text" class="form-control" id="ref_no" name="ref_no" placeholder="ระบุเลขอ้างอิง หรือเลขเช็ค">
                                                                    </div>
                                                                </div>
                                                            </div>

                                                            <div class="row">
                                                                <div class="col-md-12">
                                                                    <div class="form-group">
                                                                        <label for="picture_slip" class="control-label">แนบภาพสลิปหลักฐาน (Upload Slip)</label>
                                                                        <input type="file" class="form-control-file" id="picture_slip" name="picture_slip" accept="image/*,application/pdf">
                                                                        <small class="form-text text-muted">รองรับไฟล์รูปภาพ JPG, PNG หรือไฟล์ PDF</small>
                                                                        <div id="existing_slip_container" class="mt-2"></div>
                                                                    </div>
                                                                </div>
                                                            </div>

                                                            <div class="row">
                                                                <div class="col-md-12">
                                                                    <div class="form-group">
                                                                        <label for="description" class="control-label">รายละเอียด / คำอธิบาย</label>
                                                                        <textarea class="form-control" id="description" name="description" rows="3" placeholder="ระบุเหตุผล หรือรายละเอียดเพิ่มเติม"></textarea>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div class="modal-footer">
                                                            <input type="hidden" name="id" id="id"/>
                                                            <input type="hidden" name="action" id="action" value=""/>
                                                            <span class="icon-input-btn">
                                                                <i class="fa fa-check"></i>
                                                                <input type="submit" name="save" id="save" class="btn btn-primary" value="Save"/>
                                                            </span>
                                                            <button type="button" class="btn btn-danger" data-dismiss="modal">Close <i class="fa fa-times"></i></button>
                                                        </div>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Slip Modal -->
                                        <div class="modal fade" id="slipModal" tabindex="-1" role="dialog" aria-hidden="true">
                                            <div class="modal-dialog modal-dialog-centered modal-lg">
                                                <div class="modal-content">
                                                    <div class="modal-header">
                                                        <h5 class="modal-title">ภาพหลักฐาน/สลิปการโอนเงิน</h5>
                                                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                                            <span aria-hidden="true">&times;</span>
                                                        </button>
                                                    </div>
                                                    <div class="modal-body text-center" id="slipModalBody">
                                                        <!-- content goes here -->
                                                    </div>
                                                    <div class="modal-footer">
                                                        <button type="button" class="btn btn-secondary" data-dismiss="modal">ปิด</button>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                    </section>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
            <?php include('includes/Footer.php'); ?>
        </div>
    </div>

    <?php
    include('includes/Modal-Logout.php');
    ?>

    <a class="scroll-to-top rounded" href="#page-top">
        <i class="fas fa-angle-up"></i>
    </a>

    <script src="vendor/jquery/jquery.min.js"></script>
    <script src="vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
    <script src="vendor/jquery-easing/jquery.easing.min.js"></script>
    <script src="js/myadmin.min.js"></script>

    <script src="vendor/bootstrap-datepicker/js/bootstrap-datepicker.min.js"></script>
    <script src="vendor/date-picker-1.9/js/bootstrap-datepicker.js"></script>
    <script src="vendor/date-picker-1.9/locales/bootstrap-datepicker.th.min.js"></script>
    <link href="vendor/date-picker-1.9/css/bootstrap-datepicker.css" rel="stylesheet"/>

    <script src="vendor/datatables/v11/bootbox.min.js"></script>
    <script src="vendor/datatables/v11/jquery.dataTables.min.js"></script>
    <link rel="stylesheet" href="vendor/datatables/v11/jquery.dataTables.min.css"/>
    
    <!-- Flatpickr (For datetime fields) -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <script src="https://cdn.jsdelivr.net/npm/flatpickr/dist/l10n/th.js"></script>

    <script>
        $(document).ready(function () {
            // Setup DateTime pickers
            flatpickr("#transaction_date", {
                enableTime: true,
                dateFormat: "d-m-Y H:i",
                defaultDate: new Date(),
                locale: "th",
                time_24hr: true
            });

            $('.datepicker').datepicker({
                format: "dd-mm-yyyy",
                todayHighlight: true,
                language: "th",
                autoclose: true
            });

            // Initialize filters
            let filterAccount = '';
            let filterStartDate = '';
            let filterEndDate = '';

            let dataRecords = $('#TableRecordList').DataTable({
                'lengthMenu': [[10, 20, 50, 100], [10, 20, 50, 100]],
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
                'scrollX': true,
                'order': [[0, 'desc']], // Sort by transaction date descending
                'ajax': {
                    'url': 'model/manage_bank_transaction_process.php',
                    'data': function(d) {
                        d.action = "GET_TRANSACTIONS";
                        d.sub_action = "GET_MASTER";
                        d.filter_account = $('#filter_account').val();
                        d.filter_start_date = $('#filter_start_date').val();
                        d.filter_end_date = $('#filter_end_date').val();
                        return d;
                    }
                },
                'columns': [
                    {data: 'transaction_date'},
                    {data: 'txn_month'},
                    {data: 'txn_year'},
                    {data: 'doc_no'},
                    {data: 'bank_account'},
                    {data: 'transaction_type'},
                    {data: 'deposit_amount', className: 'dt-body-right'},
                    {data: 'withdraw_amount', className: 'dt-body-right'},
                    {data: 'fee', className: 'dt-body-right'},
                    {data: 'running_balance', className: 'dt-body-right'},
                    {data: 'ref_no'},
                    {data: 'picture_slip'},
                    {data: 'description'},
                    {data: 'update'},
                    {data: 'delete'}
                ]
            });

            $('#btnFilterSearch').on('click', function () {
                dataRecords.ajax.reload();
            });

            $('#btnReload').on('click', function () {
                $('#filter_account').val('');
                $('#filter_start_date').val('');
                $('#filter_end_date').val('');
                dataRecords.ajax.reload();
            });

            $("#btnAdd").click(function () {
                $('#recordForm')[0].reset();
                $('#recordModal').modal('show');
                $('#id').val("");
                $('#bank_account_id').val("");
                $('#transaction_type').val("");
                $('#amount').val("");
                $('#fee').val("0.00");
                $('#ref_no').val("");
                $('#description').val("");
                $('#existing_slip_container').html("");
                
                let fp = document.querySelector("#transaction_date")._flatpickr;
                fp.setDate(new Date());

                $('.modal-title').html("<i class='fa fa-plus'></i> บันทึกธุรกรรมการเงิน");
                $('#action').val('ADD');
                $('#save').val('บันทึกรายการ');
                $('#save').removeClass('btn-danger').addClass('btn-primary');
            });

            // Submit Form via FormData (to handle file uploads)
            $("#recordForm").on('submit', function (event) {
                event.preventDefault();
                $('#save').attr('disabled', 'disabled');
                
                let formData = new FormData(this);
                formData.append('action', $('#action').val());

                $.ajax({
                    url: 'model/manage_bank_transaction_process.php',
                    type: 'POST',
                    data: formData,
                    contentType: false,
                    processData: false,
                    success: function (data) {
                        alertify.success(data);
                        $('#recordForm')[0].reset();
                        $('#recordModal').modal('hide');
                        $('#save').attr('disabled', false);
                        dataRecords.ajax.reload();
                    },
                    error: function(err) {
                        alertify.error("เกิดข้อผิดพลาดในการบันทึกข้อมูล");
                        $('#save').attr('disabled', false);
                    }
                });
            });

            // Update Modal
            $("#TableRecordList").on('click', '.update', function () {
                let id = $(this).attr("id");
                let formData = {action: "GET_DATA", id: id};
                $.ajax({
                    type: "POST",
                    url: 'model/manage_bank_transaction_process.php',
                    dataType: "json",
                    data: formData,
                    success: function (response) {
                        if (response.length > 0) {
                            let data = response[0];
                            $('#recordModal').modal('show');
                            $('#id').val(data.id);
                            $('#bank_account_id').val(data.bank_account_id);
                            $('#transaction_type').val(data.transaction_type);
                            $('#amount').val(data.amount);
                            $('#fee').val(data.fee);
                            $('#ref_no').val(data.ref_no);
                            $('#description').val(data.description);
                            
                            if (data.picture_slip) {
                                let ext = data.picture_slip.split('.').pop().toLowerCase();
                                if (ext === 'pdf') {
                                    $('#existing_slip_container').html('<label class="text-info mt-2">หลักฐานที่มีอยู่: <a href="' + data.picture_slip + '" target="_blank" class="btn btn-outline-info btn-xs"><i class="fa fa-file-pdf-o"></i> ดูไฟล์ PDF เดิม</a></label>');
                                } else {
                                    $('#existing_slip_container').html('<label class="text-info mt-2">หลักฐานที่มีอยู่ (คลิกดูภาพขยาย):</label><br><img src="' + data.picture_slip + '" class="img-thumbnail img-fluid view-slip" data-url="' + data.picture_slip + '" style="max-height: 120px; cursor: pointer;" title="คลิกเพื่อดูภาพขยาย">');
                                }
                            } else {
                                $('#existing_slip_container').html('');
                            }

                            let fp = document.querySelector("#transaction_date")._flatpickr;
                            fp.setDate(data.transaction_date_raw);

                            $('.modal-title').html("<i class='fa fa-pencil'></i> แก้ไขธุรกรรมการเงิน");
                            $('#action').val('UPDATE');
                            $('#save').val('บันทึกการแก้ไข');
                            $('#save').removeClass('btn-danger').addClass('btn-primary');
                        }
                    },
                    error: function (response) {
                        alertify.error("error : " + response);
                    }
                });
            });

            // Delete Confirm Modal
            $("#TableRecordList").on('click', '.delete', function () {
                let id = $(this).attr("id");
                
                bootbox.confirm({
                    title: "ยืนยันการยกเลิกรายการ?",
                    message: "คุณต้องการยกเลิกการบันทึกรายการธุรกรรมนี้หรือไม่? ระบบจะทำการปรับยอดคงเหลือสะสมในบัญชีธนาคารกลับคืนให้โดยอัตโนมัติ",
                    buttons: {
                        cancel: {
                            label: '<i class="fa fa-times"></i> ปิด',
                            className: 'btn-secondary'
                        },
                        confirm: {
                            label: '<i class="fa fa-check"></i> ยืนยันยกเลิกรายการ',
                            className: 'btn-danger'
                        }
                    },
                    callback: function (result) {
                        if(result) {
                            $.ajax({
                                type: "POST",
                                url: 'model/manage_bank_transaction_process.php',
                                data: {action: "DELETE", id: id},
                                success: function (response) {
                                    alertify.success(response);
                                    dataRecords.ajax.reload();
                                },
                                error: function (response) {
                                    alertify.error("เกิดข้อผิดพลาดในการยกเลิกรายการ");
                                }
                            });
                        }
                    }
                });
            });

            // View Slip Modal
            $(document).on('click', '.view-slip', function () {
                let fileUrl = $(this).data('url');
                let fileExt = fileUrl.split('.').pop().toLowerCase();
                
                if (fileExt === 'pdf') {
                    $('#slipModalBody').html('<iframe src="' + fileUrl + '" style="width: 100%; height: 500px; border: none;"></iframe>');
                } else {
                    $('#slipModalBody').html('<img src="' + fileUrl + '" class="img-fluid" alt="Slip" style="max-height: 70vh; max-width: 100%;">');
                }
                $('#slipModal').modal('show');
            });

        });
    </script>
    </body>
    </html>
<?php } ?>
