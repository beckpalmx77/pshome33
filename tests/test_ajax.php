<!DOCTYPE html>
<html>
<head>

    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet"
          integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous">
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.2/dist/umd/popper.min.js"
            integrity="sha384-IQsoLXl5PILFhosVNubq5LC7Qb9DXgDA9i+tQ8Zj3iwWAwPtgFTxbJ8NT4GN1R8p"
            crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.min.js"
            integrity="sha384-cVKIPhGWiC2Al4u+LWgxfKTRIcfu0JTxR+EQDz/bgldoEyl4H0zUF0QKbrJ0EcQF"
            crossorigin="anonymous"></script>


    <script type = "text/javascript" language = "javascript">
        $(document).ready(function() {

            $("#driver").click(function(event){

                $.post(
                    "serialize.php",
                    $("#formData").serializeArray(),
                    function(data) {
                        $('#results_1').html(data);
                    }
                );

                let fields = $("#formData").serializeArray();
                //$("#results_2").empty();
                jQuery.each(fields, function(i, field){
                    $("#results_2").append(field.value + " ");
                });

            });

        });
    </script>

</head>
<body>

<div id="results_1"></div>
<div id="results_2"></div>

<div class="container-fluid">
    <form id="formData">
        <div class="mb-3">
            <label for="first_name" class="form-label">First Name</label>
            <input type="text" class="form-control" id="first_name" name="first_name" placeholder="">
        </div>
        <div class="mb-3">
            <label for="last_name" class="form-label">Last Name</label>
            <input type="text" class="form-control" id="last_name" name="last_name" placeholder="">
        </div>
        <button class="btn btn-primary mb-3">Click</button>
    </form>
</div>




</body>
</html>
