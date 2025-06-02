<?php
include('includes/Header.php');
if (strlen($_SESSION['alogin']) == "") {
    header("Location: index.php");
    exit;
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
                    <h1 class="h4 mb-0 text-gray-800"><?php echo urldecode($_GET['s']) ?></h1>
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="<?php echo $_SESSION['dashboard_page'] ?>">Home</a></li>
                        <li class="breadcrumb-item"><?php echo urldecode($_GET['m']) ?></li>
                        <li class="breadcrumb-item active" aria-current="page"><?php echo urldecode($_GET['s']) ?></li>
                    </ol>
                </div>

                <div class="row">
                    <div class="col-lg-12">
                        <div class="card mb-12">
                            <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between"></div>
                            <div class="card-body">
                                <section class="container-fluid">
                                    <div class="row">
                                        <div class="col-md-12">
                                            <form id="from_data">

                                                <div class="form-group has-success">
                                                    <label for="email">ชื่อผู้ใช้ User Name</label>
                                                    <input type="text" name="email" class="form-control" required id="email">
                                                </div>

                                                <div class="form-group has-success">
                                                    <label for="first_name">ชื่อ First Name</label>
                                                    <input type="text" name="first_name" class="form-control" required id="first_name">
                                                </div>

                                                <div class="form-group has-success">
                                                    <label for="last_name">นามสกุล Last Name</label>
                                                    <input type="text" name="last_name" class="form-control" required id="last_name">
                                                </div>

                                                <div class="form-group has-success">
                                                    <label for="password">รหัสผ่าน Password</label>
                                                    <input type="password" name="password" class="form-control" required id="password">
                                                </div>

                                                <div class="form-group has-success">
                                                    <label for="account_type">ประเภทผู้ใช้ Account Type</label>
                                                    <select id="account_type" name="account_type" class="form-control" required>
                                                        <option value="">-- เลือกประเภทผู้ใช้ --</option>
                                                        <?php
                                                        $sql1 = "SELECT * FROM ims_permission";
                                                        $query1 = $conn->prepare($sql1);
                                                        $query1->execute();
                                                        $results1 = $query1->fetchAll(PDO::FETCH_OBJ);
                                                        if ($query1->rowCount() > 0) {
                                                            foreach ($results1 as $result1) {
                                                                echo '<option value="' . htmlentities($result1->permission_id) . '">' . htmlentities($result1->permission_detail) . '</option>';
                                                            }
                                                        }
                                                        ?>
                                                    </select>
                                                </div>

                                                <input id="action" name="action" type="hidden" value="ADD">

                                                <button type="submit" class="btn btn-primary btn-block">Save</button>
                                            </form>

                                            <div id="result"></div>
                                        </div>
                                    </div>
                                </section>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <?php
            include('includes/Modal-Logout.php');
            include('includes/Footer.php');
            ?>
        </div>
    </div>
</div>

<a class="scroll-to-top rounded" href="#page-top"><i class="fas fa-angle-up"></i></a>

<!-- Scripts -->
<script src="vendor/jquery/jquery.min.js"></script>
<script src="vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
<script src="vendor/jquery-easing/jquery.easing.min.js"></script>
<script src="vendor/select2/dist/js/select2.min.js"></script>
<script src="vendor/bootstrap-datepicker/js/bootstrap-datepicker.min.js"></script>
<script src="vendor/bootstrap-touchspin/js/jquery.bootstrap-touchspin.js"></script>
<script src="vendor/clock-picker/clockpicker.js"></script>
<script src="js/myadmin.min.js"></script>
<script src="js/MyFrameWork/framework_util.js"></script>

<script>
    $(document).ready(function () {
        $("#from_data").on("submit", function (event) {
            event.preventDefault();
            const formValues = $(this).serialize();
            $.post("model/manage_account_process.php", formValues, function (response) {
                if (response == 1) {
                    $("#from_data")[0].reset();
                    alertify.success("บันทึกข้อมูลเรียบร้อย Save Data Success");
                } else if (response == 2) {
                    alertify.error("ไม่สามารถบันทึกข้อมูลได้ มี User นี้แล้ว");
                } else {
                    alertify.error("ไม่สามารถบันทึกข้อมูลได้ DB Error");
                }
            });
        });
    });
</script>
</body>
</html>
