<!DOCTYPE html>
<html>
<head>
    <title>Daftar Role</title>
</head>
<body>
    <h1>Daftar Role</h1>
    <ul>
        @foreach($roles as $role) {{-- Fixed: roles plural --}}
            <li>{{ $role['id_role'] }} - {{ $role['nama_role'] }}</li>
        @endforeach
    </ul>
</body>
</html>