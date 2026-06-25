<?php
include('includes/Header.php');
if (strlen($_SESSION['alogin']) == "" || strlen($_SESSION['doc_no']) == "") {
    header("Location: index.php");
} else {
    $curr_date = date("d-m-Y");
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
                        <h1 class="h3 mb-0 text-gray-800">จัดการบัญชีธนาคาร</h1>
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="<?php echo $_SESSION['dashboard_page'] ?>">Home</a></li>
                            <li class="breadcrumb-item">การเงิน/ธนาคาร</li>
                            <li class="breadcrumb-item active" aria-current="page">จัดการบัญชีธนาคาร</li>
                        </ol>
                    </div>

                    <div class="row">
                        <div class="col-lg-12">
                            <div class="card mb-12">
                                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                                    <h6 class="m-0 font-weight-bold text-primary">รายการบัญชีธนาคารของนิติฯ</h6>
                                </div>
                                <div class="card-body">
                                    <section class="container-fluid">

                                        <div class="col-md-12 col-md-offset-2 mb-3">
                                            <button type='button' name='btnAdd' id='btnAdd' class='btn btn-primary btn-sm'>
                                                <i class="fa fa-plus"></i> เพิ่มบัญชีธนาคาร
                                            </button>
                                            <button type="button" id="btnReload" class="btn btn-outline-success btn-sm" data-toggle="tooltip" title="Reload Data">
                                                <i class="fa fa-refresh"></i> โหลดข้อมูลใหม่
                                            </button>
                                        </div>

                                        <div class="col-md-12 col-md-offset-2">
                                            <table id='TableRecordList' class='display dataTable table table-striped table-bordered text-nowrap' style="width:100%">
                                                <thead>
                                                <tr>
                                                    <th>รหัสบัญชี</th>
                                                    <th>ชื่อบัญชี</th>
                                                    <th>ธนาคาร</th>
                                                    <th>เลขที่บัญชี</th>
                                                    <th>สาขา</th>
                                                    <th>ยоดยกมา</th>
                                                    <th>วันที่ยอดยกมา</th>
                                                    <th>ยอดเงินคงเหลือปัจจุบัน</th>
                                                    <th>สถานะ</th>
                                                    <th>แก้ไข</th>
                                                    <th>ลบ</th>
                                                </tr>
                                                </thead>
                                                <tfoot>
                                                <tr>
                                                    <th>รหัสบัญชี</th>
                                                    <th>ชื่อบัญชี</th>
                                                    <th>ธนาคาร</th>
                                                    <th>เลขที่บัญชี</th>
                                                    <th>สาขา</th>
                                                    <th>ยоดยกมา</th>
                                                    <th>วันที่ยอดยกมา</th>
                                                    <th>ยอดเงินคงเหลือปัจจุบัน</th>
                                                    <th>สถานะ</th>
                                                    <th>แก้ไข</th>
                                                    <th>ลบ</th>
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
                                                        <h4 class="modal-title">ข้อมูลบัญชีธนาคาร</h4>
                                                        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                                                    </div>
                                                    <form method="post" id="recordForm">
                                                        <div class="modal-body">
                                                            <div class="row">
                                                                <div class="col-md-6">
                                                                    <div class="form-group">
                                                                        <label for="account_code" class="control-label"><b>* รหัสอ้างอิงบัญชี (Code)</b></label>
                                                                        <input type="text" class="form-control" id="account_code" name="account_code" required placeholder="เช่น ACC-001">
                                                                    </div>
                                                                </div>
                                                                <div class="col-md-6">
                                                                    <div class="form-group">
                                                                        <label for="account_name" class="control-label"><b>* ชื่อบัญชี (Account Name)</b></label>
                                                                        <input type="text" class="form-control" id="account_name" name="account_name" required placeholder="เช่น บัญชีออมทรัพย์โครงการ">
                                                                    </div>
                                                                </div>
                                                            </div>

                                                            <div class="row">
                                                                <div class="col-md-6">
                                                                    <div class="form-group">
                                                                        <label for="bank_name" class="control-label"><b>* ธนาคาร (Bank Name)</b></label>
                                                                        <select id="bank_name" name="bank_name" class="form-control" required>
                                                                            <option value="">-- เลือกธนาคาร --</option>
                                                                            <option value="ธนาคารกสิกรไทย">ธนาคารกสิกรไทย (KBANK)</option>
                                                                            <option value="ธนาคารไทยพาณิชย์">ธนาคารไทยพาณิชย์ (SCB)</option>
                                                                            <option value="ธนาคารกรุงเทพ">ธนาคารกรุงเทพ (BBL)</option>
                                                                            <option value="ธนาคารกรุงไทย">ธนาคารกรุงไทย (KTB)</option>
                                                                            <option value="ธนาคารกรุงศรีอยุธยา">ธนาคารกรุงศรีอยุธยา (BAY)</option>
                                                                            <option value="ธนาคารทหารไทยธนชาต">ธนาคารทหารไทยธนชาต (TTB)</option>
                                                                            <option value="ธนาคารออมสิน">ธนาคารออมสิน (GSB)</option>
                                                                            <option value="ธนาคารเพื่อการเกษตรและสหกรณ์การเกษตร">ธนาคารเพื่อการเกษตรฯ (ธ.ก.ส.)</option>
                                                                        </select>
                                                                    </div>
                                                                </div>
                                                                <div class="col-md-6">
                                                                    <div class="form-group">
                                                                        <label for="account_no" class="control-label"><b>* เลขที่บัญชี (Account No.)</b></label>
                                                                        <input type="text" class="form-control" id="account_no" name="account_no" required placeholder="เช่น 099-2-72819-0">
                                                                    </div>
                                                                </div>
                                                            </div>

                                                            <div class="row">
                                                                <div class="col-md-12">
                                                                    <div class="form-group">
                                                                        <label for="branch_name" class="control-label">สาขาธนาคาร (Branch Name)</label>
                                                                        <input type="text" class="form-control" id="branch_name" name="branch_name" placeholder="ระบุสาขา (ถ้ามี)">
                                                                    </div>
                                                                </div>
                                                            </div>

                                                            <div class="row">
                                                                <div class="col-md-6">
                                                                    <div class="form-group">
                                                                        <label for="opening_balance" class="control-label"><b>ยоดยกมาเริ่มต้น (Opening Balance)</b></label>
                                                                        <input type="number" step="0.01" class="form-control" id="opening_balance" name="opening_balance" value="0.00" min="0">
                                                                    </div>
                                                                </div>
                                                                <div class="col-md-6">
                                                                    <div class="form-group">
                                                                        <label for="opening_date" class="control-label"><b>วันที่บันทึกยอดยกมา (Opening Date)</b></label>
                                                                        <input type="text" class="form-control" id="opening_date" name="opening_date" value="<?php echo $curr_date ?>" required readonly>
                                                                    </div>
                                                                </div>
                                                            </div>

                                                            <div class="form-group">
                                                                <label for="status" class="control-label">สถานะการใช้งาน (Status)</label>
                                                                <select id="status" name="status" class="form-control">
                                                                    <option value="Y">ใช้งาน (Active)</option>
                                                                    <option value="N">ไม่ใช้งาน (Inactive)</option>
                                                                </select>
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

    <script>
        $(document).ready(function () {
            $('#opening_date').datepicker({
                format: "dd-mm-yyyy",
                todayHighlight: true,
                language: "th",
                autoclose: true
            });

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
                'ajax': {
                    'url': 'model/manage_bank_account_process.php',
                    'data': function(d) {
                        d.action = "GET_ACCOUNTS";
                        d.sub_action = "GET_MASTER";
                        return d;
                    }
                },
                'columns': [
                    {data: 'account_code'},
                    {data: 'account_name'},
                    {data: 'bank_name'},
                    {data: 'account_no'},
                    {data: 'branch_name'},
                    {data: 'opening_balance'},
                    {data: 'opening_date'},
                    {data: 'current_balance'},
                    {data: 'status'},
                    {data: 'update'},
                    {data: 'delete'}
                ]
            });

            $('#btnReload').on('click', function () {
                dataRecords.ajax.reload();
            });

            $("#btnAdd").click(function () {
                $('#recordModal').modal('show');
                $('#id').val("");
                $('#account_code').val("");
                $('#account_code').prop('readonly', false);
                $('#account_name').val("");
                $('#bank_name').val("");
                $('#account_no').val("");
                $('#branch_name').val("");
                $('#opening_balance').val("0.00");
                $('#opening_balance').prop('readonly', false);
                $('#opening_date').datepicker('setDate', new Date());
                $('#status').val("Y");
                $('.modal-title').html("<i class='fa fa-plus'></i> เพิ่มบัญชีธนาคาร");
                $('#action').val('ADD');
                $('#save').val('บันทึกข้อมูล');
                $('#save').removeClass('btn-danger').addClass('btn-primary');
            });

            // Submit Form
            $("#recordModal").on('submit', '#recordForm', function (event) {
                event.preventDefault();
                $('#save').attr('disabled', 'disabled');
                let formData = $(this).serialize();
                $.ajax({
                    url: 'model/manage_bank_account_process.php',
                    method: "POST",
                    data: formData,
                    success: function (data) {
                        alertify.success(data);
                        $('#recordForm')[0].reset();
                        $('#recordModal').modal('hide');
                        $('#save').attr('disabled', false);
                        dataRecords.ajax.reload();
                    },
                    error: function(err) {
                        alertify.error("เกิดข้อผิดพลาดในการบันทึก");
                        $('#save').attr('disabled', false);
                    }
                })
            });

            // Update
            $("#TableRecordList").on('click', '.update', function () {
                let id = $(this).attr("id");
                let formData = {action: "GET_DATA", id: id};
                $.ajax({
                    type: "POST",
                    url: 'model/manage_bank_account_process.php',
                    dataType: "json",
                    data: formData,
                    success: function (response) {
                        if (response.length > 0) {
                            let data = response[0];
                            $('#recordModal').modal('show');
                            $('#id').val(data.id);
                            $('#account_code').val(data.account_code);
                            $('#account_code').prop('readonly', true);
                            $('#account_name').val(data.account_name);
                            $('#bank_name').val(data.bank_name);
                            $('#account_no').val(data.account_no);
                            $('#branch_name').val(data.branch_name);
                            $('#opening_balance').val(data.opening_balance);
                            $('#opening_balance').prop('readonly', true); // Opening balance should not be edited casually
                            $('#opening_date').val(data.opening_date);
                            $('#status').val(data.status);
                            $('.modal-title').html("<i class='fa fa-pencil'></i> แก้ไขบัญชีธนาคาร");
                            $('#action').val('UPDATE');
                            $('#save').val('บันทึกข้อมูล');
                            $('#save').removeClass('btn-danger').addClass('btn-primary');
                        }
                    },
                    error: function (response) {
                        alertify.error("error : " + response);
                    }
                });
            });

            // Delete Confirm Screen
            $("#TableRecordList").on('click', '.delete', function () {
                let id = $(this).attr("id");
                let formData = {action: "GET_DATA", id: id};
                $.ajax({
                    type: "POST",
                    url: 'model/manage_bank_account_process.php',
                    dataType: "json",
                    data: formData,
                    success: function (response) {
                        if (response.length > 0) {
                            let data = response[0];
                            $('#recordModal').modal('show');
                            $('#id').val(data.id);
                            $('#account_code').val(data.account_code);
                            $('#account_code').prop('readonly', true);
                            $('#account_name').val(data.account_name);
                            $('#bank_name').val(data.bank_name);
                            $('#account_no').val(data.account_no);
                            $('#branch_name').val(data.branch_name);
                            $('#opening_balance').val(data.opening_balance);
                            $('#opening_balance').prop('readonly', true);
                            $('#opening_date').val(data.opening_date);
                            $('#status').val(data.status);
                            $('.modal-title').html("<i class='fa fa-trash'></i> ยืนยันลบบัญชีธนาคาร");
                            $('#action').val('DELETE');
                            $('#save').val('ยืนยันการลบ');
                            $('#save').removeClass('btn-primary').addClass('btn-danger');
                        }
                    },
                    error: function (response) {
                        alertify.error("error : " + response);
                    }
                });
            });

        });
    </script>
    </body>
    </html>
<?php } ?>
