<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>

<?php

include('engine/get_data_chart_4year.php');

?>


<script>


    const labels = [
        'มกราคม',
        'กุมภาพันธ์',
        'มีนาคม',
        'เมษายน',
        'พฤษภาคม',
        'มิถุนายน',
        'กรกฎาคม',
        'สิงหาคม',
        'กันยายน',
        'ตุลาคม',
        'พฤศจิกายน',
        'ธันวาคม',
    ];

    const data = {
        labels: labels,
        datasets: [{
            label: <?php echo $label1?>,
            backgroundColor: 'rgb(255, 99, 132)',
            borderColor: 'rgb(255, 99, 132)',
            data: <?php echo $data1?>,
        },
            {
                label: <?php echo $label2?>,
                backgroundColor: 'rgb(243,87,4)',
                borderColor: 'rgb(248,117,85)',
                data: <?php echo $data2?>,
            },
            {
                label: <?php echo $label3?>,
                backgroundColor: 'rgb(16,241,46)',
                borderColor: 'rgb(135,245,88)',
                data: <?php echo $data3?>,
            },
            {
                label: <?php echo $label4?>,
                backgroundColor: 'rgb(6,107,215)',
                borderColor: 'rgb(88,141,245)',
                data: <?php echo $data4?>,
            }
        ]
    };

    const config = {
        type: 'line',
        data: data,
        options: {}
    };

</script>

<style>

    body {
        width: 1024px;
        margin: 3rem auto;
    }

    #chart-container {
        width: 100%;
        height: auto;
    }
</style>


<div>
    <canvas id="myChart"></canvas>
</div>

<script>
    const myChart = new Chart(
        document.getElementById('myChart'),
        config
    );
</script>