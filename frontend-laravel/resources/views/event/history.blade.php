@extends('layouts.guest')

@section('content')
<div class="container my-5">
    <!-- Page Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h1 class="display-6 fw-bold text-dark mb-2">Riwayat Tiket Saya</h1>
                    <p class="text-muted mb-0">Kelola dan pantau status registrasi event Anda</p>
                </div>
                <div>
                    <a href="{{ route('home') }}" class="btn btn-outline-primary">
                        <i class="fas fa-plus me-2"></i>Cari Event Lain
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Tickets List -->
    <div class="row">
        <div class="col-12">
            @if(isset($tickets['data']) && count($tickets['data']) > 0)
                <div class="row g-4">
                    @foreach($tickets['data'] as $ticket)
                    <div class="col-lg-6 col-xl-4">
                        <div class="card ticket-card shadow-sm border-0 h-100">
                            <!-- Event Poster -->
                            <div class="position-relative">
                                @if($ticket['poster'])
                                    <img src="http://localhost:3000/{{ $ticket['poster'] }}" 
                                        alt="{{ $ticket['nama_event'] }}" 
                                        class="card-img-top ticket-poster">
                                @else
                                    <div class="ticket-poster-placeholder d-flex align-items-center justify-content-center">
                                        <i class="fas fa-image fa-3x text-muted"></i>
                                    </div>
                                @endif
                                
                                <!-- Status Badge -->
                                <div class="position-absolute top-0 end-0 m-2">
                                    <span class="badge bg-{{ $ticket['status_class'] }} status-badge">
                                        {{ $ticket['status_text'] }}
                                    </span>
                                </div>
                            </div>

                            <div class="card-body d-flex flex-column">
                                <!-- Event Info -->
                                <div class="mb-3">
                                    <h5 class="card-title fw-bold text-dark mb-2">{{ $ticket['nama_event'] }}</h5>
                                    <p class="text-muted small mb-2">
                                        <i class="fas fa-user me-1"></i>{{ $ticket['organizer_name'] }}
                                    </p>
                                </div>

                                <!-- Session Details -->
                                <div class="session-info mb-3">
                                    <h6 class="fw-semibold text-dark mb-2">{{ $ticket['nama_sesi'] }}</h6>
                                    <div class="small text-muted">
                                        <div class="mb-1">
                                            <i class="fas fa-calendar me-2"></i>{{ $ticket['formatted_date'] }}
                                        </div>
                                        <div class="mb-1">
                                            <i class="fas fa-clock me-2"></i>{{ $ticket['waktu_sesi'] }}
                                        </div>
                                        @if($ticket['lokasi_sesi'])
                                        <div class="mb-1">
                                            <i class="fas fa-map-marker-alt me-2"></i>{{ $ticket['lokasi_sesi'] }}
                                        </div>
                                        @endif
                                    </div>
                                </div>

                                <!-- Price -->
                                <div class="mb-3">
                                    <span class="h6 fw-bold text-primary">{{ $ticket['formatted_price'] }}</span>
                                </div>

                                <!-- Registration Date -->
                                <div class="mb-3 small text-muted">
                                    <i class="fas fa-calendar-plus me-1"></i>
                                    Didaftar: {{ \Carbon\Carbon::parse($ticket['created_at'])->format('d M Y H:i') }}
                                </div>

                                <!-- Actions -->
                                <div class="mt-auto">
                                    @if($ticket['status'] === 'approved')
                                        <div class="d-grid gap-2">
                                            <button class="btn btn-success btn-sm" onclick="showQRCode('{{ $ticket['qrcode'] }}')">
                                                <i class="fas fa-qrcode me-2"></i>Lihat QR Code
                                            </button>
                                            <a href="#" class="btn btn-outline-primary btn-sm" onclick="downloadQR()">
                                                <i class="fas fa-download me-2"></i>Download Tiket
                                            </a>
                                        </div>
                                    @elseif($ticket['status'] === 'pending')
                                        <div class="alert alert-warning small mb-2">
                                            <i class="fas fa-clock me-1"></i>
                                            Menunggu verifikasi pembayaran
                                        </div>
                                        @if($ticket['bukti_transaksi'])
                                        <button class="btn btn-outline-info btn-sm w-100" onclick="showPaymentProof('{{ $ticket['bukti_transaksi'] }}')">
                                            <i class="fas fa-receipt me-2"></i>Lihat Bukti Bayar
                                        </button>
                                        @endif
                                    @elseif($ticket['status'] === 'declined')
                                        <div class="alert alert-danger small mb-2">
                                            <i class="fas fa-times-circle me-1"></i>
                                            Pembayaran ditolak
                                        </div>
                                        <button class="btn btn-primary btn-sm w-100">
                                            <i class="fas fa-redo me-2"></i>Upload Ulang Bukti
                                        </button>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
            @else
                <!-- Empty State -->
                <div class="text-center py-5">
                    <div class="mb-4">
                        <i class="fas fa-ticket-alt fa-4x text-muted"></i>
                    </div>
                    <h3 class="fw-bold text-dark mb-3">Belum Ada Tiket</h3>
                    <p class="text-muted mb-4">Anda belum memiliki tiket event apapun. Mari temukan event menarik!</p>
                    <a href="{{ route('home') }}" class="btn btn-primary btn-lg">
                        <i class="fas fa-search me-2"></i>Jelajahi Event
                    </a>
                </div>
            @endif
        </div>
    </div>
