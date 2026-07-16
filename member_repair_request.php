<?php
include('includes/Header.php');

if (strlen($_SESSION['alogin']) == "") {
    header("Location: index.php");
    exit();
}

include("config/connect_db.php");

$message = "";
$message_type = "";

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_repair'])) {
    $house_number = trim($_POST['house_number']);
    $subject = trim($_POST['subject']);
    $remark = trim($_POST['remark']);
    $place_name = trim($_POST['place_name']);

    if (empty($subject) || empty($remark)) {
        $message = "กรุณากรอกหัวข้อและรายละเอียดการแจ้งซ่อม";
        $message_type = "danger";
    } else {
        $photoNames = [];
        $uploadDir = "line_oa/checkin/uploads/";

        // Create directory if it doesn't exist
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        // Process file uploads (up to 3 files)
        if (isset($_FILES['images']) && !empty($_FILES['images']['name'][0])) {
            $totalFiles = count($_FILES['images']['name']);
            // Limit to max 3 files
            $limit = min($totalFiles, 3);
            for ($i = 0; $i < $limit; $i++) {
                $fileName = $_FILES['images']['name'][$i];
                $tmpName = $_FILES['images']['tmp_name'][$i];
                $fileError = $_FILES['images']['error'][$i];

                if ($fileError === UPLOAD_ERR_OK) {
                    $ext = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
                    $allowedExts = ['jpg', 'jpeg', 'png', 'webp', 'gif'];

                    if (in_array($ext, $allowedExts)) {
                        $newFileName = "repair_" . uniqid() . "_" . $i . "." . $ext;
                        $targetPath = $uploadDir . $newFileName;

                        if (move_uploaded_file($tmpName, $targetPath)) {
                            $photoNames[] = $newFileName;
                        }
                    }
                }
            }
        }

        $photoPaths = implode(",", $photoNames);
        $token_checkin = uniqid("repair_", true);
        $timestamp = date('Y-m-d H:i:s');
        $check_type = 'REPAIR_PENDING';
        $display_name = $_SESSION['first_name'] . ' ' . $_SESSION['last_name'];
        if (empty(trim($display_name))) {
            $display_name = $_SESSION['username'];
        }
        $user_id = $_SESSION['alogin'];
        $emp_id = ''; // Not assigned yet

        $full_remark = "[หัวข้อ: " . $subject . "] " . $remark;

        try {
            $sql_ins = "INSERT INTO jobrecord (user_id, display_name, place_name, latitude, longitude, checkin_time, photo_path, check_type, token_checkin, remark, emp_id) 
                        VALUES (?, ?, ?, 0.0, 0.0, ?, ?, ?, ?, ?, ?)";
            $stmt = $conn->prepare($sql_ins);
            $result = $stmt->execute([
                $user_id, 
                $display_name, 
                $place_name, 
                $timestamp, 
                $photoPaths, 
                $check_type, 
                $token_checkin, 
                $full_remark, 
                $emp_id
            ]);

            if ($result) {
                $message = "ส่งข้อมูลแจ้งซ่อมเรียบร้อยแล้ว ระบบจะเร่งประสานงานช่างเพื่อดำเนินการโดยเร็วที่สุด";
                $message_type = "success";
            } else {
                $message = "เกิดข้อผิดพลาดในการบันทึกข้อมูล กรุณาลองใหม่อีกครั้ง";
                $message_type = "danger";
            }
        } catch (Exception $e) {
            $message = "Error: " . $e->getMessage();
            $message_type = "danger";
        }
    }
}

// Fetch member's repair requests
$user_id = $_SESSION['alogin'];
$sql_history = "SELECT j.*, CONCAT(e.prefix, e.f_name, ' ', e.l_name) AS emp_fullname, e.nick_name AS emp_nickname
                FROM jobrecord j
                LEFT JOIN memployee e ON j.emp_id = e.emp_id
                WHERE j.user_id = :user_id AND (j.check_type LIKE 'REPAIR%')
                ORDER BY j.id DESC";
