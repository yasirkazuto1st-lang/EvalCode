@extends('layouts.app')

@section('content')
<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="fw-bold mb-0">Dashboard Mahasiswa</h2>
            <p class="text-muted">Selamat datang, {{ Auth::user()->name ?? 'Mahasiswa' }}</p>
        </div>
    </div>

    <h5 class="fw-bold mb-3">Ujian Aktif</h5>
    <div class="row g-4">
        <!-- Ujian Card 1 -->
        <div class="col-md-6 col-lg-4">
            <div class="card h-100 border-0 shadow-sm rounded-4 hover-shadow transition-all">
                <div class="card-body p-4 d-flex flex-column">
                    <div class="d-flex justify-content-between align-items-start mb-3">
                        <span class="badge bg-danger rounded-pill">Aktif</span>
                        <span class="text-muted small"><i class="bi bi-clock"></i> 120 Menit</span>
                    </div>
                    <h5 class="fw-bold mb-2">Ujian Komprehensif Dasar Pemrograman</h5>
                    <p class="text-muted small mb-4">Ujian ini mencakup materi dasar pemrograman, logika, dan struktur data sederhana.</p>
                    <button type="button" class="btn btn-unsulbar w-100 fw-semibold mt-auto" data-bs-toggle="modal" data-bs-target="#tokenModal">Masuk Ujian</button>
                </div>
            </div>
        </div>

        <!-- Ujian Card 2 -->
        <div class="col-md-6 col-lg-4">
            <div class="card h-100 border-0 shadow-sm rounded-4 opacity-75">
                <div class="card-body p-4 d-flex flex-column">
                    <div class="d-flex justify-content-between align-items-start mb-3">
                        <span class="badge bg-secondary rounded-pill">Selesai</span>
                        <span class="text-muted small"><i class="bi bi-clock"></i> 90 Menit</span>
                    </div>
                    <h5 class="fw-bold mb-2">Kuis Struktur Data</h5>
                    <p class="text-muted small mb-4">Ujian telah selesai dan dinilai. Anda bisa melihat hasil eksekusi kode Anda.</p>
                    <button class="btn btn-outline-secondary w-100 fw-semibold mt-auto" disabled>Lihat Hasil</button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Token Ujian -->
<div class="modal fade" id="tokenModal" tabindex="-1" aria-labelledby="tokenModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content border-0 shadow rounded-4">
      <div class="modal-header border-bottom-0">
        <h5 class="modal-title fw-bold" id="tokenModalLabel">Masukkan Token Ujian</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body py-4">
        <p class="text-muted small mb-3">Silakan minta token ujian dari Pengawas untuk dapat memulai pengerjaan ujian ini.</p>
        <form action="{{ route('ujian.detail') }}" method="GET">
            <div class="mb-4">
                <input type="text" class="form-control form-control-lg font-monospace text-center fw-bold text-uppercase" placeholder="Contoh: A7X-92Q" name="token" required autofocus>
            </div>
            <div class="d-grid">
                <button type="submit" class="btn btn-unsulbar btn-lg fw-semibold">Validasi Token</button>
            </div>
        </form>
      </div>
    </div>
  </div>
</div>

<style>
.hover-shadow:hover {
    transform: translateY(-5px);
    box-shadow: 0 .5rem 1rem rgba(0,0,0,.15)!important;
}
.transition-all {
    transition: all .3s ease;
}
</style>
@endsection
