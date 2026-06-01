<?php
include('includes/Header.php');
if (strlen($_SESSION['alogin']) == "") {
    header("Location: index.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <title>ส่งข้อความ Flex Message</title>
    <style>
        .card-header {
            background-color: #4285f4;
            color: white;
        }
        #image_preview {
            max-width: 300px;
            margin-top: 10px;
            display: none;
            border-radius: 8px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }
        #loading {
            display: none;
            position: fixed;
            z-index: 999;
            height: 2em;
            width: 2em;
            overflow: show;
            margin: auto;
            top: 0;
            left: 0;
            bottom: 0;
            right: 0;
        }
    </style>
</head>
<body id="page-top">
<div id="wrapper">
    <div id="content-wrapper" class="d-flex flex-column">
        <div id="content">
            <div class="container-fluid" id="container-wrapper">
                <div class="d-sm-flex align-items-center justify-content-between mb-4">
                    <h1 class="h3 mb-0 text-gray-800">ส่งข้อความ LINE Flex Message</h1>
                </div>

                <div class="row">
                    <div class="col-lg-8 offset-lg-2">
                        <div class="card mb-4">
                            <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                                <h6 class="m-0 font-weight-bold">รายละเอียดข้อความ</h6>
                            </div>
                            <div class="card-body">
                                <form id="flexMsgForm" enctype="multipart/form-data">
                                    <div class="form-group">
                                        <label for="target_group">ส่งไปยังกลุ่ม</label>
                                        <select class="form-control" id="target_group" name="target_group">
                                            <option value="Cd6b5e1dfc01ac62b37a7f84e9a951ae2">กลุ่มนิติ (Group 1)</option>
                                            <option value="Ca579b4e8daae57c0f07c3508696074ae">กลุ่มกรรมการ (Group 2)</option>
                                        </select>
                                    </div>
                                    <div class="form-group">
                                        <label for="message_text">ข้อความ</label>
                                        <textarea class="form-control" id="message_text" name="message_text" rows="4" placeholder="พิมพ์ข้อความที่นี่..." required></textarea>
                                    </div>
                                    <div class="form-group">
                                        <label for="image_file">รูปภาพประกอบ</label>
                                        <input type="file" class="form-control-file" id="image_file" name="image_file" accept="image/*">
                                        <img id="image_preview" src="#" alt="Preview">
                                    </div>
                                    <button type="submit" class="btn btn-primary btn-block" id="btnSubmit">
                                        <i class="fab fa-line"></i> ส่งข้อความ
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div id="loading"><i class="fa fa-spinner fa-spin fa-3x fa-fw"></i></div>
            <?php include('includes/Modal-Logout.php'); ?>
            <?php include('includes/Footer.php'); ?>
        </div>
    </div>
</div>

<script>
    $(document).ready(function() {
        // Image preview
        $("#image_file").change(function() {
            if (this.files && this.files[0]) {
                var reader = new FileReader();
                reader.onload = function(e) {
                    $('#image_preview').attr('src', e.target.result).show();
                }
                reader.readAsDataURL(this.files[0]);
            }
        });

        // Form submission
        $("#flexMsgForm").on('submit', function(e) {
            e.preventDefault();
            
            var formData = new FormData(this);
            
            $("#btnSubmit").prop("disabled", true);
            $("#loading").show();

            $.ajax({
                url: 'model/manage_send_flex_message_process.php',
                type: 'POST',
                data: formData,
                contentType: false,
                processData: false,
                success: function(response) {
                    $("#loading").hide();
                    $("#btnSubmit").prop("disabled", false);
                    if (response == 1) {
                        alertify.success("ส่งข้อความเรียบร้อยแล้ว");
                        $("#flexMsgForm")[0].reset();
                        $('#image_preview').hide();
                    } else {
                        alertify.error("เกิดข้อผิดพลาด: " + response);
                    }
                },
                error: function() {
                    $("#loading").hide();
                    $("#btnSubmit").prop("disabled", false);
                    alertify.error("ไม่สามารถเชื่อมต่อกับ Server ได้");
                }
            });
        });
    });
</script>
</body>
</html>