$stmt_history = $conn->prepare($sql_history);
$stmt_history->execute([':user_id' => $user_id]);
$requests = $stmt_history->fetchAll(PDO::FETCH_ASSOC);

?>
<!DOCTYPE html>
<html lang="th">
<head>
    <style>
        .gap-2 > * {
            margin: 4px;
        }
        .img-preview-clickable:hover {
            opacity: 0.8;
            border-color: #4e73df !important;
        }
    </style>
</head>
<body id="page-top">
<div id="wrapper">
    <?php include('includes/Side-Bar.php'); ?>
    <div id="content-wrapper" class="d-flex flex-column">
        <div id="content">
            <?php include('includes/Top-Bar.php'); ?>
            <div class="container-fluid" id="container-wrapper">
                <div class="d-sm-flex align-items-center justify-content-between mb-4">
                    <h1 class="h3 mb-0 text-gray-800"><?php echo isset($_GET['s']) ? htmlspecialchars(urldecode($_GET['s'])) : 'ระบบแจ้งซ่อม' ?></h1>
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="<?php echo $_SESSION['dashboard_page'] ?>">Home</a></li>
                        <li class="breadcrumb-item"><?php echo isset($_GET['m']) ? htmlspecialchars(urldecode($_GET['m'])) : 'ระบบแจ้งซ่อม' ?></li>
                        <li class="breadcrumb-item active" aria-current="page"><?php echo isset($_GET['s']) ? htmlspecialchars(urldecode($_GET['s'])) : 'แจ้งซ่อม/แจ้งปัญหา' ?></li>
                    </ol>
                </div>

                <?php if (!empty($message)): ?>
                    <div class="alert alert-<?php echo $message_type; ?> alert-dismissible fade show shadow-sm" role="alert">
                        <strong><?php echo $message_type === 'success' ? '<i class="fa fa-check-circle"></i> สำเร็จ!' : '<i class="fa fa-exclamation-circle"></i> ข้อผิดพลาด!'; ?></strong> <?php echo $message; ?>
                        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                <?php endif; ?>

                <div class="row">
                    <!-- Left: Request Form -->
                    <div class="col-lg-5">
                        <div class="card mb-4 shadow-sm border-left-primary">
                            <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                                <h6 class="m-0 font-weight-bold text-primary"><i class="fa fa-wrench"></i> แบบฟอร์มแจ้งซ่อม / แจ้งปัญหา</h6>
                            </div>
                            <div class="card-body">
                                <form method="POST" enctype="multipart/form-data" id="repair_form">
                                    <div class="form-group">
                                        <label for="display_name" class="font-weight-bold">ผู้แจ้งซ่อม</label>
                                        <input type="text" class="form-control bg-light" id="display_name" value="<?php echo htmlspecialchars(trim(($_SESSION['first_name'] ?? '') . ' ' . ($_SESSION['last_name'] ?? ''))); ?>" readonly>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label for="house_number" class="font-weight-bold">บ้านเลขที่</label>
                                                <input type="text" class="form-control bg-light" id="house_number" name="house_number" value="<?php echo htmlspecialchars($_SESSION['house_number'] ?? ''); ?>" readonly>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label for="place_name" class="font-weight-bold">สถานที่เกิดเหตุ</label>
                                                <input type="text" class="form-control" id="place_name" name="place_name" value="<?php echo htmlspecialchars($_SESSION['house_number'] ?? ''); ?>" required placeholder="ระบุเลขที่บ้าน หรือ ซอย">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label for="subject" class="font-weight-bold">เรื่อง / หัวข้อการแจ้งซ่อม <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control" id="subject" name="subject" required placeholder="เช่น ท่อประปารั่วซึม, ไฟถนนดับ, ประตูรั้วชำรุด">
                                    </div>
                                    <div class="form-group">
                                        <label for="remark" class="font-weight-bold">รายละเอียดปัญหา <span class="text-danger">*</span></label>
                                        <textarea class="form-control" id="remark" name="remark" rows="4" required placeholder="ระบุอาการชำรุด บริเวณที่เป็นปัญหา หรือวันเวลาที่สะดวกให้ช่างเข้าดำเนินการ"></textarea>
                                    </div>
                                    <div class="form-group">
                                        <label for="images" class="font-weight-bold">แนบรูปภาพปัญหา (สูงสุด 3 รูป)</label>
                                        <div class="custom-file">
                                            <input type="file" class="custom-file-input" id="images" name="images[]" accept="image/*" multiple>
                                            <label class="custom-file-label" for="images">เลือกรูปภาพ...</label>
                                        </div>
                                        <small class="form-text text-muted">รองรับไฟล์รูปภาพ (.jpg, .jpeg, .png, .webp, .gif) ขนาดไม่เกิน 10MB ต่อรูป</small>
                                        <div id="imagePreviewContainer" class="d-flex flex-wrap gap-2 mt-2"></div>
                                    </div>
                                    <button type="submit" name="submit_repair" class="btn btn-primary btn-block shadow-sm">
                                        <i class="fa fa-paper-plane"></i> ส่งข้อมูลแจ้งซ่อม
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>

                    <!-- Right: History List -->
                    <div class="col-lg-7">
                        <div class="card mb-4 shadow-sm">
                            <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                                <h6 class="m-0 font-weight-bold text-dark"><i class="fa fa-history"></i> ประวัติการแจ้งซ่อมของท่าน</h6>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-bordered table-hover align-items-center table-flush" id="dataTableRepair">
                                        <thead class="thead-light">
                                        <tr>
                                            <th>วันเวลาที่แจ้ง</th>
                                            <th>สถานที่</th>
                                            <th>รายละเอียดการแจ้งซ่อม</th>
                                            <th>รูปภาพ</th>
                                            <th>สถานะ / ช่างผู้ดำเนินการ</th>
                                        </tr>
                                        </thead>
                                        <tbody>
                                        <?php if (count($requests) > 0): ?>
                                            <?php foreach ($requests as $req): 
                                                // Extract Subject and Remark
                                                $remark_raw = $req['remark'];
                                                $subject_disp = "แจ้งซ่อมทั่วไป";
                                                $remark_disp = $remark_raw;
                                                if (preg_match('/^\[หัวข้อ:\s*(.*?)\]\s*(.*)$/su', $remark_raw, $matches)) {
                                                    $subject_disp = htmlspecialchars($matches[1]);
                                                    $remark_disp = htmlspecialchars($matches[2]);
                                                } else {
                                                    $remark_disp = htmlspecialchars($remark_raw);
                                                }

                                                // Determine Status and Badge
                                                $status_badge = '<span class="badge badge-warning p-2"><i class="fa fa-clock-o"></i> รอดำเนินการ (Pending)</span>';
                                                if (!empty($req['emp_id']) && $req['emp_id'] !== '0') {
                                                    $emp_name_disp = htmlspecialchars($req['emp_fullname'] ?? '');
                                                    if (!empty($req['emp_nickname'])) {
                                                        $emp_name_disp .= ' (' . htmlspecialchars($req['emp_nickname'] ?? '') . ')';
                                                    }
                                                    
                                                    if ($req['check_type'] === 'REPAIR_DONE') {
                                                        $status_badge = '<span class="badge badge-success p-2"><i class="fa fa-check-circle"></i> ดำเนินการเสร็จสิ้น (Completed)</span><br><small class="text-muted mt-1 d-block">ช่าง: ' . $emp_name_disp . '</small>';
                                                    } else {
                                                        $status_badge = '<span class="badge badge-info p-2"><i class="fa fa-spinner fa-spin"></i> กำลังดำเนินการ (In Progress)</span><br><small class="text-muted mt-1 d-block">ช่าง: ' . $emp_name_disp . '</small>';
                                                    }
                                                }
                                            ?>
                                                <tr>
                                                    <td><?php echo date('d/m/Y H:i', strtotime($req['checkin_time'])); ?></td>
                                                    <td><?php echo htmlspecialchars($req['place_name'] ?? ''); ?></td>
                                                    <td>
                                                        <strong><?php echo $subject_disp; ?></strong>
                                                        <p class="small text-muted mb-0 mt-1"><?php echo nl2br($remark_disp); ?></p>
                                                    </td>
                                                    <td class="text-center">
                                                        <?php 
                                                        if (!empty($req['photo_path'])) {
                                                            $photos = explode(',', $req['photo_path']);
                                                            foreach ($photos as $photo) {
                                                                $photo = trim($photo);
                                                                if (!empty($photo)) {
                                                                    echo '<img src="line_oa/checkin/uploads/' . htmlspecialchars($photo) . '" class="img-thumbnail m-1 img-preview-clickable" style="width: 50px; height: 50px; object-fit: cover; cursor: pointer; border: 1px solid #ddd; padding: 2px;">';
                                                                }
                                                            }
                                                        } else {
                                                            echo '<span class="text-muted small">-</span>';
                                                        }
                                                        ?>
                                                    </td>
                                                    <td><?php echo $status_badge; ?></td>
                                                </tr>
                                            <?php endforeach; ?>
                                        <?php else: ?>
                                            <tr>
                                                <td colspan="5" class="text-center text-muted py-4">ไม่พบประวัติการแจ้งซ่อมของท่าน</td>
                                            </tr>
                                        <?php endif; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <?php include('includes/Footer.php'); ?>
    </div>
