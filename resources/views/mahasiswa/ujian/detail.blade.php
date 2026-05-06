@extends('layouts.app')

@section('content')
<div class="container mt-4">
    <!-- Back Button -->
    <a href="{{ route('dashboard') }}" class="btn btn-sm btn-back mb-3">
        <i class="bi bi-arrow-left"></i>
    </a>

    <!-- Header Informasi Ujian -->
    <div class="card border-0 shadow-sm rounded-4 mb-4 detail-ujian-header position-relative overflow-hidden" style="background: linear-gradient(135deg, var(--bs-primary) 0%, #4a0000 100%); color: white;">
        <i class="bi bi-mortarboard position-absolute opacity-10" style="font-size: 12rem; right: -2rem; top: -3rem; transform: rotate(15deg);"></i>
        
        <div class="card-body p-3 p-md-4 d-flex flex-column flex-md-row justify-content-between align-items-md-center position-relative z-1">
            <div class="mb-3 mb-md-0">
                <span class="badge bg-white text-primary mb-2 px-2 py-1 rounded-pill shadow-sm fw-bold"><i class="bi bi-circle-fill text-success small me-1"></i> Sedang Berlangsung</span>
                <h4 class="fw-bold mb-1">Ujian Komprehensif Dasar Pemrograman</h4>
                <div class="d-flex flex-wrap align-items-center gap-3 opacity-75 fs-6 mt-1">
                    <span><i class="bi bi-clock me-1"></i> Waktu: 120 Menit</span>
                    <span><i class="bi bi-check2-square me-1"></i> Passing Grade: 70</span>
                </div>
            </div>
            <div class="text-md-end mt-3 mt-md-0">
                <div class="bg-black bg-opacity-25 rounded-4 p-3 border border-white border-opacity-25 shadow-sm text-center" style="backdrop-filter: blur(10px); min-width: 150px;">
                    <span class="small opacity-75 d-block text-uppercase fw-semibold" style="letter-spacing: 1px; font-size: 10px;">Progress Anda</span>
                    <h4 class="fw-bold mb-0 text-white">0 / 4 <span class="fs-6 fw-normal">Soal</span></h4>
                </div>
            </div>
        </div>
    </div>

    <!-- 2 Kolom Konten -->
    <div class="row g-4">
        <!-- Kiri: Leaderboard -->
        <div class="col-md-4">
            <div class="card border-0 shadow-sm rounded-4 h-100">
                <div class="card-header bg-white border-bottom-0 pt-4 pb-0">
                    <h5 class="fw-bold mb-0"><i class="bi bi-trophy text-warning me-2"></i> Leaderboard</h5>
                </div>
                <div class="card-body p-0 mt-3">
                    <ul class="list-group list-group-flush">
                        <li class="list-group-item d-flex justify-content-between align-items-center py-3 border-0 bg-warning bg-opacity-10 rounded mb-2">
                            <div><span class="badge bg-warning text-dark rounded-pill me-2 shadow-sm"><i class="bi bi-trophy-fill"></i> 1</span> <span class="fw-semibold">Ahmad Fauzi</span></div>
                            <span class="fw-bold text-warning">100 Pts</span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center py-3 border-0 bg-light rounded mb-2">
                            <div><span class="badge bg-secondary rounded-pill me-2 shadow-sm">2</span> <span class="fw-semibold">Budi Santoso</span></div>
                            <span class="fw-bold text-secondary">80 Pts</span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center py-3 border-0 bg-light rounded mb-2">
                            <div><span class="badge bg-danger rounded-pill me-2 shadow-sm" style="background-color: #cd7f32 !important;">3</span> <span class="fw-semibold">Caca Marica</span></div>
                            <span class="fw-bold text-muted" style="color: #cd7f32 !important;">65 Pts</span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center py-3 border border-primary bg-primary bg-opacity-10 rounded mb-2">
                            <div><span class="badge bg-primary text-white rounded-pill me-2 shadow-sm">4</span> <span class="fw-bold">{{ Auth::user()->name ?? 'Anda' }}</span></div>
                            <span class="fw-bold text-primary">0 Pts</span>
                        </li>
                    </ul>
                </div>
            </div>
        </div>

        <!-- Kanan: Daftar Soal -->
        <div class="col-md-8">
            <div class="card border-0 shadow-sm rounded-4 h-100">
                <div class="card-header bg-white border-bottom-0 pt-4 pb-0">
                    <h5 class="fw-bold mb-0"><i class="bi bi-list-task text-primary me-2"></i> Daftar Soal</h5>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <!-- Soal 1 -->
                        <div class="col-12">
                            <div class="border border-success border-opacity-50 rounded-4 p-3 d-flex flex-column flex-md-row justify-content-between align-items-md-center bg-success bg-opacity-10 shadow-sm transition-all">
                                <div class="mb-3 mb-md-0">
                                    <h6 class="fw-bold mb-1 text-success"><i class="bi bi-check-circle-fill me-2"></i>1. Hello World & Basic I/O</h6>
                                    <div class="text-muted small mt-2 d-flex gap-3">
                                        <span><i class="bi bi-star-fill text-warning me-1"></i>Bobot: 20</span>
                                        <span class="text-success fw-semibold">Status: Accepted</span>
                                    </div>
                                </div>
                                <a href="{{ route('workspace') }}" class="btn btn-sm btn-success rounded-pill px-4 shadow-sm">Lihat Solusi</a>
                            </div>
                        </div>
                        <!-- Soal 2 -->
                        <div class="col-12">
                            <div class="border border-danger border-opacity-50 rounded-4 p-3 d-flex flex-column flex-md-row justify-content-between align-items-md-center bg-danger bg-opacity-10 shadow-sm transition-all">
                                <div class="mb-3 mb-md-0">
                                    <h6 class="fw-bold mb-1 text-danger"><i class="bi bi-x-circle-fill me-2"></i>2. Deret Fibonacci</h6>
                                    <div class="text-muted small mt-2 d-flex gap-3">
                                        <span><i class="bi bi-star-fill text-warning me-1"></i>Bobot: 30</span>
                                        <span class="text-danger fw-semibold">Status: Wrong Answer</span>
                                    </div>
                                </div>
                                <a href="{{ route('workspace') }}" class="btn btn-sm btn-danger rounded-pill px-4 shadow-sm">Kerjakan Ulang</a>
                            </div>
                        </div>
                        <!-- Soal 3 -->
                        <div class="col-12">
                            <div class="border rounded-4 p-3 d-flex flex-column flex-md-row justify-content-between align-items-md-center bg-white shadow-sm hover-shadow transition-all border-light-subtle">
                                <div class="mb-3 mb-md-0">
                                    <h6 class="fw-bold mb-1 text-dark">3. Knapsack Problem (DP)</h6>
                                    <div class="text-muted small mt-2 d-flex gap-3">
                                        <span><i class="bi bi-star-fill text-warning me-1"></i>Bobot: 40</span>
                                        <span class="text-secondary fw-semibold">Status: Belum Dikerjakan</span>
                                    </div>
                                </div>
                                <a href="{{ route('workspace') }}" class="btn btn-sm btn-primary rounded-pill px-4 shadow-sm">Mulai Kerjakan</a>
                            </div>
                        </div>
                        <!-- Soal 4 -->
                        <div class="col-12">
                            <div class="border rounded-4 p-3 d-flex flex-column flex-md-row justify-content-between align-items-md-center bg-white shadow-sm hover-shadow transition-all border-light-subtle">
                                <div class="mb-3 mb-md-0">
                                    <h6 class="fw-bold mb-1 text-dark">4. Binary Search Tree</h6>
                                    <div class="text-muted small mt-2 d-flex gap-3">
                                        <span><i class="bi bi-star-fill text-warning me-1"></i>Bobot: 10</span>
                                        <span class="text-secondary fw-semibold">Status: Belum Dikerjakan</span>
                                    </div>
                                </div>
                                <a href="{{ route('workspace') }}" class="btn btn-sm btn-primary rounded-pill px-4 shadow-sm">Mulai Kerjakan</a>
                            </div>
                        </div>
                    </div></div>
            </div>
        </div>
    </div>
</div>

<style>
.hover-shadow:hover {
    transform: translateY(-2px);
    box-shadow: 0 .125rem .25rem rgba(0,0,0,.075)!important;
    border-color: #800000 !important;
}
.transition-all {
    transition: all .2s ease;
}
.btn-outline-unsulbar {
    color: #800000;
    border-color: #800000;
}
.btn-outline-unsulbar:hover {
    color: #fff;
    background-color: #800000;
    border-color: #800000;
}
</style>
@endsection
