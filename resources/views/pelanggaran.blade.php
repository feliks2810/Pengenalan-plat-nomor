<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Data Pelanggaran</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
  <link
    rel="stylesheet"
    href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css"
  />
</head>
<body class="bg-[#9ccba7] min-h-screen flex items-start justify-center p-4">
  <div class="w-full max-w-md">
    <!-- Date Filter Button -->
    <button 
      id="date-filter" 
      class="mb-4 bg-[#4dbd87] text-[#0f3f2f] font-sans text-sm font-semibold py-2 px-6 rounded shadow-sm hover:bg-[#3da872] transition-colors"
      type="button"
      onclick="toggleDateFilter()"
    >
      <span id="current-date">HH/BB/TTTT</span>
      <i class="fas fa-calendar-alt ml-2"></i>
    </button>

    <!-- Date Filter Dropdown (Hidden by default) -->
    <div id="date-filter-dropdown" class="mb-4 bg-white rounded shadow-lg border border-[#7a9a7f] p-4 hidden">
      <h4 class="text-sm font-semibold text-[#0f3f2f] mb-3">Filter Tanggal:</h4>
      <div class="flex gap-2 mb-3">
        <select id="filter-day" class="flex-1 text-xs border border-[#7a9a7f] rounded py-1 px-2">
          <option value="">DD</option>
        </select>
        <span class="text-[#0f3f2f] self-center">/</span>
        <select id="filter-month" class="flex-1 text-xs border border-[#7a9a7f] rounded py-1 px-2">
          <option value="">MM</option>
        </select>
        <span class="text-[#0f3f2f] self-center">/</span>
        <select id="filter-year" class="flex-1 text-xs border border-[#7a9a7f] rounded py-1 px-2">
          <option value="">YYYY</option>
        </select>
      </div>
      <div class="flex gap-2">
        <button 
          onclick="applyDateFilter()" 
          class="flex-1 bg-[#4dbd87] text-[#0f3f2f] text-xs py-1 px-3 rounded hover:bg-[#3da872]"
        >
          Terapkan
        </button>
        <button 
          onclick="clearDateFilter()" 
          class="flex-1 bg-gray-300 text-gray-700 text-xs py-1 px-3 rounded hover:bg-gray-400"
        >
          Reset
        </button>
      </div>
    </div>

    <!-- Loading Indicator -->
    <div id="loading" class="text-center py-4 hidden">
      <i class="fas fa-spinner fa-spin text-[#0f3f2f] text-xl"></i>
      <p class="text-[#0f3f2f] text-sm mt-2">Memuat data...</p>
    </div>

    <!-- Violations List -->
    <div id="violations-list" class="space-y-3">
      <!-- Data akan diisi oleh JavaScript -->
    </div>

    <!-- No Data Message -->
    <div id="no-data" class="text-center py-8 hidden">
      <i class="fas fa-clipboard-list text-[#0f3f2f] text-4xl mb-3"></i>
      <p class="text-[#0f3f2f] text-sm">Tidak ada pelanggaran ditemukan</p>
    </div>

    <!-- Load More Button -->
    <button 
      id="load-more" 
      class="w-full mt-4 bg-[#4dbd87] text-[#0f3f2f] font-sans text-sm font-semibold py-2 px-6 rounded shadow-sm hover:bg-[#3da872] transition-colors hidden"
      onclick="loadMoreViolations()"
    >
      Muat Lebih Banyak
    </button>

    <!-- Back Button -->
    <button 
      onclick="goBack()" 
      class="w-full mt-4 bg-gray-300 text-gray-700 font-sans text-sm font-semibold py-2 px-6 rounded shadow-sm hover:bg-gray-400 transition-colors"
    >
      <i class="fas fa-arrow-left mr-2"></i>Kembali
    </button>
  </div>

  <!-- Modal for expanded view -->
  <div id="modal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center p-4 hidden z-50">
    <div class="bg-white rounded-lg max-w-lg w-full p-6">
      <div class="flex justify-between items-center mb-4">
        <h3 class="text-lg font-semibold text-[#0f3f2f]">Detail Pelanggaran</h3>
        <button onclick="closeModal()" class="text-gray-500 hover:text-gray-700">
          <i class="fas fa-times text-xl"></i>
        </button>
      </div>
      <div id="modal-content">
        <!-- Modal content akan diisi oleh JavaScript -->
      </div>
    </div>
  </div>

  <script>
    let currentPage = 0;
    let allViolations = [];
    let filteredViolations = [];
    const itemsPerPage = 10;

    $(document).ready(function() {
      initializeDateSelectors();
      loadViolations();
      
      // Auto refresh setiap 30 detik
      setInterval(loadViolations, 30000);
    });

    function initializeDateSelectors() {
      // Populate day selector
      for (let i = 1; i <= 31; i++) {
        const day = String(i).padStart(2, '0');
        $('#filter-day').append(`<option value="${day}">${day}</option>`);
      }

      // Populate month selector
      for (let i = 1; i <= 12; i++) {
        const month = String(i).padStart(2, '0');
        $('#filter-month').append(`<option value="${month}">${month}</option>`);
      }

      // Populate year selector
      const currentYear = new Date().getFullYear();
      for (let year = 2023; year <= currentYear; year++) {
        $('#filter-year').append(`<option value="${year}">${year}</option>`);
      }

      // Set current date as default
      const today = new Date();
      const currentDateStr = today.toLocaleDateString('id-ID', {
        day: '2-digit',
        month: '2-digit', 
        year: 'numeric'
      });
      $('#current-date').text(currentDateStr);
    }

    function loadViolations() {
      $('#loading').show();
      $('#violations-list').hide();
      $('#no-data').hide();

      $.get('http://localhost:5000/api/violations', function(data) {
        allViolations = data.violations || [];
        applyCurrentFilter();
      }).fail(function(xhr, status, error) {
        console.log("Gagal mengambil data pelanggaran:", error);
        $('#loading').hide();
        $('#no-data').show();
        $('#no-data p').text('Error: Gagal memuat data');
      });
    }

    function applyCurrentFilter() {
      // Apply any active filters
      const day = $('#filter-day').val();
      const month = $('#filter-month').val();
      const year = $('#filter-year').val();

      if (day || month || year) {
        filteredViolations = allViolations.filter(violation => {
          const date = new Date(violation.timestamp);
          const vDay = String(date.getDate()).padStart(2, '0');
          const vMonth = String(date.getMonth() + 1).padStart(2, '0');
          const vYear = String(date.getFullYear());

          return (!day || vDay === day) &&
                 (!month || vMonth === month) &&
                 (!year || vYear === year);
        });
      } else {
        filteredViolations = [...allViolations];
      }

      currentPage = 0;
      displayViolations();
    }

    function displayViolations() {
      $('#loading').hide();
      
      if (filteredViolations.length === 0) {
        $('#violations-list').hide();
        $('#no-data').show();
        $('#load-more').hide();
        return;
      }

      const startIndex = currentPage * itemsPerPage;
      const endIndex = startIndex + itemsPerPage;
      const violationsToShow = filteredViolations.slice(0, endIndex);

      let html = '';
      violationsToShow.forEach((violation, index) => {
        const date = new Date(violation.timestamp);
        const dateStr = date.toLocaleDateString('id-ID', {
          day: '2-digit',
          month: '2-digit',
          year: 'numeric'
        });
        const timeStr = date.toLocaleTimeString('id-ID', {
          hour: '2-digit',
          minute: '2-digit'
        });

        const plateNumber = violation.plateNumber || 'Tidak Teridentifikasi';
        const imageUrl = violation.imageFile ? 
          `http://localhost:5000/api/images/${violation.imageFile}` : 
          'https://placehold.co/60x40?text=No+Image';

        html += `
          <div class="flex items-center bg-[#a6c9b0] rounded border border-[#7a9a7f] p-2 violation-item" data-index="${index}">
            <img 
              alt="Gambar Pelanggaran" 
              class="w-16 h-10 object-cover rounded cursor-pointer" 
              src="${imageUrl}"
              onerror="this.src='https://placehold.co/60x40?text=No+Image'"
              onclick="expandImage('${imageUrl}', '${plateNumber}', '${dateStr}', '${timeStr}', '${violation.violationType || 'Pelanggaran Lalu Lintas'}')"
            />
            <input 
              class="mx-4 flex-1 text-center text-xs font-mono bg-white border border-[#7a9a7f] rounded py-1" 
              readonly 
              type="text" 
              value="${plateNumber}"
            />
            <button 
              aria-label="Expand" 
              class="text-[#4a6a4a] hover:text-[#2f4a2f] text-lg" 
              type="button"
              onclick="expandImage('${imageUrl}', '${plateNumber}', '${dateStr}', '${timeStr}', '${violation.violationType || 'Pelanggaran Lalu Lintas'}')"
            >
              <i class="fas fa-expand"></i>
            </button>
          </div>
        `;
      });

      $('#violations-list').html(html).show();
      
      // Show/hide load more button
      if (endIndex < filteredViolations.length) {
        $('#load-more').show();
      } else {
        $('#load-more').hide();
      }
    }

    function loadMoreViolations() {
      currentPage++;
      displayViolations();
    }

    function toggleDateFilter() {
      $('#date-filter-dropdown').toggleClass('hidden');
    }

    function applyDateFilter() {
      const day = $('#filter-day').val();
      const month = $('#filter-month').val();
      const year = $('#filter-year').val();

      let dateText = '';
      if (day || month || year) {
        dateText = `${day || 'HH'}/${month || 'BB'}/${year || 'TTTT'}`;
      } else {
        const today = new Date();
        dateText = today.toLocaleDateString('id-ID', {
          day: '2-digit',
          month: '2-digit',
          year: 'numeric'
        });
      }

      $('#current-date').text(dateText);
      $('#date-filter-dropdown').addClass('hidden');
      applyCurrentFilter();
    }

    function clearDateFilter() {
      $('#filter-day').val('');
      $('#filter-month').val('');
      $('#filter-year').val('');
      
      const today = new Date();
      const todayStr = today.toLocaleDateString('id-ID', {
        day: '2-digit',
        month: '2-digit',
        year: 'numeric'
      });
      $('#current-date').text(todayStr);
      
      $('#date-filter-dropdown').addClass('hidden');
      applyCurrentFilter();
    }

    function expandImage(imageUrl, plateNumber, date, time, violationType) {
      const modalContent = `
        <div class="text-center">
          <img src="${imageUrl}" alt="Gambar Pelanggaran" class="w-full max-h-64 object-contain rounded mb-4">
          <div class="text-left space-y-2">
            <p><strong>Plat Nomor:</strong> <span class="font-mono bg-yellow-100 px-2 py-1 rounded">${plateNumber}</span></p>
            <p><strong>Jenis Pelanggaran:</strong> ${violationType}</p>
            <p><strong>Tanggal:</strong> ${date}</p>
            <p><strong>Waktu:</strong> ${time}</p>
          </div>
        </div>
      `;
      
      $('#modal-content').html(modalContent);
      $('#modal').removeClass('hidden');
    }

    function closeModal() {
      $('#modal').addClass('hidden');
    }

    function goBack() {
      if (window.history.length > 1) {
        window.history.back();
      } else {
        window.location.href = '/';
      }
    }

    // Close modal when clicking outside
    $('#modal').click(function(e) {
      if (e.target === this) {
        closeModal();
      }
    });
  </script>
</body>
</html>
