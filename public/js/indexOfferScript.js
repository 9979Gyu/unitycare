$(document).ready(function() {

    // Disabled the Tolak button in modal
    $("#decline").prop("disabled", true);
    // Hide the explaination input field
    $("#more").hide();
    
    $('.select2').select2({
        placeholder: 'Bandar atau negeri',
        allowClear: true,
    });

    var requestTable;

    if($("#roleID").val() == 1 || $("#roleID").val() == 2){
        fetch_data();
    }
    else{
        updateCardContainer();
        getCityState();
    }
    
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

                var enrolled = $.map(data.enrolledOffers, function(el) { return el.oid; });

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
                        // If contain same program id
                        if ($.inArray(offer.offer_id, enrolled) !== -1) {
                            button += '<a class="dismissAnchor btn btn-danger" href="#" id="' + offer.offer_id + '" data-bs-toggle="modal" data-bs-target="#dismissModal">' +
                            '<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="currentColor" class="bi bi-person-dash-fill" viewBox="0 0 16 16">' +
                            '<path fill-rule="evenodd" d="M11 7.5a.5.5 0 0 1 .5-.5h4a.5.5 0 0 1 0 1h-4a.5.5 0 0 1-.5-.5"/>' +
                            '<path d="M1 14s-1 0-1-1 1-4 6-4 6 3 6 4-1 1-1 1zm5-6a3 3 0 1 0 0-6 3 3 0 0 0 0 6"/>' +
                            '</svg> Tarik Diri</a>';
                        }
                        else{
                            button += '<a class="applyAnchor btn btn-success" href="/joinoffer/' + offer.offer_id + '">' +
                            '<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="currentColor" class="bi bi-person-plus-fill" viewBox="0 0 16 16">' +
                            '<path d="M1 14s-1 0-1-1 1-4 6-4 6 3 6 4-1 1-1 1zm5-6a3 3 0 1 0 0-6 3 3 0 0 0 0 6"/>' +
                            '<path fill-rule="evenodd" d="M13.5 5a.5.5 0 0 1 .5.5V7h1.5a.5.5 0 0 1 0 1H14v1.5a.5.5 0 0 1-1 0V8h-1.5a.5.5 0 0 1 0-1H13V5.5a.5.5 0 0 1 .5-.5"/>' +
                            '</svg> Mohon</a>';
                        }

                        $(".card-container").append(
                            '<p><div class="card" id="' + offer.offer_id + '">' +
                                '<div class="card-body d-flex justify-content-between">' +
                                    '<div><h4 class="card-title">' + offer.jobposition + '</h4>' +
                                    '<div><p class="card-text">' + offer.username + '<br>' + offer.city + ', ' + offer.state + '</p>' +
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
                    else if(offer.user_id == $("#uid").val()){

                        if(offer.approval_status <= 1){
                            button += '<a href="/editoffer/' + offer.offer_id + '" class="btn btn-warning m-2">Kemaskini</a>' +
                            '<a class="deleteAnchor btn btn-danger m-2" href="#" id="' + offer.offer_id + '" data-bs-toggle="modal" data-bs-target="#deleteModal">Padam</a>';
                        }

                        if(offer.reason == ""){
                            $(".card-container").append(
                                '<p><div class="card" id="' + offer.offer_id + '">' +
                                    '<div class="card-body d-flex justify-content-between">' +
                                        '<div><h4 class="card-title">' + offer.jobposition + '</h4>' +
                                        '<div><p class="card-text">' + offer.username + '<br>' + offer.city + ', ' + offer.state + '</p>' +
                                            '<p class="card-text badge badge-primary"> RM ' + minsal + ' - RM ' + maxsal + ' sebulan</p>' +
                                            ' <p class="card-text badge badge-primary">' + offer.typename + '</p>' +
                                            ' <p class="card-text badge badge-primary">' + offer.shiftname + '</p>' +
                                            '<p class="card-text">' + offer.description + '</p>' +
                                            '<p class="card-text text-secondary"> kemaskini ' + offer.updateDate + '</p>' +
                                        '</div></div>' +
                                        '<div>' + button + 
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
                                        '<div><p class="card-text">' + offer.username + '<br>' + offer.city + ', ' + offer.state + '</p>' +
                                            '<p class="card-text badge badge-primary"> RM ' + minsal + ' - RM ' + maxsal + ' sebulan</p>' +
                                            ' <p class="card-text badge badge-primary">' + offer.typename + '</p>' +
                                            ' <p class="card-text badge badge-primary">' + offer.shiftname + '</p>' +
                                            '<p class="card-text">' + offer.description + '</p>' +
                                            '<p class="card-text"> <b>Declined: ' + offer.reason + '</b></p>' +
                                            '<p class="card-text text-secondary"> kemaskini ' + offer.updateDate + '</p>' +
                                        '</div></div>' +
                                        '<div>' + button + 
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
                "targets": [1, 2, 3, 4, 5, 6, 7, 8],
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
                data: "jobname",
                name: 'name',
                orderable: true,
                searchable: true,
            },
            {
                data: "jobposition",
                name: 'position',
                orderable: true,
                searchable: true,
            }, {
                data: function(row) {
                    return row.city + ', ' + row.state;
                },
                name: 'location',
                orderable: true,
                searchable: true
            }, {
                data: "typename",
                name: 'type',
                orderable: true,
                searchable: true,
            }, {
                data: "shiftname",
                name: 'shift',
                orderable: true,
                searchable: true,
            }, {
                data: function(row) {
                    return 'RM ' + numberWithCommas(row.min_salary) + ' - RM ' + numberWithCommas(row.max_salary);
                },
                name: 'salary',
                orderable: true,
                searchable: true
            },{
                data: "description",
                name: 'description',
                orderable: true,
                searchable: true
            }, {
                data: function(row) {
                    return 'Nama: ' + row.username.toUpperCase() + 
                    '<br>Emel: ' + row.useremail + 
                    '<br>Telefon: 0' + row.usercontact;
                },
                name: 'contact',
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

    // Function to approve job offer
    $(document).on('click', '.approveAnchor', function() {
        selectedID = $(this).attr('id');
    });

    $('#approve').click(function() {
        $.ajax({
            type: 'POST',
            dataType: 'html',
            data: {selectedID : selectedID},
            url: "/approveoffer",
            success: function(data) {
                $('#approveModal').modal('hide');
                $('.condition-message').html(data);

                requestTable.ajax.reload();
            },
            error: function (data) {
                $('.condition-message').html(data);
            }
        });
    });

    $(document).on('click', '.declineAnchor', function() {
        selectedID = $(this).attr('id');
    });

    var declineReason = "";
    
    $("#reason").change(function() {

        // Disabled the Tolak button in modal
        $("#decline").prop("disabled", true);
        // Hide the explaination input field
        $("#explain").val("");
        $("#more").hide();

        // If select "lain-lain"
        if($(this).val() == "others"){
            $("#more").show();
            declineReason = "";
        }
        else{
            if($(this).val() !== "0"){
                // Enable button
                $("#decline").prop("disabled", false); 
                
                declineReason = "";
                
                if($(this).val() == "missing")
                    declineReason = "Kekurangan maklumat"; 
                else if($(this).val() == "unclear")
                    declineReason = "Penerangan tidak jelas"; 
                
            }
        }
    });

    $("#explain").change(function(){
        // Check if the field has any value
        if ($(this).val().trim() !== "") {
            // Enable button
            $("#decline").prop("disabled", false); 
            declineReason += $(this).val();
        } 
        else {
            // Disable button
            $("#decline").prop("disabled", true); 
        }
    });

    $('#decline').click(function() {

        $.ajax({
            type: 'POST',
            dataType: 'html',
            url: "/declineoffer",
            data: {
                reason: declineReason,
                selectedID: selectedID
            },
            success: function(data) {
                $('#declineModal').modal('hide');
                $('.condition-message').html(data);

                requestTable.ajax.reload();
            },
            error: function (data) {
                $('.condition-message').html(data);
            }
        });
    });

});