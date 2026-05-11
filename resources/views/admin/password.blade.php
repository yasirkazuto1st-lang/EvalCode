@extends('layouts.sidebar')

@section('title', 'Ganti Password')

@section('sidebar-menu')
    <a href="{{ route('admin.dashboard') }}"
        class="list-group-item list-group-item-action bg-transparent {{ request()->routeIs('admin.dashboard') ? 'active text-nowrap' : 'text-nowrap' }}">
        <i class="bi bi-speedometer2 sidebar-icon"></i> <span class="sidebar-text">Dashboard</span>
    </a>
    <a href="{{ route('admin.ujian.index') }}"
        class="list-group-item list-group-item-action bg-transparent {{ request()->routeIs('admin.ujian.*') ? 'active text-nowrap' : 'text-nowrap' }}">
        <i class="bi bi-card-list sidebar-icon"></i> <span class="sidebar-text">Manajemen Ujian</span>
    </a>
    <a href="{{ route('admin.users') }}"
        class="list-group-item list-group-item-action bg-transparent {{ request()->routeIs('admin.users') ? 'active text-nowrap' : 'text-nowrap' }}">
        <i class="bi bi-people sidebar-icon"></i> <span class="sidebar-text">Manajemen User</span>
    </a>
@endsection

@section('content')
    <div class="container-fluid py-4">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <h4 class="fw-bold mb-4">Ganti Password</h4>
                
                @if (session('success'))
                    <div class="alert alert-success alert-dismissible fade show rounded-4" role="alert">
                        {{ session('success') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif

                @if($errors->any())
                    <div class="alert alert-danger alert-dismissible fade show rounded-4" role="alert">
                        <ul class="mb-0">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif

                <div class="card border-0 shadow-sm rounded-4">
                    <div class="card-body p-4">
                        <form action="{{ route('password.update') }}" method="POST">
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
                            <button type="submit" class="btn btn-primary px-4 fw-semibold w-100">Simpan Password</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
