<?php

namespace App\Http\Controllers;

use Illuminate\View\View;

class MahasiswaDashboardController extends Controller
{
    public function index(): View
    {
        return view('dashboard.mahasiswa');
    }
}
