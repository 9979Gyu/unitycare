$(document).ready(function() {
    
    $('.select2').select2({
        placeholder: 'Bandar atau negeri',
        allowClear: true,
    });

    updateCardContainer();
    getCityState();

    $("#searchBtn").click(function(){
        updateCardContainer();
    });

    // Function to add , for every 3 digit on number (10,000)
    function numberWithCommas(x) {
        return x.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",");
    }

    // Funciton to display list of city and states
    function getCityState(){
        $.ajax({
            type: 'GET',
            url: "/getCityState",
            success: function(data) {
                
                $("#citystate").empty();
                // Loop through the unique state and city names array
                for(var item in data){
                    // Append each item as an option
                    $("#citystate").append('<option>' + data[item] + '</option>');
                }
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

                // Get the value of the input box and selected option
                var keyword = $('#keyword').val().toLowerCase();
                var citystate = $('#citystate option:selected').val().toLowerCase();

                // Filter offers based on keyword and citystate
                var filteredOffers = data.allOffers.filter(function(offer) {
                    var matchKeyword = true;
                    var matchCityState = true;
        
                    if (keyword) {
                        matchKeyword = offer.jobname.toLowerCase().includes(keyword) ||
                                       offer.jobposition.toLowerCase().includes(keyword) ||
                                       offer.username.toLowerCase().includes(keyword);
                    }
        
                    if (citystate) {
                        matchCityState = offer.city.toLowerCase() === citystate ||
                                         offer.state.toLowerCase() === citystate;
                    }
        
                    return matchKeyword && matchCityState;
                });

                $.each(filteredOffers, function(index, offer){

                    var button;

                    minsal = numberWithCommas(offer.min_salary);
                    maxsal = numberWithCommas(offer.max_salary);

                    button = '<a class="viewAnchor btn btn-info m-2" href="/joinoffer/' + offer.offer_id + '">' +
                        '<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-search" viewBox="0 0 16 16">' +
                        '<path d="M11.742 10.344a6.5 6.5 0 1 0-1.397 1.398h-.001q.044.06.098.115l3.85 3.85a1 1 0 0 0 1.415-1.414l-3.85-3.85a1 1 0 0 0-.115-.1zM12 6.5a5.5 5.5 0 1 1-11 0 5.5 5.5 0 0 1 11 0"/>' +
                        '</svg> Lihat </a>';

                    // B40 / OKU
                    if($("#roleID").val() == 5 && offer.approval_status == 2){
                        // Apply
                        button += '<a class="applyAnchor btn btn-success" href="/joinoffer/' + offer.offer_id + '">' +
                            '<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="currentColor" class="bi bi-person-plus-fill" viewBox="0 0 16 16">' +
                            '<path d="M1 14s-1 0-1-1 1-4 6-4 6 3 6 4-1 1-1 1zm5-6a3 3 0 1 0 0-6 3 3 0 0 0 0 6"/>' +
                            '<path fill-rule="evenodd" d="M13.5 5a.5.5 0 0 1 .5.5V7h1.5a.5.5 0 0 1 0 1H14v1.5a.5.5 0 0 1-1 0V8h-1.5a.5.5 0 0 1 0-1H13V5.5a.5.5 0 0 1 .5-.5"/>' +
                            '</svg> Mohon</a>';

                        $(".card-container").append(
                            '<p><div class="card" id="' + offer.offer_id + '">' +
                                '<div class="card-body d-flex justify-content-between">' +
                                    '<div><h4 class="card-title">' + offer.jobposition + '</h4>' +
                                    '<div><p class="card-text">' + offer.username + '<br>' + offer.city + '</p>' +
                                        '<p class="card-text badge badge-primary"> RM ' + minsal + ' - RM ' + maxsal + ' sebulan</p>' +
                                        ' <p class="card-text badge badge-primary">' + offer.typename + '</p>' +
                                        ' <p class="card-text badge badge-primary">' + offer.shiftname + '</p>' +
                                        '<p class="card-text">' + offer.description + '</p>' +
                                        '<p class="card-text text-secondary"> kemaskini ' + offer.updateDate + '</p>' +
                                    '</div></div>' +
                                    '<div>' + button + '</div>' +
                                '</div>' +
                            '</div></p>'
                        );

                    }
                    else if(offer.approval_status <= 1 && offer.user_id == $("#uid").val()){

                        if(offer.reason == ""){
                            $(".card-container").append(
                                '<p><div class="card" id="' + offer.offer_id + '">' +
                                    '<div class="card-body d-flex justify-content-between">' +
                                        '<div><h4 class="card-title">' + offer.jobposition + '</h4>' +
                                        '<div><p class="card-text">' + offer.username + '<br>' + offer.city + '</p>' +
                                            '<p class="card-text badge badge-primary"> RM ' + minsal + ' - RM ' + maxsal + ' sebulan</p>' +
                                            ' <p class="card-text badge badge-primary">' + offer.typename + '</p>' +
                                            ' <p class="card-text badge badge-primary">' + offer.shiftname + '</p>' +
                                            '<p class="card-text">' + offer.description + '</p>' +
                                            '<p class="card-text text-secondary"> kemaskini ' + offer.updateDate + '</p>' +
                                        '</div></div>' +
                                        '<div>' +
                                            '<p><a href="/editoffer/' + offer.offer_id + '" class="btn btn-warning">Kemaskini</a></p>' +
                                            '<p><a class="deleteAnchor btn btn-danger" href="#" id="' + offer.offer_id + '" data-bs-toggle="modal" data-bs-target="#deleteModal">Padam</a></p>' +
                                        '</div>' +
                                    '</div>' +
                                '</div></p>'
                            );
                        }
                        else{
                            $(".card-container").append(
                                '<p><div class="card" id="' + offer.offer_id + '">' +
                                    '<div class="card-body d-flex justify-content-between">' +
                                        '<div><h4 class="card-title">' + offer.jobposition + '</h4>' +
                                        '<div><p class="card-text">' + offer.username + '<br>' + offer.city + '</p>' +
                                            '<p class="card-text badge badge-primary"> RM ' + minsal + ' - RM ' + maxsal + ' sebulan</p>' +
                                            ' <p class="card-text badge badge-primary">' + offer.typename + '</p>' +
                                            ' <p class="card-text badge badge-primary">' + offer.shiftname + '</p>' +
                                            '<p class="card-text">' + offer.description + '</p>' +
                                            '<p class="card-text"> <b>Declined: ' + offer.reason + '</b></p>' +
                                            '<p class="card-text text-secondary"> kemaskini ' + offer.updateDate + '</p>' +
                                        '</div></div>' +
                                        '<div>' +
                                            '<p><a href="/editoffer/' + offer.offer_id + '" class="btn btn-warning">Kemaskini</a></p>' +
                                            '<p><a class="deleteAnchor btn btn-danger" href="#" id="' + offer.offer_id + '" data-bs-toggle="modal" data-bs-target="#deleteModal">Padam</a></p>' +
                                        '</div>' +
                                    '</div>' +
                                '</div></p>'
                            );
                        }
                    }
                    
                });
            },
            error: function (data) {
                $('.condition-message').html(data);
            }
        });
    }
    
    var requestTable;

    fetch_data();
    function fetch_data() {
        requestTable = $('#requestTable').DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                url: "/getoffers",
                data: {
                    rid: $("#roleID").val(),
                },
                type: 'GET',

            },
            'columnDefs': [{
                "targets": [0],
                "className": "text-center",
                "width": "2%"
            }, {
                "targets": [1, 2, 3, 4, 5],
                "className": "text-center",
            },], 
            columns: [{
                "data": null,
                searchable: false,
                "sortable": true,
                render: function(data, type, row, meta) {
                    return meta.row + meta.settings._iDisplayStart + 1;
                }
            }, {
                data: "job_id",
                name: 'name',
                orderable: true,
                searchable: true,
            },
            {
                data: "offer_id",
                name: 'position',
                orderable: true,
                searchable: true,
            }, {
                data: "description",
                name: 'description',
                orderable: true,
                searchable: true
            }, {
                data: "salary_range",
                name: 'salary',
                orderable: true,
                searchable: true
            }, {
                data: 'action',
                name: 'action',
                orderable: false,
                searchable: false
            }, ]
            
        });
    }

    // csrf token for ajax
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });

    var selectedID;
    $(document).on('click', '.deleteAnchor', function() {
        selectedID = $(this).attr('id');
    });

    $('#delete').click(function() {
        if (selectedID) {
            $.ajax({
                type: 'POST',
                dataType: 'html',
                url: "/deleteoffer",
                data: { selectedID : selectedID },
                success: function(data) {
                    $('#deleteModal').modal('hide');
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