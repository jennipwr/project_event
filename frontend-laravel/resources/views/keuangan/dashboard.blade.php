@extends('layouts.index')

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h1 class="h3 mb-0">Dashboard Keuangan</h1>
                    <p class="text-muted">Selamat datang, {{ $user['name'] }}!</p>
                </div>
                <div>
                    <a href="{{ route('keuangan.show') }}" class="btn btn-primary">
                        <i class="fas fa-list"></i> Lihat Semua Registrasi
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="row mb-4" id="statisticsCards">
        <div class="col-md-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body text-center">
                    <div class="bg-primary rounded-circle p-3 mx-auto mb-3" style="width: 60px; height: 60px;">
                        <i class="fas fa-users text-white fa-2x"></i>
                    </div>
                    <h4 class="mb-1" id="totalRegistrations">-</h4>
                    <p class="text-muted mb-0">Total Registrasi</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body text-center">
                    <div class="bg-warning rounded-circle p-3 mx-auto mb-3" style="width: 60px; height: 60px;">
                        <i class="fas fa-clock text-white fa-2x"></i>
                    </div>
                    <h4 class="mb-1" id="pendingRegistrations">-</h4>
                    <p class="text-muted mb-0">Menunggu Persetujuan</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body text-center">
                    <div class="bg-success rounded-circle p-3 mx-auto mb-3" style="width: 60px; height: 60px;">
                        <i class="fas fa-check text-white fa-2x"></i>
                    </div>
                    <h4 class="mb-1" id="approvedRegistrations">-</h4>
                    <p class="text-muted mb-0">Disetujui</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body text-center">
                    <div class="bg-danger rounded-circle p-3 mx-auto mb-3" style="width: 60px; height: 60px;">
                        <i class="fas fa-times text-white fa-2x"></i>
                    </div>
                    <h4 class="mb-1" id="declinedRegistrations">-</h4>
                    <p class="text-muted mb-0">Ditolak</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-0">
                    <h5 class="card-title mb-0">Aksi Cepat</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="d-grid gap-2">
                                <button class="btn btn-outline-primary" onclick="loadPendingRegistrations()">
                                    <i class="fas fa-clock me-2"></i>Lihat Registrasi Menunggu
                                </button>
                                <button class="btn btn-outline-success" onclick="loadApprovedRegistrations()">
                                    <i class="fas fa-check me-2"></i>Lihat Registrasi Disetujui
                                </button>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="d-grid gap-2">
                                <button class="btn btn-outline-danger" onclick="loadDeclinedRegistrations()">
                                    <i class="fas fa-times me-2"></i>Lihat Registrasi Ditolak
                                </button>
                                <button class="btn btn-outline-info" onclick="refreshData()">
                                    <i class="fas fa-sync-alt me-2"></i>Refresh Data
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Registrations -->
    <div class="row">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-0">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="card-title mb-0">Registrasi Terbaru</h5>
                        <div>
                            <button class="btn btn-sm btn-outline-secondary" onclick="refreshRegistrations()">
                                <i class="fas fa-sync-alt"></i> Refresh
                            </button>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Nama Peserta</th>
                                    <th>Event</th>
                                    <th>Sesi</th>
                                    <th>Tanggal</th>
                                    <th>Biaya</th>
                                    <th>Status</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody id="registrationsTableBody">
                                <tr>
                                    <td colspan="8" class="text-center">
                                        <div class="spinner-border text-primary" role="status">
                                            <span class="visually-hidden">Loading...</span>
                                        </div>
                                        <p class="mt-2 mb-0">Memuat data...</p>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal untuk Approve/Decline -->
<div class="modal fade" id="actionModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="actionModalTitle">Konfirmasi</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p id="actionModalText"></p>
                <div class="mb-3" id="reasonGroup" style="display: none;">
                    <label class="form-label">Alasan Penolakan</label>
                    <textarea class="form-control" id="actionReason" rows="3" placeholder="Masukkan alasan penolakan..."></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                <button type="button" class="btn" id="actionConfirmBtn" onclick="confirmAction()">Konfirmasi</button>
            </div>
        </div>
    </div>
