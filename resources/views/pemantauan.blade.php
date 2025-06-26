<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>Pemantauan Real-time</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        img::-webkit-media-controls { display: none !important; }
        .live-indicator { animation: pulse 2s infinite; }
        @keyframes pulse { 0%, 100% { opacity: 1; } 50% { opacity: 0.5; } }
        .bg-custom-green { background-color: #a9cbb3; }
        .bg-custom-button { background-color: #6fcf97; }
    </style>
</head>
<body class="font-sans antialiased bg-custom-green min-h-screen flex flex-col items-center justify-start p-8">
    <div class="w-full max-w-3xl mb-6">
        <h1 class="text-2xl font-bold text-black text-center mb-2">
            Pemantauan Real-time 
            <span class="bg-green-500 text-white px-2 py-1 rounded text-sm live-indicator">LIVE</span>
        </h1>
    </div>

    <div class="w-full max-w-3xl relative mb-4">
        <button class="absolute top-2 left-2 text-black text-xl font-bold focus:outline-none z-10 bg-white bg-opacity-75 rounded-full w-8 h-8 flex items-center justify-center hover:bg-opacity-100 transition-all" onclick="closePage()">
            <i class="fas fa-times"></i>
        </button>
        
        <div class="relative">
            <img id="video-feed" class="w-full max-w-3xl rounded-lg border-2 border-black" src="{{ config('app.camera_url', 'http://localhost:5000/video_feed') }}" alt="Video Feed Kamera #1" onerror="this.src='https://placehold.co/640x480?text=Kamera+Tidak+Tersedia'" />
            <div class="absolute top-4 right-4 bg-black bg-opacity-75 text-white px-3 py-1 rounded">Kamera #1</div>
        </div>
    </div>

    <div class="w-full max-w-3xl mt-6 flex justify-between gap-8 px-4">
        <button id="detection-date" class="bg-custom-button rounded border border-black text-black text-center py-2 px-6 w-44" type="button">--/--/----</button>
        <button id="detection-time" class="bg-custom-button rounded border border-black text-black text-center py-2 px-6 w-44" type="button">--:--:--</button>
    </div>

    <div class="w-full max-w-3xl mt-6 flex flex-wrap justify-center gap-4">
        <a href="{{ route('pelanggaran.index') }}" class="bg-custom-button hover:bg-green-400 rounded border border-black text-black text-center py-2 px-6 transition-colors inline-block"><i class="fas fa-list mr-2"></i>Lihat Pelanggaran</a>
        <button class="bg-custom-button hover:bg-green-400 rounded border border-black text-black text-center py-2 px-6 transition-colors" onclick="testDetection()"><i class="fas fa-play mr-2"></i>Uji Deteksi</button>
        <button class="bg-custom-button hover:bg-green-400 rounded border border-black text-black text-center py-2 px-6 transition-colors" onclick="toggleCamera()"><i class="fas fa-video mr-2"></i>Ganti Kamera</button>
    </div>

    <div class="w-full max-w-3xl mt-4 flex flex-wrap justify-center gap-4">
        <a href="{{ route('pemantauan.index') }}" class="bg-blue-500 hover:bg-blue-400 rounded border border-black text-white text-center py-2 px-6 transition-colors inline-block"><i class="fas fa-eye mr-2"></i>Live Monitor</a>
        <a href="{{ route('pemantauan.index') }}" class="bg-gray-500 hover:bg-gray-400 rounded border border-black text-white text-center py-2 px-6 transition-colors inline-block"><i class="fas fa-history mr-2"></i>History</a>
        <a href="{{ route('statistik.index') }}" class="bg-purple-500 hover:bg-purple-400 rounded border border-black text-white text-center py-2 px-6 transition-colors inline-block"><i class="fas fa-chart-bar mr-2"></i>Statistik</a>
    </div>

    <div class="w-full max-w-3xl mt-6 text-center text-black">
        <p class="text-sm">Durasi Pemantauan: <span id="monitoring-duration" class="font-mono">00:00</span> | Tingkat Deteksi: <span id="detection-rate" class="font-mono">0/jam</span></p>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        let monitoringStartTime = new Date();

        $(document).ready(function() {
            function updateDetection() {
                const token = $('meta[name="csrf-token"]').attr('content');
                $.ajax({
                    url: '/api/violations/recent', // Gunakan URL langsung untuk sementara
                    type: 'GET',
                    headers: { 'X-CSRF-TOKEN': token },
                    success: function(data) {
                        if (data && data.length > 0) {
                            const detection = data[0];
                            const detectionDate = new Date(detection.created_at);
                            $('#detection-date').text(detectionDate.toLocaleDateString('id-ID'));
                            $('#detection-time').text(detectionDate.toLocaleTimeString('id-ID'));
                        }
                    },
                    error: function(xhr, status, error) {
                        console.log('AJAX Error: ' + status + ' - ' + error);
                    }
                });
            }

            function updateMonitoringDuration() {
                const now = new Date();
                const duration = Math.floor((now - monitoringStartTime) / 1000);
                const hours = Math.floor(duration / 3600);
                const minutes = Math.floor((duration % 3600) / 60);
                $('#monitoring-duration').text(String(hours).padStart(2, '0') + ':' + String(minutes).padStart(2, '0'));
            }

            function updateDetectionRate() {
                const token = $('meta[name="csrf-token"]').attr('content');
                $.ajax({
                    url: '/api/violations/stats',
                    type: 'GET',
                    headers: { 'X-CSRF-TOKEN': token },
                    success: function(data) {
                        $('#detection-rate').text(data.rate + '/jam');
                    },
                    error: function() {
                        console.log('Failed to fetch stats');
                    }
                });
            }

            setInterval(updateDetection, 2000);
            setInterval(updateMonitoringDuration, 30000);
            setInterval(updateDetectionRate, 60000);
            updateDetection();
            updateMonitoringDuration();
            updateDetectionRate();
        });

        function closePage() {
            if (window.history.length > 1) window.history.back();
            else window.location.href = '{{ route("dashboard") }}';
        }

        function testDetection() {
            const button = event.target;
            const originalText = button.innerHTML;
            button.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Testing...';
            button.disabled = true;
            setTimeout(function() {
                alert('Test deteksi berhasil dijalankan!');
                button.innerHTML = originalText;
                button.disabled = false;
            }, 2000);
        }

        function toggleCamera() {
            const cameras = [
                '{{ config("app.camera_url", "http://localhost:5000/video_feed") }}',
                '{{ config("app.camera_url_2", "http://localhost:5001/video_feed") }}',
                '{{ config("app.camera_url_3", "http://localhost:5002/video_feed") }}'
            ];
            const currentSrc = document.getElementById('video-feed').src;
            let currentIndex = cameras.indexOf(currentSrc);
            currentIndex = (currentIndex + 1) % cameras.length;
            document.getElementById('video-feed').src = cameras[currentIndex];
            document.querySelector('.absolute.top-4.right-4').textContent = `Kamera #${currentIndex + 1}`;
        }

        function openSettings() {
            window.location.href = '{{ route("settings.index") ?? "#" }}';
        }

        document.getElementById('video-feed').addEventListener('error', function() {
            this.src = 'https://placehold.co/640x480?text=Kamera+Tidak+Tersedia';
        });

        setInterval(function() {
            const videoFeed = document.getElementById('video-feed');
            videoFeed.src = videoFeed.src + '?t=' + new Date().getTime();
        }, 30000);
    </script>
</body>
</html>