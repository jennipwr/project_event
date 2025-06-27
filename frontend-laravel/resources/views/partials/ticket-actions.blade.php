<div class="mt-auto">
    @if($ticket['status'] === 'approved')
        @include('partials.actions.approved-actions', ['ticket' => $ticket])
    @elseif($ticket['status'] === 'pending')
        @include('partials.actions.pending-actions', ['ticket' => $ticket])
    @elseif($ticket['status'] === 'declined')
        @include('partials.actions.declined-actions', ['ticket' => $ticket])
    @endif

    <button class="btn btn-outline-secondary btn-sm mt-2" 
            onclick="showEventDetailModal(
                '{{ $ticket['nama_event'] }}',
                '{{ $ticket['deskripsi'] ?? 'Tidak ada deskripsi.' }}',
                '{{ $ticket['syarat_ketentuan'] ?? 'Tidak ada syarat.' }}'
            )">
        <i class="fas fa-info-circle me-2"></i>Lihat Detail Event
    </button>
</div>