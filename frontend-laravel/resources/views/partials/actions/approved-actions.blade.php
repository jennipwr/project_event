<div class="d-grid gap-2">
    <button class="btn btn-success btn-sm" onclick="showQRCode('{{ $ticket['qrcode'] }}')">
        <i class="fas fa-qrcode me-2"></i>Lihat QR Code
    </button>
    <a href="#" class="btn btn-outline-primary btn-sm" onclick="downloadQR()">
        <i class="fas fa-download me-2"></i>Download Tiket
    </a>
    <a href="{{ route('certificate.download', $ticket['id_registrasi']) }}" 
        class="btn btn-outline-success btn-sm" target="_blank" rel="noopener noreferrer">
        <i class="fas fa-certificate me-2"></i>Download Sertifikat
    </a>
</div>