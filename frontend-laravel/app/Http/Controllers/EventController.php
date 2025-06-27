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
                    )->post('http://localhost:3000/api/event', $data);
                } else {
                    return redirect()->route('panitia.addEvent')->with('error', 'File poster tidak valid!');
                }
            } else {
                $response = Http::post('http://localhost:3000/api/event', $data);
            }

            if ($response->successful()) {
                return redirect()->route('panitia.listEvents')->with('success', 'Event berhasil disimpan!');
            } else {
                \Log::error('API Error: ' . $response->body());
                return redirect()->route('panitia.addEvent')->with('error', 'Gagal menyimpan event: ' . $response->status());
            }

        } catch (\Exception $e) {
            \Log::error('Exception in submitEvent: ' . $e->getMessage());
            return redirect()->route('panitia.addEvent')->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

        public function listEvents() {
        $user = session('user');
        
        try {
            $response = Http::get('http://localhost:3000/api/events/user/' . $user['id']);
            
            if ($response->successful()) {
            $eventsData = $response->json();

            $events = collect($eventsData)
                ->flatten(1)
                ->filter(function ($event) {
                    return is_array($event) && isset($event['nama_event']);
                })
                ->map(function ($event) {
                    $stats = $this->getEventStats($event['id_event']);
                    if ($stats) {
                        $event['total_peserta'] = $stats['total_peserta'] ?? 0;
                        $event['total_kapasitas'] = $stats['total_kapasitas'] ?? 0;
                        $event['sisa_kapasitas'] = $stats['sisa_kapasitas'] ?? 0;
                    } else {
                        $event['total_peserta'] = 0;
                        $event['total_kapasitas'] = $event['total_sesi'] ?? 0;
                        $event['sisa_kapasitas'] = $event['total_sesi'] ?? 0;
                    }
                    return $event;
                })
                ->values()
                ->toArray();
            
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
        try {
            $nodeUrl = "http://localhost:3000/api/panitia/events/{$id}/detail";
            
            $response = Http::get($nodeUrl);

            if ($response->successful()) {
                $eventData = $response->json();
                
                $stats = $this->getEventStats($id);
                if ($stats) {
                    $eventData['total_peserta'] = $stats['total_peserta'] ?? 0;
                    $eventData['total_kapasitas'] = $stats['total_kapasitas'] ?? 0;
                    $eventData['sisa_kapasitas'] = $stats['sisa_kapasitas'] ?? 0;
                } else {
                    $totalPeserta = 0;
                    $totalKapasitas = 0;
                    
                    if (isset($eventData['sessions']) && is_array($eventData['sessions'])) {
                        foreach ($eventData['sessions'] as $session) {
                            $totalPeserta += $session['peserta_terdaftar'] ?? 0;
                            $totalKapasitas += $session['jumlah_peserta'] ?? 0;
                        }
                    }
                    
                    $eventData['total_peserta'] = $totalPeserta;
                    $eventData['total_kapasitas'] = $totalKapasitas;
                    $eventData['sisa_kapasitas'] = $totalKapasitas - $totalPeserta;
                }
                
                return response()->json($eventData);
            }

            return response()->json([
                'error' => true,
                'message' => 'Gagal mengambil data event dari backend'
            ], $response->status());
            
        } catch (\Exception $e) {
            \Log::error('Exception in detail: ' . $e->getMessage());
            return response()->json([
                'error' => true,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ], 500);
        }
    }

    public function deleteEvent($id) {
        $user = session('user');
        
        try {
            $response = Http::delete('http://localhost:3000/api/event/' . $id, [
                'pengguna_id' => $user['id'] 
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

    public function getEventStats($eventId) {
        try {
            $response = Http::get("http://localhost:3000/api/event/{$eventId}/stats");
            
            if ($response->successful()) {
                return $response->json();
            }
            
            return null;
        } catch (\Exception $e) {
            \Log::error('Exception in getEventStats: ' . $e->getMessage());
            return null;
        }
    }

    public function getDashboardData() 
    {
        $user = session('user');
        
        if (!$user || !isset($user['id'])) {
            return response()->json(['error' => 'User tidak ditemukan'], 401);
        }
        
        try {
            $response = Http::get('http://localhost:3000/api/events/user/' . $user['id']);
            
            if (!$response->successful()) {
                return response()->json(['error' => 'Gagal mengambil data event'], $response->status());
            }
            
            $eventsData = $response->json();
            
            $events = collect($eventsData)
                ->flatten(1)
                ->filter(function ($event) {
                    return is_array($event) && isset($event['nama_event']);
                })
                ->map(function ($event) {
                    $stats = $this->getEventStats($event['id_event']);
                    
                    if ($stats) {
                        $event['total_peserta'] = $stats['total_peserta'] ?? 0;
                        $event['total_kapasitas'] = $stats['total_kapasitas'] ?? 0;
                        $event['sisa_kapasitas'] = $stats['sisa_kapasitas'] ?? 0;
                    } else {
                        $event['total_peserta'] = 0;
                        $event['total_kapasitas'] = $this->calculateTotalCapacity($event);
                        $event['sisa_kapasitas'] = $event['total_kapasitas'];
                    }
                    
                    $event['total_sesi'] = $event['total_sesi'] ?? 0;
                    
                    return $event;
                })
                ->values()
                ->toArray();
            
            return response()->json($events);
            
        } catch (\Exception $e) {
            \Log::error('Exception in getDashboardData: ' . $e->getMessage());
            return response()->json([
                'error' => 'Terjadi kesalahan: ' . $e->getMessage()
            ], 500);
        }
    }

    private function calculateTotalCapacity($event) {
        $totalKapasitas = 0;
        
        if (isset($event['sessions']) && is_array($event['sessions'])) {
            foreach ($event['sessions'] as $session) {
                $totalKapasitas += $session['jumlah_peserta'] ?? 0;
            }
        } else {
            $totalKapasitas = ($event['total_sesi'] ?? 1) * 50; 
        }
        
        return $totalKapasitas;
    }
}