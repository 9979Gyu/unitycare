// using
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
                    displayResults(response.programs, response.offers);
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

    // Function to display result of programs and job offers based on the user input keyword
    function displayResults(programs, offers) {
        // Clear previous results
        $('#searchResults').empty(); 
    
        // No record exists
        if(programs.length === 0 && offers.length === 0){
            $('#searchResults').append('<li class="list-group-item">Tiada rekod berkenaan</li>');
        } 
        else {
            if(programs.length > 0){
                $('#searchResults').append(
                    '<div class="accordion-item">'+
                        '<h2 class="accordion-header" id="headingPrograms">'+
                            '<button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#collapsePrograms" aria-expanded="true" aria-controls="collapsePrograms">' +
                                'Programs' +
                            '</button>' +
                        '</h2>' +
                        '<div id="collapsePrograms" class="accordion-collapse collapse show" aria-labelledby="headingPrograms" data-bs-parent="#searchResults">' +
                            '<div class="accordion-body">' +
                                '<ul class="list-group" id="programsList"></ul>' +
                            '</div>' +
                        '</div>' +
                    '</div>'
                );
    
                // Display each program
                programs.forEach(function(program) {
                    var organizationName = program.username;
                    var programName = '<a href="/joinprogram/' + program.program_id + '">' + program.name + '</a>';
    
                    $('#programsList').append(
                        '<li class="list-group-item">' + programName + ' <br> ' + organizationName + '<br>' + 
                            parseDate(program.start_date) + ' ' + program.start_time + ' - ' + parseDate(program.end_date) + ' ' + program.end_time + 
                        '</li>'
                    );
                });
            }
            
            if(offers.length > 0){
                $('#searchResults').append(
                    '<div class="accordion-item">'+
                        '<h2 class="accordion-header" id="headingOffers">'+
                            '<button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#collapseOffers" aria-expanded="true" aria-controls="collapseOffers">' +
                                'Pekerjaan' +
                            '</button>' +
                        '</h2>' +
                        '<div id="collapseOffers" class="accordion-collapse collapse show" aria-labelledby="headingOffers" data-bs-parent="#searchResults">' +
                            '<div class="accordion-body">' +
                                '<ul class="list-group" id="offersList"></ul>' +
                            '</div>' +
                        '</div>' +
                    '</div>'
                );
    
                // Display each offer
                offers.forEach(function(offer) {
                    var organizationName = offer.username;
                    var jobName = '<a href="/joinoffer/' + offer.offer_id + '">' + offer.jobposition + '</a>';
    
                    $('#offersList').append(
                        '<li class="list-group-item">' + jobName + ' <br> ' + organizationName + '<br>Salary: RM ' + 
                            offer.min_salary + ' - RM ' + offer.max_salary + '</li><br>'
                    );
                });
            }
        }
    }
    
    function parseDate(date){
             
        // Format dates
        var newDate = new Date(date);
    
        // Define the day names array
        var days = ['Ahad', 'Isnin', 'Selasa', 'Rabu', 'Khamis', 'Jumaat', 'Sabtu'];
    
        // Get the day name using the day number of the week
        var dayName = days[newDate.getDay()];
    
        // Format the date as "Day, dd-mm-yyyy"
        var formattedDate = dayName + ', ' + ('0' + newDate.getDate()).slice(-2) + '-' + ('0' + (newDate.getMonth() + 1)).slice(-2) + '-' + newDate.getFullYear();
    
        return formattedDate;
    
    }

});
