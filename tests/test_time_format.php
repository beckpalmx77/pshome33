<!DOCTYPE html>
<html>
<head>
    <title>Time Input Validation</title>
    <!-- Include jQuery library -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <!-- Include jQuery UI library -->
    <script src="https://code.jquery.com/ui/1.13.0/jquery-ui.min.js"></script>
    <!-- Include jQuery UI Timepicker addon -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/jquery-ui-timepicker-addon/1.6.3/jquery-ui-timepicker-addon.min.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-ui-timepicker-addon/1.6.3/jquery-ui-timepicker-addon.min.js"></script>
</head>
<body>
<label for="timeInput">Time:</label>
<input type="text" id="timeInput">

<script>
    $(document).ready(function() {
        // Initialize the timepicker
        $('#timeInput').timepicker();

        // Validate the time input on change
        $('#timeInput').on('change', function() {
            let timeInput = $(this).val();
            if (validateTimeInput(timeInput)) {
                console.log("Valid time format.");
            } else {
                console.log("Invalid time format.");
            }
        });

        function validateTimeInput(timeInput) {
            let pattern = /^(0[0-9]|1[0-9]|2[0-3]):[0-5][0-9]$/;
            return pattern.test(timeInput);
        }
    });
</script>
</body>
</html>
