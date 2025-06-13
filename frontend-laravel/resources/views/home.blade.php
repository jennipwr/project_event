@extends('layouts.guest')

@section('content')
<div class="hero-section">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-8">
                <h1 class="hero-title">Jelajahi Event Terbaik</h1>
                <p class="hero-subtitle">Temukan pengalaman tak terlupakan dan bergabunglah dengan ribuan peserta lainnya</p>
            </div>
            <div class="col-lg-4">
                <div class="hero-decoration">
                    <i class="fas fa-calendar-star"></i>
                </div>
            </div>
        </div>
    </div>
</div>

@if(session('error'))
    <div class="alert alert-danger">{{ session('error') }}</div>
@endif

<div class="container events-section">
    <div class="section-header text-center mb-5">
        <h2 class="section-title">Event Mendatang</h2>
        <div class="section-divider"></div>
        <p class="section-description">Pilih event yang sesuai dengan passion dan minat Anda</p>
    </div>
    <!-- Filter dan Search Bar -->
    <div class="row mb-4">
        <div class="col-md-6">
            <div class="search-box">
                <input type="text" class="form-control" placeholder="Cari event..." id="searchInput">
                <i class="fas fa-search search-icon"></i>
            </div>
        </div>
        <div class="col-md-6">
            <div class="d-flex gap-2">
                <select class="form-select" id="categoryFilter">
                    <option value="">Semua Kategori</option>
                    <option value="conference">Conference</option>
                    <option value="workshop">Workshop</option>
                    <option value="seminar">Seminar</option>
                    <option value="exhibition">Exhibition</option>
                </select>
                <select class="form-select" id="priceFilter">
                    <option value="">Semua Harga</option>
                    <option value="free">Gratis</option>
                    <option value="paid">Berbayar</option>
                </select>
            </div>
        </div>
    </div>

    <div id="events-container" class="row">
        <!-- Loading state -->
        <div id="loading-state" class="col-12 text-center py-5">
            <div class="loading-spinner">
                <div class="spinner-ring"></div>
                <div class="spinner-ring"></div>
                <div class="spinner-ring"></div>
            </div>
            <p class="mt-4 loading-text">Memuat event terbaik untuk Anda...</p>
        </div>
    </div>

    <!-- Empty state jika tidak ada event -->
    <div id="empty-state" class="d-none">
        <div class="row justify-content-center">
            <div class="col-md-8 text-center py-5">
                <div class="empty-state-icon">
                    <i class="fas fa-calendar-times"></i>
                </div>
                <h3 class="empty-state-title">Belum Ada Event Tersedia</h3>
                <p class="empty-state-description">Maaf, saat ini belum ada event yang dapat ditampilkan. Silakan cek kembali nanti!</p>
                <button class="btn btn-primary btn-lg mt-3" onclick="loadAllEvents()">
                    <i class="fas fa-refresh me-2"></i>Muat Ulang
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Additional CSS -->
<style>
.search-box {
    position: relative;
}

.search-box .form-control {
    padding-right: 40px;
}

.search-icon {
    position: absolute;
    right: 15px;
    top: 50%;
    transform: translateY(-50%);
    color: #6c757d;
}

.event-card {
    border: none;
    border-radius: 16px;
    overflow: hidden;
    box-shadow: 0 4px 20px rgba(0,0,0,0.08);
    transition: all 0.3s ease;
    height: 100%;
    cursor: pointer;
}

.event-card:hover {
    transform: translateY(-8px);
    box-shadow: 0 12px 40px rgba(0,0,0,0.15);
}

.event-poster {
    height: 200px;
    background-size: cover;
    background-position: center;
    background-repeat: no-repeat;
    position: relative;
    overflow: hidden;
}

.event-poster::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: linear-gradient(45deg, rgba(0,0,0,0.1), transparent);
}

