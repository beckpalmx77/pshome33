<script type="text/javascript">
    $(document).ready(function () {
        debugger;
        $.ajax({
            type: "POST",
            contentType: "application/json; charset=utf-8",
            url: "CampComparison.aspx/getLineChartDataload",
            data:{},
            async: true,
            cache: false,
            dataType: "json",
            success: OnSuccess_,
            error: OnErrorCall_
        })

        function OnSuccess_(reponse) {

            var aData = reponse.d;
            var aLabels = aData[0];
            var aDatasets1 = aData[1];
            var aDatasets2 = aData[2];
            var aDatasets3 = aData[3];
            var aDatasets4 = aData[4];
            var aDatasets5 = aData[5];

            var lineChartData = {
                labels: aLabels,
                datasets: [
                    {
                        label: "Data1",
                        //fill:false,
                        fillColor: "rgba(0,0,0,0)",
                        strokeColor: "rgba(220,220,220,1)",
                        pointColor: "rgba(200,122,20,1)",

                        data: aDatasets1
                    },
                    {
                        label: "Data2",
                        fillColor: 'rgba(0,0,0,0)',
                        strokeColor: 'rgba(220,180,0,1)',
                        pointColor: 'rgba(220,180,0,1)',
                        data: aDatasets2
                    },
                    {

                        label: "Data5",
                        fillColor: "rgba(0,0,0,0)",
                        strokeColor: "rgba(151,187,205,1)",
                        pointColor: "rgba(152,188,204,1)",
                        data: aDatasets3
                    },

                    {
                        label: "Data4",
                        fillColor: 'rgba(0,0,0,0)',
                        strokeColor: 'rgba(151,187,205,1)',
                        pointColor: 'rgba(151,187,205,1)',
                        data: aDatasets4
                    },
                    {
                        label: "Data4",
                        fillColor: 'rgba(0,0,0,0)',
                        strokeColor: 'rgba(151,187,205,1)',
                        pointColor: 'rgba(151,187,205,1)',

                        data: aDatasets5
                    },


                ]
            }
            Chart.defaults.global.animationSteps = 50;
            Chart.defaults.global.tooltipYPadding = 16;
            Chart.defaults.global.tooltipCornerRadius = 0;
            Chart.defaults.global.tooltipTitleFontStyle = "normal";
            Chart.defaults.global.tooltipFillColor = "rgba(0,160,0,0.8)";
            Chart.defaults.global.animationEasing = "easeOutBounce";
            Chart.defaults.global.responsive = true;
            Chart.defaults.global.scaleLineColor = "black";
            Chart.defaults.global.scaleFontSize = 16;
            //lineChart.destroy();
            //document.getElementById("canvas").innerHTML = '&nbsp;';
            //document.getElementById("chartContainer").innerHTML = '&nbsp;';
            //document.getElementById("chartContainer").innerHTML = '<canvas id="canvas" style="width: 650px; height: 350px;"></canvas>';
            //var ctx = document.getElementById("canvas").getContext("2d");
            //ctx.innerHTML = "";
            //var pieChartContent = document.getElementById('pieChartContent');
            //pieChartContent.innerHTML = '&nbsp;';
            //$('#pieChartContent').append('<canvas id="canvas" width="650px" height="350px"><canvas>');

            //ctx = $("#canvas").get(0).getContext("2d");
            var ctx = document.getElementById("canvas").getContext("2d");
            var lineChart = new Chart(ctx).Line(lineChartData, {

                bezierCurve: true,
                chartArea: { width: '62%' },
                responsive: true,
                pointDotRadius: 10,
                scaleShowVerticalLines: false,
                scaleGridLineColor: 'black'


            });
        }
        function OnErrorCall_(repo) {
            //alert(repo);
        }
    });

    //});

</script>

