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
            <h6 style="color: blue"><b>SAC LOTTO LIST</b></h6>
            <div class="form-group">
                <button type="button" name="backBtn" id="backBtn" tabindex="4"
                        class="form-control btn btn-danger">
                                            <span>
                                                <i class="fa fa-reply" aria-hidden="true"></i>
                                                กลับหน้าแรก
                                            </span>
            </div>
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
                    <th width="15%">Action</th>
                </tr>
                </thead>
                <tbody>
                <?php
                $stmt = $conn->prepare("SELECT* FROM ims_lotto order by id");
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
                        <td><?= $rows['create_date'];?></td>
                        <td><button type='button' name='delete' id='<?= $rows['lotto_number']; ?>' class='btn btn-danger btn-xs delete' data-toggle='tooltip' title='Delete'>Delete</button></td>
                    </tr>
                <?php } ?>
                </tbody>
            </table>
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

    $("#DataTable").on('click', '.delete', function () {

        if (confirm("ยืนยันการลบข้อมูล")) {

        let action = "DELETE";
        let lotto_number = $(this).attr("id");

            let formData = {
                action: action,
                lotto_number: lotto_number
            };
            $.ajax({
                type: "POST",
                url: 'model/lotto_process.php',
                data: formData,
                success: function (response) {
                    if (response > 1) {
                        alertify.error("ไม่สามารถลบข้อมูลได้");
                    } else {
                        alertify.success("ลบข้อมูลสำเร็จ");
                        location.reload();
                    }
                },
                error: function (response) {
                    alertify.error("error : " + response);
                }
            });

          }

    });


</script>

</body>
</html>