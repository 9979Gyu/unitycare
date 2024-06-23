$(document).ready(function() {
        
    // Initialize Bootstrap tooltip
    $('[data-bs-toggle="tooltip"]').tooltip();

    // Initialize select2
    $('#citystate').select2({
        placeholder: 'Pilih Bandar atau Negeri',
        allowClear: true,
    });


    // Define and declare variables
    var roleID = $("#roleID").val();
    var selectedShift = "all";
    var selectedValue = "all";

    // Function to handle B40 / OKU case
    getCityState();
    updateCardContainer();

    $("#searchBtn").click(function(){
        updateCardContainer();
    });

    $('#jobType').on('change', function() {
        selectedValue = $('#jobType option:selected').val();
        $('[data-toggle="tooltip' + selectedValue + '"]').tooltip();
        updateCardContainer();
    });

    $("#shiftType").on('change', function(){
        selectedShift = $("#shiftType option:selected").val();
        $('[data-toggle="tooltip' + selectedShift + '"]').tooltip();
        updateCardContainer();
    });

    // Funciton to display list of city and states
    function getCityState(){
        $.ajax({
            type: 'GET',
            url: "/getCityState",
            data:  { type : "offer" },
            success: function(data) {
                
                $("#citystate").empty();
                data.forEach(function(item){
                    $("#citystate").append('<option value="' + item.location + '">' + item.location + '</option>');
                });
            },
            error: function (data) {
                $('.condition-message').html(data);
            }
        });
    }

    // Funciton to display list of offers
    function updateCardContainer(){
        $uid = $("#uid").val();
        $.ajax({
            type: 'GET',
            url: "/getUpdatedOffers",
            success: function(data) {
                
                $(".card-container").empty();

                // No job exist
                if (data.allOffers.length == 0) {
                    $(".card-container").append("<div class='m-2'>Tiada rekod berkenaan</div>");
                }

                // Get the value of the input box and selected option
                var keyword = $('#keyword').val();
                var citystate = $('#citystate option:selected').val();

                $.each(data.allOffers, function(index, offer){

                    keyword = keyword ? keyword.toLowerCase() : null;
                    citystate = citystate ? citystate.toLowerCase() : null;

                    if(
                        (selectedShift === "all" || offer.shift_type_id == selectedShift) && 
                        (selectedValue === "all" || offer.job_type_id == selectedValue) && 
                        (!keyword || matchesKeyword(offer, keyword)) && 
                        (!citystate || matchesCityState(offer, citystate))
                    ){
                    
                        var button = '';
                        var approvalText = ''; // store the text of approval
                        var approvalColor = ''; // store the color of approval text
                        var reasonAdd = '';

                        var minsal = numberWithCommas(offer.min_salary);
                        var maxsal = numberWithCommas(offer.max_salary);;

                        // User is B40/OKU and job offer is approved by staff
                        if(roleID == 5 && offer.approval_status == 2 && offer.is_full == false){

                            // Already apply offer
                            if(offer.enrolled_approval_status != null){

                                var enrolledOffer = data.enrolledOffers[offer.offer_id];

                                button += '<a class="viewAnchor btn btn-info m-2" href="/joinoffer/' + offer.offer_id + '?action=true">' +
                                    '<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-search" viewBox="0 0 16 16">' +
                                    '<path d="M11.742 10.344a6.5 6.5 0 1 0-1.397 1.398h-.001q.044.06.098.115l3.85 3.85a1 1 0 0 0 1.415-1.414l-3.85-3.85a1 1 0 0 0-.115-.1zM12 6.5a5.5 5.5 0 1 1-11 0 5.5 5.5 0 0 1 11 0"/>' +
                                    '</svg> Lihat </a>';

                                reasonAdd = "Sebab mohon: " + enrolledOffer.description;

                                if(offer.enrolled_approval_status == 1){
                                    
                                    button += '<a class="dismissAnchor btn btn-danger" href="#" id="' + offer.offer_id + '" data-bs-toggle="modal" data-bs-target="#dismissModal">' +
                                        '<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="currentColor" class="bi bi-person-dash-fill" viewBox="0 0 16 16">' +
                                        '<path fill-rule="evenodd" d="M11 7.5a.5.5 0 0 1 .5-.5h4a.5.5 0 0 1 0 1h-4a.5.5 0 0 1-.5-.5"/>' +
                                        '<path d="M1 14s-1 0-1-1 1-4 6-4 6 3 6 4-1 1-1 1zm5-6a3 3 0 1 0 0-6 3 3 0 0 0 0 6"/>' +
                                        '</svg> Tarik Diri</a>';

                                }
                                else if(offer.enrolled_approval_status == 2){
                                    if(offer.enrolled_is_selected == 1){
                                        approvalText = "Permohonan Diterima. Sila Membuat Keputusan di Halaman Permohonan";
                                    }
                                    approvalColor = "text-success";
                                }
                                else {
                                    approvalText = "Permohonan Ditolak: " + enrolledOffer.reason;
                                    approvalColor = "text-danger";
                                }

                            }
                            // Did not enroll and not crash
                            else if(offer.crash == false && data.alwaysNo == false){
                                button += '<a class="applyAnchor btn btn-success" href="/joinoffer/' + offer.offer_id + '?action=nc1">' +
                                    '<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="currentColor" class="bi bi-person-plus-fill" viewBox="0 0 16 16">' +
                                    '<path d="M1 14s-1 0-1-1 1-4 6-4 6 3 6 4-1 1-1 1zm5-6a3 3 0 1 0 0-6 3 3 0 0 0 0 6"/>' +
                                    '<path fill-rule="evenodd" d="M13.5 5a.5.5 0 0 1 .5.5V7h1.5a.5.5 0 0 1 0 1H14v1.5a.5.5 0 0 1-1 0V8h-1.5a.5.5 0 0 1 0-1H13V5.5a.5.5 0 0 1 .5-.5"/>' +
                                    '</svg> Mohon</a>';
                            }

                            $(".card-container").append(
                                '<div class="card" id="' + offer.offer_id + '">' +
                                    '<div class="card-body d-flex justify-content-between flex-wrap">' +
                                        '<div class="m-3 mb-md-0 d-flex justify-content-center align-items-center">' +
                                            '<img src="' + offer.image + '" class="img-fluid square-box" alt="Imej Organisasi">' +
                                        '</div>' +
                                        '<div class="flex-fill">' +
                                            '<p class="card-text ' + approvalColor + '"><b>' + approvalText + '</b></p>' +
                                            '<h4 class="card-title">' + offer.jobposition + '</h4>' +
                                            '<p class="card-text">' + offer.username + '<br>' + offer.venue + ', ' + offer.postal_code + ', ' + offer.city + ', ' + offer.state + '</p>' +
                                            '<p class="card-text badge badge-primary"> RM ' + minsal + ' - RM ' + maxsal + ' sebulan</p>' +
                                            ' <p class="card-text badge badge-primary">' + offer.typename + '</p>' +
                                            ' <p class="card-text badge badge-primary">' + offer.shiftname + '</p>' +
                                            '<p class="card-text"><b>' + reasonAdd + '</b></p>' +
                                            '<p class="card-text text-secondary"> kemaskini ' + parseDate(offer.updateDate) + '</p>' +
                                        '</div>' +
                                        '<div class="text-center text-md-right">' + button + '</div>' +
                                    '</div>' +
                                '</div><br>'
                            );
                        }

                    }
                    
                });
                
                function matchesKeyword(offer, keyword) {
                    return offer.jobname.toLowerCase().includes(keyword) ||
                    offer.jobposition.toLowerCase().includes(keyword) ||
                    offer.shiftname.toLowerCase().includes(keyword) ||
                    offer.typename.toLowerCase().includes(keyword) ||
                    offer.username.toLowerCase().includes(keyword);
                }
                
                function matchesCityState(offer, cityState) {
                    return offer.city.toLowerCase().includes(cityState) ||
                    offer.state.toLowerCase().includes(cityState) ||
                    offer.venue.toLowerCase().includes(cityState);
                }

            },
            error: function (data) {
                $('.condition-message').html(data);
            }
        });
    }

    // Set interval to refresh data every 60 seconds
    setInterval(function() {
        updateCardContainer();
    }, 60000);

    // csrf token for ajax
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });

    var selectedID;

    $(document).on('click', '.dismissAnchor', function() {
        selectedID = $(this).attr('id');
    });

    $('#dismiss').click(function() {
        if (selectedID) {
            $.ajax({
                type: 'POST',
                dataType: 'html',
                url: "/dismissoffer",
                data: { offerID : selectedID },
                success: function(data) {
                    $('#dismissModal').modal('hide');
                    $('.condition-message').html(data);
                    
                    updateCardContainer();

                },
                error: function (data) {
                    $('.condition-message').html(data);
                }
            })
        }
    });

});