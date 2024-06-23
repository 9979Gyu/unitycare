function getJob(selectedUser, component){
    $.ajax({
        type: 'GET',
        url: "/getJobsByUser",
        data: { selectedUser: selectedUser },
        success: function(data) {
            $(component).empty();
            $(component).append('<option value="all" selected>Semua Pekerjaan</option>');
            data.forEach(function(item){
                $(component).append('<option value="' + item.name + '">' + item.name + '</option>');

            });
        },
        error: function (data) {
            $('.condition-message').html(data);
        }
    });
}

// Funciton to display list of city and states
function getPosition(selectedJob, selectedUser, component){
    
    $.ajax({
        type: 'GET',
        url: "/getPositions",
        data:  { 
            jobName : selectedJob,
            userID: selectedUser
        },
        success: function(data) {
            $(component).empty();
            
            $(component).append('<option value="all" selected>Semua Jawatan</option>');

            data.forEach(function(item){
                $(component).append('<option value="' + item.job_id + '">' + item.position + '</option>');
            });

            $(component).trigger('change');

        },
        error: function (data) {
            $('.condition-message').html(data);
        }
    });
}

// Function to display number of offer for each position
function updateOfferBarChart(selectedUser, selectedPosition, selectedState, startDate, endDate) {
    $.ajax({
        url: '/offer-bar-chart', // URL to fetch data from
        type: 'GET',
        dataType: 'json',
        data: {
            selectedUser :  selectedUser,
            selectedPosition: selectedPosition, 
            selectedState: selectedState,
            startDate: startDate,
            endDate: endDate,
        },
        success: function(response) {
            var labels = response.labels;
            var data = response.data;

            // Update Chart.js instance or create new one
            if (window.myChart1) {
                // Update existing chart
                window.myChart1.data.labels = labels;
                window.myChart1.data.datasets[0].data = data;
                window.myChart1.update();
            } 
            else {
                // Create new chart if not exist
                var ctx1 = document.getElementById('barChart').getContext('2d');
                window.myChart1 = new Chart(ctx1, {
                    type: 'bar',
                    data: {
                        labels: labels,
                        datasets: [{
                            label: 'Bilangan',
                            data: data,
                            backgroundColor: [
                                'rgba(75, 192, 192, 1)',
                                'rgba(255, 99, 132, 0.7)',
                                'rgba(54, 162, 235, 0.7)',
                                'rgba(255, 206, 86, 0.7)',
                                'rgba(153, 102, 255, 0.7)',
                            ],
                            borderColor: [
                                'rgba(75, 192, 192, 1)',
                                'rgba(255, 99, 132, 1)',
                                'rgba(54, 162, 235, 1)',
                                'rgba(255, 206, 86, 1)',
                                'rgba(153, 102, 255, 1)',
                            ],
                            borderWidth: 1
                        }]
                    },
                    options: {
                        plugins: {
                            title: {
                                display: true,
                                text: 'Bilangan Tawaran berdasarkan Jawatan',
                                font: {
                                    size: 18,
                                }
                            },
                            legend: {
                                display: false,
                            }
                        },
                        scales: {
                            y: {
                                beginAtZero: true
                            }
                        }
                    }
                });
            }
            
        },
        error: function(xhr, status, error) {
            console.error('Error fetching data:', error);
        }
    });
}

function updateParticipantBarChart(selectedUser, selectedPosition, selectedState, startDate, endDate) {

    $.ajax({
        url: '/app-bar-chart', // URL to fetch data from
        type: 'GET',
        dataType: 'json',
        data: {
            selectedUser :  selectedUser,
            selectedPosition: selectedPosition, 
            selectedState: selectedState,
            startDate: startDate,
            endDate: endDate
        },
        success: function(response) {
            var labels = response.labels;
            var data = response.data;

            // Update Chart.js instance or create new one
            if (window.myChart1) {
                // Update existing chart
                window.myChart1.data.labels = labels;
                window.myChart1.data.datasets[0].data = data;
                window.myChart1.update();
            } 
            else {
                // Create new chart if not exist
                var ctx1 = document.getElementById('barChart').getContext('2d');
                window.myChart1 = new Chart(ctx1, {
                    type: 'bar',
                    data: {
                        labels: labels,
                        datasets: [{
                            label: 'Bilangan',
                            data: data,
                            backgroundColor: [
                                'rgba(75, 192, 192, 1)',
                                'rgba(255, 99, 132, 0.7)',
                                'rgba(54, 162, 235, 0.7)',
                                'rgba(255, 206, 86, 0.7)',
                                'rgba(153, 102, 255, 0.7)',
                            ],
                            borderColor: [
                                'rgba(75, 192, 192, 1)',
                                'rgba(255, 99, 132, 1)',
                                'rgba(54, 162, 235, 1)',
                                'rgba(255, 206, 86, 1)',
                                'rgba(153, 102, 255, 1)',
                            ],
                            borderWidth: 1
                        }]
                    },
                    options: {
                        plugins: {
                            title: {
                                display: true,
                                text: 'Bilangan Permohonan berdasarkan Pekerjaan',
                                font: {
                                    size: 18,
                                }
                            },
                            legend: {
                                display: false,
                            }
                        },
                        scales: {
                            y: {
                                beginAtZero: true
                            }
                        }
                    }
                });
            }
            
        },
        error: function(xhr, status, error) {
            console.error('Error fetching data:', error);
        }
    });
}
