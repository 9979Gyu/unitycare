$(document).ready(function(){

    var selectedSector;
    var selectedPosition;

    // Re-render when tab is shown
    $('#program-tab').on('shown.bs.tab', function (event) {
        calendar.render();
    });

    // Manage job offer
    $('#job-tab').on('shown.bs.tab', function (event) {
        updateCardContainer();
    });
    
    $(".card-container").on("click", ".card", function() {

        selectedSector = $(this).attr("id");

        // Toggle the collapse-position of the clicked card
        $(this).find(".collapse-position").slideToggle();

        // Hide all other collapse-position except the clicked one
        $(".collapse-position").not($(this).find(".collapse-position")).slideUp();

        // Call function to display position based on sector selected
        updateCollapsePosition(selectedSector);

    });

    // Expand/collapse the positions within a sector
    $(".card-container").on("click", ".collapse-position li", function(e) {
        // Prevent the click from bubbling up to the parent card
        e.stopPropagation();

        selectedPosition = $(this).attr("id");
        
        console.log(selectedPosition);

        // Toggle the collapse-offer of the clicked card
        $(this).find(".collapse-offer").slideToggle();

        // Hide all other collapse-offer except the clicked one
        $(".collapse-offer").not($(this).find(".collapse-offer")).slideUp();

        // Call function to display offers based on position and sector selected
        updateCollapseOffer(selectedSector, selectedPosition)
    });
    
    function updateCardContainer(){
        $.ajax({
            type: 'GET',
            url: "/getCountPosition",
            success: function(data) {

                $(".card-container").empty();

                $.each(data.events, function(index, sector){

                    // Append sector to the card-container
                    $(".card-container").append(
                        '<div class="card mb-2" id="' + sector.sectorid + '">' +
                            '<a href="#"><div class="card-body d-flex justify-content-between">' +
                                '<div><h4 class="card-title">' + sector.sectorname + 
                                ' (' + sector.offercount + ')</h4></div>' +
                            '</div></a>' +
                            '<ul class="collapse-position"></ul>' +
                        '</div>'
                    );

                    $(".collapse-position").css("display", "none");

                });
                
            },
            error: function (data) {
                $('.condition-message').html(data);
            }
        });
    }

    function updateCollapsePosition(selectedSector){
        $.ajax({
            type: 'GET',
            url: "/getCountOffer",
            data: { sectorID: selectedSector },
            success: function(data) {

                var selectedCollapse = $("#" + selectedSector).find(".collapse-position");

                selectedCollapse.empty();

                $.each(data.events, function(index, position){

                    var positionID = position.jobposition.replace(/\s+/g, '_');

                    selectedCollapse.append(
                        '<div>' + 
                            '<a href="#"><li class="d-flex justify-content-between" id="' + positionID + '">' +
                                '<p>' + position.jobposition + ' (' + position.offercount + ')</p>' +
                            '</li></a>' + 
                            '<ul class="collapse-offer"></ul>' +
                        '</div>'
                    );
    
                    $(".collapse-offer").css("display", "none");

                });
            },
            error: function (data) {
                $('.condition-message').html(data);
            }
        });
    }
    
    function updateCollapseOffer(selectedSector, selectedPosition){

        
        $(".collapse-offer").css("display", "block");

        // var selectedCollapse = $("#" + selectedPosition).find('.collapse-offer');
        var selectedCollapse = $("#" + selectedSector).find("#" + selectedPosition).find(".collapse-offer");
        // var selectedCollapse = $('.collapse-offer');

        selectedPosition = selectedPosition.replace(/_/g, ' ');

        $.ajax({
            type: 'GET',
            url: "/getJobs",
            data: {
                sectorID: selectedSector,
                positionName: selectedPosition
            },
            success: function(data) {
                selectedCollapse.empty();

                $.each(data.events, function(index, offer){

                    console.log(offer);

                    selectedCollapse.append(
                        '<li class="d-flex justify-content-between" id="' + offer.offer_id + '">' +
                            '<p>' + offer.username + '</p>' +
                            // '<div><p>' + offer.username + '</p>' +
                            // '<p class="badge badge-primary"> RM ' + offer.min_salary + 
                            // ' - RM ' + offer.max_salary + ' sebulan</p>' +
                            // ' <p class="badge badge-primary">' + offer.typename + '</p>' +
                            // ' <p class="badge badge-primary">' + offer.shiftname + '</p></div>' +
                        '</li>'
                    );
                });

            },
            error: function (data) {
                $('.condition-message').html(data);
            }
        });
    }

    
});
