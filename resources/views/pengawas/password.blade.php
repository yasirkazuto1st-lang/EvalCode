@extends('layouts.sidebar')

@section('title', 'Ganti Password')

@section('sidebar-menu')
    <a href="{{ route('pengawas.dashboard') }}" class="list-group-item list-group-item-action bg-transparent {{ request()->routeIs('pengawas.dashboard', 'pengawas.ujian.*') ? 'active text-nowrap' : 'text-nowrap' }}">
        <i class="bi bi-card-list sidebar-icon"></i> <span class="sidebar-text">Daftar Ujian</span>
    </a>
@endsection

@section('content')
<div class="container-fluid py-4">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <h4 class="fw-bold mb-4">Ganti Password</h4>
            <div class="card border-0 shadow-sm rounded-4">
                <div class="card-body p-4">
                    <form action="#" method="POST">
                        @csrf
                        <div class="mb-3">
                            <label class="form-label text-muted small fw-semibold">Password Sekarang</label>
                            <input type="password" class="form-control" name="current_password" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label text-muted small fw-semibold">Password Baru</label>
                            <input type="password" class="form-control" name="new_password" required>
                        </div>
                        <div class="mb-4">
                            <label class="form-label text-muted small fw-semibold">Ulang Password Baru</label>
                            <input type="password" class="form-control" name="new_password_confirmation" required>
                        </div>
                        <button type="submit" class="btn btn-unsulbar px-4 fw-semibold w-100">Simpan Password</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
