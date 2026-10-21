const ctx = document.getElementById('transactionsChart').getContext('2d');
        new Chart(ctx, {
            type: 'line',
            data: {
                labels: ['01/11', '02/11', '03/11', '04/11', '05/11', '06/11', '07/11'],
                datasets: [
                    {
                        label: 'Entrées',
                        data: [400000, 350000, 500000, 450000, 600000, 550000, 350000],
                        borderColor: 'rgb(34, 197, 94)',
                        backgroundColor: 'rgba(34, 197, 94, 0.1)',
                        tension: 0.3,
                        fill: true
                    },
                    {
                        label: 'Sorties',
                        data: [150000, 200000, 180000, 220000, 170000, 190000, 125000],
                        borderColor: 'rgb(239, 68, 68)',
                        backgroundColor: 'rgba(239, 68, 68, 0.1)',
                        tension: 0.3,
                        fill: true
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: true,
                plugins: {
                    legend: {
                        position: 'top',
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: function(value) {
                                return value.toLocaleString() + ' FCFA';
                            }
                        }
                    }
                }
            }
        });