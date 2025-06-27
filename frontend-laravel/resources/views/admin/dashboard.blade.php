@extends('layouts.index')

@section('content')
<div class="container-fluid py-4">
    <!-- Header Section -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h4 class="text-primary mb-1">Dashboard Admin</h4>
                            <p class="text-muted mb-0">Selamat datang di panel administrasi sistem</p>
                        </div>
                        <div class="text-end">
                            <small class="text-muted">{{ date('d F Y, H:i') }}</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="row mb-4">
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <div class="bg-primary bg-gradient rounded-circle p-3">
                                <i class="fas fa-users text-white fa-lg"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <div class="small text-muted">Total Pengguna</div>
                            <div class="h5 fw-bold text-primary mb-0" id="totalUsers">
                                <div class="spinner-border spinner-border-sm" role="status"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <div class="bg-success bg-gradient rounded-circle p-3">
                                <i class="fas fa-user-check text-white fa-lg"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <div class="small text-muted">Pengguna Aktif</div>
                            <div class="h5 fw-bold text-success mb-0" id="activeUsers">
                                <div class="spinner-border spinner-border-sm" role="status"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <div class="bg-warning bg-gradient rounded-circle p-3">
                                <i class="fas fa-clipboard-list text-white fa-lg"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <div class="small text-muted">Panitia</div>
                            <div class="h5 fw-bold text-warning mb-0" id="panitiaUsers">
                                <div class="spinner-border spinner-border-sm" role="status"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <div class="bg-info bg-gradient rounded-circle p-3">
                                <i class="fas fa-wallet text-white fa-lg"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <div class="small text-muted">Keuangan</div>
                            <div class="h5 fw-bold text-info mb-0" id="keuanganUsers">
                                <div class="spinner-border spinner-border-sm" role="status"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-0">
                    <h5 class="mb-0">Aksi Cepat</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <a href="{{ route('admin.register') }}" class="btn btn-primary btn-lg w-100 d-flex align-items-center justify-content-center">
                                <i class="fas fa-user-plus me-2"></i>
                                Tambah Pengguna
                            </a>
                        </div>
                        <div class="col-md-4 mb-3">
                            <a href="{{ route('admin.akuns') }}" class="btn btn-outline-primary btn-lg w-100 d-flex align-items-center justify-content-center">
                                <i class="fas fa-users-cog me-2"></i>
                                Kelola Akun
                            </a>
                        </div>
                        <div class="col-md-4 mb-3">
                            <button class="btn btn-outline-success btn-lg w-100 d-flex align-items-center justify-content-center" onclick="loadDashboardData()">
                                <i class="fas fa-sync-alt me-2"></i>
                                Refresh Data
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Users Table -->
    <div class="row">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-0 d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Pengguna Terbaru</h5>
                    <a href="{{ route('admin.akuns') }}" class="btn btn-sm btn-outline-primary">
                        Lihat Semua <i class="fas fa-arrow-right ms-1"></i>
                    </a>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover" id="recentUsersTable">
                            <thead class="table-light">
                                <tr>
                                    <th>Nama</th>
                                    <th>Email</th>
                                    <th>Role</th>
                                    <th>Status</th>
                                    <th>Dibuat</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td colspan="5" class="text-center py-4">
                                        <div class="spinner-border text-primary" role="status">
                                            <span class="visually-hidden">Memuat data...</span>
                                        </div>
                                        <div class="mt-2 text-muted">Sedang memuat data...</div>
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

<!-- Loading Modal -->
<div class="modal fade" id="loadingModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-sm modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-body text-center py-4">
                <div class="spinner-border text-primary mb-3" role="status">
                    <span class="visually-hidden">Loading...</span>
                </div>
                <div>Sedang memproses...</div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('ExtraCss')
