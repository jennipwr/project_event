<div class="session-info mb-3">
    <h6 class="fw-semibold text-dark mb-2">{{ $ticket['nama_sesi'] }}</h6>
    <div class="small text-muted">
        <div class="mb-1">
            <i class="fas fa-calendar me-2"></i>{{ $ticket['formatted_date'] }}
        </div>
        <div class="mb-1">
            <i class="fas fa-clock me-2"></i>{{ $ticket['waktu_sesi'] }}
        </div>
        @if($ticket['lokasi_sesi'])
            <div class="mb-1">
                <i class="fas fa-map-marker-alt me-2"></i>{{ $ticket['lokasi_sesi'] }}
            </div>
        @endif
    </div>
</div>