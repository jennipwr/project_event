<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;

class RegistrasiController extends Controller
{
    private $nodeApiUrl;

    public function __construct()
    {
        $this->nodeApiUrl = config('app.node_api_url');
    }

    /**
     * Show ticket purchase form
     */
    public function showTicketForm($eventId)
    {
        // Check if user is logged in
        if (!Session::has('user')) {
            return redirect()->route('login.form')->with('error', 'Silakan login terlebih dahulu untuk membeli tiket.');
        }

        try {
            // Get the correct Node.js API URL
            $nodeApiUrl = $this->getCorrectNodeApiUrl();
            \Log::info("Using Node API URL: " . $nodeApiUrl);
            
            // Test the health check endpoint
            $healthResponse = Http::timeout(5)->get($nodeApiUrl . '/health');
            if (!$healthResponse->successful()) {
                \Log::warning("Health check failed");
            }
            
            // Test the specific endpoint we need
            $testApiUrl = $nodeApiUrl . "/api/test";
            \Log::info("Testing API endpoint: " . $testApiUrl);
            
            try {
                $testResponse = Http::timeout(10)->get($testApiUrl);
                \Log::info("API test response", [
                    'status' => $testResponse->status(),
                    'body' => $testResponse->json()
                ]);
            } catch (\Exception $e) {
                \Log::warning("API test endpoint failed: " . $e->getMessage());
            }
            
            // FIXED: Correct endpoint based on your Node.js routing
            // registerRoutes is mounted at /api, and the route is /event/:eventId
            // So the correct endpoint is /api/event/:eventId NOT /api/register/event/:eventId
            $eventApiUrl = $nodeApiUrl . "/api/event/{$eventId}";
            \Log::info("Calling event API (CORRECTED): " . $eventApiUrl);
            
            $response = Http::timeout(30)->get($eventApiUrl);
            
            \Log::info("Event API Response", [
                'status' => $response->status(),
                'headers' => $response->headers(),
                'body_preview' => substr($response->body(), 0, 200)
            ]);
            
            if ($response->status() === 404) {
                // Log available routes from error page
                $errorBody = $response->body();
                \Log::error("404 Error - Route not found", [
                    'url' => $eventApiUrl,
                    'error_body' => $errorBody
                ]);
                
                // Try alternative endpoints that might work
                $alternativeEndpoints = [
                    "/api/event/{$eventId}",
                    "/event/{$eventId}",
                    "/api/events/{$eventId}",
                    "/api/register/events/{$eventId}"
                ];
                
                foreach ($alternativeEndpoints as $endpoint) {
                    $altUrl = $nodeApiUrl . $endpoint;
                    try {
                        $altResponse = Http::timeout(5)->get($altUrl);
                        \Log::info("Alternative endpoint test: {$endpoint}", [
                            'status' => $altResponse->status(),
                            'success' => $altResponse->successful()
                        ]);
                        
                        if ($altResponse->successful()) {
                            \Log::info("Found working alternative endpoint: " . $endpoint);
                            $response = $altResponse;
                            break;
                        }
                    } catch (\Exception $e) {
                        continue;
                    }
                }
            }
            
            if (!$response->successful()) {
                return redirect()->route('home')->with('error', 'Endpoint tidak ditemukan. Periksa routing Node.js Anda.');
            }

            $responseData = $response->json();
            \Log::info("Raw response data", $responseData);
            
            // Handle different response structures
            $eventData = null;
            
            // If response has Node.js structure (with 'success' and 'data' keys)
            if (isset($responseData['success']) && isset($responseData['data'])) {
                $eventData = $responseData; // Use as-is since view expects this structure
            }
            // If response has direct structure (your old format)
            else if (isset($responseData['id_event'])) {
                // Convert to Node.js structure format for view compatibility
                $eventData = [
                    'success' => true,
                    'data' => [
                        'event' => [
                            'id_event' => $responseData['id_event'],
                            'nama_event' => $responseData['nama_event'],
                            'poster' => 'http://localhost:3000/'.$responseData['poster'],
                            'deskripsi' => $responseData['deskripsi'],
                            'syarat_ketentuan' => $responseData['syarat_ketentuan'],
                            'pengguna_id' => $responseData['pengguna_id'],
                            'nama_penyelenggara' => $responseData['nama_penyelenggara'],
                            'email_penyelenggara' => $responseData['email_penyelenggara'],
                            'price_range' => $responseData['price_range'] ?? 'Gratis'
                        ],
                        'sessions' => $responseData['sessions'] ?? []
                    ]
                ];
            }
            // If response has error flag
            else if (isset($responseData['error']) && $responseData['error'] === true) {
                return redirect()->route('home')->with('error', $responseData['message'] ?? 'Event tidak ditemukan.');
            }
            else {
                return redirect()->route('home')->with('error', 'Format respons tidak dikenali dari API.');
            }
            
            if (isset($eventData['data']['sessions'])) {
                foreach ($eventData['data']['sessions'] as &$session) {
                    // Add missing keys with default values if they don't exist
                    $session['is_free'] = $session['is_free'] ?? ($session['biaya_sesi'] == 0);
                    $session['formatted_date'] = $session['formatted_date'] ?? $this->formatDate($session['tanggal_sesi']);
                    $session['formatted_price'] = $session['formatted_price'] ?? $this->formatPrice($session['biaya_sesi']);
                    $session['jumlah_peserta'] = $session['jumlah_peserta'] ?? 0;
                }
            }
            
            $user = Session::get('user');

            return view('event.purchase', compact('eventData', 'user'));
            
        } catch (\Exception $e) {
            \Log::error("Exception in showTicketForm", [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ]);
            
            return redirect()->route('home')->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    private function formatDate($date)
    {
        if (!$date) {
            return '';
        }
        
        // If $date is already a Carbon instance
        if ($date instanceof \Carbon\Carbon) {
            return $date->format('d M Y'); // e.g., "15 Jan 2024"
        }
        
        // If $date is a string, parse it first
        try {
            return \Carbon\Carbon::parse($date)->format('d M Y');
        } catch (\Exception $e) {
            return $date; // Return original if parsing fails
        }
    }

    private function formatPrice($price)
    {
        if ($price == 0) {
            return 'Gratis';
        }
        
        return 'Rp ' . number_format($price, 0, ',', '.');
    }

    private function testNodeJsConnection($nodeApiUrl)
    {
        try {
            // Test basic connectivity
            $response = Http::timeout(5)->get($nodeApiUrl . '/health');
            
            return [
                'success' => $response->successful(),
                'status' => $response->status(),
                'body' => $response->json()
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    private function getCorrectNodeApiUrl()
    {
        // Set your correct Node.js server URL here
        $nodeApiUrl = env('NODE_API_URL', 'http://localhost:3000');
        
        // If nodeApiUrl property is set and valid, use it
        if (!empty($this->nodeApiUrl) && filter_var($this->nodeApiUrl, FILTER_VALIDATE_URL)) {
            return $this->nodeApiUrl;
        }
        
        return $nodeApiUrl;
    }

    /**
     * Process ticket registration
     */
    public function processRegistration(Request $request, $eventId)
    {
        // Check if user is logged in
        if (!Session::has('user')) {
            return redirect()->route('login')->with('error', 'Silakan login terlebih dahulu.');
        }

        $user = Session::get('user'); // FIX: This line was commented out but needed

        // Validate request
        $request->validate([
            'session_id' => 'required|integer',
            'bukti_transaksi' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048'
        ]);

        try {
            // Prepare data for API
            $formData = [
                'pengguna_id' => $user['id'],
                'event_id' => $eventId,
                'session_id' => $request->session_id
            ];

            // Handle file upload if exists
            if ($request->hasFile('bukti_transaksi')) {
                $file = $request->file('bukti_transaksi');
                $fileName = time() . '_' . $file->getClientOriginalName();
                $uploadPath = public_path('uploads/bukti_transaksi');
                
                // Create directory if it doesn't exist
                if (!file_exists($uploadPath)) {
                    mkdir($uploadPath, 0777, true);
                }
                
                $file->move($uploadPath, $fileName);
                $formData['bukti_transaksi'] = 'uploads/bukti_transaksi/' . $fileName;
            }

            // Get Node.js API URL
            $nodeApiUrl = $this->getCorrectNodeApiUrl();
            
            // Validate Node.js API URL
            if (empty($nodeApiUrl)) {
                \Log::error('Node.js API URL is not configured');
                return back()->with('error', 'Server configuration error. Please contact administrator.');
            }

            // Log the request for debugging
            \Log::info('Sending registration request to Node.js API', [
                'url' => $nodeApiUrl . '/api/registrasi/process',
                'data' => $formData
            ]);

            // Send to Node.js API with timeout and better error handling
            $response = Http::timeout(30)
                ->withHeaders([
                    'Content-Type' => 'application/json',
                    'Accept' => 'application/json'
                ])
                ->post($nodeApiUrl . '/api/registrasi/process', $formData);

            // Log the response for debugging
            \Log::info('Node.js API Response', [
                'status' => $response->status(),
                'body' => $response->body(),
                'headers' => $response->headers()
            ]);

            if ($response->successful()) {
                $result = $response->json();
                return redirect()->route('event.success')->with('success', $result['message']);
            } else {
                // More detailed error handling
                $statusCode = $response->status();
                $errorBody = $response->body();
                
                \Log::error('Node.js API Error', [
                    'status_code' => $statusCode,
                    'error_body' => $errorBody,
                    'request_data' => $formData
                ]);

                // Try to parse JSON error response
                try {
                    $error = $response->json();
                    $errorMessage = $error['message'] ?? 'Terjadi kesalahan saat memproses registrasi.';
                } catch (\Exception $jsonException) {
                    $errorMessage = "Error: HTTP {$statusCode} - {$errorBody}";
                }

                return back()->with('error', $errorMessage);
            }

        } catch (\Illuminate\Http\Client\ConnectionException $e) {
            \Log::error('Connection error to Node.js API', [
                'error' => $e->getMessage(),
                'node_api_url' => $nodeApiUrl ?? 'URL not set',
                'trace' => $e->getTraceAsString()
            ]);
            
            return back()->with('error', 'Tidak dapat terhubung ke server. Silakan coba lagi nanti.');
            
        } catch (\Illuminate\Http\Client\RequestException $e) {
            \Log::error('Request error to Node.js API', [
                'error' => $e->getMessage(),
                'response' => $e->response ? $e->response->body() : 'No response',
                'trace' => $e->getTraceAsString()
            ]);
            
            return back()->with('error', 'Terjadi kesalahan dalam permintaan. Silakan coba lagi.');
            
        } catch (\Exception $e) {
            \Log::error('General error in processRegistration', [
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
                'request_data' => $request->all(),
                'user_data' => $user ?? 'User not set'
            ]);
            
            // More specific error message based on error type
            if (strpos($e->getMessage(), 'getCorrectNodeApiUrl') !== false) {
                return back()->with('error', 'Server configuration error. Please contact administrator.');
            }
            
            return back()->with('error', 'Terjadi kesalahan sistem: ' . $e->getMessage());
        }
    }

    /**
     * Show registration success page
     */
    public function showSuccessPage()
    {
        return view('event.success');
    }

    /**
     * Show user's ticket history
     */
    public function showTicketHistory()
    {
        if (!Session::has('user')) {
            return redirect()->route('login');
        }

        $user = Session::get('user');
        
        // DEBUG: Cek user data
        \Log::info('User data: ' . json_encode($user));

        try {
            $nodeApiUrl = $this->getCorrectNodeApiUrl();
            $url = $nodeApiUrl . "/api/registrasi/history/{$user['id']}";
            
            \Log::info('Calling API: ' . $url);
            
            $response = Http::get($url);
            
            \Log::info('API Response: ' . $response->body());
            
            if ($response->successful()) {
                $tickets = $response->json();
                return view('event.history', compact('tickets'));
            }

            return view('event.history', ['tickets' => []]);
        } catch (\Exception $e) {
            \Log::error('Error: ' . $e->getMessage());
            return view('event.history', ['tickets' => []]);
        }
    }
}