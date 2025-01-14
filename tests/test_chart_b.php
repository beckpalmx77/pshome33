<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Bar chart with data value on the top of each bar</title>
    <!--script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/2.4.0/Chart.min.js"></script-->
    <script src="../js/jquery-3.6.0.js"></script>
    <script src="../js/chartjs-2.9.0.js"></script>
</head>
<body>
<div class="chart-container" style="position: relative; width:80vw">
    <canvas id="my_Chart"></canvas>
</div>
<script>
    // Data define for bar chart
    let myData = {
        labels: ["Javascript", "Java", "Python", "PHP", "C++", "TypeScript", "Linux Shell","C","Ruby on Rails"],
        datasets: [{
            label: "Hey, baby!",
            fill: false,
            backgroundColor: ['#ff0000', '#ff4000', '#ff8000', '#ffbf00', '#ffbf00', '#ffff00', '#bfff00', '#80ff00', '#40ff00', '#00ff00'],
            borderColor: 'black',
            data: [85, 60,70, 50, 18, 20, 45, 30, 20],
        }]
    };
    // Options to display value on top of bars
    let myoption = {
        tooltips: {
            enabled: true
        },
        hover: {
            animationDuration: 1
        },
        animation: {
            duration: 1,
            onComplete: function () {
                let chartInstance = this.chart,
                    ctx = chartInstance.ctx;
                ctx.textAlign = 'center';
                ctx.fillStyle = "rgba(0, 0, 0, 1)";
                ctx.textBaseline = 'bottom';
                this.data.datasets.forEach(function (dataset, i) {
                    let meta = chartInstance.controller.getDatasetMeta(i);
                    meta.data.forEach(function (bar, index) {
                        let data = dataset.data[index];
                        ctx.fillText(data, bar._model.x, bar._model.y - 5);
                    });
                });
            }
        }
    };
    //Code to drow Chart
    let ctx = document.getElementById('my_Chart').getContext('2d');
    let myChart = new Chart(ctx, {
        type: 'bar',    	// Define chart type
        data: myData,    	// Chart data
        options: myoption 	// Chart Options [This is optional paramenter use to add some extra things in the chart].
    });
</script>
</body>
</html>