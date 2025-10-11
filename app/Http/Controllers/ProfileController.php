<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rules\Password;

class ProfileController extends Controller
{
    /**
     * Menampilkan form edit profil untuk user yang sedang login.
     */
    public function edit()
    {
        // Ambil data user yang sedang login
        $user = Auth::user();
        return view('profile.edit', compact('user'));
    }

    /**
     * Mengupdate data profil user yang sedang login.
     */
    public function update(Request $request)
    {
        /** @var \App\Models\User $user */ // <-- TAMBAHKAN BARIS INI
        $user = Auth::user(); // Ambil user di awal

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'username' => 'required|string|max:255|unique:users,username,' . $user->id,
            'email' => 'required|string|email|max:255|unique:users,email,' . $user->id,
            'password' => ['nullable', 'confirmed', Password::min(8)],
        ]);

        // Siapkan data untuk diupdate
        $dataToUpdate = [
            'name' => $validated['name'],
            'username' => $validated['username'],
            'email' => $validated['email'],
        ];

        // Jika password baru diisi, tambahkan ke data yang akan diupdate
        // Model User kita akan otomatis melakukan hashing karena sudah di-setting di $casts
        if (!empty($validated['password'])) {
            $dataToUpdate['password'] = $validated['password'];
        }

        // Gunakan method update()
        $user->update($dataToUpdate);

        return back()->with('success', 'Profil berhasil diperbarui.');
    }
}
