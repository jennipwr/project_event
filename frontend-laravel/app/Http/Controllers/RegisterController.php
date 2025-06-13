<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Validator;

class RegisterController extends Controller
{
    public function index() {
        return view('register');
    }

    public function store(Request $request)
    {
        // Validasi input
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255',
            'password' => 'required|string|min:8|confirmed',
            'password_confirmation' => 'required'
        ]);

        if ($validator->fails()) {
            return back()
                ->withErrors($validator)
                ->withInput();
        }

        try {
            // Kirim data ke backend Node.js
            $response = Http::post(env('NODEJS_API_URL') . '/api/register', [
                'name' => $request->name,
                'email' => $request->email,
                'password' => $request->password,
                'role_id' => 1 // Default role untuk member, sesuaikan dengan kebutuhan
            ]);

            if ($response->successful()) {
                $data = $response->json();
                
                return redirect()->route('login.form')
                    ->with('success', 'Registration successful! Please login with your credentials.');
            } else {
                $errorData = $response->json();
                return back()
                    ->with('error', $errorData['message'] ?? 'Registration failed. Please try again.')
                    ->withInput();
            }

        } catch (\Exception $e) {
            return back()
                ->with('error', 'Connection error. Please try again later.')
                ->withInput();
        }
    }
}
