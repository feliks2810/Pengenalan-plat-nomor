<?php

namespace App\Http\Controllers;

use App\Models\Pelanggaran; // Pastikan model Pelanggaran sudah ada
use Illuminate\Http\Request;

class PelanggaranController extends Controller
{
    // Method untuk menampilkan halaman pelanggaran
    public function show()
    {
        // Ambil data pelanggaran jika perlu
        $pelanggarans = Pelanggaran::all();  // Ambil semua data pelanggaran dari database

        // Tampilkan ke view pelanggaran
        return view('pelanggaran', compact('pelanggarans'));
    }

    // Method untuk deteksi pelanggaran secara real-time
    public function deteksiPelanggaranRealTime()
    {
        // Logika untuk deteksi pelanggaran real-time
        return view('pelanggaran.deteksiRealTime');
    }
}
