@extends('layouts.app')

@section('title')
    UnityCare
@endsection

@section('content')

    <div>
        <canvas id="barChart"></canvas>
    </div>

    <script>
        var ctx = document.getElementById('barChart').getContext('2d');
        var myChart = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: {!! $num->pluck('labels') !!},
                datasets: [{
                    label: 'Data',
                    data: {!! $num->pluck('data') !!},
                    backgroundColor: 'rgba(75, 192, 192, 0.2)',
                    borderColor: 'rgba(75, 192, 192, 1)',
                    borderWidth: 1
                }]
            },
            options: {
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });
    </script>

@endsection
