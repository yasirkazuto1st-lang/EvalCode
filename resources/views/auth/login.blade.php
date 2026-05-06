@extends('layouts.auth')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-6 col-lg-4">
            <div class="card border-0 shadow-lg rounded-4 overflow-hidden">
                <div class="card-body p-4 bg-white">
                    <div class="text-center mb-4">
                        <h4 class="text-unsulbar fw-bold mb-1"><i class="bi bi-braces"></i> EvalCode</h4>
                        <p class="text-muted small">Silakan masuk ke akun Anda</p>
                    </div>

                    <form method="POST" action="{{ route('login') }}">
                        @csrf

                        <div class="mb-3">
                            <label for="nim_nip" class="form-label text-muted small fw-semibold">NIM / NIP / Username</label>
                            <input id="nim_nip" type="text" class="form-control @error('nim_nip') is-invalid @enderror" name="nim_nip" value="{{ old('nim_nip') }}" required autocomplete="nim_nip" autofocus placeholder="Masukkan Identitas Anda">
                            @error('nim_nip')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>

                        <div class="mb-4">
                            <label for="password" class="form-label text-muted small fw-semibold">Password</label>
                            <input id="password" type="password" class="form-control @error('password') is-invalid @enderror" name="password" required autocomplete="current-password" placeholder="••••••••">
                            @error('password')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>

                        <div class="mb-4 d-flex justify-content-between align-items-center">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="remember" id="remember" {{ old('remember') ? 'checked' : '' }}>
                                <label class="form-check-label small text-muted" for="remember">
                                    Ingat Saya
                                </label>
                            </div>
                        </div>

                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-unsulbar fw-semibold py-2">
                                Login
                            </button>
                        </div>
                        
                        <div class="text-center mt-4">
                            <p class="small text-muted mb-0">Mahasiswa Baru? <a href="{{ route('register') }}" class="text-decoration-none text-unsulbar fw-semibold">Register</a></p>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
