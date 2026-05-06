<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function showLogin()
    {
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'nim_nip' => 'required',
            'password' => 'required'
        ]);

        if (Auth::attempt(['nim_nip' => $credentials['nim_nip'], 'password' => $credentials['password']], $request->boolean('remember'))) {
            $request->session()->regenerate();
            
            $role = Auth::user()->role;
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

    public function showRegister()
    {
        return view('auth.register');
    }

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

        return redirect()->route('dashboard');
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}
