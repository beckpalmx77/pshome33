<!DOCTYPE html>
<html>
<head>
    <title>Time Format Validation</title>
    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
</head>
<body>
<input type="text" id="timeInput" placeholder="Enter time (HH:MM)">

<script>
    $(document).ready(function() {
        $('#timeInput').on('input', function() {
            let timeFormat = /^([01]\d|2[0-3]):([0-5]\d)$/; // Regular expression for 24-hour HH:MM format

            let userInput = $(this).val();

            if (timeFormat.test(userInput)) {
                $(this).removeClass('invalid');
            } else {
                $(this).addClass('invalid');
            }
        });
    });
</script>

<style>
    .invalid {
        border: 1px solid red;
    }
</style>

</body>
</html>
