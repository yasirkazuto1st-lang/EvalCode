@extends('layouts.sidebar')

@section('title', 'Detail Monitoring Ujian')

@section('sidebar-menu')
    <a href="{{ route('pengawas.dashboard') }}" class="list-group-item list-group-item-action bg-transparent active">
        <i class="bi bi-card-list sidebar-icon"></i> <span class="sidebar-text">Daftar Ujian</span>
    </a>
@endsection

@section('content')
    <style>
        .rotate-180 {
            transform: rotate(180deg);
        }

        .token-display {
            font-family: 'Courier New', monospace;
            letter-spacing: 4px;
            font-size: 1.8rem;
            transition: all 0.3s ease;
        }

        .token-display.refreshing {
            opacity: 0;
            transform: scale(0.8);
        }

        .countdown-bar {
            height: 4px;
            background: rgba(255, 255, 255, 0.2);
            border-radius: 2px;
            overflow: hidden;
            margin-top: 8px;
        }

        .countdown-bar-fill {
            height: 100%;
            background: linear-gradient(90deg, #ffc107, #ff5722);
            border-radius: 2px;
            transition: width 1s linear;
        }

        @keyframes pulse-glow {

            0%,
            100% {
                box-shadow: 0 0 5px rgba(255, 193, 7, 0.3);
            }

            50% {
                box-shadow: 0 0 20px rgba(255, 193, 7, 0.6);
            }
        }

        .token-box.active-pulse {
            animation: pulse-glow 2s infinite;
        }
    </style>

    <div class="container-fluid py-4">
        <!-- Back Button -->
        <a href="{{ route('pengawas.dashboard') }}" class="btn btn-sm btn-back mb-3">
            <i class="bi bi-arrow-left"></i>
        </a>

        @if (session('success'))
            <div class="alert alert-success alert-dismissible fade show rounded-4" role="alert">
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        @if (session('error'))
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

        <!-- Header Informasi Ujian -->
        <div class="card border-0 shadow-sm rounded-4 mb-4 position-relative overflow-hidden"
            style="background: linear-gradient(135deg, var(--bs-primary) 0%, #4a0000 100%); color: white;">
            <i class="bi bi-laptop position-absolute opacity-10"
                style="font-size: 12rem; right: -2rem; top: -3rem; transform: rotate(15deg);"></i>

            <div
                class="card-body p-3 p-md-4 d-flex flex-column flex-md-row justify-content-between align-items-md-center position-relative z-1">
                <div class="mb-3 mb-md-0">
                    @if ($exam->status === 'active')
                        <span class="badge bg-white text-primary mb-2 px-2 py-1 rounded-pill shadow-sm fw-bold">
                            <i class="bi bi-circle-fill text-success small me-1"></i> Sedang Berlangsung
                        </span>
                    @elseif ($exam->status === 'finished')
                        <span class="badge bg-white text-dark mb-2 px-2 py-1 rounded-pill shadow-sm fw-bold">
                            <i class="bi bi-check-circle-fill text-secondary small me-1"></i> Selesai
                        </span>
                    @else
                        <span class="badge bg-warning text-dark mb-2 px-2 py-1 rounded-pill shadow-sm fw-bold">
                            <i class="bi bi-pause-circle small me-1"></i> Belum Dimulai
                        </span>
                    @endif
                    <h4 class="fw-bold mb-1">{{ $exam->judul }}</h4>
                    <p class="mb-1 opacity-75 small">{{ $exam->deskripsi }}</p>
                    <div class="d-flex flex-wrap align-items-center gap-3 opacity-75 fs-6 mt-1">
                        <span><i class="bi bi-clock me-1"></i> Durasi: {{ $exam->durasi }} Menit</span>
                        <span><i class="bi bi-check2-square me-1"></i> Passing Grade: {{ $exam->passing_grade }} Pts</span>
                        <span><i class="bi bi-file-text me-1"></i> {{ $exam->soals->count() }} Soal</span>
                    </div>
                </div>

                <!-- Token & Action Section -->
                <div class="d-flex flex-column gap-2" style="width: 100%; max-width: 400px;">
                    <div class="d-flex gap-2">
                        <!-- Exam Timer Box -->
                        @if ($exam->status === 'active')
                            <div id="examTimerBox"
                                class="bg-black bg-opacity-25 rounded-4 p-3 px-3 border border-white border-opacity-25 shadow-sm text-center flex-fill d-flex flex-column justify-content-center"
                                style="backdrop-filter: blur(10px);">
                                <span class="small opacity-75 d-block text-uppercase fw-semibold"
                                    style="letter-spacing: 1px; font-size: 10px;">Sisa Waktu Ujian</span>
                                <div id="examTimerDisplay" class="fw-bold text-white mb-0 fs-3"
                                    style="font-family: 'Courier New', monospace; letter-spacing: 1px;">--:--:--</div>
                            </div>
                        @endif

                        <!-- Token Box -->
                        <div id="tokenBox"
                            class="bg-black bg-opacity-25 rounded-4 p-3 px-3 border border-white border-opacity-25 shadow-sm text-center {{ $exam->status === 'active' ? 'active-pulse' : '' }} token-box flex-fill d-flex flex-column justify-content-center"
                            style="backdrop-filter: blur(10px);">
                            <span class="small opacity-75 d-block text-uppercase fw-semibold"
                                style="letter-spacing: 1px; font-size: 10px;">Token Ujian</span>

                            @if ($exam->status === 'active' && $activeToken)
                                <div id="tokenDisplay" class="token-display fw-bold text-white mb-0">
                                    {{ $activeToken->kode_token }}</div>
                            @else
                                <div id="tokenDisplay" class="token-display fw-bold text-white mb-0 opacity-50">------</div>
                            @endif

                            <!-- Countdown -->
                            <div id="countdownSection" style="{{ $exam->status === 'active' ? '' : 'display:none;' }}">
                                <div class="countdown-bar">
                                    <div id="countdownBar" class="countdown-bar-fill" style="width: 100%;"></div>
                                </div>
                                <small id="countdownText" class="d-block opacity-75 mt-1" style="font-size: 10px;">
                                    <i class="bi bi-arrow-repeat me-1"></i>Token baru dalam <span
                                        id="countdownSeconds">60</span> detik
                                </small>
                            </div>

                            @if ($exam->status !== 'active')
                                <small class="d-block opacity-50 mt-1" style="font-size: 10px;">Mulai ujian untuk mengaktifkan
                                    token</small>
                            @endif
                        </div>
                    </div>

                    <!-- Start / Pause / Finish Buttons -->
                    @if ($exam->status === 'finished')
                        <div class="btn btn-secondary w-100 fw-bold shadow-sm rounded-pill disabled">
                            <i class="bi bi-check-circle-fill me-2"></i> Ujian Telah Selesai
                        </div>
                    @elseif ($exam->status === 'active')
                        <div class="d-flex gap-2">
                            <button type="button" class="btn btn-warning flex-fill fw-bold shadow-sm rounded-pill"
                                data-bs-toggle="modal" data-bs-target="#endExamModal">
                                <i class="bi bi-pause-circle-fill me-1"></i> Pause
                            </button>
                            <button type="button" class="btn btn-danger flex-fill fw-bold shadow-sm rounded-pill"
                                data-bs-toggle="modal" data-bs-target="#finishExamModal">
                                <i class="bi bi-stop-circle-fill me-1"></i> Akhiri
                            </button>
                        </div>
                    @else
                        <div class="d-flex gap-2">
                            <button type="button" class="btn btn-light flex-fill fw-bold shadow-sm rounded-pill text-primary"
                                data-bs-toggle="modal" data-bs-target="#startExamModal">
                                <i class="bi bi-play-circle-fill me-1"></i> Mulai
                            </button>
                            <button type="button" class="btn btn-danger flex-fill fw-bold shadow-sm rounded-pill"
                                data-bs-toggle="modal" data-bs-target="#finishExamModal">
                                <i class="bi bi-stop-circle-fill me-1"></i> Akhiri
                            </button>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- 2 Kolom Konten -->
        <div class="row g-4">
            <!-- Kiri: Daftar Soal -->
            <div class="col-md-3">
                <div class="card border-0 shadow-sm rounded-4">
                    <div class="card-header bg-white border-bottom-0 pt-4 pb-0">
                        <h5 class="fw-bold mb-0"><i class="bi bi-list-task text-primary me-2"></i> Daftar Soal</h5>
                    </div>
                    <div class="card-body">
                        <div class="list-group list-group-flush">
                            @forelse($exam->soals as $index => $soal)
                                @php
                                    $mockPercent = round($soal->success_percentage);
                                    $mockCount = $soal->success_count;

                                    if ($totalParticipants == 0) {
                                        $color = 'secondary';
                                        $colorClass = 'secondary';
                                        $level = 'Belum Dikerjakan';
                                        $icon = 'dash-circle-fill';
                                    } elseif ($mockPercent <= 25) {
                                        $color = 'danger';
                                        $colorClass = 'danger';
                                        $level = 'Sangat Sulit';
                                        $icon = 'exclamation-triangle-fill';
                                    } elseif ($mockPercent <= 50) {
                                        $color = 'warning text-dark';
                                        $colorClass = 'warning';
                                        $level = 'Sulit';
                                        $icon = 'activity';
                                    } elseif ($mockPercent <= 75) {
                                        $color = 'primary';
                                        $colorClass = 'primary';
                                        $level = 'Normal';
                                        $icon = 'info-circle-fill';
                                    } else {
                                        $color = 'success';
                                        $colorClass = 'success';
                                        $level = 'Gampang';
                                        $icon = 'check-circle-fill';
                                    }
                                @endphp
                                <a href="{{ route('pengawas.ujian.soal', ['examId' => $exam->ujian_id, 'soalId' => $soal->soal_id]) }}"
                                    class="list-group-item list-group-item-action border py-3 rounded mb-2 d-flex flex-column gap-1 shadow-sm">
                                    <div class="d-flex justify-content-between align-items-start mb-1">
                                        <span class="fw-semibold text-dark">{{ $index + 1 }}. {{ $soal->nama_soal }}</span>
                                        <span class="badge bg-{{ $colorClass }} rounded-pill">{{ $mockCount }}/{{ $totalParticipants }}</span>
                                    </div>
                                    <div class="progress bg-secondary bg-opacity-25" style="height: 6px;">
                                        <div class="progress-bar bg-{{ $colorClass }} rounded-pill" role="progressbar"
                                            style="width: {{ $mockPercent }}%;" aria-valuenow="{{ $mockPercent }}"
                                            aria-valuemin="0" aria-valuemax="100"></div>
                                    </div>
                                    <small class="text-{{ $color }} fw-bold mt-1" style="font-size: 0.75rem;">
                                        <i class="bi bi-{{ $icon }} me-1"></i>{{ $level }} ({{ $mockPercent }}% Selesai)
                                    </small>
                                </a>
                            @empty
                                <div class="text-center text-muted py-5">
                                    <i class="bi bi-inbox fs-2 d-block mb-2"></i>
                                    <p class="mb-0 small">Belum ada soal untuk ujian ini.</p>
                                </div>
                            @endforelse
                        </div>
                    </div>
                </div>
            </div>

            <!-- Kanan: Table Monitoring Peserta -->
            <div class="col-md-9">
                <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
                    <div class="card-header bg-white border-bottom-0 pt-4 pb-3 d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">
                        <h5 class="fw-bold mb-0"><i class="bi bi-people-fill text-primary me-2"></i> Monitoring Peserta Ujian</h5>
                        <div class="d-flex flex-column flex-md-row gap-2">
                            <input type="text" id="searchParticipantInput" class="form-control form-control-sm search-input rounded-pill" style="max-width: 250px;" placeholder="Cari peserta (NIM/Nama)...">
                            <div class="dropdown">
                                <button class="btn btn-sm btn-outline-secondary rounded-pill px-3 shadow-sm dropdown-toggle d-flex align-items-center gap-1 h-100" type="button" id="filterDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                                    <i class="bi bi-funnel-fill"></i> <span id="activeFilterText">Urutkan: Normal</span>
                                </button>
                                <ul class="dropdown-menu dropdown-menu-end shadow rounded-4 border-0" aria-labelledby="filterDropdown">
                                    <li><a class="dropdown-item filter-option active" href="#" data-sort="normal"><i class="bi bi-sort-numeric-down me-2 text-primary"></i> Normal (Skor Tertinggi)</a></li>
                                    <li><a class="dropdown-item filter-option" href="#" data-sort="terbaru"><i class="bi bi-clock-history me-2 text-success"></i> Submission Terakhir</a></li>
                                    <li><a class="dropdown-item filter-option" href="#" data-sort="terlama"><i class="bi bi-clock me-2 text-warning"></i> Submission Terlama</a></li>
                                </ul>
                            </div>
                        </div>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table id="participantTable" class="table table-hover align-middle mb-0">
                                <thead class="table-light">
                                    <tr class="text-nowrap border-bottom border-primary border-2">
                                        <th class="ps-4 py-3">NIM</th>
                                        <th class="py-3">Nama Mahasiswa</th>
                                        <th class="py-3">Status</th>
                                        <th class="py-3">Total Skor</th>
                                        <th class="py-3">Similarity Tertinggi</th>
                                        <th class="pe-4 py-3 text-center">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody class="border-top-0">
                                    @forelse($participants as $p)
                                        @php
                                            $latestSub = $p->submissions->sortByDesc('created_at')->first();
                                            $latestTime = $latestSub ? \Carbon\Carbon::parse($latestSub->created_at)->timestamp : 0;
                                        @endphp
                                        <tr class="participant-row" data-nim="{{ $p->nim_username }}" data-nama="{{ $p->name }}" data-skor="{{ $p->total_skor ?? 0 }}" data-waktu="{{ $latestTime }}">
                                            <td class="ps-4">{{ $p->nim_username }}</td>
                                            <td class="fw-semibold">{{ $p->name }}</td>
                                            <td>
                                                @if ($p->status == 'Lulus')
                                                    <span
                                                        class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25"><i
                                                            class="bi bi-check-circle-fill me-1"></i> Lulus</span>
                                                @else
                                                    <span
                                                        class="badge bg-danger bg-opacity-10 text-danger border border-danger border-opacity-25"><i
                                                            class="bi bi-x-circle-fill me-1"></i> Tidak Lulus</span>
                                                @endif

                                            </td>
                                            <td><strong>{{ $p->total_skor ?? 0 }}</strong> Pts</td>
                                            <td>
                                                @if ($p->highest_similarity !== null)
                                                    @php
                                                        $threshold = config('judge.plagiarism_threshold', 0.75) * 100;
                                                        $warnThreshold = $threshold / 2;
                                                        $pColor = $p->highest_similarity >= $threshold ? 'danger' : ($p->highest_similarity >= $warnThreshold ? 'warning text-dark' : 'success');
                                                    @endphp
                                                    <span class="badge bg-{{ $pColor }}">{{ round($p->highest_similarity, 2) }}%</span>
                                                @else
                                                    <span class="text-muted small">-</span>
                                                @endif
                                            </td>
                                            <td class="pe-4 text-center">
                                                <button class="btn btn-sm btn-outline-primary rounded-pill px-3"
                                                    type="button" data-bs-toggle="collapse"
                                                    data-bs-target="#collapseSubmissions{{ $p->user_id }}"
                                                    aria-expanded="false"
                                                    aria-controls="collapseSubmissions{{ $p->user_id }}"
                                                    onclick="this.querySelector('.bi-chevron-down').classList.toggle('rotate-180')">
                                                    Detail <i class="bi bi-chevron-down ms-1"
                                                        style="transition: transform 0.3s ease; display: inline-block;"></i>
                                                </button>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td colspan="6" class="p-0 border-0">
                                                <div class="collapse" id="collapseSubmissions{{ $p->user_id }}">
                                                    <div
                                                        class="card card-body border-0 bg-light rounded-0 border-start border-4 border-primary m-3 shadow-sm">
                                                        <!-- Status Kesempatan per Soal (di atas) -->
                                                        <div class="mb-3">
                                                            <h6 class="fw-bold mb-2"><i class="bi bi-clock-history text-primary me-2"></i>Status Kesempatan per Soal</h6>
                                                            <div class="d-flex flex-wrap gap-2">
                                                                @foreach($exam->soals as $soal)
                                                                    @php
                                                                        $soalSubmissions = $p->submissions->where('soal_id', $soal->soal_id)->where('is_reset', false);
                                                                        $attemptsUsed = $soalSubmissions->count();
                                                                        $badgeColor = $attemptsUsed >= 3 ? 'danger' : ($attemptsUsed > 0 ? 'warning text-dark' : 'secondary');
                                                                    @endphp
                                                                    <div class="d-flex align-items-center gap-2 bg-white border rounded-pill px-3 py-1 shadow-sm" style="font-size: 0.85rem;">
                                                                        <span class="fw-semibold text-dark text-truncate" style="max-width: 150px;">{{ $soal->nama_soal }}</span>
                                                                        <span class="badge bg-{{ $badgeColor }} rounded-pill">{{ $attemptsUsed }} / 3</span>
                                                                        @if ($attemptsUsed > 0)
                                                                            <form action="{{ route('pengawas.ujian.peserta.reset-attempts', ['examId' => $exam->ujian_id, 'userId' => $p->user_id, 'soalId' => $soal->soal_id]) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin mereset kesempatan submit mahasiswa ini untuk soal {{ $soal->nama_soal }}?');" class="d-inline">
                                                                                @csrf
                                                                                <button type="submit" class="btn btn-xs btn-outline-danger py-0 px-2 rounded-pill" style="font-size: 0.7rem;">
                                                                                    <i class="bi bi-arrow-counterclockwise"></i> Reset
                                                                                </button>
                                                                            </form>
                                                                        @endif
                                                                    </div>
                                                                @endforeach
                                                            </div>
                                                        </div>

                                                        <!-- Riwayat Submission (di bawah) -->
                                                        <div>
                                                            <div class="d-flex justify-content-between align-items-center mb-3">
                                                                <h6 class="fw-bold mb-0"><i class="bi bi-table text-primary me-2"></i>Riwayat Submission</h6>
                                                                <select class="form-select form-select-sm w-auto" onchange="window.filterSubmissions('{{ $p->user_id }}', this.value)">
                                                                    <option value="">Semua Soal</option>
                                                                    @foreach($exam->soals as $soal)
                                                                        <option value="{{ $soal->soal_id }}">{{ $soal->nama_soal }}</option>
                                                                    @endforeach
                                                                </select>
                                                            </div>
                                                            <table id="table-sub-{{ $p->user_id }}" class="table table-sm table-bordered mb-0 submission-table">
                                                                <thead class="table-secondary">
                                                                        <tr>
                                                                            <th>Waktu</th>
                                                                            <th>Soal</th>
                                                                            <th>Status</th>
                                                                            <th>Skor</th>
                                                                            <th>Similarity</th>
                                                                            <th>Catatan</th>
                                                                            <th>Aksi</th>
                                                                        </tr>
                                                                </thead>
                                                                <tbody>
                                                                    @forelse($p->submissions as $s)
                                                                        <tr class="submission-row" data-soal="{{ $s->soal_id }}">
                                                                            <td class="text-nowrap">
                                                                                {{ \Carbon\Carbon::parse($s->created_at)->format('d M Y, H:i') }}
                                                                            </td>
                                                                            <td>{{ $s->nama_soal }}</td>
                                                                            <td>
                                                                                @if ($s->status == 'Accepted')
                                                                                    <span class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25">Accepted</span>
                                                                                @elseif($s->status == 'Wrong Answer')
                                                                                    <span class="badge bg-danger bg-opacity-10 text-danger border border-danger border-opacity-25">Wrong Answer</span>
                                                                                @elseif($s->status == 'Time Limit Exceeded')
                                                                                    <span class="badge bg-warning bg-opacity-10 text-warning border border-warning border-opacity-25">Time Limit Exceeded</span>
                                                                                @elseif($s->status == 'Compilation Error')
                                                                                    <span class="badge bg-secondary bg-opacity-10 text-secondary border border-secondary border-opacity-25">Compilation Error</span>
                                                                                @elseif($s->status == 'Runtime Error')
                                                                                    <span class="badge border border-opacity-25" style="color: #a855f7; background-color: rgba(168,85,247,0.15); border-color: rgba(168,85,247,0.3);">Runtime Error</span>
                                                                                @else
                                                                                    <span class="badge bg-dark bg-opacity-10 text-dark border border-dark border-opacity-25">{{ $s->status }}</span>
                                                                                @endif
                                                                            </td>
                                                                            <td>{{ $s->skor }}</td>
                                                                            <td>
                                                                                @if ($s->similarity_index !== null)
                                                                                    @php
                                                                                        $sColor =
                                                                                            $s->similarity_index >= 70
                                                                                                ? 'danger'
                                                                                                : ($s->similarity_index >=
                                                                                                40
                                                                                                    ? 'warning text-dark'
                                                                                                    : 'success');
                                                                                    @endphp
                                                                                    <span
                                                                                        class="badge bg-{{ $sColor }}">{{ round($s->similarity_index, 2) }}%</span>
                                                                                @else
                                                                                    <span class="text-muted small">-</span>
                                                                                @endif
                                                                            </td>
                                                                            <td>
                                                                                @if ($s->justification_note)
                                                                                    <span class="text-muted small" title="{{ $s->justification_note }}">
                                                                                        {{ \Illuminate\Support\Str::limit($s->justification_note, 30) }}
                                                                                    </span>
                                                                                @else
                                                                                    <span class="text-muted small">-</span>
                                                                                @endif
                                                                            </td>
                                                                            <td>
                                                                                <div class="d-flex gap-2">
                                                                                    <button class="btn btn-sm btn-outline-secondary py-0 d-inline-flex align-items-center justify-content-center" style="width: 32px; height: 32px;" data-bs-toggle="modal" data-bs-target="#codeModal{{ $s->submission_id }}" title="Lihat Kode">
                                                                                        <i class="bi bi-code-slash"></i>
                                                                                    </button>
                                                                                    <button class="btn btn-sm btn-warning py-0 d-inline-flex align-items-center justify-content-center" style="width: 32px; height: 32px;" data-bs-toggle="modal" data-bs-target="#overrideModal{{ $s->submission_id }}" title="Override Skor">
                                                                                        <i class="bi bi-pencil-square"></i>
                                                                                    </button>
                                                                                    <form action="{{ route('pengawas.submission.destroy', $s->submission_id) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus submisi ini?');" class="d-inline">
                                                                                        @csrf
                                                                                        @method('DELETE')
                                                                                        <button type="submit" class="btn btn-sm btn-outline-danger btn-icon-only py-0 d-inline-flex align-items-center justify-content-center" style="width: 32px; height: 32px;" title="Hapus Submisi" data-icon-only>
                                                                                            <i class="bi bi-trash"></i>
                                                                                        </button>
                                                                                    </form>
                                                                                </div>
                                                                            </td>
                                                                        </tr>
                                                                    @empty
                                                                        <tr class="no-submissions-row">
                                                                            <td colspan="7" class="text-center text-muted">
                                                                                Belum ada submission.</td>
                                                                        </tr>
                                                                    @endforelse
                                                                </tbody>
                                                            </table>
                                                        </div>
                                                    </div>
                                                </div>

                                                <!-- Modals for source code -->
                                                @foreach ($p->submissions as $s)
                                                    <div class="modal fade" id="codeModal{{ $s->submission_id }}"
                                                        tabindex="-1" aria-hidden="true">
                                                        <div class="modal-dialog modal-lg modal-dialog-centered">
                                                            <div class="modal-content rounded-4 border-0 shadow">
                                                                <div class="modal-header border-0 pb-0">
                                                                    <h5 class="modal-title fw-bold"><i
                                                                            class="bi bi-file-earmark-code text-primary me-2"></i>Source
                                                                        Code</h5>
                                                                    <button type="button" class="btn-close"
                                                                        data-bs-dismiss="modal"></button>
                                                                </div>
                                                                <div class="modal-body">
                                                                    <pre class="bg-dark text-light p-3 rounded mt-2" style="max-height: 400px; overflow-y: auto;"><code>{{ $s->source_code }}</code></pre>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    
                                                    <!-- Override Modal -->
                                                    <div class="modal fade" id="overrideModal{{ $s->submission_id }}" tabindex="-1" aria-hidden="true">
                                                        <div class="modal-dialog modal-dialog-centered">
                                                            <div class="modal-content rounded-4 border-0 shadow">
                                                                <div class="modal-header border-0 pb-0">
                                                                    <h5 class="modal-title fw-bold"><i class="bi bi-pencil-square text-warning me-2"></i>Override Skor</h5>
                                                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                                </div>
                                                                <form action="{{ route('pengawas.submission.override', $s->submission_id) }}" method="POST">
                                                                    @csrf
                                                                    <div class="modal-body">
                                                                        <p class="mb-2">Soal: <strong>{{ $s->nama_soal }}</strong></p>
                                                                        <p class="mb-3 small text-muted">Ubah skor submisi apabila Anda merasa persentase similarity wajar dan tidak mengindikasikan kecurangan.</p>
                                                                        <div class="mb-3">
                                                                            <label class="form-label fw-semibold">Skor Baru</label>
                                                                            <input type="number" step="1" max="{{ $s->bobot_nilai }}" min="0" name="skor" class="form-control" value="{{ $s->skor }}" required>
                                                                            <small class="text-muted">Maksimal: {{ $s->bobot_nilai }}</small>
                                                                        </div>
                                                                        <div class="mb-3">
                                                                            <label class="form-label fw-semibold">Justification Note (Alasan)</label>
                                                                            <textarea name="justification_note" class="form-control" rows="3" placeholder="Contoh: Kode mirip karena menggunakan boilerplate dari soal..." required>{{ $s->justification_note ?? '' }}</textarea>
                                                                        </div>
                                                                    </div>
                                                                    <div class="modal-footer border-0 pt-0">
                                                                        <button type="button" class="btn btn-secondary rounded-pill px-4" data-bs-dismiss="modal">Batal</button>
                                                                        <button type="submit" class="btn btn-warning rounded-pill px-4 fw-bold">Simpan Perubahan</button>
                                                                    </div>
                                                                </form>
                                                            </div>
                                                        </div>
                                                    </div>
                                                @endforeach
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="6" class="text-center py-5 text-muted">
                                                <i class="bi bi-inbox fs-2 d-block mb-2"></i>
                                                Belum ada peserta yang memulai ujian ini.
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Start Exam Modal -->
    <div class="modal fade" id="startExamModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content rounded-4 border-0 shadow">
                <div class="modal-header border-0 pb-0">
                    <h5 class="modal-title fw-bold"><i class="bi bi-play-circle-fill text-primary me-2"></i>Mulai Ujian?
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p class="mb-0">Yakin ingin memulai ujian <strong>{{ $exam->judul }}</strong>?</p>
                    <p class="text-muted small mt-2 mb-0"><i class="bi bi-info-circle me-1"></i>Token akan dibuat otomatis
                        dan diperbarui setiap 1 menit.</p>
                </div>
                <div class="modal-footer border-0 pt-0">
                    <button type="button" class="btn btn-secondary rounded-pill px-4"
                        data-bs-dismiss="modal">Batal</button>
                    <form method="POST" action="{{ route('pengawas.ujian.start', $exam->ujian_id) }}">
                        @csrf
                        <button type="submit" class="btn btn-primary rounded-pill px-4 fw-bold">
                            <i class="bi bi-play-fill me-1"></i>Ya, Mulai Ujian
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Pause Exam Modal -->
    <div class="modal fade" id="endExamModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content rounded-4 border-0 shadow">
                <div class="modal-header border-0 pb-0">
                    <h5 class="modal-title fw-bold"><i class="bi bi-pause-circle-fill text-warning me-2"></i>Pause
                        Ujian?</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p class="mb-0">Yakin ingin mem-pause ujian <strong>{{ $exam->judul }}</strong>?</p>
                    <p class="text-muted small mt-2 mb-0"><i class="bi bi-info-circle me-1"></i>Token akan dinonaktifkan sementara. Anda bisa memulai ujian kembali nanti.</p>
                </div>
                <div class="modal-footer border-0 pt-0">
                    <button type="button" class="btn btn-secondary rounded-pill px-4"
                        data-bs-dismiss="modal">Batal</button>
                    <form method="POST" action="{{ route('pengawas.ujian.end', $exam->ujian_id) }}">
                        @csrf
                        <button type="submit" class="btn btn-warning rounded-pill px-4 fw-bold">
                            <i class="bi bi-pause-fill me-1"></i>Ya, Pause Ujian
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Finish Exam Modal (Permanent) -->
    <div class="modal fade" id="finishExamModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content rounded-4 border-0 shadow">
                <div class="modal-header border-0 pb-0">
                    <h5 class="modal-title fw-bold"><i class="bi bi-exclamation-triangle-fill text-danger me-2"></i>Akhiri Ujian Permanen?</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p class="mb-0">Yakin ingin <strong>mengakhiri</strong> ujian <strong>{{ $exam->judul }}</strong> secara permanen?</p>
                    <div class="alert alert-danger small mt-3 mb-0">
                        <i class="bi bi-exclamation-diamond-fill me-1"></i> <strong>Perhatian:</strong> Setelah diakhiri, ujian ini tidak bisa dimulai kembali. Pastikan semua peserta sudah selesai mengerjakan.
                    </div>
                </div>
                <div class="modal-footer border-0 pt-0">
                    <button type="button" class="btn btn-secondary rounded-pill px-4"
                        data-bs-dismiss="modal">Batal</button>
                    <form method="POST" action="{{ route('pengawas.ujian.finish', $exam->ujian_id) }}">
                        @csrf
                        <button type="submit" class="btn btn-danger rounded-pill px-4 fw-bold">
                            <i class="bi bi-stop-fill me-1"></i>Ya, Akhiri Permanen
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script>
        (function() {
            const examId = {{ $exam->ujian_id }};
            const isActive = {{ $exam->status === 'active' ? 'true' : 'false' }};
            const REFRESH_INTERVAL = 60; // seconds

            if (!isActive) return;

            const tokenDisplay = document.getElementById('tokenDisplay');
            const countdownSeconds = document.getElementById('countdownSeconds');
            const countdownBar = document.getElementById('countdownBar');
            const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

            let secondsLeft = REFRESH_INTERVAL;
            let countdownTimer = null;

            // Calculate initial seconds based on token creation time
            @if ($activeToken)
                (function() {
                    const tokenCreated = new Date('{{ $activeToken->created_at->toISOString() }}');
                    const now = new Date();
                    const elapsed = Math.floor((now - tokenCreated) / 1000);
                    secondsLeft = Math.max(0, REFRESH_INTERVAL - elapsed);
                    if (secondsLeft <= 0) {
                        refreshToken();
                    }
                })();
            @endif

            function updateCountdown() {
                secondsLeft--;
                if (secondsLeft < 0) secondsLeft = 0;

                countdownSeconds.textContent = secondsLeft;
                const percentage = (secondsLeft / REFRESH_INTERVAL) * 100;
                countdownBar.style.width = percentage + '%';

                if (secondsLeft <= 0) {
                    refreshToken();
                }
            }

            async function refreshToken() {
                try {
                    // Visual feedback
                    tokenDisplay.classList.add('refreshing');

                    const response = await fetch(`/pengawas/ujian/${examId}/token/refresh`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': csrfToken,
                            'Accept': 'application/json',
                        },
                    });

                    if (!response.ok) throw new Error('Failed to refresh');

                    const data = await response.json();

                    setTimeout(() => {
                        tokenDisplay.textContent = data.token;
                        tokenDisplay.classList.remove('refreshing');
                    }, 300);

                    // Reset countdown
                    secondsLeft = REFRESH_INTERVAL;
                    countdownBar.style.width = '100%';
                } catch (err) {
                    console.error('Token refresh failed:', err);
                    tokenDisplay.classList.remove('refreshing');
                    secondsLeft = 5; // retry in 5 seconds
                }
            }

            // Exam real-time countdown timer
            let examRemaining = {{ $exam->getRemainingSeconds() }};
            const examTimerDisplay = document.getElementById('examTimerDisplay');
            let examTimer = null;
            let statusPoll = null;

            function formatTime(seconds) {
                const totalSeconds = Math.floor(seconds);
                const h = Math.floor(totalSeconds / 3600);
                const m = Math.floor((totalSeconds % 3600) / 60);
                const s = totalSeconds % 60;
                return [
                    h.toString().padStart(2, '0'),
                    m.toString().padStart(2, '0'),
                    s.toString().padStart(2, '0')
                ].join(':');
            }

            function updateExamTimer() {
                if (examRemaining <= 0) {
                    if (examTimerDisplay) examTimerDisplay.textContent = '00:00:00';
                    window.location.reload();
                    return;
                }
                if (examTimerDisplay) {
                    examTimerDisplay.textContent = formatTime(examRemaining);
                }
                examRemaining--;
            }

            if (examTimerDisplay) {
                updateExamTimer();
                examTimer = setInterval(updateExamTimer, 1000);
            }

            // Start countdown
            countdownTimer = setInterval(updateCountdown, 1000);

            // Poll for exam status and sync remaining time every 5 seconds
            if (isActive) {
                statusPoll = setInterval(async () => {
                    try {
                        const response = await fetch(`/ujian/${examId}/status`);
                        if (response.ok) {
                            const data = await response.json();
                            if (data.status !== 'active') {
                                window.location.reload();
                                return;
                            }
                            examRemaining = data.remainingSeconds;
                        }
                    } catch (error) {
                        console.error("Gagal memeriksa status ujian:", error);
                    }
                }, 5000);
            }

            // Cleanup on page unload
            window.addEventListener('beforeunload', () => {
                if (countdownTimer) clearInterval(countdownTimer);
                if (examTimer) clearInterval(examTimer);
                if (statusPoll) clearInterval(statusPoll);
            });
        })();

        document.addEventListener('DOMContentLoaded', () => {
            window.paginateTable = function(tableId, rowsPerPage, filterSoalId = '') {
                const table = document.getElementById(tableId);
                if (!table) return;
                const tbody = table.querySelector('tbody');
                if (!tbody) return;

                // Hapus container pagination yang sudah ada jika ada
                const existingPagination = document.getElementById(tableId + '-pagination');
                if (existingPagination) {
                    existingPagination.remove();
                }

                const allRows = Array.from(tbody.querySelectorAll('.submission-row'));
                const noSubRow = tbody.querySelector('.no-submissions-row');

                if (noSubRow) {
                    noSubRow.style.display = '';
                    return;
                }

                // Filter baris
                const rows = allRows.filter(row => {
                    if (filterSoalId === '' || row.getAttribute('data-soal') === filterSoalId) {
                        return true;
                    }
                    row.style.display = 'none';
                    return false;
                });

                // Hapus empty state row jika ada
                const existingEmptyRow = tbody.querySelector('.empty-state-row');
                if (existingEmptyRow) {
                    existingEmptyRow.remove();
                }

                const totalRows = rows.length;
                if (totalRows === 0) {
                    const emptyRow = document.createElement('tr');
                    emptyRow.className = 'empty-state-row';
                    const colCount = table.querySelector('thead tr').children.length;
                    emptyRow.innerHTML = `<td colspan="${colCount}" class="text-center text-muted">Tidak ada submission untuk soal ini.</td>`;
                    tbody.appendChild(emptyRow);
                    return;
                }

                let currentPage = 1;

                if (totalRows <= rowsPerPage) {
                    rows.forEach(row => {
                        row.style.display = '';
                    });
                    // Sembunyikan baris lain yang tidak termasuk filter
                    allRows.forEach(row => {
                        if (!rows.includes(row)) {
                            row.style.display = 'none';
                        }
                    });
                    return;
                }

                const totalPages = Math.ceil(totalRows / rowsPerPage);

                const paginationContainer = document.createElement('div');
                paginationContainer.className = 'd-flex flex-column flex-md-row justify-content-between align-items-center mt-2 px-2';
                paginationContainer.id = tableId + '-pagination';
                table.parentNode.insertBefore(paginationContainer, table.nextSibling);

                function renderTable() {
                    const start = (currentPage - 1) * rowsPerPage;
                    const end = start + rowsPerPage;
                    rows.forEach((row, index) => {
                        row.style.display = (index >= start && index < end) ? '' : 'none';
                    });
                    allRows.forEach(row => {
                        if (!rows.includes(row)) {
                            row.style.display = 'none';
                        }
                    });
                    renderPagination();
                }

                function renderPagination() {
                    paginationContainer.innerHTML = '';
                    const startItem = (currentPage - 1) * rowsPerPage + 1;
                    const endItem = Math.min(currentPage * rowsPerPage, totalRows);

                    const summarySpan = document.createElement('span');
                    summarySpan.className = 'text-muted small fw-semibold mb-2 mb-md-0';
                    summarySpan.innerText = `Menampilkan ${startItem} hingga ${endItem} dari ${totalRows} submisi`;
                    paginationContainer.appendChild(summarySpan);

                    const nav = document.createElement('nav');
                    const ul = document.createElement('ul');
                    ul.className = 'pagination pagination-sm mb-0';

                    const prevLi = document.createElement('li');
                    prevLi.className = `page-item ${currentPage === 1 ? 'disabled' : ''}`;
                    prevLi.innerHTML = `<a class="page-link" href="#" aria-label="Previous"><span aria-hidden="true">&laquo;</span></a>`;
                    prevLi.onclick = (e) => {
                        e.preventDefault();
                        if (currentPage > 1) { currentPage--; renderTable(); }
                    };
                    ul.appendChild(prevLi);

                    for (let i = 1; i <= totalPages; i++) {
                        const pageLi = document.createElement('li');
                        pageLi.className = `page-item ${currentPage === i ? 'active' : ''}`;
                        pageLi.innerHTML = `<a class="page-link" href="#">${i}</a>`;
                        pageLi.onclick = (e) => {
                            e.preventDefault();
                            currentPage = i;
                            renderTable();
                        };
                        ul.appendChild(pageLi);
                    }

                    const nextLi = document.createElement('li');
                    nextLi.className = `page-item ${currentPage === totalPages ? 'disabled' : ''}`;
                    nextLi.innerHTML = `<a class="page-link" href="#" aria-label="Next"><span aria-hidden="true">&raquo;</span></a>`;
                    nextLi.onclick = (e) => {
                        e.preventDefault();
                        if (currentPage < totalPages) { currentPage++; renderTable(); }
                    };
                    ul.appendChild(nextLi);

                    nav.appendChild(ul);
                    paginationContainer.appendChild(nav);
                }

                renderTable();
            }

            window.filterSubmissions = function(userId, soalId) {
                window.paginateTable('table-sub-' + userId, 5, soalId);
            }

            document.querySelectorAll('.submission-table').forEach(table => {
                window.paginateTable(table.id, 5); // Tampilkan 5 per halaman
            });

            function initParticipantTablePagination() {
                const searchInput = document.getElementById('searchParticipantInput');
                const table = document.getElementById('participantTable');
                if (!table) return;
                const tbody = table.querySelector('tbody');
                if (!tbody) return;

                const allRows = Array.from(tbody.querySelectorAll('.participant-row')).map(row => {
                    return {
                        mainRow: row,
                        collapseRow: row.nextElementSibling,
                        skor: parseFloat(row.getAttribute('data-skor')) || 0,
                        waktu: parseInt(row.getAttribute('data-waktu')) || 0,
                        nim: (row.getAttribute('data-nim') || '').toLowerCase(),
                        nama: (row.getAttribute('data-nama') || '').toLowerCase()
                    };
                });

                const rowsPerPage = 10;
                let currentPage = 1;
                let currentSearchTerm = '';
                let currentSort = 'normal';

                const paginationContainer = document.createElement('div');
                paginationContainer.className = 'd-flex flex-column flex-md-row justify-content-between align-items-center mt-3 px-3 pb-3';
                paginationContainer.id = 'participantTable-pagination';
                table.parentNode.insertBefore(paginationContainer, table.nextSibling);

                function updateTable() {
                    let filteredItems = allRows.filter(item => {
                        return item.nim.includes(currentSearchTerm) || item.nama.includes(currentSearchTerm);
                    });

                    filteredItems.sort((a, b) => {
                        if (currentSort === 'terbaru') {
                            return b.waktu - a.waktu;
                        } else if (currentSort === 'terlama') {
                            return a.waktu - b.waktu;
                        } else {
                            return b.skor - a.skor;
                        }
                    });

                    // Re-append to tbody to reflect sorted order in DOM
                    filteredItems.forEach(item => {
                        tbody.appendChild(item.mainRow);
                        if (item.collapseRow) tbody.appendChild(item.collapseRow);
                    });

                    const totalRows = filteredItems.length;
                    const totalPages = Math.ceil(totalRows / rowsPerPage) || 1;
                    if (currentPage > totalPages) currentPage = totalPages;

                    const start = (currentPage - 1) * rowsPerPage;
                    const end = start + rowsPerPage;

                    allRows.forEach(item => { 
                        item.mainRow.style.display = 'none'; 
                        if (item.collapseRow) item.collapseRow.style.display = 'none';
                    });

                    filteredItems.forEach((item, index) => {
                        if (index >= start && index < end) {
                            item.mainRow.style.display = '';
                            if (item.collapseRow) item.collapseRow.style.display = '';
                        }
                    });

                    renderPagination(totalRows, totalPages);
                }

                function renderPagination(totalRows, totalPages) {
                    paginationContainer.innerHTML = '';
                    if (totalRows === 0) {
                        const emptySpan = document.createElement('span');
                        emptySpan.className = 'text-muted text-muted small fw-semibold';
                        emptySpan.innerText = 'Tidak ada peserta yang ditemukan';
                        paginationContainer.appendChild(emptySpan);
                        return;
                    }

                    const startItem = (currentPage - 1) * rowsPerPage + 1;
                    const endItem = Math.min(currentPage * rowsPerPage, totalRows);

                    const summarySpan = document.createElement('span');
                    summarySpan.className = 'text-muted small fw-semibold mb-2 mb-md-0';
                    summarySpan.innerText = `Menampilkan ${startItem} hingga ${endItem} dari ${totalRows} peserta`;
                    paginationContainer.appendChild(summarySpan);

                    if (totalPages <= 1) return;

                    const nav = document.createElement('nav');
                    const ul = document.createElement('ul');
                    ul.className = 'pagination pagination-sm mb-0';

                    const prevLi = document.createElement('li');
                    prevLi.className = `page-item ${currentPage === 1 ? 'disabled' : ''}`;
                    prevLi.innerHTML = `<a class="page-link" href="#" aria-label="Previous"><span aria-hidden="true">&laquo;</span></a>`;
                    prevLi.onclick = (e) => {
                        e.preventDefault();
                        if (currentPage > 1) { currentPage--; updateTable(); }
                    };
                    ul.appendChild(prevLi);

                    for (let i = 1; i <= totalPages; i++) {
                        const pageLi = document.createElement('li');
                        pageLi.className = `page-item ${currentPage === i ? 'active' : ''}`;
                        pageLi.innerHTML = `<a class="page-link" href="#">${i}</a>`;
                        pageLi.onclick = (e) => {
                            e.preventDefault();
                            currentPage = i;
                            updateTable();
                        };
                        ul.appendChild(pageLi);
                    }

                    const nextLi = document.createElement('li');
                    nextLi.className = `page-item ${currentPage === totalPages ? 'disabled' : ''}`;
                    nextLi.innerHTML = `<a class="page-link" href="#" aria-label="Next"><span aria-hidden="true">&raquo;</span></a>`;
                    nextLi.onclick = (e) => {
                        e.preventDefault();
                        if (currentPage < totalPages) { currentPage++; updateTable(); }
                    };
                    ul.appendChild(nextLi);

                    nav.appendChild(ul);
                    paginationContainer.appendChild(nav);
                }

                if (searchInput) {
                    searchInput.addEventListener('keyup', function() {
                        currentSearchTerm = this.value.toLowerCase();
                        currentPage = 1;
                        updateTable();
                    });
                }

                document.querySelectorAll('.filter-option').forEach(option => {
                    option.addEventListener('click', function(e) {
                        e.preventDefault();
                        document.querySelectorAll('.filter-option').forEach(opt => opt.classList.remove('active'));
                        this.classList.add('active');
                        currentSort = this.getAttribute('data-sort');
                        const activeText = document.getElementById('activeFilterText');
                        if (activeText) {
                            if (currentSort === 'terbaru') activeText.innerText = 'Urutkan: Terbaru';
                            else if (currentSort === 'terlama') activeText.innerText = 'Urutkan: Terlama';
                            else activeText.innerText = 'Urutkan: Normal';
                        }
                        currentPage = 1;
                        updateTable();
                    });
                });

                updateTable();
            }

            initParticipantTablePagination();
        });
    </script>

    <style>
        /* Sembunyikan teks bahasa Inggris bawaan Laravel dari pagination */
        .pagination-sm nav div.d-sm-flex > div:first-child,
        .pagination-sm p.small.text-muted {
            display: none !important;
        }
    </style>
@endsection
