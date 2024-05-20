$(document).ready(function(){
    const calendarEl = document.getElementById('calendar')
    const calendar = new FullCalendar.Calendar(calendarEl, {
        initialView: 'dayGridMonth',
        locale: 'ms',
        buttonText: {
            today: 'Hari Ini',
        },
        events: {
            url: '/getEvents',
            method: 'GET'
        },
        eventClick: function(info) {
            // Redirect to the joinprogram page with the program ID
            window.location.href = '/joinprogram/' + info.event.id;
        }
    });

    calendar.render();

    // Re-render when tab is shown
    $('#program-tab').on('shown.bs.tab', function (event) {
        calendar.render();
    });
});
