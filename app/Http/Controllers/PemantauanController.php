<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Violation;

class PemantauanController extends Controller
{
    /**
     * Display the real-time monitoring page.
     */
    public function index()
    {
        return view('pemantauan');
    }

    /**
     * Display the history of violations.
     */
    public function history()
    {
        $violations = Violation::latest()->paginate(10);
        return view('pemantauan', compact('violations'));
    }
}