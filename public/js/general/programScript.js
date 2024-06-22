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

function updateProgramBarChart(selectedState, selectedUser, selectedProgram, startDate, endDate) {
    $.ajax({
        url: '/program-bar-chart', // URL to fetch data from
        type: 'GET',
        dataType: 'json',
        data: {
            programID: selectedProgram,
            userID : selectedUser,
            state: selectedState,
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
                                'rgba(54, 162, 235, 0.7)',
                                'rgba(255, 206, 86, 0.7)',
                                'rgba(255, 99, 132, 0.7)',
                                'rgba(75, 192, 192, 0.7)',
                                'rgba(153, 102, 255, 0.7)',
                            ],
                            borderColor: [
                                'rgba(54, 162, 235, 1)',
                                'rgba(255, 206, 86, 1)',
                                'rgba(255, 99, 132, 1)',
                                'rgba(75, 192, 192, 1)',
                                'rgba(153, 102, 255, 1)',
                            ],
                            borderWidth: 1
                        }]
                    },
                    options: {
                        plugins: {
                            title: {
                                display: true,
                                text: 'Bilangan Peserta untuk Program',
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
            console.log('Error fetching data:', error);
        }
    });
}

function updateProgramPieChart(selectedState, selectedUser, selectedProgram, startDate, endDate) {
    $.ajax({
        url: '/program_type_pie_chart', // URL to fetch data from
        type: 'GET',
        dataType: 'json',
        data: {
            programID: selectedProgram,
            userID : selectedUser,
            state: selectedState,
            startDate: startDate, 
            endDate: endDate
        },
        success: function(response) {
            var labels = response.labels;
            var data = response.data;

            // Update Chart.js instance or create new one
            if (window.myChart2) {
                // Update existing chart
                window.myChart2.data.labels = labels;
                window.myChart2.data.datasets[0].data = data;
                window.myChart2.update();
            } 
            else {
                // Create new chart if not exist
                var ctx2 = document.getElementById('pieChart').getContext('2d');
                window.myChart2 = new Chart(ctx2, {
                    type: 'pie',
                    data: {
                        labels: labels,
                        datasets: [{
                            label: 'Bilangan',
                            data: data,
                            backgroundColor: [
                                'rgba(54, 162, 235, 0.7)',
                                'rgba(255, 206, 86, 0.7)',
                                'rgba(75, 192, 192, 0.7)',
                                'rgba(153, 102, 255, 0.7)',
                                'rgba(255, 99, 132, 0.7)',
                            ],
                            borderColor: [
                                'rgba(54, 162, 235, 1)',
                                'rgba(255, 206, 86, 1)',
                                'rgba(75, 192, 192, 1)',
                                'rgba(153, 102, 255, 1)',
                                'rgba(255, 99, 132, 1)',
                            ],
                            borderWidth: 1
                        }]
                    },
                    options: {
                        plugins: {
                            title: {
                                display: true,
                                text: 'Bilangan Peserta berdasarkan Jenis Penyertaan',
                                font: {
                                    size: 18,
                                }
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

function updateParticipantBarChart(selectedState, selectedUser, selectedProgram, startDate, endDate) {
    $.ajax({
        url: '/part_bar_chart', // URL to fetch data from
        type: 'GET',
        dataType: 'json',
        data: {
            programID: selectedProgram,
            userID : selectedUser,
            state: selectedState,
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
                                'rgba(54, 162, 235, 0.7)',
                                'rgba(255, 206, 86, 0.7)',
                                'rgba(255, 99, 132, 0.7)',
                                'rgba(75, 192, 192, 0.7)',
                                'rgba(153, 102, 255, 0.7)',
                            ],
                            borderColor: [
                                'rgba(54, 162, 235, 1)',
                                'rgba(255, 206, 86, 1)',
                                'rgba(255, 99, 132, 1)',
                                'rgba(75, 192, 192, 1)',
                                'rgba(153, 102, 255, 1)',
                            ],
                            borderWidth: 1
                        }]
                    },
                    options: {
                        plugins: {
                            title: {
                                display: true,
                                text: 'Bilangan Peserta untuk Program',
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
            console.log('Error fetching data:', error);
        }
    });
}

function updateParticipantPieChart(selectedState, selectedUser, selectedProgram, startDate, endDate) {
    $.ajax({
        url: '/part_type_pie_chart', // URL to fetch data from
        type: 'GET',
        dataType: 'json',
        data: {
            programID: selectedProgram,
            userID : selectedUser,
            state: selectedState,
            startDate: startDate, 
            endDate: endDate
        },
        success: function(response) {
            var labels = response.labels;
            var data = response.data;

            // Update Chart.js instance or create new one
            if (window.myChart2) {
                // Update existing chart
                window.myChart2.data.labels = labels;
                window.myChart2.data.datasets[0].data = data;
                window.myChart2.update();
            } 
            else {
                // Create new chart if not exist
                var ctx2 = document.getElementById('pieChart').getContext('2d');
                window.myChart2 = new Chart(ctx2, {
                    type: 'pie',
                    data: {
                        labels: labels,
                        datasets: [{
                            label: 'Bilangan',
                            data: data,
                            backgroundColor: [
                                'rgba(54, 162, 235, 0.7)',
                                'rgba(255, 206, 86, 0.7)',
                                'rgba(75, 192, 192, 0.7)',
                                'rgba(153, 102, 255, 0.7)',
                                'rgba(255, 99, 132, 0.7)',
                            ],
                            borderColor: [
                                'rgba(54, 162, 235, 1)',
                                'rgba(255, 206, 86, 1)',
                                'rgba(75, 192, 192, 1)',
                                'rgba(153, 102, 255, 1)',
                                'rgba(255, 99, 132, 1)',
                            ],
                            borderWidth: 1
                        }]
                    },
                    options: {
                        plugins: {
                            title: {
                                display: true,
                                text: 'Bilangan Peserta berdasarkan Jenis Penyertaan',
                                font: {
                                    size: 18,
                                }
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


$("#resetBtn").click(function(){
    $("#organization").prop('selectedIndex', 0).trigger('change');
    $("#type").prop('selectedIndex', 0).trigger('change');

    $('#startDate1').val('');
    $('#endDate1').val('').trigger('change');
    $('#allRadio').prop('checked', true).trigger('change');
});