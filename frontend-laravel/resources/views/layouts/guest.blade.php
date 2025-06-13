<!doctype html>
<html lang="en">
  <head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Event Hub - Discover Amazing Events</title>
    <link
      rel="shortcut icon"
      type="image/png"
      href="{{ asset ('assets/images/logos/favicon.png') }}"
    />
    <link rel="stylesheet" href="{{ asset ('assets/css/styles.min.css') }}" />
    <link rel="stylesheet" href="{{ asset ('assets/css/style.home.css') }}">
    <link rel="stylesheet" href="{{ asset ('assets/css/style.bar.css') }}" />
    <link
      rel="stylesheet"
      href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css"
    />
    <link
      rel="stylesheet"
      href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css"
    />
    @yield('ExtraCss')
  </head>

  <body>
    @include('layouts.navbar')
    
    @yield('content')

    @include('layouts.footer')

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    @yield('ExtraJS')
  </body>
</html>
