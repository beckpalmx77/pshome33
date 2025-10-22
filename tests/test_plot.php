<!DOCTYPE html>
<html>
<script src="https://cdn.plot.ly/plotly-latest.min.js"></script>
<body>

<div id="myPlot" style="width:100%;max-width:700px"></div>

<script>
    let xArray = ["Italy", "France", "Spain", "USA", "Argentina"];
    let yArray = [55, 49, 44, 24, 15];

    let layout = {title:"World Wide Wine Production"};

    let data = [{labels:xArray, values:yArray, hole:.4, type:"pie"}];

    Plotly.newPlot("myPlot", data, layout);

</script>

</body>
</html>
