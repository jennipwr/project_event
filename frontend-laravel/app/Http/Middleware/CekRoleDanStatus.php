<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class CekRoleDanStatus
{
    public function handle(Request $request, Closure $next, ...$roles)
    {
        $user = session('user');

        if (!$user) {
            return redirect()->route('login.form')->with('error', 'Silakan login terlebih dahulu.');
        }

        if ($user['status'] !== 'aktif') {
            return redirect()->route('login.form')->with('error', 'Akun Anda nonaktif. Hubungi admin.');
        }

        if (!in_array($user['role_id_role'], $roles)) {
            return redirect()->route('login.form')->with('error', 'Anda tidak memiliki akses ke halaman ini.');
        }

        return $next($request);
    }
}
