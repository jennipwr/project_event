@extends('layouts.guest')

@section('content')
<div class="container my-5">
    @include('partials.page-header')
    @include('partials.alert-messages')
    @include('partials.tickets-list')
</div>

@include('modals.qr-code-modal')
@include('modals.payment-proof-modal')
@include('modals.event-detail-modal')
@endsection

@section('ExtraCss')
<style>
    /* Card Styling */
    .ticket-card {
        transition: all 0.3s ease;
        border-radius: 15px;
        overflow: hidden;
    }
    
    .ticket-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 1rem 2rem rgba(0, 0, 0, 0.15) !important;
    }
    
    /* Poster Styling */
    .ticket-poster {
        height: 200px;
        object-fit: cover;
        width: 100%;
    }
    
    .ticket-poster-placeholder {
        height: 200px;
        background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
    }
    
    /* Badge Styling */
    .status-badge {
        font-size: 0.75rem;
        padding: 0.4rem 0.8rem;
        border-radius: 20px;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }
    
    /* Icon Alignment */
    .session-info i {
        width: 16px;
        text-align: center;
    }
    
    /* Button Effects */
    .btn {
        transition: all 0.3s ease;
        border-radius: 8px;
    }
    
    .btn:hover {
        transform: translateY(-1px);
    }
    
    /* Modal Styling */
    .modal-content {
        border-radius: 15px;
        border: none;
    }
    
    .card-title {
        line-height: 1.2;
    }
    
    /* Responsive Design */
    @media (max-width: 768px) {
        .ticket-poster,
        .ticket-poster-placeholder {
            height: 150px;
        }
    }
</style>
@endsection

@section('ExtraJS')
<script src="https://cdn.jsdelivr.net/npm/qrious@4.0.2/dist/qrious.min.js"></script>
<script>
    let currentQRCode = null;

    // QR Code Functions
    function showQRCode(qrcode) {
        console.log('Showing QR Code:', qrcode);
        currentQRCode = qrcode;
        
        const qrContainer = document.getElementById('qrcode');
        qrContainer.innerHTML = '';
        
        const canvas = document.createElement('canvas');
        qrContainer.appendChild(canvas);
        
        new QRious({
            element: canvas,
            value: qrcode,
            size: 250,
            background: 'white',
            foreground: 'black'
        });
        
        new bootstrap.Modal(document.getElementById('qrModal')).show();
    }

    function downloadQR() {
        if (!currentQRCode) return;
        
        const canvas = document.createElement('canvas');
        new QRious({
            element: canvas,
            value: currentQRCode,
            size: 500,
            background: 'white',
            foreground: 'black'
        });
        
        const link = document.createElement('a');
        link.download = `qr-ticket-${currentQRCode}.png`;
        link.href = canvas.toDataURL();
        link.click();
    }

    // Payment Proof Functions
    function showPaymentProof(buktiTransaksi) {
        console.log('Showing payment proof:', buktiTransaksi);
        
        const proofImage = document.getElementById('proofImage');
        const imageUrl = buktiTransaksi.startsWith('http') 
            ? buktiTransaksi 
            : `http://localhost:3000/${buktiTransaksi}`;
        
        proofImage.src = imageUrl;
        proofImage.alt = 'Bukti Pembayaran';
        
        proofImage.onerror = function() {
            console.error('Failed to load payment proof:', imageUrl);
            this.src = 'data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iMzAwIiBoZWlnaHQ9IjIwMCIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIj48cmVjdCB3aWR0aD0iMTAwJSIgaGVpZ2h0PSIxMDAlIiBmaWxsPSIjZjhmOWZhIi8+PHRleHQgeD0iNTAlIiB5PSI1MCUiIGZvbnQtZmFtaWx5PSJBcmlhbCwgc2Fucy1zZXJpZiIgZm9udC1zaXplPSIxNCIgZmlsbD0iIzZjNzU3ZCIgdGV4dC1hbmNob3I9Im1pZGRsZSIgZHk9Ii4zZW0iPkdhZ2FsIG1lbXVhdCBnYW1iYXI8L3RleHQ+PC9zdmc+';
            this.alt = 'Gagal memuat gambar bukti pembayaran';
        };
        
        new bootstrap.Modal(document.getElementById('proofModal')).show();
    }

    // Event Detail Functions
    function showEventDetailModal(nama, deskripsi, syarat) {
        document.getElementById('eventDetailTitle').innerText = nama;
        document.getElementById('eventDetailDeskripsi').innerText = deskripsi;
        document.getElementById('eventDetailSyarat').innerText = syarat;
        
        new bootstrap.Modal(document.getElementById('eventDetailModal')).show();
    }
</script>
@endsection