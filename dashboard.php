<?php
include('includes/Header.php');
if (strlen($_SESSION['alogin']) == "") {
    header("Location: index.php");
} else {

    ?>
    <!DOCTYPE html>
    <html lang="th">

    <style>
        body {
            font-family: 'Kanit', sans-serif;
        }
    </style>


    <body class="bg-gradient-login">
    <!-- Login Content -->
    <div class="container-login">
        <div class="row justify-content-center">
            <div class="col-xl-6 col-lg-6 col-md-9">
                <div class="card shadow-sm my-5">
                    <div class="card-body p-0">
                        <div class="row">
                            <div class="col-lg-12">
                                <div class="login-form">
                                    <div class="text-center">
                                        <div><img src="img/logo/logo text-01.png" width="400" height="158"/></div>
                                    </div>


                                    <?php if ($_SESSION['account_type'] !== 'cockpit') { ?>
                                        <div class="form-group">
                                            <button type="button" name="productSacBtn" id="productSacBtn" tabindex="4"
                                                    class="form-control btn btn-primary">
                                            <span>
                                                <i class="fa fa-tags" aria-hidden="true"></i>
                                                รายการสินค้า SAC
                                            </span>
                                        </div>
                                    <?php } ?>

                                    <div class="form-group">
                                        <button type="button" name="productBTCBtn" id="productBTCBtn"
                                                tabindex="4"
                                                class="form-control btn btn-primary">
                                            <span>
                                                <i class="fa fa-tags" aria-hidden="true"></i>
                                                รายการสินค้า BTC
                                            </span>
                                    </div>



                                    <div class="form-group">
                                        <button type="button" name="productCockpitBtn" id="productCockpitBtn"
                                                tabindex="4"
                                                class="form-control btn btn-primary">
                                            <span>
                                                <i class="fa fa-tags" aria-hidden="true"></i>
                                                รายการสินค้า Cockpit
                                            </span>
                                    </div>


                                    <div class="form-group">
                                        <button type="button" name="exitBtn" id="exitBtn" tabindex="4"
                                                class="form-control btn btn-danger">
                                            <span>
                                                <i class="fa fa-window-close" aria-hidden="true"></i>
                                                ออกจากระบบ
                                            </span>
                                    </div>
                                    <p style="color:blue"><?php echo $_SESSION['first_name'] . " " . $_SESSION['last_name'] ?></p>
                                </div>

                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        $(document).ready(function () {
            $("#productBtn").click(function () {
                window.location.href = "product_page";
            });
        });
    </script>

    <script>
        $(document).ready(function () {
            $("#productSacBtn").click(function () {
                window.location.href = "sac_products";
            });
        });
    </script>

    <script>
        $(document).ready(function () {
            $("#productBTCBtn").click(function () {
                window.location.href = "btc_products";
            });
        });
    </script>

    <script>
        $(document).ready(function () {
            $("#productCockpitBtn").click(function () {
                window.location.href = "cockpit_products";
            });
        });
    </script>

    <script>
        $(document).ready(function () {
            $("#customertBtn").click(function () {
                window.location.href = "customer_page";
            });
        });
    </script>

    <script>
        $(document).ready(function () {
            $("#exitBtn").click(function () {
                window.location.href = "login";
            });
        });
    </script>

    </body>
    </html>

<?php } ?>
