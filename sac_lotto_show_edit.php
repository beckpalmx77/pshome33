<?php
include('includes/Header.php');
require_once 'config/connect_lotto_db.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <!-- bootstrap -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.1/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-F3w7mX95PdgyTmZZMECAngseQB83DfGTowi0iMjiWaeVhAn4FJkqJByhZMI3AhiU" crossorigin="anonymous">
    <!-- datatable -->
    <link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.11.3/css/jquery.dataTables.css">
    <script type="text/javascript" charset="utf8" src="https://code.jquery.com/jquery-3.5.1.js"></script>
    <script type="text/javascript" charset="utf8" src="https://cdn.datatables.net/1.11.3/js/jquery.dataTables.js"></script>

    <script src="vendor/datatables/v11/bootbox.min.js"></script>
    <script src="vendor/datatables/v11/jquery.dataTables.min.js"></script>
    <link rel="stylesheet" href="vendor/datatables/v11/jquery.dataTables.min.css"/>
    <link rel="stylesheet" href="vendor/datatables/v11/buttons.dataTables.min.css"/>

    <script>
        $(document).ready(function() {
            $('#DataTable').DataTable();
        } );
    </script>

    <title>SAC LOTTO LIST</title>
</head>
<body>
<div class="container">
    <div class="row">
        <div class="col-md-12"> <br>
            <div><img src="img/logo/logo text-01.png" width="200" height="79"/></div>
            <h6 style="color: blue"><b>SAC LOTTO LIST EDIT</b></h6>
            <table id="DataTable" class="display table table-striped  table-hover table-responsive table-bordered ">
                <thead>
                <tr>
                    <th width="5%">ลำดับ</th>
                    <th width="25%">ชื่อร้าน</th>
                    <th width="10%">หมายเลขโทรศัพท์</th>
                    <th width="15%">จังหวัด</th>
                    <th width="15%">หมายเลขที่เลือก</th>
                    <th width="15%">ชื่อ Sale</th>
                    <th width="15%">วันที่บันทึก</th>
                </tr>
                </thead>
                <tbody>
                <?php
                $stmt = $conn->prepare("SELECT * FROM ims_lotto order by id");
                $stmt->execute();
                $result = $stmt->fetchAll();
                $line_no = 0;
                foreach($result as $rows) {
                    $line_no++;
                    ?>
                    <tr>
                        <td><?= $line_no;?></td>
                        <td><?= $rows['lotto_name'];?></td>
                        <td><?= $rows['lotto_phone'];?></td>
                        <td><?= $rows['lotto_province'];?></td>
                        <td><?= $rows['lotto_number'];?></td>
                        <td><?= $rows['sale_name'];?></td>
                        <td><button type='button' name='update' id=<?php echo $rows['id']?> class='btn btn-info btn-xs update' data-toggle='tooltip' title='Update'>Update</button></td>
                    </tr>
                <?php } ?>
                </tbody>
            </table>
        </div>
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
                                <label for="lotto_name" class="control-label"><b>ชื่อลูกค้า</b></label>
                                <input type="lotto_name" class="form-control"
                                       id="lotto_name" name="lotto_name"
                                       readonly="true"
                                       placeholder="ชื่อลูกค้า">
                            </div>

                            <div class="form-group">
                                <label for="lotto_phone"
                                       class="control-label"><b>หมายเลขโทรศัพท์</b></label>
                                <input type="text" class="form-control"
                                       id="lotto_phone"
                                       name="lotto_phone"
                                       required="required"
                                       placeholder="หมายเลขโทรศัพท์">
                            </div>

                            <div class="form-group">
                                <label for="lotto_province"
                                       class="control-label"><b>จังหวัด</b></label>
                                <input type="text" class="form-control"
                                       id="lotto_province"
                                       name="lotto_province"
                                       required="required"
                                       placeholder="จังหวัด">
                            </div>

                            <div class="form-group">
                                <label for="sale_name"
                                       class="control-label"><b>ชื่อ Sale</b></label>
                                <input type="text" class="form-control"
                                       id="sale_name"
                                       name="sale_name"
                                       required="required"
                                       placeholder="ชื่อ Sale">
                            </div>

                            <div class="form-group">
                                <label for="lotto_number"
                                       class="control-label"><b>หมายเลขที่เลือก</b></label>
                                <input type="text" class="form-control"
                                       id="lotto_number"
                                       name="lotto_number"
                                       required="required"
                                       placeholder="หมายเลขที่เลือก">
                            </div>


                        </div>
                    </div>
                    <div class="modal-footer">
                        <input type="text" name="id" id="id"/>
                        <input type="hidden" name="action" id="action" value=""/>
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

<script>
    $(document).ready(function () {
        $("#backBtn").click(function () {
            window.location.href = "sac_lotto";
        });
    });
</script>

<script>

    $("#DataTable").on('click', '.update', function () {
        let id = $(this).attr("id");
        let formData = {action: "GET_DATA", id: id};
        $.ajax({
            type: "POST",
            url: 'model/lotto_process_edit.php',
            dataType: "json",
            data: formData,
            success: function (response) {
                let len = response.length;
                for (let i = 0; i < len; i++) {
                    let id = response[i].id;
                    let lotto_name = response[i].lotto_name;
                    let lotto_phone = response[i].lotto_phone;
                    let lotto_province = response[i].lotto_province;
                    let sale_name = response[i].sale_name;
                    let lotto_number = response[i].lotto_number;

                    $('#recordModal').modal('show');
                    $('#id').val(id);
                    $('#lotto_name').val(lotto_name);
                    $('#lotto_phone').val(lotto_phone);
                    $('#lotto_province').val(lotto_province);
                    $('#sale_name').val(sale_name);
                    $('#lotto_number').val(lotto_number);
                    $('.modal-title').html("<i class='fa fa-plus'></i> Detail Record");
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

</body>
</html>