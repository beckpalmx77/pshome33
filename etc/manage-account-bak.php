<!DOCTYPE html>
<html lang="th">

<?php
include('includes/Header.php');
?>

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
                    <h1 class="h3 mb-0 text-gray-800"><?php echo $_GET['s'] ?></h1>
                    <ol class="breadcrumb">
                        <!--li class="breadcrumb-item"><a href="./">Home</a></li-->
                        <li class="breadcrumb-item"><?php echo $_GET['m'] ?></li>
                        <li class="breadcrumb-item active" aria-current="page"><?php echo $_GET['s'] ?></li>
                    </ol>
                </div>

                <div class="row">
                    <div class="col-lg-12">
                        <div class="card mb-12">
                            <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                            </div>
                            <div class="card-body">

                                <section class="container-fluid">
                                    <div class="row">
                                        <div class="col-md-12 col-md-offset-2">
                                            <div class="panel">
                                                <div class="panel-body">

                                                    <table id="TableRecordList"
                                                           class="display table table-striped table-bordered"
                                                           cellspacing="0" width="100%">
                                                        <thead>
                                                        <tr>
                                                            <th>#</th>
                                                            <th>ชื่อผู้ใช้</th>
                                                            <th>ชื่อ-นามสกุล</th>
                                                            <th>ประเภทผู้ใช้งาน</th>
                                                            <th>Icon</th>
                                                            <th>Action</th>
                                                        </tr>
                                                        </thead>
                                                        <tfoot>
                                                        <tr>
                                                            <th>#</th>
                                                            <th>ชื่อผู้ใช้</th>
                                                            <th>ชื่อ-นามสกุล</th>
                                                            <th>ประเภทผู้ใช้งาน</th>
                                                            <th>Icon</th>
                                                            <th>Action</th>
                                                        </tr>
                                                        </tfoot>
                                                        <tbody>
                                                        <?php $sql = "SELECT * FROM ims_user ";
                                                        $query = $conn->prepare($sql);
                                                        $query->execute();
                                                        $results = $query->fetchAll(PDO::FETCH_OBJ);
                                                        $cnt = 1;
                                                        if ($query->rowCount() > 0) {
                                                            foreach ($results as $result) {

                                                                $id = $result->id;

                                                                ?>
                                                                <tr>
                                                                    <td><?php echo htmlentities($cnt); ?></td>
                                                                    <td><?php echo htmlentities($result->email); ?></td>
                                                                    <td><?php echo htmlentities($result->first_name) . " " . htmlentities($result->last_name); ?></td>
                                                                    <td><?php echo htmlentities($result->account_type); ?></td>
                                                                    <td>
                                                                        <img src="<?php echo htmlentities($result->picture); ?>"
                                                                             width="28" height="28"</td>
                                                                    <td>
                                                                        <!--a href="edit-account.php?aid=<?php echo $id; ?>"><i
                                                                                    class="fa fa-edit"
                                                                                    title="Edit Account"></i>
                                                                        </a-->

                                                                        <a href="javascript: update_data(<?php echo $id; ?>)"><i
                                                                                    class="fa fa-edit"
                                                                                    title="Update Account"></i></a>
                                                                        &nbsp;
                                                                        <a href="reset-account-password.php?aid=<?php echo $id; ?>"
                                                                           id="del"><i
                                                                                    class="fa fa-refresh"
                                                                                    title="Reset Account Password"></i>
                                                                        </a>
                                                                        &nbsp;
                                                                        <a href="javascript: delete_id(<?php echo $id; ?>)"><i
                                                                                    class="fa fa-times"
                                                                                    title="Delete Record"></i></a>
                                                                    </td>
                                                                </tr>
                                                                <?php $cnt = $cnt + 1;
                                                            }
                                                        } ?>


                                                        </tbody>
                                                    </table>


                                                    <div id="result"></div>

                                                </div>
                                            </div>
                                        </div>
                                        <!-- /.col-md-8 col-md-offset-2 -->
                                    </div>
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
                                <label for="email" class="control-label">User Name</label>
                                <input type="email" class="form-control" id="email" name="email" placeholder="email"
                                       required>
                            </div>
                            <div class="form-group">
                                <label for="first_name" class="control-label">First Name</label>
                                <input type="text" class="form-control" id="first_name" name="first_name"
                                       placeholder="First Name">
                            </div>
                            <div class="form-group">
                                <label for="last_name" class="control-label">Last Name</label>
                                <input type="text" class="form-control" id="last_name" name="last_name"
                                       placeholder="Last Name" required>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <input type="hidden" name="id" id="id"/>
                            <input type="hidden" name="action" id="action" value=""/>
                            <input type="submit" name="save" id="save" class="btn btn-primary" value="Save"/>
                            <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
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

<script src="vendor/datatables/dataTables.bootstrap4.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/bootbox.js/5.5.2/bootbox.min.js"></script>


<script src="https://cdn.datatables.net/1.11.0/js/jquery.dataTables.min.js"></script>
<link rel="stylesheet" href="https://cdn.datatables.net/1.11.0/css/jquery.dataTables.min.css"/>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.5.3/dist/js/bootstrap.min.js"></script>


<!--script>
    $(document).ready(function () {
        $('#TableRecordList').DataTable();
    });
</script-->


<script>
    <script>
    $(document).ready(function(){
        $('#TableRecordList').DataTable({
            'processing': true,
            'serverSide': true,
            'serverMethod': 'post',
            'ajax': {
                'url':'get_records.php'
            },
            'columns': [
                { data: 'id' },
                { data: 'email' },
                { data: 'first_name' },
                { data: 'last_name' },
                { data: 'status' },
            ]
        });
    });
</script>
</script>

<script type="text/javascript">

    function delete_id(id) {

        bootbox.confirm({
            title: "Confirm Action",
            message: "ต้องการลบข้อมูลออกจากระบบ ?",
            buttons: {
                cancel: {
                    label: '<i class="fa fa-times"></i> Cancel'
                },
                confirm: {
                    label: '<i class="fa fa-check"></i> Confirm'
                }
            },
            callback: function (result) {

                if (result == true) {
                    let formData = {action: "DELETE", id: id};
                    $.post("model/manage_account_process.php", formData, function (response, data) {
                        if (response == 1) {
                            alertify.success("ลบข้อมูลเรียบร้อย Delete Data Success");
                        } else {
                            alertify.error("ไม่สามารถบันทึกข้อมูลได้ DB Error ");
                        }
                    });
                }

            }
        });

    }
</script>

<script>

    function update_data(id) {

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

                    $('#recordModal').modal('show');
                    $('#id').val(id);
                    $('#email').val(email);
                    $('#first_name').val(first_name);
                    $('#last_name').val(last_name);
                    $('.modal-title').html("<i class='fa fa-plus'></i> Edit Records");
                    $('#action').val('UPDATE');
                    $('#save').val('Save');

                }
            },
            error: function (response) {
                alert("error : " + response);
            }
        });
    }

</script>


<script>
    $("#recordModal").on('submit','#recordForm', function(event){
        event.preventDefault();
        $('#save').attr('disabled','disabled');
        let formData = $(this).serialize();

        $.ajax({
            url: 'model/manage_account_process.php',
            method:"POST",
            data:formData,
            success:function(data){
                alert(data);
                $('#recordForm')[0].reset();
                $('#recordModal').modal('hide');
                $('#save').attr('disabled', false);
            }
        })
    });
</script>

</body>

</html>