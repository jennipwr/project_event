@extends('layouts.index')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-lg-12">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-gradient-primary text-white">
                    <h4 class="mb-0 fw-bold">
                        <i class="fas fa-certificate me-2"></i>
                        Unggah Sertifikat Peserta
                    </h4>
                </div>
                <div class="card-body p-4">
                    {{-- Filter Event dan Sesi --}}
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <label for="event_id" class="form-label fw-semibold">
                                <i class="fas fa-calendar-alt me-1"></i>
                                Pilih Event
                            </label>
                            <div class="position-relative">
                                <select id="event_id" class="form-select form-select-lg shadow-sm" required>
                                    <option value="">Pilih Event</option>
                                </select>
                                <div id="eventLoader" class="position-absolute top-50 end-0 translate-middle-y pe-3" style="display: none;">
                                    <div class="spinner-border spinner-border-sm text-primary" role="status">
                                        <span class="visually-hidden">Loading...</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label for="sesi_id" class="form-label fw-semibold">
                                <i class="fas fa-clock me-1"></i>
                                Pilih Sesi
                            </label>
                            <div class="position-relative">
                                <select id="sesi_id" class="form-select form-select-lg shadow-sm" required disabled>
                                    <option value="">Pilih Event terlebih dahulu</option>
                                </select>
                                <div id="sesiLoader" class="position-absolute top-50 end-0 translate-middle-y pe-3" style="display: none;">
                                    <div class="spinner-border spinner-border-sm text-primary" role="status">
                                        <span class="visually-hidden">Loading...</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Filter status sertifikat --}}
                    <div class="row mb-4" id="filterWrapper" style="display:none">
                        <div class="col-md-4">
                            <label for="filterSertifikat" class="form-label fw-semibold">
                                <i class="fas fa-filter me-1"></i>
                                Filter Status Sertifikat
                            </label>
                            <select id="filterSertifikat" class="form-select shadow-sm">
                                <option value="all">Semua Status</option>
                                <option value="uploaded">Sudah Upload</option>
                                <option value="not_uploaded">Belum Upload</option>
                            </select>
                        </div>
                        <div class="col-md-8 d-flex align-items-end">
                            <div class="badge bg-info text-dark me-2 p-2">
                                <i class="fas fa-info-circle me-1"></i>
                                Total: <span id="totalCount">0</span> peserta
                            </div>
                            <div class="badge bg-success me-2 p-2">
                                <i class="fas fa-check-circle me-1"></i>
                                Uploaded: <span id="uploadedCount">0</span>
                            </div>
                            <div class="badge bg-warning text-dark p-2">
                                <i class="fas fa-clock me-1"></i>
                                Pending: <span id="pendingCount">0</span>
                            </div>
                        </div>
                    </div>

                    {{-- Loading untuk tabel --}}
                    <div id="tableLoader" class="text-center py-5" style="display: none;">
                        <div class="spinner-border text-primary mb-3" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                        <p class="text-muted">Memuat data peserta...</p>
                    </div>

                    {{-- Tabel Peserta --}}
                    <div id="pesertaTableWrapper" style="display: none;">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h5 class="mb-0 fw-bold text-primary">
                                <i class="fas fa-users me-2"></i>
                                Daftar Peserta Hadir
                            </h5>
                        </div>
                        
                        <div class="table-responsive shadow-sm rounded">
                            <table class="table table-hover align-middle mb-0" id="pesertaTable">
                                <thead class="table-primary">
                                    <tr>
                                        <th class="fw-bold">
                                            <i class="fas fa-user me-1"></i>
                                            Nama Peserta
                                        </th>
                                        <th class="fw-bold">
                                            <i class="fas fa-envelope me-1"></i>
                                            Email
                                        </th>
                                        <th class="fw-bold text-center">
                                            <i class="fas fa-certificate me-1"></i>
                                            Status Sertifikat
                                        </th>
                                        <th class="fw-bold text-center">
                                            <i class="fas fa-cogs me-1"></i>
                                            Aksi
                                        </th>
                                    </tr>
                                </thead>
                                <tbody></tbody>
                            </table>
                        </div>

                        {{-- Empty state --}}
                        <div id="emptyState" class="text-center py-5" style="display: none;">
                            <div class="mb-3">
                                <i class="fas fa-inbox fa-3x text-muted"></i>
                            </div>
                            <h5 class="text-muted">Tidak ada peserta ditemukan</h5>
                            <p class="text-muted mb-0">Belum ada peserta yang hadir pada sesi ini atau filter yang dipilih tidak menampilkan hasil.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Upload Progress Modal --}}
