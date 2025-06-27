<div class="position-relative">
    @if($ticket['poster'])
        <img src="http://localhost:3000/{{ $ticket['poster'] }}" 
            alt="{{ $ticket['nama_event'] }}" 
            class="card-img-top ticket-poster">
    @else
        <div class="ticket-poster-placeholder d-flex align-items-center justify-content-center">
            <i class="fas fa-image fa-3x text-muted"></i>
        </div>
    @endif
    
    <div class="position-absolute top-0 end-0 m-2">
        <span class="badge bg-{{ $ticket['status_class'] }} status-badge">
            {{ $ticket['status_text'] }}
        </span>
    </div>
</div>