<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta doc_no="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta doc_no="description" content="">
    <meta doc_no="author" content="">
    <link rel="icon" href="img/favicon.ico" type="image/x-icon">
    <link href="../img/logo/logo.png" rel="icon">
    <title>MyAdmin Dashboard</title>

    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>

    <link href="vendor/fontawesome-free/css/all.min.css" rel="stylesheet" type="text/css">
    <link href="vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet" type="text/css">
    <link href="css/myadmin.css" rel="stylesheet">
    <link rel="stylesheet" href="vendor/alertify/css/alertify.core.css"/>
    <link rel="stylesheet" href="vendor/alertify/css/alertify.default.css"/>
    <link rel="stylesheet" href="vendor/alertify/css/main.css"/>

    <script src="vendor/datatables/jquery.dataTables.min.js"></script>
    <script src="vendor/datatables/dataTables.bootstrap4.min.js"></script>

    <script src="vendor/alertify/js/alertify.js"></script>

    <!--link href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.10/css/select2.min.css" rel="stylesheet"/>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.10/js/select2.min.js"></script-->

    <!--link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" /-->
    <!--script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script-->

    <link href="../css/select2_4010.css" rel="stylesheet"/>
    <script src="../js/select2_4010.js"></script>

    <link rel="stylesheet" href="../css/datepicker.css">
    <script src="../js/bootstrap-datepicker1.js"></script>

    <?php
    session_start();
    error_reporting(0);
    ?>

    <style>
        body, h1, h2, h3, h4, h5, h6 {
            font-family: 'Prompt', sans-serif !important;
        }
    </style>

</head>

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
        if (queryString["doc_no"] != null && queryString["f_name"] != null) {

            let data = "<u>Values from QueryString</u><br /><br />";
            data += "<b>doc_no:</b> " + queryString["title"];

            $("#lblData").html(data);
            $('#doc_no').val(queryString["doc_no"]);
            $('#f_name').val(queryString["f_name"]);

        }
    });
</script>

<div class="card-body">
    <section class="container-fluid">
        <div class="row">
            <div class="col-md-12 col-md-offset-2">
                <div class="panel">
                    <div class="panel-body">

                        <span id="lblData"></span>

                        <span id="title"></span>

                        <div class="form-group">
                            <label for="doc_no" class="control-label">เลขที่เอกสาร</label>
                            <input type="text" class="form-control"
                                   id="doc_no" name="doc_no"
                                   required="required"
                                   placeholder="เลขที่เอกสาร">
                        </div>

                        <div class="form-group">
                            <label for="f_name" class="control-label">ชื่อลูกค้า</label>
                            <input type="text" class="form-control"
                                   id="f_name" name="f_name"
                                   required="required"
                                   placeholder="ชื่อลูกค้า">
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </section>
</div>



<script>


    function getUrlVars() {
        let url = document.location.href;
        let qs = url.substring(url.indexOf('?') + 1).split('&');
        for (let i = 0, result = {}; i < qs.length; i++) {
            qs[i] = qs[i].split('=');
            result[qs[i][0]] = decodeURIComponent(qs[i][1]);

            //alert(i + " = " + decodeURIComponent(qs[i][1]));
            //alert("doc_no = " + decodeURIComponent(qs[1][1]));
            $('#doc_no').val(decodeURIComponent(qs[1][1]));
            $('#doc_date').val(decodeURIComponent(qs[2][1]));
            $('#customer_id').val(decodeURIComponent(qs[3][1]));
            $('#f_name').val(decodeURIComponent(qs[4][1]));


        }
        //return result;

    }

    /*
            function getUrlVars() {
                let vars = [], hash;
                let hashes = window.location.href.slice(window.location.href.indexOf('?') + 1).split('&');
                for (let i = 0; i < hashes.length; i++) {
                    hash = hashes[i].split('=');

                    alert(hash);

                    vars.push(hash[0]);
                    vars[hash[0]] = hash[1];


                }

            }

     */
</script>