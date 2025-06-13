<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class AdminController extends Controller
{
    public function index()
    {
        $user = session('user');
        return view('admin.dashboard', compact('user'));
    }
}
