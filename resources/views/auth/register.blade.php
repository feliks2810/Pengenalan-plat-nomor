<!DOCTYPE html>
<html>
<head>
    <title>Register</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100">
    <div class="container mx-auto p-4">
        <h1 class="text-2xl font-bold mb-4">Register</h1>
        @if (session('success'))
            <div class="bg-green-100 border border-green-400 text-green-700 p-2 mb-4">{{ session('success') }}</div>
        @endif
        <form method="POST" action="{{ route('register') }}">
            @csrf
            <div class="mb-4">
                <label>Email</label>
                <input type="email" name="email" class="border p-2 w-full" required>
            </div>
            <div class="mb-4">
                <label>Password</label>
                <input type="password" name="password" class="border p-2 w-full" required>
            </div>
            <button type="submit" class="bg-blue-500 text-white p-2">Register</button>
        </form>
    </div>
</body>
</html>