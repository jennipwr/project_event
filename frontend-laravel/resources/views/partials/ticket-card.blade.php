<div class="col-lg-6 col-xl-4">
    <div class="card ticket-card shadow-sm border-0 h-100">
        @include('partials.ticket-poster', ['ticket' => $ticket])
        
        <div class="card-body d-flex flex-column">
            @include('partials.ticket-info', ['ticket' => $ticket])
            @include('partials.ticket-session', ['ticket' => $ticket])
            @include('partials.ticket-price', ['ticket' => $ticket])
            @include('partials.ticket-registration-date', ['ticket' => $ticket])
            @include('partials.ticket-actions', ['ticket' => $ticket])
        </div>
    </div>
</div>