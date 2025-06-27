<div class="modal fade" id="qrModal" tabindex="-1" aria-labelledby="qrModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header border-0">
                <h5 class="modal-title fw-bold" id="qrModalLabel">QR Code Tiket</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body text-center">
                <div id="qrcode" class="mb-3"></div>
                <p class="text-muted small">Tunjukkan QR Code ini saat check-in event</p>
            </div>
            <div class="modal-footer border-0">
                <button type="button" class="btn btn-primary" onclick="downloadQR()">
                    <i class="fas fa-download me-2"></i>Download QR
                </button>
            </div>
        </div>
    </div>
</div>