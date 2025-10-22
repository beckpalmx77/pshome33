<html>
<head>

    <link href="https://cdn.jsdelivr.net/npm/select2@4.0.13/dist/css/select2.min.css" rel="stylesheet"/>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.0.13/dist/js/select2.min.js"></script>

    <script src="https://unpkg.com/jquery@3.3.1/dist/jquery.min.js"></script>
    <script src="https://unpkg.com/bootstrap@4.1.0/dist/js/bootstrap.min.js"></script>
    <link rel="stylesheet" href="https://unpkg.com/bootstrap@4.1.0/dist/css/bootstrap.min.css">
<style>
    /* Three image containers (use 25% for four, and 50% for two, etc) */
    .column {
        float: left;
        width: 25%;
        padding: 5px;
    }

    /* Clear floats after image containers */
    .row::after {
        content: "";
        clear: both;
        display: table;
    }

</style>
</head>
<body>
<select id="select_page" style="width:200px;" class="operator">
    <option value="">Select a Page...</option>
    <option value="alpha">alpha</option>
    <option value="beta">beta</option>
    <option value="theta">theta</option>
    <option value="omega">omega</option>
</select>

<div class="row">
    <div class="col-sm-6">
        <div class="card">
            <div class="card-body">
                <h5 class="card-title">Special title treatment</h5>
                <p class="card-text">With supporting text below as a natural lead-in to additional content.</p>
                <a href="#" class="btn btn-primary">Go somewhere</a>
            </div>
        </div>
    </div>
    <div class="col-sm-6">
        <div class="card">
            <div class="card-body">
                <h5 class="card-title">Special title treatment</h5>
                <p class="card-text">With supporting text below as a natural lead-in to additional content.</p>
                <a href="#" class="btn btn-primary">Go somewhere</a>
            </div>
        </div>
    </div>
</div>

<!--p id="myDIV"></p-->

<div class="container">

    <div class="card">
        <div class="card-body">
            <div class="card-columns">
                <img src="../gallery/A001_1.jpg" alt="Snow" style="width:100%">
                &nbsp;
                <img src="../gallery/A001_1.jpg" alt="Snow" style="width:100%">
                &nbsp;
                <img src="../gallery/A001_1.jpg" alt="Snow" style="width:100%">
                &nbsp;
                <img src="../gallery/A001_1.jpg" alt="Snow" style="width:100%">
                &nbsp;
                <img src="../gallery/A001_1.jpg" alt="Snow" style="width:100%">
                &nbsp;
                <img src="../gallery/A001_1.jpg" alt="Snow" style="width:100%">
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="column">
        <img src="../gallery/A001_1.jpg" alt="Snow" style="width:100%">
    </div>
    <div class="column">
        <img src="../gallery/A001_2.jpg" alt="Forest" style="width:100%">
    </div>
    <div class="column">
        <img src="../gallery/A001_3.jpg" alt="Mountains" style="width:100%">
    </div>
</div>

</body>
</html>

<script>

    $(document).ready(function () {

        //alert("OK");

        let image_gallery = "";
        //alert("0");

        for (let i = 0; i < 7; i++) {
            //alert("A");
            if (i%3 === 1) {
                image_gallery = image_gallery + "<div class='row'>";
            }
            image_gallery = image_gallery + "<div class='col-6'>";
            image_gallery = image_gallery + "<img src=" + '../gallery/A001_1.jpg' + " width='100%' height='auto' onclick='ightbox(2)'/></div>";

            if (i%3 === 1) {
                image_gallery = image_gallery + "</div>";
            }

        }

        document.getElementById("myDIV").innerHTML = image_gallery;


    });
</script>