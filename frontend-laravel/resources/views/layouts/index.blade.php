<!doctype html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Modernize Free</title>
    <link rel="shortcut icon" type="image/png" href=" {{ asset ('assets/images/logos/favicon.png') }}" />
    <link rel="stylesheet" href=" {{ asset ('assets/css/styles.min.css') }}" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    @yield('ExtraCss')
</head>

<body>
<!--  Body Wrapper -->
<div class="page-wrapper" id="main-wrapper" data-layout="vertical" data-navbarbg="skin6" data-sidebartype="full"
     data-sidebar-position="fixed" data-header-position="fixed">
    @include('layouts.sidebar')
    <!--  Main wrapper -->
    <div class="body-wrapper">
        @include('layouts.header')
        <div class="container-fluid">
            @yield('content')

            @include('layouts.footer')
        </div>
    </div>
</div>
<script src=" {{ asset ('assets/libs/jquery/dist/jquery.min.js') }}"></script>
<script src=" {{ asset ('assets/libs/bootstrap/dist/js/bootstrap.bundle.min.js') }}"></script>
<script src=" {{ asset ('assets/js/sidebarmenu.js') }}"></script>
<script src=" {{ asset ('assets/js/app.min.js') }}"></script>
<script src=" {{ asset ('assets/libs/apexcharts/dist/apexcharts.min.js') }}"></script>
<script src=" {{ asset ('assets/libs/simplebar/dist/simplebar.js') }}"></script>
<script src=" {{ asset ('assets/js/dashboard.js') }}"></script>
@yield('ExtraJS')
</body>

</html>
