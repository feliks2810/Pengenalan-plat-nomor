<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Violation;
use Illuminate\Support\Facades\Storage;

class ViolationController extends Controller
{
    public function index()
    {
        $violations = Violation::latest()->get();
        return response()->json($violations);
    }

    public function recent()
    {
        $recentViolation = Violation::latest()->first();
        return response()->json($recentViolation ? [$recentViolation] : []);
    }

    public function stats()
    {
        $rate = Violation::where('created_at', '>=', now()->subHour())->count();
        return response()->json(['rate' => $rate]);
    }

    public function showStats()
    {
        $stats = Violation::selectRaw('count(*) as total, DATE(created_at) as date')
                        ->groupBy('date')
                        ->get();
        return view('statistik', compact('stats'));
    }

    public function store(Request $request)
{
    $data = $request->validate([
        'id' => 'required|string|unique:violations,id',
        'timestamp' => 'required|date',
        'plateNumber' => 'required|string',
        'plateConfidence' => 'required|numeric',
        'violationType' => 'required|string',
        'helmConfidence' => 'required|numeric',
        'imageFile' => 'nullable|string',
        'created_at' => 'required|date',
    ]);

    if ($request->has('imageFile')) {
        $imagePath = 'violations/' . $data['imageFile'];
        Storage::put($imagePath, file_get_contents('http://localhost:5000/api/images/' . $data['imageFile']));
        $data['image_path'] = $imagePath; // Simpan path relatif
    }

    $violation = Violation::create($data);

    return response()->json(['status' => 'success', 'id' => $violation->id], 201);
}

}