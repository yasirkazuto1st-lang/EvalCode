@extends('layouts.app')

@section('content')
<div class="container mt-4">

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show rounded-4" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show rounded-4" role="alert">
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if($errors->any())
        <div class="alert alert-danger alert-dismissible fade show rounded-4" role="alert">
            <strong><i class="bi bi-exclamation-triangle-fill me-1"></i> Terjadi Kesalahan:</strong>
            <ul class="mb-0 mt-1">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="fw-bold mb-0">Dashboard Mahasiswa</h2>
            <p class="text-muted">Selamat datang, {{ Auth::user()->name ?? 'Mahasiswa' }}</p>
        </div>
    </div>

    {{-- Ujian Berjalan --}}
    <h5 class="fw-bold mb-3"><i class="bi bi-circle-fill text-success small me-1"></i> Ujian Berjalan</h5>
    <div class="row g-4 mb-4">
        @forelse($activeExams as $exam)
            <div class="col-md-6 col-lg-4">
                <div class="card h-100 border-0 shadow-sm rounded-4 hover-shadow transition-all">
                    <div class="card-body p-4 d-flex flex-column">
                        <div class="d-flex justify-content-between align-items-start mb-3">
                            <span class="badge bg-success rounded-pill">Berjalan</span>
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
                <div class="text-center py-4 text-muted bg-white rounded-4 shadow-sm">
                    <i class="bi bi-inbox fs-3 d-block mb-2"></i>
                    <p class="mb-0">Tidak ada ujian yang sedang berjalan saat ini.</p>
                </div>
            </div>
        @endforelse
    </div>

    {{-- Ujian Belum Dimulai --}}
    <h5 class="fw-bold mb-3"><i class="bi bi-pause-circle-fill text-warning small me-1"></i> Belum Dimulai</h5>
    <div class="row g-4 mb-4">
        @forelse($closedExams as $exam)
            <div class="col-md-6 col-lg-4">
                <div class="card h-100 border-0 shadow-sm rounded-4 hover-shadow transition-all opacity-75">
                    <div class="card-body p-4 d-flex flex-column">
                        <div class="d-flex justify-content-between align-items-start mb-3">
                            <span class="badge bg-warning text-dark rounded-pill">Belum Dimulai</span>
                            <span class="text-muted small"><i class="bi bi-clock"></i> {{ $exam->durasi }} Menit</span>
                        </div>
                        <h5 class="fw-bold mb-2">{{ $exam->judul }}</h5>
                        <p class="text-muted small mb-4">{{ Str::limit($exam->deskripsi, 80) }}</p>
                        <button class="btn btn-outline-secondary w-100 fw-semibold mt-auto" disabled>
                            <i class="bi bi-lock me-1"></i> Menunggu Dimulai
                        </button>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-12">
                <div class="text-center py-4 text-muted bg-white rounded-4 shadow-sm">
                    <p class="mb-0">Tidak ada ujian yang menunggu dimulai.</p>
                </div>
            </div>
        @endforelse
    </div>

    {{-- Ujian Selesai --}}
    <h5 class="fw-bold mb-3"><i class="bi bi-check-circle-fill text-secondary small me-1"></i> Selesai</h5>
    <div class="row g-4 mb-4">
        @forelse($finishedExams as $exam)
            <div class="col-md-6 col-lg-4">
                <div class="card h-100 border-0 shadow-sm rounded-4 hover-shadow transition-all opacity-50">
                    <div class="card-body p-4 d-flex flex-column">
                        <div class="d-flex justify-content-between align-items-start mb-3">
                            <span class="badge bg-secondary rounded-pill">Selesai</span>
                            <span class="text-muted small"><i class="bi bi-clock"></i> {{ $exam->durasi }} Menit</span>
                        </div>
                        <h5 class="fw-bold mb-2">{{ $exam->judul }}</h5>
                        <p class="text-muted small mb-4">{{ Str::limit($exam->deskripsi, 80) }}</p>
                        <button class="btn btn-outline-secondary w-100 fw-semibold mt-auto" disabled>
                            <i class="bi bi-check-circle me-1"></i> Ujian Telah Berakhir
                        </button>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-12">
                <div class="text-center py-4 text-muted bg-white rounded-4 shadow-sm">
                    <p class="mb-0">Belum ada ujian yang selesai.</p>
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
