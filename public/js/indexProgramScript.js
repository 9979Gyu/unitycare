$(document).ready(function() {

    // Disabled the Tolak button in modal
    $("#decline").prop("disabled", true);
    // Hide the explaination input field
    $("#more").hide();

    var requestTable;

    fetch_data();
    function fetch_data() {
        requestTable = $('#requestTable').DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                url: "/getprogram",
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
                data: "name",
                name: 'name',
                orderable: true,
                searchable: true,
            },
            {
                data: "venue",
                name: 'venue',
                orderable: true,
                searchable: true,
            }, {
                data: function(row) {
                    return row.start_date + ' ' + row.start_time;
                },
                name: 'start_datetime',
                orderable: true,
                searchable: true
            }, {
                data: function(row) {
                    return row.end_date + ' ' + row.end_time;
                },
                name: 'end_datetime',
                orderable: true,
                searchable: true
            }, {
                data: 'description',
                name: 'description',
                orderable: true,
                searchable: true,
            },{
                data: function(row) {
                    return 'Nama: ' + row.username.toUpperCase() + 
                    '<br>Emel: ' + row.useremail + 
                    '<br>Telefon: 0' + row.usercontact;
                },
                name: 'contact',
                orderable: true,
                searchable: true
            },{
                data: 'close_date',
                name: 'close_date',
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
                url: "/deleteprogram",
                data: { selectedID : selectedID },
                success: function(data) {
                    $('#deleteModal').modal('hide');
                    $('.condition-message').html(data);

                    requestTable.ajax.reload();
                },
                error: function (data) {
                    $('.condition-message').html(data);
                }
            })
        }
    });

    $(document).on('click', '.approveAnchor', function() {
        selectedID = $(this).attr('id');

        // Get details of selected program based on given id
        $.ajax({
            url: '/getProgramById',
            type: 'GET',
            data: { pid: selectedID },
            success: function(data){
                $('.modal-body').empty();
                $('.modal-body').append(
                    '<p>Adakah anda pasti untuk meluluskan <b>' + data.program.name + '</b> ? </p>' + 
                    '<p>Tempat: ' + data.program.venue + 
                    '<br>Bermula: ' + data.program.start_date + ' ' + data.program.start_time +
                    '<br>Tamat: ' + data.program.end_date + ' ' + data.program.end_time +
                    '<br>Tarikh Tutup Pendaftaran: ' + data.program.close_date +
                    '<br>Pengurus: ' + data.program.username +
                    '</p>'
                );
                data.forEach(function(item){
                    // volunteer
                    if(item.participants.user_type_id == 2){
                        $('.modal-body').append(
                            '<p>Bilangan Sukarelawan: ' + data.participants.qty_limit + '</p>' 
                        );
                    }
                    // poor people
                    else if(item.participants.user_type_id == 3){
                        $('.modal-body').append(
                            '<p>Bilangan Peserta: ' + data.participants.qty_limit + '</p>' 
                        );
                    }
                });
            }
        });
    });

    $('#approve').click(function() {
        
        $.ajax({
            type: 'POST',
            dataType: 'html',
            url: "/approveprogram/" + selectedID,
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
            url: "/declineprogram",
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