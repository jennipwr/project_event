<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class DetailController extends Controller
{
    private $nodeApiUrl;

    public function __construct()
    {
        $this->nodeApiUrl = config('app.node_api_url', 'http://localhost:3000');
    }

    public function show($eventId)
    {
        try {
            // Log the API call for debugging
            Log::info('=== Laravel: Fetching event data ===');
            Log::info('Event ID: ' . $eventId);
            Log::info('API URL: ' . $this->nodeApiUrl . '/api/events/' . $eventId);

            // Get event detail from Node.js API with timeout
            $response = Http::timeout(30)
                ->acceptJson()
                ->get($this->nodeApiUrl . '/api/detail/' . $eventId);
            
            // Log the response for debugging
            Log::info('API Response Status: ' . $response->status());
            Log::info('API Response Headers: ' . json_encode($response->headers()));
            Log::info('API Response Body: ' . $response->body());

            // Check if request was successful
            if (!$response->successful()) {
                Log::error('API request failed with status: ' . $response->status());
                Log::error('Response body: ' . $response->body());
                
                if ($response->status() === 404) {
                    abort(404, 'Event tidak ditemukan');
                } else {
                    abort(500, 'Tidak dapat mengambil data event dari server');
                }
            }

            // Parse JSON response
            $responseData = $response->json();
            
            // Log parsed response
            Log::info('Parsed response data: ' . json_encode($responseData));

            // Validate response structure
            if (!$responseData) {
                Log::error('Empty response from API');
                abort(404, 'Event tidak ditemukan');
            }

            if (!is_array($responseData)) {
                Log::error('Invalid response format from API - not an array');
                abort(500, 'Format respons API tidak valid');
            }
            
            // Check for success status in response
            if (isset($responseData['success']) && $responseData['success'] === false) {
                Log::error('API returned error: ' . ($responseData['message'] ?? 'Unknown error'));
                abort(404, $responseData['message'] ?? 'Event tidak ditemukan');
            }

            // Check if event data exists in response
            if (!isset($responseData['event'])) {
                Log::error('Event key not found in API response');
                Log::error('Available keys: ' . implode(', ', array_keys($responseData)));
                abort(404, 'Data event tidak lengkap');
            }

            // Validate required event fields
            $requiredFields = ['id_event', 'nama_event'];
            foreach ($requiredFields as $field) {
                if (!isset($responseData['event'][$field])) {
                    Log::error("Required field '{$field}' missing from event data");
                    abort(500, 'Data event tidak lengkap');
                }
            }

            // Process and format the event data
            $eventData = $this->processEventData($responseData);

            Log::info('Successfully processed event data for ID: ' . $eventId);
            Log::info('Final eventData structure: ' . json_encode(array_keys($eventData)));

            return view('event.detail', compact('eventData'));
            
        } catch (\Illuminate\Http\Client\ConnectionException $e) {
            Log::error('Connection Exception: ' . $e->getMessage());
            abort(500, 'Tidak dapat terhubung ke server API');
        } catch (\Illuminate\Http\Client\RequestException $e) {
            Log::error('HTTP Request Exception: ' . $e->getMessage());
            abort(500, 'Gagal mengambil data dari server');
        } catch (\Exception $e) {
            Log::error('General Exception in DetailController: ' . $e->getMessage());
            Log::error('Stack trace: ' . $e->getTraceAsString());
            abort(500, 'Terjadi kesalahan saat mengambil data event');
        }
    }

    /**
     * Process and format event data
     */
    private function processEventData($responseData)
    {
        $eventData = $responseData;

        // Ensure sessions array exists
        if (!isset($eventData['event']['sessions'])) {
            $eventData['event']['sessions'] = [];
        }

        // Ensure organizer name exists
        if (!isset($eventData['event']['organizer_name']) || empty($eventData['event']['organizer_name'])) {
            $eventData['event']['organizer_name'] = 'Tidak diketahui';
        }

        // Ensure date_range exists
        if (!isset($eventData['event']['date_range']) || empty($eventData['event']['date_range'])) {
            $eventData['event']['date_range'] = $this->formatDateRange($eventData['event']);
        }

        // Ensure price_range exists
        if (!isset($eventData['event']['price_range']) || empty($eventData['event']['price_range'])) {
            $eventData['event']['price_range'] = $this->formatPriceRange($eventData['event']);
        }

        // Fix poster URL if it has backslashes
        // if (isset($eventData['event']['poster_url']) && $eventData['event']['poster_url']) {
        //     $eventData['event']['poster_url'] = str_replace('\\', '/', $eventData['event']['poster_url']);
        // }

        return $eventData;
    }

    /**
     * Format date range from event data (fallback)
     */
    private function formatDateRange($event)
    {
        try {
            if (isset($event['sessions']) && count($event['sessions']) > 0) {
                $sessionDates = collect($event['sessions'])->pluck('tanggal_sesi')->unique()->sort();
                if ($sessionDates->count() === 1) {
                    return \Carbon\Carbon::parse($sessionDates->first())->format('d F Y');
                } else {
                    $firstDate = \Carbon\Carbon::parse($sessionDates->first())->format('d F Y');
                    $lastDate = \Carbon\Carbon::parse($sessionDates->last())->format('d F Y');
                    return $firstDate . ' - ' . $lastDate;
                }
            }

            return 'Tanggal belum ditentukan';
        } catch (\Exception $e) {
            Log::error('Error formatting date range: ' . $e->getMessage());
            return 'Tanggal belum ditentukan';
        }
    }

    /**
     * Format price range from event data (fallback)
     */
    private function formatPriceRange($event)
    {
        try {
            if (isset($event['sessions']) && count($event['sessions']) > 0) {
                $prices = collect($event['sessions'])->pluck('biaya_sesi')->filter()->map(function($price) {
                    return (int) $price;
                });
                
                if ($prices->isEmpty()) {
                    return 'Gratis';
                }

                $minPrice = $prices->min();
                $maxPrice = $prices->max();

                if ($minPrice == 0 && $maxPrice == 0) {
                    return 'Gratis';
                } elseif ($minPrice == 0) {
                    return 'Gratis - Rp ' . number_format($maxPrice, 0, ',', '.');
                } elseif ($minPrice == $maxPrice) {
                    return 'Rp ' . number_format($minPrice, 0, ',', '.');
                } else {
                    return 'Rp ' . number_format($minPrice, 0, ',', '.') . ' - Rp ' . number_format($maxPrice, 0, ',', '.');
                }
            }

            return 'Harga belum ditentukan';
        } catch (\Exception $e) {
            Log::error('Error formatting price range: ' . $e->getMessage());
            return 'Harga belum ditentukan';
        }
    }
}