</div>

<!-- QR Code Modal -->
<div class="modal fade" id="qrModal" tabindex="-1" aria-labelledby="qrModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header border-0">
                <h5 class="modal-title fw-bold" id="qrModalLabel">QR Code Tiket</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body text-center">
                <div id="qrcode" class="mb-3"></div>
                <p class="text-muted small">Tunjukkan QR Code ini saat check-in event</p>
            </div>
            <div class="modal-footer border-0">
                <button type="button" class="btn btn-primary" onclick="downloadQR()">
                    <i class="fas fa-download me-2"></i>Download QR
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Payment Proof Modal -->
<div class="modal fade" id="proofModal" tabindex="-1" aria-labelledby="proofModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header border-0">
                <h5 class="modal-title fw-bold" id="proofModalLabel">Bukti Pembayaran</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body text-center">
                <img id="proofImage" src="" alt="Bukti Pembayaran" class="img-fluid rounded">
            </div>
        </div>
    </div>
</div>
@endsection

@section('ExtraCss')
<style>
    .ticket-card {
        transition: all 0.3s ease;
        border-radius: 15px;
        overflow: hidden;
    }
    
    .ticket-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 1rem 2rem rgba(0, 0, 0, 0.15) !important;
    }
    
    .ticket-poster {
        height: 200px;
        object-fit: cover;
        width: 100%;
    }
    
    .ticket-poster-placeholder {
        height: 200px;
        background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
    }
    
    .status-badge {
        font-size: 0.75rem;
        padding: 0.4rem 0.8rem;
        border-radius: 20px;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }
    
    .session-info i {
        width: 16px;
        text-align: center;
    }
    
    .btn {
        transition: all 0.3s ease;
        border-radius: 8px;
    }
    
    .btn:hover {
        transform: translateY(-1px);
    }
    
    .modal-content {
        border-radius: 15px;
        border: none;
    }
    
    .card-title {
        line-height: 1.2;
    }
    
    @media (max-width: 768px) {
        .ticket-poster {
            height: 150px;
        }
        
        .ticket-poster-placeholder {
            height: 150px;
        }
    }
</style>
@endsection

@section('ExtraJS')
<script src="https://cdn.jsdelivr.net/npm/qrious@4.0.2/dist/qrious.min.js"></script>

<script>
function showQRCode(qrcode) {
    console.log('showQRCode called with:', qrcode);
    currentQRCode = qrcode;
    
    // Clear previous QR code
    const qrContainer = document.getElementById('qrcode');
    qrContainer.innerHTML = '';
    
    // Create canvas
    const canvas = document.createElement('canvas');
    qrContainer.appendChild(canvas);
    
    // Generate QR code using QRious
    const qr = new QRious({
        element: canvas,
        value: qrcode,
        size: 250,
        background: 'white',
        foreground: 'black'
    });
    
    // Show modal
    const modal = new bootstrap.Modal(document.getElementById('qrModal'));
    modal.show();
}

function downloadQR() {
    if (!currentQRCode) return;
    
    // Create new canvas for download
    const canvas = document.createElement('canvas');
    const qr = new QRious({
        element: canvas,
        value: currentQRCode,
        size: 500,
        background: 'white',
        foreground: 'black'
    });
    
    // Download
    const link = document.createElement('a');
    link.download = `qr-ticket-${currentQRCode}.png`;
    link.href = canvas.toDataURL();
    link.click();
}
</script>
@endsection