<!DOCTYPE html>
<html>
<head>
    <title>Time Format Validation</title>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        $(document).ready(function() {
            $('#timeInput').on('input', function() {
                let timeFormat = /^([01]?[0-9]|2[0-3]):[0-5][0-9]$/;
                let inputValue = $(this).val();

                if (timeFormat.test(inputValue)) {
                    // Valid time format
                    $(this).removeClass('invalid');
                    $(this).addClass('valid');
                } else {
                    // Invalid time format
                    $(this).removeClass('valid');
                    $(this).addClass('invalid');
                }
            });
        });
    </script>
    <style>
        .invalid {
            border: 1px solid red;
        }
        .valid {
            border: 1px solid green;
        }
    </style>
</head>
<body>
<input type="text" id="timeInput" placeholder="Enter time in HH:MM format">
</body>
</html>
