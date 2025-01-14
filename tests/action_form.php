<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ajax Form Submission</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@fortawesome/fontawesome-free@6.4.2/css/fontawesome.min.css">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>
<body>

<nav class="navbar bg-body-tertiary">
    <div class="container-fluid">
        <a class="navbar-brand" href="#">
            <img src="https://getbootstrap.com/docs/5.3/assets/brand/bootstrap-logo-shadow.png" alt="Logo" width="30" height="24" class="d-inline-block align-text-top">
            Bootstrap
        </a>
    </div>
</nav>

<div class="container">
    <form id="ajaxForm">
        <div class="mb-3">
            <label for="username" class="form-label">Username</label>
            <input type="text" class="form-control" id="username" name="username" required>
        </div>
        <div class="mb-3">
            <label for="password" class="form-label">Password</label>
            <input type="password" class="form-control" id="password" name="password" required>
        </div>
        <button type="submit" class="btn btn-primary">Submit</button>
    </form>
    <div id="responseMessage" class="mt-3"></div>
</div>


<script>
    $(document).ready(function() {
        $('#ajaxForm').on('submit', function(e) {
            e.preventDefault();

            $.ajax({
                url: 'form_endpoint.php', // Replace with your server endpoint
                type: 'POST',
                data: $(this).serialize(),
                success: function(response) {
                    $('#responseMessage').html('<div class="alert alert-success">' + response + '</div>');
                },
                error: function(xhr, status, error) {
                    $('#responseMessage').html('<div class="alert alert-danger">' + xhr.responseText + '</div>');
                }
            });
        });
    });
</script>
</body>
</html>

