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
                            <h4>ข้อมูลสัตว์เลี้ยง</h4>

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
                    $('#user-info-liff').text(`  ${displayName}`);

                    // --- ขั้นตอนที่ 1: ดึงบ้านเลขที่ก่อน ---
                    $.ajax({
                        type: "POST",
                        url: 'model/get_house_line_user.php',
                        dataType: "json",
                        data: { userId: userId },
                        success: function (houseResponse) {
                            //  ✅ แก้ไขเงื่อนไข: ตรวจสอบว่ามี house_number และไม่เป็นค่าว่าง
                            if (houseResponse && houseResponse.house_number) {
                                let house_number = houseResponse.house_number;
                                // ✅ แก้ไข: รวมชื่อ-นามสกุลจาก response โดยตรง
                                let contact_name = houseResponse.f_name + " " + houseResponse.l_name;

                                $('#house_number').val(house_number);
                                $('#contact_name').val(contact_name);
                                $('#phone_number').val(houseResponse.line_phone);

                                // --- ขั้นตอนที่ 2: เมื่อได้บ้านเลขที่แล้ว จึงดึงข้อมูลสัตว์เลี้ยง ---
                                $.ajax({
                                    type: "POST",
                                    url: 'model/manage_pet_record_process.php',
                                    dataType: "json",
                                    data: { action: "GET_DATA_BY_HOUSE_NUMBER", house_number: house_number },
                                    success: function (petResponse) {
                                        if (petResponse && petResponse.length > 0) {
                                            let record = petResponse[0];
                                            $('#id').val(record.id);

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
                                            // ไม่มีข้อมูลสัตว์เลี้ยง แต่มีบ้านเลขที่ ให้ผู้ใช้กรอกใหม่ได้
                                            alertify.message('ไม่พบข้อมูลสัตว์เลี้ยงที่เคยลงทะเบียน ท่านสามารถกรอกข้อมูลใหม่ได้เลย');
                                        }
                                    },
                                    error: function () {
                                        alertify.error('เกิดข้อผิดพลาดในการดึงข้อมูลสัตว์เลี้ยง');
                                        // liff.closeWindow(); // อาจจะไม่ต้องปิดทันที ให้ผู้ใช้เห็นข้อความก่อน
                                    }
                                });

                            } else {
                                // ✅ แก้ไข: นำ alert กลับมาใช้งาน
                                alertify.alert('ไม่พบข้อมูล!', 'ไม่พบบ้านเลขที่ที่ผูกกับบัญชี LINE นี้ โปรดติดต่อผู้ดูแล', function(){
                                    if(liff.isInClient()) {
                                        liff.closeWindow();
                                    }
                                });
                            }
                        },
                        error: function () {
                            alertify.error('เกิดข้อผิดพลาดในการเชื่อมต่อเพื่อดึงบ้านเลขที่');
                        }
                    });
                });
            }
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
