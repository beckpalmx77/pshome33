document.addEventListener('DOMContentLoaded', function() {

    $('body').on('click', '.datetimepicker', function() {
        $(this).not('.hasDateTimePicker').datetimepicker({
            controlType: 'select',
            changeMonth: true,
            changeYear: true,
            dateFormat: "dd-mm-yy",
            timeFormat: 'HH:mm:ss',
            yearRange: "1900:+10",
            showOn:'focus',
            firstDay: 1
        }).focus();
    });

    $(".colorpicker").colorpicker();
    
    let calendarEl = document.getElementById('calendar');

    let calendar = new FullCalendar.Calendar(calendarEl, {
        plugins: ['interaction', 'dayGrid', 'timeGrid', 'list'],
        locale: 'th',
        header: {
            left: 'prev,next today',
            center: 'title',
            right: 'dayGridMonth,timeGridWeek,timeGridDay,listMonth'
        },
        navLinks: true, // can click day/week names to navigate views
        businessHours: true, // display business hours
        editable: true,
        //uncomment to have a default date
        //defaultDate: '2020-04-07',
        events: '../api/calendar/load.php',
        eventDrop: function(arg) {
            let start = arg.event.start.toDateString()+' '+arg.event.start.getHours()+':'+arg.event.start.getMinutes()+':'+arg.event.start.getSeconds();
            if (arg.event.end == null) {
                end = start;
            } else {
                let end = arg.event.end.toDateString()+' '+arg.event.end.getHours()+':'+arg.event.end.getMinutes()+':'+arg.event.end.getSeconds();
            }

            $.ajax({
              url:"api/calendar/update.php",
              type:"POST",
              data:{id:arg.event.id, start:start, end:end},
            });
        },
        eventResize: function(arg) {
            let start = arg.event.start.toDateString()+' '+arg.event.start.getHours()+':'+arg.event.start.getMinutes()+':'+arg.event.start.getSeconds();
            let end = arg.event.end.toDateString()+' '+arg.event.end.getHours()+':'+arg.event.end.getMinutes()+':'+arg.event.end.getSeconds();

            $.ajax({
              url:"api/calendar/update.php",
              type:"POST",
              data:{id:arg.event.id, start:start, end:end},
            });
        },
        eventClick: function(arg) {
            let id = arg.event.id;
            
            $('#editEventId').val(id);
            $('#deleteEvent').attr('data-id', id); 

            $.ajax({
              url:"api/calendar/getevent.php",
              type:"POST",
              dataType: 'json',
              data:{id:id},
              success: function(data) {
                    $('#editEventTitle').val(data.title);
                    $('#editStartDate').val(data.start);
                    $('#editEndDate').val(data.end);
                    $('#editColor').val(data.color);
                    $('#editTextColor').val(data.textColor);
                    $('#editeventmodal').modal();
                }
            });

            $('body').on('click', '#deleteEvent', function() {
                if(confirm("Are you sure you want to remove it?")) {
                    $.ajax({
                        url:"api/calendar/delete.php",
                        type:"POST",
                        data:{id:arg.event.id},
                    }); 

                    //close model
                    $('#editeventmodal').modal('hide');

                    //refresh calendar
                    calendar.refetchEvents();         
                }
            });
            
            calendar.refetchEvents();
        }
    });

    calendar.render();

    $('#createEvent').submit(function(event) {

        // stop the form refreshing the page
        event.preventDefault();

        $('.form-group').removeClass('has-error'); // remove the error class
        $('.help-block').remove(); // remove the error text

        // process the form
        $.ajax({
            type        : "POST",
            url         : '../api/calendar/insert.php',
            data        : $(this).serialize(),
            dataType    : 'json',
            encode      : true
        }).done(function(data) {

            // insert worked
            if (data.success) {
                
                //remove any form data
                $('#createEvent').trigger("reset");

                //close model
                $('#addeventmodal').modal('hide');

                //refresh calendar
                calendar.refetchEvents();

            } else {

                //if error exists update html
                if (data.errors.date) {
                    $('#date-group').addClass('has-error');
                    $('#date-group').append('<div class="help-block">' + data.errors.date + '</div>');
                }

                if (data.errors.title) {
                    $('#title-group').addClass('has-error');
                    $('#title-group').append('<div class="help-block">' + data.errors.title + '</div>');
                }

            }

        });
    });

    $('#editEvent').submit(function(event) {

        // stop the form refreshing the page
        event.preventDefault();

        $('.form-group').removeClass('has-error'); // remove the error class
        $('.help-block').remove(); // remove the error text

        //form data
        let id = $('#editEventId').val();
        let title = $('#editEventTitle').val();
        let start = $('#editStartDate').val();
        let end = $('#editEndDate').val();
        let color = $('#editColor').val();
        let textColor = $('#editTextColor').val();

        // process the form
        $.ajax({
            type        : "POST",
            url         : '../api/calendar/update.php',
            data        : {
                id:id, 
                title:title, 
                start:start,
                end:end,
                color:color,
                text_color:textColor
            },
            dataType    : 'json',
            encode      : true
        }).done(function(data) {

            // insert worked
            if (data.success) {
                
                //remove any form data
                $('#editEvent').trigger("reset");

                //close model
                $('#editeventmodal').modal('hide');

                //refresh calendar
                calendar.refetchEvents();

            } else {

                //if error exists update html
                if (data.errors.date) {
                    $('#date-group').addClass('has-error');
                    $('#date-group').append('<div class="help-block">' + data.errors.date + '</div>');
                }

                if (data.errors.title) {
                    $('#title-group').addClass('has-error');
                    $('#title-group').append('<div class="help-block">' + data.errors.title + '</div>');
                }

            }

        });
    });
});
