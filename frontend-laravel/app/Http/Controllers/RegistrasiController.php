<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Log;

class RegistrasiController extends Controller
{
    private $nodeApiUrl;

    public function __construct()
    {
        // Ambil URL dari environment variable
        $this->nodeApiUrl = env('NODE_API_URL', 'http://localhost:3000');
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
            return $date->format('d M Y');
        }
        
        // If $date is a string, parse it first
        try {
            // Pastikan timezone konsisten - gunakan createFromFormat untuk date saja
            if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
                // Jika format Y-m-d (date only), gunakan createFromFormat
                return \Carbon\Carbon::createFromFormat('Y-m-d', $date)->format('d M Y');
            } else {
                // Jika format datetime, parse dengan timezone
                return \Carbon\Carbon::parse($date)->setTimezone(config('app.timezone', 'Asia/Jakarta'))->format('d M Y');
            }
        } catch (\Exception $e) {
            // Fallback: coba parse langsung tanpa timezone handling
            try {
                return \Carbon\Carbon::parse($date)->format('d M Y');
            } catch (\Exception $e2) {
                return $date; // Return original if all parsing fails
            }
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
        if (!Session::has('user')) {
            return redirect()->route('login.form')
                ->with('error', 'Silakan login terlebih dahulu.');
        }

        $user = Session::get('user');

        try {
            // Prepare form data
            $formData = [
                'pengguna_id' => $user['id'],
                'event_id' => $eventId,
                'session_id' => $request->session_id
            ];

            // Check if file exists
            $buktiTransaksi = $request->file('bukti_transaksi');
            
            if ($buktiTransaksi && $buktiTransaksi->isValid()) {
                // File exists and valid - attach it
                $response = Http::timeout(30)
                    ->attach(
                        'bukti_transaksi',
                        file_get_contents($buktiTransaksi->path()),
                        $buktiTransaksi->getClientOriginalName()
                    )
                    ->post($this->nodeApiUrl . '/api/registrasi/process', $formData);
            } else {
                // No file or invalid file - send request without attachment
                // This handles free events or when payment proof is not required
                $response = Http::timeout(30)
                    ->post($this->nodeApiUrl . '/api/registrasi/process', $formData);
            }

            // Log the response for debugging
            \Log::info('Node.js API Response', [
                'status' => $response->status(),
                'body' => $response->body(),
                'headers' => $response->headers(),
                'has_file' => $buktiTransaksi !== null && $buktiTransaksi->isValid()
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
                    'request_data' => $formData,
                    'has_file' => $buktiTransaksi !== null && $buktiTransaksi->isValid()
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
                'node_api_url' => $this->nodeApiUrl ?? 'URL not set',
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
                'user_data' => $user ?? 'User not set',
                'file_info' => [
                    'has_file' => $request->hasFile('bukti_transaksi'),
                    'file_valid' => $request->hasFile('bukti_transaksi') ? $request->file('bukti_transaksi')->isValid() : false
                ]
            ]);
            
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

    public function reuploadPaymentProof(Request $request, $registrationId)
    {
        if (!Session::has('user')) {
            return redirect()->route('login.form')
                ->with('error', 'Silakan login terlebih dahulu.');
        }

        $user = Session::get('user');

        // Validasi file upload
        $request->validate([
            'bukti_transaksi' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048'
        ], [
            'bukti_transaksi.required' => 'File bukti pembayaran harus diupload',
            'bukti_transaksi.image' => 'File harus berupa gambar',
            'bukti_transaksi.mimes' => 'Format file harus: jpeg, png, jpg, atau gif',
            'bukti_transaksi.max' => 'Ukuran file maksimal 2MB'
        ]);

        try {
            $nodeApiUrl = $this->getCorrectNodeApiUrl();
            
            // Upload file ke Node.js API
            $response = Http::timeout(30)
                ->attach(
                    'bukti_transaksi',
                    file_get_contents($request->file('bukti_transaksi')->path()),
                    $request->file('bukti_transaksi')->getClientOriginalName()
                )
                ->put($nodeApiUrl . "/api/registrasi/reupload/{$registrationId}");

            \Log::info('Reupload API Response', [
                'status' => $response->status(),
                'body' => $response->body()
            ]);

            if ($response->successful()) {
                $result = $response->json();
                return redirect()->route('event.history')
                    ->with('success', $result['message'] ?? 'Bukti pembayaran berhasil diupload ulang. Menunggu verifikasi.');
            } else {
                $statusCode = $response->status();
                $errorBody = $response->body();
                
                \Log::error('Reupload API Error', [
                    'status_code' => $statusCode,
                    'error_body' => $errorBody,
                    'registration_id' => $registrationId
                ]);

                try {
                    $error = $response->json();
                    $errorMessage = $error['message'] ?? 'Terjadi kesalahan saat upload ulang bukti pembayaran.';
                } catch (\Exception $jsonException) {
                    $errorMessage = "Error: HTTP {$statusCode} - Gagal upload ulang";
                }

                return back()->with('error', $errorMessage);
            }

        } catch (\Illuminate\Http\Client\ConnectionException $e) {
            \Log::error('Connection error to Node.js API for reupload', [
                'error' => $e->getMessage(),
                'registration_id' => $registrationId
            ]);
            
            return back()->with('error', 'Tidak dapat terhubung ke server. Silakan coba lagi nanti.');
            
        } catch (\Exception $e) {
            \Log::error('General error in reuploadPaymentProof', [
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'registration_id' => $registrationId
            ]);
            
            return back()->with('error', 'Terjadi kesalahan sistem: ' . $e->getMessage());
        }
    }

    /**
     * Show reupload form
     */
    public function showReuploadForm($registrationId)
    {
        if (!Session::has('user')) {
            return redirect()->route('login.form')
                ->with('error', 'Silakan login terlebih dahulu.');
        }

        $user = Session::get('user');

        try {
            $nodeApiUrl = $this->getCorrectNodeApiUrl();
            $fullUrl = $nodeApiUrl . "/api/registrasi/details/{$registrationId}";
            
            \Log::info('=== REUPLOAD FORM DEBUG START ===', [
                'registration_id' => $registrationId,
                'registration_id_type' => gettype($registrationId),
                'user_id' => $user['id'],
                'node_api_url' => $nodeApiUrl,
                'full_url' => $fullUrl
            ]);
            
            // Test koneksi dasar ke Node.js dulu
            try {
                $healthCheck = Http::timeout(10)->get($nodeApiUrl . '/api/test');
                \Log::info('Node.js health check', [
                    'status' => $healthCheck->status(),
                    'reachable' => $healthCheck->successful()
                ]);
            } catch (\Exception $healthError) {
                \Log::error('Node.js not reachable', ['error' => $healthError->getMessage()]);
            }
            
            // Panggil API dengan error handling yang lebih detail
            $response = Http::timeout(30)->get($fullUrl);
            
            \Log::info('API Response Details', [
                'status_code' => $response->status(),
                'successful' => $response->successful(),
                'headers' => $response->headers(),
                'raw_body' => $response->body(),
                'body_length' => strlen($response->body())
            ]);
            
            if ($response->successful()) {
                $result = $response->json();
                
                \Log::info('Parsed JSON Response', [
                    'has_success_key' => isset($result['success']),
                    'success_value' => $result['success'] ?? 'not set',
                    'has_data_key' => isset($result['data']),
                    'data_keys' => isset($result['data']) ? array_keys($result['data']) : 'no data',
                    'full_result' => $result
                ]);
                
                // Validasi response structure
                if (!isset($result['success'])) {
                    \Log::error('Response missing success field');
                    return redirect()->route('event.history')
                        ->with('error', 'Format response API tidak valid (missing success field).');
                }
                
                if (!$result['success']) {
                    $errorMessage = $result['message'] ?? 'API returned unsuccessful response';
                    \Log::error('API returned unsuccessful', ['message' => $errorMessage]);
                    return redirect()->route('event.history')
                        ->with('error', $errorMessage);
                }
                
                if (!isset($result['data'])) {
                    \Log::error('Response missing data field');
                    return redirect()->route('event.history')
                        ->with('error', 'Format response API tidak valid (missing data field).');
                }
                
                $registration = $result['data'];
                
                \Log::info('Registration Data Analysis', [
                    'registration_id' => $registration['id_registrasi'] ?? 'missing',
                    'user_id' => $registration['pengguna_id'] ?? 'missing',
                    'status' => $registration['status'] ?? 'missing',
                    'has_required_fields' => isset($registration['id_registrasi'], $registration['pengguna_id'], $registration['status'])
                ]);
                
                // Validasi ownership
                if (!isset($registration['pengguna_id'])) {
                    \Log::error('Registration data missing pengguna_id');
                    return redirect()->route('event.history')
                        ->with('error', 'Data registrasi tidak lengkap.');
                }
                
                if ($registration['pengguna_id'] != $user['id']) {
                    \Log::warning('Access denied - user mismatch', [
                        'current_user_id' => $user['id'],
                        'registration_user_id' => $registration['pengguna_id']
                    ]);
                    return redirect()->route('event.history')
                        ->with('error', 'Anda tidak memiliki akses ke registrasi ini.');
                }
                
                // Validasi status
                if (!isset($registration['status'])) {
                    \Log::error('Registration data missing status');
                    return redirect()->route('event.history')
                        ->with('error', 'Status registrasi tidak ditemukan.');
                }
                
                if ($registration['status'] !== 'declined') {
                    \Log::warning('Invalid status for reupload', [
                        'current_status' => $registration['status']
                    ]);
                    return redirect()->route('event.history')
                        ->with('error', 'Hanya registrasi yang ditolak yang dapat upload ulang bukti pembayaran.');
                }
                
                // Semua validasi passed
                \Log::info('All validations passed, showing reupload form');
                
                $registrationData = [
                    'success' => true,
                    'data' => $registration
                ];
                
                return view('event.reupload', compact('registrationData'));
                
            } else {
                // Handle HTTP error responses
                $statusCode = $response->status();
                $rawBody = $response->body();
                
                \Log::error('HTTP Error Response', [
                    'status_code' => $statusCode,
                    'raw_body' => $rawBody
                ]);
                
                try {
                    $errorData = $response->json();
                    $errorMessage = $errorData['message'] ?? "HTTP Error {$statusCode}";
                    \Log::info('Parsed error response', ['error_data' => $errorData]);
                } catch (\Exception $jsonError) {
                    $errorMessage = "HTTP Error {$statusCode}: Tidak dapat mengparse response";
                    \Log::error('Failed to parse error response', ['json_error' => $jsonError->getMessage()]);
                }
                
                return redirect()->route('event.history')
                    ->with('error', $errorMessage);
            }
            
        } catch (\Illuminate\Http\Client\ConnectionException $e) {
            \Log::error('Connection Exception', [
                'error_message' => $e->getMessage(),
                'error_code' => $e->getCode(),
                'registration_id' => $registrationId
            ]);
            
            return redirect()->route('event.history')
                ->with('error', 'Tidak dapat terhubung ke server. Silakan coba lagi nanti.');
                
        } catch (\Exception $e) {
            \Log::error('General Exception in showReuploadForm', [
                'error_message' => $e->getMessage(),
                'error_file' => $e->getFile(),
                'error_line' => $e->getLine(),
                'error_trace' => $e->getTraceAsString(),
                'registration_id' => $registrationId
            ]);
            
            return redirect()->route('event.history')
                ->with('error', 'Terjadi kesalahan sistem: ' . $e->getMessage());
        } finally {
            \Log::info('=== REUPLOAD FORM DEBUG END ===');
        }
    }

    public function downloadCertificate($registrationId)
    {
        try {
            $response = Http::get($this->nodeApiUrl . '/api/registrasi/certificate/download/' . $registrationId);

            Log::info('Response Body: ' . $response->body());

            if ($response->successful()) {
                $data = $response->json();
                Log::info('Parsed Response:', $data);

                if (filter_var($data['success'], FILTER_VALIDATE_BOOLEAN) && isset($data['certificate_url'])) {
                    return redirect()->away($data['certificate_url']);
                }
            }

            return redirect()->back()->with('error', 'Sertifikat belum tersedia atau terjadi kesalahan');
        } catch (Exception $e) {
            Log::error('Download certificate error: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Terjadi kesalahan saat mengunduh sertifikat');
        }
    }

    

}