<style>
    .card {
        transition: all 0.3s ease;
    }
    
    .card:hover {
        transform: translateY(-2px);
        box-shadow: 0 .5rem 1rem rgba(0,0,0,.15)!important;
    }
    
    .bg-gradient {
        background: linear-gradient(135deg, var(--bs-primary), var(--bs-primary-dark));
    }
    
    .bg-success.bg-gradient {
        background: linear-gradient(135deg, var(--bs-success), #198754);
    }
    
    .bg-warning.bg-gradient {
        background: linear-gradient(135deg, var(--bs-warning), #fd7e14);
    }
    
    .bg-info.bg-gradient {
        background: linear-gradient(135deg, var(--bs-info), #0dcaf0);
    }
    
    .table-hover tbody tr:hover {
        background-color: rgba(0,123,255,.075);
    }
    
    .spinner-border {
        animation: spin 1s linear infinite;
    }
    
    @keyframes spin {
        0% { transform: rotate(0deg); }
        100% { transform: rotate(360deg); }
    }
    
    .btn {
        transition: all 0.3s ease;
    }
    
    .btn:hover {
        transform: translateY(-1px);
    }
</style>
@endsection

@section('ExtraJS')
<script>
$(document).ready(function() {
    // Load dashboard data saat halaman dimuat
    loadDashboardData();
});

function loadDashboardData() {
    console.log('Loading dashboard data...');
    
    // Menggunakan endpoint yang sudah ada untuk mengambil data pengguna
    $.ajax({
        url: '{{ env("NODEJS_API_URL") }}/api/pengguna/panitia-dan-keuangan',
        method: 'GET',
        timeout: 30000,
        success: function(response) {
            console.log('API Response:', response);
            
            if (response.success && response.data) {
                updateStatistics(response.data);
                updateRecentUsersTable(response.data);
            } else {
                console.error('Invalid response format:', response);
                showError();
            }
        },
        error: function(xhr, status, error) {
            console.error('Error loading dashboard data:', error);
            console.error('Status:', status);
            console.error('Response:', xhr.responseText);
            showError();
        }
    });
}

function updateStatistics(users) {
    // Hitung statistik dari data yang diterima
    const totalUsers = users.length;
    const activeUsers = users.filter(user => user.status === 'aktif').length;
    const panitiaUsers = users.filter(user => user.role_id_role === 2).length;
    const keuanganUsers = users.filter(user => user.role_id_role === 4).length;
    
    // Update tampilan statistik
    $('#totalUsers').html(totalUsers);
    $('#activeUsers').html(activeUsers);
    $('#panitiaUsers').html(panitiaUsers);
    $('#keuanganUsers').html(keuanganUsers);
    
    console.log('Statistics updated:', { totalUsers, activeUsers, panitiaUsers, keuanganUsers });
}

function updateRecentUsersTable(users) {
    // Ambil 5 pengguna terbaru
    const recentUsers = users.slice(0, 5);
    
    let tbody = '';
    
    if (recentUsers.length === 0) {
        tbody = `
            <tr>
                <td colspan="5" class="text-center py-4">
                    <div class="text-muted">Belum ada data pengguna</div>
                </td>
            </tr>
        `;
    } else {
        recentUsers.forEach(user => {
            const statusBadge = user.status === 'aktif' 
                ? '<span class="badge bg-success">Aktif</span>'
                : '<span class="badge bg-secondary">Nonaktif</span>';
                
            const roleBadge = user.role_id_role === 2
                ? '<span class="badge bg-warning">Panitia</span>'
                : '<span class="badge bg-info">Keuangan</span>';
                
            tbody += `
                <tr>
                    <td>${user.name || '-'}</td>
                    <td>${user.email || '-'}</td>
                    <td>${roleBadge}</td>
                    <td>${statusBadge}</td>
                    <td>${formatDate(user.created_at)}</td>
                </tr>
            `;
        });
    }
    
    $('#recentUsersTable tbody').html(tbody);
    console.log('Recent users table updated with', recentUsers.length, 'users');
}

function showError() {
    // Update statistik dengan error state
    $('#totalUsers').html('<i class="fas fa-exclamation-triangle text-danger"></i>');
    $('#activeUsers').html('<i class="fas fa-exclamation-triangle text-danger"></i>');
    $('#panitiaUsers').html('<i class="fas fa-exclamation-triangle text-danger"></i>');
    $('#keuanganUsers').html('<i class="fas fa-exclamation-triangle text-danger"></i>');
    
    // Update tabel dengan pesan error
    $('#recentUsersTable tbody').html(`
        <tr>
            <td colspan="5" class="text-center py-4">
                <div class="text-danger">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    Gagal memuat data. Silakan refresh halaman.
                </div>
            </td>
        </tr>
    `);
    
    showToast('Gagal memuat data dashboard', 'error');
}

function formatDate(dateString) {
    if (!dateString) return '-';
    
    try {
        const date = new Date(dateString);
        return date.toLocaleDateString('id-ID', {
            year: 'numeric',
            month: 'short',
            day: 'numeric'
        });
    } catch (e) {
        return '-';
    }
}

function showToast(message, type = 'info') {
    const toastHtml = `
        <div class="toast align-items-center text-white bg-${type === 'error' ? 'danger' : type} border-0" role="alert">
            <div class="d-flex">
                <div class="toast-body">
                    ${message}
                </div>
                <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
            </div>
        </div>
    `;
    
    if (!$('#toastContainer').length) {
        $('body').append('<div id="toastContainer" class="toast-container position-fixed top-0 end-0 p-3"></div>');
    }
    
    const $toast = $(toastHtml);
    $('#toastContainer').append($toast);
    
    const toast = new bootstrap.Toast($toast[0]);
    toast.show();
    
    $toast.on('hidden.bs.toast', function() {
        $(this).remove();
    });
}
</script>
@endsection