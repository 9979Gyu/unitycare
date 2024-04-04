function displayState(){
    var state = ["Kelantan", "Melaka", 'Negeri Sembilan'];
    
    var select = document.getElementById('state');
    select.innerHTML = "";

    for(var i = 0; i<state.length; i++){
        var option = document.createElement('option');
        option.value = state[i].toLowerCase();
        option.text = state[i];
        select.appendChild(option);
    }

}

function displayCity(state){
    var city = [
        [
            "Kota Bharu", 
            "Pasir Mas", 
            "Tanah Merah", 
            "Gua Musang", 
            "Machang", 
            "Kuala Krai", 
            "Jeli", 
            "Tumpat",
            "Ketereh"
        ],
        [
            "Alor Gajah",
            "Ayer Keroh",
            "Ayer Molek",
            "Batu Berendam",
            "Bemban",
            "Bukit Baru",
            "Bukit Rambai",
            "Jasin",
            "Klebang Besar",
            "Kuala Sungai Baru",
            "Masjid Tanah",
            "Melaka",
            "Pulau Sebang",
            "Sungai Udang"
        ],
        [
            "Seremban",
            "Port Dickson",
            "Nilai",
            "Kuala Pilah",
            "Bahau"
        ],
    ];

    var select = document.getElementById('city');
    select.innerHTML = "";

    var control = -1;

    if(state == "kelantan"){
        control = 0;
    }
    else if(state == "melaka"){
        control = 1;
    }
    else if(state == "negeri sembilan"){
        control = 2;
    }

    if(control > -1){
        for(var i = 0; i<city[control].length; i++){
            var option = document.createElement('option');
            option.value = state[i].toLowerCase();
            option.text = state[i];
            select.appendChild(option);
        }
        
    }
    
}