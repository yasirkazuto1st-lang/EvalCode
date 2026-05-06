@extends('layouts.sidebar')

@section('title', 'Dashboard Pengawas')

@section('sidebar-menu')
    <a href="{{ route('pengawas.dashboard') }}" class="list-group-item list-group-item-action bg-transparent active">
        <i class="bi bi-card-list sidebar-icon"></i> <span class="sidebar-text">Daftar Ujian</span>
    </a>
@endsection

@section('content')
<div class="container-fluid">
    <!-- Section 1: Daftar Ujian Tersedia -->
    <h4 class="fw-bold mb-3"><i class="bi bi-journal-check text-unsulbar me-2"></i>Daftar Ujian Tersedia</h4>
    <div class="row g-4 mb-5">
        <!-- Ujian Aktif -->
        <div class="col-md-6 col-lg-4">
            <div class="card h-100 border-0 shadow-sm rounded-4">
                <div class="card-body p-4 d-flex flex-column">
                    <div class="d-flex justify-content-between align-items-start mb-3">
                        <span class="badge bg-danger rounded-pill">Aktif</span>
                        <span class="text-muted small"><i class="bi bi-clock"></i> 120 Menit</span>
                    </div>
                    <h5 class="fw-bold mb-2">Ujian Komprehensif Dasar Pemrograman</h5>
                    <p class="text-muted small mb-4">Ujian mencakup materi logika dan struktur data. Total 5 soal.</p>
                    <a href="{{ route('pengawas.ujian.detail') }}" class="btn btn-unsulbar w-100 fw-semibold mt-auto">Monitoring & Manajemen</a>
                </div>
            </div>
        </div>

        <!-- Ujian Belum Dimulai -->
        <div class="col-md-6 col-lg-4">
            <div class="card h-100 border-0 shadow-sm rounded-4">
                <div class="card-body p-4 d-flex flex-column">
                    <div class="d-flex justify-content-between align-items-start mb-3">
                        <span class="badge bg-warning text-dark rounded-pill">Belum Dimulai</span>
                        <span class="text-muted small"><i class="bi bi-clock"></i> 90 Menit</span>
                    </div>
                    <h5 class="fw-bold mb-2">Kuis OOP Java</h5>
                    <p class="text-muted small mb-4">Ujian praktikum berorientasi objek menggunakan Java. Total 10 Soal.</p>
                    <a href="{{ route('pengawas.ujian.detail') }}" class="btn btn-outline-unsulbar w-100 fw-semibold mt-auto">Monitoring & Manajemen</a>
                </div>
            </div>
        </div>
    </div>

    <!-- Separator -->
    <hr class="mb-4">

    <!-- Section 2: Daftar Ujian Selesai -->
    <h4 class="fw-bold mb-3"><i class="bi bi-journal-x text-muted me-2"></i>Daftar Ujian Selesai</h4>
    <div class="row g-4">
        <!-- Ujian Selesai 1 -->
        <div class="col-md-6 col-lg-4">
            <div class="card h-100 border-0 shadow-sm rounded-4 opacity-75">
                <div class="card-body p-4 d-flex flex-column">
                    <div class="d-flex justify-content-between align-items-start mb-3">
                        <span class="badge bg-secondary rounded-pill">Selesai</span>
                        <span class="text-muted small"><i class="bi bi-calendar-check"></i> 28 Apr 2026</span>
                    </div>
                    <h5 class="fw-bold mb-2">Ujian Algoritma & Pemrograman</h5>
                    <p class="text-muted small mb-4">Peserta: 32 | Rata-rata Nilai: 78.5</p>
                    <a href="#" class="btn btn-outline-secondary w-100 fw-semibold mt-auto">Lihat Rekap</a>
                </div>
            </div>
        </div>

        <!-- Ujian Selesai 2 -->
        <div class="col-md-6 col-lg-4">
            <div class="card h-100 border-0 shadow-sm rounded-4 opacity-75">
                <div class="card-body p-4 d-flex flex-column">
                    <div class="d-flex justify-content-between align-items-start mb-3">
                        <span class="badge bg-secondary rounded-pill">Selesai</span>
                        <span class="text-muted small"><i class="bi bi-calendar-check"></i> 15 Mar 2026</span>
                    </div>
                    <h5 class="fw-bold mb-2">Kuis Basis Data SQL</h5>
                    <p class="text-muted small mb-4">Peserta: 28 | Rata-rata Nilai: 65.2</p>
                    <a href="#" class="btn btn-outline-secondary w-100 fw-semibold mt-auto">Lihat Rekap</a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