.free-badge {
    position: absolute;
    top: 12px;
    right: 12px;
    background: linear-gradient(135deg, #28a745, #20c997);
    color: white;
    padding: 4px 12px;
    border-radius: 20px;
    font-size: 12px;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    box-shadow: 0 2px 8px rgba(40, 167, 69, 0.3);
}

.popular-badge {
    position: absolute;
    top: 12px;
    left: 12px;
    background: linear-gradient(135deg, #ff6b6b, #ff8e8e);
    color: white;
    padding: 4px 12px;
    border-radius: 20px;
    font-size: 12px;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    box-shadow: 0 2px 8px rgba(255, 107, 107, 0.3);
}

.event-meta {
    padding: 20px;
    display: flex;
    flex-direction: column;
    height: calc(100% - 200px);
}

.event-date {
    color:rgb(216, 224, 232);
    font-size: 13px;
    font-weight: 500;
    margin-bottom: 8px;
    display: flex;
    align-items: center;
    gap: 6px;
}

.event-date::before {
    content: '\f073';
    font-family: 'Font Awesome 5 Free';
    font-weight: 900;
    color:rgb(241, 241, 241);
}

.event-title {
    font-size: 16px;
    font-weight: 700;
    margin-bottom: 8px;
    color: #2c3e50;
    line-height: 1.4;
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}

.event-description {
    font-size: 14px;
    color: #6c757d;
    line-height: 1.5;
    margin-bottom: 15px;
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
    flex-grow: 1;
}

.organizer-info {
    display: flex;
    align-items: center;
    gap: 8px;
    margin-bottom: 15px;
    padding: 8px 12px;
    background: #f8f9fa;
    border-radius: 8px;
    font-size: 13px;
    color: #495057;
}

.organizer-info i {
    color: #007bff;
    width: 16px;
}

.event-footer {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-top: auto;
    padding-top: 15px;
    border-top: 1px solid #e9ecef;
}

.event-price {
    font-size: 16px;
    font-weight: 700;
    color: #007bff;
}

.event-price.free {
    color: #28a745;
}

.session-count {
    display: flex;
    align-items: center;
    gap: 6px;
    font-size: 13px;
    color: #6c757d;
}

.session-count i {
    color: #ffc107;
}

.event-stats {
    display: flex;
    gap: 15px;
    margin-bottom: 12px;
    font-size: 12px;
    color: #6c757d;
}

.event-stat {
    display: flex;
    align-items: center;
    gap: 4px;
}

.event-stat i {
    color: #007bff;
}

.fade-in {
    opacity: 0;
    transform: translateY(20px);
    animation: fadeInUp 0.6s ease forwards;
}

@keyframes fadeInUp {
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.loading-spinner {
    display: flex;
    justify-content: center;
    gap: 10px;
}

.spinner-ring {
    width: 12px;
    height: 12px;
    border: 2px solid #007bff;
    border-radius: 50%;
    border-top-color: transparent;
    animation: spin 1s linear infinite;
}

.spinner-ring:nth-child(2) {
    animation-delay: 0.2s;
}

.spinner-ring:nth-child(3) {
    animation-delay: 0.4s;
}

@keyframes spin {
    to {
        transform: rotate(360deg);
    }
}

/* Responsive adjustments */
@media (max-width: 768px) {
    .event-card {
        margin-bottom: 20px;
    }
    
    .event-poster {
        height: 180px;
    }
    
    .event-meta {
        padding: 15px;
    }
}
</style>
@endsection

@section('ExtraJS')
<script>
document.addEventListener('DOMContentLoaded', function() {
    loadAllEvents();
    
    // Search functionality
    document.getElementById('searchInput').addEventListener('input', function() {
        filterEvents();
    });
    
    // Filter functionality
    document.getElementById('categoryFilter').addEventListener('change', function() {
        filterEvents();
    });
    
    document.getElementById('priceFilter').addEventListener('change', function() {
        filterEvents();
    });
});

let allEvents = [];

async function loadAllEvents() {
    try {
        const response = await fetch('/api/events/all');
        const data = await response.json();
        
        // Hide loading state
        document.getElementById('loading-state').style.display = 'none';
        
        if (data.error || !data.events || data.events.length === 0) {
            // Show empty state
            document.getElementById('empty-state').classList.remove('d-none');
            return;
        }
        
        allEvents = data.events;
        displayEvents(allEvents);
        
    } catch (error) {
        console.error('Error loading events:', error);
        // Hide loading state and show error
        document.getElementById('loading-state').innerHTML = `
            <div class="col-12 text-center py-5">
                <div class="empty-state-icon">
                    <i class="fas fa-exclamation-triangle text-warning"></i>
                </div>
                <h4 class="empty-state-title">Gagal Memuat Event</h4>
                <p class="empty-state-description">Terjadi kesalahan saat memuat data event. Silakan coba lagi.</p>
                <button class="btn btn-primary btn-lg mt-3" onclick="loadAllEvents()">
                    <i class="fas fa-refresh me-2"></i>Coba Lagi
                </button>
            </div>
        `;
    }
}

function displayEvents(events) {
    const container = document.getElementById('events-container');
    let eventsHtml = '';
    
    events.forEach((event, index) => {
        let posterUrl = '/images/default-event.jpg';
        if (event.poster) {
            const normalizedPath = event.poster.replace(/\\/g, '/');
            posterUrl = `http://localhost:3000/${normalizedPath}`;
        }
        
        const minPrice = getMinPrice(event.sessions || []);
        const priceDisplay = minPrice === 0 ? 'GRATIS' : `Rp ${formatRupiah(minPrice)}`;
        const priceClass = minPrice === 0 ? 'free' : '';
        const freeBadge = minPrice === 0 ? '<div class="free-badge">Gratis</div>' : '';
        
        // Random popular badge for demo (you can replace with actual logic)
        const popularBadge = Math.random() > 0.7 ? '<div class="popular-badge">Popular</div>' : '';
        
        // Get date range from sessions
        const dateRange = getEventDateRange(event.sessions || []);
        
        // Mock data for additional stats (replace with actual data)
        const attendees = Math.floor(Math.random() * 500) + 50;
        const rating = (Math.random() * 2 + 3).toFixed(1); // 3.0 - 5.0
        
        eventsHtml += `
            <div class="col-xl-3 col-lg-4 col-md-6 mb-4">
                <div class="card event-card fade-in" style="animation-delay: ${index * 0.1}s" onclick="viewEventDetail(${event.id_event})">
                    <div class="event-poster" style="background-image: url('${posterUrl}')">
                        ${freeBadge}
                        ${popularBadge}
                    </div>
                    <div class="event-meta">
                        ${dateRange ? `<div class="event-date">${dateRange}</div>` : ''}
                        <h5 class="event-title">${escapeHtml(event.nama_event)}</h5>
                        <p class="event-description">${escapeHtml(event.deskripsi || 'Bergabunglah dengan event menarik ini dan dapatkan pengalaman tak terlupakan bersama para peserta lainnya.')}</p>
                        
                        <div class="event-stats">
                            <div class="event-stat">
                                <i class="fas fa-users"></i>
                                <span>${attendees}+ peserta</span>
                            </div>
                            <div class="event-stat">
                                <i class="fas fa-star"></i>
                                <span>${rating}</span>
                            </div>
                        </div>
                        
                        <div class="organizer-info">
                            <i class="fas fa-user-tie"></i>
                            <span>${escapeHtml(event.nama_penyelenggara || 'Penyelenggara')}</span>
                        </div>
                        
                        <div class="event-footer">
                            <span class="event-price ${priceClass}">${priceDisplay}</span>
                            <div class="session-count">
                                <i class="fas fa-calendar-check"></i>
                                <span>${event.total_sesi || 1} sesi</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        `;
    });
    
    container.innerHTML = eventsHtml;
}

function filterEvents() {
    const searchTerm = document.getElementById('searchInput').value.toLowerCase();
    const categoryFilter = document.getElementById('categoryFilter').value;
    const priceFilter = document.getElementById('priceFilter').value;
    
    let filteredEvents = allEvents.filter(event => {
        const matchesSearch = event.nama_event.toLowerCase().includes(searchTerm) || 
                            (event.deskripsi && event.deskripsi.toLowerCase().includes(searchTerm));
        
        const matchesCategory = !categoryFilter || 
                              (event.kategori && event.kategori.toLowerCase() === categoryFilter);
        
        const minPrice = getMinPrice(event.sessions || []);
        const matchesPrice = !priceFilter || 
                           (priceFilter === 'free' && minPrice === 0) ||
                           (priceFilter === 'paid' && minPrice > 0);
        
        return matchesSearch && matchesCategory && matchesPrice;
    });
    
    displayEvents(filteredEvents);
    
    if (filteredEvents.length === 0) {
        document.getElementById('events-container').innerHTML = `
            <div class="col-12 text-center py-5">
                <div class="empty-state-icon">
                    <i class="fas fa-search"></i>
                </div>
                <h4 class="empty-state-title">Tidak Ada Event Ditemukan</h4>
                <p class="empty-state-description">Coba ubah filter atau kata kunci pencarian Anda.</p>
            </div>
        `;
    }
}

function getMinPrice(sessions) {
    if (!sessions || sessions.length === 0) return 0;
    
    const prices = sessions.map(session => parseFloat(session.biaya_sesi) || 0);
    return Math.min(...prices);
}

function formatRupiah(number) {
    return new Intl.NumberFormat('id-ID').format(number);
}

function getEventDateRange(sessions) {
    if (!sessions || sessions.length === 0) return null;
    
    const dates = sessions.map(session => new Date(session.tanggal_sesi)).sort();
    const firstDate = dates[0];
    const lastDate = dates[dates.length - 1];
    
    const months = [
        'Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun',
        'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'
    ];
    
    if (dates.length === 1) {
        return `${firstDate.getDate()} ${months[firstDate.getMonth()]} ${firstDate.getFullYear()}`;
    }
    
    if (firstDate.getMonth() === lastDate.getMonth() && firstDate.getFullYear() === lastDate.getFullYear()) {
        return `${firstDate.getDate()}-${lastDate.getDate()} ${months[firstDate.getMonth()]} ${firstDate.getFullYear()}`;
    }
    
    if (firstDate.getFullYear() === lastDate.getFullYear()) {
        return `${firstDate.getDate()} ${months[firstDate.getMonth()]} - ${lastDate.getDate()} ${months[lastDate.getMonth()]} ${firstDate.getFullYear()}`;
    }
    
    return `${firstDate.getDate()} ${months[firstDate.getMonth()]} ${firstDate.getFullYear()} - ${lastDate.getDate()} ${months[lastDate.getMonth()]} ${lastDate.getFullYear()}`;
}

function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

function viewEventDetail(eventId) {
    console.log('View event detail:', eventId);
    window.location.href = `/events/${eventId}`;
}
</script>
@endsection