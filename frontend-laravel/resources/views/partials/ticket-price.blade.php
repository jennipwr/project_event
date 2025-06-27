<div class="mb-3">
    <span class="h6 fw-bold text-primary">{{ $ticket['formatted_price'] }}</span>
</div>

{{-- resources/views/partials/ticket-registration-date.blade.php --}}
<div class="mb-3 small text-muted">
    <i class="fas fa-calendar-plus me-1"></i>
    Didaftar: {{ \Carbon\Carbon::parse($ticket['created_at'])->format('d M Y H:i') }}
</div>