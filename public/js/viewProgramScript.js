$(document).ready(function() {

    updateCardContainer();

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
                    updateCardContainer();
                },
                error: function (data) {
                    $('.condition-message').html(data);
                }
            })
        }
    });

    $(document).on('click', '.dismissAnchor', function() {
        selectedID = $(this).attr('id');
    });

    $('#dismiss').click(function() {
        if (selectedID) {
            $.ajax({
                type: 'POST',
                dataType: 'html',
                url: "/dismissprogram",
                data: { selectedID : selectedID },
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

    // If checkbox state changed, reload the card-container
    $("#voluntaryCheckBox, #skillDevCheckBox").change(function(){
        updateCardContainer();
    });

    function updateCardContainer(){
        $.ajax({
            type: 'GET',
            url: "/getUpdatedPrograms",
            data: { selectedID : selectedID },
            success: function(data) {
                
                $(".card-container").empty();

                // Check the status of the checkboxes
                var volChecked = $('#voluntaryCheckBox').is(':checked');
                var skillDevChecked = $('#skillDevCheckBox').is(':checked');

                var enrolledPrograms = $.map(data.enrolled, function(el) { return el.pid; });

                $.each(data.allPrograms, function(index, program){

                    var button;
                    
                    // format date
                    const today = new Date();
                    const year = today.getFullYear();
                    const month = String(today.getMonth() + 1).padStart(2, '0');
                    const day = String(today.getDate()).padStart(2, '0');
                    const currentDate = `${year}-${month}-${day}`;

                    button = '<a class="viewAnchor btn btn-info me-2" href="/joinprogram/' + program.program_id + '">' +
                        '<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-search" viewBox="0 0 16 16">' +
                        '<path d="M11.742 10.344a6.5 6.5 0 1 0-1.397 1.398h-.001q.044.06.098.115l3.85 3.85a1 1 0 0 0 1.415-1.414l-3.85-3.85a1 1 0 0 0-.115-.1zM12 6.5a5.5 5.5 0 1 1-11 0 5.5 5.5 0 0 1 11 0"/>' +
                        '</svg> Lihat </a>';

                    // If contain same program id
                    if ($.inArray(program.program_id, enrolledPrograms) !== -1) {
                        button += '<a class="dismissAnchor btn btn-danger" href="#" id="' + program.program_id + '" data-bs-toggle="modal" data-bs-target="#dismissModal">' +
                        '<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="currentColor" class="bi bi-person-dash-fill" viewBox="0 0 16 16">' +
                        '<path fill-rule="evenodd" d="M11 7.5a.5.5 0 0 1 .5-.5h4a.5.5 0 0 1 0 1h-4a.5.5 0 0 1-.5-.5"/>' +
                        '<path d="M1 14s-1 0-1-1 1-4 6-4 6 3 6 4-1 1-1 1zm5-6a3 3 0 1 0 0-6 3 3 0 0 0 0 6"/>' +
                        '</svg> Keluar</a>';
                    }
                    else{
                        if(program.close_date >= currentDate){
                            button += '<a class="applyAnchor btn btn-success" href="/joinprogram/' + program.program_id + '">' +
                            '<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="currentColor" class="bi bi-person-plus-fill" viewBox="0 0 16 16">' +
                            '<path d="M1 14s-1 0-1-1 1-4 6-4 6 3 6 4-1 1-1 1zm5-6a3 3 0 1 0 0-6 3 3 0 0 0 0 6"/>' +
                            '<path fill-rule="evenodd" d="M13.5 5a.5.5 0 0 1 .5.5V7h1.5a.5.5 0 0 1 0 1H14v1.5a.5.5 0 0 1-1 0V8h-1.5a.5.5 0 0 1 0-1H13V5.5a.5.5 0 0 1 .5-.5"/>' +
                            '</svg> Mohon</a>';
                        }
                    }

                    if((volChecked && program.type_id == 1) || (skillDevChecked && program.type_id == 2) || (!volChecked && !skillDevChecked)){
                        if(program.approved_status == 2){
                            $(".card-container").append(
                                '<div class="card" id="' + program.program_id + '">' +
                                    '<div class="card-header bg-primary text-white">' + program.typename + '</div>' +
                                    '<div class="card-body d-flex justify-content-between">' +
                                        '<div><h5 class="card-title">' + program.name + '</h5>' +
                                            '<p class="card-text">' + program.venue + '</p>' +
                                            '<p class="card-text">' + program.description + '</p>' +
                                        '</div>' +
                                        '<div>' + button + '</div>' +
                                    '</div>' +
                                '</div>' + 
                                '<br>'
                            );
                            
                        }
                        else if(program.approved_status <= 1 && program.user_id == $("#uid").val()){
                            
                            if(program.reason == ""){
                                $(".card-container").append(
                                    '<div class="card" id="' + program.program_id + '">' +
                                        '<div class="card-header bg-primary text-white">' + program.typename + '</div>' +
                                        '<div class="card-body d-flex justify-content-between">' +
                                            '<div><h5 class="card-title">' + program.name + '</h5>' +
                                                '<p class="card-text">' + program.venue + '</p>' +
                                                '<p class="card-text">' + program.description + '</p>' +
                                            '</div>' +
                                            '<div>' +
                                                '<p><a href="/editprogram/' + program.program_id + '" class="btn btn-warning">Kemaskini</a></p>' +
                                                '<p><a class="deleteAnchor btn btn-danger" href="#" id="' + program.program_id + '" data-bs-toggle="modal" data-bs-target="#deleteModal">Padam</a></p>' +
                                            '</div>' +
                                        '</div>' +
                                    '</div><br>'
                                );
                            }
                            else{
                                $(".card-container").append(
                                    '<div class="card" id="' + program.program_id + '">' +
                                        '<div class="card-header bg-primary text-white">' + program.typename + '</div>' +
                                        '<div class="card-body d-flex justify-content-between">' +
                                            '<div><h5 class="card-title">' + program.name + '</h5>' +
                                                '<p class="card-text">' + program.venue + '</p>' +
                                                '<p class="card-text">' + program.description + '</p>' +
                                                '<p class="card-text"> <b>Declined: ' + program.reason + '</b></p>' +
                                            '</div>' +
                                            '<div>' +
                                                '<p><a href="/editprogram/' + program.program_id + '" class="btn btn-warning">Kemaskini</a></p>' +
                                                '<p><a class="deleteAnchor btn btn-danger" href="#" id="' + program.program_id + '" data-bs-toggle="modal" data-bs-target="#deleteModal">Padam</a></p>' +
                                            '</div>' +
                                        '</div>' +
                                    '</div><br>'
                                );
                            }
                            
                        }
                    }
                });
            },
            error: function (data) {
                $('.condition-message').html(data);
            }
        });
    }
});