<div class="row">
    <div class="col-12">
        @if(isset($tickets['data']) && count($tickets['data']) > 0)
            <div class="row g-4">
                @foreach($tickets['data'] as $ticket)
                    @include('partials.ticket-card', ['ticket' => $ticket])
                @endforeach
            </div>
        @else
            @include('partials.empty-state')
        @endif
    </div>
</div>