@extends('layouts.index')

@section('content')
<div class="container-fluid px-4">
    <!-- Header Section -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-0 shadow-sm bg-gradient-primary text-white">
                <div class="card-body py-4">
                    <div class="d-flex align-items-center justify-content-between">
                        <div class="d-flex align-items-center">
                            <div class="me-3">
                                <i class="fas fa-users-cog fa-2x opacity-75"></i>
                            </div>
                            <div>
                                <h4 class="mb-1 fw-bold">Pengelolaan Akun Panitia & Keuangan</h4>
                                <p class="mb-0 opacity-90">Manajemen akun pengguna sistem</p>
                            </div>
                        </div>
                        <div class="text-end">
                            <div class="h5 mb-0">{{ count($users) }}</div>
                            <small class="opacity-75">Total Akun</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Alert Messages -->
    @if(session('success'))
        <div class="alert alert-success border-0 shadow-sm alert-dismissible fade show" role="alert">
            <div class="d-flex align-items-center">
                <i class="fas fa-check-circle me-2"></i>
                <div>{{ session('success') }}</div>
            </div>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger border-0 shadow-sm alert-dismissible fade show" role="alert">
            <div class="d-flex align-items-center">
                <i class="fas fa-exclamation-circle me-2"></i>
                <div>{{ session('error') }}</div>
            </div>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <!-- Filter Section -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-4">
                    <label class="form-label fw-semibold">
                        <i class="fas fa-search me-2 text-muted"></i>Cari Pengguna
                    </label>
                    <input type="text" id="searchInput" class="form-control" placeholder="Cari nama atau email...">
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-semibold">
                        <i class="fas fa-filter me-2 text-muted"></i>Filter Role
                    </label>
                    <select id="roleFilter" class="form-select">
                        <option value="">Semua Role</option>
                        <option value="2">Panitia</option>
                        <option value="4">Keuangan</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-semibold">
                        <i class="fas fa-toggle-on me-2 text-muted"></i>Filter Status
                    </label>
                    <select id="statusFilter" class="form-select">
                        <option value="">Semua Status</option>
                        <option value="aktif">Aktif</option>
                        <option value="nonaktif">Nonaktif</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label fw-semibold text-white">.</label>
                    <button type="button" id="resetFilter" class="btn btn-outline-secondary w-100">
                        <i class="fas fa-undo me-2"></i>Reset
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Data Table -->
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-light border-0 py-3">
            <div class="d-flex justify-content-between align-items-center">
                <h6 class="mb-0 fw-semibold text-dark">
                    <i class="fas fa-table me-2 text-muted"></i>Data Akun Pengguna
                </h6>
                <div class="d-flex align-items-center">
                    <span class="badge bg-light text-dark me-2">Menampilkan: <span id="showingCount">{{ count($users) }}</span></span>
                    <small class="text-muted">dari {{ count($users) }} total</small>
                </div>
            </div>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover table-striped mb-0" id="dataTable">
                    <thead class="table-dark">
                        <tr>
                            <th class="text-center" style="width: 80px;">ID</th>
                            <th style="min-width: 200px;">Nama</th>
                            <th style="min-width: 200px;">Email</th>
                            <th class="text-center" style="width: 120px;">Role</th>
                            <th class="text-center" style="width: 100px;">Status</th>
                            <th class="text-center" style="width: 180px;">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($users as $user)
                        <tr data-role="{{ $user['role_id_role'] }}" data-status="{{ $user['status'] }}" data-name="{{ strtolower($user['name']) }}" data-email="{{ strtolower($user['email']) }}">
                            <td class="text-center fw-bold">{{ $user['id'] }}</td>
                            <td>
                                <div class="d-flex align-items-center">
                                    <div class="bg-gradient-primary rounded-circle d-flex align-items-center justify-content-center text-white me-3"
                                         style="width: 35px; height: 35px; min-width: 35px;">
                                        <i class="fas fa-user"></i>
                                    </div>
                                    <div class="fw-semibold">{{ $user['name'] }}</div>
                                </div>
                            </td>
                            <td class="text-muted">{{ $user['email'] }}</td>
                            <td class="text-center">
                                @if($user['role_id_role'] == 2)
                                    <span class="badge bg-info text-white">
                                        <i class="fas fa-users me-1"></i>Panitia
                                    </span>
                                @elseif($user['role_id_role'] == 4)
                                    <span class="badge bg-warning text-dark">
                                        <i class="fas fa-calculator me-1"></i>Keuangan
                                    </span>
                                @else
                                    <span class="badge bg-secondary">
                                        <i class="fas fa-question me-1"></i>Unknown
                                    </span>
                                @endif
                            </td>
                            <td class="text-center">
                                @if($user['status'] == 'aktif')
                                    <span class="badge bg-success">
                                        <i class="fas fa-check-circle me-1"></i>Aktif
                                    </span>
                                @else
                                    <span class="badge bg-danger">
                                        <i class="fas fa-times-circle me-1"></i>Nonaktif
                                    </span>
                                @endif
                            </td>
                            <td class="text-center">
                                <div class="btn-group" role="group">
                                    {{-- Edit Button --}}
                                    <button type="button" class="btn btn-sm btn-primary" 
                                            data-bs-toggle="modal" data-bs-target="#editModal-{{ $user['id'] }}"
                                            title="Edit Akun">
                                        <i class="fas fa-edit"></i>
                                    </button>

                                    {{-- Status Toggle --}}
                                    <form action="{{ route('admin.pengguna.status', $user['id']) }}" method="POST" class="d-inline">
                                        @csrf
                                        @method('PATCH')
                                        <input type="hidden" name="status" value="{{ $user['status'] == 'aktif' ? 'nonaktif' : 'aktif' }}">
                                        <button type="submit" 
                                                class="btn btn-sm {{ $user['status'] == 'aktif' ? 'btn-warning' : 'btn-success' }}" 
                                                onclick="return confirm('Yakin ingin mengubah status akun ini?')"
                                                title="{{ $user['status'] == 'aktif' ? 'Nonaktifkan' : 'Aktifkan' }}">
                                            <i class="fas {{ $user['status'] == 'aktif' ? 'fa-pause' : 'fa-play' }}"></i>
                                        </button>
                                    </form>

                                    {{-- Delete Button --}}
                                    <form action="{{ route('admin.pengguna.destroy', $user['id']) }}" method="POST" class="d-inline" 
                                          onsubmit="return confirm('Yakin ingin menghapus akun ini? Tindakan ini tidak dapat dibatalkan!')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-danger" title="Hapus Akun">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr id="noDataRow">
                            <td colspan="6" class="text-center py-5">
                                <div class="text-muted">
                                    <i class="fas fa-users fa-3x mb-3 opacity-25"></i>
                                    <div class="h5">Tidak ada data akun</div>
                                    <p class="mb-0">Belum ada akun yang terdaftar dalam sistem</p>
                                </div>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
                
                <!-- No Data Message for Filtered Results -->
                <div id="noFilterResults" class="text-center py-5" style="display: none;">
                    <div class="text-muted">
                        <i class="fas fa-search fa-3x mb-3 opacity-25"></i>
                        <div class="h5">Tidak ada data yang cocok</div>
                        <p class="mb-0">Coba ubah kriteria pencarian atau filter</p>
                    </div>
                </div>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

