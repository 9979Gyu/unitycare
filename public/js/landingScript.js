$(document).ready(function(){
    // Initialize Bootstrap tooltip
    $('[data-bs-toggle="tooltip"]').tooltip();

    var toolTipContent = '<ul><li>Nama Program atau Pekerjaan (Contoh: Kerjaya)</li>' +
        '<li>Tarikh Mula (Contoh: 24-06-2023)</li>' +
        '</ul>';

    // Set tooltip content for the info circle icon
    $(".bi-info-circle-fill").attr('title', toolTipContent);

    // Manage event on calendar
    const calendarEl = document.getElementById('calendar');
    const calendar = new FullCalendar.Calendar(calendarEl, {
        initialView: 'dayGridMonth',
        locale: 'ms',
        buttonText: {
            today: 'Hari Ini',
        },
        events: {
            url: '/getPrograms',
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

    $('#searchBtn').on('click', function() {
        const input = $("#searchInput").val().trim();

        // Perform search only if query is not empty
        if (input !== '') {
            $.ajax({
                url: '/search',
                method: 'GET',
                data: { 
                    query: input,
                },
                success: function(response) {
                    console.log(response.option);
                    displayResults(response.results, response.option);
                },
                error: function(xhr, status, error) {
                    console.error(error);
                }
            });
        } 
        else {
            // Clear results if query is empty
            $('#searchResults').empty(); 
        }
    });

    function displayResults(results, option) {
        $('#searchResults').empty(); // Clear previous results

        if(results.length === 0){
            $('#searchResults').append('<li class="list-group-item">Tiada rekod berkenaan</li>');
        }
        if(option == "programs"){
            // Display each search result
            results.forEach(function(program) {
                var organizationName = program.organization.name;
                var programName = '<a href="/joinprogram/' + program.program_id + '">' + program.name + '</a>';
        
                // Format start and end dates
                var startDate = new Date(program.start_date);
                var endDate = new Date(program.end_date);

                // Format start and end times
                var startTime = program.start_time;
                var endTime = program.end_time;

                // Define the day names array
                var days = ['Ahad', 'Isnin', 'Selasa', 'Rabu', 'Khamis', 'Jumaat', 'Sabtu'];

                // Get the day name using the day number of the week
                var dayName = days[startDate.getDay()];
                var endDayName = days[endDate.getDay()];

                // Format the date as "Day, dd-mm-yyyy"
                var formattedStartDate = dayName + ', ' + ('0' + startDate.getDate()).slice(-2) + '-' + ('0' + (startDate.getMonth() + 1)).slice(-2) + '-' + startDate.getFullYear();
                var formattedEndDate = endDayName + ', ' + ('0' + endDate.getDate()).slice(-2) + '-' + ('0' + (endDate.getMonth() + 1)).slice(-2) + '-' + endDate.getFullYear();

                $('#searchResults').append(
                    '<li class="list-group-item">' + programName + ' <br> ' + organizationName + '<br>' + 
                        formattedStartDate + ' ' + program.start_time + ' - ' + formattedEndDate + ' ' + program.end_time + '</li><br>'
                );
            });
        }
        if(option == "offers"){
            // Display each search result
            results.forEach(function(result) {
                console.log(result);
                var organizationName = result.organization.name;
                var jobName = '<a href="/joinoffer/' + 
                result.offer_id + '">' + result.job.position + '</a>';

                $('#searchResults').append(
                    '<li class="list-group-item">' + jobName + ' <br> ' + organizationName + '<br>Salary: RM ' + 
                        result.min_salary + ' - RM ' + result.max_salary + '</li><br>'
                );
            });
        }
        
    }

});
