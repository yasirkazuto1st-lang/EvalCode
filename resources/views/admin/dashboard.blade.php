@extends('layouts.sidebar')

@section('title', 'Admin Dashboard')

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
        <!-- Welcome Banner -->
        <div class="row g-4 mb-4">
            <div class="col-md-12">
                <div class="card border-0 shadow-sm rounded-4 position-relative overflow-hidden"
                    style="background: linear-gradient(135deg, var(--bs-primary) 0%, #4a0000 100%); color: white;">
                    <i class="bi bi-speedometer2 position-absolute opacity-10"
                        style="font-size: 15rem; right: -2rem; top: -4rem; transform: rotate(15deg);"></i>
                    <div class="card-body p-4 p-md-5 position-relative z-1">
                        <span class="badge bg-white text-primary mb-3 px-3 py-2 rounded-pill shadow-sm fw-bold"><i
                                class="bi bi-shield-lock-fill text-danger me-1"></i> Mode Administrator</span>
                        <h2 class="fw-bold mb-3">Selamat Datang di Pusat Kendali!</h2>
                        <p class="fs-6 opacity-75 mb-0" style="max-width: 650px; line-height: 1.6;">Anda memegang kendali
                            penuh atas sistem EvalCode. Gunakan dasbor ini untuk mengelola bank soal, memantau ujian yang
                            sedang berlangsung secara *real-time*, dan mengatur seluruh akses pengguna aplikasi.</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="d-flex justify-content-between align-items-center mb-3 mt-5">
            <h5 class="fw-bold mb-0 text-dark">Ringkasan Sistem</h5>
        </div>

        <!-- Stats Row 1 -->
        <div class="row g-4 mb-4">
            <!-- Card Ujian -->
            <div class="col-md-3">
                <div class="card border-0 shadow-sm rounded-4 h-100 overflow-hidden position-relative text-white hover-shadow"
                    style="background: linear-gradient(135deg, #0d6efd 0%, #0a58ca 100%); transition: transform 0.3s ease;">
                    <i class="bi bi-journal-text position-absolute opacity-25"
                        style="font-size: 6rem; right: -10px; bottom: -20px; transform: rotate(-15deg);"></i>
                    <div class="card-body p-4 position-relative z-1">
                        <h6 class="fw-semibold opacity-75 mb-2">Total Ujian Aktif</h6>
                        <h2 class="fw-bold mb-0">{{ $stats->total_ujian }}</h2>
                    </div>
                </div>
            </div>
            <!-- Card Admin -->
            <div class="col-md-3">
                <div class="card border-0 shadow-sm rounded-4 h-100 overflow-hidden position-relative text-white hover-shadow"
                    style="background: linear-gradient(135deg, #17a2b8 0%, #117a8b 100%); transition: transform 0.3s ease;">
                    <i class="bi bi-person-badge position-absolute opacity-25"
                        style="font-size: 6rem; right: -10px; bottom: -20px; transform: rotate(-15deg);"></i>
                    <div class="card-body p-4 position-relative z-1">
                        <h6 class="fw-semibold opacity-75 mb-2">Total Administrator</h6>
                        <h2 class="fw-bold mb-0">{{ $stats->total_admin }}</h2>
                    </div>
                </div>
            </div>
            <!-- Card Pengawas -->
            <div class="col-md-3">
                <div class="card border-0 shadow-sm rounded-4 h-100 overflow-hidden position-relative text-dark hover-shadow"
                    style="background: linear-gradient(135deg, #ffc107 0%, #d39e00 100%); transition: transform 0.3s ease;">
                    <i class="bi bi-person-video2 position-absolute opacity-25"
                        style="font-size: 6rem; right: -10px; bottom: -20px; transform: rotate(-15deg);"></i>
                    <div class="card-body p-4 position-relative z-1">
                        <h6 class="fw-semibold opacity-75 mb-2">Total Pengawas</h6>
                        <h2 class="fw-bold mb-0">{{ $stats->total_pengawas }}</h2>
                    </div>
                </div>
            </div>
            <!-- Card Mahasiswa -->
            <div class="col-md-3">
                <div class="card border-0 shadow-sm rounded-4 h-100 overflow-hidden position-relative text-white hover-shadow"
                    style="background: linear-gradient(135deg, #198754 0%, #146c43 100%); transition: transform 0.3s ease;">
                    <i class="bi bi-mortarboard position-absolute opacity-25"
                        style="font-size: 6rem; right: -10px; bottom: -20px; transform: rotate(-15deg);"></i>
                    <div class="card-body p-4 position-relative z-1">
                        <h6 class="fw-semibold opacity-75 mb-2">Total Mahasiswa</h6>
                        <h2 class="fw-bold mb-0">{{ $stats->total_mahasiswa }}</h2>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
