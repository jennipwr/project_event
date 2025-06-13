<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class GuestController extends Controller
{
    /**
     * Tampilkan halaman home dengan daftar event
     */
    public function index()
    {
        return view('home');
    }

    /**
     * API endpoint untuk mendapatkan semua event via Node.js
     */
    public function getAllEvents()
    {
        try {
            $nodeUrl = "http://localhost:3000/api/events/all";
            $response = Http::timeout(30)->get($nodeUrl);

            if ($response->successful()) {
                $data = $response->json();
                
                // Log untuk debugging
                Log::info('Events data fetched successfully', [
                    'count' => isset($data['events']) ? count($data['events']) : 0,
                    'has_error' => $data['error'] ?? false
                ]);

                return response()->json($data);
            } else {
                Log::error('Failed to fetch events from Node.js API', [
                    'status' => $response->status(),
                    'body' => $response->body()
                ]);
                
                return response()->json([
                    'error' => true,
                    'message' => 'Gagal mengambil data event dari server'
                ], 500);
            }
        } catch (\Exception $e) {
            Log::error('Error calling Node.js API for events', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'error' => true,
                'message' => 'Terjadi kesalahan saat mengambil data event',
                'details' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
        
    }

    /**
     * API endpoint untuk mendapatkan detail event via Node.js
     */
    public function getEventDetail($eventId)
    {
        try {
            if (!is_numeric($eventId)) {
                return response()->json([
                    'error' => true,
                    'message' => 'ID event tidak valid'
                ], 400);
            }

            $nodeUrl = "http://localhost:3000/api/events/{$eventId}";
            $response = Http::timeout(30)->get($nodeUrl);

            if ($response->successful()) {
                $data = $response->json();
                
                Log::info('Event detail fetched successfully', [
                    'eventId' => $eventId,
                    'has_error' => $data['error'] ?? false
                ]);

                return response()->json($data);
            } else {
                Log::error('Failed to fetch event detail from Node.js API', [
                    'eventId' => $eventId,
                    'status' => $response->status(),
                    'body' => $response->body()
                ]);
                
                return response()->json([
                    'error' => true,
                    'message' => 'Event tidak ditemukan'
                ], 404);
            }
        } catch (\Exception $e) {
            Log::error('Error calling Node.js API for event detail', [
                'eventId' => $eventId,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'error' => true,
                'message' => 'Terjadi kesalahan saat mengambil detail event',
                'details' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    /**
     * API endpoint untuk pencarian event via Node.js
     */
    public function searchEvents(Request $request)
    {
        try {
            $query = $request->get('q');
            
            if (!$query || trim($query) === '') {
                return response()->json([
                    'error' => true,
                    'message' => 'Query pencarian tidak boleh kosong'
                ], 400);
            }

            $nodeUrl = "http://localhost:3000/api/events/search?q=" . urlencode($query);
            $response = Http::timeout(30)->get($nodeUrl);

            if ($response->successful()) {
                $data = $response->json();
                
                Log::info('Event search completed', [
                    'query' => $query,
                    'results_count' => isset($data['events']) ? count($data['events']) : 0
                ]);

                return response()->json($data);
            } else {
                Log::error('Failed to search events from Node.js API', [
                    'query' => $query,
                    'status' => $response->status(),
                    'body' => $response->body()
                ]);
                
                return response()->json([
                    'error' => true,
                    'message' => 'Gagal melakukan pencarian event'
                ], 500);
            }
        } catch (\Exception $e) {
            Log::error('Error calling Node.js API for event search', [
                'query' => $request->get('q'),
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'error' => true,
                'message' => 'Terjadi kesalahan saat mencari event',
                'details' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    /**
     * Tampilkan halaman detail event
     */
    public function showEventDetail($eventId)
    {
        return view('event.detail', compact('eventId'));
    }

    /**
     * Method untuk testing - mendapatkan data langsung untuk debugging
     */
    public function testConnection()
    {
        try {
            $nodeUrl = "http://localhost:3000/api/events/all";
            $response = Http::timeout(10)->get($nodeUrl);
            
            return response()->json([
                'status' => $response->status(),
                'successful' => $response->successful(),
                'data' => $response->json(),
                'node_url' => $nodeUrl
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => true,
                'message' => $e->getMessage(),
                'node_url' => $nodeUrl ?? 'Not set'
            ]);
        }
    }
}