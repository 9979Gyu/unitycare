$(document).ready(function(){

    // Initailize map
    var map = L.map('map').setView([0, 0], 2);

    L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png', {
        maxZoom: 19,
        attribution: '&copy; <a href="http://www.openstreetmap.org/copyright">OpenStreetMap</a>'
    }).addTo(map);

    var marker = L.marker([51.5, -0.09]).addTo(map);

    var address = $("#address").text();
    console.log('address: ', address);
    updateMarkerFromAddress(address);

    function updateMarkerFromAddress(address) {
        if (address.trim() !== "") {
            fetch(`https://nominatim.openstreetmap.org/search?format=json&q=${encodeURIComponent(address)}`)
            .then(response => response.json())
            .then(data => {
                if (data.length > 0) {
                    var newLocation = [parseFloat(data[0].lat), parseFloat(data[0].lon)];
                    updateMarker(newLocation);
                } 
                else {
                    console.error('No results found for the given address.');
                }
            })
            .catch(error => console.error('Error fetching data:', error));
        }
    }

    function updateMarker(location) {
        if (!marker) {
            marker = L.marker(location).addTo(map);
        } else {
            marker.setLatLng(location);
        }

        map.setView(location, 14); // Set the map view to the marker location
    }

});