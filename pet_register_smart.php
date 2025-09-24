<?php
// ไฟล์นี้ไม่จำเป็นต้องมี session หลังบ้าน แต่ต้องเชื่อมต่อ DB
// include('config/connect_db.php');
?>
<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ลงทะเบียนข้อมูลสัตว์เลี้ยง</title>
    <link href="vendor/fontawesome-free/css/all.min.css" rel="stylesheet" type="text/css">
    <link href="vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet" type="text/css">
    <link href="css/myadmin.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Prompt:wght@300;400;500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/alertifyjs@1.13.1/build/css/alertify.min.css"/>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/alertifyjs@1.13.1/build/css/themes/bootstrap.min.css"/>

    <style>
        body {
            background: url('img/mint-bg.jpg') no-repeat center center fixed;
            background-size: cover;
            font-family: 'Prompt', sans-serif;
            color: #2f4f4f;
        }

        #wrapper {
            display: flex;
            justify-content: center;
            align-items: flex-start; /* Changed to flex-start for long forms */
            min-height: 100vh;
            padding-top: 1rem;
            padding-bottom: 1rem;
        }

        .container {
            max-width: 900px; /* Limit width for better readability */
        }

        .card {
            border: none;
            border-radius: 12px;
            background-color: rgba(234, 255, 240, 0.95); /* very light mint green with transparency */
            box-shadow: 0 4px 12px rgba(144, 238, 144, 0.3);
            margin-bottom: 1rem;
        }

        label {
            font-weight: 500;
            color: #2e7d32;
        }

        .form-control {
            border-radius: 10px;
            border: 1px solid #a8d5ba;
            background-color: #ffffff;
        }

        .btn-primary {
            background-color: #90ee90;
            border-color: #90ee90;
            color: #2e7d32;
            font-weight: bold;
            border-radius: 10px;
        }

        .btn-primary:hover {
            background-color: #7cd67c;
            border-color: #7cd67c;
        }

        .img-thumbnail {
            background-color: #fff;
            border: 1px solid #ddd;
        }
    </style>
</head>

<body>
<div id="wrapper">
    <div id="content-wrapper" class="d-flex flex-column">
        <div id="content">
            <div class="container py-4">

                <div class="d-sm-flex align-items-center justify-content-between mb-4">
                    <h1 class="h5 mb-0 text-gray-800">ลงทะเบียนข้อมูลสัตว์เลี้ยง</h1>
                    <div class="d-flex align-items-center gap-3">
                        <img id="profilePic" src="" class="rounded-circle" width="50" height="50" alt="Profile Pic">
                        <div class="text-sm text-muted" id="user-info-liff"></div>
                    </div>
                </div>

                <div class="card">
                    <div class="card-body">
                        <form method="post" id="recordForm" enctype="multipart/form-data">
                            <h4>ข้อมูลบ้าน (ดึงจากโปรไฟล์ LINE)</h4>
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="house_number" class="control-label">บ้านเลขที่</label>
                                        <input type="text" class="form-control" id="house_number" name="house_number"
                                               required readonly>
                                    </div>
                                </div>
                                <div class="col-md-5">
                                    <div class="form-group">
                                        <label for="contact_name" class="control-label">ชื่อผู้ติดต่อ</label>
                                        <input type="text" class="form-control" id="contact_name" name="contact_name"
                                               required readonly>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label for="phone_number" class="control-label">หมายเลขโทรศัพท์</label>
                                        <input type="text" class="form-control" id="phone_number" name="phone_number"
                                               required placeholder="">
                                    </div>
                                </div>
                            </div>

                            <hr>
                            <h4>ข้อมูลสัตว์เลี้ยง (กรอกข้อมูลด้านล่าง)</h4>

                            <?php for ($i = 1; $i <= 6; $i++): ?>
                                <div class="row align-items-center border-bottom py-2">
                                    <div class="col-md-2">
                                        <div class="form-group">
                                            <label for="type_<?php echo $i; ?>"
                                                   class="control-label">ประเภทสัตว์เลี้ยง <?php echo $i; ?></label>
                                            <select name="type_<?php echo $i; ?>" id="type_<?php echo $i; ?>"
                                                    class="form-control">
                                                <option value="">-</option>
                                                <option value="D">สุนัข</option>
                                                <option value="C">แมว</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-2">
                                        <div class="form-group">
                                            <label for="pet_<?php echo $i; ?>"
                                                   class="control-label">ชื่อสัตว์เลี้ยง <?php echo $i; ?></label>
                                            <input type="text" class="form-control" id="pet_<?php echo $i; ?>"
                                                   name="pet_<?php echo $i; ?>" placeholder="">
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label for="picture_pet_<?php echo $i; ?>"
                                                   class="control-label">เลือกรูปภาพ</label>
                                            <input type="file" name="picture_pet_<?php echo $i; ?>"
                                                   id="picture_pet_<?php echo $i; ?>" class="form-control">
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group mb-0">
                                            <img id="preview_pet_<?php echo $i; ?>" src="#" alt="Preview"
                                                 class="img-thumbnail"
                                                 style="display: none; max-height: 90px; margin-top: 10px;"/>
                                        </div>
                                    </div>
                                </div>
                            <?php endfor; ?>
                            <div id="loading" style="display: none; text-align: center; margin-top: 20px;">
                                <p>กำลังบันทึกข้อมูล...</p>
                            </div>

                            <div class="modal-footer mt-4">
                                <input type="hidden" name="id" id="id"/>
                                <input type="hidden" name="action" id="action" value="UPDATE"/>
                                <button type="submit" name="save" id="save" class="btn btn-primary btn-block">
                                    บันทึกข้อมูล
                                </button>
                            </div>

                            <div class="modal-footer mt-4">
                                <button type="button" id="BackBtn" class="btn btn-danger btn-block">
                                    กลับหน้าแรก
                                </button>
                            </div>


                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="vendor/jquery/jquery.min.js"></script>
