<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class AdminAkunController extends Controller
{
    public function dashboard()
    {
        return view('admin.dashboard');
    }

    public function index()
    {
        try {
            $response = Http::timeout(30)->get(env('NODEJS_API_URL') . '/api/pengguna/panitia-dan-keuangan');
            
            if ($response->successful()) {
                $responseData = $response->json();
                $users = $responseData['data'] ?? [];
                
                Log::info('API Response:', ['data' => $responseData]);
                
                return view('admin.pengelolaan-akun', compact('users'));
            } else {
                Log::error('API Error:', [
                    'status' => $response->status(),
                    'body' => $response->body()
                ]);
                
                return view('admin.pengelolaan-akun', ['users' => []])
                    ->with('error', 'Gagal mengambil data dari server');
            }
        } catch (\Exception $e) {
            Log::error('Exception in AdminAkunController@index:', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return view('admin.pengelolaan-akun', ['users' => []])
                ->with('error', 'Terjadi kesalahan saat mengambil data: ' . $e->getMessage());
        }
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255'
        ]);

        try {
            $response = Http::timeout(30)->put(env('NODEJS_API_URL') . "/api/pengguna/$id", [
                'name' => $request->name,
                'email' => $request->email
            ]);

            if ($response->successful()) {
                $responseData = $response->json();
                return redirect()->back()->with('success', $responseData['message'] ?? 'Data berhasil diperbarui');
            } else {
                Log::error('Update API Error:', [
                    'status' => $response->status(),
                    'body' => $response->body()
                ]);
                
                $errorMessage = $response->json()['message'] ?? 'Terjadi kesalahan saat update data';
                return redirect()->back()->with('error', $errorMessage);
            }
        } catch (\Exception $e) {
            Log::error('Exception in AdminAkunController@update:', [
                'message' => $e->getMessage(),
                'id' => $id,
                'request_data' => $request->all()
            ]);
            
            return redirect()->back()->with('error', 'Terjadi kesalahan saat update data: ' . $e->getMessage());
        }
    }

    public function toggleStatus(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|in:aktif,nonaktif'
        ]);

        try {
            $response = Http::timeout(30)->patch(env('NODEJS_API_URL') . "/api/pengguna/$id/status", [
                'status' => $request->status
            ]);

            if ($response->successful()) {
                $responseData = $response->json();
                return redirect()->back()->with('success', $responseData['message'] ?? 'Status berhasil diubah');
            } else {
                Log::error('Toggle Status API Error:', [
                    'status' => $response->status(),
                    'body' => $response->body()
                ]);
                
                $errorMessage = $response->json()['message'] ?? 'Terjadi kesalahan saat mengubah status';
                return redirect()->back()->with('error', $errorMessage);
            }
        } catch (\Exception $e) {
            Log::error('Exception in AdminAkunController@toggleStatus:', [
                'message' => $e->getMessage(),
                'id' => $id,
                'status' => $request->status
            ]);
            
            return redirect()->back()->with('error', 'Terjadi kesalahan saat mengubah status: ' . $e->getMessage());
        }
    }

    public function destroy($id)
    {
        try {
            $response = Http::timeout(30)->delete(env('NODEJS_API_URL') . "/api/pengguna/$id");

            if ($response->successful()) {
                $responseData = $response->json();
                return redirect()->back()->with('success', $responseData['message'] ?? 'Akun berhasil dihapus');
            } else {
                Log::error('Delete API Error:', [
                    'status' => $response->status(),
                    'body' => $response->body()
                ]);
                
                $errorMessage = $response->json()['message'] ?? 'Terjadi kesalahan saat menghapus akun';
                return redirect()->back()->with('error', $errorMessage);
            }
        } catch (\Exception $e) {
            Log::error('Exception in AdminAkunController@destroy:', [
                'message' => $e->getMessage(),
                'id' => $id
            ]);
            
            return redirect()->back()->with('error', 'Terjadi kesalahan saat menghapus akun: ' . $e->getMessage());
        }
    }
}