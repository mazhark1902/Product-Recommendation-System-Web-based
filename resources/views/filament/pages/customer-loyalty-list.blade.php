<x-filament-panels::page>
<div class="p-6 bg-white rounded-xl shadow">
        <h2 class="text-xl font-bold mb-4">Customer Recommendation Chart</h2>
        <canvas id="recommendationChart" height="100"></canvas>
    </div>

    {{-- Load Chart.js --}}
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <script>
        document.addEventListener("DOMContentLoaded", function () {
            const ctx = document.getElementById('recommendationChart').getContext('2d');
            new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: ['Customer A', 'Customer B', 'Customer C', 'Customer D'],
                    datasets: [{
                        label: 'Recommendation Score',
                        data: [12, 19, 3, 5],
                        backgroundColor: 'rgba(75, 192, 192, 0.6)',
                        borderColor: 'rgba(75, 192, 192, 1)',
                        borderWidth: 1
                    }]
                },
                options: {
                    responsive: true,
                    scales: {
                        y: {
                            beginAtZero: true
                        }
                    }
                }
            });
        });
    </script>
</x-filament-panels::page>
