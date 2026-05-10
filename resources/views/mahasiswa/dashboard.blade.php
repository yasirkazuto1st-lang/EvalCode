@extends('layouts.app')

@section('content')
<div class="container mt-4">

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show rounded-4" role="alert">
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="fw-bold mb-0">Dashboard Mahasiswa</h2>
            <p class="text-muted">Selamat datang, {{ Auth::user()->name ?? 'Mahasiswa' }}</p>
        </div>
    </div>

    <h5 class="fw-bold mb-3">Ujian Aktif</h5>
    <div class="row g-4">
        @forelse($activeExams as $exam)
            <div class="col-md-6 col-lg-4">
                <div class="card h-100 border-0 shadow-sm rounded-4 hover-shadow transition-all">
                    <div class="card-body p-4 d-flex flex-column">
                        <div class="d-flex justify-content-between align-items-start mb-3">
                            <span class="badge bg-danger rounded-pill">Aktif</span>
                            <span class="text-muted small"><i class="bi bi-clock"></i> {{ $exam->durasi }} Menit</span>
                        </div>
                        <h5 class="fw-bold mb-2">{{ $exam->judul }}</h5>
                        <p class="text-muted small mb-4">{{ Str::limit($exam->deskripsi, 80) }}</p>
                        <button type="button" class="btn btn-unsulbar w-100 fw-semibold mt-auto" 
                                data-bs-toggle="modal" data-bs-target="#tokenModal-{{ $exam->ujian_id }}">
                            Masuk Ujian
                        </button>
                    </div>
                </div>
            </div>

            <!-- Modal Token Ujian -->
            <div class="modal fade" id="tokenModal-{{ $exam->ujian_id }}" tabindex="-1" aria-hidden="true">
              <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content border-0 shadow rounded-4">
                  <div class="modal-header border-bottom-0">
                    <h5 class="modal-title fw-bold">Masukkan Token Ujian</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                  </div>
                  <div class="modal-body py-4">
                    <p class="text-muted small mb-3">Silakan minta token ujian dari Pengawas untuk dapat memulai pengerjaan ujian <strong>{{ $exam->judul }}</strong>.</p>
                    <form action="{{ route('ujian.join', $exam->ujian_id) }}" method="POST">
                        @csrf
                        <div class="mb-4">
                            <input type="text" class="form-control form-control-lg font-monospace text-center fw-bold text-uppercase" 
                                   placeholder="Contoh: A7X-92Q" name="token" required autofocus>
                        </div>
                        <div class="d-grid">
                            <button type="submit" class="btn btn-unsulbar btn-lg fw-semibold">Validasi Token</button>
                        </div>
                    </form>
                  </div>
                </div>
              </div>
            </div>
        @empty
            <div class="col-12">
                <div class="text-center py-5 text-muted bg-white rounded-4 shadow-sm">
                    <i class="bi bi-inbox fs-2 d-block mb-2"></i>
                    <p class="mb-0">Belum ada ujian yang aktif saat ini.</p>
                </div>
            </div>
        @endforelse
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
