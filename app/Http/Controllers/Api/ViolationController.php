<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Violation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Http; // Pastikan Http diimpor
use Illuminate\Support\Facades\Log; // Tambahkan impor untuk Log

class ViolationController extends Controller
{
    /**
     * Simpan data pelanggaran baru
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'plate_number' => 'required|string',
            'detection_time' => 'required|date',
            'confidence' => 'required|numeric',
            'image' => 'required|string', // Base64 encoded image
            'violation_type' => 'required|string',
            'helm_confidence' => 'nullable|numeric'
        ]);

        // Decode dan simpan gambar
        $imageData = base64_decode($validated['image']);
        $imageName = 'violations/' . time() . '.jpg';
        Storage::disk('public')->put($imageName, $imageData);

        // Simpan data pelanggaran
        $violation = Violation::create([
            'plate_number' => $validated['plate_number'],
            'detection_time' => $validated['detection_time'],
            'confidence' => $validated['confidence'],
            'image_path' => $imageName,
            'violation_type' => $validated['violation_type'],
            'helm_confidence' => $validated['helm_confidence'] ?? null
        ]);

        return response()->json(['message' => 'Pelanggaran tersimpan', 'data' => $violation], 201);
    }

    /**
     * Ambil daftar semua pelanggaran
     */
    public function index(Request $request)
    {
        $query = Violation::query();

        $timeRange = $request->query('time_range', 'all');
        $now = Carbon::now();

        switch ($timeRange) {
            case 'harian':
                $query->whereDate('detection_time', $now->toDateString());
                break;
            case 'mingguan':
                $query->where('detection_time', '>=', $now->subWeek());
                break;
            case 'bulanan':
                $query->where('detection_time', '>=', $now->subMonth());
                break;
        }

        $violations = $query->latest()->get();
        return response()->json($violations);
    }

    /**
     * Ambil statistik pelanggaran
     */
    public function stats(Request $request)
    {
        $timeRange = $request->query('time_range', 'harian');
        $now = Carbon::now();

        $query = Violation::query();

        switch ($timeRange) {
            case 'harian':
                $query->whereDate('detection_time', $now->toDateString());
                break;
            case 'mingguan':
                $query->where('detection_time', '>=', $now->subWeek());
                break;
            case 'bulanan':
                $query->where('detection_time', '>=', $now->subMonth());
                break;
        }

        $violations = $query->get()->groupBy(function ($item) {
            return $item->detection_time->toDateString();
        });

        $stats = [];
        foreach ($violations as $date => $items) {
            $stats[] = [
                'date' => $date,
                'count' => $items->count(),
                'unique_plates' => $items->pluck('plate_number')->unique()->values()->all()
            ];
        }

        return response()->json(['stats' => $stats]);
    }

    /**
     * Tampilkan halaman statistik
     */
    public function showStats(Request $request)
    {
        $timeRange = $request->query('time_range', 'harian');
        try {
            $response = Http::timeout(5)->get("http://localhost:5000/api/stats?time_range={$timeRange}");
            $stats = $response->json()['stats'] ?? [];
        } catch (\Exception $e) {
            Log::error('Gagal koneksi ke Flask: ' . $e->getMessage()); // Menggunakan Log facade
            $stats = []; // Fallback jika koneksi gagal
        }

        return view('statistik', ['stats' => $stats]);
    }
}