<div class="modal fade" id="uploadModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-body text-center p-4">
                <div class="spinner-border text-primary mb-3" role="status">
                    <span class="visually-hidden">Uploading...</span>
                </div>
                <h5>Mengunggah Sertifikat...</h5>
                <p class="text-muted mb-0">Mohon tunggu, file sedang diproses.</p>
            </div>
        </div>
    </div>
</div>

<style>
.bg-gradient-primary {
    background: linear-gradient(135deg, #007bff 0%, #0056b3 100%);
}

.card {
    border-radius: 15px;
    transition: all 0.3s ease;
}

.form-select:focus, .form-control:focus {
    border-color: #007bff;
    box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.25);
}

.table th {
    border-top: none;
    background: linear-gradient(135deg, #e3f2fd 0%, #bbdefb 100%);
}

.table-hover tbody tr:hover {
    background-color: rgba(0, 123, 255, 0.05);
}

.btn {
    border-radius: 8px;
    transition: all 0.3s ease;
}

.btn:hover {
    transform: translateY(-1px);
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
}

.badge {
    border-radius: 8px;
    font-size: 0.85rem;
}

.status-badge {
    font-size: 0.8rem;
    padding: 6px 12px;
    border-radius: 20px;
    font-weight: 600;
}

.fade-in {
    animation: fadeIn 0.5s ease-in-out;
}

@keyframes fadeIn {
    from { opacity: 0; transform: translateY(20px); }
    to { opacity: 1; transform: translateY(0); }
}

.upload-form {
    background: #f8f9fa;
    border-radius: 8px;
    padding: 15px;
    border: 2px dashed #dee2e6;
    transition: all 0.3s ease;
}

.upload-form:hover {
    border-color: #007bff;
    background: #e3f2fd;
}

.file-input-wrapper {
    position: relative;
    overflow: hidden;
    display: inline-block;
    width: 100%;
}

.file-input-wrapper input[type=file] {
    font-size: 100px;
    position: absolute;
    left: 0;
    top: 0;
    opacity: 0;
    cursor: pointer;
    width: 100%;
    height: 100%;
}
</style>
@endsection

@section('ExtraJS')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const eventSelect = document.getElementById('event_id');
    const sesiSelect = document.getElementById('sesi_id');
    const pesertaTableWrapper = document.getElementById('pesertaTableWrapper');
    const pesertaTableBody = document.querySelector('#pesertaTable tbody');
    const filterSertifikat = document.getElementById('filterSertifikat');
    const filterWrapper = document.getElementById('filterWrapper');
    const emptyState = document.getElementById('emptyState');
    const tableLoader = document.getElementById('tableLoader');
    const eventLoader = document.getElementById('eventLoader');
    const sesiLoader = document.getElementById('sesiLoader');
    
    // Counter elements
    const totalCount = document.getElementById('totalCount');
    const uploadedCount = document.getElementById('uploadedCount');
    const pendingCount = document.getElementById('pendingCount');

    let pesertaData = [];

    // Load events with animation
    function loadEvents() {
        eventLoader.style.display = 'block';
        eventSelect.disabled = true;
        
        fetch(`/panitia/scan/events`)
            .then(res => res.json())
            .then(data => {
                setTimeout(() => { // Small delay for better UX
                    eventSelect.innerHTML = '<option value="">Pilih Event</option>';
                    if (data.success) {
                        data.data.forEach(event => {
                            eventSelect.innerHTML += `<option value="${event.id_event}">${event.nama_event}</option>`;
                        });
                    }
                    eventLoader.style.display = 'none';
                    eventSelect.disabled = false;
                }, 500);
            })
            .catch(err => {
                console.error('Error loading events:', err);
                eventLoader.style.display = 'none';
                eventSelect.disabled = false;
                showToast('Gagal memuat daftar event', 'error');
            });
    }

    function formatDate(dateString) {
        if (!dateString) return 'Tanggal tidak tersedia';
        
        try {
            const date = new Date(dateString);
            
            // Check if date is valid
            if (isNaN(date.getTime())) {
                return 'Format tanggal tidak valid';
            }
            
            // Format to Indonesian locale
            const options = {
                year: 'numeric',
                month: 'long',
                day: 'numeric',
                timeZone: 'Asia/Jakarta'
            };
            
            return date.toLocaleDateString('id-ID', options);
        } catch (error) {
            console.error('Error formatting date:', error);
            return 'Error format tanggal';
        }
    }

    function formatDateTime(dateTimeString) {
        if (!dateTimeString) return 'Waktu tidak tersedia';
        
        try {
            const date = new Date(dateTimeString);
            
            // Check if date is valid
            if (isNaN(date.getTime())) {
                return 'Format waktu tidak valid';
            }
            
            // Format to Indonesian locale with time
            const options = {
                year: 'numeric',
                month: 'long', 
                day: 'numeric',
                hour: '2-digit',
                minute: '2-digit',
                timeZone: 'Asia/Jakarta'
            };
            
            return date.toLocaleDateString('id-ID', options);
        } catch (error) {
            console.error('Error formatting datetime:', error);
            return 'Error format waktu';
        }
    }

    function formatTime(timeString) {
        if (!timeString) return 'Waktu tidak tersedia';
        
        try {
            // If it's a full datetime string
            if (timeString.includes('T') || timeString.includes(' ')) {
                const date = new Date(timeString);
                if (isNaN(date.getTime())) {
                    return 'Format waktu tidak valid';
                }
                return date.toLocaleTimeString('id-ID', { 
                    hour: '2-digit', 
                    minute: '2-digit',
                    timeZone: 'Asia/Jakarta'
                });
            }
            
            // If it's just time format (HH:MM:SS or HH:MM)
            const timeParts = timeString.split(':');
            if (timeParts.length >= 2) {
                const hour = timeParts[0].padStart(2, '0');
                const minute = timeParts[1].padStart(2, '0');
                return `${hour}:${minute}`;
            }
            
            return timeString;
        } catch (error) {
            console.error('Error formatting time:', error);
            return 'Error format waktu';
        }
    }

    function loadSessions(eventId) {
        sesiLoader.style.display = 'block';
        sesiSelect.disabled = true;
        sesiSelect.innerHTML = '<option value="">Loading sesi...</option>';
        
        fetch(`/panitia/events/${eventId}/sessions`)
            .then(res => res.json())
            .then(data => {
                setTimeout(() => {
                    sesiSelect.innerHTML = '<option value="">Pilih Sesi</option>';
                    if (data.success) {
                        data.data.forEach(sesi => {
                            sesiSelect.innerHTML += `<option value="${sesi.id_sesi}">${sesi.nama_sesi} (${formatDate(sesi.tanggal_sesi)} - ${formatTime(sesi.waktu_sesi)}  )</option>`;
                        });
                    }
                    sesiLoader.style.display = 'none';
                    sesiSelect.disabled = false;
                }, 500);
            })
            .catch(err => {
                console.error('Error loading sessions:', err);
                sesiLoader.style.display = 'none';
                sesiSelect.disabled = false;
                showToast('Gagal memuat daftar sesi', 'error');
            });
    }

    function loadParticipants(eventId, sesiId) {
        tableLoader.style.display = 'block';
        pesertaTableWrapper.style.display = 'none';
        filterWrapper.style.display = 'none';
        
        fetch(`/panitia/sertifikat/peserta/${eventId}/${sesiId}`)
            .then(res => res.json())
            .then(data => {
                setTimeout(() => {
                    if (data.success) {
                        pesertaData = data.data;
                        renderTable();
                        pesertaTableWrapper.style.display = 'block';
                        pesertaTableWrapper.classList.add('fade-in');
                        filterWrapper.style.display = 'block';
                        filterWrapper.classList.add('fade-in');
                    } else {
                        showToast(data.message || 'Gagal memuat data peserta', 'error');
                    }
                    tableLoader.style.display = 'none';
                }, 700);
            })
            .catch(err => {
                console.error('Error loading participants:', err);
                tableLoader.style.display = 'none';
                showToast('Terjadi kesalahan saat memuat data', 'error');
            });
    }

    eventSelect.addEventListener('change', function () {
        const eventId = this.value;
        pesertaTableWrapper.style.display = 'none';
        filterWrapper.style.display = 'none';
        sesiSelect.innerHTML = '<option value="">Pilih Event terlebih dahulu</option>';
        sesiSelect.disabled = true;

        if (!eventId) return;

        loadSessions(eventId);
    });

    sesiSelect.addEventListener('change', function () {
        const eventId = eventSelect.value;
        const sesiId = this.value;

        if (!eventId || !sesiId) return;

        loadParticipants(eventId, sesiId);
    });

    filterSertifikat.addEventListener('change', renderTable);

    function renderTable() {
        const filter = filterSertifikat.value;
        pesertaTableBody.innerHTML = '';
        
        let filteredData = pesertaData.filter(peserta => {
            const isUploaded = !!peserta.sertifikat_file;
            if (filter === 'uploaded' && !isUploaded) return false;
            if (filter === 'not_uploaded' && isUploaded) return false;
            return true;
        });

        const totalPeserta = pesertaData.length;
        const uploadedPeserta = pesertaData.filter(p => !!p.sertifikat_file).length;
        const pendingPeserta = totalPeserta - uploadedPeserta;
        
        totalCount.textContent = totalPeserta;
        uploadedCount.textContent = uploadedPeserta;
        pendingCount.textContent = pendingPeserta;

        if (filteredData.length === 0) {
            emptyState.style.display = 'block';
            document.querySelector('#pesertaTable').style.display = 'none';
            return;
        }

        emptyState.style.display = 'none';
        document.querySelector('#pesertaTable').style.display = 'table';

        filteredData.forEach(peserta => {
            const isUploaded = !!peserta.sertifikat_file;
            
            pesertaTableBody.innerHTML += `
                <tr class="fade-in">
                    <td class="fw-semibold">${peserta.nama}</td>
                    <td class="text-muted">${peserta.email}</td>
                    <td class="text-center">
                        ${isUploaded ? 
                            '<span class="badge bg-success status-badge"><i class="fas fa-check me-1"></i>Sudah Upload</span>' : 
                            '<span class="badge bg-warning text-dark status-badge"><i class="fas fa-clock me-1"></i>Belum Upload</span>'
                        }
                    </td>
                    <td class="text-center">
                            ${isUploaded ? `
                                <div class="btn-group" role="group">
                                    <a href="/panitia/sertifikat/view/${peserta.sertifikat_file}" target="_blank" 
                                    class="btn btn-sm btn-outline-primary" title="Lihat Sertifikat">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <a href="/panitia/sertifikat/download/${peserta.sertifikat_file}" 
                                    class="btn btn-sm btn-outline-success" title="Download Sertifikat">
                                        <i class="fas fa-download"></i>
                                    </a>
                                </div>
                            ` : `
                            <div class="upload-form">
                                <form class="uploadForm" enctype="multipart/form-data">
                                    <input type="hidden" name="kehadiran_id" value="${peserta.kehadiran_id}">
                                    <input type="hidden" name="registrasi_id_registrasi" value="${peserta.registrasi_id_registrasi}">
                                    <input type="hidden" name="registrasi_pengguna_id" value="${peserta.registrasi_pengguna_id}">
                                    <input type="hidden" name="registrasi_event_id_event" value="${peserta.registrasi_event_id_event}">
                                    <input type="hidden" name="registrasi_event_sesi_id_sesi" value="${peserta.registrasi_event_sesi_id_sesi}">
                                    
                                    <div class="mb-2">
                                        <input type="file" name="file" accept=".pdf,.jpg,.png,.jpeg" required 
                                               class="form-control form-control-sm">
                                        <small class="text-muted">Format: PDF, JPG, PNG (Max: 5MB)</small>
                                    </div>
                                    <button type="submit" class="btn btn-sm btn-success w-100">
                                        <i class="fas fa-upload me-1"></i>Upload Sertifikat
                                    </button>
                                </form>
                            </div>
                        `}
                    </td>
                </tr>
            `;
        });
    }

    document.addEventListener('submit', function (e) {
        if (e.target.matches('.uploadForm')) {
            e.preventDefault();
            const form = e.target;
            const formData = new FormData(form);
            const submitBtn = form.querySelector('button[type="submit"]');
            const originalText = submitBtn.innerHTML;

            submitBtn.disabled = true;
            submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>Uploading...';

            const uploadModal = new bootstrap.Modal(document.getElementById('uploadModal'));
            uploadModal.show();

            fetch('/panitia/sertifikat/upload', {
                method: 'POST',
                body: formData,
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                }
            })
            .then(res => res.json())
            .then(data => {
                setTimeout(() => {
                    uploadModal.hide();
                    
                    if (data.success) {
                        showToast('Sertifikat berhasil diunggah!', 'success');
                        const eventId = eventSelect.value;
                        const sesiId = sesiSelect.value;
                        loadParticipants(eventId, sesiId);
                    } else {
                        showToast(data.message || 'Upload gagal', 'error');
                    }
                    
                    submitBtn.disabled = false;
                    submitBtn.innerHTML = originalText;
                }, 1000);
            })
            .catch(err => {
                console.error('Upload error:', err);
                uploadModal.hide();
                showToast('Terjadi kesalahan saat mengunggah', 'error');
                
                submitBtn.disabled = false;
                submitBtn.innerHTML = originalText;
            });
        }
    });

    function showToast(message, type = 'info') {
        const toastHtml = `
            <div class="toast align-items-center text-white bg-${type === 'success' ? 'success' : type === 'error' ? 'danger' : 'info'} border-0" role="alert">
                <div class="d-flex">
                    <div class="toast-body">
                        <i class="fas fa-${type === 'success' ? 'check-circle' : type === 'error' ? 'exclamation-circle' : 'info-circle'} me-2"></i>
                        ${message}
                    </div>
                    <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
                </div>
            </div>
        `;

        let toastContainer = document.querySelector('.toast-container');
        if (!toastContainer) {
            toastContainer = document.createElement('div');
            toastContainer.className = 'toast-container position-fixed top-0 end-0 p-3';
            document.body.appendChild(toastContainer);
        }

        toastContainer.insertAdjacentHTML('beforeend', toastHtml);
        const toastElement = toastContainer.lastElementChild;
        const toast = new bootstrap.Toast(toastElement, { delay: 4000 });
        toast.show();

        toastElement.addEventListener('hidden.bs.toast', () => {
            toastElement.remove();
        });
    }

    loadEvents();
});
</script>
@endsection