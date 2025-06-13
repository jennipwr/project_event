<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class MemberController extends Controller
{
    public function index()
    {
        $user = session('user');
        return view('member.dashboard', compact('user'));
    }
}
