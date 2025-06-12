<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class PelanggaranController extends Controller
{
    public function show()
    {
        // Logika untuk menampilkan halaman pelanggaran
        return view('pelanggaran'); // Pastikan file pelanggaran.blade.php ada
    }

    public function deteksiPelanggaranRealTime()
    {
        // Logika untuk deteksi real-time (opsional)
        return view('deteksi-real-time'); // Jika ada file terpisah
    }
}