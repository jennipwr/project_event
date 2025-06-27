@extends('layouts.index')

@section('content')
<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-lg-10 col-xl-8">
            <!-- Header Section -->
            <div class="text-center mb-4">
                <div class="d-inline-flex align-items-center justify-content-center bg-primary rounded-circle mb-3" style="width: 80px; height: 80px;">
                    <i class="fas fa-qrcode text-white" style="font-size: 2.5rem;"></i>
                </div>
                <h2 class="fw-bold text-dark mb-2">Scan QR Code Kehadiran</h2>
                <p class="text-muted">Scan QR code peserta untuk mencatat kehadiran pada event</p>
            </div>

            <!-- Main Card -->
            <div class="card shadow-lg border-0 rounded-4 overflow-hidden">
                <!-- Card Header -->
                <div class="card-header bg-gradient-primary text-white py-4 border-0">
                    <div class="d-flex align-items-center">
                        <i class="fas fa-camera me-3" style="font-size: 1.5rem;"></i>
                        <div>
                            <h5 class="mb-1 fw-semibold">Scanner Kehadiran</h5>
                            <small class="opacity-75">Pilih event dan sesi, lalu scan QR code peserta</small>
                        </div>
                    </div>
                </div>

                <div class="card-body p-4">
                    <form id="scanForm">
                        @csrf
                        
                        <!-- Event Selection Section -->
                        <div class="row g-4 mb-4">
                            <div class="col-md-6">
                                <label for="event_id" class="form-label fw-semibold">
                                    <i class="fas fa-calendar-alt text-primary me-2"></i>Pilih Event
                                </label>
                                <div class="position-relative">
                                    <select class="form-select form-select-lg border-2 rounded-3" id="event_id" name="event_id" required>
                                        <option value="">Loading events...</option>
                                    </select>
                                    <div class="position-absolute top-50 end-0 translate-middle-y me-3 pe-none">
                                        <i class="fas fa-chevron-down text-muted"></i>
                                    </div>
                                </div>
                                <div id="eventDebug" class="small text-muted mt-1"></div>
                            </div>
                            
                            <div class="col-md-6">
                                <label for="event_sesi_id" class="form-label fw-semibold">
                                    <i class="fas fa-clock text-primary me-2"></i>Pilih Sesi
                                </label>
                                <div class="position-relative">
                                    <select class="form-select form-select-lg border-2 rounded-3" id="event_sesi_id" name="event_sesi_id" required>
                                        <option value="">Pilih Event terlebih dahulu</option>
                                    </select>
                                    <div class="position-absolute top-50 end-0 translate-middle-y me-3 pe-none">
                                        <i class="fas fa-chevron-down text-muted"></i>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Scanner Section -->
                        <div class="mb-4">
                            <label class="form-label fw-semibold mb-3">
                                <i class="fas fa-qrcode text-primary me-2"></i>Scan QR Code
                            </label>
                            
                            <!-- Instructions -->
                            <div class="alert alert-info border-0 rounded-3 mb-4" style="background: linear-gradient(45deg, #e3f2fd, #bbdefb);">
                                <div class="d-flex align-items-start">
                                    <i class="fas fa-info-circle text-info me-3 mt-1"></i>
                                    <div>
                                        <h6 class="mb-2 fw-semibold text-info">Petunjuk Penggunaan:</h6>
                                        <ul class="mb-0 small text-info">
                                            <li>Pastikan Event dan Sesi sudah dipilih sebelum melakukan scan</li>
                                            <li>Arahkan kamera ke QR Code peserta</li>
                                            <li>QR Code akan langsung diproses setelah berhasil terbaca</li>
                                        </ul>
                                    </div>
                                </div>
                            </div>

                            <!-- QR Scanner Container -->
                            <div class="scanner-container position-relative">
                                <div class="scanner-frame mx-auto mb-3" style="max-width: 400px;">
                                    <div id="qr-reader" class="rounded-4 overflow-hidden shadow-sm border-3 border-primary"></div>
                                </div>
                                <input type="hidden" id="qrcode" name="qrcode" required>
                            </div>
                        </div>

                        <!-- Status Section -->
                        <div class="mb-3">
                            <div class="alert alert-secondary border-0 rounded-3 shadow-sm" id="scanStatus" style="background: linear-gradient(45deg, #f8f9fa, #e9ecef);">
                                <div class="d-flex align-items-center">
                                    <i class="fas fa-camera text-secondary me-3"></i>
                                    <span>Siap untuk scan QR Code...</span>
                                </div>
                            </div>
                        </div>
                    </form>

                    <!-- Results Section -->
                    <div id="result" class="mt-4" style="display: none;">
                        <div class="alert border-0 rounded-3 shadow-sm" id="resultAlert"></div>
                    </div>
                </div>
            </div>

            <!-- Additional Info Card -->
            <div class="row mt-4">
                <div class="col-md-4">
                    <div class="card border-0 shadow-sm rounded-3 h-100">
                        <div class="card-body text-center p-4">
                            <div class="bg-success bg-opacity-10 rounded-circle d-inline-flex align-items-center justify-content-center mb-3" style="width: 60px; height: 60px;">
                                <i class="fas fa-check-circle text-success" style="font-size: 1.5rem;"></i>
                            </div>
                            <h6 class="fw-semibold mb-2">Scan Berhasil</h6>
                            <small class="text-muted">Data kehadiran tersimpan otomatis</small>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card border-0 shadow-sm rounded-3 h-100">
                        <div class="card-body text-center p-4">
                            <div class="bg-warning bg-opacity-10 rounded-circle d-inline-flex align-items-center justify-content-center mb-3" style="width: 60px; height: 60px;">
                                <i class="fas fa-clock text-warning" style="font-size: 1.5rem;"></i>
                            </div>
                            <h6 class="fw-semibold mb-2">Real-time</h6>
                            <small class="text-muted">Pemrosesan data secara langsung</small>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card border-0 shadow-sm rounded-3 h-100">
                        <div class="card-body text-center p-4">
                            <div class="bg-info bg-opacity-10 rounded-circle d-inline-flex align-items-center justify-content-center mb-3" style="width: 60px; height: 60px;">
                                <i class="fas fa-shield-alt text-info" style="font-size: 1.5rem;"></i>
                            </div>
                            <h6 class="fw-semibold mb-2">Aman</h6>
                            <small class="text-muted">Data terlindungi dengan enkripsi</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.bg-gradient-primary {
    background: linear-gradient(135deg, #007bff 0%, #0056b3 100%);
}

.scanner-container {
    background: linear-gradient(45deg, #f8f9fa, #ffffff);
    padding: 2rem;
    border-radius: 1rem;
    border: 2px dashed #dee2e6;
}

.scanner-frame {
    position: relative;
}

.scanner-frame::before {
    content: '';
    position: absolute;
    top: -10px;
    left: -10px;
    right: -10px;
    bottom: -10px;
    background: linear-gradient(45deg, #667eea, #764ba2, #667eea);
    border-radius: 1.5rem;
    z-index: -1;
    opacity: 0.1;
}

.form-select:focus,
.form-control:focus {
    border-color: #667eea;
    box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
}

.card {
    transition: transform 0.2s ease-in-out;
}

.card:hover {
    transform: translateY(-2px);
}

.alert {
    transition: all 0.3s ease;
}

#restartScannerBtn {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    border: none;
    border-radius: 50px;
    padding: 12px 30px;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    transition: all 0.3s ease;
    box-shadow: 0 4px 15px rgba(102, 126, 234, 0.3);
}

#restartScannerBtn:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 20px rgba(102, 126, 234, 0.4);
}

.pulse {
    animation: pulse 2s infinite;
}

@keyframes pulse {
    0% {
        box-shadow: 0 0 0 0 rgba(102, 126, 234, 0.7);
    }
    70% {
        box-shadow: 0 0 0 10px rgba(102, 126, 234, 0);
    }
    100% {
        box-shadow: 0 0 0 0 rgba(102, 126, 234, 0);
    }
}

.status-processing {
    background: linear-gradient(45deg, #fff3cd, #ffeaa7) !important;
    border-left: 4px solid #f39c12 !important;
}

.status-success {
    background: linear-gradient(45deg, #d4edda, #a8e6cf) !important;
    border-left: 4px solid #28a745 !important;
}

.status-error {
    background: linear-gradient(45deg, #f8d7da, #ffb3ba) !important;
    border-left: 4px solid #dc3545 !important;
}

.fade-in {
    animation: fadeIn 0.5s ease-in;
}

@keyframes fadeIn {
    from { opacity: 0; transform: translateY(10px); }
    to { opacity: 1; transform: translateY(0); }
}
</style>
@endsection

@section('ExtraJS')
<script src="https://unpkg.com/html5-qrcode" type="text/javascript"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    const eventSelect = document.getElementById('event_id');
    const sessionSelect = document.getElementById('event_sesi_id');
    const qrCodeInput = document.getElementById('qrcode');
    const scanForm = document.getElementById('scanForm');
    const resultDiv = document.getElementById('result');
    const resultAlert = document.getElementById('resultAlert');
    const eventDebug = document.getElementById('eventDebug');
    const scanStatus = document.getElementById('scanStatus');

    let isProcessing = false;
    let html5QrcodeScanner = null;
    let lastScannedCode = null;
    let scanCooldown = false;
    let isScannerPaused = false;

    function showLoadingState(element, text = 'Loading...') {
        element.innerHTML = `<option value=""><i class="fas fa-spinner fa-spin"></i> ${text}</option>`;
    }

    function loadEvents() {
        showLoadingState(eventSelect, 'Memuat events...');
        
        fetch('/panitia/scan/events')
            .then(response => response.json())
            .then(data => {
                eventSelect.innerHTML = '<option value="">🎯 Pilih Event</option>';
                if (data.success && data.data.length) {
                    data.data.forEach(event => {
                        eventSelect.innerHTML += `<option value="${event.id_event}">📅 ${event.nama_event}</option>`;
                    });
                    eventSelect.classList.add('fade-in');
                } else {
                    eventSelect.innerHTML = '<option value="">❌ Tidak ada event tersedia</option>';
                }
            })
            .catch(error => {
                console.error('Error loading events:', error);
                eventSelect.innerHTML = '<option value="">⚠️ Gagal memuat event</option>';
            });
    }

    function formatDate(dateString) {
        if (!dateString) return '';
        
        if (dateString.match(/^\d{4}-\d{2}-\d{2}$/)) {
            const [year, month, day] = dateString.split('-').map(Number);
            const date = new Date(year, month - 1, day); // month di Date() mulai dari 0
            
            return date.toLocaleDateString('id-ID', {
                day: '2-digit',
                month: 'short',
                year: 'numeric'
            });
        }
        
        const date = new Date(dateString);
        if (isNaN(date.getTime())) {
            return dateString;
        }
        
        return date.toLocaleDateString('id-ID', {
            day: '2-digit',
            month: 'short', 
            year: 'numeric'
        });
    }

    function formatDateSimple(dateString) {
        if (!dateString) return '';
        
        // Untuk format Y-m-d, split manual
        if (dateString.includes('-') && dateString.length === 10) {
            const [year, month, day] = dateString.split('-');
            const months = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 
                        'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];
            return `${parseInt(day)} ${months[parseInt(month) - 1]} ${year}`;
        }
        
        return dateString;
    }

    eventSelect.addEventListener('change', function () {
    const eventId = this.value;
    showLoadingState(sessionSelect, 'Memuat sesi...');

    if (eventId) {
        fetch(`/panitia/events/${eventId}/sessions`)
            .then(res => res.json())
            .then(data => {
                sessionSelect.innerHTML = '<option value="">⏰ Pilih Sesi</option>';
                if (data.success && data.data) {
                    data.data.forEach(session => {
                        // Gunakan formatDate untuk tanggal
                        const formattedDate = formatDate(session.tanggal_sesi);
                        const waktu = session.waktu_sesi ? ` ${session.waktu_sesi}` : '';
                        const label = `${session.nama_sesi} - ${formattedDate}${waktu}`;
                        sessionSelect.innerHTML += `<option value="${session.id_sesi}">🕐 ${label}</option>`;
                    });
                    sessionSelect.classList.add('fade-in');
                } else {
                    sessionSelect.innerHTML = '<option value="">❌ Tidak ada sesi tersedia</option>';
                }
            })
            .catch(err => {
                console.error('Gagal ambil sesi:', err);
                sessionSelect.innerHTML = '<option value="">⚠️ Error ambil sesi</option>';
            });
    } else {
        sessionSelect.innerHTML = '<option value="">Pilih Event terlebih dahulu</option>';
        }
    });

    async function processQRScan(qrCodeData) {
        console.log('=== PROCESSING QR SCAN ===');
        console.log('QR Code:', qrCodeData);
        
        if (isProcessing || scanCooldown) {
            console.log('Masih dalam cooldown atau memproses scan...');
            return;
        }

        if (lastScannedCode === qrCodeData) {
            console.log('QR Code sama dengan scan sebelumnya, skip...');
            return;
        }

        if (!eventSelect.value || !sessionSelect.value) {
            showResult('danger', '<strong>❌ Error!</strong> Pilih Event dan Sesi terlebih dahulu');
            return;
        }

        isProcessing = true;
        scanCooldown = true;
        lastScannedCode = qrCodeData;

        if (html5QrcodeScanner && !isScannerPaused) {
            try {
                console.log('Stopping scanner...');
                await html5QrcodeScanner.clear();
                console.log('Scanner stopped');
                isScannerPaused = true;
            } catch (err) {
                console.error('Failed to stop scanner:', err);
                isScannerPaused = true;
            }
        }

        updateScanStatus('warning', '<i class="fas fa-spinner fa-spin"></i> 🔄 Memproses QR Code...', 'status-processing');

        const formData = {
            qrcode: qrCodeData,
            event_id: parseInt(eventSelect.value),
            event_sesi_id: parseInt(sessionSelect.value)
        };

        try {
            const res = await fetch('/panitia/scan', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify(formData)
            });

            const data = await res.json();

            if (data.success) {
                showResult('success', `<strong>✅ Berhasil!</strong> ${data.message}<br>
                    <div class="mt-2">
                        <strong>👤 Nama:</strong> ${data.data.nama}<br>
                        <strong>📧 Email:</strong> ${data.data.email}<br>
                        <strong>⏰ Waktu:</strong> ${data.data.waktu_scan}
                    </div>`);
                updateScanStatus('success', '<i class="fas fa-check-circle"></i> ✅ Scan berhasil! Klik "Mulai Scan Lagi" untuk scan berikutnya.', 'status-success');
            } else {
                showResult('danger', `<strong>❌ Error!</strong> ${data.message}`);
                updateScanStatus('danger', '<i class="fas fa-exclamation-circle"></i> ❌ Scan gagal! Klik "Mulai Scan Lagi" untuk mencoba lagi.', 'status-error');
            }

        } catch (err) {
            console.error('Error processing scan:', err);
            showResult('danger', '<strong>⚠️ Error!</strong> Terjadi kesalahan sistem');
            updateScanStatus('danger', '<i class="fas fa-exclamation-circle"></i> ⚠️ Error sistem! Klik "Mulai Scan Lagi" untuk mencoba lagi.', 'status-error');
        } finally {
            isProcessing = false;
            setTimeout(() => {
                scanCooldown = false;
            }, 2000);
        }

        showRestartButton();
    }

    function showResult(type, message) {
        resultDiv.style.display = 'block';
        resultAlert.className = `alert alert-${type} border-0 rounded-3 shadow-sm fade-in`;
        resultAlert.innerHTML = message;

        if (type === 'success') {
            setTimeout(() => {
                resultDiv.style.display = 'none';
            }, 8000);
        }
    }

    function showRestartButton() {
        const restartBtnId = 'restartScannerBtn';
        
        const existingBtn = document.getElementById(restartBtnId);
        if (existingBtn) {
            existingBtn.remove();
        }
        
        const restartBtn = document.createElement('button');
        restartBtn.id = restartBtnId;
        restartBtn.className = 'btn btn-primary mt-3 pulse';
        restartBtn.innerHTML = '<i class="fas fa-camera me-2"></i>🔄 Mulai Scan Lagi';
        
        restartBtn.addEventListener('click', function() {
            restartScanner();
            this.remove();
        });
        
        scanStatus.parentNode.insertBefore(restartBtn, scanStatus.nextSibling);
    }

    function restartScanner() {
        console.log('Restarting scanner...');
        
        isProcessing = false;
        scanCooldown = false;
        lastScannedCode = null;
        isScannerPaused = false;
        
        resultDiv.style.display = 'none';
        
        initQRScanner();
        updateScanStatus('secondary', '<i class="fas fa-camera"></i> 📱 Siap untuk scan QR Code...', '');
    }

    function updateScanStatus(type, message, additionalClass = '') {
        scanStatus.className = `alert alert-${type} border-0 rounded-3 shadow-sm ${additionalClass}`;
        scanStatus.innerHTML = `<div class="d-flex align-items-center">${message}</div>`;
    }

    function initQRScanner() {
        if (html5QrcodeScanner) {
            html5QrcodeScanner.clear().catch(err => console.log('Error clearing scanner:', err));
        }
        
        html5QrcodeScanner = new Html5QrcodeScanner(
            "qr-reader",
            { 
                fps: 10, 
                qrbox: { width: 280, height: 280 },
                aspectRatio: 1.0,
                disableFlip: false
            },
            false
        );

        html5QrcodeScanner.render(
            function (decodedText, decodedResult) {
                console.log(`=== QR CODE DETECTED ===`);
                console.log('Decoded text:', decodedText);
                
                if (!scanCooldown && !isProcessing && !isScannerPaused) {
                    qrCodeInput.value = decodedText;
                    updateScanStatus('info', '<i class="fas fa-qrcode"></i> 🔍 QR Code terdeteksi, memproses...', 'status-processing');
                    
                    processQRScan(decodedText);
                }
            },
            function (error) {
            }
        );
        
        isScannerPaused = false;
    }

    sessionSelect.addEventListener('change', function() {
        if (this.value) {
            updateScanStatus('success', '<i class="fas fa-camera"></i> ✅ Konfigurasi lengkap. Siap untuk scan QR Code!', 'status-success');
        } else {
            updateScanStatus('warning', '<i class="fas fa-exclamation-triangle"></i> ⚠️ Pilih sesi event untuk melanjutkan scan.', 'status-processing');
        }
    });

    loadEvents();
    initQRScanner();
    
    setTimeout(() => {
        document.querySelector('.card').classList.add('fade-in');
    }, 100);
});
</script>
@endsection