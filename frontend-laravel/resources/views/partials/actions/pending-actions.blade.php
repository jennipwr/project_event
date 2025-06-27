<div class="alert alert-warning small mb-2">
    <i class="fas fa-clock me-1"></i>
    Menunggu verifikasi pembayaran
</div>
@if($ticket['bukti_transaksi'])
    <button class="btn btn-outline-info btn-sm w-100" 
            onclick="showPaymentProof('{{ $ticket['bukti_transaksi'] }}')">
        <i class="fas fa-receipt me-2"></i>Lihat Bukti Bayar
    </button>
@endif