<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Login PINHEL</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet"/>
</head>
<body class="bg-gradient-to-br from-green-100 via-green-200 to-green-100 min-h-screen flex items-center justify-center">

    <div class="w-full max-w-md bg-white rounded-xl shadow-lg p-6 animate-fadeIn">
        <div class="flex justify-center mb-4">
            <img src="{{ asset('images/Logo.png') }}" alt="Logo PINHEL" class="rounded-full" width="120" height="120">
        </div>

        <h2 class="text-center text-2xl font-bold text-green-600 mb-6">MASUK</h2>

        {{-- Alert Session --}}
        @if(session('success'))
            <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-4 rounded">
                <p>{{ session('success') }}</p>
            </div>
        @endif

        @if($errors->has('loginError'))
            <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-4 rounded">
                <p>{{ $errors->first('loginError') }}</p>
            </div>
        @endif

        <form action="{{ route('login') }}" method="POST" class="space-y-5">
            @csrf

            <div class="relative">
                <label for="username" class="text-sm font-semibold text-gray-700 mb-1 block">Nama Pengguna</label>
                <span class="absolute left-3 top-10 text-gray-400">
                    <i class="fas fa-user"></i>
                </span>
                <input type="text" name="username" id="username" required placeholder="Masukkan nama pengguna"
                       class="pl-10 w-full px-4 py-3 border rounded-lg focus:outline-none focus:ring-2 focus:ring-green-400 text-gray-700">
            </div>

            <div class="relative">
                <label for="password" class="text-sm font-semibold text-gray-700 mb-1 block">Kata Sandi</label>
                <span class="absolute left-3 top-10 text-gray-400">
                    <i class="fas fa-lock"></i>
                </span>
                <input type="password" name="password" id="password" required placeholder="Masukkan kata sandi"
                       class="pl-10 w-full px-4 py-3 border rounded-lg focus:outline-none focus:ring-2 focus:ring-green-400 text-gray-700">
                <span class="absolute right-3 top-10 text-gray-400 cursor-pointer password-toggle">
                    <i class="fas fa-eye"></i>
                </span>
            </div>

            <div class="flex items-center justify-between">
                <label class="flex items-center text-sm text-gray-700">
                    <input type="checkbox" class="h-4 w-4 text-green-500 border-gray-300 rounded mr-2">
                    Ingat saya
                </label>
                <a href="#" class="text-sm text-green-500 hover:text-green-700">Lupa kata sandi?</a>
            </div>

            <button type="submit"
                    class="w-full py-3 bg-green-500 hover:bg-green-600 text-white font-bold rounded-lg transition duration-300">
                MASUK
            </button>
        </form>

        <div class="text-center mt-6">
            <p class="text-gray-600">Belum Memiliki Akun?
                <a href="{{ route('register') }}" class="text-green-600 font-bold hover:underline">DAFTAR</a>
            </p>
        </div>
    </div>

    <style>
        .animate-fadeIn {
            animation: fadeIn 0.6s ease-in-out;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(15px); }
            to { opacity: 1; transform: translateY(0); }
        }
    </style>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const toggleBtn = document.querySelector('.password-toggle');
            const passInput = document.querySelector('#password');

            toggleBtn.addEventListener('click', () => {
                const type = passInput.type === 'password' ? 'text' : 'password';
                passInput.type = type;

                const icon = toggleBtn.querySelector('i');
                icon.classList.toggle('fa-eye');
                icon.classList.toggle('fa-eye-slash');
            });
        });
    </script>

</body>
</html>
