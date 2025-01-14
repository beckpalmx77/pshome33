<?php
session_start();
error_reporting(0);
include('includes/Header.php');
if (strlen($_SESSION['alogin']) == "") {
    header("Location: index.php");
} else {
    ?>

    <!doctype html>
    <html>
    <head lang="en">
        <meta charset="utf-8">
        <title>สงวนออโต้คาร์</title>
        <script type="text/javascript" src="js/jquery-1.11.3-jquery.min.js"></script>
        <script type="text/javascript" src="js/script.js"></script>
        <script type="text/javascript" src="js/alertify/alertify.js"></script>
        <link rel="stylesheet" href="js/alertify/css/alertify.css">
        <!--script src="https://ajax.googleapis.com/ajax/libs/jquery/3.2.1/jquery.min.js"></script>
        <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css"
              integrity="sha384-BVYiiSIFeK1dGmJRAkycuHAHRg32OmUcww7on3RYdg4Va+PmSTsz/K68vbdEjh4u"
              crossorigin="anonymous"-->
    </head>
    <body>
    <div id="content-wrapper" class="d-flex flex-column">
        <div id="content">
            <!-- Container Fluid-->
            <div class="container-fluid" id="container-wrapper">
                <div class="d-sm-flex align-items-center justify-content-between mb-4">
                    <h1 class="h3 mb-0 text-gray-800"><span id="title"></span></h1>
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="<?php echo $_SESSION['dashboard_page'] ?>">Home</a>
                        </li>
                        <li class="breadcrumb-item"><span id="main_menu"></li>
                        <li class="breadcrumb-item active"
                            aria-current="page"><span id="sub_menu"></li>
                    </ol>
                </div>

                <div class="col-md-8">

                    <form method="post" id="my_form" enctype="multipart/form-data">
                        <div class="modal-body">
                            <div class="modal-body">


                                <div class="form-group row">
                                    <div class="col-sm-6">
                                        <label for="text"
                                               class="control-label">เลขที่เอกสาร</label>
                                        <input type="doc_id" class="form-control"
                                               id="doc_id" name="doc_id"
                                               readonly="true"
                                               required="required"
                                               value=""
                                               placeholder="">
                                    </div>

                                    <div class="col-sm-4">
                                        <label for="doc_date"
                                               class="control-label">วันที่เอกสาร</label>
                                        <i class="fa fa-calendar"
                                           aria-hidden="true"></i>
                                        <input type="text" class="form-control"
                                               id="doc_date"
                                               name="doc_date"
                                               required="required"
                                               value=""
                                               readonly="true"
                                               placeholder="วันที่เอกสาร">
                                    </div>
                                </div>

                                <div class="form-group row">
                                    <div class="col-sm-4">
                                        <label for="emp_id"
                                               class="control-label">รหัสพนักงาน</label>
                                        <input type="text" class="form-control"
                                               id="emp_id" name="emp_id"
                                               readonly="true"
                                               required="required"
                                               value=""
                                               placeholder="">
                                    </div>
                                    <div class="col-sm-6">
                                        <label for="full_name"
                                               class="control-label">ชื่อ -
                                            นามสกุล</label>
                                        <input type="text" class="form-control"
                                               id="full_name" name="full_name"
                                               readonly="true"
                                               value=""
                                               placeholder="">
                                    </div>

                                </div>

                                <div class="form-group row">
                                    <div class="col-sm-10">
                                        <label for="leave_type_detail"
                                               class="control-label">ประเภทการลา</label>
                                        <input type="text" class="form-control"
                                               id="leave_type_detail" name="leave_type_detail"
                                               readonly="true"
                                               required="required"
                                               value=""
                                               placeholder="">
                                    </div>
                                </div>

                                <div class="form-group row">
                                    <div class="col-sm-4">
                                        <!--label for="picture"
                                               class="control-label">ชื่อไฟล์</label-->
                                        <input type="hidden" class="form-control"
                                               id="picture" name="picture"
                                               readonly="true"
                                               required="required"
                                               value=""
                                               placeholder="">
                                    </div>
                                </div>

                                <div class="form-group row">
                                    <div class="col-sm-10">
                                        <label for="uploadImage"
                                               class="control-label">เลือกไฟล์รูปภาพที่ต้องการ Upload</label>
                                        <input class="form-control" type="file" id="uploadImage" accept="image/*"
                                               name="image"
                                               onchange="readURL(this)" multiple/>
                                        <div>Upload File (ไฟล์ .jpg , .png เท่านั้น) ชี้ที่รูปเพื่อขยาย</div>
                                    </div>
                                </div>

                            </div>

                        </div>

                        <div class="modal-footer">

                            <div class='preview'>
                                <img class="enlarge" src="" id="img" width="200" height="200">
                            </div>

                            <!--div class='preview'>
                                <img class="enlarge" src="" id="img" width="200" height="200"
                                     onclick="window.open(this.src,'_blank')">
                            </div-->

                            <input type="hidden" name="id" id="id"/>
                            <input type="hidden" name="action" id="action" value=""/>
                            <span class="icon-input-btn">
                                <input class="btn btn-success" type="submit" value="Upload">
                            </span>
                            <button type="button" class="btn btn-danger"
                                    id="btnClose">Close <i
                                        class="fa fa-window-close"></i>
                            </button>
                        </div>
                    </form>

                </div>
            </div>
        </div>

        </form>

        <div id="err"></div>

    </div>

    <style>

        .preview {
            width: 200px;
            height: 200px;
            border: 0;
            margin: 0 auto;
            background: white;
        }

    </style>

    <script>
        function encodeURL(url) {
            return encodeURIComponent(url);
        }

        function decodeURL(url) {
            return encodeURIComponent(url);
        }
    </script>

    <script>
        function bigImg(x) {
            x.style.height = "100%";
            x.style.width = "100%";
        }

        function normalImg(x) {
            x.style.height = "200px";
            x.style.width = "200px";
        }
    </script>

    <style>
        .enlarge {
            height: 100%;
            width: 100%;
            float: left;
            -webkit-transition: all 0.5s ease-in-out;
            -moz-transition: all 0.5s ease-in-out;
            transition: all 0.5s ease-in-out;
        }

        .enlarge:hover {
            height: 300%;
            width: 300%;
            -webkit-transition: all 0.5s ease-in-out;
            -moz-transition: all 0.5s ease-in-out;
            transition: all 0.5s ease-in-out;
            cursor: pointer;
        }
    </style>

    <script>
        function readURL(input) {
            if (input.files && input.files[0]) {
                let reader = new FileReader();
                reader.onload = function (e) {
                    $('#img')
                        .attr('src', e.target.result);
                };

                reader.readAsDataURL(input.files[0]);
            }
        }
    </script>


    <script type="text/javascript">
        let queryString = new Array();
        $(function () {
            if (queryString.length == 0) {
                if (window.location.search.split('?').length > 1) {
                    let params = window.location.search.split('?')[1].split('&');
                    for (let i = 0; i < params.length; i++) {
                        let key = params[i].split('=')[0];
                        let value = decodeURIComponent(params[i].split('=')[1]);
                        queryString[key] = value;
                    }
                }
            }

            let data = "<b>" + queryString["title"] + "</b>";
            $("#title").html(data);
            $("#main_menu").html(queryString["main_menu"]);
            $("#sub_menu").html(queryString["sub_menu"]);
            $('#action').val(queryString["action"]);

            if (queryString["id"] != null && queryString["doc_id"] != null) {

                $('#id').val(queryString["id"]);
                $('#doc_id').val(queryString["doc_id"]);
                $('#doc_date').val(queryString["doc_date"]);
                $('#emp_id').val(queryString["emp_id"]);
                $('#full_name').val(queryString["full_name"]);
                $('#date_leave_start').val(queryString["date_leave_start"]);
                $('#date_leave_to').val(queryString["date_leave_to"]);
                $('#leave_type_detail').val(queryString["leave_type_detail"]);

                let image_load = "img_doc/" + queryString["picture"];

                $("#img").attr("src", image_load);

                $('#picture').val(queryString["picture"]);

            }
        });
    </script>

    <script>

        $(document).ready(function (e) {
            $("#my_form").on('submit', (function (e) {
                e.preventDefault();
                let fd = new FormData();
                $.ajax({
                    url: "upload_ajax.php",
                    type: "POST",
                    data: new FormData(this),
                    contentType: false,
                    cache: false,
                    processData: false,
                    beforeSend: function () {
//$("#preview").fadeOut();
                        $("#err").fadeOut();
                    },
                    success: function (data) {
                        if (data === 'invalid') {
// invalid file format.
                            $("#err").html("กรุณาเลือกไฟล์ที่ถูกต้องเพื่อ Upload !").fadeIn();
                            alertify.alert('กรุณาเลือกไฟล์ที่ถูกต้องเพื่อ Upload !');
                        } else {
// view uploaded file.
                            //$("#preview").html(data).fadeIn();
                            //$("#form")[0].reset();

                            $("#img").attr("src", data);
                            $('.preview img').show();

                            alertify
                                .alert("Upload รูปภาพเรียบร้อยแล้ว", function () {
                                    alertify.message('OK');
                                });

                        }
                    },
                    error: function (e) {
                        $("#err").html(e).fadeIn();
                    }
                });
            }));
        });
    </script>

    <script>
        $(document).ready(function () {
            $("#btnClose").click(function () {
                window.opener = self;
                window.close();
            });
        });
    </script>

    </body>
    </html>

    <?php
}
?>

