@extends('layouts.index')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Manajemen Konfirmasi Pembayaran</h3>
                    <div class="card-tools">
                        <button type="button" class="btn btn-tool" data-card-widget="collapse">
                            <i class="fas fa-minus"></i>
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    <!-- Filter Section -->
                    <div class="row mb-3">
                        <div class="col-md-3">
                            <select id="statusFilter" class="form-control">
                                <option value="">Semua Status</option>
                                <option value="pending">Menunggu Konfirmasi</option>
                                <option value="approved">Disetujui</option>
                                <option value="declined">Ditolak</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <select id="eventFilter" class="form-control">
                                <option value="">Semua Event</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <input type="date" id="dateFilter" class="form-control" placeholder="Filter Tanggal">
                        </div>
                        <div class="col-md-3">
                            <button onclick="loadRegistrations()" class="btn btn-primary">
                                <i class="fas fa-search"></i> Filter
                            </button>
                            <button onclick="resetFilters()" class="btn btn-secondary">
                                <i class="fas fa-refresh"></i> Reset
                            </button>
                        </div>
                    </div>

                    <!-- Statistics Cards -->
                    <div class="row mb-4">
                        <div class="col-md-3">
                            <div class="small-box bg-warning">
                                <div class="inner">
                                    <h3 id="pendingCount">0</h3>
                                    <p>Menunggu Konfirmasi</p>
                                </div>
                                <div class="icon">
                                    <i class="fas fa-clock"></i>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="small-box bg-success">
                                <div class="inner">
                                    <h3 id="approvedCount">0</h3>
                                    <p>Disetujui</p>
                                </div>
                                <div class="icon">
                                    <i class="fas fa-check"></i>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="small-box bg-danger">
                                <div class="inner">
                                    <h3 id="declinedCount">0</h3>
                                    <p>Ditolak</p>
                                </div>
                                <div class="icon">
                                    <i class="fas fa-times"></i>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="small-box bg-info">
                                <div class="inner">
                                    <h3 id="totalCount">0</h3>
                                    <p>Total Registrasi</p>
                                </div>
                                <div class="icon">
                                    <i class="fas fa-users"></i>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Data Table -->
                    <div class="table-responsive">
                        <table id="registrationTable" class="table table-bordered table-striped">
                            <thead>
                                <tr>
                                    <th>ID Registrasi</th>
                                    <th>Nama Peserta</th>
                                    <th>Event</th>
                                    <th>Sesi</th>
                                    <th>Tanggal Sesi</th>
                                    <th>Biaya</th>
                                    <th>Status</th>
                                    <th>Bukti Pembayaran</th>
                                    <th>Tanggal Daftar</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody id="registrationTableBody">
                                <!-- Data will be loaded here -->
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    <div class="row mt-3">
                        <div class="col-sm-12 col-md-5">
                            <div class="dataTables_info" id="tableInfo">
                                Menampilkan 0 sampai 0 dari 0 entri
                            </div>
                        </div>
                        <div class="col-sm-12 col-md-7">
                            <div class="dataTables_paginate paging_simple_numbers" id="tablePagination">
                                <!-- Pagination will be loaded here -->
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal for Payment Proof -->
<div class="modal fade" id="paymentProofModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Bukti Pembayaran</h5>
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <div class="modal-body text-center">
                <img id="paymentProofImage" src="" alt="Bukti Pembayaran" class="img-fluid">
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal for Confirmation -->
<div class="modal fade" id="confirmationModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="confirmationModalTitle">Konfirmasi Aksi</h5>
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <p id="confirmationModalMessage"></p>
                <div id="declineReasonSection" style="display: none;">
                    <label for="declineReason">Alasan Penolakan:</label>
                    <textarea id="declineReason" class="form-control" rows="3" placeholder="Masukkan alasan penolakan..."></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                <button type="button" class="btn btn-primary" id="confirmActionBtn">Konfirmasi</button>
            </div>
        </div>
    </div>
</div>

<!-- Loading Overlay -->
<div id="loadingOverlay" class="overlay" style="display: none;">
    <i class="fas fa-2x fa-sync-alt fa-spin"></i>
</div>
@endsection