</div>
@endsection

@section('ExtraCss')
<style>
.card {
    transition: transform 0.2s;
}
.card:hover {
    transform: translateY(-2px);
}
.status-badge {
    font-size: 0.75rem;
    padding: 0.25rem 0.5rem;
    border-radius: 0.25rem;
}
.status-pending { 
    background-color: #fff3cd; 
    color: #856404; 
    border: 1px solid #ffeaa7;
}
.status-approved { 
    background-color: #d1edff; 
    color: #0c5460; 
    border: 1px solid #b8daff;
}
.status-declined { 
    background-color: #f8d7da; 
    color: #721c24; 
    border: 1px solid #f5c6cb;
}
</style>
@endsection

@section('ExtraJS')
<script>
let currentAction = '';
let currentRegistrationId = '';
let currentPage = 1;
let currentFilters = {};

// Initialize dashboard
document.addEventListener('DOMContentLoaded', function() {
    loadStatistics();
    loadRegistrations();
});

// Load statistics - sesuaikan dengan route Laravel
function loadStatistics() {
    // Tidak perlu panggil terpisah, akan didapat dari loadRegistrations
    console.log('Statistics will be loaded from loadRegistrations');
}

// Load registrations - sesuaikan dengan route Laravel yang benar
function loadRegistrations(filters = {}) {
    const params = new URLSearchParams({
        page: 1,
        limit: 10,
        ...filters
    });
    
    // Gunakan route Laravel yang benar - sesuai dengan yang ada di Controller
    fetch(`{{ route('keuangan.registrations') }}?${params}`, {
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json'
        }
    })
    .then(response => {
        if (!response.ok) {
            throw new Error('Network response was not ok');
        }
        return response.json();
    })
    .then(data => {
        if (data.success) {
            renderRegistrationsTable(data.data.registrations);
            updateStatistics(data.data.statistics);
        } else {
            throw new Error(data.message || 'Failed to load data');
        }
    })
    .catch(error => {
        console.error('Error loading registrations:', error);
        document.getElementById('registrationsTableBody').innerHTML = 
            '<tr><td colspan="8" class="text-center text-danger">Gagal memuat data. Silakan refresh halaman.</td></tr>';
    });
}

// Update statistics dari data yang diterima
function updateStatistics(stats) {
    if (stats) {
        document.getElementById('totalRegistrations').textContent = stats.total || 0;
        document.getElementById('pendingRegistrations').textContent = stats.pending || 0;
        document.getElementById('approvedRegistrations').textContent = stats.approved || 0;
        document.getElementById('declinedRegistrations').textContent = stats.declined || 0;
    }
}

// Render registrations table
function renderRegistrationsTable(registrations) {
    const tbody = document.getElementById('registrationsTableBody');
    
    if (!registrations || registrations.length === 0) {
        tbody.innerHTML = '<tr><td colspan="8" class="text-center text-muted">Tidak ada data registrasi</td></tr>';
        return;
    }
    
    tbody.innerHTML = registrations.map(reg => `
        <tr>
            <td>${reg.id_registrasi}</td>
            <td>${reg.user_name || 'N/A'}</td>
            <td>${reg.nama_event || 'N/A'}</td>
            <td>${reg.nama_sesi || 'N/A'}</td>
            <td>
                ${reg.tanggal_sesi ? 
                    new Date(reg.tanggal_sesi).toLocaleDateString('id-ID', {
                        year: 'numeric',
                        month: 'short', 
                        day: 'numeric'
                    }) : 'N/A'
                }
            </td>
            <td>
                ${reg.biaya_sesi ? 
                    'Rp ' + parseInt(reg.biaya_sesi).toLocaleString('id-ID') : 
                    'Rp 0'
                }
            </td>
            <td>
                <span class="badge status-badge status-${reg.status}">
                    ${getStatusText(reg.status)}
                </span>
            </td>
            <td>
                <div class="btn-group" role="group">
                    ${reg.bukti_transaksi ? 
                        `<button class="btn btn-sm btn-outline-info" onclick="viewProof('${reg.bukti_transaksi}')" title="Lihat Bukti">
                            <i class="fas fa-eye"></i>
                        </button>` : ''
                    }
                    ${reg.status === 'pending' ? `
                        <button class="btn btn-sm btn-outline-success" onclick="showActionModal('approve', '${reg.id_registrasi}')" title="Setujui">
                            <i class="fas fa-check"></i>
                        </button>
                        <button class="btn btn-sm btn-outline-danger" onclick="showActionModal('decline', '${reg.id_registrasi}')" title="Tolak">
                            <i class="fas fa-times"></i>
                        </button>
                    ` : ''}
                </div>
            </td>
        </tr>
    `).join('');
}

