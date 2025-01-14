<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Color Picker</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            padding: 20px;
        }
        #colorCode {
            font-size: 1.5em;
            margin-top: 20px;
        }
    </style>
</head>
<body>

<h1>เลือกสี</h1>
<input type="color" id="colorPicker">
<div id="colorCode">เลือกสีเพื่อแสดงโค้ด HTML</div>

<script>
    const colorPicker = document.getElementById('colorPicker');
    const colorCode = document.getElementById('colorCode');

    colorPicker.addEventListener('input', function() {
        const selectedColor = colorPicker.value;
        colorCode.textContent = `โค้ดสี HTML: ${selectedColor}`;
    });
</script>

</body>
</html>