{{-- Edit Modals --}}
@foreach($users as $user)
<div class="modal fade" id="editModal-{{ $user['id'] }}" tabindex="-1" aria-labelledby="editModalLabel-{{ $user['id'] }}" aria-hidden="true">
    <div class="modal-dialog">
        <form action="{{ route('admin.pengguna.update', $user['id']) }}" method="POST">
            @csrf
            @method('PUT')
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title fw-bold" id="editModalLabel-{{ $user['id'] }}">
                        <i class="fas fa-user-edit me-2"></i>Edit Akun - {{ $user['name'] }}
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-12">
                            <div class="mb-3">
                                <label for="name-{{ $user['id'] }}" class="form-label fw-semibold">
                                    <i class="fas fa-user me-2 text-primary"></i>Nama Lengkap
                                </label>
                                <input type="text" name="name" id="name-{{ $user['id'] }}" 
                                       class="form-control" value="{{ $user['name'] }}" required>
                                <div class="form-text">Masukkan nama lengkap pengguna</div>
                            </div>
                        </div>
                        <div class="col-md-12">
                            <div class="mb-3">
                                <label for="email-{{ $user['id'] }}" class="form-label fw-semibold">
                                    <i class="fas fa-envelope me-2 text-primary"></i>Alamat Email
                                </label>
                                <input type="email" name="email" id="email-{{ $user['id'] }}" 
                                       class="form-control" value="{{ $user['email'] }}" required>
                                <div class="form-text">Email akan digunakan untuk login</div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="alert alert-info border-0">
                        <i class="fas fa-info-circle me-2"></i>
                        <strong>Info:</strong> Role dan status akun dapat diubah melalui tombol aksi di tabel utama.
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save me-2"></i>Simpan Perubahan
                    </button>
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="fas fa-times me-2"></i>Batal
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>
@endforeach
@endsection

