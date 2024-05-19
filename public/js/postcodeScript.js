$(document).ready(function(){
    $("#postalCode").on('change', function(){
        var postcode = $(this).val();
        if(postcode){
            $.ajax({
                url: '/search',
                type: 'GET',
                data: {postcode: postcode},
                success: function(data){
                    $('#state').empty();
                    $("#city").empty();
                    data.forEach(function(item){
                        $("#state").append('<option>' + item.state + '</option>');
                        $("#city").append('<option>' + item.city + '</option>');
                    });
                }
            });
        }
        else{
            $('#state').empty();
            $("#city").empty();
            $("#state").append('<option selected>Pilih Negeri</option>');
            $("#city").append('<option selected>Pilih Bandar</option>');
        }
    });
});