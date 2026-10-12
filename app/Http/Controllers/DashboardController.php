<?php

namespace App\Http\Controllers;

use App\Models\Blast;
use App\Models\BlastMessage;
use App\Models\Student;

class DashboardController extends Controller
{
    public function index()
    {
        $monthStart = now()->startOfMonth();

        return view('dashboard', [
            'sentThisMonth' => BlastMessage::where('status', 'sent')->where('sent_at', '>=', $monthStart)->count(),
            'failedThisMonth' => BlastMessage::where('status', 'failed')->where('updated_at', '>=', $monthStart)->count(),
            'totalStudents' => Student::count(),
            'recent' => Blast::latest()->take(5)->get(),
        ]);
    }
}
