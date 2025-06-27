<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ScanController extends Controller
{
    private $nodeApiUrl;

    public function __construct()
    {
        $this->nodeApiUrl = env('NODE_API_URL', 'http://localhost:3000');
    }

    
    public function index()
    {
        $user = session('user');
        return view('panitia.scan', compact('user'));
    }

    public function scanQrCode(Request $request)
    {
        try {
            $response = Http::post('http://localhost:3000/api/panitia/scan/qrcode', [
                'qrcode' => $request->qrcode,
                'event_id' => $request->event_id,
                'event_sesi_id' => $request->event_sesi_id,
            ]);

            if ($response->successful()) {
                return response()->json($response->json());
            } else {
                Log::error('Scan Error:', [
                    'status' => $response->status(),
                    'body' => $response->body()
                ]);

                return response()->json([
                    'success' => false,
                    'message' => 'Gagal memproses scan'
                ], $response->status());
            }
        } catch (\Exception $e) {
            Log::error('Exception saat scan QR:', ['error' => $e->getMessage()]);
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan sistem'
            ], 500);
        }
    }

    public function getEventsByUser()
    {
        try {
            $userId = session('user_id') ?? session('user')['id'] ?? null;
            
            Log::info('Getting events for user ID: ' . $userId);
            Log::info('Session data: ' . json_encode(session()->all()));
            
            if (!$userId) {
                return response()->json([
                    'success' => false,
                    'message' => 'User tidak terautentikasi',
                    'debug' => [
                        'session_user_id' => session('user_id'),
                        'session_user' => session('user'),
                        'all_session' => session()->all()
                    ]
                ], 401);
            }

            $url = $this->nodeApiUrl . "/api/panitia/scan/events/{$userId}";
            Log::info('Calling Node.js API: ' . $url);
            
            $response = Http::get($url);
            
            Log::info('Node.js response status: ' . $response->status());
            Log::info('Node.js response body: ' . $response->body());
            
            if ($response->successful()) {
                return response()->json($response->json());
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Gagal mengambil data events',
                    'debug' => [
                        'node_status' => $response->status(),
                        'node_response' => $response->body()
                    ]
                ], $response->status());
            }

        } catch (\Exception $e) {
            Log::error('Get Events By User Error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat mengambil data events',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function getEventSessions($eventId)
    {
        try {
            $response = Http::get($this->nodeApiUrl . "/api/panitia/scan/sessions/{$eventId}");
            
            if ($response->successful()) {
                return response()->json($response->json());
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Gagal mengambil data sesi event'
                ], $response->status());
            }

        } catch (\Exception $e) {
            Log::error('Get Event Sessions Error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat mengambil data sesi'
            ], 500);
        }
    }
}