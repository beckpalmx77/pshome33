<?php
include('includes/Header.php');
if (strlen($_SESSION['alogin']) == "") {
    header("Location: index.php");
} else {
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
                        <h1 class="h3 mb-0 text-gray-800"><?php echo urldecode($_GET['s']) ?></h1>
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="<?php echo $_SESSION['dashboard_page']?>">Home</a></li>
                            <li class="breadcrumb-item"><?php echo urldecode($_GET['m']) ?></li>
                            <li class="breadcrumb-item active"
                                aria-current="page"><?php echo urldecode($_GET['s']) ?></li>
                        </ol>
                    </div>

                    <div class="row">
                        <div class="col-lg-12">
                            <div class="card mb-12">
                                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                                </div>
                                <div class="card-body">

                                    <section class="container-fluid">

                                        <div class="col-md-12 col-md-offset-2">
                                            <table id='TableRecordList' class='display dataTable'>

                                                <thead>
                                                <tr>
                                                    <th>#</th>
                                                    <th>Email</th>
                                                    <th>First Name</th>
                                                    <th>Last Name</th>
                                                    <th>Type</th>
                                                    <th>Status</th>
                                                    <th>Action</th>
                                                    <th>Action</th>
                                                </tr>
                                                </thead>
                                                <tfoot>
                                                <tr>
                                                    <th>#</th>
                                                    <th>Email</th>
                                                    <th>First Name</th>
                                                    <th>Last Name</th>
                                                    <th>Type</th>
                                                    <th>Status</th>
                                                    <th>Action</th>
                                                    <th>Action</th>
                                                </tr>
                                                </tfoot>
                                            </table>

                                            <div id="result"></div>

                                        </div>

                                        <!--/div-->
                                        <!-- /.row -->

                                    </section>


                                </div>

                            </div>

                        </div>

                    </div>
                    <!--Row-->

                    <!-- Row -->

                </div>

                <div id="recordModal" class="modal fade">
                    <div class="modal-dialog">
                        <form method="post" id="recordForm">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                                    <h4 class="modal-title"><i class="fa fa-plus"></i> Edit Record</h4>
                                </div>
                                <div class="modal-body">
                                    <div class="form-group"
                                    <label for="email" class="control-label">Email</label>
                                    <input type="email" class="form-control" id="email" name="email" placeholder="email"
                                           required>
                                </div>

                                <div class="form-group">
                                    <label for="first_name" class="control-label">ชื่อ</label>
                                    <input type="text" class="form-control" id="first_name" name="first_name"
                                           placeholder="First Name">
                                </div>
                                <div class="form-group">
                                    <label for="last_name" class="control-label">นามสกุล</label>
                                    <input type="text" class="form-control" id="last_name" name="last_name"
                                           placeholder="Last Name" required>
                                </div>
                                <div class=”form-group”>
                                    <label for="account_type" class="control-label">ประเภทผู้ใช้
                                        (Administrator=จัดการระบบ)</label>
                                    <select id="account_type" name="account_type"
                                            class="form-control" data-live-search="true"
                                            title="Please select">
                                        <option
                                                value="<?php echo htmlentities($result->permission_id); ?>"
                                                selected><?php echo htmlentities($result->permission_detail); ?></option>
                                        <?php $sql1 = "SELECT * from ims_permission";
                                        $query1 = $conn->prepare($sql1);
                                        $query1->execute();
                                        $results1 = $query1->fetchAll(PDO::FETCH_OBJ);
                                        if ($query1->rowCount() > 0) {
                                            foreach ($results1 as $result1) { ?>
                                                <option
                                                        value="<?php echo htmlentities($result1->permission_id); ?>"><?php echo htmlentities($result1->permission_detail); ?></option>
                                            <?php }
                                        } ?>
                                    </select>
                                </div>
                                <div class=”form-group”>
                                    <label for="status" class="control-label">Status</label>
                                    <select id="status" name="status"
                                            class="form-control" data-live-search="true"
                                            title="Please select">
                                        <option>Active</option>
                                        <option>Inactive</option>
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
                                <button type="button" class="btn btn-danger"
                                        data-dismiss="modal">Close <i
                                            class="fa fa-window-close"></i>
                                </button>
                            </div>
                    </div>
                    </form>
                </div>

            </div>


            <!---Container Fluid-->

        </div>

        <?php
        include('includes/Modal-Logout.php');
        include('includes/Footer.php');
        ?>

    </div>
    </div>

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
        $(document).ready(function () {
            let formData = {action: "GET_ACCOUNT", sub_action: "GET_MASTER"};
            let dataRecords = $('#TableRecordList').DataTable({
                'processing': true,
                'serverSide': true,
                'serverMethod': 'post',
                'ajax': {
                    'url': 'model/manage_account_process.php',
                    'data': formData
                },
                'columns': [
                    {data: 'line_no'},
                    {data: 'email'},
                    {data: 'first_name'},
                    {data: 'last_name'},
                    {data: 'picture'},
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
                    url: 'model/manage_account_process.php',
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

        $("#TableRecordList").on('click', '.update', function () {
            let id = $(this).attr("id");
            let formData = {action: "GET_DATA", id: id};
            $.ajax({
                type: "POST",
                url: 'model/manage_account_process.php',
                dataType: "json",
                data: formData,
                success: function (response) {
                    let len = response.length;
                    for (let i = 0; i < len; i++) {
                        let id = response[i].id;
                        let email = response[i].email;
                        let first_name = response[i].first_name;
                        let last_name = response[i].last_name;
                        let account_type = response[i].account_type;
                        let status = response[i].status;

                        $('#recordModal').modal('show');
                        $('#id').val(id);
                        $('#email').val(email);
                        $('#first_name').val(first_name);
                        $('#last_name').val(last_name);
                        $('#account_type').val(account_type);
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
                url: 'model/manage_account_process.php',
                dataType: "json",
                data: formData,
                success: function (response) {
                    let len = response.length;
                    for (let i = 0; i < len; i++) {
                        let id = response[i].id;
                        let email = response[i].email;
                        let first_name = response[i].first_name;
                        let last_name = response[i].last_name;
                        let account_type = response[i].account_type;
                        let status = response[i].status;

                        $('#recordModal').modal('show');
                        $('#id').val(id);
                        $('#email').val(email);
                        $('#first_name').val(first_name);
                        $('#last_name').val(last_name);
                        $('#account_type').val(account_type);
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
            $('[data-toggle="tooltip"]').tooltip();
        });
    </script>

    <script>
        $('#check').click(function () {
            if ('password' == $('#test-input').attr('type')) {
                $('#test-input').prop('type', 'text');
            } else {
                $('#test-input').prop('type', 'password');
            }
        });
    </script>


    </body>

    </html>

<?php } ?>