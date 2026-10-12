<?php

namespace App\Http\Controllers;

class DashboardController extends Controller
{
    public function index()
    {
        // Kartu statistik dan riwayat blast ditambahkan di tahap berikutnya
        return view('dashboard');
    }
}
