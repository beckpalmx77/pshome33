<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FullCalendar with Bootstrap 5</title>
    <!-- Bootstrap CSS -->
    <link href="https://maxcdn.bootstrapcdn.com/bootstrap/5.1.3/css/bootstrap.min.css" rel="stylesheet">
    <!-- FullCalendar CSS -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/fullcalendar/5.10.1/main.min.css" rel="stylesheet">
    <style>
        #calendar {
            max-width: 900px;
            margin: 0 auto;
        }
    </style>
</head>
<body>
<div class="container mt-5">
    <div id="calendar"></div>
</div>

<!-- jQuery -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<!-- Bootstrap JS -->
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/5.1.3/js/bootstrap.bundle.min.js"></script>
<!-- FullCalendar JS -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/fullcalendar/5.10.1/main.min.js"></script>
<!-- FullCalendar locales (optional) -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/fullcalendar/5.10.1/locales-all.min.js"></script>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        let calendarEl = document.getElementById('calendar');

        let calendar = new FullCalendar.Calendar(calendarEl, {
            initialView: 'dayGridMonth',
            headerToolbar: {
                left: 'prev,next today',
                center: 'title',
                right: 'dayGridMonth,timeGridWeek,timeGridDay'
            },
            events: {
                url: 'db_connect.php',
                failure: function() {
                    alert('There was an error while fetching events!');
                }
            }
        });

        calendar.render();
    });
</script>
</body>
</html>