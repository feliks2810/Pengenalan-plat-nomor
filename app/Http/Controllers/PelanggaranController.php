<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Violation;

class PelanggaranController extends Controller
{
    public function index()
    {
        $pelanggaran = Violation::all();
        return view('pelanggaran', compact('pelanggaran'));
    }

    public function show($id)
    {
        $pelanggaran = Violation::findOrFail($id);
        return view('pelanggaran', compact('pelanggaran'));
    }
}