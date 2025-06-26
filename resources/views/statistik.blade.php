<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Statistik Pelanggaran</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="bg-gray-100 font-sans">
    <div class="container mx-auto p-4">
        <div class="flex justify-between items-center mb-4">
            <h1 class="text-3xl font-bold text-gray-800">Statistik Pelanggaran</h1>
            <button class="text-red-500 text-2xl" onclick="window.history.back()">×</button>
        </div>
        <div id="statistik-content" class="bg-white p-4 rounded shadow">
            <h2 class="text-xl font-bold">Jumlah Pelanggaran per Hari</h2>
            <ul>
                @foreach($stats as $stat)
                    <li>{{ $stat->date }}: {{ $stat->total }} pelanggaran</li>
                @endforeach
            </ul>
        </div>
    </div>
</body>
</html>