</div>

<!-- Scroll to top -->
<a class="scroll-to-top rounded" href="#page-top">
    <i class="fas fa-angle-up"></i>
</a>

<!-- Modal for Image Preview -->
<div class="modal fade" id="imageModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header border-0 pb-0">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body text-center pt-0">
                <img src="" id="modalImage" class="img-fluid rounded" style="max-height: 80vh;">
            </div>
        </div>
    </div>
</div>

<script src="vendor/jquery/jquery.min.js"></script>
<script src="vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
<script src="vendor/jquery-easing/jquery.easing.min.js"></script>
<script src="js/myadmin.min.js"></script>
<script src="vendor/datatables/v11/jquery.dataTables.min.js"></script>
<link rel="stylesheet" href="vendor/datatables/v11/jquery.dataTables.min.css"/>

<script>
    $(document).ready(function () {
        // Initialize DataTable
        $('#dataTableRepair').DataTable({
            "order": [[0, "desc"]],
            "language": {
                "lengthMenu": "แสดง _MENU_ รายการต่อหน้า",
                "zeroRecords": "ไม่พบข้อมูล",
                "info": "แสดงหน้าที่ _PAGE_ จากทั้งหมด _PAGES_ หน้า",
                "infoEmpty": "ไม่มีข้อมูล",
                "infoFiltered": "(กรองจากทั้งหมด _MAX_ รายการ)",
                "search": "ค้นหา:",
                "paginate": {
                    "first": "หน้าแรก",
                    "last": "หน้าสุดท้าย",
                    "next": "ถัดไป",
                    "previous": "ก่อนหน้า"
                }
            }
        });

        // Show image filename in custom file input label
        $('.custom-file-input').on('change', function() {
            let files = $(this)[0].files;
            let labelText = '';
            if (files.length === 1) {
                labelText = files[0].name;
            } else {
                labelText = files.length + ' รูปภาพถูกเลือก';
            }
            $(this).next('.custom-file-label').html(labelText);

            // Preview selected images
            $('#imagePreviewContainer').html('');
            let limit = Math.min(files.length, 3);
            for (let i = 0; i < limit; i++) {
                let file = files[i];
                let reader = new FileReader();
                reader.onload = function(e) {
                    let imgHtml = `<div class="position-relative m-1">
                        <img src="${e.target.result}" class="img-thumbnail" style="width: 80px; height: 80px; object-fit: cover;">
                    </div>`;
                    $('#imagePreviewContainer').append(imgHtml);
                }
                reader.readAsDataURL(file);
            }
        });

        // Click on image preview to zoom
        $(document).on('click', '.img-preview-clickable', function() {
            let src = $(this).attr('src');
            $('#modalImage').attr('src', src);
            $('#imageModal').modal('show');
        });
    });
</script>
</body>
</html>
<?php // Close connection if needed
$conn = null;
?>
