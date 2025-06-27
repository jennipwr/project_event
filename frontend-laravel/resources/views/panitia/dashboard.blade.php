@extends('layouts.index')

@section('content')
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h1 class="h3 mb-1">Dashboard Panitia</h1>
                    <p class="text-muted mb-0">Selamat datang kembali, {{ $user['name'] }}!</p>
                </div>
                <div>
                    <a href="{{ route('panitia.addEvent') }}" class="btn btn-primary">
                        <i class="fas fa-plus me-2"></i>Buat Event Baru
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="row mb-4">
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-primary shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                Total Event
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800" id="totalEvents">
                                <div class="spinner-border spinner-border-sm" role="status"></div>
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-calendar fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-success shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                Total Peserta
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800" id="totalPeserta">
                                <div class="spinner-border spinner-border-sm" role="status"></div>
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-users fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-info shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                                Event Aktif
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800" id="eventAktif">
                                <div class="spinner-border spinner-border-sm" role="status"></div>
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-clipboard-list fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-warning shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                Kapasitas Tersisa
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800" id="kapasitasTersisa">
                                <div class="spinner-border spinner-border-sm" role="status"></div>
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-chair fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Aksi Cepat</h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-3 col-sm-6 mb-3">
                            <a href="{{ route('panitia.addEvent') }}" class="btn btn-outline-primary btn-block">
                                <i class="fas fa-plus mb-2"></i><br>
                                Buat Event Baru
                            </a>
                        </div>
                        <div class="col-md-3 col-sm-6 mb-3">
                            <a href="{{ route('panitia.listEvents') }}" class="btn btn-outline-success btn-block">
                                <i class="fas fa-list mb-2"></i><br>
                                Kelola Event
                            </a>
                        </div>
                        <div class="col-md-3 col-sm-6 mb-3">
                            <a href="#" class="btn btn-outline-info btn-block" onclick="refreshStats()">
                                <i class="fas fa-sync-alt mb-2"></i><br>
                                Refresh Data
                            </a>
                        </div>
                        <div class="col-md-3 col-sm-6 mb-3">
                            <a href="#" class="btn btn-outline-warning btn-block" data-toggle="modal" data-target="#helpModal">
                                <i class="fas fa-question-circle mb-2"></i><br>
                                Bantuan
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="card shadow">
                <div class="card-header py-3 d-flex justify-content-between align-items-center">
                    <h6 class="m-0 font-weight-bold text-primary">Event Terbaru</h6>
                    <a href="{{ route('panitia.listEvents') }}" class="btn btn-sm btn-primary">Lihat Semua</a>
                </div>
                <div class="card-body">
                    <div id="recentEventsContainer">
                        <div class="text-center">
                            <div class="spinner-border" role="status">
                                <span class="sr-only">Loading...</span>
                            </div>
                            <p class="mt-2">Memuat data event...</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="helpModal" tabindex="-1" role="dialog" aria-labelledby="helpModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="helpModalLabel">Panduan Dashboard Panitia</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-6">
                        <h6><i class="fas fa-calendar text-primary"></i> Mengelola Event</h6>
                        <ul class="list-unstyled ml-3">
                            <li>• Buat event baru dengan mengklik tombol "Buat Event Baru"</li>
                            <li>• Kelola event yang sudah ada melalui menu "Kelola Event"</li>
                            <li>• Edit atau hapus event sesuai kebutuhan</li>
                        </ul>
                    </div>
                    <div class="col-md-6">
                        <h6><i class="fas fa-chart-bar text-success"></i> Memahami Statistik</h6>
                        <ul class="list-unstyled ml-3">
                            <li>• <strong>Total Event:</strong> Jumlah semua event yang Anda buat</li>
                            <li>• <strong>Total Peserta:</strong> Jumlah peserta terdaftar di semua event</li>
                            <li>• <strong>Event Aktif:</strong> Event yang masih bisa diikuti</li>
                            <li>• <strong>Kapasitas Tersisa:</strong> Sisa slot peserta</li>
                        </ul>
                    </div>
                </div>
                <hr>
                <div class="alert alert-info">
                    <i class="fas fa-info-circle"></i>
                    <strong>Tips:</strong> Gunakan tombol "Refresh Data" untuk memperbarui statistik terbaru secara manual.
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>
@endsection

