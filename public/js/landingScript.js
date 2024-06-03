$(document).ready(function(){

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
        const selectedOption = $("#searchOption").val().toLowerCase();

        // Perform search only if query is not empty
        if (input !== '') {
            $.ajax({
                url: '/search',
                method: 'GET',
                data: { 
                    query: input,
                    option: selectedOption, 
                },
                success: function(response) {
                    displayResults(response, selectedOption);
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
        else if(option == "program"){
            // Display each search result
            results.forEach(function(program) {
                var organizationName = program.organization.name;
                var programName = '<a href="/joinprogram/' + program.program_id + '">' + program.name + '</a>';
        
                $('#searchResults').append(
                    '<li class="list-group-item">' + programName + ' <br> ' + organizationName + '<br>Date: ' + 
                        program.start_date + ' - ' + program.end_date + '</li><br>'
                );
            });
        }
        else{
            // Display each search result
            results.forEach(function(result) {
                // Iterate over each job offer for the current job
                result.job_offers.forEach(function(jobOffer) {
                    var organizationName = jobOffer.organization.name;
                    var jobName = '<a href="/joinoffer/' + 
                    jobOffer.offer_id + '">' + result.name + '</a>';

                    $('#searchResults').append(
                        '<li class="list-group-item">' + jobName + ' <br> ' + organizationName + '<br>Salary: RM ' + 
                            jobOffer.min_salary + ' - RM ' + jobOffer.max_salary + '</li><br>'
                    );
                });
            });
        }
        
    }

});