@section('ExtraCSS')
<style>
.bg-gradient-primary {
    background: linear-gradient(135deg, #007bff 0%, #0056b3 100%);
}

.table-hover tbody tr:hover {
    background-color: rgba(0, 123, 255, 0.05);
    transform: scale(1.01);
    transition: all 0.2s ease;
}

.btn-group .btn {
    margin-right: 2px;
}

.btn-group .btn:last-child {
    margin-right: 0;
}

.card {
    transition: all 0.3s ease;
}

.badge {
    font-size: 0.75rem;
    padding: 0.4rem 0.6rem;
}

.form-control:focus, .form-select:focus {
    border-color: #007bff;
    box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.15);
}

.alert {
    border-radius: 0.5rem;
}

.table th {
    font-weight: 600;
    background: linear-gradient(135deg, #343a40 0%, #495057 100%);
}

.table td {
    vertical-align: middle;
    padding: 1rem 0.75rem;
}

.hidden-row {
    display: none !important;
}
</style>
@endsection

@section('ExtraJS')
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Auto dismiss alerts
    const alerts = document.querySelectorAll('.alert');
    alerts.forEach(alert => {
        setTimeout(() => {
            const bsAlert = new bootstrap.Alert(alert);
            bsAlert.close();
        }, 5000);
    });

    // Filter functionality
    const searchInput = document.getElementById('searchInput');
    const roleFilter = document.getElementById('roleFilter');
    const statusFilter = document.getElementById('statusFilter');
    const resetButton = document.getElementById('resetFilter');
    const tableRows = document.querySelectorAll('#dataTable tbody tr[data-role]');
    const showingCount = document.getElementById('showingCount');
    const noDataRow = document.getElementById('noDataRow');
    const noFilterResults = document.getElementById('noFilterResults');
    const tableBody = document.querySelector('#dataTable tbody');

    function filterTable() {
        const searchTerm = searchInput.value.toLowerCase();
        const selectedRole = roleFilter.value;
        const selectedStatus = statusFilter.value;
        let visibleCount = 0;

        // Hide original no data row if exists
        if (noDataRow) {
            noDataRow.style.display = 'none';
        }

        // Filter table rows
        tableRows.forEach(row => {
            const name = row.dataset.name || '';
            const email = row.dataset.email || '';
            const role = row.dataset.role || '';
            const status = row.dataset.status || '';

            const matchesSearch = name.includes(searchTerm) || email.includes(searchTerm);
            const matchesRole = !selectedRole || role === selectedRole;
            const matchesStatus = !selectedStatus || status === selectedStatus;

            if (matchesSearch && matchesRole && matchesStatus) {
                row.classList.remove('hidden-row');
                visibleCount++;
            } else {
                row.classList.add('hidden-row');
            }
        });

        // Update count
        if (showingCount) {
            showingCount.textContent = visibleCount;
        }

        // Show/hide no results message
        if (visibleCount === 0 && (searchTerm || selectedRole || selectedStatus)) {
            // Show filter no results message
            if (noFilterResults) {
                noFilterResults.style.display = 'block';
            }
        } else {
            // Hide filter no results message
            if (noFilterResults) {
                noFilterResults.style.display = 'none';
            }
        }

        // Show original no data message if no data exists and no filters applied
        if (tableRows.length === 0 && !searchTerm && !selectedRole && !selectedStatus) {
            if (noDataRow) {
                noDataRow.style.display = '';
            }
        }
    }

    // Event listeners
    searchInput.addEventListener('input', filterTable);
    roleFilter.addEventListener('change', filterTable);
    statusFilter.addEventListener('change', filterTable);

    resetButton.addEventListener('click', function() {
        searchInput.value = '';
        roleFilter.value = '';
        statusFilter.value = '';
        filterTable();
    });

    // Initialize tooltips
    const tooltipTriggerList = [].slice.call(document.querySelectorAll('[title]'));
    const tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });
});
</script>
@endsection