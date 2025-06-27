<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class LoginController extends Controller
{
    public function login(Request $request)
    {
        // Validasi input
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required|string|min:6',
        ]);

        try {
            // Kirim request ke backend Node.js
            $response = Http::timeout(60)->post('http://localhost:3000/api/login', [
                'email' => $credentials['email'],
                'password' => $credentials['password'],
            ]);

            // Handle response
            if ($response->successful()) {
                $userData = $response->json();
                
                // Validasi struktur data user
                if (!isset($userData['token']) || !isset($userData['role']) || !isset($userData['name']) || !isset($userData['email'])  || !isset($userData['id'])) {
                    Log::error('Invalid user data structure from Node.js', ['response' => $userData]);
                    return redirect()->back()->with('error', 'Invalid server response format');
                }

                $user = [
                    'id' => $userData['id'],
                    'role' => $userData['role'],
                    'name' => $userData['name'],
                    'email' => $userData['email'],
                    'status' => $userData['status'],
                    'role_id_role' => $userData['role_id_role'] ?? null
                ];
                $token = $userData['token'];

                // Simpan data ke session
                session([
                    'user' => $user,
                    'jwt_token' => $token, // Simpan token JWT untuk request API selanjutnya
                ]);

                // Redirect berdasarkan role dengan default fallback
                return $this->handleRoleRedirect($user['role'] ?? 'member');

            } else {
                // Handle error response dari Node.js
                $errorMessage = $response->json()['message'] ?? 'Login failed';
                Log::warning('Login failed', [
                    'status' => $response->status(),
                    'error' => $errorMessage,
                    'email' => $credentials['email']
                ]);
                
                return redirect()->back()
                    ->withInput()
                    ->with('error', $this->getFriendlyErrorMessage($errorMessage));
            }

        } catch (\Exception $e) {
            Log::error('Login process error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return redirect()->back()
                ->withInput()
                ->with('error', 'Terjadi kesalahan sistem. Silakan coba lagi.');
        }
    }

    public function logout(Request $request)
    {
        try {
            $token = $request->session()->get('jwt_token');
            if ($token) {
                Http::withToken($token)
                    ->post('http://localhost:3000/api/logout');
            }

            // Bersihkan session lokal
            $request->session()->flush();
            
            return redirect('/login')->with('success', 'Anda telah berhasil logout!');
            
        } catch (\Exception $e) {
            Log::error('Logout error', ['error' => $e->getMessage()]);
            return redirect('/login')->with('error', 'Logout gagal. Silakan coba lagi.');
        }
    }

    /**
     * Handle redirect berdasarkan role user
     */
    protected function handleRoleRedirect(string $role)
    {
        $redirectMap = [
            'admin' => '/admin',
            'keuangan' => '/keuangan',
            'panitia' => '/panitia',
            'member' => '/',
        ];

        return redirect($redirectMap[strtolower($role)] ?? '/dashboard');
    }

    /**
     * Ubah pesan error teknis menjadi pesan yang ramah pengguna
     */
    protected function getFriendlyErrorMessage(string $error): string
    {
        $messages = [
            'User tidak ditemukan' => 'Email tidak terdaftar',
            'Password salah' => 'Password yang Anda masukkan salah',
            'Email dan password wajib diisi.' => 'Harap isi email dan password',
        ];

        return $messages[$error] ?? 'Email atau password salah';
    }
}