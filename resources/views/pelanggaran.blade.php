<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Halaman Pelanggaran Lalu Lintas</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css" rel="stylesheet" />
  <style>
    .popup-overlay {
      display: none;
      position: fixed;
      top: 0;
      left: 0;
      right: 0;
      bottom: 0;
      background-color: rgba(0, 0, 0, 0.5);
      z-index: 50;
      align-items: center;
      justify-content: center;
    }
    .popup-overlay.active {
      display: flex;
    }
  </style>
</head>
<body class="flex items-center justify-center min-h-screen bg-gray-100">
  <div class="bg-white p-6 rounded-xl shadow-lg w-[95%] max-w-md sm:max-w-lg mx-auto">
    <div class="flex justify-between items-center mb-6">
      <h1 class="text-xl font-bold text-gray-800">Data Pelanggaran</h1>
      <a href="{{ route('dashboard') }}" class="text-gray-500 hover:text-gray-700 transition" title="Kembali ke Dashboard">
        <button id="close-button" class="text-2xl font-bold text-gray-600 hover:text-red-600 transition-colors">
          ×
        </button>
      </a>
    </div>

    <div class="flex justify-center mb-6">
      <div class="flex items-center bg-blue-50 p-2 rounded-lg border border-blue-200">
        <select class="bg-white border border-gray-300 rounded p-2 w-16 text-center text-gray-700">
          <option value="" disabled selected>HH</option>
          @for ($i = 1; $i <= 31; $i++)
            <option>{{ str_pad($i, 2, '0', STR_PAD_LEFT) }}</option>
          @endfor
        </select>
        <div class="text-gray-400 mx-1">/</div>
        <select class="bg-white border border-gray-300 rounded p-2 w-16 text-center text-gray-700">
          <option value="" disabled selected>BB</option>
          @for ($i = 1; $i <= 12; $i++)
            <option>{{ str_pad($i, 2, '0', STR_PAD_LEFT) }}</option>
          @endfor
        </select>
        <div class="text-gray-400 mx-1">/</div>
        <select class="bg-white border border-gray-300 rounded p-2 w-20 text-center text-gray-700">
          <option value="" disabled selected>TTTT</option>
          @for ($year = 2023; $year <= 2026; $year++)
            <option>{{ $year }}</option>
          @endfor
        </select>
        <button class="bg-blue-600 hover:bg-blue-700 text-white p-2 rounded-lg ml-2">
          <i class="fas fa-search"></i>
        </button>
      </div>
    </div>

    <div class="space-y-4">
      @php
        $pelanggarans = [
          ['plate' => 'T 3253 SZ', 'date' => '06/07/2024', 'time' => '13:25 WIB'],
          ['plate' => 'KH 3258 YF', 'date' => '05/07/2024', 'time' => '09:45 WIB'],
          ['plate' => 'KT 3488 QF', 'date' => '04/07/2024', 'time' => '16:10 WIB'],
          ['plate' => 'Z 4618 WA', 'date' => '03/07/2024', 'time' => '11:30 WIB'],
        ];
      @endphp

      @foreach ($pelanggarans as $data)
        <div class="flex items-center bg-gray-50 p-3 rounded-lg border border-gray-200">
          <div class="relative w-16 h-16 rounded-lg mr-4 overflow-hidden">
            <img alt="Pengendara motor" class="object-cover w-full h-full" src="{{ asset('images/sample_motor.jpg') }}" />
            <div class="absolute bottom-0 left-0 right-0 bg-black bg-opacity-40 text-white text-xs p-1 text-center">Motor</div>
          </div>
          <div class="flex-1">
            <input class="bg-white border border-gray-300 rounded-lg p-2 w-full text-center font-medium" readonly type="text" value="{{ $data['plate'] }}" />
          </div>
          <button class="text-blue-600 hover:text-blue-800 ml-3 transition info-btn"
            data-plate="{{ $data['plate'] }}"
            data-date="{{ $data['date'] }}"
            data-time="{{ $data['time'] }}">
            <i class="fas fa-external-link-alt"></i>
          </button>
        </div>
      @endforeach
    </div>

    <div class="mt-6 text-center">
      <button class="bg-blue-600 hover:bg-blue-700 text-white py-2 px-4 rounded-lg font-medium transition shadow-md">
        Lihat Semua Pelanggaran
      </button>
    </div>
  </div>

  <div class="popup-overlay" id="popupOverlay">
    <div class="bg-white p-4 rounded-lg shadow-lg max-w-md">
      <div class="flex justify-end">
        <button class="text-xl font-bold close-popup">×</button>
      </div>
      <img alt="Pengendara motor melanggar" class="w-full h-auto rounded-lg mb-4" src="{{ asset('images/sample_popup.jpg') }}" />
      <div class="bg-blue-100 p-4 rounded-lg">
        <p class="text-black"><strong>Plate Nomor:</strong> <span id="plateNumber">T 3253 SZ</span></p>
        <p class="text-black"><strong>Tanggal:</strong> <span id="violationDate">06/07/2024</span></p>
        <p class="text-black"><strong>Jam:</strong> <span id="violationTime">13:25 WIB</span></p>
      </div>
    </div>
  </div>

  <script>
    document.addEventListener('DOMContentLoaded', function() {
      const infoButtons = document.querySelectorAll('.info-btn');
      const popupOverlay = document.getElementById('popupOverlay');
      const closeButton = document.querySelector('.close-popup');
      const plateElement = document.getElementById('plateNumber');
      const dateElement = document.getElementById('violationDate');
      const timeElement = document.getElementById('violationTime');

      infoButtons.forEach(button => {
        button.addEventListener('click', function() {
          const plate = this.getAttribute('data-plate');
          const date = this.getAttribute('data-date');
          const time = this.getAttribute('data-time');

          plateElement.textContent = plate;
          dateElement.textContent = date;
          timeElement.textContent = time;

          popupOverlay.classList.add('active');
        });
      });

      closeButton.addEventListener('click', function() {
        popupOverlay.classList.remove('active');
      });

      popupOverlay.addEventListener('click', function(e) {
        if (e.target === popupOverlay) {
          popupOverlay.classList.remove('active');
        }
      });
    });
  </script>
</body>
</html>
