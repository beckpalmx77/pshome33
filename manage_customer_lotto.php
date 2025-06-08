<?php
include('includes/Header.php');
?>

<!DOCTYPE html>
<html lang="th">
<body id="page-top">
<div id="wrapper">

    <div id="content-wrapper" class="d-flex flex-column">
        <div id="content">
            <!-- Container Fluid-->
            <div class="container-fluid" id="container-wrapper">
                <div class="row">
                    <div class="col-lg-12">
                        <div class="card mb-12">
                            <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                                <div><img src="img/logo/logo text-01.png" width="200" height="79"/></div>
                                <h6 class="m-0 font-weight-bold text-primary">จัดการรายชื่อ Sale</h6>
                            </div>
                            <div class="card-body">
                                <section class="container-fluid">

                                    <div class="col-md-12 col-md-offset-2">
                                        <label for="name_t"
                                               class="control-label"><b>เพิ่ม</b></label>

                                        <button type='button' name='btnAdd' id='btnAdd'
                                                class='btn btn-primary btn-xs'>Add
                                            <i class="fa fa-plus"></i>
                                        </button>
                                        <button type="button" id="backBtn" class="btn btn-danger flex-fill">
                                            <span><i class="fa fa-reply" aria-hidden="true"></i> กลับหน้าแรก</span>
                                        </button>
                                    </div>
                                    <br>
                                    <div class="col-md-12 col-md-offset-2">
                                        <table id='TableRecordList' class='display dataTable'>
                                            <thead>
                                            <tr>
                                                <th>ชื่อ Sale</th>
                                                <th>หมายเหตุ</th>
                                                <th>Status</th>
                                                <th>Action</th>
                                                <th>Action</th>
                                            </tr>
                                            </thead>
                                            <tfoot>
                                            <tr>
                                                <th>ชื่อ Sale</th>
                                                <th>หมายเหตุ</th>
                                                <th>Status</th>
                                                <th>Action</th>
                                                <th>Action</th>
                                            </tr>
                                            </tfoot>
                                        </table>

                                        <div id="result"></div>

                                    </div>

                                    <div class="modal fade" id="recordModal">
                                        <div class="modal-dialog modal-lg">
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <h4 class="modal-title">Modal title</h4>
                                                    <button type="button" class="close" data-dismiss="modal"
                                                            aria-hidden="true">×
                                                    </button>
                                                </div>
                                                <form method="post" id="recordForm">
                                                    <div class="modal-body">
                                                        <div class="modal-body">

                                                            <div class="form-group">
                                                                <label for="customer_name"
                                                                       class="control-label">ชื่อ Sale</label>
                                                                <input type="customer_name" class="form-control"
                                                                       id="customer_name" name="customer_name"
                                                                       placeholder="">
                                                            </div>

                                                            <div class="form-group">
                                                                <label for="name_t"
                                                                       class="control-label">หมายเหตุ</label>
                                                                <input type="text" class="form-control"
                                                                       id="remark"
                                                                       name="remark"
                                                                       required="required"
                                                                       placeholder="">
                                                            </div>

                                                            <div class="form-group">
                                                                <label for="status" class="control-label">Status</label>
                                                                <select id="status" name="status"
                                                                        class="form-control" data-live-search="true"
                                                                        title="Please select">
                                                                    <option value="Y">Y</option>
                                                                    <option value="N">N</option>
                                                                </select>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="modal-footer">
                                                        <input type="hidden" name="id" id="id"/>
                                                        <input type="hidden" name="action" id="action" value=""/>
                                                        <span class="icon-input-btn">
                                                                <i class="fa fa-check"></i>
                                                            <input type="submit" name="save" id="save"
                                                                   class="btn btn-primary" value="Save"/>
                                                            </span>
                                                        <button type="button" class="btn btn-danger"
                                                                data-dismiss="modal">Close <i
                                                                    class="fa fa-window-close"></i>
                                                        </button>
                                                    </div>
                                                </form>

                                            </div>
                                        </div>
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


<!-- Scroll to top -->
<a class="scroll-to-top rounded" href="#page-top">
    <i class="fas fa-angle-up"></i>
</a>


<script src="vendor/jquery/jquery.min.js"></script>
<script src="vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
<script src="vendor/jquery-easing/jquery.easing.min.js"></script>
<script src="js/myadmin.min.js"></script>

<!-- Page level plugins -->

<!--script src="https://cdnjs.cloudflare.com/ajax/libs/bootbox.js/5.5.2/bootbox.min.js"></script>
<script src="https://cdn.datatables.net/1.11.0/js/jquery.dataTables.min.js"></script>
<link rel="stylesheet" href="https://cdn.datatables.net/1.11.0/css/jquery.dataTables.min.css"/>
<link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.0.0/css/buttons.dataTables.min.css"/-->

<script src="vendor/datatables/v11/bootbox.min.js"></script>
<script src="vendor/datatables/v11/jquery.dataTables.min.js"></script>
<link rel="stylesheet" href="vendor/datatables/v11/jquery.dataTables.min.css"/>
<link rel="stylesheet" href="vendor/datatables/v11/buttons.dataTables.min.css"/>

