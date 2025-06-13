@extends('layouts.index')

@section('content')
<div class="container">
    <h1>Dashboard Member</h1>
    <p>Selamat datang, {{ $user['name'] }}!</p>
</div>
@endsection