@section('ExtraCSS')
<style>
.border-left-primary {
    border-left: 0.25rem solid #4e73df !important;
}

.border-left-success {
    border-left: 0.25rem solid #1cc88a !important;
}

.border-left-info {
    border-left: 0.25rem solid #36b9cc !important;
}

.border-left-warning {
    border-left: 0.25rem solid #f6c23e !important;
}

.card {
    border: 0;
    border-radius: 0.35rem;
}

.card .card-header {
    background-color: #f8f9fc;
    border-bottom: 1px solid #e3e6f0;
}

.text-xs {
    font-size: 0.7rem;
}

.event-card {
    transition: transform 0.2s;
}

.event-card:hover {
    transform: translateY(-2px);
}

.badge-status {
    font-size: 0.65rem;
}

.progress-thin {
    height: 0.5rem;
}

.btn-outline-primary:hover,
.btn-outline-success:hover,
.btn-outline-info:hover,
.btn-outline-warning:hover {
    transform: translateY(-1px);
}

.quick-action-btn {
    min-height: 80px;
    display: flex;
    flex-direction: column;
    justify-content: center;
    align-items: center;
}

@media (max-width: 768px) {
    .col-xl-3 {
        margin-bottom: 1rem;
    }
    
    .h5 {
        font-size: 1.1rem;
    }
}
</style>
@endsection

@section('ExtraJS')
<script>
$(document).ready(function() {
    loadDashboardData();
    
    setInterval(loadDashboardData, 300000);
});

function loadDashboardData() {
    const userId = @json($user['id'] ?? null);
    
    if (!userId) {
        console.error('User ID is not defined:', userId);
        showError('ID pengguna tidak valid');
        return;
    }
    
    console.log('Loading dashboard data for user:', userId);
    
    $.ajax({
        url: "{{ route('panitia.dashboardData') }}",
        method: 'GET',
        timeout: 10000,
        dataType: 'json',
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
            'Accept': 'application/json'
        },
        success: function(response) {
            console.log('Dashboard API Response:', response);
            
            let events = [];
            
            if (Array.isArray(response)) {
                events = response;
            } else if (response && response.data) {
                events = response.data;
            } else if (response && response.events) {
                events = response.events;
            }
            
            console.log('Processed events for dashboard:', events);
            updateStatistics(events);
            loadRecentEvents(events);
        },
        error: function(xhr, status, error) {
            console.error('Dashboard API Error:', {
                status: status,
                error: error,
                response: xhr.responseText,
                statusCode: xhr.status
            });
            
            showError(`Gagal memuat data dashboard: ${error}`);
            setDefaultValues();
        }
    });
}

function updateStatistics(events) {
    let totalEvents = 0;
    let totalPeserta = 0;
    let totalKapasitas = 0;
    let eventAktif = 0;
    
    console.log('Calculating statistics from events:', events);
    
    if (!Array.isArray(events)) {
        console.warn('Events is not an array:', events);
        events = [];
    }
    
    events.forEach(event => {
        if (!event || typeof event !== 'object') {
            console.warn('Invalid event object:', event);
            return;
        }
        
        totalEvents++;
        
        // Controller sudah menyediakan field yang benar
        const pesertaCount = parseInt(event.total_peserta || 0);
        const kapasitasCount = parseInt(event.total_kapasitas || 0);
        
        console.log(`Event "${event.nama_event || 'Unknown'}":`, {
            peserta: pesertaCount,
            kapasitas: kapasitasCount,
            sisa: event.sisa_kapasitas || 0
        });
        
        totalPeserta += pesertaCount;
        totalKapasitas += kapasitasCount;
        
        // Check if event is active (future date)
        if (event.tanggal) {
            const eventDate = new Date(event.tanggal);
            const now = new Date();
            if (eventDate >= now) {
                eventAktif++;
            }
        } else {
            eventAktif++; // If no date, consider active
        }
    });
    
    const kapasitasTersisa = Math.max(0, totalKapasitas - totalPeserta);
    
    console.log('Final dashboard statistics:', {
        totalEvents,
        totalPeserta,
        totalKapasitas,
        eventAktif,
        kapasitasTersisa
    });
    
    // Update UI dengan animasi
    animateCounterUpdate('#totalEvents', totalEvents);
    animateCounterUpdate('#totalPeserta', totalPeserta);
    animateCounterUpdate('#eventAktif', eventAktif);
    animateCounterUpdate('#kapasitasTersisa', kapasitasTersisa);
}

