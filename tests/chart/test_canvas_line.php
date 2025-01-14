<script src="https://canvasjs.com/assets/script/canvasjs.min.js"></script>
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>

<?php

$dataPoints = array(
    array("x" => 946665000000, "y" => 3289000),
    array("x" => 978287400000, "y" => 3830000),
    array("x" => 1009823400000, "y" => 2009000),
    array("x" => 1041359400000, "y" => 2840000),
    array("x" => 1072895400000, "y" => 2396000),
    array("x" => 1104517800000, "y" => 1613000),
    array("x" => 1136053800000, "y" => 1821000),
    array("x" => 1167589800000, "y" => 2000000),
    array("x" => 1199125800000, "y" => 1397000),
    array("x" => 1230748200000, "y" => 2506000),
    array("x" => 1262284200000, "y" => 6704000),
    array("x" => 1293820200000, "y" => 5704000),
    array("x" => 1325356200000, "y" => 4009000),
    array("x" => 1356978600000, "y" => 3026000),
    array("x" => 1388514600000, "y" => 2394000),
    array("x" => 1420050600000, "y" => 1872000),
    array("x" => 1451586600000, "y" => 2140000)
);

?>
<!DOCTYPE HTML>
<html>
<head>
    <script>
        window.onload = function () {

            var chart = new CanvasJS.Chart("chartContainer", {
                animationEnabled: true,
                title: {
                    text: "Company Revenue by Year"
                },
                axisY: {
                    title: "Revenue in USD",
                    valueFormatString: "#0,,.",
                    suffix: "mn",
                    prefix: "$"
                },
                data: [{
                    type: "spline",
                    markerSize: 5,
                    xValueFormatString: "YYYY",
                    yValueFormatString: "$#,##0.##",
                    xValueType: "dateTime",
                    dataPoints: <?php echo json_encode($dataPoints, JSON_NUMERIC_CHECK); ?>
                }]
            });

            chart.render();

        }
    </script>

    <script>

        $(document).ready(function () {
            alert("66");
            $.ajax({
                type: "POST",
                url: 'get_data.php',
                dataType: "json",
                data: formData,
                success: function (response) {
                    alert(response);
                },
                error: function (response) {
                    alertify.error("error : " + response);
                }
            });
        });

    </script>

    <script>
        $(document).ready(function () {
            // Instantiate an xhr object
            let xhr = new XMLHttpRequest();

            // What to do when response is ready
            xhr.onreadystatechange = () => {
                if (xhr.readyState === 4) {
                    if (xhr.status === 200) {
                        //document.getElementById("txt").innerHTML =
                            //xhr.responseText;
                        alert(xhr.responseText);
                    } else {
                        console.log('Error Code: ' + xhr.status);
                        console.log('Error Message: ' + xhr.statusText);
                    }
                }
            }
            xhr.open('GET', 'get_data.php');

            // Send the request
            xhr.send();
        });
    </script>


    <script>
        let fetchBtn = document.getElementById('fetchBtn');
        fetchBtn.addEventListener('click', buttonClickHandler);

        function buttonClickHandler() {

            // Instantiate an xhr object
            var xhr = new XMLHttpRequest();

            // What to do when response is ready
            xhr.onreadystatechange = () => {
                if (xhr.readyState === 4) {
                    if (xhr.status === 200) {
                        document.getElementById("txt").innerHTML =
                            xhr.responseText;
                    } else {
                        console.log('Error Code: ' + xhr.status);
                        console.log('Error Message: ' + xhr.statusText);
                    }
                }
            }
            xhr.open('GET', 'data.php');

            // Send the request
            xhr.send();
        }
    </script>

</head>
<body>
<div id="chartContainer" style="height: 370px; width: 100%;"></div>
</body>
</html>
