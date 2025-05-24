<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Daftar - PINHEL</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet"/>
</head>
<body class="min-h-screen flex items-center justify-center bg-gradient-to-br from-green-100 to-green-200">

    <div class="bg-white rounded-2xl shadow-lg w-full max-w-md p-8 space-y-6 animate-fadeIn">
        <div class="flex flex-col items-center">
            <div class="w-24 h-24 bg-white rounded-full shadow-lg overflow-hidden border-4 border-green-400 -mt-20">
                <img src="{{ asset('images/Logo.png') }}" alt="Logo PINHEL" class="rounded-full" width="120" height="120">
            </div>
            <h1 class="text-3xl font-bold text-green-600 mt-4">Daftar Akun</h1>
        </div>

        @if ($errors->any())
            <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 rounded">
                <p class="font-bold mb-1">Kesalahan:</p>
                <ul class="list-disc ml-5 text-sm">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form action="{{ route('register') }}" method="POST" class="space-y-4">
            @csrf
            <div>
                <label for="username" class="block mb-1 font-semibold text-gray-700">Nama Pengguna</label>
                <div class="relative">
                    <i class="fas fa-user absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400"></i>
                    <input type="text" name="username" id="username" required
                        placeholder="Nama pengguna"
                        class="pl-10 pr-4 py-3 w-full border rounded-xl shadow-sm focus:outline-none focus:ring-2 focus:ring-green-400 focus:border-green-500">
                </div>
            </div>

            <div>
                <label for="password" class="block mb-1 font-semibold text-gray-700">Kata Sandi</label>
                <div class="relative">
                    <i class="fas fa-lock absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400"></i>
                    <input type="password" name="password" id="password" required
                        placeholder="Minimal 8 karakter"
                        class="pl-10 pr-10 py-3 w-full border rounded-xl shadow-sm focus:outline-none focus:ring-2 focus:ring-green-400 focus:border-green-500">
                    <span class="absolute right-3 top-1/2 transform -translate-y-1/2 text-gray-400 cursor-pointer" onclick="togglePassword('password', this)">
                        <i class="fas fa-eye"></i>
                    </span>
                </div>
            </div>

            <div>
                <label for="password_confirmation" class="block mb-1 font-semibold text-gray-700">Konfirmasi Sandi</label>
                <div class="relative">
                    <i class="fas fa-lock absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400"></i>
                    <input type="password" name="password_confirmation" id="password_confirmation" required
                        placeholder="Ulangi kata sandi"
                        class="pl-10 pr-10 py-3 w-full border rounded-xl shadow-sm focus:outline-none focus:ring-2 focus:ring-green-400 focus:border-green-500">
                    <span class="absolute right-3 top-1/2 transform -translate-y-1/2 text-gray-400 cursor-pointer" onclick="togglePassword('password_confirmation', this)">
                        <i class="fas fa-eye"></i>
                    </span>
                </div>
            </div>

            <button type="submit"
                class="w-full bg-green-500 hover:bg-green-600 text-white font-bold py-3 rounded-full transition duration-300 shadow">
                Daftar Sekarang
            </button>
        </form>

        <div class="text-center text-sm text-gray-600">
            Sudah punya akun? 
            <a href="{{ route('login') }}" class="text-green-600 font-semibold hover:underline">Masuk di sini</a>
        </div>
    </div>

    <script>
        function togglePassword(id, el) {
            const input = document.getElementById(id);
            const icon = el.querySelector('i');
            if (input.type === "password") {
                input.type = "text";
                icon.classList.remove("fa-eye");
                icon.classList.add("fa-eye-slash");
            } else {
                input.type = "password";
                icon.classList.remove("fa-eye-slash");
                icon.classList.add("fa-eye");
            }
        }
    </script>

    <style>
        .animate-fadeIn {
            animation: fadeIn 0.6s ease-in-out;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }
    </style>
</body>
</html>
