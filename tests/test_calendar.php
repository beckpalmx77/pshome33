<!-- FullCalender.io -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/fullcalendar/3.4.0/fullcalendar.css" />
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.2.1/jquery.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jqueryui/1.12.1/jquery-ui.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.18.1/moment.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/fullcalendar/3.4.0/fullcalendar.min.js"></script>
<script type="text/javascript">
    $(document).ready(function() {
        let calendar = $('#calendar').fullCalendar({
            editable: true,
            header: {
                left: 'prev,next today',
                center: 'title',
                right: 'month,agendaWeek,agendaDay'
            },
            events: 'fullcalender.io/load.php',
            selectable: true,
            selectHelper: true,

            select: function(start, end, allDay) {
                let title = prompt("Enter Event Title");
                if (title) {
                    let start = $.fullCalendar.formatDate(start, "Y-MM-DD HH:mm:ss");
                    let end = $.fullCalendar.formatDate(end, "Y-MM-DD HH:mm:ss");
                    $.ajax({
                        url: "fullcalender.io/insert.php",
                        type: "POST",
                        data: {
                            title: title,
                            start: start,
                            end: end
                        },
                        success: function() {
                            calendar.fullCalendar('refetchEvents');
                            alert("Added Successfully");
                        }
                    })
                }
            },

            editable: true,

            eventResize: function(event) {
                let start = $.fullCalendar.formatDate(event.start, "Y-MM-DD HH:mm:ss");
                let end = $.fullCalendar.formatDate(event.end, "Y-MM-DD HH:mm:ss");
                let title = event.title;
                let id = event.id;
                $.ajax({
                    url: "fullcalender.io/update.php",
                    type: "POST",
                    data: {
                        title: title,
                        start: start,
                        end: end,
                        id: id
                    },
                    success: function() {
                        calendar.fullCalendar('refetchEvents');
                        alert('Event Update');
                    }
                })
            },

            eventDrop: function(event) {
                let start = $.fullCalendar.formatDate(event.start, "Y-MM-DD HH:mm:ss");
                let end = $.fullCalendar.formatDate(event.end, "Y-MM-DD HH:mm:ss");
                let title = event.title;
                let id = event.id;
                $.ajax({
                    url: "fullcalender.io/update.php",
                    type: "POST",
                    data: {
                        title: title,
                        start: start,
                        end: end,
                        id: id
                    },
                    success: function() {
                        calendar.fullCalendar('refetchEvents');
                        alert("Event Updated");
                    }
                });
            },

            eventClick: function(event) {
                if (confirm("Are you sure you want to remove it?")) {
                    let id = event.id;
                    $.ajax({
                        url: "fullcalender.io/delete.php",
                        type: "POST",
                        data: {
                            id: id
                        },
                        success: function() {
                            calendar.fullCalendar('refetchEvents');
                            alert("Event Removed");
                        }
                    })
                }
            },

        });
    });
</script>
<!-- FullCalender.io -->