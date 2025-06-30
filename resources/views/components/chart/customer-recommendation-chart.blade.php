<div>
    <canvas id="recommendation-chart"></canvas>

    <script>
        const ctx = document.getElementById('recommendation-chart');
        new Chart(ctx, {
            type: 'bar',
            data: {
                labels: {!! json_encode($labels) !!},
                datasets: [{
                    label: 'Recommendations',
                    data: {!! json_encode($data) !!},
                    backgroundColor: 'rgba(54, 162, 235, 0.6)',
                }]
            },
        });
    </script>
</div>
