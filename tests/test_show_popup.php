<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>โครงการบ้าน .....</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.0/css/bootstrap.min.css">
    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.0/js/bootstrap.min.js"></script>

    <script>
        $(document).ready(function () {
            $("#myModal").modal('show');
        });
    </script>

    <script>
        $(document).ready(function () {
            $("#btnSave").click(function () {
                if ($('#full_name').val() == '' && $('#phone').val() == '') {
                    alert("กรุณาป้อน ชื่อ - หมายเลขโทรศัพท์ ");
                    $('#action').val("SEND");
                } else {
                    let formData = $('#ContactForm').serialize();
                    $.ajax({
                        url: 'TEST_POPUP_API.php',
                        method: "POST",
                        data: formData,
                        success: function (data) {
                            alert(data);
                            $("#myModal").modal('hide');
                        }
                    })
                }
            });
        });
    </script>

</head>
<body>
<div id="myModal" class="modal fade">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">โครงการบ้าน .....</h5>
                <button type="button" class="close" data-dismiss="modal">&times;</button>
            </div>
            <div class="modal-body">
                <p>ติดต่อเราเพื่อนัดหมายเข้าเยี่ยมชมโครงการ</p>
                <form id="ContactForm">
                    <input type="hidden" class="form-control" id="action" name="action">
                    <div class="form-group">
                        <input type="text" class="form-control" id="full_name" name="full_name"
                               placeholder="ชื่อ - นามสกุล">
                    </div>
                    <div class="form-group">
                        <input type="text" class="form-control" id="phone" name="phone" placeholder="หมายเลขโทรศัพท์">
                    </div>
                    <div class="form-group">
                        <input type="email" class="form-control" id="email_address" name="email_address"
                               placeholder="อีเมล์">
                    </div>
                    <button type="button" class="btn btn-primary" id="btnSave">Send</button>
                </form>
            </div>
        </div>
    </div>
</div>
</body>
</html>