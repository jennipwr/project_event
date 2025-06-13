<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class PanitiaController extends Controller
{
    public function index()
    {
        $user = session('user');
        return view('panitia.dashboard', compact('user'));
    }
    
    public function tampil() {
        return view('panitia.daftar-event');
    }
}
