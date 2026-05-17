<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;

class AuthController extends Controller
{
    /**
     * Tampilkan form login.
     * 
     * @return \Illuminate\View\View
     */
    public function showLogin()
    {
        return view('auth.login');
    }

    /**
     * Proses autentikasi login (mendukung single-session per akun).
     * 
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'nim_nip' => 'required',
            'password' => 'required'
        ]);

        if (Auth::attempt(['nim_nip' => $credentials['nim_nip'], 'password' => $credentials['password']], $request->boolean('remember'))) {
            $user = Auth::user();

            // Invalidate previous session if exists (single session enforcement)
            if ($user->last_session_id && $user->last_session_id !== session()->getId()) {
                // Delete the old session from the sessions table (if using database driver)
                DB::table('sessions')->where('id', $user->last_session_id)->delete();
            }

            $request->session()->regenerate();

            // Store current session ID
            $user->update(['last_session_id' => session()->getId()]);

            $role = $user->role;
            if ($role === 'Admin') {
                return redirect()->route('admin.dashboard');
            } elseif ($role === 'Pengawas') {
                return redirect()->route('pengawas.dashboard');
            } else {
                return redirect()->route('dashboard');
            }
        }

        return back()->withErrors([
            'nim_nip' => 'Kredensial yang diberikan tidak cocok dengan data kami.',
        ])->onlyInput('nim_nip');
    }

    /**
     * Tampilkan form registrasi akun Mahasiswa baru.
     * 
     * @return \Illuminate\View\View
     */
    public function showRegister()
    {
        return view('auth.register');
    }

    /**
     * Proses registrasi akun Mahasiswa baru dan langsung login.
     * 
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function register(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'nim_nip' => ['required', 'string', 'size:8', 'regex:/^[A-Za-z]/', 'unique:users,nim_nip'],
            'password' => 'required|string|min:8|confirmed',
        ], [
            'nim_nip.size' => 'NIM harus tepat 8 karakter.',
            'nim_nip.regex' => 'Karakter pertama NIM harus berupa huruf.',
            'nim_nip.unique' => 'NIM sudah terdaftar.',
            'password.confirmed' => 'Konfirmasi password tidak cocok.'
        ]);

        $user = User::create([
            'name' => $request->name,
            'nim_nip' => $request->nim_nip,
            'role' => 'Mahasiswa',
            'password' => Hash::make($request->password),
        ]);

        Auth::login($user);

        // Store session ID for single-session enforcement
        $user->update(['last_session_id' => session()->getId()]);

        return redirect()->route('dashboard');
    }

    /**
     * Proses penggantian password oleh user yang sedang login.
     * 
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function updatePassword(Request $request)
    {
        $request->validate([
            'current_password' => 'required',
            'new_password' => 'required|string|min:8|confirmed',
        ], [
            'new_password.confirmed' => 'Konfirmasi password baru tidak cocok.',
            'new_password.min' => 'Password baru minimal 8 karakter.'
        ]);

        $user = Auth::user();

        if (!Hash::check($request->current_password, $user->password)) {
            return back()->withErrors(['current_password' => 'Password saat ini salah.']);
        }

        $user->update([
            'password' => Hash::make($request->new_password)
        ]);

        return back()->with('success', 'Password berhasil diubah.');
    }

    /**
     * Proses logout, invalidate session, dan bersihkan `last_session_id`.
     * 
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function logout(Request $request)
    {
        // Clear session ID from user record
        $user = Auth::user();
        if ($user) {
            $user->update(['last_session_id' => null]);
        }

        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}
