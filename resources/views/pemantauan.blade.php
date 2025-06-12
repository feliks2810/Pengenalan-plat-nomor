<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Pemantauan Real-time</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
  <link
    rel="stylesheet"
    href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css"
  />
  <style>
    /* Remove default video controls to replicate the custom control style */
    img::-webkit-media-controls {
      display: none !important;
    }
    .live-indicator {
      animation: pulse 2s infinite;
    }
    @keyframes pulse {
      0%, 100% { opacity: 1; }
      50% { opacity: 0.5; }
    }
  </style>
</head>
<body class="bg-[#a9cbb3] min-h-screen flex flex-col items-center justify-start p-8">
  <!-- Header dengan Live Indicator -->
  <div class="w-full max-w-3xl mb-6">
    <h1 class="text-2xl font-bold text-black text-center mb-2">
      Pemantauan Real-time 
      <span class="bg-green-500 text-white px-2 py-1 rounded text-sm live-indicator">LIVE</span>
    </h1>
  </div>

  <!-- Video Feed Container -->
  <div class="w-full max-w-3xl relative mb-4">
    <button
      aria-label="Close"
      class="absolute top-2 left-2 text-black text-xl font-bold focus:outline-none z-10 bg-white bg-opacity-75 rounded-full w-8 h-8 flex items-center justify-center hover:bg-opacity-100 transition-all"
      onclick="closePage()"
    >
      <i class="fas fa-times"></i>
    </button>
    
    <!-- Camera Feed -->
    <div class="relative">
      <img
        id="video-feed"
        class="w-full max-w-3xl rounded-lg border-2 border-black"
        src="http://localhost:5000/video_feed"
        alt="Video Feed Kamera #1"
        onerror="this.src='https://placehold.co/640x480?text=Kamera+Tidak+Tersedia'"
      />
      
      <!-- Camera Label -->
      <div class="absolute top-4 right-4 bg-black bg-opacity-75 text-white px-3 py-1 rounded">
        Kamera #1
      </div>
    </div>
  </div>

  <!-- Detection Info and Time Display -->
  <div class="w-full max-w-3xl mt-6 flex justify-between gap-8 px-4">
    <button
      id="detection-date"
      class="bg-[#6fcf97] rounded border border-black text-black text-center py-2 px-6 w-44"
      type="button"
    >
      --/--/----
    </button>
    <button
      id="detection-time"
      class="bg-[#6fcf97] rounded border border-black text-black text-center py-2 px-6 w-44"
      type="button"
    >
      --:--:--
    </button>
  </div>



  <!-- Control Buttons -->
  <div class="w-full max-w-3xl mt-6 flex flex-wrap justify-center gap-4">
    <button 
      onclick="window.location.href='/pelanggaran'" 
      class="bg-[#6fcf97] hover:bg-green-400 rounded border border-black text-black text-center py-2 px-6 transition-colors"
    >
      <i class="fas fa-list mr-2"></i>Lihat Pelanggaran
    </button>
    <button 
      class="bg-[#6fcf97] hover:bg-green-400 rounded border border-black text-black text-center py-2 px-6 transition-colors"
      onclick="testDetection()"
    >
      <i class="fas fa-play mr-2"></i>Uji Deteksi
    </button>
    <button 
      class="bg-[#6fcf97] hover:bg-green-400 rounded border border-black text-black text-center py-2 px-6 transition-colors"
      onclick="toggleCamera()"
    >
      <i class="fas fa-video mr-2"></i>Ganti Kamera
    </button>
    <button 
      class="bg-[#6fcf97] hover:bg-green-400 rounded border border-black text-black text-center py-2 px-6 transition-colors"
      onclick="openSettings()"
    >
      <i class="fas fa-cog mr-2"></i>Pengaturan
    </button>
  </div>

  <!-- Status Info -->
  <div class="w-full max-w-3xl mt-6 text-center text-black">
    <p class="text-sm">
      Durasi Pemantauan: <span id="monitoring-duration" class="font-mono">00:00</span> | 
      Tingkat Deteksi: <span id="detection-rate" class="font-mono">0/jam</span>
    </p>
  </div>

  <script>
    let monitoringStartTime = new Date();

    $(document).ready(function() {
      function updateDetection() {
        $.get('http://localhost:5000/api/latest_detection', function(data) {
          if (data.detections && data.detections.length > 0) {
            const detection = data.detections[0];
            const detectionDate = new Date(detection.time);
            
            // Update date and time displays
            $('#detection-date').text(detectionDate.toLocaleDateString('id-ID'));
            $('#detection-time').text(detectionDate.toLocaleTimeString('id-ID'));
          }
        }).fail(function() {
          console.log('Failed to fetch detection data');
        });
      }

      function updateMonitoringDuration() {
        const now = new Date();
        const duration = Math.floor((now - monitoringStartTime) / 1000);
        const hours = Math.floor(duration / 3600);
        const minutes = Math.floor((duration % 3600) / 60);
        $('#monitoring-duration').text(
          String(hours).padStart(2, '0') + ':' + String(minutes).padStart(2, '0')
        );
      }

      // Update detection every 2 seconds
      setInterval(updateDetection, 2000);
      
      // Update monitoring duration every 30 seconds
      setInterval(updateMonitoringDuration, 30000);
      
      // Initial calls
      updateDetection();
      updateMonitoringDuration();
    });

    // Control functions
    function closePage() {
      // Opsi 1: Kembali ke halaman sebelumnya
      if (window.history.length > 1) {
        window.history.back();
      } else {
        // Opsi 2: Redirect ke halaman utama jika tidak ada history
        window.location.href = '/';
      }
      
      // Opsi 3: Uncomment jika ingin menutup tab/window
      // window.close();
    }

    function testDetection() {
      alert('Menjalankan tes deteksi...');
    }

    function toggleCamera() {
      alert('Fitur ganti kamera akan segera tersedia');
    }

    function openSettings() {
      alert('Membuka pengaturan sistem...');
    }

    // Handle video feed error
    document.getElementById('video-feed').addEventListener('error', function() {
      this.src = 'https://placehold.co/640x480?text=Kamera+Tidak+Tersedia';
    });
  </script>
</body>
</html>