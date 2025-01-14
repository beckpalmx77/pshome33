<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1"/>
    <title>My page</title>

    <!-- CSS dependencies -->
    <link rel="stylesheet" type="text/css" href="../bootstrap/css/bootstrap.css">
</head>
<body>
<p>Content here. <a class="show-alert" href="#">Alert!</a></p>

<!-- JS dependencies -->
<script
        src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
<!-- Bootstrap 4+ dependency -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/2.9.2/umd/popper.min.js"></script>
<script src="../bootstrap/js/bootstrap.min.js"></script>

<!-- bootbox code -->
<script src="../js/bootbox/bootbox.all.js"></script>
<script>
    $(document).on('click', '.show-alert', function (e) {
        bootbox.alert('Hello world!', function () {
            console.log('Alert Callback');
        });
    });
</script>
</body>
</html>