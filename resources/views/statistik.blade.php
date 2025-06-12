<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Statistik Pelanggaran</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/3.9.1/chart.min.js"></script>
    <style>
        .modal-overlay {
            background: rgba(74, 222, 128, 0.8);
        }
        .modal-content {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-radius: 16px;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
        }
        .tab-active {
            color: #10b981;
            border-bottom: 2px solid #10b981;
            font-weight: 600;
        }
        .tab-inactive {
            color: #6b7280;
            border-bottom: 2px solid transparent;
        }
        .tab-inactive:hover {
            color: #374151;
        }
        .chart-container {
            position: relative;
            height: 300px;
            width: 100%;
        }
        .bar-green {
            background: linear-gradient(135deg, #10b981, #34d399);
        }
    </style>
</head>
<body class="min-h-screen modal-overlay flex items-center justify-center p-4">
    <div class="modal-content w-full max-w-4xl p-8">
        <!-- Header -->
        <div class="flex justify-between items-center mb-8">
            <h1 class="text-2xl font-bold text-gray-800">Statistik Pelanggaran</h1>
            <button onclick="closeModal()" class="text-gray-400 hover:text-gray-600 text-2xl font-bold w-8 h-8 flex items-center justify-center">
                ×
            </button>
        </div>

        <!-- Tab Navigation -->
        <div class="flex border-b border-gray-200 mb-8">
            <button id="tab-harian" onclick="switchTab('harian')" class="px-6 py-3 text-sm font-medium tab-active transition-colors duration-200">
                Harian
            </button>
            <button id="tab-mingguan" onclick="switchTab('mingguan')" class="px-6 py-3 text-sm font-medium tab-inactive transition-colors duration-200">
                Mingguan
            </button>
            <button id="tab-bulanan" onclick="switchTab('bulanan')" class="px-6 py-3 text-sm font-medium tab-inactive transition-colors duration-200">
                Bulanan
            </button>
        </div>

        <!-- Chart Legend -->
        <div class="flex items-center mb-6">
            <div class="flex items-center">
                <div class="w-4 h-4 bar-green rounded mr-2"></div>
                <span class="text-sm text-gray-600">Jumlah Pelanggaran</span>
            </div>
        </div>

        <!-- Chart Container -->
        <div class="chart-container mb-8">
            <canvas id="violationChart"></canvas>
        </div>


    </div>

    <script>
        // Sample data - replace with your actual data
        const violationData = {
            harian: {
                labels: ['Sen', 'Sel', 'Rab', 'Kam', 'Jum', 'Sab', 'Min'],
                data: [5, 7, 3, 4, 6, 2, 1],
                details: [
                    { period: 'Senin', count: 5, plates: ['B1234AB', 'D5678CD', 'F9012EF'] },
                    { period: 'Selasa', count: 7, plates: ['B1111AA', 'D2222BB', 'F3333CC', 'H4444DD'] },
                    { period: 'Rabu', count: 3, plates: ['B5555EE', 'D6666FF'] },
                    { period: 'Kamis', count: 4, plates: ['B7777GG', 'D8888HH', 'F9999II'] },
                    { period: 'Jumat', count: 6, plates: ['B0000JJ', 'D1111KK', 'F2222LL', 'H3333MM'] },
                    { period: 'Sabtu', count: 2, plates: ['B4444NN'] },
                    { period: 'Minggu', count: 1, plates: ['D5555OO'] }
                ]
            },
            mingguan: {
                labels: ['Minggu 1', 'Minggu 2', 'Minggu 3', 'Minggu 4'],
                data: [15, 12, 18, 10],
                details: [
                    { period: 'Minggu 1', count: 15, plates: ['B1234AB', 'D5678CD', 'F9012EF', 'H3456GH'] },
                    { period: 'Minggu 2', count: 12, plates: ['B7890IJ', 'D1234KL', 'F5678MN'] },
                    { period: 'Minggu 3', count: 18, plates: ['B9012OP', 'D3456QR', 'F7890ST', 'H1234UV', 'J5678WX'] },
                    { period: 'Minggu 4', count: 10, plates: ['B2345YZ', 'D6789AB', 'F0123CD'] }
                ]
            },
            bulanan: {
                labels: ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun'],
                data: [45, 38, 52, 41, 49, 35],
                details: [
                    { period: 'Januari', count: 45, plates: ['B1111AA', 'D2222BB', 'F3333CC', 'H4444DD', 'J5555EE'] },
                    { period: 'Februari', count: 38, plates: ['B6666FF', 'D7777GG', 'F8888HH', 'H9999II'] },
                    { period: 'Maret', count: 52, plates: ['B0000JJ', 'D1111KK', 'F2222LL', 'H3333MM', 'J4444NN', 'L5555OO'] },
                    { period: 'April', count: 41, plates: ['B6666PP', 'D7777QQ', 'F8888RR', 'H9999SS'] },
                    { period: 'Mei', count: 49, plates: ['B0000TT', 'D1111UU', 'F2222VV', 'H3333WW', 'J4444XX'] },
                    { period: 'Juni', count: 35, plates: ['B5555YY', 'D6666ZZ', 'F7777AA', 'H8888BB'] }
                ]
            }
        };

        let currentTab = 'harian';
        let chart = null;

        function initChart() {
            const ctx = document.getElementById('violationChart').getContext('2d');
            const data = violationData[currentTab];
            
            if (chart) {
                chart.destroy();
            }

            chart = new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: data.labels,
                    datasets: [{
                        label: 'Jumlah Pelanggaran',
                        data: data.data,
                        backgroundColor: 'rgba(16, 185, 129, 0.8)',
                        borderColor: 'rgba(16, 185, 129, 1)',
                        borderWidth: 1,
                        borderRadius: 4,
                        borderSkipped: false,
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: false
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                stepSize: 1,
                                color: '#6b7280'
                            },
                            grid: {
                                color: '#f3f4f6'
                            }
                        },
                        x: {
                            ticks: {
                                color: '#6b7280'
                            },
                            grid: {
                                display: false
                            }
                        }
                    }
                }
            });
        }

        function switchTab(tab) {
            // Remove active class from all tabs
            document.querySelectorAll('[id^="tab-"]').forEach(tabBtn => {
                tabBtn.className = tabBtn.className.replace('tab-active', 'tab-inactive');
            });
            
            // Add active class to selected tab
            document.getElementById(`tab-${tab}`).className = document.getElementById(`tab-${tab}`).className.replace('tab-inactive', 'tab-active');
            
            currentTab = tab;
            initChart();
        }



        function closeModal() {
            // Try multiple methods to close the modal/window
            if (window.parent && window.parent !== window) {
                // If opened in iframe, try to communicate with parent
                window.parent.postMessage('closeModal', '*');
            } else if (window.opener) {
                // If opened as popup, close and focus back to opener
                window.close();
            } else {
                // If opened as regular page, try to go back or close
                if (history.length > 1) {
                    history.back();
                } else {
                    // As last resort, try to close (may not work in all browsers)
                    window.close();
                    // If close doesn't work, redirect or hide the modal
                    if (!window.closed) {
                        document.body.style.display = 'none';
                        alert('Halaman akan ditutup. Silakan tutup tab ini secara manual.');
                    }
                }
            }
        }

        // Initialize the chart when page loads
        window.onload = function() {
            initChart();
        };
    </script>
</body>
</html>