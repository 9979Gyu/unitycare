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

    // // Add slide up and slide down animation for accordion
    // $(document).on('click', '.accordion-button', function() {
    //     const target = $(this).attr('data-bs-target');
    //     const targetElement = $(target);

    //     targetElement.stop(true, true).slideToggle(300, function() {
    //         targetElement.toggleClass('show');
    //     });
    // });



    // Manage job offer
    // $('#job-tab').on('shown.bs.tab', function (event) {
    //     updateCardContainer();
    // });
    
    // $(".card-container").on("click", ".card", function() {

    //     // Hide all other collapse-content except the clicked one
    //     $(".collapse-content").not($(this).find(".collapse-content")).slideUp();

    //     // Toggle the collapse-content of the clicked card
    //     $(this).find(".collapse-content").slideToggle();
        
    // });
    

    // function updateCardContainer(){
    //     $.ajax({
    //         type: 'GET',
    //         url: "/getCountPosition",
    //         success: function(data) {

    //             $(".card-container").empty();

    //             $.each(data.events, function(index, sector){

    //                 // Append sector to the card-container
    //                 $(".card-container").append(
    //                     '<div class="card mb-2" id="' + sector.sectorid + '">' +
    //                         '<div class="card-body d-flex justify-content-between">' +
    //                             '<div><h4 class="card-title">' + sector.sectorname + 
    //                             ' (' + sector.offercount + ')'  + '</h4></div>' +
    //                         '</div>' +
    //                         '<div class="collapse-content">' +
    //                             '<p>Additional information about ' + sector.sectorname + '.</p>' +
    //                         '</div>' +
    //                     '</div>'
    //                 );
                    
    //                 $(".collapse-content").css("display", "none");
                    
    //             });
                
    //         },
    //         error: function (data) {
    //             $('.condition-message').html(data);
    //         }
    //     });
    // }

    // function updateCardContainer2(){
    //     $.ajax({
    //         type: 'GET',
    //         url: "/getCountPosition",
    //         success: function(data) {

    //             $(".card-container").empty();

    //             $.each(data.events, function(index, offer){

    //                 var button;

    //                 button = '<a class="viewAnchor btn btn-info m-2" href="/joinoffer/' + offer.offer_id + '">' +
    //                     '<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-search" viewBox="0 0 16 16">' +
    //                     '<path d="M11.742 10.344a6.5 6.5 0 1 0-1.397 1.398h-.001q.044.06.098.115l3.85 3.85a1 1 0 0 0 1.415-1.414l-3.85-3.85a1 1 0 0 0-.115-.1zM12 6.5a5.5 5.5 0 1 1-11 0 5.5 5.5 0 0 1 11 0"/>' +
    //                     '</svg> Lihat </a>';

    //                 $(".card-container").append(
    //                     '<p><div class="card" id="' + offer.offer_id + '">' +
    //                         '<div class="card-body d-flex justify-content-between">' +
    //                             '<div><h4 class="card-title">' + offer.jobposition + '</h4>' +
    //                             '<div><p class="card-text">' + offer.username + '</p>' +
    //                                 '<p class="card-text badge badge-primary"> RM ' + offer.min_salary + ' - RM ' + offer.max_salary + ' sebulan</p>' +
    //                                 ' <p class="card-text badge badge-primary">' + offer.typename + '</p>' +
    //                                 ' <p class="card-text badge badge-primary">' + offer.shiftname + '</p>' +
    //                             '</div></div>' +
    //                             '<div>' + button + '</div>' +
    //                         '</div>' +
    //                     '</div></p>'
    //                 );
                    
    //             });
    //         },
    //         error: function (data) {
    //             $('.condition-message').html(data);
    //         }
    //     });
    // }
});
