<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;

class EventController extends Controller
{
    public function addEvent() {
        $user = session('user');
        return view('panitia.add', compact('user'));
    }

    public function submitEvent(Request $request) {
        $user = session('user');
        $request->validate([
            'nama_event' => 'required|string|max:255',
            'deskripsi' => 'required|string',
            'syarat_ketentuan' => 'required|string',
            'poster' => 'nullable|image|max:2048',
            'sessions' => 'required|array|min:1',
            'sessions.*.nama_sesi' => 'required|string',
            'sessions.*.narasumber_sesi' => 'required|string',
            'sessions.*.tanggal_sesi' => 'required|date',
            'sessions.*.waktu_sesi' => 'required',
            'sessions.*.jumlah_peserta' => 'required|integer|min:1',
            'sessions.*.lokasi_sesi' => 'required|string',
            'sessions.*.biaya_sesi' => 'required|numeric|min:0',
        ]);

        try {
            // Persiapkan data untuk dikirim
            $data = [
                'nama_event' => $request->nama_event,
                'deskripsi' => $request->deskripsi,
                'syarat_ketentuan' => $request->syarat_ketentuan,
                'sessions' => json_encode($request->sessions), // Convert array to JSON string
                'pengguna_id' => $user['id'],
            ];

            // Cek apakah ada file poster
            if ($request->hasFile('poster')) {
                $poster = $request->file('poster');
                
                // Pastikan file valid
                if ($poster->isValid()) {
                    // Kirim dengan file attachment
                    $response = Http::attach(
                        'poster', // nama field
                        file_get_contents($poster->getRealPath()), // contents file
                        $poster->getClientOriginalName() // nama file
                    )->post('http://localhost:3000/api/event', $data);
                } else {
                    return redirect()->route('panitia.addEvent')->with('error', 'File poster tidak valid!');
                }
            } else {
                // Kirim tanpa file attachment
                $response = Http::post('http://localhost:3000/api/event', $data);
            }

            if ($response->successful()) {
                return redirect()->route('panitia.listEvents')->with('success', 'Event berhasil disimpan!');
            } else {
                // Log error untuk debugging
                \Log::error('API Error: ' . $response->body());
                return redirect()->route('panitia.addEvent')->with('error', 'Gagal menyimpan event: ' . $response->status());
            }

        } catch (\Exception $e) {
            \Log::error('Exception in submitEvent: ' . $e->getMessage());
            return redirect()->route('panitia.addEvent')->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    // Method baru untuk menampilkan daftar event
        public function listEvents() {
        $user = session('user');
        
        try {
            // Panggil API untuk mendapatkan event berdasarkan pengguna_id
            $response = Http::get('http://localhost:3000/api/events/user/' . $user['id']);
            
            if ($response->successful()) {
                $eventsData = $response->json();
                
                // Flatten array structure menggunakan Laravel Collection
                $events = collect($eventsData)
                    ->flatten(1) // Flatten 1 level deep
                    ->filter(function ($event) {
                        return is_array($event) && isset($event['nama_event']);
                    })
                    ->values()
                    ->toArray();
                
                // Debug: uncomment untuk melihat struktur yang sudah di-flatten
                // dd($events);
                
                return view('panitia.list', compact('user', 'events'));
            } else {
                return view('panitia.list', compact('user'))->with('error', 'Gagal mengambil data event');
            }
        } catch (\Exception $e) {
            \Log::error('Exception in listEvents: ' . $e->getMessage());
            return view('panitia.list', compact('user'))->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

        public function detail($id)
    {
        $nodeUrl = "http://localhost:3000/api/panitia/events/{$id}/detail";
        
        $response = Http::get($nodeUrl);

        if ($response->successful()) {
            return response()->json($response->json());
        }

        return response()->json([
            'error' => true,
            'message' => 'Gagal mengambil data event dari backend'
        ], $response->status());
    }

    // Method untuk menghapus event
    public function deleteEvent($id) {
        $user = session('user');
        
        try {
            // Panggil API untuk menghapus event
            $response = Http::delete('http://localhost:3000/api/event/' . $id, [
                'pengguna_id' => $user['id'] // Kirim pengguna_id untuk validasi
            ]);
            
            if ($response->successful()) {
                return redirect()->route('panitia.listEvents')->with('success', 'Event berhasil dihapus!');
            } else {
                return redirect()->route('panitia.listEvents')->with('error', 'Gagal menghapus event');
            }
        } catch (\Exception $e) {
            \Log::error('Exception in deleteEvent: ' . $e->getMessage());
            return redirect()->route('panitia.listEvents')->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

        // Method untuk menampilkan form edit
    public function editEvent($id) {
        $user = session('user');
        
        try {
            $response = Http::get('http://localhost:3000/api/event/' . $id);
            
            if ($response->successful()) {
                $event = $response->json();
                return view('panitia.edit', compact('user', 'event'));
            } else {
                return redirect()->route('panitia.listEvents')->with('error', 'Event tidak ditemukan');
            }
        } catch (\Exception $e) {
            \Log::error('Exception in editEvent: ' . $e->getMessage());
            return redirect()->route('panitia.listEvents')->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    // Method untuk update event
    public function updateEvent(Request $request, $id) {
        $user = session('user');
        $request->validate([
                'nama_event' => 'required|string|max:255',
                'deskripsi' => 'required|string',
                'syarat_ketentuan' => 'required|string',
                'poster' => 'nullable|image|max:2048',
                'sessions' => 'required|array|min:1',
                'sessions.*.nama_sesi' => 'required|string',
                'sessions.*.narasumber_sesi' => 'required|string',
                'sessions.*.tanggal_sesi' => 'required|date',
                'sessions.*.waktu_sesi' => 'required',
                'sessions.*.jumlah_peserta' => 'required|integer|min:1',
                'sessions.*.lokasi_sesi' => 'required|string',
                'sessions.*.biaya_sesi' => 'required|numeric|min:0',
        ]);

        try {
            $data = [
                'nama_event' => $request->nama_event,
                'deskripsi' => $request->deskripsi,
                'syarat_ketentuan' => $request->syarat_ketentuan,
                'sessions' => json_encode($request->sessions),
                'pengguna_id' => $user['id'],
            ];

            if ($request->hasFile('poster')) {
                $poster = $request->file('poster');
                
                if ($poster->isValid()) {
                    $response = Http::attach(
                        'poster',
                        file_get_contents($poster->getRealPath()),
                        $poster->getClientOriginalName()
                    )->put('http://localhost:3000/api/event/' . $id, $data);
                } else {
                    return redirect()->back()->with('error', 'File poster tidak valid!');
                }
            } else {
                $response = Http::put('http://localhost:3000/api/event/' . $id, $data);
            }

            if ($response->successful()) {
                return redirect()->route('panitia.listEvents')->with('success', 'Event berhasil diupdate!');
            } else {
                \Log::error('API Error: ' . $response->body());
                return redirect()->back()->with('error', 'Gagal mengupdate event: ' . $response->status());
            }

        } catch (\Exception $e) {
            \Log::error('Exception in updateEvent: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }
}