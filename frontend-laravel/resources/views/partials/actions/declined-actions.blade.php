<div class="alert alert-danger small mb-2">
    <i class="fas fa-times-circle me-1"></i>
    Pembayaran ditolak
</div>
<div class="d-grid gap-2">
    <a href="{{ route('event.reupload.form', $ticket['id_registrasi']) }}" 
       class="btn btn-primary btn-sm">
        <i class="fas fa-redo me-2"></i>Upload Ulang Bukti
    </a>
    @if($ticket['bukti_transaksi'])
        <button class="btn btn-outline-secondary btn-sm" 
                onclick="showPaymentProof('{{ $ticket['bukti_transaksi'] }}')">
            <i class="fas fa-eye me-2"></i>Lihat Bukti Lama
        </button>
    @endif
</div>