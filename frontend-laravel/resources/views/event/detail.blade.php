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
            <li class="breadcrumb-item active" aria-current="page">
                {{ $eventData['event']['nama_event'] ?? 'Unknown Event' }}
            </li>
        </ol>
    </nav>

    <div class="row g-4">
        <!-- Left Section - Event Details -->
        <div class="col-lg-8">
            <!-- Event Poster -->
            <div class="card shadow-sm mb-4">
                @if(isset($eventData['event']['poster_url']) && $eventData['event']['poster_url'])
                    <img src="{{ config('app.node_api_url') }}{{ $eventData['event']['poster_url'] }}" 
                         alt="{{ $eventData['event']['nama_event'] ?? 'Event Poster' }}" 
                         class="card-img-top event-poster">
                @else
                    <div class="event-poster-placeholder d-flex align-items-center justify-content-center">
                        <i class="fas fa-image fa-4x text-muted"></i>
                    </div>
                @endif
            </div>

            <!-- Event Title -->
            <div class="card shadow-sm mb-4">
                <div class="card-body">
                    <h1 class="card-title display-5 fw-bold text-dark mb-3">
                        {{ $eventData['event']['nama_event'] ?? 'Nama Event Tidak Tersedia' }}
                    </h1>
                    
                    <!-- Organizer Info -->
                    <div class="d-flex align-items-center text-muted mb-3">
                        <i class="fas fa-user me-2"></i>
                        <span class="fw-medium">
                            Diselenggarakan oleh: {{ $eventData['event']['organizer_name'] ?? 'Tidak diketahui' }}
                        </span>
                    </div>
                </div>
            </div>

            <!-- Event Description -->
            @if(isset($eventData['event']['deskripsi']) && $eventData['event']['deskripsi'])
            <div class="card shadow-sm mb-4">
                <div class="card-body">
                    <h2 class="card-title h3 fw-bold text-dark mb-3">Deskripsi Event</h2>
                    <div class="text-muted lh-lg">
                        {!! nl2br(e($eventData['event']['deskripsi'])) !!}
                    </div>
                </div>
            </div>
            @endif

            <!-- Terms and Conditions -->
            @if(isset($eventData['event']['syarat_ketentuan']) && $eventData['event']['syarat_ketentuan'])
            <div class="card shadow-sm mb-4">
                <div class="card-body">
                    <h2 class="card-title h3 fw-bold text-dark mb-3">Syarat & Ketentuan</h2>
                    <div class="text-muted lh-lg">
                        {!! nl2br(e($eventData['event']['syarat_ketentuan'])) !!}
                    </div>
                </div>
            </div>
            @endif

            <!-- Event Sessions -->
            @if(isset($eventData['event']['sessions']) && count($eventData['event']['sessions']) > 0)
            <div class="card shadow-sm">
                <div class="card-body">
                    <h2 class="card-title h3 fw-bold text-dark mb-4">Jadwal Sesi</h2>
                    <div class="row g-3">
                        @foreach($eventData['event']['sessions'] as $session)
                        <div class="col-12">
                            <div class="card border session-card">
                                <div class="card-body">
                                    <div class="row align-items-center">
                                        <div class="col-md-8">
                                            <h5 class="card-title fw-semibold text-dark mb-2">
                                                {{ $session['nama_sesi'] ?? 'Sesi Tidak Tersedia' }}
                                            </h5>
                                            <div class="session-details">
                                                @if(isset($session['tanggal_sesi']))
                                                <div class="d-flex align-items-center mb-1 text-muted">
                                                    <i class="fas fa-calendar me-2"></i>
                                                    <span>{{ \Carbon\Carbon::parse($session['tanggal_sesi'])->format('d F Y') }}</span>
                                                </div>
                                                @endif
                                                @if(isset($session['waktu_sesi']))
                                                <div class="d-flex align-items-center mb-1 text-muted">
                                                    <i class="fas fa-clock me-2"></i>
                                                    <span>{{ $session['waktu_sesi'] }}</span>
                                                </div>
                                                @endif
                                                @if(isset($session['lokasi_sesi']))
                                                <div class="d-flex align-items-center mb-1 text-muted">
                                                    <i class="fas fa-map-marker-alt me-2"></i>
                                                    <span>{{ $session['lokasi_sesi'] }}</span>
                                                </div>
                                                @endif
                                                @if(isset($session['narasumber_sesi']) && $session['narasumber_sesi'])
                                                <div class="d-flex align-items-center mb-1 text-muted">
                                                    <i class="fas fa-user-tie me-2"></i>
                                                    <span>{{ $session['narasumber_sesi'] }}</span>
                                                </div>
                                                @endif
                                            </div>
                                        </div>
                                        <div class="col-md-4 text-md-end mt-3 mt-md-0">
                                            <div class="h5 fw-bold text-primary mb-1">
                                                @if(isset($session['biaya_sesi']) && $session['biaya_sesi'] == 0)
                                                    Gratis
                                                @elseif(isset($session['biaya_sesi']))
                                                    Rp {{ number_format($session['biaya_sesi'], 0, ',', '.') }}
                                                @else
                                                    Harga belum tersedia
                                                @endif
                                            </div>
                                            <div class="small text-muted">
                                                {{ $session['jumlah_peserta'] ?? 0 }} peserta
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>
            @endif
        </div>

        <!-- Right Section - Event Info Box -->
        <div class="col-lg-4">
            <div class="sticky-top" style="top: 80px;">
                <div class="card shadow-sm border-0 bg-light">
                    <div class="card-body p-4">
                        <h3 class="card-title h4 fw-bold text-dark mb-4">
                            <i class="fas fa-info-circle me-2 text-primary"></i>Informasi Event
                        </h3>
                        
                        <!-- Event Basic Info -->
                        <div class="mb-4">
                            <div class="mb-3">
                                <h6 class="fw-semibold text-dark mb-1">Nama Event</h6>
                                <p class="text-dark mb-0">{{ $eventData['event']['nama_event'] ?? 'Tidak tersedia' }}</p>
                            </div>
                            
                            <div class="mb-3">
                                <h6 class="fw-semibold text-dark mb-1">
                                    <i class="fas fa-calendar me-1"></i>Tanggal
                                </h6>
                                <p class="text-dark mb-0">{{ $eventData['event']['date_range'] ?? 'Tanggal belum ditentukan' }}</p>
                            </div>
                            
                            <div class="mb-3">
                                <h6 class="fw-semibold text-dark mb-1">
                                    <i class="fas fa-user me-1"></i>Penyelenggara
                                </h6>
                                <p class="text-dark mb-0">{{ $eventData['event']['organizer_name'] ?? 'Tidak diketahui' }}</p>
                            </div>
                            
                            <div class="mb-4">
                                <h6 class="fw-semibold text-dark mb-1">
                                    <i class="fas fa-dollar-sign me-1"></i>Harga Tiket
                                </h6>
                                <p class="text-primary fw-bold fs-5 mb-0">{{ $eventData['event']['price_range'] ?? 'Harga belum ditentukan' }}</p>
                            </div>
                        </div>

                        <!-- Buy Ticket Button -->
                        <div class="d-grid mb-4">
                            <button id="buyTicketBtn" class="btn btn-primary btn-lg fw-bold">
                                <i class="fas fa-ticket-alt me-2"></i>Beli Tiket
                            </button>
                        </div>

                        <!-- Additional Info -->
                        <div class="text-center small text-muted">
                            <div class="mb-1">
                                <i class="fas fa-phone me-1"></i>Butuh bantuan? Hubungi kami
                            </div>
                            <div>
                                <i class="fas fa-credit-card me-1"></i>Pembayaran aman & terpercaya
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
    .event-poster {
        height: 400px;
        object-fit: cover;
        width: 100%;
    }
    
    .event-poster-placeholder {
        height: 400px;
        background-color: #f8f9fa;
        border: 2px dashed #dee2e6;
    }
    
    .session-card {
        transition: all 0.3s ease;
        border: 1px solid #e9ecef;
    }
    
    .session-card:hover {
        box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15) !important;
        transform: translateY(-2px);
    }
    
    .session-details i {
        width: 16px;
        text-align: center;
    }
    
    .sticky-top {
        position: -webkit-sticky;
        position: sticky;
    }
    
    @media (max-width: 991.98px) {
        .sticky-top {
            position: static;
        }
        
        .event-poster {
            height: 250px;
        }
        
        .event-poster-placeholder {
            height: 250px;
        }
    }
    
    .card {
        border: none;
        box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
    }
    
    .card:hover {
        box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
    }
    
    .btn-primary {
        background: linear-gradient(135deg, #007bff 0%, #0056b3 100%);
        border: none;
        transition: all 0.3s ease;
    }
    
    .btn-primary:hover {
        transform: translateY(-2px);
        box-shadow: 0 0.5rem 1rem rgba(0, 123, 255, 0.25);
    }
    
    .breadcrumb-item a {
        color: #6c757d;
    }
    
    .breadcrumb-item a:hover {
        color: #007bff;
    }
    
    .lh-lg {
        line-height: 1.8;
    }
</style>
@endsection

@section('ExtraJS')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const buyTicketBtn = document.getElementById('buyTicketBtn');
    
    if (buyTicketBtn) {
        buyTicketBtn.addEventListener('click', function() {
            // Add loading state
            const originalText = this.innerHTML;
            this.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Memproses...';
            this.disabled = true;
            
            // Simulate processing
            setTimeout(() => {
                this.innerHTML = originalText;
                this.disabled = false;
                
                // Check if eventData exists before using it
                @if(isset($eventData['event']['id_event']))
                    const eventId = {{ $eventData['event']['id_event'] }};
                    window.location.href = `/events/${eventId}/tickets`;
                @else
                    alert('Event ID tidak tersedia');
                @endif
            }, 1000);
        });
    }
    
    // Add smooth scroll for anchor links
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function (e) {
            e.preventDefault();
            const target = document.querySelector(this.getAttribute('href'));
            if (target) {
                target.scrollIntoView({
                    behavior: 'smooth',
                    block: 'start'
                });
            }
        });
    });
    
    // Add animation on scroll for cards
    const observerOptions = {
        threshold: 0.1,
        rootMargin: '0px 0px -50px 0px'
    };
    
    const observer = new IntersectionObserver(function(entries) {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.style.opacity = '1';
                entry.target.style.transform = 'translateY(0)';
            }
        });
    }, observerOptions);
    
    // Observe all cards
    document.querySelectorAll('.card').forEach(card => {
        card.style.opacity = '0';
        card.style.transform = 'translateY(20px)';
        card.style.transition = 'opacity 0.6s ease, transform 0.6s ease';
        observer.observe(card);
    });
});
</script>
@endsection