<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SertifikatController extends Controller
{
    private $nodeApiUrl;

    public function __construct()
    {
        $this->nodeApiUrl = env('NODE_API_URL', 'http://localhost:3000');
    }

    public function index()
    {
        $user = session('user');
        return view('panitia.sertifikat', compact('user'));
    }

    public function getPesertaByEventSession($eventId, $sesiId)
    {
        try {
            $url = $this->nodeApiUrl . "/api/sertifikat/peserta/{$eventId}/{$sesiId}";
            $response = Http::get($url);

            if ($response->successful()) {
                return response()->json($response->json());
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Gagal mengambil peserta'
                ], $response->status());
            }
        } catch (\Exception $e) {
            Log::error('Get Peserta Error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan sistem'
            ], 500);
        }
    }

    public function upload(Request $request)
    {
        try {
            $request->validate([
                'file' => 'required|file|mimes:pdf,jpg,jpeg,png|max:2048',
                'kehadiran_id' => 'required',
                'registrasi_id_registrasi' => 'required',
                'registrasi_pengguna_id' => 'required',
                'registrasi_event_id_event' => 'required',
                'registrasi_event_sesi_id_sesi' => 'required',
            ]);

            $file = $request->file('file');
            $response = Http::attach(
                'file',
                file_get_contents($file->getRealPath()),
                $file->getClientOriginalName()
            )->post($this->nodeApiUrl . '/api/sertifikat/upload', [
                'kehadiran_id' => $request->kehadiran_id,
                'registrasi_id_registrasi' => $request->registrasi_id_registrasi,
                'registrasi_pengguna_id' => $request->registrasi_pengguna_id,
                'registrasi_event_id_event' => $request->registrasi_event_id_event,
                'registrasi_event_sesi_id_sesi' => $request->registrasi_event_sesi_id_sesi
            ]);

            if ($response->successful()) {
                return response()->json($response->json());
            } else {
                Log::error('Upload gagal: ' . $response->body()); 
                return response()->json([
                    'success' => false,
                    'message' => 'Upload gagal'
                ], $response->status());
            }

        } catch (\Exception $e) {
            Log::error('Upload Sertifikat Error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat upload'
            ], 500);
        }
    }

    public function viewSertifikat($filename)
    {
        try {
            $url = $this->nodeApiUrl . "/api/sertifikat/view/{$filename}";
            $response = Http::get($url);

            if ($response->successful()) {
                $fileContent = $response->body();
                $contentType = $response->header('Content-Type') ?: 'application/octet-stream';
                
                return response($fileContent)
                    ->header('Content-Type', $contentType)
                    ->header('Content-Disposition', 'inline; filename="' . $filename . '"');
            } else {
                abort(404, 'Sertifikat tidak ditemukan');
            }
        } catch (\Exception $e) {
            Log::error('View Sertifikat Error: ' . $e->getMessage());
            abort(404, 'Sertifikat tidak ditemukan');
        }
    }

    public function downloadSertifikat($filename)
    {
        try {
            $url = $this->nodeApiUrl . "/api/sertifikat/download/{$filename}";
            $response = Http::get($url);

            if ($response->successful()) {
                $fileContent = $response->body();
                $contentType = $response->header('Content-Type') ?: 'application/octet-stream';
                
                return response($fileContent)
                    ->header('Content-Type', $contentType)
                    ->header('Content-Disposition', 'attachment; filename="' . $filename . '"');
            } else {
                abort(404, 'Sertifikat tidak ditemukan');
            }
        } catch (\Exception $e) {
            Log::error('Download Sertifikat Error: ' . $e->getMessage());
            abort(404, 'Sertifikat tidak ditemukan');
        }
    }
}
