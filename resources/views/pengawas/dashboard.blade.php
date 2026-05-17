@extends('layouts.sidebar')

@section('title', 'Dashboard Pengawas')

@section('sidebar-menu')
    <a href="{{ route('pengawas.dashboard') }}" class="list-group-item list-group-item-action bg-transparent active">
        <i class="bi bi-card-list sidebar-icon"></i> <span class="sidebar-text">Daftar Ujian</span>
    </a>
@endsection

@section('content')
<div class="container-fluid">

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show rounded-4" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @php $allExams = $activeExams->merge($closedExams); @endphp

    <h4 class="fw-bold mb-3"><i class="bi bi-journal-check text-unsulbar me-2"></i>Daftar Ujian</h4>
    <div class="row g-4 mb-5">
        @forelse($allExams as $exam)
            @php $isActive = $exam->status === 'active'; @endphp
            <div class="col-md-6 col-lg-4">
                <div class="card h-100 border-0 shadow-sm rounded-4">
                    <div class="card-body p-4 d-flex flex-column">
                        <div class="d-flex justify-content-between align-items-start mb-3">
                            @if($isActive)
                                <span class="badge bg-danger rounded-pill"><i class="bi bi-circle-fill me-1" style="font-size: 8px;"></i> Aktif</span>
                            @else
                                <span class="badge bg-warning text-dark rounded-pill">Belum Dimulai</span>
                            @endif
                            <span class="text-muted small"><i class="bi bi-clock"></i> {{ $exam->durasi }} Menit</span>
                        </div>
                        <h5 class="fw-bold mb-2">{{ $exam->judul }}</h5>
                        <p class="text-muted small mb-2">{{ Str::limit($exam->deskripsi, 80) }}</p>
                        <div class="d-flex gap-2 text-muted small mb-4">
                            <span><i class="bi bi-file-text me-1"></i>{{ $exam->soals()->count() }} Soal</span>
                            <span><i class="bi bi-check2-square me-1"></i>PG: {{ $exam->passing_grade }} Pts</span>
                        </div>
                        <a href="{{ route('pengawas.ujian.detail', $exam->ujian_id) }}"
                           class="btn {{ $isActive ? 'btn-unsulbar' : 'btn-outline-unsulbar' }} w-100 fw-semibold mt-auto">
                            Monitoring & Manajemen
                        </a>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-12">
                <div class="text-center py-5 text-muted">
                    <i class="bi bi-inbox fs-2 d-block mb-2"></i>
                    <p>Belum ada ujian yang dibuat. Silakan minta Admin untuk membuat ujian terlebih dahulu.</p>
                </div>
            </div>
        @endforelse
    </div>

    {{-- Ujian Selesai --}}
    @if($finishedExams->count() > 0)
        <h4 class="fw-bold mb-3"><i class="bi bi-check-circle-fill text-secondary me-2"></i>Ujian Selesai</h4>
        <div class="row g-4 mb-5">
            @foreach($finishedExams as $exam)
                <div class="col-md-6 col-lg-4">
                    <div class="card h-100 border-0 shadow-sm rounded-4 opacity-75">
                        <div class="card-body p-4 d-flex flex-column">
                            <div class="d-flex justify-content-between align-items-start mb-3">
                                <span class="badge bg-secondary rounded-pill"><i class="bi bi-check-circle-fill me-1"></i> Selesai</span>
                                <span class="text-muted small"><i class="bi bi-clock"></i> {{ $exam->durasi }} Menit</span>
                            </div>
                            <h5 class="fw-bold mb-2">{{ $exam->judul }}</h5>
                            <p class="text-muted small mb-2">{{ Str::limit($exam->deskripsi, 80) }}</p>
                            <div class="d-flex gap-2 text-muted small mb-4">
                                <span><i class="bi bi-file-text me-1"></i>{{ $exam->soals()->count() }} Soal</span>
                                <span><i class="bi bi-check2-square me-1"></i>PG: {{ $exam->passing_grade }} Pts</span>
                            </div>
                            <a href="{{ route('pengawas.ujian.detail', $exam->ujian_id) }}"
                               class="btn btn-outline-secondary w-100 fw-semibold mt-auto">
                                Lihat Hasil
                            </a>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @endif
</div>
@endsection
