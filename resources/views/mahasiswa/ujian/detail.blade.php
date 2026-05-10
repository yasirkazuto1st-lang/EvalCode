@extends('layouts.app')

@section('content')
    <div class="container mt-4">
        <!-- Back Button -->
        <a href="{{ route('dashboard') }}" class="btn btn-sm btn-back mb-3">
            <i class="bi bi-arrow-left"></i>
        </a>

        <!-- Header Informasi Ujian -->
        <div class="card border-0 shadow-sm rounded-4 mb-4 detail-ujian-header position-relative overflow-hidden"
            style="background: linear-gradient(135deg, var(--bs-primary) 0%, #4a0000 100%); color: white;">
            <i class="bi bi-mortarboard position-absolute opacity-10"
                style="font-size: 12rem; right: -2rem; top: -3rem; transform: rotate(15deg);"></i>

            <div
                class="card-body p-3 p-md-4 d-flex flex-column flex-md-row justify-content-between align-items-md-center position-relative z-1">
                <div class="mb-3 mb-md-0">
                    <span class="badge bg-white text-primary mb-2 px-2 py-1 rounded-pill shadow-sm fw-bold"><i
                            class="bi bi-circle-fill text-success small me-1"></i> Sedang Berlangsung</span>
                    <h4 class="fw-bold mb-1">{{ $exam->judul }}</h4>
                    <div class="d-flex flex-wrap align-items-center gap-3 opacity-75 fs-6 mt-1">
                        <span><i class="bi bi-clock me-1"></i> Waktu: {{ $exam->durasi }} Menit</span>
                        <span><i class="bi bi-check2-square me-1"></i> Passing Grade: {{ $exam->passing_grade }} Pts</span>
                    </div>
                </div>
                <div class="text-md-end mt-3 mt-md-0">
                    <div class="bg-black bg-opacity-25 rounded-4 p-3 border border-white border-opacity-25 shadow-sm text-center"
                        style="backdrop-filter: blur(10px); min-width: 150px;">
                        <span class="small opacity-75 d-block text-uppercase fw-semibold"
                            style="letter-spacing: 1px; font-size: 10px;">Progress Anda</span>
                        <!-- Placeholder progress, can be dynamic later -->
                        <h4 class="fw-bold mb-0 text-white">{{ $acceptedCount }} / {{ $exam->soals->count() }} <span
                                class="fs-6 fw-normal">Soal</span></h4>
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
                            @forelse($leaderboard as $idx => $lb)
                                @php
                                    $bgClass = 'bg-light';
                                    $textClass = 'text-secondary';
                                    $badgeClass = 'bg-secondary';

                                    if ($idx == 0) {
                                        $bgClass = 'bg-warning bg-opacity-10';
                                        $textClass = 'text-warning';
                                        $badgeClass = 'bg-warning text-dark';
                                    } elseif ($idx == 1) {
                                        $textClass = 'text-secondary';
                                        $badgeClass = 'bg-secondary';
                                    } elseif ($idx == 2) {
                                        $badgeClass = 'bg-danger';
                                        $textClass = 'text-muted';
                                    }

                                    if ($lb->user_id == Auth::id()) {
                                        $bgClass = 'bg-primary bg-opacity-10 border border-primary';
                                        $textClass = 'text-primary';
                                        $badgeClass = 'bg-primary text-white';
                                    }
                                @endphp
                                <li
                                    class="list-group-item d-flex justify-content-between align-items-center py-3 border-0 {{ $bgClass }} rounded mb-2">
                                    <div>
                                        <span class="badge {{ $badgeClass }} rounded-pill me-2 shadow-sm">
                                            @if ($idx == 0)
                                                <i class="bi bi-trophy-fill"></i>
                                            @endif {{ $idx + 1 }}
                                        </span>
                                        <span class="fw-semibold">{{ $lb->name }}</span>
                                    </div>
                                    <span class="fw-bold {{ $textClass }}">{{ $lb->total_skor }} Pts</span>
                                </li>
                            @empty
                                <li class="list-group-item text-center text-muted py-4 border-0">
                                    Belum ada data leaderboard.
                                </li>
                            @endforelse
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
                            @forelse($exam->soals as $index => $soal)
                                <div class="col-12">
                                    @php
                                        $borderColor = 'border-light-subtle bg-white';
                                        if ($soal->status_pengerjaan == 'Accepted') {
                                            $borderColor = 'border-success border-2 bg-success bg-opacity-10';
                                        } elseif (
                                            in_array($soal->status_pengerjaan, [
                                                'Wrong Answer',
                                                'Time Limit Exceeded',
                                                'Runtime Error',
                                            ])
                                        ) {
                                            $borderColor = 'border-danger border-2 bg-danger bg-opacity-10';
                                        }
                                    @endphp
                                    <div
                                        class="border rounded-4 p-3 d-flex flex-column flex-md-row justify-content-between align-items-md-center shadow-sm hover-shadow transition-all {{ $borderColor }}">
                                        <div class="mb-3 mb-md-0">
                                            <h6 class="fw-bold mb-1 text-dark">{{ $index + 1 }}. {{ $soal->nama_soal }}
                                            </h6>
                                            <div class="text-muted small mt-2 d-flex gap-3 flex-wrap">
                                                <span><i class="bi bi-star-fill text-warning me-1"></i>Bobot:
                                                    {{ $soal->bobot_nilai }}</span>

                                                @php
                                                    $statusColor = 'text-secondary';
                                                    if ($soal->status_pengerjaan == 'Accepted') {
                                                        $statusColor = 'text-success';
                                                    } elseif (
                                                        in_array($soal->status_pengerjaan, [
                                                            'Wrong Answer',
                                                            'Time Limit Exceeded',
                                                            'Runtime Error',
                                                        ])
                                                    ) {
                                                        $statusColor = 'text-danger';
                                                    }
                                                @endphp

                                                <span class="{{ $statusColor }} fw-semibold">
                                                    Status: {{ $soal->status_pengerjaan }}
                                                    @if ($soal->status_pengerjaan != 'Belum Dikerjakan')
                                                        (Skor Tertinggi: {{ $soal->skor_tertinggi }})
                                                    @endif
                                                </span>
                                            </div>
                                        </div>
                                        <a href="{{ route('workspace', ['examId' => $exam->ujian_id, 'soalId' => $soal->soal_id]) }}"
                                            class="btn btn-sm btn-primary rounded-pill px-4 shadow-sm">Mulai Kerjakan</a>
                                    </div>
                                </div>
                            @empty
                                <div class="col-12 text-center text-muted py-5">
                                    <i class="bi bi-inbox fs-2 d-block mb-2"></i>
                                    <p class="mb-0">Belum ada soal untuk ujian ini.</p>
                                </div>
                            @endforelse
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <style>
        .hover-shadow:hover {
            transform: translateY(-2px);
            box-shadow: 0 .125rem .25rem rgba(0, 0, 0, .075) !important;
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