// Get status text in Indonesian
function getStatusText(status) {
    switch(status) {
        case 'pending': return 'Menunggu';
        case 'approved': return 'Disetujui';
        case 'declined': return 'Ditolak';
        default: return status;
    }
}

// Load specific status registrations
function loadPendingRegistrations() {
    loadRegistrations({ status: 'pending' });
}

function loadApprovedRegistrations() {
    loadRegistrations({ status: 'approved' });
}

function loadDeclinedRegistrations() {
    loadRegistrations({ status: 'declined' });
}

// Refresh functions
function refreshData() {
    loadStatistics();
    loadRegistrations();
}

function refreshRegistrations() {
    loadRegistrations(currentFilters);
}

// Show action modal
function showActionModal(action, registrationId) {
    currentAction = action;
    currentRegistrationId = registrationId;
    
    const modal = new bootstrap.Modal(document.getElementById('actionModal'));
    const title = document.getElementById('actionModalTitle');
    const text = document.getElementById('actionModalText');
    const confirmBtn = document.getElementById('actionConfirmBtn');
    const reasonGroup = document.getElementById('reasonGroup');
    
    if (action === 'approve') {
        title.textContent = 'Setujui Registrasi';
        text.textContent = 'Apakah Anda yakin ingin menyetujui registrasi ini?';
        confirmBtn.textContent = 'Setujui';
        confirmBtn.className = 'btn btn-success';
        reasonGroup.style.display = 'none';
    } else {
        title.textContent = 'Tolak Registrasi';
        text.textContent = 'Apakah Anda yakin ingin menolak registrasi ini?';
        confirmBtn.textContent = 'Tolak';
        confirmBtn.className = 'btn btn-danger';
        reasonGroup.style.display = 'block';
    }
    
    document.getElementById('actionReason').value = '';
    modal.show();
}

// Confirm action - sesuaikan dengan route Laravel yang benar
async function confirmAction() {
    const reason = document.getElementById('actionReason').value;
    
    if (currentAction === 'decline' && !reason.trim()) {
        alert('Alasan penolakan harus diisi');
        return;
    }
    
    try {
        // Gunakan route Laravel yang benar dengan named route
        const url = `{{ route('keuangan.updateStatus', ':id') }}`.replace(':id', currentRegistrationId);
        
        const response = await fetch(url, {
            method: 'PUT',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: JSON.stringify({
                action: currentAction,
                reason: reason || null
            })
        });
        
        const data = await response.json();
        
        if (data.success) {
            bootstrap.Modal.getInstance(document.getElementById('actionModal')).hide();
            refreshData();
            alert(data.message || 'Status berhasil diperbarui');
        } else {
            alert(data.message || 'Terjadi kesalahan');
        }
    } catch (error) {
        console.error('Error:', error);
        alert('Terjadi kesalahan server');
    }
}

// View proof
function viewProof(proofUrl) {
    if (proofUrl) {
        window.open(proofUrl, '_blank');
    }
}
</script>
@endsection