@section('ExtraCss')
<style>
    .overlay {
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background-color: rgba(0, 0, 0, 0.5);
        display: flex;
        justify-content: center;
        align-items: center;
        z-index: 9999;
        color: white;
    }
    
    .status-badge {
        padding: 0.25rem 0.5rem;
        border-radius: 0.25rem;
        font-size: 0.75rem;
        font-weight: 600;
        text-transform: uppercase;
    }
    
    .status-pending {
        background-color: #ffc107;
        color: #212529;
    }
    
    .status-approved {
        background-color: #28a745;
        color: white;
    }
    
    .status-declined {
        background-color: #dc3545;
        color: white;
    }
    
    .payment-proof-thumbnail {
        width: 50px;
        height: 50px;
        object-fit: cover;
        border-radius: 4px;
        cursor: pointer;
    }
    
    .action-buttons .btn {
        margin-right: 5px;
        margin-bottom: 5px;
    }
    
    .table td {
        vertical-align: middle;
    }
    
    .small-box {
        border-radius: 0.25rem;
        box-shadow: 0 0 1px rgba(0,0,0,.125), 0 1px 3px rgba(0,0,0,.2);
        display: block;
        margin-bottom: 20px;
        position: relative;
    }
    
    .small-box > .inner {
        padding: 10px;
    }
    
    .small-box .icon {
        color: rgba(0,0,0,.15);
        z-index: 0;
    }
    
    .small-box .icon > i {
        font-size: 70px;
        position: absolute;
        right: 15px;
        top: 15px;
        transition: transform .3s linear;
    }
    
    .small-box:hover .icon > i {
        transform: scale(1.1);
    }
    
    .small-box h3 {
        font-size: 2.2rem;
        font-weight: 700;
        margin: 0 0 10px;
        padding: 0;
        white-space: nowrap;
    }
</style>
@endsection

