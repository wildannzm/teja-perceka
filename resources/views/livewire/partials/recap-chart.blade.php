@script
<script>
    (function () {
        let chart = null;

        function buildChart(labels, income, expenses) {
            const canvas = document.getElementById('recapChart');
            if (!canvas || typeof window.Chart === 'undefined') return;
            if (chart) chart.destroy();
            chart = new Chart(canvas.getContext('2d'), {
                type: 'line',
                data: {
                    labels: labels,
                    datasets: [
                        {
                            label: 'Pemasukan',
                            data: income,
                            borderColor: '#10b981',
                            backgroundColor: 'rgba(16, 185, 129, 0.1)',
                            borderWidth: 2,
                            fill: true,
                            tension: 0.3
                        },
                        {
                            label: 'Pengeluaran',
                            data: expenses,
                            borderColor: '#ef4444',
                            backgroundColor: 'rgba(239, 68, 68, 0.1)',
                            borderWidth: 2,
                            fill: true,
                            tension: 0.3
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'top',
                        },
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    let label = context.dataset.label || '';
                                    if (label) {
                                        label += ': ';
                                    }
                                    if (context.parsed.y !== null) {
                                        label += new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR' }).format(context.parsed.y);
                                    }
                                    return label;
                                }
                            }
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            suggestedMax: 1000000,
                            ticks: {
                                callback: function(value) {
                                    if (value === 0) return '0';
                                    if (value >= 1000000000) {
                                        return 'Rp ' + (value / 1000000000).toLocaleString('id-ID', { maximumFractionDigits: 2 }) + ' M';
                                    }
                                    if (value >= 1000000) {
                                        return 'Rp ' + (value / 1000000).toLocaleString('id-ID', { maximumFractionDigits: 2 }) + ' Jt';
                                    }
                                    if (value >= 1000) {
                                        return 'Rp ' + (value / 1000).toLocaleString('id-ID', { maximumFractionDigits: 2 }) + ' Rb';
                                    }
                                    return 'Rp ' + value.toLocaleString('id-ID');
                                }
                            }
                        }
                    }
                }
            });
        }

        buildChart(@json($chartLabels), @json($incomeChart), @json($expenseChart));

        $wire.on('recap-chart-update', (event) => {
            const e = Array.isArray(event) ? event[0] : event;
            buildChart(e.labels ?? [], e.income ?? [], e.expenses ?? []);
        });
    })();
</script>
@endscript
