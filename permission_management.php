<?php
include('includes/Header.php');
if (strlen($_SESSION['alogin']) == "" || strlen($_SESSION['department_id']) == "") {
    header("Location: index.php");
} else {
    ?>

    <!DOCTYPE html>
    <html lang="th">
    <body id="page-top" onload="LoadCheckBox();">
    <div id="wrapper">
        <?php
        include('includes/Side-Bar.php');
        ?>

        <div id="content-wrapper" class="d-flex flex-column">
            <div id="content">
                <?php
                include('includes/Top-Bar.php');
                ?>

                <!-- Container Fluid-->
                <div class="container-fluid" id="container-wrapper">
                    <div class="d-sm-flex align-items-center justify-content-between mb-4">
                        <h1 class="h3 mb-0 text-gray-800"><?php echo urldecode($_GET['s']) ?></h1>
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
                            <div class="card mb-12">
                                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                                </div>
                                <div class="card-body">
                                    <section class="container-fluid">
                                        <div class="row">
                                            <div class="col-md-12 col-md-offset-2">
                                                <div class="panel">
                                                    <div class="panel-body">

                                                        <form id="from_data">
                                                            <div class="form-group has-success">
                                                                <div class="form-group has-success">
                                                                    <label class="control-label" for="lang"><h3>
                                                                            กำหนดสิทธิ์การใช้งานระบบ
                                                                            Initial Permission</h3></label>

                                                                    <div class="form-group row">
                                                                        <div class="col-sm-3">
                                                                            <label for="permission_id"
                                                                                   class="control-label">รหัสสิทธิ์การใช้งาน</label>
                                                                            <input type="text" class="form-control"
                                                                                   id="permission_id"
                                                                                   name="permission_id"
                                                                                   placeholder="รหัสสิทธิ์การใช้งาน">
                                                                        </div>
                                                                        <div class="col-sm-4">
                                                                            <label for="permission_detail"
                                                                                   class="control-label">รายละเอียด</label>
                                                                            <input type="text" class="form-control"
                                                                                   id="permission_detail"
                                                                                   name="permission_detail"
                                                                                   required="required"
                                                                                   placeholder="รายละเอียด">
                                                                        </div>

                                                                        <div class="form-group">
                                                                            <label for="dashboard_page">หน้า
                                                                                DASHBOARD</label>
                                                                            <select class="form-control"
                                                                                    name="dashboard_page"
                                                                                    id="dashboard_page">
                                                                                <option value="Dashboard_admin">
                                                                                    Dashboard_admin
                                                                                </option>
                                                                                <option value="Dashboard_employee">
                                                                                    Dashboard_employee
                                                                                </option>
                                                                                <option value="Dashboard_general">
                                                                                    Dashboard_general
                                                                                </option>
                                                                                <option value="Dashboard_purchase">
                                                                                    Dashboard_purchase
                                                                                </option>
                                                                                <option value="Dashboard_sale">
                                                                                    Dashboard_sale
                                                                                </option>
                                                                                <option value="Dashboard_warehouse">
                                                                                    Dashboard_warehouse
                                                                                </option>
                                                                            </select>
                                                                        </div>

                                                                        <div class="col-sm-2">
                                                                            <label for="PermissionModal"
                                                                                   class="control-label">
                                                                                เลือกรหัสสิทธิ์ </label>
                                                                            <a data-toggle="modal"
                                                                               href="#SearchPermissionModal"
                                                                               class="btn btn-primary">
                                                                                Click <i class="fa fa-search"
                                                                                         aria-hidden="true"></i>
                                                                            </a>

                                                                        </div>
                                                                    </div>

                                                                    <input type="button" name="BtnAll"
                                                                           class="btn btn-info btn-block"
                                                                           value="เลือกทั้งหมด - Select All" id="BtnAll"
                                                                           onclick="CheckAllBox(true);">

                                                                    <input type="button" name="BtnUnSelect"
                                                                           class="btn btn-warning btn-block"
                                                                           value="ไม่เลือกทั้งหมด - Unselect All"
                                                                           id="BtnUnSelect"
                                                                           onclick="CheckAllBox(false);">

                                                                    <div class=”form-group”>
                                                                        <tr>
                                                                            <td valign="top" class="">&nbsp;</td>
                                                                            <td valign="top">
                                                                                <div align="left">
                                                                                    <table class="large" border="0"
                                                                                           cellpadding="0"
                                                                                           cellspacing="0">
                                                                                        <tr>
                                                                                            <p id="myDIV"></p>
                                                                                        </tr>
                                                                                    </table>
                                                                                </div>
                                                                            </td>
                                                                        </tr>


                                                                    </div>
                                                                    <span class="help-block"></span>
                                                                </div>

                                                            </div>

                                                            <div>
                                                                <input id="login_id" name="login_id" type="hidden">
                                                            </div>

                                                        </form>
                                                        <div id="result"></div>
                                                        <input type="button" name="BtnSave"
                                                               class="btn btn-primary btn-block"
                                                               value="Save - บันทึกข้อมูล" id="BtnSave">
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                    </section>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="modal fade" id="SearchPermissionModal">
                        <div class="modal-dialog modal-lg">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h4 class="modal-title">Permission</h4>
                                    <button type="button" class="close" data-dismiss="modal"
                                            aria-hidden="true">×
                                    </button>
                                </div>

                                <div class="container"></div>
                                <div class="modal-body">

                                    <div class="modal-body">

                                        <table cellpadding="0" cellspacing="0" border="0"
                                               class="display"
                                               id="TablePermissionList"
                                               width="100%">
                                            <thead>
                                            <tr>
                                                <th>รหัสสิทธิ์การใช้งาน</th>
                                                <th>รายละเอียดสิทธิ์การใช้งาน</th>
                                                <th>Action</th>
                                            </tr>
                                            </thead>
                                            <tfoot>
                                            <tr>
                                                <th>รหัสสิทธิ์การใช้งาน</th>
                                                <th>รายละเอียดสิทธิ์การใช้งาน</th>
                                                <th>Action</th>
                                            </tr>
                                            </tfoot>
                                        </table>
                                    </div>
                                </div>
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

    <!-- Scroll to top -->
    <a class="scroll-to-top rounded" href="#page-top">
        <i class="fas fa-angle-up"></i>
    </a>

    <script src="vendor/jquery/jquery.min.js"></script>
    <script src="vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
    <script src="vendor/jquery-easing/jquery.easing.min.js"></script>
    <!-- Select2 -->
    <script src="vendor/select2/dist/js/select2.min.js"></script>
    <!-- Bootstrap Datepicker -->
    <script src="vendor/bootstrap-datepicker/js/bootstrap-datepicker.min.js"></script>
    <!-- Bootstrap Touchspin -->
    <script src="vendor/bootstrap-touchspin/js/jquery.bootstrap-touchspin.js"></script>
    <!-- ClockPicker -->
    <script src="vendor/clock-picker/clockpicker.js"></script>
    <!-- RuangAdmin Javascript -->
    <script src="js/myadmin.min.js"></script>
    <!-- Javascript for this page -->

    <script src="js/modal/show_permision_modal.js"></script>

    <!--script src="https://cdnjs.cloudflare.com/ajax/libs/bootbox.js/5.5.2/bootbox.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.0/js/jquery.dataTables.min.js"></script>
    <link rel="stylesheet" href="https://cdn.datatables.net/1.11.0/css/jquery.dataTables.min.css"/>
    <link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.0.0/css/buttons.dataTables.min.css"/-->

    <script src="vendor/datatables/v11/bootbox.min.js"></script>
    <script src="vendor/datatables/v11/jquery.dataTables.min.js"></script>
    <link rel="stylesheet" href="vendor/datatables/v11/jquery.dataTables.min.css"/>
    <link rel="stylesheet" href="vendor/datatables/v11/buttons.dataTables.min.css"/>

    <script>

        $(document).ready(function () {
            let email = '<?php echo $_SESSION['alogin']; ?>';
            let login_id = '<?php echo $_SESSION['login_id']; ?>';
            let lang = '<?php echo $_SESSION['lang']; ?>';
            $('#email').val(email);
            $('#login_id').val(login_id);
            $('#lang').val(lang);
        });

    </script>

    <script>
        function LoadCheckBox() {

            let formData = {action: "INIT", table_main_name: "menu_main", table_sub_name: "menu_sub"};
            $.ajax({
                url: 'model/manage_permission.php',
                method: "POST",
                data: formData,
                success: function (data) {
                    let text = data;
                    document.getElementById("myDIV").innerHTML = text;
                }
            })
        }

    </script>

    <script>

        function CheckAllBox(Check) {

            let main_list = document.getElementsByName("menu_main");
            for (let i = 0; i < main_list.length; i++) {
                document.getElementsByName("menu_main")[i].checked = Check;
            }

            let sub_list = document.getElementsByName("menu_sub");
            for (let i = 0; i < sub_list.length; i++) {
                document.getElementsByName("menu_sub")[i].checked = Check;
            }
        }

    </script>

    <script>
        function getMainValue() {
            let main_list_value = "";
            let main_list = document.getElementsByName("menu_main");
            for (let i = 0; i < main_list.length; i++) {
                if (document.getElementsByName("menu_main")[i].checked === true) {
                    main_list_value = main_list_value + main_list[i].value + ",";
                }
            }
            return main_list_value;
        }
    </script>

    <script>
        function getSubValue() {
            let sub_list_value = "";
            let sub_list = document.getElementsByName("menu_sub");
            for (let i = 0; i < sub_list.length; i++) {
                if (document.getElementsByName("menu_sub")[i].checked === true) {
                    sub_list_value = sub_list_value + sub_list[i].value + ",";
                }
            }
            return sub_list_value;
        }
    </script>

    <script>
        $(document).ready(function () {
            $('#BtnSave').on('click', function () {
                let action = "SAVE";
                let permission_id = $('#permission_id').val();
                let permission_detail = $('#permission_detail').val();
                let dashboard_page = $('#dashboard_page').val();
                let main_list_value = getMainValue();
                let sub_list_value = getSubValue();

                if (permission_id !== "" && permission_detail !== "" && main_list_value !== "" && sub_list_value !== "") {
                    $.ajax({
                        url: "model/manage_permission.php",
                        type: "POST",
                        data: {
                            action: action,
                            permission_id: permission_id,
                            permission_detail: permission_detail,
                            dashboard_page: dashboard_page,
                            main_list_value: main_list_value,
                            sub_list_value: sub_list_value
                        },
                        cache: false,
                        success: function (dataResult) {
                            var dataResult = JSON.parse(dataResult);
                            if (dataResult.statusCode == 200) {
                                alertify.success("บันทึกข้อมูลเรียบร้อย");
                            } else if (dataResult.statusCode == 201) {
                                alertify.error("ไม่สามารถบันทึกข้อมูลได้");
                            }
                        }
                    });
                } else {
                    alertify.error('กรุณาป้อนข้อมูลให้ครบถ้วน');
                }
            });
        });
    </script>


    <script>
        $(document).ready(function () {

            $("#permission_id").blur(function (e) {
                let permission_id = $('#permission_id').val();
                let formData = {action: "CHECK_DUP", permission_id: permission_id};
                $.ajax({
                    url: 'model/manage_permission.php',
                    method: "POST",
                    data: formData,
                    success: function (data) {
                        if (data === "have") {
                            alertify.error("มีรหัสนี้อยู่แล้ว เปลี่ยนหรือเลือกจาก LIst เพื่อปรับปรุง");
                        }
                    }
                })

            });
        });
    </script>

    </body>

    </html>

<?php } ?>