@section('ExtraJS')
<script>
    let currentPage = 1;
    let totalPages = 1;
    let currentFilters = {
        status: '',
        event: '',
        date: ''
    };

    // API Base URL - sesuaikan dengan URL backend Node.js Anda
    const API_BASE_URL = 'http://localhost:3000/api';

    $(document).ready(function() {
        loadRegistrations();
        loadEvents();
        setupEventHandlers();
    });

    function setupEventHandlers() {
        // Filter change handlers
        $('#statusFilter, #eventFilter, #dateFilter').change(function() {
            currentPage = 1;
            loadRegistrations();
        });

        // Confirmation modal handlers
        $('#confirmActionBtn').click(function() {
            const action = $(this).data('action');
            const registrationId = $(this).data('registration-id');
            const reason = $('#declineReason').val();
            
            if (action === 'decline' && !reason.trim()) {
                alert('Alasan penolakan harus diisi!');
                return;
            }
            
            updateRegistrationStatus(registrationId, action, reason);
            $('#confirmationModal').modal('hide');
        });
    }

    function showLoading() {
        $('#loadingOverlay').show();
    }

    function hideLoading() {
        $('#loadingOverlay').hide();
    }

    function loadRegistrations() {
        showLoading();
        
        const filters = {
            status: $('#statusFilter').val(),
            event: $('#eventFilter').val(),
            date: $('#dateFilter').val(),
            page: currentPage,
            limit: 10
        };

        $.ajax({
            url: `${API_BASE_URL}/keuangan/registrations`,
            method: 'GET',
            data: filters,
            success: function(response) {
                if (response.success) {
                    displayRegistrations(response.data.registrations);
                    updateStatistics(response.data.statistics);
                    updatePagination(response.data.pagination);
                } else {
                    showError('Gagal memuat data registrasi');
                }
            },
            error: function(xhr, status, error) {
                console.error('Error loading registrations:', error);
                showError('Terjadi kesalahan saat memuat data');
            },
            complete: function() {
                hideLoading();
            }
        });
    }

    function loadEvents() {
        $.ajax({
            url: `${API_BASE_URL}/events`,
            method: 'GET',
            success: function(response) {
                if (response.success) {
                    const eventSelect = $('#eventFilter');
                    eventSelect.empty().append('<option value="">Semua Event</option>');
                    
                    response.data.forEach(event => {
                        eventSelect.append(`<option value="${event.id_event}">${event.nama_event}</option>`);
                    });
                }
            },
            error: function(xhr, status, error) {
                console.error('Error loading events:', error);
            }
        });
    }

    function displayRegistrations(registrations) {
        const tbody = $('#registrationTableBody');
        tbody.empty();

        if (registrations.length === 0) {
            tbody.append(`
                <tr>
                    <td colspan="10" class="text-center">Tidak ada data registrasi</td>
                </tr>
            `);
            return;
        }

        registrations.forEach(registration => {
            const row = createRegistrationRow(registration);
            tbody.append(row);
        });
    }

    function createRegistrationRow(registration) {
        const statusClass = getStatusClass(registration.status);
        const statusText = getStatusText(registration.status);
        const formattedDate = formatDate(registration.tanggal_sesi);
        const formattedPrice = formatCurrency(registration.biaya_sesi);
        const createdAt = formatDateTime(registration.created_at);
        
        let paymentProofCell = '';
        if (registration.bukti_transaksi) {
            paymentProofCell = `
                <img src="${API_BASE_URL}/uploads/${registration.bukti_transaksi}" 
                     class="payment-proof-thumbnail" 
                     onclick="showPaymentProof('${API_BASE_URL}/uploads/${registration.bukti_transaksi}')"
                     alt="Bukti Pembayaran">
            `;
        } else {
            paymentProofCell = '<span class="text-muted">-</span>';
        }

        const actionButtons = createActionButtons(registration);

        return `
            <tr>
                <td>${registration.id_registrasi}</td>
                <td>${registration.user_name}</td>
                <td>${registration.nama_event}</td>
                <td>${registration.nama_sesi}</td>
                <td>${formattedDate}</td>
                <td>${formattedPrice}</td>
                <td><span class="status-badge status-${registration.status}">${statusText}</span></td>
                <td>${paymentProofCell}</td>
                <td>${createdAt}</td>
                <td class="action-buttons">${actionButtons}</td>
            </tr>
        `;
    }

    function createActionButtons(registration) {
        let buttons = '';
        
        if (registration.status === 'pending') {
            buttons += `
                <button class="btn btn-success btn-sm" onclick="confirmAction('${registration.id_registrasi}', 'approve')">
                    <i class="fas fa-check"></i> Setujui
                </button>
                <button class="btn btn-danger btn-sm" onclick="confirmAction('${registration.id_registrasi}', 'decline')">
                    <i class="fas fa-times"></i> Tolak
                </button>
            `;
        } else if (registration.status === 'declined') {
            buttons += `
                <button class="btn btn-success btn-sm" onclick="confirmAction('${registration.id_registrasi}', 'approve')">
                    <i class="fas fa-check"></i> Setujui
                </button>
            `;
        } else if (registration.status === 'approved') {
            buttons += `
                <button class="btn btn-warning btn-sm" onclick="confirmAction('${registration.id_registrasi}', 'decline')">
                    <i class="fas fa-times"></i> Batalkan
                </button>
            `;
        }
        
        buttons += `
            <button class="btn btn-info btn-sm" onclick="viewDetails('${registration.id_registrasi}')">
                <i class="fas fa-eye"></i> Detail
            </button>
        `;
        
        return buttons;
    }

    function confirmAction(registrationId, action) {
        const modal = $('#confirmationModal');
        const title = modal.find('#confirmationModalTitle');
        const message = modal.find('#confirmationModalMessage');
        const reasonSection = modal.find('#declineReasonSection');
        const confirmBtn = modal.find('#confirmActionBtn');
        
        reasonSection.hide();
        $('#declineReason').val('');
        
        if (action === 'approve') {
            title.text('Konfirmasi Persetujuan');
            message.text('Apakah Anda yakin ingin menyetujui registrasi ini?');
            confirmBtn.removeClass('btn-danger').addClass('btn-success').text('Setujui');
        } else if (action === 'decline') {
            title.text('Konfirmasi Penolakan');
            message.text('Apakah Anda yakin ingin menolak registrasi ini?');
            reasonSection.show();
            confirmBtn.removeClass('btn-success').addClass('btn-danger').text('Tolak');
        }
        
        confirmBtn.data('action', action).data('registration-id', registrationId);
        modal.modal('show');
    }

    function updateRegistrationStatus(registrationId, action, reason = '') {
        showLoading();
        
        const data = {
            action: action,
            reason: reason
        };
        
        $.ajax({
            url: `${API_BASE_URL}/keuangan/registrations/${registrationId}/status`,
            method: 'PUT',
            contentType: 'application/json',
            data: JSON.stringify(data),
            success: function(response) {
                if (response.success) {
                    showSuccess(response.message);
                    loadRegistrations();
                } else {
                    showError(response.message);
                }
            },
            error: function(xhr, status, error) {
                console.error('Error updating status:', error);
                showError('Terjadi kesalahan saat memperbarui status');
            },
            complete: function() {
                hideLoading();
            }
        });
    }

    function showPaymentProof(imageUrl) {
        $('#paymentProofImage').attr('src', imageUrl);
        $('#paymentProofModal').modal('show');
    }

    function viewDetails(registrationId) {
        // Implementasi untuk melihat detail registrasi
        // Bisa redirect ke halaman detail atau buka modal
        window.open(`/registrations/${registrationId}`, '_blank');
    }

    function updateStatistics(statistics) {
        $('#pendingCount').text(statistics.pending || 0);
        $('#approvedCount').text(statistics.approved || 0);
        $('#declinedCount').text(statistics.declined || 0);
        $('#totalCount').text(statistics.total || 0);
    }

    function updatePagination(pagination) {
        currentPage = pagination.current_page;
        totalPages = pagination.total_pages;
        
        const info = `Menampilkan ${pagination.from} sampai ${pagination.to} dari ${pagination.total} entri`;
        $('#tableInfo').text(info);
        
        // Create pagination buttons
        let paginationHtml = '';
        
        if (currentPage > 1) {
            paginationHtml += `<button class="btn btn-sm btn-outline-primary" onclick="changePage(${currentPage - 1})">Previous</button>`;
        }
        
        for (let i = Math.max(1, currentPage - 2); i <= Math.min(totalPages, currentPage + 2); i++) {
            const activeClass = i === currentPage ? 'btn-primary' : 'btn-outline-primary';
            paginationHtml += `<button class="btn btn-sm ${activeClass}" onclick="changePage(${i})">${i}</button>`;
        }
        
        if (currentPage < totalPages) {
            paginationHtml += `<button class="btn btn-sm btn-outline-primary" onclick="changePage(${currentPage + 1})">Next</button>`;
        }
        
        $('#tablePagination').html(paginationHtml);
    }

    function changePage(page) {
        currentPage = page;
        loadRegistrations();
    }

    function resetFilters() {
        $('#statusFilter').val('');
        $('#eventFilter').val('');
        $('#dateFilter').val('');
        currentPage = 1;
        loadRegistrations();
    }

    // Utility functions
    function getStatusClass(status) {
        const statusMap = {
            'pending': 'warning',
            'approved': 'success',
            'declined': 'danger'
        };
        return statusMap[status] || 'secondary';
    }

    function getStatusText(status) {
        const statusMap = {
            'pending': 'Menunggu Konfirmasi',
            'approved': 'Disetujui',
            'declined': 'Ditolak'
        };
        return statusMap[status] || status;
    }

    function formatDate(dateString) {
        return new Date(dateString).toLocaleDateString('id-ID', {
            day: 'numeric',
            month: 'long',
            year: 'numeric'
        });
    }

    function formatDateTime(dateString) {
        return new Date(dateString).toLocaleString('id-ID', {
            day: 'numeric',
            month: 'short',
            year: 'numeric',
            hour: '2-digit',
            minute: '2-digit'
        });
    }

    function formatCurrency(amount) {
        if (amount === 0) return 'Gratis';
        return `Rp ${parseInt(amount).toLocaleString('id-ID')}`;
    }

    function showSuccess(message) {
        // Implementasi notifikasi success (bisa menggunakan toastr, sweet alert, dll)
        alert('Success: ' + message);
    }

    function showError(message) {
        // Implementasi notifikasi error
        alert('Error: ' + message);
    }
</script>
@endsection