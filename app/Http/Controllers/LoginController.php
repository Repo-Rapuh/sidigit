<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LoginController extends Controller
{
    /**
     * Menampilkan halaman form login.
     */
    public function showLoginForm()
    {
        return view('login.login');
    }

    /**
     * Menangani permintaan login.
     */
    public function login(Request $request)
    {
        // 1. Validasi input
        $request->validate([
            'login'    => 'required|string',
            'password' => 'required|string',
        ]);

        // 2. Tentukan tipe login (email atau username)
        $loginType = filter_var($request->login, FILTER_VALIDATE_EMAIL) ? 'email' : 'username';


        // Asumsi kolom username di database adalah 'name'
        // Jika nama kolomnya 'username', ganti 'name' menjadi 'username'
        $credentials = [
            $loginType => $request->login,
            'password' => $request->password,
        ];

        // 3. Coba lakukan autentikasi
        if (Auth::attempt($credentials)) {
            // Jika berhasil, regenerate session
            $request->session()->regenerate();

            // Redirect ke halaman yang dituju sebelumnya, atau ke dashboard
            return redirect()->intended('/dashboard');
        }

        // 4. Jika gagal, kembali ke halaman login dengan pesan error
        return back()->withErrors([
            'login' => 'Username/Email atau Password salah.',
        ])->onlyInput('login');
    }

    /**
     * Menangani permintaan logout.
     */
    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect('/login');
    }


}
