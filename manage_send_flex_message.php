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
        .preview-item {
            position: relative;
            width: 200px;
            height: 200px;
            border: 2px solid #ddd;
            border-radius: 10px;
            overflow: hidden;
            background-color: #f8f9fc;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }
        .preview-item img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        .remove-btn {
            position: absolute;
            top: 8px;
            right: 8px;
            background: rgba(255, 0, 0, 0.85);
            color: white;
            border: none;
            border-radius: 50%;
            width: 30px;
            height: 30px;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 18px;
            font-weight: bold;
            transition: all 0.3s;
            z-index: 10;
        }
        .remove-btn:hover {
            background: rgba(255, 0, 0, 1);
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
    <?php include('includes/Side-Bar.php'); ?>

    <div id="content-wrapper" class="d-flex flex-column">
        <div id="content">
            <?php include('includes/Top-Bar.php'); ?>

            <!-- Container Fluid-->
            <div class="container-fluid" id="container-wrapper">
                <div class="d-sm-flex align-items-center justify-content-between mb-4">
                    <h1 class="h3 mb-0 text-gray-800"><?php echo isset($_GET['s']) ? urldecode($_GET['s']) : 'ส่งข้อความ LINE Flex Message' ?></h1>
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="<?php echo $_SESSION['dashboard_page'] ?>">Home</a></li>
                        <?php if(isset($_GET['m'])) { ?><li class="breadcrumb-item"><?php echo urldecode($_GET['m']) ?></li><?php } ?>
                        <li class="breadcrumb-item active" aria-current="page"><?php echo isset($_GET['s']) ? urldecode($_GET['s']) : 'Send Flex Message' ?></li>
                    </ol>
                </div>

                <div class="row">
                    <div class="col-lg-12">
                        <div class="card mb-4">
                            <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between bg-primary text-white">
                                <h6 class="m-0 font-weight-bold">รายละเอียดการส่งข้อความ Broadcast / Group</h6>
                            </div>
                            <div class="card-body">
                                <form id="flexMsgForm" enctype="multipart/form-data">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label for="target_group"><b>ส่งไปยังเป้าหมาย (Target)</b></label>
                                                <select class="form-control" id="target_group" name="target_group">
                                                    <option value="broadcast" style="color: red; font-weight: bold;">📢 Broadcast to All Followers (ส่งหาทุกคน)</option>
                                                    <optgroup label="Send to Specific Group">
                                                        <?php
                                                        include('config/connect_db.php');
                                                        $sql_groups = "SELECT * FROM ims_line_group WHERE status = 'Y' ORDER BY group_name";
                                                        $query_groups = $conn->prepare($sql_groups);
                                                        $query_groups->execute();
                                                        $groups = $query_groups->fetchAll(PDO::FETCH_OBJ);
                                                        foreach ($groups as $group) {
                                                            echo "<option value='{$group->group_id}'>{$group->group_name}</option>";
                                                        }
                                                        ?>
                                                    </optgroup>
                                                </select>
                                                <small id="target_warning" class="form-text text-danger" style="display:none; font-weight: bold;">
                                                    ⚠️ คำเตือน: ข้อความนี้จะถูกส่งไปยังผู้ติดตามทุกคนของ LINE OA
                                                </small>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="col-md-12">
                                            <div class="form-group">
                                                <label for="message_text"><b>ข้อความ (Message Content)</b></label>
                                                <textarea class="form-control" id="message_text" name="message_text" rows="5" placeholder="พิมพ์ข้อความที่ต้องการส่งที่นี่..." required></textarea>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="col-md-12">
                                            <div class="form-group">
                                                <label for="image_file"><b>รูปภาพประกอบ (เลือกได้หลายรูป)</b></label>
                                                <div class="custom-file">
                                                    <input type="file" class="custom-file-input" id="image_file" name="image_file[]" accept="image/*" multiple>
                                                    <label class="custom-file-label" for="image_file">เลือกไฟล์รูปภาพ...</label>
                                                </div>
                                                <div id="image_preview_container" style="display: flex; flex-wrap: wrap; gap: 15px; margin-top: 20px;"></div>
                                            </div>
                                        </div>
                                    </div>

                                    <hr>
                                    
                                    <div class="row">
                                        <div class="col-md-4 offset-md-4">
                                            <button type="submit" class="btn btn-primary btn-block btn-lg" id="btnSubmit">
                                                <i class="fab fa-line"></i> ยืนยันการส่งข้อความ
                                            </button>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <!---Container Fluid-->
        </div>
        
        <div id="loading"><i class="fa fa-spinner fa-spin fa-3x fa-fw text-primary"></i></div>
        <?php include('includes/Modal-Logout.php'); ?>
        <?php include('includes/Footer.php'); ?>
    </div>
</div>

    <script src="vendor/jquery/jquery.min.js"></script>
    <script src="vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
    <script src="vendor/jquery-easing/jquery.easing.min.js"></script>
    <script src="js/myadmin.min.js"></script>

<script>
    $(document).ready(function() {
        let selectedFiles = [];

        // Image preview and management
        $("#image_file").change(function() {
            const files = Array.from(this.files);
            
            files.forEach(file => {
                selectedFiles.push(file);
            });
            
            renderPreviews();
            $(this).val('');
            // Update custom file label
            if (selectedFiles.length > 0) {
                $('.custom-file-label').html('เลือกแล้ว ' + selectedFiles.length + ' รูปภาพ');
            } else {
                $('.custom-file-label').html('เลือกไฟล์รูปภาพ...');
            }
        });

        function renderPreviews() {
            const container = $('#image_preview_container');
            container.empty();
            
            selectedFiles.forEach((file, index) => {
                const reader = new FileReader();
                reader.onload = function(e) {
                    const previewHtml = `
                        <div class="preview-item" data-index="${index}">
                            <img src="${e.target.result}" alt="Preview">
                            <button type="button" class="remove-btn" onclick="removeImage(${index})">&times;</button>
                        </div>
                    `;
                    container.append(previewHtml);
                }
                reader.readAsDataURL(file);
            });
        }

        window.removeImage = function(index) {
            selectedFiles.splice(index, 1);
            renderPreviews();
            if (selectedFiles.length > 0) {
                $('.custom-file-label').html('เลือกแล้ว ' + selectedFiles.length + ' รูปภาพ');
            } else {
                $('.custom-file-label').html('เลือกไฟล์รูปภาพ...');
            }
        };

        $("#target_group").on('change', function() {
            if ($(this).val() === 'broadcast') {
                $("#target_warning").show();
            } else {
                $("#target_warning").hide();
            }
        });

        $("#flexMsgForm").on('submit', function(e) {
            e.preventDefault();

            const target = $("#target_group").val();
            if (target === 'broadcast') {
                if (!confirm("⚠️ คำเตือนสำคัญ!\n\nคุณกำลังจะส่งข้อความ Broadcast หาผู้ติดตามทุกคนของ LINE OA\nยืนยันการส่งข้อความนี้ใช่หรือไม่?")) {
                    return;
                }
            }
            
            var formData = new FormData(this);
            formData.delete('image_file[]');
            selectedFiles.forEach(file => {
                formData.append('image_file[]', file);
            });
            
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
                        selectedFiles = [];
                        $('#image_preview_container').empty();
                        $('.custom-file-label').html('เลือกไฟล์รูปภาพ...');
                        $("#target_warning").hide();
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