<script src="vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/alertifyjs@1.13.1/build/alertify.min.js"></script>
<script src="https://static.line-scdn.net/liff/edge/2/sdk.js"></script>
<script src="line_oa/house/jsconfig/config_pet_register.js"></script>

<script>

    liff.init({liffId: LIFF_ID})
        .then(() => {
            if (!liff.isLoggedIn()) {
                liff.login();
            } else {
                liff.getProfile().then(profile => {
                    const userId = profile.userId;
                    const pictureUrl = profile.pictureUrl;
                    const displayName = profile.displayName;

                    $('#profilePic').attr('src', pictureUrl);
                    $('#user-info-liff').text(`ผู้ใช้: ${displayName}`);

                    // Fetch user's pet record data from backend
                    $.ajax({
                        type: "POST",
                        url: 'model/manage_pet_record_process.php', // ใช้ process เดิม
                        dataType: "json",
                        data: {action: "GET_DATA_BY_LIFF", userId: userId},
                        success: function (response) {
                            if (response && response.length > 0) {
                                let record = response[0];
                                $('#id').val(record.id);
                                $('#house_number').val(record.house_number);
                                $('#contact_name').val(record.contact_name);
                                $('#phone_number').val(record.phone_number);

                                for (let i = 1; i <= 6; i++) {
                                    $('#type_' + i).val(record['type_' + i]);
                                    $('#pet_' + i).val(record['pet_' + i]);

                                    let picture_filename = record['picture_pet_' + i];
                                    if (picture_filename) {
                                        let imagePath = 'uploads/pet/' + picture_filename;
                                        $('#preview_pet_' + i).attr('src', imagePath).show();
                                    }
                                }
                            } else {
                                alert('ไม่พบบ้านเลขที่ของคุณในระบบ โปรดติดต่อผู้ดูแล');
                                liff.closeWindow();
                            }
                        },
                        error: function () {
                            alert('เกิดข้อผิดพลาดในการดึงข้อมูลบ้าน');
                            liff.closeWindow();
                        }
                    });
                });
            }
        });
</script>

<script>
    $(document).ready(function () {
        // Image Preview Logic
        for (let i = 1; i <= 6; i++) {
            $('#picture_pet_' + i).on('change', function (event) {
                const previewImage = $('#preview_pet_' + i);
                if (event.target.files && event.target.files[0]) {
                    const reader = new FileReader();
                    reader.onload = function (e) {
                        previewImage.attr('src', e.target.result).show();
                    }
                    reader.readAsDataURL(event.target.files[0]);
                } else {
                    previewImage.hide().attr('src', '#');
                }
            });
        }

        // Form Submission Logic
        $("#recordForm").on("submit", function (event) {
            event.preventDefault();
            $('#save').prop('disabled', true);
            $("#loading").show();

            let formData = new FormData(this);

            $.ajax({
                url: 'model/manage_pet_record_process.php',
                method: "POST",
                data: formData,
                contentType: false,
                processData: false,
                success: function (data) {
                    $("#loading").hide();
                    $('#save').prop('disabled', false);
                    alertify.success("บันทึกข้อมูลสัตว์เลี้ยงเรียบร้อยแล้ว");
                    setTimeout(() => {
                        if (liff.isInClient()) {
                            liff.closeWindow();
                        }
                    }, 1500); // Delay for 1.5 seconds before closing
                },
                error: function () {
                    $("#loading").hide();
                    $('#save').prop('disabled', false);
                    alertify.error("เกิดข้อผิดพลาดในการบันทึกข้อมูล");
                }
            });
        });
    });
</script>

<script>
    $(document).ready(function () {

        // When the "Sticker" button is clicked
        $('#BackBtn').on('click', function () {
            // Redirect to the sticker page URL
            window.location.href = 'https://liff.line.me/2007370141-AxBy7eGD'; // <-- ‼️ **แก้ไข URL ของหน้าสติกเกอร์ที่นี่**
        });
    });
</script>

</body>
</html>
