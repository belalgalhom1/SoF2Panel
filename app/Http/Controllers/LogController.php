<?php

namespace App\Http\Controllers;

use App\Models\Log;
use Illuminate\Http\Request;

class LogController extends Controller
{
    public function index()
    {
        $logs = Log::with('user')->latest()->paginate(50);
        return view('logs.index', compact('logs'));
    }
}
