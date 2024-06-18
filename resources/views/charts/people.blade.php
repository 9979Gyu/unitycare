<div>
    <canvas id="pieChart"></canvas>
</div>

<script>
    // JavaScript using jQuery for AJAX
    $(document).ready(function() {
        // Function to fetch data and update pie chart
        function updatePieChart() {
            $.ajax({
                url: '/user-pie-chart', // URL to fetch data from
                type: 'GET',
                dataType: 'json',
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
                        var ctx1 = document.getElementById('pieChart').getContext('2d');
                        window.myChart1 = new Chart(ctx1, {
                            type: 'pie',
                            data: {
                                labels: labels,
                                datasets: [{
                                    data: data,
                                    backgroundColor: [
                                        'rgba(255, 99, 132, 0.7)',
                                        'rgba(54, 162, 235, 0.7)',
                                        'rgba(255, 206, 86, 0.7)',
                                        'rgba(75, 192, 192, 0.7)',
                                        'rgba(153, 102, 255, 0.7)',
                                    ],
                                    borderColor: [
                                        'rgba(255, 99, 132, 1)',
                                        'rgba(54, 162, 235, 1)',
                                        'rgba(255, 206, 86, 1)',
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
                                        text: 'Bilangan Pengguna',
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

        // Initial call to update pie chart on page load
        updatePieChart();

        // Update chart every 30 seconds
        setInterval(function() {
            updatePieChart();
        }, 30000);
    });

</script>