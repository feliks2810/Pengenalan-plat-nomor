<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Daftar Pelanggaran</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="bg-gray-100 font-sans">
    <div class="container mx-auto p-4">
        <h1 class="text-3xl font-bold text-gray-800 mb-4">Daftar Pelanggaran</h1>
        <div class="mb-4">
            @if($pelanggaran->isEmpty())
                <p>Tidak ada pelanggaran ditemukan</p>
            @else
                <ul>
                    @foreach($pelanggaran as $violation)
                        <li class="border p-2 mb-2">
                            ID: {{ $violation->id }} - Deskripsi: {{ $violation->description }} - Waktu: {{ $violation->created_at }}
                            <a href="{{ route('pelanggaran.show', $violation->id) }}" class="text-blue-500 ml-2">Detail</a>
                        </li>
                    @endforeach
                </ul>
            @endif
        </div>
        <a href="{{ route('dashboard') }}" class="text-blue-500 hover:underline mt-4 inline-block">Kembali</a>
    </div>
</body>
</html>