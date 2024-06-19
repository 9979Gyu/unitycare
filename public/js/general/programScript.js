function getPrograms(component, selectedUser){
    if(selectedUser){
        $.ajax({
            method: 'GET',
            dataType: 'json',
            data: {selectedUser : selectedUser},
            url: "/getProgramsByUserID",
            success: function(data) {
    
                $(component).empty();
                $(component).append(
                    '<option value="all" selected>Semua Jenis</option>' +
                    '<option value="vol">Sukarelawan</option>' +
                    '<option value="skill">Pembangunan Kemahiran</option>'
                );
    
                data.forEach(function(item){
                    $(component).append('<option value="' + item.program_id + '">' + item.name + '</option>');
                });
    
            },
            error: function (data) {
                $('.condition-message').html(data);
            }
        });
    }
}