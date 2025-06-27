@extends('layouts.guest')

@section('content')
<div class="container my-5">
    <!-- Page Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h1 class="display-6 fw-bold text-dark mb-2">Upload Ulang Bukti Pembayaran</h1>
                    <p class="text-muted mb-0">Upload bukti pembayaran yang baru untuk registrasi yang ditolak</p>
                </div>
                <div>
                    <a href="{{ route('event.history') }}" class="btn btn-outline-secondary">
                        <i class="fas fa-arrow-left me-2"></i>Kembali ke Riwayat
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Error Messages -->
    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="fas fa-exclamation-triangle me-2"></i>{{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif
    
    @if($errors->any())
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="fas fa-exclamation-triangle me-2"></i>
            <ul class="mb-0">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="row justify-content-center">
        <div class="col-lg-8">
            <!-- Registration Details Card -->
            @if(isset($registrationData['data']['alasan_penolakan']) && $registrationData['data']['alasan_penolakan'])
            <div class="col-12">
                <div class="alert alert-warning d-flex align-items-center" role="alert">
                    <div>
                        <strong>Alasan Penolakan:</strong>
                        <span>{{ $registrationData['data']['alasan_penolakan'] }}</span>
                    </div>
                </div>
            </div>
            @endif
            @if(isset($registrationData['data']))
            <div class="card border-0 shadow-lg mb-4">
                <div class="card-header bg-gradient-primary text-white py-3">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-info-circle me-2"></i>Detail Registrasi
                    </h5>
                </div>
                <div class="card-body p-4">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <div class="d-flex align-items-center mb-3">
                                <div class="icon-circle bg-primary text-white me-3">
                                    <i class="fas fa-ticket-alt"></i>
                                </div>
                                <div>
                                    <small class="text-muted d-block">ID Registrasi</small>
                                    <strong class="text-dark">{{ $registrationData['data']['id_registrasi'] }}</strong>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="d-flex align-items-center mb-3">
                                <div class="icon-circle bg-danger text-white me-3">
                                    <i class="fas fa-exclamation-circle"></i>
                                </div>
                                <div>
                                    <small class="text-muted d-block">Status</small>
                                    <span class="badge bg-danger">Ditolak</span>
                                </div>
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="d-flex align-items-start mb-3">
                                <div class="icon-circle bg-info text-white me-3">
                                    <i class="fas fa-calendar-alt"></i>
                                </div>
                                <div>
                                    <small class="text-muted d-block">Event</small>
                                    <strong class="text-dark">{{ $registrationData['data']['nama_event'] ?? 'Event tidak ditemukan' }}</strong>
                                    @if(isset($registrationData['data']['nama_sesi']))
                                        <br><small class="text-muted">Sesi: {{ $registrationData['data']['nama_sesi'] }}</small>
                                    @endif
                                </div>
                            </div>
                        </div>
                        @if(isset($registrationData['data']['formatted_date']))
                        <div class="col-md-6">
                            <div class="d-flex align-items-center mb-3">
                                <div class="icon-circle bg-success text-white me-3">
                                    <i class="fas fa-clock"></i>
                                </div>
                                <div>
                                    <small class="text-muted d-block">Tanggal Event</small>
                                    <strong class="text-dark">{{ $registrationData['data']['formatted_date'] }}</strong>
                                </div>
                            </div>
                        </div>
                        @endif
                        @if(isset($registrationData['data']['formatted_price']))
                        <div class="col-md-6">
                            <div class="d-flex align-items-center mb-3">
                                <div class="icon-circle bg-warning text-white me-3">
                                    <i class="fas fa-money-bill-wave"></i>
                                </div>
                                <div>
                                    <small class="text-muted d-block">Biaya Registrasi</small>
                                    <strong class="text-dark">{{ $registrationData['data']['formatted_price'] }}</strong>
                                </div>
                            </div>
                        </div>
                        @endif
                    </div>
                </div>
            </div>
            @endif

            <!-- Upload Form Card -->
            <div class="card border-0 shadow-lg">
                <div class="card-header bg-gradient-warning text-dark py-3">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-upload me-2"></i>Upload Bukti Pembayaran Baru
                    </h5>
                </div>
                <div class="card-body p-4">
                    <!-- Instructions -->
                    <div class="alert alert-info border-0 mb-4">
                        <div class="d-flex">
                            <div class="flex-shrink-0">
                                <i class="fas fa-info-circle fa-lg text-info"></i>
                            </div>
                            <div class="flex-grow-1 ms-3">
                                <h6 class="alert-heading mb-2">Petunjuk Upload</h6>
                                <ul class="mb-0 small">
                                    <li>Upload bukti pembayaran yang jelas dan dapat dibaca</li>
                                    <li>Format file yang diperbolehkan: JPG, JPEG, PNG, GIF</li>
                                    <li>Ukuran maksimal file: 2MB</li>
                                    <li>Pastikan semua informasi pembayaran terlihat jelas</li>
                                </ul>
                            </div>
                        </div>
                    </div>

                    <!-- Upload Form -->
                    <form action="{{ route('event.reupload.process', $registrationData['data']['id_registrasi'] ?? '') }}" 
                          method="POST" 
                          enctype="multipart/form-data"
                          id="reuploadForm">
                        @csrf
                        
                        <div class="mb-4">
                            <label for="bukti_transaksi" class="form-label fw-semibold">
                                <i class="fas fa-file-image me-2 text-primary"></i>Pilih File Bukti Pembayaran <span class="text-danger">*</span>
                            </label>
                            <div class="upload-area border-2 border-dashed border-primary rounded-3 p-4 text-center position-relative">
                                <input type="file" 
                                       class="form-control" 
                                       id="bukti_transaksi" 
                                       name="bukti_transaksi" 
                                       accept="image/*"
                                       required>
                                <div class="upload-placeholder mt-3" id="uploadPlaceholder">
                                    <i class="fas fa-cloud-upload-alt fa-3x text-primary mb-3"></i>
                                    <p class="mb-2 fw-semibold">Klik untuk memilih file atau drag & drop</p>
                                    <p class="text-muted small">JPG, JPEG, PNG, GIF (Max. 2MB)</p>
                                </div>
                                <div class="upload-preview" id="uploadPreview" style="display: none;">
                                    <img id="previewImage" src="" alt="Preview" class="img-fluid rounded mb-2" style="max-height: 200px;">
                                    <p class="text-success mb-0">
                                        <i class="fas fa-check-circle me-1"></i>
                                        <span id="fileName"></span>
                                    </p>
                                    <button type="button" class="btn btn-sm btn-outline-danger mt-2" id="removeFile">
                                        <i class="fas fa-times me-1"></i>Hapus File
                                    </button>
                                </div>
                            </div>
                        </div>

                        <!-- Current Payment Proof (if exists) -->
                        @if(isset($registrationData['data']['bukti_transaksi']) && $registrationData['data']['bukti_transaksi'])
                        <div class="mb-4">
                            <label class="form-label fw-semibold">
                                <i class="fas fa-file-alt me-2 text-secondary"></i>Bukti Pembayaran Sebelumnya
                            </label>
                            <div class="border rounded-3 p-3 bg-light">
                                <div class="d-flex align-items-center">
                                    <img src="{{ 'http://localhost:3000/' . $registrationData['data']['bukti_transaksi'] }}" 
                                         alt="Bukti Pembayaran Lama" 
                                         class="img-thumbnail me-3"
                                         style="max-width: 100px; max-height: 100px;">
                                    <div>
                                        <p class="mb-1 fw-semibold">Bukti pembayaran yang ditolak</p>
                                        <small class="text-muted">File ini akan diganti dengan upload baru Anda</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                        @endif

                        <!-- Submit Buttons -->
                        <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                            <a href="{{ route('event.history') }}" class="btn btn-outline-secondary btn-lg me-md-2">
                                <i class="fas fa-times me-2"></i>Batal
                            </a>
                            <button type="submit" class="btn btn-primary btn-lg" id="submitBtn">
                                <i class="fas fa-upload me-2"></i>Upload Bukti Pembayaran
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.icon-circle {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 14px;
}

.bg-gradient-primary {
    background: linear-gradient(45deg, #007bff, #0056b3);
}

.bg-gradient-warning {
    background: linear-gradient(45deg, #ffc107, #ff8c00);
}

.upload-area {
    transition: all 0.3s ease;
    cursor: pointer;
}

.upload-area:hover {
    border-color: #0056b3 !important;
    background-color: #f8f9ff;
}

.upload-area input[type="file"] {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    opacity: 0;
    cursor: pointer;
}

.card {
    transition: transform 0.2s ease-in-out;
}

.card:hover {
    transform: translateY(-2px);
}

.alert {
    border-left: 4px solid;
}

.alert-info {
    border-left-color: #17a2b8;
}

.alert-danger {
    border-left-color: #dc3545;
}

#submitBtn:disabled {
    opacity: 0.6;
    cursor: not-allowed;
}

.loading {
    pointer-events: none;
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const fileInput = document.getElementById('bukti_transaksi');
    const uploadPlaceholder = document.getElementById('uploadPlaceholder');
    const uploadPreview = document.getElementById('uploadPreview');
    const previewImage = document.getElementById('previewImage');
    const fileName = document.getElementById('fileName');
    const removeFileBtn = document.getElementById('removeFile');
    const submitBtn = document.getElementById('submitBtn');
    const form = document.getElementById('reuploadForm');

    // File input change handler
    fileInput.addEventListener('change', function(e) {
        const file = e.target.files[0];
        
        if (file) {
            // Validate file size
            if (file.size > 2 * 1024 * 1024) { // 2MB
                alert('Ukuran file terlalu besar. Maksimal 2MB.');
                this.value = '';
                return;
            }

            // Validate file type
            const validTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif'];
            if (!validTypes.includes(file.type)) {
                alert('Format file tidak valid. Gunakan JPG, JPEG, PNG, atau GIF.');
                this.value = '';
                return;
            }

            // Show preview
            const reader = new FileReader();
            reader.onload = function(e) {
                previewImage.src = e.target.result;
                fileName.textContent = file.name;
                uploadPlaceholder.style.display = 'none';
                uploadPreview.style.display = 'block';
            };
            reader.readAsDataURL(file);
        }
    });

    // Remove file button handler
    removeFileBtn.addEventListener('click', function() {
        fileInput.value = '';
        uploadPlaceholder.style.display = 'block';
        uploadPreview.style.display = 'none';
        previewImage.src = '';
        fileName.textContent = '';
    });

    // Form submit handler
    form.addEventListener('submit', function(e) {
        const file = fileInput.files[0];
        
        if (!file) {
            e.preventDefault();
            alert('Silakan pilih file bukti pembayaran terlebih dahulu.');
            return;
        }

        // Show loading state
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Mengupload...';
        form.classList.add('loading');
    });

    // Drag and drop functionality
    const uploadArea = document.querySelector('.upload-area');
    
    ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
        uploadArea.addEventListener(eventName, preventDefaults, false);
    });

    function preventDefaults(e) {
        e.preventDefault();
        e.stopPropagation();
    }

    ['dragenter', 'dragover'].forEach(eventName => {
        uploadArea.addEventListener(eventName, highlight, false);
    });

    ['dragleave', 'drop'].forEach(eventName => {
        uploadArea.addEventListener(eventName, unhighlight, false);
    });

    function highlight(e) {
        uploadArea.classList.add('border-primary');
        uploadArea.style.borderColor = '#0056b3';
        uploadArea.style.backgroundColor = '#f8f9ff';
    }

    function unhighlight(e) {
        uploadArea.classList.remove('border-primary');
        uploadArea.style.borderColor = '';
        uploadArea.style.backgroundColor = '';
    }

    uploadArea.addEventListener('drop', handleDrop, false);

    function handleDrop(e) {
        const dt = e.dataTransfer;
        const files = dt.files;
        
        if (files.length > 0) {
            fileInput.files = files;
            fileInput.dispatchEvent(new Event('change'));
        }
    }
});
</script>
@endsection