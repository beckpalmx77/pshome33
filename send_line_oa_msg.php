<?php
include('includes/Header.php');
if (strlen($_SESSION['alogin']) == "") {
    header("Location: index.php");
} else {
    // ตั้งค่า LINE Messaging API
    define('LINE_ACCESS_TOKEN', 'UeQDGaIitsNRqYib1mPUo1VjLZfY6lQYvLK1LguyO0hIEYYMZHABHfWEu9UvM4hK8QrGR1V5pUNu/SO+7kOvvLoLjecwTGAE9JsslpnkD1+4mpRtyJqDcZZyQa4/WCuDNHNE9fL1sqR1ujE+mXLnwgdB04t89/1O/w1cDnyilFU=');

    $message_status = "";

    // ตรวจสอบเมื่อมีการกดส่ง Form
    if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['btnSend'])) {
        $text_message = trim($_POST['message'] ?? '');
        $target_id = trim($_POST['target_id'] ?? '');

        $images = [];

        // Handle Multi-Image Upload from Screen
        if (isset($_FILES['image_upload']) && !empty($_FILES['image_upload']['name'][0])) {
            $upload_dir = 'uploads/line_oa/';
            if (!file_exists($upload_dir)) {
                mkdir($upload_dir, 0777, true);
            }

            $files = $_FILES['image_upload'];
            $count = count($files['name']);

            for ($i = 0; $i < $count; $i++) {
                if ($files['error'][$i] == UPLOAD_ERR_OK) {
                    $file_name = $files['name'][$i];
                    $tmp_name = $files['tmp_name'][$i];
                    $ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));

                    $allowed_ext = ['jpg', 'jpeg', 'png'];
                    if (in_array($ext, $allowed_ext)) {
                        $new_name = uniqid('line_img_') . '.' . $ext;
                        $target_file = $upload_dir . $new_name;

                        if (move_uploaded_file($tmp_name, $target_file)) {
                            // Construct Public URL
                            $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";
                            $host = $_SERVER['HTTP_HOST'];
                            $request_uri = $_SERVER['REQUEST_URI'];
                            $current_dir = rtrim(dirname($request_uri), '/\\');
                            $public_url = $protocol . $host . $current_dir . '/' . $target_file;

                            $images[] = $public_url;
                        }
                    }
                }
            }
        }

        $messages = [];
        if (!empty($text_message)) {
            $messages[] = ['type' => 'text', 'text' => $text_message];
        }

        foreach ($images as $url) {
            if (count($messages) < 5) {
                $messages[] = [
                    'type' => 'image',
                    'originalContentUrl' => $url,
                    'previewImageUrl' => $url
                ];
            }
        }

        if (empty($messages)) {
            $message_status = "<div class='alert alert-danger alert-dismissible fade show' role='alert'>
                                <strong>ผิดพลาด!</strong> กรุณากรอกข้อความหรือเลือกรูปภาพ
                                <button type='button' class='close' data-dismiss='alert' aria-label='Close'>
                                    <span aria-hidden='true'>&times;</span>
                                </button>
                              </div>";
        } else {
            $endpoint = 'https://api.line.me/v2/bot/message/broadcast';
            $data = ['messages' => $messages];

            if (!empty($target_id)) {
                $endpoint = 'https://api.line.me/v2/bot/message/push';
                $data['to'] = $target_id;
            }

            $post_data = json_encode($data);
            $ch = curl_init($endpoint);
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
            curl_setopt($ch, CURLOPT_POSTFIELDS, $post_data);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                'Content-Type: application/json',
                'Authorization: Bearer ' . LINE_ACCESS_TOKEN
            ]);

            $result = curl_exec($ch);
            $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);

            if ($http_code == 200) {
                $type_sent = !empty($target_id) ? "Push ($target_id)" : "Broadcast";
                $message_status = "<div class='alert alert-success alert-dismissible fade show' role='alert'>
                                    <strong>สำเร็จ!</strong> ส่งข้อความแบบ $type_sent เรียบร้อยแล้ว
                                    <button type='button' class='close' data-dismiss='alert' aria-label='Close'>
                                        <span aria-hidden='true'>&times;</span>
                                    </button>
                                  </div>";
            } else {
                $message_status = "<div class='alert alert-danger alert-dismissible fade show' role='alert'>
                                    <strong>ผิดพลาด!</strong> (โค้ด: $http_code): " . htmlspecialchars($result) . "
                                    <button type='button' class='close' data-dismiss='alert' aria-label='Close'>
                                        <span aria-hidden='true'>&times;</span>
                                    </button>
                                  </div>";
            }
        }
    }
    ?>

    <!DOCTYPE html>
    <html lang="th">
    <body id="page-top">
    <div id="wrapper">
        <?php include('includes/Side-Bar.php'); ?>

        <div id="content-wrapper" class="d-flex flex-column">
            <div id="content">
                <?php include('includes/Top-Bar.php'); ?>

                <div class="container-fluid" id="container-wrapper">
                    <div class="d-sm-flex align-items-center justify-content-between mb-4">
                        <h1 class="h3 mb-0 text-gray-800"><?php echo urldecode($_GET['s']) ?></h1>
                        <input type="hidden" id="main_menu" value="<?php echo urldecode($_GET['m']) ?>">
                        <input type="hidden" id="sub_menu" value="<?php echo urldecode($_GET['s']) ?>">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="<?php echo $_SESSION['dashboard_page'] ?>">Home</a>
                            </li>
                            <li class="breadcrumb-item"><?php echo urldecode($_GET['m']) ?></li>
                            <li class="breadcrumb-item active"
                                aria-current="page"><?php echo urldecode($_GET['s']) ?></li>
                        </ol>
                    </div>

                    <div class="row">
                        <div class="col-lg-12">
                            <div class="card mb-4">
                                <!--div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                                    <h6 class="m-0 font-weight-bold text-primary">ฟอร์มส่งข้อความและรูปภาพเข้า LINE</h6>
                                </div-->
                                <div class="card-body">
                                    <?php echo $message_status; ?>

                                    <form action="" method="POST" enctype="multipart/form-data">
                                        <!--div class="form-group">
                                            <label for="target_id"><strong>Target ID (User ID / Group ID):</strong></label>
                                            <input type="text" class="form-control" id="target_id" name="target_id" placeholder="เว้นว่างไว้หากต้องการ Broadcast ทุกคน">
                                            <small class="form-text text-muted">* หากส่งเข้ากลุ่ม ต้องใช้ Group ID (ขึ้นต้นด้วย C)</small>
                                        </div-->

                                        <input type="hidden" class="form-control" id="target_id" name="target_id" placeholder="เว้นว่างไว้หากต้องการ Broadcast ทุกคน">

                                        <div class="form-group">
                                            <label for="message"><strong>ข้อความตัวอักษร:</strong></label>
                                            <textarea class="form-control" id="message" name="message" rows="3" placeholder="พิมพ์ข้อความที่ต้องการส่ง..."></textarea>
                                        </div>

                                        <div class="card border-left-success shadow h-100 py-2 mb-4">
                                            <div class="card-body">
                                                <div class="row no-gutters align-items-center">
                                                    <div class="col mr-2">
                                                        <div class="text-xs font-weight-bold text-success text-uppercase mb-1">อัปโหลดรูปภาพ</div>
                                                        <input type="file" class="form-control-file" id="image_upload" name="image_upload[]" accept="image/*" multiple onchange="previewImages(this)">
                                                        <div id="image_preview" class="row mt-3 px-3"></div>
                                                        <small class="form-text text-muted">* เลือกได้หลายรูปพร้อมกัน (รองรับ .jpg, .png)</small>
                                                    </div>
                                                    <div class="col-auto">
                                                        <i class="fas fa-image fa-2x text-gray-300"></i>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="alert alert-info small">
                                            <i class="fa fa-info-circle"></i> หมายเหตุ: รูปภาพที่อัปโหลดต้องเป็นไฟล์ .jpg หรือ .png และระบบจะจัดการสร้าง URL ที่ปลอดภัยสำหรับ LINE ให้อัตโนมัติ
                                        </div>

                                        <button type="submit" name="btnSend" class="btn btn-success btn-lg btn-block">
                                            <i class="fa fa-paper-plane"></i> ส่งข้อความและรูปภาพ
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <?php include('includes/Footer.php'); ?>
        </div>
    </div>

    <a class="scroll-to-top rounded" href="#page-top">
        <i class="fas fa-angle-up"></i>
    </a>

    <?php include('includes/Modal-Logout.php'); ?>

    <script src="vendor/jquery/jquery.min.js"></script>
    <script src="vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
    <script src="vendor/jquery-easing/jquery.easing.min.js"></script>
    <script src="js/myadmin.min.js"></script>

    <script>
        function previewImages(input) {
            var preview = document.getElementById('image_preview');
            preview.innerHTML = '';
            if (input.files) {
                Array.from(input.files).forEach(file => {
                    var reader = new FileReader();
                    reader.onload = function (e) {
                        var div = document.createElement('div');
                        div.className = 'col-auto mb-2';
                        div.innerHTML = '<img src="' + e.target.result + '" class="img-thumbnail" style="width: 100px; height: 100px; object-fit: cover;">';
                        preview.appendChild(div);
                    }
                    reader.readAsDataURL(file);
                });
            }
        }
    </script>
    </body>
    </html>
<?php } ?>
