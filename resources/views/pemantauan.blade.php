<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Pemantauan Real-time - PINHEL</title>

  <!-- Tailwind CSS CDN -->
  <script src="https://cdn.tailwindcss.com"></script>

  <!-- Font Awesome CDN -->
  <link
    rel="stylesheet"
    href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css"
  />

  <style>
    .alert-pelanggaran {
      position: fixed;
      top: 20px;
      right: 20px;
      background-color: #ff4444;
      color: white;
      padding: 15px 20px;
      border-radius: 5px;
      z-index: 10000;
      display: none;
      box-shadow: 0 4px 8px rgb(0 0 0 / 0.2);
      min-width: 250px;
      font-weight: 600;
      user-select: none;
    }
  </style>
</head>

<body class="min-h-screen bg-green-100 flex justify-center items-start py-10 px-4">
  <div
    class="bg-white p-6 rounded-xl shadow-lg max-w-7xl w-full
           flex flex-col gap-6
           sm:p-8"
  >
    <!-- Header -->
    <header
      class="flex flex-col sm:flex-row justify-between items-center gap-4 sm:gap-0"
    >
      <div class="flex items-center gap-3">
        <h2 class="text-2xl font-bold text-gray-800">Pemantauan Real-time</h2>
        <span
          class="bg-blue-600 text-white text-xs px-3 py-1 rounded-full select-none"
          >LIVE</span
        >
      </div>
      <div class="flex items-center gap-4">
        <div
          id="connection-status"
          class="disconnected flex items-center gap-2 font-semibold px-3 py-1 rounded select-none"
        >
          <i class="fas fa-circle text-red-500"></i>
          Tidak Terhubung
        </div>
        <button
          id="back-button"
          class="bg-gray-200 hover:bg-gray-300 transition-colors px-4 py-2 rounded flex items-center gap-2 text-gray-700"
        >
          <i class="fas fa-arrow-left"></i> Kembali
        </button>
      </div>
    </header>

    <!-- Alert Pelanggaran -->
    <div id="alert-pelanggaran" class="alert-pelanggaran" role="alert">
      <i class="fas fa-exclamation-triangle mr-2"></i> Pelanggaran Terdeteksi!
    </div>

    <!-- Video Container -->
    <section
      id="video-container"
      class="border-4 border-blue-500 p-1 rounded-lg relative overflow-hidden max-w-full"
      style="aspect-ratio: 16 / 9;"
    >
      <img
        id="video-stream"
        src="http://127.0.0.1:5000/video_feed"
        alt="Live stream"
        onerror="handleVideoError()"
        onload="handleVideoConnected()"
        class="w-full h-full object-cover rounded"
        draggable="false"
      />

      <div
        class="absolute bottom-0 left-0 right-0 bg-black bg-opacity-60 p-3 flex justify-between items-center"
      >
        <div id="camera-label" class="text-white font-semibold select-none">
          Kamera #1
        </div>
        <div class="video-controls flex gap-2">
          <button
            id="reload-btn"
            class="bg-gray-800 hover:bg-gray-700 text-white p-2 rounded"
            title="Muat Ulang"
            aria-label="Muat ulang video"
          >
            <i class="fas fa-sync-alt"></i>
          </button>
          <button
            id="zoom-out-btn"
            class="bg-gray-800 hover:bg-gray-700 text-white p-2 rounded"
            title="Kecilkan"
            aria-label="Perbesar video"
          >
            <i class="fas fa-search-minus"></i>
          </button>
          <button
            id="zoom-in-btn"
            class="bg-gray-800 hover:bg-gray-700 text-white p-2 rounded"
            title="Perbesar"
            aria-label="Perbesar video"
          >
            <i class="fas fa-search-plus"></i>
          </button>
          <button
            id="fullscreen-btn"
            class="bg-gray-800 hover:bg-gray-700 text-white p-2 rounded"
            title="Layar Penuh"
            aria-label="Layar penuh"
          >
            <i class="fas fa-expand"></i>
          </button>
        </div>
      </div>
    </section>

    <!-- Info Grid -->
    <section
      class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-5 mt-3"
      aria-label="Informasi sistem dan waktu"
    >
      <article
        class="bg-blue-100 p-5 rounded-lg shadow select-none"
        aria-live="polite"
      >
        <div class="text-sm text-gray-600 mb-1">Tanggal &amp; Waktu</div>
        <div class="flex justify-between font-semibold text-black text-lg">
          <span id="date-display">--/--/----</span>
          <span id="time-display">--:--:--</span>
        </div>
      </article>

      <article
        class="bg-blue-100 p-5 rounded-lg shadow select-none"
        aria-live="polite"
      >
        <div class="text-sm text-gray-600 mb-1">Status Sistem</div>
        <div
          class="flex justify-between items-center font-semibold text-green-600 text-lg"
        >
          <span id="system-status">
            <i class="fas fa-check-circle"></i> Aktif
          </span>
          <span
            id="detection-status"
            class="text-blue-700 font-semibold text-base sm:text-lg"
            >Deteksi: <span id="detection-count">0</span></span
          >
        </div>
      </article>

      <article
        class="bg-blue-100 p-5 rounded-lg shadow select-none"
        aria-live="polite"
      >
        <div class="text-sm text-gray-600 mb-1">Deteksi Terakhir</div>
        <div class="flex justify-between items-center font-semibold text-gray-800 text-lg">
          <span id="last-detection-time">--:--:--</span>
          <a
            href="/violations"
            class="text-blue-600 hover:underline text-sm sm:text-base flex items-center gap-1"
            >Lihat Pelanggaran <i class="fas fa-arrow-right"></i
          ></a>
        </div>
      </article>
    </section>

    <!-- Statistik dan Kontrol Grid -->
    <section
      class="grid grid-cols-1 sm:grid-cols-2 gap-6 mt-6"
      aria-label="Statistik dan kontrol sistem"
    >
      <article
        class="bg-white border border-gray-200 rounded-lg p-5 shadow"
        aria-live="polite"
      >
        <h3
          class="text-lg font-bold mb-5 flex items-center gap-3 text-gray-700"
          ><i class="fas fa-chart-bar text-blue-500"></i> Statistik Harian</h3
        >
        <div class="grid grid-cols-2 gap-4">
          <div class="bg-green-50 p-4 rounded-lg text-center">
            <div class="text-sm text-gray-600 mb-1">Total Deteksi</div>
            <div
              class="text-3xl font-extrabold text-green-700"
              id="total-detections"
              >0</div
            >
          </div>
          <div class="bg-yellow-50 p-4 rounded-lg text-center">
            <div class="text-sm text-gray-600 mb-1">Belum Diproses</div>
            <div
              class="text-3xl font-extrabold text-yellow-700"
              id="pending-count"
              >0</div
            >
          </div>
          <div class="bg-blue-50 p-4 rounded-lg text-center">
            <div class="text-sm text-gray-600 mb-1">Durasi Pemantauan</div>
            <div
              class="text-3xl font-extrabold text-blue-700"
              id="monitoring-duration"
              >00:00</div
            >
          </div>
          <div class="bg-purple-50 p-4 rounded-lg text-center">
            <div class="text-sm text-gray-600 mb-1">Tingkat Deteksi</div>
            <div
              class="text-3xl font-extrabold text-purple-700"
              id="detection-rate"
              >0/jam</div
            >
          </div>
        </div>
      </article>

      <article
        class="bg-white border border-gray-200 rounded-lg p-5 shadow"
        aria-label="Kontrol sistem"
      >
        <h3
          class="text-lg font-bold mb-5 flex items-center gap-3 text-gray-700"
          ><i class="fas fa-cog text-blue-500"></i> Kontrol Sistem</h3
        >
        <div class="grid grid-cols-2 gap-4">
          <button
            id="view-violations-btn"
            class="bg-blue-600 hover:bg-blue-700 text-white py-3 rounded flex items-center justify-center gap-2"
          >
            <i class="fas fa-list"></i> Lihat Pelanggaran
          </button>
          <button
            id="test-detection-btn"
            class="bg-green-600 hover:bg-green-700 text-white py-3 rounded flex items-center justify-center gap-2"
          >
            <i class="fas fa-vial"></i> Uji Deteksi
          </button>
          <button
            id="switch-camera-btn"
            class="bg-purple-600 hover:bg-purple-700 text-white py-3 rounded flex items-center justify-center gap-2"
          >
            <i class="fas fa-video"></i> Ganti Kamera
          </button>
          <button
            id="settings-btn"
            class="bg-gray-600 hover:bg-gray-700 text-white py-3 rounded flex items-center justify-center gap-2"
          >
            <i class="fas fa-cogs"></i> Pengaturan
          </button>
        </div>
      </article>
    </section>

    <!-- Notifikasi Terakhir -->
    <section
      class="mt-6 bg-white border border-gray-200 rounded-lg p-5 shadow max-h-52 overflow-y-auto"
      aria-label="Notifikasi terakhir"
    >
      <h3
        class="text-lg font-bold mb-4 flex items-center gap-2 text-gray-700"
      >
        <i class="fas fa-bell text-blue-500"></i> Notifikasi Terakhir
      </h3>
      <div id="notification-list" class="space-y-3">
        <!-- Contoh notifikasi -->
        <div
          class="p-3 bg-blue-50 rounded-lg flex justify-between items-center border-l-4 border-blue-500"
          role="alert"
          aria-live="polite"
        >
          <div>
            <div class="font-semibold">Sistem dimulai</div>
            <div class="text-sm text-gray-600">
              Pemantauan aktif dan berjalan normal
            </div>
          </div>
          <div class="text-xs text-gray-500 whitespace-nowrap">Baru saja</div>
        </div>
      </div>
    </section>
  </div>

  <script>
    document.addEventListener("DOMContentLoaded", function () {
      let currentSize = "medium";
      let isConnected = false;
      let detectionCount = 0;
      let pendingCount = 0;
      let lastDetectionTime = 0;
      let monitoringStartTime = new Date();
      const detectionCooldown = 3000; // 3 detik cooldown antar deteksi
      const API_ENDPOINT = "http://127.0.0.1:5000/api";

      const connectionStatus = document.getElementById("connection-status");
      const videoStream = document.getElementById("video-stream");
      const alertPelanggaran = document.getElementById("alert-pelanggaran");
      const detectionCountEl = document.getElementById("detection-count");
      const totalDetectionsEl = document.getElementById("total-detections");
      const pendingCountEl = document.getElementById("pending-count");
      const lastDetectionTimeEl = document.getElementById("last-detection-time");
      const monitoringDurationEl = document.getElementById("monitoring-duration");
      const detectionRateEl = document.getElementById("detection-rate");
      const notificationList = document.getElementById("notification-list");

      function updateConnectionStatus(connected) {
        isConnected = connected;
        if (connected) {
          connectionStatus.className =
            "connected flex items-center gap-2 font-semibold px-3 py-1 rounded bg-green-600 text-white select-none";
          connectionStatus.innerHTML =
            '<i class="fas fa-circle"></i> Terhubung';
          addNotification("Koneksi berhasil dibuat", "Terhubung ke server pemantauan");
        } else {
          connectionStatus.className =
            "disconnected flex items-center gap-2 font-semibold px-3 py-1 rounded bg-red-600 text-white select-none";
          connectionStatus.innerHTML =
            '<i class="fas fa-circle"></i> Tidak Terhubung';
          addNotification(
            "Koneksi terputus",
            "Tidak dapat terhubung ke server pemantauan",
            "warning"
          );
        }
      }

      window.handleVideoError = function () {
        updateConnectionStatus(false);
        console.error("Tidak dapat terhubung ke video feed");
      };

      window.handleVideoConnected = function () {
        updateConnectionStatus(true);
      };

      function checkServerConnection() {
        fetch(API_ENDPOINT + "/ping")
          .then((response) => {
            if (response.ok) {
              updateConnectionStatus(true);
            } else {
              updateConnectionStatus(false);
            }
          })
          .catch(() => {
            updateConnectionStatus(false);
          });
      }

      // Perbarui tanggal dan waktu setiap detik
      function updateDateTime() {
        const now = new Date();
        document.getElementById("date-display").textContent = now.toLocaleDateString(
          "id-ID",
          { year: "numeric", month: "2-digit", day: "2-digit" }
        );
        document.getElementById("time-display").textContent = now.toLocaleTimeString(
          "id-ID"
        );

        // Durasi pemantauan
        const diffMs = now - monitoringStartTime;
        const diffMins = Math.floor(diffMs / 60000);
        const diffSecs = Math.floor((diffMs % 60000) / 1000);
        monitoringDurationEl.textContent = `${diffMins
          .toString()
          .padStart(2, "0")}:${diffSecs.toString().padStart(2, "0")}`;

        // Perbarui tingkat deteksi per jam
        const hours = diffMs / (1000 * 60 * 60);
        const rate = hours > 0 ? (detectionCount / hours).toFixed(2) : "0";
        detectionRateEl.textContent = `${rate}/jam`;
      }

      // Simulasi deteksi pelanggaran
      function simulateDetection() {
        const now = Date.now();
        if (now - lastDetectionTime < detectionCooldown) {
          return;
        }
        lastDetectionTime = now;

        detectionCount++;
        pendingCount++;

        detectionCountEl.textContent = detectionCount;
        totalDetectionsEl.textContent = detectionCount;
        pendingCountEl.textContent = pendingCount;
        lastDetectionTimeEl.textContent = new Date().toLocaleTimeString("id-ID");

        // Tampilkan alert pelanggaran sebentar
        alertPelanggaran.style.display = "block";
        setTimeout(() => {
          alertPelanggaran.style.display = "none";
        }, 3500);

        addNotification(
          "Pelanggaran terdeteksi",
          `Pelanggaran ke-${detectionCount} pada waktu ${new Date().toLocaleTimeString(
            "id-ID"
          )}`
        );
      }

      // Tambah notifikasi baru ke daftar
      function addNotification(title, message, type = "info") {
        const notif = document.createElement("div");
        notif.className =
          "p-3 rounded-lg flex justify-between items-center border-l-4 " +
          (type === "warning"
            ? "border-yellow-400 bg-yellow-50 text-yellow-800"
            : "border-blue-500 bg-blue-50 text-blue-700");
        notif.setAttribute("role", "alert");
        notif.setAttribute("aria-live", "polite");

        notif.innerHTML = `
          <div>
            <div class="font-semibold">${title}</div>
            <div class="text-sm">${message}</div>
          </div>
          <div class="text-xs text-gray-500 whitespace-nowrap">${new Date().toLocaleTimeString(
            "id-ID"
          )}</div>
        `;
        notificationList.prepend(notif);

        // Hapus notifikasi setelah 1 menit
        setTimeout(() => {
          notif.remove();
        }, 60000);
      }

      // Event tombol
      document.getElementById("reload-btn").addEventListener("click", () => {
        videoStream.src = videoStream.src.split("?")[0] + "?reload=" + Date.now();
      });

      document.getElementById("zoom-in-btn").addEventListener("click", () => {
        if (currentSize === "medium") {
          videoStream.style.width = "150%";
          currentSize = "large";
        } else if (currentSize === "small") {
          videoStream.style.width = "100%";
          currentSize = "medium";
        }
      });

      document.getElementById("zoom-out-btn").addEventListener("click", () => {
        if (currentSize === "medium") {
          videoStream.style.width = "70%";
          currentSize = "small";
        } else if (currentSize === "large") {
          videoStream.style.width = "100%";
          currentSize = "medium";
        }
      });

      document.getElementById("fullscreen-btn").addEventListener("click", () => {
        if (videoStream.requestFullscreen) {
          videoStream.requestFullscreen();
        } else if (videoStream.webkitRequestFullscreen) {
          videoStream.webkitRequestFullscreen();
        } else if (videoStream.msRequestFullscreen) {
          videoStream.msRequestFullscreen();
        }
      });

      document.getElementById("back-button").addEventListener("click", () => {
        window.history.back();
      });

      document.getElementById("view-violations-btn").addEventListener("click", () => {
        window.location.href = "/violations";
      });

      document.getElementById("test-detection-btn").addEventListener("click", () => {
        simulateDetection();
      });

      document.getElementById("switch-camera-btn").addEventListener("click", () => {
        addNotification("Ganti Kamera", "Fitur ganti kamera belum diimplementasikan");
      });

      document.getElementById("settings-btn").addEventListener("click", () => {
        addNotification("Pengaturan", "Fitur pengaturan belum diimplementasikan");
      });

      // Inisialisasi
      setInterval(updateDateTime, 1000);
      setInterval(checkServerConnection, 15000);

      // Cek koneksi awal
      checkServerConnection();
    });
  </script>
</body>
</html>
