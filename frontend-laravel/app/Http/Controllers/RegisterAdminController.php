<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Validator;

class RegisterAdminController extends Controller
{
    public function index() {
        return view('admin.register');
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name'     => 'required|string|max:255',
            'email'    => 'required|string|email|max:255',
            'password' => 'required|string|min:8|confirmed',
            'role_id_role'     => 'required|in:2,4',
        ]);

        if ($validator->fails()) {
            return back()
                ->withErrors($validator)
                ->withInput();
        }

        try {
            $response = Http::post(env('NODEJS_API_URL') . '/api/register-admin', [
                'name'     => $request->name,
                'email'    => $request->email,
                'password' => $request->password,
                'role_id_role'  => $request->role_id_role,
            ]);

            if ($response->successful()) {
                return redirect()->route('admin.akuns')
                    ->with('success', 'Akun berhasil dibuat!');
            } else {
                return back()
                    ->with('error', $response->json()['message'] ?? 'Gagal registrasi')
                    ->withInput();
            }
        } catch (\Exception $e) {
            return back()
                ->with('error', 'Koneksi ke server gagal.')
                ->withInput();
        }
    }
}
