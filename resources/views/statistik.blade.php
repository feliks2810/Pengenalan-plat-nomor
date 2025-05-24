<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Statistik Pelanggaran - PINHEL</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body class="bg-gradient-to-b from-green-200 to-green-300 min-h-screen flex items-center justify-center px-4 py-8">

  <div class="bg-white p-6 rounded-2xl shadow-2xl w-full max-w-lg">
    <div class="flex justify-between items-center mb-6">
      <h1 class="text-xl md:text-2xl font-bold text-green-800">Statistik Pelanggaran</h1>
      <a href="/dashboard" class="text-gray-500 hover:text-red-500 text-xl font-bold">
        &times;
      </a>
    </div>

    <!-- Filter Buttons -->
    <div class="flex flex-wrap justify-center gap-4 mb-6">
      <button onclick="updateChart('harian')" class="btn-filter">Harian</button>
      <button onclick="updateChart('mingguan')" class="btn-filter">Mingguan</button>
      <button onclick="updateChart('bulanan')" class="btn-filter">Bulanan</button>
    </div>

    <!-- Chart Canvas -->
    <canvas id="statsChart" class="w-full max-h-96"></canvas>
  </div>

  <script>
    const dataSets = {
      harian: {
        labels: ['Sen', 'Sel', 'Rab', 'Kam', 'Jum', 'Sab', 'Min'],
        data: [5, 7, 3, 4, 6, 2, 1]
      },
      mingguan: {
        labels: ['Minggu 1', 'Minggu 2', 'Minggu 3', 'Minggu 4'],
        data: [15, 20, 10, 18]
      },
      bulanan: {
        labels: ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun'],
        data: [50, 70, 60, 40, 90, 80]
      }
    };

    const ctx = document.getElementById('statsChart').getContext('2d');
    let statsChart = new Chart(ctx, {
      type: 'bar',
      data: {
        labels: dataSets.harian.labels,
        datasets: [{
          label: 'Jumlah Pelanggaran',
          data: dataSets.harian.data,
          backgroundColor: '#22c55e'
        }]
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        scales: {
          y: {
            beginAtZero: true,
            ticks: {
              precision: 0
            }
          }
        }
      }
    });

    function updateChart(type) {
      statsChart.data.labels = dataSets[type].labels;
      statsChart.data.datasets[0].data = dataSets[type].data;
      statsChart.update();
    }
  </script>

  <style>
    .btn-filter {
      @apply bg-green-500 hover:bg-green-600 text-white px-4 py-2 rounded-lg transition shadow;
    }
  </style>
</body>
</html>
