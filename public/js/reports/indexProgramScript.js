$(document).ready(function(){

    // Disabled the Tolak button in modal
    $("#decline").prop("disabled", true);
    // Hide the explaination input field
    $("#more").hide();

    // Initialize select2
    $('#organization').select2({
        placeholder: 'Pilih Pengurus',
    });

    $('#type').select2({
        placeholder: 'Pilih Program',
    });

    // Declare variables
    var requestTable;
    var selectedState = 3;
    var selectedUser = "all";
    var selectedType = "all"
    var status = 1;
    var startDate = "";
    var endDate = "";

    $("#organization").on('change', function(){
        selectedUser = $("#organization option:selected").val();
        getPrograms("#type", selectedUser);
        // Fetch data based on the selected position
        fetch_data(selectedUser, selectedState, selectedType, startDate, endDate);
    });

    $("#type").change(function(){
        selectedType = $("#type option:selected").val();

        // Fetch data based on the selected position
        fetch_data(selectedUser, selectedState, selectedType, startDate, endDate);
    });

    $("#startDate1, #endDate1").change(function(){
        startDate = $("#startDate1").val();
        endDate = $("#endDate1").val();
        if(endDate == ""){
            endDate = startDate;
        }

        // Fetch data based on the selected position
        fetch_data(selectedUser, selectedState, selectedType, startDate, endDate);
    });

    $('#organization').prop('selectedIndex', 0).trigger('change');
    $('#type').prop('selectedIndex', 0);

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
        fetch_data(selectedUser, selectedState, selectedType, startDate, endDate);
    });

    function fetch_data(selectedUser, selectedState, selectedType, startDate, endDate){

        // Make AJAX request to fetch data based on the selected position
        if ($.fn.DataTable.isDataTable('#requestTable')) {
            // If DataTable already initialized, destroy it
            $('#requestTable').DataTable().destroy();
        }

        if(selectedUser != null && selectedState != null && selectedType != null && status != null){
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
                    url: "/getProgramsDatatable",
                    data: {
                        selectedUser: selectedUser,
                        selectedState: selectedState,
                        selectedType: selectedType,
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
                    data: "name",
                    name: 'name',
                    orderable: true,
                    searchable: true,
                }, {
                    data: 'typename',
                    name: 'typename',
                    orderable: true,
                    searchable: true,
                }, {
                    data: function(row) {
                        return 'Yuran Pendaftaran: ' + row.fee + 
                        ' MYR<br><br>' + row.description
                    },
                    name: 'description',
                    orderable: true,
                    searchable: true,
                }, {
                    data: 'address',
                    name: 'address',
                    orderable: true,
                    searchable: true,
                }, {
                    data: 'start',
                    name: 'start_datetime',
                    orderable: true,
                    searchable: true
                }, {
                    data: 'end',
                    name: 'end_datetime',
                    orderable: true,
                    searchable: true
                }, {
                    data: 'vol',
                    name: 'volunteer',
                    orderable: true,
                    searchable: true
                }, {
                    data: 'poor',
                    name: 'poor',
                    orderable: true,
                    searchable: true
                }, {
                    data: 'close_date',
                    name: 'close_date',
                    orderable: true,
                    searchable: true
                }, {
                    data: function(row) {
                        return row.username + 
                        '<br>' + row.useremail + 
                        '<br>+60' + row.usercontact;
                    },
                    name: 'creator',
                    orderable: true,
                    searchable: true
                }, {
                    data: function(row) {
                        if(row.approved_status == 0){
                            return '<span class="text-danger"><b>' + row.approval + '</b></span>';
                        }
                        else if(row.approved_status == 2){
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
        else{
            alert('Data tidak lengkap');
        }
    }

    // Function to remove program
    var selectedID;
    $(document).on('click', '.deleteAnchor', function() {
        selectedID = $(this).attr('id');
    });

    $('#delete').click(function() {
        if (selectedID) {
            $.ajax({
                type: 'POST',
                dataType: 'html',
                url: "/deleteprogram",
                data: { selectedID : selectedID },
                success: function(response) {
                    $('#deleteModal').modal('hide');
                    $('.condition-message').text(response.message);

                    requestTable.ajax.reload();
                },
                error: function (data) {
                    $('.condition-message').html(data);
                }
            })
        }
    });

    // Function to approve program
    $(document).on('click', '.boostAnchor', function() {
        selectedID = $(this).attr('id');
    });

    $('#boost').click(function() {
        
        $.ajax({
            type: 'POST',
            dataType: 'html',
            url: "/boostprogram",
            data: { selectedID : selectedID },
            success: function(data) {
                $('#boostModal').modal('hide');
                $('.condition-message').html(data);

                requestTable.ajax.reload();
            },
            error: function (data) {
                $('.condition-message').html(data);
            }
        });
    });

    // Function to approve program
    $(document).on('click', '.approveAnchor', function() {
        selectedID = $(this).attr('id');
    });

    $('#approve').click(function() {
        
        $.ajax({
            type: 'POST',
            dataType: 'html',
            url: "/updateapproval",
            data: { 
                selectedID : selectedID,
                approval : 2,
            },
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

    // Function to decline program
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
            url: "/updateapproval",
            data: {
                reason: declineReason,
                selectedID: selectedID,
                approval: 0,
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

    // csrf token for ajax
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });

});