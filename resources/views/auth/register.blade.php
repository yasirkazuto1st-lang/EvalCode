@extends('layouts.auth')

@section('content')
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-6 col-lg-4">
                <div class="card border-0 shadow-lg rounded-4 overflow-hidden">
                    <div class="card-body p-4 bg-white">
                        <div class="text-center mb-4">
                            <h4 class="text-unsulbar fw-bold mb-1"><i class="bi bi-braces"></i> EvalCode</h4>
                            <p class="text-muted small">Registrasi Akun Mahasiswa</p>
                        </div>

                        <form method="POST" action="{{ route('register') }}">
                            @csrf

                            <div class="mb-3">
                                <label for="name" class="form-label text-muted small fw-semibold">Nama Lengkap</label>
                                <input id="name" type="text"
                                    class="form-control @error('name') is-invalid @enderror" name="name"
                                    value="{{ old('name') }}" required autocomplete="name" autofocus
                                    placeholder="Masukkan nama lengkap">
                                @error('name')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>

                            <div class="mb-3">
                                <label for="nim_nip" class="form-label text-muted small fw-semibold">NIM</label>
                                <input id="nim_nip" type="text"
                                    class="form-control @error('nim_nip') is-invalid @enderror" name="nim_nip"
                                    value="{{ old('nim_nip') }}" required autocomplete="nim_nip"
                                    placeholder="Contoh: D0221001">
                                @error('nim_nip')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>

                            <div class="mb-3">
                                <label for="password" class="form-label text-muted small fw-semibold">Password</label>
                                <input id="password" type="password"
                                    class="form-control @error('password') is-invalid @enderror" name="password" required
                                    autocomplete="new-password" placeholder="Minimal 8 karakter">
                                @error('password')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>

                            <div class="mb-4">
                                <label for="password-confirm" class="form-label text-muted small fw-semibold">Konfirmasi
                                    Password</label>
                                <input id="password-confirm" type="password" class="form-control"
                                    name="password_confirmation" required autocomplete="new-password"
                                    placeholder="Ulangi password">
                            </div>

                            <div class="d-grid gap-2">
                                <button type="submit" class="btn btn-unsulbar fw-semibold py-2">
                                    Daftar Sekarang
                                </button>
                            </div>

                            <div class="text-center mt-4">
                                <p class="small text-muted mb-0">Sudah memiliki akun? <a href="{{ route('login') }}"
                                        class="text-decoration-none text-unsulbar fw-semibold">Login</a></p>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
