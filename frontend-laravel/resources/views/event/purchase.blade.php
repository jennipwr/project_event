@extends('layouts.guest')

@section('content')
<div class="container my-5">
    <!-- Breadcrumb -->
    <nav aria-label="breadcrumb" class="mb-4">
        <ol class="breadcrumb">
            <li class="breadcrumb-item">
                <a href="{{ route('home') }}" class="text-decoration-none">
                    <i class="fas fa-home me-1"></i>Beranda
                </a>
            </li>
            <li class="breadcrumb-item">
                <a href="{{ route('home') }}" class="text-decoration-none">Events</a>
            </li>
            <li class="breadcrumb-item">
                <a href="{{ route('events.show', $eventData['data']['event']['id_event']) }}" class="text-decoration-none">
                    {{ $eventData['data']['event']['nama_event'] }}
                </a>
            </li>
            <li class="breadcrumb-item active" aria-current="page">Beli Tiket</li>
        </ol>
    </nav>

    <div class="row g-4">
        <!-- Left Section - Purchase Form -->
        <div class="col-lg-8">
            <div class="card shadow-sm">
                <div class="card-header bg-primary text-white">
                    <h3 class="card-title mb-0">
                        <i class="fas fa-ticket-alt me-2"></i>Beli Tiket Event
                    </h3>
                </div>
                <div class="card-body p-4">
                    @if(session('error'))
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            {{ session('error') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif

                    <form id="registrationForm" action="{{ route('event.process', $eventData['data']['event']['id_event']) }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        
                        <!-- User Information (Read-only) -->
                        <div class="row mb-4">
                            <div class="col-12">
                                <h5 class="fw-bold text-dark mb-3">Informasi Pembeli</h5>
                                <div class="bg-light p-3 rounded">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <label class="form-label fw-semibold">Nama Lengkap</label>
                                            <input type="text" class="form-control" value="{{ $user['name'] }}" readonly>
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label fw-semibold">Email</label>
                                            <input type="text" class="form-control" value="{{ $user['email'] }}" readonly>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Session Selection -->
                        <div class="row mb-4">
                            <div class="col-12">
                                <h5 class="fw-bold text-dark mb-3">Pilih Sesi Event</h5>
                                @if(isset($eventData['data']['sessions']) && count($eventData['data']['sessions']) > 0)
                                    <div class="row g-3">
                                        @foreach($eventData['data']['sessions'] as $session)
                                        <div class="col-12">
                                            <div class="card border session-option" data-session-id="{{ $session['id_sesi'] }}" data-is-free="{{ $session['is_free'] ? 1 : 0 }}">
                                                <div class="card-body">
                                                    <div class="form-check">
                                                        <input class="form-check-input session-radio" type="radio" name="session_id" 
                                                               value="{{ $session['id_sesi'] }}" id="session{{ $session['id_sesi'] }}" required>
                                                        <label class="form-check-label w-100" for="session{{ $session['id_sesi'] }}">
                                                            <div class="row align-items-center">
                                                                <div class="col-md-8">
                                                                    <h6 class="fw-semibold text-dark mb-2">{{ $session['nama_sesi'] }}</h6>
                                                                    <div class="session-details">
                                                                        <div class="d-flex align-items-center mb-1 text-muted">
                                                                            <i class="fas fa-calendar me-2"></i>
                                                                            <span>{{ $session['formatted_date'] }}</span>
                                                                        </div>
                                                                        <div class="d-flex align-items-center mb-1 text-muted">
                                                                            <i class="fas fa-clock me-2"></i>
                                                                            <span>{{ $session['waktu_sesi'] }}</span>
                                                                        </div>
                                                                        @if($session['lokasi_sesi'])
                                                                        <div class="d-flex align-items-center mb-1 text-muted">
                                                                            <i class="fas fa-map-marker-alt me-2"></i>
                                                                            <span>{{ $session['lokasi_sesi'] }}</span>
                                                                        </div>
                                                                        @endif
                                                                        @if($session['narasumber_sesi'])
                                                                        <div class="d-flex align-items-center text-muted">
                                                                            <i class="fas fa-user-tie me-2"></i>
                                                                            <span>{{ $session['narasumber_sesi'] }}</span>
                                                                        </div>
                                                                        @endif
                                                                    </div>
                                                                </div>
                                                                <div class="col-md-4 text-md-end">
                                                                    <div class="h5 fw-bold text-primary mb-1">
                                                                        {{ $session['formatted_price'] }}
                                                                    </div>
                                                                    <div class="small text-muted">
                                                                        {{ $session['jumlah_peserta'] ?? 0 }} peserta
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </label>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        @endforeach
                                    </div>
                                @else
                                    <div class="alert alert-warning">
                                        <i class="fas fa-exclamation-triangle me-2"></i>
                                        Belum ada sesi tersedia untuk event ini.
                                    </div>
                                @endif
                            </div>
                        </div>

                        <!-- Payment Proof Upload -->
                        <div class="row mb-4" id="paymentProofSection" style="display: none;">
                            <div class="col-12">
                                <h5 class="fw-bold text-dark mb-3">Bukti Pembayaran</h5>
                                <div class="card border-warning bg-warning bg-opacity-10">
                                    <div class="card-body">
                                        <div class="alert alert-info mb-3">
                                            <i class="fas fa-info-circle me-2"></i>
                                            <strong>Informasi Pembayaran:</strong><br>
                                            Silakan lakukan pembayaran terlebih dahulu, kemudian upload bukti pembayaran Anda di bawah ini.
                                        </div>
                                        
                                        <div class="mb-3">
                                            <label for="bukti_transaksi" class="form-label fw-semibold">
                                                Upload Bukti Transaksi <span class="text-danger">*</span>
                                            </label>
                                            <input type="file" class="form-control" id="bukti_transaksi" name="bukti_transaksi" 
                                                   accept="image/*" required>
                                            <div class="form-text">
                                                <i class="fas fa-upload me-1"></i>
                                                Format yang didukung: JPG, PNG, GIF (Max: 2MB)
                                            </div>
                                        </div>

                                        <!-- Preview Image -->
                                        <div id="imagePreview" style="display: none;">
                                            <label class="form-label fw-semibold">Preview:</label>
                                            <img id="previewImg" src="" alt="Preview" class="img-thumbnail" style="max-width: 300px; max-height: 200px;">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Free Event Notice -->
                        <div class="row mb-4" id="freeEventNotice" style="display: none;">
                            <div class="col-12">
                                <div class="alert alert-success">
                                    <i class="fas fa-check-circle me-2"></i>
                                    <strong>Event Gratis!</strong> Tiket Anda akan otomatis dikonfirmasi setelah registrasi.
                                </div>
                            </div>
                        </div>

                        <!-- Submit Button -->
                        <div class="d-grid">
                            <button type="submit" id="submitBtn" class="btn btn-primary btn-lg fw-bold" disabled>
                                <i class="fas fa-credit-card me-2"></i>Proses Registrasi
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Right Section - Event Summary -->
        <div class="col-lg-4">
            <div class="sticky-top" style="top: 20px;">
                <div class="card shadow-sm border-0">
                    <div class="card-header bg-light">
                        <h5 class="card-title mb-0">
                            <i class="fas fa-info-circle me-2 text-primary"></i>Ringkasan Event
                        </h5>
                    </div>
                    <div class="card-body">
                        <!-- Event Poster -->
                        @if(isset($eventData['data']['event']['poster']) && $eventData['data']['event']['poster'])
                            <img src="{{ config('app.node_api_url') }}{{ $eventData['data']['event']['poster'] }}" 
                                 alt="{{ $eventData['data']['event']['nama_event'] }}" 
                                 class="img-fluid rounded mb-3">
                        @endif

                        <!-- Event Details -->
                        <h6 class="fw-bold text-dark">{{ $eventData['data']['event']['nama_event'] }}</h6>
                        <p class="text-muted small mb-3">{{ $eventData['data']['event']['organizer_name'] ?? 'Organizer tidak diketahui' }}</p>
                        
                        <!-- Selected Session Details -->
                        <div id="selectedSessionDetails" style="display: none;">
                            <hr>
                            <h6 class="fw-semibold text-dark mb-2">Sesi Terpilih:</h6>
                            <div id="sessionDetailsContent"></div>
                        </div>

                        <!-- Help Info -->
                        <hr>
                        <div class="text-center small text-muted">
                            <div class="mb-1">
                                <i class="fas fa-phone me-1"></i>Butuh bantuan? Hubungi kami
                            </div>
                            <div>
                                <i class="fas fa-shield-alt me-1"></i>Transaksi aman & terpercaya
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('ExtraCss')
<style>
    .session-option {
        cursor: pointer;
        transition: all 0.3s ease;
        border: 2px solid #e9ecef;
    }
    
    .session-option:hover {
        border-color: #007bff;
        box-shadow: 0 0.5rem 1rem rgba(0, 123, 255, 0.15);
    }
    
    .session-option.selected {
        border-color: #007bff;
        background-color: #f8f9ff;
        box-shadow: 0 0.5rem 1rem rgba(0, 123, 255, 0.15);
    }
    
    .session-details i {
        width: 16px;
        text-align: center;
    }
    
    .form-check-input:checked {
        background-color: #007bff;
        border-color: #007bff;
    }
    
    .sticky-top {
        position: -webkit-sticky;
        position: sticky;
    }
    
    @media (max-width: 991.98px) {
        .sticky-top {
            position: static;
        }
    }
    
    .btn-primary {
        background: linear-gradient(135deg, #007bff 0%, #0056b3 100%);
        border: none;
        transition: all 0.3s ease;
    }
    
    .btn-primary:hover:not(:disabled) {
        transform: translateY(-2px);
        box-shadow: 0 0.5rem 1rem rgba(0, 123, 255, 0.25);
    }
    
    .btn-primary:disabled {
        opacity: 0.6;
        cursor: not-allowed;
    }
    
    .card {
        border: none;
        box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
    }
    
    .img-thumbnail {
        border: 2px solid #007bff;
    }
</style>
@endsection

@section('ExtraJS')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const sessionRadios = document.querySelectorAll('.session-radio');
    const paymentProofSection = document.getElementById('paymentProofSection');
    const freeEventNotice = document.getElementById('freeEventNotice');
    const buktiTransaksiInput = document.getElementById('bukti_transaksi');
    const submitBtn = document.getElementById('submitBtn');
    const selectedSessionDetails = document.getElementById('selectedSessionDetails');
    const sessionDetailsContent = document.getElementById('sessionDetailsContent');
    const imagePreview = document.getElementById('imagePreview');
    const previewImg = document.getElementById('previewImg');

    // Handle session selection
    sessionRadios.forEach(radio => {
        radio.addEventListener('change', function() {
            // Remove selected class from all options
            document.querySelectorAll('.session-option').forEach(option => {
                option.classList.remove('selected');
            });
            
            // Add selected class to current option
            const selectedOption = this.closest('.session-option');
            selectedOption.classList.add('selected');
            
            // Get session data
            const isFree = selectedOption.dataset.isFree === '1';
            const sessionData = getSessionData(this.value);
            
            // Show/hide payment proof section
            if (isFree) {
                paymentProofSection.style.display = 'none';
                freeEventNotice.style.display = 'block';
                buktiTransaksiInput.removeAttribute('required');
                submitBtn.disabled = false;
            } else {
                paymentProofSection.style.display = 'block';
                freeEventNotice.style.display = 'none';
                buktiTransaksiInput.setAttribute('required', 'required');
                updateSubmitButton();
            }
            
            // Show selected session details
            if (sessionData) {
                showSelectedSessionDetails(sessionData);
            }
        });
    });

    // Handle file upload
    if (buktiTransaksiInput) {
        buktiTransaksiInput.addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (file) {
                // Validate file size (2MB)
                if (file.size > 2 * 1024 * 1024) {
                    alert('Ukuran file terlalu besar. Maksimal 2MB.');
                    this.value = '';
                    imagePreview.style.display = 'none';
                    updateSubmitButton();
                    return;
                }
                
                // Validate file type
                if (!file.type.startsWith('image/')) {
                    alert('File harus berupa gambar.');
                    this.value = '';
                    imagePreview.style.display = 'none';
                    updateSubmitButton();
                    return;
                }
                
                // Show preview
                const reader = new FileReader();
                reader.onload = function(e) {
                    previewImg.src = e.target.result;
                    imagePreview.style.display = 'block';
                };
                reader.readAsDataURL(file);
            } else {
                imagePreview.style.display = 'none';
            }
            
            updateSubmitButton();
        });
    }

    // Handle form submission
    document.getElementById('registrationForm').addEventListener('submit', function(e) {
        const selectedSession = document.querySelector('.session-radio:checked');
        if (!selectedSession) {
            e.preventDefault();
            alert('Silakan pilih sesi terlebih dahulu.');
            return;
        }

        const isFree = selectedSession.closest('.session-option').dataset.isFree === '1';
        if (!isFree && buktiTransaksiInput && !buktiTransaksiInput.files[0]) {
            e.preventDefault();
            alert('Silakan upload bukti pembayaran terlebih dahulu.');
            return;
        }

        // Show loading state
        submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Memproses...';
        submitBtn.disabled = true;
    });

    function updateSubmitButton() {
        const selectedSession = document.querySelector('.session-radio:checked');
        if (!selectedSession) {
            submitBtn.disabled = true;
            return;
        }

        const isFree = selectedSession.closest('.session-option').dataset.isFree === '1';
        if (isFree) {
            submitBtn.disabled = false;
        } else {
            submitBtn.disabled = !buktiTransaksiInput.files[0];
        }
    }

    function getSessionData(sessionId) {
        const sessions = @json($eventData['data']['sessions']);
        return sessions.find(session => session.id_sesi == sessionId);
    }

    function showSelectedSessionDetails(sessionData) {
        sessionDetailsContent.innerHTML = `
            <div class="mb-2">
                <strong>${sessionData.nama_sesi}</strong>
            </div>
            <div class="small text-muted mb-1">
                <i class="fas fa-calendar me-1"></i> ${sessionData.formatted_date}
            </div>
            <div class="small text-muted mb-1">
                <i class="fas fa-clock me-1"></i> ${sessionData.waktu_sesi}
            </div>
            ${sessionData.lokasi_sesi ? `<div class="small text-muted mb-1"><i class="fas fa-map-marker-alt me-1"></i> ${sessionData.lokasi_sesi}</div>` : ''}
            <div class="fw-bold text-primary mt-2">
                ${sessionData.formatted_price}
            </div>
        `;
        selectedSessionDetails.style.display = 'block';
    }
});
</script>
@endsection