function animateCounterUpdate(selector, finalValue) {
    const $element = $(selector);
    $element.html(finalValue);

    const startValue = 0;
    const duration = 1000;
    const steps = 20;
    const increment = finalValue / steps;
    let currentValue = startValue;
    let step = 0;
    
    const timer = setInterval(() => {
        step++;
        currentValue += increment;
        
        if (step >= steps) {
            $element.html(finalValue);
            clearInterval(timer);
        } else {
            $element.html(Math.floor(currentValue));
        }
    }, duration / steps);
}

function setDefaultValues() {
    $('#totalEvents').html('0');
    $('#totalPeserta').html('0');
    $('#eventAktif').html('0');
    $('#kapasitasTersisa').html('0');
    
    $('#recentEventsContainer').html(`
        <div class="text-center py-4">
            <i class="fas fa-exclamation-triangle fa-3x text-warning mb-3"></i>
            <h5 class="text-muted">Gagal memuat data</h5>
            <p class="text-muted">Terjadi kesalahan saat memuat data dashboard.</p>
            <button class="btn btn-primary" onclick="refreshStats()">
                <i class="fas fa-refresh me-2"></i>Coba Lagi
            </button>
        </div>
    `);
}

function loadRecentEvents(events) {
    console.log('Loading recent events:', events);
    
    let eventArray = [];
    
    try {
        if (Array.isArray(events)) {
            eventArray = events;
        } else if (events && events.events) {
            eventArray = events.events;
        } else if (events && typeof events === 'object') {
            eventArray = [events];
        }
        
        eventArray.sort((a, b) => {
            const dateA = new Date(a.created_at || a.tanggal || 0);
            const dateB = new Date(b.created_at || b.tanggal || 0);
            return dateB - dateA;
        });
        
        const recentEvents = eventArray.slice(0, 3);
        
        if (recentEvents.length === 0) {
            $('#recentEventsContainer').html(`
                <div class="text-center py-4">
                    <i class="fas fa-calendar-plus fa-3x text-muted mb-3"></i>
                    <h5 class="text-muted">Belum ada event</h5>
                    <p class="text-muted">Mulai dengan membuat event pertama Anda!</p>
                    <a href="{{ route('panitia.addEvent') }}" class="btn btn-primary">
                        <i class="fas fa-plus me-2"></i>Buat Event Baru
                    </a>
                </div>
            `);
            return;
        }
        
        let eventsHtml = '<div class="row">';
        
        recentEvents.forEach(event => {
            console.log('Processing event:', event);
            
            const eventDate = event.tanggal ? 
                new Date(event.tanggal).toLocaleDateString('id-ID') : 
                'Tanggal tidak tersedia';
            
            const totalSesi = parseInt(event.total_sesi) || 0;
            const totalPeserta = parseInt(event.total_peserta) || 0;
            const totalKapasitas = parseInt(event.total_kapasitas) || 0;
            const progressPercent = totalKapasitas > 0 ? 
                Math.round((totalPeserta / totalKapasitas) * 100) : 0;
            
            const eventName = (event.nama_event || 'Event Tanpa Nama')
                .replace(/'/g, "&#39;")
                .replace(/"/g, "&quot;");
            
            const eventDesc = event.deskripsi || '';
            const shortDesc = eventDesc.length > 100 ? 
                eventDesc.substring(0, 100) + '...' : eventDesc;

            eventsHtml += `
                <div class="col-md-4 mb-3">
                    <div class="card event-card h-100">
                        <div class="card-body">
                            <h6 class="card-title font-weight-bold">${eventName}</h6>
                            <p class="card-text text-muted small">${shortDesc}</p>
                            
                            <div class="row text-center mb-3">
                                <div class="col-4">
                                    <small class="text-muted d-block">Sesi</small>
                                    <strong class="text-primary">${totalSesi}</strong>
                                </div>
                                <div class="col-4">
                                    <small class="text-muted d-block">Peserta</small>
                                    <strong class="text-success">${totalPeserta}</strong>
                                </div>
                                <div class="col-4">
                                    <small class="text-muted d-block">Kapasitas</small>
                                    <strong class="text-info">${totalKapasitas}</strong>
                                </div>
                            </div>
                            
                            <div class="progress progress-thin mb-3">
                                <div class="progress-bar bg-success" role="progressbar" 
                                    style="width: ${progressPercent}%">
                                </div>
                            </div>
                            
                            <div class="d-flex justify-content-between">
                                <a href="{{ url('panitia/events') }}" class="btn btn-outline-primary btn-sm">
                                    <i class="fas fa-edit me-1"></i>Edit
                                </a>
                                <button class="btn btn-primary btn-sm" onclick="viewEventDetail(${event.id_event})">
                                    <i class="fas fa-eye me-1"></i>Detail
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            `;
        });
        
        eventsHtml += '</div>';
        $('#recentEventsContainer').html(eventsHtml);
        
    } catch (error) {
        console.error('Error loading recent events:', error);
        $('#recentEventsContainer').html(`
            <div class="text-center py-4">
                <i class="fas fa-exclamation-triangle fa-3x text-warning mb-3"></i>
                <h5 class="text-muted">Gagal memuat event</h5>
                <p class="text-muted">Terjadi kesalahan saat memuat daftar event.</p>
            </div>
        `);
    }
}

function refreshStats() {
    // Show loading state
    $('#totalEvents, #totalPeserta, #eventAktif, #kapasitasTersisa').html('<div class="spinner-border spinner-border-sm" role="status"></div>');
    
    // Show loading in recent events
    $('#recentEventsContainer').html(`
        <div class="text-center">
            <div class="spinner-border" role="status">
                <span class="sr-only">Loading...</span>
            </div>
            <p class="mt-2">Memuat ulang data...</p>
        </div>
    `);
    
    // Reload data
    loadDashboardData();
    
    // Show success message after a delay
    setTimeout(() => {
        showSuccess('Data berhasil diperbarui!');
    }, 1000);
}

function viewEventDetail(eventId) {
    if (!eventId) {
        showError('ID event tidak valid');
        return;
    }
    window.location.href = `{{ url('panitia/events') }}`;
}

function showSuccess(message) {
    $('.alert.position-fixed').remove();
    
    const toast = $(`
        <div class="alert alert-success alert-dismissible fade show position-fixed" 
             style="top: 20px; right: 20px; z-index: 9999; min-width: 300px;">
            <strong>Sukses!</strong> ${message}
            <button type="button" class="close" data-dismiss="alert">
                <span>&times;</span>
            </button>
        </div>
    `);
    
    $('body').append(toast);
    
    setTimeout(() => {
        toast.alert('close');
    }, 3000);
}

function showError(message) {
    $('.alert.position-fixed').remove();
    
    const toast = $(`
        <div class="alert alert-danger alert-dismissible fade show position-fixed" 
             style="top: 20px; right: 20px; z-index: 9999; min-width: 300px;">
            <strong>Error!</strong> ${message}
            <button type="button" class="close" data-dismiss="alert">
                <span>&times;</span>
            </button>
        </div>
    `);
    
    $('body').append(toast);
    
    setTimeout(() => {
        toast.alert('close');
    }, 5000);
}
</script>
@endsection