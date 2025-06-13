<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class KeuanganController extends Controller
{
    private $nodeApiUrl;

    public function __construct()
    {
        // Sesuaikan dengan URL API Node.js Anda
        $this->nodeApiUrl = env('NODE_API_URL', 'http://localhost:3000/api');
    }

    public function index()
    {
        $user = session('user');
        return view('keuangan.dashboard', compact('user'));
    }

    /**
     * Display the keuangan dashboard
     */
    public function showDashboard()
    {
        try {
            // Ambil data statistik untuk dashboard
            $response = Http::get($this->nodeApiUrl . '/keuangan/statistics');
            
            $statistics = [];
            if ($response->successful()) {
                $data = $response->json();
                if ($data['success']) {
                    $statistics = $data['data'];
                }
            }

            return view('keuangan.index', compact('statistics'));
        } catch (\Exception $e) {
            Log::error('Error loading keuangan dashboard: ' . $e->getMessage());
            return view('keuangan.index', ['statistics' => []]);
        }
    }

    /**
     * Get registration data for DataTables
     */
    public function getRegistrations(Request $request)
    {
        try {
            $queryParams = [
                'status' => $request->get('status'),
                'event' => $request->get('event'),
                'date' => $request->get('date'),
                'page' => $request->get('page', 1),
                'limit' => $request->get('limit', 10),
                'search' => $request->get('search')
            ];

            // Remove empty parameters
            $queryParams = array_filter($queryParams, function($value) {
                return $value !== null && $value !== '';
            });

            $response = Http::get($this->nodeApiUrl . '/keuangan/registrations', $queryParams);

            if ($response->successful()) {
                return $response->json();
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Gagal mengambil data registrasi'
                ], 500);
            }
        } catch (\Exception $e) {
            Log::error('Error fetching registrations: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan server'
            ], 500);
        }
    }

    /**
     * Update registration status
     */
    public function updateRegistrationStatus(Request $request, $registrationId)
    {
        try {
            $request->validate([
                'action' => 'required|in:approve,decline',
                'reason' => 'required_if:action,decline|string|max:500'
            ]);

            $data = [
                'action' => $request->action,
                'reason' => $request->reason ?? null,
                'updated_by' => auth()->user()->id ?? null
            ];

            $response = Http::put(
                $this->nodeApiUrl . '/keuangan/registrations/' . $registrationId . '/status',
                $data
            );

            if ($response->successful()) {
                return $response->json();
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Gagal memperbarui status registrasi'
                ], 500);
            }
        } catch (\Exception $e) {
            Log::error('Error updating registration status: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan server'
            ], 500);
        }
    }

    /**
     * Get registration details
     */
    public function getRegistrationDetails($registrationId)
    {
        try {
            $response = Http::get($this->nodeApiUrl . '/keuangan/registrations/' . $registrationId);

            if ($response->successful()) {
                $data = $response->json();
                if ($data['success']) {
                    return view('keuangan.registration-detail', ['registration' => $data['data']]);
                }
            }

            return redirect()->back()->with('error', 'Registrasi tidak ditemukan');
        } catch (\Exception $e) {
            Log::error('Error fetching registration details: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Terjadi kesalahan server');
        }
    }

    /**
     * Export registrations to Excel
     */
    public function exportRegistrations(Request $request)
    {
        try {
            $queryParams = [
                'status' => $request->get('status'),
                'event' => $request->get('event'),
                'date' => $request->get('date'),
                'format' => 'excel'
            ];

            // Remove empty parameters
            $queryParams = array_filter($queryParams, function($value) {
                return $value !== null && $value !== '';
            });

            $response = Http::get($this->nodeApiUrl . '/keuangan/registrations/export', $queryParams);

            if ($response->successful()) {
                $filename = 'registrasi_' . date('Y-m-d_H-i-s') . '.xlsx';
                
                return response($response->body())
                    ->header('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet')
                    ->header('Content-Disposition', 'attachment; filename="' . $filename . '"');
            } else {
                return redirect()->back()->with('error', 'Gagal mengeksport data');
            }
        } catch (\Exception $e) {
            Log::error('Error exporting registrations: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Terjadi kesalahan server');
        }
    }

    /**
     * Get events for filter dropdown
     */
    public function getEvents()
    {
        try {
            $response = Http::get($this->nodeApiUrl . '/events');

            if ($response->successful()) {
                return $response->json();
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Gagal mengambil data event'
                ], 500);
            }
        } catch (\Exception $e) {
            Log::error('Error fetching events: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan server'
            ], 500);
        }
    }

    /**
     * Get payment statistics
     */
    public function getPaymentStatistics(Request $request)
    {
        try {
            $queryParams = [
                'period' => $request->get('period', 'monthly'),
                'year' => $request->get('year', date('Y')),
                'month' => $request->get('month', date('m'))
            ];

            $response = Http::get($this->nodeApiUrl . '/keuangan/payment-statistics', $queryParams);

            if ($response->successful()) {
                return $response->json();
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Gagal mengambil statistik pembayaran'
                ], 500);
            }
        } catch (\Exception $e) {
            Log::error('Error fetching payment statistics: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan server'
            ], 500);
        }
    }

    /**
     * Bulk update registration status
     */
    public function bulkUpdateStatus(Request $request)
    {
        try {
            $request->validate([
                'registration_ids' => 'required|array',
                'registration_ids.*' => 'string',
                'action' => 'required|in:approve,decline',
                'reason' => 'required_if:action,decline|string|max:500'
            ]);

            $data = [
                'registration_ids' => $request->registration_ids,
                'action' => $request->action,
                'reason' => $request->reason ?? null,
                'updated_by' => auth()->user()->id ?? null
            ];

            $response = Http::put($this->nodeApiUrl . '/keuangan/registrations/bulk-status', $data);

            if ($response->successful()) {
                return $response->json();
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Gagal memperbarui status registrasi'
                ], 500);
            }
        } catch (\Exception $e) {
            Log::error('Error bulk updating registration status: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan server'
            ], 500);
        }
    }

    /**
     * Download payment proof
     */
    public function downloadPaymentProof($registrationId)
    {
        try {
            $response = Http::get($this->nodeApiUrl . '/keuangan/registrations/' . $registrationId . '/payment-proof');

            if ($response->successful()) {
                $data = $response->json();
                if ($data['success'] && isset($data['data']['file_url'])) {
                    $fileResponse = Http::get($data['data']['file_url']);
                    
                    if ($fileResponse->successful()) {
                        $filename = 'bukti_pembayaran_' . $registrationId . '.' . pathinfo($data['data']['file_url'], PATHINFO_EXTENSION);
                        
                        return response($fileResponse->body())
                            ->header('Content-Type', $fileResponse->header('content-type'))
                            ->header('Content-Disposition', 'attachment; filename="' . $filename . '"');
                    }
                }
            }

            return redirect()->back()->with('error', 'File bukti pembayaran tidak ditemukan');
        } catch (\Exception $e) {
            Log::error('Error downloading payment proof: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Terjadi kesalahan server');
        }
    }

    /**
     * Send notification to user
     */
    public function sendNotification(Request $request, $registrationId)
    {
        try {
            $request->validate([
                'message' => 'required|string|max:500',
                'type' => 'required|in:approval,decline,reminder'
            ]);

            $data = [
                'message' => $request->message,
                'type' => $request->type,
                'sent_by' => auth()->user()->id ?? null
            ];

            $response = Http::post(
                $this->nodeApiUrl . '/keuangan/registrations/' . $registrationId . '/notification',
                $data
            );

            if ($response->successful()) {
                return $response->json();
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Gagal mengirim notifikasi'
                ], 500);
            }
        } catch (\Exception $e) {
            Log::error('Error sending notification: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan server'
            ], 500);
        }
    }

    /**
     * Get registration history for a specific user
     */
    public function getUserRegistrationHistory($userId)
    {
        try {
            $response = Http::get($this->nodeApiUrl . '/keuangan/users/' . $userId . '/registrations');

            if ($response->successful()) {
                $data = $response->json();
                if ($data['success']) {
                    return view('keuangan.user-history', [
                        'registrations' => $data['data'],
                        'user_id' => $userId
                    ]);
                }
            }

            return redirect()->back()->with('error', 'Data registrasi user tidak ditemukan');
        } catch (\Exception $e) {
            Log::error('Error fetching user registration history: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Terjadi kesalahan server');
        }
    }

    /**
     * Generate payment report
     */
    public function generatePaymentReport(Request $request)
    {
        try {
            $request->validate([
                'start_date' => 'required|date',
                'end_date' => 'required|date|after_or_equal:start_date',
                'format' => 'required|in:pdf,excel'
            ]);

            $queryParams = [
                'start_date' => $request->start_date,
                'end_date' => $request->end_date,
                'format' => $request->format,
                'include_charts' => $request->boolean('include_charts', true)
            ];

            $response = Http::get($this->nodeApiUrl . '/keuangan/reports/payment', $queryParams);

            if ($response->successful()) {
                $contentType = $request->format === 'pdf' 
                    ? 'application/pdf' 
                    : 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet';
                
                $extension = $request->format === 'pdf' ? 'pdf' : 'xlsx';
                $filename = 'laporan_pembayaran_' . $request->start_date . '_to_' . $request->end_date . '.' . $extension;
                
                return response($response->body())
                    ->header('Content-Type', $contentType)
                    ->header('Content-Disposition', 'attachment; filename="' . $filename . '"');
            } else {
                return redirect()->back()->with('error', 'Gagal menggenerate laporan');
            }
        } catch (\Exception $e) {
            Log::error('Error generating payment report: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Terjadi kesalahan server');
        }
    }

    /**
     * Dashboard with charts and statistics
     */
    public function dashboard()
    {
        try {
            // Get dashboard data from Node.js API
            $response = Http::get($this->nodeApiUrl . '/keuangan/dashboard');
            
            $dashboardData = [];
            if ($response->successful()) {
                $data = $response->json();
                if ($data['success']) {
                    $dashboardData = $data['data'];
                }
            }

            return view('keuangan.dashboard', compact('dashboardData'));
        } catch (\Exception $e) {
            Log::error('Error loading keuangan dashboard: ' . $e->getMessage());
            return view('keuangan.dashboard', ['dashboardData' => []]);
        }
    }

    /**
     * Handle file upload for bulk import
     */
    public function bulkImport(Request $request)
    {
        try {
            $request->validate([
                'file' => 'required|file|mimes:xlsx,xls,csv|max:2048'
            ]);

            $file = $request->file('file');
            
            // Send file to Node.js API
            $response = Http::attach(
                'file', 
                file_get_contents($file->path()), 
                $file->getClientOriginalName()
            )->post($this->nodeApiUrl . '/keuangan/registrations/bulk-import');

            if ($response->successful()) {
                return $response->json();
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Gagal mengimport data'
                ], 500);
            }
        } catch (\Exception $e) {
            Log::error('Error bulk importing: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan server'
            ], 500);
        }
    }
}