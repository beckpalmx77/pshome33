<!DOCTYPE html>
<html lang="th">

<?php
include('includes/CheckDevice.php');
include('includes/Header.php');
require_once 'config/connect_lotto_db.php';

// ตรวจสอบว่ามีการส่งข้อมูลเงื่อนไขมาหรือไม่
$condition = '';
$params = [];
$message = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (empty($_POST['lotto_name']) && empty($_POST['lotto_phone'])) {
        $message = 'กรุณาป้อนเงื่อนไข (ชื่อร้าน หรือ หมายเลขโทรศัพท์) ก่อน';
    } else {
        if (!empty($_POST['lotto_name'])) {
            $condition .= " AND lotto_name LIKE :lotto_name";
            $params[':lotto_name'] = "%" . $_POST['lotto_name'] . "%";
        }
        if (!empty($_POST['lotto_phone'])) {
            $condition .= " AND lotto_phone LIKE :lotto_phone";
            $params[':lotto_phone'] = "%" . $_POST['lotto_phone'] . "%";
        }
    }
}

// สร้างคำสั่ง SQL โดยเพิ่มเงื่อนไขกรองข้อมูล
$sql = "SELECT * FROM ims_lotto WHERE 1=1 $condition ORDER BY id DESC";
$stmt = $conn->prepare($sql);
$stmt->execute($params);
$result = $stmt->fetchAll();


?>

<style>
    body {
        font-family: 'Prompt', sans-serif;
    }
</style>

<style type="text/css">
    .toggleeye {
        float: right;
        margin-right: 6px;
        margin-top: -20px;
        position: relative;
        z-index: 2;
        color: darkgrey;
    }
</style>

<body class="bg-gradient-login">
<!-- Login Content -->
<div class="container-login">
    <div class="row justify-content-center">
        <div class="col-xl-12 col-lg-12 col-md-12">
            <div class="card shadow-sm my-5">
                <div class="card-body p-0">
                    <div class="row">
                        <div class="col-lg-12">
                            <div class="login-form">
                                <div><img src="img/logo/logo text-01.png" width="200" height="79"/></div>
                                <h6 style="color: blue"><b>ค้นหาข้อมูลการลงทะเบียน SAC LOTTO</b></h6>





                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- jQuery และ Script -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
    $(document).ready(function () {
        $("#sac-submit").click(function () {
            window.location.href = "sac_lotto_login";
        });

        $("#customer-submit").click(function () {
            window.location.href = "sac_lotto_select";
        });

        $("#check-customer-submit").click(function () {
            window.location.href = "search_lotto_data";
        });
    });
</script>

</body>
</html>
