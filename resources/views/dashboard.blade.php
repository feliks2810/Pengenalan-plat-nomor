<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Beranda PINHEL</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css" rel="stylesheet">
  <style>
    .fade-in {
      animation: fadeIn 0.6s ease-out;
    }
    @keyframes fadeIn {
      from { opacity: 0; transform: translateY(20px); }
      to { opacity: 1; transform: translateY(0); }
    }
  </style>
</head>
<body class="bg-gradient-to-br from-green-200 to-white min-h-screen relative font-sans">

  <!-- Tombol Logout -->
  <div class="absolute top-4 right-4">
    <form action="{{ route('logout') }}" method="POST">
      @csrf
      <button type="submit" class="text-red-600 hover:text-red-800 text-xl">
        <i class="fas fa-sign-out-alt"></i>
      </button>
    </form>
  </div>

  <!-- Konten Utama -->
  <div class="flex items-center justify-center min-h-screen p-4">
    <div class="bg-white bg-opacity-90 p-10 rounded-2xl shadow-xl w-full max-w-md text-center fade-in">
      
      <!-- Logo -->
      <div class="relative inline-block mb-6">
        <img src="{{ asset('images/Logo APK1.png') }}" 
            alt="Logo PINHEL"
            class="mx-auto w-36 h-auto rounded-xl border-4 border-green-300 shadow">
      </div>

      <!-- Judul -->
      <h1 class="text-2xl font-bold text-green-800 mb-8">Selamat Datang di <span class="text-black">PINHEL</span></h1>

      <!-- Tombol Menu -->
      <div>
        <a href="{{ url('/pemantauan') }}">
          <button class="bg-green-400 hover:bg-green-500 text-white font-semibold py-4 px-6 rounded-xl w-full shadow-md transition duration-300 flex items-center justify-center space-x-3">
            <i class="fas fa-video text-lg"></i>
            <span>Pemantauan Real-Time</span>
          </button>
        </a>

        <a href="{{ url('/pelanggaran') }}">
          <button class="bg-green-400 hover:bg-green-500 text-white font-semibold py-4 px-6 rounded-xl w-full shadow-md transition duration-300 flex items-center justify-center space-x-3 mt-6">
            <i class="fas fa-exclamation-triangle text-lg"></i>
            <span>Daftar Pelanggaran</span>
          </button>
        </a>

        <a href="{{ url('/statistik') }}">
          <button class="bg-green-400 hover:bg-green-500 text-white font-semibold py-4 px-6 rounded-xl w-full shadow-md transition duration-300 flex items-center justify-center space-x-3 mt-6">
            <i class="fas fa-chart-bar text-lg"></i>
            <span>Statistik</span>
          </button>
        </a>
      </div>
    </div>
  </div>
</body>
</html>
