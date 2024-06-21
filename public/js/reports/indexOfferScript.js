$(document).ready(function() {

    // Disabled the Tolak button in modal
    $("#decline").prop("disabled", true);
    // Hide the explaination input field
    $("#more").hide();

    // Initialize select2
    $('#organization').select2({
        placeholder: 'Pilih Organisasi',
    });

    $('#jobname').select2({
        placeholder: 'Pilih Pekerjaan',
    });

    $('#position').select2({
        placeholder: 'Pilih Jawatan',
    });

    $('#citystate').select2({
        placeholder: 'Pilih Bandar atau Negeri',
        allowClear: true,
    });
    
    // Define and declare variables
    var requestTable;
    var selectedState = 3;
    var selectedUser = "all";
    var selectedJob = "all";
    var selectedPosition = "all";
    var status = 1;
    var roleID = $("#roleID").val();
    var startDate = "";
    var endDate = "";

    $("#organization").change(function(){
        // set value
        selectedUser = $("#organization option:selected").val();

        // get job list for dropdown
        getJob(selectedUser, "#jobname");

        fetch_data(selectedUser, selectedPosition, selectedState, startDate, endDate);

    });
    
    $("#organization").prop('selectedIndex', 0).trigger('change');

    $("#jobname").on('change', function(){
        // set value
        selectedJob = $("#jobname option:selected").val();
        // get job list for dropdown
        getPosition(selectedJob, selectedUser, "#position");
    });

    $("#position").on('change', function(){
        // set value
        selectedPosition = $("#position option:selected").val();

        // get job list for dropdown
        fetch_data(selectedUser, selectedPosition, selectedState, startDate, endDate);
    });

    $("#startDate1, #endDate1").change(function(){

        startDate = $("#startDate1").val();
        endDate = $("#endDate1").val();

        if(endDate == ""){
            endDate = startDate;
        }
        // Fetch data
        fetch_data(selectedUser, selectedPosition, selectedState, startDate, endDate);
    });
    
    // Function to handle radio button value
    $('#allRadio, #pendingRadio, #approveRadio, #declineRadio, #deleteRadio').change(function() {

        if ($('#allRadio').is(':checked')) {
            selectedState = 3;
        }
        else if ($('#pendingRadio').is(':checked')) {
            selectedState = 1;
        } 
        else if ($('#approveRadio').is(':checked')) {
            selectedState = 2;
        } 
        else if ($('#declineRadio').is(':checked')) {
            selectedState = 0;
        }
        else if ($('#deleteRadio').is(':checked')) {
            selectedState = 4;
        }

        // Fetch data based on the selected position
        fetch_data(selectedUser, selectedPosition, selectedState, startDate, endDate);

    });
    
    function fetch_data(selectedUser, selectedPosition, selectedState, startDate, endDate) {

        updateOfferBarChart(selectedUser, selectedPosition, selectedState, startDate, endDate);

        // Make AJAX request to fetch data based on the selected position
        if ($.fn.DataTable.isDataTable('#requestTable')) {
            // If DataTable already initialized, destroy it
            $('#requestTable').DataTable().destroy();
        }

        requestTable = $('#requestTable').DataTable({
            language: {
                "sEmptyTable":     "Tiada data tersedia dalam jadual",
                "sInfo":           "Memaparkan _START_ hingga _END_ daripada _TOTAL_ rekod",
                "sInfoEmpty":      "Memaparkan 0 hingga 0 daripada 0 rekod",
                "sInfoFiltered":   "(ditapis daripada jumlah _MAX_ rekod)",
                "sInfoPostFix":    "",
                "sInfoThousands":  ",",
                "sLengthMenu":     "Tunjukkan _MENU_ rekod",
                "sLoadingRecords": "Sedang memuatkan...",
                "sProcessing":     "Sedang memproses...",
                "sSearch":         "Cari:",
                "sZeroRecords":    "Tiada padanan rekod yang dijumpai",
                "oPaginate": {
                    "sFirst":    "<<",
                    "sLast":     ">>",
                    "sNext":     ">",
                    "sPrevious": "<"
                },
                "oAria": {
                    "sSortAscending":  ": diaktifkan kepada susunan lajur menaik",
                    "sSortDescending": ": diaktifkan kepada susunan lajur menurun"
                }
            },
            processing: true,
            serverSide: true,
            ajax: {
                url: "/getoffersbyposition",
                data: {
                    selectedUser : selectedUser, 
                    selectedPosition : selectedPosition, 
                    selectedState : selectedState, 
                    status : status,
                    startDate: startDate,
                    endDate: endDate,
                },
                type: 'GET',

            },
            'columnDefs': [{
                "targets": [0],
                "className": "text-center",
                "width": "2%"
            }, {
                "targets": [1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13],
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
                data: function(row){
                    return row.jobname + ' - ' + row.jobposition
                },
                name: 'name',
                orderable: true,
                searchable: true,
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
                data: 'address',
                name: 'location',
                orderable: true,
                searchable: true
            }, {
                data: 'start',
                name: 'startdatetime',
                orderable: true,
                searchable: true
            }, {
                data: 'end',
                name: 'enddatetime',
                orderable: true,
                searchable: true
            }, {
                data: function(row) {
                    return 'RM ' + numberWithCommas(row.min_salary) + ' - RM ' + numberWithCommas(row.max_salary);
                },
                name: 'salary',
                orderable: true,
                searchable: true
            }, {
                data: "people",
                name: 'quantity',
                orderable: true,
                searchable: true
            }, {
                data: "description",
                name: 'description',
                orderable: true,
                searchable: true
            }, {
                data: function(row) {
                    return row.username + 
                    '<br>' + row.useremail + 
                    '<br>+60' + row.usercontact;
                },
                name: 'contact',
                orderable: true,
                searchable: true
            }, {
                data: function(row) {
                    if(row.approval_status == 0){
                        return '<span class="text-danger"><b>' + row.approval + '</b></span>';
                    }
                    else if(row.approval_status == 2){
                        return '<span class="text-success"><b>' + row.approval + '</b></span>';
                    }
                    else{
                        return '<span><b>' + row.approval + '</b></span>';
                    }
                },
                name: 'approval',
                orderable: true,
                searchable: true
            }, {
                data: function(row) {
                    if(row.processedname != null){
                        return row.processedname + 
                        '<br>' + row.processedemail +
                        '<br> Pada: ' + row.approved_at;
                    }
                    else{
                        return " ";
                    }
                },
                name: 'processed',
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

    // Function to handle B40 / OKU case
    if(roleID == 5){
        getCityState();
        updateCardContainer();
    }

    $("#searchBtn").click(function(){
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

                var enrolled = $.map(data.enrolledOffers, function(el) { 
                    return { 
                        oid: el.oid, 
                        approval_status: el.approval_status, 
                        reason: el.reason, 
                        description: el.description, 
                        is_selected: el.is_selected,
                        status: el.status, 
                    }; 
                });

                $.each(data.allOffers, function(index, offer){

                    keyword = keyword ? keyword.toLowerCase() : null;
                    citystate = citystate ? citystate.toLowerCase() : null;

                    if(
                        (!keyword || matchesKeyword(offer, keyword)) && 
                        (!citystate || matchesCityState(offer, citystate) && !offer.is_full)
                    ){
                    
                        // User is B40/OKU and job offer is approved by staff
                        if(roleID == 5 && offer.approval_status == 2){

                            var button;
                            var approvalText = ''; // store the text of approval
                            var approvalColor = ''; // store the color of approval text
                            var reasonAdd = '';

                            minsal = numberWithCommas(offer.min_salary);
                            maxsal = numberWithCommas(offer.max_salary);
    
                            button = '<a class="viewAnchor btn btn-info m-2" href="/joinoffer/' + offer.offer_id + '">' +
                                '<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-search" viewBox="0 0 16 16">' +
                                '<path d="M11.742 10.344a6.5 6.5 0 1 0-1.397 1.398h-.001q.044.06.098.115l3.85 3.85a1 1 0 0 0 1.415-1.414l-3.85-3.85a1 1 0 0 0-.115-.1zM12 6.5a5.5 5.5 0 1 1-11 0 5.5 5.5 0 0 1 11 0"/>' +
                                '</svg> Lihat </a>';

                            // check if user enroll the offer already
                            var enrolledOffer = enrolled.find(e => e.oid === offer.offer_id);

                            if(enrolledOffer){

                                reasonAdd = "Sebab mohon: " + enrolledOffer.description;

                                if(enrolledOffer.approval_status == 1){
                                    button += '<a class="dismissAnchor btn btn-danger" href="#" id="' + offer.offer_id + '" data-bs-toggle="modal" data-bs-target="#dismissModal">' +
                                        '<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="currentColor" class="bi bi-person-dash-fill" viewBox="0 0 16 16">' +
                                        '<path fill-rule="evenodd" d="M11 7.5a.5.5 0 0 1 .5-.5h4a.5.5 0 0 1 0 1h-4a.5.5 0 0 1-.5-.5"/>' +
                                        '<path d="M1 14s-1 0-1-1 1-4 6-4 6 3 6 4-1 1-1 1zm5-6a3 3 0 1 0 0-6 3 3 0 0 0 0 6"/>' +
                                        '</svg> Tarik Diri</a>';

                                }
                                else if(enrolledOffer.approval_status == 2){
                                    if(enrolledOffer.is_selected == 1){
                                        approvalText = "Permohonan Diterima. Sila Membuat Keputusan di Halaman Permohonan";
                                    }
                                    approvalColor = "text-success";
                                }
                                else {
                                    approvalText = "Permohonan Ditolak: " + enrolledOffer.reason;
                                    approvalColor = "text-danger";
                                }

                            }
                            // Did not enroll
                            else{
                                button += '<a class="applyAnchor btn btn-success" href="/joinoffer/' + offer.offer_id + '">' +
                                    '<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="currentColor" class="bi bi-person-plus-fill" viewBox="0 0 16 16">' +
                                    '<path d="M1 14s-1 0-1-1 1-4 6-4 6 3 6 4-1 1-1 1zm5-6a3 3 0 1 0 0-6 3 3 0 0 0 0 6"/>' +
                                    '<path fill-rule="evenodd" d="M13.5 5a.5.5 0 0 1 .5.5V7h1.5a.5.5 0 0 1 0 1H14v1.5a.5.5 0 0 1-1 0V8h-1.5a.5.5 0 0 1 0-1H13V5.5a.5.5 0 0 1 .5-.5"/>' +
                                    '</svg> Mohon</a>';
                            }

                            $(".card-container").append(
                                '<div class="card" id="' + offer.offer_id + '">' +
                                    '<div class="card-body d-flex justify-content-between">' +
                                        '<div><p class="card-text ' +  approvalColor + '"><b>' + approvalText + '</b></p>' +
                                            '<h4 class="card-title">' + offer.jobposition + '</h4>' +
                                            '<p class="card-text">' + offer.username + '<br>' + offer.venue + ', ' + offer.postal_code + ', ' + offer.city + ', ' + offer.state + '</p>' +
                                            '<p class="card-text badge badge-primary"> RM ' + minsal + ' - RM ' + maxsal + ' sebulan</p>' +
                                            ' <p class="card-text badge badge-primary">' + offer.typename + '</p>' +
                                            ' <p class="card-text badge badge-primary">' + offer.shiftname + '</p>' +
                                            '<p class="card-text"><b>' + reasonAdd + '</b></p>' +
                                            '<p class="card-text text-secondary"> kemaskini ' + parseDate(offer.updateDate) + '</p>' +
                                        '</div>' +
                                        '<div>' + button + '</div>' +
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
                    offer.username.toLowerCase().includes(keyword) ||
                    offer.start_date.includes(keyword) ||
                    offer.end_date.includes(keyword);
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
        fetch_data(selectedUser, selectedPosition, selectedState, startDate, endDate);
        updateCardContainer();
    }, 60000);
    
    $("#resetBtn").click(function(){
        $("#organization").trigger('change');
        $("#jobname").trigger('change');

        $('#startDate1').val('');
        $('#endDate1').val('').trigger('change');
    });

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
                data: { offerID : selectedID },
                success: function(response) {
                    $('#deleteModal').modal('hide');
                    $('.condition-message').html(response);

                    requestTable.ajax.reload();
                },
                error: function(xhr, status, error) {
                    // Handle error
                    console.error(xhr.responseText);
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
            data: {
                approval_status : 2,
                offerID : selectedID
            },
            url: "/approval",
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
            url: "/approval",
            data: {
                approval_status : 0,
                reason: declineReason,
                offerID: selectedID
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