<style>

    .icon-input-btn {
        display: inline-block;
        position: relative;
    }

    .icon-input-btn input[type="submit"] {
        padding-left: 2em;
    }

    .icon-input-btn .fa {
        display: inline-block;
        position: absolute;
        left: 0.65em;
        top: 30%;
    }
</style>
<script>
    $(document).ready(function () {
        $(".icon-input-btn").each(function () {
            let btnFont = $(this).find(".btn").css("font-size");
            let btnColor = $(this).find(".btn").css("color");
            $(this).find(".fa").css({'font-size': btnFont, 'color': btnColor});
        });
    });
</script>

<script>

    $("#remark").blur(function () {
        let method = $('#action').val();
        if (method === "ADD") {
            let customer_name = $('#customer_name').val();
            let remark = $('#remark').val();
            let formData = {action: "SEARCH", customer_name: customer_name, remark: remark};
            $.ajax({
                url: 'model/manage_customer_lotto_process.php',
                method: "POST",
                data: formData,
                success: function (data) {
                    if (data == 2) {
                        alert("Duplicate มีข้อมูลนี้แล้วในระบบ กรุณาตรวจสอบ");
                    }
                }
            })
        }
    });

</script>

<script>
    $(document).ready(function () {
        let formData = {action: "GET_CUSTOMER_LOTTO", sub_action: "GET_MASTER"};
        let dataRecords = $('#TableRecordList').DataTable({
            'lengthMenu': [[5, 10, 20, 50, 100], [5, 10, 20, 50, 100]],
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
            'autoWidth': true,
            'searching': true,
            <?php  if ($_SESSION['deviceType'] !== 'computer') {
                echo "'scrollX': true,";
            }?>
            'serverMethod': 'post',
            'ajax': {
                'url': 'model/manage_customer_lotto_process.php',
                'data': formData
            },
            'columns': [
                {data: 'customer_name'},
                {data: 'remark'},
                {data: 'status'},
                {data: 'update'},
                {data: 'delete'}
            ]
        });

        <!-- *** FOR SUBMIT FORM *** -->
        $("#recordModal").on('submit', '#recordForm', function (event) {
            event.preventDefault();
            $('#save').attr('disabled', 'disabled');
            let formData = $(this).serialize();
            $.ajax({
                url: 'model/manage_customer_lotto_process.php',
                method: "POST",
                data: formData,
                success: function (data) {
                    alertify.success(data);
                    $('#recordForm')[0].reset();
                    $('#recordModal').modal('hide');
                    $('#save').attr('disabled', false);
                    dataRecords.ajax.reload();
                }
            })
        });
        <!-- *** FOR SUBMIT FORM *** -->
    });
</script>

<script>
    $(document).ready(function () {
        $("#btnAdd").click(function () {
            $('#recordModal').modal('show');
            $('#id').val("");
            $('#customer_name').val("");
            $('#remark').val("");
            $('.modal-title').html("<i class='fa fa-plus'></i> ADD Record");
            $('#action').val('ADD');
            $('#save').val('Save');
        });
    });
</script>

<script>

    $("#TableRecordList").on('click', '.update', function () {
        let id = $(this).attr("id");
        //alert(id);
        let formData = {action: "GET_DATA", id: id};
        $.ajax({
            type: "POST",
            url: 'model/manage_customer_lotto_process.php',
            dataType: "json",
            data: formData,
            success: function (response) {
                let len = response.length;
                for (let i = 0; i < len; i++) {
                    let id = response[i].id;
                    let customer_name = response[i].customer_name;
                    let remark = response[i].remark;
                    let status = response[i].status;

                    $('#recordModal').modal('show');
                    $('#id').val(id);
                    $('#customer_name').val(customer_name);
                    $('#remark').val(remark);
                    $('#status').val(status);
                    $('.modal-title').html("<i class='fa fa-plus'></i> Edit Record");
                    $('#action').val('UPDATE');
                    $('#save').val('Save');
                }
            },
            error: function (response) {
                alertify.error("error : " + response);
            }
        });
    });

</script>

<script>

    $("#TableRecordList").on('click', '.delete', function () {
        let id = $(this).attr("id");
        let formData = {action: "GET_DATA", id: id};
        $.ajax({
            type: "POST",
            url: 'model/manage_customer_lotto_process.php',
            dataType: "json",
            data: formData,
            success: function (response) {
                let len = response.length;
                for (let i = 0; i < len; i++) {
                    let id = response[i].id;
                    let customer_name = response[i].customer_name;
                    let remark = response[i].remark;
                    let status = response[i].status;

                    $('#recordModal').modal('show');
                    $('#id').val(id);
                    $('#customer_name').val(customer_name);
                    $('#remark').val(remark);
                    $('#status').val(status);
                    $('.modal-title').html("<i class='fa fa-minus'></i> Delete Record");
                    $('#action').val('DELETE');
                    $('#save').val('Confirm Delete');
                }
            },
            error: function (response) {
                alertify.error("error : " + response);
            }
        });
    });

</script>

<script>
    $(document).ready(function () {
        $("#backBtn").click(function () {
            window.location.href = "sac_lotto_dashboard";
        });
    });
</script>

</body>
</html>